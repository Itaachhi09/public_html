<?php
/**
 * Employee HMO Enrollment Model
 * Manages employee plan assignments and enrollment tracking
 * 
 * Features:
 * - Plan assignment & coverage tracking
 * - Enrollment lifecycle management
 * - Waiting period auto-activation
 * - Plan changes & upgrades/downgrades
 * - Dependent management
 * - Payroll & Finance integration
 * - HR Core integration
 */

require_once(__DIR__ . '/../../../config/BaseModel.php');

class EmployeeHMOEnrollment extends BaseModel {
    protected $table = 'employee_hmo_enrollments';
    
    protected $fillable = [
        'employee_id',
        'plan_id',
        'provider_id',
        'enrollment_date',
        'effective_date',
        'coverage_end_date',
        'renewal_date',
        'termination_date',
        'enrollment_status',
        'coverage_type',
        'waiting_period_days',
        'waiting_period_start_date',
        'waiting_period_end_date',
        'is_primary_enrollment',
        'employee_premium_amount',
        'employer_share_amount',
        'total_premium_amount',
        'suspension_reason',
        'created_at',
        'updated_at'
    ];

    // Enrollment Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_WAITING_PERIOD = 'waiting_period';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_EXPIRED = 'expired';

    // Coverage Type Constants
    const COVERAGE_EMPLOYEE_ONLY = 'employee_only';
    const COVERAGE_WITH_DEPENDENTS = 'with_dependents';

    /**
     * A. PLAN ASSIGNMENT METHODS
     */
    
    public function getEmployeeEnrollments($employee_id) {
        $query = "SELECT e.*, 
                         hp.plan_name, hp.plan_code,
                         hpr.provider_name, hpr.provider_code,
                         COUNT(DISTINCT d.id) as dependent_count
                  FROM {$this->table} e 
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                  LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id
                  LEFT JOIN hmo_enrollment_dependents d ON e.id = d.enrollment_id AND d.is_active = 1
                  WHERE e.employee_id = " . intval($employee_id) . " 
                  GROUP BY e.id
                  ORDER BY e.effective_date DESC";
        return $this->query($query);
    }

    public function getEnrollmentWithDependents($enrollment_id) {
        $query = "SELECT e.*, 
                         hp.plan_name, hp.plan_code, hp.coverage_details,
                         hpr.provider_name, hpr.provider_code
                  FROM {$this->table} e 
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                  LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id
                  WHERE e.id = " . intval($enrollment_id);
        return $this->queryOne($query);
    }

    public function getDependents($enrollment_id) {
        $query = "SELECT * FROM hmo_enrollment_dependents 
                  WHERE enrollment_id = " . intval($enrollment_id) . " 
                  AND is_active = 1
                  ORDER BY created_at ASC";
        return $this->query($query);
    }

    /**
     * B. ENROLLMENT DATES - Status Tracking Methods
     */

    public function getActiveEnrollments() {
        $query = "SELECT e.*, hp.plan_name, hp.plan_code,
                         hpr.provider_name, hpr.provider_code,
                         emp.first_name, emp.last_name, emp.employee_code
                  FROM {$this->table} e 
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                  LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id
                  LEFT JOIN employees emp ON e.employee_id = emp.employee_id
                  WHERE e.enrollment_status = 'active' 
                  AND e.effective_date <= NOW() 
                  AND (e.coverage_end_date IS NULL OR e.coverage_end_date > NOW())
                  AND (e.termination_date IS NULL OR e.termination_date > NOW())
                  ORDER BY e.employee_id ASC";
        return $this->query($query);
    }

    public function getPendingEnrollments() {
        $query = "SELECT e.*, hp.plan_name, hp.plan_code,
                         hpr.provider_name, hpr.provider_code,
                         emp.first_name, emp.last_name, emp.employee_code
                  FROM {$this->table} e 
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                  LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id
                  LEFT JOIN employees emp ON e.employee_id = emp.employee_id
                  WHERE e.enrollment_status = 'pending' 
                  ORDER BY e.enrollment_date DESC";
        return $this->query($query);
    }

    /**
     * C. ENROLLMENT STATUS TRACKING
     */

    public function getWaitingPeriodEnrollments() {
        $query = "SELECT e.*, hp.plan_name, hp.plan_code,
                         hpr.provider_name, hpr.provider_code,
                         emp.first_name, emp.last_name, emp.employee_code,
                         DATEDIFF(e.waiting_period_end_date, NOW()) as days_remaining
                  FROM {$this->table} e 
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                  LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id
                  LEFT JOIN employees emp ON e.employee_id = emp.employee_id
                  WHERE e.enrollment_status = 'waiting_period'
                  AND e.waiting_period_end_date > NOW()
                  ORDER BY e.waiting_period_end_date ASC";
        return $this->query($query);
    }

    public function getSuspendedEnrollments() {
        $query = "SELECT e.*, hp.plan_name, hp.plan_code,
                         hpr.provider_name, hpr.provider_code,
                         emp.first_name, emp.last_name, emp.employee_code
                  FROM {$this->table} e 
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                  LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id
                  LEFT JOIN employees emp ON e.employee_id = emp.employee_id
                  WHERE e.enrollment_status = 'suspended'
                  ORDER BY e.updated_at DESC";
        return $this->query($query);
    }

    public function getTerminatedEnrollments() {
        $query = "SELECT e.*, hp.plan_name, hp.plan_code,
                         hpr.provider_name, hpr.provider_code,
                         emp.first_name, emp.last_name, emp.employee_code
                  FROM {$this->table} e 
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                  LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id
                  LEFT JOIN employees emp ON e.employee_id = emp.employee_id
                  WHERE e.enrollment_status IN ('terminated', 'expired')
                  ORDER BY e.termination_date DESC";
        return $this->query($query);
    }

    /**
     * D. WAITING PERIOD MANAGEMENT
     */

    public function autoActivateWaitingPeriodEnrollments() {
        $query = "UPDATE {$this->table} 
                  SET enrollment_status = 'active',
                      updated_at = NOW()
                  WHERE enrollment_status = 'waiting_period'
                  AND waiting_period_end_date <= NOW()";
        return $this->execute($query);
    }

    public function overrideWaitingPeriod($enrollment_id, $admin_notes = '') {
        $query = "UPDATE {$this->table} 
                  SET enrollment_status = 'active',
                      waiting_period_end_date = NOW(),
                      updated_at = NOW()
                  WHERE id = " . intval($enrollment_id);
        $result = $this->execute($query);
        
        if ($result) {
            $this->logActivity($enrollment_id, 'waiting_period_override', $admin_notes);
        }
        return $result;
    }

    /**
     * E. UPGRADE / DOWNGRADE HANDLING
     */

    public function requestPlanChange($enrollment_id, $new_plan_id, $effective_date = null, $reason = '') {
        $effective_date = $effective_date ?? date('Y-m-d');
        
        $query = "INSERT INTO hmo_plan_change_requests 
                  (enrollment_id, old_plan_id, new_plan_id, request_date, effective_date, reason, status)
                  SELECT id, plan_id, " . intval($new_plan_id) . ", NOW(), ?, ?, 'pending'
                  FROM {$this->table}
                  WHERE id = " . intval($enrollment_id);
        
        return $this->execute($query, [$effective_date, $reason]);
    }

    public function approvePlanChange($change_request_id) {
        $query = "SELECT * FROM hmo_plan_change_requests WHERE id = " . intval($change_request_id);
        $changeRequest = $this->queryOne($query);
        
        if (!$changeRequest) return false;

        // Get old premium and calculate adjustment
        $oldPremium = $this->getPlanPremiumAmount($changeRequest['old_plan_id']);
        $newPremium = $this->getPlanPremiumAmount($changeRequest['new_plan_id']);
        $adjustment = $newPremium - $oldPremium;

        // Update enrollment
        $updateQuery = "UPDATE {$this->table} 
                        SET plan_id = " . intval($changeRequest['new_plan_id']) . ",
                            employee_premium_amount = " . floatval($newPremium) . ",
                            updated_at = NOW()
                        WHERE id = " . intval($changeRequest['enrollment_id']);
        
        $result = $this->execute($updateQuery);
        
        if ($result) {
            // Update change request status
            $statusQuery = "UPDATE hmo_plan_change_requests 
                            SET status = 'approved', approved_date = NOW()
                            WHERE id = " . intval($change_request_id);
            $this->execute($statusQuery);

            // Log activity
            $this->logActivity($changeRequest['enrollment_id'], 'plan_change', 
                             "Plan changed from {$changeRequest['old_plan_id']} to {$changeRequest['new_plan_id']}. Adjustment: {$adjustment}");
        }

        return $result;
    }

    public function rejectPlanChange($change_request_id, $reason = '') {
        $query = "UPDATE hmo_plan_change_requests 
                  SET status = 'rejected', rejection_reason = ?, rejected_date = NOW()
                  WHERE id = " . intval($change_request_id);
        
        return $this->execute($query, [$reason]);
    }

    public function getPlanChangeHistory($enrollment_id) {
        $query = "SELECT * FROM hmo_plan_change_requests 
                  WHERE enrollment_id = " . intval($enrollment_id) . "
                  ORDER BY request_date DESC";
        return $this->query($query);
    }

    /**
     * F. PAYROLL & FINANCE INTEGRATION
     */

    public function getPlanPremiumAmount($plan_id) {
        $query = "SELECT premium_amount FROM hmo_plans WHERE id = " . intval($plan_id);
        $result = $this->queryOne($query);
        return $result ? $result['premium_amount'] : 0;
    }

    public function calculateProratedDeduction($enrollment_id, $start_date, $end_date) {
        $enrollment = $this->queryOne("SELECT * FROM {$this->table} WHERE id = " . intval($enrollment_id));
        if (!$enrollment) return 0;

        $days_in_period = (strtotime($end_date) - strtotime($start_date)) / 86400;
        $days_in_month = 30; // Standard calculation
        $daily_rate = $enrollment['employee_premium_amount'] / $days_in_month;
        
        return $daily_rate * $days_in_period;
    }

    public function getPayrollDeductionSummary($employee_id, $payroll_month) {
        $query = "SELECT e.id, e.employee_id, e.plan_id, 
                         hp.plan_name, hp.plan_code,
                         e.employee_premium_amount, e.employer_share_amount,
                         (e.employee_premium_amount + e.employer_share_amount) as total_premium,
                         e.enrollment_status
                  FROM {$this->table} e
                  LEFT JOIN hmo_plans hp ON e.plan_id = hp.id
                  WHERE e.employee_id = " . intval($employee_id) . "
                  AND e.enrollment_status = 'active'
                  AND e.effective_date <= LAST_DAY(?)
                  AND (e.termination_date IS NULL OR e.termination_date > ?)";
        
        return $this->queryOne($query, [$payroll_month, $payroll_month]);
    }

    /**
     * G. HR CORE INTEGRATION
     */

    public function terminateOnEmployeeSeparation($employee_id, $termination_date, $reason = 'Employee Separation') {
        $query = "UPDATE {$this->table} 
                  SET enrollment_status = 'terminated',
                      termination_date = ?,
                      updated_at = NOW()
                  WHERE employee_id = " . intval($employee_id) . "
                  AND enrollment_status IN ('active', 'waiting_period')";
        
        $result = $this->execute($query, [$termination_date]);
        
        if ($result) {
            $this->logActivity(0, 'employee_separation', 
                             "Employee $employee_id separated on $termination_date. HMO enrollments terminated.");
        }
        
        return $result;
    }

    public function suspendOnLeave($employee_id, $leave_start_date) {
        $query = "UPDATE {$this->table} 
                  SET enrollment_status = 'suspended',
                      suspension_reason = 'Leave Without Pay',
                      updated_at = NOW()
                  WHERE employee_id = " . intval($employee_id) . "
                  AND enrollment_status = 'active'";
        
        $this->execute($query);
        
        $this->logActivity(0, 'leave_suspension', 
                          "Employee $employee_id on leave. HMO enrollments suspended from $leave_start_date.");
        
        return true;
    }

    public function resumeAfterLeave($employee_id, $resume_date) {
        $query = "UPDATE {$this->table} 
                  SET enrollment_status = 'active',
                      suspension_reason = NULL,
                      updated_at = NOW()
                  WHERE employee_id = " . intval($employee_id) . "
                  AND enrollment_status = 'suspended'
                  AND suspension_reason = 'Leave Without Pay'";
        
        $this->execute($query);
        
        $this->logActivity(0, 'leave_resume', 
                          "Employee $employee_id resumed from leave. HMO enrollments reactivated from $resume_date.");
        
        return true;
    }

    public function validateEmployeeEligibility($employee_id) {
        $query = "SELECT e.employment_type_id, e.employment_status 
                  FROM employees e
                  WHERE e.employee_id = " . intval($employee_id);
        
        $employee = $this->queryOne($query);
        
        if (!$employee) return ['eligible' => false, 'reason' => 'Employee not found'];
        
        if (!in_array($employee['employment_status'], ['active', 'on_leave'])) {
            return ['eligible' => false, 'reason' => 'Invalid employment status'];
        }
        
        return ['eligible' => true, 'reason' => 'Employee is eligible for HMO enrollment'];
    }

    /**
     * UTILITY METHODS
     */

    private function logActivity($enrollment_id, $activity_type, $notes = '') {
        $query = "INSERT INTO hmo_enrollment_activity_log (enrollment_id, activity_type, notes, created_at)
                  VALUES (?, ?, ?, NOW())";
        
        return $this->execute($query, [$enrollment_id, $activity_type, $notes]);
    }

    public function getActivityLog($enrollment_id, $limit = 50) {
        $query = "SELECT * FROM hmo_enrollment_activity_log 
                  WHERE enrollment_id = " . intval($enrollment_id) . "
                  ORDER BY created_at DESC
                  LIMIT " . intval($limit);
        
        return $this->query($query);
    }

    public function getEnrollmentStats() {
        $query = "SELECT 
                    COUNT(CASE WHEN enrollment_status = 'active' THEN 1 END) as active_count,
                    COUNT(CASE WHEN enrollment_status = 'pending' THEN 1 END) as pending_count,
                    COUNT(CASE WHEN enrollment_status = 'waiting_period' THEN 1 END) as waiting_count,
                    COUNT(CASE WHEN enrollment_status = 'suspended' THEN 1 END) as suspended_count,
                    COUNT(CASE WHEN enrollment_status IN ('terminated', 'expired') THEN 1 END) as terminated_count,
                    SUM(CASE WHEN enrollment_status = 'active' THEN employee_premium_amount ELSE 0 END) as total_employee_premium,
                    SUM(CASE WHEN enrollment_status = 'active' THEN employer_share_amount ELSE 0 END) as total_employer_contribution
                  FROM {$this->table}";
        
        return $this->queryOne($query);
    }
}
