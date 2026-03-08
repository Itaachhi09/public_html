<?php
/**
 * Populate Payslip Details - Insert Sample Earnings and Deductions
 * This script inserts earnings and deductions data for existing payslips
 */

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Database.php';

$db = new Database();

// Clear old deductions for fresh population
echo "Clearing old deductions...\n";
$db->query("DELETE FROM deductions");
echo "✓ Deductions table cleared.\n\n";

// Get all payslips
$payslips = $db->query("
    SELECT DISTINCT p.id, p.employee_id, p.payroll_run_id, p.gross_pay, p.total_deductions, p.net_pay,
           e.first_name, e.last_name, e.employee_code
    FROM payslip_records p
    JOIN employees e ON p.employee_id = e.employee_id
    ORDER BY p.id DESC
");

if (empty($payslips)) {
    echo "No payslips found to populate.\n";
    exit;
}

$inserted_earnings = 0;
$inserted_deductions = 0;

foreach ($payslips as $payslip) {
    $employee_id = $payslip['employee_id'];
    $payroll_run_id = $payslip['payroll_run_id'];
    $gross_pay = $payslip['gross_pay'];
    $total_deductions = $payslip['total_deductions'];
    
    echo "\n--- Processing Payslip ID: {$payslip['id']} ({$payslip['first_name']} {$payslip['last_name']}) ---\n";
    
    // Check if earnings already exist
    $existing_earnings = $db->query(
        "SELECT COUNT(*) as count FROM employee_earnings WHERE employee_id = ? AND payroll_run_id = ?",
        [$employee_id, $payroll_run_id]
    );
    
    if ($existing_earnings[0]['count'] == 0) {
        // Calculate earnings breakdown (assuming 60% basic, 20% allowances, 20% other)
        $basic_salary = $gross_pay * 0.60;
        $rice_allowance = $gross_pay * 0.08;
        $transport_allowance = $gross_pay * 0.07;
        $overtime_pay = $gross_pay * 0.25;
        
        // Insert Basic Salary
        $db->query(
            "INSERT INTO employee_earnings (employee_id, payroll_run_id, earning_type, earning_code, description, amount, is_taxable, earning_date, status, approved_by, approved_at, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'regular', 'BASIC', 'Basic Monthly Salary', $basic_salary, 1, date('Y-m-d'), 'approved', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_earnings++;
        echo "✓ Inserted Basic Salary: ₱" . number_format($basic_salary, 2) . "\n";
        
        // Insert Rice Allowance
        $db->query(
            "INSERT INTO employee_earnings (employee_id, payroll_run_id, earning_type, earning_code, description, amount, is_taxable, earning_date, status, approved_by, approved_at, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'allowance', 'RICE', 'Rice Allowance', $rice_allowance, 0, date('Y-m-d'), 'approved', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_earnings++;
        echo "✓ Inserted Rice Allowance: ₱" . number_format($rice_allowance, 2) . "\n";
        
        // Insert Transportation Allowance
        $db->query(
            "INSERT INTO employee_earnings (employee_id, payroll_run_id, earning_type, earning_code, description, amount, is_taxable, earning_date, status, approved_by, approved_at, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'allowance', 'TRANS', 'Transportation Allowance', $transport_allowance, 0, date('Y-m-d'), 'approved', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_earnings++;
        echo "✓ Inserted Transportation Allowance: ₱" . number_format($transport_allowance, 2) . "\n";
        
        // Insert Overtime Pay
        $db->query(
            "INSERT INTO employee_earnings (employee_id, payroll_run_id, earning_type, earning_code, description, amount, is_taxable, earning_date, status, approved_by, approved_at, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'overtime', 'OT_PAY', 'Overtime Compensation', $overtime_pay, 1, date('Y-m-d'), 'approved', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_earnings++;
        echo "✓ Inserted Overtime Pay: ₱" . number_format($overtime_pay, 2) . "\n";
    } else {
        echo "⊘ Earnings already exist, skipping...\n";
    }
    
    // Check if deductions already exist
    $existing_deductions = $db->query(
        "SELECT COUNT(*) as count FROM deductions WHERE employee_id = ? AND payroll_run_id = ?",
        [$employee_id, $payroll_run_id]
    );
    
    if ($existing_deductions[0]['count'] == 0) {
        // Calculate deductions breakdown (approximately 15% of gross pay)
        $deduction_total = $gross_pay * 0.15;
        $sss = $deduction_total * 0.30;
        $philhealth = $deduction_total * 0.20;
        $pagibig = $deduction_total * 0.20;
        $tax = $deduction_total * 0.30;
        
        // Insert SSS Contribution
        $db->query(
            "INSERT INTO deductions (employee_id, payroll_run_id, type, amount, description, created_by, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'SSS', $sss, 'SSS Contribution', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_deductions++;
        echo "✓ Inserted SSS Contribution: ₱" . number_format($sss, 2) . "\n";
        
        // Insert PhilHealth Contribution
        $db->query(
            "INSERT INTO deductions (employee_id, payroll_run_id, type, amount, description, created_by, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'PhilHealth', $philhealth, 'PhilHealth Contribution', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_deductions++;
        echo "✓ Inserted PhilHealth Contribution: ₱" . number_format($philhealth, 2) . "\n";
        
        // Insert Pag-IBIG Contribution
        $db->query(
            "INSERT INTO deductions (employee_id, payroll_run_id, type, amount, description, created_by, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'Pag-IBIG', $pagibig, 'Pag-IBIG Contribution', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_deductions++;
        echo "✓ Inserted Pag-IBIG Contribution: ₱" . number_format($pagibig, 2) . "\n";
        
        // Insert Withholding Tax
        $db->query(
            "INSERT INTO deductions (employee_id, payroll_run_id, type, amount, description, created_by, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$employee_id, $payroll_run_id, 'Tax', $tax, 'Withholding Tax', 1, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
        $inserted_deductions++;
        echo "✓ Inserted Withholding Tax: ₱" . number_format($tax, 2) . "\n";
    } else {
        echo "⊘ Deductions already exist, skipping...\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "Total Earnings Records Inserted: $inserted_earnings\n";
echo "Total Deduction Records Inserted: $inserted_deductions\n";
echo "\nSample data population complete!\n";
echo "The Payslip Details modal should now display earnings and deductions breakdown.\n";
?>
