<?php
/**
 * Employee Model - HR Core Module
 */

class Employee extends BaseModel {
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';  // Correct primary key for employees table
    protected $fillable = [
        'user_id', 'employee_code', 'first_name', 'middle_name', 'last_name',
        'date_of_birth', 'gender', 'marital_status', 'email', 'phone',
        'address', 'city', 'state', 'postal_code', 'country', 'nationality',
        'job_title_id', 'department_id', 'employment_type_id', 'location_id',
        'supervisor_id', 'date_of_joining', 'employment_status', 'last_working_day',
        'created_at', 'updated_at'
    ];

    /**
     * Get employee with full job details
     */
    public function getWithDetails($id) {
        $query = "
            SELECT 
                e.*,
                jt.title as job_title,
                d.department_name,
                et.type_name as employment_type,
                l.location_name,
                CONCAT(s.first_name, ' ', s.last_name) as supervisor_name
            FROM {$this->table} e
            LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
            LEFT JOIN departments d ON e.department_id = d.department_id
            LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
            LEFT JOIN locations l ON e.location_id = l.location_id
            LEFT JOIN {$this->table} s ON e.supervisor_id = s.employee_id
            WHERE e.employee_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all employees with pagination and filters
     */
    public function getAllWithDetails($limit = 10, $offset = 0, $filters = []) {
        try {
            $query = "
                SELECT 
                    e.*,
                    jt.title as job_title,
                    d.department_name,
                    et.type_name as employment_type,
                    l.location_name
                FROM {$this->table} e
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
                LEFT JOIN locations l ON e.location_id = l.location_id
                WHERE 1=1
            ";

            $params = [];

            if (!empty($filters['department_id'])) {
                $query .= " AND e.department_id = ?";
                $params[] = $filters['department_id'];
            }

            if (!empty($filters['employment_status'])) {
                $query .= " AND e.employment_status = ?";
                $params[] = $filters['employment_status'];
            }

            $query .= " ORDER BY e.first_name ASC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Employee::getAllWithDetails: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total employee count with filters
     */
    public function getTotalCount($filters = []) {
        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";

            if (!empty($filters['department_id'])) {
                $query .= " AND department_id = ?";
            }

            if (!empty($filters['employment_status'])) {
                $query .= " AND employment_status = ?";
            }

            $stmt = $this->db->prepare($query);
            $params = array_values($filters);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ? (int) $row['count'] : 0;
        } catch (\PDOException $e) {
            error_log('Employee::getTotalCount: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Search employees by keyword
     */
    public function search($keyword, $limit = 10) {
        $keyword = '%' . $keyword . '%';
        $query = "
            SELECT * FROM {$this->table}
            WHERE first_name LIKE ? 
            OR last_name LIKE ? 
            OR email LIKE ? 
            OR employee_code LIKE ?
            AND employment_status != 'Terminated'
            LIMIT ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$keyword, $keyword, $keyword, $keyword, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employee by code
     */
    public function findByCode($code) {
        $query = "SELECT * FROM {$this->table} WHERE employee_code = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employees by department
     */
    public function getByDepartment($department_id) {
        $query = "
            SELECT e.* FROM {$this->table} e
            WHERE e.department_id = ?
            AND e.employment_status = 'Active'
            ORDER BY e.first_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$department_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employees by position
     */
    public function getByPosition($position_id) {
        $query = "
            SELECT e.* FROM {$this->table} e
            WHERE e.job_title_id = ?
            AND e.employment_status = 'Active'
            ORDER BY e.first_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$position_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employees by supervisor
     */
    public function getBySupervisor($supervisor_id) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE supervisor_id = ?
            AND employment_status = 'Active'
            ORDER BY first_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$supervisor_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get active employees count
     */
    public function getActiveCount() {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE employment_status = 'Active'";
        $stmt = $this->db->query($query);
        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get new hires this month
     */
    public function getNewHiresThisMonth() {
        $query = "
            SELECT * FROM {$this->table}
            WHERE MONTH(date_of_joining) = MONTH(NOW())
            AND YEAR(date_of_joining) = YEAR(NOW())
            ORDER BY date_of_joining DESC
        ";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>
