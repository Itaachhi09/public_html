<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['token'] = 'test';

require __DIR__ . '/../config/BaseConfig.php';
require __DIR__ . '/../config/Database.php';
require __DIR__ . '/../config/Auth.php';

$db = new Database();

// Get a sample payslip ID to test
$payslip = $db->query("SELECT id FROM payslip_records ORDER BY id DESC LIMIT 1");
$testPayslipId = $payslip[0]['id'];

echo "Testing API with Payslip ID: " . $testPayslipId . "\n\n";

// Simulate the API call
$payslipData = $db->query("
    SELECT p.*, e.employee_id, e.employee_code, e.first_name, e.last_name,
           COALESCE(jt.title, 'N/A') as position, COALESCE(d.department_name, 'N/A') as department,
           pr.period_name, pr.start_date, pr.end_date, pr.pay_date, pr.id as payroll_run_id
    FROM payslip_records p
    JOIN employees e ON p.employee_id = e.employee_id
    LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    JOIN payroll_runs pr ON p.payroll_run_id = pr.id
    WHERE p.id = ?
    LIMIT 1
", [$testPayslipId]);

if ($payslipData) {
    $ps = $payslipData[0];
    echo "Payslip Retrieved:\n";
    echo "  Employee ID: " . $ps['employee_id'] . "\n";
    echo "  Payroll Run ID: " . $ps['payroll_run_id'] . "\n";
    echo "  Name: " . $ps['first_name'] . " " . $ps['last_name'] . "\n\n";
    
    // Now fetch earnings exactly like the API does
    echo "Fetching earnings...\n";
    $earnings = $db->query("
        SELECT earning_code, earning_type, description, amount
        FROM employee_earnings
        WHERE employee_id = ? AND payroll_run_id = ?
        ORDER BY earning_type
    ", [$ps['employee_id'], $ps['payroll_run_id']]);
    
    echo "Earnings result: " . (is_array($earnings) ? count($earnings) : "null") . " records\n";
    if ($earnings) {
        foreach ($earnings as $e) {
            echo "  " . json_encode($e) . "\n";
        }
    }
    echo "\n";
    
    // Now fetch deductions exactly like the API does
    echo "Fetching deductions...\n";
    $deductions = $db->query("
        SELECT type, description, amount
        FROM deductions
        WHERE employee_id = ? AND payroll_run_id = ?
        ORDER BY type
    ", [$ps['employee_id'], $ps['payroll_run_id']]);
    
    echo "Deductions result: " . (is_array($deductions) ? count($deductions) : "null") . " records\n";
    if ($deductions) {
        foreach ($deductions as $d) {
            echo "  " . json_encode($d) . "\n";
        }
    }
}
?>
