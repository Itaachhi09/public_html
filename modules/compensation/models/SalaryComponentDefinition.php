<?php
/**
 * Salary Component Definition Model
 * Master definition of salary components (e.g. ER Duty Pay). No payroll computation.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class SalaryComponentDefinition extends BaseModel {
    protected $table = 'salary_component_definitions';
    protected $primaryKey = 'id';
    protected $fillable = ['code', 'name', 'description', 'component_type', 'taxable', 'is_active'];

    /**
     * Get all salary component definitions (for structure setup and payroll reference)
     */
    public function getAll($activeOnly = true) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        $query .= " ORDER BY name ASC";
        return $this->query($query);
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
