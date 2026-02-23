<?php
/**
 * Compensation Structure - Modern Compact Design
 * Clean, efficient layout. Dense but readable.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/SalaryComponentDefinition.php';
require_once __DIR__ . '/../models/IncentiveType.php';
require_once __DIR__ . '/../models/BenefitDefinition.php';

$salaryComp = new SalaryComponentDefinition();
$incentiveType = new IncentiveType();
$benefitDef = new BenefitDefinition();

$salaryComponents = $salaryComp->getAll(false);
$incentiveComponents = $incentiveType->getAll(false);
$benefits = $benefitDef->getAll(false);

$handlerUrl = 'modules/compensation/compensation_structure_handler.php';
$currentTab = $_GET['tab'] ?? 'base';

// Helper to read enum options from DB
function getEnumOptions($table, $column) {
    try {
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['COLUMN_TYPE'])) return [];
        if (preg_match("/^enum\\((.*)\\)$/i", $row['COLUMN_TYPE'], $m)) {
            preg_match_all("/'((?:[^']|\\\\')*)'/", $m[1], $matches);
            return $matches[1] ?? [];
        }
    } catch (Exception $e) {
        return [];
    }
    return [];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Compensation Structure</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body { 
    background: #f5f5f5; 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #1f2937;
    font-size: 13px;
    line-height: 1.4;
}

.container { width: 100%; background: #fff; }
.header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.title { font-size: 17px; font-weight: 600; color: #111827; }
.context-link { font-size: 11px; color: #6b7280; text-decoration: none; border: 1px solid #e5e7eb; padding: 4px 8px; border-radius: 3px; cursor: pointer; }
.context-link:hover { background: #f9fafb; }

.content { max-width: 1080px; }
.section { padding: 12px 24px; border-bottom: 1px solid #e5e7eb; }
.section-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
.section-title { font-size: 14px; font-weight: 600; color: #111827; }

.msg-bar { padding: 12px 24px; display: flex; gap: 8px; }
.msg { background: #d1fae5; border-left: 3px solid #10b981; color: #065f46; padding: 8px 12px; border-radius: 3px; font-size: 12px; flex: 1; }
.err { background: #fee2e2; border-left: 3px solid #ef4444; color: #991b1b; padding: 8px 12px; border-radius: 3px; font-size: 12px; flex: 1; }

.tabs { display: flex; gap: 6px; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; }
.tab { padding: 6px 12px; font-size: 12px; font-weight: 500; border: 1px solid #d1d5db; background: #fff; color: #6b7280; border-radius: 14px; cursor: pointer; height: 28px; display: flex; align-items: center; }
.tab:hover { border-color: #9ca3af; color: #374151; }
.tab.active { background: #1e40af; color: #fff; border-color: #1e40af; }

.table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 8px; }
.table th { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; height: 28px; }
.table td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; height: 30px; vertical-align: middle; }
.table tbody tr:hover { background: #f9fafb; }

.code { font-family: 'Courier New', monospace; background: #f3f4f6; padding: 2px 4px; border-radius: 2px; font-size: 11px; }

.badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; display: inline-block; }
.badge-active { background: #d1fae5; color: #065f46; }
.badge-inactive { background: #f3f4f6; color: #6b7280; }
.badge-archived { background: #fee2e2; color: #991b1b; }

.btn { padding: 6px 12px; font-size: 12px; font-weight: 500; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 4px; cursor: pointer; height: 30px; display: inline-flex; align-items: center; gap: 4px; }
.btn:hover { border-color: #9ca3af; background: #f9fafb; }
.btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.btn-primary:hover { background: #1c3aa0; }
.btn-sm { padding: 4px 8px; font-size: 11px; height: 24px; }
.btn-icon { width: 24px; padding: 0; justify-content: center; }

.empty-state { padding: 24px 8px; color: #9ca3af; font-size: 12px; }

.add-form { display: none; background: #f9fafb; padding: 12px; border: 1px solid #e5e7eb; border-radius: 4px; margin-bottom: 8px; }
.add-form.visible { display: block; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.form-row.full { grid-template-columns: 1fr; }
.form-group { display: flex; flex-direction: column; gap: 3px; }
.form-label { font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
.required { color: #ef4444; }

.form-input, .form-select, .form-textarea { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; font-family: inherit; color: #1f2937; height: 30px; }
.form-textarea { height: 60px; resize: vertical; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.1); }

.form-checkbox-group { display: flex; align-items: center; gap: 6px; height: 30px; }
.form-checkbox-group input[type="checkbox"] { width: 14px; height: 14px; cursor: pointer; }
.form-checkbox-group label { font-size: 12px; cursor: pointer; }

.form-actions { display: flex; gap: 6px; margin-top: 8px; }

.inline-form { display: contents; }
.tab-content { display: none; }
.tab-content.active { display: block; }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .table { font-size: 11px; }
    .table th, .table td { padding: 4px 6px; }
}
</style>
</head>
<body>

<div class="container">
    
    <!-- Header -->
    <div class="header">
        <div><span class="title">Compensation Structure</span> <a class="context-link" onclick="alert('Organization: Primary Hospital 30 beds ER focused. Last updated: ' + new Date().toDateString()); return false;">Context</a></div>
    </div>

    <!-- Messages -->
    <?php if (!empty($_GET['msg']) || !empty($_GET['err'])): ?>
    <div class="msg-bar">
        <?php if (!empty($_GET['msg'])): ?>
        <div class="msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['err'])): ?>
        <div class="err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="content">

        <!-- 1. SALARY COMPONENTS -->
        <div class="section">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="section-title">1. Salary Components</div>
                    <div class="tabs" style="border: none; padding: 0; margin: 8px 0 0 0;">
                        <button class="tab <?php echo $currentTab === 'base' ? 'active' : ''; ?>" onclick="switchTab('base', 'salary'); return false;">Basic Pay</button>
                        <button class="tab <?php echo $currentTab === 'allowance' ? 'active' : ''; ?>" onclick="switchTab('allowance', 'salary'); return false;">Allowances</button>
                        <button class="tab <?php echo $currentTab === 'deduction' ? 'active' : ''; ?>" onclick="switchTab('deduction', 'salary'); return false;">Deductions</button>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-salary-form'); return false;">+ Add</button>
            </div>

            <!-- Tab: Base -->
            <div id="salary-base" class="tab-content <?php echo $currentTab === 'base' ? 'active' : ''; ?>">
                <?php 
                $baseComps = array_filter($salaryComponents, fn($c) => $c['component_type'] === 'base');
                if (empty($baseComps)): 
                ?>
                <div class="empty-state">No base pay components.</div>
                <?php else: ?>
                <table class="table">
                    <thead><tr><th>Code</th><th>Name</th><th>Taxable</th><th>Status</th><th style="width:50px;"></th></tr></thead>
                    <tbody>
                    <?php foreach ($baseComps as $s): ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($s['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo !empty($s['taxable']) ? 'Yes' : 'No'; ?></td>
                        <td><span class="badge badge-<?php echo !empty($s['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                        <td><?php if (!empty($s['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate?');"><input type="hidden" name="action" value="deactivate_salary"><input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon">–</button></form><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Tab: Allowance -->
            <div id="salary-allowance" class="tab-content <?php echo $currentTab === 'allowance' ? 'active' : ''; ?>">
                <?php 
                $allowComps = array_filter($salaryComponents, fn($c) => $c['component_type'] === 'allowance');
                if (empty($allowComps)): 
                ?>
                <div class="empty-state">No allowance components.</div>
                <?php else: ?>
                <table class="table">
                    <thead><tr><th>Code</th><th>Name</th><th>Taxable</th><th>Status</th><th style="width:50px;"></th></tr></thead>
                    <tbody>
                    <?php foreach ($allowComps as $s): ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($s['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo !empty($s['taxable']) ? 'Yes' : 'No'; ?></td>
                        <td><span class="badge badge-<?php echo !empty($s['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                        <td><?php if (!empty($s['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate?');"><input type="hidden" name="action" value="deactivate_salary"><input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon">–</button></form><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Tab: Deduction -->
            <div id="salary-deduction" class="tab-content <?php echo $currentTab === 'deduction' ? 'active' : ''; ?>">
                <?php 
                $dedComps = array_filter($salaryComponents, fn($c) => $c['component_type'] === 'deduction');
                if (empty($dedComps)): 
                ?>
                <div class="empty-state">No deduction components.</div>
                <?php else: ?>
                <table class="table">
                    <thead><tr><th>Code</th><th>Name</th><th>Taxable</th><th>Status</th><th style="width:50px;"></th></tr></thead>
                    <tbody>
                    <?php foreach ($dedComps as $s): ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($s['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td><?php echo !empty($s['taxable']) ? 'Yes' : 'No'; ?></td>
                        <td><span class="badge badge-<?php echo !empty($s['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                        <td><?php if (!empty($s['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate?');"><input type="hidden" name="action" value="deactivate_salary"><input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon">–</button></form><?php endif; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Add Form -->
            <div id="add-salary-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_salary_component">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Code <span class="required">•</span></label><input type="text" name="code" required class="form-input" placeholder="BASE_PAY" maxlength="50"></div>
                        <div class="form-group"><label class="form-label">Name <span class="required">•</span></label><input type="text" name="name" required class="form-input" placeholder="Base Pay" maxlength="255"></div>
                    </div>
                    <div class="form-row full"><div class="form-group"><label class="form-label">Description <span class="required">•</span></label><textarea name="description" required class="form-textarea" placeholder="What is this?" maxlength="500"></textarea></div></div>
                    <div class="form-row"><div class="form-group"><label class="form-label">Type</label><select name="component_type" class="form-select">
                        <?php
                        $copts = getEnumOptions('salary_component_definitions', 'component_type');
                        if (!empty($copts)) {
                            foreach ($copts as $co) {
                                $sel = $co === 'allowance' ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($co) . '"' . $sel . '>' . htmlspecialchars(ucwords(str_replace('_',' ',$co))) . '</option>';
                            }
                        } else {
                            ?>
                            <option value="base">Base</option>
                            <option value="allowance" selected>Allowance</option>
                            <option value="deduction">Deduction</option>
                        <?php
                        }
                        ?>
                        </select></div><div class="form-checkbox-group"><input type="checkbox" id="sal-tax" name="taxable" value="1"><label for="sal-tax">Taxable</label></div></div>
                    <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm">Create</button><button type="button" class="btn btn-sm" onclick="toggleForm('add-salary-form'); return false;">Cancel</button></div>
                </form>
            </div>
        </div>

        <!-- 2. INCENTIVE COMPONENTS -->
        <div class="section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="section-title">2. Incentive Components</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-incentive-form'); return false;">+ Add</button>
            </div>

            <?php if (empty($incentiveComponents)): ?>
            <div class="empty-state">No incentive components.</div>
            <?php else: ?>
            <table class="table">
                <thead><tr><th>Code</th><th>Name</th><th>Rate Type</th><th>Status</th><th style="width:50px;"></th></tr></thead>
                <tbody>
                <?php foreach ($incentiveComponents as $i): 
                    $status = $i['status'] ?? 'active';
                    $statusClass = $status === 'archived' ? 'archived' : ($status === 'inactive' ? 'inactive' : 'active');
                ?>
                <tr>
                    <td><span class="code"><?php echo htmlspecialchars($i['code']); ?></span></td>
                    <td><?php echo htmlspecialchars($i['name']); ?></td>
                    <td><?php echo isset($i['rate_type']) ? ucwords(str_replace('_', ' ', $i['rate_type'])) : 'Per Case'; ?></td>
                    <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span></td>
                    <td><?php if ($status === 'active'): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate?');"><input type="hidden" name="action" value="deactivate_incentive"><input type="hidden" name="id" value="<?php echo (int)$i['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon">–</button></form><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Add Form -->
            <div id="add-incentive-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_incentive_component">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Code <span class="required">•</span></label><input type="text" name="code" required class="form-input" placeholder="ADMISSION" maxlength="50"></div>
                        <div class="form-group"><label class="form-label">Name <span class="required">•</span></label><input type="text" name="name" required class="form-input" placeholder="Admission" maxlength="255"></div>
                    </div>
                    <div class="form-row full"><div class="form-group"><label class="form-label">Description <span class="required">•</span></label><textarea name="description" required class="form-textarea" placeholder="What is this?"></textarea></div></div>
                    <div class="form-row"><div class="form-group"><label class="form-label">Rate Type</label><select name="rate_type" class="form-select">
                        <?php
                        // rate_type may not be a DB enum in this schema; try incentive_types.rate_type first
                        $ropts = getEnumOptions('incentive_types', 'rate_type');
                        if (!empty($ropts)) {
                            foreach ($ropts as $r) {
                                echo '<option value="' . htmlspecialchars($r) . '">' . htmlspecialchars(ucwords(str_replace('_',' ',$r))) . '</option>';
                            }
                        } else {
                            // fallback static options
                            ?>
                            <option value="per_case" selected>Per Case</option>
                            <option value="fixed_amount">Fixed Amount</option>
                        <?php
                        }
                        ?>
                        </select></div><div class="form-group"><label class="form-label">Default Rate (₱)</label><input type="number" name="default_rate" class="form-input" placeholder="0.00" step="0.01" min="0"></div></div>
                    <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm">Create</button><button type="button" class="btn btn-sm" onclick="toggleForm('add-incentive-form'); return false;">Cancel</button></div>
                </form>
            </div>
        </div>

        <!-- 3. BENEFITS -->
        <div class="section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="section-title">3. Benefits</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-benefit-form'); return false;">+ Add</button>
            </div>

            <?php if (empty($benefits)): ?>
            <div class="empty-state">No benefits defined.</div>
            <?php else: ?>
            <table class="table">
                <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Taxable</th><th>Status</th><th style="width:50px;"></th></tr></thead>
                <tbody>
                <?php foreach ($benefits as $b): ?>
                <tr>
                    <td><span class="code"><?php echo htmlspecialchars($b['code']); ?></span></td>
                    <td><?php echo htmlspecialchars($b['name']); ?></td>
                    <td><?php echo ucwords(str_replace('_', ' ', $b['benefit_category'] ?? 'Non Cash')); ?></td>
                    <td><?php echo !empty($b['taxable']) ? 'Yes' : 'No'; ?></td>
                    <td><span class="badge badge-<?php echo !empty($b['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($b['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                    <td><?php if (!empty($b['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate?');"><input type="hidden" name="action" value="deactivate_benefit"><input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon">–</button></form><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Add Form -->
            <div id="add-benefit-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_benefit">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Code <span class="required">•</span></label><input type="text" name="code" required class="form-input" placeholder="FREE_MEALS_TID" maxlength="50"></div>
                        <div class="form-group"><label class="form-label">Name <span class="required">•</span></label><input type="text" name="name" required class="form-input" placeholder="Free Meals" maxlength="255"></div>
                    </div>
                    <div class="form-row full"><div class="form-group"><label class="form-label">Description <span class="required">•</span></label><textarea name="description" required class="form-textarea" placeholder="What is this?"></textarea></div></div>
                    <div class="form-row"><div class="form-group"><label class="form-label">Category</label><select name="benefit_category" class="form-select">
                        <?php
                        // No dedicated DB enum/table for benefit categories in this schema; keep fallback options
                        ?>
                        <option value="non_cash" selected>Non Cash</option>
                        <option value="cash_equivalent">Cash Equivalent</option>
                        </select></div><div class="form-group"><label class="form-label">Impact</label><select name="payroll_impact" class="form-select">
                        <?php
                        // payroll_impact not present as an enum in schema; fallback options kept
                        ?>
                        <option value="informational" selected>Informational</option>
                        <option value="included_in_payroll">In Payroll</option>
                        </select></div></div>
                    <div class="form-row"><div class="form-group"><label class="form-label">From <span class="required">•</span></label><input type="date" name="effective_from" required class="form-input"></div><div class="form-group"><label class="form-label">To</label><input type="date" name="effective_to" class="form-input"></div></div>
                    <div class="form-row"><div class="form-checkbox-group"><input type="checkbox" id="ben-tax" name="taxable" value="1"><label for="ben-tax">Taxable</label></div><div class="form-group"><label class="form-label">Attach</label><select name="attach_to" class="form-select">
                        <?php
                        $aopts = getEnumOptions('benefit_definitions', 'attach_to');
                        if (!empty($aopts)) {
                            foreach ($aopts as $a) {
                                $display = htmlspecialchars(ucwords(str_replace('_',' ',$a)));
                                $value = htmlspecialchars($a);
                                $sel = $a === 'role' ? ' selected' : '';
                                echo "<option value=\"{$value}\"{$sel}>{$display}</option>";
                            }
                        } else {
                            ?>
                            <option value="role" selected>Role</option>
                            <option value="duty">Duty</option>
                        <?php
                        }
                        ?>
                        </select></div></div>
                    <div class="form-actions"><button type="submit" class="btn btn-primary btn-sm">Create</button><button type="button" class="btn btn-sm" onclick="toggleForm('add-benefit-form'); return false;">Cancel</button></div>
                </form>
            </div>
        </div>

    </div>

</div>

<script>
function toggleForm(id) {
    document.getElementById(id).classList.toggle('visible');
}

function switchTab(tab, sec) {
    // Hide all tab content sections
    document.querySelectorAll('#' + sec + '-base, #' + sec + '-allowance, #' + sec + '-deduction').forEach(e => e.classList.remove('active'));
    // Show selected tab content
    const contentEl = document.getElementById(sec + '-' + tab);
    if (contentEl) contentEl.classList.add('active');
    // Remove active class from all tab buttons
    document.querySelectorAll('.tabs .tab').forEach(e => e.classList.remove('active'));
    // Add active class to the clicked button (find by matching tab text or data attribute)
    const tabButtons = document.querySelectorAll('.tabs .tab');
    tabButtons.forEach(btn => {
        if (btn.textContent.toLowerCase().includes(tab.replace('-', ' ').toLowerCase())) {
            btn.classList.add('active');
        }
    });
    window.history.replaceState({}, '', '?tab=' + tab);
}
</script>

</body>
</html>
