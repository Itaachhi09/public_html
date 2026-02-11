<?php
/**
 * PayrollAuditTrail Model
 * Logs and tracks all payroll operations for compliance and security
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollAuditTrail extends BaseModel {
    protected $table = 'payroll_audit_trail';

    public function getAll() {
        return $this->all();
    }

    public function getByUser($userId) {
        return $this->query("SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_date DESC", [$userId]);
    }

    public function getByPayrollRun($payrollRunId) {
        return $this->query("SELECT * FROM {$this->table} WHERE payroll_run_id = ? ORDER BY created_date DESC", [$payrollRunId]);
    }

    public function getByAction($action) {
        return $this->query("SELECT * FROM {$this->table} WHERE action = ? ORDER BY created_date DESC", [$action]);
    }

    public function getRecent($minutes = 60) {
        $since = date('Y-m-d H:i:s', time() - ($minutes * 60));
        return $this->query("SELECT * FROM {$this->table} WHERE created_date >= ? ORDER BY created_date DESC", [$since]);
    }
}
