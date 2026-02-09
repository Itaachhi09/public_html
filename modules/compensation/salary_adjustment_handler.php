<?php
/**
 * Salary Adjustment Form Handler
 * Create adjustment records (pending); approve/reject. AJAX JSON response.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/SalaryAdjustment.php';
require_once __DIR__ . '/models/SalaryBand.php';

$adjustment = new SalaryAdjustment();
$salaryBand = new SalaryBand();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $adjustment_type = trim($_POST['adjustment_type'] ?? '');
        $trigger_type = trim($_POST['trigger_type'] ?? '');
        $previous_amount = isset($_POST['previous_amount']) && $_POST['previous_amount'] !== '' ? (float) $_POST['previous_amount'] : null;
        $new_amount = (float) ($_POST['new_amount'] ?? 0);
        $effective_date = trim($_POST['effective_date'] ?? '');
        $end_date = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
        $reason = trim($_POST['reason'] ?? '');
        $salary_band_id = !empty($_POST['salary_band_id']) ? (int) $_POST['salary_band_id'] : null;

        if (!$employee_id || !in_array($adjustment_type, ['Temporary', 'Permanent'], true) || !in_array($trigger_type, ['Promotion', 'Approved exception', 'Policy change'], true)) {
            $err = 'Employee, adjustment type (Temporary/Permanent), and trigger (Promotion/Approved exception/Policy change) are required.';
        } elseif ($new_amount <= 0 || !$effective_date || !$reason) {
            $err = 'New amount, effective date, and reason are required.';
        } else {
            if ($salary_band_id) {
                $check = $salaryBand->isWithinBand($salary_band_id, $new_amount);
                if (!$check['valid']) {
                    $err = $check['message'] . ' (Adjustments respect salary bands.)';
                }
            }
            if ($err === '') {
                $adjustment->create([
                    'employee_id' => $employee_id,
                    'adjustment_type' => $adjustment_type,
                    'trigger_type' => $trigger_type,
                    'previous_amount' => $previous_amount,
                    'new_amount' => $new_amount,
                    'effective_date' => $effective_date,
                    'end_date' => $adjustment_type === 'Temporary' ? $end_date : null,
                    'reason' => $reason,
                    'status' => 'pending',
                    'salary_band_id' => $salary_band_id,
                ]);
                $msg = 'Adjustment record created (pending approval).';
            }
        }
    } elseif ($action === 'approve' || $action === 'reject') {
        $id = (int) ($_POST['id'] ?? 0);
        $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
        if ($id) {
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $adjustment->update($id, [
                'status' => $status,
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s'),
            ]);
            $msg = $action === 'approve' ? 'Adjustment approved.' : 'Adjustment rejected.';
        } else {
            $err = 'Invalid adjustment.';
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => empty($err),
        'message' => $msg ?: $err,
        'action' => $action
    ]);
    exit;
}

// No POST request
header('HTTP/1.1 400 Bad Request');
echo json_encode(['error' => 'No POST data']);
exit;
