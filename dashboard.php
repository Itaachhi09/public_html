<?php
/**
 * Hospital HR System - Unified Dashboard
 * Professional two-layer navigation (top bar + collapsible sidebar)
 */

// Share session cookie across app (login is in modules/auth/controllers/)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
    session_start();
}

// Require login: redirect to login page if no session token
if (empty($_SESSION['token'])) {
    header('Location: index.php');
    exit;
}

// User from session for display
$name = isset($_SESSION['name']) ? trim($_SESSION['name']) : '';
$parts = $name ? explode(' ', $name, 2) : ['John', 'Manager'];
$user = [
    'id' => $_SESSION['user_id'] ?? 1,
    'first_name' => $parts[0] ?? 'John',
    'last_name' => $parts[1] ?? 'Manager',
    'role' => $_SESSION['role'] ?? 'Admin',
    'email' => $_SESSION['email'] ?? 'user@hospital.com',
    'avatar' => $name ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? $parts[0], 0, 1)) : 'JM'
];

$system_status = 'online';
$notifications = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Healthcare HR - Management System</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="assets/css/dashboard.css?v=3.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --primary: #1e40af;
      --primary-dark: #1e3a8a;
      --primary-light: #3b82f6;
      --success: #22c55e;
      --warning: #f59e0b;
      --danger: #ef4444;
      --light: #f9fafb;
      --border: #e5e7eb;
      --text-dark: #1f2937;
      --text-light: #6b7280;
      --text-lighter: #9ca3af;
      --bg-dark: #111827;
      --bg-darker: #0f172a;
      --shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
      --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.16);
    }

    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: var(--light);
    }

    body {
      display: flex;
      flex-direction: column;
      padding-top: 64px;
    }

    /* ===== TOP NAVIGATION BAR ===== */
    .top-navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
      z-index: 1000;
      display: flex;
      align-items: center;
      padding: 0 1.5rem;
      gap: 1.5rem;
    }

    .navbar-left {
      display: flex;
      align-items: center;
      gap: 1rem;
      min-width: 250px;
    }

    .navbar-brand-icon {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
      font-weight: bold;
    }

    .navbar-brand-text {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .navbar-brand-name {
      font-size: 16px;
      font-weight: 700;
      color: white;
      margin: 0;
    }

    .navbar-brand-subtitle {
      font-size: 11px;
      color: rgba(255, 255, 255, 0.7);
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin: 0;
    }

    .navbar-center {
      flex: 1;
      display: flex;
      justify-content: center;
    }

    .search-bar {
      width: 100%;
      max-width: 400px;
      height: 40px;
      background: rgba(255, 255, 255, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 20px;
      padding: 0 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: white;
    }

    .search-bar i {
      color: rgba(255, 255, 255, 0.6);
      font-size: 18px;
    }

    .search-bar input {
      flex: 1;
      background: none;
      border: none;
      color: white;
      font-size: 14px;
      outline: none;
    }

    .search-bar input::placeholder {
      color: rgba(255, 255, 255, 0.6);
    }

    .navbar-right {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-width: 200px;
      justify-content: flex-end;
      padding-left: 1rem;
      border-left: 1px solid rgba(255, 255, 255, 0.15);
    }

    .navbar-icon-btn {
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      transition: background 0.2s ease;
      position: relative;
    }

    .navbar-icon-btn:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .notification-badge {
      position: absolute;
      top: 8px;
      right: 8px;
      background: var(--danger);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 700;
    }

    .user-menu-container {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      cursor: pointer;
      position: relative;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.25);
      border: 2px solid rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 16px;
      transition: all 0.2s ease;
    }

    .user-menu-container:hover .user-avatar {
      background: rgba(255, 255, 255, 0.35);
      border-color: rgba(255, 255, 255, 0.6);
    }

    .user-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .user-name {
      font-size: 14px;
      font-weight: 600;
      color: white;
    }

    .user-role {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.7);
    }

    /* Dropdown Menu */
    .user-dropdown {
      display: none;
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      border: 1px solid var(--border);
      border-radius: 8px;
      box-shadow: var(--shadow-lg);
      min-width: 200px;
      margin-top: 0.5rem;
      z-index: 2000;
    }

    .user-dropdown.active {
      display: block;
    }

    .dropdown-item {
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      color: var(--text-dark);
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      transition: background 0.2s ease;
    }

    .dropdown-item:hover {
      background: var(--light);
    }

    .dropdown-item i {
      color: var(--text-light);
      min-width: 20px;
    }

    .dropdown-divider {
      height: 1px;
      background: var(--border);
      margin: 0.5rem 0;
    }

    /* ===== LEFT SIDEBAR ===== */
    .sidebar {
      position: fixed;
      left: 0;
      top: 60px;
      bottom: 0;
      width: 240px;
      background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
      box-shadow: 2px 0 8px rgba(0, 0, 0, 0.12);
      display: flex;
      flex-direction: column;
      overflow-y: auto;
      transition: width 0.3s ease;
      z-index: 999;
    }

    .sidebar.collapsed {
      width: 72px;
    }

    /* Sidebar Top Section */
    .sidebar-top {
      padding: 1rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
    }

    .sidebar-logo {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
      font-weight: bold;
      flex-shrink: 0;
    }

    .sidebar-status {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      flex: 1;
    }

    .status-dot {
      width: 8px;
      height: 8px;
      background: var(--success);
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.6; }
    }

    .status-text {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.8);
      white-space: nowrap;
      overflow: hidden;
    }

    .sidebar.collapsed .status-text {
      display: none;
    }

    .toggle-btn {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 6px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      transition: background 0.2s ease;
      flex-shrink: 0;
    }

    .toggle-btn:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    /* Sidebar Navigation */
    .sidebar-nav {
      flex: 1;
      overflow-y: auto;
      padding: 1rem 0;
      scrollbar-width: thin;
      scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
    }

    .sidebar-nav::-webkit-scrollbar {
      width: 4px;
    }

    .sidebar-nav::-webkit-scrollbar-track {
      background: transparent;
    }

    .sidebar-nav::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 2px;
    }

    .nav-section-title {
      padding: 0.75rem 1rem;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: rgba(255, 255, 255, 0.5);
      margin-top: 1.25rem;
    }

    .sidebar.collapsed .nav-section-title {
      display: none;
    }

    .nav-item {
      padding: 0.75rem 1rem;
      margin: 0.25rem 0.5rem;
      border-radius: 6px;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .nav-item i {
      font-size: 16px;
      min-width: 18px;
      flex-shrink: 0;
    }

    .nav-item:hover {
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    .nav-item.active {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border-left: 3px solid white;
      padding-left: calc(1rem - 3px);
    }

    .sidebar.collapsed .nav-item {
      padding: 0.75rem;
      margin: 0.25rem auto;
      justify-content: center;
    }

    .sidebar.collapsed .nav-item span {
      display: none;
    }

    .sidebar.collapsed .nav-item.active {
      border-left: none;
      padding-left: 0.75rem;
    }

    /* Tooltips for Collapsed Sidebar */
    .nav-item {
      position: relative;
    }

    .sidebar.collapsed .nav-item:hover::after {
      content: attr(data-tooltip);
      position: absolute;
      left: 100%;
      top: 50%;
      transform: translateY(-50%);
      margin-left: 0.5rem;
      background: var(--bg-dark);
      color: white;
      padding: 0.5rem 0.75rem;
      border-radius: 4px;
      font-size: 12px;
      white-space: nowrap;
      pointer-events: none;
      z-index: 1001;
      box-shadow: var(--shadow-lg);
    }

    /* Nav Items with Submenu */
    .nav-group {
      margin: 0;
    }

    .nav-group-header {
      padding: 0.75rem 1rem;
      margin: 0.25rem 0.5rem;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      color: rgba(255, 255, 255, 0.7);
      cursor: pointer;
      font-size: 14px;
      transition: all 0.2s ease;
    }

    .nav-group-header:hover {
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    .nav-group-header i:first-child {
      font-size: 18px;
      min-width: 20px;
      flex-shrink: 0;
    }

    .nav-group-header i:last-child {
      font-size: 16px;
      transition: transform 0.2s ease;
      margin-left: auto;
    }

    .nav-group-header.active i:last-child {
      transform: rotate(180deg);
    }

    .sidebar.collapsed .nav-group-header span {
      display: none;
    }

    .sidebar.collapsed .nav-group-header i:last-child {
      display: none;
    }

    .nav-submenu {
      display: none;
      padding-left: 0;
      background: rgba(0, 0, 0, 0.1);
    }

    .nav-submenu.active {
      display: block;
    }

    .nav-subitem {
      padding: 0.5rem 1rem 0.5rem 3rem;
      margin: 0.1rem 0.5rem;
      border-radius: 4px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: rgba(255, 255, 255, 0.6);
      text-decoration: none;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .nav-subitem:hover {
      background: rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.9);
    }

    .nav-subitem.active {
      color: white;
      background: rgba(255, 255, 255, 0.15);
      border-left: 2px solid white;
      padding-left: calc(3rem - 2px);
    }

    .sidebar.collapsed .nav-submenu {
      display: none !important;
    }

    /* Sidebar Footer */
    .sidebar-footer {
      padding: 1rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      margin-top: auto;
    }

    .sidebar-footer a {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      font-size: 14px;
      transition: all 0.2s ease;
      padding: 0.5rem;
      border-radius: 4px;
    }

    .sidebar-footer a:hover {
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    .sidebar.collapsed .sidebar-footer a span {
      display: none;
    }

    /* ===== MAIN CONTENT AREA ===== */
    .main-container {
      margin-top: 64px;
      margin-left: 240px;
      transition: margin-left 0.3s ease;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .main-container.expanded {
      margin-left: 72px;
    }

    .page-header {
      padding: 1.5rem;
      border-bottom: 1px solid var(--border);
      background: white;
    }

    .page-title {
      font-size: 28px;
      font-weight: 700;
      color: var(--text-dark);
      margin: 0 0 0.25rem 0;
    }

    .page-subtitle {
      font-size: 14px;
      color: var(--text-dark);
      margin: 0;
      opacity: 0.85;
      font-weight: 500;
    }

    .content-area {
      flex: 1;
      overflow-y: auto;
      padding: 1.5rem;
    }

    /* Card Styles */
    .card {
      background: white;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 1.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
      transition: all 0.2s ease;
    }

    .card:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .card-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .card-subtitle {
      font-size: 13px;
      color: var(--text-light);
      font-weight: 500;
      margin: 0;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .card-title {
      font-size: 16px;
      color: var(--text-dark);
      font-weight: 600;
      margin: 0;
    }

    .quick-action-item {
      text-align: center;
      cursor: pointer;
      text-decoration: none;
      color: inherit;
      padding: 1.5rem 1.25rem;
      border-radius: 8px;
      background: linear-gradient(135deg, rgba(30, 64, 175, 0.02) 0%, rgba(30, 64, 175, 0.04) 100%);
      border: 1px solid rgba(30, 64, 175, 0.1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .quick-action-item::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at center, rgba(30, 64, 175, 0.08) 0%, transparent 70%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .quick-action-item:hover {
      background: linear-gradient(135deg, rgba(30, 64, 175, 0.08) 0%, rgba(30, 64, 175, 0.12) 100%);
      border-color: rgba(30, 64, 175, 0.25);
      transform: translateY(-4px);
      box-shadow: 0 8px 16px rgba(30, 64, 175, 0.15), 0 0 1px rgba(30, 64, 175, 0.1);
    }

    .quick-action-item:hover::before {
      opacity: 1;
    }

    .quick-action-item > div:first-child {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 64px;
      height: 64px;
      background: linear-gradient(135deg, rgba(30, 64, 175, 0.05) 0%, rgba(30, 64, 175, 0.1) 100%);
      border-radius: 8px;
      margin: 0 auto 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 2px 4px rgba(30, 64, 175, 0.08);
    }

    .quick-action-item:hover > div:first-child {
      background: linear-gradient(135deg, rgba(30, 64, 175, 0.12) 0%, rgba(30, 64, 175, 0.18) 100%);
      transform: scale(1.1);
      box-shadow: 0 4px 8px rgba(30, 64, 175, 0.15);
    }

    /* Loading Indicator */
    .content-loader {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      z-index: 1500;
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: var(--shadow-lg);
    }

    .spinner {
      border: 4px solid rgba(30, 64, 175, 0.2);
      border-top: 4px solid var(--primary-light);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 768px) {
      .navbar-brand-text {
        display: none;
      }

      .navbar-center {
        display: none;
      }

      .sidebar {
        width: 72px;
      }

      .sidebar.collapsed {
        width: 72px;
      }

      .toggle-btn {
        display: none;
      }

      .main-container {
        margin-left: 72px;
      }

      .main-container.expanded {
        margin-left: 72px;
      }

      .nav-section-title {
        display: none;
      }

      .nav-item span, .nav-group-header span {
        display: none;
      }

      .user-info {
        display: none;
      }

      .search-bar {
        max-width: 200px;
      }
    }

    @media (max-width: 480px) {
      .top-navbar {
        padding: 0 1rem;
        gap: 0.75rem;
      }

      .navbar-left {
        min-width: auto;
      }

      .navbar-brand-name, .navbar-brand-subtitle {
        display: none;
      }

      .navbar-center {
        display: none;
      }

      .search-bar {
        display: none;
      }

      .navbar-right {
        min-width: auto;
        gap: 0.5rem;
      }

      .user-name, .user-role {
        display: none;
      }

      .page-header {
        padding: 1rem;
      }

      .page-title {
        font-size: 24px;
      }

      .content-area {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <script>
    // Sync session token to localStorage so payroll/HR module API calls are authenticated
    <?php if (!empty($_SESSION['token'])): ?>
    try { localStorage.setItem('token', <?php echo json_encode($_SESSION['token']); ?>); } catch (e) {}
    <?php endif; ?>
  </script>
  <!-- TOP NAVIGATION BAR -->
  <nav class="top-navbar">
    <div class="navbar-left">
      <img src="logo.png" alt="Healthcare HR Logo" class="navbar-brand-icon" style="width: 40px; height: 40px; object-fit: contain;">
      <div class="navbar-brand-text">
        <p class="navbar-brand-name">Healthcare HR</p>
        <p class="navbar-brand-subtitle">Management System</p>
      </div>
    </div>

    <div class="navbar-center">
      <div class="search-bar">
        <i class='bx bx-search'></i>
        <input type="text" placeholder="Search employees..." id="global-search">
      </div>
    </div>

    <div class="navbar-right">
      <button class="navbar-icon-btn" onclick="showNotifications(event)" style="position: relative;">
        <i class='bx bx-bell'></i>
        <span class="notification-badge" id="notif-count"><?php echo $notifications; ?></span>
      </button>

      <button class="navbar-icon-btn" onclick="showCalendar(event)">
        <i class='bx bx-calendar'></i>
      </button>

      <div class="user-menu-container" onclick="toggleUserDropdown()">
        <div class="user-avatar"><?php echo $user['avatar']; ?></div>
        <div class="user-info">
          <span class="user-name"><?php echo $user['first_name']; ?> <?php echo $user['last_name']; ?></span>
          <span class="user-role"><?php echo $user['role']; ?></span>
        </div>
        <i class='bx bx-chevron-down' style="color: white; margin-left: auto;"></i>

        <div class="user-dropdown" id="user-dropdown">
          <a href="#" class="dropdown-item" onclick="event.preventDefault(); alert('Profile page coming soon')">
            <i class='bx bx-user'></i>
            <span>My Profile</span>
          </a>
          <a href="#" class="dropdown-item" onclick="event.preventDefault(); alert('Settings coming soon')">
            <i class='bx bx-cog'></i>
            <span>Settings</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item" onclick="event.preventDefault(); logout()">
            <i class='bx bx-log-out'></i>
            <span>Sign Out</span>
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- LEFT SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
      <img src="logo.png" alt="Healthcare HR Logo" class="sidebar-logo" style="width: 40px; height: 40px; object-fit: contain;">
      <div class="sidebar-status">
        <div class="status-dot"></div>
        <span class="status-text">System Online</span>
      </div>
      <button class="toggle-btn" onclick="toggleSidebar()">
        <i class='bx bx-menu'></i>
      </button>
    </div>

    <nav class="sidebar-nav">
      <!-- Dashboard -->
      <a href="#" onclick="loadDashboard(event)" class="nav-item active" id="nav-dashboard" data-tooltip="Dashboard">
        <i class='bx bxs-dashboard'></i>
        <span>Dashboard</span>
      </a>

      <!-- HR CORE -->
      <div class="nav-section-title">SYSTEM</div>
      <div class="nav-group">
        <div class="nav-group-header active" onclick="toggleNavGroup(this)" data-tooltip="HR Core">
          <i class='bx bxs-user-detail'></i>
          <span>HR CORE</span>
          <i class='bx bx-chevron-down'></i>
        </div>
        <div class="nav-submenu active">
          <a href="#" onclick="loadHRCorePage(event, 'employees')" class="nav-subitem" data-page="employees">
            <i class='bx bx-user'></i>
            <span>Employee Directory</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'documents')" class="nav-subitem" data-page="documents">
            <i class='bx bx-file'></i>
            <span>Employee Documents</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'movements')" class="nav-subitem" data-page="movements">
            <i class='bx bx-arrow-to-right'></i>
            <span>Movements & Changes</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'departments')" class="nav-subitem" data-page="departments">
            <i class='bx bx-building'></i>
            <span>Departments</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'onboarding')" class="nav-subitem" data-page="onboarding">
            <i class='bx bx-rocket'></i>
            <span>Onboarding</span>
          </a>

          <div style="padding: 0.5rem 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 0.5rem 0;"></div>
          <span class="nav-section-title" style="margin-left: 1rem;">Master Data</span>

          <a href="#" onclick="loadHRCorePage(event, 'job_titles')" class="nav-subitem" data-page="job_titles">
            <i class='bx bx-briefcase'></i>
            <span>Job Titles</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'employment_types')" class="nav-subitem" data-page="employment_types">
            <i class='bx bx-id-card'></i>
            <span>Employment Types</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'locations')" class="nav-subitem" data-page="locations">
            <i class='bx bx-map'></i>
            <span>Locations</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'roles')" class="nav-subitem" data-page="roles">
            <i class='bx bx-shield'></i>
            <span>Roles & Permissions</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'shifts')" class="nav-subitem" data-page="shifts">
            <i class='bx bx-time'></i>
            <span>Shifts</span>
          </a>
          <a href="#" onclick="loadHRCorePage(event, 'schedules')" class="nav-subitem" data-page="schedules">
            <i class='bx bx-calendar'></i>
            <span>Schedules</span>
          </a>
        </div>
      </div>

      <!-- PAYROLL -->
      <div class="nav-section-title">OPERATIONS</div>
      <div class="nav-group">
        <div class="nav-group-header" onclick="toggleNavGroup(this)" data-tooltip="Payroll">
          <i class='bx bxs-wallet'></i>
          <span>PAYROLL</span>
          <i class='bx bx-chevron-down'></i>
        </div>
        <div class="nav-submenu">
          <a href="#" onclick="loadPayrollPage(event, 'payroll_processing')" class="nav-subitem">
            <i class='bx bx-money'></i>
            <span>Payroll Processing</span>
          </a>
          <a href="#" onclick="loadPayrollPage(event, 'salaries')" class="nav-subitem">
            <i class='bx bx-receipt'></i>
            <span>Salaries</span>
          </a>
          <a href="#" onclick="loadPayrollPage(event, 'payslips')" class="nav-subitem">
            <i class='bx bx-file-blank'></i>
            <span>Payslips</span>
          </a>
        </div>
      </div>

      <!-- ATTENDANCE -->
      <div class="nav-group">
        <div class="nav-group-header" onclick="toggleNavGroup(this)" data-tooltip="Attendance">
          <i class='bx bxs-calendar-check'></i>
          <span>ATTENDANCE</span>
          <i class='bx bx-chevron-down'></i>
        </div>
        <div class="nav-submenu">
          <a href="#" onclick="loadAttendance(event)" class="nav-subitem">
            <i class='bx bx-time-five'></i>
            <span>Time Tracking</span>
          </a>
          <a href="#" onclick="loadAttendance(event)" class="nav-subitem">
            <i class='bx bx-calendar-minus'></i>
            <span>Leaves</span>
          </a>
        </div>
      </div>

      <!-- ANALYTICS -->
      <div class="nav-section-title">INSIGHTS</div>
      <div class="nav-group">
        <div class="nav-group-header" onclick="toggleNavGroup(this)" data-tooltip="Analytics">
          <i class='bx bxs-bar-chart-alt-2'></i>
          <span>ANALYTICS</span>
          <i class='bx bx-chevron-down'></i>
        </div>
        <div class="nav-submenu">
          <a href="#" onclick="loadAnalytics(event)" class="nav-subitem">
            <i class='bx bx-chart'></i>
            <span>Reports</span>
          </a>
          <a href="#" onclick="loadAnalytics(event)" class="nav-subitem">
            <i class='bx bx-line-chart'></i>
            <span>Custom Dashboards</span>
          </a>
        </div>
      </div>
    </nav>

    <div class="sidebar-footer">
      <a href="#" onclick="event.preventDefault(); logout()">
        <i class='bx bx-log-out'></i>
        <span>Logout</span>
      </a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="main-container" id="main-container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title" id="page-title">Hospital HR Dashboard</h1>
      <p class="page-subtitle" id="page-subtitle"><?php echo date('l, F d, Y'); ?></p>
    </div>

    <!-- Content Area -->
    <main class="content-area" id="content-area">
      <!-- Default Dashboard Content -->
      <section style="padding: 0;">
        <!-- KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; padding: 1.5rem;">
          <div class="card">
            <div class="card-header">
              <div style="flex: 1;">
                <p class="card-subtitle">Total Employees</p>
                <h2 style="font-size: 32px; font-weight: 700; margin: 0.5rem 0 0 0; color: var(--primary);">450</h2>
                <p style="font-size: 12px; color: var(--success); margin: 0.5rem 0 0 0; font-weight: 500;">↑ 5% from last month</p>
              </div>
              <div style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">👥</div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div style="flex: 1;">
                <p class="card-subtitle">New Hires (This Month)</p>
                <h2 style="font-size: 32px; font-weight: 700; margin: 0.5rem 0 0 0; color: var(--primary);">12</h2>
                <p style="font-size: 12px; color: var(--text-light); margin: 0.5rem 0 0 0; font-weight: 500;">On track for Q1</p>
              </div>
              <div style="width: 56px; height: 56px; background: rgba(34, 197, 94, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">✨</div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div style="flex: 1;">
                <p class="card-subtitle">Employees On Leave</p>
                <h2 style="font-size: 32px; font-weight: 700; margin: 0.5rem 0 0 0; color: var(--warning);">23</h2>
                <p style="font-size: 12px; color: var(--text-light); margin: 0.5rem 0 0 0; font-weight: 500;">Today</p>
              </div>
              <div style="width: 56px; height: 56px; background: rgba(251, 191, 36, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">🏖️</div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <div style="flex: 1;">
                <p class="card-subtitle">Pending Approvals</p>
                <h2 style="font-size: 32px; font-weight: 700; margin: 0.5rem 0 0 0; color: var(--danger);">8</h2>
                <p style="font-size: 12px; color: var(--text-light); margin: 0.5rem 0 0 0; font-weight: 500;">Awaiting action</p>
              </div>
              <div style="width: 56px; height: 56px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">⏳</div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin: 0 1.5rem 2rem 1.5rem;">
          <div class="card-header">
            <h3 class="card-title">Quick Access</h3>
          </div>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem; padding: 1.5rem 0;">
            <a href="#" onclick="loadHRCorePage(event, 'employees')" class="quick-action-item">
              <div style="font-size: 40px; margin-bottom: 1rem;">👥</div>
              <h3 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 14px; font-weight: 600;">Employees</h3>
              <p style="margin: 0; font-size: 12px; color: var(--text-light);">Manage records</p>
            </a>
            <a href="#" onclick="loadHRCorePage(event, 'documents')" class="quick-action-item">
              <div style="font-size: 40px; margin-bottom: 1rem;">📄</div>
              <h3 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 14px; font-weight: 600;">Documents</h3>
              <p style="margin: 0; font-size: 12px; color: var(--text-light);">Track documents</p>
            </a>
            <a href="#" onclick="loadHRCorePage(event, 'schedules')" class="quick-action-item">
              <div style="font-size: 40px; margin-bottom: 1rem;">📅</div>
              <h3 style="margin: 0 0 0.5rem 0; color: var(--dark); font-size: 14px; font-weight: 600;">Schedules</h3>
              <p style="margin: 0; font-size: 12px; color: var(--text-light);">Staff planning</p>
            </a>
            <a href="#" onclick="loadHRCorePage(event, 'shifts')" class="quick-action-item">
              <div style="font-size: 40px; margin-bottom: 1rem;">⏱️</div>
              <h3 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 14px; font-weight: 600;">Shifts</h3>
              <p style="margin: 0; font-size: 12px; color: var(--text-light);">Time blocks</p>
            </a>
            <a href="#" onclick="loadHRCorePage(event, 'movements')" class="quick-action-item">
              <div style="font-size: 40px; margin-bottom: 1rem;">↔️</div>
              <h3 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 14px; font-weight: 600;">Movements</h3>
              <p style="margin: 0; font-size: 12px; color: var(--text-light);">Transfers</p>
            </a>
            <a href="#" onclick="loadHRCorePage(event, 'departments')" class="quick-action-item">
              <div style="font-size: 40px; margin-bottom: 1rem;">🏢</div>
              <h3 style="margin: 0 0 0.5rem 0; color: var(--text-dark); font-size: 14px; font-weight: 600;">Departments</h3>
              <p style="margin: 0; font-size: 12px; color: var(--text-light);">Organization</p>
            </a>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Loading Indicator -->
  <div class="content-loader" id="content-loader">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>

  </script>

  <script>
    // ===== SIDEBAR & NAVIGATION =====
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const mainContainer = document.getElementById('main-container');
      
      sidebar.classList.toggle('collapsed');
      mainContainer.classList.toggle('expanded');
    }

    function toggleNavGroup(headerElement) {
      const submenu = headerElement.nextElementSibling;
      const isActive = headerElement.classList.contains('active');
      
      // Close all other submenus
      document.querySelectorAll('.nav-group-header.active').forEach(el => {
        if (el !== headerElement) {
          el.classList.remove('active');
          el.nextElementSibling.classList.remove('active');
        }
      });
      
      headerElement.classList.toggle('active');
      submenu.classList.toggle('active');
    }

    // ===== USER MENU =====
    function toggleUserDropdown() {
      const dropdown = document.getElementById('user-dropdown');
      dropdown.classList.toggle('active');
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(event) {
        if (!event.target.closest('.user-menu-container')) {
          dropdown.classList.remove('active');
        }
      });
    }

    // ===== PAGE LOADING =====
    // Generic module loader (shared by both HR Core and Payroll)
    function loadModule(event, page, module) {
      if (event) event.preventDefault();
      
      // Remove active state from all items
      document.querySelectorAll('.nav-item, .nav-subitem').forEach(el => {
        el.classList.remove('active');
      });
      
      // Add active to current item
      if (event && event.target) {
        const clickedElement = event.target.closest('.nav-subitem, .nav-item');
        if (clickedElement) {
          clickedElement.classList.add('active');
        }
      }

      // Page titles mapping
      const pageNames = {
        // HR Core
        employees: 'Employee Directory',
        documents: 'Employee Documents',
        movements: 'Movements & Changes',
        departments: 'Departments',
        onboarding: 'Onboarding Checklist',
        job_titles: 'Job Titles',
        employment_types: 'Employment Types',
        locations: 'Locations',
        roles: 'Roles & Permissions',
        shifts: 'Shift Management',
        schedules: 'Work Schedules',
        // Payroll
        payroll_processing: 'Payroll Processing',
        salaries: 'Salaries Management',
        payslips: 'Payslips'
      };

      const pageTitle = document.getElementById('page-title');
      const pageSubtitle = document.getElementById('page-subtitle');
      const loader = document.getElementById('content-loader');
      const contentArea = document.getElementById('content-area');
      
      if (pageTitle) pageTitle.textContent = pageNames[page] || 'Page';
      if (pageSubtitle) pageSubtitle.textContent = new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      
      // Show loading state
      if (loader) loader.style.display = 'block';
      
      // Fetch module data
      const viewPath = `modules/${module}/views/${page}.php`;
      
      fetch(viewPath)
        .then(response => {
          if (!response.ok) throw new Error(`HTTP ${response.status}`);
          return response.text();
        })
        .then(html => {
          // Extract scripts BEFORE parsing
          const scriptRegex = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
          const scripts = [];
          let match;
          const execRegex = new RegExp(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi);
          while ((match = execRegex.exec(html)) !== null) {
            const scriptTag = match[0];
            const scriptContent = scriptTag.replace(/<script[^>]*>/i, '').replace(/<\/script>/i, '');
            scripts.push(scriptContent);
          }
          
          // Remove scripts from HTML before parsing
          const htmlWithoutScripts = html.replace(scriptRegex, '');
          
          // Parse HTML without scripts
          const parser = new DOMParser();
          const doc = parser.parseFromString(htmlWithoutScripts, 'text/html');
          
          // Get all styles and inject them
          const styles = doc.querySelectorAll('style');
          styles.forEach(style => {
            try {
              const newStyle = document.createElement('style');
              newStyle.setAttribute('data-module-style', 'true');
              newStyle.textContent = style.textContent;
              if (document.head) {
                document.head.appendChild(newStyle);
              }
            } catch (e) {
              console.error('Error injecting style:', e);
            }
          });
          
          // Find and inject HTML content
          const mainContent = doc.querySelector('.main-content');
          if (mainContent && contentArea) {
            try {
              contentArea.innerHTML = mainContent.innerHTML;
            } catch (e) {
              console.error('Error setting main content:', e);
              if (contentArea) contentArea.innerHTML = '<div class="card"><p>Error loading content</p></div>';
            }
          } else if (contentArea) {
            try {
              contentArea.innerHTML = doc.body.innerHTML;
            } catch (e) {
              console.error('Error setting body content:', e);
              if (contentArea) contentArea.innerHTML = '<div class="card"><p>Error loading content</p></div>';
            }
          }
          
          // Execute scripts in order
          setTimeout(() => {
            scripts.forEach((scriptContent, idx) => {
              try {
                const scriptEl = document.createElement('script');
                scriptEl.textContent = scriptContent;
                document.body.appendChild(scriptEl);
                scriptEl.parentNode.removeChild(scriptEl);
              } catch (e) {
                console.error('Error executing script:', e);
              }
            });
          }, 100);
          
          // Trigger data loading after scripts have executed
          setTimeout(() => {
            const loadFunctions = {
              employees: () => window.loadEmployees?.(),
              documents: () => window.loadDocuments?.(),
              movements: () => window.loadMovements?.(),
              departments: () => window.loadDepartments?.(),
              onboarding: () => window.loadOnboarding?.(),
              job_titles: () => window.loadJobTitles?.(),
              employment_types: () => window.loadEmploymentTypes?.(),
              locations: () => window.loadLocations?.(),
              roles: () => window.loadRoles?.(),
              shifts: () => window.loadShifts?.(),
              schedules: () => window.loadSchedules?.(),
              payroll_processing: () => window.loadPayrollRuns?.(),
              salaries: () => window.loadSalaries?.(),
              payslips: () => window.loadPayslips?.()
            };
            
            if (loadFunctions[page]) {
              try {
                loadFunctions[page]();
              } catch (e) {
                console.warn('Data load error for', page, ':', e.message);
              }
            }
          }, 200);
          
          if (document.getElementById('content-loader')) {
            document.getElementById('content-loader').style.display = 'none';
          }
        })
        .catch(error => {
          console.error('Error loading page:', error);
          const contentArea = document.getElementById('content-area');
          if (contentArea) {
            contentArea.innerHTML = '<div class="card"><p style="color: #ef4444;">Error: ' + error.message + '</p></div>';
          }
          if (document.getElementById('content-loader')) {
            document.getElementById('content-loader').style.display = 'none';
          }
        });
    }

    function loadHRCorePage(event, page) {
      loadModule(event, page, 'hr_core');
    }

    function loadPayrollPage(event, page) {
      loadModule(event, page, 'payroll');
    }

    // Load dashboard
    function loadDashboard(event) {
      event.preventDefault();
      
      // Remove active from all items
      document.querySelectorAll('.nav-item, .nav-subitem').forEach(el => {
        el.classList.remove('active');
      });
      
      // Add active to dashboard
      document.getElementById('nav-dashboard').classList.add('active');
      document.getElementById('page-title').textContent = 'Hospital HR Dashboard';
      document.getElementById('page-subtitle').textContent = new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });

      // Reload page to show default dashboard
      location.reload();
    }

    function loadAttendance(event) {
      event.preventDefault();
      document.getElementById('page-title').textContent = 'Attendance';
      document.getElementById('content-area').innerHTML = '<div class="card"><h2>Attendance Module</h2><p>Attendance module coming soon</p></div>';
    }

    function loadAnalytics(event) {
      event.preventDefault();
      document.getElementById('page-title').textContent = 'Analytics';
      document.getElementById('content-area').innerHTML = '<div class="card"><h2>Analytics Module</h2><p>Analytics module coming soon</p></div>';
    }

    // ===== GLOBAL SEARCH FUNCTIONALITY =====
    document.addEventListener('DOMContentLoaded', function() {
      const globalSearch = document.getElementById('global-search');
      if (globalSearch) {
        globalSearch.addEventListener('keyup', function(e) {
          const query = this.value.trim();
          if (query.length >= 2) {
            performGlobalSearch(query);
          }
        });
        
        globalSearch.addEventListener('focus', function() {
          this.style.background = 'rgba(255, 255, 255, 0.25)';
        });
        
        globalSearch.addEventListener('blur', function() {
          this.style.background = 'rgba(255, 255, 255, 0.15)';
        });
      }
    });

    function performGlobalSearch(query) {
      console.log('Searching for:', query);
      // Search across employees, documents, departments, etc.
      fetch(`modules/hr_core/api.php?action=search&q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log('Search results:', data.data);
            // Show search results (could be implemented with a dropdown)
          }
        })
        .catch(error => console.error('Search error:', error));
    }

    // ===== NOTIFICATION HANDLER =====
    function showNotifications(event) {
      event.preventDefault();
      event.stopPropagation();
      
      const notifBtn = event.currentTarget || event.target.closest('.navbar-icon-btn');
      
      // Guard against null notifBtn
      if (!notifBtn) {
        console.error('Notification button not found');
        return;
      }
      
      // Check if dropdown already exists
      let existingDropdown = notifBtn.querySelector('[data-notification-panel]');
      if (existingDropdown) {
        existingDropdown.remove();
        return;
      }
      
      // Create notification panel
      const notificationPanel = document.createElement('div');
      notificationPanel.setAttribute('data-notification-panel', 'true');
      notificationPanel.style.cssText = `
        position: absolute;
        right: -10px;
        top: 100%;
        margin-top: 0.75rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        width: 360px;
        max-height: 420px;
        overflow-y: auto;
        z-index: 2000;
      `;
      
      // Fetch notifications or show sample data
      fetch('modules/hr_core/api.php?action=getNotifications')
        .then(response => response.json())
        .then(data => {
          let notificationList = [];
          if (data.success && data.data && data.data.notifications) {
            notificationList = data.data.notifications;
          }
          
          // Show sample notifications if none exist
          if (notificationList.length === 0) {
            notificationList = [
              { id: 1, title: 'Welcome', message: 'Welcome to Healthcare HR System', time: 'Just now' },
              { id: 2, title: 'System Online', message: 'All systems operational', time: '5 minutes ago' },
              { id: 3, title: 'New Feature', message: 'Employee Dashboard updated', time: '1 hour ago' }
            ];
          }
          
          let html = '<div style="padding: 1rem; border-bottom: 1px solid var(--border); background: rgba(30, 64, 175, 0.02);">';
          html += '<h3 style="margin: 0; font-size: 14px; font-weight: 600; color: var(--text-dark);">Notifications</h3>';
          html += '</div>';
          
          if (notificationList.length === 0) {
            html += '<div style="padding: 2rem; text-align: center; color: var(--text-light);"><p style="margin: 0; font-size: 13px;">No notifications</p></div>';
          } else {
            notificationList.slice(0, 6).forEach(notif => {
              html += `
                <div style="padding: 1rem; border-bottom: 1px solid var(--border); cursor: pointer; transition: all 0.2s ease;" 
                     onmouseover="this.style.background='var(--light)'" 
                     onmouseout="this.style.background='white'">
                  <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <div style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%; margin-top: 0.375rem; flex-shrink: 0;"></div>
                    <div style="flex: 1;">
                      <p style="margin: 0 0 0.25rem 0; font-weight: 600; color: var(--text-dark); font-size: 13px;">${notif.title || 'Notification'}</p>
                      <p style="margin: 0; font-size: 12px; color: var(--text-light); line-height: 1.4;">${notif.message || ''}</p>
                      <p style="margin: 0.5rem 0 0 0; font-size: 11px; color: var(--text-lighter);">${notif.time || ''}</p>
                    </div>
                  </div>
                </div>
              `;
            });
          }
          
          html += '<div style="padding: 1rem; text-align: center; border-top: 1px solid var(--border);">';
          html += '<a href="#" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 600;">View All Notifications</a>';
          html += '</div>';
          
          notificationPanel.innerHTML = html;
          if (notifBtn) {
            notifBtn.style.position = 'relative';
            notifBtn.appendChild(notificationPanel);
          }
        })
        .catch(error => {
          console.error('Notification error:', error);
          
          // Show fallback sample notifications on error
          let html = '<div style="padding: 1rem; border-bottom: 1px solid var(--border); background: rgba(30, 64, 175, 0.02);">';
          html += '<h3 style="margin: 0; font-size: 14px; font-weight: 600; color: var(--text-dark);">Notifications</h3>';
          html += '</div>';
          
          const sampleNotifications = [
            { title: 'Welcome', message: 'Welcome to Healthcare HR System', time: 'Just now' },
            { title: 'System Online', message: 'All systems operational', time: '5 minutes ago' },
            { title: 'New Employee', message: '3 new employees onboarded', time: '2 hours ago' },
            { title: 'Pending Approvals', message: 'You have 5 pending approvals', time: '3 hours ago' }
          ];
          
          sampleNotifications.forEach(notif => {
            html += `
              <div style="padding: 1rem; border-bottom: 1px solid var(--border); cursor: pointer; transition: all 0.2s ease;" 
                   onmouseover="this.style.background='var(--light)'" 
                   onmouseout="this.style.background='white'">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                  <div style="width: 8px; height: 8px; background: var(--primary); border-radius: 50%; margin-top: 0.375rem; flex-shrink: 0;"></div>
                  <div style="flex: 1;">
                    <p style="margin: 0 0 0.25rem 0; font-weight: 600; color: var(--text-dark); font-size: 13px;">${notif.title}</p>
                    <p style="margin: 0; font-size: 12px; color: var(--text-light); line-height: 1.4;">${notif.message}</p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 11px; color: var(--text-lighter);">${notif.time}</p>
                  </div>
                </div>
              </div>
            `;
          });
          
          html += '<div style="padding: 1rem; text-align: center; border-top: 1px solid var(--border);">';
          html += '<a href="#" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 600;">View All Notifications</a>';
          html += '</div>';
          
          notificationPanel.innerHTML = html;
          if (notifBtn) {
            notifBtn.style.position = 'relative';
            notifBtn.appendChild(notificationPanel);
          }
        });
    }

    // ===== CALENDAR HANDLER =====
    function showCalendar(event) {
      event.preventDefault();
      event.stopPropagation();
      alert('Calendar feature coming soon - click to view schedule');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.user-menu-container')) {
        const dropdown = document.getElementById('user-dropdown');
        if (dropdown) dropdown.classList.remove('active');
      }
      
      // Close notification panel when clicking outside
      if (!e.target.closest('.navbar-icon-btn[onclick*="showNotifications"]')) {
        document.querySelectorAll('[data-notification-panel]').forEach(el => el.remove());
      }
    });

    // Logout function
    function logout() {
      if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'index.php';
      }
    }

    // Search functionality (fallback)
    const searchBox = document.querySelector('.search-box input');
    if (searchBox) {
      searchBox.addEventListener('keyup', function(e) {
        const query = this.value;
        if (query.length > 2) {
          console.log('Searching for:', query);
        }
      });
    }
  </script>
</body>
</html>
