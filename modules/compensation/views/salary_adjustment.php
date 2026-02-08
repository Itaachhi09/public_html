<?php
/**
 * Salary Adjustment View
 * Controlled changes to base pay: create records (Temporary/Permanent), triggers, approval. PHP/HTML/CSS only; no JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/SalaryAdjustment.php';
require_once __DIR__ . '/../models/SalaryBand.php';

$adjustmentModel = new SalaryAdjustment();
$bandModel = new SalaryBand();

$adjustments = $adjustmentModel->getAllWithDetails([]);
$bands = $bandModel->getAllWithDetails(false);
$employees = $adjustmentModel->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);

$handlerUrl = 'modules/compensation/salary_adjustment_handler.php';
?>
<div class="main-content salary-adjustment-content">
<style>
.salary-adjustment-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.sa-header { margin-bottom: 1.5rem; }
.sa-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.sa-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.sa-rules { background: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #1e40af; }
.sa-rules strong { display: block; margin-bottom: 0.25rem; }
.sa-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.sa-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.sa-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.sa-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.sa-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.sa-table th, .sa-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.sa-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.sa-table .sa-num { text-align: right; }
.sa-table .sa-status-pending { color: #b45309; }
.sa-table .sa-status-approved { color: #047857; }
.sa-table .sa-status-rejected { color: #b91c1c; }
.sa-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.sa-form input, .sa-form select, .sa-form textarea { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.sa-form textarea { min-height: 60px; resize: vertical; }
.sa-form .sa-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.sa-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.sa-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.sa-btn-success { background: #047857; color: #fff; }
.sa-btn-danger { background: #b91c1c; color: #fff; }
.sa-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.sa-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.sa-empty { color: #9ca3af; font-size: 0.875rem; padding: 1rem; text-align: center; }
.sa-inline-form { display: inline; }
</style>

<div class="sa-header">
  <h1 class="sa-title">Salary Adjustment</h1>
  <p class="sa-subtitle">Handle controlled changes to base pay. Create adjustment records (Temporary or Permanent); triggers: Promotion, Approved exception, Policy change. Each change stores date, reason, and approver. No payroll computation.</p>
</div>

<div class="sa-rules">
  <strong>Rules</strong>
  Adjustments require approval. Adjustments respect salary bands. Each change stores date, reason, and approver. Clean adjustment history only; no payroll computation.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="sa-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="sa-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<div class="sa-card">
  <h3>Create adjustment record</h3>
  <form class="sa-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create">
    <label>Employee *</label>
    <select name="employee_id" required>
      <option value="">Select employee</option>
      <?php foreach ($employees as $emp): ?>
      <option value="<?php echo (int)$emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['employee_code'] . ' – ' . $emp['last_name'] . ', ' . $emp['first_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <div class="sa-row">
      <div>
        <label>Adjustment type *</label>
        <select name="adjustment_type" required>
          <option value="">Select</option>
          <option value="Temporary">Temporary</option>
          <option value="Permanent">Permanent</option>
        </select>
      </div>
      <div>
        <label>Trigger *</label>
        <select name="trigger_type" required>
          <option value="">Select</option>
          <option value="Promotion">Promotion</option>
          <option value="Approved exception">Approved exception</option>
          <option value="Policy change">Policy change</option>
        </select>
      </div>
    </div>
    <div class="sa-row">
      <div><label>Previous amount (optional)</label><input type="number" name="previous_amount" step="0.01" min="0" placeholder="Current base"></div>
      <div><label>New amount *</label><input type="number" name="new_amount" step="0.01" min="0" required></div>
    </div>
    <div class="sa-row">
      <div><label>Effective date *</label><input type="date" name="effective_date" required></div>
      <div><label>End date (for Temporary)</label><input type="date" name="end_date"></div>
    </div>
    <label>Salary band (optional; if set, new amount must be within band)</label>
    <select name="salary_band_id">
      <option value="">— None —</option>
      <?php foreach ($bands as $b): ?>
      <option value="<?php echo (int)$b['id']; ?>"><?php echo htmlspecialchars($b['pay_grade_name'] . ($b['grade_level_name'] ? ' – ' . $b['grade_level_name'] : '') . ' (' . $b['min_salary'] . '–' . $b['max_salary'] . ')'); ?></option>
      <?php endforeach; ?>
    </select>
    <label>Reason *</label>
    <textarea name="reason" required placeholder="e.g. Promotion to Senior; policy change effective Jan 2025"></textarea>
    <button type="submit" class="sa-btn sa-btn-primary">Create (pending approval)</button>
  </form>
</div>

<div class="sa-card">
  <h3>Adjustment history</h3>
  <?php if (empty($adjustments)): ?>
  <p class="sa-empty">No adjustment records yet. Create one above.</p>
  <?php else: ?>
  <table class="sa-table">
    <thead>
      <tr>
        <th>Employee</th>
        <th>Type</th>
        <th>Trigger</th>
        <th class="sa-num">Previous</th>
        <th class="sa-num">New</th>
        <th>Effective</th>
        <th>End</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($adjustments as $a): ?>
      <tr>
        <td><?php echo htmlspecialchars(($a['last_name'] ?? '') . ', ' . ($a['first_name'] ?? '') . ' (' . ($a['employee_code'] ?? '') . ')'); ?></td>
        <td><?php echo htmlspecialchars($a['adjustment_type']); ?></td>
        <td><?php echo htmlspecialchars($a['trigger_type']); ?></td>
        <td class="sa-num"><?php echo $a['previous_amount'] !== null ? number_format((float)$a['previous_amount'], 2) : '—'; ?></td>
        <td class="sa-num"><?php echo number_format((float)$a['new_amount'], 2); ?></td>
        <td><?php echo htmlspecialchars($a['effective_date']); ?></td>
        <td><?php echo $a['end_date'] ? htmlspecialchars($a['end_date']) : '—'; ?></td>
        <td><span class="sa-status-<?php echo $a['status']; ?>"><?php echo htmlspecialchars($a['status']); ?></span></td>
        <td>
          <?php if ($a['status'] === 'pending'): ?>
          <form class="sa-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
            <button type="submit" class="sa-btn sa-btn-success sa-btn-sm">Approve</button>
          </form>
          <form class="sa-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm">Reject</button>
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
</div>
