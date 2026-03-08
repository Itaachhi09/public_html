<?php
/**
 * Payroll Module API
 * RESTful endpoints for payroll operations
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/BaseConfig.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/controllers/PayrollRunController.php';
require_once __DIR__ . '/controllers/SalaryController.php';
require_once __DIR__ . '/controllers/BonusIncentiveController.php';
require_once __DIR__ . '/controllers/DeductionController.php';
require_once __DIR__ . '/controllers/PayslipController.php';

/**
 * Generate Payslip HTML for download
 */
function generatePayslipHTML($payslip, $earnings, $deductions) {
    $totalEarnings = 0;
    $earningsHTML = '';
    
    if ($earnings && count($earnings) > 0) {
        foreach ($earnings as $e) {
            $amount = floatval($e['amount']);
            $totalEarnings += $amount;
            $earningsHTML .= '<tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($e['description']) . '</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">₱' . number_format($amount, 2) . '</td>
            </tr>';
        }
    }
    
    $totalDeductions = 0;
    $deductionsHTML = '';
    
    if ($deductions && count($deductions) > 0) {
        foreach ($deductions as $d) {
            $amount = floatval($d['amount']);
            $totalDeductions += $amount;
            $deductionsHTML .= '<tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . htmlspecialchars($d['description']) . '</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">₱' . number_format($amount, 2) . '</td>
            </tr>';
        }
    }
    
    $netPay = $payslip['net_pay'];
    $startDate = date('M d, Y', strtotime($payslip['start_date']));
    $endDate = date('M d, Y', strtotime($payslip['end_date']));
    $payDate = date('M d, Y', strtotime($payslip['pay_date']));
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip - ' . $payslip['employee_code'] . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #1f2937;
        }
        .header p {
            margin: 5px 0;
            color: #6b7280;
        }
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 4px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #1f2937;
            font-size: 0.9rem;
        }
        .info-value {
            color: #6b7280;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            color: #1f2937;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #3b82f6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background: #f3f4f6;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #d1d5db;
        }
        .summary {
            background: #f9fafb;
            padding: 15px;
            border-radius: 4px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
        }
        .net-pay {
            color: #10b981;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAYSLIP</h1>
        <p>Healthcare HR - Management System</p>
    </div>
    
    <div class="employee-info">
        <div>
            <div class="info-row">
                <div class="info-label">Employee Name</div>
                <div class="info-value">' . htmlspecialchars($payslip['first_name'] . ' ' . $payslip['last_name']) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Employee ID</div>
                <div class="info-value">' . htmlspecialchars($payslip['employee_code']) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Department</div>
                <div class="info-value">' . htmlspecialchars($payslip['department']) . '</div>
            </div>
        </div>
        <div>
            <div class="info-row">
                <div class="info-label">Position</div>
                <div class="info-value">' . htmlspecialchars($payslip['position']) . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pay Period</div>
                <div class="info-value">' . $startDate . ' to ' . $endDate . '</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pay Date</div>
                <div class="info-value">' . $payDate . '</div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Earnings</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                ' . ($earningsHTML ?: '<tr><td colspan="2" style="padding: 8px; text-align: center; color: #9ca3af;">No earnings recorded</td></tr>') . '
                <tr style="background: #f3f4f6; font-weight: bold;">
                    <td style="padding: 10px 8px;">Total Earnings</td>
                    <td style="padding: 10px 8px; text-align: right;">₱' . number_format($totalEarnings, 2) . '</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">Deductions</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                ' . ($deductionsHTML ?: '<tr><td colspan="2" style="padding: 8px; text-align: center; color: #9ca3af;">No deductions recorded</td></tr>') . '
                <tr style="background: #f3f4f6; font-weight: bold;">
                    <td style="padding: 10px 8px;">Total Deductions</td>
                    <td style="padding: 10px 8px; text-align: right;">₱' . number_format($totalDeductions, 2) . '</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">Salary Summary</div>
        <div class="summary">
            <div class="summary-item">
                <div class="summary-label">Gross Pay</div>
                <div class="summary-value">₱' . number_format($payslip['gross_pay'], 2) . '</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Deductions</div>
                <div class="summary-value">₱' . number_format($totalDeductions, 2) . '</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Net Pay</div>
                <div class="summary-value net-pay">₱' . number_format($netPay, 2) . '</div>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #d1d5db; font-size: 0.9rem; color: #6b7280; text-align: center;">
        <p>This is an official payslip generated by Healthcare HR - Management System</p>
        <p>Generated on ' . date('F d, Y H:i:s') . '</p>
    </div>
</body>
</html>';
    
    return $html;
}

try {
    // Check authentication
    $auth = new Auth();
    $token = $auth->getBearerToken() ?? ($_SESSION['token'] ?? null);
    
    // Allow if we have a valid token or valid session user
    $hasValidAuth = false;
    
    if ($token && $auth->verifyToken($token)) {
        $hasValidAuth = true;
    } elseif (!empty($_SESSION) && isset($_SESSION['user_id'])) {
        // Session-based authentication is valid
        $hasValidAuth = true;
    }
    
    if (!$hasValidAuth) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    if (empty($action)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Action parameter required']);
        exit;
    }

    $response = ['success' => false, 'error' => 'Invalid action'];

    // ===== PAYROLL RUN ACTIONS =====
    if (strpos($action, 'PayrollRun') !== false || strpos($action, 'payrollRun') !== false) {
        $controller = new PayrollRunController();
        
        if ($action === 'getPayrollRuns') {
            $response = $controller->getAll();
        } elseif ($action === 'getPayrollRunByStatus') {
            $status = $_GET['status'] ?? $_POST['status'] ?? null;
            $response = $controller->getByStatus($status);
        } elseif ($action === 'getArchivedPayrollRuns') {
            try {
                $db = new Database();
                $records = $db->query("SELECT * FROM payroll_runs WHERE status = 'Archived' ORDER BY updated_at DESC");
                $response = ['success' => true, 'records' => $records ?? []];
            } catch (Throwable $e) {
                $response = ['success' => false, 'error' => 'Database error', 'records' => []];
            }
        } elseif ($action === 'getPayrollRun') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->get($id);
        } elseif ($action === 'createPayrollRun') {
            $response = $controller->create();
        } elseif ($action === 'updatePayrollRun') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->update($id);
        } elseif ($action === 'deletePayrollRun') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->delete($id);
        } elseif ($action === 'archivePayrollRun') {
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            if ($id <= 0) {
                $response = ['success' => false, 'error' => 'Invalid ID'];
            } else {
                try {
                    $db = new Database();
                    // First, ensure the enum has 'Archived' as a valid value
                    $db->query("ALTER TABLE payroll_runs MODIFY COLUMN status enum('Draft','Processed','Closed','Archived') NOT NULL DEFAULT 'Draft'");
                    // Then update the record
                    $result = $db->query("UPDATE payroll_runs SET status = 'Archived', updated_at = NOW() WHERE id = ?", [$id]);
                    $response = ['success' => true, 'message' => 'Archived successfully'];
                } catch (Throwable $e) {
                    $response = ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
                }
            }
        } elseif ($action === 'restorePayrollRun') {
            $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            if ($id <= 0) {
                $response = ['success' => false, 'error' => 'Invalid ID'];
            } else {
                try {
                    $db = new Database();
                    // Restore to 'Processed' status
                    $result = $db->query("UPDATE payroll_runs SET status = 'Processed', updated_at = NOW() WHERE id = ?", [$id]);
                    $response = ['success' => true, 'message' => 'Restored successfully'];
                } catch (Throwable $e) {
                    $response = ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
                }
            }
        }
    }

    // ===== SALARY ACTIONS =====
    elseif (strpos($action, 'Salary') !== false) {
        $controller = new SalaryController();
        
        if ($action === 'getSalaries') {
            $response = $controller->getAll();
        } elseif ($action === 'getSalaryForEmployee') {
            $employeeId = $_GET['employee_id'] ?? $_POST['employee_id'] ?? null;
            $response = $controller->getForEmployee($employeeId);
        } elseif ($action === 'getPayrollEligibleSalaries') {
            $response = $controller->getPayrollEligible();
        } elseif ($action === 'createSalary') {
            $response = $controller->create();
        } elseif ($action === 'updateSalary') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->update($id);
        } elseif ($action === 'deleteSalary') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->delete($id);
        }
    }

    // ===== BONUS/INCENTIVE ACTIONS =====
    elseif (strpos($action, 'BonusIncentive') !== false || strpos($action, 'Bonus') !== false) {
        $controller = new BonusIncentiveController();
        
        if ($action === 'getBonusIncentives') {
            $response = $controller->getAll();
        } elseif ($action === 'getBonusIncentivesByPayrollRun') {
            $payrollRunId = $_GET['payroll_run_id'] ?? $_POST['payroll_run_id'] ?? null;
            $response = $controller->getByPayrollRun($payrollRunId);
        } elseif ($action === 'createBonusIncentive') {
            $response = $controller->create();
        } elseif ($action === 'updateBonusIncentive') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->update($id);
        } elseif ($action === 'deleteBonusIncentive') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->delete($id);
        }
    }

    // ===== DEDUCTION ACTIONS =====
    elseif (strpos($action, 'Deduction') !== false) {
        $controller = new DeductionController();
        
        if ($action === 'getDeductions') {
            $response = $controller->getAll();
        } elseif ($action === 'getDeductionsByPayrollRun') {
            $payrollRunId = $_GET['payroll_run_id'] ?? $_POST['payroll_run_id'] ?? null;
            $response = $controller->getByPayrollRun($payrollRunId);
        } elseif ($action === 'createDeduction') {
            $response = $controller->create();
        } elseif ($action === 'updateDeduction') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->update($id);
        } elseif ($action === 'deleteDeduction') {
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            $response = $controller->delete($id);
        }
    }

    // ===== PAYSLIP ACTIONS =====
    elseif (strpos($action, 'Payslip') !== false) {
        $controller = new PayslipController();
        
        if ($action === 'getPayslips') {
            $response = $controller->getAll();
        } elseif ($action === 'getPayslipsByPayrollRun') {
            $payrollRunId = $_GET['payroll_run_id'] ?? $_POST['payroll_run_id'] ?? null;
            $response = $controller->getByPayrollRun($payrollRunId);
        } elseif ($action === 'getPayslipForEmployee') {
            $payrollRunId = $_GET['payroll_run_id'] ?? $_POST['payroll_run_id'] ?? null;
            $employeeId = $_GET['employee_id'] ?? $_POST['employee_id'] ?? null;
            $response = $controller->getEmployeePayslip($payrollRunId, $employeeId);
        } elseif ($action === 'getEmployeePayslipHistory') {
            $employeeId = $_GET['employee_id'] ?? $_POST['employee_id'] ?? null;
            $response = $controller->getEmployeeHistory($employeeId);
        } elseif ($action === 'createPayslip') {
            $response = $controller->create();
        } elseif ($action === 'getPayrollRunPayslips') {
            // Special action to get payslips with payroll run info for printing
            $payrollRunId = $_GET['payroll_run_id'] ?? $_POST['payroll_run_id'] ?? null;
            if ($payrollRunId) {
                $db = new Database();
                $payslips = $db->query("
                    SELECT pr.id, pr.gross_pay, pr.total_deductions, pr.net_pay, pr.status,
                           e.employee_id, e.first_name, e.last_name, e.employee_code, COALESCE(jt.title, 'N/A') as position
                    FROM payslip_records pr
                    JOIN employees e ON pr.employee_id = e.employee_id
                    LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                    WHERE pr.payroll_run_id = ?
                    ORDER BY e.first_name, e.last_name
                ", [$payrollRunId]);
                
                $payrollRun = $db->query("SELECT id, period_name, pay_date FROM payroll_runs WHERE id = ?", [$payrollRunId]);
                
                $response = [
                    'success' => true,
                    'payslips' => $payslips ?? [],
                    'payroll_run' => $payrollRun[0] ?? []
                ];
            } else {
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            }
        } elseif ($action === 'getPayslipDetails') {
            // Get detailed view of a single payslip with earnings and deductions breakdown
            $payslipId = $_GET['id'] ?? $_POST['id'] ?? null;
            if ($payslipId) {
                $db = new Database();
                $payslip = $db->query("
                    SELECT p.*, e.employee_id, e.employee_code, e.first_name, e.last_name,
                           COALESCE(jt.title, 'N/A') as position, COALESCE(d.department_name, 'N/A') as department,
                           pr.period_name, pr.start_date, pr.end_date, pr.pay_date, pr.id as payroll_run_id
                    FROM payslip_records p
                    JOIN employees e ON p.employee_id = e.employee_id
                    LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    JOIN payroll_runs pr ON p.payroll_run_id = pr.id
                    WHERE p.id = ?
                    LIMIT 1
                ", [$payslipId]);
                
                if (!empty($payslip)) {
                    $payslipData = $payslip[0];
                    
                    // Fetch earnings breakdown - check both employee_earnings and by calculation
                    $earnings = $db->query("
                        SELECT earning_code, earning_type, description, amount
                        FROM employee_earnings
                        WHERE employee_id = ? AND payroll_run_id = ?
                        ORDER BY earning_type
                    ", [$payslipData['employee_id'], $payslipData['payroll_run_id']]);
                    
                    // Fetch deductions breakdown
                    $deductions = $db->query("
                        SELECT type, description, amount
                        FROM deductions
                        WHERE employee_id = ? AND payroll_run_id = ?
                        ORDER BY type
                    ", [$payslipData['employee_id'], $payslipData['payroll_run_id']]);
                    
                    $response = [
                        'success' => true,
                        'payslip' => $payslipData,
                        'earnings' => $earnings ?? [],
                        'deductions' => $deductions ?? [],
                        'hasDetails' => !empty($earnings) && !empty($deductions)
                    ];
                } else {
                    $response = ['success' => false, 'error' => 'Payslip not found'];
                }
            } else {
                $response = ['success' => false, 'error' => 'Payslip ID required'];
            }
        } elseif ($action === 'downloadPayslip') {
            // Download payslip as text/CSV or display as PDF
            $payslipId = $_GET['id'] ?? $_POST['id'] ?? null;
            if ($payslipId) {
                $db = new Database();
                $payslip = $db->query("
                    SELECT p.*, e.employee_id, e.employee_code, e.first_name, e.last_name,
                           COALESCE(jt.title, 'N/A') as position, COALESCE(d.department_name, 'N/A') as department,
                           pr.period_name, pr.start_date, pr.end_date, pr.pay_date, pr.id as payroll_run_id
                    FROM payslip_records p
                    JOIN employees e ON p.employee_id = e.employee_id
                    LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    JOIN payroll_runs pr ON p.payroll_run_id = pr.id
                    WHERE p.id = ?
                    LIMIT 1
                ", [$payslipId]);
                
                if (!empty($payslip)) {
                    $ps = $payslip[0];
                    
                    // Fetch earnings and deductions
                    $earnings = $db->query("
                        SELECT earning_type, description, amount FROM employee_earnings
                        WHERE employee_id = ? AND payroll_run_id = ?
                        ORDER BY earning_type
                    ", [$ps['employee_id'], $ps['payroll_run_id']]);
                    
                    $deductions = $db->query("
                        SELECT type, description, amount FROM deductions
                        WHERE employee_id = ? AND payroll_run_id = ?
                        ORDER BY type
                    ", [$ps['employee_id'], $ps['payroll_run_id']]);
                    
                    // Generate HTML for PDF
                    $html = generatePayslipHTML($ps, $earnings, $deductions);
                    
                    // Set headers for PDF download
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="Payslip_' . $ps['employee_code'] . '_' . date('Ymd') . '.pdf"');
                    
                    // For now, output HTML as a styled document (can be enhanced with TCPDF/mPDF later)
                    header('Content-Type: text/html; charset=utf-8');
                    header('Content-Disposition: attachment; filename="Payslip_' . $ps['employee_code'] . '_' . date('Ymd') . '.html"');
                    echo $html;
                    exit;
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Payslip not found']);
                    exit;
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Payslip ID required']);
                exit;
            }
        }
    }

    http_response_code($response['success'] ? 200 : 400);
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
