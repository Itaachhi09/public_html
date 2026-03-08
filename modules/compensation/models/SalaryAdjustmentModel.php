<?php
/**
 * Salary Adjustment Model
 * 
 * Maps to salary_adjustments table
 * Fields: id, employee_id, adjustment_type, trigger_type, previous_amount, new_amount,  
 *         effective_date, end_date, reason, status, approved_by, approved_at, salary_band_id
 */

class SalaryAdjustmentModel extends BaseModel {
    protected $table = 'salary_adjustments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'adjustment_type',
        'trigger_type',
        'previous_amount',
        'new_amount',
        'effective_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'salary_band_id'
    ];

    /**
     * Get all salary adjustments with employee details
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT sa.*, e.employee_code, e.first_name, e.last_name, d.department_name
                  FROM ' . $this->table . ' sa
                  LEFT JOIN employees e ON sa.employee_id = e.employee_id
                  LEFT JOIN departments d ON e.department_id = d.department_id
                  ORDER BY sa.id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get adjustments by employee
     */
    public function getByEmployee($employee_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE employee_id = ? ORDER BY effective_date DESC';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employee_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
