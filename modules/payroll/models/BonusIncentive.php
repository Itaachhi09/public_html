<?php
/**
 * Bonus & Incentive Model
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class BonusIncentive extends BaseModel {
    protected $table = 'bonus_incentives';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'payroll_run_id', 'type', 'amount', 'description', 'created_by'];

    /**
     * Get all bonus/incentives
     */
    public function getAll() {
        return $this->query(
            "SELECT b.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} b
             JOIN employees e ON e.employee_id = b.employee_id
             ORDER BY b.created_at DESC"
        );
    }

    /**
     * Get by payroll run
     */
    public function getByPayrollRun($payrollRunId) {
        return $this->query(
            "SELECT b.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} b
             JOIN employees e ON e.employee_id = b.employee_id
             WHERE b.payroll_run_id = ?
             ORDER BY e.last_name",
            [$payrollRunId]
        );
    }

    /**
     * Get by employee
     */
    public function getByEmployee($employeeId) {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE employee_id = ?
             ORDER BY created_at DESC",
            [$employeeId]
        );
    }
}
?>
