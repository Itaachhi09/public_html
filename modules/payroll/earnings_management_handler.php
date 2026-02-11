<?php
/**
 * Earnings Management Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/EmployeeSalary.php';

$salary = new EmployeeSalary();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'adjust_salary') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $basic_rate = (float) ($_POST['basic_rate'] ?? 0);
        
        if (!$employee_id || $basic_rate <= 0) {
            $err = 'Employee and basic rate are required.';
        } else {
            // Check if employee salary exists
            $existing = $salary->queryOne("SELECT id FROM employee_salaries WHERE employee_id = ?", [$employee_id]);
            if ($existing) {
                $salary->update($existing['id'], ['basic_rate' => $basic_rate]);
                $msg = 'Employee salary updated.';
            } else {
                $salary->create([
                    'employee_id' => $employee_id,
                    'basic_rate' => $basic_rate,
                    'payroll_eligible' => 1,
                ]);
                $msg = 'Employee salary created.';
            }
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'earnings_management'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
