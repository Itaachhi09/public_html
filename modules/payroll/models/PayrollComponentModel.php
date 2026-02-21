<?php
/**
 * Payroll Component Model
 * 
 * Maps to payroll_components table
 * Fields: id, code, name, description, component_type, category, is_active
 */

class PayrollComponentModel extends BaseModel {
    protected $table = 'payroll_components';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'name',
        'description',
        'component_type',
        'category',
        'is_active'
    ];

    /**
     * Get all payroll components with pagination
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active components
     */
    public function getActive() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE status = ? ORDER BY component_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['Active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
