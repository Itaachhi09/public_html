<?php
/**
 * System Health Check
 * Tests connectivity, API endpoints, and overall system status
 */

header('Content-Type: application/json');
$report = [];

// 1. Database Connectivity
try {
    require_once 'config/Database.php';
    $db = new Database();
    $conn = $db->connect();
    $report['database'] = ['status' => 'OK', 'message' => 'Connected successfully'];
} catch (Exception $e) {
    $report['database'] = ['status' => 'FAILED', 'message' => $e->getMessage()];
}

// 2. Session/Auth System
session_start();
$report['session'] = ['status' => 'OK', 'message' => 'Session system working'];

// 3. Check API Files
$api_files = [
    'modules/auth/controllers/AuthController.php',
    'modules/hr_core/api.php',
    'modules/payroll/api.php',
    'modules/compensation/api.php',
    'modules/hmo/api.php',
    'modules/analytics/api.php',
    'dashboard_api.php'
];

$report['api_endpoints'] = [];
foreach ($api_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $report['api_endpoints'][$file] = 'EXISTS';
    } else {
        $report['api_endpoints'][$file] = 'MISSING';
    }
}

// 4. Check Models
$model_dirs = [
    'modules/payroll/models',
    'modules/hr_core/models',
    'modules/compensation/models',
    'modules/hmo/models',
    'modules/auth/models'
];

$report['models'] = [];
foreach ($model_dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (is_dir($full_path)) {
        $files = @glob($full_path . '/*.php');
        $report['models'][$dir] = count($files) . ' models';
    } else {
        $report['models'][$dir] = 'DIR NOT FOUND';
    }
}

// 5. Check Views
$view_dirs = [
    'modules/payroll/views',
    'modules/hr_core/views',
    'modules/compensation/views',
    'modules/hmo/views',
    'modules/analytics/views'
];

$report['views'] = [];
foreach ($view_dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (is_dir($full_path)) {
        $files = @glob($full_path . '/*.php');
        $report['views'][$dir] = count($files) . ' views';
    } else {
        $report['views'][$dir] = 'DIR NOT FOUND';
    }
}

// 6. Configuration files
$report['config'] = [];
$config_files = ['Database.php', 'Auth.php', 'EmailService.php', 'OTPService.php'];
foreach ($config_files as $file) {
    $full_path = __DIR__ . '/config/' . $file;
    $report['config'][$file] = file_exists($full_path) ? 'OK' : 'MISSING';
}

// 7. Database Schema Files
$report['database_schemas'] = [];
$schema_files = @glob(__DIR__ . '/database/*.sql');
$report['database_schemas']['total_schemas'] = count($schema_files);
$report['database_schemas']['files'] = array_map('basename', $schema_files);

// 8. Error Logs
$report['logs'] = [];
if (file_exists(__DIR__ . '/logs/auth_attempts.log')) {
    $lines = file(__DIR__ . '/logs/auth_attempts.log');
    $report['logs']['auth_attempts'] = count($lines) . ' entries';
}

echo json_encode($report, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
?>
