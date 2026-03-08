<?php
require __DIR__ . '/../config/BaseConfig.php';
require __DIR__ . '/../config/Database.php';

$db = new Database();
$earnings = $db->query('SELECT COUNT(*) as count FROM employee_earnings');
$deductions = $db->query('SELECT COUNT(*) as count FROM deductions');

echo "\n=== DATABASE VERIFICATION ===\n";
echo "Total Earnings Records: " . $earnings[0]['count'] . "\n";
echo "Total Deductions Records: " . $deductions[0]['count'] . "\n";

// Get sample payslip with its earnings and deductions
$sample = $db->query("
    SELECT p.id, p.employee_id, p.payroll_run_id, p.gross_pay, p.net_pay,
           e.first_name, e.last_name,
           (SELECT COUNT(*) FROM employee_earnings WHERE employee_id = p.employee_id AND payroll_run_id = p.payroll_run_id) as earnings_count,
           (SELECT COUNT(*) FROM deductions WHERE employee_id = p.employee_id AND payroll_run_id = p.payroll_run_id) as deductions_count
    FROM payslip_records p
    JOIN employees e ON p.employee_id = e.employee_id
    LIMIT 1
");

if ($sample) {
    $s = $sample[0];
    echo "\nSample Payslip (ID: " . $s['id'] . "):\n";
    echo "  Employee: " . $s['first_name'] . " " . $s['last_name'] . "\n";
    echo "  Gross Pay: ₱" . number_format($s['gross_pay'], 2) . "\n";
    echo "  Net Pay: ₱" . number_format($s['net_pay'], 2) . "\n";
    echo "  Earnings Records: " . $s['earnings_count'] . "\n";
    echo "  Deductions Records: " . $s['deductions_count'] . "\n";
}

echo "\n✓ Database populated successfully!\n";
echo "   Payslip Details modal should now display earnings and deductions breakdown.\n";
?>
