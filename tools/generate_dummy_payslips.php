<?php
/**
 * Generate Dummy Payslip Data with Email
 * Creates test payroll run, employees, and payslips
 * Sends payslips via email for testing
 */

session_start();

require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/ReliableEmailService.php';
require_once __DIR__ . '/../modules/payroll/models/PayslipRecord.php';
require_once __DIR__ . '/../modules/payroll/models/PayslipEmailLog.php';

$db = new Database();
$conn = $db->connect();

$message = '';
$error = '';

try {
    // Check if test payroll run exists
    $test_payroll_name = 'TEST_PAYROLL_' . date('Ymd_His');
    $payroll_id = null;
    
    // Create test payroll run if needed
    $query = "INSERT INTO payroll_runs 
              (period_name, start_date, end_date, pay_date, status, created_by, created_at)
              VALUES (?, ?, ?, ?, 'Processed', ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        $test_payroll_name,
        date('Y-m-d', strtotime('-15 days')),
        date('Y-m-d'),
        date('Y-m-d', strtotime('+5 days')),
        $_SESSION['user_id'] ?? 1
    ]);
    
    $payroll_id = $conn->lastInsertId();
    $message .= "✓ Created test payroll: $test_payroll_name (ID: $payroll_id)\n\n";
    
    // Create test employee with your email
    $employee_query = "INSERT INTO employees 
                      (first_name, last_name, email, employee_code, department_id, position_id, status)
                      VALUES (?, ?, ?, ?, ?, ?, 'Active')
                      ON DUPLICATE KEY UPDATE email = VALUES(email)";
    $emp_stmt = $conn->prepare($employee_query);
    
    $test_employees = [
        [
            'first_name' => 'John Paul',
            'last_name' => 'Austria',
            'email' => 'johnpaulaustria321@gmail.com',
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'department_id' => 1,
            'position_id' => 1
        ],
        [
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'email' => 'johnpaulaustria321@gmail.com',
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'department_id' => 2,
            'position_id' => 2
        ]
    ];
    
    $employee_ids = [];
    foreach ($test_employees as $emp) {
        $emp_stmt->execute([
            $emp['first_name'],
            $emp['last_name'],
            $emp['email'],
            $emp['employee_code'],
            $emp['department_id'],
            $emp['position_id']
        ]);
        
        // Get the inserted employee ID
        $sel_query = "SELECT employee_id FROM employees WHERE email = ? AND last_name = ? LIMIT 1";
        $sel_stmt = $conn->prepare($sel_query);
        $sel_stmt->execute([$emp['email'], $emp['last_name']]);
        $result = $sel_stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $employee_ids[] = $result['employee_id'];
            $message .= "✓ Created/Updated test employee: {$emp['first_name']} {$emp['last_name']} (ID: {$result['employee_id']})\n";
        }
    }
    
    $message .= "\n";
    
    // Add employees to payroll run
    foreach ($employee_ids as $emp_id) {
        $pre_query = "INSERT INTO payroll_run_employees 
                      (payroll_run_id, employee_id, gross_pay, total_deductions, net_pay)
                      VALUES (?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE gross_pay = VALUES(gross_pay)";
        $pre_stmt = $conn->prepare($pre_query);
        $pre_stmt->execute([
            $payroll_id,
            $emp_id,
            45000 + rand(0, 10000),  // Random gross pay
            5000 + rand(0, 2000),     // Random deductions
            40000 + rand(0, 8000)     // Random net pay
        ]);
        
        $message .= "✓ Added employee #$emp_id to payroll run\n";
    }
    
    $message .= "\n";
    
    // Generate payslips
    $payslipRecord = new PayslipRecord();
    $payslipEmailLog = new PayslipEmailLog();
    
    $query = "SELECT pre.*, e.first_name, e.last_name, e.email
              FROM payroll_run_employees pre
              JOIN employees e ON pre.employee_id = e.employee_id
              WHERE pre.payroll_run_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$payroll_id]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $sent_count = 0;
    $failed_count = 0;
    
    foreach ($employees as $employee) {
        // Generate payslip
        $payslip_number = "PS-" . date('Ymd') . "-{$payroll_id}-{$employee['employee_id']}";
        
        $ps_query = "INSERT INTO payslip_records 
                     (payroll_run_id, employee_id, gross_pay, total_deductions, net_pay, payslip_number, generated_at, generated_by)
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        $ps_stmt = $conn->prepare($ps_query);
        $ps_stmt->execute([
            $payroll_id,
            $employee['employee_id'],
            $employee['gross_pay'],
            $employee['total_deductions'],
            $employee['net_pay'],
            $payslip_number,
            $_SESSION['user_id'] ?? 1
        ]);
        
        $payslip_id = $conn->lastInsertId();
        $message .= "✓ Generated payslip #$payslip_id: $payslip_number\n";
        
        // Send email
        $email_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .payslip-box { background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .total-row { background: #d1fae5; border: 2px solid #10b981; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Hospital HR System</h1>
            <p>Your Payslip Summary</p>
        </div>
        <div class="content">
            <p>Hello <strong>{$employee['first_name']} {$employee['last_name']}</strong>,</p>
            <p>Your payslip for <strong>{$test_payroll_name}</strong> has been generated and is ready for download.</p>
            
            <div class="payslip-box">
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                    <span><strong>Gross Pay:</strong></span>
                    <span><strong>₱ {$employee['gross_pay']}.00</strong></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                    <span>Total Deductions:</span>
                    <span>₱ {$employee['total_deductions']}.00</span>
                </div>
                
                <div class="total-row">
                    <div style="display: flex; justify-content: space-between;">
                        <span><strong>NET PAY:</strong></span>
                        <span style="font-size: 18px; color: #667eea;"><strong>₱ {$employee['net_pay']}.00</strong></span>
                    </div>
                </div>
            </div>
            
            <p>Payslip #: <strong>$payslip_number</strong></p>
            <p>Generated: <strong>{$test_payroll_name}</strong></p>
            
            <p style="color: #999; font-size: 13px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
                This is an automated test email from the Hospital HR System.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0;">© 2026 Hospital HR Management System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;

        // Send email via ReliableEmailService
        $email_service = new ReliableEmailService();
        $result = $email_service->send(
            $employee['email'],
            $employee['first_name'] . ' ' . $employee['last_name'],
            'Your Payslip - ' . $test_payroll_name,
            $email_body
        );
        
        if ($result) {
            // Log success
            $log_data = [
                'payslip_id' => $payslip_id,
                'employee_id' => $employee['employee_id'],
                'payroll_run_id' => $payroll_id,
                'email_address' => $employee['email'],
                'status' => 'sent'
            ];
            $payslipEmailLog->create($log_data);
            $sent_count++;
            $message .= "   ✓ Email sent to {$employee['email']}\n";
        } else {
            // Log failure
            $error_msg = $email_service->getLastError();
            $log_data = [
                'payslip_id' => $payslip_id,
                'employee_id' => $employee['employee_id'],
                'payroll_run_id' => $payroll_id,
                'email_address' => $employee['email'],
                'status' => 'failed',
                'error_message' => $error_msg
            ];
            $payslipEmailLog->create($log_data);
            $failed_count++;
            $message .= "   ✗ Email FAILED: {$error_msg}\n";
        }
    }
    
    $message .= "\n✓ Test completed! Payslips sent: $sent_count, Failed: $failed_count";
    
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Generate Dummy Payslip Data</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 20px; border-radius: 4px; color: #065f46; }
        .error { background: #fee; border-left: 4px solid #c00; padding: 20px; border-radius: 4px; color: #c00; }
        .log { background: #1a1a1a; color: #0f0; padding: 20px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.6; margin: 20px 0; white-space: pre-wrap; word-break: break-all; overflow-x: auto; }
        .footer { background: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eee; }
        a { text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Generate Dummy Payslip Data</h1>
            <p>Create test payslips and send emails</p>
        </div>
        
        <div class="content">
            <?php if ($error): ?>
                <div class="error">
                    <strong>❌ Error:</strong><br>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif ($message): ?>
                <div class="success">
                    <strong>✅ Success!</strong>
                </div>
                <div class="log"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <a href="<?= BASE_URL ?>dashboard.php" style="display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
