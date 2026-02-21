<?php
/**
 * Analytics Data Diagnostic
 * Show exactly what AnalyticsService is returning
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    die('You must be logged in');
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/AnalyticsService.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Analytics Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .container { max-width: 1400px; margin: 0 auto; }
        h2 { color: #4ec9b0; border-bottom: 2px solid #4ec9b0; padding-bottom: 10px; margin-top: 30px; }
        .method { background: #252526; padding: 15px; margin: 10px 0; border-left: 4px solid #007acc; }
        .method h3 { color: #9cdcfe; margin: 0 0 10px 0; }
        .result { background: #1e1e1e; padding: 10px; margin: 5px 0; border-left: 2px solid #6a9955; color: #ce9178; }
        .error { border-left-color: #f48771; color: #f48771; }
        .number { color: #b5cea8; }
        .key { color: #9cdcfe; }
        .string { color: #ce9178; }
    </style>
</head>
<body>

<div class="container">
    <h1>📊 Analytics Service Diagnostic Output</h1>
    <p>Real data being returned from AnalyticsService methods</p>

    <?php
    try {
        $service = new AnalyticsService();

        // Test 1: Headcount
        echo '<h2>1. Headcount Summary</h2>';
        echo '<div class="method">';
        echo '<h3>getHeadcountSummary()</h3>';
        $data = $service->getHeadcountSummary();
        echo '<div class="result">';
        foreach ($data as $key => $val) {
            echo '<div><span class="key">' . $key . '</span>: <span class="number">' . $val . '</span></div>';
        }
        echo '</div></div>';

        // Test 2: Headcount by Department
        echo '<div class="method">';
        echo '<h3>getHeadcountByDepartment()</h3>';
        $data = $service->getHeadcountByDepartment();
        echo '<div class="result">';
        echo 'Records: <span class="number">' . count($data) . '</span><br>';
        if (count($data) > 0) {
            echo 'First 3:<pre>' . json_encode(array_slice($data, 0, 3), JSON_PRETTY_PRINT) . '</pre>';
        }
        echo '</div></div>';

        // Test 3: Payroll Summary
        echo '<h2>2. Payroll Data</h2>';
        echo '<div class="method">';
        echo '<h3>getPayrollSummary()</h3>';
        $data = $service->getPayrollSummary();
        echo '<div class="result">';
        foreach ($data as $key => $val) {
            echo '<div><span class="key">' . $key . '</span>: <span class="number">' . number_format($val) . '</span></div>';
        }
        echo '</div></div>';

        // Test 4: Average Salary
        echo '<div class="method">';
        echo '<h3>getAverageSalary()</h3>';
        $data = $service->getAverageSalary();
        echo '<div class="result">';
        foreach ($data as $key => $val) {
            echo '<div><span class="key">' . $key . '</span>: <span class="number">' . number_format($val, 2) . '</span></div>';
        }
        echo '</div></div>';

        // Test 5: HMO
        echo '<h2>3. HMO Data</h2>';
        echo '<div class="method">';
        echo '<h3>getHMOSnapshot()</h3>';
        $data = $service->getHMOSnapshot();
        echo '<div class="result">';
        foreach ($data as $key => $val) {
            echo '<div><span class="key">' . $key . '</span>: <span class="number">' . number_format($val) . '</span></div>';
        }
        echo '</div></div>';

        // Test 6: Movement
        echo '<h2>4. Movement Data</h2>';
        echo '<div class="method">';
        echo '<h3>getMovementData(30)</h3>';
        $data = $service->getMovementData(30);
        echo '<div class="result">';
        foreach ($data as $key => $val) {
            echo '<div><span class="key">' . $key . '</span>: <span class="number">' . $val . '</span></div>';
        }
        echo '</div></div>';

        // Test 7: Compliance
        echo '<h2>5. Compliance Data</h2>';
        echo '<div class="method">';
        echo '<h3>getContractExpiryData()</h3>';
        $data = $service->getContractExpiryData();
        echo '<div class="result">';
        if (is_array($data)) {
            echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
        } else {
            echo 'Result: ' . ($data ?? 'null');
        }
        echo '</div></div>';

        echo '<p style="color: #6a9955; margin-top: 30px;">✓ All methods executed successfully</p>';

    } catch (Exception $e) {
        echo '<h2 style="color: #f48771;">Error Executing Diagnostic</h2>';
        echo '<div class="method error">';
        echo '<div class="result error">' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    }
    ?>

</div>

</body>
</html>
