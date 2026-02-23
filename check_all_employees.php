<?php
require 'config/Database.php';

$db = new Database();
$conn = $db->connect();

echo "✓ All Employees in Database:\n\n";

$result = $conn->query("SELECT employee_id, employee_code, first_name, last_name FROM employees ORDER BY employee_code");
$rows = $result->fetchAll(PDO::FETCH_ASSOC);

foreach($rows as $r) {
    echo "ID {$r['employee_id']}: {$r['employee_code']} {$r['first_name']} {$r['last_name']}\n";
}

echo "\n✓ Total employees: " . count($rows) . "\n";

echo "\n\nChecking for 'EMP-' pattern:\n";
$emp_rows = array_filter($rows, function($r) { return strpos($r['employee_code'], 'EMP-') === 0; });
echo "Found " . count($emp_rows) . " employees with EMP- code\n";

echo "\n✓ Employees with deductions (from employee_deductions table):\n";
$dedResult = $conn->query("
    SELECT DISTINCT ed.employee_id, e.employee_code, e.first_name, e.last_name, COUNT(*) as deduction_count
    FROM employee_deductions ed
    JOIN employees e ON ed.employee_id = e.employee_id
    GROUP BY ed.employee_id, e.employee_code, e.first_name, e.last_name
    ORDER BY e.employee_code
");
$dedRows = $dedResult->fetchAll(PDO::FETCH_ASSOC);
foreach($dedRows as $r) {
    echo "ID {$r['employee_id']}: {$r['employee_code']} {$r['first_name']} {$r['last_name']} ({$r['deduction_count']} deductions)\n";
}

echo "\n\n";
?>
