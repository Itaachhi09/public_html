<?php
/**
 * HMO Provider Model
 */

class HMOProviderModel extends BaseModel {
    protected $table = 'hmo_providers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'provider_code',
        'provider_name',
        'description',
        'head_office_address',
        'contact_phone',
        'contact_email',
        'primary_contact_person',
        'primary_contact_title',
        'website',
        'accreditation_number',
        'established_year',
        'provider_status',
        'provider_type',
        'contract_start_date',
        'contract_end_date',
        'payment_terms_days',
        'is_active'
    ];

    /**
     * Get all HMO providers with pagination
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active providers
     */
    public function getActive() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE provider_status = ? ORDER BY provider_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['Active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
