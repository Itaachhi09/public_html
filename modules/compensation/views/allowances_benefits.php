<?php
/**
 * Allowances and Benefits - Modern Design
 * Manage non-salary compensation benefits
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/BenefitDefinition.php';

$benefitModel = new BenefitDefinition();
$benefits = $benefitModel->getAll(false);

$handlerUrl = 'modules/compensation/allowances_benefits_handler.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Allowances & Benefits</title>
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
.header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; }
.title { font-size: 17px; font-weight: 600; color: #111827; }

.info-row { padding: 8px 24px; color: #6b7280; font-size: 11px; border-bottom: 1px solid #e5e7eb; }

.content { max-width: 1080px; }
.msg-bar { padding: 12px 24px; display: flex; gap: 8px; }
.msg { background: #d1fae5; border-left: 3px solid #10b981; color: #065f46; padding: 8px 12px; border-radius: 3px; font-size: 12px; flex: 1; }
.err { background: #fee2e2; border-left: 3px solid #ef4444; color: #991b1b; padding: 8px 12px; border-radius: 3px; font-size: 12px; flex: 1; }

.section { padding: 12px 24px; border-bottom: 1px solid #e5e7eb; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.section-title { font-size: 14px; font-weight: 600; color: #111827; }

.table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 8px; }
.table th { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; height: 28px; }
.table td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; height: 30px; vertical-align: middle; }
.table tbody tr:hover { background: #f9fafb; }
.table .code { font-family: 'Courier New', monospace; background: #f3f4f6; padding: 2px 4px; border-radius: 2px; font-size: 11px; }

.badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; display: inline-block; }
.badge-active { background: #d1fae5; color: #065f46; }
.badge-inactive { background: #f3f4f6; color: #6b7280; }

.btn { padding: 6px 12px; font-size: 12px; font-weight: 500; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 4px; cursor: pointer; height: 28px; display: inline-flex; align-items: center; }
.btn:hover { border-color: #9ca3af; background: #f9fafb; }
.btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.btn-primary:hover { background: #1c3aa0; }
.btn-sm { padding: 4px 8px; font-size: 11px; height: 24px; }

.empty-state { padding: 24px 8px; color: #9ca3af; font-size: 12px; }

.add-form { display: none; background: #f9fafb; padding: 12px; border: 1px solid #e5e7eb; border-radius: 4px; margin-bottom: 8px; }
.add-form.visible { display: block; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.form-row.full { grid-template-columns: 1fr; }
.form-group { display: flex; flex-direction: column; gap: 3px; }
.form-label { font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
.required { color: #ef4444; }

.form-input, .form-select, .form-textarea { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; font-family: inherit; color: #1f2937; height: 30px; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.1); }
.form-textarea { height: auto; min-height: 50px; max-height: 50px; resize: none; }

.form-actions { display: flex; gap: 6px; margin-top: 8px; justify-content: flex-end; }

.checkbox-inline { display: flex; align-items: center; gap: 6px; font-size: 11px; }
.checkbox-inline input { width: auto; cursor: pointer; }

.role-list { position: relative; cursor: help; }
.role-tooltip { display: none; position: absolute; background: #1f2937; color: #fff; padding: 6px 8px; border-radius: 3px; font-size: 10px; z-index: 100; white-space: nowrap; bottom: 120%; left: 50%; transform: translateX(-50%); }
.role-list:hover .role-tooltip { display: block; }

.action-icon { cursor: pointer; text-align: center; }
.action-icon button { background: none; border: none; color: #374151; cursor: pointer; font-size: 14px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
.action-icon button:hover { color: #1f2937; }

.lock-icon { font-size: 11px; color: #6b7280; }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .section { padding: 12px 16px; }
}
</style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <h1 class="title">Allowances &amp; Benefits</h1>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        Benefits attach to duty or role. Payroll reads as reference only.
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

        <!-- 1. BENEFIT DEFINITIONS TABLE (Primary Focus) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Benefit Definitions</div>
            </div>

            <?php if (empty($benefits)): ?>
            <div class="empty-state">No benefits defined yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Tax</th>
                        <th>Roles</th>
                        <th>Period</th>
                        <th>Attach</th>
                        <th>Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($benefits as $b): 
                        $roles = $b['eligible_roles'] ?? 'All';
                        $roleParts = explode(',', $roles);
                        $firstRole = trim($roleParts[0]);
                        $moreCount = count($roleParts) - 1;
                    ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($b['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($b['name']); ?></td>
                        <td><?php echo !empty($b['taxable']) ? 'Yes' : 'No'; ?></td>
                        <td>
                            <?php if ($moreCount > 0): ?>
                            <span class="role-list">
                                <?php echo htmlspecialchars($firstRole); ?> <strong>+<?php echo $moreCount; ?></strong>
                                <div class="role-tooltip"><?php echo htmlspecialchars($roles); ?></div>
                            </span>
                            <?php else: ?>
                            <?php echo htmlspecialchars($firstRole); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php 
                            $period = htmlspecialchars($b['effective_from']);
                            if (!empty($b['effective_to'])) {
                                $period .= ' – ' . htmlspecialchars($b['effective_to']);
                            }
                            echo $period;
                        ?></td>
                        <td><?php echo htmlspecialchars(strlen($b['attach_to']) > 4 ? substr($b['attach_to'], 0, 4) : $b['attach_to']); ?></td>
                        <td><span class="badge badge-<?php echo !empty($b['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($b['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
                        <td class="action-icon">
                            <?php if (!empty($b['is_active'])): ?>
                            <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="deactivate">
                                <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                                <button type="submit" title="Deactivate">−</button>
                            </form>
                            <?php else: ?>
                            —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- 2. DEFINE BENEFIT FORM (Collapsed) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Define Benefit</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-benefit-form'); return false;">+ Add benefit</button>
            </div>

            <div id="add-benefit-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create">

                    <!-- Top row: Code, Name -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Code <span class="required">•</span></label>
                            <input type="text" name="code" required class="form-input" placeholder="FREE_MEALS" maxlength="50">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name <span class="required">•</span></label>
                            <input type="text" name="name" required class="form-input" placeholder="Free Meals Allowance" maxlength="255">
                        </div>
                    </div>

                    <!-- Second row: Attach to, Eligible roles -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Attach To <span class="required">•</span></label>
                            <select name="attach_to" required class="form-select" onchange="toggleRoleField()">
                                <option value="">Select</option>
                                <option value="duty">Duty</option>
                                <option value="role">Role</option>
                            </select>
                        </div>
                        <div class="form-group" id="roles-field">
                            <label class="form-label">Eligible Roles</label>
                            <input type="text" name="eligible_roles" class="form-input" placeholder="Nurse, Doctor or All" maxlength="500" value="All">
                        </div>
                    </div>

                    <!-- Third row: Effective from, Effective to -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Effective From <span class="required">•</span></label>
                            <input type="date" name="effective_from" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Effective To</label>
                            <input type="date" name="effective_to" class="form-input">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-textarea" placeholder="Free meals three times daily on duty"></textarea>
                        </div>
                    </div>

                    <!-- Taxable checkbox inline -->
                    <div class="form-row full">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="taxable" value="1">
                            Taxable
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-benefit-form'); return false;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<script>
function toggleForm(id) {
    document.getElementById(id).classList.toggle('visible');
}

function toggleRoleField() {
    const attachTo = document.querySelector('select[name="attach_to"]').value;
    const rolesField = document.getElementById('roles-field');
    rolesField.style.display = (attachTo === 'duty') ? 'none' : 'flex';
}
</script>

</body>
</html>
