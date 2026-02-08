<?php
/**
 * Employee Salary Model
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeeSalary extends BaseModel {
    protected $table = 'employee_salaries';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'salary_type', 'basic_rate', 'effective_date', 'tax_status', 'payroll_eligible', 'bank_name', 'bank_account'];

    /**
     * Get all with employee names
     */
    public function getAllWithEmployee() {
        return $this->query(
            "SELECT es.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} es
             JOIN employees e ON e.employee_id = es.employee_id
             ORDER BY e.last_name, es.effective_date DESC"
        );
    }

    /**
     * Get current salary for employee (latest effective_date)
     */
    public function getCurrentForEmployee($employeeId) {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE employee_id = ? ORDER BY effective_date DESC LIMIT 1",
            [$employeeId]
        );
    }

    /**
     * Get payroll-eligible employees with current salary (latest effective per employee)
     */
    public function getPayrollEligible() {
        return $this->query(
            "SELECT es.*, e.employee_code, e.first_name, e.last_name
             FROM {$this->table} es
             JOIN employees e ON e.employee_id = es.employee_id AND e.employment_status = 'Active'
             WHERE es.payroll_eligible = 1 AND es.effective_date <= CURDATE()
             AND es.id = (SELECT MAX(es2.id) FROM {$this->table} es2 WHERE es2.employee_id = es.employee_id AND es2.effective_date <= CURDATE())"
        );
    }
}
