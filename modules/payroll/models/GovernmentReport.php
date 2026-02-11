<?php
/**
 * GovernmentReport Model
 * Manages statutory reports for government agencies (SSS, PhilHealth, Pag-IBIG, BIR)
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class GovernmentReport extends BaseModel {
    protected $table = 'government_reports';

    public function getAll() {
        return $this->all();
    }

    public function getByAgency($agency) {
        return $this->query("SELECT * FROM {$this->table} WHERE agency = ? ORDER BY reporting_date DESC", [$agency]);
    }

    public function getByPeriod($startDate, $endDate) {
        return $this->query("SELECT * FROM {$this->table} WHERE reporting_date BETWEEN ? AND ? ORDER BY reporting_date DESC", [$startDate, $endDate]);
    }

    public function getByStatus($status) {
        return $this->query("SELECT * FROM {$this->table} WHERE submission_status = ? ORDER BY created_date DESC", [$status]);
    }
}
