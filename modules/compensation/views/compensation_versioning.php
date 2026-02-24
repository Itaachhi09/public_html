<?php
/**
 * Compensation Versioning - Modern Design
 * Track versions of salary components, bands, policies, assignments. Audit trail immutable.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/CompensationVersionHistory.php';

$versionModel = new CompensationVersionHistory();
$allHistory = $versionModel->getAll([]);
$approvedActive = $versionModel->getApprovedActive();

// Calculate status counts
$statusCounts = [];
foreach ($allHistory as $h) {
    $status = strtolower($h['status']);
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
}

$handlerUrl = 'modules/compensation/compensation_versioning_handler.php';
$pageTitle = 'Compensation Versioning';
require __DIR__ . '/partials/header.php';

// Build friendly labels for entity types from distinct values in DB (fallback to sensible defaults)
$typeLabels = [];
try {
    $rows = $versionModel->query("SELECT DISTINCT entity_type FROM compensation_version_history ORDER BY entity_type");
    if (!empty($rows)) {
        foreach ($rows as $r) {
            $key = $r['entity_type'];
            $typeLabels[$key] = ucwords(str_replace(['_', '-'], ' ', $key));
        }
    } else {
        // fallback defaults
        $typeLabels = [
            'pay_component' => 'Pay component',
            'salary_band' => 'Salary band',
            'policy' => 'Policy',
            'employee_assignment' => 'Employee assignment',
        ];
    }
} catch (Exception $e) {
    $typeLabels = [
        'pay_component' => 'Pay component',
        'salary_band' => 'Salary band',
        'policy' => 'Policy',
        'employee_assignment' => 'Employee assignment',
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

.version-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.version-table thead {
  background: #f9fafb;
}

.version-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.version-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}

.version-table tbody tr:hover {
  background: #f9fafb;
}

.version-table .code-badge {
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

.status-badge.status-approved {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.status-scheduled {
  background: #dbeafe;
  color: #1e40af;
}

.status-badge.status-active {
  background: #d1fae5;
  color: #065f46;
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
      <h1>Compensation Versioning</h1>
      <p>Track salary structure changes with complete audit trail</p>
    </div>
    <div class="header-actions">
      <button onclick="toggleForm('add-version-form'); return false;">+ Record Version</button>
    </div>
  </div>

  <!-- Info Box -->
  <div class="info-box">
    <strong>📋 Info:</strong> Payroll reads approved active versions only. All changes audited immutably.
  </div>

  <!-- Search & Filters -->
  <div class="search-filters">
    <div class="search-box">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
      <input id="comp-search" type="text" placeholder="Search history, entities, reasons..." onkeypress="if(event.key==='Enter') performCompSearch();">
    </div>
    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterHistory('all'); return false;">All (<?php echo count($allHistory); ?>)</button>
      <button class="filter-btn" onclick="filterHistory('pending'); return false;">Pending (<?php echo $statusCounts['pending'] ?? 0; ?>)</button>
      <button class="filter-btn" onclick="filterHistory('approved'); return false;">Approved (<?php echo $statusCounts['approved'] ?? 0; ?>)</button>
    </div>
  </div>

  <!-- Approved Active Versions -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Approved Active Versions (Payroll Reference)</h3>
    </div>

    <?php if (empty($approvedActive)): ?>
    <div class="empty-message">No approved active versions. Only these rows are used by payroll.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="version-table">
        <thead>
          <tr>
            <th>Entity</th>
            <th>Entity ID</th>
            <th>Version</th>
            <th>Effective Date</th>
            <th>Approved By</th>
            <th>Approved On</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($approvedActive as $v): ?>
          <tr>
            <td><?php echo htmlspecialchars($typeLabels[$v['entity_type']] ?? $v['entity_type']); ?></td>
            <td><span class="code-badge"><?php echo (int)$v['entity_id']; ?></span></td>
            <td><?php echo (int)$v['version_number']; ?></td>
            <td><?php echo htmlspecialchars(substr($v['effective_date'], 0, 10)); ?></td>
            <td><?php echo htmlspecialchars($v['approved_by'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars(substr($v['approved_at'] ?? '', 0, 10)); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Version History -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Version History</h3>
    </div>

    <?php if (empty($allHistory)): ?>
    <div class="empty-message">No version history. Record a version to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="version-table" id="history-table">
        <thead>
          <tr>
            <th>Entity</th>
            <th>Entity ID</th>
            <th>Version</th>
            <th>Effective Date</th>
            <th>Reason</th>
            <th>Requested By</th>
            <th>Approved By</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allHistory as $h): 
            $status = strtolower($h['status']);
          ?>
          <tr class="history-row" data-status="<?php echo $status; ?>">
            <td><?php echo htmlspecialchars($typeLabels[$h['entity_type']] ?? $h['entity_type']); ?></td>
            <td><span class="code-badge"><?php echo (int)$h['entity_id']; ?></span></td>
            <td><?php echo (int)$h['version_number']; ?></td>
            <td><?php echo htmlspecialchars(substr($h['effective_date'], 0, 10)); ?></td>
            <td title="<?php echo htmlspecialchars($h['reason']); ?>"><?php echo htmlspecialchars(mb_substr($h['reason'], 0, 40) . (mb_strlen($h['reason']) > 40 ? '…' : '')); ?></td>
            <td><?php echo htmlspecialchars($h['changed_by'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($h['approved_by'] ?? '—'); ?></td>
            <td>
              <span class="status-badge status-<?php echo $status; ?>">
                <?php 
                if ($status === 'pending') echo '⏳ Pending';
                elseif ($status === 'approved') echo '✓ Approved';
                else echo '📋 ' . ucfirst($status);
                ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Record New Version Form -->
  <div class="section-card">
    <div id="add-version-form" class="add-form" style="display: none;">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">Record New Version</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="record_version">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Entity Type <span class="required">*</span></label>
            <select name="entity_type" required class="form-select">
              <option value="">Select type</option>
              <option value="salary_band">Salary Band</option>
              <option value="pay_component">Pay Component</option>
              <option value="policy">Policy</option>
              <option value="employee_assignment">Employee Assignment</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Entity ID <span class="required">*</span></label>
            <input type="number" name="entity_id" required class="form-input" placeholder="Record ID" min="1">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label class="form-label">Effective Date <span class="required">*</span></label>
            <input type="date" name="effective_date" required class="form-input">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label class="form-label">Reason <span class="required">*</span></label>
            <textarea name="reason" required class="form-textarea" placeholder="e.g., Band update per policy change, promotion tier adjustment"></textarea>
          </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Record Version (Pending Approval)</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-version-form'); return false;">Cancel</button>
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

function filterHistory(status) {
    const rows = document.querySelectorAll('.history-row');
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
    var q = document.getElementById('comp-search')?.value || '';
    var params = new URLSearchParams(window.location.search);
    if(q) params.set('q', q); else params.delete('q');
    window.location.search = params.toString();
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>


<!-- Detail Panel -->
<div id="detail-panel" class="detail-panel">
    <button class="detail-panel-close" onclick="closeDetail()">✕</button>
    <div id="detail-content"></div>
</div>
<div id="overlay" class="overlay" onclick="closeDetail()"></div>

<script>
// Hardcoded history data for demo - replace with PHP data in production
const historyData = <?php echo json_encode($allHistory); ?>;

function openDetail(id) {
    const record = historyData.find(r => r.id == id);
    if (!record) return;
    
    const panel = document.getElementById('detail-panel');
    const overlay = document.getElementById('overlay');
    const content = document.getElementById('detail-content');
    
    const typeLabels = {
        'pay_component': 'Pay component',
        'salary_band': 'Salary band',
        'policy': 'Policy',
        'employee_assignment': 'Employee assignment'
    };
    
    content.innerHTML = `
        <h2>Version Details</h2>
        <div class="detail-row">
            <div class="detail-label">Entity</div>
            <div class="detail-value">${typeLabels[record.entity_type] || record.entity_type}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Entity ID</div>
            <div class="detail-value">${record.entity_id}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Version</div>
            <div class="detail-value">${record.version_number}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Effective Date</div>
            <div class="detail-value">${record.effective_date.split(' ')[0]}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Reason</div>
            <div class="detail-value">${record.reason}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Requested By</div>
            <div class="detail-value">${record.changed_by || '—'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Approved By</div>
            <div class="detail-value">${record.approved_by || '—'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">${record.status}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Created</div>
            <div class="detail-value">${record.created_at || '—'}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Approval Trail</div>
            <div class="detail-value">${record.reviewed_at ? 'Reviewed at ' + record.reviewed_at : 'Pending review'}</div>
        </div>
    `;
    
    panel.classList.add('visible');
    overlay.classList.add('visible');
}

function closeDetail() {
    document.getElementById('detail-panel').classList.remove('visible');
    document.getElementById('overlay').classList.remove('visible');
}
</script>
<script>
// Compensation table enhancer (per-view)
;(function(){
    try {
        var table = document.querySelector('table.comp-table');
        if (!table) return;
        var parent = table.parentElement;
        if (!parent.classList.contains('list-card')) {
            var card = document.createElement('div'); card.className='list-card'; parent.insertBefore(card, table); card.appendChild(table); parent = card;
        }
        var toolbar = document.createElement('div'); toolbar.className='comp-toolbar';
        var search = document.createElement('input'); search.type='search'; search.className='comp-search'; search.placeholder='Search table...';
        var pageSize = document.createElement('select'); pageSize.className='comp-page-size'; [10,25,50,100].forEach(function(n){ var o=document.createElement('option'); o.value=n; o.textContent=n+' / page'; pageSize.appendChild(o); });
        var pagination = document.createElement('div'); pagination.className='comp-pagination';
        toolbar.appendChild(search); toolbar.appendChild(pageSize); toolbar.appendChild(pagination);
        parent.parentElement.insertBefore(toolbar, parent);
        var tbody = table.tBodies[0]; var rows = Array.prototype.slice.call(tbody.rows); var currentPage=1;
        function renderPagination(totalPages){ pagination.innerHTML=''; if(totalPages<=1) return; var prev=document.createElement('button'); prev.textContent='Prev'; prev.disabled=currentPage===1; prev.addEventListener('click', function(){ if(currentPage>1){ currentPage--; update(); } }); pagination.appendChild(prev); for(var i=1;i<=totalPages;i++){ (function(p){ var b=document.createElement('button'); b.textContent=p; if(p===currentPage) b.classList.add('active'); b.addEventListener('click', function(){ currentPage=p; update(); }); pagination.appendChild(b); })(i); } var next=document.createElement('button'); next.textContent='Next'; next.disabled=currentPage===totalPages; next.addEventListener('click', function(){ if(currentPage<totalPages){ currentPage++; update(); } }); pagination.appendChild(next); }
        function update(){ var q=(search.value||'').toLowerCase().trim(); var filtered = rows.filter(function(r){ return r.textContent.toLowerCase().indexOf(q)!==-1; }); var pageSizeVal = parseInt(pageSize.value,10)||10; var totalPages = Math.max(1, Math.ceil(filtered.length/pageSizeVal)); if(currentPage>totalPages) currentPage=totalPages; rows.forEach(function(r){ r.style.display='none'; }); var start=(currentPage-1)*pageSizeVal, end=start+pageSizeVal; filtered.slice(start,end).forEach(function(r){ r.style.display=''; }); renderPagination(totalPages); }
        search.addEventListener('input', function(){ currentPage=1; update(); }); pageSize.addEventListener('change', function(){ currentPage=1; update(); }); update();
    } catch (e){ console && console.error('comp table enhancer', e); }
})();
function performCompSearch(){
    var q = document.getElementById('comp-search')?.value || '';
    var params = new URLSearchParams(window.location.search);
    if(q) params.set('q', q); else params.delete('q');
    window.location.search = params.toString();
}
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>