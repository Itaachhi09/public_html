<?php
/**
 * Compensation Module API
 * RESTful API endpoints for compensation management
 * Covers: Salary components, pay grades, grade levels, salary bands, 
 * incentive types, benefit definitions, adjustments, approvals, and employee assignments
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
require_once __DIR__ . '/models/SalaryComponentDefinition.php';
require_once __DIR__ . '/models/PayGrade.php';
require_once __DIR__ . '/models/GradeLevel.php';
require_once __DIR__ . '/models/SalaryBand.php';
require_once __DIR__ . '/models/IncentiveType.php';
require_once __DIR__ . '/models/IncentiveEvent.php';
require_once __DIR__ . '/models/BenefitDefinition.php';
require_once __DIR__ . '/models/SalaryAdjustment.php';
require_once __DIR__ . '/models/CompensationApprovalRequest.php';
require_once __DIR__ . '/models/EmployeeContractAssignment.php';
require_once __DIR__ . '/models/OutOfBandApproval.php';

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
    $salaryComp = new SalaryComponentDefinition();
    $payGrade = new PayGrade();
    $gradeLevel = new GradeLevel();
    $salaryBand = new SalaryBand();
    $incentiveType = new IncentiveType();
    $incentiveEvent = new IncentiveEvent();
    $benefitDef = new BenefitDefinition();
    $salaryAdj = new SalaryAdjustment();
    $compApproval = new CompensationApprovalRequest();
    $empContract = new EmployeeContractAssignment();
    $outOfBand = new OutOfBandApproval();
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $response = ['success' => false, 'message' => 'Invalid action'];
    
    // ===== ROLE-BASED ACCESS CONTROL =====
    // Define restricted actions for each submodule
    $restrictedActions = [
        'compensation_approval' => [
            'getCompensationApprovals', 'getCompensationApprovalDetail', 'createCompensationApproval',
            'approveCompensationRequest', 'rejectCompensationRequest'
        ],
        'pay_bonds_contracts' => [
            'getEmployeeContracts', 'getEmployeeContractDetail', 'assignEmployeeContract'
        ],
        'compensation_versioning' => []  // No specific actions - history tracking is internal
    ];
    
    // Check if action is restricted
    foreach ($restrictedActions as $submodule => $actions) {
        if (in_array($action, $actions)) {
            if (!canAccessMenuItem('compensation', $submodule)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied: ' . $submodule]);
                exit;
            }
            break;
        }
    }
    
    switch ($action) {
        // ==================== SALARY COMPONENTS ====================
        case 'getSalaryComponents':
            $activeOnly = $_GET['active_only'] ?? '1';
            $response = ['success' => true, 'data' => $salaryComp->getAll($activeOnly == '1')];
            break;
        
        case 'getSalaryComponentsByType':
            $type = $_GET['type'] ?? '';
            if (!$type) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Component type required'];
            } else {
                $response = ['success' => true, 'data' => $salaryComp->getByType($type)];
            }
            break;
        
        case 'getSalaryComponentDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Component ID required'];
            } else {
                $component = $salaryComp->find($id);
                $response = $component ? 
                    ['success' => true, 'data' => $component] : 
                    ['success' => false, 'error' => 'Component not found', 'http_code' => 404];
            }
            break;
        
        case 'createSalaryComponent':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $required = ['code', 'name', 'component_type'];
            $missing = array_diff($required, array_keys($data));
            
            if ($missing) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required: ' . implode(', ', $missing)];
            } else {
                $id = $salaryComp->create($data);
                $response = ['success' => true, 'message' => 'Component created', 'id' => $id];
            }
            break;
        
        case 'updateSalaryComponent':
            $id = $_GET['id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Component ID required'];
            } else {
                $salaryComp->update($id, $data);
                $response = ['success' => true, 'message' => 'Component updated'];
            }
            break;
        
        // ==================== PAY GRADES ====================
        case 'getPayGrades':
            $activeOnly = $_GET['active_only'] ?? '1';
            $response = ['success' => true, 'data' => $payGrade->getAllWithBands($activeOnly == '1')];
            break;
        
        case 'getPayGradeDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Pay grade ID required'];
            } else {
                $grade = $payGrade->find($id);
                $response = $grade ? 
                    ['success' => true, 'data' => $grade] : 
                    ['success' => false, 'error' => 'Pay grade not found', 'http_code' => 404];
            }
            break;
        
        case 'createPayGrade':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['code']) || !isset($data['name'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Code and name required'];
            } else {
                $id = $payGrade->create($data);
                $response = ['success' => true, 'message' => 'Pay grade created', 'id' => $id];
            }
            break;
        
        case 'updatePayGrade':
            $id = $_GET['id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Pay grade ID required'];
            } else {
                $payGrade->update($id, $data);
                $response = ['success' => true, 'message' => 'Pay grade updated'];
            }
            break;
        
        // ==================== GRADE LEVELS ====================
        case 'getGradeLevels':
            $pay_grade_id = $_GET['pay_grade_id'] ?? null;
            if ($pay_grade_id) {
                $levels = $gradeLevel->getByPayGrade($pay_grade_id);
            } else {
                $levels = $gradeLevel->getAllWithGrade();
            }
            $response = ['success' => true, 'data' => $levels];
            break;
        
        case 'getGradeLevelDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Grade level ID required'];
            } else {
                $level = $gradeLevel->find($id);
                $response = $level ? 
                    ['success' => true, 'data' => $level] : 
                    ['success' => false, 'error' => 'Grade level not found', 'http_code' => 404];
            }
            break;
        
        case 'createGradeLevel':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['pay_grade_id']) || !isset($data['code']) || !isset($data['name'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Pay grade ID, code, and name required'];
            } else {
                $id = $gradeLevel->create($data);
                $response = ['success' => true, 'message' => 'Grade level created', 'id' => $id];
            }
            break;
        
        case 'updateGradeLevel':
            $id = $_GET['id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Grade level ID required'];
            } else {
                $gradeLevel->update($id, $data);
                $response = ['success' => true, 'message' => 'Grade level updated'];
            }
            break;
        
        // ==================== SALARY BANDS ====================
        case 'getSalaryBands':
            $bands = $salaryBand->getAllWithDetails();
            $response = ['success' => true, 'data' => $bands];
            break;
        
        case 'getSalaryBandDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Salary band ID required'];
            } else {
                $band = $salaryBand->find($id);
                $response = $band ? 
                    ['success' => true, 'data' => $band] : 
                    ['success' => false, 'error' => 'Salary band not found', 'http_code' => 404];
            }
            break;
        
        case 'createSalaryBand':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['pay_grade_id']) || !isset($data['min_salary']) || !isset($data['max_salary'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Pay grade ID, min and max salary required'];
            } else {
                $id = $salaryBand->create($data);
                $response = ['success' => true, 'message' => 'Salary band created', 'id' => $id];
            }
            break;
        
        case 'updateSalaryBand':
            $id = $_GET['id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Salary band ID required'];
            } else {
                $salaryBand->update($id, $data);
                $response = ['success' => true, 'message' => 'Salary band updated'];
            }
            break;
        
        // ==================== INCENTIVE TYPES ====================
        case 'getIncentiveTypes':
            $response = ['success' => true, 'data' => $incentiveType->getAll()];
            break;
        
        case 'getIncentiveTypeDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Incentive type ID required'];
            } else {
                $type = $incentiveType->find($id);
                $response = $type ? 
                    ['success' => true, 'data' => $type] : 
                    ['success' => false, 'error' => 'Incentive type not found', 'http_code' => 404];
            }
            break;
        
        case 'createIncentiveType':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['name'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Name required'];
            } else {
                $id = $incentiveType->create($data);
                $response = ['success' => true, 'message' => 'Incentive type created', 'id' => $id];
            }
            break;
        
        // ==================== INCENTIVE EVENTS ====================
        case 'getIncentiveEvents':
            $emp_id = $_GET['employee_id'] ?? null;
            $filters = [];
            if ($emp_id) {
                $filters['employee_id'] = $emp_id;
            }
            $events = $incentiveEvent->getAllWithDetails($filters);
            $response = ['success' => true, 'data' => $events];
            break;
        
        case 'getIncentiveEventDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Incentive event ID required'];
            } else {
                $event = $incentiveEvent->find($id);
                $response = $event ? 
                    ['success' => true, 'data' => $event] : 
                    ['success' => false, 'error' => 'Incentive event not found', 'http_code' => 404];
            }
            break;
        
        case 'createIncentiveEvent':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['employee_id']) || !isset($data['incentive_type_id'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee and incentive type required'];
            } else {
                $id = $incentiveEvent->create($data);
                $response = ['success' => true, 'message' => 'Incentive event created', 'id' => $id];
            }
            break;
        
        // ==================== BENEFIT DEFINITIONS ====================
        case 'getBenefitDefinitions':
            $response = ['success' => true, 'data' => $benefitDef->getAll()];
            break;
        
        case 'getBenefitDefinitionDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Benefit ID required'];
            } else {
                $benefit = $benefitDef->find($id);
                $response = $benefit ? 
                    ['success' => true, 'data' => $benefit] : 
                    ['success' => false, 'error' => 'Benefit not found', 'http_code' => 404];
            }
            break;
        
        case 'createBenefitDefinition':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['benefit_name'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Benefit name required'];
            } else {
                $id = $benefitDef->create($data);
                $response = ['success' => true, 'message' => 'Benefit created', 'id' => $id];
            }
            break;
        
        // ==================== SALARY ADJUSTMENTS ====================
        case 'getSalaryAdjustments':
            $emp_id = $_GET['employee_id'] ?? null;
            $filters = [];
            if ($emp_id) {
                $filters['employee_id'] = $emp_id;
            }
            $adjustments = $salaryAdj->getAllWithDetails($filters);
            $response = ['success' => true, 'data' => $adjustments];
            break;
        
        case 'getSalaryAdjustmentDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Adjustment ID required'];
            } else {
                $adj = $salaryAdj->find($id);
                $response = $adj ? 
                    ['success' => true, 'data' => $adj] : 
                    ['success' => false, 'error' => 'Adjustment not found', 'http_code' => 404];
            }
            break;
        
        case 'createSalaryAdjustment':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['employee_id']) || !isset($data['adjustment_amount'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee ID and adjustment amount required'];
            } else {
                $id = $salaryAdj->create($data);
                $response = ['success' => true, 'message' => 'Salary adjustment created', 'id' => $id];
            }
            break;
        
        // ==================== COMPENSATION APPROVALS ====================
        case 'getCompensationApprovals':
            $status = $_GET['status'] ?? null;
            $filters = [];
            if ($status) {
                $filters['status'] = $status;
            }
            $approvals = $compApproval->getAll($filters);
            $response = ['success' => true, 'data' => $approvals];
            break;
        
        case 'getCompensationApprovalDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Approval ID required'];
            } else {
                $approval = $compApproval->find($id);
                $response = $approval ? 
                    ['success' => true, 'data' => $approval] : 
                    ['success' => false, 'error' => 'Approval not found', 'http_code' => 404];
            }
            break;
        
        case 'createCompensationApproval':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['request_type'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Request type required'];
            } else {
                $id = $compApproval->create($data);
                $response = ['success' => true, 'message' => 'Approval request created', 'id' => $id];
            }
            break;
        
        case 'approveCompensationRequest':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Approval ID required'];
            } else {
                $compApproval->update($id, ['status' => 'Approved', 'approved_by' => $_SESSION['user_id'] ?? null, 'approved_at' => date('Y-m-d H:i:s')]);
                $response = ['success' => true, 'message' => 'Request approved'];
            }
            break;
        
        case 'rejectCompensationRequest':
            $id = $_GET['id'] ?? null;
            $reason = $_GET['reason'] ?? '';
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Approval ID required'];
            } else {
                $compApproval->update($id, ['status' => 'Rejected', 'rejection_reason' => $reason]);
                $response = ['success' => true, 'message' => 'Request rejected'];
            }
            break;
        
        // ==================== EMPLOYEE CONTRACT ASSIGNMENTS ====================
        case 'getEmployeeContracts':
            $emp_id = $_GET['employee_id'] ?? null;
            $filters = [];
            if ($emp_id) {
                $filters['employee_id'] = $emp_id;
            }
            $contracts = $empContract->getAllWithDetails($filters);
            $response = ['success' => true, 'data' => $contracts];
            break;
        
        case 'getEmployeeContractDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Contract ID required'];
            } else {
                $contract = $empContract->find($id);
                $response = $contract ? 
                    ['success' => true, 'data' => $contract] : 
                    ['success' => false, 'error' => 'Contract not found', 'http_code' => 404];
            }
            break;
        
        case 'assignEmployeeContract':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['employee_id']) || !isset($data['pay_contract_id'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee and contract required'];
            } else {
                $id = $empContract->create($data);
                $response = ['success' => true, 'message' => 'Contract assigned', 'id' => $id];
            }
            break;
        
        // ==================== OUT OF BAND APPROVALS ====================
        case 'getOutOfBandRequests':
            $status = $_GET['status'] ?? null;
            if ($status === 'pending') {
                $requests = $outOfBand->getPending();
            } else {
                $requests = $outOfBand->query('SELECT o.*, sb.min_salary, sb.midpoint_salary, sb.max_salary FROM out_of_band_approvals o LEFT JOIN salary_bands sb ON sb.id = o.salary_band_id ORDER BY o.created_at DESC');
            }
            $response = ['success' => true, 'data' => $requests];
            break;
        
        case 'getOutOfBandRequestDetail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Request ID required'];
            } else {
                $req = $outOfBand->find($id);
                $response = $req ? 
                    ['success' => true, 'data' => $req] : 
                    ['success' => false, 'error' => 'Request not found', 'http_code' => 404];
            }
            break;
        
        case 'createOutOfBandRequest':
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (!isset($data['employee_id']) || !isset($data['proposed_salary'])) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee ID and proposed salary required'];
            } else {
                $id = $outOfBand->create($data);
                $response = ['success' => true, 'message' => 'Out of band request created', 'id' => $id];
            }
            break;
        
        case 'approveOutOfBandRequest':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Request ID required'];
            } else {
                $outOfBand->update($id, ['status' => 'Approved']);
                $response = ['success' => true, 'message' => 'Out of band request approved'];
            }
            break;
        
        default:
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Invalid action: ' . htmlspecialchars($action)];
    }
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Compensation API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    $response = [
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ];
}

// Send response
echo json_encode($response);
exit;
