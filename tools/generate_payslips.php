<?php
/**
 * CLI helper to generate payslips for a payroll run.
 * Usage: php generate_payslips.php <payroll_id> [--email]
 */

if (php_sapi_name() !== 'cli') {
    echo "This script is intended to be run from the command line.\n";
    exit(1);
}

$argv_copy = $argv;
array_shift($argv_copy); // script name
$payroll_id = $argv_copy[0] ?? null;
$email_flag = in_array('--email', $argv_copy, true) || in_array('-e', $argv_copy, true);

if (!$payroll_id || !is_numeric($payroll_id)) {
    echo "Usage: php generate_payslips.php <payroll_id> [--email]\n";
    exit(1);
}

// Boot minimal app context
chdir(__DIR__ . '/..');
require_once __DIR__ . '/../config/BaseConfig.php';
require_once __DIR__ . '/../modules/payroll/controllers/PayslipController.php';

// Ensure a session-like user for audit fields
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;

$controller = new PayslipController();

try {
    if ($email_flag) {
        $result = $controller->generateAndEmail((int)$payroll_id);
    } else {
        $result = $controller->generateAll((int)$payroll_id);
    }

    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    exit(0);
} catch (Exception $e) {
    $out = ['success' => false, 'message' => $e->getMessage()];
    echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
    exit(2);
}
