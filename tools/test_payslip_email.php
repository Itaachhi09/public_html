<?php
/**
 * Test Payslip Email Script
 * Sends a test payslip email to verify email configuration
 * Usage: http://localhost/PUBLIC_HTML/tools/test_payslip_email.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load required files
require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../config/EmailConfig.php';
require_once __DIR__ . '/../config/ReliableEmailService.php';

// Test recipient - change this to your email
$test_recipient_email = 'johnpaulaustria321@gmail.com';
$test_recipient_name = 'John Paul Austria';

// Create test payslip HTML content
$test_subject = 'Test Payslip - February 2026';
$test_body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 20px; line-height: 1.5; }
        .payslip-box { background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .payslip-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .payslip-row:last-child { border-bottom: none; }
        .payslip-label { font-weight: bold; color: #666; }
        .payslip-value { text-align: right; color: #333; font-weight: bold; }
        .total-row { background: #f0f4ff; padding: 15px; border-radius: 4px; margin-top: 10px; }
        .net-pay { font-size: 18px; color: #667eea; }
        .link-btn { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
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
            <p class="greeting">Hello <strong>$test_recipient_name</strong>,</p>
            <p style="color: #666; font-size: 15px; line-height: 1.6;">
                Your payslip for <strong>February 2026 (Feb 5 - Feb 20)</strong> has been generated and is ready for review.
            </p>
            
            <div class="payslip-box">
                <div class="payslip-row">
                    <span class="payslip-label">Basic Salary:</span>
                    <span class="payslip-value">₱ 45,000.00</span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Allowances:</span>
                    <span class="payslip-value">₱ 5,000.00</span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Overtime/Bonus:</span>
                    <span class="payslip-value">₱ 2,500.00</span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label"><strong>Gross Pay:</strong></span>
                    <span class="payslip-value"><strong>₱ 52,500.00</strong></span>
                </div>
                
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                
                <div class="payslip-row">
                    <span class="payslip-label">Income Tax (BIR):</span>
                    <span class="payslip-value">₱ 3,150.00</span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">SSS Contribution:</span>
                    <span class="payslip-value">₱ 1,575.00</span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">PhilHealth:</span>
                    <span class="payslip-value">₱ 525.00</span>
                </div>
                <div class="payslip-row">
                    <span class="payslip-label">Pag-IBIG:</span>
                    <span class="payslip-value">₱ 200.00</span>
                </div>
                
                <div class="total-row">
                    <div class="payslip-row">
                        <span class="payslip-label"><strong>Total Deductions:</strong></span>
                        <span class="payslip-value"><strong>₱ 5,450.00</strong></span>
                    </div>
                </div>
                
                <div class="total-row" style="background: #d1fae5; border: 2px solid #10b981; margin-top: 15px;">
                    <div class="payslip-row">
                        <span class="payslip-label"><strong>NET PAY:</strong></span>
                        <span class="payslip-value net-pay"><strong>₱ 47,050.00</strong></span>
                    </div>
                </div>
            </div>
            
            <p style="color: #666; font-size: 14px; line-height: 1.6;">
                <strong>Payment Date:</strong> February 28, 2026<br>
                <strong>Account:</strong> ••••••••••••5678 (Metrobank)<br>
                <strong>Payslip #:</strong> PS-20260224-1-101
            </p>
            
            <p style="text-align: center;">
                <a href="https://localhost/PUBLIC_HTML/employee_portal/view_payslip.php?id=test" class="link-btn">View Full Payslip</a>
            </p>
            
            <div style="background: #fffbea; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <strong style="color: #856404;">📋 Important Notes:</strong>
                <p style="color: #856404; font-size: 13px; margin: 5px 0 0 0; line-height: 1.5;">
                    • This is a test payslip email to verify email configuration<br>
                    • For payslip inquiries, contact the Payroll Department<br>
                    • Keep this email for your records
                </p>
            </div>
            
            <p style="color: #999; font-size: 13px; margin-top: 20px;">
                If you have any questions about your payslip, please contact: payroll@healthcare.com
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0;">© 2026 Hospital HR Management System. All rights reserved.</p>
            <p style="margin: 5px 0 0 0;">This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;

// Validate email config
$config_errors = EmailConfig::validateConfig();
if (!empty($config_errors)) {
    echo '<div style="background: #fee; padding: 20px; border-radius: 4px; margin: 20px;">';
    echo '<h3 style="color: #c00; margin-top: 0;">Email Configuration Errors:</h3>';
    echo '<ul style="color: #c00;">';
    foreach ($config_errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
} else {
    echo '<div style="background: #f0f4ff; padding: 20px; border-radius: 4px; margin: 20px; border: 2px solid #667eea;">';
    echo '<h3 style="color: #667eea; margin-top: 0;">Email Configuration ✓</h3>';
    
    $config = EmailConfig::getConfig();
    echo '<p><strong>SMTP Host:</strong> ' . htmlspecialchars($config['host']) . '</p>';
    echo '<p><strong>SMTP Port:</strong> ' . htmlspecialchars($config['port']) . '</p>';
    echo '<p><strong>From Email:</strong> ' . htmlspecialchars($config['from_email']) . '</p>';
    echo '<p><strong>Send To:</strong> ' . htmlspecialchars($test_recipient_email) . '</p>';
    
    echo '<hr style="margin: 20px 0; border: none; border-top: 1px solid #ccc;">';
    
    // Attempt to send email
    $email_service = new ReliableEmailService();
    $result = $email_service->send(
        $test_recipient_email,
        $test_recipient_name,
        $test_subject,
        $test_body
    );
    
    if ($result) {
        echo '<div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; border-radius: 4px; color: #065f46;">';
        echo '<h4 style="margin-top: 0;">✓ Email Sent Successfully!</h4>';
        echo '<p>Test payslip email has been sent to: <strong>' . htmlspecialchars($test_recipient_email) . '</strong></p>';
        echo '<p>Check your inbox (and spam folder) for the test email.</p>';
        echo '</div>';
    } else {
        $error = $email_service->getLastError();
        echo '<div style="background: #fee; border-left: 4px solid #c00; padding: 15px; border-radius: 4px; color: #c00;">';
        echo '<h4 style="margin-top: 0;">✗ Email Send Failed</h4>';
        echo '<p><strong>Error:</strong> ' . htmlspecialchars($error) . '</p>';
        echo '<p>Check the following:</p>';
        echo '<ul>';
        echo '<li>Gmail App Password is correct</li>';
        echo '<li>2-Step Verification is enabled on the Gmail account</li>';
        echo '<li>SMTP credentials are properly configured</li>';
        echo '<li>Internet connection is available</li>';
        echo '</ul>';
        echo '</div>';
    }
    
    echo '</div>';
}

echo '<div style="text-align: center; margin-top: 30px; padding: 20px;">';
echo '<a href="' . BASE_URL . 'dashboard.php" style="display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px;">Back to Dashboard</a>';
echo '</div>';
?>
