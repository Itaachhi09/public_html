<?php
/**
 * Claims and Utilization Model
 * 
 * Manages HMO claims, benefit tracking, and service utilization monitoring
 * 
 * Claims Records:
 * - Employee and dependent claims
 * - Provider and plan reference
 * - Service types (Inpatient, Outpatient, ER, Dental, etc.)
 * - Claim amounts and dates
 * 
 * Balance Tracking:
 * - Annual benefit limits
 * - Amount used to date
 * - Remaining balance
 * 
 * Usage Controls:
 * - Claim validation against plan status
 * - Benefit exhaustion prevention
 * - Coverage type verification
 * 
 * Reporting:
 * - High utilization employees
 * - High cost plans
 * - Provider comparisons
 * - Cost trend analysis
 */

require_once __DIR__ . '/../../config/BaseModel.php';

class Claims extends BaseModel {
    protected $table = 'hmo_claims';

    /**
     * Get all claims with filters
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT c.*, 
                   e.employee_code,
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   p.plan_name,
                   pr.provider_name,
                   ee.coverage_type,
                   d.first_name as dependent_first_name,
                   d.last_name as dependent_last_name
            FROM {$this->table} c
            LEFT JOIN employees e ON c.employee_id = e.id
            LEFT JOIN hmo_plans p ON c.plan_id = p.id
            LEFT JOIN hmo_providers pr ON c.provider_id = pr.id
            LEFT JOIN employee_hmo_enrollments ee ON c.enrollment_id = ee.id
            LEFT JOIN hmo_dependents d ON c.dependent_id = d.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['plan_id'])) {
            $sql .= " AND c.plan_id = ?";
            $params[] = $filters['plan_id'];
        }

        if (!empty($filters['provider_id'])) {
            $sql .= " AND c.provider_id = ?";
            $params[] = $filters['provider_id'];
        }

        if (!empty($filters['service_type'])) {
            $sql .= " AND c.service_type = ?";
            $params[] = $filters['service_type'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(c.service_date) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(c.service_date) <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY c.service_date DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get claim by ID
     */
    public function getById($id) {
        return $this->queryOne("
            SELECT c.*, 
                   e.employee_code,
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   p.plan_name,
                   pr.provider_name,
                   ee.coverage_type
            FROM {$this->table} c
            LEFT JOIN employees e ON c.employee_id = e.id
            LEFT JOIN hmo_plans p ON c.plan_id = p.id
            LEFT JOIN hmo_providers pr ON c.provider_id = pr.id
            LEFT JOIN employee_hmo_enrollments ee ON c.enrollment_id = ee.id
            WHERE c.id = ?
        ", [$id]);
    }

    /**
     * Get claims for employee
     */
    public function getByEmployee($employeeId, $year = null) {
        $sql = "
            SELECT c.*, 
                   p.plan_name,
                   pr.provider_name,
                   ee.coverage_type
            FROM {$this->table} c
            LEFT JOIN hmo_plans p ON c.plan_id = p.id
            LEFT JOIN hmo_providers pr ON c.provider_id = pr.id
            LEFT JOIN employee_hmo_enrollments ee ON c.enrollment_id = ee.id
            WHERE c.employee_id = ?
        ";

        $params = [$employeeId];

        if ($year) {
            $sql .= " AND YEAR(c.service_date) = ?";
            $params[] = $year;
        }

        $sql .= " ORDER BY c.service_date DESC";

        return $this->query($sql, $params);
    }

    /**
     * Create new claim record
     */
    public function create($data) {
        $sql = "
            INSERT INTO {$this->table}
            (enrollment_id, employee_id, dependent_id, plan_id, provider_id,
             service_type, claim_amount, service_date, claim_status,
             notes, created_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ";

        $params = [
            $data['enrollment_id'],
            $data['employee_id'],
            $data['dependent_id'] ?? null,
            $data['plan_id'],
            $data['provider_id'],
            $data['service_type'],
            $data['claim_amount'],
            $data['service_date'],
            $data['claim_status'] ?? 'pending',
            $data['notes'] ?? null,
            null
        ];

        return $this->execute($sql, $params);
    }

    /**
     * Update claim status (approve, reject, etc.)
     */
    public function updateStatus($id, $status, $notes = null) {
        $sql = "UPDATE {$this->table} SET claim_status = ?, notes = ? WHERE id = ?";
        return $this->execute($sql, [$status, $notes, $id]);
    }

    /**
     * Validate claim against plan
     */
    public function validateClaim($enrollmentId, $planId, $claimAmount) {
        // Check if enrollment is active
        $enrollment = $this->queryOne("
            SELECT * FROM employee_hmo_enrollments
            WHERE id = ? AND enrollment_status = 'active'
        ", [$enrollmentId]);

        if (!$enrollment) {
            return ['valid' => false, 'reason' => 'Enrollment is not active'];
        }

        // Check plan benefits
        $plan = $this->queryOne("
            SELECT * FROM hmo_plans WHERE id = ?
        ", [$planId]);

        if (!$plan) {
            return ['valid' => false, 'reason' => 'Plan not found'];
        }

        // Check annual benefit limit
        $usedAmount = $this->queryOne("
            SELECT SUM(claim_amount) as total_used
            FROM {$this->table}
            WHERE enrollment_id = ?
              AND YEAR(service_date) = YEAR(NOW())
              AND claim_status = 'approved'
        ", [$enrollmentId]);

        $usedAmount = $usedAmount['total_used'] ?? 0;
        $benefitLimit = $plan['annual_benefit_limit'] ?? 0;

        if ($benefitLimit > 0 && ($usedAmount + $claimAmount) > $benefitLimit) {
            return [
                'valid' => false,
                'reason' => 'Claim exceeds annual benefit limit',
                'limit' => $benefitLimit,
                'used' => $usedAmount,
                'remaining' => max(0, $benefitLimit - $usedAmount)
            ];
        }

        return ['valid' => true];
    }

    /**
     * Get benefit balance for employee
     */
    public function getBenefitBalance($enrollmentId, $planId) {
        $plan = $this->queryOne("
            SELECT annual_benefit_limit FROM hmo_plans WHERE id = ?
        ", [$planId]);

        $benefitLimit = $plan['annual_benefit_limit'] ?? 0;

        $used = $this->queryOne("
            SELECT SUM(claim_amount) as total_used
            FROM {$this->table}
            WHERE enrollment_id = ?
              AND YEAR(service_date) = YEAR(NOW())
              AND claim_status = 'approved'
        ", [$enrollmentId]);

        $usedAmount = $used['total_used'] ?? 0;

        return [
            'benefit_limit' => $benefitLimit,
            'amount_used' => $usedAmount,
            'remaining_balance' => max(0, $benefitLimit - $usedAmount),
            'utilization_percent' => $benefitLimit > 0 ? round(($usedAmount / $benefitLimit) * 100, 2) : 0
        ];
    }

    /**
     * Get claims by service type
     */
    public function getClaimsByServiceType($serviceType, $startDate = null, $endDate = null) {
        $sql = "
            SELECT c.*, 
                   e.employee_code,
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   p.plan_name,
                   pr.provider_name
            FROM {$this->table} c
            LEFT JOIN employees e ON c.employee_id = e.id
            LEFT JOIN hmo_plans p ON c.plan_id = p.id
            LEFT JOIN hmo_providers pr ON c.provider_id = pr.id
            WHERE c.service_type = ? AND c.claim_status = 'approved'
        ";

        $params = [$serviceType];

        if ($startDate) {
            $sql .= " AND DATE(c.service_date) >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND DATE(c.service_date) <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY c.service_date DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get high utilization employees
     */
    public function getHighUtilizationEmployees($threshold = 80, $limit = 20) {
        return $this->query("
            SELECT e.id, e.employee_code,
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   p.plan_name,
                   hp.annual_benefit_limit,
                   SUM(c.claim_amount) as total_claims,
                   COUNT(c.id) as claim_count,
                   ROUND((SUM(c.claim_amount) / hp.annual_benefit_limit) * 100, 2) as utilization_percent
            FROM {$this->table} c
            JOIN employees e ON c.employee_id = e.id
            JOIN hmo_plans p ON c.plan_id = p.id
            JOIN hmo_premium_cost_sharing hp ON p.id = hp.plan_id
            WHERE YEAR(c.service_date) = YEAR(NOW())
              AND c.claim_status = 'approved'
            GROUP BY e.id, p.id
            HAVING utilization_percent >= ?
            ORDER BY utilization_percent DESC
            LIMIT ?
        ", [$threshold, $limit]);
    }

    /**
     * Get high cost plans
     */
    public function getHighCostPlans($limit = 10) {
        return $this->query("
            SELECT p.id, p.plan_name,
                   pr.provider_name,
                   COUNT(c.id) as claim_count,
                   SUM(c.claim_amount) as total_claims,
                   ROUND(AVG(c.claim_amount), 2) as average_claim,
                   COUNT(DISTINCT c.employee_id) as affected_employees
            FROM {$this->table} c
            JOIN hmo_plans p ON c.plan_id = p.id
            JOIN hmo_providers pr ON p.provider_id = pr.id
            WHERE YEAR(c.service_date) = YEAR(NOW())
              AND c.claim_status = 'approved'
            GROUP BY p.id
            ORDER BY total_claims DESC
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Get provider comparison
     */
    public function getProviderComparison($planId = null) {
        $sql = "
            SELECT pr.id, pr.provider_name,
                   COUNT(c.id) as claim_count,
                   SUM(c.claim_amount) as total_claims,
                   ROUND(AVG(c.claim_amount), 2) as average_claim,
                   COUNT(DISTINCT c.employee_id) as unique_patients,
                   ROUND((SUM(c.claim_amount) / (SELECT SUM(claim_amount) FROM {$this->table} WHERE claim_status = 'approved' AND YEAR(service_date) = YEAR(NOW())) * 100), 2) as market_share
            FROM {$this->table} c
            JOIN hmo_providers pr ON c.provider_id = pr.id
            WHERE YEAR(c.service_date) = YEAR(NOW())
              AND c.claim_status = 'approved'
        ";

        $params = [];

        if ($planId) {
            $sql .= " AND c.plan_id = ?";
            $params[] = $planId;
        }

        $sql .= " GROUP BY pr.id ORDER BY total_claims DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get cost trend analysis
     */
    public function getCostTrends($months = 12) {
        return $this->query("
            SELECT 
                DATE_FORMAT(c.service_date, '%Y-%m') as period,
                COUNT(c.id) as claim_count,
                SUM(c.claim_amount) as total_claims,
                ROUND(AVG(c.claim_amount), 2) as average_claim,
                COUNT(DISTINCT c.employee_id) as unique_employees
            FROM {$this->table} c
            WHERE c.claim_status = 'approved'
              AND c.service_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(c.service_date, '%Y-%m')
            ORDER BY period ASC
        ", [$months]);
    }

    /**
     * Get claims by service type statistics
     */
    public function getServiceTypeStatistics() {
        return $this->query("
            SELECT 
                service_type,
                COUNT(id) as claim_count,
                SUM(claim_amount) as total_amount,
                ROUND(AVG(claim_amount), 2) as average_amount,
                MIN(claim_amount) as minimum_amount,
                MAX(claim_amount) as maximum_amount,
                COUNT(DISTINCT employee_id) as unique_employees
            FROM {$this->table}
            WHERE claim_status = 'approved'
              AND YEAR(service_date) = YEAR(NOW())
            GROUP BY service_type
            ORDER BY total_amount DESC
        ");
    }

    /**
     * Get utilization statistics
     */
    public function getUtilizationStatistics() {
        return $this->queryOne("
            SELECT 
                COUNT(DISTINCT employee_id) as total_claimants,
                COUNT(DISTINCT dependent_id) as dependent_claims,
                COUNT(id) as total_claims,
                SUM(claim_amount) as total_claims_amount,
                ROUND(AVG(claim_amount), 2) as average_claim_amount,
                COUNT(CASE WHEN claim_status = 'pending' THEN 1 END) as pending_claims,
                COUNT(CASE WHEN claim_status = 'approved' THEN 1 END) as approved_claims,
                COUNT(CASE WHEN claim_status = 'rejected' THEN 1 END) as rejected_claims
            FROM {$this->table}
            WHERE YEAR(service_date) = YEAR(NOW())
        ");
    }
}

