<?php
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
  session_start();
}

require_once __DIR__ . '/config/EmailConfig.php';
require_once __DIR__ . '/config/Database.php';

$user_id = isset($_SESSION['otp_pending_user_id']) ? (int)$_SESSION['otp_pending_user_id'] : 0;
$email = isset($_SESSION['otp_pending_email']) ? $_SESSION['otp_pending_email'] : '';

// Determine resend cooldown remaining (server-side) so JS can start accurate timer
$resend_delay = EmailConfig::$otp_resend_delay ?? 60;
$seconds_left = 0;
if ($user_id) {
  try {
    $db = new Database();
    $db->connect();
    $conn = $db->getConnection();
    $stmt = $conn->prepare('SELECT MAX(created_at) AS last_created FROM login_otp WHERE user_id = :user_id');
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['last_created']) {
      $last = strtotime($row['last_created']);
      $elapsed = time() - $last;
      $seconds_left = max(0, $resend_delay - $elapsed);
    }
  } catch (Exception $e) {
    // If DB check fails, default to 0 (allow resend)
    $seconds_left = 0;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Verify OTP</title>
  <link rel="stylesheet" href="assets/css/auth.css">
  <style>
    .otp-container { max-width:420px; margin:4rem auto; }
    .otp-card { padding:2rem; border-radius:8px; background:#fff; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
    .small { font-size:0.9rem; color:#555; }
    .message { margin-top:1rem; }
  </style>
</head>
<body>
  <div class="otp-container">
    <div class="otp-card">
      <h2>Enter OTP</h2>
      <?php if (!$user_id): ?>
        <p class="small">No pending OTP verification found. Please <a href="index.php">log in</a> again.</p>
      <?php else: ?>
        <p class="small">An OTP was sent to <strong><?php echo htmlspecialchars($email); ?></strong>. Please enter it below.</p>

        <div>
          <label for="otp">One-Time Password</label>
          <input id="otp" name="otp" type="text" maxlength="6" placeholder="123456" style="width:100%; padding:0.5rem; margin-top:0.25rem; font-size:1.1rem;">
        </div>

        <div style="display:flex; gap:8px; margin-top:1rem;">
          <button id="verifyBtn" style="flex:1; padding:0.6rem;">Verify</button>
          <button id="resendBtn" style="padding:0.6rem;">Resend</button>
        </div>

        <div id="msg" class="message small"></div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    const userId = <?php echo json_encode($user_id); ?>;
    const resendDelay = <?php echo json_encode((int)$resend_delay); ?>;
    let secondsLeft = <?php echo json_encode((int)$seconds_left); ?>;

    function showMessage(text, isError = false) {
      const el = document.getElementById('msg');
      el.textContent = text;
      el.style.color = isError ? '#a94442' : '#064e3b';
    }

    function updateResendUI() {
      const resendBtn = document.getElementById('resendBtn');
      const verifyBtn = document.getElementById('verifyBtn');
      const otpInput = document.getElementById('otp');
      const msgEl = document.getElementById('msg');

      if (!resendBtn) return;

      if (secondsLeft > 0) {
        resendBtn.disabled = true;
        resendBtn.textContent = `Resend (${secondsLeft}s)`;
      } else {
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend';
      }

      // Basic accessibility: focus OTP input when available
      if (otpInput && document.activeElement !== otpInput) {
        otpInput.focus();
      }
    }

    // Start countdown if needed
    if (secondsLeft > 0) {
      updateResendUI();
      const countdown = setInterval(() => {
        secondsLeft -= 1;
        if (secondsLeft <= 0) {
          clearInterval(countdown);
          secondsLeft = 0;
        }
        updateResendUI();
      }, 1000);
    }

    document.getElementById('verifyBtn')?.addEventListener('click', async function (e) {
      e.preventDefault();
      const otp = document.getElementById('otp').value.trim();
      if (!/^[0-9]{6}$/.test(otp)) {
        showMessage('Please enter a 6-digit OTP', true);
        return;
      }

      showMessage('Verifying...');

      try {
        const res = await fetch('modules/auth/controllers/AuthController.php?action=verifyOTP', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ user_id: userId, otp: otp })
        });

        const data = await res.json();

        if (data.success) {
          const token = (data.data && data.data.token) ? data.data.token : data.token;
          if (token) localStorage.setItem('token', token);
          showMessage('OTP verified — redirecting...');
          setTimeout(() => window.location.href = 'dashboard.php', 700);
        } else {
          showMessage(data.message || 'Verification failed', true);
        }
      } catch (err) {
        showMessage('Server error. Try again.', true);
      }
    });

    document.getElementById('resendBtn')?.addEventListener('click', async function (e) {
      e.preventDefault();
      if (secondsLeft > 0) {
        showMessage(`Please wait ${secondsLeft}s before resending`, true);
        return;
      }
      showMessage('Requesting new OTP...');
      try {
        const res = await fetch('modules/auth/controllers/AuthController.php?action=resendOTP', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ user_id: userId })
        });

        const data = await res.json();
        if (data.success) {
          showMessage(data.message || 'New OTP sent');
          // restart client-side cooldown
          secondsLeft = resendDelay;
          updateResendUI();
          const countdown2 = setInterval(() => {
            secondsLeft -= 1;
            if (secondsLeft <= 0) {
              clearInterval(countdown2);
              secondsLeft = 0;
            }
            updateResendUI();
          }, 1000);
        } else {
          showMessage(data.message || 'Unable to resend OTP', true);
        }
      } catch (err) {
        showMessage('Server error. Try again.', true);
      }
    });
  </script>
</body>
</html>
