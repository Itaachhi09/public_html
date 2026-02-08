<?php
/**
 * Payroll Salaries Form Handler. No JS.
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
    if ($action === 'add') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $salary_type = trim($_POST['salary_type'] ?? 'Monthly');
        $basic_rate = (float) ($_POST['basic_rate'] ?? 0);
        $effective_date = trim($_POST['effective_date'] ?? '');
        if (!$employee_id || $basic_rate <= 0 || !$effective_date) {
            $err = 'Employee, basic rate, and effective date are required.';
        } else {
            $salary->create([
                'employee_id' => $employee_id,
                'salary_type' => $salary_type,
                'basic_rate' => $basic_rate,
                'effective_date' => $effective_date,
                'tax_status' => trim($_POST['tax_status'] ?? 'Single'),
                'payroll_eligible' => !empty($_POST['payroll_eligible']) ? 1 : 0,
                'bank_name' => trim($_POST['bank_name'] ?? ''),
                'bank_account' => trim($_POST['bank_account'] ?? ''),
            ]);
            $msg = 'Salary record added.';
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'salaries'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
