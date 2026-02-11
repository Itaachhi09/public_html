<?php
/**
 * PayrollApproval Model
 * Manages multi-level approval workflow for payroll runs
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollApproval extends BaseModel {
    protected $table = 'payroll_approvals';

    public function getAll() {
        return $this->all();
    }

    public function getByPayrollRun($payrollRunId) {
        return $this->query("SELECT * FROM {$this->table} WHERE payroll_run_id = ? ORDER BY approval_level ASC", [$payrollRunId]);
    }

    public function getByStatus($status) {
        return $this->query("SELECT * FROM {$this->table} WHERE approval_status = ? ORDER BY created_at DESC", [$status]);
    }

    public function getPendingApprovals($userId) {
        return $this->query("SELECT * FROM {$this->table} WHERE approver_id = ? AND approval_status = 'pending' ORDER BY created_at DESC", [$userId]);
    }
}
