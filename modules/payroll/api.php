<?php
/**
 * Payroll Module API Endpoint
 * Routes requests to PayrollController
 */

require_once __DIR__ . '/controllers/PayrollController.php';

// Handle CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    exit;
}

// Initialize controller and dispatch
$controller = new PayrollController();
$action = $_GET['action'] ?? 'getPayrollRuns';

try {
    $controller->dispatch($action);
} catch (Exception $e) {
    Response::serverError('Internal server error: ' . $e->getMessage());
}

?>
