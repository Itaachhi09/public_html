<?php
/**
 * Payroll – Processing. Create runs, process draft runs. PHP/HTML/CSS only; no JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayrollRun.php';
require_once __DIR__ . '/../models/PayrollRunEmployee.php';

$run = new PayrollRun();
$runs = $run->getAll();
$handlerUrl = 'modules/payroll/payroll_processing_handler.php';
?>
<div class="main-content payroll-processing-content">
<style>
.payroll-processing-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
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
.py-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.py-form input { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.py-form .py-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.py-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; }
.py-btn-primary { background: #1e40af; color: #fff; }
.py-btn-success { background: #047857; color: #fff; }
.py-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.py-empty { color: #9ca3af; font-size: 0.875rem; padding: 1rem; }
.py-inline-form { display: inline; }
</style>
<div class="py-header">
  <h1 class="py-title">Payroll Processing</h1>
  <p class="py-subtitle">Create payroll runs (period name and dates). Process a Draft run to generate payslips from current salaries.</p>
</div>
<?php if (!empty($_GET['msg'])): ?><div class="py-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div><?php endif; ?>
<?php if (!empty($_GET['err'])): ?><div class="py-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div><?php endif; ?>
<div class="py-card">
  <h3>Payroll runs</h3>
  <?php if (empty($runs)): ?>
  <p class="py-empty">No runs yet. Create one below.</p>
  <?php else: ?>
  <table class="py-table">
    <thead><tr><th>Period</th><th>Start</th><th>End</th><th>Pay date</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($runs as $r): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['period_name']); ?></td>
        <td><?php echo htmlspecialchars($r['start_date']); ?></td>
        <td><?php echo htmlspecialchars($r['end_date']); ?></td>
        <td><?php echo htmlspecialchars($r['pay_date']); ?></td>
        <td><?php echo htmlspecialchars($r['status']); ?></td>
        <td>
          <?php if ($r['status'] === 'Draft'): ?>
          <form class="py-inline-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
            <input type="hidden" name="action" value="process_run">
            <input type="hidden" name="run_id" value="<?php echo (int)$r['id']; ?>">
            <button type="submit" class="py-btn py-btn-success py-btn-sm">Process</button>
          </form>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <h3 style="margin-top: 1rem;">Create payroll run</h3>
  <form class="py-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create_run">
    <label>Period name *</label>
    <input type="text" name="period_name" required placeholder="e.g. January 2025">
    <div class="py-row">
      <div><label>Start date *</label><input type="date" name="start_date" required></div>
      <div><label>End date *</label><input type="date" name="end_date" required></div>
    </div>
    <label>Pay date *</label>
    <input type="date" name="pay_date" required>
    <button type="submit" class="py-btn py-btn-primary">Create run</button>
  </form>
</div>
</div>
