<?php
/**
 * Shift Model - HR Core Master Data
 */

class Shift extends BaseModel {
    protected $table = 'shifts';
    protected $primaryKey = 'shift_id';  // Correct primary key
    protected $fillable = [
        'shift_name', 'shift_code', 'start_time', 'end_time', 'duration_hours',
        'break_hours', 'description', 'is_night_shift', 'status', 'created_at', 'updated_at'
    ];

    /**
     * Get all active shifts
     */
    public function getAllActive($limit = 50, $offset = 0) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE status = 'Active'
            ORDER BY shift_name ASC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get shift with employee count
     */
    public function getWithEmployeeCount($shift_id) {
        $query = "
            SELECT s.*, COUNT(DISTINCT es.employee_id) as employee_count
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.shift_id = es.shift_id AND es.status = 'Active'
            WHERE s.shift_id = ?
            GROUP BY s.shift_id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$shift_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get employees assigned to shift
     */
    public function getEmployeesByShift($shift_id) {
        $query = "
            SELECT e.* FROM employees e
            JOIN employee_shifts es ON e.employee_id = es.employee_id
            WHERE es.shift_id = ? AND es.status = 'Active'
            ORDER BY e.first_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$shift_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find by shift code
     */
    public function findByCode($code) {
        $query = "SELECT * FROM {$this->table} WHERE shift_code = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get shift statistics for KPI cards
     */
    public function getShiftStats() {
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN is_night_shift = 1 THEN 1 ELSE 0 END) as night_shifts,
                SUM(CASE WHEN shift_type = 'oncall' THEN 1 ELSE 0 END) as oncall_shifts,
                SUM(CASE WHEN overtime_eligible = 1 THEN 1 ELSE 0 END) as overtime_eligible,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_shifts
            FROM {$this->table}
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all shifts with employee assignment counts
     */
    public function getAll($with_details = true) {
        $query = "
            SELECT 
                s.*,
                COUNT(DISTINCT es.employee_id) as assigned_count,
                COALESCE(s.required_staff_count, 0) as required_count,
                CASE WHEN COUNT(DISTINCT es.employee_id) < COALESCE(s.required_staff_count, 1) THEN 1 ELSE 0 END as is_understaffed
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.shift_id = es.shift_id AND es.status = 'Active'
            GROUP BY s.shift_id
            ORDER BY s.shift_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get night shifts with coverage info
     */
    public function getNightShifts() {
        $query = "
            SELECT 
                s.*,
                COUNT(DISTINCT es.employee_id) as assigned_count,
                COALESCE(s.required_staff_count, 0) as required_count
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.shift_id = es.shift_id AND es.status = 'Active'
            WHERE s.is_night_shift = 1 AND s.status = 'Active'
            GROUP BY s.shift_id
            ORDER BY s.start_time ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unassigned shifts (no employee assignments)
     */
    public function getUnassignedShifts() {
        $query = "
            SELECT 
                s.*,
                COUNT(DISTINCT es.employee_id) as assigned_count
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.shift_id = es.shift_id AND es.status = 'Active'
            WHERE s.status = 'Active'
            GROUP BY s.shift_id
            HAVING COUNT(DISTINCT es.employee_id) = 0
            ORDER BY s.shift_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recently modified shifts
     */
    public function getRecentlyModified($limit = 5) {
        $query = "
            SELECT 
                s.*,
                COUNT(DISTINCT es.employee_id) as assigned_count
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.shift_id = es.shift_id AND es.status = 'Active'
            GROUP BY s.shift_id
            ORDER BY s.updated_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get shifts by type
     */
    public function getByType($shift_type) {
        $query = "
            SELECT 
                s.*,
                COUNT(DISTINCT es.employee_id) as assigned_count
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.shift_id = es.shift_id AND es.status = 'Active'
            WHERE s.shift_type = ? AND s.status = 'Active'
            GROUP BY s.shift_id
            ORDER BY s.shift_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$shift_type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get understaffed shifts (assigned < required)
     */
    public function getUnderstaffedShifts() {
        $query = "
            SELECT 
                s.*,
                COUNT(DISTINCT es.employee_id) as assigned_count,
                COALESCE(s.required_staff_count, 0) as required_count
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.id = es.shift_id AND es.status = 'Active'
            WHERE s.status = 'Active' AND COALESCE(s.required_staff_count, 0) > 0
            GROUP BY s.id
            HAVING COUNT(DISTINCT es.employee_id) < COALESCE(s.required_staff_count, 0)
            ORDER BY s.shift_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check shift coverage (assigned vs required)
     */
    public function checkShiftCoverage($shift_id) {
        $query = "
            SELECT 
                s.id,
                s.shift_name,
                COUNT(DISTINCT es.employee_id) as assigned_count,
                COALESCE(s.required_staff_count, 0) as required_count,
                CASE WHEN COUNT(DISTINCT es.employee_id) < COALESCE(s.required_staff_count, 1) THEN 1 ELSE 0 END as is_understaffed
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.id = es.shift_id AND es.status = 'Active'
            WHERE s.id = ?
            GROUP BY s.id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$shift_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get shifts with payroll rules
     */
    public function getWithPayrollRules() {
        $query = "
            SELECT 
                s.*,
                COUNT(DISTINCT es.employee_id) as assigned_count,
                COALESCE(s.required_staff_count, 0) as required_count
            FROM {$this->table} s
            LEFT JOIN employee_shifts es ON s.id = es.shift_id AND es.status = 'Active'
            WHERE s.status = 'Active'
            GROUP BY s.id
            ORDER BY s.shift_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
