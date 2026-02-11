<?php
/**
 * EmployeePayrollProfile Model
 * Manages employee payroll eligibility and profile details
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeePayrollProfile extends BaseModel {
    protected $table = 'employee_payroll_profiles';

    public function getAll() {
        return $this->all();
    }

    public function getByEmployee($employeeId) {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE employee_id = ?", [$employeeId]);
    }

    public function getEligibleEmployees() {
        return $this->query("SELECT * FROM {$this->table} WHERE payroll_eligible = 1 ORDER BY employee_id");
    }
}
