<?php
/**
 * Payroll Module Index Router
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify user is authenticated
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/BaseConfig.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';

$action = $_GET['action'] ?? 'list';
$page = $_GET['page'] ?? 'payroll_runs';

// Route to appropriate view based on page parameter
$views = [
    'payroll_runs' => 'payroll_runs.php',
    'salaries' => 'salaries.php',
    'bonuses_incentives' => 'bonuses_incentives.php',
    'deductions' => 'deductions.php',
    'view_payslips' => 'view_payslips.php'
];

if (isset($views[$page])) {
    define('SYSTEM_INIT', true);
    require_once __DIR__ . '/views/' . $views[$page];
} else {
    http_response_code(404);
    echo 'Page not found';
}
?>
