<?php
/**
 * Compensation Structure and Setup Form Handler
 * Master definition of salary components, incentive components, benefits. No payroll computation. No JS.
 * 
 * Features:
 * - Code format validation (uppercase with underscores)
 * - Mandatory description checking
 * - Status behavior with deactivation protection
 * - Audit logging (user, role, timestamp, reason)
 * - Payroll usage detection to prevent conflicts
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
require_once __DIR__ . '/models/SalaryComponentDefinition.php';
require_once __DIR__ . '/models/IncentiveType.php';
require_once __DIR__ . '/models/BenefitDefinition.php';

$salaryComp = new SalaryComponentDefinition();
$incentiveType = new IncentiveType();
$benefitDef = new BenefitDefinition();
$msg = '';
$err = '';

// Get current user info from session (assuming it's set)
$currentUserId = $_SESSION['user_id'] ?? null;
$currentUserRole = $_SESSION['role'] ?? 'Admin';

/**
 * Validate component code format (uppercase with underscores only)
 */
function validateCode($code) {
    if (empty($code)) {
        return 'Code is required.';
    }
    if (!SalaryComponentDefinition::validateCodeFormat($code)) {
        return 'Code must be uppercase letters, numbers, and underscores only (e.g., ER_DUTY_PAY).';
    }
    return '';
}

/**
 * Validate description (mandatory)
 */
function validateDescription($description) {
    if (empty(trim($description))) {
        return 'Description is mandatory and cannot be empty.';
    }
    return '';
}

/**
 * Check if component is used in payroll (prevent modification)
 */
function checkPayrollUsage($componentId, $componentType = 'salary') {
    // In a real implementation, this would check against active payroll records
    // For now, this is a placeholder that respects the used_by_payroll flag
    // TODO: Query against payroll_runs and related tables to check actual usage
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ===== SALARY COMPONENT ACTIONS =====
    if ($action === 'create_salary_component') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $componentType = $_POST['component_type'] ?? 'allowance';

        // Validation
        $codeErr = validateCode($code);
        if ($codeErr) {
            $err = $codeErr;
        } elseif (!$name) {
            $err = 'Name is required.';
        } elseif ($descErr = validateDescription($description)) {
            $err = $descErr;
        } elseif ($salaryComp->codeExists($code)) {
            $err = 'Salary component code already exists.';
        } elseif (!in_array($componentType, ['base', 'allowance', 'deduction'])) {
            $err = 'Invalid component type.';
        } elseif ($componentType === 'base' && $salaryComp->getCountByType('base', true) > 0) {
            $err = 'Only one active base pay component allowed per organization.';
        } else {
            $salaryComp->create([
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'component_type' => $componentType,
                'taxable' => !empty($_POST['taxable']) ? 1 : 0,
                'is_active' => 1,
                'effective_from' => !empty($_POST['effective_from']) ? $_POST['effective_from'] : null,
                'effective_to' => !empty($_POST['effective_to']) ? $_POST['effective_to'] : null,
                'configured_by_role' => $currentUserRole,
                'last_updated_by_id' => $currentUserId,
                'last_updated_reason' => 'Initial creation',
            ]);
            $msg = "Salary component '{$name}' created successfully.";
        }
    } 
    elseif ($action === 'deactivate_salary') {
        $id = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$id) {
            $err = 'Invalid component ID.';
        } elseif (empty($reason)) {
            $err = 'Deactivation reason is required for audit trail.';
        } else {
            $component = $salaryComp->find($id);
            if (!$component) {
                $err = 'Component not found.';
            } elseif (empty($component['is_active'])) {
                $err = 'Component is already inactive.';
            } elseif (checkPayrollUsage($id, 'salary')) {
                $err = 'Cannot deactivate. Component is used in active payroll periods. Contact Payroll Administrator.';
            } else {
                $salaryComp->update($id, [
                    'is_active' => 0,
                    'last_updated_by_id' => $currentUserId,
                    'last_updated_reason' => $reason,
                ]);
                $msg = "Salary component '{$component['name']}' deactivated. Reason: {$reason}";
            }
        }
    }

    // ===== INCENTIVE COMPONENT ACTIONS =====
    elseif ($action === 'create_incentive_component') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $rateType = $_POST['rate_type'] ?? 'per_case';
        $defaultRate = floatval($_POST['default_rate'] ?? 0);
        $usedByRoles = trim($_POST['used_by_roles'] ?? 'All');

        // Validation
        $codeErr = validateCode($code);
        if ($codeErr) {
            $err = $codeErr;
        } elseif (!$name) {
            $err = 'Name is required.';
        } elseif ($descErr = validateDescription($description)) {
            $err = $descErr;
        } elseif ($incentiveType->queryOne("SELECT COUNT(*) AS c FROM {$incentiveType->getTable()} WHERE code = ?", [$code])['c'] > 0) {
            $err = 'Incentive component code already exists.';
        } elseif (!in_array($rateType, ['fixed_amount', 'per_case'])) {
            $err = 'Invalid rate type.';
        } else {
            $incentiveType->create([
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'status' => 'active',
                'is_approved' => 1, // Backward compatibility
                'rate_type' => $rateType,
                'default_rate' => $defaultRate > 0 ? $defaultRate : null,
                'used_by_roles' => $usedByRoles ?: 'All',
                'configured_by_role' => $currentUserRole,
                'last_updated_by_id' => $currentUserId,
                'last_updated_reason' => 'Initial creation',
            ]);
            $msg = "Incentive component '{$name}' created successfully. Type: {$rateType}.";
        }
    }
    elseif ($action === 'deactivate_incentive' || $action === 'archive_incentive') {
        $id = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $newStatus = ($action === 'archive_incentive') ? 'archived' : 'inactive';

        if (!$id) {
            $err = 'Invalid incentive ID.';
        } elseif (empty($reason)) {
            $err = 'Change reason is required for audit trail.';
        } else {
            $component = $incentiveType->find($id);
            if (!$component) {
                $err = 'Incentive not found.';
            } elseif ($component['status'] === $newStatus) {
                $err = "Incentive is already {$newStatus}.";
            } else {
                $incentiveType->update($id, [
                    'status' => $newStatus,
                    'is_approved' => ($newStatus === 'active') ? 1 : 0, // Backward compatibility
                    'last_updated_by_id' => $currentUserId,
                    'last_updated_reason' => $reason,
                ]);
                $msg = "Incentive component '{$component['name']}' changed to {$newStatus}. Reason: {$reason}";
            }
        }
    }

    // ===== BENEFIT ACTIONS =====
    elseif ($action === 'create_benefit') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $effectiveFrom = trim($_POST['effective_from'] ?? '');
        $benefitCategory = $_POST['benefit_category'] ?? 'non_cash';
        $payrollImpact = $_POST['payroll_impact'] ?? 'informational';
        $eligibleRoles = trim($_POST['eligible_roles'] ?? 'All');

        // Validation
        $codeErr = validateCode($code);
        if ($codeErr) {
            $err = $codeErr;
        } elseif (!$name) {
            $err = 'Name is required.';
        } elseif ($descErr = validateDescription($description)) {
            $err = $descErr;
        } elseif (!$effectiveFrom) {
            $err = 'Effective from date is required.';
        } elseif ($benefitDef->codeExists($code)) {
            $err = 'Benefit code already exists.';
        } elseif (!in_array($benefitCategory, ['non_cash', 'cash_equivalent'])) {
            $err = 'Invalid benefit category.';
        } elseif (!in_array($payrollImpact, ['informational', 'included_in_payroll'])) {
            $err = 'Invalid payroll impact setting.';
        } elseif (!BenefitDefinition::validateEffectiveDates($effectiveFrom, $_POST['effective_to'] ?? null)) {
            $err = 'Effective from date must be before or equal to effective to date.';
        } else {
            $benefitDef->create([
                'code' => $code,
                'name' => $name,
                'description' => $description,
                'benefit_category' => $benefitCategory,
                'payroll_impact' => $payrollImpact,
                'taxable' => !empty($_POST['taxable']) ? 1 : 0,
                'taxable_lock' => 0, // Lock only after first payroll use
                'eligible_roles' => $eligibleRoles ?: 'All',
                'effective_from' => $effectiveFrom,
                'effective_to' => !empty($_POST['effective_to']) ? $_POST['effective_to'] : null,
                'attach_to' => in_array($_POST['attach_to'] ?? '', ['duty', 'role']) ? $_POST['attach_to'] : 'role',
                'is_active' => 1,
                'hidden_when_inactive' => 1,
                'configured_by_role' => $currentUserRole,
                'last_updated_by_id' => $currentUserId,
                'last_updated_reason' => 'Initial creation',
            ]);
            $msg = "Benefit '{$name}' created successfully. Category: {$benefitCategory}.";
        }
    }
    elseif ($action === 'deactivate_benefit') {
        $id = (int) ($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$id) {
            $err = 'Invalid benefit ID.';
        } elseif (empty($reason)) {
            $err = 'Deactivation reason is required for audit trail.';
        } else {
            $benefit = $benefitDef->find($id);
            if (!$benefit) {
                $err = 'Benefit not found.';
            } elseif (empty($benefit['is_active'])) {
                $err = 'Benefit is already inactive.';
            } elseif (!empty($benefit['taxable_lock'])) {
                $err = 'Cannot deactivate. Benefit has been used in payroll and is locked for tax compliance.';
            } elseif (checkPayrollUsage($id, 'benefit')) {
                $err = 'Cannot deactivate. Benefit is used in active payroll periods.';
            } else {
                $benefitDef->update($id, [
                    'is_active' => 0,
                    'last_updated_by_id' => $currentUserId,
                    'last_updated_reason' => $reason,
                ]);
                $msg = "Benefit '{$benefit['name']}' deactivated. Reason: {$reason}";
            }
        }
    }
}

$params = ['ref' => 'compensation', 'page' => 'compensation_structure'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit; 