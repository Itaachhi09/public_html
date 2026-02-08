<?php
/**
 * Dashboard Statistics API
 * Returns real-time dashboard metrics from database
 */

// Suppress all output except JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Set proper headers FIRST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Catch any errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $errstr
    ]);
    exit;
});

// Clear output buffer
ob_end_clean();

// Include database
require_once __DIR__ . '/config/Database.php';

try {
    $dbInstance = new Database();
    $db = $dbInstance->connect();

    $action = isset($_GET['action']) ? $_GET['action'] : 'getStats';

    switch ($action) {
        case 'getStats':
            getStats($db);
            break;
        
        case 'getEmployeeStats':
            getEmployeeStats($db);
            break;
        
        case 'getDepartmentStats':
            getDepartmentStats($db);
            break;
        
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Unknown action'
            ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage()
    ]);
}

/**
 * Get main dashboard statistics
 */
function getStats($db) {
    try {
        // Get today's date for comparisons
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        $thisYear = date('Y');
        $lastYear = $thisYear - 1;
        $lastMonth = date('Y-m', strtotime('-1 month'));

        // 1. Total Active Employees
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM employees WHERE employment_status = 'Active'");
        $stmt->execute();
        $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // 2. New Hires This Month
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM employees WHERE DATE_FORMAT(date_of_joining, '%Y-%m') = ?");
        $stmt->execute([$thisMonth]);
        $newHiresThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // 3. Employees on Leave Today
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM employees WHERE employment_status = 'On Leave'");
        $stmt->execute();
        $onLeave = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // 4. Pending Approvals (compensation approvals in 'requested' or 'under_review' status)
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM compensation_approval_requests WHERE status IN ('requested', 'under_review')");
        $stmt->execute();
        $pendingApprovals = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // 5. Employee Movements Pending
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM employee_movements WHERE status = 'Pending'");
        $stmt->execute();
        $pendingMovements = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // 6. Total Departments
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM departments WHERE status = 'Active'");
        $stmt->execute();
        $totalDepartments = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // 7. Employees Hired Last Month (for comparison)
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM employees WHERE DATE_FORMAT(date_of_joining, '%Y-%m') = ?");
        $stmt->execute([$lastMonth]);
        $newHiresLastMonth = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

        // 8. Percentage change for total employees
        $stmt = $db->prepare("
            SELECT 
                (SELECT COUNT(*) FROM employees WHERE employment_status = 'Active' AND DATE_FORMAT(updated_at, '%Y-%m') = ?) as last_month_count
        ");
        $stmt->execute([$lastMonth]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastMonthCount = $result['last_month_count'] ?? $totalEmployees;
        
        $percentChange = $lastMonthCount > 0 
            ? round((($totalEmployees - $lastMonthCount) / $lastMonthCount * 100), 1)
            : 0;

        // Calculate employee distribution by employment status
        $stmt = $db->prepare("
            SELECT employment_status, COUNT(*) as count 
            FROM employees 
            GROUP BY employment_status
        ");
        $stmt->execute();
        $statusDistribution = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $statusDistribution[$row['employment_status']] = $row['count'];
        }

        // Get top departments by employee count
        $stmt = $db->prepare("
            SELECT d.department_name, COUNT(e.employee_id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.department_id = e.department_id AND e.employment_status = 'Active'
            WHERE d.status = 'Active'
            GROUP BY d.department_id
            ORDER BY employee_count DESC
            LIMIT 5
        ");
        $stmt->execute();
        $topDepartments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Dashboard stats retrieved successfully',
            'data' => [
                'totalEmployees' => (int)$totalEmployees,
                'percentChange' => $percentChange,
                'newHiresThisMonth' => (int)$newHiresThisMonth,
                'onLeaveToday' => (int)$onLeave,
                'pendingApprovals' => (int)$pendingApprovals,
                'pendingMovements' => (int)$pendingMovements,
                'totalDepartments' => (int)$totalDepartments,
                'statusDistribution' => $statusDistribution,
                'topDepartments' => $topDepartments
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving stats',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Get detailed employee statistics
 */
function getEmployeeStats($db) {
    try {
        // Employment type distribution
        $stmt = $db->prepare("
            SELECT et.type_name, COUNT(e.employee_id) as count
            FROM employment_types et
            LEFT JOIN employees e ON et.employment_type_id = e.employment_type_id
            GROUP BY et.employment_type_id
        ");
        $stmt->execute();
        $employmentTypeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Gender distribution
        $stmt = $db->prepare("
            SELECT gender, COUNT(*) as count
            FROM employees
            GROUP BY gender
        ");
        $stmt->execute();
        $genderStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Location distribution
        $stmt = $db->prepare("
            SELECT l.location_name, COUNT(e.employee_id) as count
            FROM locations l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            GROUP BY l.location_id
        ");
        $stmt->execute();
        $locationStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'employmentType' => $employmentTypeStats,
                'gender' => $genderStats,
                'location' => $locationStats
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving employee stats',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Get department-level statistics
 */
function getDepartmentStats($db) {
    try {
        $stmt = $db->prepare("
            SELECT 
                d.department_id,
                d.department_name,
                COUNT(e.employee_id) as total_employees,
                SUM(CASE WHEN e.employment_status = 'Active' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN e.employment_status = 'On Leave' THEN 1 ELSE 0 END) as on_leave_count
            FROM departments d
            LEFT JOIN employees e ON d.department_id = e.department_id
            WHERE d.status = 'Active'
            GROUP BY d.department_id
            ORDER BY active_count DESC
        ");
        $stmt->execute();
        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $departments
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving department stats',
            'error' => $e->getMessage()
        ]);
    }
}
?>
