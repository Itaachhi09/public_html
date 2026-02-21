<?php
/**
 * PayrollCalendar Model
 * Manages payroll calendar periods, cutoff dates, and pay dates
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollCalendar extends BaseModel {
    protected $table = 'payroll_calendar';
    protected $fillable = [
        'payroll_year',
        'period_number',
        'cutoff_start',
        'cutoff_end',
        'pay_date',
        'frequency',
        'status',
        'remarks'
    ];

    /**
     * Get all calendar periods
     */
    public function getAll() {
        return $this->all();
    }

    /**
     * Get calendar periods by year
     */
    public function getByYear($year) {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE payroll_year = ? ORDER BY period_number ASC",
            [$year]
        );
    }

    /**
     * Get active periods for a specific year
     */
    public function getActivePeriodsByYear($year) {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE payroll_year = ? AND status = 'Active' ORDER BY period_number ASC",
            [$year]
        );
    }

    /**
     * Get a specific period
     */
    public function getPeriod($year, $period_number) {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE payroll_year = ? AND period_number = ?",
            [$year, $period_number]
        );
    }

    /**
     * Create a new calendar period
     */
    public function create($data) {
        // Check if this period already exists
        $existing = $this->getPeriod($data['payroll_year'] ?? 0, $data['period_number'] ?? 0);
        
        if ($existing) {
            // Update existing
            return $this->update($existing['id'], $data);
        }
        
        // Insert new
        return parent::create($data);
    }

    /**
     * Update calendar period
     */
    public function updatePeriod($id, $data) {
        return $this->update($id, $data);
    }

    /**
     * Delete calendar period
     */
    public function deletePeriod($id) {
        return $this->delete($id);
    }

    /**
     * Get current active payroll period
     */
    public function getCurrentPeriod() {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE payroll_year = YEAR(CURDATE()) AND status = 'Active' AND cutoff_end >= CURDATE() AND cutoff_start <= CURDATE() LIMIT 1"
        );
    }

    /**
     * Check if payroll calendar exists for a year
     */
    public function yearExists($year) {
        $result = $this->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE payroll_year = ?",
            [$year]
        );
        return $result[0]['count'] ?? 0;
    }
}
