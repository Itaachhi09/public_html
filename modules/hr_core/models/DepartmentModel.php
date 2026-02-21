<?php
/**
 * Department Model
 */

class DepartmentModel extends BaseModel {
    protected $table = 'departments';
    protected $primaryKey = 'department_id';
    protected $fillable = [
        'department_name',
        'name',
        'code',
        'head_id',
        'parent_id',
        'description',
        'status'
    ];

    /**
     * Get all departments with pagination
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY department_id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get department by code
     */
    public function getByCode($code) {
        return $this->first('code', $code);
    }

    /**
     * Get total count
     */
    public function getTotal() {
        return $this->count();
    }

    /**
     * Get active departments
     */
    public function getActive() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE status = ? ORDER BY department_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['Active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
