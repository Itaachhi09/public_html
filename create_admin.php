<?php
/**
 * Admin Account Creator
 * Access: http://localhost/public_html/create_admin.php
 * DELETE THIS FILE AFTER CREATING YOUR ADMIN ACCOUNT
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Create Admin Account</title>
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
                background: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                font-weight: bold;
            }
            button:hover {
                background: #45a049;
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
            <h1>Create Admin Account</h1>
            <div class="warning">
                ⚠️ WARNING: Delete this file after creating your admin account!
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="lipejuje@gmail.com">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <button type="submit">Create Admin Account</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Process form submission
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/BaseModel.php';
require_once __DIR__ . '/config/Auth.php';
require_once __DIR__ . '/modules/auth/models/User.php';

try {
    $user = new User();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    
    // Validate inputs
    if (!$email || !$password || !$first_name || !$last_name) {
        throw new Exception('All fields are required');
    }
    
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters');
    }
    
    // Check if user already exists
    $existing = $user->findByEmail($email);
    if ($existing) {
        throw new Exception('Email already exists');
    }
    
    // Create admin account
    $userId = $user->registerUser([
        'email' => $email,
        'password' => $password,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => 'admin',
        'status' => 'active'
    ]);
    
    if ($userId) {
        echo '<div style="font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
        echo '<h1 style="color: #4CAF50; margin-top: 0;">✓ Success!</h1>';
        echo '<p><strong>Admin account created:</strong></p>';
        echo '<ul>';
        echo '<li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>';
        echo '<li><strong>Name:</strong> ' . htmlspecialchars($first_name . ' ' . $last_name) . '</li>';
        echo '<li><strong>Role:</strong> admin</li>';
        echo '</ul>';
        echo '<p style="color: red; font-weight: bold;">⚠️ DELETE this file (create_admin.php) after use!</p>';
        echo '<a href="index.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Go to Login</a>';
        echo '</div>';
    } else {
        throw new Exception('Failed to create admin account');
    }
    
} catch (Exception $e) {
    echo '<div style="font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24;">';
    echo '<h1 style="margin-top: 0;">✗ Error</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<a href="create_admin.php" style="color: #721c24; text-decoration: underline;">Try Again</a>';
    echo '</div>';
}
?>
