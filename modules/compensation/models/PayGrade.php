<?php
/**
 * Pay Grade Model
 * Salary planning: pay grades (e.g. ER Doctor, Staff Nurse)
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayGrade extends BaseModel {
    protected $table = 'pay_grades';
    protected $primaryKey = 'id';
    protected $fillable = ['code', 'name', 'description', 'status'];

    /**
     * Get all active pay grades with band summary
     */
    public function getAllWithBands($activeOnly = true) {
        $query = "SELECT pg.*,
            (SELECT COUNT(*) FROM salary_bands sb WHERE sb.pay_grade_id = pg.id AND sb.status = 'Active') AS band_count,
            (SELECT CONCAT(MIN(sb.min_salary), ' - ', MAX(sb.max_salary)) FROM salary_bands sb WHERE sb.pay_grade_id = pg.id AND sb.status = 'Active') AS range_summary
            FROM {$this->table} pg WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND pg.status = 'Active'";
        }
        $query .= " ORDER BY pg.name ASC";
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
        return (int)($row['c'] ?? 0) > 0;
    }
}
