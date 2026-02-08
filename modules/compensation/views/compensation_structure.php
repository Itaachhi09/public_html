<?php
/**
 * Compensation Structure and Setup View
 * Master definition of all pay components. No payroll computation. PHP/HTML/CSS only; no JS.
 * System context: Primary hospital, 30 beds, no ICU, ER focused. Toxic cases referred to THOC.
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

$handlerUrl = 'modules/compensation/compensation_structure_handler.php';
?>
<div class="main-content compensation-structure-content">
<style>
.compensation-structure-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.cs-header { margin-bottom: 1rem; }
.cs-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.cs-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.cs-context { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #374151; }
.cs-context strong { display: block; margin-bottom: 0.25rem; }
.cs-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.cs-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.cs-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.cs-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.cs-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.cs-table th, .cs-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.cs-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.cs-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.cs-form input, .cs-form select, .cs-form textarea { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.cs-form .cs-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.cs-form input[type=checkbox] { width: auto; margin-right: 0.5rem; }
.cs-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.cs-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.cs-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.cs-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.cs-empty { color: #9ca3af; font-size: 0.875rem; padding: 0.75rem; }
.cs-inline-form { display: inline; }
.cs-badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; background: #d1fae5; color: #065f46; }
.cs-badge-inactive { background: #fee2e2; color: #991b1b; }
</style>

<div class="cs-header">
  <h1 class="cs-title">Compensation Structure and Setup</h1>
  <p class="cs-subtitle">Master definition of all pay components. No payroll computation. Authoritative list readable by other submodules and Payroll.</p>
</div>

<div class="cs-context">
  <strong>System context</strong>
  Primary hospital. 30 beds. No ICU. ER focused. Toxic cases referred immediately to THOC.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="cs-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="cs-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<!-- 1. Salary components -->
<div class="cs-card">
  <h3>1. Salary components</h3>
  <?php if (empty($salaryComponents)): ?>
  <p class="cs-empty">No salary components defined. Add e.g. ER Duty Pay (24 hour ER duty base pay).</p>
  <?php else: ?>
  <table class="cs-table">
    <thead><tr><th>Code</th><th>Name</th><th>Description</th><th>Type</th><th>Taxable</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($salaryComponents as $s): ?>
      <tr>
        <td><?php echo htmlspecialchars($s['code']); ?></td>
        <td><?php echo htmlspecialchars($s['name']); ?></td>
        <td><?php echo htmlspecialchars($s['description'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($s['component_type']); ?></td>
        <td><?php echo !empty($s['taxable']) ? 'Yes' : 'No'; ?></td>
        <td><span class="cs-badge <?php echo empty($s['is_active']) ? 'cs-badge-inactive' : ''; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
        <td>
          <?php if (!empty($s['is_active'])): ?>
          <form class="cs-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="deactivate_salary">
            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
            <button type="submit" class="cs-btn cs-btn-outline cs-btn-sm">Deactivate</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <h3 style="margin-top: 1rem;">Add salary component</h3>
  <form class="cs-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create_salary_component">
    <div class="cs-row">
      <div><label>Code *</label><input type="text" name="code" required placeholder="e.g. ER_DUTY_PAY"></div>
      <div><label>Name *</label><input type="text" name="name" required placeholder="e.g. ER Duty Pay"></div>
    </div>
    <label>Description</label>
    <input type="text" name="description" placeholder="e.g. 24 hour ER duty base pay">
    <div class="cs-row">
      <div><label>Type</label><select name="component_type"><option value="base">Base</option><option value="allowance" selected>Allowance</option><option value="deduction">Deduction</option></select></div>
      <div><label><input type="checkbox" name="taxable" value="1"> Taxable</label></div>
    </div>
    <button type="submit" class="cs-btn cs-btn-primary">Add salary component</button>
  </form>
</div>

<!-- 2. Incentive components -->
<div class="cs-card">
  <h3>2. Incentive components</h3>
  <p class="cs-subtitle" style="margin: 0 0 0.5rem 0;">Each incentive is logged per case. Multiple records increase value. Only predefined incentives are allowed.</p>
  <?php if (empty($incentiveComponents)): ?>
  <p class="cs-empty">No incentive components. Add e.g. Admission, ER Consult, Suturing, DOA, etc.</p>
  <?php else: ?>
  <table class="cs-table">
    <thead><tr><th>Code</th><th>Name</th><th>Description</th><th>Approved</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($incentiveComponents as $i): ?>
      <tr>
        <td><?php echo htmlspecialchars($i['code']); ?></td>
        <td><?php echo htmlspecialchars($i['name']); ?></td>
        <td><?php echo htmlspecialchars($i['description'] ?? '—'); ?></td>
        <td><span class="cs-badge <?php echo empty($i['is_approved']) ? 'cs-badge-inactive' : ''; ?>"><?php echo !empty($i['is_approved']) ? 'Yes' : 'No'; ?></span></td>
        <td>
          <?php if (!empty($i['is_approved'])): ?>
          <form class="cs-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="deactivate_incentive">
            <input type="hidden" name="id" value="<?php echo (int)$i['id']; ?>">
            <button type="submit" class="cs-btn cs-btn-outline cs-btn-sm">Deactivate</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <h3 style="margin-top: 1rem;">Add incentive component</h3>
  <form class="cs-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create_incentive_component">
    <div class="cs-row">
      <div><label>Code *</label><input type="text" name="code" required placeholder="e.g. ADMISSION"></div>
      <div><label>Name *</label><input type="text" name="name" required placeholder="e.g. Admission"></div>
    </div>
    <label>Description</label>
    <input type="text" name="description" placeholder="e.g. Patient admission procedure">
    <button type="submit" class="cs-btn cs-btn-primary">Add incentive component</button>
  </form>
</div>

<!-- 3. Benefits -->
<div class="cs-card">
  <h3>3. Benefits</h3>
  <p class="cs-subtitle" style="margin: 0 0 0.5rem 0;">e.g. Free Meals TID: non taxable, applies only to ER staff on duty.</p>
  <?php if (empty($benefits)): ?>
  <p class="cs-empty">No benefits defined. Add e.g. Free Meals TID.</p>
  <?php else: ?>
  <table class="cs-table">
    <thead><tr><th>Code</th><th>Name</th><th>Taxable</th><th>Eligible roles</th><th>Attach to</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($benefits as $b): ?>
      <tr>
        <td><?php echo htmlspecialchars($b['code']); ?></td>
        <td><?php echo htmlspecialchars($b['name']); ?></td>
        <td><?php echo !empty($b['taxable']) ? 'Yes' : 'No'; ?></td>
        <td><?php echo htmlspecialchars($b['eligible_roles']); ?></td>
        <td><?php echo htmlspecialchars($b['attach_to']); ?></td>
        <td><span class="cs-badge <?php echo empty($b['is_active']) ? 'cs-badge-inactive' : ''; ?>"><?php echo !empty($b['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
        <td>
          <?php if (!empty($b['is_active'])): ?>
          <form class="cs-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="deactivate_benefit">
            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
            <button type="submit" class="cs-btn cs-btn-outline cs-btn-sm">Deactivate</button>
          </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <h3 style="margin-top: 1rem;">Add benefit</h3>
  <form class="cs-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create_benefit">
    <div class="cs-row">
      <div><label>Code *</label><input type="text" name="code" required placeholder="e.g. FREE_MEALS_TID"></div>
      <div><label>Name *</label><input type="text" name="name" required placeholder="e.g. Free Meals TID"></div>
    </div>
    <label>Description</label>
    <input type="text" name="description" placeholder="e.g. Free meals three times daily on duty">
    <label><input type="checkbox" name="taxable" value="1"> Taxable</label>
    <div class="cs-row">
      <div><label>Eligible roles</label><input type="text" name="eligible_roles" placeholder="e.g. ER Staff" value="ER Staff"></div>
      <div><label>Attach to</label><select name="attach_to"><option value="duty" selected>Duty only</option><option value="role">Role</option></select></div>
    </div>
    <div class="cs-row">
      <div><label>Effective from *</label><input type="date" name="effective_from" required></div>
      <div><label>Effective to (optional)</label><input type="date" name="effective_to"></div>
    </div>
    <button type="submit" class="cs-btn cs-btn-primary">Add benefit</button>
  </form>
</div>

<p class="cs-subtitle" style="margin-top: 0.5rem;">Output: Authoritative list of all compensation components. Readable by other submodules and Payroll.</p>
</div>
