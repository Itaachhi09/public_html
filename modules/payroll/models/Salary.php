<?php
/**
 * Salary Model
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class Salary extends BaseModel {
    protected $table = 'employee_salaries';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'basic_rate', 'salary_type', 'effective_date', 'tax_status', 'payroll_eligible'];

    /**
     * Get all salaries with employee info
     */
    public function getAllWithEmployee() {
        return $this->query(
            "SELECT s.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} s
             JOIN employees e ON e.employee_id = s.employee_id
             ORDER BY e.last_name, s.effective_date DESC"
        );
    }

    /**
     * Get current salary for employee
     */
    public function getCurrentForEmployee($employeeId) {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE employee_id = ? AND effective_date <= CURDATE()
             ORDER BY effective_date DESC LIMIT 1",
            [$employeeId],
            true
        );
    }

    /**
     * Get payroll-eligible employees
     */
    public function getPayrollEligible() {
        return $this->query(
            "SELECT DISTINCT e.employee_id, e.employee_code, e.first_name, e.last_name, e.department_id,
                    s.basic_rate, s.salary_type
             FROM {$this->table} s
             JOIN employees e ON e.employee_id = s.employee_id
             WHERE s.payroll_eligible = 1 AND e.employment_status = 'Active'
             AND s.effective_date <= CURDATE()
             ORDER BY e.last_name"
        );
    }
}
?>
