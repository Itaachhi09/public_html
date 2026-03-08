.<?php
/**
 * Work Schedule Model - HR Core Master Data
 * Bridges schedules to attendance and payroll
 */

class WorkSchedule extends BaseModel {
    protected $table = 'work_schedules';
    protected $primaryKey = 'schedule_id';  // Correct primary key
    protected $fillable = [
        'schedule_name', 'schedule_type', 'shift_pattern', 'hours_per_day', 'work_days_per_week',
        'weekly_hours', 'has_night_shift', 'is_trainee', 'description', 'status', 
        'start_date', 'end_date', 'created_at', 'updated_at'
    ];

    /**
     * Get all schedules with stats
     */
    public function getSchedulesWithStats() {
        $query = "
            SELECT 
                ws.*,
                COALESCE(COUNT(DISTINCT ea.employee_id), 0) as assigned_count,
                COALESCE(MAX(COALESCE(ws.required_count, 1)), 1) as required_count
            FROM {$this->table} ws
            LEFT JOIN employee_assignments ea ON ws.schedule_id = ea.schedule_id AND ea.status = 'Active'
            WHERE ws.status = 'Active'
            GROUP BY ws.schedule_id
            ORDER BY ws.schedule_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active schedules with pagination
     */
    public function getAllActive($limit = null, $offset = 0) {
        $query = "
            SELECT 
                ws.*,
                COALESCE(COUNT(DISTINCT ea.employee_id), 0) as assigned_count
            FROM {$this->table} ws
            LEFT JOIN employee_assignments ea ON ws.schedule_id = ea.schedule_id AND ea.status = 'Active'
            WHERE ws.status = 'Active'
            GROUP BY ws.schedule_id
            ORDER BY ws.schedule_name ASC
        ";
        
        if ($limit) {
            $query .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get schedule statistics for dashboard
     */
    public function getScheduleStats() {
        $query = "
            SELECT 
                COALESCE(COUNT(CASE WHEN status = 'Active' THEN 1 END), 0) as active_count,
                COALESCE(COUNT(CASE WHEN has_night_shift = 1 THEN 1 END), 0) as night_schedule_count,
                COALESCE(SUM(COALESCE((SELECT COUNT(*) FROM employee_assignments ea WHERE ea.schedule_id = ws.schedule_id AND ea.status = 'Active'), 0)), 0) as total_assigned,
                COALESCE(COUNT(CASE WHEN schedule_id NOT IN (SELECT DISTINCT schedule_id FROM employee_assignments WHERE status = 'Active') THEN 1 END), 0) as unassigned_count
            FROM {$this->table} ws
            WHERE status = 'Active'
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get unassigned schedules
     */
    public function getUnassignedSchedules() {
        $query = "
            SELECT ws.* FROM {$this->table} ws
            WHERE ws.status = 'Active'
            AND ws.schedule_id NOT IN (
                SELECT DISTINCT schedule_id FROM employee_assignments 
                WHERE status = 'Active'
            )
            ORDER BY ws.schedule_name ASC
            LIMIT 5
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get night shift schedules
     */
    public function getNightSchedules() {
        $query = "
            SELECT ws.* FROM {$this->table} ws
            WHERE ws.status = 'Active'
            AND ws.has_night_shift = 1
            ORDER BY ws.schedule_name ASC
            LIMIT 5
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recently modified schedules
     */
    public function getRecentlyModified($limit = 5) {
        $query = "
            SELECT ws.* FROM {$this->table} ws
            WHERE ws.status = 'Active'
            ORDER BY ws.updated_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get schedules by type
     */
    public function getByType($type) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE schedule_type = ? AND status = 'Active'
            ORDER BY schedule_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get schedule with employee count
     */
    public function getWithEmployeeCount($schedule_id) {
        $query = "
            SELECT ws.*, COUNT(DISTINCT ea.employee_id) as assigned_count
            FROM {$this->table} ws
            LEFT JOIN employee_assignments ea ON ws.id = ea.schedule_id AND ea.status = 'Active'
            WHERE ws.id = ?
            GROUP BY ws.id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$schedule_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get employees on this schedule
     */
    public function getEmployeesBySchedule($schedule_id) {
        $query = "
            SELECT e.* FROM employees e
            JOIN employee_assignments ea ON e.id = ea.employee_id
            WHERE ea.schedule_id = ? AND ea.status = 'Active'
            ORDER BY e.first_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$schedule_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get OJT schedules (training)
     */
    public function getOJTSchedules() {
        $query = "
            SELECT * FROM {$this->table}
            WHERE is_trainee = 1 AND status = 'Active'
            ORDER BY schedule_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get schedules with payroll differential eligibility
     */
    public function getPayrollDifferentialEligible() {
        $query = "
            SELECT * FROM {$this->table}
            WHERE has_night_shift = 1 AND is_trainee = 0 AND status = 'Active'
            ORDER BY schedule_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate weekly hours (utility)
     */
    public function calculateWeeklyHours($hours_per_day, $work_days_per_week) {
        return floatval($hours_per_day) * intval($work_days_per_week);
    }

    /**
     * Deactivate schedule instead of delete
     */
    public function deactivateSchedule($schedule_id) {
        $query = "UPDATE {$this->table} SET status = 'Inactive', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$schedule_id]);
    }

    /**
     * Get understaffed schedules (coverage issues)
     */
    public function getUnderstaffedSchedules() {
        $query = "
            SELECT ws.*, 
                   COALESCE(COUNT(DISTINCT ea.employee_id), 0) as assigned_count,
                   COALESCE(ws.required_count, 1) as required_count
            FROM {$this->table} ws
            LEFT JOIN employee_assignments ea ON ws.id = ea.schedule_id AND ea.status = 'Active'
            WHERE ws.status = 'Active'
            GROUP BY ws.id
            HAVING assigned_count < required_count
            ORDER BY ws.schedule_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

