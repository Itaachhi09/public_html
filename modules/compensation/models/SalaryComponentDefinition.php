<?php
/**
 * Salary Component Definition Model
 * Master definition of salary components (e.g. ER Duty Pay). No payroll computation.
 * Includes audit fields: configured_by_role, last_updated_by_id, last_updated_reason
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class SalaryComponentDefinition extends BaseModel {
    protected $table = 'salary_component_definitions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code', 'name', 'description', 'component_type', 'taxable', 'is_active',
        'effective_from', 'effective_to', 'used_by_payroll',
        'configured_by_role', 'last_updated_by_id', 'last_updated_reason'
    ];

    /**
     * Get all salary component definitions (for structure setup and payroll reference)
     */
    public function getAll($activeOnly = true) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        $query .= " ORDER BY component_type ASC, name ASC";
        return $this->query($query);
    }

    /**
     * Get components by type (base, allowance, deduction)
     */
    public function getByType($type) {
        $query = "SELECT * FROM {$this->table} WHERE component_type = ? AND is_active = 1 ORDER BY name ASC";
        return $this->query($query, [$type]);
    }

    /**
     * Get active base pay component (only one allowed per role)
     */
    public function getActiveBasePay() {
        $query = "SELECT * FROM {$this->table} WHERE component_type = 'base' AND is_active = 1 LIMIT 1";
        return $this->queryOne($query);
    }

    /**
     * Check if code exists (for validation)
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
     * Check if component is used in active payroll period (read-only flag)
     */
    public function isUsedByPayroll($id) {
        $row = $this->find($id);
        return $row && !empty($row['used_by_payroll']);
    }

    /**
     * Get count of active components (used to enforce one active base pay)
     */
    public function getCountByType($type, $activeOnly = true) {
        $query = "SELECT COUNT(*) AS c FROM {$this->table} WHERE component_type = ?";
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        $row = $this->queryOne($query, [$type]);
        return (int) ($row['c'] ?? 0);
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
            return true; // Optional field
        }
        
        if (!is_null($effectiveTo)) {
            return strtotime($effectiveFrom) <= strtotime($effectiveTo);
        }
        
        return true;
    }
}
