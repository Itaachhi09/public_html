<?php
/**
 * Payroll Processing and Approval Module
 * Execute payroll safely with multi-level approval workflow
 */
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

  <!-- Payroll Runs List -->
  <div class="section">
    <h3 class="section-header">📋 Payroll Runs</h3>

    <div class="payroll-list">
      <!-- Current Pending Payroll -->
      <div class="payroll-item">
        <div class="payroll-info">
          <h4>Payroll Run - February 2026 (Period 1: Feb 1-15)</h4>
          <p>Cut-off: Feb 1-15, 2026 | Pay Date: Feb 22, 2026</p>
          <p style="margin-top: 0.5rem;">
            <span class="badge badge-draft">DRAFT</span>
            <span style="margin-left: 0.5rem; color: #6b7280;">8 employees | ₱87,000.00 gross</span>
          </p>
        </div>
        <div class="payroll-actions">
          <form method="GET" style="display: inline;">
            <input type="hidden" name="action" value="preview">
            <input type="hidden" name="payroll_id" value="PAYROLL-2026-02-01">
            <button type="submit" class="btn btn-primary btn-sm">Preview & Process</button>
          </form>
        </div>
      </div>

      <!-- Previous Approved Payroll -->
      <div class="payroll-item locked">
        <div class="payroll-info">
          <h4>Payroll Run - January 2026 (Period 2: Jan 16-31)</h4>
          <p>Cut-off: Jan 16-31, 2026 | Pay Date: Feb 7, 2026</p>
          <p style="margin-top: 0.5rem;">
            <span class="badge badge-locked">✓ LOCKED</span>
            <span style="margin-left: 0.5rem; color: #6b7280;">8 employees | ₱85,500.00 gross</span>
          </p>
        </div>
        <div class="payroll-actions">
          <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayrollModal('PAYROLL-2026-01-16', 'January 2026 Period 2', '8', '85500.00')">View Details</button>
        </div>
      </div>

      <!-- Previous Approved Payroll -->
      <div class="payroll-item locked">
        <div class="payroll-info">
          <h4>Payroll Run - January 2026 (Period 1: Jan 1-15)</h4>
          <p>Cut-off: Jan 1-15, 2026 | Pay Date: Jan 20, 2026</p>
          <p style="margin-top: 0.5rem;">
            <span class="badge badge-locked">✓ LOCKED</span>
            <span style="margin-left: 0.5rem; color: #6b7280;">8 employees | ₱83,200.00 gross</span>
          </p>
        </div>
        <div class="payroll-actions">
          <button type="button" class="btn btn-secondary btn-sm" onclick="window.openPayrollModal('PAYROLL-2026-01-01', 'January 2026 Period 1', '8', '83200.00')">View Details</button>
        </div>
      </div>
    </div>

    <!-- Create New Payroll Run -->
    <form method="POST" action="">
      <div class="form-section">
        <h4>🚀 Create New Payroll Run</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Select Payroll Period <span style="color: #ef4444;">*</span></label>
            <select name="payroll_period" required>
              <option value="">-- Select Period --</option>
              <option value="2026-02-01">February 2026 (Period 1: Feb 1-15)</option>
              <option value="2026-02-16">February 2026 (Period 2: Feb 16-28)</option>
              <option value="2026-03-01">March 2026 (Period 1: Mar 1-15)</option>
            </select>
            <small>Cutoff dates are defined in Setup & Configuration</small>
          </div>
          <div class="form-group">
            <label>Include Employees</label>
            <div style="padding: 0.75rem; background: white; border: 1px solid #d1d5db; border-radius: 4px; color: #6b7280; font-size: 13px;">
              8 active employees with payroll profiles will be included
            </div>
          </div>
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="run_payroll" class="btn btn-primary">Run Payroll</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Payroll Preview (After Running) -->
  <div class="section">
    <h3 class="section-header">👁️ Payroll Preview - February 2026 Period 1</h3>

    <div class="alert alert-info">
      This is a preview of the payroll run. Review all details carefully before proceeding to approval workflow.
    </div>

    <!-- Preview Summary -->
    <div class="preview-summary">
      <h4 style="margin: 0 0 1rem 0; color: #1e40af;">Payroll Summary</h4>
      <div class="preview-summary-row">
        <div class="preview-summary-item">
          <label>Payroll Period</label>
          <value>Feb 1-15, 2026</value>
        </div>
        <div class="preview-summary-item">
          <label>Employee Count</label>
          <value>8</value>
        </div>
        <div class="preview-summary-item">
          <label>Total Gross Earnings</label>
          <value>₱ 87,000.00</value>
        </div>
      </div>
      <div class="preview-summary-row" style="margin-top: 1rem;">
        <div class="preview-summary-item">
          <label>Total Deductions</label>
          <value>₱ 26,500.00</value>
        </div>
        <div class="preview-summary-item">
          <label>Total Net Payable</label>
          <value>₱ 60,500.00</value>
        </div>
        <div class="preview-summary-item">
          <label>Status</label>
          <value style="color: #f59e0b;">DRAFT</value>
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
          <tr>
            <td>EMP-001</td>
            <td>John Doe</td>
            <td class="amount">11,000.00</td>
            <td class="amount">2,850.00</td>
            <td class="amount">500.00</td>
            <td class="amount">3,350.00</td>
            <td class="amount">7,650.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <tr>
            <td>EMP-002</td>
            <td>Jane Smith</td>
            <td class="amount">11,000.00</td>
            <td class="amount">2,900.00</td>
            <td class="amount">800.00</td>
            <td class="amount">3,700.00</td>
            <td class="amount">7,300.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <tr>
            <td>EMP-003</td>
            <td>Michael Johnson</td>
            <td class="amount">12,500.00</td>
            <td class="amount">3,250.00</td>
            <td class="amount">1,200.00</td>
            <td class="amount">4,450.00</td>
            <td class="amount">8,050.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <tr>
            <td>EMP-004</td>
            <td>Sarah Williams</td>
            <td class="amount">9,000.00</td>
            <td class="amount">2,200.00</td>
            <td class="amount">400.00</td>
            <td class="amount">2,600.00</td>
            <td class="amount">6,400.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <tr>
            <td>EMP-005</td>
            <td>Robert Brown</td>
            <td class="amount">9,000.00</td>
            <td class="amount">2,150.00</td>
            <td class="amount">600.00</td>
            <td class="amount">2,750.00</td>
            <td class="amount">6,250.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <tr>
            <td>EMP-006</td>
            <td>Emily Davis</td>
            <td class="amount">11,000.00</td>
            <td class="amount">2,875.00</td>
            <td class="amount">700.00</td>
            <td class="amount">3,575.00</td>
            <td class="amount">7,425.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <tr>
            <td>EMP-007</td>
            <td>David Martinez</td>
            <td class="amount">9,700.00</td>
            <td class="amount">2,380.00</td>
            <td class="amount">850.00</td>
            <td class="amount">3,230.00</td>
            <td class="amount">6,470.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
          <tr>
            <td>EMP-008</td>
            <td>Jessica Wilson</td>
            <td class="amount">13,800.00</td>
            <td class="amount">3,595.00</td>
            <td class="amount">1,395.00</td>
            <td class="amount">4,990.00</td>
            <td class="amount">8,810.00</td>
            <td><span class="badge badge-draft">DRAFT</span></td>
          </tr>
        </tbody>
        <tfoot>
          <tr style="background: #f3f4f6; font-weight: 600;">
            <td colspan="2" style="text-align: right;">TOTAL</td>
            <td class="amount">87,000.00</td>
            <td class="amount">22,200.00</td>
            <td class="amount">6,445.00</td>
            <td class="amount">28,645.00</td>
            <td class="amount">58,355.00</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Outliers -->
    <div class="outlier-section">
      <h4>⚠️ Outliers & Alerts</h4>
      <div class="outlier-item">
        <span class="outlier-type">HIGH DEDUCTION</span>
        <strong>EMP-008 (Jessica Wilson):</strong> Deductions are 36.3% of gross (₱4,990.00). Contains pending manual loan deduction.
      </div>
      <div class="outlier-item">
        <span class="outlier-type">NEGATIVE VARIANCE</span>
        <strong>EMP-004 (Sarah Williams):</strong> Net pay 28.9% below average. Verify deductions are correct.
      </div>
    </div>

    <!-- Payroll Approval Workflow -->
    <h3 class="section-header" style="margin-top: 2rem;">✓ Approval Workflow</h3>

    <div class="alert alert-info">
      Payroll requires three-step approval before processing. Each approver verifies and authorizes specific aspects of the payroll.
    </div>

    <div class="approval-flow">
      <div class="flow-step completed">
        <h4>Step 1: Payroll Officer</h4>
        <p>Initiates payroll run, reviews preview</p>
        <div class="status">✓ COMPLETED</div>
        <div style="margin-top: 0.5rem; font-size: 11px; color: #6b7280;">
          Juan dela Cruz | Feb 8, 2026 10:30 AM
        </div>
      </div>

      <div class="flow-step current">
        <h4>Step 2: HR Manager</h4>
        <p>Verifies employee records and calculations</p>
        <div class="status">AWAITING APPROVAL</div>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid;">
          <form method="POST" action="" style="display: flex; gap: 0.5rem;">
            <button type="submit" name="action" value="hr_approve" class="btn btn-success btn-sm">Approve</button>
            <button type="submit" name="action" value="hr_reject" class="btn btn-danger btn-sm">Reject</button>
          </form>
        </div>
      </div>

      <div class="flow-step pending">
        <h4>Step 3: Finance Manager</h4>
        <p>Final authorization before processing</p>
        <div class="status">PENDING</div>
      </div>
    </div>

    <!-- HR Approval Form (When In Step 2) -->
    <div class="form-section" style="margin-top: 2rem; background: #dbeafe; border-left-color: #f59e0b;">
      <h4 style="color: #1e40af;">HR Manager Review & Approval</h4>
      <div class="alert alert-info">
        As HR Manager, verify that all employee records, earnings, and deductions are accurate before proceeding to Finance approval.
      </div>

      <form method="POST" action="">
        <div class="form-row full">
          <div class="form-group">
            <label>Review Notes (Optional)</label>
            <textarea name="hr_notes" style="min-height: 80px; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-family: inherit; font-size: 13px;" placeholder="Add any review notes or concerns..."></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Employee Count Verified</label>
            <input type="checkbox" name="hr_employee_count" value="1" style="width: auto; margin-top: 0.25rem;">
            <small>I confirm 8 active employees included in payroll</small>
          </div>
          <div class="form-group">
            <label>Calculations Verified</label>
            <input type="checkbox" name="hr_calculations" value="1" style="width: auto; margin-top: 0.25rem;">
            <small>I verified earnings, deductions, and net pay calculations</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>No System Errors</label>
            <input type="checkbox" name="hr_no_errors" value="1" style="width: auto; margin-top: 0.25rem;">
            <small>No system errors or warnings detected</small>
          </div>
          <div class="form-group">
            <label>Deductions Validated</label>
            <input type="checkbox" name="hr_deductions" value="1" style="width: auto; margin-top: 0.25rem;">
            <small>All statutory and other deductions are correct</small>
          </div>
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="hr_approve" class="btn btn-success">Approve for Finance Review</button>
          <button type="submit" name="action" value="hr_reject" class="btn btn-danger">Reject - Request Corrections</button>
          <button type="reset" class="btn btn-secondary">Clear Form</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Locked Payroll View -->
  <div class="section">
    <h3 class="section-header">🔒 Locked Payroll - January 2026 Period 2</h3>

    <div class="alert alert-success">
      <strong>✓ Payroll Locked:</strong> This payroll has been approved and processed. No further edits are permitted. All changes are tracked in the audit log.
    </div>

    <div class="approval-flow">
      <div class="flow-step completed">
        <h4>Step 1: Payroll Officer</h4>
        <p>Initiated payroll run</p>
        <div class="status">✓ COMPLETED</div>
        <div style="margin-top: 0.5rem; font-size: 11px; color: #6b7280;">
          Juan dela Cruz | Jan 31, 2026 9:15 AM
        </div>
      </div>

      <div class="flow-step completed">
        <h4>Step 2: HR Manager</h4>
        <p>Verified employee records</p>
        <div class="status">✓ APPROVED</div>
        <div style="margin-top: 0.5rem; font-size: 11px; color: #6b7280;">
          Maria Santos | Jan 31, 2026 2:00 PM
        </div>
      </div>

      <div class="flow-step completed">
        <h4>Step 3: Finance Manager</h4>
        <p>Final authorization</p>
        <div class="status">✓ APPROVED</div>
        <div style="margin-top: 0.5rem; font-size: 11px; color: #6b7280;">
          Carlos Reyes | Feb 1, 2026 10:30 AM
        </div>
      </div>
    </div>

    <div class="alert alert-info" style="margin-top: 2rem;">
      <strong>ℹ️ Next Steps:</strong> This locked payroll is ready for bank file generation and salary disbursement. Process through Banking module to generate bank transfer files.
    </div>
  </div>

  <!-- Important Rules & Information -->
  <div class="section">
    <h3 class="section-header">📋 Payroll Processing Rules</h3>

    <div class="alert alert-warning">
      <strong>⚠️ Critical Rules:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>Approved payroll is locked:</strong> Once all three approvals are obtained, payroll cannot be edited or deleted.</li>
        <li><strong>No edits after approval:</strong> Any corrections require creating a new payroll run (adjustment payroll).</li>
        <li><strong>Outlier review required:</strong> All employees with outliers must be reviewed before HR approval.</li>
        <li><strong>Mandatory verifications:</strong> HR approver must verify employee count, calculations, and deductions before approving.</li>
        <li><strong>Three-step approval:</strong> Payroll Officer initiates → HR verifies → Finance authorizes.</li>
      </ul>
    </div>

    <div class="alert alert-info">
      <strong>ℹ️ Approval Roles & Responsibilities:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>Payroll Officer:</strong> Creates payroll run, reviews preview, submits for HR approval.</li>
        <li><strong>HR Manager:</strong> Verifies employee data accuracy, earnings, deductions, and outliers. Can reject if issues found.</li>
        <li><strong>Finance Manager:</strong> Final approval with authority to process payroll. Verifies bank details and payment instructions.</li>
      </ul>
    </div>

    <div class="alert alert-success">
      <strong>✓ Audit Trail:</strong> All approvals, rejections, and payroll changes are logged with timestamp and user information for compliance and audit purposes.
    </div>
  </div>

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
  
  // Sample employee data
  const employees = [
    {name: 'John Doe (EMP-001)', basic: '6,000.00', gross: '11,000.00', ded: '3,350.00', net: '7,650.00'},
    {name: 'Jane Smith (EMP-002)', basic: '6,000.00', gross: '11,000.00', ded: '3,700.00', net: '7,300.00'},
    {name: 'Michael Johnson (EMP-003)', basic: '7,000.00', gross: '12,500.00', ded: '4,450.00', net: '8,050.00'},
    {name: 'Sarah Williams (EMP-004)', basic: '5,000.00', gross: '9,000.00', ded: '2,600.00', net: '6,400.00'},
    {name: 'Robert Brown (EMP-005)', basic: '5,000.00', gross: '9,000.00', ded: '2,750.00', net: '6,250.00'},
    {name: 'Emily Davis (EMP-006)', basic: '6,000.00', gross: '11,000.00', ded: '3,575.00', net: '7,425.00'},
    {name: 'David Martinez (EMP-007)', basic: '5,400.00', gross: '9,700.00', ded: '3,230.00', net: '6,470.00'},
    {name: 'Jessica Wilson (EMP-008)', basic: '7,500.00', gross: '13,800.00', ded: '4,990.00', net: '8,810.00'}
  ];
  
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

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    window.closePayrollModal();
  }
});
</script>
