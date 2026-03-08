<?php
/**
 * Pay Bonds and Contracts Form Handler
 * Create pay contracts; bind employees to contracts. Expired contracts block new entries. No JS.
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

// Role-based access control
if (!canAccessMenuItem('compensation', 'pay_bonds_contracts')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied: pay_bonds_contracts']);
    exit;
}

require_once __DIR__ . '/models/PayContract.php';
require_once __DIR__ . '/models/EmployeeContractAssignment.php';

$contract = new PayContract();
$assignment = new EmployeeContractAssignment();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_contract') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $pay_grade_id = (int) ($_POST['pay_grade_id'] ?? 0);
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        if (!$code || !$name || !$pay_grade_id || !$start_date || !$end_date) {
            $err = 'Code, name, pay grade, start date, and end date are required.';
        } elseif ($end_date < $start_date) {
            $err = 'End date must be on or after start date.';
        } elseif ($contract->codeExists($code)) {
            $err = 'Contract code already exists.';
        } else {
            $contract->create([
                'code' => $code,
                'name' => $name,
                'pay_grade_id' => $pay_grade_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'applicable_policies' => trim($_POST['applicable_policies'] ?? ''),
            ]);
            $msg = 'Pay contract created.';
        }
    } elseif ($action === 'assign_employee') {
        $employee_id = (int) ($_POST['employee_id'] ?? 0);
        $contract_id = (int) ($_POST['contract_id'] ?? 0);
        $effective_from = trim($_POST['effective_from'] ?? '');
        $effective_to = !empty($_POST['effective_to']) ? trim($_POST['effective_to']) : null;
        if (!$employee_id || !$contract_id || !$effective_from) {
            $err = 'Employee, contract, and effective from are required.';
        } elseif (!$contract->isActive($contract_id)) {
            $err = 'Expired contracts block new entries. Select an active contract.';
        } else {
            $assignment->create([
                'employee_id' => $employee_id,
                'contract_id' => $contract_id,
                'effective_from' => $effective_from,
                'effective_to' => $effective_to,
            ]);
            $msg = 'Employee assigned to contract. Active contract required to receive compensation.';
        }
    } elseif ($action === 'end_assignment') {
        $id = (int) ($_POST['id'] ?? 0);
        $effective_to = trim($_POST['effective_to'] ?? date('Y-m-d'));
        if ($id) {
            $assignment->update($id, ['effective_to' => $effective_to]);
            $msg = 'Assignment ended.';
        }
    }
}

$params = ['ref' => 'compensation', 'page' => 'pay_bonds_contracts'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ' . BASE_URL . 'dashboard.php?' . http_build_query($params));
exit;
