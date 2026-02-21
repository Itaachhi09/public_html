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

$employeeSalary = new EmployeeSalary();
$payrollEmployee = new PayrollRunEmployee();

// Fetch payslips
$payslips = $payrollEmployee->query("SELECT pre.*, pr.period_name, pr.pay_date, e.first_name, e.last_name, e.employee_code 
                                      FROM payroll_run_employees pre 
                                      JOIN payroll_runs pr ON pre.payroll_run_id = pr.id 
                                      JOIN employees e ON pre.employee_id = e.employee_id 
                                      ORDER BY pr.pay_date DESC, e.employee_code ASC");
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

    <form method="POST" action="<?= BASE_URL ?>dashboard.php">
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
              <option value="1" selected>February 2026 Period 1 (Feb 1-15)</option>
              <option value="2">January 2026 Period 2 (Jan 16-31)</option>
              <option value="3">January 2026 Period 1 (Jan 1-15)</option>
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
    <h3 class="section-header">📋 Payslip Status - February 2026 Period 1</h3>

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
          <tr>
            <td>EMP-001</td>
            <td>John Doe</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱11,000.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱7,650.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-001', 'EMP-001', 'John Doe')">View</button>
            </td>
          </tr>
          <tr>
            <td>EMP-002</td>
            <td>Jane Smith</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱11,000.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱7,300.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-002', 'EMP-002', 'Jane Smith')">View</button>
            </td>
          </tr>
          <tr>
            <td>EMP-003</td>
            <td>Michael Johnson</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱12,500.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱8,050.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-003', 'EMP-003', 'Michael Johnson')">View</button>
            </td>
          </tr>
          <tr>
            <td>EMP-004</td>
            <td>Sarah Williams</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱9,000.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱6,400.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-004', 'EMP-004', 'Sarah Williams')">View</button>
            </td>
          </tr>
          <tr>
            <td>EMP-005</td>
            <td>Robert Brown</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱9,000.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱6,250.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-005', 'EMP-005', 'Robert Brown')">View</button>
            </td>
          </tr>
          <tr>
            <td>EMP-006</td>
            <td>Emily Davis</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱11,000.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱7,425.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-006', 'EMP-006', 'Emily Davis')">View</button>
            </td>
          </tr>
          <tr>
            <td>EMP-007</td>
            <td>David Martinez</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱9,700.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱6,470.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-007', 'EMP-007', 'David Martinez')">View</button>
            </td>
          </tr>
          <tr>
            <td>EMP-008</td>
            <td>Jessica Wilson</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱13,800.00</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱8,810.00</td>
            <td><span class="badge badge-generated">Generated</span></td>
            <td><span class="badge badge-sent">Sent</span></td>
            <td>
              <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayslipModal('PS-2026-02-008', 'EMP-008', 'Jessica Wilson')">View</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sample Payslip Preview -->
  <div class="section">
    <h3 class="section-header">📄 Payslip Sample - John Doe (EMP-001)</h3>

    <div class="alert alert-info">
      This is a read-only payslip. Employees can download this as PDF or view in their employee portal using their login credentials.
    </div>

    <!-- Payslip Document -->
    <div class="payslip-preview">
      <!-- Header -->
      <div class="payslip-header">
        <div class="company-info">
          <h3>Healthcare Hospital Inc.</h3>
          <p>123 Hospital Avenue, Makati City</p>
          <p>Email: payroll@healthcare.com | Phone: +63-2-555-1234</p>
        </div>
        <div class="payslip-title">
          <h2>PAYSLIP</h2>
          <p>Pay Period: February 1-15, 2026</p>
          <p>Payment Date: February 22, 2026</p>
        </div>
      </div>

      <!-- Employee Information -->
      <div class="employee-info">
        <div>
          <div class="info-item">
            <label>Employee ID</label>
            <value>EMP-001</value>
          </div>
          <div class="info-item">
            <label>Name</label>
            <value>John Doe</value>
          </div>
          <div class="info-item">
            <label>Department</label>
            <value>Human Resources</value>
          </div>
          <div class="info-item">
            <label>Position</label>
            <value>HR Manager</value>
          </div>
        </div>
        <div>
          <div class="info-item">
            <label>Employment Type</label>
            <value>Regular Full-Time</value>
          </div>
          <div class="info-item">
            <label>Pay Type</label>
            <value>Per Duty</value>
          </div>
          <div class="info-item">
            <label>Bank Account</label>
            <value>•••••••...4567</value>
          </div>
          <div class="info-item">
            <label>Generated</label>
            <value>February 8, 2026</value>
          </div>
        </div>
      </div>

      <!-- Earnings Section -->
      <div class="payslip-details">
        <h4>EARNINGS</h4>
        <div class="detail-row">
          <label>Basic Pay</label>
          <value>6,000.00</value>
        </div>
        <div class="detail-row">
          <label>Incentives</label>
          <value>2,000.00</value>
        </div>
        <div class="detail-row">
          <label>Double Pay</label>
          <value>3,000.00</value>
        </div>
        <div class="detail-row section-total">
          <label>Total Earnings</label>
          <value>11,000.00</value>
        </div>
      </div>

      <!-- Deductions Section -->
      <div class="payslip-details">
        <h4>STATUTORY DEDUCTIONS</h4>
        <div class="detail-row">
          <label>Withholding Tax (BIR)</label>
          <value>0.00</value>
        </div>
        <div class="detail-row">
          <label>SSS Contribution</label>
          <value>406.24</value>
        </div>
        <div class="detail-row">
          <label>PhilHealth Premium</label>
          <value>247.50</value>
        </div>
        <div class="detail-row">
          <label>Pag-IBIG Contribution</label>
          <value>200.00</value>
        </div>
        <div class="detail-row">
          <label>Pag-IBIG Housing Loan</label>
          <value>1,796.26</value>
        </div>
        <div class="detail-row section-total">
          <label>Statutory Deductions</label>
          <value>2,850.00</value>
        </div>
      </div>

      <div class="payslip-details">
        <h4>OTHER DEDUCTIONS</h4>
        <div class="detail-row">
          <label>HMO Premium</label>
          <value>0.00</value>
        </div>
        <div class="detail-row">
          <label>Employee Loan</label>
          <value>500.00</value>
        </div>
        <div class="detail-row section-total">
          <label>Other Deductions</label>
          <value>500.00</value>
        </div>
      </div>

      <!-- Summary -->
      <div class="payslip-details">
        <div class="detail-row" style="font-size: 14px; font-weight: 700; padding: 1rem 0; border-bottom: 2px solid #1f2937;">
          <label>NET PAY</label>
          <value>7,650.00</value>
        </div>
        <div style="color: #6b7280; font-size: 12px; margin-top: 0.5rem;">
          Net payment via bank transfer to registered bank account
        </div>
      </div>

      <!-- Footer -->
      <div class="payslip-footer">
        <div>
          <h4 style="color: #6b7280; font-size: 11px; font-weight: 600; margin: 0 0 0.5rem 0;">PAYROLL PROCESSOR</h4>
          <p style="margin: 0; color: #6b7280; font-size: 11px;">Juan dela Cruz<br>Payroll Officer<br>Date: February 8, 2026</p>
        </div>
        <div class="ytd-summary">
          <h4>Year-to-Date Summary (Jan-Feb 2026)</h4>
          <div class="ytd-item">
            <label>Total Earnings</label>
            <value>₱21,850.00</value>
          </div>
          <div class="ytd-item">
            <label>Total Statutory Deductions</label>
            <value>₱5,610.00</value>
          </div>
          <div class="ytd-item">
            <label>Total Other Deductions</label>
            <value>₱1,200.00</value>
          </div>
          <div class="ytd-item" style="border-top: 1px solid #d1d5db; padding-top: 0.5rem; margin-top: 0.5rem;">
            <label>Total Net Paid</label>
            <value style="color: #1e40af;">₱15,040.00</value>
          </div>
        </div>
      </div>
    </div>

    <!-- Payslip Actions -->
    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
      <div class="btn-group">
        <button type="button" onclick="window.print()" class="btn btn-primary">Print Payslip</button>
        <button type="button" onclick="alert('PDF download would be generated')" class="btn btn-secondary">Download as PDF</button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="resend_email">
          <input type="hidden" name="payslip_id" value="PS-2026-02-001">
          <button type="submit" class="btn btn-secondary">Resend Email</button>
        </form>
      </div>
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

  <!-- Important Rules & Information -->
  <div class="section">
    <h3 class="section-header">📋 Payslip Management Rules</h3>

    <div class="alert alert-warning">
      <strong>⚠️ Critical Rules:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>Read-Only Payslips:</strong> Generated payslips cannot be edited or deleted. They are permanent records.</li>
        <li><strong>Approval Required:</strong> Payslips can only be generated from approved payroll runs. Draft payroll cannot generate payslips.</li>
        <li><strong>Employee Access:</strong> Only employees with payroll profiles for the period can receive payslips.</li>
        <li><strong>Email Distribution:</strong> Payslips are emailed to employee email addresses on file with portal access link.</li>
        <li><strong>PDF Generation:</strong> Payslips remain archival-ready and can be regenerated if needed.</li>
      </ul>
    </div>

    <div class="alert alert-info">
      <strong>ℹ️ Payslip Contents (Standard):</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>Earnings Section:</strong> Basic pay, incentives, bonuses, allowances, overtime</li>
        <li><strong>Statutory Deductions:</strong> Withholding tax, SSS, PhilHealth, Pag-IBIG</li>
        <li><strong>Other Deductions:</strong> HMO, loans, absences, penalties</li>
        <li><strong>Net Pay:</strong> Gross less all deductions</li>
        <li><strong>Year-to-Date:</strong> Running totals for earnings, deductions, net pay</li>
        <li><strong>Metadata:</strong> Employee info, pay period, payment date, processor signature</li>
      </ul>
    </div>

    <div class="alert alert-success">
      <strong>✓ Audit Trail:</strong> All payslip generation, email delivery, and portal access is logged. Employees can access only their own payslips. System maintains permanent record of all generated payslips for compliance.
    </div>
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
window.openPayslipModal = function(payslipId, empId, empName) {
  const modal = document.getElementById('payslip-modal-overlay');
  const title = document.getElementById('payslip-modal-title');
  const body = document.getElementById('payslip-modal-body');
  
  title.textContent = 'Payslip - ' + empName + ' (' + empId + ')';
  
  // Sample payslip data
  let html = '<div class="payslip-preview" style="padding: 1.5rem; margin: 0;">';
  html += '<div class="payslip-header" style="margin-bottom: 1rem;">';
  html += '<div class="company-info"><h3 style="font-size: 14px; margin-bottom: 0.25rem;">Healthcare Hospital Inc.</h3>';
  html += '<p style="margin: 0; font-size: 11px; color: #6b7280;">Payslip ID: ' + payslipId + '</p></div>';
  html += '<div class="payslip-title"><h2 style="font-size: 14px; margin: 0;">PAYSLIP</h2>';
  html += '<p style="margin: 0.25rem 0 0 0; font-size: 11px; color: #6b7280;">Pay Period: February 1-15, 2026</p></div>';
  html += '</div>';
  
  html += '<div class="employee-info" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">';
  html += '<div><div class="info-item" style="display: flex; justify-content: space-between; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 0;">';
  html += '<label style="font-size: 12px; color: #6b7280;">Employee ID</label><value style="font-size: 12px;">' + empId + '</value></div>';
  html += '<div class="info-item" style="display: flex; justify-content: space-between; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 0;">';
  html += '<label style="font-size: 12px; color: #6b7280;">Name</label><value style="font-size: 12px;">' + empName + '</value></div></div>';
  html += '<div><div class="info-item" style="display: flex; justify-content: space-between; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 0;">';
  html += '<label style="font-size: 12px; color: #6b7280;">Pay Date</label><value style="font-size: 12px;">February 22, 2026</value></div>';
  html += '<div class="info-item" style="display: flex; justify-content: space-between; padding: 0.5rem 0;">';
  html += '<label style="font-size: 12px; color: #6b7280;">Status</label><value style="font-size: 12px;"><span class="badge badge-generated">Generated</span></value></div></div>';
  html += '</div>';
  
  html += '<div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">';
  html += '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; font-size: 12px;">';
  html += '<label>Basic Pay</label><value style="font-family: monospace;">6,000.00</value></div>';
  html += '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; font-size: 12px;">';
  html += '<label>Incentives</label><value style="font-family: monospace;">2,000.00</value></div>';
  html += '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-weight: 600; border-top: 2px solid #e5e7eb; padding-top: 0.75rem; font-size: 12px;">';
  html += '<label>Gross Pay</label><value style="font-family: monospace;">11,000.00</value></div>';
  html += '</div>';
  
  html += '<div style="border-top: 1px solid #e5e7eb; padding-top: 1rem; margin-top: 1rem;">';
  html += '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; font-size: 12px;">';
  html += '<label>SSS</label><value style="font-family: monospace;">406.24</value></div>';
  html += '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; font-size: 12px;">';
  html += '<label>PhilHealth</label><value style="font-family: monospace;">247.50</value></div>';
  html += '<div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-weight: 600; border-top: 2px solid #e5e7eb; padding-top: 0.75rem; font-size: 12px;">';
  html += '<label>Total Deductions</label><value style="font-family: monospace;">3,350.00</value></div>';
  html += '</div>';
  
  html += '<div style="border-top: 2px solid #1f2937; padding-top: 1rem; margin-top: 1rem; display: flex; justify-content: space-between; font-weight: 700; font-size: 14px;">';
  html += '<label>NET PAY</label><value style="font-family: monospace;">7,650.00</value></div>';
  html += '</div>';
  
  body.innerHTML = html;
  modal.classList.add('active');
  body.scrollTop = 0;
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
