<?php
/**
 * Earnings Management Module
 * Build gross earnings from approved Compensation records
 * 
 * SECURITY: This view must only be accessed through dashboard.php
 * Direct access via URL is blocked by SYSTEM_INIT check below
 */

// Enforce single-entry routing: this file should only be loaded through dashboard.php
if (!defined('SYSTEM_INIT')) {
    http_response_code(403);
    die('No direct access allowed. Please use dashboard.php');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/BaseConfig.php';
require_once __DIR__ . '/../models/PayrollModel.php';

// Load controller data
require_once __DIR__ . '/../controllers/PayrollController.php';

// Check for modal request from query string
$modal = $_GET['modal'] ?? null;
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

// Get data from controller or use defaults if not available
$earnings = $controllerData['earnings'] ?? [];
$totalEmployees = $controllerData['totalEmployees'] ?? 0;
$totalGross = $controllerData['totalGross'] ?? 0;
$departments = $controllerData['departments'] ?? [];
$pendingCompensation = $controllerData['pendingCompensation'] ?? 0;
$filters = $controllerData['filters'] ?? [];

// For modal view, get employee details from database
$employeeEarnings = null;
if ($modal === 'view' && $employee_id) {
    $payrollModel = new PayrollModel();
    $employeeEarnings = $payrollModel->calculateGrossEarnings($employee_id);
}
?>

<style>
  .earnings-container {
    max-width: 1400px;
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

  .filter-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 14px;
  }

  .form-group input,
  .form-group select {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
  }

  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
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

  .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 13px;
  }

  .table-container {
    overflow-x: auto;
    margin-bottom: 1.5rem;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  table thead {
    background: #f3f4f6;
  }

  table th {
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #d1d5db;
  }

  table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
  }

  table tr:hover {
    background: #f9fafb;
  }

  .amount {
    text-align: right;
    font-family: 'Courier New', monospace;
    font-weight: 500;
  }

  .amount-gross {
    font-weight: 600;
    color: #1e40af;
  }

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-yes {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-no {
    background: #f3f4f6;
    color: #6b7280;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-approved {
    background: #d1fae5;
    color: #065f46;
  }

  .detail-section {
    background: #f9fafb;
    padding: 2rem;
    border-radius: 8px;
    margin-top: 2rem;
    border-left: 4px solid #3b82f6;
  }

  .detail-section h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .detail-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 1.5rem;
  }

  .detail-item {
    display: flex;
    justify-content: space-between;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
  }

  .detail-item label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
  }

  .detail-item value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
    text-align: right;
    min-width: 100px;
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

  .earnings-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .summary-card {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
  }

  .summary-card.success {
    border-left-color: #22c55e;
  }

  .summary-card.warning {
    border-left-color: #f59e0b;
  }

  .summary-card label {
    display: block;
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .summary-card .value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
  }

  .no-data {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
  }

  .compensation-status {
    padding: 1rem;
    border-radius: 4px;
    background: #dbeafe;
    color: #1e40af;
    font-size: 13px;
    margin-bottom: 1rem;
  }

  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 2rem;
  }

  .modal-overlay.active {
    display: flex;
  }

  .modal-box {
    background: white;
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
  }

  .modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
  }

  .modal-close-btn {
    background: #e5e7eb;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 20px;
    font-weight: bold;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .modal-close-btn:hover {
    background: #d1d5db;
    color: #1f2937;
  }

  .modal-content {
    color: #374151;
  }

  .modal-section {
    margin-bottom: 1.5rem;
  }

  .modal-section-title {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .modal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1rem;
  }

  .modal-field {
    display: flex;
    justify-content: space-between;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
  }

  .modal-field-label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
  }

  .modal-field-value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
    text-align: right;
    min-width: 120px;
  }

  .modal-gross-section {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1.5rem 0;
  }

  .modal-gross-section .modal-field {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  }

  .modal-gross-section .modal-field-label {
    color: rgba(255, 255, 255, 0.8);
  }

  .modal-gross-section .modal-field-value {
    color: white;
    font-size: 24px;
    font-weight: 700;
  }

  .modal-formula {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1rem;
  }

  .modal-formula-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
  }

  .modal-formula-text {
    color: #6b7280;
    font-size: 12px;
    font-family: 'Courier New', monospace;
    line-height: 1.6;
  }

  .modal-status {
    background: #dbeafe;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    padding: 0.75rem;
    border-radius: 4px;
    font-size: 13px;
    margin-bottom: 1rem;
  }

  .modal-footer {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
  }

  .modal-footer a {
    text-decoration: none;
    color: #3b82f6;
    font-weight: 500;
    font-size: 13px;
  }

  .modal-footer a:hover {
    text-decoration: underline;
  }
</style>

<script>
  window.openEarningsModal = function(employeeId) {
    // Fetch modal content via AJAX without page refresh
    const url = 'dashboard.php?module=payroll&view=earnings_management&modal=view&employee_id=' + employeeId;
    
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
          // Add the active class to display modal
          modalOverlay.classList.add('active');
        }
      })
      .catch(error => console.error('Error loading modal:', error));
  };

  window.closeEarningsModal = function() {
    // Remove all modal overlays
    document.querySelectorAll('.modal-overlay').forEach(m => {
      m.classList.remove('active');
      setTimeout(() => m.remove(), 300);
    });
    // Reset URL back to main dashboard
    window.history.replaceState({}, '', 'dashboard.php');
  };
</script>

<div class="earnings-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Earnings Management</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Build gross earnings from approved Compensation records. All earning values are sourced from Compensation module and cannot be manually edited.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ How it Works:</strong> Gross earnings are calculated by reading Base Pay, Incentives, and applying Double Pay and Hazard Pay flags from approved Compensation records.
    </div>
  </div>

  <!-- Filters -->
  <div class="section">
    <form method="GET" action="<?= BASE_URL ?>dashboard.php">
      <div class="filter-section">
        <div class="form-group">
          <label>Search Employee</label>
          <input type="text" name="search" placeholder="Employee ID or Name..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label>Department</label>
          <select name="department">
            <option value="">-- All Departments --</option>
            <?php foreach ($departments as $dept): ?>
            <option value="<?php echo (int) $dept['department_id']; ?>" <?php echo ($filters['department'] == $dept['department_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($dept['department_name']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Compensation Status</label>
          <select name="compensation_status">
            <option value="">-- All Statuses --</option>
            <option value="approved" <?php echo ($filters['compensation_status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
            <option value="pending" <?php echo ($filters['compensation_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
          </select>
        </div>
        <div style="display: flex; align-items: flex-end;">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Earnings Summary -->
  <div class="earnings-summary">
    <div class="summary-card success">
      <label>Total Employees with Earnings</label>
      <div class="value"><?php echo (int) $totalEmployees; ?></div>
    </div>
    <div class="summary-card">
      <label>Total Gross Earnings (Monthly)</label>
      <div class="value">₱ <?php echo number_format($totalGross, 2); ?></div>
    </div>
    <div class="summary-card warning">
      <label>Pending Compensation</label>
      <div class="value">0</div>
    </div>
  </div>

  <!-- Earnings Table -->
  <div class="section">
    <h3 class="section-header">Employee Earnings Breakdown</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Department</th>
            <th>Base Pay</th>
            <th>Incentives</th>
            <th>Double Pay</th>
            <th>Hazard Pay</th>
            <th>Gross Earnings</th>
            <th>Compensation Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($earnings)): ?>
          <tr><td colspan="10" style="text-align: center; padding: 2rem; color: #9ca3af;">No employee earnings found</td></tr>
          <?php else: foreach ($earnings as $emp): ?>
          <tr>
            <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
            <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
            <td><?php echo htmlspecialchars($emp['department_name'] ?? '—'); ?></td>
            <td class="amount"><?php echo number_format((float) $emp['basic_rate'], 2); ?></td>
            <td class="amount"><?php echo number_format((float) ($emp['incentives'] ?? 0), 2); ?></td>
            <td class="amount"><?php echo number_format((float) ($emp['double_pay'] ?? 0), 2); ?></td>
            <td class="amount"><?php echo number_format((float) ($emp['hazard_pay'] ?? 0), 2); ?></td>
            <td class="amount amount-gross"><?php echo number_format((float) $emp['gross_earnings'], 2); ?></td>
            <td><span class="badge badge-approved"><?php echo htmlspecialchars($emp['compensation_status']); ?></span></td>
            <td>
              <button type="button" onclick="window.openEarningsModal(<?php echo (int) $emp['employee_id']; ?>)" class="btn btn-secondary btn-sm">View Details</button>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>



  <!-- Important Notes -->
  <div class="section">
    <h3 class="section-header">Important Information</h3>
    
    <div class="alert alert-warning">
      <strong>⚠️ Data Source & Restrictions:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>No Manual Entry:</strong> All earning values are read from approved Compensation records and cannot be manually edited here</li>
        <li><strong>Compensation Reference:</strong> Base Pay and Incentives values are sourced directly from Compensation module</li>
        <li><strong>Pending Compensation:</strong> Employees with pending compensation changes will show the most recently approved values</li>
        <li><strong>Double Pay Flags:</strong> Applied when employee is flagged for double pay in Compensation record</li>
        <li><strong>Hazard Pay Flags:</strong> Applied when employee is flagged for hazard pay/dangerous work allowance in Compensation record</li>
        <li><strong>Gross Earnings Use:</strong> Final gross earnings are used in Deductions Management and Payroll Processing</li>
      </ul>
    </div>

    <div class="alert alert-info">
      <strong>ℹ️ To Modify Earnings:</strong> Update the employee's Compensation record in the Compensation module, then sync/approve the changes. Earnings will automatically recalculate in this module.
    </div>
  </div>

</div>

<!-- View Details Modal Popup -->
<?php if ($modal === 'view' && $employee_id && $employeeEarnings): ?>
<div class="modal-overlay">
  <div class="modal-box">
    <!-- Modal Header -->
    <div class="modal-header">
      <h3>📊 Earnings Calculation</h3>
      <button type="button" onclick="window.closeEarningsModal()" class="modal-close-btn">×</button>
    </div>

    <!-- Modal Content -->
    <div class="modal-content">
      <!-- Employee Info Section -->
      <div class="modal-section">
        <div class="modal-section-title">Employee Information</div>
        <div class="modal-grid">
          <div class="modal-field">
            <span class="modal-field-label">Employee Name</span>
            <span class="modal-field-value"><?php echo htmlspecialchars($employeeEarnings['employee_name']); ?></span>
          </div>
          <div class="modal-field">
            <span class="modal-field-label">Employee ID</span>
            <span class="modal-field-value"><?php echo htmlspecialchars($employeeEarnings['employee_code']); ?></span>
          </div>
        </div>
        <div class="modal-status">
          <strong>Compensation Status:</strong> <?php echo htmlspecialchars($employeeEarnings['compensation_status']); ?>
        </div>
      </div>

      <!-- Earnings Components Section -->
      <div class="modal-section">
        <div class="modal-section-title">Earnings Components</div>
        <div class="modal-grid">
          <div class="modal-field">
            <span class="modal-field-label">Base Pay</span>
            <span class="modal-field-value">₱ <?php echo number_format($employeeEarnings['base_pay'], 2); ?></span>
          </div>
          <div class="modal-field">
            <span class="modal-field-label">Incentives</span>
            <span class="modal-field-value">₱ <?php echo number_format($employeeEarnings['incentives'], 2); ?></span>
          </div>
          <div class="modal-field">
            <span class="modal-field-label">Double Pay</span>
            <span class="modal-field-value">
              <?php if ($employeeEarnings['double_pay'] > 0): ?>
              <span class="badge badge-yes">YES</span> ₱ <?php echo number_format($employeeEarnings['double_pay'], 2); ?>
              <?php else: ?>
              <span class="badge badge-no">NO</span>
              <?php endif; ?>
            </span>
          </div>
          <div class="modal-field">
            <span class="modal-field-label">Hazard Pay</span>
            <span class="modal-field-value">
              <?php if ($employeeEarnings['hazard_pay'] > 0): ?>
              <span class="badge badge-yes">YES</span> ₱ <?php echo number_format($employeeEarnings['hazard_pay'], 2); ?>
              <?php else: ?>
              <span class="badge badge-no">NO</span>
              <?php endif; ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Gross Earnings Highlight -->
      <div class="modal-gross-section">
        <div class="modal-field">
          <span class="modal-field-label">GROSS EARNINGS</span>
          <span class="modal-field-value">₱ <?php echo number_format($employeeEarnings['gross_earnings'], 2); ?></span>
        </div>
      </div>

      <!-- Calculation Formula -->
      <div class="modal-formula">
        <div class="modal-formula-title">📝 Calculation Formula</div>
        <div class="modal-formula-text">
          Gross Earnings = Base Pay + Incentives + Double Pay + Hazard Pay<br><br>
          ₱<?php echo number_format($employeeEarnings['base_pay'], 2); ?> + 
          ₱<?php echo number_format($employeeEarnings['incentives'], 2); ?> + 
          ₱<?php echo number_format($employeeEarnings['double_pay'], 2); ?> + 
          ₱<?php echo number_format($employeeEarnings['hazard_pay'], 2); ?><br><br>
          <strong>= ₱<?php echo number_format($employeeEarnings['gross_earnings'], 2); ?></strong>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" onclick="window.closeEarningsModal()" style="background: none; border: none; color: #3b82f6; cursor: pointer; text-decoration: none; padding: 0; font: inherit;">← Back to Earnings List</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
