<?php
/**
 * Payroll Module Models
 * Comprehensive models for payroll processing, salaries, and payslips
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

/**
 * Employee Salary Model
 */
class EmployeeSalary extends BaseModel {
    protected $table = 'employee_salaries';
    protected $primaryKey = 'salary_id';
    protected $fillable = [
        'employee_id', 'salary_type', 'basic_rate', 'salary_grade_id',
        'tax_status', 'dependents_count', 'bank_name', 'bank_account_number',
        'payout_method', 'payroll_eligible', 'effective_date', 'end_date'
    ];

    /**
     * Get active salary for employee
     */
    public function getActiveSalary($employee_id) {
        $query = "
            SELECT es.*, sg.grade_name, sg.grade_code
            FROM {$this->table} es
            LEFT JOIN salary_grades sg ON es.salary_grade_id = sg.salary_grade_id
            WHERE es.employee_id = ? 
            AND (es.end_date IS NULL OR es.end_date >= CURDATE())
            AND es.effective_date <= CURDATE()
            AND es.payroll_eligible = TRUE
            ORDER BY es.effective_date DESC
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employee_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get salary history
     */
    public function getSalaryHistory($employee_id) {
        $query = "
            SELECT es.*, sg.grade_name, sg.grade_code
            FROM {$this->table} es
            LEFT JOIN salary_grades sg ON es.salary_grade_id = sg.salary_grade_id
            WHERE es.employee_id = ?
            ORDER BY es.effective_date DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employee_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all employees eligible for payroll: active employees with an eligible salary record,
     * or active employees without a salary record (so they appear in runs and can be assigned later).
     */
    public function getEligibleEmployees() {
        $query = "
            SELECT DISTINCT e.employee_id, e.employee_code,
                   CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   COALESCE(es.basic_rate, 0) as basic_rate,
                   COALESCE(es.salary_type, 'Monthly') as salary_type
            FROM employees e
            LEFT JOIN {$this->table} es ON es.employee_id = e.employee_id
                AND es.payroll_eligible = TRUE
                AND (es.end_date IS NULL OR es.end_date >= CURDATE())
                AND es.effective_date <= CURDATE()
            WHERE e.employment_status = 'Active'
            ORDER BY e.first_name ASC
        ";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Employee Allowance Model
 */
class EmployeeAllowance extends BaseModel {
    protected $table = 'employee_allowances';
    protected $primaryKey = 'allowance_id';
    protected $fillable = [
        'employee_id', 'allowance_type', 'allowance_name', 'amount',
        'is_taxable', 'effective_date', 'end_date', 'status'
    ];

    /**
     * Get active allowances for employee
     */
    public function getActiveAllowances($employee_id) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE employee_id = ?
            AND status = 'Active'
            AND (end_date IS NULL OR end_date >= CURDATE())
            AND effective_date <= CURDATE()
            ORDER BY allowance_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employee_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Employee Deduction Rule Model
 */
class EmployeeDeductionRule extends BaseModel {
    protected $table = 'employee_deduction_rules';
    protected $primaryKey = 'deduction_rule_id';
    protected $fillable = [
        'employee_id', 'deduction_type', 'deduction_name', 'amount',
        'percentage', 'calculation_base', 'priority_order',
        'effective_date', 'end_date', 'status'
    ];

    /**
     * Get active deduction rules for employee
     */
    public function getActiveDeductionRules($employee_id) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE employee_id = ?
            AND status = 'Active'
            AND (end_date IS NULL OR end_date >= CURDATE())
            AND effective_date <= CURDATE()
            ORDER BY priority_order ASC, deduction_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employee_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Payroll Calendar Model
 */
class PayrollCalendar extends BaseModel {
    protected $table = 'payroll_calendar';
    protected $primaryKey = 'calendar_id';
    protected $fillable = [
        'payroll_frequency', 'period_name', 'period_code',
        'start_date', 'end_date', 'cutoff_date', 'pay_date', 'status'
    ];

    /**
     * Get calendar by period code
     */
    public function getByPeriodCode($period_code) {
        $query = "SELECT * FROM {$this->table} WHERE period_code = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$period_code]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get open periods
     */
    public function getOpenPeriods($frequency = null) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE status = 'Open'";
            if ($frequency) {
                $query .= " AND payroll_frequency = ?";
            }
            $query .= " ORDER BY start_date DESC";
            
            $stmt = $this->db->prepare($query);
            if ($frequency) {
                $stmt->execute([$frequency]);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table might not exist yet
            error_log('Payroll getOpenPeriods error: ' . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            error_log('Payroll getOpenPeriods error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get period by date range
     */
    public function getPeriodByDate($date, $frequency = null) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE ? BETWEEN start_date AND end_date
        ";
        if ($frequency) {
            $query .= " AND payroll_frequency = ?";
        }
        $query .= " ORDER BY start_date DESC LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        if ($frequency) {
            $stmt->execute([$date, $frequency]);
        } else {
            $stmt->execute([$date]);
        }
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Find an existing calendar row matching the manual dates, or create one.
     * Used when the UI does not select a pre-seeded calendar period.
     */
    public function findOrCreateManualPeriod($frequency, $start_date, $end_date, $pay_date) {
        $frequency = $frequency ?: 'Semi-Monthly';

        // Try reuse an existing identical period
        $query = "
            SELECT {$this->primaryKey} as calendar_id
            FROM {$this->table}
            WHERE payroll_frequency = ?
              AND start_date = ?
              AND end_date = ?
              AND pay_date = ?
            LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$frequency, $start_date, $end_date, $pay_date]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row && !empty($row['calendar_id'])) {
            return (int) $row['calendar_id'];
        }

        // Create a new manual period
        $freqKey = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $frequency), 0, 2));
        $code = 'MAN-' . $freqKey . '-' . str_replace('-', '', $start_date) . '-' . str_replace('-', '', $end_date) . '-' . str_replace('-', '', $pay_date);
        $name = 'Manual ' . $start_date . ' - ' . $end_date;

        return (int) $this->create([
            'payroll_frequency' => $frequency,
            'period_name' => $name,
            'period_code' => $code,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'cutoff_date' => $end_date,
            'pay_date' => $pay_date,
            'status' => 'Open'
        ]);
    }
}

/**
 * Payroll Run Model
 */
class PayrollRun extends BaseModel {
    protected $table = 'payroll_runs';
    protected $primaryKey = 'payroll_run_id';
    protected $fillable = [
        'calendar_id', 'run_number', 'run_date', 'payroll_frequency',
        'period_start', 'period_end', 'pay_date', 'total_employees',
        'total_gross_pay', 'total_deductions', 'total_net_pay',
        'status', 'validated_by', 'validated_at', 'approved_by',
        'approved_at', 'locked_at', 'notes', 'created_by'
    ];

    /**
     * Get payroll run with details
     */
    public function getWithDetails($id) {
        $query = "
            SELECT pr.*,
                   pc.period_name, pc.period_code,
                   CONCAT(uc.first_name, ' ', uc.last_name) as created_by_name,
                   CONCAT(uv.first_name, ' ', uv.last_name) as validated_by_name,
                   CONCAT(ua.first_name, ' ', ua.last_name) as approved_by_name
            FROM {$this->table} pr
            LEFT JOIN payroll_calendar pc ON pr.calendar_id = pc.calendar_id
            LEFT JOIN users uc ON pr.created_by = uc.id
            LEFT JOIN users uv ON pr.validated_by = uv.id
            LEFT JOIN users ua ON pr.approved_by = ua.id
            WHERE pr.payroll_run_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all payroll runs with pagination
     */
    public function getAllWithDetails($limit = 20, $offset = 0, $filters = []) {
        try {
            $query = "
                SELECT pr.*, pc.period_name, pc.period_code
                FROM {$this->table} pr
                LEFT JOIN payroll_calendar pc ON pr.calendar_id = pc.calendar_id
                WHERE 1=1
            ";
            
            $params = [];
            if (!empty($filters['status'])) {
                $query .= " AND pr.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['frequency'])) {
                $query .= " AND pr.payroll_frequency = ?";
                $params[] = $filters['frequency'];
            }
            
            $query .= " ORDER BY pr.run_date DESC, pr.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table might not exist yet
            error_log('Payroll getAllWithDetails error: ' . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            error_log('Payroll getAllWithDetails error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if payroll run exists for period
     */
    public function existsForPeriod($period_start, $period_end, $frequency) {
        $query = "
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE period_start = ? AND period_end = ? AND payroll_frequency = ?
            AND status != 'Cancelled'
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$period_start, $period_end, $frequency]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Generate unique run number
     */
    public function generateRunNumber($frequency, $period_start) {
        $year = date('Y', strtotime($period_start));
        $month = date('m', strtotime($period_start));
        $prefix = strtoupper(substr($frequency, 0, 1));
        
        $query = "
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE run_number LIKE ?
        ";
        $pattern = $prefix . $year . $month . '%';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$pattern]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $sequence = str_pad($result['count'] + 1, 3, '0', STR_PAD_LEFT);
        return $prefix . $year . $month . $sequence;
    }
}

/**
 * Payroll Run Employee Model
 */
class PayrollRunEmployee extends BaseModel {
    protected $table = 'payroll_run_employees';
    protected $primaryKey = 'run_employee_id';
    protected $fillable = [
        'payroll_run_id', 'employee_id', 'regular_hours', 'overtime_hours',
        'night_differential_hours', 'holiday_hours', 'basic_pay',
        'gross_pay', 'total_deductions', 'net_pay', 'status', 'validation_errors'
    ];

    /**
     * Get run employee with details
     */
    public function getWithDetails($id) {
        $query = "
            SELECT pre.*,
                   e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   e.department_id, d.department_name
            FROM {$this->table} pre
            JOIN employees e ON pre.employee_id = e.employee_id
            LEFT JOIN departments d ON e.department_id = d.department_id
            WHERE pre.run_employee_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employees for payroll run
     */
    public function getByPayrollRun($payroll_run_id) {
        $query = "
            SELECT pre.*,
                   e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   d.department_name
            FROM {$this->table} pre
            JOIN employees e ON pre.employee_id = e.employee_id
            LEFT JOIN departments d ON e.department_id = d.department_id
            WHERE pre.payroll_run_id = ?
            ORDER BY e.first_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$payroll_run_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Payroll Earning Model
 */
class PayrollEarning extends BaseModel {
    protected $table = 'payroll_earnings';
    protected $primaryKey = 'earning_id';
    protected $fillable = [
        'run_employee_id', 'payroll_run_id', 'employee_id',
        'earning_type', 'earning_name', 'amount', 'is_taxable'
    ];

    /**
     * Get earnings for run employee
     */
    public function getByRunEmployee($run_employee_id) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE run_employee_id = ?
            ORDER BY earning_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$run_employee_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Payroll Deduction Model
 */
class PayrollDeduction extends BaseModel {
    protected $table = 'payroll_deductions';
    protected $primaryKey = 'deduction_id';
    protected $fillable = [
        'run_employee_id', 'payroll_run_id', 'employee_id',
        'deduction_type', 'deduction_name', 'amount', 'employer_contribution'
    ];

    /**
     * Get deductions for run employee
     */
    public function getByRunEmployee($run_employee_id) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE run_employee_id = ?
            ORDER BY deduction_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$run_employee_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Payslip Model
 */
class Payslip extends BaseModel {
    protected $table = 'payslips';
    protected $primaryKey = 'payslip_id';
    protected $fillable = [
        'payroll_run_id', 'employee_id', 'run_employee_id', 'payslip_number',
        'period_start', 'period_end', 'pay_date', 'basic_pay',
        'gross_pay', 'total_deductions', 'net_pay', 'status',
        'issued_at', 'pdf_path'
    ];

    /**
     * Get payslip with employee details
     */
    public function getWithEmployee($id) {
        $query = "
            SELECT p.*,
                   e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   e.email, e.phone, d.department_name,
                   jt.title as job_title
            FROM {$this->table} p
            JOIN employees e ON p.employee_id = e.employee_id
            LEFT JOIN departments d ON e.department_id = d.department_id
            LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
            WHERE p.payslip_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employee payslips
     */
    public function getEmployeePayslips($employee_id, $limit = 12) {
        $query = "
            SELECT p.*, pr.run_number, pr.status as payroll_status
            FROM {$this->table} p
            LEFT JOIN payroll_runs pr ON p.payroll_run_id = pr.payroll_run_id
            WHERE p.employee_id = ?
            ORDER BY p.period_end DESC, p.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employee_id, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get payslips by payroll run
     */
    public function getByPayrollRun($payroll_run_id) {
        $query = "
            SELECT p.*,
                   e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM {$this->table} p
            JOIN employees e ON p.employee_id = e.employee_id
            WHERE p.payroll_run_id = ?
            ORDER BY e.first_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$payroll_run_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Generate unique payslip number
     */
    public function generatePayslipNumber($employee_id, $period_end) {
        $year = date('Y', strtotime($period_end));
        $month = date('m', strtotime($period_end));
        $empCode = str_pad($employee_id, 4, '0', STR_PAD_LEFT);
        return 'PS' . $year . $month . $empCode;
    }
}

/**
 * Payslip Reissue Model
 */
class PayslipReissue extends BaseModel {
    protected $table = 'payslip_reissues';
    protected $primaryKey = 'reissue_id';
    protected $fillable = ['payslip_id', 'reason', 'reissued_by'];

    /**
     * Get reissues for payslip
     */
    public function getByPayslip($payslip_id) {
        $query = "
            SELECT pr.*,
                   CONCAT(u.first_name, ' ', u.last_name) as reissued_by_name
            FROM {$this->table} pr
            JOIN users u ON pr.reissued_by = u.id
            WHERE pr.payslip_id = ?
            ORDER BY pr.reissued_at DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$payslip_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Salary Template Model (hospital position/role templates)
 */
class SalaryTemplate extends BaseModel {
    protected $table = 'salary_templates';
    protected $primaryKey = 'template_id';
    protected $fillable = [
        'position', 'role', 'employment_type', 'salary_type',
        'shift_duration_hours', 'basic_rate', 'rate_per_hour_reference',
        'payroll_frequency', 'payout_day_1', 'payout_day_2', 'cutoff_start_day', 'cutoff_end_day',
        'double_pay_addon', 'incentives_in_payroll', 'overtime_outside_base', 'night_differential_applied',
        'incentive_rules', 'benefits', 'status', 'effective_date', 'end_date'
    ];

    public function getActiveByPositionRole($position, $role) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE position = ? AND role = ? AND status = 'Active'
            AND effective_date <= CURDATE()
            AND (end_date IS NULL OR end_date >= CURDATE())
            ORDER BY effective_date DESC LIMIT 1
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$position, $role]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getAllActive() {
        try {
            $query = "SELECT * FROM {$this->table} WHERE status = 'Active' AND effective_date <= CURDATE() AND (end_date IS NULL OR end_date >= CURDATE()) ORDER BY position, role";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }
}

/**
 * Special Pay Date Model (double pay dates)
 */
class SpecialPayDate extends BaseModel {
    protected $table = 'special_pay_dates';
    protected $primaryKey = 'id';

    public function isSpecialPayDate($date) {
        $stmt = $this->db->prepare("SELECT 1 FROM {$this->table} WHERE date = ? LIMIT 1");
        $stmt->execute([$date]);
        return (bool) $stmt->fetch();
    }
}

?>
