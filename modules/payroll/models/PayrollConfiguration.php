<?php
/**
 * PayrollConfiguration Model
 * Manages payroll setup rules, calendars, and settings
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollConfiguration extends BaseModel {
    protected $table = 'payroll_configurations';

    public function getAll() {
        return $this->all();
    }

    public function getByKey($key) {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE config_key = ?", [$key]);
    }

    public function getActive() {
        return $this->query("SELECT * FROM {$this->table} WHERE is_active = 1");
    }
}
