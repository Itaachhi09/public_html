<?php
require 'config/Database.php';

// Test: Apply invalid department filter
$_GET['department'] = 'hr';  // Not in database
$_GET['deduction_status'] = 'calculated';  // No records with this status

$department = $_GET['department'];
$deduction_status = $_GET['deduction_status'];

$db = new Database();
$conn = $db->connect();

// WITH invalid filters (should return 0 rows)
$result1 = $conn->prepare("
    SELECT COUNT(*) as cnt FROM employee_deductions ed
    JOIN employees e ON ed.employee_id = e.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    WHERE d.department_name = ? AND ed.status = ?
");
$result1->execute([$department, $deduction_status]);
$count1 = $result1->fetch(PDO::FETCH_ASSOC)['cnt'];

// WITHOUT department filter, with active status (should return data)
$result2 = $conn->prepare("
    SELECT COUNT(*) as cnt FROM employee_deductions ed
    WHERE ed.status IN ('active', 'processed')
");
$result2->execute([]);
$count2 = $result2->fetch(PDO::FETCH_ASSOC)['cnt'];

echo "Invalid filters (dept='hr', status='calculated'): $count1 records\n";
echo "Default filters (status IN active,processed): $count2 records ✓\n";
?>
