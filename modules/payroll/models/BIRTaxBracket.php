<?php
/**
 * BIR Tax Bracket Model
 * Manages BIR (Bureau of Internal Revenue) withholding tax brackets
 */
require_once __DIR__ . '/../../../config/BaseModel.php';

class BIRTaxBracket extends BaseModel {
    protected $table = 'bir_tax_brackets';
    protected $fillable = [
        'annual_salary_from',
        'annual_salary_to',
        'tax_rate',
        'deductible_amount',
        'effective_year',
        'is_active'
    ];

    public function getByYear($year) {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE effective_year = ? AND is_active = 1 ORDER BY annual_salary_from ASC",
            [$year]
        );
    }

    public function getActive($year = null) {
        if (!$year) {
            $year = date('Y');
        }
        return $this->query(
            "SELECT * FROM {$this->table} WHERE effective_year = ? AND is_active = 1 ORDER BY annual_salary_from ASC",
            [$year]
        );
    }
}
