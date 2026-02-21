<?php
/**
 * HMO Plan Model
 */

class HMOPlanModel extends BaseModel {
    protected $table = 'hmo_plans';
    protected $primaryKey = 'id';
    protected $fillable = [
        'provider_id',
        'plan_code',
        'plan_name',
        'plan_type',
        'description',
        'annual_premium_per_employee',
        'annual_premium_per_dependent',
        'monthly_premium',
        'out_of_pocket_limit',
        'deductible_amount',
        'copay_percentage',
        'in_network_doctors',
        'in_network_hospitals',
        'is_active',
        'plan_launch_date'
    ];

    /**
     * Get all HMO plans with provider details
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT hp.*, hp_prov.provider_name
                  FROM ' . $this->table . ' hp
                  LEFT JOIN hmo_providers hp_prov ON hp.provider_id = hp_prov.id
                  ORDER BY hp.id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get by provider
     */
    public function getByProvider($provider_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE provider_id = ? ORDER BY plan_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active plans
     */
    public function getActive() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE is_active = 1 ORDER BY plan_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
