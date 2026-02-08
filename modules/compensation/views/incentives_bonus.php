<?php
/**
 * Incentives and Bonuses View
 * Record workload-based earnings. Log incentive events (employee, duty date, procedure type, quantity, approver). PHP/HTML/CSS only; no JS.
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
<div class="main-content incentives-bonus-content">
<style>
.incentives-bonus-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.ib-header { margin-bottom: 1.5rem; }
.ib-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.ib-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.ib-rules { background: #f0fdf4; border: 1px solid #22c55e; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #166534; }
.ib-rules strong { display: block; margin-bottom: 0.25rem; }
.ib-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.ib-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.ib-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.ib-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.ib-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.ib-table th, .ib-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.ib-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.ib-table .ib-num { text-align: right; }
.ib-table .ib-status-pending { color: #b45309; }
.ib-table .ib-status-approved { color: #047857; }
.ib-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.ib-form input, .ib-form select { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.ib-form .ib-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.ib-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.ib-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.ib-btn-success { background: #047857; color: #fff; }
.ib-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.ib-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.ib-empty { color: #9ca3af; font-size: 0.875rem; padding: 1rem; text-align: center; }
.ib-inline-form { display: inline; }
</style>

<div class="ib-header">
  <h1 class="ib-title">Incentives and Bonuses</h1>
  <p class="ib-subtitle">Record workload-based earnings. Log incentive events per ER duty: employee, duty date, procedure type, quantity. Only approved incentive types allowed. Each event links to one ER duty. Unapproved records stay inactive. Multiple procedures increase take home; used in ER duty simulation.</p>
</div>

<div class="ib-rules">
  <strong>Rules</strong>
  Only approved incentive types allowed. Each event links to one ER duty. Unapproved records stay inactive. Output: validated incentive records for Payroll reference.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="ib-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="ib-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<div class="ib-card">
  <h3>Log incentive event</h3>
  <form class="ib-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create">
    <label>Employee *</label>
    <select name="employee_id" required>
      <option value="">Select employee</option>
      <?php foreach ($employees as $emp): ?>
      <option value="<?php echo (int)$emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['employee_code'] . ' – ' . $emp['last_name'] . ', ' . $emp['first_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <div class="ib-row">
      <div>
        <label>Duty date *</label>
        <input type="date" name="duty_date" required>
      </div>
      <div>
        <label>Procedure type * (approved only)</label>
        <select name="procedure_type_id" required>
          <option value="">Select procedure type</option>
          <?php foreach ($approvedTypes as $t): ?>
          <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['name'] . ' (' . $t['code'] . ')'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <label>Quantity *</label>
    <input type="number" name="quantity" min="1" value="1" required>
    <button type="submit" class="ib-btn ib-btn-primary">Log event (pending approval)</button>
  </form>
</div>

<div class="ib-card">
  <h3>Incentive events (for Payroll reference)</h3>
  <?php if (empty($events)): ?>
  <p class="ib-empty">No incentive events yet. Log one above. Approved records are used for payroll.</p>
  <?php else: ?>
  <table class="ib-table">
    <thead>
      <tr>
        <th>Employee</th>
        <th>Duty date</th>
        <th>Procedure type</th>
        <th class="ib-num">Quantity</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $e): ?>
      <tr>
        <td><?php echo htmlspecialchars(($e['last_name'] ?? '') . ', ' . ($e['first_name'] ?? '') . ' (' . ($e['employee_code'] ?? '') . ')'); ?></td>
        <td><?php echo htmlspecialchars($e['duty_date']); ?></td>
        <td><?php echo htmlspecialchars($e['procedure_name'] ?? $e['procedure_code']); ?></td>
        <td class="ib-num"><?php echo (int)$e['quantity']; ?></td>
        <td><span class="ib-status-<?php echo $e['status']; ?>"><?php echo htmlspecialchars($e['status']); ?></span></td>
        <td>
          <?php if ($e['status'] === 'pending'): ?>
          <form class="ib-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="id" value="<?php echo (int)$e['id']; ?>">
            <button type="submit" class="ib-btn ib-btn-success ib-btn-sm">Approve</button>
          </form>
          <?php else: ?>
          Approved
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
</div>
