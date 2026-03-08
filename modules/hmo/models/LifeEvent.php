<?php
/**
 * Life Events Model
 * 
 * Manages employee life events and enrollment status transitions
 * 
 * Supported Life Events:
 * - Marriage: Allow dependent add
 * - Birth: Allow dependent add
 * - Resignation: Auto terminate enrollment
 * - Termination: Auto terminate enrollment
 * - Reinstatement: Allow reactivation
 * 
 * System Behavior:
 * - Auto update enrollment status
 * - Auto update dependent status
 * - Auto stop payroll deductions on exit
 * - Auto resume deductions on reinstatement
 */

require_once __DIR__ . '/../../config/BaseModel.php';

class LifeEvent extends BaseModel {
    protected $table = 'hmo_life_events';

    /**
     * Get all life events
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT le.*, 
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   COUNT(DISTINCT ee.id) as affected_enrollments
            FROM {$this->table} le
            LEFT JOIN employees emp ON le.employee_id = emp.id
            LEFT JOIN employee_hmo_enrollments ee ON le.employee_id = ee.employee_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['event_type'])) {
            $sql .= " AND le.event_type = ?";
            $params[] = $filters['event_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND le.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(le.event_date) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(le.event_date) <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " GROUP BY le.id ORDER BY le.event_date DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get life event by ID
     */
    public function getById($id) {
        return $this->queryOne("
            SELECT le.*, 
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name
            FROM {$this->table} le
            LEFT JOIN employees emp ON le.employee_id = emp.id
            WHERE le.id = ?
        ", [$id]);
    }

    /**
     * Get life events for employee
     */
    public function getByEmployee($employeeId) {
        return $this->query("
            SELECT * FROM {$this->table}
            WHERE employee_id = ?
            ORDER BY event_date DESC
        ", [$employeeId]);
    }

    /**
     * Create new life event
     */
    public function create($data) {
        $sql = "
            INSERT INTO {$this->table}
            (employee_id, event_type, event_date, description, 
             supporting_document, change_window_start, change_window_end,
             status, processed_at, notes, created_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ";

        $params = [
            $data['employee_id'],
            $data['event_type'],
            $data['event_date'],
            $data['description'] ?? null,
            $data['supporting_document'] ?? null,
            $data['change_window_start'] ?? null,
            $data['change_window_end'] ?? null,
            $data['status'] ?? 'pending',
            $data['processed_at'] ?? null,
            $data['notes'] ?? null,
            null
        ];

        return $this->execute($sql, $params);
    }

    /**
     * Update life event status
     */
    public function updateStatus($id, $status, $processedAt = false) {
        $sql = "UPDATE {$this->table} SET status = ?";
        $params = [$status];

        if ($processedAt) {
            $sql .= ", processed_at = NOW()";
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        return $this->execute($sql, $params);
    }

    /**
     * Get pending life events
     */
    public function getPending() {
        return $this->query("
            SELECT le.*, 
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name
            FROM {$this->table} le
            LEFT JOIN employees emp ON le.employee_id = emp.id
            WHERE le.status = 'pending'
            ORDER BY le.event_date ASC
        ");
    }

    /**
     * Get life events outside change window
     */
    public function getOutsideChangeWindow() {
        return $this->query("
            SELECT le.*, 
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name
            FROM {$this->table} le
            LEFT JOIN employees emp ON le.employee_id = emp.id
            WHERE le.status = 'pending'
              AND (NOW() < le.change_window_start OR NOW() > le.change_window_end)
            ORDER BY le.event_date ASC
        ");
    }

    /**
     * Process life event - auto update enrollments
     */
    public function processEvent($eventId) {
        $event = $this->getById($eventId);

        if (!$event) {
            return ['success' => false, 'error' => 'Event not found'];
        }

        // Get employee's active enrollments
        $enrollments = $this->query("
            SELECT * FROM employee_hmo_enrollments
            WHERE employee_id = ? AND enrollment_status IN ('active', 'suspended', 'waiting_period')
        ", [$event['employee_id']]);

        $processedCount = 0;

        foreach ($enrollments as $enrollment) {
            switch ($event['event_type']) {
                case 'resignation':
                case 'termination':
                    // Auto terminate enrollment
                    $this->execute("
                        UPDATE employee_hmo_enrollments 
                        SET enrollment_status = 'terminated', 
                            termination_date = ?,
                            termination_reason = ?
                        WHERE id = ?
                    ", [$event['event_date'], strtolower($event['event_type']), $enrollment['id']]);

                    // Stop payroll deductions
                    $this->execute("
                        UPDATE hmo_payroll_deductions
                        SET is_active = 0
                        WHERE enrollment_id = ? AND deduction_date >= ?
                    ", [$enrollment['id'], $event['event_date']]);

                    $processedCount++;
                    break;

                case 'reinstatement':
                    // Reactivate enrollment if policy allows
                    $this->execute("
                        UPDATE employee_hmo_enrollments
                        SET enrollment_status = 'active',
                            effective_date = ?
                        WHERE id = ?
                    ", [$event['event_date'], $enrollment['id']]);

                    // Resume payroll deductions
                    $this->execute("
                        UPDATE hmo_payroll_deductions
                        SET is_active = 1
                        WHERE enrollment_id = ? AND deduction_date >= ?
                    ", [$enrollment['id'], $event['event_date']]);

                    $processedCount++;
                    break;
            }
        }

        // Mark event as processed
        $this->updateStatus($eventId, 'processed', true);

        return [
            'success' => true,
            'message' => "Life event processed. {$processedCount} enrollment(s) updated.",
            'processed_count' => $processedCount
        ];
    }

    /**
     * Get events requiring action
     */
    public function getEventsRequiringAction() {
        return $this->query("
            SELECT le.*, 
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   CASE 
                       WHEN le.status = 'pending' AND NOW() > le.change_window_end THEN 'WINDOW_CLOSED'
                       WHEN le.status = 'pending' AND NOW() BETWEEN le.change_window_start AND le.change_window_end THEN 'WINDOW_OPEN'
                       ELSE 'PENDING'
                   END as action_required
            FROM {$this->table} le
            LEFT JOIN employees emp ON le.employee_id = emp.id
            WHERE le.status IN ('pending', 'awaiting_approval')
            ORDER BY 
                CASE WHEN action_required = 'WINDOW_CLOSED' THEN 1
                     WHEN action_required = 'WINDOW_OPEN' THEN 2
                     ELSE 3 END,
                le.event_date ASC
        ");
    }

    /**
     * Get life event statistics
     */
    public function getStatistics() {
        return $this->queryOne("
            SELECT 
                COUNT(*) as total_events,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_events,
                COUNT(CASE WHEN status = 'processed' THEN 1 END) as processed_events,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_events,
                SUM(CASE WHEN event_type = 'marriage' THEN 1 ELSE 0 END) as marriage_count,
                SUM(CASE WHEN event_type = 'birth' THEN 1 ELSE 0 END) as birth_count,
                SUM(CASE WHEN event_type = 'resignation' THEN 1 ELSE 0 END) as resignation_count,
                SUM(CASE WHEN event_type = 'termination' THEN 1 ELSE 0 END) as termination_count,
                SUM(CASE WHEN event_type = 'reinstatement' THEN 1 ELSE 0 END) as reinstatement_count
            FROM {$this->table}
        ");
    }

    /**
     * Get events by type
     */
    public function getByType($eventType) {
        return $this->query("
            SELECT le.*, 
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name
            FROM {$this->table} le
            LEFT JOIN employees emp ON le.employee_id = emp.id
            WHERE le.event_type = ?
            ORDER BY le.event_date DESC
        ", [$eventType]);
    }

    /**
     * Validate life event eligibility
     */
    public function validateEligibility($employeeId, $eventType, $eventDate) {
        // Check if employee has active enrollment
        $enrollment = $this->queryOne("
            SELECT * FROM employee_hmo_enrollments
            WHERE employee_id = ? AND enrollment_status = 'active'
            LIMIT 1
        ", [$employeeId]);

        if (!$enrollment) {
            return ['valid' => false, 'reason' => 'No active enrollment found'];
        }

        // Check for duplicate recent events
        $duplicate = $this->queryOne("
            SELECT id FROM {$this->table}
            WHERE employee_id = ?
              AND event_type = ?
              AND DATE(event_date) = ?
              AND status != 'rejected'
            LIMIT 1
        ", [$employeeId, $eventType, $eventDate]);

        if ($duplicate) {
            return ['valid' => false, 'reason' => 'Similar event already recorded'];
        }

        return ['valid' => true];
    }
}

