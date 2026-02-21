<?php
/**
 * Database Structure Check
 * Verify actual tables and data in database
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Structure Check</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #ccc; }
        .error { border-left-color: #dc3545; background: #fff5f5; }
        .success { border-left-color: #28a745; background: #f0fff4; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        .count { font-weight: bold; color: #0066cc; font-size: 18px; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 Database Structure & Data Check</h1>

    <?php
    try {
        // Check each critical table
        $tables = [
            'employees' => 'SELECT COUNT(*) as cnt FROM employees',
            'departments' => 'SELECT COUNT(*) as cnt FROM departments',
            'payroll_runs' => 'SELECT COUNT(*) as cnt FROM payroll_runs',
            'payroll_run_employees' => 'SELECT COUNT(*) as cnt FROM payroll_run_employees',
            'employee_hmo_enrollments' => 'SELECT COUNT(*) as cnt FROM employee_hmo_enrollments',
            'employee_movements' => 'SELECT COUNT(*) as cnt FROM employee_movements',
            'pay_contracts' => 'SELECT COUNT(*) as cnt FROM pay_contracts',
            'employee_documents' => 'SELECT COUNT(*) as cnt FROM employee_documents',
            'employee_payroll_profiles' => 'SELECT COUNT(*) as cnt FROM employee_payroll_profiles',
        ];

        echo '<h2>📊 Table Data Counts</h2>';
        
        foreach ($tables as $table => $query) {
            try {
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $result['cnt'] ?? 0;
                
                $class = $count > 0 ? 'success' : 'error';
                echo '<div class="section ' . $class . '">';
                echo '<strong>' . $table . ':</strong> ';
                echo '<span class="count">' . number_format($count) . '</span> records';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="section error">';
                echo '<strong>' . $table . ':</strong> ERROR - ' . $e->getMessage();
                echo '</div>';
            }
        }

        // Check payroll_runs with recent data
        echo '<h2>📅 Recent Payroll Runs</h2>';
        try {
            $stmt = $conn->prepare("
                SELECT id, start_date, end_date, status, created_at, COUNT(*) as emp_count
                FROM payroll_runs pr
                LEFT JOIN payroll_run_employees pre ON pr.id = pre.payroll_run_id
                GROUP BY pr.id
                ORDER BY pr.created_at DESC
                LIMIT 5
            ");
            $stmt->execute();
            $runs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($runs) > 0) {
                echo '<div class="section success"><strong>Found ' . count($runs) . ' payroll runs:</strong>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Period</th><th>Status</th><th>Employees</th><th>Created</th></tr>';
                foreach ($runs as $run) {
                    echo '<tr>';
                    echo '<td>' . $run['id'] . '</td>';
                    echo '<td>' . ($run['start_date'] ?? 'N/A') . ' to ' . ($run['end_date'] ?? 'N/A') . '</td>';
                    echo '<td>' . ($run['status'] ?? 'N/A') . '</td>';
                    echo '<td>' . $run['emp_count'] . '</td>';
                    echo '<td>' . ($run['created_at'] ?? 'N/A') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="section error"><strong>No payroll runs found!</strong></div>';
            }
        } catch (Exception $e) {
            echo '<div class="section error">Error checking payroll_runs: ' . $e->getMessage() . '</div>';
        }

        // Check employees
        echo '<h2>👥 Sample Employees</h2>';
        try {
            $stmt = $conn->prepare("
                SELECT e.id, e.first_name, e.last_name, e.employee_code, d.department_name, e.status
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LIMIT 5
            ");
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($employees) > 0) {
                echo '<div class="section success"><strong>Found ' . count($employees) . ' sample employees:</strong>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Name</th><th>Code</th><th>Department</th><th>Status</th></tr>';
                foreach ($employees as $emp) {
                    echo '<tr>';
                    echo '<td>' . $emp['id'] . '</td>';
                    echo '<td>' . ($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '') . '</td>';
                    echo '<td>' . ($emp['employee_code'] ?? 'N/A') . '</td>';
                    echo '<td>' . ($emp['department_name'] ?? 'N/A') . '</td>';
                    echo '<td>' . ($emp['status'] ?? 'N/A') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="section error"><strong>No employees found!</strong></div>';
            }
        } catch (Exception $e) {
            echo '<div class="section error">Error checking employees: ' . $e->getMessage() . '</div>';
        }

        // Check payroll_run_employees
        echo '<h2>💰 Sample Payroll Data</h2>';
        try {
            $stmt = $conn->prepare("
                SELECT pre.id, pre.employee_id, pre.payroll_run_id, pre.basic_pay, pre.gross_pay, pre.total_deductions, pre.net_pay
                FROM payroll_run_employees pre
                LIMIT 5
            ");
            $stmt->execute();
            $payroll = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($payroll) > 0) {
                echo '<div class="section success"><strong>Found payroll_run_employees data:</strong>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Employee ID</th><th>Run ID</th><th>Basic</th><th>Gross</th><th>Deductions</th><th>Net</th></tr>';
                foreach ($payroll as $p) {
                    echo '<tr>';
                    echo '<td>' . $p['id'] . '</td>';
                    echo '<td>' . $p['employee_id'] . '</td>';
                    echo '<td>' . $p['payroll_run_id'] . '</td>';
                    echo '<td>' . number_format($p['basic_pay'] ?? 0, 2) . '</td>';
                    echo '<td>' . number_format($p['gross_pay'] ?? 0, 2) . '</td>';
                    echo '<td>' . number_format($p['total_deductions'] ?? 0, 2) . '</td>';
                    echo '<td>' . number_format($p['net_pay'] ?? 0, 2) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="section error"><strong>No payroll_run_employees data found!</strong></div>';
            }
        } catch (Exception $e) {
            echo '<div class="section error">Error checking payroll_run_employees: ' . $e->getMessage() . '</div>';
        }

        // Check HMO
        echo '<h2>🏥 Sample HMO Data</h2>';
        try {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as cnt, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
                FROM employee_hmo_enrollments
            ");
            $stmt->execute();
            $hmo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo '<div class="section ' . ($hmo['cnt'] > 0 ? 'success' : 'error') . '">';
            echo '<strong>HMO Enrollments:</strong> ';
            echo 'Total: ' . number_format($hmo['cnt']) . ', Active: ' . number_format($hmo['active'] ?? 0);
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="section error">Error checking HMO: ' . $e->getMessage() . '</div>';
        }

    } catch (Exception $e) {
        echo '<div class="section error">';
        echo '<strong>Database Connection Error:</strong> ' . $e->getMessage();
        echo '</div>';
    }
    ?>

</div>

</body>
</html>
