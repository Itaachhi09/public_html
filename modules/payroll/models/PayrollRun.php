<?php
/**
 * Payroll Run Model
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollRun extends BaseModel {
    protected $table = 'payroll_runs';
    protected $primaryKey = 'id';
    protected $fillable = ['period_name', 'start_date', 'end_date', 'pay_date', 'status', 'created_by'];

    /**
     * Get all payroll runs
     */
    public function getAll() {
        return $this->query("SELECT * FROM {$this->table} ORDER BY start_date DESC");
    }

    /**
     * Get payroll runs by status
     */
    public function getByStatus($status) {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE status = ? ORDER BY start_date DESC",
            [$status]
        );
    }

    /**
     * Get payroll run with employee details
     */
    public function getWithEmployees($id) {
        $run = $this->find($id);
        if (!$run) return null;

        $employees = $this->query(
            "SELECT e.*, pr.gross_pay, pr.total_deductions, pr.net_pay
             FROM payroll_run_employees pr
             JOIN employees e ON e.employee_id = pr.employee_id
             WHERE pr.payroll_run_id = ?
             ORDER BY e.last_name",
            [$id]
        );

        $run['employees'] = $employees;
        return $run;
    }
}
?>
