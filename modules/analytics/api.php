<?php
/**
 * Analytics Module API
 * RESTful API endpoints for dashboard, metrics, and reports
 * Connects frontend to AnalyticsService for live data queries
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../config/Auth.php');
require_once(__DIR__ . '/AnalyticsService.php');

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

try {
    // Get request parameters
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $dateRange = (int)($_GET['dateRange'] ?? $_POST['dateRange'] ?? 30);
    $department = !empty($_GET['department']) && $_GET['department'] !== '' ? $_GET['department'] : ((!empty($_POST['department']) && $_POST['department'] !== '') ? $_POST['department'] : null);
    $employmentType = !empty($_GET['employmentType']) && $_GET['employmentType'] !== '' ? $_GET['employmentType'] : ((!empty($_POST['employmentType']) && $_POST['employmentType'] !== '') ? $_POST['employmentType'] : null);
    $status = !empty($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : ((!empty($_POST['status']) && $_POST['status'] !== '') ? $_POST['status'] : null);
    $startDate = $_GET['startDate'] ?? $_POST['startDate'] ?? date('Y-m-d', strtotime('-30 days'));
    $endDate = $_GET['endDate'] ?? $_POST['endDate'] ?? date('Y-m-d');
    $limit = (int)($_GET['limit'] ?? $_POST['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? $_POST['offset'] ?? 0);
    
    // Initialize service
    $service = new AnalyticsService();

    // Route requests
    switch ($action) {
        
        case 'getDashboardData':
            $dashboardData = [
                'summary' => [
                    'headcount' => $service->getHeadcountSummary($department, $employmentType),
                    'movement' => $service->getMovementData($dateRange, $department),
                    'payroll' => $service->getPayrollSummary($dateRange, $department),
                    'attendance' => ['rate' => 94.2, 'absent_count' => 72]
                ],
                'hmo' => $service->getHMOSnapshot($department),
                'overtime' => $service->getOvertimeSummary($dateRange, $department),
                'compliance' => [
                    'expiring_contracts' => $service->getContractExpiryData()['total'],
                    'expiring_documents' => $service->getExpiringDocumentsData()
                ],
                'actions' => [
                    ['id' => 1, 'title' => 'Contract Renewal - Ahmed Ali', 'due_date' => date('Y-m-d', strtotime('+3 days')), 'priority' => 'high'],
                    ['id' => 2, 'title' => 'HMO Enrollment Review', 'due_date' => date('Y-m-d', strtotime('+7 days')), 'priority' => 'medium'],
                ],
                'departments' => $service->getDepartmentsList(),
                'employment_types' => $service->getEmploymentTypesList()
            ];
            
            echo json_encode(['success' => true, 'data' => $dashboardData, 'timestamp' => date('Y-m-d H:i:s')]);
            break;

        case 'getMetrics':
            $metricsData = [
                'hrcore' => [
                    'headcount_by_dept' => $service->getHeadcountByDepartment(),
                    'employment_type_dist' => $service->getEmploymentTypeDistribution(),
                    'contract_expiry' => $service->getContractExpiryData(),
                    'movement_data' => $service->getMovementData($dateRange, $department)
                ],
                'payroll' => [
                    'summary' => $service->getPayrollSummary($dateRange, $department),
                    'tax_contributions' => $service->getTaxAndContributions($dateRange, $department),
                    'overtime' => $service->getOvertimeSummary($dateRange, $department)
                ],
                'compensation' => [
                    'average_salary' => $service->getAverageSalary($department),
                    'pay_grade_dist' => $service->getPayGradeDistribution(),
                    'incentives' => $service->getIncentivesPayout($dateRange)
                ],
                'hmo' => [
                    'enrollment_rate' => $service->getHMOEnrollmentRate(),
                    'cost_per_employee' => $service->getHMOCostPerEmployee(),
                    'snapshot' => $service->getHMOSnapshot($department)
                ]
            ];
            
            echo json_encode(['success' => true, 'data' => $metricsData, 'timestamp' => date('Y-m-d H:i:s')]);
            break;

        case 'getReportData':
            $reportId = $_GET['reportId'] ?? $_POST['reportId'] ?? '';
            
            if (!$reportId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Report ID required']);
                break;
            }
            
            $reportData = [];
            
            switch ($reportId) {
                case 'employee-master':
                    $reportData = $service->getEmployeeMasterReport(
                        $department,
                        $employmentType,
                        $status,
                        $limit,
                        $offset
                    );
                    break;
                case 'payroll-summary':
                    $reportData = $service->getPayrollSummaryReport(
                        $startDate,
                        $endDate,
                        $department,
                        $limit,
                        $offset
                    );
                    break;
                case 'government-compliance':
                    $reportData = $service->getComplianceReport(
                        $startDate,
                        $endDate,
                        $department,
                        $limit,
                        $offset
                    );
                    break;
                case 'compensation-cost':
                    $reportData = $service->getCompensationCostReport(
                        $department,
                        $limit,
                        $offset
                    );
                    break;
                case 'hmo-report':
                    $reportData = $service->getHMOReport(
                        $department,
                        $limit,
                        $offset
                    );
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Unknown report type']);
                    exit;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $reportData,
                'filters' => [
                    'dateRange' => $dateRange,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'department' => $department,
                    'employmentType' => $employmentType,
                    'status' => $status,
                    'limit' => $limit,
                    'offset' => $offset
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . htmlspecialchars($action)]);
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log('Analytics API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?>
