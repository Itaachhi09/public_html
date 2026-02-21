<?php
/**
 * Employee Payroll Profile Handler
 * Saves and deletes payroll profile data to the database.
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

// Ensure table has all columns (add if missing)
try {
    $db = new Database();
    $conn = $db->connect();
    $cols = ['pay_type', 'payroll_status', 'tax_status', 'sss_status', 'philhealth_status', 'pagibig_status', 'bank_name', 'account_type', 'account_status'];
    $stmt = $conn->query("SHOW COLUMNS FROM employee_payroll_profiles");
    $existing = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing[] = $row['Field'];
    }
    foreach ($cols as $col) {
        if (!in_array($col, $existing, true)) {
            $type = in_array($col, ['pay_type', 'payroll_status', 'tax_status', 'sss_status', 'philhealth_status', 'pagibig_status', 'account_type', 'account_status'], true) ? 'VARCHAR(50) DEFAULT NULL' : 'VARCHAR(100) DEFAULT NULL';
            $conn->exec("ALTER TABLE employee_payroll_profiles ADD COLUMN `{$col}` {$type}");
        }
    }
} catch (Exception $e) {
    // Ignore if columns already exist
}

$baseUrl = '/public_html/dashboard.php';
$redirectParams = ['ref' => 'payroll', 'page' => 'employee_payroll_profile'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? 'save');

    if ($action === 'delete') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        if (!$employee_id) {
            $err = 'Employee is required to delete profile.';
        } else {
            $existing = $profile->getByEmployee($employee_id);
            if ($existing) {
                $profile->delete($existing['id']);
                $msg = 'Payroll profile deleted. Employee will be excluded from payroll.';
            } else {
                $err = 'No payroll profile found for this employee.';
            }
        }
    } else {
        // save or update
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        if (!$employee_id) {
            $err = 'Employee is required.';
        } else {
            $payroll_status = trim($_POST['payroll_status'] ?? '');
            $payroll_eligible = ($payroll_status === 'active') ? 1 : 0;

            $data = [
                'employee_id' => $employee_id,
                'payroll_eligible' => $payroll_eligible,
                'pay_type' => trim($_POST['pay_type'] ?? '') ?: null,
                'payroll_status' => $payroll_status ?: null,
                'tax_status' => trim($_POST['tax_status'] ?? '') ?: null,
                'sss_status' => trim($_POST['sss_status'] ?? '') ?: null,
                'sss_number' => trim($_POST['sss_number'] ?? '') ?: null,
                'philhealth_status' => trim($_POST['philhealth_status'] ?? '') ?: null,
                'philhealth_number' => trim($_POST['philhealth_number'] ?? '') ?: null,
                'pagibig_status' => trim($_POST['pagibig_status'] ?? '') ?: null,
                'pagibig_number' => trim($_POST['pagibig_number'] ?? '') ?: null,
                'tax_identification_number' => trim($_POST['tin'] ?? '') ?: null,
                'bank_name' => trim($_POST['bank_name'] ?? '') ?: null,
                'bank_code' => trim($_POST['bank_name'] ?? '') ?: null,
                'account_type' => trim($_POST['account_type'] ?? '') ?: null,
                'bank_account_number' => trim($_POST['bank_account_number'] ?? '') ?: null,
                'bank_account_holder' => trim($_POST['account_holder_name'] ?? '') ?: null,
                'account_status' => trim($_POST['account_status'] ?? '') ?: null,
            ];

            $existing = $profile->getByEmployee($employee_id);
            if ($existing) {
                unset($data['employee_id']);
                $profile->update($existing['id'], $data);
                $msg = 'Payroll profile updated successfully.';
            } else {
                $profile->create($data);
                $msg = 'Payroll profile created successfully.';
            }
        }
    }
}

// Use session so message and employee_id are available when view is loaded via fetch
$_SESSION['payroll_profile_msg'] = $msg;
$_SESSION['payroll_profile_err'] = $err;
$redirectEmployeeId = null;
if (!empty($_POST['employee_id'])) {
    $redirectEmployeeId = (int) $_POST['employee_id'];
    $_SESSION['payroll_profile_employee_id'] = $redirectEmployeeId;
}
if ($redirectEmployeeId) {
    $redirectParams['employee_id'] = $redirectEmployeeId;
    // Add modal parameters to keep modal open after save
    $redirectParams['modal'] = 'edit';
    if (!$err) {
        $redirectParams['success'] = 1;
    }
}
header('Location: ' . $baseUrl . '?' . http_build_query($redirectParams));
exit;
