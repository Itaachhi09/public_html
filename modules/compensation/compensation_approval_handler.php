<?php
/**
 * Compensation Approval Workflow Handler
 * Request → Review → Approval/Rejection. Unapproved items never reach Payroll. Auditable. No JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect AJAX requests upfront
$isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (empty($_SESSION['token'])) {
    if ($isAjaxRequest) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/BaseConfig.php';

// Role-based access control
if (!canAccessMenuItem('compensation', 'compensation_approval')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied: compensation_approval']);
    exit;
}

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


// If AJAX, return JSON response instead of redirect
if ($isAjaxRequest) {
    header('Content-Type: application/json');
    http_response_code(empty($err) ? 200 : 400);
    echo json_encode([
        'success' => empty($err),
        'message' => $err ?: $msg,
        'error' => $err,
    ]);
    exit;
}

// For traditional form submission, redirect back to the compensation approval view
if ($msg) $_SESSION['compensation_approval_msg'] = $msg;
if ($err) $_SESSION['compensation_approval_err'] = $err;

header('Location: ../../dashboard.php?ref=compensation&page=compensation_approval');
exit;
