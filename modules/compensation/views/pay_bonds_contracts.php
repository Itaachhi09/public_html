<?php
/**
 * Pay Bonds & Contracts - Modern Design
 * Manage pay contracts and employee assignments
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayContract.php';
require_once __DIR__ . '/../models/EmployeeContractAssignment.php';
require_once __DIR__ . '/../models/PayGrade.php';

$contractModel = new PayContract();
$assignmentModel = new EmployeeContractAssignment();
$payGradeModel = new PayGrade();

$contracts = $contractModel->getAllWithGrade(false);
$assignments = $assignmentModel->getAllWithDetails([]);
$activeContracts = $contractModel->getActive();
$payGrades = $payGradeModel->getAllWithBands(false);
$employees = $assignmentModel->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);

$handlerUrl = 'modules/compensation/pay_bonds_contracts_handler.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pay Bonds &amp; Contracts</title>
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

.form-input, .form-select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; font-family: inherit; color: #1f2937; height: 30px; }
.form-input:focus, .form-select:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.1); }

.form-actions { display: flex; gap: 6px; margin-top: 8px; justify-content: flex-end; }

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
        <h1 class="title">Pay Bonds &amp; Contracts</h1>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        Active contract required for compensation. Expired contracts block new entries.
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

        <!-- 1. PAY CONTRACTS (Primary Focus) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Pay Contracts</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-contract-form'); return false;">+ New Contract</button>
            </div>

            <?php if (empty($contracts)): ?>
            <div class="empty-state">No pay contracts yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Pay Grade</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $c): 
                        $isActive = $c['end_date'] && $c['end_date'] >= date('Y-m-d');
                        $period = htmlspecialchars($c['start_date']) . ' to ' . htmlspecialchars($c['end_date']);
                    ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($c['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td><?php echo htmlspecialchars($c['pay_grade_name']); ?></td>
                        <td><?php echo $period; ?></td>
                        <td><span class="badge badge-<?php echo $isActive ? 'active' : 'inactive'; ?>"><?php echo $isActive ? 'Active' : 'Expired'; ?></span></td>
                        <td class="action-icon">
                            <?php if ($isActive): ?>
                            <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="deactivate_contract">
                                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
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

            <!-- Create Contract Form -->
            <div id="add-contract-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_contract">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Code <span class="required">•</span></label>
                            <input type="text" name="code" required class="form-input" placeholder="ER_DOCTOR_CONTRACT" maxlength="50">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Name <span class="required">•</span></label>
                            <input type="text" name="name" required class="form-input" placeholder="ER Doctor Contract" maxlength="255">
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Pay Grade <span class="required">•</span></label>
                            <select name="pay_grade_id" required class="form-select">
                                <option value="">Select Pay Grade</option>
                                <?php foreach ($payGrades as $pg): ?>
                                <option value="<?php echo (int)$pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Start Date <span class="required">•</span></label>
                            <input type="date" name="start_date" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date <span class="required">•</span></label>
                            <input type="date" name="end_date" required class="form-input">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-contract-form'); return false;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. EMPLOYEE ASSIGNMENTS (Operational View) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Employee Assignments</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-assignment-form'); return false;">+ Assign</button>
            </div>

            <?php if (empty($assignments)): ?>
            <div class="empty-state">No assignments yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Contract</th>
                        <th>Period</th>
                        <th>Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $a): 
                        $assignmentActive = (!$a['effective_to'] || $a['effective_to'] >= date('Y-m-d')) && $a['contract_end'] && $a['contract_end'] >= date('Y-m-d');
                        $period = htmlspecialchars($a['effective_from']) . ' to ' . ($a['effective_to'] ? htmlspecialchars($a['effective_to']) : '∞');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars(($a['last_name'] ?? '') . ', ' . ($a['first_name'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($a['contract_name']); ?></td>
                        <td><?php echo $period; ?></td>
                        <td><span class="badge badge-<?php echo $assignmentActive ? 'active' : 'inactive'; ?>"><?php echo $assignmentActive ? 'Active' : 'Ended'; ?></span></td>
                        <td class="action-icon">
                            <?php if ($assignmentActive && !$a['effective_to']): ?>
                            <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="end_assignment">
                                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                                <input type="hidden" name="effective_to" value="<?php echo date('Y-m-d'); ?>">
                                <button type="submit" title="End">−</button>
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

            <!-- Assign Employee Form -->
            <div id="add-assignment-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="assign_employee">

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
                            <label class="form-label">Contract <span class="required">•</span></label>
                            <select name="contract_id" required class="form-select">
                                <option value="">Select Contract</option>
                                <?php foreach ($activeContracts as $c): ?>
                                <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($activeContracts)): ?>
                                <option value="" disabled>No active contracts</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Effective From <span class="required">•</span></label>
                            <input type="date" name="effective_from" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Effective To (optional)</label>
                            <input type="date" name="effective_to" class="form-input">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-assignment-form'); return false;">Cancel</button>
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
