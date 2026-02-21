<?php
/**
 * Analytics Connection Test
 * Test if database connection and queries work properly
 */

session_start();

// Redirect if not authenticated
if (empty($_SESSION['token'])) {
    die('Unauthorized. Please login first.');
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/AnalyticsService.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
        }
        .test {
            margin: 15px 0;
            padding: 15px;
            border-left: 4px solid #ccc;
        }
        .test.success {
            border-left-color: #28a745;
            background: #f0fff4;
        }
        .test.error {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .test.warning {
            border-left-color: #ffc107;
            background: #fffaf0;
        }
        .test h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .test pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
        }
        .status.ok {
            background: #28a745;
            color: white;
        }
        .status.fail {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📊 Analytics Module - Connection & Data Test</h1>

    <?php
    try {
        // Test 1: Database Connection
        echo '<div class="test success">';
        echo '<h3>✓ Test 1: Database Connection <span class="status ok">PASS</span></h3>';
        
        $database = Database::getInstance();
        $conn = $database->getConnection();
        
        if ($conn) {
            echo '<p>Database connected successfully!</p>';
            echo '<pre>Connection Type: PDO MySQL</pre>';
        }
        echo '</div>';

        // Test 2: Simple Query
        echo '<div class="test">';
        echo '<h3>Test 2: Simple Query Execution</h3>';
        try {
            $sql = "SELECT COUNT(*) as count FROM employees WHERE employment_status = 'Active'";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo '<div class="test success">';
            echo '<h3>✓ Query Result <span class="status ok">PASS</span></h3>';
            echo '<p>Active Employees: <strong>' . $result['count'] . '</strong></p>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="test error">';
            echo '<h3>✗ Query Error <span class="status fail">FAIL</span></h3>';
            echo '<p>Error: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        echo '</div>';

        // Test 3: Analytics Service
        echo '<div class="test">';
        echo '<h3>Test 3: AnalyticsService Methods</h3>';
        try {
            $service = new AnalyticsService();
            
            $headcount = $service->getHeadcountSummary();
            echo '<div class="test success">';
            echo '<h3>✓ getHeadcountSummary <span class="status ok">PASS</span></h3>';
            echo '<pre>' . json_encode($headcount, JSON_PRETTY_PRINT) . '</pre>';
            echo '</div>';
            
            $departments = $service->getHeadcountByDepartment();
            echo '<div class="test success">';
            echo '<h3>✓ getHeadcountByDepartment <span class="status ok">PASS</span></h3>';
            echo '<p>Departments found: ' . count($departments) . '</p>';
            if (!empty($departments)) {
                echo '<pre>' . json_encode(array_slice($departments, 0, 3), JSON_PRETTY_PRINT) . '</pre>';
            }
            echo '</div>';
            
            $payroll = $service->getPayrollSummary(30);
            echo '<div class="test success">';
            echo '<h3>✓ getPayrollSummary <span class="status ok">PASS</span></h3>';
            echo '<pre>' . json_encode($payroll, JSON_PRETTY_PRINT) . '</pre>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="test error">';
            echo '<h3>✗ Service Error <span class="status fail">FAIL</span></h3>';
            echo '<p>Error: ' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
            echo '</div>';
        }
        echo '</div>';

        // Test 4: Table Existence Check
        echo '<div class="test">';
        echo '<h3>Test 4: Required Tables Check</h3>';
        
        $tables = [
            'employees' => 'Employee Master',
            'departments' => 'Departments',
            'employment_types' => 'Employment Types',
            'payroll_runs' => 'Payroll Runs',
            'payroll_run_employees' => 'Payroll Run Employees',
            'employee_movements' => 'Employee Movements',
            'pay_contracts' => 'Pay Contracts',
            'employee_documents' => 'Employee Documents',
            'employee_hmo_enrollments' => 'HMO Enrollments',
            'employee_salaries' => 'Employee Salaries'
        ];
        
        $tables_ok = 0;
        $tables_missing = 0;
        
        foreach ($tables as $table_name => $table_label) {
            try {
                $sql = "SELECT COUNT(*) FROM $table_name LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $tables_ok++;
                echo '<p style="color: #28a745;">✓ ' . $table_label . ' (' . $table_name . ')</p>';
            } catch (Exception $e) {
                $tables_missing++;
                echo '<p style="color: #dc3545;">✗ ' . $table_label . ' (' . $table_name . ') - MISSING</p>';
            }
        }
        
        echo '<pre>Tables OK: ' . $tables_ok . '/' . count($tables) . '</pre>';
        echo '</div>';

        // Test 5: API Endpoint
        echo '<div class="test">';
        echo '<h3>Test 5: API Endpoint Test</h3>';
        echo '<p>Try one of these endpoints:</p>';
        echo '<pre>
/modules/analytics/api.php?action=getDashboardData
/modules/analytics/api.php?action=getMetrics
/modules/analytics/api.php?action=getHeadcountAnalytics
        </pre>';
        echo '</div>';

    } catch (Exception $e) {
        echo '<div class="test error">';
        echo '<h3>✗ Critical Error <span class="status fail">FAIL</span></h3>';
        echo '<p>Error: ' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
        echo '</div>';
    }
    ?>

</div>

</body>
</html>
