<?php
/**
 * Payroll Processing and Approval Module
 * Execute payroll safely with multi-level approval workflow
 * Follows MVC pattern - all routing through PayrollRunController
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
require_once __DIR__ . '/../models/PayrollApproval.php';
require_once __DIR__ . '/../models/PayrollRun.php';
require_once __DIR__ . '/../models/PayrollRunEmployee.php';
require_once __DIR__ . '/../controllers/PayrollRunController.php';

$payrollApproval = new PayrollApproval();
$payrollRun = new PayrollRun();
$payrollRunEmployee = new PayrollRunEmployee();

// Route controller actions (handles POST/GET requests)
$controllerData = PayrollRunController::route();

// Handle preview parameter from URL
if (!empty($_GET['preview_payroll_id'])) {
    $_SESSION['preview_payroll_id'] = (int)$_GET['preview_payroll_id'];
}

// Check if user selected a payroll to preview
$previewPayrollId = $_SESSION['preview_payroll_id'] ?? null;
$previewData = null;
$previewEmployees = [];

if ($previewPayrollId) {
    $previewData = $payrollRun->find($previewPayrollId);
    if ($previewData) {
        $previewEmployees = $payrollRunEmployee->getByRunWithEmployee($previewPayrollId);
    }
}

// Fetch approval data
$pendingApprovals = $payrollApproval->getByStatus('pending');
$approvedRuns = $payrollApproval->getByStatus('approved');
$rejectedRuns = $payrollApproval->getByStatus('rejected');
?>

<style>
  .processing-container {
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

  .btn-primary:disabled {
    background: #9ca3af;
    cursor: not-allowed;
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

  .btn-danger {
    background: #ef4444;
    color: white;
  }

  .btn-danger:hover {
    background: #dc2626;
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

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-draft {
    background: #f3f4f6;
    color: #6b7280;
  }

  .badge-pending-hr {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-pending-finance {
    background: #fce7f3;
    color: #be185d;
  }

  .badge-approved {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-locked {
    background: #e0e7ff;
    color: #3730a3;
  }

  .badge-rejected {
    background: #fee2e2;
    color: #991b1b;
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
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
  }

  .approval-flow {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .flow-step {
    padding: 1.5rem;
    border-radius: 8px;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    text-align: center;
  }

  .flow-step.completed {
    background: #d1fae5;
    border-color: #22c55e;
  }

  .flow-step.current {
    background: #dbeafe;
    border-color: #3b82f6;
    border-width: 3px;
  }

  .flow-step.pending {
    background: #fef3c7;
    border-color: #f59e0b;
  }

  .flow-step h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .flow-step p {
    margin: 0;
    color: #6b7280;
    font-size: 12px;
  }

  .flow-step .status {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid;
    border-color: inherit;
    font-weight: 600;
    color: #065f46;
    font-size: 12px;
  }

  .flow-step.pending .status {
    color: #92400e;
  }

  .outlier-section {
    margin-top: 2rem;
    padding: 1rem;
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 4px;
  }

  .outlier-section h4 {
    margin: 0 0 1rem 0;
    color: #92400e;
    font-size: 14px;
    font-weight: 600;
  }

  .outlier-item {
    padding: 0.75rem;
    background: white;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    font-size: 13px;
    color: #1f2937;
  }

  .outlier-item .type {
    display: inline-block;
    background: #f59e0b;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-weight: 500;
    margin-right: 0.5rem;
    font-size: 11px;
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

  .alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
  }

  .payroll-list {
    margin-bottom: 2rem;
  }

  /* Scrollable History Table */
  .payroll-history-table {
    max-height: 600px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }

  .payroll-history-table::-webkit-scrollbar {
    width: 8px;
  }

  .payroll-history-table::-webkit-scrollbar-track {
    background: #f3f4f6;
  }

  .payroll-history-table::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
  }

  .payroll-history-table::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
  }

  .payroll-history-table table tr:hover {
    background: #f3f4f6 !important;
  }

  .payroll-item {
    padding: 1.5rem;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid #3b82f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .payroll-item.locked {
    border-left-color: #e0e7ff;
  }

  .payroll-info h4 {
    margin: 0 0 0.25rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .payroll-info p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .payroll-actions {
    display: flex;
    gap: 0.5rem;
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
  }

  .preview-summary {
    background: #dbeafe;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
  }

  .preview-summary-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
  }

  .preview-summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .preview-summary-item label {
    color: #1e40af;
    font-weight: 500;
    font-size: 13px;
  }

  .preview-summary-item .value {
    font-size: 20px;
    font-weight: 700;
    color: #1e3a8a;
    font-family: 'Courier New', monospace;
  }

  /* Modal Styles for Payroll Details */
  .payroll-modal-overlay {
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

  .payroll-modal-overlay.active {
    display: flex;
  }

  .payroll-modal {
    background: white;
    border-radius: 8px;
    max-width: 900px;
    width: 95%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
  }

  .payroll-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
  }

  .payroll-modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
  }

  .payroll-modal-close {
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

  .payroll-modal-close:hover {
    color: #1f2937;
    background: #e5e7eb;
    border-radius: 4px;
  }

  .payroll-modal-body {
    padding: 2rem;
  }
</style>

<div class="processing-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Payroll Processing & Approval</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Execute payroll safely with multi-level approval workflow. Preview payroll before approval, then proceed through HR and Finance authorizations.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #d1fae5; border-radius: 4px; color: #065f46; font-size: 13px;">
      <strong>✓ Safety Features:</strong> Approved payroll is locked. No edits allowed after approval. All changes tracked in audit log.
    </div>
  </div>

  <!-- Message Display -->
  <?php 
  $current_payroll_id = $previewPayrollId ?? null;
  $message = $_SESSION['payroll_message'] ?? null;
  $message_payroll_id = $_SESSION['payroll_message_payroll_id'] ?? null;
  
  if ($message && ($message_payroll_id === $current_payroll_id || !$current_payroll_id)) {
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
      ?>
      <div style="margin-bottom: 2rem; padding: 1rem; background: <?php echo $bg_color; ?>; border-radius: 4px; color: <?php echo $text_color; ?>; font-size: 14px; border-left: 4px solid <?php echo $text_color; ?>;">
          <strong><?php echo $icon; ?> <?php echo ucfirst($message['type']); ?>:</strong> <?php echo htmlspecialchars($message['text']); ?>
      </div>
      <?php 
      unset($_SESSION['payroll_message']);
      unset($_SESSION['payroll_message_payroll_id']);
  } 
  ?>

  <!-- Create New Payroll Run & History -->
  <?php if (!$previewData): ?>
  <div class="section">
    <!-- Create New Payroll Run -->
    <div class="form-section">
      <h4>🚀 Create New Payroll Run</h4>
      <div class="form-row">
        <div class="form-group">
          <label>Period Start Date <span style="color: #ef4444;">*</span></label>
          <input type="date" id="payroll_start_date" name="start_date" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
        </div>
        <div class="form-group">
          <label>Period End Date <span style="color: #ef4444;">*</span></label>
          <input type="date" id="payroll_end_date" name="end_date" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Pay Date <span style="color: #ef4444;">*</span></label>
          <input type="date" id="payroll_pay_date" name="pay_date" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;">
        </div>
        <div class="form-group">
          <label>Include Employees</label>
          <div style="padding: 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 4px; color: #6b7280; font-size: 13px;">
            8 active employees with payroll profiles will be included
          </div>
        </div>
      </div>

      <div class="btn-group">
        <button type="button" onclick="window.openPayrollConfirmModal()" class="btn btn-primary">Run Payroll</button>
      </div>
    </div>

    <h3 class="section-header" style="margin-top: 2rem;">📋 Payroll Runs History</h3>

    <div class="payroll-history-table">
      <?php 
        // Fetch all payroll runs with user info from database
        $allRuns = Database::getInstance()->query(
          "SELECT pr.*, u.name as created_by_name 
           FROM payroll_runs pr 
           LEFT JOIN users u ON pr.created_by = u.id 
           ORDER BY pr.start_date DESC"
        );
        
        if (empty($allRuns)) {
          echo '<div style="padding: 2rem; text-align: center; color: #6b7280; background: #f9fafb; border-radius: 4px;">No payroll runs created yet.</div>';
        } else {
      ?>
      <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <thead>
          <tr style="background: #f3f4f6; border-bottom: 2px solid #e5e7eb; position: sticky; top: 0; z-index: 10;">
            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #1f2937;">Period Name</th>
            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #1f2937;">Cut-off Dates</th>
            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #1f2937;">Pay Date</th>
            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #1f2937;">Status</th>
            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #1f2937;">Employees</th>
            <th style="padding: 1rem; text-align: right; font-weight: 600; color: #1f2937;">Gross Amount</th>
            <th style="padding: 1rem; text-align: left; font-weight: 600; color: #1f2937;">Processed By</th>
            <th style="padding: 1rem; text-align: center; font-weight: 600; color: #1f2937;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $rowCount = 0;
            foreach ($allRuns as $pRun) {
              $rowCount++;
              // Get employees and totals for this run
              $employees = $payrollRunEmployee->getByRunWithEmployee($pRun['id']);
              $employeeCount = count($employees);
              $totalGross = 0;
              $totalDeductions = 0;
              $totalNet = 0;
              
              foreach ($employees as $emp) {
                $totalGross += (float)$emp['gross_pay'];
                $totalDeductions += (float)$emp['total_deductions'];
                $totalNet += (float)$emp['net_pay'];
              }
              
              // Determine status badge
              $statusBadge = '';
              $statusColor = '';
              switch (strtoupper($pRun['status'])) {
                case 'DRAFT':
                  $statusBadge = 'DRAFT';
                  $statusColor = '#fbbf24';
                  break;
                case 'PROCESSED':
                  $statusBadge = '⏳ PROCESSED';
                  $statusColor = '#3b82f6';
                  break;
                case 'LOCKED':
                  $statusBadge = '✓ LOCKED';
                  $statusColor = '#10b981';
                  break;
                case 'APPROVED':
                  $statusBadge = '✓ APPROVED';
                  $statusColor = '#10b981';
                  break;
                default:
                  $statusBadge = strtoupper($pRun['status']);
                  $statusColor = '#6b7280';
              }
              
              $bgColor = $rowCount % 2 === 0 ? '#f9fafb' : 'white';
              $createdByName = $pRun['created_by_name'] ?? 'System';
          ?>
          <tr style="border-bottom: 1px solid #e5e7eb; background: <?php echo $bgColor; ?>;">
            <td style="padding: 1rem; color: #1f2937; font-weight: 500;"><?php echo htmlspecialchars($pRun['period_name']); ?></td>
            <td style="padding: 1rem; color: #6b7280; font-size: 13px;">
              <?php echo date('M d, Y', strtotime($pRun['start_date'])); ?> - <?php echo date('M d, Y', strtotime($pRun['end_date'])); ?>
            </td>
            <td style="padding: 1rem; color: #6b7280; font-size: 13px;">
              <?php echo date('M d, Y', strtotime($pRun['pay_date'])); ?>
            </td>
            <td style="padding: 1rem; text-align: center;">
              <span style="background: <?php echo $statusColor; ?>; color: white; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 12px; font-weight: 500; display: inline-block;">
                <?php echo $statusBadge; ?>
              </span>
            </td>
            <td style="padding: 1rem; text-align: center; color: #1f2937; font-weight: 500;">
              <?php echo $employeeCount; ?>
            </td>
            <td style="padding: 1rem; text-align: right; color: #1f2937; font-weight: 500;">
              ₱<?php echo number_format($totalGross, 2); ?>
            </td>
            <td style="padding: 1rem; color: #6b7280; font-size: 13px;">
              <?php echo htmlspecialchars($createdByName); ?>
            </td>
            <td style="padding: 1rem; text-align: center;">
              <button type="button" style="background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;" 
                onclick="window.viewPayrollDetails(<?php echo (int)$pRun['id']; ?>)">
                View Details
              </button>
            </td>
          </tr>
          <?php
            }
          ?>
        </tbody>
      </table>
      <?php
        }
      ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Payroll Preview (After Running) -->
  <?php if ($previewData): ?>
  <div class="section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
      <h3 class="section-header" style="margin: 0;">👁️ Payroll Preview</h3>
      <button type="button" onclick="window.location.href='<?= BASE_URL ?>dashboard.php'" class="btn btn-secondary btn-sm">← Back to List</button>
    </div>

    <div class="alert alert-info">
      This is a preview of the payroll run. Review all details carefully before proceeding to approval workflow.
    </div>

    <!-- Preview Summary -->
    <div class="preview-summary">
      <h4 style="margin: 0 0 1rem 0; color: #1e40af;">Payroll Summary</h4>
      <?php 
        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        foreach ($previewEmployees as $emp) {
          $totalGross += (float)$emp['gross_pay'];
          $totalDeductions += (float)$emp['total_deductions'];
          $totalNet += (float)$emp['net_pay'];
        }
      ?>
      <div class="preview-summary-row">
        <div class="preview-summary-item">
          <label>Payroll Period</label>
          <value><?php echo htmlspecialchars($previewData['period_name'] ?? ''); ?></value>
        </div>
        <div class="preview-summary-item">
          <label>Employee Count</label>
          <value><?php echo count($previewEmployees); ?></value>
        </div>
        <div class="preview-summary-item">
          <label>Total Gross Earnings</label>
          <value>₱ <?php echo number_format($totalGross, 2); ?></value>
        </div>
      </div>
      <div class="preview-summary-row" style="margin-top: 1rem;">
        <div class="preview-summary-item">
          <label>Total Deductions</label>
          <value>₱ <?php echo number_format($totalDeductions, 2); ?></value>
        </div>
        <div class="preview-summary-item">
          <label>Total Net Payable</label>
          <value>₱ <?php echo number_format($totalNet, 2); ?></value>
        </div>
        <div class="preview-summary-item">
          <label>Status</label>
          <value style="color: #f59e0b;"><?php echo htmlspecialchars($previewData['status'] ?? 'DRAFT'); ?></value>
        </div>
      </div>
    </div>

    <!-- Detailed Payroll Table -->
    <h4 style="color: #1f2937; margin: 2rem 0 1rem 0; font-size: 14px; font-weight: 600;">Detailed Payroll Breakdown</h4>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Gross Earnings</th>
            <th>Statutory Deductions</th>
            <th>Other Deductions</th>
            <th>Total Deductions</th>
            <th>Net Pay</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($previewEmployees as $emp): ?>
          <tr>
            <td><?php echo htmlspecialchars($emp['employee_code'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '')); ?></td>
            <td class="amount"><?php echo number_format((float)$emp['gross_pay'], 2); ?></td>
            <td class="amount">—</td>
            <td class="amount">—</td>
            <td class="amount"><?php echo number_format((float)$emp['total_deductions'], 2); ?></td>
            <td class="amount"><?php echo number_format((float)$emp['net_pay'], 2); ?></td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="background: #f3f4f6; font-weight: 600;">
            <td colspan="2" style="text-align: right;">TOTAL</td>
            <td class="amount"><?php echo number_format($totalGross, 2); ?></td>
            <td class="amount">—</td>
            <td class="amount">—</td>
            <td class="amount"><?php echo number_format($totalDeductions, 2); ?></td>
            <td class="amount"><?php echo number_format($totalNet, 2); ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>


  </div>
  <?php endif; ?>

</div>

<!-- Modal for Payroll Details View -->
<div class="payroll-modal-overlay" id="payroll-modal-overlay" onclick="if(event.target === this) window.closePayrollModal()">
  <div class="payroll-modal">
    <div class="payroll-modal-header">
      <h2 class="payroll-modal-title" id="payroll-modal-title">Payroll Details</h2>
      <button type="button" class="payroll-modal-close" onclick="window.closePayrollModal()">×</button>
    </div>
    <div class="payroll-modal-body" id="payroll-modal-body">
      <!-- Content will be injected here -->
    </div>
  </div>
</div>

<script>
<?php
  $jsEmployees = [];
  foreach ($previewEmployees as $pe) {
    $jsEmployees[] = [
      'name' => trim(($pe['first_name'] ?? '') . ' ' . ($pe['last_name'] ?? '') . ' (' . ($pe['employee_code'] ?? '') . ')'),
      'basic' => number_format($pe['basic_pay'] ?? 0, 2),
      'gross' => number_format($pe['gross_pay'] ?? 0, 2),
      'ded' => number_format($pe['total_deductions'] ?? 0, 2),
      'net' => number_format($pe['net_pay'] ?? 0, 2),
    ];
  }
?>
// Payroll Modal Functions
window.openPayrollModal = function(payrollId, periodName, employeeCount, grossAmount) {
  const modal = document.getElementById('payroll-modal-overlay');
  const title = document.getElementById('payroll-modal-title');
  const body = document.getElementById('payroll-modal-body');
  
  title.textContent = 'Payroll Details - ' + periodName;
  
  // Sample payroll details data
  let html = '<div style="padding: 1rem;">';
  html += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">';
  html += '<div><label style="font-size: 12px; color: #6b7280; font-weight: 600;">Payroll ID</label><div style="font-size: 16px; color: #1f2937; font-weight: 600;">' + payrollId + '</div></div>';
  html += '<div><label style="font-size: 12px; color: #6b7280; font-weight: 600;">Period</label><div style="font-size: 16px; color: #1f2937; font-weight: 600;">' + periodName + '</div></div>';
  html += '<div><label style="font-size: 12px; color: #6b7280; font-weight: 600;">Employees</label><div style="font-size: 16px; color: #1f2937; font-weight: 600;">' + employeeCount + '</div></div>';
  html += '<div><label style="font-size: 12px; color: #6b7280; font-weight: 600;">Total Gross</label><div style="font-size: 16px; color: #1f2937; font-weight: 600; font-family: monospace;">₱' + parseFloat(grossAmount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</div></div>';
  html += '</div>';
  
  html += '<div style="border-top: 2px solid #e5e7eb; padding-top: 1.5rem; margin-top: 1.5rem;">';
  html += '<h3 style="margin: 0 0 1rem 0; font-size: 14px; font-weight: 600; color: #1f2937;">Summary by Employee</h3>';
  html += '<div style="overflow-x: auto;">';
  html += '<table style="width: 100%; font-size: 12px; border-collapse: collapse;">';
  html += '<thead><tr style="background: #f3f4f6;">';
  html += '<th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Employee</th>';
  html += '<th style="padding: 0.75rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Basic</th>';
  html += '<th style="padding: 0.75rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Gross</th>';
  html += '<th style="padding: 0.75rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Deductions</th>';
  html += '<th style="padding: 0.75rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Net</th>';
  html += '</tr></thead>';
  html += '<tbody>';
  
  // Use server-provided preview employees when available
  const employees = <?php echo json_encode($jsEmployees); ?> || [];
  
  employees.forEach(emp => {
    html += '<tr style="border-bottom: 1px solid #e5e7eb;">';
    html += '<td style="padding: 0.75rem;">' + emp.name + '</td>';
    html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace;">₱' + emp.basic + '</td>';
    html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace;">₱' + emp.gross + '</td>';
    html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace;">₱' + emp.ded + '</td>';
    html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace; font-weight: 600;">₱' + emp.net + '</td>';
    html += '</tr>';
  });
  
  html += '<tr style="background: #f9fafb; font-weight: 600; border-top: 2px solid #d1d5db;">';
  html += '<td style="padding: 0.75rem;">TOTAL</td>';
  html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace;">₱48,900.00</td>';
  html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace;' + (grossAmount === '85500.00' ? '">₱85,500.00' : '">₱83,200.00') + '</td>';
  html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace;">₱28,500.00</td>';
  html += '<td style="padding: 0.75rem; text-align: right; font-family: monospace;">₱' + (grossAmount === '85500.00' ? '57,000.00' : '54,700.00') + '</td>';
  html += '</tr>';
  html += '</tbody>';
  html += '</table>';
  html += '</div>';
  html += '</div>';
  
  body.innerHTML = html;
  modal.classList.add('active');
  body.scrollTop = 0;
};

window.closePayrollModal = function() {
  const modal = document.getElementById('payroll-modal-overlay');
  modal.classList.remove('active');
};

// Payroll Confirmation Modal Functions
window.openPayrollConfirmModal = function() {
  const startDate = document.getElementById('payroll_start_date').value;
  const endDate = document.getElementById('payroll_end_date').value;
  const payDate = document.getElementById('payroll_pay_date').value;
  
  // Validate inputs
  if (!startDate) {
    alert('Please enter a period start date.');
    document.getElementById('payroll_start_date').focus();
    return;
  }
  
  if (!endDate) {
    alert('Please enter a period end date.');
    document.getElementById('payroll_end_date').focus();
    return;
  }
  
  if (!payDate) {
    alert('Please enter a pay date.');
    document.getElementById('payroll_pay_date').focus();
    return;
  }
  
  // Validate date logic
  if (startDate >= endDate) {
    alert('Start date must be before end date.');
    return;
  }
  
  if (endDate >= payDate) {
    alert('End date must be before pay date.');
    return;
  }
  
  // Format dates for display
  const startObj = new Date(startDate);
  const endObj = new Date(endDate);
  const payObj = new Date(payDate);
  
  const startFormatted = startObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const endFormatted = endObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const payFormatted = payObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  
  // Generate period name
  const periodName = `Payroll Period: ${startFormatted} - ${endFormatted}`;
  
  
  // Create modal HTML
  const modalHTML = `
    <div class="payroll-modal-overlay active" id="payroll-confirm-overlay" onclick="if(event.target === this) window.closePayrollConfirmModal()">
      <div class="payroll-modal" style="width: 550px;">
        <div class="payroll-modal-header">
          <h2 class="payroll-modal-title">⚠️ Admin Verification Required</h2>
          <button type="button" class="payroll-modal-close" onclick="window.closePayrollConfirmModal()">×</button>
        </div>
        <div class="payroll-modal-body" style="padding: 2rem;">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; padding: 1rem; background: #f3f4f6; border-radius: 6px;">
            <div>
              <label style="font-size: 12px; color: #6b7280; font-weight: 600;">Payroll Period</label>
              <div style="font-size: 13px; color: #1f2937; font-weight: 500; margin-top: 0.5rem;">${startFormatted} to ${endFormatted}</div>
              <div style="font-size: 12px; color: #6b7280; margin-top: 0.25rem;">Pay Date: ${payFormatted}</div>
            </div>
            <div>
              <label style="font-size: 12px; color: #6b7280; font-weight: 600;">Employees to Process</label>
              <div style="font-size: 15px; color: #1f2937; font-weight: 600; margin-top: 0.5rem;">8 employees</div>
            </div>
          </div>
          
          <div style="padding: 1rem; background: #dbeafe; border-radius: 6px; border-left: 4px solid #3b82f6; margin-bottom: 2rem;">
            <div style="font-size: 13px; color: #1e40af; line-height: 1.6;">
              <strong>What will happen:</strong><br>
              ✓ All earnings components (basic, incentives, bonuses, allowances, overtime) will be calculated<br>
              ✓ All statutory deductions (withholding tax, SSS, PhilHealth, Pag-IBIG) will be calculated<br>
              ✓ Net pay will be generated for each employee<br>
              ✓ Payroll records will be locked
            </div>
          </div>
          
          <div style="margin-bottom: 2rem; padding: 1rem; background: #fef3c7; border-radius: 6px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 13px; color: #92400e; line-height: 1.6;">
              <strong>🔐 Security:</strong><br>
              Enter your admin password to authorize this payroll run. This action is logged for audit purposes.
            </div>
          </div>

          <div style="margin-bottom: 2rem;">
            <label style="font-size: 12px; color: #6b7280; font-weight: 600; display: block; margin-bottom: 0.5rem;">Admin Password <span style="color: #ef4444;">*</span></label>
            <input type="password" id="payroll_admin_password" placeholder="Enter your admin password" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; box-sizing: border-box;" />
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <button type="button" onclick="window.closePayrollConfirmModal()" class="btn btn-secondary" style="padding: 0.75rem; border: 1px solid #d1d5db; background: white; color: #1f2937; border-radius: 4px; cursor: pointer; font-weight: 500;">
              Cancel
            </button>
            <button type="button" onclick="window.submitPayrollRunWithPassword()" class="btn btn-primary" style="padding: 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
              Confirm & Run Payroll
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Remove existing confirm modals
  const existing = document.getElementById('payroll-confirm-overlay');
  if (existing) {
    existing.remove();
  }
  
  // Insert modal
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Focus on password field
  setTimeout(() => {
    document.getElementById('payroll_admin_password').focus();
  }, 100);
  
  // Allow Enter key to submit
  document.getElementById('payroll_admin_password').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      window.submitPayrollRunWithPassword();
    }
  });
};

window.closePayrollConfirmModal = function() {
  const overlay = document.getElementById('payroll-confirm-overlay');
  if (overlay) {
    overlay.classList.remove('active');
    setTimeout(() => {
      overlay.remove();
    }, 300);
  }
};

window.submitPayrollRunWithPassword = function() {
  const startDate = document.getElementById('payroll_start_date').value;
  const endDate = document.getElementById('payroll_end_date').value;
  const payDate = document.getElementById('payroll_pay_date').value;
  const adminPassword = document.getElementById('payroll_admin_password').value;
  
  if (!adminPassword || adminPassword.trim() === '') {
    alert('Please enter your admin password.');
    document.getElementById('payroll_admin_password').focus();
    return;
  }
  
  // Close modal
  window.closePayrollConfirmModal();
  
  // Build period name from dates
  const startObj = new Date(startDate);
  const endObj = new Date(endDate);
  const startFormatted = startObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const endFormatted = endObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const periodName = `Payroll Period: ${startFormatted} - ${endFormatted}`;
  
  // Build period value string: "Period Name|start_date|end_date|pay_date"
  const periodValue = `${periodName}|${startDate}|${endDate}|${payDate}`;
  
  // Use AJAX to submit without full page redirect
  const formData = new FormData();
  formData.append('action', 'run_payroll');
  formData.append('payroll_period', periodValue);
  formData.append('admin_password', adminPassword);
  
  // Show loading indicator
  const btn = document.querySelector('.btn-primary');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Processing Payroll...';
  }
  
  fetch('<?= BASE_URL ?>modules/payroll/payroll_processing_handler.php', {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Show success message
      const messageDiv = document.createElement('div');
      messageDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #22c55e; color: white; padding: 1rem 1.5rem; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 9999; animation: slideIn 0.3s ease;';
      messageDiv.innerHTML = '✓ ' + data.message;
      document.body.appendChild(messageDiv);
      
      // Reload page after success to show updated list
      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      // Show error message
      alert('Error: ' + data.message);
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Confirm & Run Payroll';
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while processing the payroll: ' + error);
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Confirm & Run Payroll';
    }
  });
};

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    window.closePayrollConfirmModal();
    window.closePayrollModal();
  }
});

// Payroll Approval Action Handler
window.submitPayrollApprovalAction = function(action, payrollId) {
  if (!payrollId) {
    alert('Payroll ID is missing.');
    return;
  }
  
  // Create and submit hidden form
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '<?= BASE_URL ?>dashboard.php';
  
  const actionInput = document.createElement('input');
  actionInput.type = 'hidden';
  actionInput.name = 'action';
  actionInput.value = action;
  
  const payrollIdInput = document.createElement('input');
  payrollIdInput.type = 'hidden';
  payrollIdInput.name = 'payroll_id';
  payrollIdInput.value = payrollId;
  
  const pageInput = document.createElement('input');
  pageInput.type = 'hidden';
  pageInput.name = 'page';
  pageInput.value = 'payroll_processing_approval';
  
  const refInput = document.createElement('input');
  refInput.type = 'hidden';
  refInput.name = 'ref';
  refInput.value = 'payroll';
  
  form.appendChild(actionInput);
  form.appendChild(payrollIdInput);
  form.appendChild(pageInput);
  form.appendChild(refInput);
  
  document.body.appendChild(form);
  form.submit();
};

// View Payroll Details in Modal
window.viewPayrollDetails = function(payrollId) {
  // Fetch payroll details via AJAX
  const formData = new FormData();
  formData.append('action', 'get_payroll_details');
  formData.append('payroll_id', payrollId);
  
  fetch('<?= BASE_URL ?>modules/payroll/payroll_processing_handler.php', {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      window.currentPayrollData = data.payroll;
      window.openPayrollDetailsModal(data.payroll, data.employees);
    } else {
      alert('Error loading payroll details: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while loading payroll details.');
  });
};

// Open Payroll Details Modal
window.openPayrollDetailsModal = function(payrollData, employees) {
  // Store employees globally for access in payslip display
  window.currentPayrollEmployees = employees;
  
  let employeeList = '';
  let firstEmployeeId = employees[0]?.employee_id || 0;
  
  employees.forEach((emp, idx) => {
    employeeList += `
      <div style="padding: 0.75rem 1rem; cursor: pointer; background: ${idx === 0 ? '#dbeafe' : '#f9fafb'}; border-left: ${idx === 0 ? '4px solid #3b82f6' : '4px solid transparent'}; transition: all 0.2s;"
        onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='${idx === 0 ? '#dbeafe' : '#f9fafb'}'"
        onclick="window.showPayslipDetail(${idx})">        <div style="font-weight: 500; color: #1f2937;">${emp.employee_code}</div>
        <div style="font-size: 12px; color: #6b7280;">${emp.first_name} ${emp.last_name}</div>
        <div style="font-size: 12px; color: #6b7280; margin-top: 0.25rem;">₱${parseFloat(emp.gross_pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
      </div>
    `;
  });
  
  const startDate = new Date(payrollData.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const endDate = new Date(payrollData.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  const payDate = new Date(payrollData.pay_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  
  const modalHTML = `
    <div class="payroll-modal-overlay active" id="details-modal-overlay" onclick="if(event.target === this) window.closePayrollDetailsModal()">
      <div class="payroll-modal" style="width: 1100px; max-height: 90vh; display: grid; grid-template-columns: 300px 1fr;">
        <!-- Employee List -->
        <div style="background: white; border-right: 1px solid #e5e7eb; overflow-y: auto; max-height: 90vh;">
          <div style="padding: 1rem; background: #f3f4f6; border-bottom: 1px solid #e5e7eb; font-weight: 600; color: #1f2937;">
            📋 Select Employee
          </div>
          <div id="employee-list-container" style="padding: 0;">
            ${employeeList}
          </div>
        </div>
        
        <!-- Payslip Detail -->
        <div style="overflow-y: auto; max-height: 90vh;">
          <div class="payroll-modal-header" style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <h2 class="payroll-modal-title" style="margin: 0;">PAYSLIP</h2>
              <button type="button" class="payroll-modal-close" onclick="window.closePayrollDetailsModal()" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #6b7280;">×</button>
            </div>
          </div>
          <div id="payslip-detail-container" style="padding: 2rem;">
            <!-- Will be populated by showPayslipDetail -->
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Remove existing modals
  const existing = document.getElementById('details-modal-overlay');
  if (existing) {
    existing.remove();
  }
  
  // Insert modal
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Show first employee's payslip
  if (employees.length > 0) {
    window.showPayslipDetail(0);
  }
};

// Show individual payslip detail
window.showPayslipDetail = function(empIdx) {
  // Get employee from global array
  const emp = window.currentPayrollEmployees ? window.currentPayrollEmployees[empIdx] : null;
  
  if (!emp) {
    console.error('Employee not found at index', empIdx);
    return;
  }
  
  // Use actual employee data from database
  const basicPay = emp.basic_pay || 0;
  const incentives = emp.incentives !== null && emp.incentives !== undefined ? emp.incentives : 0;
  const withholdingTax = emp.withholding_tax !== null && emp.withholding_tax !== undefined ? emp.withholding_tax : 0;
  const sssContribution = emp.sss_contribution !== null && emp.sss_contribution !== undefined ? emp.sss_contribution : 0;
  const philhealthContribution = emp.philhealth_contribution !== null && emp.philhealth_contribution !== undefined ? emp.philhealth_contribution : 0;
  const pagibigContribution = emp.pagibig_contribution !== null && emp.pagibig_contribution !== undefined ? emp.pagibig_contribution : 0;
  const grossPay = emp.gross_pay || 0;
  const totalDeductions = emp.total_deductions || 0;
  const netPay = emp.net_pay || 0;
  
  const payslipHTML = `
    <!-- Company Header -->
    <div style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #1f2937;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1rem;">
        <div>
          <h3 style="margin: 0 0 0.5rem 0; font-size: 16px; font-weight: 700;">Healthcare Hospital Inc.</h3>
          <p style="margin: 0.25rem 0; font-size: 12px; color: #6b7280;">123 Hospital Avenue, Makati City</p>
          <p style="margin: 0.25rem 0; font-size: 12px; color: #6b7280;">Email: payroll@healthcare.com | Phone: +63-2-555-1234</p>
        </div>
        <div style="text-align: right;">
          <h2 style="margin: 0; font-size: 20px; font-weight: 700;">PAYSLIP</h2>
          <p style="margin: 0.5rem 0 0 0; font-size: 12px; color: #6b7280;">Pay Period: ${window.currentPayrollData && window.currentPayrollData.start_date ? new Date(window.currentPayrollData.start_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) + ' - ' + new Date(window.currentPayrollData.end_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : ''}</p>
          <p style="margin: 0.25rem 0 0 0; font-size: 12px; color: #6b7280;">Payment Date: ${window.currentPayrollData && window.currentPayrollData.pay_date ? new Date(window.currentPayrollData.pay_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) : ''}</p>
        </div>
      </div>
    </div>
    
    <!-- Employee Information -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; padding: 1rem; background: #f9fafb; border-radius: 6px;">
      <div>
        <div style="margin-bottom: 1rem;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Employee ID</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">${emp.employee_code}</div>
        </div>
        <div style="margin-bottom: 1rem;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Name</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">${emp.first_name} ${emp.last_name}</div>
        </div>
        <div style="margin-bottom: 1rem;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Department</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">${emp.department_name || 'N/A'}</div>
        </div>
        <div style="margin-bottom: 0;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Position</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">${emp.position_name || 'N/A'}</div>
        </div>
      </div>
      <div>
        <div style="margin-bottom: 1rem;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Employment Type</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">Regular Full-Time</div>
        </div>
        <div style="margin-bottom: 1rem;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Pay Type</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">Per Month</div>
        </div>
        <div style="margin-bottom: 1rem;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Bank Account</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">•••••••...XXXX</div>
        </div>
        <div style="margin-bottom: 0;">
          <label style="font-size: 11px; color: #6b7280; font-weight: 600;">Generated</label>
          <div style="font-size: 14px; color: #1f2937; font-weight: 500;">${new Date().toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}</div>
        </div>
      </div>
    </div>
    
    <!-- Earnings Section -->
    <div style="margin-bottom: 1.5rem;">
      <h4 style="margin: 0 0 0.75rem 0; font-size: 12px; font-weight: 700; color: #1f2937; border-bottom: 1px solid #d1d5db; padding-bottom: 0.5rem;">EARNINGS</h4>
      <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 13px;">
        <span style="color: #1f2937;">Basic Pay</span>
        <span style="font-family: monospace; font-weight: 500;">₱${parseFloat(basicPay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 13px;">
        <span style="color: #1f2937;">Incentives / Allowances</span>
        <span style="font-family: monospace; font-weight: 500;">₱${parseFloat(incentives).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; font-size: 13px; font-weight: 600; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
        <span style="color: #1f2937;">Total Earnings</span>
        <span style="font-family: monospace;">₱${parseFloat(grossPay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
    </div>
    
    <!-- Deductions Section -->
    <div style="margin-bottom: 1.5rem;">
      <h4 style="margin: 0 0 0.75rem 0; font-size: 12px; font-weight: 700; color: #1f2937; border-bottom: 1px solid #d1d5db; padding-bottom: 0.5rem;">STATUTORY DEDUCTIONS</h4>
      <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 13px;">
        <span style="color: #1f2937;">Withholding Tax (BIR)</span>
        <span style="font-family: monospace; font-weight: 500;">₱${parseFloat(withholdingTax).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 13px;">
        <span style="color: #1f2937;">SSS Contribution</span>
        <span style="font-family: monospace; font-weight: 500;">₱${parseFloat(sssContribution).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 13px;">
        <span style="color: #1f2937;">PhilHealth Premium</span>
        <span style="font-family: monospace; font-weight: 500;">₱${parseFloat(philhealthContribution).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 13px;">
        <span style="color: #1f2937;">Pag-IBIG Contribution</span>
        <span style="font-family: monospace; font-weight: 500;">₱${parseFloat(pagibigContribution).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
      <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; font-size: 13px; font-weight: 600; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
        <span style="color: #1f2937;">Total Deductions</span>
        <span style="font-family: monospace;">₱${parseFloat(totalDeductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
      </div>
    </div>
    
    <!-- Net Pay -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #dbeafe; border-radius: 6px; border-left: 4px solid #3b82f6;">
      <span style="font-size: 14px; font-weight: 700; color: #1e40af;">NET PAY</span>
      <span style="font-family: monospace; font-size: 18px; font-weight: 700; color: #1e40af;">₱${parseFloat(netPay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
    </div>
    
    <div style="color: #6b7280; font-size: 12px; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
      Net payment via bank transfer to registered bank account
    </div>
  `;
  
  document.getElementById('payslip-detail-container').innerHTML = payslipHTML;
};

// Close Payroll Details Modal
window.closePayrollDetailsModal = function() {
  const overlay = document.getElementById('details-modal-overlay');
  if (overlay) {
    overlay.classList.remove('active');
    setTimeout(() => {
      overlay.remove();
    }, 300);
  }
};

// Clear payroll approval form
window.clearPayrollApprovalForm = function() {
  document.getElementById('hr_notes').value = '';
  document.getElementById('hr_employee_count').checked = false;
  document.getElementById('hr_calculations').checked = false;
  document.getElementById('hr_no_errors').checked = false;
  document.getElementById('hr_deductions').checked = false;
};



</script>
