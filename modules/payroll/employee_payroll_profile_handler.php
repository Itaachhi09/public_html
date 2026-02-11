<?php
/**
 * Employee Payroll Profile Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/EmployeePayrollProfile.php';

$profile = new EmployeePayrollProfile();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $payroll_eligible = !empty($_POST['payroll_eligible']) ? 1 : 0;
        $bank_account = trim($_POST['bank_account'] ?? '');
        
        if (!$employee_id) {
            $err = 'Employee is required.';
        } else {
            $existing = $profile->getByEmployee($employee_id);
            if ($existing) {
                $profile->update($existing['id'], [
                    'payroll_eligible' => $payroll_eligible,
                    'bank_account_number' => $bank_account,
                ]);
                $msg = 'Employee payroll profile updated.';
            } else {
                $profile->create([
                    'employee_id' => $employee_id,
                    'payroll_eligible' => $payroll_eligible,
                    'bank_account_number' => $bank_account,
                ]);
                $msg = 'Employee payroll profile created.';
            }
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'employee_payroll_profile'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
