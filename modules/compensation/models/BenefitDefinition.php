<?php
/**
 * Benefit Definition Model
 * Non-salary compensation: taxable flag, eligible roles, effective dates. Attach to duty or role.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class BenefitDefinition extends BaseModel {
    protected $table = 'benefit_definitions';
    protected $primaryKey = 'id';
    protected $fillable = ['code', 'name', 'description', 'taxable', 'eligible_roles', 'effective_from', 'effective_to', 'attach_to', 'is_active'];

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
            ORDER BY name ASC";
        return $this->query($query, [$date, $date]);
    }

    /**
     * Get benefits by attach type (duty or role)
     */
    public function getByAttachTo($attachTo) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 AND attach_to = ? ORDER BY name ASC";
        return $this->query($query, [$attachTo]);
    }

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
}
