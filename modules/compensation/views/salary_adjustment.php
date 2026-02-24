<?php
/**
 * Salary Adjustment - Clean Professional Design
 * Controlled changes to base pay with approval workflow
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/SalaryAdjustment.php';
require_once __DIR__ . '/../models/SalaryBand.php';
require_once __DIR__ . '/../../../config/currency.php';

$adjustmentModel = new SalaryAdjustment();
$bandModel = new SalaryBand();

$adjustments = $adjustmentModel->getAllWithDetails([]);
$bands = $bandModel->getAllWithDetails(false);
$employees = $adjustmentModel->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);

// Calculate status counts
$pendingCount = count(array_filter($adjustments, fn($a) => strtolower($a['status']) === 'pending'));
$approvedCount = count(array_filter($adjustments, fn($a) => strtolower($a['status']) === 'approved'));
$rejectedCount = count(array_filter($adjustments, fn($a) => strtolower($a['status']) === 'rejected'));

$handlerUrl = 'modules/compensation/salary_adjustment_handler.php';
$filterStatus = $_GET['filter'] ?? 'all';
$pageTitle = 'Salary Adjustment';
require __DIR__ . '/partials/header.php';

// Helper: read enum values from information_schema for a given table.column
function getEnumOptions($table, $column) {
    try {
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['COLUMN_TYPE'])) return [];
        if (preg_match("/^enum\\((.*)\\)$/i", $row['COLUMN_TYPE'], $m)) {
            preg_match_all("/'((?:[^']|\\\\')*)'/", $m[1], $matches);
            return $matches[1] ?? [];
        }
    } catch (Exception $e) {
        return [];
    }
    return [];
}
?>

<style>
.page-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px;
}

.msg-bar {
  padding: 12px 24px;
  margin-bottom: 24px;
  border-radius: 8px;
  display: flex;
  gap: 8px;
}

.msg-bar .msg {
  background: #d1fae5;
  color: #065f46;
  padding: 12px 16px;
  border-radius: 8px;
  flex: 1;
}

.msg-bar .err {
  background: #fee2e2;
  color: #991b1b;
  padding: 12px 16px;
  border-radius: 8px;
  flex: 1;
}

.page-header {
  background: white;
  padding: 24px;
  margin-bottom: 24px;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.header-content h1 {
  margin: 0 0 4px 0;
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

.header-content p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.header-actions button {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
}

.header-actions button:hover {
  background: #2563eb;
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.search-filters {
  background: white;
  padding: 16px 24px;
  margin-bottom: 24px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 250px;
  position: relative;
}

.search-box input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.search-box input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.filter-tabs {
  display: flex;
  gap: 12px;
}

.filter-btn {
  padding: 8px 16px;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 13px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #f0f9ff;
}

.filter-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.section-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.section-card-title {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.table-responsive {
  overflow-x: auto;
}

.salary-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.salary-table thead {
  background: #f9fafb;
}

.salary-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.salary-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}

.salary-table tbody tr:hover {
  background: #f9fafb;
}

.salary-table .code-badge {
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 12px;
  font-weight: 600;
}

.salary-table .num {
  text-align: right;
  font-weight: 500;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.status-approved {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.status-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.action-btn {
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #f0f9ff;
}

.action-btn.approve {
  border-color: #10b981;
  color: #10b981;
}

.action-btn.approve:hover {
  background: #f0fdf4;
  border-color: #059669;
  color: #059669;
}

.empty-message {
  text-align: center;
  padding: 48px 24px;
  color: #9ca3af;
  font-size: 14px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 13px;
}

.add-form {
  display: none;
  background: #f9fafb;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  margin-bottom: 20px;
}

.add-form.visible {
  display: block;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.form-row.full {
  grid-template-columns: 1fr;
}

.form-row.three {
  grid-template-columns: 1fr 1fr 1fr;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-weight: 600;
  color: #374151;
  font-size: 13px;
}

.required {
  color: #ef4444;
}

.form-input,
.form-select,
.form-textarea {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.info-box {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  padding: 12px 16px;
  border-radius: 4px;
  margin-bottom: 16px;
  font-size: 13px;
  color: #1e40af;
}

.validation-inline {
  font-size: 12px;
  color: #3b82f6;
  margin-top: 4px;
}

.validation-warning {
  font-size: 12px;
  color: #f59e0b;
  margin-top: 4px;
}
</style>

<?php if (!empty($_GET['msg']) || !empty($_GET['err'])): ?>
<div class="page-container">
  <div class="msg-bar">
    <?php if (!empty($_GET['msg'])): ?>
    <div class="msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['err'])): ?>
    <div class="err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="page-container">

  <!-- Page Header -->
  <div class="page-header">
    <div class="header-content">
      <h1>Salary Adjustment</h1>
      <p>Manage salary changes with approval workflow</p>
    </div>
    <div class="header-actions">
      <button onclick="window.toggleSalaryAdjustmentForm('add-adjustment-form'); return false;">+ New Adjustment</button>
    </div>
  </div>

  <!-- Info Box -->
  <div class="info-box">
    <strong>📋 Info:</strong> All adjustments require approval. Changes outside salary bands require additional authorization.
  </div>

  <!-- Search & Filters -->
  <div class="search-filters">
    <div class="search-box">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
      <input id="comp-search" type="text" placeholder="Search adjustments, employees..." onkeypress="if(event.key==='Enter') performCompSearch();">
    </div>
    <div class="filter-tabs">
      <button class="filter-btn <?php echo $filterStatus === 'all' ? 'active' : ''; ?>" onclick="window.filterSalaryAdjustments('all'); return false;">All (<?php echo count($adjustments); ?>)</button>
      <button class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>" onclick="window.filterSalaryAdjustments('pending'); return false;">Pending (<?php echo $pendingCount; ?>)</button>
      <button class="filter-btn <?php echo $filterStatus === 'approved' ? 'active' : ''; ?>" onclick="window.filterSalaryAdjustments('approved'); return false;">Approved (<?php echo $approvedCount; ?>)</button>
      <button class="filter-btn <?php echo $filterStatus === 'rejected' ? 'active' : ''; ?>" onclick="window.filterSalaryAdjustments('rejected'); return false;">Rejected (<?php echo $rejectedCount; ?>)</button>
    </div>
  </div>

  <!-- Adjustment History Table -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Adjustment History</h3>
    </div>

    <?php 
    $displayAdjustments = $adjustments;
    if ($filterStatus !== 'all') {
        $displayAdjustments = array_filter($adjustments, fn($a) => strtolower($a['status']) === $filterStatus);
    }
    ?>

    <?php if (empty($displayAdjustments)): ?>
    <div class="empty-message">No salary adjustments found. Create one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="salary-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Previous</th>
            <th>New Amount</th>
            <th>Effective</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($displayAdjustments as $a): ?>
          <tr>
            <td><?php echo htmlspecialchars(($a['last_name'] ?? '') . ', ' . ($a['first_name'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars($a['adjustment_type']); ?></td>
            <td class="num"><?php echo $a['previous_amount'] !== null ? format_currency($a['previous_amount'], 0) : '—'; ?></td>
            <td class="num"><strong><?php echo format_currency($a['new_amount'], 0); ?></strong></td>
            <td><?php echo htmlspecialchars($a['effective_date']); ?></td>
            <td>
              <span class="status-badge status-<?php echo strtolower($a['status']); ?>">
                <?php 
                if (strtolower($a['status']) === 'pending') echo '⏳ Pending';
                elseif (strtolower($a['status']) === 'approved') echo '✓ Approved';
                else echo '✕ Rejected';
                ?>
              </span>
            </td>
            <td class="action-buttons">
              <?php if (strtolower($a['status']) === 'pending'): ?>
              <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                <button type="submit" class="action-btn approve" title="Approve">✓ Approve</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Create Adjustment Form -->
  <div class="section-card">
    <div id="add-adjustment-form" class="add-form" style="display: none;">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">New Salary Adjustment</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="create">
        
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Employee <span class="required">*</span></label>
            <select name="employee_id" required class="form-select">
              <option value="">Select Employee</option>
              <?php foreach ($employees as $emp): ?>
              <option value="<?php echo (int)$emp['employee_id']; ?>"><?php echo htmlspecialchars($emp['employee_code'] . ' – ' . $emp['last_name'] . ', ' . $emp['first_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Type <span class="required">*</span></label>
            <select name="adjustment_type" required class="form-select">
              <option value="">Select Type</option>
              <option value="Promotion">Promotion</option>
              <option value="Merit increase">Merit Increase</option>
              <option value="Market alignment">Market Alignment</option>
              <option value="Promotion">Cost of Living Adjustment</option>
              <option value="Correction">Correction</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Trigger <span class="required">*</span></label>
            <select name="trigger_type" required class="form-select">
              <option value="">Select Trigger</option>
              <option value="Performance review">Performance Review</option>
              <option value="Role change">Role Change</option>
              <option value="Policy update">Policy Update</option>
              <option value="Management decision">Management Decision</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Salary Band (Optional)</label>
            <select name="salary_band_id" class="form-select" onchange="window.showBandRange(this)">
              <option value="">— None —</option>
              <?php foreach ($bands as $b): ?>
              <option value="<?php echo (int)$b['id']; ?>" data-min="<?php echo (float)$b['min_salary']; ?>" data-max="<?php echo (float)$b['max_salary']; ?>"><?php echo htmlspecialchars($b['pay_grade_name'] . ($b['grade_level_name'] ? ' – ' . $b['grade_level_name'] : '')); ?></option>
              <?php endforeach; ?>
            </select>
            <div id="band-range" class="validation-inline" style="display: none;"></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Previous Amount</label>
            <input type="number" name="previous_amount" class="form-input" placeholder="Auto-filled" readonly>
          </div>
          <div class="form-group">
            <label class="form-label">New Amount <span class="required">*</span></label>
            <input type="number" name="new_amount" required class="form-input" placeholder="0" step="1" min="0">
            <div id="band-warning" class="validation-warning" style="display: none;"></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Effective Date <span class="required">*</span></label>
            <input type="date" name="effective_date" required class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">End Date (if temporary)</label>
            <input type="date" name="end_date" class="form-input">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label class="form-label">Reason <span class="required">*</span></label>
            <textarea name="reason" required class="form-textarea" placeholder="e.g., Promotion to Senior; market adjustment for retention"></textarea>
          </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Create (Pending Approval)</button>
          <button type="button" class="btn btn-sm" onclick="window.toggleSalaryAdjustmentForm('add-adjustment-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
window.APP_CURRENCY = '<?php echo app_currency_symbol(); ?>';

// Toggle form visibility - namespaced to window to prevent collisions
window.toggleSalaryAdjustmentForm = function(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('visible');
        if (form.classList.contains('visible')) {
            setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
        }
    }
};

window.showBandRange = function(select) {
    const option = select.options[select.selectedIndex];
    const range = document.getElementById('band-range');
    
    if (option.value && option.dataset.min && option.dataset.max) {
        const CUR = window.APP_CURRENCY || '₱';
        range.textContent = 'Band range: ' + CUR + Number(option.dataset.min).toLocaleString() + ' – ' + CUR + Number(option.dataset.max).toLocaleString();
        range.style.display = 'block';
    } else {
        range.style.display = 'none';
    }
};

document.querySelector('input[name="new_amount"]')?.addEventListener('change', function() {
    const bandSelect = document.querySelector('select[name="salary_band_id"]');
    const option = bandSelect.options[bandSelect.selectedIndex];
    const warning = document.getElementById('band-warning');
    
    if (option.value && option.dataset.min && option.dataset.max) {
        const newAmount = parseFloat(this.value);
        const min = parseFloat(option.dataset.min);
        const max = parseFloat(option.dataset.max);
        
        if (newAmount < min || newAmount > max) {
            warning.textContent = '⚠️ Outside band range. Requires additional approval.';
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }
});

function performCompSearch(){
    var q = document.getElementById('comp-search')?.value || '';
    var params = new URLSearchParams(window.location.search);
    if(q) params.set('q', q); else params.delete('q');
    window.location.search = params.toString();
}

window.filterSalaryAdjustments = function(status) {
    var params = new URLSearchParams(window.location.search);
    params.set('filter', status);
    window.location.search = params.toString();
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
