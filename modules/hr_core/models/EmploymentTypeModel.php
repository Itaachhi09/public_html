<?php
/**
 * Employment Type Model
 */

class EmploymentTypeModel extends BaseModel {
    protected $table = 'employment_types';
    protected $primaryKey = 'employment_type_id';
    protected $fillable = [
        'type_name',
        'description',
        'probation_period_days',
        'contract_duration_months',
        'benefits_eligible',
        'status'
    ];

    /**
     * Get all employment types with pagination
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY employment_type_id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active employment types
     */
    public function getActive() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE status = ? ORDER BY type_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['Active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
