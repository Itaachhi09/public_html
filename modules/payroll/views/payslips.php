<?php
/**
 * Payroll – Payslips. List payslips from processed runs. PHP/HTML/CSS only; no JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayrollRunEmployee.php';

$pre = new PayrollRunEmployee();
$payslips = $pre->getAllPayslips();
?>
<div class="main-content payroll-payslips-content">
<style>
.payroll-payslips-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.py-header { margin-bottom: 1rem; }
.py-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.py-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.py-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.py-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.py-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.py-table th, .py-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.py-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.py-table .py-num { text-align: right; }
.py-empty { color: #9ca3af; font-size: 0.875rem; padding: 1rem; }
</style>
<div class="py-header">
  <h1 class="py-title">Payslips</h1>
  <p class="py-subtitle">Payslips are generated when you process a payroll run. Listed below by run and employee.</p>
</div>
<div class="py-card">
  <h3>Payslips</h3>
  <?php if (empty($payslips)): ?>
  <p class="py-empty">No payslips yet. Create a payroll run and process it from Payroll Processing.</p>
  <?php else: ?>
  <table class="py-table">
    <thead>
      <tr>
        <th>Payslip #</th>
        <th>Employee</th>
        <th>Period</th>
        <th>Pay date</th>
        <th class="py-num">Basic</th>
        <th class="py-num">Gross</th>
        <th class="py-num">Deductions</th>
        <th class="py-num">Net</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($payslips as $p): ?>
      <tr>
        <td><?php echo htmlspecialchars($p['payslip_number'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($p['last_name'] . ', ' . $p['first_name'] . ' (' . $p['employee_code'] . ')'); ?></td>
        <td><?php echo htmlspecialchars($p['period_name']); ?></td>
        <td><?php echo htmlspecialchars($p['pay_date']); ?></td>
        <td class="py-num"><?php echo number_format((float)$p['basic_pay'], 2); ?></td>
        <td class="py-num"><?php echo number_format((float)$p['gross_pay'], 2); ?></td>
        <td class="py-num"><?php echo number_format((float)$p['total_deductions'], 2); ?></td>
        <td class="py-num"><?php echo number_format((float)$p['net_pay'], 2); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
</div>
