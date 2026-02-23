<?php
/**
 * Approval Workflow - Modern Design
 * Compensation approval queue and requests
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/CompensationApprovalRequest.php';

$approvalModel = new CompensationApprovalRequest();
$requests = $approvalModel->getAll([]);
$pending = $approvalModel->getPending();

$handlerUrl = 'modules/compensation/compensation_approval_handler.php';

// Helper to read enum values from information_schema
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

// Build friendly labels for request types from DB enum
$typeLabels = [];
$rtypes = getEnumOptions('compensation_approval_requests', 'request_type');
if (!empty($rtypes)) {
    foreach ($rtypes as $t) {
        $typeLabels[$t] = ucwords(str_replace(['_', '-'], ' ', $t));
    }
} else {
    $typeLabels = [
        'salary_above_band' => 'Salary above band',
        'double_pay_tagging' => 'Double pay tagging',
        'manual_adjustment' => 'Manual adjustment',
        'policy_exception' => 'Policy exception',
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Approval Workflow</title>
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
.status-pending { background: #fef3c7; color: #b45309; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }
.status-under_review { background: #dbeafe; color: #1e40af; }

.dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
.dot-pending { background: #f59e0b; }
.dot-approved { background: #10b981; }
.dot-rejected { background: #ef4444; }
.dot-under_review { background: #3b82f6; }

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

.audit-note { font-size: 10px; color: #6b7280; margin-top: 4px; font-style: italic; }

.detail-panel { display: none; position: fixed; right: 0; top: 0; width: 400px; height: 100%; background: #fff; border-left: 1px solid #e5e7eb; box-shadow: -2px 0 8px rgba(0,0,0,0.1); z-index: 200; padding: 24px; overflow-y: auto; }
.detail-panel.visible { display: block; }
.detail-panel-close { position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; }
.detail-panel h2 { font-size: 14px; font-weight: 600; margin-bottom: 12px; }
.detail-row { margin-bottom: 12px; }
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
        <h1 class="title">Approval Workflow</h1>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        Only approved items reach payroll. All actions audited.
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

        <!-- 1. APPROVAL REQUESTS QUEUE (Primary Focus) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Approval Queue</div>
            </div>

            <?php if (empty($requests)): ?>
            <div class="empty-state">No approval requests.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Requested by</th>
                        <th>Status</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                    <tr onclick="openDetail(<?php echo (int)$r['id']; ?>, event)">
                        <td><?php echo htmlspecialchars(substr($r['requested_at'] ?? '', 0, 10)); ?></td>
                        <td><?php echo htmlspecialchars($typeLabels[$r['request_type']] ?? $r['request_type']); ?></td>
                        <td><?php echo htmlspecialchars($r['reference_type'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($r['requested_by'] ?? '—'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo str_replace(' ', '_', $r['status']); ?>">
                                <span class="dot dot-<?php echo str_replace(' ', '_', $r['status']); ?>"></span>
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $r['status']))); ?>
                            </span>
                        </td>
                        <td class="action-icons" onclick="event.stopPropagation();">
                            <?php if ($r['status'] === 'pending' || $r['status'] === 'under_review'): ?>
                            <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                <button class="action-icon" type="submit" title="Approve" style="color: #10b981;">✓</button>
                            </form>
                            <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                                <button class="action-icon" type="submit" title="Reject" style="color: #ef4444;">✕</button>
                            </form>
                            <?php else: ?>
                            —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if (!empty($r['approval_notes']) || $r['reviewed_at']): ?>
                    <tr style="background: #f9fafb;">
                        <td colspan="6" class="audit-note">
                            <?php if ($r['reviewed_at']): ?>Reviewed <?php echo htmlspecialchars($r['reviewed_at']); ?>.<?php endif; ?>
                            <?php if (!empty($r['approval_notes'])): ?> <?php echo htmlspecialchars($r['approval_notes']); ?><?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- 2. SUBMIT REQUEST FORM (Collapsed) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">Submit Request</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-request-form'); return false;">+ Submit</button>
            </div>

            <div id="add-request-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_request">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Type <span class="required">•</span></label>
                            <select name="request_type" required class="form-select">
                                <option value="">Select Type</option>
                                <?php
                                $reqOpts = getEnumOptions('compensation_approval_requests', 'request_type');
                                if (!empty($reqOpts)) {
                                    foreach ($reqOpts as $ro) {
                                        echo '<option value="' . htmlspecialchars($ro) . '">' . htmlspecialchars(ucwords(str_replace('_',' ',$ro))) . '</option>';
                                    }
                                } else {
                                    ?>
                                    <option value="salary_above_band">Salary above band</option>
                                    <option value="double_pay_tagging">Double pay tagging</option>
                                    <option value="manual_adjustment">Manual adjustment</option>
                                    <option value="policy_exception">Policy exception</option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference_type" class="form-input" placeholder="e.g. salary_adjustment" maxlength="100">
                        </div>
                    </div>

                    <div class="form-row full" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Reference ID</label>
                            <input type="number" name="reference_id" class="form-input" placeholder="Auto-filled from module" min="0">
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label class="form-label">Description <span class="required">•</span></label>
                            <textarea name="description" required class="form-textarea" placeholder="Describe what requires approval"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-request-form'); return false;">Cancel</button>
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
function toggleForm(id) {
    document.getElementById(id).classList.toggle('visible');
}

function openDetail(id, event) {
    if (event && event.target.closest('.action-icons')) {
        return;
    }
    
    const panel = document.getElementById('detail-panel');
    const overlay = document.getElementById('overlay');
    const content = document.getElementById('detail-content');
    
    // Get row data
    const row = event.currentTarget;
    const cells = row.querySelectorAll('td');
    
    if (cells.length >= 4) {
        const date = cells[0].textContent.trim();
        const type = cells[1].textContent.trim();
        const reference = cells[2].textContent.trim();
        const requester = cells[3].textContent.trim();
        const status = cells[4].textContent.trim();
        
        content.innerHTML = `
            <h2>Request Details</h2>
            <div class="detail-row">
                <div class="detail-label">ID</div>
                <div class="detail-value">${id}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Type</div>
                <div class="detail-value">${type}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Reference</div>
                <div class="detail-value">${reference}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Requested by</div>
                <div class="detail-value">${requester}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date</div>
                <div class="detail-value">${date}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value">${status}</div>
            </div>
        `;
        
        panel.classList.add('visible');
        overlay.classList.add('visible');
    }
}

function closeDetail() {
    document.getElementById('detail-panel').classList.remove('visible');
    document.getElementById('overlay').classList.remove('visible');
}
</script>

</body>
</html>
