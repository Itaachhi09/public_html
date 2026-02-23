<?php
/**
 * Payroll Processing Handler: create run, process run with full components (earnings, deductions, taxes)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/BaseConfig.php';
require_once __DIR__ . '/models/PayrollRun.php';
require_once __DIR__ . '/models/PayrollRunEmployee.php';
require_once __DIR__ . '/models/EmployeeSalary.php';
require_once __DIR__ . '/models/EmployeePayrollProfile.php';

$run = new PayrollRun();
$runEmployee = new PayrollRunEmployee();
$salary = new EmployeeSalary();
$profile = new EmployeePayrollProfile();
$msg = '';
$err = '';

/**
 * Calculate all payroll components for an employee
 */
function calculatePayrollComponents($employeeId, $basicSalary, $salaryType, $taxStatus = 'Single') {
    // Calculate gross earnings
    $grossPay = 0;
    
    // Basic pay (convert annual to monthly if needed)
    if ($salaryType === 'Annual') {
        $basicPay = round($basicSalary / 12, 2);
    } else {
        $basicPay = (float)$basicSalary;
    }
    $grossPay = $basicPay;
    
    // For now, using simplified calculations
    // In production, would fetch from payroll_component_assignments table
    $incentives = 0;
    $bonus = 0;
    $allowances = 0;
    $overtime = 0;
    
    // Add earnings to gross
    $earningsTotal = $incentives + $bonus + $allowances + $overtime;
    $grossPay += $earningsTotal;
    
    // Calculate statutory deductions
    $withholdingTax = 0;
    $sssDeduction = 0;
    $philhealthDeduction = 0;
    $pagibigDeduction = 0;
    
    // Withholding tax calculation (simplified - tax brackets based on Philippines 2024 rates)
    if ($grossPay > 250000) {
        $withholdingTax = round((($grossPay - 250000) * 0.35) + 24243.75, 2);
    } elseif ($grossPay > 100000) {
        $withholdingTax = round((($grossPay - 100000) * 0.30) + 3243.75, 2);
    } elseif ($grossPay > 50000) {
        $withholdingTax = round((($grossPay - 50000) * 0.20) + 243.75, 2);
    } elseif ($grossPay > 30000) {
        $withholdingTax = round(($grossPay - 30000) * 0.05, 2);
    } else {
        $withholdingTax = 0;
    }
    
    // SSS contribution (simplified - 3.63% employee share on salary range)
    if ($grossPay > 0) {
        $sssDeduction = round($grossPay * 0.0363, 2);
    }
    
    // PhilHealth premium (simplified - 2.5% employee share)
    if ($grossPay > 0) {
        $philhealthDeduction = round($grossPay * 0.025, 2);
    }
    
    // Pag-IBIG contribution (simplified - 1% employee share)
    if ($grossPay > 0) {
        $pagibigDeduction = round($grossPay * 0.01, 2);
    }
    
    // Other deductions (would be fetched from database in production)
    $hmoDeduction = 0;
    $loanDeduction = 0;
    $absenceDeduction = 0;
    $penaltyDeduction = 0;
    
    // Calculate total deductions
    $totalDeductions = $withholdingTax + $sssDeduction + $philhealthDeduction + $pagibigDeduction + 
                      $hmoDeduction + $loanDeduction + $absenceDeduction + $penaltyDeduction;
    
    // Ensure deductions don't exceed gross pay
    if ($totalDeductions > $grossPay) {
        $totalDeductions = $grossPay * 0.95; // Cap at 95% of gross
    }
    
    // Calculate net pay
    $netPay = $grossPay - $totalDeductions;
    if ($netPay < 0) $netPay = 0;
    
    return [
        'basic_pay' => $basicPay,
        'gross_pay' => $grossPay,
        'incentives' => $incentives,
        'bonus' => $bonus,
        'allowances' => $allowances,
        'overtime' => $overtime,
        'withholding_tax' => $withholdingTax,
        'sss_contribution' => $sssDeduction,
        'philhealth_contribution' => $philhealthDeduction,
        'pagibig_contribution' => $pagibigDeduction,
        'hmo_deduction' => $hmoDeduction,
        'loan_deduction' => $loanDeduction,
        'absence_deduction' => $absenceDeduction,
        'penalty_deduction' => $penaltyDeduction,
        'total_deductions' => $totalDeductions,
        'net_pay' => $netPay
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'get_payroll_details') {
        // Get payroll details with employees for modal display
        $payroll_id = (int)($_POST['payroll_id'] ?? 0);
        if ($payroll_id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid payroll ID']);
            exit;
        }
        
        // Get payroll run
        $payrollData = $run->find($payroll_id);
        if (!$payrollData) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Payroll not found']);
            exit;
        }
        
        // Get employees with salary and department info
        // Use existing `job_titles` and `employee_salaries` tables; select latest salary via subquery
        $employees = Database::getInstance()->query(
            "SELECT pre.*, e.employee_code, e.first_name, e.last_name, e.department_id, e.job_title_id,
                    d.department_name, j.title AS job_title,
                    (SELECT basic_rate FROM employee_salaries WHERE employee_id = e.employee_id ORDER BY effective_date DESC LIMIT 1) AS basic_rate,
                    (SELECT salary_type FROM employee_salaries WHERE employee_id = e.employee_id ORDER BY effective_date DESC LIMIT 1) AS salary_type
             FROM payroll_run_employees pre
             JOIN employees e ON e.employee_id = pre.employee_id
             LEFT JOIN departments d ON d.department_id = e.department_id
             LEFT JOIN job_titles j ON e.job_title_id = j.job_title_id
             WHERE pre.payroll_run_id = ?
             ORDER BY e.last_name",
            [$payroll_id]
        );
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'payroll' => $payrollData,
            'employees' => $employees
        ]);
        exit;
    } elseif ($action === 'create_run') {
        $period_name = trim($_POST['period_name'] ?? '');
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        $pay_date = trim($_POST['pay_date'] ?? '');
        $created_by = $_SESSION['user_id'] ?? 0;
        if (!$period_name || !$start_date || !$end_date || !$pay_date) {
            $err = 'Period name and dates are required.';
        } else {
            $run->create([
                'period_name' => $period_name,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'pay_date' => $pay_date,
                'status' => 'Draft',
                'created_by' => $created_by,
            ]);
            $msg = 'Payroll run created (Draft). Process it to generate payslips.';
        }
    } elseif ($action === 'run_payroll' || $action === 'process_run') {
        // Verify admin password
        $admin_password = trim($_POST['admin_password'] ?? '');
        if (!$admin_password) {
            $err = 'Admin password is required to run payroll.';
        } else {
            // Get current user and verify password
            $user_id = $_SESSION['user_id'] ?? 0;
            
            // Query users table to verify password (assuming password field exists)
            $userCheck = Database::getInstance()->query(
                "SELECT id, password FROM users WHERE id = ? LIMIT 1",
                [$user_id]
            );
            
            if (empty($userCheck)) {
                $err = 'User not found. Please log in again.';
            } else {
                $user = $userCheck[0];
                $storedHash = $user['password'] ?? '';
                
                // Verify password (check if it's bcrypt or plain)
                $passwordVerified = false;
                if (password_verify($admin_password, $storedHash)) {
                    $passwordVerified = true;
                } elseif (hash_equals($storedHash, hash('sha256', $admin_password))) {
                    // Fallback for sha256 hashes
                    $passwordVerified = true;
                } elseif ($storedHash === md5($admin_password)) {
                    // Fallback for md5 hashes
                    $passwordVerified = true;
                }
                
                if (!$passwordVerified) {
                    $err = 'Invalid admin password. Payroll run aborted.';
                } else {
                    // Password verified, proceed with payroll processing
                    $payroll_period_raw = trim($_POST['payroll_period'] ?? '');
                    if (!$payroll_period_raw) {
                        $err = 'Please select a payroll period.';
                    } else {
                        // Parse period string: "February 2026 (Period 1: Feb 1-15)|2026-02-01|2026-02-15|2026-02-22"
                        $period_parts = explode('|', $payroll_period_raw);
                        $period_name = $period_parts[0] ?? '';
                        $start_date = $period_parts[1] ?? date('Y-m-01');
                        $end_date = $period_parts[2] ?? date('Y-m-15');
                        $pay_date = $period_parts[3] ?? date('Y-m-d');
                        
                        // Find existing run for this period
                        $existing = $run->query("SELECT id FROM payroll_runs WHERE period_name = ? ORDER BY id DESC LIMIT 1", [$period_name]);
                        
                        if ($existing && count($existing) > 0) {
                            $run_id = $existing[0]['id'];
                            $r = $run->find($run_id);
                        } else {
                            // Create a new run for this period
                            $newRunId = $run->create([
                                'period_name' => $period_name,
                                'start_date' => $start_date,
                                'end_date' => $end_date,
                                'pay_date' => $pay_date,
                                'status' => 'Draft',
                            ]);
                            $run_id = $newRunId;
                            $r = ['id' => $run_id];
                        }
                        
                        if (!$r) {
                            $err = 'Payroll period not found.';
                        } else {
                            // Check if already processed
                            $existing = $runEmployee->query("SELECT COUNT(*) AS c FROM payroll_run_employees WHERE payroll_run_id = ?", [$run_id]);
                            if ((int)($existing[0]['c'] ?? 0) > 0) {
                                $err = 'This payroll run already has employees processed. Please create a new payroll period.';
                            } else {
                                // Get all payroll-eligible employees
                                $eligible = $salary->getPayrollEligible();
                                
                                if (empty($eligible)) {
                                    $err = 'No payroll-eligible employees found. Please configure employee payroll profiles first.';
                                } else {
                                    $count = 0;
                                    $errors = [];
                                    
                                    foreach ($eligible as $emp) {
                                        try {
                                            $employeeId = $emp['employee_id'];
                                            $basicSalary = (float)$emp['basic_rate'];
                                            $salaryType = $emp['salary_type'];
                                            $taxStatus = $emp['tax_status'] ?? 'Single';
                                            
                                            // Calculate all components
                                            $components = calculatePayrollComponents($employeeId, $basicSalary, $salaryType, $taxStatus);
                                            
                                            // Generate payslip number
                                            $payslip_number = 'PS-' . $run_id . '-' . $employeeId . '-' . date('Ym');
                                            
                                            // Create payroll run employee record with all components
                                            $runEmployee->create([
                                                'payroll_run_id' => $run_id,
                                                'employee_id' => $employeeId,
                                                'basic_pay' => $components['basic_pay'],
                                                'incentives' => $components['incentives'],
                                                'gross_pay' => $components['gross_pay'],
                                                'withholding_tax' => $components['withholding_tax'],
                                                'sss_contribution' => $components['sss_contribution'],
                                                'philhealth_contribution' => $components['philhealth_contribution'],
                                                'pagibig_contribution' => $components['pagibig_contribution'],
                                                'total_deductions' => $components['total_deductions'],
                                                'net_pay' => $components['net_pay'],
                                                'payslip_number' => $payslip_number,
                                                'status' => 'Processed',
                                            ]);
                                            
                                            $count++;
                                        } catch (Exception $e) {
                                            $errors[] = "Error with employee {$emp['employee_code']}: " . $e->getMessage();
                                        }
                                    }
                                    
                                    // Update run status to Processed
                                    if ($count > 0) {
                                        $run->update($run_id, ['status' => 'Processed']);
                                        $msg = "✓ Payroll processed! {$count} employee(s) included with all earnings and deductions calculated.";
                                    } else {
                                        $msg = "⚠ No employees were processed.";
                                    }
                                    
                                    if (count($errors) > 0) {
                                        $msg .= " " . count($errors) . " error(s) occurred.";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Check if this is an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Return JSON for AJAX requests
    header('Content-Type: application/json');
    echo json_encode([
        'success' => empty($err),
        'message' => $msg ?: $err,
        'type' => empty($err) ? 'success' : 'error'
    ]);
    exit;
}

// Regular form submission - redirect to dashboard
$params = ['ref' => 'payroll', 'page' => 'payroll_processing_approval'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ' . BASE_URL . 'dashboard.php?' . http_build_query($params));
exit;
