<?php
/**
 * Billing and Reconciliation Model
 * 
 * Finance validation and billing reconciliation
 * 
 * Inputs:
 * - Provider billing file
 * - Monthly premium statement
 * 
 * System data:
 * - Active enrollment list
 * - Payroll deduction records
 * 
 * Reconciliation:
 * - Match provider billing to system enrollment
 * - Detect missing employees
 * - Detect extra billed employees
 * - Detect premium mismatches
 * 
 * Variance handling:
 * - Overpayment identified and logged
 * - Underpayment identified and logged
 * - Variance reason required
 */

require_once __DIR__ . '/../../config/BaseModel.php';

class BillingReconciliation extends BaseModel {
    protected $table = 'hmo_billing_reconciliation';

    /**
     * Get all billing records
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT br.*,
                   COUNT(DISTINCT bd.id) as discrepancy_count
            FROM {$this->table} br
            LEFT JOIN hmo_billing_discrepancies bd ON br.id = bd.reconciliation_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND br.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['billing_month'])) {
            $sql .= " AND DATE_FORMAT(br.billing_month, '%Y-%m') = ?";
            $params[] = $filters['billing_month'];
        }

        $sql .= " GROUP BY br.id ORDER BY br.billing_month DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get billing record by ID
     */
    public function getById($id) {
        return $this->queryOne("
            SELECT * FROM {$this->table}
            WHERE id = ?
        ", [$id]);
    }

    /**
     * Create billing reconciliation record
     */
    public function create($data) {
        $sql = "
            INSERT INTO {$this->table}
            (billing_month, provider_billing_amount, system_premium_amount,
             active_enrollments_count, tolerance_percentage, status,
             prepared_by, prepared_date, reviewed_by, reviewed_date, 
             created_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, NOW(), ?)
        ";

        $params = [
            $data['billing_month'],
            $data['provider_billing_amount'] ?? 0,
            $data['system_premium_amount'] ?? 0,
            $data['active_enrollments_count'] ?? 0,
            $data['tolerance_percentage'] ?? 2,
            $data['status'] ?? 'pending',
            $data['prepared_by'] ?? null,
            $data['reviewed_by'] ?? null,
            $data['reviewed_date'] ?? null,
            null
        ];

        return $this->execute($sql, $params);
    }

    /**
     * Update reconciliation status
     */
    public function updateStatus($id, $status, $reviewedBy = null) {
        $sql = "UPDATE {$this->table} SET status = ?";
        $params = [$status];

        if ($reviewedBy) {
            $sql .= ", reviewed_by = ?, reviewed_date = NOW()";
            $params[] = $reviewedBy;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        return $this->execute($sql, $params);
    }

    /**
     * Record billing discrepancy
     */
    public function recordDiscrepancy($reconciliationId, $data) {
        $sql = "
            INSERT INTO hmo_billing_discrepancies
            (reconciliation_id, discrepancy_type, employee_id, enrollment_id,
             system_amount, provider_amount, variance_amount, variance_reason,
             provider_name, status, created_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ";

        $params = [
            $reconciliationId,
            $data['discrepancy_type'], // overpayment, underpayment, missing, extra
            $data['employee_id'] ?? null,
            $data['enrollment_id'] ?? null,
            $data['system_amount'] ?? 0,
            $data['provider_amount'] ?? 0,
            $data['variance_amount'] ?? 0,
            $data['variance_reason'] ?? null,
            $data['provider_name'] ?? null,
            $data['status'] ?? 'pending',
            null
        ];

        return $this->execute($sql, $params);
    }

    /**
     * Get discrepancies for reconciliation
     */
    public function getDiscrepancies($reconciliationId) {
        return $this->query("
            SELECT bd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   hp.plan_name
            FROM hmo_billing_discrepancies bd
            LEFT JOIN employees emp ON bd.employee_id = emp.id
            LEFT JOIN hmo_plans hp ON bd.enrollment_id IN (
                SELECT plan_id FROM employee_hmo_enrollments WHERE id = bd.enrollment_id
            )
            WHERE bd.reconciliation_id = ?
            ORDER BY bd.discrepancy_type, bd.variance_amount DESC
        ", [$reconciliationId]);
    }

    /**
     * Generate discrepancy report
     */
    public function generateDiscrepancyReport($reconciliationId) {
        $reconciliation = $this->getById($reconciliationId);
        if (!$reconciliation) {
            return ['success' => false, 'error' => 'Reconciliation not found'];
        }

        $discrepancies = $this->getDiscrepancies($reconciliationId);
        
        $summary = [
            'reconciliation_id' => $reconciliationId,
            'billing_month' => $reconciliation['billing_month'],
            'provider_billing_amount' => $reconciliation['provider_billing_amount'],
            'system_premium_amount' => $reconciliation['system_premium_amount'],
            'variance_amount' => $reconciliation['provider_billing_amount'] - $reconciliation['system_premium_amount'],
            'variance_percentage' => $reconciliation['system_premium_amount'] > 0 
                ? round((($reconciliation['provider_billing_amount'] - $reconciliation['system_premium_amount']) / $reconciliation['system_premium_amount']) * 100, 2)
                : 0,
            'total_discrepancies' => count($discrepancies),
            'overpayments' => 0,
            'underpayments' => 0,
            'missing_employees' => 0,
            'extra_billed' => 0,
            'total_overpayment' => 0,
            'total_underpayment' => 0
        ];

        foreach ($discrepancies as $disc) {
            switch ($disc['discrepancy_type']) {
                case 'overpayment':
                    $summary['overpayments']++;
                    $summary['total_overpayment'] += $disc['variance_amount'];
                    break;
                case 'underpayment':
                    $summary['underpayments']++;
                    $summary['total_underpayment'] += abs($disc['variance_amount']);
                    break;
                case 'missing':
                    $summary['missing_employees']++;
                    break;
                case 'extra':
                    $summary['extra_billed']++;
                    break;
            }
        }

        return [
            'success' => true,
            'summary' => $summary,
            'discrepancies' => $discrepancies
        ];
    }

    /**
     * Calculate variance tolerance
     */
    public function calculateVarianceTolerance($reconciliationId) {
        $reconciliation = $this->getById($reconciliationId);
        if (!$reconciliation) {
            return ['success' => false, 'error' => 'Reconciliation not found'];
        }

        $variance = $reconciliation['provider_billing_amount'] - $reconciliation['system_premium_amount'];
        $tolerance = ($reconciliation['system_premium_amount'] * $reconciliation['tolerance_percentage']) / 100;
        $isWithinTolerance = abs($variance) <= $tolerance;

        return [
            'success' => true,
            'variance' => $variance,
            'tolerance' => $tolerance,
            'is_within_tolerance' => $isWithinTolerance,
            'variance_percentage' => $reconciliation['system_premium_amount'] > 0 
                ? round(($variance / $reconciliation['system_premium_amount']) * 100, 2)
                : 0
        ];
    }

    /**
     * Get reconciliation summary
     */
    public function getSummary($year = null, $month = null) {
        $sql = "
            SELECT 
                DATE_FORMAT(br.billing_month, '%Y-%m') as period,
                COUNT(*) as reconciliation_count,
                SUM(br.provider_billing_amount) as total_provider_billing,
                SUM(br.system_premium_amount) as total_system_premium,
                SUM(br.provider_billing_amount) - SUM(br.system_premium_amount) as total_variance,
                COUNT(CASE WHEN br.status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN br.status = 'pending' THEN 1 END) as pending_count
            FROM {$this->table} br
            WHERE 1=1
        ";

        $params = [];

        if ($year) {
            $sql .= " AND YEAR(br.billing_month) = ?";
            $params[] = $year;
        }

        if ($month) {
            $sql .= " AND MONTH(br.billing_month) = ?";
            $params[] = $month;
        }

        $sql .= " GROUP BY DATE_FORMAT(br.billing_month, '%Y-%m')
                  ORDER BY br.billing_month DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get adjustments required
     */
    public function getAdjustmentsRequired() {
        return $this->query("
            SELECT bd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   br.billing_month
            FROM hmo_billing_discrepancies bd
            JOIN {$this->table} br ON bd.reconciliation_id = br.id
            WHERE bd.status = 'pending'
              AND bd.discrepancy_type IN ('overpayment', 'underpayment')
            ORDER BY br.billing_month DESC, bd.variance_amount DESC
        ");
    }

    /**
     * Approve discrepancy adjustment
     */
    public function approveAdjustment($discrepancyId, $approvalNotes = null) {
        return $this->execute("
            UPDATE hmo_billing_discrepancies
            SET status = 'approved', approval_notes = ?
            WHERE id = ?
        ", [$approvalNotes, $discrepancyId]);
    }

    /**
     * Get billing statistics
     */
    public function getStatistics() {
        return $this->queryOne("
            SELECT 
                COUNT(*) as total_reconciliations,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                AVG(tolerance_percentage) as avg_tolerance,
                SUM(CASE WHEN provider_billing_amount > system_premium_amount THEN (provider_billing_amount - system_premium_amount) ELSE 0 END) as total_overpayments,
                SUM(CASE WHEN system_premium_amount > provider_billing_amount THEN (system_premium_amount - provider_billing_amount) ELSE 0 END) as total_underpayments
            FROM {$this->table}
        ");
    }
}

