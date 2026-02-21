<?php
/**
 * Direct SQL Query Tester
 * Run exact queries from AnalyticsService and show raw results
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    die('Must be logged in');
}

require_once __DIR__ . '/../../config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SQL Query Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        h2 { color: #4ec9b0; margin-top: 30px; }
        .test { background: #252526; padding: 15px; margin: 15px 0; border-left: 4px solid #007acc; }
        .query { background: #1e1e1e; padding: 10px; color: #ce9178; margin: 10px 0; }
        .result { background: #1e1e1e; padding: 10px; color: #b5cea8; margin: 10px 0; border-left: 2px solid #6a9955; }
        .error { color: #f48771; }
    </style>
</head>
<body>

<h1>🔍 Direct SQL Query Test</h1>

<?php

// Test 1: Headcount
echo '<h2>1. Headcount Summary Query</h2>';
echo '<div class="test">';
$query = "SELECT COUNT(*) as total, SUM(CASE WHEN employment_status = 'Active' THEN 1 ELSE 0 END) as active FROM employees";
echo '<div class="query">' . htmlspecialchars($query) . '</div>';
try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<div class="result">Total: ' . $result['total'] . ', Active: ' . $result['active'] . '</div>';
} catch (Exception $e) {
    echo '<div class="result error">ERROR: ' . $e->getMessage() . '</div>';
}
echo '</div>';

// Test 2: Payroll Summary
echo '<h2>2. Payroll Summary Query</h2>';
echo '<div class="test">';
$query = "SELECT SUM(pre.gross_pay) as gross_total FROM payroll_run_employees pre JOIN payroll_runs pr ON pre.payroll_run_id = pr.id WHERE pr.status IN ('Draft', 'Processed', 'Closed')";
echo '<div class="query">' . htmlspecialchars($query) . '</div>';
try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<div class="result">Gross: ' . ($result['gross_total'] ?? 0) . '</div>';
} catch (Exception $e) {
    echo '<div class="result error">ERROR: ' . $e->getMessage() . '</div>';
}
echo '</div>';

// Test 3: Average Salary
echo '<h2>3. Average Salary Query</h2>';
echo '<div class="test">';
$query = "SELECT COUNT(*) as cnt FROM employee_salaries";
echo '<div class="query">Check if employee_salaries has data: ' . htmlspecialchars($query) . '</div>';
try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<div class="result">Records: ' . $result['cnt'] . '</div>';
} catch (Exception $e) {
    echo '<div class="result error">ERROR: ' . $e->getMessage() . '</div>';
}
echo '</div>';

// Test 4: HMO Enrollment
echo '<h2>4. HMO Enrollment Query</h2>';
echo '<div class="test">';
$query = "SELECT COUNT(*) as enrolled FROM employee_hmo_enrollments WHERE enrollment_status = 'active'";
echo '<div class="query">' . htmlspecialchars($query) . '</div>';
try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<div class="result">Enrolled: ' . $result['enrolled'] . '</div>';
} catch (Exception $e) {
    echo '<div class="result error">ERROR: ' . $e->getMessage() . '</div>';
}
echo '</div>';

// Test 5: Departments
echo '<h2>5. Departments Query</h2>';
echo '<div class="test">';
$query = "SELECT COUNT(*) as cnt FROM departments";
echo '<div class="query">' . htmlspecialchars($query) . '</div>';
try {
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<div class="result">Departments: ' . $result['cnt'] . '</div>';
} catch (Exception $e) {
    echo '<div class="result error">ERROR: ' . $e->getMessage() . '</div>';
}
echo '</div>';

// Test 6: Check actual AnalyticsService
echo '<h2>6. Call Actual Service Methods</h2>';
require_once __DIR__ . '/AnalyticsService.php';
$service = new AnalyticsService();

echo '<div class="test">';
echo '<strong>getHeadcountSummary():</strong><br>';
$result = $service->getHeadcountSummary();
echo '<div class="result">' . json_encode($result) . '</div>';
echo '</div>';

echo '<div class="test">';
echo '<strong>getPayrollSummary():</strong><br>';
$result = $service->getPayrollSummary();
echo '<div class="result">' . json_encode($result) . '</div>';
echo '</div>';

echo '<div class="test">';
echo '<strong>getAverageSalary():</strong><br>';
$result = $service->getAverageSalary();
echo '<div class="result">' . json_encode($result) . '</div>';
echo '</div>';

echo '<div class="test">';
echo '<strong>getHMOSnapshot():</strong><br>';
$result = $service->getHMOSnapshot();
echo '<div class="result">' . json_encode($result) . '</div>';
echo '</div>';

?>

</body>
</html>
