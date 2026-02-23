<?php
/**
 * Salary Adjustment - Modern Design
 * Controlled changes to base pay with approval workflow
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/SalaryAdjustment.php';
require_once __DIR__ . '/../models/SalaryBand.php';
require_once __DIR__ . '/../../../config/currency.php';

$adjustmentModel = new SalaryAdjustment();
$bandModel = new SalaryBand();

$adjustments = $adjustmentModel->getAllWithDetails([]);
$bands = $bandModel->getAllWithDetails(false);
$employees = $adjustmentModel->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);

$handlerUrl = 'modules/compensation/salary_adjustment_handler.php';

// Helper: read enum values from information_schema for a given table.column
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
<title>Salary Adjustment</title>
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
.table .code { font-family: 'Courier New', monospace; background: #f3f4f6; padding: 2px 4px; border-radius: 2px; font-size: 11px; }

.status-badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.status-pending { background: #fef3c7; color: #b45309; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
.dot-pending { background: #b45309; }
.dot-approved { background: #10b981; }
.dot-rejected { background: #ef4444; }

.btn { padding: 6px 12px; font-size: 12px; font-weight: 500; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 4px; cursor: pointer; height: 28px; display: inline-flex; align-items: center; }
.btn:hover { border-color: #9ca3af; background: #f9fafb; }
.btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.btn-primary:hover { background: #1c3aa0; }
.btn-success { background: #10b981; color: #fff; border-color: #10b981; }
.btn-success:hover { background: #059669; }
.btn-danger { background: #ef4444; color: #fff; border-color: #ef4444; }
.btn-danger:hover { background: #dc2626; }
.btn-sm { padding: 4px 8px; font-size: 11px; height: 24px; }

.empty-state { padding: 24px 8px; color: #9ca3af; font-size: 12px; }

.add-form { display: none; background: #f9fafb; padding: 12px; border: 1px solid #e5e7eb; border-radius: 4px; margin-bottom: 8px; }
.add-form.visible { display: block; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.form-row.full { grid-template-columns: 1fr; }
.form-row.three { grid-template-columns: 1fr 1fr 1fr; }
.form-group { display: flex; flex-direction: column; gap: 3px; }
.form-label { font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
.required { color: #ef4444; }

.form-input, .form-select, .form-textarea { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; font-family: inherit; color: #1f2937; height: 30px; }
.form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.1); }
.form-textarea { height: auto; min-height: 60px; max-height: 60px; resize: none; }

.form-actions { display: flex; gap: 6px; margin-top: 8px; }

.action-btn { display: inline; }

.validation-inline { font-size: 10px; color: #6b7280; margin-top: 2px; }
.validation-warning { font-size: 10px; color: #ef4444; margin-top: 2px; }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .form-row.three { grid-template-columns: 1fr; }
    .section { padding: 12px 16px; }
}
</style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <h1 class="title">Salary Adjustment</h1>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        Adjustments require approval. Each change respects salary bands and stores date, reason, and approver information.
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

        <!-- 1. ADJUSTMENT HISTORY (Main View) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Adjustment History</div>
            </div>

            <?php if (empty($adjustments)): ?>
            <div class="empty-state">No adjustments yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Old</th>
                        <th>New</th>
                        <th>Effective</th>
                        <th>Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adjustments as $a): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(($a['last_name'] ?? '') . ', ' . ($a['first_name'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($a['adjustment_type']); ?></td>
                        <td class="num"><?php echo $a['previous_amount'] !== null ? format_currency($a['previous_amount'], 2) : '—'; ?></td>
                        <td class="num"><?php echo format_currency($a['new_amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($a['effective_date']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($a['status']); ?>">
                                <span class="dot dot-<?php echo strtolower($a['status']); ?>"></span>
                                <?php echo htmlspecialchars($a['status']); ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <?php if (strtolower($a['status']) === 'pending'): ?>
                            <form class="action-btn" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                                <button type="submit" class="btn btn-success btn-sm" title="Approve">✓</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- 2. CREATE ADJUSTMENT (Collapsed) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Create Adjustment</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-adjustment-form'); return false;">+ New Adjustment</button>
            </div>

            <div id="add-adjustment-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create">
                    
                    <!-- Top row: Employee, Type -->
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
                            <label class="form-label">Type <span class="required">•</span></label>
                            <select name="adjustment_type" required class="form-select">
                                <option value="">Select Type</option>
                                <?php
                                $opts = getEnumOptions('salary_adjustments', 'adjustment_type');
                                if (!empty($opts)) {
                                    foreach ($opts as $opt) {
                                        $val = htmlspecialchars($opt);
                                        echo "<option value=\"{$val}\">{$val}</option>\n";
                                    }
                                } else {
                                    // fallback to previous static list when enum not available
                                    ?>
                                    <option value="Promotion">Promotion</option>
                                    <option value="Merit increase">Merit increase</option>
                                    <option value="Market alignment">Market alignment</option>
                                    <option value="Temporary allowance">Temporary allowance</option>
                                    <option value="Correction">Correction</option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- Second row: Trigger, Salary band -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Trigger <span class="required">•</span></label>
                            <select name="trigger_type" required class="form-select">
                                <option value="">Select Trigger</option>
                                <?php
                                $topts = getEnumOptions('salary_adjustments', 'trigger_type');
                                if (!empty($topts)) {
                                    foreach ($topts as $t) {
                                        $v = htmlspecialchars($t);
                                        echo "<option value=\"{$v}\">{$v}</option>\n";
                                    }
                                } else {
                                    // fallback to sensible defaults
                                    ?>
                                    <option value="Performance review">Performance review</option>
                                    <option value="Role change">Role change</option>
                                    <option value="Policy update">Policy update</option>
                                    <option value="Management decision">Management decision</option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Salary Band (Optional)</label>
                            <select name="salary_band_id" class="form-select" onchange="showBandRange(this)">
                                <option value="">— None —</option>
                                <?php foreach ($bands as $b): ?>
                                <option value="<?php echo (int)$b['id']; ?>" data-min="<?php echo (float)$b['min_salary']; ?>" data-max="<?php echo (float)$b['max_salary']; ?>"><?php echo htmlspecialchars($b['pay_grade_name'] . ($b['grade_level_name'] ? ' – ' . $b['grade_level_name'] : '')); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div id="band-range" class="validation-inline" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Amounts row: Previous (read-only), New -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Previous Amount</label>
                            <input type="number" name="previous_amount" class="form-input" placeholder="Read-only" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">New Amount <span class="required">•</span></label>
                            <input type="number" name="new_amount" required class="form-input" placeholder="Enter new amount" step="1" min="0">
                            <div id="band-warning" class="validation-warning" style="display: none;"></div>
                        </div>
                    </div>

                    <!-- Dates row: Effective, End (if temporary) -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Effective Date <span class="required">•</span></label>
                            <input type="date" name="effective_date" required class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date (if temporary)</label>
                            <input type="date" name="end_date" class="form-input">
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Reason <span class="required">•</span></label>
                            <textarea name="reason" required class="form-textarea" placeholder="e.g. Promotion to Senior; market adjustment"></textarea>
                        </div>
                    </div>

                    <div class="form-actions" style="justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary btn-sm">Create. Pending approval</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-adjustment-form'); return false;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<script>
// expose app currency symbol to JS
window.APP_CURRENCY = '<?php echo app_currency_symbol(); ?>';
function toggleForm(id) {
    document.getElementById(id).classList.toggle('visible');
}

function showBandRange(select) {
    const option = select.options[select.selectedIndex];
    const range = document.getElementById('band-range');
    
    if (option.value && option.dataset.min && option.dataset.max) {
        const CUR = window.APP_CURRENCY || '₱';
        range.textContent = 'Band range: ' + CUR + Number(option.dataset.min).toLocaleString() + ' – ' + CUR + Number(option.dataset.max).toLocaleString();
        range.style.display = 'block';
    } else {
        range.style.display = 'none';
    }
}

// Band validation on new amount input
document.querySelector('input[name="new_amount"]')?.addEventListener('change', function() {
    const bandSelect = document.querySelector('select[name="salary_band_id"]');
    const option = bandSelect.options[bandSelect.selectedIndex];
    const warning = document.getElementById('band-warning');
    
    if (option.value && option.dataset.min && option.dataset.max) {
        const newAmount = parseFloat(this.value);
        const min = parseFloat(option.dataset.min);
        const max = parseFloat(option.dataset.max);
        
        if (newAmount < min || newAmount > max) {
            warning.textContent = '⚠ Outside band range. Requires approval.';
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }
});

// Handle form submissions via AJAX
document.querySelectorAll('form:not(.action-btn)').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const handler = this.getAttribute('action');
        const action = formData.get('action');
        
        fetch(handler, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            // Create or update message bar
            let msgBar = document.querySelector('.msg-bar');
            if (!msgBar) {
                msgBar = document.createElement('div');
                msgBar.className = 'msg-bar';
                document.querySelector('.content').parentNode.insertBefore(msgBar, document.querySelector('.content'));
            }
            
            if (data.success) {
                msgBar.innerHTML = `<div class="msg">✓ ${data.message}</div>`;
                if (action === 'create') {
                    form.reset();
                    document.getElementById('add-adjustment-form').classList.remove('visible');
                }
            } else {
                msgBar.innerHTML = `<div class="err">✕ ${data.message}</div>`;
            }
            
            // Reload page after 1.5 seconds to show updated data
            setTimeout(() => location.reload(), 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            const msgBar = document.querySelector('.msg-bar') || (() => {
                const bar = document.createElement('div');
                bar.className = 'msg-bar';
                document.querySelector('.content').parentNode.insertBefore(bar, document.querySelector('.content'));
                return bar;
            })();
            msgBar.innerHTML = `<div class="err">✕ Error submitting form: ${error.message}</div>`;
        });
    });
});
</script>

</body>
</html>
