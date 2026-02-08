<?php
/**
 * Deductions Management Module
 * Compute statutory and other deductions, apply them correctly per setup tables
 */
?>

<style>
  .deductions-container {
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

  .subtitle {
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 1.5rem;
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

  .btn-danger {
    background: #ef4444;
    color: white;
  }

  .btn-danger:hover {
    background: #dc2626;
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

  .amount-total {
    font-weight: 600;
    color: #dc2626;
  }

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-auto {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-manual {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-approved {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .deduction-card {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    border-left: 4px solid #3b82f6;
  }

  .deduction-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .deduction-card.statutory {
    border-left-color: #3b82f6;
  }

  .deduction-card.other {
    border-left-color: #f59e0b;
  }

  .deduction-breakdown {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
  }

  .deduction-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
  }

  .deduction-item label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
  }

  .deduction-item value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
    text-align: right;
    min-width: 100px;
  }

  .summary-card {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
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

  .summary-card.warning {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
  }

  .summary-card.warning label,
  .summary-card.warning .value {
    color: #92400e;
  }

  .summary-card.danger {
    background: #fee2e2;
    border-left: 4px solid #ef4444;
  }

  .summary-card.danger label,
  .summary-card.danger .value {
    color: #991b1b;
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

  .form-group small {
    color: #6b7280;
    font-size: 12px;
    margin-top: 0.25rem;
    display: block;
  }

  .deductions-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
  }
</style>

<div class="deductions-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Deductions Management</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Apply statutory and other deductions correctly following setup tables. Deductions include withholding tax, SSS, PhilHealth, Pag-IBIG, HMO, loans, absences, and penalties.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Key Rules:</strong> Deductions follow setup tables automatically. Manual entries require approval. Total deductions never exceed gross pay.
    </div>
  </div>

  <!-- Filters -->
  <div class="section">
    <form method="GET" action="">
      <div class="filter-section">
        <div class="form-group">
          <label>Search Employee</label>
          <input type="text" name="search" placeholder="Employee ID or Name..." value="">
        </div>
        <div class="form-group">
          <label>Department</label>
          <select name="department">
            <option value="">-- All Departments --</option>
            <option value="hr">Human Resources</option>
            <option value="it">Information Technology</option>
            <option value="ops">Operations</option>
            <option value="finance">Finance</option>
          </select>
        </div>
        <div class="form-group">
          <label>Deduction Status</label>
          <select name="deduction_status">
            <option value="">-- All Status --</option>
            <option value="calculated">Calculated</option>
            <option value="pending">Pending Review</option>
          </select>
        </div>
        <div style="display: flex; align-items: flex-end;">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Deduction Summary -->
  <div class="deductions-summary">
    <div class="summary-card">
      <label>Total Statutory Deductions</label>
      <div class="value">₱ 165,500</div>
    </div>
    <div class="summary-card">
      <label>Other Deductions</label>
      <div class="value">₱ 45,200</div>
    </div>
    <div class="summary-card danger">
      <label>Total All Deductions</label>
      <div class="value">₱ 210,700</div>
    </div>
    <div class="summary-card warning">
      <label>Pending Manual Approvals</label>
      <div class="value">3</div>
    </div>
  </div>

  <!-- Deductions Table -->
  <div class="section">
    <h3 class="section-header">Employee Deduction Summary</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Gross Pay</th>
            <th>Statutory</th>
            <th>Other Deductions</th>
            <th>Total Deductions</th>
            <th>Net Pay</th>
            <th>% of Gross</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>EMP-001</td>
            <td>John Doe</td>
            <td class="amount">11,000.00</td>
            <td class="amount">2,850.00</td>
            <td class="amount">500.00</td>
            <td class="amount amount-total">3,350.00</td>
            <td class="amount">7,650.00</td>
            <td style="text-align: center;">30.5%</td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-001">
                <button type="submit" class="btn btn-secondary btn-sm">View</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-002</td>
            <td>Jane Smith</td>
            <td class="amount">11,000.00</td>
            <td class="amount">2,900.00</td>
            <td class="amount">800.00</td>
            <td class="amount amount-total">3,700.00</td>
            <td class="amount">7,300.00</td>
            <td style="text-align: center;">33.6%</td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-002">
                <button type="submit" class="btn btn-secondary btn-sm">View</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-003</td>
            <td>Michael Johnson</td>
            <td class="amount">12,500.00</td>
            <td class="amount">3,250.00</td>
            <td class="amount">1,200.00</td>
            <td class="amount amount-total">4,450.00</td>
            <td class="amount">8,050.00</td>
            <td style="text-align: center;">35.6%</td>
            <td><span class="badge badge-pending">Pending Review</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-003">
                <button type="submit" class="btn btn-secondary btn-sm">View</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-004</td>
            <td>Sarah Williams</td>
            <td class="amount">9,000.00</td>
            <td class="amount">2,200.00</td>
            <td class="amount">400.00</td>
            <td class="amount amount-total">2,600.00</td>
            <td class="amount">6,400.00</td>
            <td style="text-align: center;">28.9%</td>
            <td><span class="badge badge-pending">Pending Review</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-004">
                <button type="submit" class="btn btn-secondary btn-sm">View</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-005</td>
            <td>Robert Brown</td>
            <td class="amount">9,000.00</td>
            <td class="amount">2,150.00</td>
            <td class="amount">600.00</td>
            <td class="amount amount-total">2,750.00</td>
            <td class="amount">6,250.00</td>
            <td style="text-align: center;">30.6%</td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-005">
                <button type="submit" class="btn btn-secondary btn-sm">View</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-006</td>
            <td>Emily Davis</td>
            <td class="amount">11,000.00</td>
            <td class="amount">2,875.00</td>
            <td class="amount">700.00</td>
            <td class="amount amount-total">3,575.00</td>
            <td class="amount">7,425.00</td>
            <td style="text-align: center;">32.5%</td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-006">
                <button type="submit" class="btn btn-secondary btn-sm">View</button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Detailed Deduction Breakdown -->
  <div class="section">
    <h3 class="section-header">Deduction Breakdown - John Doe (EMP-001)</h3>

    <!-- Statutory Deductions -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 14px; font-weight: 600;">📋 Statutory Deductions (Automatic - From Setup Tables)</h4>

      <div class="deduction-card statutory">
        <h4>Withholding Tax (BIR)</h4>
        <div class="deduction-breakdown">
          <div class="deduction-item">
            <label>Gross Pay</label>
            <value>₱ 11,000.00</value>
          </div>
          <div class="deduction-item">
            <label>Tax Rate Applied</label>
            <value>5%</value>
          </div>
          <div class="deduction-item">
            <label>Withholding Tax</label>
            <value style="font-weight: 600; color: #1f2937;">₱ 550.00</value>
          </div>
        </div>
        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">✓ Calculated from BIR withholding table based on gross pay</small>
      </div>

      <div class="deduction-card statutory">
        <h4>SSS (Social Security System)</h4>
        <div class="deduction-breakdown">
          <div class="deduction-item">
            <label>Gross Pay</label>
            <value>₱ 11,000.00</value>
          </div>
          <div class="deduction-item">
            <label>Employee Rate</label>
            <value>3.63%</value>
          </div>
          <div class="deduction-item">
            <label>SSS Contribution</label>
            <value style="font-weight: 600; color: #1f2937;">₱ 399.30</value>
          </div>
        </div>
        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">✓ Calculated from SSS table as per Employee Payroll Profile (Enrolled)</small>
      </div>

      <div class="deduction-card statutory">
        <h4>PhilHealth (Philippine Health Insurance)</h4>
        <div class="deduction-breakdown">
          <div class="deduction-item">
            <label>Gross Pay</label>
            <value>₱ 11,000.00</value>
          </div>
          <div class="deduction-item">
            <label>Employee Rate</label>
            <value>2.25%</value>
          </div>
          <div class="deduction-item">
            <label>PhilHealth Premium</label>
            <value style="font-weight: 600; color: #1f2937;">₱ 247.50</value>
          </div>
        </div>
        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">✓ Calculated from PhilHealth table as per Employee Payroll Profile (Enrolled)</small>
      </div>

      <div class="deduction-card statutory">
        <h4>Pag-IBIG (Home Development Mutual Fund)</h4>
        <div class="deduction-breakdown">
          <div class="deduction-item">
            <label>Gross Pay</label>
            <value>₱ 11,000.00</value>
          </div>
          <div class="deduction-item">
            <label>Contribution Rate</label>
            <value>1.61%</value>
          </div>
          <div class="deduction-item">
            <label>Pag-IBIG Contribution</label>
            <value style="font-weight: 600; color: #1f2937;">₱ 177.10</value>
          </div>
          <div class="deduction-item">
            <label>Early Housing Loan</label>
            <value style="font-weight: 600; color: #1f2937;">₱ 1,476.10</value>
          </div>
        </div>
        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">✓ Calculated from Pag-IBIG table as per Employee Payroll Profile (Enrolled with housing loan)</small>
      </div>

      <div style="background: #dbeafe; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
        <strong style="color: #1e40af;">Total Statutory Deductions: ₱ 2,850.00</strong>
      </div>
    </div>

    <!-- Other Deductions -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 14px; font-weight: 600;">💳 Other Deductions (Manual - Require Approval)</h4>

      <div class="deduction-card other">
        <h4>HMO (Health Maintenance Organization)</h4>
        <div class="deduction-breakdown">
          <div class="deduction-item">
            <label>HMO Plan</label>
            <value>Gold Plan</value>
          </div>
          <div class="deduction-item">
            <label>Monthly Premium</label>
            <value style="font-weight: 600; color: #1f2937;">₱ 0.00</value>
          </div>
          <div class="deduction-item">
            <label>Status</label>
            <value><span class="badge badge-auto">Auto-Deducted</span></value>
          </div>
        </div>
        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">✓ From Employee Payroll Profile - HMO enrollment</small>
      </div>

      <div class="deduction-card other">
        <h4>Employee Loan Deduction</h4>
        <div class="deduction-breakdown">
          <div class="deduction-item">
            <label>Loan Type</label>
            <value>Emergency Loan</value>
          </div>
          <div class="deduction-item">
            <label>Loan Balance</label>
            <value>₱ 5,000.00</value>
          </div>
          <div class="deduction-item">
            <label>Monthly Amortization</label>
            <value style="font-weight: 600; color: #1f2937;">₱ 500.00</value>
          </div>
          <div class="deduction-item">
            <label>Status</label>
            <value><span class="badge badge-approved">Approved</span></value>
          </div>
        </div>
        <small style="color: #6b7280; display: block; margin-top: 0.5rem;">✓ Company loan deduction - Manual entry with approval</small>
      </div>

      <div style="background: #fef3c7; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
        <strong style="color: #92400e;">Total Other Deductions: ₱ 500.00</strong>
      </div>
    </div>

    <!-- Summary Calculation -->
    <div style="background: #fee2e2; padding: 1.5rem; border-radius: 4px; border-left: 4px solid #ef4444;">
      <h4 style="margin: 0 0 1rem 0; color: #991b1b;">⚠️ Deduction Summary & Validation</h4>
      <div class="deduction-breakdown">
        <div class="deduction-item" style="padding-bottom: 1rem; border-bottom: 2px solid #fecaca;">
          <label style="color: #991b1b; font-weight: 600;">Gross Pay</label>
          <value style="color: #991b1b; font-weight: 600;">₱ 11,000.00</value>
        </div>
        <div class="deduction-item">
          <label>Statutory Deductions</label>
          <value>-₱ 2,850.00</value>
        </div>
        <div class="deduction-item">
          <label>Other Deductions</label>
          <value>-₱ 500.00</value>
        </div>
        <div class="deduction-item" style="padding-top: 1rem; border-top: 2px solid #fecaca;">
          <label style="color: #991b1b; font-weight: 600;">NET PAY</label>
          <value style="color: #991b1b; font-weight: 600;">₱ 7,650.00</value>
        </div>
      </div>
      <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #fecaca;">
        <small style="color: #991b1b;">
          <strong>✓ Validation Passed:</strong> Total deductions (₱3,350.00) do not exceed gross pay (₱11,000.00). Safe to process payroll.
        </small>
      </div>
    </div>
  </div>

  <!-- Add Manual Deduction Form -->
  <div class="section">
    <h3 class="section-header">Add Manual Deduction (Requires Approval)</h3>
    
    <p class="subtitle">Use this form to add manual deductions such as absences, penalties, or voluntary deductions.</p>

    <form method="POST" action="">
      <div class="form-section">
        <h4>Deduction Details</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Employee ID <span style="color: #ef4444;">*</span></label>
            <select name="employee_id" required>
              <option value="">-- Select Employee --</option>
              <option value="EMP-001" selected>EMP-001 - John Doe</option>
              <option value="EMP-002">EMP-002 - Jane Smith</option>
              <option value="EMP-003">EMP-003 - Michael Johnson</option>
            </select>
          </div>
          <div class="form-group">
            <label>Deduction Type <span style="color: #ef4444;">*</span></label>
            <select name="deduction_type" required>
              <option value="">-- Select Type --</option>
              <option value="absence">Absence/Undertime</option>
              <option value="penalty">Penalty/Disciplinary</option>
              <option value="voluntary">Voluntary Deduction</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Deduction Description <span style="color: #ef4444;">*</span></label>
            <input type="text" name="description" placeholder="e.g., Absence on Jan 15, Violation penalty..." required>
          </div>
          <div class="form-group">
            <label>Amount <span style="color: #ef4444;">*</span></label>
            <input type="number" name="amount" step="0.01" min="0" placeholder="0.00" required>
            <small>Amount to be deducted from gross pay</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Remarks/Justification</label>
            <textarea name="remarks" placeholder="Provide details or justification for this deduction..." style="min-height: 80px; font-family: inherit;"></textarea>
          </div>
        </div>
      </div>

      <div class="alert alert-warning">
        <strong>⚠️ Important:</strong> Manual deductions require approval before payroll processing. Ensure the amount does not cause total deductions to exceed employee's gross pay.
      </div>

      <div class="btn-group">
        <button type="submit" class="btn btn-primary">Submit for Approval</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
      </div>
    </form>
  </div>

  <!-- Important Rules & Information -->
  <div class="section">
    <h3 class="section-header">Deduction Rules & Information</h3>
    
    <div class="alert alert-info">
      <strong>ℹ️ Statutory Deductions (Automatic):</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>Withholding Tax:</strong> Applied based on BIR withholding tax table from Setup & Configuration</li>
        <li><strong>SSS:</strong> Applied based on SSS contribution table if employee is enrolled (per Employee Payroll Profile)</li>
        <li><strong>PhilHealth:</strong> Applied based on PhilHealth premium table if employee is enrolled</li>
        <li><strong>Pag-IBIG:</strong> Applied based on Pag-IBIG contribution table if employee is enrolled, includes early housing loan if applicable</li>
      </ul>
    </div>

    <div class="alert alert-info">
      <strong>ℹ️ Other Deductions (Require Approval):</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>HMO:</strong> Auto-deducted if employee has HMO enrollment (from Employee Payroll Profile)</li>
        <li><strong>Loans:</strong> Based on approved employee loan records with monthly amortization schedule</li>
        <li><strong>Absences/Undertime:</strong> Calculated based on missing hours and hourly rate</li>
        <li><strong>Penalties:</strong> Manual entry required with approval for disciplinary deductions</li>
      </ul>
    </div>

    <div class="alert alert-warning">
      <strong>⚠️ Critical Rules:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>Deduction Limit:</strong> Total deductions (statutory + other) can NEVER exceed gross pay. System will prevent overage.</li>
        <li><strong>Automatic Calculation:</strong> Statutory deductions automatically follow setup tables - no manual override allowed.</li>
        <li><strong>Manual Entry Approval:</strong> All manual deduction entries require supervisor/admin approval before inclusion in payroll.</li>
        <li><strong>Setup Table Updates:</strong> Changes to statutory tables in Setup & Configuration automatically apply to next payroll run.</li>
        <li><strong>Employee Profile Sync:</strong> Enrollment status changes in Employee Payroll Profile immediately affect deduction eligibility.</li>
      </ul>
    </div>
  </div>

</div>
