<?php
/**
 * Grade Level Model
 * Salary planning: grade levels (e.g. Senior for doctors, Mid for nurses)
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class GradeLevel extends BaseModel {
    protected $table = 'grade_levels';
    protected $primaryKey = 'id';
    protected $fillable = ['pay_grade_id', 'code', 'name', 'description', 'sort_order', 'status'];

    /**
     * Get all grade levels with pay grade name
     */
    public function getAllWithGrade($activeOnly = true) {
        $query = "SELECT gl.*, pg.name AS pay_grade_name, pg.code AS pay_grade_code
            FROM {$this->table} gl
            JOIN pay_grades pg ON pg.id = gl.pay_grade_id
            WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND gl.status = 'Active' AND pg.status = 'Active'";
        }
        $query .= " ORDER BY pg.name, gl.sort_order, gl.name";
        return $this->query($query);
    }

    public function getByPayGrade($payGradeId) {
        $query = "SELECT * FROM {$this->table} WHERE pay_grade_id = ? AND status = 'Active' ORDER BY sort_order, name";
        return $this->query($query, [$payGradeId]);
    }

    public function codeExistsForGrade($payGradeId, $code, $excludeId = null) {
        $query = "SELECT COUNT(*) AS c FROM {$this->table} WHERE pay_grade_id = ? AND code = ?";
        $params = [$payGradeId, $code];
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        $row = $this->queryOne($query, $params);
        return (int)($row['c'] ?? 0) > 0;
    }
}
