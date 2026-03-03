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
                           e.employee_id, e.first_name, e.last_name, e.position, e.employee_code
                    FROM payslip_records pr
                    JOIN employees e ON pr.employee_id = e.employee_id
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
