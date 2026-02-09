<?php
/**
 * Incentives & Bonuses - Modern Design
 * Log workload-based earnings with approval workflow
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/IncentiveType.php';
require_once __DIR__ . '/../models/IncentiveEvent.php';

$typeModel = new IncentiveType();
$eventModel = new IncentiveEvent();

$events = $eventModel->getAllWithDetails([]);
$approvedTypes = $typeModel->getApproved();
$employees = $eventModel->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);

$handlerUrl = 'modules/compensation/incentives_bonus_handler.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Incentives &amp; Bonuses</title>
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
.table .num { text-align: right; }

.status-badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.status-pending { background: #fef3c7; color: #b45309; }
.status-approved { background: #d1fae5; color: #065f46; }

.dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
.dot-pending { background: #f59e0b; }
.dot-approved { background: #10b981; }

.btn { padding: 6px 12px; font-size: 12px; font-weight: 500; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 4px; cursor: pointer; height: 28px; display: inline-flex; align-items: center; }
.btn:hover { border-color: #9ca3af; background: #f9fafb; }
.btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.btn-primary:hover { background: #1c3aa0; }
.btn-success { background: #10b981; color: #fff; border-color: #10b981; }
.btn-success:hover { background: #059669; }
.btn-sm { padding: 4px 8px; font-size: 11px; height: 24px; }

.empty-state { padding: 24px 8px; color: #9ca3af; font-size: 12px; }

.add-form { display: none; background: #f9fafb; padding: 12px; border: 1px solid #e5e7eb; border-radius: 4px; margin-bottom: 8px; }
.add-form.visible { display: block; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.form-row.full { grid-template-columns: 1fr; }
.form-group { display: flex; flex-direction: column; gap: 3px; }
.form-label { font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
.required { color: #ef4444; }

.form-input, .form-select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; font-family: inherit; color: #1f2937; height: 30px; }
.form-input:focus, .form-select:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.1); }

.form-actions { display: flex; gap: 6px; margin-top: 8px; }

.action-icon { cursor: pointer; text-align: center; }
.action-icon button { background: none; border: none; color: #374151; cursor: pointer; font-size: 14px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
.action-icon button:hover { color: #1f2937; }

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
        <h1 class="title">Incentives &amp; Bonuses</h1>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        Only approved procedure types allowed. Each event requires approval before payroll processing.
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

        <!-- 1. INCENTIVE EVENTS TABLE (Main View) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Incentive Events</div>
            </div>

            <?php if (empty($events)): ?>
            <div class="empty-state">No incentive events yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Procedure</th>
                        <th class="num">Qty</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $e): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(($e['last_name'] ?? '') . ', ' . ($e['first_name'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($e['procedure_name'] ?? $e['procedure_code'] ?? '—'); ?></td>
                        <td class="num"><?php echo (int)$e['quantity']; ?></td>
                        <td><?php echo htmlspecialchars($e['duty_date']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($e['status']); ?>">
                                <span class="dot dot-<?php echo strtolower($e['status']); ?>"></span>
                                <?php echo htmlspecialchars($e['status']); ?>
                            </span>
                        </td>
                        <td class="action-icon">
                            <?php if (strtolower($e['status']) === 'pending'): ?>
                            <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?php echo (int)$e['id']; ?>">
                                <button type="submit" title="Approve">✓</button>
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

        <!-- 2. LOG EVENT FORM (Collapsed) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Log Event</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-event-form'); return false;">+ Log event</button>
            </div>

            <div id="add-event-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create">

                    <!-- Top row: Employee, Procedure -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Employee <span class="required">•</span></label>
                            <select name="employee_id" required class="form-select">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo (int)$emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['employee_code'] . ' – ' . $emp['last_name'] . ', ' . $emp['first_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Procedure <span class="required">•</span></label>
                            <select name="procedure_type_id" required class="form-select">
                                <option value="">Select Procedure</option>
                                <?php foreach ($approvedTypes as $t): ?>
                                <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['code'] . ' – ' . $t['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Second row: Duty Date, Quantity -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Duty Date <span class="required">•</span></label>
                            <input type="date" name="duty_date" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantity <span class="required">•</span></label>
                            <input type="number" name="quantity" required class="form-input" value="1" min="1">
                        </div>
                    </div>

                    <div class="form-actions" style="justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary btn-sm">Log. Pending approval</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-event-form'); return false;">Cancel</button>
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
</script>

</body>
</html>
