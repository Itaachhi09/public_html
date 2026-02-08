<?php
/**
 * Employee Contract Assignment Model
 * Binds employee to a pay contract. Active contract required to receive compensation.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeeContractAssignment extends BaseModel {
    protected $table = 'employee_contract_assignments';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'contract_id', 'effective_from', 'effective_to'];

    /**
     * Get all assignments with employee and contract details
     */
    public function getAllWithDetails($filters = []) {
        $query = "SELECT a.*,
            e.employee_code, e.first_name, e.last_name,
            pc.code AS contract_code, pc.name AS contract_name, pc.start_date AS contract_start, pc.end_date AS contract_end,
            pg.name AS pay_grade_name
            FROM {$this->table} a
            JOIN employees e ON e.employee_id = a.employee_id
            JOIN pay_contracts pc ON pc.id = a.contract_id
            JOIN pay_grades pg ON pg.id = pc.pay_grade_id
            WHERE 1=1";
        $params = [];
        if (!empty($filters['employee_id'])) {
            $query .= " AND a.employee_id = ?";
            $params[] = $filters['employee_id'];
        }
        if (!empty($filters['contract_id'])) {
            $query .= " AND a.contract_id = ?";
            $params[] = $filters['contract_id'];
        }
        $query .= " ORDER BY a.effective_from DESC";
        return $this->query($query, $params);
    }

    /**
     * Check if employee has an active assignment (effective_to is null or >= today)
     */
    public function getActiveAssignmentForEmployee($employeeId) {
        $query = "SELECT a.*, pc.name AS contract_name, pc.end_date AS contract_end
            FROM {$this->table} a
            JOIN pay_contracts pc ON pc.id = a.contract_id
            WHERE a.employee_id = ? AND (a.effective_to IS NULL OR a.effective_to >= CURDATE())
            AND pc.end_date >= CURDATE()
            ORDER BY a.effective_from DESC LIMIT 1";
        return $this->queryOne($query, [$employeeId]);
    }
}
