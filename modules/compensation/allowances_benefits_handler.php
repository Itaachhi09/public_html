<?php
/**
 * Allowances and Benefits Form Handler
 * Define benefits (taxable, eligible roles, effective dates). No JS; server-side only.
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
require_once __DIR__ . '/models/BenefitDefinition.php';

$benefit = new BenefitDefinition();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $effective_from = trim($_POST['effective_from'] ?? '');
        $eligible_roles = trim($_POST['eligible_roles'] ?? 'All');
        if (!$eligible_roles) $eligible_roles = 'All';

        if (!$code || !$name || !$effective_from) {
            $err = 'Code, name, and effective from date are required.';
        } elseif ($benefit->codeExists($code)) {
            $err = 'Benefit code already exists.';
        } else {
            $benefit->create([
                'code' => $code,
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'taxable' => !empty($_POST['taxable']) ? 1 : 0,
                'eligible_roles' => $eligible_roles,
                'effective_from' => $effective_from,
                'effective_to' => !empty($_POST['effective_to']) ? trim($_POST['effective_to']) : null,
                'attach_to' => in_array($_POST['attach_to'] ?? '', ['duty', 'role']) ? $_POST['attach_to'] : 'role',
                'is_active' => 1,
            ]);
            $msg = 'Benefit defined. Data available for Payroll reference.';
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $effective_from = trim($_POST['effective_from'] ?? '');
        $eligible_roles = trim($_POST['eligible_roles'] ?? 'All');
        if (!$eligible_roles) $eligible_roles = 'All';

        if (!$id || !$code || !$name || !$effective_from) {
            $err = 'ID, code, name, and effective from are required.';
        } elseif ($benefit->codeExists($code, $id)) {
            $err = 'Benefit code already in use by another record.';
        } else {
            $benefit->update($id, [
                'code' => $code,
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'taxable' => !empty($_POST['taxable']) ? 1 : 0,
                'eligible_roles' => $eligible_roles,
                'effective_from' => $effective_from,
                'effective_to' => !empty($_POST['effective_to']) ? trim($_POST['effective_to']) : null,
                'attach_to' => in_array($_POST['attach_to'] ?? '', ['duty', 'role']) ? $_POST['attach_to'] : 'role',
            ]);
            $msg = 'Benefit updated.';
        }
    } elseif ($action === 'deactivate') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $benefit->update($id, ['is_active' => 0]);
            $msg = 'Benefit deactivated.';
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

// For traditional form submission, redirect back to the allowances & benefits view
if ($msg) $_SESSION['allowances_benefits_msg'] = $msg;
if ($err) $_SESSION['allowances_benefits_err'] = $err;

header('Location: ../../dashboard.php?ref=compensation&page=allowances_benefits');
exit;
