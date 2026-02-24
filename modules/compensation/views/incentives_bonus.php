<?php
/**
 * Incentives & Bonuses - Modern Design
 * Log workload-based earnings with approval workflow
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/IncentiveType.php';
require_once __DIR__ . '/../models/IncentiveEvent.php';

$typeModel = new IncentiveType();
$eventModel = new IncentiveEvent();

$allEvents = $eventModel->getAllWithDetails([]);
$approvedTypes = $typeModel->getApproved();
$employees = $eventModel->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);

// Calculate status counts
$pendingCount = count(array_filter($allEvents, fn($e) => strtolower($e['status']) === 'pending'));
$approvedCount = count(array_filter($allEvents, fn($e) => strtolower($e['status']) === 'approved'));
$rejectedCount = count(array_filter($allEvents, fn($e) => strtolower($e['status']) === 'rejected'));

$handlerUrl = 'modules/compensation/incentives_bonus_handler.php';
$pageTitle = 'Incentives & Bonuses';
require __DIR__ . '/partials/header.php';
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

.incentive-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.incentive-table thead {
  background: #f9fafb;
}

.incentive-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.incentive-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}

.incentive-table tbody tr:hover {
  background: #f9fafb;
}

.incentive-table .code-badge {
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 12px;
  font-weight: 600;
}

.incentive-table .num {
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
.form-select {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
      <h1>Incentives & Bonuses</h1>
      <p>Log workload-based earnings with approval workflow</p>
    </div>
    <div class="header-actions">
      <button onclick="toggleForm('add-event-form'); return false;">+ Log Event</button>
    </div>
  </div>

  <!-- Info Box -->
  <div class="info-box">
    <strong>📋 Info:</strong> Only approved procedure types allowed. Each event requires approval before payroll processing.
  </div>

  <!-- Search & Filters -->
  <div class="search-filters">
    <div class="search-box">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
      <input id="comp-search" type="text" placeholder="Search events, employees, procedures..." onkeypress="if(event.key==='Enter') performCompSearch();">
    </div>
    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterEvents('all'); return false;">All (<?php echo count($allEvents); ?>)</button>
      <button class="filter-btn" onclick="filterEvents('pending'); return false;">Pending (<?php echo $pendingCount; ?>)</button>
      <button class="filter-btn" onclick="filterEvents('approved'); return false;">Approved (<?php echo $approvedCount; ?>)</button>
      <button class="filter-btn" onclick="filterEvents('rejected'); return false;">Rejected (<?php echo $rejectedCount; ?>)</button>
    </div>
  </div>

  <!-- Incentive Events Table -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Incentive Events</h3>
    </div>

    <?php if (empty($allEvents)): ?>
    <div class="empty-message">No incentive events logged yet. Create one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="incentive-table" id="incentive-table-body">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Procedure</th>
            <th class="num">Quantity</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allEvents as $e): ?>
          <tr class="event-row" data-status="<?php echo strtolower($e['status']); ?>">
            <td><?php echo htmlspecialchars(($e['last_name'] ?? '') . ', ' . ($e['first_name'] ?? '')); ?></td>
            <td>
              <span class="code-badge"><?php echo htmlspecialchars($e['procedure_code'] ?? '—'); ?></span>
              <?php echo htmlspecialchars($e['procedure_name'] ?? ''); ?>
            </td>
            <td class="num"><?php echo (int)$e['quantity']; ?></td>
            <td><?php echo htmlspecialchars($e['duty_date']); ?></td>
            <td>
              <span class="status-badge status-<?php echo strtolower($e['status']); ?>">
                <?php 
                if (strtolower($e['status']) === 'pending') echo '⏳ Pending';
                elseif (strtolower($e['status']) === 'approved') echo '✓ Approved';
                else echo '✕ Rejected';
                ?>
              </span>
            </td>
            <td class="action-buttons">
              <?php if (strtolower($e['status']) === 'pending'): ?>
              <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?php echo (int)$e['id']; ?>">
                <button type="submit" class="action-btn approve" title="Approve">✓ Approve</button>
              </form>
              <?php else: ?>
              <span style="color: #d1d5db; font-size: 13px;">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Log Event Form -->
  <div class="section-card">
    <div id="add-event-form" class="add-form" style="display: none;">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">Log New Event</h4>
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
            <label class="form-label">Procedure <span class="required">*</span></label>
            <select name="procedure_type_id" required class="form-select">
              <option value="">Select Procedure</option>
              <?php foreach ($approvedTypes as $t): ?>
              <option value="<?php echo (int)$t['id']; ?>"><?php echo htmlspecialchars($t['code'] . ' – ' . $t['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Duty Date <span class="required">*</span></label>
            <input type="date" name="duty_date" required class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">Quantity <span class="required">*</span></label>
            <input type="number" name="quantity" required class="form-input" value="1" min="1">
          </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Log Event (Pending Approval)</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-event-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
function toggleForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('visible');
        if (form.classList.contains('visible')) {
            setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
        }
    }
}

function filterEvents(status) {
    const rows = document.querySelectorAll('.event-row');
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        if (status === 'all') {
            row.style.display = '';
        } else if (status === rowStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update filter button states
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function performCompSearch(){
    var q = document.getElementById('comp-search')?.value.toLowerCase() || '';
    var rows = document.querySelectorAll('table.incentive-table tbody tr');
    
    rows.forEach(row => {
        var text = row.textContent.toLowerCase();
        if (q === '' || text.indexOf(q) !== -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
