<?php
/**
 * OTP Verification Page
 * Two-Factor Authentication - OTP Input
 * 
 * User is redirected here after successful password validation
 * Shows OTP input form and handles OTP verification
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
    session_start();
}

// Check if there's a pending OTP verification
if (empty($_SESSION['otp_pending_user_id'])) {
    header('Location: index.php');
    exit;
}

// Get user email from session
$user_email = $_SESSION['otp_pending_email'] ?? 'your email';
$user_id = $_SESSION['otp_pending_user_id'] ?? 0;

// Check for OTP verification errors (passed via GET)
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verify Your OTP - Hospital HR Management</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="assets/css/auth.css">
  <style>
    .otp-container {
      max-width: 500px;
      margin: 40px auto;
      padding: 30px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .otp-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .otp-header h2 {
      color: #333;
      margin: 0 0 10px 0;
      font-size: 24px;
    }

    .otp-header p {
      color: #666;
      margin: 0;
      font-size: 14px;
    }

    .otp-email-info {
      background: #e3f2fd;
      border-left: 4px solid #2196F3;
      padding: 12px;
      margin: 20px 0;
      border-radius: 4px;
      font-size: 14px;
      color: #1565c0;
    }

    .otp-form-group {
      margin-bottom: 20px;
    }

    .otp-form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
      font-size: 14px;
    }

    .otp-input-container {
      display: flex;
      gap: 8px;
      justify-content: center;
      margin: 20px 0;
    }

    .otp-input-field {
      width: 50px;
      height: 50px;
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      border: 2px solid #ddd;
      border-radius: 8px;
      transition: border-color 0.3s;
    }

    .otp-input-field:focus {
      outline: none;
      border-color: #2196F3;
      box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
    }

    .otp-single-input {
      width: 100%;
      padding: 12px;
      border: 2px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s;
    }

    .otp-single-input:focus {
      outline: none;
      border-color: #2196F3;
      box-shadow: 0 0 5px rgba(33, 150, 243, 0.3);
    }

    .otp-timer {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      padding: 12px;
      margin: 20px 0;
      border-radius: 4px;
      font-size: 13px;
      color: #856404;
      text-align: center;
    }

    .otp-timer strong {
      color: #e74c3c;
    }

    .error-message {
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 4px;
      font-size: 14px;
    }

    .success-message {
      background: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 4px;
      font-size: 14px;
    }

    .otp-actions {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .btn-verify {
      flex: 1;
      padding: 12px;
      background: #2196F3;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn-verify:hover {
      background: #1976D2;
    }

    .btn-verify:disabled {
      background: #ccc;
      cursor: not-allowed;
    }

    .btn-resend {
      flex: 1;
      padding: 12px;
      background: #fff;
      color: #2196F3;
      border: 2px solid #2196F3;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-resend:hover {
      background: #e3f2fd;
    }

    .btn-resend:disabled {
      background: #f5f5f5;
      color: #ccc;
      border-color: #ccc;
      cursor: not-allowed;
    }

    .otp-loading {
      display: none;
      text-align: center;
      padding: 20px;
    }

    .spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #2196F3;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .otp-back-link {
      text-align: center;
      margin-top: 20px;
    }

    .otp-back-link a {
      color: #2196F3;
      text-decoration: none;
      font-size: 14px;
    }

    .otp-back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="otp-container">
      <!-- OTP Header -->
      <div class="otp-header">
        <h2>Verify Your Identity</h2>
        <p>Two-Factor Authentication</p>
      </div>

      <!-- Display error if any -->
      <?php if ($error_message): ?>
        <div class="error-message">
          <i class='bx bxs-x-circle'></i>
          <?php echo $error_message; ?>
        </div>
      <?php endif; ?>

      <!-- Display success if any -->
      <?php if ($success_message): ?>
        <div class="success-message">
          <i class='bx bxs-check-circle'></i>
          <?php echo $success_message; ?>
        </div>
      <?php endif; ?>

      <!-- Email Info -->
      <div class="otp-email-info">
        <i class='bx bxs-envelope'></i>
        A 6-digit OTP has been sent to <strong><?php echo substr($user_email, 0, 2) . '****' . substr($user_email, -7); ?></strong>
      </div>

      <!-- OTP Form -->
      <form id="otpForm" method="POST" action="modules/auth/controllers/AuthController.php?action=verifyOTP" class="otp-form">
        <!-- OTP Input -->
        <div class="otp-form-group">
          <label for="otp">Enter 6-Digit OTP</label>
          <input 
            type="text" 
            id="otp"
            name="otp" 
            class="otp-single-input"
            placeholder="000000"
            maxlength="6" 
            pattern="[0-9]{6}"
            inputmode="numeric"
            required
            autocomplete="off"
          >
          <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
            Enter the 6-digit code from your email
          </small>
        </div>

        <!-- Hidden user ID -->
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

        <!-- Timer Info -->
        <div class="otp-timer">
          <i class='bx bxs-time'></i>
          <span>OTP expires in <strong id="timer">10:00</strong> minutes</span>
        </div>

        <!-- Form Actions -->
        <div class="otp-actions">
          <button type="submit" class="btn-verify" id="verifyBtn">
            <i class='bx bxs-check'></i>
            Verify OTP
          </button>
          <button type="button" class="btn-resend" id="resendBtn" onclick="resendOTP()">
            <i class='bx bxs-refresh'></i>
            Resend OTP
          </button>
        </div>

        <!-- Loading spinner (hidden by default) -->
        <div class="otp-loading" id="loading">
          <div class="spinner"></div>
          <p style="margin-top: 10px; color: #666;">Verifying OTP...</p>
        </div>
      </form>

      <!-- Back to Login -->
      <div class="otp-back-link">
        <a href="index.php">← Back to Login</a>
      </div>
    </div>
  </div>

  <!-- Simple JavaScript for OTP handling (form submission via POST) -->
  <script>
    // Timer for OTP expiry (10 minutes = 600 seconds)
    let expiryTime = 600;
    
    function updateTimer() {
      const minutes = Math.floor(expiryTime / 60);
      const seconds = expiryTime % 60;
      document.getElementById('timer').textContent = 
        minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
      
      if (expiryTime <= 0) {
        document.getElementById('verifyBtn').disabled = true;
        document.getElementById('verifyBtn').textContent = 'OTP Expired';
      } else {
        expiryTime--;
        setTimeout(updateTimer, 1000);
      }
    }
    
    // Start timer on page load
    updateTimer();
    
    // Handle OTP input - auto-focus next, only numbers
    const otpInput = document.getElementById('otp');
    otpInput.addEventListener('input', function(e) {
      // Only allow numbers
      this.value = this.value.replace(/[^0-9]/g, '');
      
      // Limit to 6 digits
      if (this.value.length > 6) {
        this.value = this.value.slice(0, 6);
      }
    });
    
    // Handle form submission
    document.getElementById('otpForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const otp = document.getElementById('otp').value.trim();
      const userId = document.querySelector('input[name="user_id"]').value;
      
      if (!otp || otp.length !== 6) {
        alert('Please enter a valid 6-digit OTP');
        return;
      }
      
      // Show loading state
      document.getElementById('loading').style.display = 'block';
      document.getElementById('verifyBtn').disabled = true;
      
      try {
        const response = await fetch('modules/auth/controllers/AuthController.php?action=verifyOTP', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            user_id: userId,
            otp: otp
          })
        });
        
        const data = await response.json();
        
        if (data.success) {
          // OTP verified successfully
          const token = (data.data && data.data.token) ? data.data.token : data.token;
          if (token) {
            localStorage.setItem('token', token);
          }
          
          // Redirect to dashboard after success
          setTimeout(() => {
            window.location.href = 'dashboard.php';
          }, 1000);
        } else {
          // OTP verification failed
          document.getElementById('loading').style.display = 'none';
          document.getElementById('verifyBtn').disabled = false;
          
          // Clear OTP input
          otpInput.value = '';
          otpInput.focus();
          
          alert(data.message || 'OTP verification failed. Please try again.');
        }
      } catch (error) {
        console.error('Error:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('verifyBtn').disabled = false;
        alert('An error occurred. Please try again.');
      }
    });
    
    // Handle resend OTP
    function resendOTP() {
      const userId = document.querySelector('input[name="user_id"]').value;
      const resendBtn = document.getElementById('resendBtn');
      
      // Disable resend button temporarily (30 second cooldown)
      resendBtn.disabled = true;
      let cooldown = 30;
      
      const cooldownInterval = setInterval(() => {
        resendBtn.textContent = `Resend in ${cooldown}s`;
        cooldown--;
        
        if (cooldown < 0) {
          clearInterval(cooldownInterval);
          resendBtn.disabled = false;
          resendBtn.innerHTML = '<i class="bx bxs-refresh"></i> Resend OTP';
        }
      }, 1000);
      
      // Send resend request
      fetch('modules/auth/controllers/AuthController.php?action=resendOTP', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          user_id: userId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('New OTP sent to your email');
          // Reset timer
          expiryTime = 600;
          updateTimer();
          // Clear input
          document.getElementById('otp').value = '';
          document.getElementById('otp').focus();
        } else {
          alert(data.message || 'Failed to resend OTP');
          clearInterval(cooldownInterval);
          resendBtn.disabled = false;
          resendBtn.innerHTML = '<i class="bx bxs-refresh"></i> Resend OTP';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        clearInterval(cooldownInterval);
        resendBtn.disabled = false;
        resendBtn.innerHTML = '<i class="bx bxs-refresh"></i> Resend OTP';
      });
    }
  </script>
</body>
</html>
