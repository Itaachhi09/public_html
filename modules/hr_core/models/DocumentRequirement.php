<?php
/**
 * Document Requirements Model
 * Manages hospital document requirements and employee document checklist
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class DocumentRequirement extends BaseModel {
    protected $table = 'document_requirements';
    protected $primaryKey = 'requirement_id';
    protected $fillable = ['category_id', 'document_type', 'document_description', 'is_mandatory', 'requires_expiry_date', 'expiry_alert_days', 'role_based', 'employment_status', 'notes'];

    /**
     * Get all document requirements by category
     */
    public function getByCategory($categoryId) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE category_id = ?
            ORDER BY document_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all document categories with requirements
     */
    public function getAllWithCategories() {
        $query = "
            SELECT 
                dc.category_id,
                dc.category_name,
                dc.category_description,
                dc.sort_order,
                COUNT(dr.requirement_id) as requirement_count
            FROM document_categories dc
            LEFT JOIN {$this->table} dr ON dc.category_id = dr.category_id
            GROUP BY dc.category_id
            ORDER BY dc.sort_order ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get requirements with category details
     */
    public function getWithCategories() {
        $query = "
            SELECT 
                dr.*,
                dc.category_name,
                dc.sort_order
            FROM {$this->table} dr
            INNER JOIN document_categories dc ON dr.category_id = dc.category_id
            ORDER BY dc.sort_order ASC, dr.document_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get mandatory requirements only
     */
    public function getMandatory() {
        $query = "
            SELECT 
                dr.*,
                dc.category_name
            FROM {$this->table} dr
            INNER JOIN document_categories dc ON dr.category_id = dc.category_id
            WHERE dr.is_mandatory = TRUE
            ORDER BY dc.sort_order ASC, dr.document_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get role-based requirements
     */
    public function getByRole($role) {
        $query = "
            SELECT 
                dr.*,
                dc.category_name
            FROM {$this->table} dr
            INNER JOIN document_categories dc ON dr.category_id = dc.category_id
            WHERE dr.role_based = ? OR dr.role_based IS NULL
            ORDER BY dc.sort_order ASC, dr.document_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$role]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employment status based requirements
     */
    public function getByEmploymentStatus($status) {
        $query = "
            SELECT 
                dr.*,
                dc.category_name
            FROM {$this->table} dr
            INNER JOIN document_categories dc ON dr.category_id = dc.category_id
            WHERE dr.employment_status = ? OR dr.employment_status IS NULL
            ORDER BY dc.sort_order ASC, dr.document_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$status]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

?>
