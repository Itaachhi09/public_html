<?php
/**
 * Compensation History and Versioning View
 * Track versions of pay components, salary bands, policies, employee assignments. Attributes: version number, effective date, changed by, reason. PHP/HTML/CSS only; no JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/CompensationVersionHistory.php';

$versionModel = new CompensationVersionHistory();
$history = $versionModel->getAll([]);
$approvedActive = $versionModel->getApprovedActive();

$handlerUrl = 'modules/compensation/compensation_versioning_handler.php';

$typeLabels = [
    'pay_component' => 'Pay component',
    'salary_band' => 'Salary band',
    'policy' => 'Policy',
    'employee_assignment' => 'Employee assignment',
];
?>
<div class="main-content compensation-versioning-content">
<style>
.compensation-versioning-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.cvh-header { margin-bottom: 1rem; }
.cvh-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.cvh-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.cvh-rules { background: #f0fdf4; border: 1px solid #22c55e; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #166534; }
.cvh-rules strong { display: block; margin-bottom: 0.25rem; }
.cvh-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.cvh-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.cvh-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.cvh-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.cvh-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.cvh-table th, .cvh-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.cvh-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.cvh-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.cvh-form input, .cvh-form select, .cvh-form textarea { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.cvh-form textarea { min-height: 60px; resize: vertical; }
.cvh-form .cvh-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.cvh-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.cvh-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.cvh-empty { color: #9ca3af; font-size: 0.875rem; padding: 0.75rem; }
.cvh-badge { font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; }
.cvh-badge-active { background: #d1fae5; color: #065f46; }
.cvh-badge-inactive { background: #e5e7eb; color: #374151; }
.cvh-badge-approved { background: #dbeafe; color: #1e40af; }
</style>

<div class="cvh-header">
  <h1 class="cvh-title">Compensation History and Versioning</h1>
  <p class="cvh-subtitle">Maintain audit integrity. Track versions of: Pay components, Salary bands, Policies, Employee assignments. Attributes: Version number, Effective date, Changed by, Reason.</p>
</div>

<div class="cvh-rules">
  <strong>Rules</strong>
  Old versions stay immutable. Payroll reads only approved active versions. Output: Complete compensation audit trail. Clean and safe Payroll dependency.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="cvh-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="cvh-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<!-- Record new version -->
<div class="cvh-card">
  <h3>Record new version</h3>
  <form class="cvh-form" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="record_version">
    <div class="cvh-row">
      <div>
        <label>Entity type *</label>
        <select name="entity_type" required>
          <option value="">Select type</option>
          <option value="pay_component">Pay component</option>
          <option value="salary_band">Salary band</option>
          <option value="policy">Policy</option>
          <option value="employee_assignment">Employee assignment</option>
        </select>
      </div>
      <div>
        <label>Entity ID *</label>
        <input type="number" name="entity_id" required min="1" placeholder="ID of the record being versioned">
      </div>
    </div>
    <div class="cvh-row">
      <div><label>Effective date *</label><input type="date" name="effective_date" required></div>
      <div></div>
    </div>
    <label>Reason *</label>
    <textarea name="reason" required placeholder="e.g. Band update per policy; new pay component"></textarea>
    <button type="submit" class="cvh-btn cvh-btn-primary">Record version</button>
  </form>
</div>

<!-- Full audit trail -->
<div class="cvh-card">
  <h3>Version history (audit trail)</h3>
  <?php if (empty($history)): ?>
  <p class="cvh-empty">No version history yet. Record a version above.</p>
  <?php else: ?>
  <table class="cvh-table">
    <thead>
      <tr>
        <th>Entity type</th>
        <th>Entity ID</th>
        <th>Version</th>
        <th>Effective date</th>
        <th>Changed by</th>
        <th>Reason</th>
        <th>Status</th>
        <th>Active</th>
        <th>Created</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($history as $h): ?>
      <tr>
        <td><?php echo htmlspecialchars($typeLabels[$h['entity_type']] ?? $h['entity_type']); ?></td>
        <td><?php echo (int)$h['entity_id']; ?></td>
        <td><?php echo (int)$h['version_number']; ?></td>
        <td><?php echo htmlspecialchars($h['effective_date']); ?></td>
        <td><?php echo $h['changed_by'] ? (int)$h['changed_by'] : '—'; ?></td>
        <td><?php echo htmlspecialchars(mb_substr($h['reason'], 0, 50) . (mb_strlen($h['reason']) > 50 ? '…' : '')); ?></td>
        <td><span class="cvh-badge cvh-badge-approved"><?php echo htmlspecialchars($h['status']); ?></span></td>
        <td><span class="cvh-badge <?php echo !empty($h['is_active']) ? 'cvh-badge-active' : 'cvh-badge-inactive'; ?>"><?php echo !empty($h['is_active']) ? 'Active' : '—'; ?></span></td>
        <td><?php echo htmlspecialchars($h['created_at'] ?? '—'); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Approved active (for Payroll) -->
<div class="cvh-card">
  <h3>Approved active versions (Payroll reads these only)</h3>
  <?php if (empty($approvedActive)): ?>
  <p class="cvh-empty">No approved active versions. Payroll dependency: only these rows are used.</p>
  <?php else: ?>
  <table class="cvh-table">
    <thead>
      <tr>
        <th>Entity type</th>
        <th>Entity ID</th>
        <th>Version</th>
        <th>Effective date</th>
        <th>Reason</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($approvedActive as $h): ?>
      <tr>
        <td><?php echo htmlspecialchars($typeLabels[$h['entity_type']] ?? $h['entity_type']); ?></td>
        <td><?php echo (int)$h['entity_id']; ?></td>
        <td><?php echo (int)$h['version_number']; ?></td>
        <td><?php echo htmlspecialchars($h['effective_date']); ?></td>
        <td><?php echo htmlspecialchars(mb_substr($h['reason'], 0, 60) . (mb_strlen($h['reason']) > 60 ? '…' : '')); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<p class="cvh-subtitle" style="margin-top: 0.5rem;">Output: Complete compensation audit trail. Clean and safe Payroll dependency.</p>
</div>
