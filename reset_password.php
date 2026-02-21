<?php
/**
 * Admin Password Reset
 * Access: http://localhost/public_html/reset_password.php
 * DELETE THIS FILE AFTER RESETTING PASSWORD
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Admin Password</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 400px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .form-container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                margin-bottom: 20px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                color: #555;
            }
            input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
                font-size: 14px;
            }
            button {
                width: 100%;
                padding: 10px;
                background: #ff9800;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                font-weight: bold;
            }
            button:hover {
                background: #e68900;
            }
            .warning {
                background: #fff3cd;
                border: 1px solid #ffc107;
                color: #856404;
                padding: 10px;
                border-radius: 4px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="form-container">
            <h1>Reset Admin Password</h1>
            <div class="warning">
                ⚠️ WARNING: Delete this file after resetting password!
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="lipejuje@gmail.com">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required value="123">
                </div>
                <button type="submit">Reset Password</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Process password reset
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/BaseModel.php';
require_once __DIR__ . '/config/Auth.php';
require_once __DIR__ . '/modules/auth/models/User.php';

try {
    $user = new User();
    
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    
    // Validate inputs
    if (!$email || !$new_password) {
        throw new Exception('Email and password are required');
    }
    
    if (strlen($new_password) < 3) {
        throw new Exception('Password must be at least 3 characters');
    }
    
    // Find user by email
    $existing_user = $user->findByEmail($email);
    if (!$existing_user) {
        throw new Exception('User not found');
    }
    
    if ($existing_user['role'] !== 'admin') {
        throw new Exception('This account is not an admin account');
    }
    
    // Hash new password
    $auth = new Auth();
    $hashed_password = $auth->hashPassword($new_password);
    
    // Update password
    $updated = $user->update($existing_user['id'], [
        'password' => $hashed_password
    ]);
    
    if ($updated) {
        echo '<div style="font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        echo '<h1 style="color: #ff9800; margin-top: 0;">✓ Password Reset!</h1>';
        echo '<p><strong>Details:</strong></p>';
        echo '<ul>';
        echo '<li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>';
        echo '<li><strong>New Password:</strong> ' . htmlspecialchars($new_password) . '</li>';
        echo '</ul>';
        echo '<p style="color: red; font-weight: bold;">⚠️ DELETE this file (reset_password.php) after use!</p>';
        echo '<a href="index.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #ff9800; color: white; text-decoration: none; border-radius: 4px;">Go to Login</a>';
        echo '</div>';
    } else {
        throw new Exception('Failed to reset password');
    }
    
} catch (Exception $e) {
    echo '<div style="font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24;">';
    echo '<h1 style="margin-top: 0;">✗ Error</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<a href="reset_password.php" style="color: #721c24; text-decoration: underline;">Try Again</a>';
    echo '</div>';
}
?>
