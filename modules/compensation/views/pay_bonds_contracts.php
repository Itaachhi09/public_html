<?php
/**
 * Pay Bonds and Contracts View
 * Bind employees to compensation rules. Active contract required to receive compensation; expired contracts block new entries. PHP/HTML/CSS only; no JS.
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
<div class="main-content pay-bonds-contracts-content">
<style>
.pay-bonds-contracts-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.pbc-header { margin-bottom: 1rem; }
.pbc-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.pbc-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.pbc-rules { background: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #1e40af; }
.pbc-rules strong { display: block; margin-bottom: 0.25rem; }
.pbc-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.pbc-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.pbc-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.pbc-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.pbc-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.pbc-table th, .pbc-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.pbc-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.pbc-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.pbc-form input, .pbc-form select, .pbc-form textarea { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.pbc-form .pbc-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.pbc-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.pbc-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.pbc-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.pbc-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.pbc-empty { color: #9ca3af; font-size: 0.875rem; padding: 0.75rem; }
.pbc-inline-form { display: inline; }
.pbc-badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; background: #d1fae5; color: #065f46; }
.pbc-badge-expired { background: #fee2e2; color: #991b1b; }
</style>

<div class="pbc-header">
  <h1 class="pbc-title">Pay Bonds and Contracts</h1>
  <p class="pbc-subtitle">Bind employees to compensation rules. Create pay contracts (start/end date, linked pay grade, applicable policies). Active contract required to receive compensation. Expired contracts block new entries.</p>
</div>

<div class="pbc-rules">
  <strong>Rules</strong>
  Active contract required to receive compensation. Expired contracts block new entries. Output: clear contractual control over compensation.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="pbc-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="pbc-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<!-- Pay contracts -->
<div class="pbc-card">
  <h3>Pay contracts</h3>
  <?php if (empty($contracts)): ?>
  <p class="pbc-empty">No pay contracts. Add ER Doctor contract and Staff Nurse contract below.</p>
  <?php else: ?>
  <table class="pbc-table">
    <thead><tr><th>Code</th><th>Name</th><th>Pay grade</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($contracts as $c): ?>
      <?php $isActive = $c['end_date'] && $c['end_date'] >= date('Y-m-d'); ?>
      <tr>
        <td><?php echo htmlspecialchars($c['code']); ?></td>
        <td><?php echo htmlspecialchars($c['name']); ?></td>
        <td><?php echo htmlspecialchars($c['pay_grade_name']); ?></td>
        <td><?php echo htmlspecialchars($c['start_date']); ?></td>
        <td><?php echo htmlspecialchars($c['end_date']); ?></td>
        <td><span class="pbc-badge <?php echo $isActive ? '' : 'pbc-badge-expired'; ?>"><?php echo $isActive ? 'Active' : 'Expired'; ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <h3 style="margin-top: 1rem;">Create pay contract</h3>
  <form class="pbc-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create_contract">
    <div class="pbc-row">
      <div><label>Code *</label><input type="text" name="code" required placeholder="e.g. ER_DOCTOR_CONTRACT"></div>
      <div><label>Name *</label><input type="text" name="name" required placeholder="e.g. ER Doctor contract"></div>
    </div>
    <label>Linked pay grade *</label>
    <select name="pay_grade_id" required>
      <option value="">Select pay grade</option>
      <?php foreach ($payGrades as $pg): ?>
      <option value="<?php echo (int)$pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <div class="pbc-row">
      <div><label>Start date *</label><input type="date" name="start_date" required></div>
      <div><label>End date *</label><input type="date" name="end_date" required></div>
    </div>
    <label>Applicable policies</label>
    <textarea name="applicable_policies" rows="2" placeholder="e.g. Salary bands; incentive rules; benefits per policy"></textarea>
    <button type="submit" class="pbc-btn pbc-btn-primary">Create contract</button>
  </form>
</div>

<!-- Employee contract assignments -->
<div class="pbc-card">
  <h3>Employee contract assignments</h3>
  <p class="pbc-subtitle" style="margin: 0 0 0.5rem 0;">Bind employee to a contract. Only active contracts can be assigned. Active contract required to receive compensation.</p>
  <?php if (empty($assignments)): ?>
  <p class="pbc-empty">No assignments yet. Assign an employee to a contract below.</p>
  <?php else: ?>
  <table class="pbc-table">
    <thead><tr><th>Employee</th><th>Contract</th><th>Pay grade</th><th>Effective from</th><th>Effective to</th><th>Contract end</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($assignments as $a): ?>
      <?php $assignmentActive = (!$a['effective_to'] || $a['effective_to'] >= date('Y-m-d')) && $a['contract_end'] && $a['contract_end'] >= date('Y-m-d'); ?>
      <tr>
        <td><?php echo htmlspecialchars(($a['last_name'] ?? '') . ', ' . ($a['first_name'] ?? '') . ' (' . ($a['employee_code'] ?? '') . ')'); ?></td>
        <td><?php echo htmlspecialchars($a['contract_name']); ?></td>
        <td><?php echo htmlspecialchars($a['pay_grade_name']); ?></td>
        <td><?php echo htmlspecialchars($a['effective_from']); ?></td>
        <td><?php echo $a['effective_to'] ? htmlspecialchars($a['effective_to']) : 'Ongoing'; ?></td>
        <td><?php echo htmlspecialchars($a['contract_end']); ?></td>
        <td>
          <?php if ($assignmentActive && !$a['effective_to']): ?>
          <form class="pbc-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="end_assignment">
            <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
            <input type="hidden" name="effective_to" value="<?php echo date('Y-m-d'); ?>">
            <button type="submit" class="pbc-btn pbc-btn-outline pbc-btn-sm">End assignment</button>
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
  <h3 style="margin-top: 1rem;">Assign employee to contract</h3>
  <form class="pbc-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="assign_employee">
    <label>Employee *</label>
    <select name="employee_id" required>
      <option value="">Select employee</option>
      <?php foreach ($employees as $emp): ?>
      <option value="<?php echo (int)$emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['employee_code'] . ' – ' . $emp['last_name'] . ', ' . $emp['first_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label>Contract * (only active contracts; expired block new entries)</label>
    <select name="contract_id" required>
      <option value="">Select contract</option>
      <?php foreach ($activeContracts as $c): ?>
      <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['name'] . ' (' . $c['pay_grade_name'] . ') – to ' . $c['end_date']); ?></option>
      <?php endforeach; ?>
      <?php if (empty($activeContracts)): ?>
      <option value="" disabled>No active contracts</option>
      <?php endif; ?>
    </select>
    <div class="pbc-row">
      <div><label>Effective from *</label><input type="date" name="effective_from" required></div>
      <div><label>Effective to (optional)</label><input type="date" name="effective_to" placeholder="Leave blank for ongoing"></div>
    </div>
    <button type="submit" class="pbc-btn pbc-btn-primary">Assign to contract</button>
  </form>
</div>

<p class="pbc-subtitle" style="margin-top: 0.5rem;">Output: Clear contractual control over compensation.</p>
</div>
