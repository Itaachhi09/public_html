<?php
/**
 * Compensation Structure - Enhanced Modern Design
 * Inspired by HR CORE with KPI cards, better layout, and modern styling
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

// Calculate statistics for KPI cards
$baseCompCount = count(array_filter($salaryComponents, fn($c) => $c['component_type'] === 'base'));
$allowanceCount = count(array_filter($salaryComponents, fn($c) => $c['component_type'] === 'allowance'));
$deductionCount = count(array_filter($salaryComponents, fn($c) => $c['component_type'] === 'deduction'));
$totalSalaryComps = count($salaryComponents);
$activeIncentives = count(array_filter($incentiveComponents, fn($i) => ($i['status'] ?? 'active') === 'active'));
$activeBenefits = count(array_filter($benefits, fn($b) => !empty($b['is_active'])));

$handlerUrl = 'modules/compensation/compensation_structure_handler.php';
$currentTab = $_GET['tab'] ?? 'base';
$pageTitle = 'Compensation Structure';
require __DIR__ . '/partials/header.php';

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
</head>
<body>
<main class="main-content">
    <div style="padding: 0 24px; margin: 12px 0 8px 0;">
        <a class="context-link" onclick="alert('Organization: Primary Hospital 30 beds ER focused. Last updated: ' + new Date().toDateString()); return false;">Context</a>
    </div>

    <!-- Messages -->
    <?php if (!empty($_GET['msg']) || !empty($_GET['err'])): ?>
    <div class="msg-bar" style="margin: 12px 24px 0;">
        <?php if (!empty($_GET['msg'])): ?>
        <div class="msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['err'])): ?>
        <div class="err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="content" style="padding: 16px 24px;">
        
        <!-- KPI Cards Section -->
        <section class="kpi-section">
            <div class="kpi-grid">
                <div class="kpi-card kpi-card-total">
                    <div class="kpi-icon">💼</div>
                    <div class="kpi-content">
                        <p class="kpi-label">Salary Components</p>
                        <h2 class="kpi-value"><?php echo $totalSalaryComps; ?></h2>
                        <p class="kpi-description"><?php echo $totalSalaryComps > 0 ? 'Active setup' : 'None defined'; ?></p>
                    </div>
                </div>

                <div class="kpi-card kpi-card-salary">
                    <div class="kpi-icon">📊</div>
                    <div class="kpi-content">
                        <p class="kpi-label">Base + Allowances</p>
                        <h2 class="kpi-value"><?php echo ($baseCompCount + $allowanceCount); ?></h2>
                        <p class="kpi-description"><?php echo $baseCompCount; ?> base • <?php echo $allowanceCount; ?> allow</p>
                    </div>
                </div>

                <div class="kpi-card kpi-card-incentive">
                    <div class="kpi-icon">🎯</div>
                    <div class="kpi-content">
                        <p class="kpi-label">Incentive Types</p>
                        <h2 class="kpi-value"><?php echo count($incentiveComponents); ?></h2>
                        <p class="kpi-description"><?php echo $activeIncentives; ?> active</p>
                    </div>
                </div>

                <div class="kpi-card kpi-card-benefit">
                    <div class="kpi-icon">🎁</div>
                    <div class="kpi-content">
                        <p class="kpi-label">Benefits</p>
                        <h2 class="kpi-value"><?php echo count($benefits); ?></h2>
                        <p class="kpi-description"><?php echo $activeBenefits; ?> active</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Search & Filters Section -->
        <section class="filters-section">
            <div class="card">
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: end;">
                    <div class="form-group">
                        <label class="form-label">Search</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;">🔍</span>
                            <input type="text" id="comp-search" class="form-input" placeholder="Code, name, description..." style="padding-left: 36px;">
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-outline btn-sm" onclick="performCompSearch()" title="Search components">Search</button>
                        <button class="btn btn-outline btn-sm" onclick="resetCompSearch()" title="Reset search">↻ Reset</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 1. SALARY COMPONENTS SECTION -->
        <section class="table-section">
            <div class="section-card">
                <div class="section-card-header">
                    <div>
                        <h3 class="section-card-title">Salary Components</h3>
                        <p class="section-card-subtitle">Define salary structure elements (Base Pay, Allowances, Deductions)</p>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="toggleForm('add-salary-form'); return false;">+ Add Component</button>
                </div>

                <!-- Salary Components Tabs -->
                <div class="tabs">
                    <button class="tab <?php echo $currentTab === 'base' ? 'active' : ''; ?>" onclick="switchTab('base', 'salary', this); return false;">💰 Basic Pay (<?php echo $baseCompCount; ?>)</button>
                    <button class="tab <?php echo $currentTab === 'allowance' ? 'active' : ''; ?>" onclick="switchTab('allowance', 'salary', this); return false;">➕ Allowances (<?php echo $allowanceCount; ?>)</button>
                    <button class="tab <?php echo $currentTab === 'deduction' ? 'active' : ''; ?>" onclick="switchTab('deduction', 'salary', this); return false;">➖ Deductions (<?php echo $deductionCount; ?>)</button>
                </div>

                <!-- Tab: Base -->
                <div id="salary-base" class="tab-content <?php echo $currentTab === 'base' ? 'active' : ''; ?>">
                    <?php 
                    $baseComps = array_filter($salaryComponents, fn($c) => $c['component_type'] === 'base');
                    if (empty($baseComps)): 
                    ?>
                    <div class="empty-state">No base pay components defined. Add your first component to get started.</div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-head-fixed">
                                <tr>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 25%;">Name</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 10%;">Taxable</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 5%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($baseComps as $s): ?>
                            <tr>
                                <td><span class="code"><?php echo htmlspecialchars($s['code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars(substr($s['description'] ?? '', 0, 60)); ?><?php if (strlen($s['description'] ?? '') > 60) echo '...'; ?></td>
                                <td><?php echo !empty($s['taxable']) ? '<span style="color: #10b981;">✓ Yes</span>' : '<span style="color: #9ca3af;">✗ No</span>'; ?></td>
                                <td><span class="badge badge-<?php echo !empty($s['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                                <td style="text-align: center;"><?php if (!empty($s['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate this component?');"><input type="hidden" name="action" value="deactivate_salary"><input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon" title="Deactivate">–</button></form><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab: Allowance -->
                <div id="salary-allowance" class="tab-content <?php echo $currentTab === 'allowance' ? 'active' : ''; ?>">
                    <?php 
                    $allowComps = array_filter($salaryComponents, fn($c) => $c['component_type'] === 'allowance');
                    if (empty($allowComps)): 
                    ?>
                    <div class="empty-state">No allowance components defined. Add your first component to get started.</div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-head-fixed">
                                <tr>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 25%;">Name</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 10%;">Taxable</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 5%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($allowComps as $s): ?>
                            <tr>
                                <td><span class="code"><?php echo htmlspecialchars($s['code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars(substr($s['description'] ?? '', 0, 60)); ?><?php if (strlen($s['description'] ?? '') > 60) echo '...'; ?></td>
                                <td><?php echo !empty($s['taxable']) ? '<span style="color: #10b981;">✓ Yes</span>' : '<span style="color: #9ca3af;">✗ No</span>'; ?></td>
                                <td><span class="badge badge-<?php echo !empty($s['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                                <td style="text-align: center;"><?php if (!empty($s['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate this component?');"><input type="hidden" name="action" value="deactivate_salary"><input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon" title="Deactivate">–</button></form><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab: Deduction -->
                <div id="salary-deduction" class="tab-content <?php echo $currentTab === 'deduction' ? 'active' : ''; ?>">
                    <?php 
                    $dedComps = array_filter($salaryComponents, fn($c) => $c['component_type'] === 'deduction');
                    if (empty($dedComps)): 
                    ?>
                    <div class="empty-state">No deduction components defined. Add your first component to get started.</div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-head-fixed">
                                <tr>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 25%;">Name</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 10%;">Taxable</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 5%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($dedComps as $s): ?>
                            <tr>
                                <td><span class="code"><?php echo htmlspecialchars($s['code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                                <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars(substr($s['description'] ?? '', 0, 60)); ?><?php if (strlen($s['description'] ?? '') > 60) echo '...'; ?></td>
                                <td><?php echo !empty($s['taxable']) ? '<span style="color: #10b981;">✓ Yes</span>' : '<span style="color: #9ca3af;">✗ No</span>'; ?></td>
                                <td><span class="badge badge-<?php echo !empty($s['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                                <td style="text-align: center;"><?php if (!empty($s['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate this component?');"><input type="hidden" name="action" value="deactivate_salary"><input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon" title="Deactivate">–</button></form><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Add Form -->
                <div id="add-salary-form" class="add-form">
                    <h4 style="margin: 0 0 12px 0; font-size: 13px; font-weight: 600; color: #111827;">New Salary Component</h4>
                    <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                        <input type="hidden" name="action" value="create_salary_component">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Code <span class="required">*</span></label>
                                <input type="text" name="code" required class="form-input" placeholder="BASE_PAY" maxlength="50">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Name <span class="required">*</span></label>
                                <input type="text" name="name" required class="form-input" placeholder="Base Pay" maxlength="255">
                            </div>
                        </div>
                        <div class="form-row full">
                            <div class="form-group">
                                <label class="form-label">Description <span class="required">*</span></label>
                                <textarea name="description" required class="form-textarea" placeholder="Describe this component..." maxlength="500" style="min-height: 60px; resize: vertical;"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <select name="component_type" class="form-select">
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
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" style="display: flex; align-items: center; gap: 4px;">
                                    <input type="checkbox" id="sal-tax" name="taxable" value="1" style="width: auto; height: auto; margin: 0;">
                                    <span>Taxable</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-sm">Create Component</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleForm('add-salary-form'); return false;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>


        <!-- 2. INCENTIVE COMPONENTS SECTION -->
        <section class="table-section">
            <div class="section-card">
                <div class="section-card-header">
                    <div>
                        <h3 class="section-card-title">Incentive Components</h3>
                        <p class="section-card-subtitle">Define incentive types and calculations</p>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="toggleForm('add-incentive-form'); return false;">+ Add Incentive</button>
                </div>

                <!-- Incentive Tabs -->
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('active-inc', 'incentive', this); return false;">🎯 Active (<?php echo $activeIncentives; ?>)</button>
                    <button class="tab" onclick="switchTab('all-inc', 'incentive', this); return false;">📋 All (<?php echo count($incentiveComponents); ?>)</button>
                </div>

                <!-- Tab: Active Incentives -->
                <div id="incentive-active-inc" class="tab-content active">
                    <?php 
                    $activeInc = array_filter($incentiveComponents, fn($i) => ($i['status'] ?? 'active') === 'active');
                    if (empty($activeInc)): 
                    ?>
                    <div class="empty-state">No active incentive components. Add one to get started.</div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-head-fixed">
                                <tr>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 25%;">Name</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 20%;">Rate Type</th>
                                    <th style="width: 8%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($activeInc as $i): ?>
                            <tr>
                                <td><span class="code"><?php echo htmlspecialchars($i['code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($i['name']); ?></strong></td>
                                <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars(substr($i['description'] ?? '', 0, 50)); ?><?php if (strlen($i['description'] ?? '') > 50) echo '...'; ?></td>
                                <td><span style="background: #f0f9ff; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $i['rate_type'] ?? 'Per Case'))); ?></span></td>
                                <td style="text-align: center;"><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate this incentive?');"><input type="hidden" name="action" value="deactivate_incentive"><input type="hidden" name="id" value="<?php echo (int)$i['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon" title="Deactivate">–</button></form></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab: All Incentives -->
                <div id="incentive-all-inc" class="tab-content">
                    <?php if (empty($incentiveComponents)): ?>
                    <div class="empty-state">No incentive components defined. Add your first incentive type to get started.</div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-head-fixed">
                                <tr>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 25%;">Name</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 15%;">Rate Type</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 8%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($incentiveComponents as $i): 
                                $status = $i['status'] ?? 'active';
                                $statusClass = $status === 'archived' ? 'archived' : ($status === 'inactive' ? 'inactive' : 'active');
                            ?>
                            <tr>
                                <td><span class="code"><?php echo htmlspecialchars($i['code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($i['name']); ?></strong></td>
                                <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars(substr($i['description'] ?? '', 0, 50)); ?><?php if (strlen($i['description'] ?? '') > 50) echo '...'; ?></td>
                                <td><span style="background: #f0f9ff; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $i['rate_type'] ?? 'Per Case'))); ?></span></td>
                                <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span></td>
                                <td style="text-align: center;"><?php if ($status === 'active'): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate this incentive?');"><input type="hidden" name="action" value="deactivate_incentive"><input type="hidden" name="id" value="<?php echo (int)$i['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon" title="Deactivate">–</button></form><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Add Form -->
                <div id="add-incentive-form" class="add-form">
                    <h4 style="margin: 0 0 12px 0; font-size: 13px; font-weight: 600; color: #111827;">New Incentive Component</h4>
                    <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                        <input type="hidden" name="action" value="create_incentive_component">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Code <span class="required">*</span></label>
                                <input type="text" name="code" required class="form-input" placeholder="ADMISSION" maxlength="50">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Name <span class="required">*</span></label>
                                <input type="text" name="name" required class="form-input" placeholder="Admission Incentive" maxlength="255">
                            </div>
                        </div>
                        <div class="form-row full">
                            <div class="form-group">
                                <label class="form-label">Description <span class="required">*</span></label>
                                <textarea name="description" required class="form-textarea" placeholder="Describe this incentive..." style="min-height: 60px; resize: vertical;"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Rate Type</label>
                                <select name="rate_type" class="form-select">
                                    <?php
                                    $ropts = getEnumOptions('incentive_types', 'rate_type');
                                    if (!empty($ropts)) {
                                        foreach ($ropts as $r) {
                                            echo '<option value="' . htmlspecialchars($r) . '">' . htmlspecialchars(ucwords(str_replace('_',' ',$r))) . '</option>';
                                        }
                                    } else {
                                        ?>
                                        <option value="per_case" selected>Per Case</option>
                                        <option value="fixed_amount">Fixed Amount</option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Default Rate (₱)</label>
                                <input type="number" name="default_rate" class="form-input" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-sm">Create Incentive</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleForm('add-incentive-form'); return false;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- 3. BENEFITS SECTION -->
        <section class="table-section">
            <div class="section-card">
                <div class="section-card-header">
                    <div>
                        <h3 class="section-card-title">Benefits</h3>
                        <p class="section-card-subtitle">Define benefits available to employees</p>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="toggleForm('add-benefit-form'); return false;">+ Add Benefit</button>
                </div>

                <!-- Benefits Tabs -->
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('active-ben', 'benefit', this); return false;">✓ Active (<?php echo $activeBenefits; ?>)</button>
                    <button class="tab" onclick="switchTab('all-ben', 'benefit', this); return false;">📋 All (<?php echo count($benefits); ?>)</button>
                </div>

                <!-- Tab: Active Benefits -->
                <div id="benefit-active-ben" class="tab-content active">
                    <?php 
                    $activeBen = array_filter($benefits, fn($b) => !empty($b['is_active']));
                    if (empty($activeBen)): 
                    ?>
                    <div class="empty-state">No active benefits defined. Add one to get started.</div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-head-fixed">
                                <tr>
                                    <th style="width: 12%;">Code</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 12%;">Category</th>
                                    <th style="width: 10%;">Taxable</th>
                                    <th style="width: 8%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($activeBen as $b): ?>
                            <tr>
                                <td><span class="code"><?php echo htmlspecialchars($b['code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($b['name']); ?></strong></td>
                                <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars(substr($b['description'] ?? '', 0, 50)); ?><?php if (strlen($b['description'] ?? '') > 50) echo '...'; ?></td>
                                <td><span style="background: #f3e8ff; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $b['benefit_category'] ?? 'Non Cash'))); ?></span></td>
                                <td><?php echo !empty($b['taxable']) ? '<span style="color: #10b981;">✓ Yes</span>' : '<span style="color: #9ca3af;">✗ No</span>'; ?></td>
                                <td style="text-align: center;"><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate this benefit?');"><input type="hidden" name="action" value="deactivate_benefit"><input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon" title="Deactivate">–</button></form></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Tab: All Benefits -->
                <div id="benefit-all-ben" class="tab-content">
                    <?php if (empty($benefits)): ?>
                    <div class="empty-state">No benefits defined. Add your first benefit to get started.</div>
                    <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead class="table-head-fixed">
                                <tr>
                                    <th style="width: 12%;">Code</th>
                                    <th style="width: 20%;">Name</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 12%;">Category</th>
                                    <th style="width: 10%;">Taxable</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 9%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($benefits as $b): ?>
                            <tr>
                                <td><span class="code"><?php echo htmlspecialchars($b['code']); ?></span></td>
                                <td><strong><?php echo htmlspecialchars($b['name']); ?></strong></td>
                                <td style="font-size: 12px; color: #6b7280;"><?php echo htmlspecialchars(substr($b['description'] ?? '', 0, 50)); ?><?php if (strlen($b['description'] ?? '') > 50) echo '...'; ?></td>
                                <td><span style="background: #f3e8ff; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $b['benefit_category'] ?? 'Non Cash'))); ?></span></td>
                                <td><?php echo !empty($b['taxable']) ? '<span style="color: #10b981;">✓ Yes</span>' : '<span style="color: #9ca3af;">✗ No</span>'; ?></td>
                                <td><span class="badge badge-<?php echo !empty($b['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($b['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                                <td style="text-align: center;"><?php if (!empty($b['is_active'])): ?><form class="inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return confirm('Deactivate this benefit?');"><input type="hidden" name="action" value="deactivate_benefit"><input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>"><input type="hidden" name="reason" value="Deactivated"><button type="submit" class="btn btn-sm btn-icon" title="Deactivate">–</button></form><?php endif; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Add Form -->
                <div id="add-benefit-form" class="add-form">
                    <h4 style="margin: 0 0 12px 0; font-size: 13px; font-weight: 600; color: #111827;">New Benefit</h4>
                    <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                        <input type="hidden" name="action" value="create_benefit">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Code <span class="required">*</span></label>
                                <input type="text" name="code" required class="form-input" placeholder="FREE_MEALS_TID" maxlength="50">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Name <span class="required">*</span></label>
                                <input type="text" name="name" required class="form-input" placeholder="Free Meals" maxlength="255">
                            </div>
                        </div>
                        <div class="form-row full">
                            <div class="form-group">
                                <label class="form-label">Description <span class="required">*</span></label>
                                <textarea name="description" required class="form-textarea" placeholder="Describe this benefit..." style="min-height: 60px; resize: vertical;"></textarea>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="benefit_category" class="form-select">
                                    <option value="non_cash" selected>Non-Cash</option>
                                    <option value="cash_equivalent">Cash Equivalent</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Payroll Impact</label>
                                <select name="payroll_impact" class="form-select">
                                    <option value="informational" selected>Informational</option>
                                    <option value="included_in_payroll">Included in Payroll</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Effective From <span class="required">*</span></label>
                                <input type="date" name="effective_from" required class="form-input">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Effective To</label>
                                <input type="date" name="effective_to" class="form-input">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" style="display: flex; align-items: center; gap: 4px;">
                                    <input type="checkbox" id="ben-tax" name="taxable" value="1" style="width: auto; height: auto; margin: 0;">
                                    <span>Taxable</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Attach To</label>
                                <select name="attach_to" class="form-select">
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
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-sm">Create Benefit</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleForm('add-benefit-form'); return false;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

    </div>
</main>

<script>
// Toggle form visibility
function toggleForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('visible');
        if (form.classList.contains('visible')) {
            setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
        }
    }
}

// Switch between tabs
function switchTab(tab, sec, button) {
    // Hide all tab content in this section
    if (sec === 'salary') {
        document.querySelectorAll('#salary-base, #salary-allowance, #salary-deduction').forEach(e => {
            e.classList.remove('active');
            e.style.display = 'none';
        });
    } else if (sec === 'incentive') {
        document.querySelectorAll('#incentive-active-inc, #incentive-all-inc').forEach(e => {
            e.classList.remove('active');
            e.style.display = 'none';
        });
    } else if (sec === 'benefit') {
        document.querySelectorAll('#benefit-active-ben, #benefit-all-ben').forEach(e => {
            e.classList.remove('active');
            e.style.display = 'none';
        });
    }
    
    // Show selected tab content
    const contentEl = document.getElementById(sec + '-' + tab);
    if (contentEl) {
        contentEl.classList.add('active');
        contentEl.style.display = 'block';
    }
    
    // Style buttons - remove active from all in same tabs container, add to clicked button
    if (button && button.parentElement) {
        button.parentElement.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
        button.classList.add('active');
    }
    
    window.history.replaceState({}, '', '?tab=' + tab);
}

// Search compensation components
function performCompSearch() {
    var q = document.getElementById('comp-search')?.value || '';
    var params = new URLSearchParams(window.location.search);
    if (q) {
        params.set('q', q);
    } else {
        params.delete('q');
    }
    window.location.search = params.toString();
}

// Reset search
function resetCompSearch() {
    document.getElementById('comp-search').value = '';
    var params = new URLSearchParams(window.location.search);
    params.delete('q');
    window.location.search = params.toString();
}

// Enter key on search
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('comp-search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performCompSearch();
            }
        });
    }
    
    // Initialize tab visibility
    function initializeTabs() {
        // Hide all tab-content divs
        document.querySelectorAll('.tab-content').forEach(el => {
            el.style.display = 'none';
        });
        
        // Show active tab-content divs
        document.querySelectorAll('.tab-content.active').forEach(el => {
            el.style.display = 'block';
        });
    }
    
    initializeTabs();
});

// Enhanced table functionality
;(function() {
    try {
        // Add smooth hover effects to rows
        const tables = document.querySelectorAll('table.table');
        tables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.zIndex = '1';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.zIndex = 'auto';
                });
            });
        });
    } catch (e) {
        console.error('Table enhancement error:', e);
    }
})();
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>
