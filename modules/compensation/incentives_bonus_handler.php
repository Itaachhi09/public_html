<?php
/**
 * Incentives and Bonuses Form Handler
 * Log incentive events (approved types only); approve pending. No JS; server-side only.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/IncentiveType.php';
require_once __DIR__ . '/models/IncentiveEvent.php';

$incentiveType = new IncentiveType();
$incentiveEvent = new IncentiveEvent();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $duty_date = trim($_POST['duty_date'] ?? '');
        $procedure_type_id = (int) ($_POST['procedure_type_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        if ($quantity < 1) $quantity = 1;

        if (!$employee_id || !$duty_date || !$procedure_type_id) {
            $err = 'Employee, duty date, and procedure type are required.';
        } elseif (!$incentiveType->isApproved($procedure_type_id)) {
            $err = 'Only approved incentive types are allowed. Selected procedure type is not approved.';
        } else {
            $incentiveEvent->create([
                'employee_id' => $employee_id,
                'duty_date' => $duty_date,
                'procedure_type_id' => $procedure_type_id,
                'quantity' => $quantity,
                'status' => 'pending',
            ]);
            $msg = 'Incentive event logged (pending approval).';
        }
    } elseif ($action === 'approve') {
        $id = (int) ($_POST['id'] ?? 0);
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        if ($id) {
            $incentiveEvent->update($id, [
                'status' => 'approved',
                'approved_by' => $userId,
            ]);
            $msg = 'Incentive event approved.';
        } else {
            $err = 'Invalid event.';
        }
    }
}

$params = ['ref' => 'compensation', 'page' => 'incentives_bonus'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
