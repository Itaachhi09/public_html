<?php
/**
 * Premium and Cost Sharing Model
 * 
 * Manages premium structure, cost sharing rules, payroll integration, and adjustments
 * 
 * Premium Structure:
 * - Total premium per plan
 * - Employer paid portion
 * - Employee paid portion
 * 
 * Cost Sharing Rules:
 * - Fixed amount or percentage based
 * - Different rates per coverage type
 * - Separate rates for dependents
 * 
 * Payroll Integration:
 * - Employee portion deduction mapping
 * - Frequency aligned with payroll cycle
 * - Deduction dates (effective to termination)
 * 
 * Adjustments:
 * - Retro premium computation
 * - Backdated deductions
 * - Adjustment records per payroll run
 */

require_once __DIR__ . '/../../config/BaseModel.php';

class PremiumCostSharing extends BaseModel
{
    protected $table = 'hmo_premium_cost_sharing';

    /**
     * Get premium configuration for a plan
     */
    public function getPremiumByPlan($planId)
    {
        return $this->queryOne("
            SELECT * FROM hmo_premium_cost_sharing 
            WHERE plan_id = ? AND is_active = 1
        ", [$planId]);
    }

    /**
     * Get active premium rules
     */
    public function getActivePremiums($limit = 100)
    {
        return $this->query("
            SELECT pcs.*,
                   p.plan_name,
                   COUNT(eee.id) as total_enrollments
            FROM hmo_premium_cost_sharing pcs
            LEFT JOIN hmo_plans p ON p.id = pcs.plan_id
            LEFT JOIN employee_hmo_enrollments eee ON eee.plan_id = p.id AND eee.enrollment_status = 'active'
            WHERE pcs.is_active = 1
            GROUP BY pcs.id
            ORDER BY p.plan_name
            LIMIT ?
        ", [$limit]);
    }

    /**
     * Get all premium rules with filters
     */
    public function getAllPremiums($filters = [])
    {
        $query = "
            SELECT pcs.*,
                   p.plan_name,
                   COUNT(eee.id) as total_enrollments
            FROM hmo_premium_cost_sharing pcs
            LEFT JOIN hmo_plans p ON p.id = pcs.plan_id
            LEFT JOIN employee_hmo_enrollments eee ON eee.plan_id = p.id AND eee.enrollment_status = 'active'
        ";

        $params = [];
        $conditions = [];

        if (!empty($filters['plan_id'])) {
            $conditions[] = "pcs.plan_id = ?";
            $params[] = $filters['plan_id'];
        }

        if (isset($filters['is_active'])) {
            $conditions[] = "pcs.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY pcs.id ORDER BY p.plan_name";

        return $this->query($query, $params);
    }

    /**
     * Get cost sharing rules by coverage type
     */
    public function getCostSharingByType($planId, $coverageType)
    {
        return $this->queryOne("
            SELECT * FROM hmo_cost_sharing_rules 
            WHERE plan_id = ? AND coverage_type = ? AND is_active = 1
        ", [$planId, $coverageType]);
    }

    /**
     * Calculate employee share for enrollment
     */
    public function calculateEmployeeShare($planId, $coverageType, $dependentCount = 0)
    {
        $rule = $this->getCostSharingByType($planId, $coverageType);
        
        if (!$rule) {
            return 0;
        }

        $baseAmount = $rule['is_percentage'] 
            ? ($rule['total_premium'] * $rule['employee_share_rate'] / 100)
            : $rule['employee_share_fixed'];

        // Add dependent charges if applicable
        if ($dependentCount > 0 && $rule['dependent_additional_rate']) {
            $dependentCharge = $rule['is_percentage']
                ? ($rule['total_premium'] * $rule['dependent_additional_rate'] / 100 * $dependentCount)
                : ($rule['dependent_additional_rate'] * $dependentCount);
            $baseAmount += $dependentCharge;
        }

        return round($baseAmount, 2);
    }

    /**
     * Calculate employer share for enrollment
     */
    public function calculateEmployerShare($planId, $coverageType, $dependentCount = 0)
    {
        $rule = $this->getCostSharingByType($planId, $coverageType);
        
        if (!$rule) {
            return 0;
        }

        $totalPremium = $rule['total_premium'];
        $employeeShare = $this->calculateEmployeeShare($planId, $coverageType, $dependentCount);
        
        return round($totalPremium - $employeeShare, 2);
    }

    /**
     * Get active deductions for an enrollment
     */
    public function getDeductionsByEnrollment($enrollmentId)
    {
        return $this->query("
            SELECT pd.*,
                   p.payroll_month,
                   p.payroll_status
            FROM hmo_payroll_deductions pd
            LEFT JOIN payroll_runs p ON p.id = pd.payroll_run_id
            WHERE pd.enrollment_id = ? AND pd.is_active = 1
            ORDER BY p.payroll_month DESC
        ", [$enrollmentId]);
    }

    /**
     * Create payroll deduction for enrollment
     */
    public function createPayrollDeduction($enrollmentData)
    {
        return $this->execute("
            INSERT INTO hmo_payroll_deductions 
            (enrollment_id, payroll_run_id, employee_code, deduction_amount, deduction_date, 
             deduction_frequency, payroll_code, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ", [
            $enrollmentData['enrollment_id'],
            $enrollmentData['payroll_run_id'] ?? null,
            $enrollmentData['employee_code'],
            $enrollmentData['deduction_amount'],
            $enrollmentData['deduction_date'],
            $enrollmentData['deduction_frequency'],
            $enrollmentData['payroll_code'],
        ]);
    }

    /**
     * Record premium adjustment
     */
    public function recordAdjustment($adjustmentData)
    {
        return $this->execute("
            INSERT INTO hmo_premium_adjustments 
            (enrollment_id, adjustment_type, original_amount, adjusted_amount, reason, 
             effective_date, payroll_run_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ", [
            $adjustmentData['enrollment_id'],
            $adjustmentData['adjustment_type'],
            $adjustmentData['original_amount'],
            $adjustmentData['adjusted_amount'],
            $adjustmentData['reason'],
            $adjustmentData['effective_date'],
            $adjustmentData['payroll_run_id'] ?? null,
        ]);
    }

    /**
     * Get adjustment history for enrollment
     */
    public function getAdjustmentHistory($enrollmentId)
    {
        return $this->query("
            SELECT * FROM hmo_premium_adjustments 
            WHERE enrollment_id = ?
            ORDER BY effective_date DESC
        ", [$enrollmentId]);
    }

    /**
     * Validate premium configuration
     */
    public function validatePremium($premiumData)
    {
        $errors = [];

        // Validate total premium
        if (empty($premiumData['total_premium']) || $premiumData['total_premium'] <= 0) {
            $errors[] = 'Total premium must be greater than 0';
        }

        // Validate employer share
        if (empty($premiumData['employer_share']) || $premiumData['employer_share'] < 0) {
            $errors[] = 'Employer share must be non-negative';
        }

        // Validate employee share
        if (empty($premiumData['employee_share']) || $premiumData['employee_share'] < 0) {
            $errors[] = 'Employee share must be non-negative';
        }

        // Validate that employer + employee = total
        $total = floatval($premiumData['employer_share']) + floatval($premiumData['employee_share']);
        if (abs($total - floatval($premiumData['total_premium'])) > 0.01) {
            $errors[] = 'Employer share + Employee share must equal total premium';
        }

        return $errors;
    }

    /**
     * Get premium statistics
     */
    public function getPremiumStats()
    {
        return $this->queryOne("
            SELECT 
                COUNT(*) as total_premiums,
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_premiums,
                AVG(total_premium) as avg_premium,
                SUM(total_premium) as total_premium_sum,
                AVG(employee_share) as avg_employee_share,
                AVG(employer_share) as avg_employer_share
            FROM hmo_premium_cost_sharing
        ");
    }

    /**
     * Get payroll deduction statistics
     */
    public function getDeductionStats($startDate = null, $endDate = null)
    {
        $query = "
            SELECT 
                COUNT(*) as total_deductions,
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_deductions,
                SUM(deduction_amount) as total_deducted,
                AVG(deduction_amount) as avg_deduction,
                MIN(deduction_date) as first_deduction,
                MAX(deduction_date) as last_deduction
            FROM hmo_payroll_deductions
            WHERE 1=1
        ";

        $params = [];

        if ($startDate) {
            $query .= " AND deduction_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $query .= " AND deduction_date <= ?";
            $params[] = $endDate;
        }

        return $this->queryOne($query, $params);
    }

    /**
     * Stop deduction on termination
     */
    public function stopDeductionOnTermination($enrollmentId, $terminationDate)
    {
        return $this->execute("
            UPDATE hmo_payroll_deductions 
            SET is_active = 0, deduction_end_date = ?
            WHERE enrollment_id = ? AND is_active = 1
        ", [$terminationDate, $enrollmentId]);
    }

    /**
     * Get deductions blocked by closed payroll period
     */
    public function getBlockedDeductions()
    {
        return $this->query("
            SELECT pd.*, pr.payroll_status
            FROM hmo_payroll_deductions pd
            LEFT JOIN payroll_runs pr ON pr.id = pd.payroll_run_id
            WHERE pr.payroll_status = 'closed'
            ORDER BY pd.created_at DESC
        ");
    }
}
