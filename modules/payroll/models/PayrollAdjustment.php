<?php
/**
 * PayrollAdjustment Model
 * Manages payroll adjustments (final pay, back pay, 13th month, separation pay)
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollAdjustment extends BaseModel {
    protected $table = 'payroll_adjustments';

    public function getAll() {
        return $this->all();
    }

    public function getByEmployee($employeeId) {
        return $this->query("SELECT * FROM {$this->table} WHERE employee_id = ? ORDER BY adjustment_date DESC", [$employeeId]);
    }

    public function getByType($adjustmentType) {
        return $this->query("SELECT * FROM {$this->table} WHERE adjustment_type = ?", [$adjustmentType]);
    }
}
