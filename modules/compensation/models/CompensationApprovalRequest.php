<?php
/**
 * Compensation Approval Request Model
 * Workflow: Request → Review → Approval/Rejection. Unapproved items never reach Payroll. Auditable.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class CompensationApprovalRequest extends BaseModel {
    protected $table = 'compensation_approval_requests';
    protected $primaryKey = 'id';
    protected $fillable = [
        'request_type', 'reference_type', 'reference_id', 'description', 'status',
        'requested_by', 'requested_at', 'reviewed_by', 'reviewed_at', 'approval_notes'
    ];

    /**
     * Get all requests with optional status filter (for listing and audit)
     */
    public function getAll($filters = []) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['request_type'])) {
            $query .= " AND request_type = ?";
            $params[] = $filters['request_type'];
        }
        $query .= " ORDER BY requested_at DESC";
        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . (int) $filters['limit'];
        }
        return $this->query($query, $params);
    }

    /**
     * Get pending (requested or under_review) - never reach Payroll until approved
     */
    public function getPending() {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE status IN ('requested', 'under_review') ORDER BY requested_at ASC"
        );
    }

    /**
     * Get approved only (for Payroll reference - only these reach Payroll)
     */
    public function getApproved() {
        return $this->query("SELECT * FROM {$this->table} WHERE status = 'approved' ORDER BY reviewed_at DESC");
    }
}
