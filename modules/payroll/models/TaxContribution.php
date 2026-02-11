<?php
/**
 * TaxContribution Model
 * Manages tax and contribution calculations (SSS, PhilHealth, Pag-IBIG, BIR, etc.)
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class TaxContribution extends BaseModel {
    protected $table = 'tax_contributions';

    public function getAll() {
        return $this->all();
    }

    public function getByPayrollRun($payrollRunId) {
        return $this->query("SELECT * FROM {$this->table} WHERE payroll_run_id = ?", [$payrollRunId]);
    }

    public function getByEmployee($employeeId) {
        return $this->query("SELECT * FROM {$this->table} WHERE employee_id = ?", [$employeeId]);
    }

    public function getByType($contributionType) {
        return $this->query("SELECT * FROM {$this->table} WHERE contribution_type = ?", [$contributionType]);
    }
}
