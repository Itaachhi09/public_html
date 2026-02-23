<?php
/**
 * Payslip Management Module
 * Generate and manage employee payslips with employee portal access
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
require_once __DIR__ . '/../models/EmployeeSalary.php';
require_once __DIR__ . '/../models/PayrollRunEmployee.php';
require_once __DIR__ . '/../models/PayrollRun.php';

$employeeSalary = new EmployeeSalary();
$payrollEmployee = new PayrollRunEmployee();
$payrollRunModel = new PayrollRun();

// Determine selected payroll run (from GET or default to latest processed/approved)
$selected_payroll = isset($_GET['payroll_id']) && is_numeric($_GET['payroll_id']) ? (int)$_GET['payroll_id'] : null;
$approved_runs = $payrollRunModel->query("SELECT * FROM payroll_runs WHERE status IN ('Approved','Processed') ORDER BY start_date DESC");

if (!$selected_payroll) {
  // pick the first available approved/processed run as default
  $selected_payroll = !empty($approved_runs) ? (int)$approved_runs[0]['id'] : null;
}

$payslips = [];
$selected_run = null;
if ($selected_payroll) {
  $selected_run = $payrollRunModel->find($selected_payroll);
  $payslips = $payrollEmployee->query(
    "SELECT pre.*, pr.period_name, pr.pay_date, e.first_name, e.last_name, e.employee_code 
     FROM payroll_run_employees pre 
     JOIN payroll_runs pr ON pre.payroll_run_id = pr.id 
     JOIN employees e ON pre.employee_id = e.employee_id 
     WHERE pre.payroll_run_id = ? 
     ORDER BY e.employee_code ASC",
    [$selected_payroll]
  );
}

// Fetch payroll runs that are approved or processed (ready for payslip generation)
$approved_runs = $payrollRunModel->query("SELECT * FROM payroll_runs WHERE status IN ('Approved','Processed') ORDER BY start_date DESC");
?>

<style>
  .payslip-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
  }
(...existing code...)

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

  .btn-success {
    background: #22c55e;
    color: white;
  }

  .btn-success:hover {
    background: #16a34a;
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

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-generated {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-sent {
    background: #dbeafe;
    color: #1e40af;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
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

  .alert-success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
  }

  .alert-warning {
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
  }

  .payslip-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
  }

  .payslip-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
  }

  .payslip-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .payslip-card p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 13px;
  }

  .payslip-summary {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
  }

  .payslip-amount {
    flex: 1;
    min-width: 100px;
  }

  .payslip-amount label {
    display: block;
    color: #6b7280;
    font-size: 11px;
    font-weight: 500;
    margin-bottom: 0.25rem;
  }

  .payslip-amount value {
    display: block;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Courier New', monospace;
  }

  .payslip-card .actions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .payslip-preview {
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 3rem 2rem;
    margin-bottom: 2rem;
    page-break-after: avoid;
  }

  .payslip-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #d1d5db;
  }

  .company-info h3 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 700;
  }

  .company-info p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .payslip-title {
    text-align: right;
  }

  .payslip-title h2 {
    margin: 0;
    color: #1f2937;
    font-size: 20px;
    font-weight: 700;
  }

  .payslip-title p {
    margin: 0.5rem 0 0 0;
    color: #6b7280;
    font-size: 12px;
  }

  .employee-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 4px;
  }

  .info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
  }

  .info-item label {
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
  }

  .info-item value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
  }

  .payslip-details {
    margin-bottom: 2rem;
  }

  .payslip-details h4 {
    margin: 1.5rem 0 1rem 0;
    color: #1f2937;
    font-size: 13px;
    font-weight: 600;
    border-bottom: 1px solid #3b82f6;
    padding-bottom: 0.5rem;
  }

  .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 13px;
  }

  .detail-row label {
    color: #6b7280;
    font-weight: 500;
  }

  .detail-row value {
    color: #1f2937;
    font-weight: 500;
    font-family: 'Courier New', monospace;
    text-align: right;
    min-width: 80px;
  }

  .detail-row.section-total {
    font-weight: 600;
    padding-top: 1rem;
    border-top: 2px solid #d1d5db;
    border-bottom: none;
    color: #1f2937;
  }

  .payslip-footer {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #d1d5db;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
  }

  .ytd-summary {
    background: #eff6ff;
    padding: 1rem;
    border-radius: 4px;
  }

  .ytd-summary h4 {
    margin: 0 0 1rem 0;
    color: #1e40af;
    font-size: 12px;
    font-weight: 600;
  }

  .ytd-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 12px;
    color: #1f2937;
  }

  .ytd-item label {
    color: #6b7280;
  }

  .ytd-item value {
    font-weight: 600;
    font-family: 'Courier New', monospace;
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

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1rem;
  }

  .form-row.full {
    grid-template-columns: 1fr;
  }

  .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
  }

  .summary-cards {
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

  /* Modal Styles */
  .payslip-modal-overlay {
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

  .payslip-modal-overlay.active {
    display: flex;
  }

  .payslip-modal {
    background: white;
    border-radius: 8px;
    max-width: 900px;
    width: 95%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
  }

  .payslip-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
  }

  .payslip-modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }

  .payslip-modal-close {
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

  .payslip-modal-close:hover {
    color: #1f2937;
    background: #e5e7eb;
    border-radius: 4px;
  }

  .payslip-modal-body {
    padding: 2rem;
  }

  @media print {
    body {
      background: white;
      padding: 0;
    }
    .payslip-container {
      padding: 0;
    }
    .section {
      box-shadow: none;
      page-break-inside: avoid;
    }
  }
</style>

<div class="payslip-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Payslip Management</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Generate and manage employee payslips. Payslips are read-only and available only after payroll approval. Employees can access payslips via employee portal.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Features:</strong> Payslip generation, PDF download, email distribution, year-to-date summaries, and employee portal access.
    </div>
  </div>

  <?php
  // Display status messages
  $message = $_SESSION['payslip_message'] ?? null;
  $payroll_id_msg = $_SESSION['payslip_message_payroll_id'] ?? null;
  $payroll_id_param = $_GET['payroll_id'] ?? null;
  
  if ($message && ($payroll_id_msg == $payroll_id_param || !$payroll_id_param)) {
    $bg_color = match($message['type']) {
      'success' => '#d1fae5',
      'error' => '#fee2e2',
      'warning' => '#fef3c7',
      default => '#e0e7ff'
    };
    $text_color = match($message['type']) {
      'success' => '#065f46',
      'error' => '#7f1d1d',
      'warning' => '#92400e',
      default => '#3730a3'
    };
    $icon = match($message['type']) {
      'success' => '✓',
      'error' => '✕',
      'warning' => '⚠',
      default => 'ℹ'
    };
    
    echo "<div style=\"background: {$bg_color}; color: {$text_color}; padding: 1rem; margin: 1rem 0; border-radius: 4px; border-left: 4px solid {$text_color}; display: flex; align-items: flex-start; gap: 0.75rem; font-size: 14px;\">";
    echo "<div style=\"flex-shrink: 0; font-weight: bold; font-size: 18px; margin-top: -2px;\">{$icon}</div>";
    echo "<div style=\"flex: 1;\">{$message['text']}</div>";
    echo "</div>";
    
    unset($_SESSION['payslip_message']);
    unset($_SESSION['payslip_message_payroll_id']);
  }
  ?>

  <!-- Generate Payslips -->
  <div class="section">
    <h3 class="section-header">🚀 Generate Payslips</h3>

    <form id="payslipGenerateForm" method="POST" action="<?= BASE_URL ?>dashboard.php">
      <input type="hidden" name="module" value="payroll">
      <input type="hidden" name="view" value="payslip_management">
      <input type="hidden" name="payroll_id" id="payroll_id_generate">
      
      <div class="form-section">
        <h4>Select Payroll Period</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Payroll Period <span style="color: #ef4444;">*</span></label>
            <select name="payroll_id" id="payroll_period_select" required onchange="document.getElementById('payroll_id_generate').value = this.value;">
              <option value="">-- Select Period --</option>
              <?php if (!empty($approved_runs)) : ?>
                <?php foreach ($approved_runs as $r): ?>
                  <option value="<?php echo (int)$r['id']; ?>" <?php echo (isset($_GET['payroll_id']) && $_GET['payroll_id'] == $r['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['period_name'] . ' (' . ($r['start_date'] ?? '') . ' - ' . ($r['end_date'] ?? '') . ')'); ?></option>
                <?php endforeach; ?>
              <?php else: ?>
                <option value="">No approved payroll runs available</option>
              <?php endif; ?>
            </select>
            <small>Select the approved payroll to generate payslips</small>
          </div>
          <div class="form-group">
            <label>Status</label>
            <div style="padding: 0.75rem; background: #d1fae5; border-radius: 4px; color: #065f46; font-size: 13px;">
              ✓ Approved & Ready for Payslips
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          Payslips can only be generated from approved payroll runs. Selected period shows 8 employees with payroll data.
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="generate_all" class="btn btn-primary">Generate All Payslips</button>
          <button type="submit" name="action" value="generate_and_email" class="btn btn-success">Generate & Send via Email</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Payslips List & Status -->
  <div class="section">
  <?php
    // Shorten header label by removing trailing date ranges if present (e.g. "Payroll Period: Mar 5, 2026 - Mar 20, 2026" -> "Payroll Period")
    $header_label = $selected_run['period_name'] ?? 'Payroll Period';
    if (strpos($header_label, ':') !== false) {
      $parts = explode(':', $header_label, 2);
      $header_label = trim($parts[0]);
    }
  ?>
    <h3 class="section-header">📋 Payslip Status - <?php echo htmlspecialchars($header_label); ?></h3>

    <div class="summary-cards">
      <div class="summary-card">
        <label>Total Generated</label>
        <div class="value">8</div>
      </div>
      <div class="summary-card" style="border-left-color: #22c55e;">
        <label>Emails Sent</label>
        <div class="value">8</div>
      </div>
      <div class="summary-card" style="border-left-color: #3b82f6;">
        <label>PDF Downloads</label>
        <div class="value">5</div>
      </div>
      <div class="summary-card" style="border-left-color: #f59e0b;">
        <label>Portal Access Views</label>
        <div class="value">7</div>
      </div>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Gross Pay</th>
            <th>Net Pay</th>
            <th>Generated</th>
            <th>Email Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payslips as $p): ?>
          <tr>
            <td><?php echo htmlspecialchars($p['employee_code'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); ?></td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱<?php echo number_format($p['gross_pay'] ?? 0, 2); ?></td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱<?php echo number_format($p['net_pay'] ?? 0, 2); ?></td>
            <td><?php echo (!empty($p['payslip_number'])) ? '<span class="badge badge-generated">Generated</span>' : '<span class="badge">Pending</span>'; ?></td>
            <td><?php echo (!empty($p['payslip_sent']) || (!empty($p['sent_date']))) ? '<span class="badge badge-sent">Sent</span>' : '<span class="badge">Not Sent</span>'; ?></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal(<?php echo (int)($p['id'] ?? 0); ?>, <?php echo (int)($p['payroll_run_id'] ?? 0); ?>, '<?php echo addslashes(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')); ?>', '<?php echo addslashes($p['period_name'] ?? ''); ?>', '<?php echo addslashes($p['pay_date'] ?? ''); ?>')">View</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Payslip Preview Notice -->
  <div class="section">
    <h3 class="section-header">📄 Payslip Preview</h3>
    <div class="alert alert-info">
      Click "View" on any employee row to preview the actual payslip. The preview loads real payout and deduction data from the database.
    </div>
  </div>

  <!-- Employee Portal Access -->
  <div class="section">
    <h3 class="section-header">🔐 Employee Portal Access</h3>

    <div class="alert alert-success">
      <strong>✓ Employee Portal Available:</strong> Employees can access their payslips using their employee ID and portal credentials. No special admin access required.
    </div>

    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
      <h4 style="margin: 0 0 1rem 0; color: #1f2937;">Employee Portal Features</h4>
      <ul style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 13px; line-height: 1.8;">
        <li><strong style="color: #1f2937;">View Payslips:</strong> Employees can view all their generated payslips by period</li>
        <li><strong style="color: #1f2937;">Download PDF:</strong> Download payslips as PDF for their records</li>
        <li><strong style="color: #1f2937;">Year-to-Date Summary:</strong> View YTD earnings, deductions, and net pay</li>
        <li><strong style="color: #1f2937;">Email Notification:</strong> Automatic email when payslips are generated with access link</li>
        <li><strong style="color: #1f2937;">Print Option:</strong> Print-friendly payslip format</li>
        <li><strong style="color: #1f2937;">Secure Access:</strong> Password-protected portal requires employee login</li>
      </ul>
    </div>

    <h4 style="color: #1f2937; margin: 2rem 0 1rem 0; font-size: 14px; font-weight: 600;">Employee Portal Login URL</h4>
    <div style="background: #f3f4f6; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
      <code style="color: #1f2937; font-size: 12px; font-family: 'Courier New', monospace;">
        https://healthcare-system.com/employee-portal/payslips
      </code>
      <small style="display: block; margin-top: 0.5rem; color: #6b7280;">
        Employees log in with their employee ID and password
      </small>
    </div>
  </div>

  <!-- Email Distribution Settings -->
  <div class="section">
    <h3 class="section-header">📧 Email Distribution Settings</h3>

    <form method="POST" action="<?= BASE_URL ?>dashboard.php">
      <input type="hidden" name="module" value="payroll">
      <input type="hidden" name="view" value="payslip_management">
      
      <div class="form-section">
        <h4>Email Configuration</h4>
        <div class="form-row full">
          <div class="form-group">
            <label>Send payslips via email automatically</label>
            <input type="checkbox" name="auto_email" value="1" checked style="width: auto; margin-top: 0.25rem;">
            <small>When enabled, payslips are automatically emailed to employees after generation</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Email From Address</label>
            <input type="text" name="email_from" value="payroll@healthcare.com" style="background: #f3f4f6;">
          </div>
          <div class="form-group">
            <label>Email Subject</label>
            <input type="text" name="email_subject" value="Your Payslip for [PERIOD]" style="background: #f3f4f6;">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Email Body Template</label>
            <textarea name="email_template" style="min-height: 150px; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px; font-family: 'Courier New', monospace;">Dear [EMPLOYEE_NAME],

Your payslip for [PAY_PERIOD] has been generated and is ready for download.

Gross Pay: ₱[GROSS_PAY]
Total Deductions: ₱[TOTAL_DEDUCTIONS]
Net Pay: ₱[NET_PAY]

You can view and download your payslip using the following link:
[PAYSLIP_PORTAL_LINK]

For payroll inquiries, contact: payroll@healthcare.com

---
Healthcare Hospital Inc.
Payroll Department</textarea>
          </div>
        </div>

        <div class="alert alert-info">
          <strong>ℹ️ Automatic Email:</strong> When you select "Generate & Send via Email", payslips are generated and immediately emailed to all employees with portal access links included.
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="update_email_settings" class="btn btn-primary">Update Email Settings</button>
          <button type="submit" name="action" value="reset_email_settings" class="btn btn-secondary">Reset</button>
        </div>
      </div>
    </form>
  </div>



</div>

<!-- Modal for Payslip View -->
<div class="payslip-modal-overlay" id="payslip-modal-overlay" onclick="if(event.target === this) window.closePayslipModal()">
  <div class="payslip-modal">
    <div class="payslip-modal-header">
      <h2 class="payslip-modal-title" id="payslip-modal-title">Payslip Details</h2>
      <button type="button" class="payslip-modal-close" onclick="window.closePayslipModal()">×</button>
    </div>
    <div class="payslip-modal-body" id="payslip-modal-body">
      <!-- Content will be injected here -->
    </div>
  </div>
</div>

<script>
// Payslip Modal Functions
window.openPayslipModal = function(runEmployeeId, payrollRunId, empName, periodName, payDate) {
  const modal = document.getElementById('payslip-modal-overlay');
  const title = document.getElementById('payslip-modal-title');
  const body = document.getElementById('payslip-modal-body');

  title.textContent = 'Payslip - ' + (empName || '');

  var baseApi = '<?php echo BASE_URL; ?>modules/payroll/api.php';
  function fmt(n){ return '₱' + Number(n || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }

  // First fetch the run employee detail to know the employee_id
  fetch(baseApi + '?action=getPayrollRunEmployeeDetail&id=' + encodeURIComponent(runEmployeeId), { credentials: 'same-origin' })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if (!res.success) throw new Error(res.error || 'Failed to fetch run employee');
      var d = res.data || {};
      var employeeId = d.employee_id;

      // Fetch employee payslip history
      return fetch(baseApi + '?action=getEmployeePayslipHistory&employee_id=' + encodeURIComponent(employeeId), { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(histRes){
          if (!histRes.success) histRes.data = [];
          var history = histRes.data || [];

          // Build two-column modal: left - history list, right - detail preview
          var html = '<div style="display:grid; grid-template-columns: 320px 1fr; gap:1rem;">';

          // Left: history list with cutoff filters
          html += '<div style="border-right:1px solid #e5e7eb; padding-right:0.5rem;">';
          html += '<div style="margin-bottom:0.5rem;"> <strong>Payroll History</strong><br><small style="color:#6b7280;">Filter by cut-off / period</small></div>';
          html += '<div style="max-height:60vh; overflow:auto;">';
          if (history.length === 0) {
            html += '<div style="color:#6b7280;">No payslip history found for this employee.</div>';
          } else {
            html += '<ul style="list-style:none; padding:0; margin:0;">';
            history.forEach(function(h){
              var label = (h.period_name || (h.start_date + ' - ' + h.end_date));
              var status = h.payslip_number ? 'Generated' : 'Pending';
              html += '<li style="margin:0 0 0.5rem 0;">';
              html += '<button type="button" class="history-row" data-runempid="' + h.run_employee_id + '" data-label="' + (label+'').replace(/"/g,'') + '" style="width:100%; text-align:left; padding:0.5rem; border-radius:4px; border:1px solid #e5e7eb; background:white;">';
              html += '<div style="display:flex; justify-content:space-between; align-items:center;">';
              html += '<div style="font-size:13px; color:#1f2937;">' + label + '</div>';
              html += '<div style="font-size:12px; color:#6b7280;">' + (h.run_status || '') + '</div>';
              html += '</div>';
              html += '<div style="display:flex; justify-content:space-between; margin-top:6px; font-size:12px; color:#374151;">';
              html += '<div>' + (h.pay_date || '') + '</div>';
              html += '<div>' + (h.payslip_number ? '<span class="badge badge-generated">Generated</span>' : '<span class="badge">Pending</span>') + '</div>';
              html += '</div>';
              html += '</button>';
              html += '</li>';
            });
            html += '</ul>';
          }
          html += '</div>'; // history scroll
          html += '</div>'; // left column

          // Right: detail placeholder
          html += '<div id="payslip-detail-panel">';
          html += '<div style="color:#6b7280; margin-bottom:0.5rem;">Select a payroll period on the left to view the detailed payslip.</div>';
          html += '</div>';

          html += '</div>'; // grid

          body.innerHTML = html;
          modal.classList.add('active');
          body.scrollTop = 0;

          // Attach click handlers to history rows
          var rows = body.querySelectorAll('.history-row');
          rows.forEach(function(btn){
            btn.addEventListener('click', function(){
              var rid = this.getAttribute('data-runempid');
              var lbl = this.getAttribute('data-label');
              loadPayslipDetail(rid, lbl);
            });
          });

          // Auto-load the clicked runEmployeeId (the one that opened the modal) if present
          if (runEmployeeId) {
            loadPayslipDetail(runEmployeeId, periodName || '');
          }

          // Function to load individual payslip detail into right panel
          function loadPayslipDetail(runEmpId, label) {
            var panel = document.getElementById('payslip-detail-panel');
            panel.innerHTML = '<div style="padding:1rem; color:#6b7280;">Loading payslip...</div>';
            fetch(baseApi + '?action=getPayrollRunEmployeeDetail&id=' + encodeURIComponent(runEmpId), { credentials: 'same-origin' })
              .then(function(r){ return r.json(); })
              .then(function(r2){
                if (!r2.success) throw new Error(r2.error || 'Failed to load payslip');
                var d = r2.data || {};
                var html = '<div class="payslip-preview" style="padding: 1rem; margin: 0;">';
                html += '<div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:1rem;">';
                html += '<div><h3 style="margin:0; font-size:14px;">Healthcare Hospital Inc.</h3><p style="margin:0; font-size:11px; color:#6b7280;">Payslip ID: ' + (d.payslip_number || '') + '</p></div>';
                html += '<div style="text-align:right;"><h2 style="margin:0; font-size:14px;">PAYSLIP</h2><p style="margin:0; font-size:11px; color:#6b7280;">' + (label || '') + '</p></div>';
                html += '</div>';

                html += '<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">';
                html += '<div><div style="display:flex; justify-content:space-between; border-bottom:1px solid #e5e7eb; padding:0.5rem 0;"> <label style="color:#6b7280;">Employee ID</label><value>' + (d.employee_id || '') + '</value></div>';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0;"> <label style="color:#6b7280;">Name</label><value>' + (empName || (d.first_name + ' ' + d.last_name)) + '</value></div></div>';
                html += '<div><div style="display:flex; justify-content:space-between; border-bottom:1px solid #e5e7eb; padding:0.5rem 0;"> <label style="color:#6b7280;">Pay Date</label><value>' + (d.pay_date || '') + '</value></div>';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0;"> <label style="color:#6b7280;">Status</label><value>' + (d.payslip_number ? '<span class="badge badge-generated">Generated</span>' : '<span class="badge">Pending</span>') + '</value></div></div>';
                html += '</div>';

                html += '<div style="border-top:1px solid #e5e7eb; padding-top:1rem;">';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0;">Basic Pay <value>' + fmt(d.basic_pay) + '</value></div>';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0;">Incentives <value>' + fmt(d.incentives) + '</value></div>';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0; font-weight:600;">Gross Pay <value>' + fmt(d.gross_pay) + '</value></div>';
                html += '</div>';

                html += '<div style="border-top:1px solid #e5e7eb; padding-top:1rem;">';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0;">SSS <value>' + fmt(d.sss_contribution) + '</value></div>';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0;">PhilHealth <value>' + fmt(d.philhealth_contribution) + '</value></div>';
                html += '<div style="display:flex; justify-content:space-between; padding:0.5rem 0; font-weight:600;">Total Deductions <value>' + fmt(d.total_deductions) + '</value></div>';
                html += '</div>';

                html += '<div style="border-top:2px solid #1f2937; padding-top:1rem; margin-top:1rem; display:flex; justify-content:space-between; font-weight:700;">NET PAY <value>' + fmt(d.net_pay) + '</value></div>';
                html += '</div>';

                panel.innerHTML = html;
              }).catch(function(err){
                panel.innerHTML = '<div style="color:#ef4444;">Failed to load payslip detail.</div>';
                console.error(err);
              });
          }
        });
    }).catch(function(err){
      console.error('Failed to load payslip data', err);
      alert('Failed to load payslip data.');
    });
};

window.closePayslipModal = function() {
  const modal = document.getElementById('payslip-modal-overlay');
  modal.classList.remove('active');
};

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    window.closePayslipModal();
  }
});
</script>

<script>
// AJAX submit for payslip generation / generate & email
;(function(){
  var form = document.getElementById('payslipGenerateForm');
  if (!form) return;

  form.addEventListener('submit', function(e){
    e.preventDefault();
    var submitter = e.submitter || document.activeElement;
    var actionVal = (submitter && submitter.name === 'action') ? submitter.value : (new FormData(form)).get('action');
    if (!actionVal) {
      alert('Please choose an action.');
      return;
    }

    var fd = new FormData(form);
    fd.set('ref', 'payroll');
    fd.set('page', 'payslip_management');
    fd.set('action', actionVal);

    // disable buttons
    var buttons = form.querySelectorAll('button[type="submit"]');
    buttons.forEach(function(b){ b.disabled = true; });

    fetch('<?= BASE_URL ?>dashboard.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    }).then(function(r){ return r.json(); })
    .then(function(json){
      if (json && json.success) {
        alert(json.message || 'Operation successful');
        // reload module content in-place
        return fetch('<?= BASE_URL ?>dashboard.php?module=payroll&view=payslip_management', { credentials: 'same-origin' });
      }
      throw new Error((json && json.message) ? json.message : 'Operation failed');
    }).then(function(r){ return r.text(); }).then(function(html){
      var container = document.getElementById('content-area');
      if (container) container.innerHTML = html;
      else location.reload();
    }).catch(function(err){
      console.error(err);
      alert('Error: ' + (err.message || err));
    }).finally(function(){
      buttons.forEach(function(b){ b.disabled = false; });
    });
  });
})();
</script>
