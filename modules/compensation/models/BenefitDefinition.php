<?php
/**
 * Benefit Definition Model
 * Non-salary compensation: taxable flag, eligible roles, effective dates. Attach to duty or role.
 * Enhanced with benefit categories, payroll impact, and audit fields.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class BenefitDefinition extends BaseModel {
    protected $table = 'benefit_definitions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code', 'name', 'description', 'benefit_category', 'payroll_impact',
        'taxable', 'taxable_lock', 'eligible_roles',
        'effective_from', 'effective_to', 'attach_to', 'is_active',
        'hidden_when_inactive', 'configured_by_role', 'last_updated_by_id', 'last_updated_reason'
    ];

    /**
     * Get all benefit definitions (for listing and payroll reference)
     */
    public function getAll($activeOnly = true) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        $query .= " ORDER BY name ASC";
        return $this->query($query);
    }

    /**
     * Get benefits effective on a given date (for payroll reference)
     */
    public function getEffectiveOn($date) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1
            AND effective_from <= ?
            AND (effective_to IS NULL OR effective_to >= ?)
            ORDER BY benefit_category ASC, name ASC";
        return $this->query($query, [$date, $date]);
    }

    /**
     * Get benefits by attach type (duty or role)
     */
    public function getByAttachTo($attachTo) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 AND attach_to = ? ORDER BY name ASC";
        return $this->query($query, [$attachTo]);
    }

    /**
     * Get benefits by category
     */
    public function getByCategory($category) {
        $query = "SELECT * FROM {$this->table} WHERE benefit_category = ? AND is_active = 1 ORDER BY name ASC";
        return $this->query($query, [$category]);
    }

    /**
     * Get benefits by payroll impact
     */
    public function getByPayrollImpact($impact) {
        $query = "SELECT * FROM {$this->table} WHERE payroll_impact = ? AND is_active = 1 ORDER BY name ASC";
        return $this->query($query, [$impact]);
    }

    /**
     * Get benefits for a specific role
     */
    public function getByRole($role) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 AND (eligible_roles = 'All' OR FIND_IN_SET(?, eligible_roles)) ORDER BY name ASC";
        return $this->query($query, [$role]);
    }

    /**
     * Check if code exists
     */
    public function codeExists($code, $excludeId = null) {
        $query = "SELECT COUNT(*) AS c FROM {$this->table} WHERE code = ?";
        $params = [$code];
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        $row = $this->queryOne($query, $params);
        return (int) ($row['c'] ?? 0) > 0;
    }

    /**
     * Check if benefit is locked for taxable changes (used in payroll)
     */
    public function isTaxableLocked($id) {
        $row = $this->find($id);
        return $row && !empty($row['taxable_lock']);
    }

    /**
     * Check if benefit should be hidden when inactive
     */
    public function isHiddenWhenInactive($id) {
        $row = $this->find($id);
        return $row && !empty($row['hidden_when_inactive']);
    }

    /**
     * Validate code format: uppercase with underscores only
     */
    public static function validateCodeFormat($code) {
        return preg_match('/^[A-Z0-9_]+$/', $code) === 1;
    }

    /**
     * Validate effective dates
     */
    public static function validateEffectiveDates($effectiveFrom, $effectiveTo = null) {
        if (is_null($effectiveFrom)) {
            return false; // Required field for benefits
        }
        
        if (!is_null($effectiveTo)) {
            return strtotime($effectiveFrom) <= strtotime($effectiveTo);
        }
        
        return true;
    }

    /**
     * Parse eligible roles from comma-separated string to array
     */
    public static function parseEligibleRoles($rolesString) {
        if ($rolesString === 'All') {
            return ['All'];
        }
        return array_filter(array_map('trim', explode(',', $rolesString)));
    }

    /**
     * Format eligible roles array back to comma-separated string
     */
    public static function formatEligibleRoles(array $roles) {
        if (in_array('All', $roles)) {
            return 'All';
        }
        return implode(', ', $roles);
    }
}
