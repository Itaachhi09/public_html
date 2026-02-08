<?php
/**
 * Compensation Approval Workflow View
 * Enforce governance and control. Request → Review → Approval/Rejection. Auditable. PHP/HTML/CSS only; no JS.
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

$typeLabels = [
    'salary_above_band' => 'Salary above band',
    'double_pay_tagging' => 'Double pay tagging',
    'manual_adjustment' => 'Manual adjustment',
    'policy_exception' => 'Policy exception',
];
?>
<div class="main-content compensation-approval-content">
<style>
.compensation-approval-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.caw-header { margin-bottom: 1rem; }
.caw-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.caw-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.caw-rules { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #92400e; }
.caw-rules strong { display: block; margin-bottom: 0.25rem; }
.caw-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.caw-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.caw-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.caw-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.caw-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.caw-table th, .caw-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.caw-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.caw-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.caw-form input, .caw-form select, .caw-form textarea { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.caw-form textarea { min-height: 60px; resize: vertical; }
.caw-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.caw-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.caw-btn-success { background: #047857; color: #fff; }
.caw-btn-danger { background: #b91c1c; color: #fff; }
.caw-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.caw-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.caw-empty { color: #9ca3af; font-size: 0.875rem; padding: 0.75rem; }
.caw-inline-form { display: inline; margin-right: 0.25rem; }
.caw-badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; }
.caw-badge-requested { background: #dbeafe; color: #1e40af; }
.caw-badge-under_review { background: #fef3c7; color: #92400e; }
.caw-badge-approved { background: #d1fae5; color: #065f46; }
.caw-badge-rejected { background: #fee2e2; color: #991b1b; }
.caw-audit { font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem; }
</style>

<div class="caw-header">
  <h1 class="caw-title">Compensation Approval Workflow</h1>
  <p class="caw-subtitle">Enforce governance and control. Approval required for: Salary above band, Double pay tagging, Manual adjustments, Policy exceptions. Workflow: Request → Review → Approval → Rejection.</p>
</div>

<div class="caw-rules">
  <strong>Rules</strong>
  Unapproved items never reach Payroll. Approval status is auditable. Output: controlled and compliant compensation data.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="caw-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="caw-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<!-- New request -->
<div class="caw-card">
  <h3>Submit approval request</h3>
  <form class="caw-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create_request">
    <label>Request type *</label>
    <select name="request_type" required>
      <option value="">Select type</option>
      <option value="salary_above_band">Salary above band</option>
      <option value="double_pay_tagging">Double pay tagging</option>
      <option value="manual_adjustment">Manual adjustment</option>
      <option value="policy_exception">Policy exception</option>
    </select>
    <label>Reference (optional)</label>
    <input type="text" name="reference_type" placeholder="e.g. salary_adjustment">
    <input type="number" name="reference_id" placeholder="Reference ID" min="0">
    <label>Description *</label>
    <textarea name="description" required placeholder="Describe what requires approval"></textarea>
    <button type="submit" class="caw-btn caw-btn-primary">Submit request</button>
  </form>
</div>

<!-- All requests (auditable) -->
<div class="caw-card">
  <h3>Approval requests (auditable)</h3>
  <?php if (empty($requests)): ?>
  <p class="caw-empty">No approval requests yet. Submit one above.</p>
  <?php else: ?>
  <table class="caw-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Description</th>
        <th>Status</th>
        <th>Requested</th>
        <th>Reviewed</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($requests as $r): ?>
      <tr>
        <td><?php echo (int)$r['id']; ?></td>
        <td><?php echo htmlspecialchars($typeLabels[$r['request_type']] ?? $r['request_type']); ?></td>
        <td><?php echo htmlspecialchars(mb_substr($r['description'], 0, 80) . (mb_strlen($r['description']) > 80 ? '…' : '')); ?></td>
        <td><span class="caw-badge caw-badge-<?php echo str_replace(' ', '_', $r['status']); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $r['status']))); ?></span></td>
        <td><?php echo htmlspecialchars($r['requested_at'] ?? '—'); ?></td>
        <td><?php echo $r['reviewed_at'] ? htmlspecialchars($r['reviewed_at']) : '—'; ?></td>
        <td>
          <?php if ($r['status'] === 'requested'): ?>
          <form class="caw-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="set_review">
            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
            <button type="submit" class="caw-btn caw-btn-outline caw-btn-sm">Mark under review</button>
          </form>
          <?php endif; ?>
          <?php if ($r['status'] === 'requested' || $r['status'] === 'under_review'): ?>
          <form class="caw-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
            <input type="hidden" name="approval_notes" value="">
            <button type="submit" class="caw-btn caw-btn-success caw-btn-sm">Approve</button>
          </form>
          <form class="caw-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
            <input type="hidden" name="approval_notes" value="">
            <button type="submit" class="caw-btn caw-btn-danger caw-btn-sm">Reject</button>
          </form>
          <?php else: ?>
          —
          <?php endif; ?>
        </td>
      </tr>
      <?php if (!empty($r['approval_notes']) || $r['reviewed_at']): ?>
      <tr>
        <td colspan="7" class="caw-audit" style="padding-left: 1.5rem;">
          <?php if ($r['reviewed_at']): ?>Reviewed at <?php echo htmlspecialchars($r['reviewed_at']); ?>.<?php endif; ?>
          <?php if (!empty($r['approval_notes'])): ?> Notes: <?php echo htmlspecialchars($r['approval_notes']); ?><?php endif; ?>
        </td>
      </tr>
      <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Approve/Reject with notes (for items needing notes) -->
<?php if (!empty($pending)): ?>
<div class="caw-card">
  <h3>Approve or reject with notes</h3>
  <?php foreach ($pending as $r): ?>
  <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem;">
    <strong><?php echo htmlspecialchars($typeLabels[$r['request_type']] ?? $r['request_type']); ?></strong> — <?php echo htmlspecialchars(mb_substr($r['description'], 0, 120) . (mb_strlen($r['description']) > 120 ? '…' : '')); ?>
    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; align-items: flex-end; flex-wrap: wrap;">
      <form class="caw-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
        <input type="text" name="approval_notes" placeholder="Notes (optional)" style="width: 180px; margin-right: 0.25rem;">
        <button type="submit" class="caw-btn caw-btn-success caw-btn-sm">Approve</button>
      </form>
      <form class="caw-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="reject">
        <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
        <input type="text" name="approval_notes" placeholder="Reason (optional)" style="width: 180px; margin-right: 0.25rem;">
        <button type="submit" class="caw-btn caw-btn-danger caw-btn-sm">Reject</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<p class="caw-subtitle" style="margin-top: 0.5rem;">Output: Controlled and compliant compensation data. Only approved items reach Payroll.</p>
</div>
