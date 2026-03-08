<?php
/**
 * Onboarding Checklist Model
 * Manages new hire checklist and onboarding process
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class OnboardingChecklist extends BaseModel {
    protected $table = 'onboarding_checklists';
    protected $primaryKey = 'onboarding_checklist_id';  // Correct primary key
    protected $fillable = ['employee_id', 'checklist_item', 'responsible_department', 'due_date', 'completed_date', 'completed_by', 'status', 'remarks', 'created_at', 'updated_at'];
    protected $hidden = [];

    /**
     * Get onboarding checklist by employee
     */
    public function getByEmployee($employeeId) {
        $query = "
            SELECT 
                oc.*,
                CONCAT(u.first_name, ' ', u.last_name) as completed_by_name
            FROM {$this->table} oc
            LEFT JOIN users u ON oc.completed_by = u.user_id
            WHERE oc.employee_id = ?
            ORDER BY oc.due_date ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get onboarding status for employee
     */
    public function getOnboardingStatus($employeeId) {
        $query = "
            SELECT 
                COUNT(*) as total_items,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_items,
                ROUND((SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as completion_percentage
            FROM {$this->table}
            WHERE employee_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get overdue onboarding items
     */
    public function getOverdueItems() {
        $query = "
            SELECT 
                oc.*,
                e.employee_code,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM {$this->table} oc
            JOIN employees e ON oc.employee_id = e.employee_id
            WHERE oc.status = 'Pending'
            AND oc.due_date < CURDATE()
            ORDER BY oc.due_date ASC
        ";
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Complete checklist item
     */
    public function completeItem($checklistId, $completedBy) {
        $query = "
            UPDATE {$this->table}
            SET status = 'Completed', completed_date = NOW(), completed_by = ?, updated_at = NOW()
            WHERE onboarding_checklist_id = ?
        ";
        return $this->db->prepare($query)->execute([$completedBy, $checklistId]);
    }
}
