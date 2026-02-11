<?php
/**
 * PayrollComponent Model
 * Manages payroll-specific components (earnings, deductions, taxes)
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollComponent extends BaseModel {
    protected $table = 'payroll_components';

    public function getAll() {
        return $this->all();
    }

    public function getByType($type) {
        return $this->query("SELECT * FROM {$this->table} WHERE component_type = ?", [$type]);
    }
}
