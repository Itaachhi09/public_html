<?php
/**
 * Salary Planning - Comprehensive Design
 * Pay Grades > Grade Levels > Salary Bands
 * Merged from salary_planning.php, salary_planning_modern.php, and salary_planning_old.php
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

$handlerUrl = '/PUBLIC_HTML/modules/compensation/salary_planning_handler.php';
$filterStatus = $_GET['filter'] ?? 'all';
$expandGrade = $_GET['expand_grade'] ?? '';
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
  flex-wrap: wrap;
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

.btn-outline {
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
}

.btn-outline:hover {
  background: #f9fafb;
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

.callout {
  display: flex;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 6px;
  margin-bottom: 16px;
  font-size: 13px;
}

.callout-info {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  color: #1e40af;
}

.callout-warning {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  color: #92400e;
}

.callout-success {
  background: #d1fae5;
  border-left: 4px solid #10b981;
  color: #065f46;
}

.callout-icon {
  font-size: 18px;
  line-height: 1;
}

.callout-text {
  flex: 1;
}

.callout-text strong {
  display: block;
  margin-bottom: 2px;
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

.code {
  background: #f3f4f6;
  padding: 2px 6px;
  border-radius: 3px;
  font-family: monospace;
  font-size: 12px;
}

.badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

.comp-toolbar {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
  padding: 8px;
  background: #f9fafb;
  border-radius: 6px;
}

.comp-search {
  flex: 1;
  padding: 6px 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 13px;
}

.comp-page-size {
  padding: 6px 10px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 13px;
}

.comp-pagination {
  display: flex;
  gap: 4px;
}

.comp-pagination button {
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.comp-pagination button.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.comp-pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modal Styles */
.modal-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  align-items: center;
  justify-content: center;
}

.modal-overlay.active {
  display: flex;
}

.modal-dialog {
  background: white;
  border-radius: 8px;
  box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #6b7280;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close:hover {
  color: #1f2937;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.modal-form-group {
  margin-bottom: 16px;
}

.modal-form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
  font-size: 13px;
}

.modal-form-group input,
.modal-form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.modal-form-group input:focus,
.modal-form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.modal-info-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.modal-info-item {
  background: #f9fafb;
  padding: 12px;
  border-radius: 6px;
}

.modal-info-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.modal-info-value {
  font-size: 16px;
  font-weight: 700;
  color: #1f2937;
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
      <h1>Salary Planning</h1>
      <p>Configure pay grades, salary bands, and grade levels</p>
    </div>
    <div class="header-actions">
      <button onclick="window.toggleSalaryPlanningForm('add-band-form'); return false;">+ New Band</button>
    </div>
  </div>

  <!-- Search & Filters -->
  <div class="search-filters">
    <div class="search-box">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
      <input id="comp-search" type="text" placeholder="Search pay grades, bands, levels..." onkeypress="if(event.key==='Enter') window.performCompSearch();">
    </div>
    <div class="filter-tabs">
      <button class="filter-btn <?php echo $filterStatus === 'all' ? 'active' : ''; ?>" onclick="window.filterBands('all'); return false;">All Status (<?php echo $totalBands; ?>)</button>
      <button class="filter-btn <?php echo $filterStatus === 'active' ? 'active' : ''; ?>" onclick="window.filterBands('active'); return false;">Active (<?php echo $activeBands; ?>)</button>
      <button class="filter-btn <?php echo $filterStatus === 'inactive' ? 'active' : ''; ?>" onclick="window.filterBands('inactive'); return false;">Inactive (<?php echo $inactiveBands; ?>)</button>
    </div>
  </div>

  <!-- 3. SALARY BANDS (Main Section) -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">3. Salary Bands</h3>
    </div>

    <div class="callout callout-warning">
      <div class="callout-icon">⚠</div>
      <div class="callout-text"><strong>Critical:</strong> Salary bands define compensation ranges. Min ≤ Mid ≤ Max must be maintained. Assignments outside bands require approval. This is the foundation of salary equity.</div>
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
          <button type="button" class="btn btn-sm" onclick="window.toggleSalaryPlanningForm('add-band-form'); return false;">Cancel</button>
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
      <table class="salary-table comp-table">
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
              <button class="btn btn-sm btn-outline" onclick="window.editSalaryBand(<?php echo (int) $b['id']; ?>, <?php echo (float) $b['min_salary']; ?>, <?php echo (float) $b['midpoint_salary']; ?>, <?php echo (float) $b['max_salary']; ?>, '<?php echo htmlspecialchars($b['pay_grade_id']); ?>'); return false;">✎ Edit</button>
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
      <h3 class="section-card-title">1. Pay Grades</h3>
      <button class="btn btn-primary btn-sm" onclick="window.toggleSalaryPlanningForm('add-paygrade-form'); return false;">+ Add</button>
    </div>

    <div class="callout callout-info">
      <div class="callout-icon">ℹ</div>
      <div class="callout-text"><strong>Pay Grades</strong> group positions by salary level. Each grade can have multiple bands and levels. Required to set up salary structure.</div>
    </div>

    <?php if (empty($payGrades)): ?>
    <div class="empty-message">No pay grades yet. Add one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="salary-table comp-table">
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
              <button class="btn btn-sm btn-outline" onclick="window.editPayGrade(<?php echo (int) $pg['id']; ?>, '<?php echo htmlspecialchars($pg['code']); ?>', '<?php echo htmlspecialchars($pg['name']); ?>'); return false;">✎ Edit</button>
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
          <button type="button" class="btn btn-sm" onclick="window.toggleSalaryPlanningForm('add-paygrade-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. GRADE LEVELS -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">2. Grade Levels</h3>
      <button class="btn btn-primary btn-sm" onclick="window.toggleSalaryPlanningForm('add-gradelevel-form'); return false;">+ Add</button>
    </div>

    <div class="callout callout-info">
      <div class="callout-icon">ℹ</div>
      <div class="callout-text"><strong>Grade Levels</strong> represent steps within a pay grade (e.g., Junior, Senior, Lead). Optional but recommended for career progression.</div>
    </div>

    <?php if (empty($gradeLevels)): ?>
    <div class="empty-message">No grade levels yet. Add one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="salary-table comp-table">
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
              <button class="btn btn-sm btn-outline" onclick="window.editGradeLevel(<?php echo (int) $gl['id']; ?>, <?php echo (int) $gl['pay_grade_id']; ?>, '<?php echo htmlspecialchars($gl['code']); ?>', '<?php echo htmlspecialchars($gl['name']); ?>'); return false;">✎ Edit</button>
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
          <button type="button" class="btn btn-sm" onclick="window.toggleSalaryPlanningForm('add-gradelevel-form'); return false;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 4. VALIDATED SALARY STRUCTURE -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">4. Validated Salary Structure</h3>
    </div>

    <div class="callout callout-success">
      <div class="callout-icon">✓</div>
      <div class="callout-text"><strong>Structure Status:</strong> All sections must be configured to enable employee compensation management. Use the checklist below.</div>
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

<!-- Edit Salary Band Modal -->
<div id="editBandModal" class="modal-overlay">
  <div class="modal-dialog">
    <div class="modal-header">
      <h2>Edit Salary Band</h2>
      <button class="modal-close" onclick="window.closeEditModal(); return false;">✕</button>
    </div>
    <div class="modal-body">
      <div class="modal-info-row">
        <div class="modal-info-item">
          <div class="modal-info-label">Pay Grade</div>
          <div class="modal-info-value" id="bandPayGrade">—</div>
        </div>
        <div class="modal-info-item">
          <div class="modal-info-label">Grade Level</div>
          <div class="modal-info-value" id="bandGradeLevel">—</div>
        </div>
        <div class="modal-info-item">
          <div class="modal-info-label">Status</div>
          <div class="modal-info-value" id="bandStatus">Active</div>
        </div>
      </div>
      
      <form id="editBandForm">
        <input type="hidden" id="bandId" name="id" value="">
        <input type="hidden" id="bandPayGradeId" name="pay_grade_id" value="">
        
        <div class="modal-form-group">
          <label>Minimum Salary <span class="required">*</span></label>
          <input type="number" id="bandMinSalary" name="min_salary" required step="1" min="0">
        </div>
        
        <div class="modal-form-group">
          <label>Midpoint Salary <span class="required">*</span></label>
          <input type="number" id="bandMidSalary" name="midpoint_salary" required step="1" min="0">
        </div>
        
        <div class="modal-form-group">
          <label>Maximum Salary <span class="required">*</span></label>
          <input type="number" id="bandMaxSalary" name="max_salary" required step="1" min="0">
        </div>
        
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px; border-radius: 4px; font-size: 13px; color: #1e40af; margin-top: 16px;">
          <strong>Rule:</strong> Minimum ≤ Midpoint ≤ Maximum must be maintained
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-sm" onclick="window.closeEditModal(); return false;">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="window.submitBandEdit(); return false;">Save Changes</button>
    </div>
  </div>
</div>

<!-- Edit Pay Grade Modal -->
<div id="editPayGradeModal" class="modal-overlay">
  <div class="modal-dialog">
    <div class="modal-header">
      <h2>Edit Pay Grade</h2>
      <button class="modal-close" onclick="window.closePayGradeModal(); return false;">✕</button>
    </div>
    <div class="modal-body">
      <form id="editPayGradeForm">
        <input type="hidden" id="payGradeId" name="id" value="">
        
        <div class="modal-form-group">
          <label>Code <span class="required">*</span></label>
          <input type="text" id="payGradeCode" name="code" required maxlength="50" placeholder="e.g., STAFF_NURSE">
        </div>
        
        <div class="modal-form-group">
          <label>Name <span class="required">*</span></label>
          <input type="text" id="payGradeName" name="name" required maxlength="255" placeholder="e.g., Staff Nurse">
        </div>
        
        <div class="modal-form-group">
          <label>Description</label>
          <textarea id="payGradeDescription" name="description" rows="3" placeholder="Description of this pay grade..." style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; font-family: inherit; width: 100%;"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-sm" onclick="window.closePayGradeModal(); return false;">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="window.submitPayGradeEdit(); return false;">Save Changes</button>
    </div>
  </div>
</div>

<!-- Edit Grade Level Modal -->
<div id="editGradeLevelModal" class="modal-overlay">
  <div class="modal-dialog">
    <div class="modal-header">
      <h2>Edit Grade Level</h2>
      <button class="modal-close" onclick="window.closeGradeLevelModal(); return false;">✕</button>
    </div>
    <div class="modal-body">
      <form id="editGradeLevelForm">
        <input type="hidden" id="gradeLevelId" name="id" value="">
        <input type="hidden" id="gradeLevelPayGradeId" name="pay_grade_id" value="">
        
        <div class="modal-form-group">
          <label>Code <span class="required">*</span></label>
          <input type="text" id="gradeLevelCode" name="code" required maxlength="50" placeholder="e.g., SN01">
        </div>
        
        <div class="modal-form-group">
          <label>Name <span class="required">*</span></label>
          <input type="text" id="gradeLevelName" name="name" required maxlength="255" placeholder="e.g., Junior Staff Nurse">
        </div>
        
        <div class="modal-form-group">
          <label>Sort Order</label>
          <input type="number" id="gradeLevelOrder" name="sort_order" value="0" min="0">
        </div>
        
        <div class="modal-form-group">
          <label>Description</label>
          <textarea id="gradeLevelDescription" name="description" rows="3" placeholder="Description of this grade level..." style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; font-family: inherit; width: 100%;"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-sm" onclick="window.closeGradeLevelModal(); return false;">Cancel</button>
      <button class="btn btn-primary btn-sm" onclick="window.submitGradeLevelEdit(); return false;">Save Changes</button>
    </div>
  </div>
</div>

<script>
// Toggle form visibility - namespaced to window
window.toggleSalaryPlanningForm = function(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('visible');
        if (form.classList.contains('visible')) {
            setTimeout(() => {
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        }
    }
};

// Legacy function name for backward compatibility
window.toggleForm = function(formId) {
    window.toggleSalaryPlanningForm(formId);
};

// Modal control functions
window.closeEditModal = function() {
    const modal = document.getElementById('editBandModal');
    if (modal) {
        modal.classList.remove('active');
    }
};

window.submitBandEdit = function() {
    const form = document.getElementById('editBandForm');
    const minSalary = parseFloat(document.getElementById('bandMinSalary').value);
    const midSalary = parseFloat(document.getElementById('bandMidSalary').value);
    const maxSalary = parseFloat(document.getElementById('bandMaxSalary').value);
    
    // Validate band order
    if (!(minSalary <= midSalary && midSalary <= maxSalary)) {
        alert('Error: Minimum ≤ Midpoint ≤ Maximum must be maintained');
        return;
    }
    
    // Submit form
    var submitForm = document.createElement('form');
    submitForm.method = 'POST';
    submitForm.action = '<?php echo htmlspecialchars($handlerUrl); ?>';
    submitForm.innerHTML = '<input type="hidden" name="action" value="update_band">' +
                          '<input type="hidden" name="id" value="' + document.getElementById('bandId').value + '">' +
                          '<input type="hidden" name="min_salary" value="' + minSalary + '">' +
                          '<input type="hidden" name="midpoint_salary" value="' + midSalary + '">' +
                          '<input type="hidden" name="max_salary" value="' + maxSalary + '">';
    document.body.appendChild(submitForm);
    submitForm.submit();
};

// Edit functions for salary planning items
window.editSalaryBand = function(id, minSalary, midpoint, maxSalary, payGradeId) {
    const modal = document.getElementById('editBandModal');
    if (!modal) return;
    
    // Populate modal with current values
    document.getElementById('bandId').value = id;
    document.getElementById('bandPayGradeId').value = payGradeId;
    document.getElementById('bandMinSalary').value = minSalary;
    document.getElementById('bandMidSalary').value = midpoint;
    document.getElementById('bandMaxSalary').value = maxSalary;
    
    // Get the row data for display
    const row = event.target.closest('tr');
    if (row) {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 2) {
            document.getElementById('bandPayGrade').textContent = cells[0].textContent.trim();
            document.getElementById('bandGradeLevel').textContent = cells[1].textContent.trim() || '—';
        }
    }
    
    // Show modal
    modal.classList.add('active');
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('bandMinSalary').focus();
    }, 100);
};

// Pay Grade Modal Functions
window.closePayGradeModal = function() {
    const modal = document.getElementById('editPayGradeModal');
    if (modal) {
        modal.classList.remove('active');
    }
};

window.editPayGrade = function(id, code, name) {
    // Fetch pay grade details from API
    fetch(`/PUBLIC_HTML/modules/compensation/api.php?action=getPayGradeDetail&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const grade = data.data;
                document.getElementById('payGradeId').value = grade.id;
                document.getElementById('payGradeCode').value = grade.code || '';
                document.getElementById('payGradeName').value = grade.name || '';
                document.getElementById('payGradeDescription').value = grade.description || '';
                
                const modal = document.getElementById('editPayGradeModal');
                if (modal) {
                    modal.classList.add('active');
                    setTimeout(() => {
                        document.getElementById('payGradeName').focus();
                    }, 100);
                }
            } else {
                alert('Error loading pay grade details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading pay grade details');
        });
};

window.submitPayGradeEdit = function() {
    const form = document.getElementById('editPayGradeForm');
    const id = document.getElementById('payGradeId').value;
    const code = document.getElementById('payGradeCode').value;
    const name = document.getElementById('payGradeName').value;
    const description = document.getElementById('payGradeDescription').value;
    
    if (!code || !name) {
        alert('Code and name are required');
        return;
    }
    
    // Submit via API
    fetch('/PUBLIC_HTML/modules/compensation/api.php?action=updatePayGrade', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            code: code,
            name: name,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pay grade updated successfully');
            window.closePayGradeModal();
            // Reload the page to refresh data
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update pay grade'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating pay grade');
    });
};

// Grade Level Modal Functions
window.closeGradeLevelModal = function() {
    const modal = document.getElementById('editGradeLevelModal');
    if (modal) {
        modal.classList.remove('active');
    }
};

window.editGradeLevel = function(id, payGradeId, code, name) {
    // Fetch grade level details from API
    fetch(`/PUBLIC_HTML/modules/compensation/api.php?action=getGradeLevelDetail&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const level = data.data;
                document.getElementById('gradeLevelId').value = level.id;
                document.getElementById('gradeLevelPayGradeId').value = level.pay_grade_id || payGradeId;
                document.getElementById('gradeLevelCode').value = level.code || '';
                document.getElementById('gradeLevelName').value = level.name || '';
                document.getElementById('gradeLevelOrder').value = level.sort_order || 0;
                document.getElementById('gradeLevelDescription').value = level.description || '';
                
                const modal = document.getElementById('editGradeLevelModal');
                if (modal) {
                    modal.classList.add('active');
                    setTimeout(() => {
                        document.getElementById('gradeLevelName').focus();
                    }, 100);
                }
            } else {
                alert('Error loading grade level details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading grade level details');
        });
};

window.submitGradeLevelEdit = function() {
    const form = document.getElementById('editGradeLevelForm');
    const id = document.getElementById('gradeLevelId').value;
    const payGradeId = document.getElementById('gradeLevelPayGradeId').value;
    const code = document.getElementById('gradeLevelCode').value;
    const name = document.getElementById('gradeLevelName').value;
    const sortOrder = document.getElementById('gradeLevelOrder').value;
    const description = document.getElementById('gradeLevelDescription').value;
    
    if (!code || !name) {
        alert('Code and name are required');
        return;
    }
    
    // Submit via API
    fetch('/PUBLIC_HTML/modules/compensation/api.php?action=updateGradeLevel', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            pay_grade_id: payGradeId,
            code: code,
            name: name,
            sort_order: sortOrder,
            description: description
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Grade level updated successfully');
            window.closeGradeLevelModal();
            // Reload the page to refresh data
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update grade level'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating grade level');
    });
};

window.performCompSearch = function() {
    var q = document.getElementById('comp-search')?.value.toLowerCase() || '';
    var tables = document.querySelectorAll('table.comp-table');
    
    tables.forEach(table => {
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            var text = row.textContent.toLowerCase();
            if (q === '' || text.indexOf(q) !== -1) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
};

window.filterBands = function(status) {
    // Update active button state
    var buttons = document.querySelectorAll('.filter-tabs .filter-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter bands table client-side
    var table = document.querySelector('table.comp-table');
    if (!table) return;
    
    var rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        var statusCell = row.querySelector('td:nth-child(6)');
        if (!statusCell) {
            row.style.display = 'none';
            return;
        }
        
        var statusText = statusCell.textContent.toLowerCase();
        
        if (status === 'all') {
            row.style.display = '';
        } else if (status === 'active' && statusText.includes('active')) {
            row.style.display = '';
        } else if (status === 'inactive' && !statusText.includes('active')) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
};

// Initialize table enhancements
;(function() {
    try {
        var tables = document.querySelectorAll('table.comp-table');
        tables.forEach(function(table) {
            var parent = table.parentElement;
            if (!parent.classList.contains('list-card')) {
                var card = document.createElement('div');
                card.className = 'list-card';
                parent.insertBefore(card, table);
                card.appendChild(table);
            }
        });
    } catch (e) {
        console && console.error('Table enhancement error:', e);
    }
})();

// Close modal when clicking outside dialog
document.getElementById('editBandModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        window.closeEditModal();
    }
});

document.getElementById('editPayGradeModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        window.closePayGradeModal();
    }
});

document.getElementById('editGradeLevelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        window.closeGradeLevelModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const bandModal = document.getElementById('editBandModal');
        if (bandModal && bandModal.classList.contains('active')) {
            window.closeEditModal();
        }
        const pgModal = document.getElementById('editPayGradeModal');
        if (pgModal && pgModal.classList.contains('active')) {
            window.closePayGradeModal();
        }
        const glModal = document.getElementById('editGradeLevelModal');
        if (glModal && glModal.classList.contains('active')) {
            window.closeGradeLevelModal();
        }
    }
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>