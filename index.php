<?php
/**
 * Hospital HR System - Authentication Module
 * Login Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hospital HR Management - Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <!-- Logo -->
      <div class="logo-section">
        <img src="logo.png" alt="Hospital HR Management" class="logo-image">
      </div>

      <!-- Title Section -->
      <div class="title-section">
        <h1>Hospital HR Management</h1>
        <p>Professional Healthcare Administration</p>
      </div>

      <?php if (isset($_GET['expired'])): ?>
      <div style="background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 14px;">
        Your session has expired. Please log in again.
      </div>
      <?php endif; ?>

      <!-- Login Form -->
      <form id="loginForm" method="POST" action="modules/auth/controllers/AuthController.php?action=login" class="login-form">
        <!-- Username/Email Input -->
        <div class="form-group">
          <label for="email">Username or Email</label>
          <div class="input-wrapper">
            <i class='bx bxs-user'></i>
            <input 
              type="email" 
              id="email"
              name="email" 
              placeholder="Enter your username or email" 
              required
            >
          </div>
        </div>

        <!-- Password Input -->
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <i class='bx bxs-lock-alt'></i>
            <input 
              type="password" 
              id="password"
              name="password" 
              placeholder="Enter your password" 
              required
            >
            <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
              <i class='bx bxs-show'></i>
            </button>
          </div>
        </div>

        <!-- Forgot Password Link -->
        <div class="forgot-password-section">
          <a href="forgot-password.php" class="forgot-password-link">Forgot password?</a>
        </div>

        <!-- Sign In Button -->
        <button type="submit" class="btn-signin">
          <i class='bx bxs-right-arrow-alt'></i>
          Sign in
        </button>
      </form>

      <!-- Sign Up Link -->
      <div class="signup-section">
        <p>Don't have an account? <a href="register.php" class="signup-link">Create Account</a></p>
      </div>
    </div>
  </div>

  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const toggleBtn = document.querySelector('.toggle-password');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.innerHTML = "<i class='bx bxs-hide'></i>";
      } else {
        passwordInput.type = 'password';
        toggleBtn.innerHTML = "<i class='bx bxs-show'></i>";
      }
    }

    // Handle login form submission
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
      e.preventDefault();

      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value.trim();

      if (!email || !password) {
        alert('Please fill in all fields');
        return;
      }

      try {
        const response = await fetch('modules/auth/controllers/AuthController.php?action=login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            email: email,
            password: password
          })
        });

        const data = await response.json();

        if (data.success) {
          // Check if OTP verification is required (Two-Factor Authentication)
          if (data.data && data.data.otp_required === true) {
            // OTP sent, redirect to OTP verification page
            window.location.href = 'verify-otp.php';
          } else {
            // Traditional login (OTP not required) - store token and redirect
            const token = (data.data && data.data.token) ? data.data.token : data.token;
            if (token) {
              localStorage.setItem('token', token);
            }
            // Login successful, redirect to dashboard
            window.location.href = 'dashboard.php';
          }
        } else {
          // Login failed
          alert(data.message || 'Login failed. Please try again.');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      }
    });
  </script>
</body>
</html>