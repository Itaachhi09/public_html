<?php
/**
 * Payroll Module API
 * RESTful endpoints for payroll management
 * Covers: Payroll runs, employee profiles, salaries, adjustments, tax contributions,
 * disbursement, government reports, and audit trails
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error handling
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Set proper headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error',
        'error' => $errstr
    ]);
    exit;
});

ob_end_clean();

require_once __DIR__ . '/../../config/BaseConfig.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/models/PayrollRun.php';
require_once __DIR__ . '/models/PayrollRunEmployee.php';
require_once __DIR__ . '/models/EmployeeSalary.php';
require_once __DIR__ . '/models/EmployeePayrollProfile.php';
require_once __DIR__ . '/models/PayrollComponent.php';
require_once __DIR__ . '/models/PayrollAdjustment.php';
require_once __DIR__ . '/models/TaxContribution.php';
require_once __DIR__ . '/models/Disbursement.php';
require_once __DIR__ . '/models/GovernmentReport.php';
require_once __DIR__ . '/models/PayrollConfiguration.php';
require_once __DIR__ . '/models/PayrollApproval.php';
require_once __DIR__ . '/models/PayrollAuditTrail.php';

try {
    // Authentication
    $auth = new Auth();
    $token = $auth->getBearerToken() ?? ($_SESSION['token'] ?? null);
    
    if (!$token || !$auth->verifyToken($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    // Inject token for controller usage
    if (!$auth->getBearerToken()) {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    
    // Database connection
    $dbInstance = new Database();
    $db = $dbInstance->connect();
    
    // Initialize models
    $payrollRun = new PayrollRun();
    $runEmployee = new PayrollRunEmployee();
    $empSalary = new EmployeeSalary();
    $empProfile = new EmployeePayrollProfile();
    $component = new PayrollComponent();
    $adjustment = new PayrollAdjustment();
    $taxContribution = new TaxContribution();
    $disbursement = new Disbursement();
    $govReport = new GovernmentReport();
    $configuration = new PayrollConfiguration();
    $approval = new PayrollApproval();
    $auditTrail = new PayrollAuditTrail();
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    // ===== ROLE-BASED ACCESS CONTROL =====
    // Define restricted actions for each submodule
    $restrictedActions = [
        'setup_configuration' => [
            'getPayrollConfiguration', 'updatePayrollConfiguration'
        ],
        'tax_contributions_engine' => [
            'getTaxContributions', 'getTaxContributionDetail', 'createTaxContribution', 
            'updateTaxContribution', 'deleteTaxContribution'
        ],
        'disbursement_bank_files' => [
            'getDisbursements', 'getDisbursementDetail', 'createDisbursement', 
            'updateDisbursement', 'deleteDisbursement'
        ],
        'government_reports_compliance' => [
            'getGovernmentReports', 'getGovernmentReportDetail', 'createGovernmentReport', 
            'updateGovernmentReport', 'deleteGovernmentReport'
        ],
        'security_audit_trail' => [
            'getPayrollAuditTrails', 'getPayrollAuditTrailDetail'
        ]
    ];
    
    // Check if action is restricted
    foreach ($restrictedActions as $submodule => $actions) {
        if (in_array($action, $actions)) {
            if (!canAccessMenuItem('payroll', $submodule)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied: ' . $submodule]);
                exit;
            }
            break;
        }
    }
    
    switch ($action) {
        // ==================== PAYROLL RUNS ====================
        case 'getPayrollRuns':
            $status = $_GET['status'] ?? null;
            if ($status) {
                $runs = $payrollRun->getByStatus($status);
            } else {
                $runs = $payrollRun->getAll();
            }
            $response = ['success' => true, 'data' => $runs];
            break;
        
        case 'getPayrollRunDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            } else {
                $run = $payrollRun->find($id);
                if ($run) {
                    $run['employees'] = $runEmployee->getByRunWithEmployee($id);
                    $response = ['success' => true, 'data' => $run];
                } else {
                    http_response_code(404);
                    $response = ['success' => false, 'error' => 'Payroll run not found'];
                }
            }
            break;
        
        case 'createPayrollRun':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $required = ['period_name', 'start_date', 'end_date', 'pay_date'];
            $missing = array_diff($required, array_keys($data));
            
            if ($missing) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required: ' . implode(', ', $missing)];
            } else {
                $data['status'] = 'Draft';
                $id = $payrollRun->create($data);
                $response = ['success' => true, 'message' => 'Payroll run created', 'id' => $id];
            }
            break;
        
        case 'processPayrollRun':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            } else {
                $run = $payrollRun->find($id);
                if (!$run) {
                    http_response_code(404);
                    $response = ['success' => false, 'error' => 'Payroll run not found'];
                } else {
                    $payrollRun->update($id, ['status' => 'Processing']);
                    $response = ['success' => true, 'message' => 'Payroll run processing started'];
                }
            }
            break;
        
        case 'approvePayrollRun':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            } else {
                $payrollRun->update($id, ['status' => 'Approved', 'approved_by' => $_SESSION['user_id'] ?? null, 'approved_at' => date('Y-m-d H:i:s')]);
                $response = ['success' => true, 'message' => 'Payroll run approved'];
            }
            break;
        
        case 'rejectPayrollRun':
            $id = $_GET['id'] ?? null;
            $reason = $_GET['reason'] ?? '';
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            } else {
                $payrollRun->update($id, ['status' => 'Rejected', 'rejection_reason' => $reason]);
                $response = ['success' => true, 'message' => 'Payroll run rejected'];
            }
            break;
        
        case 'finalizePayrollRun':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            } else {
                $payrollRun->update($id, ['status' => 'Finalized', 'finalized_at' => date('Y-m-d H:i:s')]);
                $response = ['success' => true, 'message' => 'Payroll run finalized'];
            }
            break;
        
        // ==================== PAYROLL RUN EMPLOYEES ====================
        case 'getPayrollRunEmployees':
            $run_id = $_GET['payroll_run_id'] ?? null;
            if (!$run_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            } else {
                $employees = $runEmployee->getByRunWithEmployee($run_id);
                $response = ['success' => true, 'data' => $employees];
            }
            break;
        
        case 'getPayrollRunEmployeeDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Run employee ID required'];
            } else {
                $emp = $runEmployee->find($id);
                $response = $emp ? 
                    ['success' => true, 'data' => $emp] : 
                    ['success' => false, 'error' => 'Run employee not found', 'http_code' => 404];
            }
            break;
        
        case 'addEmployeeToPayrollRun':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['payroll_run_id']) || !isset($data['employee_id'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run and employee ID required'];
            } else {
                $id = $runEmployee->create($data);
                $response = ['success' => true, 'message' => 'Employee added to payroll run', 'id' => $id];
            }
            break;
        
        // ==================== EMPLOYEE SALARIES ====================
        case 'getEmployeeSalaries':
            $emp_id = $_GET['employee_id'] ?? null;
            if ($emp_id) {
                $salary = $empSalary->getCurrentForEmployee($emp_id);
                $salaries = $salary ? [$salary] : [];
            } else {
                $salaries = $empSalary->getAllWithEmployee();
            }
            $response = ['success' => true, 'data' => $salaries];
            break;
        
        case 'getEmployeeSalaryDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Salary ID required'];
            } else {
                $salary = $empSalary->find($id);
                $response = $salary ? 
                    ['success' => true, 'data' => $salary] : 
                    ['success' => false, 'error' => 'Salary not found', 'http_code' => 404];
            }
            break;
        
        case 'updateEmployeeSalary':
            $id = $_GET['id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Salary ID required'];
            } else {
                $empSalary->update($id, $data);
                $response = ['success' => true, 'message' => 'Salary updated'];
            }
            break;
        
        // ==================== EMPLOYEE PAYROLL PROFILES ====================
        case 'getEmployeePayrollProfiles':
            $response = ['success' => true, 'data' => $empProfile->getAll()];
            break;
        
        case 'getEmployeePayrollProfileDetail':
            $emp_id = $_GET['employee_id'] ?? null;
            if (!$emp_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee ID required'];
            } else {
                $profile = $empProfile->getByEmployee($emp_id);
                $response = $profile ? 
                    ['success' => true, 'data' => $profile] : 
                    ['success' => false, 'error' => 'Profile not found', 'http_code' => 404];
            }
            break;
        
        case 'updateEmployeePayrollProfile':
            $emp_id = $_GET['employee_id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$emp_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee ID required'];
            } else {
                $profile = $empProfile->getByEmployee($emp_id);
                if ($profile) {
                    $empProfile->update($profile['id'], $data);
                    $response = ['success' => true, 'message' => 'Profile updated'];
                } else {
                    http_response_code(404);
                    $response = ['success' => false, 'error' => 'Profile not found'];
                }
            }
            break;
        
        // ==================== PAYROLL COMPONENTS ====================
        case 'getPayrollComponents':
            $response = ['success' => true, 'data' => $component->getAll()];
            break;
        
        case 'getPayrollComponentDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Component ID required'];
            } else {
                $comp = $component->find($id);
                $response = $comp ? 
                    ['success' => true, 'data' => $comp] : 
                    ['success' => false, 'error' => 'Component not found', 'http_code' => 404];
            }
            break;
        
        // ==================== PAYROLL ADJUSTMENTS ====================
        case 'getPayrollAdjustments':
            $emp_id = $_GET['employee_id'] ?? null;
            if ($emp_id) {
                $adjustments = $adjustment->getByEmployee($emp_id);
            } else {
                $adjustments = $adjustment->getAll();
            }
            $response = ['success' => true, 'data' => $adjustments];
            break;
        
        case 'getPayrollAdjustmentDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Adjustment ID required'];
            } else {
                $adj = $adjustment->find($id);
                $response = $adj ? 
                    ['success' => true, 'data' => $adj] : 
                    ['success' => false, 'error' => 'Adjustment not found', 'http_code' => 404];
            }
            break;
        
        case 'createPayrollAdjustment':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['employee_id']) || !isset($data['adjustment_type'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee ID and adjustment type required'];
            } else {
                $id = $adjustment->create($data);
                $response = ['success' => true, 'message' => 'Adjustment created', 'id' => $id];
            }
            break;
        
        // ==================== TAX CONTRIBUTIONS ====================
        case 'getTaxContributions':
            $emp_id = $_GET['employee_id'] ?? null;
            if ($emp_id) {
                $taxes = $taxContribution->getByEmployee($emp_id);
            } else {
                $taxes = $taxContribution->getAll();
            }
            $response = ['success' => true, 'data' => $taxes];
            break;
        
        case 'getTaxContributionDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Tax contribution ID required'];
            } else {
                $tax = $taxContribution->find($id);
                $response = $tax ? 
                    ['success' => true, 'data' => $tax] : 
                    ['success' => false, 'error' => 'Tax contribution not found', 'http_code' => 404];
            }
            break;
        
        case 'createTaxContribution':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['employee_id']) || !isset($data['contribution_type'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee ID and contribution type required'];
            } else {
                $id = $taxContribution->create($data);
                $response = ['success' => true, 'message' => 'Tax contribution created', 'id' => $id];
            }
            break;
        
        // ==================== DISBURSEMENTS ====================
        case 'getDisbursements':
            $run_id = $_GET['payroll_run_id'] ?? null;
            if ($run_id) {
                $disbursements = $disbursement->getByPayrollRun($run_id);
            } else {
                $disbursements = $disbursement->getAll();
            }
            $response = ['success' => true, 'data' => $disbursements];
            break;
        
        case 'getDisbursementDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Disbursement ID required'];
            } else {
                $disb = $disbursement->find($id);
                $response = $disb ? 
                    ['success' => true, 'data' => $disb] : 
                    ['success' => false, 'error' => 'Disbursement not found', 'http_code' => 404];
            }
            break;
        
        case 'createDisbursement':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['payroll_run_id'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Payroll run ID required'];
            } else {
                $id = $disbursement->create($data);
                $response = ['success' => true, 'message' => 'Disbursement created', 'id' => $id];
            }
            break;
        
        // ==================== GOVERNMENT REPORTS ====================
        case 'getGovernmentReports':
            $month = $_GET['month'] ?? date('Y-m');
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            $reports = $govReport->getByPeriod($startDate, $endDate);
            $response = ['success' => true, 'data' => $reports];
            break;
        
        case 'getGovernmentReportDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Report ID required'];
            } else {
                $rep = $govReport->find($id);
                $response = $rep ? 
                    ['success' => true, 'data' => $rep] : 
                    ['success' => false, 'error' => 'Report not found', 'http_code' => 404];
            }
            break;
        
        case 'generateGovernmentReport':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['report_type']) || !isset($data['period'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Report type and period required'];
            } else {
                $id = $govReport->create($data);
                $response = ['success' => true, 'message' => 'Report generated', 'id' => $id];
            }
            break;
        
        case 'exportGovernmentReport':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Report ID required'];
            } else {
                $rep = $govReport->find($id);
                if ($rep) {
                    // Export logic here
                    $response = ['success' => true, 'message' => 'Report exported', 'data' => $rep];
                } else {
                    http_response_code(404);
                    $response = ['success' => false, 'error' => 'Report not found'];
                }
            }
            break;
        
        // ==================== PAYROLL CONFIGURATION ====================
        case 'getPayrollConfiguration':
            $config = $configuration->getActive();
            $response = ['success' => true, 'data' => $config];
            break;
        
        case 'updatePayrollConfiguration':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $key = $data['config_key'] ?? null;
            if (!$key) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'config_key required'];
            } else {
                $existing = $configuration->getByKey($key);
                if ($existing) {
                    $configuration->update($existing['id'], $data);
                    $response = ['success' => true, 'message' => 'Configuration updated', 'id' => $existing['id']];
                } else {
                    $id = $configuration->create($data);
                    $response = ['success' => true, 'message' => 'Configuration created', 'id' => $id];
                }
            }
            break;
        
        // ==================== PAYROLL APPROVALS ====================
        case 'getPayrollApprovals':
            $status = $_GET['status'] ?? null;
            if ($status) {
                $approvals = $approval->getByStatus($status);
            } else {
                $approvals = $approval->getAll();
            }
            $response = ['success' => true, 'data' => $approvals];
            break;
        
        case 'getPayrollApprovalDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Approval ID required'];
            } else {
                $appr = $approval->find($id);
                $response = $appr ? 
                    ['success' => true, 'data' => $appr] : 
                    ['success' => false, 'error' => 'Approval not found', 'http_code' => 404];
            }
            break;
        
        // ==================== AUDIT TRAILS ====================
        case 'getPayrollAuditTrails':
            $run_id = $_GET['payroll_run_id'] ?? null;
            if ($run_id) {
                $trails = $auditTrail->getByPayrollRun($run_id);
            } else {
                $trails = $auditTrail->getAll();
            }
            $response = ['success' => true, 'data' => $trails];
            break;
        
        case 'getPayrollAuditTrailDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Audit trail ID required'];
            } else {
                $trail = $auditTrail->find($id);
                $response = $trail ? 
                    ['success' => true, 'data' => $trail] : 
                    ['success' => false, 'error' => 'Audit trail not found', 'http_code' => 404];
            }
            break;
        
        default:
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Invalid action: ' . htmlspecialchars($action)];
    }
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Payroll API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    $response = [
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ];
}

// Send response
echo json_encode($response);
exit;
