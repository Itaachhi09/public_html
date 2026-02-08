<?php
/**
 * Out-of-Band Approval Model
 * Assignments outside the defined salary band require approval.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class OutOfBandApproval extends BaseModel {
    protected $table = 'out_of_band_approvals';
    protected $primaryKey = 'id';
    protected $fillable = ['entity_type', 'entity_id', 'requested_amount', 'salary_band_id', 'reason', 'status', 'approved_by', 'approved_at'];

    /**
     * Get all pending approvals
     */
    public function getPending() {
        return $this->query(
            "SELECT o.*, sb.min_salary, sb.midpoint_salary, sb.max_salary
             FROM {$this->table} o
             JOIN salary_bands sb ON sb.id = o.salary_band_id
             WHERE o.status = 'pending'
             ORDER BY o.created_at DESC"
        );
    }

    /**
     * Get approvals for an entity (e.g. employee_salary)
     */
    public function getByEntity($entityType, $entityId) {
        return $this->query(
            "SELECT o.*, sb.min_salary, sb.midpoint_salary, sb.max_salary
             FROM {$this->table} o
             JOIN salary_bands sb ON sb.id = o.salary_band_id
             WHERE o.entity_type = ? AND o.entity_id = ?
             ORDER BY o.created_at DESC",
            [$entityType, $entityId]
        );
    }
}
