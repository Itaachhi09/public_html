<?php
/**
 * Pag-IBIG Contribution Model
 * Manages Pag-IBIG (Home Development Mutual Fund) contribution brackets
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class PagIbigContribution extends BaseModel {
    protected $table = 'pagibig_contribution_brackets';
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
