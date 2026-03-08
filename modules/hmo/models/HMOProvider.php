<?php
/**
 * HMO Provider Model
 * Manages HMO companies and their details
 */

require_once(__DIR__ . '/../../../config/BaseModel.php');

class HMOProvider extends BaseModel {
    protected $table = 'hmo_providers';
    
    protected $fillable = [
        // Provider Profile
        'provider_name',
        'provider_code',
        'sec_dti_number',
        'tin_number',
        'provider_status',
        'provider_type',
        'address',
        'email',
        'phone',
        
        // Contract Management
        'contract_start_date',
        'contract_end_date',
        'contract_renewal_terms',
        'grace_period_days',
        'auto_expiry_enabled',
        
        // Coverage Rules & Limits
        'max_dependents',
        'employee_age_min',
        'employee_age_max',
        'dependent_age_max',
        'coverage_exclusions',
        'hospital_only_coverage',
        'annual_benefit_reset_date',
        
        // Contact & Escalation
        'primary_account_manager',
        'account_manager_phone',
        'claims_contact',
        'claims_contact_phone',
        'billing_contact',
        'billing_contact_phone',
        'emergency_escalation_contact',
        'emergency_escalation_phone',
        'sla_response_time_hours',
        
        // Financial & Payroll Integration
        'billing_cycle',
        'premium_payment_terms',
        'employer_share_percentage',
        'employee_share_percentage',
        'auto_deduction_eligible',
        'cost_center',
        'is_active',
        
        'created_at',
        'updated_at'
    ];

    public function getActiveProviders() {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY provider_name ASC";
        return $this->query($query);
    }

    public function getProviderById($id) {
        return $this->findById($id);
    }

    public function getProviderCatalog($provider_id) {
        $query = "SELECT p.* FROM hmo_plans p 
                  WHERE p.provider_id = " . intval($provider_id) . " 
                  AND p.is_active = 1 
                  ORDER BY p.plan_name ASC";
        return $this->query($query);
    }

    public function getProvidersByStatus($status) {
        $query = "SELECT * FROM {$this->table} WHERE provider_status = '" . $status . "' ORDER BY provider_name ASC";
        return $this->query($query);
    }

    public function getExpiringContracts($days = 30) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE contract_end_date <= DATE_ADD(NOW(), INTERVAL " . intval($days) . " DAY) 
                  AND contract_end_date > NOW() 
                  ORDER BY contract_end_date ASC";
        return $this->query($query);
    }

    public function getExpiredContracts() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE contract_end_date <= NOW() 
                  ORDER BY contract_end_date DESC";
        return $this->query($query);
    }

    public function getProviderByType($type) {
        $query = "SELECT * FROM {$this->table} WHERE provider_type = '" . $type . "' AND is_active = 1 ORDER BY provider_name ASC";
        return $this->query($query);
    }
}
