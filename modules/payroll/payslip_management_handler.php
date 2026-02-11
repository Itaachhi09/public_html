<?php
/**
 * Payslip Management Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'send_payslip') {
        $payroll_run_id = (int) ($_POST['payroll_run_id'] ?? 0);
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        
        if (!$payroll_run_id || !$employee_id) {
            $err = 'Payroll run and employee are required.';
        } else {
            // Update payslip status to sent
            $db = new Database();
            $conn = $db->connect();
            $query = "UPDATE payroll_run_employees SET payslip_sent = 1, sent_date = NOW() WHERE payroll_run_id = ? AND employee_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$payroll_run_id, $employee_id]);
            $msg = 'Payslip sent to employee.';
        }
    } elseif ($action === 'archive') {
        $payroll_run_id = (int) ($_POST['payroll_run_id'] ?? 0);
        
        if (!$payroll_run_id) {
            $err = 'Payroll run is required.';
        } else {
            $db = new Database();
            $conn = $db->connect();
            $query = "UPDATE payroll_run_employees SET archived = 1 WHERE payroll_run_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$payroll_run_id]);
            $msg = 'Payslips archived.';
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'payslip_management'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
