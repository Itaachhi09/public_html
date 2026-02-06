<?php
/**
 * Department Model - HR Core Module
 */

class Department extends BaseModel {
    protected $table = 'departments';
    protected $primaryKey = 'department_id';  // Correct primary key for departments table
    protected $fillable = ['department_name', 'name', 'code', 'head_id', 'parent_id', 'description'];

    /**
     * Get department with head information
     */
    public function getWithHead($id) {
        $query = 'SELECT d.*, CONCAT(u.first_name, " ", u.last_name) as head_name
                  FROM ' . $this->table . ' d
                  LEFT JOIN employees e ON d.head_id = e.id
                  LEFT JOIN users u ON e.user_id = u.id
                  WHERE d.id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all departments with hierarchy
     */
    public function getAllWithHierarchy() {
        $query = 'SELECT d.*, CONCAT(u.first_name, " ", u.last_name) as head_name
                  FROM ' . $this->table . ' d
                  LEFT JOIN employees e ON d.head_id = e.id
                  LEFT JOIN users u ON e.user_id = u.id
                  ORDER BY d.parent_id ASC, d.name ASC';
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get sub-departments
     */
    public function getSubDepartments($parent_id) {
        return $this->where('parent_id', '=', $parent_id);
    }
}
?>
