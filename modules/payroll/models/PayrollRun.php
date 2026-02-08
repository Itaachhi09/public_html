<?php
/**
 * Payroll Run Model
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollRun extends BaseModel {
    protected $table = 'payroll_runs';
    protected $primaryKey = 'id';
    protected $fillable = ['period_name', 'start_date', 'end_date', 'pay_date', 'status'];

    /**
     * Get all runs ordered by date
     */
    public function getAll() {
        return $this->query("SELECT * FROM {$this->table} ORDER BY start_date DESC");
    }

    /**
     * Get run by status
     */
    public function getByStatus($status) {
        return $this->query("SELECT * FROM {$this->table} WHERE status = ? ORDER BY start_date DESC", [$status]);
    }
}
