<?php
/**
 * Compensation Structure and Setup Form Handler
 * Master definition of salary components, incentive components, benefits. No payroll computation. No JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/SalaryComponentDefinition.php';
require_once __DIR__ . '/models/IncentiveType.php';
require_once __DIR__ . '/models/BenefitDefinition.php';

$salaryComp = new SalaryComponentDefinition();
$incentiveType = new IncentiveType();
$benefitDef = new BenefitDefinition();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_salary_component') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        if (!$code || !$name) {
            $err = 'Code and name are required for salary component.';
        } elseif ($salaryComp->codeExists($code)) {
            $err = 'Salary component code already exists.';
        } else {
            $salaryComp->create([
                'code' => $code,
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'component_type' => in_array($_POST['component_type'] ?? '', ['base', 'allowance', 'deduction']) ? $_POST['component_type'] : 'allowance',
                'taxable' => !empty($_POST['taxable']) ? 1 : 0,
                'is_active' => 1,
            ]);
            $msg = 'Salary component added.';
        }
    } elseif ($action === 'create_incentive_component') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        if (!$code || !$name) {
            $err = 'Code and name are required for incentive component.';
        } elseif ($incentiveType->queryOne("SELECT COUNT(*) AS c FROM incentive_types WHERE code = ?", [$code])['c'] > 0) {
            $err = 'Incentive component code already exists.';
        } else {
            $incentiveType->create([
                'code' => $code,
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'is_approved' => 1,
            ]);
            $msg = 'Incentive component added. Only predefined incentives are allowed when logging.';
        }
    } elseif ($action === 'create_benefit') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $effective_from = trim($_POST['effective_from'] ?? '');
        $eligible_roles = trim($_POST['eligible_roles'] ?? 'ER Staff');
        if (!$code || !$name || !$effective_from) {
            $err = 'Code, name, and effective from are required for benefit.';
        } elseif ($benefitDef->codeExists($code)) {
            $err = 'Benefit code already exists.';
        } else {
            $benefitDef->create([
                'code' => $code,
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'taxable' => !empty($_POST['taxable']) ? 1 : 0,
                'eligible_roles' => $eligible_roles ?: 'ER Staff',
                'effective_from' => $effective_from,
                'effective_to' => !empty($_POST['effective_to']) ? trim($_POST['effective_to']) : null,
                'attach_to' => in_array($_POST['attach_to'] ?? '', ['duty', 'role']) ? $_POST['attach_to'] : 'duty',
                'is_active' => 1,
            ]);
            $msg = 'Benefit added.';
        }
    } elseif ($action === 'deactivate_salary' || $action === 'deactivate_incentive' || $action === 'deactivate_benefit') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            if ($action === 'deactivate_salary') {
                $salaryComp->update($id, ['is_active' => 0]);
                $msg = 'Salary component deactivated.';
            } elseif ($action === 'deactivate_incentive') {
                $incentiveType->update($id, ['is_approved' => 0]);
                $msg = 'Incentive component deactivated.';
            } else {
                $benefitDef->update($id, ['is_active' => 0]);
                $msg = 'Benefit deactivated.';
            }
        }
    }
}

$params = ['ref' => 'compensation', 'page' => 'compensation_structure'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
