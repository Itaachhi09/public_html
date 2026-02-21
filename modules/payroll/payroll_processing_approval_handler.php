<?php
/**
 * Payroll Approval Handler
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
require_once __DIR__ . '/models/PayrollApproval.php';

$approval = new PayrollApproval();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'preview') {
        // Store the payroll ID in session to display preview on the main page
        $_SESSION['preview_payroll_id'] = (int) ($_POST['payroll_id'] ?? 0);
    } elseif ($action === 'cancel_preview') {
        // Clear the preview from session
        unset($_SESSION['preview_payroll_id']);
    } elseif ($action === 'approve') {
        $id = (int) ($_POST['id'] ?? 0);
        $record = $approval->find($id);
        
        if (!$record) {
            $err = 'Approval record not found.';
        } else {
            $approval->update($id, [
                'approval_status' => 'approved',
                'approver_id' => $_SESSION['user_id'] ?? 0,
                'approval_date' => date('Y-m-d H:i:s'),
            ]);
            $msg = 'Payroll approved.';
        }
    } elseif ($action === 'reject') {
        $id = (int) ($_POST['id'] ?? 0);
        $comments = trim($_POST['comments'] ?? '');
        $record = $approval->find($id);
        
        if (!$record) {
            $err = 'Approval record not found.';
        } else {
            $approval->update($id, [
                'approval_status' => 'rejected',
                'approver_id' => $_SESSION['user_id'] ?? 0,
                'comments' => $comments,
                'approval_date' => date('Y-m-d H:i:s'),
            ]);
            $msg = 'Payroll rejected.';
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'payroll_processing_approval'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: /public_html/dashboard.php?' . http_build_query($params));
exit;
