// Sync session token to localStorage so payroll/HR module API calls are authenticated
    <?php if (!empty($_SESSION['token'])): ?>
    try { localStorage.setItem('token', <?php echo json_encode($_SESSION['token']); ?>); } catch (e) {}
    <?php endif; ?>
