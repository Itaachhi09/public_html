<?php
require 'config/Database.php';

$db = new Database();
$conn = $db->connect();

echo "🔍 Checking current deductions data:\n\n";

// Check if those employee IDs exist
$result = $conn->query("
    SELECT DISTINCT e.employee_id, e.employee_code, e.first_name, e.last_name
    FROM employee_deductions ed
    JOIN employees e ON ed.employee_id = e.employee_id
    ORDER BY e.employee_code
");

$rows = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Employees with deductions in database:\n";
foreach ($rows as $row) {
    echo "- ID {$row['employee_id']}: {$row['employee_code']} {$row['first_name']} {$row['last_name']}\n";
}

echo "\nDetails of each deduction:\n";
$result = $conn->query("
    SELECT 
        ed.id,
        e.employee_code,
        e.first_name,
        e.last_name,
        ed.deduction_code,
        ed.amount,
        ed.is_mandatory,
        ed.status
    FROM employee_deductions ed
    JOIN employees e ON ed.employee_id = e.employee_id
    ORDER BY e.employee_code, ed.id
");

$rows = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $type = $row['is_mandatory'] ? 'Mandatory' : 'Other';
    echo "{$row['employee_code']} {$row['first_name']} {$row['last_name']}: {$row['deduction_code']} ₱{$row['amount']} ({$type}, {$row['status']})\n";
}

echo "\nSummary totals:\n";
$stat = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM employee_deductions WHERE is_mandatory = 1")->fetch(PDO::FETCH_ASSOC);
$other = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM employee_deductions WHERE is_mandatory = 0")->fetch(PDO::FETCH_ASSOC);
echo "✓ Total Statutory (Mandatory): ₱" . number_format($stat['total'], 2) . "\n";
echo "✓ Total Other (Non-mandatory): ₱" . number_format($other['total'], 2) . "\n";
echo "✓ Grand Total: ₱" . number_format($stat['total'] + $other['total'], 2) . "\n";
echo "\n✅ This data is from the DATABASE, not hardcoded!\n";
?>
