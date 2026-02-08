<?php
/**
 * Allowances and Benefits View
 * Manage non-salary compensation. Define benefits: taxable, eligible roles, effective dates. PHP/HTML/CSS only; no JS.
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
<div class="main-content allowances-benefits-content">
<style>
.allowances-benefits-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.ab-header { margin-bottom: 1.5rem; }
.ab-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.ab-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.ab-rules { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #92400e; }
.ab-rules strong { display: block; margin-bottom: 0.25rem; }
.ab-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.ab-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.ab-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.ab-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.ab-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.ab-table th, .ab-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.ab-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.ab-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.ab-form input, .ab-form select, .ab-form textarea { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.ab-form .ab-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.ab-form input[type=checkbox] { width: auto; margin-right: 0.5rem; }
.ab-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.ab-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.ab-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.ab-btn-danger { background: #b91c1c; color: #fff; }
.ab-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.ab-empty { color: #9ca3af; font-size: 0.875rem; padding: 1rem; text-align: center; }
.ab-inline-form { display: inline; }
.ab-badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; background: #e5e7eb; color: #374151; }
.ab-badge-active { background: #d1fae5; color: #065f46; }
.ab-badge-inactive { background: #fee2e2; color: #991b1b; }
</style>

<div class="ab-header">
  <h1 class="ab-title">Allowances and Benefits</h1>
  <p class="ab-subtitle">Manage non-salary compensation. Define benefits with taxable flag, eligible roles, and effective dates. Benefits attach to duty or role; they do not affect salary bands. Benefit data is passed to Payroll as reference.</p>
</div>

<div class="ab-rules">
  <strong>Rules</strong>
  Benefits attach to duty or role. Benefits do not affect salary bands. Output: benefit data passed to Payroll as reference.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="ab-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="ab-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<div class="ab-card">
  <h3>Define benefit</h3>
  <form class="ab-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create">
    <div class="ab-row">
      <div><label>Code *</label><input type="text" name="code" required placeholder="e.g. FREE_MEALS_TID"></div>
      <div><label>Name *</label><input type="text" name="name" required placeholder="e.g. Free Meals TID"></div>
    </div>
    <label>Description</label>
    <textarea name="description" rows="2" placeholder="e.g. Free meals three times daily on duty"></textarea>
    <label><input type="checkbox" name="taxable" value="1"> Taxable</label>
    <div class="ab-row">
      <div>
        <label>Attach to *</label>
        <select name="attach_to" required>
          <option value="role">Role</option>
          <option value="duty">Duty</option>
        </select>
      </div>
      <div><label>Eligible roles</label><input type="text" name="eligible_roles" placeholder="e.g. ER Staff,Nurse,Doctor or All" value="All"></div>
    </div>
    <div class="ab-row">
      <div><label>Effective from *</label><input type="date" name="effective_from" required></div>
      <div><label>Effective to (optional)</label><input type="date" name="effective_to" placeholder="Leave blank for no end"></div>
    </div>
    <button type="submit" class="ab-btn ab-btn-primary">Add benefit</button>
  </form>
</div>

<div class="ab-card">
  <h3>Benefit definitions (Payroll reference)</h3>
  <?php if (empty($benefits)): ?>
  <p class="ab-empty">No benefits defined yet. Add one above. Payroll uses this list as reference.</p>
  <?php else: ?>
  <table class="ab-table">
    <thead>
      <tr>
        <th>Code</th>
        <th>Name</th>
        <th>Taxable</th>
        <th>Eligible roles</th>
        <th>Effective from</th>
        <th>Effective to</th>
        <th>Attach to</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($benefits as $b): ?>
      <tr>
        <td><?php echo htmlspecialchars($b['code']); ?></td>
        <td><?php echo htmlspecialchars($b['name']); ?></td>
        <td><?php echo !empty($b['taxable']) ? 'Yes' : 'No'; ?></td>
        <td><?php echo htmlspecialchars($b['eligible_roles']); ?></td>
        <td><?php echo htmlspecialchars($b['effective_from']); ?></td>
        <td><?php echo $b['effective_to'] ? htmlspecialchars($b['effective_to']) : '—'; ?></td>
        <td><?php echo htmlspecialchars($b['attach_to']); ?></td>
        <td><span class="ab-badge <?php echo !empty($b['is_active']) ? 'ab-badge-active' : 'ab-badge-inactive'; ?>"><?php echo !empty($b['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
        <td>
          <?php if (!empty($b['is_active'])): ?>
          <form class="ab-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="deactivate">
            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
            <button type="submit" class="ab-btn ab-btn-outline ab-btn-sm">Deactivate</button>
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
