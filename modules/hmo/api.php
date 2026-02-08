<?php
/**
 * HMO Module API
 * RESTful API endpoints for HMO operations
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../config/Auth.php');
require_once(__DIR__ . '/controllers/HMOController.php');

header('Content-Type: application/json');

// Verify authentication
$auth = new Auth();
$token = null;

// Check for token in Authorization header first
$token = $auth->getBearerToken();

// Fall back to session token if no Bearer token
if (!$token && isset($_SESSION['token'])) {
    $token = $_SESSION['token'];
}

// Verify token
if (!$token || !$auth->verifyToken($token)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Inject token into Authorization header so BaseController can find it
if (!$auth->getBearerToken()) {
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
}

$action = $_GET['action'] ?? '';
$controller = null;
$response = ['success' => false, 'error' => 'Invalid action'];

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

        // Plan endpoints
        case 'getPlans':
            $response = $controller->getPlans();
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

        // Claims endpoints
        case 'getAllClaims':
            $response = $controller->getAllClaims();
            break;

        case 'getHighUtilizationEmployees':
            $threshold = $_GET['threshold'] ?? 80;
            $response = $controller->getHighUtilizationEmployees($threshold);
            break;

        case 'getProviderComparison':
            $response = $controller->getProviderComparison();
            break;

        case 'approveClaim':
            $request = json_decode(file_get_contents('php://input'), true);
            $claim_id = $request['id'] ?? null;
            if (!$claim_id) {
                http_response_code(400);
                $response = ['success' => false, 'error' => 'Claim ID required'];
            } else {
                $response = $controller->approveClaim($claim_id);
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
            $request = json_decode(file_get_contents('php://input'), true);
            $reconciliation_id = $request['id'] ?? null;
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
        
        default:
            http_response_code(400);
            $response = ['success' => false, 'error' => 'Unknown action: ' . htmlspecialchars($action)];
    }
} catch (Exception $e) {
    http_response_code(500);
    $response = ['success' => false, 'error' => $e->getMessage()];
}

echo json_encode($response);
