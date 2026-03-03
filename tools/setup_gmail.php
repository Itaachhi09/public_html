<?php
/**
 * Gmail App Password Setup Wizard
 * Easy configuration of email credentials
 */

session_start();
require_once __DIR__ . '/../config/BaseConfig.php';

$config_file = __DIR__ . '/../config/EmailConfig.php';
$message = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_password = $_POST['app_password'] ?? '';
    
    if (empty($app_password)) {
        $message = 'App password cannot be empty';
    } elseif (strlen($app_password) < 10) {
        $message = 'App password seems too short (should be ~16 characters)';
    } else {
        // Update EmailConfig.php with new password
        $config_content = file_get_contents($config_file);
        
        // Replace the password line
        $old_line = "public static \$smtp_password = 'YOUR_APP_PASSWORD_HERE';";
        $new_line = "public static \$smtp_password = '" . addslashes($app_password) . "';";
        
        $config_content = str_replace($old_line, $new_line, $config_content);
        
        if (file_put_contents($config_file, $config_content)) {
            $success = true;
            $message = '✓ App password updated successfully!';
        } else {
            $message = 'Error writing to config file. Make sure the file is writable.';
        }
    }
}

// Current password status
$config_content = file_get_contents($config_file);
$is_configured = !strpos($config_content, "YOUR_APP_PASSWORD_HERE");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gmail App Password Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 700px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .content { padding: 40px 30px; }
        .steps { margin: 30px 0; }
        .step { margin: 20px 0; }
        .step-num { display: inline-block; background: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; text-align: center; line-height: 30px; font-weight: bold; margin-right: 10px; }
        .step p { display: inline-block; margin: 0; }
        .step-content { margin-left: 40px; margin-top: 10px; font-size: 14px; line-height: 1.6; color: #666; }
        .code-box { background: #1a1a1a; color: #0f0; padding: 12px; border-radius: 4px; font-family: monospace; font-size: 12px; inline-block: block; overflow-x: auto; margin: 10px 0; }
        .form-group { margin: 20px 0; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        input[type="password"], input[type="text"] { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 4px; font-size: 14px; }
        input[type="password"]:focus, input[type="text"]:focus { outline: none; border-color: #667eea; }
        .btn { display: inline-block; background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn:hover { background: #5568d3; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; border-radius: 4px; color: #065f46; margin: 20px 0; }
        .error { background: #fee; border-left: 4px solid #c00; padding: 15px; border-radius: 4px; color: #c00; margin: 20px 0; }
        .status { background: #f0f4ff; border-left: 4px solid #667eea; padding: 15px; border-radius: 4px; color: #444; margin: 20px 0; }
        .link-text { color: #667eea; text-decoration: none; }
        .link-text:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Gmail Setup</h1>
            <p>Configure App Password for Email Sending</p>
        </div>
        
        <div class="content">
            <?php if ($success): ?>
                <div class="success">
                    <strong>✓ Success!</strong> App password has been saved. You can now send emails.
                    <br><br>
                    <a href="<?= BASE_URL ?>tools/test_payslip_email.php" style="color: inherit; text-decoration: underline;">Test Email Now →</a>
                </div>
            <?php elseif ($message): ?>
                <div class="error">
                    <strong>✗ Error:</strong> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($is_configured): ?>
                <div class="status">
                    <strong>✓ Status: Configured</strong><br>
                    Your Gmail app password is configured and ready to use.
                </div>
            <?php else: ?>
                <div class="steps">
                    <h3>📋 Step-by-Step Setup:</h3>
                    
                    <div class="step">
                        <div><span class="step-num">1</span><p><strong>Go to Google Account:</strong></p></div>
                        <div class="step-content">
                            Visit: <a href="https://myaccount.google.com" class="link-text" target="_blank">myaccount.google.com</a> (make sure you're logged in as johnpaulaustria321@gmail.com)
                        </div>
                    </div>
                    
                    <div class="step">
                        <div><span class="step-num">2</span><p><strong>Open Security Settings:</strong></p></div>
                        <div class="step-content">
                            Click <strong>Security</strong> in the left sidebar
                        </div>
                    </div>
                    
                    <div class="step">
                        <div><span class="step-num">3</span><p><strong>Enable 2-Step Verification (if needed):</strong></p></div>
                        <div class="step-content">
                            If you see a yellow warning, enable 2-Step Verification first
                        </div>
                    </div>
                    
                    <div class="step">
                        <div><span class="step-num">4</span><p><strong>Generate App Password:</strong></p></div>
                        <div class="step-content">
                            Search for <strong>"App passwords"</strong> in Security settings<br>
                            Select: <strong>Mail</strong> → <strong>Windows Computer</strong> → <strong>Generate</strong>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div><span class="step-num">5</span><p><strong>Copy the 16-character password:</strong></p></div>
                        <div class="step-content">
                            Google will show you a 16-character password like: <code>abcd efgh ijkl mnop</code><br>
                            Copy the entire password (ignore spaces)
                        </div>
                    </div>
                    
                    <div class="step">
                        <div><span class="step-num">6</span><p><strong>Paste it below and save:</strong></p></div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="app_password">Gmail App Password:</label>
                    <input 
                        type="password" 
                        id="app_password" 
                        name="app_password" 
                        placeholder="Paste your 16-character app password here"
                        required
                    >
                    <div style="font-size: 12px; color: #999; margin-top: 5px;">
                        Example: abcd efgh ijkl mnop (with or without spaces)
                    </div>
                </div>
                
                <button type="submit" class="btn">Save App Password</button>
            </form>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 13px; color: #999;">
                <p><strong>Email Configuration:</strong></p>
                <div style="margin: 10px 0; background: #f9f9f9; padding: 10px; border-radius: 4px;">
                    📧 From: johnpaulaustria321@gmail.com<br>
                    🔐 SMTP Host: smtp.gmail.com<br>
                    🔌 SMTP Port: 587<br>
                    🔒 Encryption: TLS
                </div>
                
                <p style="margin-top: 15px;">
                    <strong>Next steps after saving:</strong>
                </p>
                <ol style="margin: 10px 0 0 20px;">
                    <li><a href="<?= BASE_URL ?>tools/test_payslip_email.php" class="link-text">Test email sending</a></li>
                    <li><a href="<?= BASE_URL ?>tools/generate_dummy_payslips.php" class="link-text">Generate test payslips</a></li>
                    <li>Go to Dashboard → Payroll → Payslip Management → "Generate & Send via Email"</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>
