<?php
/**
 * Employee Salary Model
 */

class EmployeeSalaryModel extends BaseModel {
    protected $table = 'employee_salaries';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'salary_type',
        'basic_rate',
        'effective_date',
        'tax_status',
        'payroll_eligible',
        'bank_name',
        'bank_account'
    ];

    /**
     * Get all employee salaries with details
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT es.*, e.employee_code, e.first_name, e.last_name, d.department_name
                  FROM ' . $this->table . ' es
                  LEFT JOIN employees e ON es.employee_id = e.employee_id
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  ORDER BY es.id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get salary by employee
     */
    public function getByEmployee($employee_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE employee_id = ? ORDER BY effective_date DESC LIMIT 1';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employee_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
