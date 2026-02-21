<?php
/**
 * SSS Contribution Model
 * Manages SSS (Social Security System) contribution brackets
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class SSSContribution extends BaseModel {
    protected $table = 'sss_contribution_brackets';
    protected $fillable = [
        'salary_from',
        'salary_to',
        'monthly_contribution',
        'effective_year',
        'is_active'
    ];

    public function getByYear($year) {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE effective_year = ? AND is_active = 1 ORDER BY salary_from ASC",
            [$year]
        );
    }

    public function getActive($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        return $this->query(
            "SELECT * FROM {$this->table} WHERE effective_year = ? AND is_active = 1 ORDER BY salary_from ASC",
            [$year]
        );
    }
}
