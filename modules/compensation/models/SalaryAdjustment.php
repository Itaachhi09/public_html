<?php
/**
 * Salary Adjustment Model
 * Controlled changes to base pay: temporary/permanent; triggers: Promotion, Approved exception, Policy change.
 * Adjustments require approval; respect salary bands; store date, reason, approver.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class SalaryAdjustment extends BaseModel {
    protected $table = 'salary_adjustments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id', 'adjustment_type', 'trigger_type', 'previous_amount', 'new_amount',
        'effective_date', 'end_date', 'reason', 'status', 'approved_by', 'approved_at', 'salary_band_id'
    ];

    /**
     * Get all adjustments with employee and band info (clean history)
     */
    public function getAllWithDetails($filters = []) {
        $query = "SELECT sa.*,
            e.employee_code, e.first_name, e.last_name,
            sb.min_salary AS band_min, sb.max_salary AS band_max
            FROM {$this->table} sa
            JOIN employees e ON e.employee_id = sa.employee_id
            LEFT JOIN salary_bands sb ON sb.id = sa.salary_band_id
            WHERE 1=1";
        $params = [];
        if (!empty($filters['employee_id'])) {
            $query .= " AND sa.employee_id = ?";
            $params[] = $filters['employee_id'];
        }
        if (isset($filters['status'])) {
            $query .= " AND sa.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['adjustment_type'])) {
            $query .= " AND sa.adjustment_type = ?";
            $params[] = $filters['adjustment_type'];
        }
        $query .= " ORDER BY sa.effective_date DESC, sa.created_at DESC";
        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . (int) $filters['limit'];
        }
        return $this->query($query, $params);
    }

    /**
     * Get pending adjustments (for approval list)
     */
    public function getPending() {
        return $this->getAllWithDetails(['status' => 'pending', 'limit' => 500]);
    }

    /**
     * Get adjustment history for one employee
     */
    public function getByEmployee($employeeId) {
        return $this->getAllWithDetails(['employee_id' => $employeeId]);
    }
}
