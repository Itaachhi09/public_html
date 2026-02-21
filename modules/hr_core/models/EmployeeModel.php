<?php
/**
 * Employee Model
 */

class EmployeeModel extends BaseModel {
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'nationality',
        'job_title_id',
        'department_id',
        'employment_type_id',
        'location_id',
        'supervisor_id',
        'date_of_joining',
        'employment_status',
        'last_working_day'
    ];

    /**
     * Get all employees with pagination and joins
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT e.*, 
                         d.department_name,
                         jt.job_title_name,
                         et.type_name,
                         l.location_name
                  FROM ' . $this->table . ' e
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                  LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
                  LEFT JOIN locations l ON e.location_id = l.location_id
                  ORDER BY e.employee_id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single employee with related data
     */
    public function findWithDetails($id) {
        $query = 'SELECT e.*, 
                         d.department_name,
                         jt.job_title_name,
                         et.type_name,
                         l.location_name
                  FROM ' . $this->table . ' e
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                  LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
                  LEFT JOIN locations l ON e.location_id = l.location_id
                  WHERE e.employee_id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get by employee code
     */
    public function getByCode($code) {
        return $this->first('employee_code', $code);
    }

    /**
     * Get active employees
     */
    public function getActive() {
        $query = 'SELECT e.* FROM ' . $this->table . ' e WHERE e.employment_status = ? ORDER BY e.first_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['Active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Search employees
     */
    public function search($term) {
        $term = '%' . $term . '%';
        $query = 'SELECT e.*, 
                         d.department_name,
                         jt.job_title_name
                  FROM ' . $this->table . ' e
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                  WHERE e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR e.employee_code LIKE ?
                  ORDER BY e.first_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$term, $term, $term, $term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
