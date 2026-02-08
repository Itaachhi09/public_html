<?php
/**
 * Salary Band Model
 * Salary planning: min / midpoint / max bands per pay grade (and optionally grade level)
 * Bands are enforced system-wide; out-of-band assignments require approval.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class SalaryBand extends BaseModel {
    protected $table = 'salary_bands';
    protected $primaryKey = 'id';
    protected $fillable = ['pay_grade_id', 'grade_level_id', 'min_salary', 'midpoint_salary', 'max_salary', 'effective_from', 'effective_to', 'status'];

    /**
     * Get all bands with pay grade and grade level names
     */
    public function getAllWithDetails($activeOnly = true) {
        $query = "SELECT sb.*, pg.name AS pay_grade_name, pg.code AS pay_grade_code,
            gl.name AS grade_level_name, gl.code AS grade_level_code
            FROM {$this->table} sb
            JOIN pay_grades pg ON pg.id = sb.pay_grade_id
            LEFT JOIN grade_levels gl ON gl.id = sb.grade_level_id
            WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND sb.status = 'Active' AND pg.status = 'Active'";
        }
        $query .= " ORDER BY pg.name, gl.sort_order, sb.min_salary";
        return $this->query($query);
    }

    /**
     * Validate amount is within band (min <= amount <= max)
     */
    public function isWithinBand($bandId, $amount) {
        $band = $this->find($bandId);
        if (!$band) {
            return ['valid' => false, 'message' => 'Band not found'];
        }
        $min = (float) $band['min_salary'];
        $max = (float) $band['max_salary'];
        $amount = (float) $amount;
        if ($amount < $min || $amount > $max) {
            return ['valid' => false, 'message' => "Amount must be between {$min} and {$max}", 'band' => $band];
        }
        return ['valid' => true, 'band' => $band];
    }

    /**
     * Find band for pay grade (and optionally grade level)
     */
    public function getBandForGrade($payGradeId, $gradeLevelId = null) {
        $query = "SELECT * FROM {$this->table} WHERE pay_grade_id = ? AND status = 'Active'";
        $params = [$payGradeId];
        if ($gradeLevelId !== null) {
            $query .= " AND (grade_level_id = ? OR grade_level_id IS NULL)";
            $params[] = $gradeLevelId;
        }
        $query .= " ORDER BY grade_level_id IS NOT NULL DESC LIMIT 1";
        return $this->queryOne($query, $params);
    }

    /**
     * Check if a band already exists for this pay grade + grade level (unique constraint)
     */
    public function existsForGradeLevel($payGradeId, $gradeLevelId = null) {
        $query = "SELECT COUNT(*) AS c FROM {$this->table} WHERE pay_grade_id = ?";
        $params = [$payGradeId];
        if ($gradeLevelId === null || $gradeLevelId === '') {
            $query .= " AND grade_level_id IS NULL";
        } else {
            $query .= " AND grade_level_id = ?";
            $params[] = $gradeLevelId;
        }
        $row = $this->queryOne($query, $params);
        return (int) ($row['c'] ?? 0) > 0;
    }

    /**
     * Validate band order: min <= midpoint <= max
     */
    public function validateBandOrder($min, $midpoint, $max) {
        $min = (float) $min;
        $midpoint = (float) $midpoint;
        $max = (float) $max;
        return $min <= $midpoint && $midpoint <= $max;
    }
}
