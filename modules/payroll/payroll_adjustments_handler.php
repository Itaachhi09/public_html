<?php
/**
 * Payroll Adjustments Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/BaseConfig.php';
require_once __DIR__ . '/models/PayrollAdjustment.php';

$adjustment = new PayrollAdjustment();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $adjustment_type = trim($_POST['adjustment_type'] ?? '');
        $amount = (float) ($_POST['amount'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $effective_date = trim($_POST['effective_date'] ?? '');
        
        if (!$employee_id || !$adjustment_type || $amount <= 0 || !$reason || !$effective_date) {
            $err = 'All fields are required.';
        } else {
            $adjustment->create([
                'employee_id' => $employee_id,
                'adjustment_type' => $adjustment_type,
                'amount' => $amount,
                'reason' => $reason,
                'effective_date' => $effective_date,
                'status' => 'pending',
            ]);
            $msg = 'Payroll adjustment created and pending approval.';
        }
    } elseif ($action === 'approve') {
        $id = (int) ($_POST['id'] ?? 0);
        $component = $adjustment->find($id);
        if (!$component) {
            $err = 'Adjustment not found.';
        } else {
            $adjustment->update($id, ['status' => 'approved', 'approved_by' => $_SESSION['user_id'] ?? 0]);
            $msg = 'Adjustment approved.';
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'payroll_adjustments_special_pay'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ' . BASE_URL . 'dashboard.php?' . http_build_query($params));
exit;
