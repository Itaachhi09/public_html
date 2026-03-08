<?php
/**
 * Approval Workflow - Modern Design
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/CompensationApprovalRequest.php';

$approvalModel = new CompensationApprovalRequest();
$allRequests = $approvalModel->getAll([]);
$pending = $approvalModel->getPending();

// Calculate status counts
$statusCounts = [];
foreach ($allRequests as $r) {
    $status = strtolower($r['status']);
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
}

$handlerUrl = 'modules/compensation/compensation_approval_handler.php';
$pageTitle = 'Approval Workflow';
require __DIR__ . '/partials/header.php';

// Helper to read enum values from information_schema
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

// Build friendly labels for request types from DB enum
$typeLabels = [];
$rtypes = getEnumOptions('compensation_approval_requests', 'request_type');
if (!empty($rtypes)) {
    foreach ($rtypes as $t) {
        $typeLabels[$t] = ucwords(str_replace(['_', '-'], ' ', $t));
    }
} else {
    $typeLabels = [
        'salary_above_band' => 'Salary above band',
        'double_pay_tagging' => 'Double pay tagging',
        'manual_adjustment' => 'Manual adjustment',
        'policy_exception' => 'Policy exception',
    ];
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

.approval-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.approval-table thead {
  background: #f9fafb;
}

.approval-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.approval-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}

.approval-table tbody tr:hover {
  background: #f9fafb;
  cursor: pointer;
}

.approval-table .code-badge {
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 12px;
  font-weight: 600;
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

.status-badge.status-under_review {
  background: #dbeafe;
  color: #1e40af;
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

.action-btn.reject {
  border-color: #ef4444;
  color: #ef4444;
}

.action-btn.reject:hover {
  background: #fef2f2;
  border-color: #dc2626;
  color: #dc2626;
}

.audit-note {
  font-size: 13px;
  color: #6b7280;
  font-style: italic;
  padding: 10px 16px !important;
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
  margin-top: 20px;
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
</style>

<?php 
$msg = $_GET['msg'] ?? $_SESSION['compensation_approval_msg'] ?? null;
$err = $_GET['err'] ?? $_SESSION['compensation_approval_err'] ?? null;
?>
<?php if (!empty($msg) || !empty($err)): ?>
<div class="page-container">
  <div class="msg-bar">
    <?php if (!empty($msg)): ?>
    <div class="msg"><?php echo htmlspecialchars(is_string($msg) && strpos($msg, '%') !== false ? urldecode($msg) : $msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($err)): ?>
    <div class="err"><?php echo htmlspecialchars(is_string($err) && strpos($err, '%') !== false ? urldecode($err) : $err); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php
// Clear session variables after display
unset($_SESSION['compensation_approval_msg'], $_SESSION['compensation_approval_err']);
?>
<?php endif; ?>

<div class="page-container">

  <!-- Page Header -->
  <div class="page-header">
    <div class="header-content">
      <h1>Approval Workflow</h1>
      <p>Review and approve compensation requests</p>
    </div>
    <div class="header-actions">
      <button onclick="toggleForm('add-request-form'); return false;">+ Submit Request</button>
    </div>
  </div>

  <!-- Info Box -->
  <div class="info-box">
    <strong>📋 Info:</strong> Only approved items reach payroll. All actions audited.
  </div>

  <!-- Search & Filters -->
  <div class="search-filters">
    <div class="search-box">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
      <input id="comp-search" type="text" placeholder="Search requests, types, references..." onkeypress="if(event.key==='Enter') performCompSearch();">
    </div>
    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterRequests('all'); return false;">All (<?php echo count($allRequests); ?>)</button>
      <button class="filter-btn" onclick="filterRequests('pending'); return false;">Pending (<?php echo $statusCounts['pending'] ?? 0; ?>)</button>
      <button class="filter-btn" onclick="filterRequests('under_review'); return false;">Under Review (<?php echo $statusCounts['under_review'] ?? 0; ?>)</button>
      <button class="filter-btn" onclick="filterRequests('approved'); return false;">Approved (<?php echo $statusCounts['approved'] ?? 0; ?>)</button>
      <button class="filter-btn" onclick="filterRequests('rejected'); return false;">Rejected (<?php echo $statusCounts['rejected'] ?? 0; ?>)</button>
    </div>
  </div>

  <!-- Approval Queue Table -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Approval Queue</h3>
    </div>

    <?php if (empty($allRequests)): ?>
    <div class="empty-message">No approval requests. Submit one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="approval-table" id="approval-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Reference</th>
            <th>Requested By</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allRequests as $r): 
            $status = strtolower($r['status']);
          ?>
          <tr class="request-row" data-status="<?php echo $status; ?>">
            <td><?php echo htmlspecialchars(substr($r['requested_at'] ?? '', 0, 10)); ?></td>
            <td><?php echo htmlspecialchars($typeLabels[$r['request_type']] ?? $r['request_type']); ?></td>
            <td><span class="code-badge"><?php echo htmlspecialchars($r['reference_type'] ?? '—'); ?></span></td>
            <td><?php echo htmlspecialchars($r['requested_by'] ?? '—'); ?></td>
            <td>
              <span class="status-badge status-<?php echo str_replace(' ', '_', $status); ?>">
                <?php 
                if ($status === 'pending') echo '⏳ Pending';
                elseif ($status === 'under_review') echo '👁 Under Review';
                elseif ($status === 'approved') echo '✓ Approved';
                else echo '✕ Rejected';
                ?>
              </span>
            </td>
            <td class="action-buttons">
              <?php if ($status === 'pending' || $status === 'under_review'): ?>
              <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                <button type="submit" class="action-btn approve" title="Approve">✓ Approve</button>
              </form>
              <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                <button type="submit" class="action-btn reject" title="Reject">✕ Reject</button>
              </form>
              <?php else: ?>
              <span style="color: #d1d5db; font-size: 13px;">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php if (!empty($r['approval_notes']) || $r['reviewed_at']): ?>
          <tr style="background: #f9fafb;">
            <td colspan="6" class="audit-note">
              <?php if ($r['reviewed_at']): ?><strong>Reviewed:</strong> <?php echo htmlspecialchars($r['reviewed_at']); ?><?php endif; ?>
              <?php if (!empty($r['approval_notes'])): ?> — <?php echo htmlspecialchars($r['approval_notes']); ?><?php endif; ?>
            </td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Submit Request Form -->
  <div class="section-card">
    <div id="add-request-form" class="add-form" style="display: none;">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">Submit Approval Request</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="create_request">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Type <span class="required">*</span></label>
            <select name="request_type" required class="form-select">
              <option value="">Select Type</option>
              <?php
              $reqOpts = getEnumOptions('compensation_approval_requests', 'request_type');
              if (!empty($reqOpts)) {
                  foreach ($reqOpts as $ro) {
                      echo '<option value="' . htmlspecialchars($ro) . '">' . htmlspecialchars(ucwords(str_replace('_',' ',$ro))) . '</option>';
                  }
              } else {
                  ?>
                  <option value="salary_above_band">Salary above band</option>
                  <option value="double_pay_tagging">Double pay tagging</option>
                  <option value="manual_adjustment">Manual adjustment</option>
                  <option value="policy_exception">Policy exception</option>
              <?php
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Reference</label>
            <input type="text" name="reference_type" class="form-input" placeholder="e.g. salary_adjustment" maxlength="100">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label class="form-label">Description <span class="required">*</span></label>
            <textarea name="description" required class="form-textarea" placeholder="Describe what requires approval and why"></textarea>
          </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Submit Request</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-request-form'); return false;">Cancel</button>
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

function filterRequests(status) {
    const rows = document.querySelectorAll('.request-row');
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        if (status === 'all') {
            row.style.display = '';
        } else if (status === rowStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
        
        // Also show/hide associated audit notes
        const nextRow = row.nextElementSibling;
        if (nextRow && nextRow.cells && nextRow.cells[0]?.classList.contains('audit-note')) {
            nextRow.style.display = row.style.display;
        }
    });
    
    // Update filter button states
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function performCompSearch(){
    var q = document.getElementById('comp-search')?.value.toLowerCase() || '';
    var rows = document.querySelectorAll('table.approval-table tbody tr');
    
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

function filterRequests(status) {
    const rows = document.querySelectorAll('.request-row');
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        if (status === 'all') {
            row.style.display = '';
        } else if (status === rowStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
        
        // Also show/hide associated audit notes
        const nextRow = row.nextElementSibling;
        if (nextRow && nextRow.cells && nextRow.cells[0]?.classList.contains('audit-note')) {
            nextRow.style.display = row.style.display;
        }
    });
    
    // Update filter button states
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function performCompSearch(){
    var q = document.getElementById('comp-search')?.value || '';
    var params = new URLSearchParams(window.location.search);
    if(q) params.set('q', q); else params.delete('q');
    window.location.search = params.toString();
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
