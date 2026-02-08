<?php
/**
 * Compensation Approval Workflow Handler
 * Request → Review → Approval/Rejection. Unapproved items never reach Payroll. Auditable. No JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/CompensationApprovalRequest.php';

$approval = new CompensationApprovalRequest();
$msg = '';
$err = '';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_request') {
        $request_type = trim($_POST['request_type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $validTypes = ['salary_above_band', 'double_pay_tagging', 'manual_adjustment', 'policy_exception'];
        if (!in_array($request_type, $validTypes, true)) {
            $err = 'Invalid request type.';
        } elseif (!$description) {
            $err = 'Description is required.';
        } else {
            $approval->create([
                'request_type' => $request_type,
                'reference_type' => !empty($_POST['reference_type']) ? trim($_POST['reference_type']) : null,
                'reference_id' => !empty($_POST['reference_id']) ? (int) $_POST['reference_id'] : null,
                'description' => $description,
                'status' => 'requested',
                'requested_by' => $userId,
            ]);
            $msg = 'Approval request submitted. Workflow: Request → Review → Approval/Rejection.';
        }
    } elseif ($action === 'set_review') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $approval->update($id, ['status' => 'under_review']);
            $msg = 'Request set to under review.';
        }
    } elseif ($action === 'approve') {
        $id = (int) ($_POST['id'] ?? 0);
        $notes = trim($_POST['approval_notes'] ?? '');
        if ($id) {
            $approval->update($id, [
                'status' => 'approved',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'approval_notes' => $notes,
            ]);
            $msg = 'Request approved. Only approved items reach Payroll.';
        }
    } elseif ($action === 'reject') {
        $id = (int) ($_POST['id'] ?? 0);
        $notes = trim($_POST['approval_notes'] ?? '');
        if ($id) {
            $approval->update($id, [
                'status' => 'rejected',
                'reviewed_by' => $userId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'approval_notes' => $notes,
            ]);
            $msg = 'Request rejected. Unapproved items never reach Payroll.';
        }
    }
}

$params = ['ref' => 'compensation', 'page' => 'compensation_approval'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
