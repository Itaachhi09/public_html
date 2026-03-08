<?php
require __DIR__ . '/../config/BaseConfig.php';
require __DIR__ . '/../config/Database.php';

$db = new Database();

// Check if data exists
$earningsCount = $db->query("SELECT COUNT(*) as count FROM employee_earnings");
$deductionsCount = $db->query("SELECT COUNT(*) as count FROM deductions");

echo "=== DATA POPULATION CHECK ===\n";
echo "Employee Earnings Records: " . $earningsCount[0]['count'] . "\n";
echo "Deductions Records: " . $deductionsCount[0]['count'] . "\n\n";

// Get a sample payslip to debug
$payslip = $db->query("
    SELECT p.id, p.employee_id, p.payroll_run_id, 
           e.first_name, e.last_name,
           pr.period_name
    FROM payslip_records p
    JOIN employees e ON p.employee_id = e.employee_id
    JOIN payroll_runs pr ON p.payroll_run_id = pr.id
    ORDER BY p.id DESC
    LIMIT 1
");

if ($payslip) {
    $ps = $payslip[0];
    echo "Sample Payslip:\n";
    echo "  Payslip ID: " . $ps['id'] . "\n";
    echo "  Employee ID: " . $ps['employee_id'] . "\n";
    echo "  Payroll Run ID: " . $ps['payroll_run_id'] . "\n";
    echo "  Employee: " . $ps['first_name'] . " " . $ps['last_name'] . "\n";
    echo "  Period: " . $ps['period_name'] . "\n\n";
    
    // Check earnings for this payslip
    $earnings = $db->query("
        SELECT * FROM employee_earnings 
        WHERE employee_id = ? AND payroll_run_id = ?
    ", [$ps['employee_id'], $ps['payroll_run_id']]);
    
    echo "Earnings for this payslip: " . count($earnings) . " records\n";
    if (!empty($earnings)) {
        foreach ($earnings as $e) {
            echo "  - " . $e['earning_type'] . " | " . $e['description'] . " | ₱" . number_format($e['amount'], 2) . "\n";
        }
    }
    echo "\n";
    
    // Check deductions for this payslip
    $deductions = $db->query("
        SELECT * FROM deductions 
        WHERE employee_id = ? AND payroll_run_id = ?
    ", [$ps['employee_id'], $ps['payroll_run_id']]);
    
    echo "Deductions for this payslip: " . count($deductions) . " records\n";
    if (!empty($deductions)) {
        foreach ($deductions as $d) {
            echo "  - " . $d['type'] . " | " . $d['description'] . " | ₱" . number_format($d['amount'], 2) . "\n";
        }
    }
}
?>
