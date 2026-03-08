<?php
/**
 * Payslip Model
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class Payslip extends BaseModel {
    protected $table = 'payslip_records';
    protected $primaryKey = 'id';
    protected $fillable = ['payroll_run_id', 'employee_id', 'gross_pay', 'total_deductions', 'net_pay', 'status'];

    /**
     * Get all payslips
     */
    public function getAll() {
        return $this->query(
            "SELECT p.*, e.employee_code, e.first_name, e.last_name, pr.period_name, pr.pay_date
             FROM {$this->table} p
             JOIN payroll_runs pr ON pr.id = p.payroll_run_id
             JOIN employees e ON e.employee_id = p.employee_id
             ORDER BY pr.start_date DESC, e.last_name"
        );
    }

    /**
     * Get payslips by payroll run
     */
    public function getByPayrollRun($payrollRunId) {
        return $this->query(
            "SELECT p.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} p
             JOIN employees e ON e.employee_id = p.employee_id
             WHERE p.payroll_run_id = ?
             ORDER BY e.last_name",
            [$payrollRunId]
        );
    }

    /**
     * Get payslip for employee in payroll run
     */
    public function getEmployeePayslip($payrollRunId, $employeeId) {
        return $this->query(
            "SELECT p.*, e.employee_code, e.first_name, e.last_name,
                    pr.period_name, pr.start_date, pr.end_date, pr.pay_date
             FROM {$this->table} p
             JOIN payroll_runs pr ON pr.id = p.payroll_run_id
             JOIN employees e ON e.employee_id = p.employee_id
             WHERE p.payroll_run_id = ? AND p.employee_id = ?
             LIMIT 1",
            [$payrollRunId, $employeeId],
            true
        );
    }

    /**
     * Get payslip history for employee
     */
    public function getEmployeeHistory($employeeId, $limit = 12) {
        return $this->query(
            "SELECT p.*, pr.period_name, pr.start_date, pr.end_date, pr.pay_date
             FROM {$this->table} p
             JOIN payroll_runs pr ON pr.id = p.payroll_run_id
             WHERE p.employee_id = ?
             ORDER BY pr.start_date DESC
             LIMIT ?",
            [$employeeId, $limit]
        );
    }
}
?>
