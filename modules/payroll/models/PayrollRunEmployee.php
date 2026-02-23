<?php
/**
 * Payroll Run Employee Model (payslip data per run)
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollRunEmployee extends BaseModel {
    protected $table = 'payroll_run_employees';
    protected $primaryKey = 'id';
    protected $fillable = [
        'payroll_run_id', 
        'employee_id', 
        'basic_pay', 
        'incentives',
        'gross_pay', 
        'withholding_tax',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'total_deductions', 
        'net_pay', 
        'payslip_number', 
        'status'
    ];

    /**
     * Get all for a run with employee details
     */
    public function getByRunWithEmployee($payrollRunId) {
        return $this->query(
            "SELECT pre.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} pre
             JOIN employees e ON e.employee_id = pre.employee_id
             WHERE pre.payroll_run_id = ?
             ORDER BY e.last_name",
            [$payrollRunId]
        );
    }

    /**
     * Get all payslips (all runs) with employee and run info
     */
    public function getAllPayslips() {
        return $this->query(
            "SELECT pre.*, e.employee_code, e.first_name, e.last_name, pr.period_name, pr.start_date, pr.end_date, pr.pay_date, pr.status AS run_status
             FROM {$this->table} pre
             JOIN employees e ON e.employee_id = pre.employee_id
             JOIN payroll_runs pr ON pr.id = pre.payroll_run_id
             ORDER BY pr.start_date DESC, e.last_name"
        );
    }
}
