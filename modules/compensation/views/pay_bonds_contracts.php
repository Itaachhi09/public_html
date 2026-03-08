<?php
/**
 * Pay Bonds & Contracts - Modern Design
 * Manage pay contracts and employee assignments
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

$allContracts = $contractModel->getAllWithGrade(false);
$allAssignments = $assignmentModel->getAllWithDetails([]);
$activeContracts = $contractModel->getActive();
$payGrades = $payGradeModel->getAllWithBands(false);
$employees = $assignmentModel->query(
    'SELECT employee_id, employee_code, first_name, last_name FROM employees WHERE employment_status = ? ORDER BY last_name, first_name',
    ['Active']
);

// Calculate contract status counts
$activeContractCount = 0;
$expiredContractCount = 0;
foreach ($allContracts as $c) {
    $isActive = $c['end_date'] && $c['end_date'] >= date('Y-m-d');
    if ($isActive) $activeContractCount++;
    else $expiredContractCount++;
}

// Calculate assignment status counts
$activeAssignmentCount = 0;
$endedAssignmentCount = 0;
foreach ($allAssignments as $a) {
    $assignmentActive = (!$a['effective_to'] || $a['effective_to'] >= date('Y-m-d')) && $a['contract_end'] && $a['contract_end'] >= date('Y-m-d');
    if ($assignmentActive) $activeAssignmentCount++;
    else $endedAssignmentCount++;
}

$handlerUrl = 'modules/compensation/pay_bonds_contracts_handler.php';
$pageTitle = 'Pay Bonds & Contracts';
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

.filter-tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
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

.table-responsive {
  overflow-x: auto;
}

.contracts-table,
.assignments-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.contracts-table thead,
.assignments-table thead {
  background: #f9fafb;
}

.contracts-table th,
.assignments-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.contracts-table td,
.assignments-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}

.contracts-table tbody tr:hover,
.assignments-table tbody tr:hover {
  background: #f9fafb;
}

.code-badge {
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

.status-badge.status-active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.status-expired,
.status-badge.status-ended {
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
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
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
      <h1>Pay Bonds & Contracts</h1>
      <p>Manage compensation contracts and employee assignments</p>
    </div>
    <div class="header-actions">
      <button onclick="toggleForm('add-contract-form'); return false;">+ New Contract</button>
    </div>
  </div>

  <!-- Info Box -->
  <div class="info-box">
    <strong>📋 Info:</strong> Active contract required for compensation. Expired contracts block new entries.
  </div>

  <!-- PAY CONTRACTS SECTION -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Pay Contracts</h3>
    </div>

    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterContracts('all'); return false;">All (<?php echo count($allContracts); ?>)</button>
      <button class="filter-btn" onclick="filterContracts('active'); return false;">Active (<?php echo $activeContractCount; ?>)</button>
      <button class="filter-btn" onclick="filterContracts('expired'); return false;">Expired (<?php echo $expiredContractCount; ?>)</button>
    </div>

    <?php if (empty($allContracts)): ?>
    <div class="empty-message">No pay contracts yet. Create one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="contracts-table" id="contracts-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Pay Grade</th>
            <th>Period</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allContracts as $c): 
            $isActive = $c['end_date'] && $c['end_date'] >= date('Y-m-d');
            $period = htmlspecialchars($c['start_date']) . ' to ' . htmlspecialchars($c['end_date']);
          ?>
          <tr class="contract-row" data-status="<?php echo $isActive ? 'active' : 'expired'; ?>">
            <td><span class="code-badge"><?php echo htmlspecialchars($c['code']); ?></span></td>
            <td><?php echo htmlspecialchars($c['name']); ?></td>
            <td><?php echo htmlspecialchars($c['pay_grade_name']); ?></td>
            <td><?php echo $period; ?></td>
            <td>
              <span class="status-badge status-<?php echo $isActive ? 'active' : 'expired'; ?>">
                <?php echo $isActive ? '✓ Active' : '✕ Expired'; ?>
              </span>
            </td>
            <td class="action-buttons">
              <?php if ($isActive): ?>
              <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                <input type="hidden" name="action" value="deactivate_contract">
                <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                <button type="submit" class="action-btn" title="Deactivate">Deactivate</button>
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

    <!-- Create Contract Form -->
    <div id="add-contract-form" class="add-form" style="display: none;">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">New Pay Contract</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="create_contract">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Code <span class="required">*</span></label>
            <input type="text" name="code" required class="form-input" placeholder="ER_DOCTOR_CONTRACT" maxlength="50">
          </div>
          <div class="form-group">
            <label class="form-label">Name <span class="required">*</span></label>
            <input type="text" name="name" required class="form-input" placeholder="ER Doctor Contract" maxlength="255">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label class="form-label">Pay Grade <span class="required">*</span></label>
            <select name="pay_grade_id" required class="form-select">
              <option value="">Select Pay Grade</option>
              <?php foreach ($payGrades as $pg): ?>
              <option value="<?php echo (int)$pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Start Date <span class="required">*</span></label>
            <input type="date" name="start_date" required class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">End Date <span class="required">*</span></label>
            <input type="date" name="end_date" required class="form-input">
          </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Create Contract</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-contract-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- EMPLOYEE ASSIGNMENTS SECTION -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Employee Assignments</h3>
      <button class="btn btn-primary btn-sm" onclick="toggleForm('add-assignment-form'); return false;">+ Assign Employee</button>
    </div>

    <div class="filter-tabs">
      <button class="filter-btn active" onclick="filterAssignments('all'); return false;">All (<?php echo count($allAssignments); ?>)</button>
      <button class="filter-btn" onclick="filterAssignments('active'); return false;">Active (<?php echo $activeAssignmentCount; ?>)</button>
      <button class="filter-btn" onclick="filterAssignments('ended'); return false;">Ended (<?php echo $endedAssignmentCount; ?>)</button>
    </div>

    <?php if (empty($allAssignments)): ?>
    <div class="empty-message">No assignments yet. Assign an employee to a contract.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="assignments-table" id="assignments-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Contract</th>
            <th>Period</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allAssignments as $a): 
            $assignmentActive = (!$a['effective_to'] || $a['effective_to'] >= date('Y-m-d')) && $a['contract_end'] && $a['contract_end'] >= date('Y-m-d');
            $period = htmlspecialchars($a['effective_from']) . ' to ' . ($a['effective_to'] ? htmlspecialchars($a['effective_to']) : '∞');
          ?>
          <tr class="assignment-row" data-status="<?php echo $assignmentActive ? 'active' : 'ended'; ?>">
            <td><?php echo htmlspecialchars(($a['last_name'] ?? '') . ', ' . ($a['first_name'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars($a['contract_name']); ?></td>
            <td><?php echo $period; ?></td>
            <td>
              <span class="status-badge status-<?php echo $assignmentActive ? 'active' : 'ended'; ?>">
                <?php echo $assignmentActive ? '✓ Active' : '✕ Ended'; ?>
              </span>
            </td>
            <td class="action-buttons">
              <?php if ($assignmentActive && !$a['effective_to']): ?>
              <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                <input type="hidden" name="action" value="end_assignment">
                <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                <input type="hidden" name="effective_to" value="<?php echo date('Y-m-d'); ?>">
                <button type="submit" class="action-btn" title="End">End Assignment</button>
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

    <!-- Assign Employee Form -->
    <div id="add-assignment-form" class="add-form" style="display: none;">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">Assign Employee to Contract</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="assign_employee">

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
            <label class="form-label">Contract <span class="required">*</span></label>
            <select name="contract_id" required class="form-select">
              <option value="">Select Contract</option>
              <?php foreach ($activeContracts as $c): ?>
              <option value="<?php echo (int)$c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
              <?php if (empty($activeContracts)): ?>
              <option value="" disabled>No active contracts</option>
              <?php endif; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Effective From <span class="required">*</span></label>
            <input type="date" name="effective_from" required class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">Effective To (optional)</label>
            <input type="date" name="effective_to" class="form-input">
          </div>
        </div>

        <div class="form-actions" style="justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Create Assignment</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-assignment-form'); return false;">Cancel</button>
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

function filterContracts(status) {
    const rows = document.querySelectorAll('.contract-row');
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
    document.querySelectorAll('#contracts-table~.filter-tabs .filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function filterAssignments(status) {
    const rows = document.querySelectorAll('.assignment-row');
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
    document.querySelectorAll('#assignments-table~.filter-tabs .filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function performCompSearch(){
    var q = document.getElementById('comp-search')?.value.toLowerCase() || '';
    var rows = document.querySelectorAll('table.assignments-table tbody tr');
    
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
