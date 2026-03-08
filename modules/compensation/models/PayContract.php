<?php
/**
 * Pay Contract Model
 * Bind employees to compensation rules. Attributes: start date, end date, linked pay grade, applicable policies.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayContract extends BaseModel {
    protected $table = 'pay_contracts';
    protected $primaryKey = 'id';
    protected $fillable = ['code', 'name', 'pay_grade_id', 'start_date', 'end_date', 'applicable_policies'];

    /**
     * Get all contracts with pay grade name; optionally only active (not expired)
     */
    public function getAllWithGrade($activeOnly = false) {
        $query = "SELECT pc.*, pg.name AS pay_grade_name, pg.code AS pay_grade_code
            FROM {$this->table} pc
            JOIN pay_grades pg ON pg.id = pc.pay_grade_id
            WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND pc.end_date >= CURDATE()";
        }
        $query .= " ORDER BY pc.start_date DESC";
        return $this->query($query);
    }

    /**
     * Check if contract is active (not expired): end_date >= today
     */
    public function isActive($contractId) {
        $row = $this->queryOne("SELECT end_date FROM {$this->table} WHERE id = ?", [$contractId]);
        return $row && $row['end_date'] && $row['end_date'] >= date('Y-m-d');
    }

    /**
     * Get active contracts only (for dropdown when assigning; expired contracts block new entries)
     */
    public function getActive() {
        return $this->getAllWithGrade(true);
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
