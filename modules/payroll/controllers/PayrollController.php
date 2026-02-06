<?php
/**
 * Payroll Module - Comprehensive Controller
 * Handles Payroll Processing, Salaries, and Payslips
 */
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
set_exception_handler(function (Throwable $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => 'Internal Server Error', 'data' => [], 'error' => $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
    exit;
});

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/Auth.php';
require_once __DIR__ . '/../../../config/Response.php';
require_once __DIR__ . '/../../../config/Request.php';
require_once __DIR__ . '/../../../config/BaseController.php';
require_once __DIR__ . '/../../../config/BaseModel.php';
require_once __DIR__ . '/../../../config/AuditLogger.php';
require_once __DIR__ . '/../models/PayrollModels.php';

class PayrollController extends BaseController {
    protected $salaryModel;
    protected $allowanceModel;
    protected $deductionRuleModel;
    protected $calendarModel;
    protected $payrollRunModel;
    protected $runEmployeeModel;
    protected $earningModel;
    protected $deductionModel;
    protected $payslipModel;
    protected $reissueModel;
    protected $auditLogger;
    protected $db;

    public function __construct() {
        parent::__construct();
        $dbInstance = new Database();
        $this->db = $dbInstance->connect();
        $this->salaryModel = new EmployeeSalary();
        $this->allowanceModel = new EmployeeAllowance();
        $this->deductionRuleModel = new EmployeeDeductionRule();
        $this->calendarModel = new PayrollCalendar();
        $this->payrollRunModel = new PayrollRun();
        $this->runEmployeeModel = new PayrollRunEmployee();
        $this->earningModel = new PayrollEarning();
        $this->deductionModel = new PayrollDeduction();
        $this->payslipModel = new Payslip();
        $this->reissueModel = new PayslipReissue();
        $this->auditLogger = new AuditLogger();
    }

    /**
     * Get salary templates (hospital: Doctor, Nurse, etc.)
     * GET ?action=getSalaryTemplates
     */
    public function getSalaryTemplates() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'payroll', 'finance']);
        try {
            $templateModel = new SalaryTemplate();
            $templates = $templateModel->getAllActive();
            $this->respondSuccess(['templates' => $templates]);
        } catch (Throwable $e) {
            $this->respondSuccess(['templates' => []]);
        }
    }

    // ===================== PAYROLL PROCESSING =====================

    /**
     * Get payroll calendar periods
     * GET ?action=getPayrollCalendar&frequency=Monthly
     */
    public function getPayrollCalendar() {
        $this->checkRole(['admin', 'hr', 'payroll', 'finance']);
        
        try {
            $frequency = $this->getInput('frequency');
            $status = $this->getInput('status');
            
            // Ensure Semi-Monthly periods exist (for hospital: payout 7th & 22nd)
            $this->ensureSemiMonthlyPeriods();
            
            $periods = $this->calendarModel->getOpenPeriods($frequency);
            if ($periods === false) {
                $periods = [];
            }
            
            if ($status) {
                $periods = array_filter($periods, function($p) use ($status) {
                    return isset($p['status']) && $p['status'] === $status;
                });
            }
            
            $this->respondSuccess([
                'periods' => array_values($periods),
                'total' => count($periods)
            ]);
        } catch (Exception $e) {
            $this->respondSuccess([
                'periods' => [],
                'total' => 0
            ]);
        }
    }

    /**
     * Insert Semi-Monthly periods for 2026 if missing (hospital: payout 7th & 22nd)
     */
    private function ensureSemiMonthlyPeriods() {
        try {
            $check = $this->db->query("SELECT 1 FROM payroll_calendar WHERE payroll_frequency = 'Semi-Monthly' AND status = 'Open' LIMIT 1");
            if ($check && $check->fetch()) {
                return; // Already have Semi-Monthly
            }
            // Payout: 7th & 22nd. Cutoff: 15th & 30th (or 28 for Feb)
            $periods = [
                ['Semi-Monthly', 'Jan 1-15 2026', '2026-01-A', '2026-01-01', '2026-01-15', '2026-01-15', '2026-01-22'],
                ['Semi-Monthly', 'Jan 16-30 2026', '2026-01-B', '2026-01-16', '2026-01-30', '2026-01-30', '2026-02-07'],
                ['Semi-Monthly', 'Feb 1-15 2026', '2026-02-A', '2026-02-01', '2026-02-15', '2026-02-15', '2026-02-22'],
                ['Semi-Monthly', 'Feb 16-28 2026', '2026-02-B', '2026-02-16', '2026-02-28', '2026-02-28', '2026-03-07'],
                ['Semi-Monthly', 'Mar 1-15 2026', '2026-03-A', '2026-03-01', '2026-03-15', '2026-03-15', '2026-03-22'],
                ['Semi-Monthly', 'Mar 16-30 2026', '2026-03-B', '2026-03-16', '2026-03-30', '2026-03-30', '2026-04-07'],
            ];
            $stmt = $this->db->prepare("INSERT IGNORE INTO payroll_calendar (payroll_frequency, period_name, period_code, start_date, end_date, cutoff_date, pay_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Open')");
            foreach ($periods as $p) {
                $stmt->execute($p);
            }
        } catch (Throwable $e) {
            // Ignore - table might not exist
        }
    }

    /**
     * Create payroll calendar period
     * POST ?action=createPayrollCalendar
     */
    public function createPayrollCalendar() {
        $this->checkRole(['admin', 'payroll']);
        
        $input = $this->getInput();
        $this->validate([
            'payroll_frequency' => 'required',
            'period_name' => 'required',
            'period_code' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'cutoff_date' => 'required',
            'pay_date' => 'required'
        ]);
        
        // Check if period code exists
        $existing = $this->calendarModel->getByPeriodCode($input['period_code']);
        if ($existing) {
            $this->respondError('Period code already exists', 400);
            return;
        }
        
        $calendar_id = $this->calendarModel->create($input);
        
        if (!$calendar_id) {
            $this->respondError('Failed to create payroll calendar', 400);
            return;
        }
        
        $this->auditLogger->log(
            'payroll_calendar_create',
            'Created payroll calendar period: ' . $input['period_name'],
            $calendar_id,
            $this->user['user_id']
        );
        
        $calendar = $this->calendarModel->find($calendar_id);
        $this->respondSuccess($calendar, 'Payroll calendar created successfully', 201);
    }

    /**
     * Get all payroll runs
     * GET ?action=getPayrollRuns&limit=20&offset=0&status=Draft
     */
    public function getPayrollRuns() {
        $this->checkRole(['admin', 'hr', 'payroll', 'finance']);
        
        try {
            $limit = $this->getInput('limit') ?? 20;
            $offset = $this->getInput('offset') ?? 0;
            $status = $this->getInput('status');
            $frequency = $this->getInput('frequency');
            
            $filters = [];
            if ($status) $filters['status'] = $status;
            if ($frequency) $filters['frequency'] = $frequency;
            
            $runs = $this->payrollRunModel->getAllWithDetails($limit, $offset, $filters);
            if ($runs === false) {
                $runs = [];
            }
            
            // Get total count with filters
            $total = count($runs);
            if (empty($filters)) {
                try {
                    $total = $this->payrollRunModel->count();
                } catch (Exception $e) {
                    $total = count($runs);
                }
            }
            
            $this->respondSuccess([
                'runs' => $runs ?: [],
                'total' => $total ?: 0,
                'limit' => $limit,
                'offset' => $offset
            ]);
        } catch (Exception $e) {
            $this->respondSuccess([
                'runs' => [],
                'total' => 0,
                'limit' => $limit ?? 20,
                'offset' => $offset ?? 0
            ]);
        }
    }

    /**
     * Get single payroll run with details
     * GET ?action=getPayrollRun&id=1
     */
    public function getPayrollRun() {
        $this->checkRole(['admin', 'hr', 'payroll', 'finance']);
        
        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Payroll run ID required', 400);
            return;
        }
        
        $run = $this->payrollRunModel->getWithDetails($id);
        if (!$run) {
            $this->respondError('Payroll run not found', 404);
            return;
        }
        
        // Get employees in this run
        $employees = $this->runEmployeeModel->getByPayrollRun($id);
        
        $this->respondSuccess([
            'run' => $run,
            'employees' => $employees,
            'employee_count' => count($employees)
        ]);
    }

    /**
     * Add eligible employees to an existing payroll run (e.g. run created when no salaries existed).
     * POST ?action=addEmployeesToRun&id=7
     */
    public function addEmployeesToRun() {
        $this->checkRole(['admin', 'payroll']);
        
        $run_id = $this->getInput('id');
        if (!$run_id) {
            $this->respondError('Payroll run ID required', 400);
            return;
        }
        
        $run = $this->payrollRunModel->find($run_id);
        if (!$run || $run['status'] !== 'Draft') {
            $this->respondError('Payroll run not found or not in Draft status', 400);
            return;
        }
        
        $existing = $this->runEmployeeModel->getByPayrollRun($run_id);
        $existing_ids = array_column($existing, 'employee_id');
        $eligible = $this->salaryModel->getEligibleEmployees();
        $added = 0;
        foreach ($eligible as $emp) {
            if (in_array((int)$emp['employee_id'], array_map('intval', $existing_ids))) {
                continue;
            }
            $this->runEmployeeModel->create([
                'payroll_run_id' => $run_id,
                'employee_id' => $emp['employee_id'],
                'status' => 'Pending'
            ]);
            $added++;
        }
        
        if ($added > 0) {
            $new_total = count($existing) + $added;
            $this->payrollRunModel->update($run_id, ['total_employees' => $new_total]);
        }
        
        $run = $this->payrollRunModel->getWithDetails($run_id);
        $employees = $this->runEmployeeModel->getByPayrollRun($run_id);
        $this->respondSuccess([
            'run' => $run,
            'employees' => $employees,
            'employee_count' => count($employees),
            'added' => $added
        ], $added > 0 ? 'Employees added to run' : 'No new employees to add');
    }

    /**
     * Create new payroll run
     * POST ?action=createPayrollRun
     */
    public function createPayrollRun() {
        $this->checkRole(['admin', 'payroll']);
        
        $input = $this->getInput();
        $this->validate([
            'period_start' => 'required',
            'period_end' => 'required',
            'pay_date' => 'required'
        ]);

        // Normalize frequency (UI is manual now; default to Semi-Monthly if not provided)
        $freqInput = $input['payroll_frequency'] ?? 'Semi-Monthly';
        $freq = trim($freqInput);
        $freq = preg_replace('/\s+/', '-', $freq);
        if (stripos($freq, 'semi') !== false && stripos($freq, 'month') !== false) {
            $freq = 'Semi-Monthly';
        } elseif (strtolower($freq) === 'weekly') {
            $freq = 'Weekly';
        } elseif (strtolower($freq) === 'monthly') {
            $freq = 'Monthly';
        } elseif (strtolower($freq) === 'annual') {
            $this->respondError('Annual payroll frequency is not allowed. Use Weekly, Semi-Monthly, or Monthly.', 400);
            return;
        } else {
            $freq = $freqInput;
        }
        $input['payroll_frequency'] = $freq;

        // Resolve / auto-create calendar period (manual setup)
        $calendar_id = null;
        if (!empty($input['calendar_id'])) {
            $calendar_id = (int) $input['calendar_id'];
        } else {
            $calendar_id = (int) $this->calendarModel->findOrCreateManualPeriod(
                $input['payroll_frequency'],
                $input['period_start'],
                $input['period_end'],
                $input['pay_date']
            );
        }

        if (!$calendar_id) {
            $this->respondError('Failed to resolve payroll calendar period', 500);
            return;
        }

        // Check if payroll run already exists for this period
        if ($this->payrollRunModel->existsForPeriod(
            $input['period_start'],
            $input['period_end'],
            $input['payroll_frequency']
        )) {
            $this->respondError('Payroll run already exists for this period', 400);
            return;
        }
        
        // Generate run number
        $run_number = $this->payrollRunModel->generateRunNumber(
            $input['payroll_frequency'],
            $input['period_start']
        );
        
        $run_data = [
            'calendar_id' => $calendar_id,
            'run_number' => $run_number,
            'run_date' => date('Y-m-d'),
            'payroll_frequency' => $input['payroll_frequency'],
            'period_start' => $input['period_start'],
            'period_end' => $input['period_end'],
            'pay_date' => $input['pay_date'],
            'status' => 'Draft',
            'created_by' => $this->user['user_id'],
            'notes' => $input['notes'] ?? null
        ];
        
        $this->db->beginTransaction();
        try {
            $run_id = $this->payrollRunModel->create($run_data);
            
            if (!$run_id) {
                throw new Exception('Failed to create payroll run');
            }
            
            // Get eligible employees
            $employees = $this->salaryModel->getEligibleEmployees();
            
            // Add employees to payroll run
            foreach ($employees as $emp) {
                $this->runEmployeeModel->create([
                    'payroll_run_id' => $run_id,
                    'employee_id' => $emp['employee_id'],
                    'status' => 'Pending'
                ]);
            }
            
            // Update total employees count
            $this->payrollRunModel->update($run_id, [
                'total_employees' => count($employees)
            ]);
            
            $this->db->commit();
            
            $this->auditLogger->log(
                'payroll_run_create',
                'Created payroll run: ' . $run_number,
                $run_id,
                $this->user['user_id']
            );
            
            $run = $this->payrollRunModel->getWithDetails($run_id);
            $this->respondSuccess($run, 'Payroll run created successfully', 201);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->respondError('Failed to create payroll run: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate payroll for a run
     * POST ?action=calculatePayroll&id=1
     */
    public function calculatePayroll() {
        $this->checkRole(['admin', 'payroll']);
        
        $run_id = $this->getInput('id');
        if (!$run_id) {
            $this->respondError('Payroll run ID required', 400);
            return;
        }
        
        $run = $this->payrollRunModel->find($run_id);
        if (!$run || $run['status'] !== 'Draft') {
            $this->respondError('Payroll run not found or not in Draft status', 400);
            return;
        }
        
        $employees = $this->runEmployeeModel->getByPayrollRun($run_id);
        
        $this->db->beginTransaction();
        try {
            $total_gross = 0;
            $total_deductions = 0;
            $total_net = 0;
            
            foreach ($employees as $emp) {
                $calculation = $this->calculateEmployeePayroll(
                    $emp['employee_id'],
                    $run['period_start'],
                    $run['period_end']
                );
                
                // Update run employee record
                $this->runEmployeeModel->update($emp['run_employee_id'], [
                    'regular_hours' => $calculation['regular_hours'],
                    'overtime_hours' => $calculation['overtime_hours'],
                    'night_differential_hours' => $calculation['night_differential_hours'],
                    'holiday_hours' => $calculation['holiday_hours'],
                    'basic_pay' => $calculation['basic_pay'],
                    'gross_pay' => $calculation['gross_pay'],
                    'total_deductions' => $calculation['total_deductions'],
                    'net_pay' => $calculation['net_pay'],
                    'status' => 'Calculated',
                    'validation_errors' => $calculation['errors'] ? json_encode($calculation['errors']) : null
                ]);
                
                // Save earnings
                foreach ($calculation['earnings'] as $earning) {
                    $this->earningModel->create([
                        'run_employee_id' => $emp['run_employee_id'],
                        'payroll_run_id' => $run_id,
                        'employee_id' => $emp['employee_id'],
                        'earning_type' => $earning['type'],
                        'earning_name' => $earning['name'],
                        'amount' => $earning['amount'],
                        'is_taxable' => $earning['is_taxable'] ?? true
                    ]);
                }
                
                // Save deductions
                foreach ($calculation['deductions'] as $deduction) {
                    $this->deductionModel->create([
                        'run_employee_id' => $emp['run_employee_id'],
                        'payroll_run_id' => $run_id,
                        'employee_id' => $emp['employee_id'],
                        'deduction_type' => $deduction['type'],
                        'deduction_name' => $deduction['name'],
                        'amount' => $deduction['amount'],
                        'employer_contribution' => $deduction['employer_contribution'] ?? 0
                    ]);
                }
                
                $total_gross += $calculation['gross_pay'];
                $total_deductions += $calculation['total_deductions'];
                $total_net += $calculation['net_pay'];
            }
            
            // Update payroll run totals
            $this->payrollRunModel->update($run_id, [
                'total_gross_pay' => $total_gross,
                'total_deductions' => $total_deductions,
                'total_net_pay' => $total_net
            ]);
            
            $this->db->commit();
            
            $this->auditLogger->log(
                'payroll_calculate',
                'Calculated payroll for run: ' . $run['run_number'],
                $run_id,
                $this->user['user_id']
            );
            
            $updated_run = $this->payrollRunModel->getWithDetails($run_id);
            $this->respondSuccess($updated_run, 'Payroll calculated successfully');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->respondError('Failed to calculate payroll: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Validate payroll run
     * POST ?action=validatePayroll&id=1
     */
    public function validatePayroll() {
        $this->checkRole(['admin', 'payroll']);
        
        $run_id = $this->getInput('id');
        if (!$run_id) {
            $this->respondError('Payroll run ID required', 400);
            return;
        }
        
        $run = $this->payrollRunModel->find($run_id);
        if (!$run || $run['status'] !== 'Draft') {
            $this->respondError('Payroll run not found or not in Draft status', 400);
            return;
        }
        
        // Validate all employees
        $employees = $this->runEmployeeModel->getByPayrollRun($run_id);
        $errors = [];
        
        foreach ($employees as $emp) {
            if ($emp['status'] !== 'Calculated') {
                $errors[] = "Employee {$emp['employee_name']} not calculated";
            }
            if ($emp['gross_pay'] <= 0) {
                $errors[] = "Employee {$emp['employee_name']} has zero gross pay";
            }
            if ($emp['net_pay'] < 0) {
                $errors[] = "Employee {$emp['employee_name']} has negative net pay";
            }
        }
        
        if (!empty($errors)) {
            $this->respondError('Validation failed', 400, ['errors' => $errors]);
            return;
        }
        
        // Update status
        $this->payrollRunModel->update($run_id, [
            'status' => 'Validated',
            'validated_by' => $this->user['user_id'],
            'validated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->auditLogger->log(
            'payroll_validate',
            'Validated payroll run: ' . $run['run_number'],
            $run_id,
            $this->user['user_id']
        );
        
        $updated_run = $this->payrollRunModel->getWithDetails($run_id);
        $this->respondSuccess($updated_run, 'Payroll validated successfully');
    }

    /**
     * Approve payroll run
     * POST ?action=approvePayroll&id=1
     */
    public function approvePayroll() {
        $this->checkRole(['admin']);
        
        $run_id = $this->getInput('id');
        if (!$run_id) {
            $this->respondError('Payroll run ID required', 400);
            return;
        }
        
        $run = $this->payrollRunModel->find($run_id);
        if (!$run || $run['status'] !== 'Validated') {
            $this->respondError('Payroll run not found or not validated', 400);
            return;
        }
        
        $this->db->beginTransaction();
        try {
            // Update payroll run status
            $this->payrollRunModel->update($run_id, [
                'status' => 'Approved',
                'approved_by' => $this->user['user_id'],
                'approved_at' => date('Y-m-d H:i:s')
            ]);
            
            // Generate payslips for all employees
            $employees = $this->runEmployeeModel->getByPayrollRun($run_id);
            
            foreach ($employees as $emp) {
                $payslip_number = $this->payslipModel->generatePayslipNumber(
                    $emp['employee_id'],
                    $run['period_end']
                );
                
                $this->payslipModel->create([
                    'payroll_run_id' => $run_id,
                    'employee_id' => $emp['employee_id'],
                    'run_employee_id' => $emp['run_employee_id'],
                    'payslip_number' => $payslip_number,
                    'period_start' => $run['period_start'],
                    'period_end' => $run['period_end'],
                    'pay_date' => $run['pay_date'],
                    'basic_pay' => $emp['basic_pay'],
                    'gross_pay' => $emp['gross_pay'],
                    'total_deductions' => $emp['total_deductions'],
                    'net_pay' => $emp['net_pay'],
                    'status' => 'Generated'
                ]);
            }
            
            $this->db->commit();
            
            $this->auditLogger->log(
                'payroll_approve',
                'Approved payroll run: ' . $run['run_number'],
                $run_id,
                $this->user['user_id']
            );
            
            $updated_run = $this->payrollRunModel->getWithDetails($run_id);
            $this->respondSuccess($updated_run, 'Payroll approved and payslips generated');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->respondError('Failed to approve payroll: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Lock payroll run
     * POST ?action=lockPayroll&id=1
     */
    public function lockPayroll() {
        $this->checkRole(['admin']);
        
        $run_id = $this->getInput('id');
        if (!$run_id) {
            $this->respondError('Payroll run ID required', 400);
            return;
        }
        
        $run = $this->payrollRunModel->find($run_id);
        if (!$run || $run['status'] !== 'Approved') {
            $this->respondError('Payroll run not found or not approved', 400);
            return;
        }
        
        $this->payrollRunModel->update($run_id, [
            'status' => 'Locked',
            'locked_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->auditLogger->log(
            'payroll_lock',
            'Locked payroll run: ' . $run['run_number'],
            $run_id,
            $this->user['user_id']
        );
        
        $updated_run = $this->payrollRunModel->getWithDetails($run_id);
        $this->respondSuccess($updated_run, 'Payroll locked successfully');
    }

    /**
     * Calculate employee payroll (helper method)
     */
    private function calculateEmployeePayroll($employee_id, $period_start, $period_end) {
        $result = [
            'regular_hours' => 0,
            'overtime_hours' => 0,
            'night_differential_hours' => 0,
            'holiday_hours' => 0,
            'basic_pay' => 0,
            'gross_pay' => 0,
            'total_deductions' => 0,
            'net_pay' => 0,
            'earnings' => [],
            'deductions' => [],
            'errors' => []
        ];
        
        // Get employee salary
        $salary = $this->salaryModel->getActiveSalary($employee_id);
        if (!$salary) {
            $result['errors'][] = 'No active salary found';
            return $result;
        }
        
        // TODO: Fetch attendance data (regular hours, overtime, night differential, holidays)
        // For now, using default values - this should integrate with attendance module
        $result['regular_hours'] = 160; // Default 160 hours per month
        $result['overtime_hours'] = 0;
        $result['night_differential_hours'] = 0;
        $result['holiday_hours'] = 0;
        
        // Calculate basic pay based on salary type
        $basic_rate = $salary['basic_rate'];
        switch ($salary['salary_type']) {
            case 'Hourly':
                $result['basic_pay'] = $basic_rate * $result['regular_hours'];
                break;
            case 'Daily':
                $result['basic_pay'] = $basic_rate * ($result['regular_hours'] / 8);
                break;
            case 'Monthly':
                $result['basic_pay'] = $basic_rate;
                break;
            case 'Annual':
                $result['basic_pay'] = $basic_rate / 12;
                break;
        }
        
        // Add overtime (1.25x rate)
        $overtime_pay = ($basic_rate / ($result['regular_hours'] ?: 160)) * $result['overtime_hours'] * 1.25;
        
        // Add night differential (10% premium)
        $night_diff_pay = ($basic_rate / ($result['regular_hours'] ?: 160)) * $result['night_differential_hours'] * 0.10;
        
        // Add holiday pay (2x rate)
        $holiday_pay = ($basic_rate / ($result['regular_hours'] ?: 160)) * $result['holiday_hours'] * 2.0;
        
        // Get allowances
        $allowances = $this->allowanceModel->getActiveAllowances($employee_id);
        $total_allowances = 0;
        foreach ($allowances as $allowance) {
            $total_allowances += $allowance['amount'];
            $result['earnings'][] = [
                'type' => $allowance['allowance_type'],
                'name' => $allowance['allowance_name'],
                'amount' => $allowance['amount'],
                'is_taxable' => $allowance['is_taxable']
            ];
        }
        
        // Calculate gross pay
        $result['gross_pay'] = $result['basic_pay'] + $overtime_pay + $night_diff_pay + $holiday_pay + $total_allowances;
        
        // Get deduction rules
        $deduction_rules = $this->deductionRuleModel->getActiveDeductionRules($employee_id);
        $total_deductions = 0;
        
        foreach ($deduction_rules as $rule) {
            $amount = 0;
            
            if ($rule['amount']) {
                $amount = $rule['amount'];
            } elseif ($rule['percentage']) {
                $base = $rule['calculation_base'] === 'Gross Pay' ? $result['gross_pay'] : $result['basic_pay'];
                $amount = $base * ($rule['percentage'] / 100);
            }
            
            // Special handling for tax
            if ($rule['deduction_type'] === 'Tax') {
                $amount = $this->calculateWithholdingTax($result['gross_pay'], $salary['tax_status'], $salary['dependents_count']);
            }
            
            if ($amount > 0) {
                $total_deductions += $amount;
                $result['deductions'][] = [
                    'type' => $rule['deduction_type'],
                    'name' => $rule['deduction_name'],
                    'amount' => $amount,
                    'employer_contribution' => 0
                ];
            }
        }
        
        $result['total_deductions'] = $total_deductions;
        $result['net_pay'] = $result['gross_pay'] - $total_deductions;
        
        return $result;
    }

    /**
     * Calculate withholding tax (simplified Philippine tax calculation)
     */
    private function calculateWithholdingTax($gross_pay, $tax_status, $dependents) {
        // Simplified tax calculation - should use actual BIR tax tables
        $annual_gross = $gross_pay * 12;
        $tax = 0;
        
        // Basic exemption based on status
        $exemption = 250000; // Single
        if (strpos($tax_status, 'Married') !== false) {
            $exemption = 500000;
            $exemption += $dependents * 25000; // Per dependent
        }
        
        $taxable_income = max(0, $annual_gross - $exemption);
        
        if ($taxable_income > 0) {
            // Simplified progressive tax (should use actual BIR tables)
            if ($taxable_income <= 250000) {
                $tax = 0;
            } elseif ($taxable_income <= 400000) {
                $tax = ($taxable_income - 250000) * 0.20;
            } elseif ($taxable_income <= 800000) {
                $tax = 30000 + ($taxable_income - 400000) * 0.25;
            } elseif ($taxable_income <= 2000000) {
                $tax = 130000 + ($taxable_income - 800000) * 0.30;
            } else {
                $tax = 490000 + ($taxable_income - 2000000) * 0.32;
            }
        }
        
        return $tax / 12; // Monthly tax
    }

    // ===================== SALARIES =====================

    /**
     * Get employee list for dropdowns (Salaries, etc.)
     * GET ?action=getEmployeeList&limit=1000
     */
    public function getEmployeeList() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'payroll', 'finance']);
        try {
            $limit = (int) ($this->getInput('limit') ?? 1000);
            $stmt = $this->db->prepare(
                "SELECT employee_id, employee_code, first_name, last_name FROM employees ORDER BY first_name ASC LIMIT " . $limit
            );
            $stmt->execute();
            $employees = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->respondSuccess(['employees' => $employees ?: [], 'total' => count($employees)]);
        } catch (\Throwable $e) {
            error_log('Payroll getEmployeeList: ' . $e->getMessage());
            $this->respondSuccess(['employees' => [], 'total' => 0]);
        }
    }

    /**
     * Get employee salary
     * GET ?action=getEmployeeSalary&employee_id=1
     */
    public function getEmployeeSalary() {
        $this->checkRole(['admin', 'hr', 'payroll', 'finance']);

        $employee_id = $this->getInput('employee_id');
        if (!$employee_id) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $salary = $this->salaryModel->getActiveSalary($employee_id);
        if (!$salary) {
            $this->respondError('No active salary found', 404);
            return;
        }
        
        // Get allowances and deductions
        $salary['allowances'] = $this->allowanceModel->getActiveAllowances($employee_id);
        $salary['deduction_rules'] = $this->deductionRuleModel->getActiveDeductionRules($employee_id);

        $this->respondSuccess($salary);
    }

    /**
     * Get salary history
     * GET ?action=getSalaryHistory&employee_id=1
     */
    public function getSalaryHistory() {
        $this->checkRole(['admin', 'hr', 'payroll', 'finance']);

        $employee_id = $this->getInput('employee_id');
        if (!$employee_id) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $history = $this->salaryModel->getSalaryHistory($employee_id);
        $this->respondSuccess([
            'history' => $history,
            'total' => count($history)
        ]);
    }

    /**
     * Create/Update employee salary
     * POST ?action=saveEmployeeSalary
     */
    public function saveEmployeeSalary() {
        $this->checkRole(['admin', 'hr', 'payroll']);

        $input = $this->getInput();
        $this->validate([
            'employee_id' => 'required|numeric',
            'salary_type' => 'required',
            'basic_rate' => 'required|numeric',
            'effective_date' => 'required'
        ]);

        // End previous salary record if exists
        $existing = $this->salaryModel->getActiveSalary($input['employee_id']);
        if ($existing && $input['effective_date'] > $existing['effective_date']) {
            $this->salaryModel->update($existing['salary_id'], [
                'end_date' => date('Y-m-d', strtotime($input['effective_date'] . ' -1 day'))
            ]);
        }
        
        $salary_id = $this->salaryModel->create($input);

        if (!$salary_id) {
            $this->respondError('Failed to save salary', 400);
            return;
        }
        
        $this->auditLogger->log(
            'salary_create',
            'Created salary for employee: ' . $input['employee_id'],
            $salary_id,
            $this->user['user_id']
        );

        $salary = $this->salaryModel->find($salary_id);
        $this->respondSuccess($salary, 'Salary saved successfully', 201);
    }

    /**
     * Save employee allowance
     * POST ?action=saveEmployeeAllowance
     */
    public function saveEmployeeAllowance() {
        $this->checkRole(['admin', 'hr', 'payroll']);
        
        $input = $this->getInput();
        $this->validate([
            'employee_id' => 'required|numeric',
            'allowance_type' => 'required',
            'allowance_name' => 'required',
            'amount' => 'required|numeric',
            'effective_date' => 'required'
        ]);
        
        $allowance_id = $this->allowanceModel->create($input);
        
        if (!$allowance_id) {
            $this->respondError('Failed to save allowance', 400);
            return;
        }
        
        $this->auditLogger->log(
            'allowance_create',
            'Created allowance for employee: ' . $input['employee_id'],
            $allowance_id,
            $this->user['user_id']
        );
        
        $allowance = $this->allowanceModel->find($allowance_id);
        $this->respondSuccess($allowance, 'Allowance saved successfully', 201);
    }

    /**
     * Save employee deduction rule
     * POST ?action=saveEmployeeDeductionRule
     */
    public function saveEmployeeDeductionRule() {
        $this->checkRole(['admin', 'hr', 'payroll']);
        
        $input = $this->getInput();
        $this->validate([
            'employee_id' => 'required|numeric',
            'deduction_type' => 'required',
            'deduction_name' => 'required',
            'effective_date' => 'required'
        ]);
        
        $rule_id = $this->deductionRuleModel->create($input);
        
        if (!$rule_id) {
            $this->respondError('Failed to save deduction rule', 400);
            return;
        }
        
        $this->auditLogger->log(
            'deduction_rule_create',
            'Created deduction rule for employee: ' . $input['employee_id'],
            $rule_id,
            $this->user['user_id']
        );
        
        $rule = $this->deductionRuleModel->find($rule_id);
        $this->respondSuccess($rule, 'Deduction rule saved successfully', 201);
    }

    // ===================== PAYSLIPS =====================

    /**
     * Get payslip
     * GET ?action=getPayslip&id=1
     */
    public function getPayslip() {
        $employee_id = $this->getInput('employee_id');
        $payslip_id = $this->getInput('id');

        if (!$payslip_id) {
            $this->respondError('Payslip ID required', 400);
            return;
        }

        $payslip = $this->payslipModel->getWithEmployee($payslip_id);
        if (!$payslip) {
            $this->respondError('Payslip not found', 404);
            return;
        }
        
        // Check access - employees can only view their own payslips
        if ($this->user['role'] !== 'admin' && $this->user['role'] !== 'hr' && $this->user['role'] !== 'payroll') {
            if ($payslip['employee_id'] != $employee_id) {
                $this->respondError('Unauthorized access', 403);
                return;
            }
        }
        
        // Get earnings and deductions
        $run_employee = $this->runEmployeeModel->find($payslip['run_employee_id']);
        $earnings = $this->earningModel->getByRunEmployee($payslip['run_employee_id']);
        $deductions = $this->deductionModel->getByRunEmployee($payslip['run_employee_id']);
        
        $payslip['earnings'] = $earnings;
        $payslip['deductions'] = $deductions;

        $this->respondSuccess($payslip);
    }

    /**
     * Get employee payslips
     * GET ?action=getEmployeePayslips&employee_id=1
     */
    public function getEmployeePayslips() {
        $employee_id = $this->getInput('employee_id');
        if (!$employee_id) {
            $this->respondError('Employee ID required', 400);
            return;
        }
        
        // Check access
        if ($this->user['role'] !== 'admin' && $this->user['role'] !== 'hr' && $this->user['role'] !== 'payroll') {
            // Employees can only view their own payslips
            // This would need to match employee_id from user session
        }

        $payslips = $this->payslipModel->getEmployeePayslips($employee_id);
        $this->respondSuccess([
            'payslips' => $payslips,
            'total' => count($payslips)
        ]);
    }

    /**
     * Reissue payslip
     * POST ?action=reissuePayslip&id=1
     */
    public function reissuePayslip() {
        $this->checkRole(['admin', 'payroll']);
        
        $payslip_id = $this->getInput('id');
        $reason = $this->getInput('reason');
        
        if (!$payslip_id || !$reason) {
            $this->respondError('Payslip ID and reason required', 400);
            return;
        }

        $payslip = $this->payslipModel->find($payslip_id);
        if (!$payslip) {
            $this->respondError('Payslip not found', 404);
            return;
        }
        
        // Log reissue
        $this->reissueModel->create([
            'payslip_id' => $payslip_id,
            'reason' => $reason,
            'reissued_by' => $this->user['user_id']
        ]);
        
        // Update payslip status
        $this->payslipModel->update($payslip_id, [
            'status' => 'Reissued',
            'issued_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->auditLogger->log(
            'payslip_reissue',
            'Reissued payslip: ' . $payslip['payslip_number'] . ' - ' . $reason,
            $payslip_id,
            $this->user['user_id']
        );
        
        $updated_payslip = $this->payslipModel->getWithEmployee($payslip_id);
        $this->respondSuccess($updated_payslip, 'Payslip reissued successfully');
    }

    /**
     * Route dispatcher
     */
    public function dispatch($action) {
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            Response::notFound('Action not found');
        }
    }
}

// Initialize and dispatch
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

try {
    $controller = new PayrollController();
    $action = $_GET['action'] ?? 'getPayrollRuns';
    $controller->dispatch($action);
} catch (Throwable $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => 'Internal Server Error',
        'data' => [],
        'error' => $e->getMessage()
    ]);
    exit;
}
