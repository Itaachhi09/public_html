<?php
/**
 * Populate Missing Sample Data
 * Add salary and document data so analytics has valid information to display
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

echo '<pre>';
echo "=== Populating Sample Data ===\n\n";

try {
    // Check if employee_salaries has data
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM employee_salaries");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $salaryCount = $result['cnt'];
    
    echo "1. Employee Salaries: Found $salaryCount records\n";
    
    if ($salaryCount == 0) {
        echo "   Adding salary data for 10 employees...\n";
        $sql = "INSERT INTO employee_salaries (employee_id, salary_type, basic_rate, effective_date, payroll_eligible) VALUES";
        $values = [];
        for ($i = 1; $i <= 10; $i++) {
            $salary = 50000 + ($i * 5000);
            $values[] = "($i, 'Monthly', $salary, '2026-01-01', 1)";
        }
        $sql .= implode(',', $values);
        $conn->exec($sql);
        echo "   ✓ Added " . count($values) . " salary records\n";
    }
    
    // Check employee_documents
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM employee_documents");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $docCount = $result['cnt'];
    
    echo "2. Employee Documents: Found $docCount records\n";
    
    if ($docCount == 0) {
        echo "   Adding sample document data...\n";
        $sql = "INSERT INTO employee_documents (employee_id, document_type, document_name, expiry_date) VALUES";
        $types = ['Passport', 'License', 'Visa', 'Medical Certificate'];
        $values = [];
        for ($i = 1; $i <= 10; $i++) {
            $type = $types[($i - 1) % count($types)];
            $expiry = date('Y-m-d', strtotime('+' . (60 + $i * 10) . ' days'));
            $values[] = "($i, '$type', 'Sample $type', '$expiry')";
        }
        $sql .= implode(',', $values);
        $conn->exec($sql);
        echo "   ✓ Added " . count($values) . " document records\n";
    }
    
    // Check if we need more payroll data
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM payroll_run_employees");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $payrollCount = $result['cnt'];
    
    echo "3. Payroll Run Employees: Found $payrollCount records\n";
    
    if ($payrollCount < 10) {
        echo "   Enhancing payroll data...\n";
        $stmt = $conn->prepare("SELECT id FROM payroll_runs LIMIT 1");
        $stmt->execute();
        $run = $stmt->fetch(PDO::FETCH_ASSOC);
        $runId = $run['id'];
        
        // Get current count and add more
        for ($eid = 3; $eid <= 10; $eid++) {
            $stmt = $conn->prepare("SELECT * FROM payroll_run_employees WHERE payroll_run_id = ? AND employee_id = ?");
            $stmt->execute([$runId, $eid]);
            if (!$stmt->fetch()) {
                $basic = 50000 + ($eid * 5000);
                $gross = $basic * 1.1;
                $deductions = $basic * 0.13;
                $net = $basic - $deductions;
                
                $insert = $conn->prepare("INSERT INTO payroll_run_employees (payroll_run_id, employee_id, basic_pay, gross_pay, total_deductions, net_pay, payslip_number, status)
                                         VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
                $insert->execute([$runId, $eid, $basic, $gross, $deductions, $net, "PS-2026-02-00$eid"]);
            }
        }
        echo "   ✓ Payroll data synchronized\n";
    }
    
    echo "\n=== Sample Data Population Complete ===\n";
    echo "✓ Analytics should now display real data\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo '</pre>';
?>
<br><br>
<button onclick="location.href='/modules/analytics/'">← Back to Analytics Dashboard</button>
<button onclick="location.href='/modules/analytics/check_database.php'">View Database Status</button>
