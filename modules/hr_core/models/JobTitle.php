<?php
/**
 * Job Title Model
 * Manages job titles and job descriptions
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class JobTitle extends BaseModel {
    protected $table = 'job_titles';
    protected $primaryKey = 'job_title_id';  // Correct primary key
    protected $fillable = ['title', 'description', 'department_id', 'salary_grade_id', 'status', 'created_at', 'updated_at'];
    protected $hidden = [];

    /**
     * Get job title with department and salary grade details
     */
    public function getWithDetails($id) {
        $query = "
            SELECT 
                jt.*,
                d.department_name,
                sg.grade_code
            FROM {$this->table} jt
            LEFT JOIN departments d ON jt.department_id = d.department_id
            LEFT JOIN salary_grades sg ON jt.salary_grade_id = sg.salary_grade_id
            WHERE jt.job_title_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all job titles with details
     */
    public function getAllWithDetails($limit = null, $offset = null) {
        $query = "
            SELECT 
                jt.*,
                d.department_name,
                sg.grade_code,
                COUNT(DISTINCT e.employee_id) as employee_count
            FROM {$this->table} jt
            LEFT JOIN departments d ON jt.department_id = d.department_id
            LEFT JOIN salary_grades sg ON jt.salary_grade_id = sg.salary_grade_id
            LEFT JOIN employees e ON jt.job_title_id = e.job_title_id AND e.employment_status = 'Active'
            WHERE jt.status = 'Active'
            GROUP BY jt.job_title_id
        ";

        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get job titles by department
     */
    public function getByDepartment($departmentId) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE department_id = ? AND status = 'Active'
            ORDER BY title ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Search job titles
     */
    public function search($keyword, $limit = 10, $offset = 0) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE (title LIKE ? OR description LIKE ?)
            AND status = 'Active'
            ORDER BY title ASC
            LIMIT ? OFFSET ?
        ";
        $searchTerm = "%{$keyword}%";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$searchTerm, $searchTerm, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
