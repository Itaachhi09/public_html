<?php
/**
 * Salary Planning - Enhanced Professional Design
 * Pay Grades > Grade Levels > Salary Bands
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayGrade.php';
require_once __DIR__ . '/../models/GradeLevel.php';
require_once __DIR__ . '/../models/SalaryBand.php';
require_once __DIR__ . '/../../../config/currency.php';

$payGradeModel = new PayGrade();
$gradeLevelModel = new GradeLevel();
$salaryBandModel = new SalaryBand();

$payGrades = $payGradeModel->getAllWithBands(false);
$gradeLevels = $gradeLevelModel->getAllWithGrade(false);
$bands = $salaryBandModel->getAllWithDetails(false);

// Calculate band statistics
$activeBands = count(array_filter($bands, fn($b) => $b['status'] === 'Active'));
$inactiveBands = count(array_filter($bands, fn($b) => $b['status'] !== 'Active'));
$totalBands = count($bands);

$handlerUrl = 'modules/compensation/salary_planning_handler.php';
$filterStatus = $_GET['filter'] ?? 'all';
$pageTitle = 'Salary Planning';
require __DIR__ . '/partials/header.php';
?>

<style>
html, body { margin: 0; padding: 0; }

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
  align-items: flex-start;
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
  color: #1f2937;
}

.salary-table .num {
  text-align: right;
  font-weight: 500;
}

.salary-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.badge-active {
  background: #d1fae5;
  color: #065f46;
}

.badge-archived {
  background: #dbeafe;
  color: #0c4a6e;
}

.badge-inactive {
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

.rules-notice {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  padding: 12px 16px;
  border-radius: 4px;
  margin-bottom: 16px;
  font-size: 13px;
  color: #1e40af;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 16px;
  margin-top: 16px;
}

.summary-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 16px;
}

.summary-card-title {
  font-weight: 600;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.summary-check {
  font-size: 16px;
}

.summary-details {
  font-size: 13px;
  color: #6b7280;
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
      <h1>Salary Planning</h1>
      <p>Configure pay grades, salary bands, and grade levels</p>
    </div>
    <div class="header-actions">
      <button onclick="toggleForm('add-band-form'); return false;">+ New Band</button>
    </div>
  </div>

  <!-- Search & Filters -->
  <div class="search-filters">
    <div class="search-box">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
      <input id="comp-search" type="text" placeholder="Search pay grades, bands, levels..." onkeypress="if(event.key==='Enter') performCompSearch();">
    </div>
    <div class="filter-tabs">
      <button class="filter-btn <?php echo $filterStatus === 'all' ? 'active' : ''; ?>" onclick="filterBands('all'); return false;">All Status</button>
      <button class="filter-btn <?php echo $filterStatus === 'active' ? 'active' : ''; ?>" onclick="filterBands('active'); return false;">Active (<?php echo $activeBands; ?>)</button>
      <button class="filter-btn <?php echo $filterStatus === 'inactive' ? 'active' : ''; ?>" onclick="filterBands('inactive'); return false;">Inactive (<?php echo $inactiveBands; ?>)</button>
    </div>
  </div>

  <!-- 3. SALARY BANDS (Main Section) -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Salary Bands</h3>
    </div>

    <div class="rules-notice">
      <strong>📋 Rules:</strong> Assignments outside band require approval. Min ≤ Mid ≤ Max.
    </div>

    <!-- Add Form -->
    <div id="add-band-form" class="add-form">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">New Salary Band</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="create_salary_band">
        <div class="form-row full">
          <div class="form-group">
            <label class="form-label">Pay Grade <span class="required">*</span></label>
            <select name="pay_grade_id" required class="form-select">
              <option value="">Select Pay Grade</option>
              <?php foreach ($payGrades as $pg): ?>
              <option value="<?php echo (int) $pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row full">
          <div class="form-group">
            <label class="form-label">Grade Level (Optional)</label>
            <select name="grade_level_id" class="form-select">
              <option value="">Leave blank for all levels</option>
              <?php foreach ($gradeLevels as $gl): ?>
              <option value="<?php echo (int) $gl['id']; ?>"><?php echo htmlspecialchars($gl['pay_grade_name']) . ' - ' . htmlspecialchars($gl['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row three">
          <div class="form-group">
            <label class="form-label">Min <span class="required">*</span></label>
            <input type="number" name="min_salary" required class="form-input" placeholder="100000" step="1" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Mid <span class="required">*</span></label>
            <input type="number" name="midpoint_salary" required class="form-input" placeholder="150000" step="1" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Max <span class="required">*</span></label>
            <input type="number" name="max_salary" required class="form-input" placeholder="200000" step="1" min="0">
          </div>
        </div>
        <div class="form-actions" style="justify-content: flex-end;">
          <button type="submit" class="btn btn-primary btn-sm">Create Band</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-band-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Bands Table -->
    <?php 
    $displayBands = $bands;
    if ($filterStatus === 'active') {
        $displayBands = array_filter($bands, fn($b) => $b['status'] === 'Active');
    } elseif ($filterStatus === 'inactive') {
        $displayBands = array_filter($bands, fn($b) => $b['status'] !== 'Active');
    }
    ?>

    <?php if (empty($displayBands)): ?>
    <div class="empty-message">No salary bands found. Add one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="salary-table">
        <thead>
          <tr>
            <th>Pay Grade</th>
            <th>Level</th>
            <th class="num">Min Salary</th>
            <th class="num">Midpoint</th>
            <th class="num">Max Salary</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($displayBands as $b): ?>
          <tr>
            <td><span class="code-badge"><?php echo htmlspecialchars($b['pay_grade_code']); ?></span></td>
            <td><?php echo $b['grade_level_name'] ? htmlspecialchars($b['grade_level_name']) : '<span style="color:#9ca3af;">—</span>'; ?></td>
            <td class="num"><?php echo format_currency($b['min_salary'], 0); ?></td>
            <td class="num"><?php echo format_currency($b['midpoint_salary'], 0); ?></td>
            <td class="num"><?php echo format_currency($b['max_salary'], 0); ?></td>
            <td>
              <span class="salary-badge badge-<?php echo strtolower($b['status']); ?>">
                <?php echo $b['status'] === 'Active' ? '✓ Active' : '○ ' . $b['status']; ?>
              </span>
            </td>
            <td class="action-buttons">
              <button class="action-btn">✎ Edit</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- 1. PAY GRADES -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Pay Grades</h3>
      <button class="btn btn-primary btn-sm" onclick="toggleForm('add-paygrade-form'); return false;">+ Add</button>
    </div>

    <?php if (empty($payGrades)): ?>
    <div class="empty-message">No pay grades yet. Add one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="salary-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Bands</th>
            <th>Range</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payGrades as $pg): ?>
          <tr>
            <td><span class="code-badge"><?php echo htmlspecialchars($pg['code']); ?></span></td>
            <td><?php echo htmlspecialchars($pg['name']); ?></td>
            <td><strong><?php echo (int) ($pg['band_count'] ?? 0); ?></strong></td>
            <td><?php echo htmlspecialchars($pg['range_summary'] ?? '—'); ?></td>
            <td class="action-buttons">
              <button class="action-btn">✎ Edit</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- Add Form -->
    <div id="add-paygrade-form" class="add-form">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">New Pay Grade</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="create_pay_grade">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Code <span class="required">*</span></label>
            <input type="text" name="code" required class="form-input" placeholder="STAFF_NURSE" maxlength="50">
          </div>
          <div class="form-group">
            <label class="form-label">Name <span class="required">*</span></label>
            <input type="text" name="name" required class="form-input" placeholder="Staff Nurse" maxlength="255">
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary btn-sm">Create</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-paygrade-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. GRADE LEVELS -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Grade Levels</h3>
      <button class="btn btn-primary btn-sm" onclick="toggleForm('add-gradelevel-form'); return false;">+ Add</button>
    </div>

    <?php if (empty($gradeLevels)): ?>
    <div class="empty-message">No grade levels yet. Add one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="salary-table">
        <thead>
          <tr>
            <th>Pay Grade</th>
            <th>Code</th>
            <th>Level</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($gradeLevels as $gl): ?>
          <tr>
            <td><span class="code-badge"><?php echo htmlspecialchars($gl['pay_grade_code']); ?></span></td>
            <td><span class="code-badge"><?php echo htmlspecialchars($gl['code']); ?></span></td>
            <td><?php echo htmlspecialchars($gl['name']); ?></td>
            <td class="action-buttons">
              <button class="action-btn">✎ Edit</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- Add Form -->
    <div id="add-gradelevel-form" class="add-form">
      <h4 style="margin: 0 0 16px 0; color: #1f2937; font-weight: 600;">New Grade Level</h4>
      <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
        <input type="hidden" name="action" value="create_grade_level">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Pay Grade <span class="required">*</span></label>
            <select name="pay_grade_id" required class="form-select">
              <option value="">Select Pay Grade</option>
              <?php foreach ($payGrades as $pg): ?>
              <option value="<?php echo (int) $pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Code <span class="required">*</span></label>
            <input type="text" name="code" required class="form-input" placeholder="SENIOR" maxlength="50">
          </div>
          <div class="form-group">
            <label class="form-label">Name <span class="required">*</span></label>
            <input type="text" name="name" required class="form-input" placeholder="Senior Nurse" maxlength="255">
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary btn-sm">Create</button>
          <button type="button" class="btn btn-sm" onclick="toggleForm('add-gradelevel-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 4. VALIDATED SALARY STRUCTURE -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Validated Salary Structure</h3>
    </div>

    <div class="summary-grid">
      <?php 
      $activeGrades = array_filter($payGrades, fn($p) => $p['status'] === 'Active');
      if (empty($activeGrades)): 
      ?>
      <div class="summary-card">
        <div style="color: #9ca3af; font-size: 12px;">No pay grades configured yet.</div>
      </div>
      <?php else: ?>
      <?php foreach ($activeGrades as $pg): 
          $gradeBands = array_filter($bands, fn($b) => $b['pay_grade_id'] === $pg['id'] && $b['status'] === 'Active');
          $isValid = !empty($gradeBands);
      ?>
      <div class="summary-card">
        <div class="summary-card-title">
          <span class="summary-check"><?php echo $isValid ? '✓' : '○'; ?></span>
          <?php echo htmlspecialchars($pg['name']); ?>
        </div>
        <div class="summary-details">
          <?php if ($isValid): ?>
            <?php echo count($gradeBands); ?> band(s) configured. Range: <?php echo htmlspecialchars($pg['range_summary']); ?>
          <?php else: ?>
            No bands configured.
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
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

function performCompSearch(){
    var q = document.getElementById('comp-search')?.value || '';
    var params = new URLSearchParams(window.location.search);
    if(q) params.set('q', q); else params.delete('q');
    window.location.search = params.toString();
}

function filterBands(status) {
    var params = new URLSearchParams(window.location.search);
    params.set('filter', status);
    window.location.search = params.toString();
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
