<?php
/**
 * EmployeePayrollProfile Model
 * Manages employee payroll eligibility and profile details
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeePayrollProfile extends BaseModel {
    protected $table = 'employee_payroll_profiles';
    protected $fillable = [
        'employee_id',
        'payroll_eligible',
        'payroll_frequency',
        'pay_schedule_day_1',
        'pay_schedule_day_2',
        'pay_type',
        'payroll_status',
        'tax_status',
        'sss_status',
        'philhealth_status',
        'pagibig_status',
        'bank_account_holder',
        'bank_account_number',
        'bank_code',
        'bank_name',
        'account_type',
        'account_status',
        'tax_identification_number',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
    ];

    public function getAll() {
        return $this->query("SELECT * FROM {$this->table} ORDER BY employee_id");
    }

    public function getByEmployee($employeeId) {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE employee_id = ?", [$employeeId]);
    }

    public function getEligibleEmployees() {
        return $this->query("SELECT * FROM {$this->table} WHERE payroll_eligible = 1 ORDER BY employee_id");
    }

    /**
     * Get all employees with optional payroll profile and department/job title for listing
     */
    public function getEmployeesWithProfile($search = '') {
        $sql = "SELECT e.employee_id, e.employee_code, e.first_name, e.last_name,
                e.department_id, e.job_title_id,
                d.department_name,
                j.title AS job_title,
                p.id AS profile_id, p.payroll_eligible, p.payroll_frequency, p.pay_type, p.payroll_status
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN job_titles j ON e.job_title_id = j.job_title_id
                LEFT JOIN {$this->table} p ON p.employee_id = e.employee_id
                WHERE e.employment_status IN ('Active', 'On Leave', 'Suspended')";
        $params = [];
        if ($search !== '') {
            $sql .= " AND (e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
            $term = '%' . $search . '%';
            $params = [$term, $term, $term];
        }
        $sql .= " ORDER BY e.last_name, e.first_name";
        return $this->query($sql, $params);
    }
}
