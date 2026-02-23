<?php
/**
 * Employee Payroll Profile Module
 * Define payroll eligibility and profile details per employee. All data is stored in the database.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/EmployeePayrollProfile.php';
require_once __DIR__ . '/../models/EmployeeSalary.php';

$employeeProfile = new EmployeePayrollProfile();
$employeeSalary = new EmployeeSalary();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_employee_id = isset($_SESSION['payroll_profile_employee_id']) ? (int) $_SESSION['payroll_profile_employee_id'] : (isset($_GET['employee_id']) ? (int) $_GET['employee_id'] : null);
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
$modal = $_GET['modal'] ?? null;
if ($selected_employee_id && isset($_SESSION['payroll_profile_employee_id'])) {
    unset($_SESSION['payroll_profile_employee_id']);
}
$msg = isset($_SESSION['payroll_profile_msg']) ? trim($_SESSION['payroll_profile_msg']) : (isset($_GET['msg']) ? trim($_GET['msg']) : '');
$err = isset($_SESSION['payroll_profile_err']) ? trim($_SESSION['payroll_profile_err']) : (isset($_GET['err']) ? trim($_GET['err']) : '');
if (isset($_SESSION['payroll_profile_msg'])) unset($_SESSION['payroll_profile_msg']);
if (isset($_SESSION['payroll_profile_err'])) unset($_SESSION['payroll_profile_err']);

// Fetch all employees with their payroll profile (for table)
$employees = $employeeProfile->getEmployeesWithProfile($search);

// If an employee is selected, load their details and profile for the form
$selectedEmployee = null;
$selectedProfile = null;
$currentSalary = null;
if ($selected_employee_id) {
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.department_id, e.job_title_id, d.department_name, j.title AS job_title FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id LEFT JOIN job_titles j ON e.job_title_id = j.job_title_id WHERE e.employee_id = ?");
    $stmt->execute([$selected_employee_id]);
    $selectedEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($selectedEmployee) {
        $selectedProfile = $employeeProfile->getByEmployee($selected_employee_id);
        $currentSalary = $employeeSalary->getCurrentForEmployee($selected_employee_id);
    }
}

// Handler and dashboard URLs (work when view is loaded via fetch into dashboard)
// Use absolute paths from server root, not relative paths
$handlerUrl = '/public_html/modules/payroll/employee_payroll_profile_handler.php';
$dashboardUrl = '/public_html/dashboard.php';
$baseQuery = 'ref=payroll&page=employee_payroll_profile';
?>

<style>
  .profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }

  .section {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .section-header {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #3b82f6;
  }

  .search-box {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .search-box input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
  }

  .search-box button {
    padding: 0.75rem 1.5rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .search-box button:hover {
    background: #2563eb;
  }

  .table-container {
    overflow-x: auto;
    margin-bottom: 1.5rem;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  table thead {
    background: #f3f4f6;
  }

  table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #d1d5db;
  }

  table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
  }

  table tr:hover {
    background: #f9fafb;
  }

  .status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
  }

  .status-active {
    background: #d1fae5;
    color: #065f46;
  }

  .status-inactive {
    background: #fee2e2;
    color: #991b1b;
  }

  .status-no-profile {
    background: #fef3c7;
    color: #92400e;
  }

  .btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-primary {
    background: #3b82f6;
    color: white;
  }

  .btn-primary:hover {
    background: #2563eb;
  }

  .btn-secondary {
    background: #e5e7eb;
    color: #1f2937;
  }

  .btn-secondary:hover {
    background: #d1d5db;
  }

  .btn-danger {
    background: #ef4444;
    color: white;
  }

  .btn-danger:hover {
    background: #dc2626;
  }

  .form-group {
    margin-bottom: 1.5rem;
  }

  .form-group label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 14px;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
  }

  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .form-group input:disabled,
  .form-group select:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
    color: #9ca3af;
  }

  .form-group small {
    color: #6b7280;
    font-size: 12px;
    margin-top: 0.25rem;
    display: block;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
  }

  .form-row.full {
    grid-template-columns: 1fr;
  }

  .form-row.three {
    grid-template-columns: 1fr 1fr 1fr;
  }

  .form-section {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #3b82f6;
  }

  .form-section h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
  }

  .alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 14px;
  }

  .alert-info {
    background: #dbeafe;
    border: 1px solid #bfdbfe;
    color: #1e40af;
  }

  .alert-warning {
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
  }

  .alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal.show {
    display: block;
  }

  .modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 2rem;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  }

  .modal-header {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 1rem;
  }

  .modal-footer {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
  }

  .no-data {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
  }

  .no-data p {
    font-size: 14px;
    margin: 0;
  }

  /* Profile Modal Styles */
  .profile-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 2000;
    align-items: center;
    justify-content: center;
  }

  .profile-modal-overlay.active {
    display: flex;
  }

  .profile-modal {
    background: white;
    border-radius: 8px;
    max-width: 600px;
    width: 95%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
  }

  .profile-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
  }

  .profile-modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }

  .profile-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #9ca3af;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .profile-modal-close:hover {
    color: #1f2937;
    background: #e5e7eb;
    border-radius: 4px;
  }

  .profile-modal-body {
    padding: 2rem;
  }

  /* Form Modal Overlay */
  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
  }

  .modal-overlay.show {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-box {
    background: white;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    width: 95%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
    background: #f9fafb;
  }

  .modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }

  .modal-close {
    display: inline-block;
    text-decoration: none;
    color: #9ca3af;
    font-size: 24px;
    line-height: 1;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.3s ease;
  }

  .modal-close:hover {
    color: #1f2937;
    background: #e5e7eb;
  }

  .modal-body {
    padding: 2rem;
  }

  .modal-success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-size: 14px;
  }
</style>

<script>
  window.openProfileModal = function(mode, employeeId) {
    // Fetch modal content via AJAX without page refresh
    const currentUrl = new URL(window.location.href);
    const search = currentUrl.searchParams.get('search') || '';
    let url = 'dashboard.php?module=payroll&view=employee_payroll_profile&modal=' + mode + '&employee_id=' + employeeId;
    if (search) {
      url += '&search=' + encodeURIComponent(search);
    }
    
    fetch(url)
      .then(response => response.text())
      .then(html => {
        // Create a temporary container to parse the response
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const modalOverlay = temp.querySelector('.modal-overlay');
        
        if (modalOverlay) {
          // Remove old modals if any
          document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
          // Add new modal to page
          document.body.appendChild(modalOverlay);
          // Add the show class to display modal
          modalOverlay.classList.add('show');
        }
      })
      .catch(error => console.error('Error loading modal:', error));
  };

  window.closeProfileModal = function() {
    // Remove all modal overlays
    document.querySelectorAll('.modal-overlay').forEach(m => {
      m.classList.remove('show');
      setTimeout(() => m.remove(), 300);
    });
    // Reset URL back to main dashboard
    window.history.replaceState({}, '', 'dashboard.php');
  };
</script>

<div class="profile-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Employee Payroll Profile</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Define payroll eligibility and profile details for each employee. Employees without a payroll profile are excluded from payroll processing.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Note:</strong> Salary values are read-only and sourced from Compensation module. Only payroll-specific settings can be modified here.
    </div>
  </div>

  <?php if ($msg): ?>
  <div class="alert alert-info" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
  <div class="alert alert-danger" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>

  <!-- Search and Filter -->
  <div class="section">
    <form id="profileSearchForm" method="GET" action="<?php echo htmlspecialchars($dashboardUrl); ?>">
      <?php if ($selected_employee_id): ?><input type="hidden" name="employee_id" value="<?php echo $selected_employee_id; ?>"><?php endif; ?>
      <div class="search-box">
        <input type="text" name="search" placeholder="Search by Employee ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
      </div>
    </form>
  </div>

  <!-- Employees Table (from database) -->
  <div class="section">
    <h3 class="section-header">Employee Payroll Profiles</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Pay Type</th>
            <th>Payroll Status</th>
            <th>Profile Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($employees)): ?>
          <tr><td colspan="6" class="no-data">No employees found.</td></tr>
          <?php else: ?>
          <?php foreach ($employees as $emp): 
            $hasProfile = !empty($emp['profile_id']);
            $payTypeLabel = !empty($emp['pay_type']) ? htmlspecialchars($emp['pay_type']) : '---';
            $payrollActive = isset($emp['payroll_eligible']) && (int)$emp['payroll_eligible'] === 1;
            $name = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''));
            $code = $emp['employee_code'] ?? ('EMP-' . str_pad($emp['employee_id'], 3, '0', STR_PAD_LEFT));
            $q = $baseQuery . '&employee_id=' . (int)$emp['employee_id'];
            if ($search) $q .= '&search=' . urlencode($search);
            ?>
          <tr id="employee-row-<?php echo (int)$emp['employee_id']; ?>">
            <td><?php echo htmlspecialchars($code); ?></td>
            <td><?php echo htmlspecialchars($name); ?></td>
            <td><?php echo htmlspecialchars($payTypeLabel); ?></td>
            <td><span class="status-badge <?php echo $payrollActive ? 'status-active' : 'status-inactive'; ?>"><?php echo $payrollActive ? 'Active' : 'Inactive'; ?></span></td>
            <td><span class="status-badge <?php echo $hasProfile ? 'status-active' : 'status-no-profile'; ?>"><?php echo $hasProfile ? 'Configured' : 'No Profile'; ?></span></td>
            <td>
              <?php if ($hasProfile): ?>
              <button type="button" onclick="window.openProfileModal('edit', <?php echo (int)$emp['employee_id']; ?>)" class="btn btn-secondary">View/Edit</button>
              <?php else: ?>
              <button type="button" onclick="window.openProfileModal('create', <?php echo (int)$emp['employee_id']; ?>)" class="btn btn-primary">Create Profile</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Overlay (shown when modal parameter exists) -->
  <?php 
  $modalMode = isset($_GET['modal']) ? htmlspecialchars($_GET['modal']) : null;
  $modalEmployeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : null;
  $showModal = $modalMode && $modalEmployeeId && in_array($modalMode, ['create', 'edit'], true);
  $modalSuccess = isset($_GET['success']) ? (int)$_GET['success'] === 1 : false;
  
  if ($showModal) {
      // Load employee data for modal
      $modalEmployee = null;
      $modalProfile = null;
      $modalSalary = null;
      if ($modalMode === 'edit' || $modalMode === 'create') {
          $db = new Database();
          $conn = $db->connect();
          $stmt = $conn->prepare("SELECT e.employee_id, e.employee_code, e.first_name, e.last_name, e.department_id, e.job_title_id, d.department_name, j.title AS job_title FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id LEFT JOIN job_titles j ON e.job_title_id = j.job_title_id WHERE e.employee_id = ?");
          $stmt->execute([$modalEmployeeId]);
          $modalEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($modalEmployee) {
              $modalProfile = $employeeProfile->getByEmployee($modalEmployeeId);
              $modalSalary = $employeeSalary->getCurrentForEmployee($modalEmployeeId);
          }
      }
  ?>
  <div class="modal-overlay <?php echo $showModal ? 'show' : ''; ?>">
    <div class="modal-box">
      <div class="modal-header">
        <h2 class="modal-title"><?php echo $modalMode === 'create' ? 'Create Payroll Profile' : 'Edit Payroll Profile'; ?></h2>
        <button type="button" onclick="window.closeProfileModal()" class="modal-close">✕</button>
      </div>
      <div class="modal-body">
        <?php if ($modalSuccess): ?>
        <div class="modal-success">✓ Profile saved successfully!</div>
        <?php endif; ?>
        
        <?php if ($modalEmployee): 
          $p = $modalProfile;
          $emp = $modalEmployee;
          $sal = $modalSalary;
          $code = $emp['employee_code'] ?? ('EMP-' . str_pad($emp['employee_id'], 3, '0', STR_PAD_LEFT));
          $fullName = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''));
          $basicRate = $sal ? number_format((float)$sal['basic_rate'], 2) : '0.00';
        ?>
        <form id="profileForm" method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" data-ajax="1">
          <input type="hidden" name="employee_id" value="<?php echo (int)$emp['employee_id']; ?>">

          <div class="form-section">
            <h4>Employee Information</h4>
            <div class="form-row">
              <div class="form-group">
                <label>Employee ID</label>
                <input type="text" value="<?php echo htmlspecialchars($code); ?>" disabled>
              </div>
              <div class="form-group">
                <label>Employee Name</label>
                <input type="text" value="<?php echo htmlspecialchars($fullName); ?>" disabled>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Department</label>
                <input type="text" value="<?php echo htmlspecialchars($emp['department_name'] ?? '—'); ?>" disabled>
              </div>
              <div class="form-group">
                <label>Job Title</label>
                <input type="text" value="<?php echo htmlspecialchars($emp['job_title'] ?? '—'); ?>" disabled>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4>Payroll Configuration</h4>
            <div class="form-row">
              <div class="form-group">
                <label>Pay Type <span style="color: #ef4444;">*</span></label>
                <select name="pay_type" required>
                  <option value="">-- Select Pay Type --</option>
                  <option value="Per Duty" <?php echo ($p && ($p['pay_type'] ?? '') === 'Per Duty') ? 'selected' : ''; ?>>Per Duty</option>
                  <option value="Per Shift" <?php echo ($p && ($p['pay_type'] ?? '') === 'Per Shift') ? 'selected' : ''; ?>>Per Shift</option>
                  <option value="Hourly" <?php echo ($p && ($p['pay_type'] ?? '') === 'Hourly') ? 'selected' : ''; ?>>Hourly</option>
                  <option value="Daily" <?php echo ($p && ($p['pay_type'] ?? '') === 'Daily') ? 'selected' : ''; ?>>Daily</option>
                  <option value="Monthly" <?php echo ($p && ($p['pay_type'] ?? '') === 'Monthly') ? 'selected' : ''; ?>>Monthly</option>
                </select>
                <small>Determines how employee salary is calculated and paid</small>
              </div>
              <div class="form-group">
                <label>Payroll Status <span style="color: #ef4444;">*</span></label>
                <select name="payroll_status" required>
                  <option value="">-- Select Status --</option>
                  <option value="active" <?php echo ((!$p || !isset($p['payroll_eligible']) || (int)$p['payroll_eligible'] === 1) ? 'selected' : ''); ?>>Active</option>
                  <option value="inactive" <?php echo ($p && (int)($p['payroll_eligible'] ?? 1) === 0) ? 'selected' : ''; ?>>Inactive</option>
                </select>
                <small>Active employees are included in payroll processing</small>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4>Salary Information (Read-Only)</h4>
            <div class="form-row">
              <div class="form-group">
                <label>Basic Salary</label>
                <input type="number" value="<?php echo $basicRate; ?>" step="0.01" disabled>
                <small>From Compensation module</small>
              </div>
              <div class="form-group">
                <label>Total Allowances</label>
                <input type="number" value="0.00" step="0.01" disabled>
                <small>From Compensation module</small>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4>Tax Information</h4>
            <div class="form-row">
              <div class="form-group">
                <label>TIN (Tax Identification Number)</label>
                <input type="text" name="tin" placeholder="e.g., XXX-XXX-XXX-XXX" value="<?php echo htmlspecialchars($p['tax_identification_number'] ?? ''); ?>">
              </div>
              <div class="form-group">
                <label>Tax Status</label>
                <select name="tax_status">
                  <option value="">-- Select --</option>
                  <option value="taxable" <?php echo ($p && ($p['tax_status'] ?? '') === 'taxable') ? 'selected' : ''; ?>>Taxable</option>
                  <option value="exempt" <?php echo ($p && ($p['tax_status'] ?? '') === 'exempt') ? 'selected' : ''; ?>>Tax Exempt</option>
                </select>
                <small>Determines if subject to withholding tax</small>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4>Government Contributions</h4>
            <div class="form-row">
              <div class="form-group">
                <label>SSS Status</label>
                <select name="sss_status">
                  <option value="">-- Select --</option>
                  <option value="enrolled" <?php echo ($p && ($p['sss_status'] ?? '') === 'enrolled') ? 'selected' : ''; ?>>Enrolled</option>
                  <option value="not_enrolled" <?php echo ($p && ($p['sss_status'] ?? '') === 'not_enrolled') ? 'selected' : ''; ?>>Not Enrolled</option>
                  <option value="exempt" <?php echo ($p && ($p['sss_status'] ?? '') === 'exempt') ? 'selected' : ''; ?>>Exempt</option>
                </select>
              </div>
              <div class="form-group">
                <label>SSS Number</label>
                <input type="text" name="sss_number" placeholder="XX-XXXXXXX-X" value="<?php echo htmlspecialchars($p['sss_number'] ?? ''); ?>">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>PhilHealth Status</label>
                <select name="philhealth_status">
                  <option value="">-- Select --</option>
                  <option value="enrolled" <?php echo ($p && ($p['philhealth_status'] ?? '') === 'enrolled') ? 'selected' : ''; ?>>Enrolled</option>
                  <option value="not_enrolled" <?php echo ($p && ($p['philhealth_status'] ?? '') === 'not_enrolled') ? 'selected' : ''; ?>>Not Enrolled</option>
                  <option value="exempt" <?php echo ($p && ($p['philhealth_status'] ?? '') === 'exempt') ? 'selected' : ''; ?>>Exempt</option>
                </select>
              </div>
              <div class="form-group">
                <label>PhilHealth Number</label>
                <input type="text" name="philhealth_number" placeholder="XX-XXXXXXXXXX-X" value="<?php echo htmlspecialchars($p['philhealth_number'] ?? ''); ?>">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Pag-IBIG Status</label>
                <select name="pagibig_status">
                  <option value="">-- Select --</option>
                  <option value="enrolled" <?php echo ($p && ($p['pagibig_status'] ?? '') === 'enrolled') ? 'selected' : ''; ?>>Enrolled</option>
                  <option value="not_enrolled" <?php echo ($p && ($p['pagibig_status'] ?? '') === 'not_enrolled') ? 'selected' : ''; ?>>Not Enrolled</option>
                  <option value="exempt" <?php echo ($p && ($p['pagibig_status'] ?? '') === 'exempt') ? 'selected' : ''; ?>>Exempt</option>
                </select>
              </div>
              <div class="form-group">
                <label>Pag-IBIG Number</label>
                <input type="text" name="pagibig_number" placeholder="XXXX-XXXX-XXXX" value="<?php echo htmlspecialchars($p['pagibig_number'] ?? ''); ?>">
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4>Bank Account</h4>
            <div class="form-row">
              <div class="form-group">
                <label>Bank Name</label>
                <input type="text" name="bank_name" placeholder="e.g., BDO, BPI, Metrobank" value="<?php echo htmlspecialchars($p['bank_name'] ?? ''); ?>">
              </div>
              <div class="form-group">
                <label>Account Type</label>
                <select name="account_type">
                  <option value="">-- Select --</option>
                  <option value="savings" <?php echo ($p && ($p['account_type'] ?? '') === 'savings') ? 'selected' : ''; ?>>Savings</option>
                  <option value="checking" <?php echo ($p && ($p['account_type'] ?? '') === 'checking') ? 'selected' : ''; ?>>Checking</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Bank Account Number</label>
                <input type="text" name="bank_account_number" placeholder="Account number" value="<?php echo htmlspecialchars($p['bank_account_number'] ?? ''); ?>">
              </div>
              <div class="form-group">
                <label>Account Holder Name</label>
                <input type="text" name="account_holder_name" value="<?php echo htmlspecialchars($p['bank_account_holder'] ?? $fullName); ?>">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Account Status</label>
                <select name="account_status">
                  <option value="">-- Select --</option>
                  <option value="active" <?php echo (!$p || ($p['account_status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                  <option value="inactive" <?php echo ($p && ($p['account_status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
              </div>
            </div>
          </div>

          <div class="btn-group">
            <button type="submit" class="btn btn-primary"><?php echo $modalProfile ? 'Update' : 'Save'; ?> Profile</button>
            <button type="button" onclick="window.closeProfileModal()" class="btn btn-secondary" style="text-decoration: none;">Cancel</button>
          </div>
        </form>
        <?php if ($modalProfile): ?>
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
          <form id="deleteProfileForm" method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;" data-ajax="1">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="employee_id" value="<?php echo (int)$emp['employee_id']; ?>">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this profile?');">Delete Profile</button>
          </form>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <p style="color: #6b7280;">Employee not found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php } ?>

</div>
<script>
// Intercept profile create/update and delete forms to submit via AJAX and avoid full-page redirect
;(function(){
  const handlerUrl = '<?php echo $handlerUrl; ?>';

  function submitAjaxForm(form, onSuccess, onError) {
    const fd = new FormData(form);
    fetch(form.action || handlerUrl, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: fd,
      credentials: 'same-origin'
    })
    .then(async res => {
      const text = await res.text();
      try {
        const json = JSON.parse(text);
        onSuccess && onSuccess(json);
      } catch (err) {
        onError && onError(new Error('Invalid JSON response: ' + text));
      }
    })
    .catch(err => onError && onError(err));
  }

  const profileForm = document.getElementById('profileForm');
  if (profileForm) {
    profileForm.addEventListener('submit', function(e){
      e.preventDefault();
      const submitBtn = profileForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';
      submitAjaxForm(profileForm, function(result){
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        console.log('Profile save response:', result);
        if (result && result.success) {
          const successDiv = document.querySelector('.modal-success');
          if (successDiv) successDiv.textContent = '✓ Profile saved successfully!';
          else {
            const md = document.createElement('div'); md.className = 'modal-success'; md.textContent = '✓ Profile saved successfully!';
            const modalBody = document.querySelector('.modal-body'); if (modalBody) modalBody.insertBefore(md, modalBody.firstChild);
          }
          // Update the employee row in the table if server returned updated snapshot
          if (result.employee && result.employee.employee_id) {
            const rid = 'employee-row-' + result.employee.employee_id;
            const row = document.getElementById(rid);
            if (row) {
              // Pay type (3rd column, index 2)
              if (row.children[2]) row.children[2].textContent = result.employee.pay_type || '---';
              // Payroll status (4th column, index 3)
              if (row.children[3]) {
                const active = parseInt(result.employee.payroll_eligible || 0, 10) === 1;
                row.children[3].innerHTML = `<span class="status-badge ${active ? 'status-active' : 'status-inactive'}">${active ? 'Active' : 'Inactive'}</span>`;
              }
              // Profile status (5th column, index 4)
              if (row.children[4]) {
                const hasProfile = !!result.employee.has_profile;
                row.children[4].innerHTML = `<span class="status-badge ${hasProfile ? 'status-active' : 'status-no-profile'}">${hasProfile ? 'Configured' : 'No Profile'}</span>`;
              }
              // Action button (6th column) - update to View/Edit or Create Profile
              if (row.children[5]) {
                const btnHtml = result.employee.has_profile ?
                  `<button type="button" onclick="window.openProfileModal('edit', ${result.employee.employee_id})" class="btn btn-secondary">View/Edit</button>` :
                  `<button type="button" onclick="window.openProfileModal('create', ${result.employee.employee_id})" class="btn btn-primary">Create Profile</button>`;
                row.children[5].innerHTML = btnHtml;
              }
            }
          }
          // Refresh module list to ensure consistent state (in case some fields weren't returned)
          if (typeof window.loadPayrollPage === 'function') {
            // Reload payroll employee profile module
            window.loadPayrollPage(null, 'employee_payroll_profile');
          }
        } else {
          alert(result && result.message ? result.message : 'Failed to save profile');
        }
      }, function(err){
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        console.error('Profile save error:', err);
        alert('Error saving profile. Check console for details.');
      });
    });
  }

  const deleteForm = document.getElementById('deleteProfileForm');
  if (deleteForm) {
    deleteForm.addEventListener('submit', function(e){
      if (!confirm('Are you sure you want to delete this profile?')) { e.preventDefault(); return; }
      e.preventDefault();
      const btn = deleteForm.querySelector('button[type="submit"]');
      const orig = btn.textContent;
      btn.disabled = true; btn.textContent = 'Deleting...';
      submitAjaxForm(deleteForm, function(result){
        btn.disabled = false; btn.textContent = orig;
        if (result && result.success) {
          const md = document.createElement('div'); md.className = 'modal-success'; md.textContent = '✓ Profile deleted.';
          const modalBody = document.querySelector('.modal-body'); if (modalBody) modalBody.insertBefore(md, modalBody.firstChild);
          if (result.employee && result.employee.employee_id) {
            const rid = 'employee-row-' + result.employee.employee_id;
            const row = document.getElementById(rid);
            if (row) {
              // Set pay type to '---' if not provided
              if (row.children[2]) row.children[2].textContent = result.employee.pay_type || '---';
              // Payroll status
              if (row.children[3]) {
                const active = parseInt(result.employee.payroll_eligible || 0, 10) === 1;
                row.children[3].innerHTML = `<span class="status-badge ${active ? 'status-active' : 'status-inactive'}">${active ? 'Active' : 'Inactive'}</span>`;
              }
              // Profile status -> No Profile
              if (row.children[4]) {
                row.children[4].innerHTML = `<span class="status-badge status-no-profile">No Profile</span>`;
              }
              // Action -> Create Profile
              if (row.children[5]) {
                row.children[5].innerHTML = `<button type="button" onclick="window.openProfileModal('create', ${result.employee.employee_id})" class="btn btn-primary">Create Profile</button>`;
              }
            }
          }
          // Refresh the module list to reflect deletion
          if (typeof window.loadPayrollPage === 'function') {
            window.loadPayrollPage(null, 'employee_payroll_profile');
          }
        } else {
          alert(result && result.message ? result.message : 'Failed to delete profile');
        }
      }, function(err){
        btn.disabled = false; btn.textContent = orig;
        console.error('Delete profile error:', err);
        alert('Error deleting profile. Check console for details.');
      });
    });
  }
})();
</script>

<script>
  // Intercept the payroll profile search to load results via the dashboard loader
  (function(){
    const form = document.getElementById('profileSearchForm');
    if (!form) return;
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const fd = new FormData(form);
      const params = new URLSearchParams();
      for (const [k,v] of fd.entries()) {
        if (v !== null && String(v).trim() !== '') params.append(k, v);
      }
      const newQuery = params.toString();
      const viewUrl = 'dashboard.php?module=payroll&view=employee_payroll_profile' + (newQuery ? '&' + newQuery : '');

      // Update browser URL so results are shareable/bookmarkable
      history.replaceState(null, '', '?' + (newQuery ? ('module=payroll&view=employee_payroll_profile&' + newQuery) : 'module=payroll&view=employee_payroll_profile'));

      fetch(viewUrl)
        .then(resp => { if (!resp.ok) throw new Error('HTTP ' + resp.status); return resp.text(); })
        .then(html => {
          const scriptRegex = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
          const execRegex = new RegExp(scriptRegex);
          const scripts = [];
          let match;
          while ((match = execRegex.exec(html)) !== null) {
            const scriptTag = match[0];
            const scriptContent = scriptTag.replace(/<script[^>]*>/i, '').replace(/<\/script>/i, '');
            scripts.push(scriptContent);
          }

          const htmlWithoutScripts = html.replace(scriptRegex, '');
          const parser = new DOMParser();
          const doc = parser.parseFromString(htmlWithoutScripts, 'text/html');

          // Inject styles from module
          const styles = doc.querySelectorAll('style');
          styles.forEach(style => {
            try {
              const newStyle = document.createElement('style');
              newStyle.setAttribute('data-module-style', 'true');
              newStyle.textContent = style.textContent;
              document.head.appendChild(newStyle);
            } catch (e) { console.error('Error injecting style:', e); }
          });

          // Replace content area with returned module HTML
          const mainContent = doc.querySelector('.main-content');
          const contentArea = document.getElementById('content-area');
          if (contentArea) {
            try {
              if (mainContent) contentArea.innerHTML = mainContent.innerHTML;
              else contentArea.innerHTML = doc.body.innerHTML;
            } catch (e) { console.error('Error setting content:', e); }
          }

          // Execute embedded scripts
          setTimeout(() => {
            scripts.forEach(scriptContent => {
              try {
                const scriptEl = document.createElement('script');
                scriptEl.textContent = scriptContent;
                document.body.appendChild(scriptEl);
                scriptEl.parentNode.removeChild(scriptEl);
              } catch (e) { console.error('Error executing script:', e); }
            });
          }, 50);
        })
        .catch(err => {
          console.error('Error loading profile search results:', err);
          alert('Error loading results. Check console for details.');
        });
    });
  })();
</script>
