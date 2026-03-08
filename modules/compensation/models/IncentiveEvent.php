<?php
/**
 * Incentive Event Model
 * Workload-based earnings: employee, duty date, procedure type, quantity, approver.
 * Each event links to one ER duty (employee + duty_date). Unapproved = inactive for payroll.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class IncentiveEvent extends BaseModel {
    protected $table = 'incentive_events';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'duty_date', 'procedure_type_id', 'quantity', 'approved_by', 'status'];

    /**
     * Get all events with employee and procedure type details (for listing and payroll reference)
     */
    public function getAllWithDetails($filters = []) {
        $query = "SELECT ie.*,
            e.employee_code, e.first_name, e.last_name,
            it.code AS procedure_code, it.name AS procedure_name
            FROM {$this->table} ie
            JOIN employees e ON e.employee_id = ie.employee_id
            JOIN incentive_types it ON it.id = ie.procedure_type_id
            WHERE 1=1";
        $params = [];
        if (!empty($filters['employee_id'])) {
            $query .= " AND ie.employee_id = ?";
            $params[] = $filters['employee_id'];
        }
        if (!empty($filters['duty_date'])) {
            $query .= " AND ie.duty_date = ?";
            $params[] = $filters['duty_date'];
        }
        if (isset($filters['status'])) {
            $query .= " AND ie.status = ?";
            $params[] = $filters['status'];
        }
        $query .= " ORDER BY ie.duty_date DESC, ie.created_at DESC";
        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . (int) $filters['limit'];
        }
        return $this->query($query, $params);
    }

    /**
     * Get approved events only (for payroll reference)
     */
    public function getApprovedForPayroll($dutyDateFrom = null, $dutyDateTo = null) {
        $query = "SELECT ie.*, e.employee_code, e.first_name, e.last_name, it.code AS procedure_code, it.name AS procedure_name
            FROM {$this->table} ie
            JOIN employees e ON e.employee_id = ie.employee_id
            JOIN incentive_types it ON it.id = ie.procedure_type_id
            WHERE ie.status = 'approved'";
        $params = [];
        if ($dutyDateFrom) {
            $query .= " AND ie.duty_date >= ?";
            $params[] = $dutyDateFrom;
        }
        if ($dutyDateTo) {
            $query .= " AND ie.duty_date <= ?";
            $params[] = $dutyDateTo;
        }
        $query .= " ORDER BY ie.employee_id, ie.duty_date, it.name";
        return $this->query($query, $params);
    }

    /**
     * Get pending events (for approval list)
     */
    public function getPending() {
        return $this->getAllWithDetails(['status' => 'pending', 'limit' => 500]);
    }
}
