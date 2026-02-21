<?php
/**
 * PayrollComponent Model
 * Manages payroll-specific components (earnings, deductions, taxes)
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollComponent extends BaseModel {
    protected $table = 'payroll_components';
    protected $fillable = [
        'code',
        'name',
        'description',
        'component_type',
        'category',
        'is_active'
    ];

    public function getAll() {
        return $this->all();
    }

    public function getByType($type) {
        return $this->query("SELECT * FROM {$this->table} WHERE component_type = ? AND is_active = 1 ORDER BY name ASC", [$type]);
    }

    public function getActive() {
        return $this->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY component_type, name ASC");
    }

    public function getByCode($code) {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE code = ?", [$code]);
    }
}
