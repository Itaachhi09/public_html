<?php
/**
 * HMO Plan Model
 * Manages insurance plans with comprehensive benefits, limits, and network restrictions
 */

require_once(__DIR__ . '/../../../config/BaseModel.php');

class HMOPlan extends BaseModel {
    protected $table = 'hmo_plans';
    
    protected $fillable = [
        // A. Plan Identification
        'plan_name',
        'plan_code',
        'provider_id',
        'plan_status',
        
        // B. Coverage Types
        'inpatient_coverage',
        'outpatient_coverage',
        'emergency_coverage',
        'dental_coverage',
        'optical_coverage',
        'maternity_coverage',
        
        // C. Benefit Limits
        'annual_benefit_limit',
        'per_illness_limit',
        'per_visit_limit',
        'room_board_limit',
        'room_type_allowed',
        'daily_rate_cap',
        'icu_limit',
        
        // D. Co-pay & Co-insurance Rules
        'fixed_copay_amount',
        'coinsurance_percentage',
        'emergency_copay',
        'non_emergency_copay',
        'network_copay',
        'non_network_copay',
        
        // E. Hospital & Clinic Network
        'accredited_hospitals',
        'accredited_clinics',
        'accredited_doctors',
        'network_type',
        
        // F. Eligibility Rules
        'regular_employee_eligible',
        'probationary_employee_eligible',
        'contractual_employee_eligible',
        'minimum_tenure_required_days',
        'dependent_eligibility_rules',
        'upgrade_eligibility_conditions',
        
        // Status
        'is_active',
        'created_at',
        'updated_at'
    ];

    // Get all active plans
    public function getActivePlans() {
        $query = "SELECT * FROM {$this->table} 
                  WHERE plan_status = 'active' OR (plan_status IS NULL AND is_active = 1)
                  ORDER BY plan_name ASC";
        return $this->query($query);
    }

    // Get plans by provider with all details
    public function getPlansByProvider($provider_id = null) {
        if ($provider_id) {
            $query = "SELECT p.*, h.provider_name 
                      FROM {$this->table} p
                      LEFT JOIN hmo_providers h ON p.provider_id = h.id
                      WHERE p.provider_id = " . intval($provider_id) . "
                      ORDER BY p.plan_name ASC";
        } else {
            $query = "SELECT p.*, h.provider_name 
                      FROM {$this->table} p
                      LEFT JOIN hmo_providers h ON p.provider_id = h.id
                      GROUP BY p.provider_id
                      ORDER BY h.provider_name ASC";
        }
        return $this->query($query);
    }

    // Get plans by coverage type
    public function getPlanByCoverage($coverage_type = null) {
        if ($coverage_type) {
            $coverage_column = $this->getCoverageColumnName($coverage_type);
            if ($coverage_column) {
                $query = "SELECT * FROM {$this->table} 
                          WHERE {$coverage_column} = 1 AND is_active = 1
                          ORDER BY plan_name ASC";
                return $this->query($query);
            }
        } else {
            // Return aggregated coverage data
            $query = "SELECT 
                        'inpatient' as coverage_type, COUNT(*) as plan_count,
                        AVG(annual_benefit_limit) as avg_abl
                      FROM {$this->table} WHERE inpatient_coverage = 1
                      UNION ALL
                      SELECT 'outpatient', COUNT(*), AVG(annual_benefit_limit)
                      FROM {$this->table} WHERE outpatient_coverage = 1
                      UNION ALL
                      SELECT 'emergency', COUNT(*), AVG(annual_benefit_limit)
                      FROM {$this->table} WHERE emergency_coverage = 1
                      UNION ALL
                      SELECT 'dental', COUNT(*), AVG(annual_benefit_limit)
                      FROM {$this->table} WHERE dental_coverage = 1
                      UNION ALL
                      SELECT 'optical', COUNT(*), AVG(annual_benefit_limit)
                      FROM {$this->table} WHERE optical_coverage = 1
                      UNION ALL
                      SELECT 'maternity', COUNT(*), AVG(annual_benefit_limit)
                      FROM {$this->table} WHERE maternity_coverage = 1";
            return $this->query($query);
        }
    }

    // Get plans by eligibility type
    public function getPlansByEmploymentType($employment_type) {
        $column = '';
        switch(strtolower($employment_type)) {
            case 'regular':
                $column = 'regular_employee_eligible';
                break;
            case 'probationary':
                $column = 'probationary_employee_eligible';
                break;
            case 'contractual':
                $column = 'contractual_employee_eligible';
                break;
            default:
                return [];
        }
        
        $query = "SELECT * FROM {$this->table} 
                  WHERE {$column} = 1 AND is_active = 1
                  ORDER BY plan_name ASC";
        return $this->query($query);
    }

    // Get plans by network type
    public function getPlansByNetworkType($network_type) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE network_type = '" . $this->escapeString($network_type) . "' 
                  AND is_active = 1 
                  ORDER BY plan_name ASC";
        return $this->query($query);
    }

    // Get high coverage plans (ABL above threshold)
    public function getHighCoveragePlans($min_abl) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE annual_benefit_limit >= " . floatval($min_abl) . "
                  AND is_active = 1
                  ORDER BY annual_benefit_limit DESC";
        return $this->query($query);
    }

    // Get plans with specific coverage combinations
    public function getPlansWithCoveragers($coverages = []) {
        if (empty($coverages)) return [];
        
        $conditions = [];
        foreach ($coverages as $coverage) {
            $col = $this->getCoverageColumnName($coverage);
            if ($col) {
                $conditions[] = "{$col} = 1";
            }
        }
        
        if (empty($conditions)) return [];
        
        $query = "SELECT * FROM {$this->table} 
                  WHERE (" . implode(' OR ', $conditions) . ")
                  AND is_active = 1
                  ORDER BY plan_name ASC";
        return $this->query($query);
    }

    // Helper: Convert coverage type to column name
    private function getCoverageColumnName($type) {
        $mapping = [
            'inpatient' => 'inpatient_coverage',
            'outpatient' => 'outpatient_coverage',
            'emergency' => 'emergency_coverage',
            'dental' => 'dental_coverage',
            'optical' => 'optical_coverage',
            'maternity' => 'maternity_coverage'
        ];
        return $mapping[strtolower($type)] ?? null;
    }

    // Get plans with cost-sharing analysis
    public function getPlansWithCostSharing() {
        $query = "SELECT *,
                    ((fixed_copay_amount * 12) + (COALESCE(coinsurance_percentage, 0) * annual_benefit_limit / 100)) as annual_out_of_pocket
                  FROM {$this->table}
                  WHERE is_active = 1
                  ORDER BY annual_out_of_pocket ASC";
        return $this->query($query);
    }
}
