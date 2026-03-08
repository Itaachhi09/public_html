<?php
/**
 * Deduction Model
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class Deduction extends BaseModel {
    protected $table = 'deductions';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'payroll_run_id', 'type', 'amount', 'description', 'created_by'];

    /**
     * Get all deductions
     */
    public function getAll() {
        return $this->query(
            "SELECT d.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} d
             JOIN employees e ON e.employee_id = d.employee_id
             ORDER BY d.created_at DESC"
        );
    }

    /**
     * Get by payroll run
     */
    public function getByPayrollRun($payrollRunId) {
        return $this->query(
            "SELECT d.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} d
             JOIN employees e ON e.employee_id = d.employee_id
             WHERE d.payroll_run_id = ?
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

    /**
     * Get by type (tax, contribution, loan, etc)
     */
    public function getByType($type) {
        return $this->query(
            "SELECT d.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} d
             JOIN employees e ON e.employee_id = d.employee_id
             WHERE d.type = ?
             ORDER BY d.created_at DESC",
            [$type]
        );
    }
}
?>
