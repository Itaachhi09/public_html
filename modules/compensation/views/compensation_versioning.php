<?php
/**
 * Compensation Versioning - Modern Design
 * Track versions of salary components, bands, policies, assignments. Audit trail immutable.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/CompensationVersionHistory.php';

$versionModel = new CompensationVersionHistory();
$history = $versionModel->getAll([]);
$approvedActive = $versionModel->getApprovedActive();

$handlerUrl = 'modules/compensation/compensation_versioning_handler.php';

$typeLabels = [
    'pay_component' => 'Pay component',
    'salary_band' => 'Salary band',
    'policy' => 'Policy',
    'employee_assignment' => 'Employee assignment',
];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Compensation Versioning</title>
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
.table tbody tr:hover { background: #f9fafb; cursor: pointer; }

.status-badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.status-active { background: #d1fae5; color: #065f46; }
.status-scheduled { background: #dbeafe; color: #1e40af; }
.status-pending { background: #fef3c7; color: #b45309; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-inactive { background: #e5e7eb; color: #374151; }

.dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
.dot-active { background: #10b981; }
.dot-scheduled { background: #3b82f6; }
.dot-pending { background: #f59e0b; }

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
.form-textarea { height: auto; min-height: 60px; max-height: 60px; resize: none; }

.form-actions { display: flex; gap: 6px; margin-top: 8px; justify-content: flex-end; }

.action-icons { display: flex; gap: 4px; }
.action-icon { cursor: pointer; }
.action-icon button { background: none; border: none; color: #374151; cursor: pointer; font-size: 14px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
.action-icon button:hover { color: #1f2937; }

.detail-panel { display: none; position: fixed; right: 0; top: 0; width: 400px; height: 100%; background: #fff; border-left: 1px solid #e5e7eb; box-shadow: -2px 0 8px rgba(0,0,0,0.1); z-index: 200; padding: 24px; overflow-y: auto; }
.detail-panel.visible { display: block; }
.detail-panel-close { position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; }
.detail-panel h2 { font-size: 14px; font-weight: 600; margin-bottom: 12px; }
.detail-row { margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: 10px; font-weight: 600; color: #6b7280; text-transform: uppercase; }
.detail-value { font-size: 12px; color: #1f2937; margin-top: 4px; }

.overlay { display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.1); z-index: 100; }
.overlay.visible { display: block; }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .section { padding: 12px 16px; }
    .detail-panel { width: 100%; }
}
</style>
</head>
<body>

<div class="container">

    <!-- Header -->
    <div class="header">
        <h1 class="title">Compensation Versioning</h1>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        Payroll reads approved active versions only. All changes audited.
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

        <!-- 1. APPROVED ACTIVE VERSIONS (Primary Focus) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Approved Active Versions</div>
            </div>

            <?php if (empty($approvedActive)): ?>
            <div class="empty-state">No approved active versions. Payroll dependency: only these rows are used.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Entity</th>
                        <th>Entity ID</th>
                        <th>Version</th>
                        <th>Effective date</th>
                        <th>Approved by</th>
                        <th>Approved on</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approvedActive as $v): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($typeLabels[$v['entity_type']] ?? $v['entity_type']); ?></td>
                        <td><?php echo (int)$v['entity_id']; ?></td>
                        <td><?php echo (int)$v['version_number']; ?></td>
                        <td><?php echo htmlspecialchars(substr($v['effective_date'], 0, 10)); ?></td>
                        <td><?php echo htmlspecialchars($v['approved_by'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars(substr($v['approved_at'] ?? '', 0, 10)); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo !empty($v['is_active']) ? 'active' : 'scheduled'; ?>">
                                <span class="dot dot-<?php echo !empty($v['is_active']) ? 'active' : 'scheduled'; ?>"></span>
                                <?php echo !empty($v['is_active']) ? 'Active' : 'Scheduled'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- 2. VERSION HISTORY (Secondary Focus) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Version History</div>
            </div>

            <?php if (empty($history)): ?>
            <div class="empty-state">No version history yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Entity</th>
                        <th>Entity ID</th>
                        <th>Version</th>
                        <th>Effective date</th>
                        <th>Reason</th>
                        <th>Requested by</th>
                        <th>Approved by</th>
                        <th>Status</th>
                        <th style="width: 40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($typeLabels[$h['entity_type']] ?? $h['entity_type']); ?></td>
                        <td><?php echo (int)$h['entity_id']; ?></td>
                        <td><?php echo (int)$h['version_number']; ?></td>
                        <td><?php echo htmlspecialchars(substr($h['effective_date'], 0, 10)); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr($h['reason'], 0, 40) . (mb_strlen($h['reason']) > 40 ? '…' : '')); ?></td>
                        <td><?php echo htmlspecialchars($h['changed_by'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($h['approved_by'] ?? '—'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo str_replace(' ', '_', $h['status']); ?>">
                                <span class="dot dot-<?php echo str_replace(' ', '_', $h['status']); ?>" style="background: <?php echo $h['status'] === 'approved' ? '#10b981' : ($h['status'] === 'pending' ? '#f59e0b' : '#3b82f6'); ?>;"></span>
                                <?php echo htmlspecialchars(ucfirst($h['status'])); ?>
                            </span>
                        </td>
                        <td class="action-icons" onclick="event.stopPropagation();">
                            <button class="action-icon" type="button" onclick="openDetail(<?php echo (int)$h['id']; ?>)" title="View details">◊</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- 3. RECORD NEW VERSION (Collapsed) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Record New Version</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-version-form'); return false;">+ Record</button>
            </div>

            <div id="add-version-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="record_version">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Entity type <span class="required">•</span></label>
                            <select name="entity_type" required class="form-select">
                                <option value="">Select type</option>
                                <option value="salary_band">Salary band</option>
                                <option value="pay_component">Pay component</option>
                                <option value="policy">Policy</option>
                                <option value="employee_assignment">Employee assignment</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Entity ID <span class="required">•</span></label>
                            <input type="number" name="entity_id" required class="form-input" placeholder="Record ID" min="1">
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Effective date <span class="required">•</span></label>
                            <input type="date" name="effective_date" required class="form-input">
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Reason <span class="required">•</span></label>
                            <textarea name="reason" required class="form-textarea" placeholder="e.g. Band update per policy change"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Record. Pending approval</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-version-form'); return false;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>

<!-- Detail Panel -->
<div id="detail-panel" class="detail-panel">
    <button class="detail-panel-close" onclick="closeDetail()">✕</button>
    <div id="detail-content"></div>
</div>
<div id="overlay" class="overlay" onclick="closeDetail()"></div>

<script>
// Hardcoded history data for demo - replace with PHP data in production
const historyData = <?php echo json_encode($history); ?>;

function toggleForm(id) {
    document.getElementById(id).classList.toggle('visible');
}

function openDetail(id) {
    const record = historyData.find(r => r.id == id);
    if (!record) return;
    
    const panel = document.getElementById('detail-panel');
    const overlay = document.getElementById('overlay');
    const content = document.getElementById('detail-content');
    
    const typeLabels = {
        'pay_component': 'Pay component',
        'salary_band': 'Salary band',
        'policy': 'Policy',
        'employee_assignment': 'Employee assignment'
    };
    
    content.innerHTML = `
        <h2>Version Details</h2>
        <div class="detail-row">
            <div class="detail-label">Entity</div>
            <div class="detail-value">${typeLabels[record.entity_type] || record.entity_type}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Entity ID</div>
            <div class="detail-value">${record.entity_id}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Version</div>
            <div class="detail-value">${record.version_number}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Effective Date</div>
            <div class="detail-value">${record.effective_date.split(' ')[0]}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Reason</div>
            <div class="detail-value">${record.reason}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Requested By</div>
            <div class="detail-value">${record.changed_by || '—'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Approved By</div>
            <div class="detail-value">${record.approved_by || '—'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">${record.status}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Created</div>
            <div class="detail-value">${record.created_at || '—'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Approval Trail</div>
            <div class="detail-value">${record.reviewed_at ? 'Reviewed at ' + record.reviewed_at : 'Pending review'}</div>
        </div>
    `;
    
    panel.classList.add('visible');
    overlay.classList.add('visible');
}

function closeDetail() {
    document.getElementById('detail-panel').classList.remove('visible');
    document.getElementById('overlay').classList.remove('visible');
}
</script>

</body>
</html>

