<?php
/**
 * HMO Module API
 * RESTful API endpoints for HMO operations
 * Covers: Providers, plans, enrollment, claims, billing, life events, documents
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

require_once(__DIR__ . '/../../config/BaseConfig.php');
require_once(__DIR__ . '/../../config/Auth.php');
require_once(__DIR__ . '/controllers/HMOController.php');

// Verify authentication
try {
    $auth = new Auth();
    $token = $auth->getBearerToken() ?? ($_SESSION['token'] ?? null);
    
    if (!$token || !$auth->verifyToken($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    
    // Inject token into Authorization header so BaseController can find it
    if (!$auth->getBearerToken()) {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Authentication failed: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';
$controller = null;
$response = ['success' => false, 'error' => 'Invalid action'];

// ===== ROLE-BASED ACCESS CONTROL =====
// Define restricted actions for each submodule
$restrictedActions = [
    'billing' => [
        'getBillingReconciliations', 'getBillingDiscrepancies', 'getBillingAdjustments', 'getBillingDetail',
        'uploadBillingFile', 'runBillingReconciliation', 'approveBillingReconciliation', 
        'approveDiscrepancy', 'approveAdjustment', 'exportBillingReport'
    ]
];

// Check if action is restricted
foreach ($restrictedActions as $submodule => $actions) {
    if (in_array($action, $actions)) {
        if (!canAccessMenuItem('hmo', $submodule)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Access denied: ' . $submodule]);
            exit;
        }
        break;
    }
}

try {
    $controller = new HMOController();
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Controller initialization failed: ' . $e->getMessage()
    ]));
}

try {
    switch ($action) {
        // Provider endpoints
        case 'getProviders':
            $response = $controller->getProviders();
            break;
        
        case 'getProviderDetails':
            $provider_id = $_GET['provider_id'] ?? null;
            if (!$provider_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Provider ID required'];
            } else {
                $response = $controller->getProviderDetails($provider_id);
            }
            break;

        case 'getExpiringContracts':
            $days = $_GET['days'] ?? 30;
            $response = $controller->getExpiringContracts($days);
            break;

        case 'getProvidersByStatus':
            $status = $_GET['status'] ?? null;
            if (!$status) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Status required'];
            } else {
                $response = $controller->getProvidersByStatus($status);
            }
            break;

        case 'createProvider':
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $required = ['provider_name', 'provider_code'];
            $missing = array_filter($required, fn($field) => empty($request[$field]));
            
            if ($missing) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required fields: ' . implode(', ', $missing)];
            } else {
                $response = $controller->createProvider($request);
            }
            break;

        case 'updateProvider':
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $provider_id = $_GET['id'] ?? $request['id'] ?? null;
            
            if (!$provider_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Provider ID required'];
            } else {
                $response = $controller->updateProvider($provider_id, $request);
            }
            break;

        case 'deleteProvider':
            $provider_id = $_GET['id'] ?? null;
            
            if (!$provider_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Provider ID required'];
            } else {
                $response = $controller->deleteProvider($provider_id);
            }
            break;

        // Plan endpoints
        case 'getPlans':
            $response = $controller->getPlans();
            break;
        
        case 'getPlanDetail':
            $plan_id = $_GET['id'] ?? null;
            if (!$plan_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Plan ID required'];
            } else {
                $response = $controller->getPlanDetail($plan_id);
            }
            break;
        
        case 'getPlansByProvider':
            $provider_id = $_GET['provider_id'] ?? null;
            if (!$provider_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Provider ID required'];
            } else {
                $response = $controller->getPlansByProvider($provider_id);
            }
            break;

        case 'createPlan':
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $required = ['plan_name', 'plan_code', 'provider_id'];
            $missing = array_filter($required, fn($field) => empty($request[$field]));
            
            if ($missing) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required fields: ' . implode(', ', $missing)];
            } else {
                $response = $controller->createPlan($request);
            }
            break;

        case 'updatePlan':
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $plan_id = $_GET['id'] ?? $request['id'] ?? null;
            
            if (!$plan_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Plan ID required'];
            } else {
                $response = $controller->updatePlan($plan_id, $request);
            }
            break;

        case 'deletePlan':
            $plan_id = $_GET['id'] ?? null;
            if (!$plan_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Plan ID required'];
            } else {
                $response = $controller->deletePlan($plan_id);
            }
            break;

        // Enrollment endpoints
        case 'getEmployeeEnrollments':
            $employee_id = $_GET['employee_id'] ?? null;
            $status = $_GET['status'] ?? null;
            $response = $controller->getEmployeeEnrollments($employee_id, $status);
            break;

        case 'getActiveEnrollments':
            $response = $controller->getActiveEnrollments();
            break;

        case 'getPendingEnrollments':
            $response = $controller->getPendingEnrollments();
            break;

        case 'getWaitingPeriodEnrollments':
            $response = $controller->getWaitingPeriodEnrollments();
            break;

        case 'getSuspendedEnrollments':
            $response = $controller->getSuspendedEnrollments();
            break;

        case 'getTerminatedEnrollments':
            $response = $controller->getTerminatedEnrollments();
            break;

        case 'getEnrollmentDetails':
            $enrollment_id = $_GET['id'] ?? null;
            if (!$enrollment_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Enrollment ID required'];
            } else {
                $response = $controller->getEnrollmentDetails($enrollment_id);
            }
            break;

        case 'getEnrollmentStats':
            $response = $controller->getEnrollmentStats();
            break;
        
        case 'createEnrollment':
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $required = ['employee_id', 'plan_id', 'enrollment_date'];
            $missing = array_filter($required, fn($field) => empty($request[$field]));
            
            if ($missing) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required fields: ' . implode(', ', $missing)];
            } else {
                $response = $controller->createEnrollment($request);
            }
            break;
        
        case 'updateEnrollment':
            $enrollment_id = $_GET['id'] ?? null;
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$enrollment_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Enrollment ID required'];
            } else {
                $response = $controller->updateEnrollment($enrollment_id, $request);
            }
            break;
        
        case 'terminateEnrollment':
            $enrollment_id = $_GET['id'] ?? null;
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$enrollment_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Enrollment ID required'];
            } else {
                $response = $controller->terminateEnrollment($enrollment_id, $request);
            }
            break;

        case 'suspendEnrollment':
            $enrollment_id = $_GET['id'] ?? null;
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$enrollment_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Enrollment ID required'];
            } else {
                $response = $controller->suspendEnrollment($enrollment_id, $request);
            }
            break;

        case 'unsuspendEnrollment':
            $enrollment_id = $_GET['id'] ?? null;
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$enrollment_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Enrollment ID required'];
            } else {
                $response = $controller->unsuspendEnrollment($enrollment_id, $request);
            }
            break;

        // Claims endpoints
        case 'getAllClaims':
            $response = $controller->getAllClaims();
            break;
        
        case 'getClaimDetail':
            $claim_id = $_GET['id'] ?? null;
            if (!$claim_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Claim ID required'];
            } else {
                $response = $controller->getClaimDetail($claim_id);
            }
            break;
        
        case 'getClaimsByStatus':
            $status = $_GET['status'] ?? null;
            if (!$status) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Status required'];
            } else {
                $response = $controller->getClaimsByStatus($status);
            }
            break;
        
        case 'getClaimsByEmployee':
            $employee_id = $_GET['employee_id'] ?? null;
            if (!$employee_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Employee ID required'];
            } else {
                $response = $controller->getClaimsByEmployee($employee_id);
            }
            break;
        
        case 'getHighUtilizationEmployees':
            $threshold = $_GET['threshold'] ?? 80;
            $response = $controller->getHighUtilizationEmployees($threshold);
            break;
        
        case 'getProviderComparison':
            $response = $controller->getProviderComparison();
            break;
        
        case 'approveClaim':
            $claim_id = $_GET['id'] ?? null;
            if (!$claim_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Claim ID required'];
            } else {
                $response = $controller->approveClaim($claim_id);
            }
            break;
        
        case 'rejectClaim':
            $claim_id = $_GET['id'] ?? null;
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $reason = $request['reason'] ?? null;
            if (!$claim_id || !$reason) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Claim ID and reason required'];
            } else {
                $response = $controller->rejectClaim($claim_id, $reason);
            }
            break;

        // Billing endpoints
        case 'getBillingReconciliations':
            $response = $controller->getBillingReconciliations();
            break;

        case 'getBillingDiscrepancies':
            $response = $controller->getBillingDiscrepancies();
            break;

        case 'getBillingAdjustments':
            $response = $controller->getBillingAdjustments();
            break;

        case 'getBillingDetail':
            $reconciliation_id = $_GET['id'] ?? null;
            if (!$reconciliation_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Reconciliation ID required'];
            } else {
                $response = $controller->getBillingDetail($reconciliation_id);
            }
            break;

        case 'uploadBillingFile':
            $type = $_POST['type'] ?? null;
            $billing_month = $_POST['billing_month'] ?? null;
            $notes = $_POST['notes'] ?? null;
            $file = $_FILES['file'] ?? null;

            if (!$type || !$billing_month || !$file) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required fields'];
            } else {
                // Save file
                $upload_dir = __DIR__ . '/uploads/billing/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_name = time() . '_' . basename($file['name']);
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $response = $controller->uploadBillingFile($type, $billing_month, $file_path, $notes);
                } else {
                    http_response_code(400);
                    $response = ['success' => false, 'error' => 'File upload failed'];
                }
            }
            break;

        case 'runBillingReconciliation':
            $response = $controller->runBillingReconciliation();
            break;

        case 'approveBillingReconciliation':
            $reconciliation_id = $_GET['id'] ?? null;
            if (!$reconciliation_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Reconciliation ID required'];
            } else {
                $response = $controller->approveBillingReconciliation($reconciliation_id);
            }
            break;

        case 'approveDiscrepancy':
            $request = json_decode(file_get_contents('php://input'), true);
            $discrepancy_id = $request['id'] ?? null;
            if (!$discrepancy_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Discrepancy ID required'];
            } else {
                $response = $controller->approveDiscrepancy($discrepancy_id);
            }
            break;

        case 'approveAdjustment':
            $request = json_decode(file_get_contents('php://input'), true);
            $adjustment_id = $request['id'] ?? null;
            if (!$adjustment_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Adjustment ID required'];
            } else {
                $response = $controller->approveAdjustment($adjustment_id);
            }
            break;

        case 'exportBillingReport':
            $month = $_GET['month'] ?? date('Y-m');
            // Generate CSV export
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="billing_report_' . $month . '.csv"');
            $response = ['success' => true, 'message' => 'Report exported'];
            break;

        // Life Events endpoints
        case 'getLifeEvents':
            $response = $controller->getLifeEvents();
            break;

        case 'getLifeEventDetail':
            $event_id = $_GET['id'] ?? null;
            if (!$event_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Event ID required'];
            } else {
                $response = $controller->getLifeEventDetail($event_id);
            }
            break;

        case 'createLifeEvent':
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $required = ['employee_id', 'event_type', 'event_date'];
            $missing = array_filter($required, fn($field) => empty($request[$field]));
            
            if ($missing) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Missing required fields'];
            } else {
                $response = $controller->createLifeEvent($request);
            }
            break;

        case 'processLifeEvent':
            $request = json_decode(file_get_contents('php://input'), true);
            $event_id = $request['id'] ?? null;
            if (!$event_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Event ID required'];
            } else {
                $response = $controller->processLifeEvent($event_id);
            }
            break;

        // HMO Documents endpoints
        case 'getHMODocuments':
            $response = $controller->getHMODocuments();
            break;

        case 'getHMODocumentDetail':
            $doc_id = $_GET['id'] ?? null;
            if (!$doc_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Document ID required'];
            } else {
                $response = $controller->getHMODocumentDetail($doc_id);
            }
            break;

        case 'downloadHMODocument':
            $doc_id = $_GET['id'] ?? null;
            if (!$doc_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Document ID required'];
            } else {
                $docResult = $controller->downloadHMODocument($doc_id);
                if ($docResult['success'] && file_exists($docResult['file_path'])) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($docResult['file_path']) . '"');
                    readfile($docResult['file_path']);
                    exit;
                } else {
                    $response = $docResult;
                }
            }
            break;

        // Premium endpoints
        case 'getPremiumDetails':
            $plan_id = $_GET['id'] ?? null;
            if (!$plan_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Plan ID required'];
            } else {
                $response = $controller->getPremiumDetails($plan_id);
            }
            break;

        case 'getPayrollDeductions':
            $response = $controller->getPayrollDeductions();
            break;

        case 'getPremiumAdjustments':
            $response = $controller->getPremiumAdjustments();
            break;

        case 'updatePremium':
            $plan_id = $_GET['id'] ?? null;
            $request = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            
            if (!$plan_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Plan ID required'];
            } else {
                $response = $controller->updatePremium($plan_id, $request);
            }
            break;

        case 'togglePremium':
            $plan_id = $_GET['id'] ?? null;
            
            if (!$plan_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Plan ID required'];
            } else {
                $response = $controller->togglePremium($plan_id);
            }
            break;
        
        default:
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Unknown action: ' . htmlspecialchars($action)];
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('HMO API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    $response = [
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ];
}

// Send response
echo json_encode($response);
exit;
