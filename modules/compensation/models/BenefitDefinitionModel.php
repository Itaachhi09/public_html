<?php
/**
 * Benefit Definition Model
 */

class BenefitDefinitionModel extends BaseModel {
    protected $table = 'benefit_definitions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code',
        'name',
        'description',
        'taxable',
        'eligible_roles',
        'effective_from',
        'effective_to',
        'attach_to',
        'is_active'
    ];

    /**
     * Get all benefits with pagination
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active benefits
     */
    public function getActive() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE is_active = 1 ORDER BY name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
