<?php
require __DIR__ . '/../config/BaseConfig.php';
require __DIR__ . '/../config/Database.php';

$db = new Database();

echo "=== PAYSLIP DATA VERIFICATION ===\n";
echo "ID | Employee | Earnings | Deductions\n";
echo str_repeat("-", 60) . "\n";

$payslips = $db->query("
    SELECT p.id, e.first_name, e.last_name, p.gross_pay,
           (SELECT COUNT(*) FROM employee_earnings WHERE employee_id=p.employee_id AND payroll_run_id=p.payroll_run_id) as earnings_count,
           (SELECT COUNT(*) FROM deductions WHERE employee_id=p.employee_id AND payroll_run_id=p.payroll_run_id) as deductions_count
    FROM payslip_records p
    JOIN employees e ON p.employee_id=e.employee_id
    ORDER BY p.id DESC
    LIMIT 20
");

foreach ($payslips as $p) {
    $earnings_status = $p['earnings_count'] > 0 ? "✓ " . $p['earnings_count'] : "✗ 0";
    $deductions_status = $p['deductions_count'] > 0 ? "✓ " . $p['deductions_count'] : "✗ 0";
    
    printf("%2d | %-20s | %-15s | %-15s\n", 
        $p['id'],
        substr($p['first_name'] . " " . $p['last_name'], 0, 20),
        $earnings_status,
        $deductions_status
    );
}

echo "\n✓ = Has data | ✗ = No data\n";
?>
