<?php
/**
 * Payroll – Salaries. PHP/HTML/CSS only; no JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/EmployeeSalary.php';

$salary = new EmployeeSalary();
$list = $salary->getAllWithEmployee();
$employees = $salary->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);
$handlerUrl = 'modules/payroll/salaries_handler.php';
?>
<div class="main-content payroll-salaries-content">
<style>
.payroll-salaries-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.py-header { margin-bottom: 1rem; }
.py-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.py-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.py-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.py-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.py-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.py-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.py-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.py-table th, .py-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.py-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.py-table .py-num { text-align: right; }
.py-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.py-form input, .py-form select { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.py-form .py-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.py-form input[type=checkbox] { width: auto; }
.py-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.py-btn-primary { background: #1e40af; color: #fff; }
.py-empty { color: #9ca3af; font-size: 0.875rem; padding: 1rem; }
</style>
<div class="py-header">
  <h1 class="py-title">Salaries</h1>
  <p class="py-subtitle">Manage employee salary rates. Payroll-eligible employees are included in payroll runs.</p>
</div>
<?php if (!empty($_GET['msg'])): ?><div class="py-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div><?php endif; ?>
<?php if (!empty($_GET['err'])): ?><div class="py-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div><?php endif; ?>
<div class="py-card">
  <h3>Salary records</h3>
  <?php if (empty($list)): ?>
  <p class="py-empty">No salary records. Add one below.</p>
  <?php else: ?>
  <table class="py-table">
    <thead><tr><th>Employee</th><th>Type</th><th class="py-num">Basic rate</th><th>Effective</th><th>Payroll eligible</th></tr></thead>
    <tbody>
    <?php foreach ($list as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['last_name'] . ', ' . $row['first_name'] . ' (' . $row['employee_code'] . ')'); ?></td>
        <td><?php echo htmlspecialchars($row['salary_type']); ?></td>
        <td class="py-num"><?php echo number_format((float)$row['basic_rate'], 2); ?></td>
        <td><?php echo htmlspecialchars($row['effective_date']); ?></td>
        <td><?php echo !empty($row['payroll_eligible']) ? 'Yes' : 'No'; ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <h3 style="margin-top: 1rem;">Add salary</h3>
  <form class="py-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="add">
    <label>Employee *</label>
    <select name="employee_id" required>
      <option value="">Select</option>
      <?php foreach ($employees as $e): ?>
      <option value="<?php echo (int)$e['employee_id']; ?>"><?php echo htmlspecialchars($e['employee_code'] . ' – ' . $e['last_name'] . ', ' . $e['first_name']); ?></option>
      <?php endforeach; ?>
    </select>
    <div class="py-row">
      <div><label>Salary type *</label><select name="salary_type"><option value="Monthly" selected>Monthly</option><option value="Hourly">Hourly</option><option value="Daily">Daily</option><option value="Annual">Annual</option></select></div>
      <div><label>Basic rate *</label><input type="number" name="basic_rate" step="0.01" min="0" required></div>
    </div>
    <div class="py-row">
      <div><label>Effective date *</label><input type="date" name="effective_date" required></div>
      <div><label>Tax status</label><input type="text" name="tax_status" value="Single"></div>
    </div>
    <label><input type="checkbox" name="payroll_eligible" value="1" checked> Payroll eligible</label>
    <div class="py-row">
      <div><label>Bank name</label><input type="text" name="bank_name"></div>
      <div><label>Bank account</label><input type="text" name="bank_account"></div>
    </div>
    <button type="submit" class="py-btn py-btn-primary">Add salary</button>
  </form>
</div>
</div>
