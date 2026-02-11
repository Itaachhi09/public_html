<?php
/**
 * Disbursement Model
 * Manages payroll disbursement and bank file generation
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class Disbursement extends BaseModel {
    protected $table = 'disbursements';

    public function getAll() {
        return $this->all();
    }

    public function getByPayrollRun($payrollRunId) {
        return $this->query("SELECT * FROM {$this->table} WHERE payroll_run_id = ?", [$payrollRunId]);
    }

    public function getByStatus($status) {
        return $this->query("SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC", [$status]);
    }

    public function getPending() {
        return $this->query("SELECT * FROM {$this->table} WHERE status IN ('pending', 'pending_approval') ORDER BY created_at DESC");
    }
}
