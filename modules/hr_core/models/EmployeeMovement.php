<?php
/**
 * Employee Movement Model
 * Tracks promotions, transfers, secondments, and status changes
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeeMovement extends BaseModel {
    protected $table = 'employee_movements';
    protected $primaryKey = 'movement_id';  // Correct primary key
    protected $fillable = ['employee_id', 'movement_type', 'effective_date', 'from_job_title_id', 'to_job_title_id', 'from_department_id', 'to_department_id', 'from_location_id', 'to_location_id', 'from_salary', 'to_salary', 'reason', 'status', 'approved_by', 'created_at', 'updated_at'];
    protected $hidden = [];

    /**
     * Get movements by employee
     */
    public function getByEmployee($employeeId, $limit = null, $offset = null) {
        $query = "
            SELECT 
                em.*,
                e.employee_code,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                jt1.title as from_job_title,
                jt2.title as to_job_title,
                d1.department_name as from_department,
                d2.department_name as to_department,
                CONCAT(u.first_name, ' ', u.last_name) as approved_by_name
            FROM {$this->table} em
            JOIN employees e ON em.employee_id = e.employee_id
            LEFT JOIN job_titles jt1 ON em.from_job_title_id = jt1.job_title_id
            LEFT JOIN job_titles jt2 ON em.to_job_title_id = jt2.job_title_id
            LEFT JOIN departments d1 ON em.from_department_id = d1.department_id
            LEFT JOIN departments d2 ON em.to_department_id = d2.department_id
            LEFT JOIN users u ON em.approved_by = u.user_id
            WHERE em.employee_id = ?
            ORDER BY em.effective_date DESC
        ";

        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employeeId, $limit, $offset]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get movements by type
     */
    public function getByType($movementType, $limit = null, $offset = null) {
        $query = "
            SELECT 
                em.*,
                e.employee_code,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM {$this->table} em
            JOIN employees e ON em.employee_id = e.employee_id
            WHERE em.movement_type = ? AND em.status = 'Approved'
            ORDER BY em.effective_date DESC
        ";

        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$movementType, $limit, $offset]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->prepare($query);
        $stmt->execute([$movementType]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get pending approvals
     */
    public function getPendingApprovals($limit = null, $offset = null) {
        $query = "
            SELECT 
                em.*,
                e.employee_code,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                jt1.title as from_job_title,
                jt2.title as to_job_title
            FROM {$this->table} em
            JOIN employees e ON em.employee_id = e.employee_id
            LEFT JOIN job_titles jt1 ON em.from_job_title_id = jt1.job_title_id
            LEFT JOIN job_titles jt2 ON em.to_job_title_id = jt2.job_title_id
            WHERE em.status = 'Pending'
            ORDER BY em.created_at ASC
        ";

        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Approve movement
     */
    public function approve($movementId, $approvedBy) {
        $query = "
            UPDATE {$this->table}
            SET status = 'Approved', approved_by = ?, updated_at = NOW()
            WHERE employee_movement_id = ?
        ";
        return $this->db->prepare($query)->execute([$approvedBy, $movementId]);
    }

    /**
     * Reject movement
     */
    public function reject($movementId) {
        $query = "
            UPDATE {$this->table}
            SET status = 'Rejected', updated_at = NOW()
            WHERE employee_movement_id = ?
        ";
        return $this->db->prepare($query)->execute([$movementId]);
    }
}
