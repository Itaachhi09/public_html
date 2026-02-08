<?php
/**
 * Employee Payroll Profile Module
 * Define payroll eligibility and profile details per employee
 */
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
</style>

<div class="profile-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Employee Payroll Profile</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Define payroll eligibility and profile details for each employee. Employees without a payroll profile are excluded from payroll processing.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Note:</strong> Salary values are read-only and sourced from Compensation module. Only payroll-specific settings can be modified here.
    </div>
  </div>

  <!-- Search and Filter -->
  <div class="section">
    <form method="GET" action="">
      <div class="search-box">
        <input type="text" name="search" placeholder="Search by Employee ID or Name..." value="">
        <button type="submit">Search</button>
      </div>
    </form>
  </div>

  <!-- Employees Table -->
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
          <tr>
            <td>EMP-001</td>
            <td>John Doe</td>
            <td>Per Duty</td>
            <td><span class="status-badge status-active">Active</span></td>
            <td><span class="status-badge status-active">Configured</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="employee_id" value="EMP-001">
                <button type="submit" class="btn btn-secondary">View/Edit</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-002</td>
            <td>Jane Smith</td>
            <td>Per Shift</td>
            <td><span class="status-badge status-active">Active</span></td>
            <td><span class="status-badge status-active">Configured</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="employee_id" value="EMP-002">
                <button type="submit" class="btn btn-secondary">View/Edit</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-003</td>
            <td>Michael Johnson</td>
            <td>---</td>
            <td><span class="status-badge status-inactive">Inactive</span></td>
            <td><span class="status-badge status-no-profile">No Profile</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="employee_id" value="EMP-003">
                <button type="submit" class="btn btn-primary">Create Profile</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-004</td>
            <td>Sarah Williams</td>
            <td>Per Duty</td>
            <td><span class="status-badge status-active">Active</span></td>
            <td><span class="status-badge status-active">Configured</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="employee_id" value="EMP-004">
                <button type="submit" class="btn btn-secondary">View/Edit</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-005</td>
            <td>Robert Brown</td>
            <td>---</td>
            <td><span class="status-badge status-inactive">Inactive</span></td>
            <td><span class="status-badge status-no-profile">No Profile</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="employee_id" value="EMP-005">
                <button type="submit" class="btn btn-primary">Create Profile</button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Profile Form -->
  <div class="section">
    <h3 class="section-header">Payroll Profile Details</h3>

    <form method="POST" action="">
      <!-- Employee Information (Read-Only) -->
      <div class="form-section">
        <h4>Employee Information</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Employee ID</label>
            <input type="text" value="EMP-001" disabled>
          </div>
          <div class="form-group">
            <label>Employee Name</label>
            <input type="text" value="John Doe" disabled>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Department</label>
            <input type="text" value="Human Resources" disabled>
          </div>
          <div class="form-group">
            <label>Job Title</label>
            <input type="text" value="HR Manager" disabled>
          </div>
        </div>
      </div>

      <!-- Payroll Configuration -->
      <div class="form-section">
        <h4>Payroll Configuration</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Pay Type <span style="color: #ef4444;">*</span></label>
            <select name="pay_type" required>
              <option value="">-- Select Pay Type --</option>
              <option value="per_duty" selected>Per Duty</option>
              <option value="per_shift">Per Shift</option>
            </select>
            <small>Determines how employee salary is calculated and paid</small>
          </div>
          <div class="form-group">
            <label>Payroll Status <span style="color: #ef4444;">*</span></label>
            <select name="payroll_status" required>
              <option value="">-- Select Status --</option>
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <small>Active employees are included in payroll processing</small>
          </div>
        </div>
      </div>

      <!-- Salary Source (Read-Only from Compensation) -->
      <div class="form-section">
        <h4>Salary Information (Read-Only from Compensation Module)</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Basic Salary</label>
            <input type="number" value="50000.00" step="0.01" disabled>
            <small>Read-only from Compensation module</small>
          </div>
          <div class="form-group">
            <label>Total Allowances</label>
            <input type="number" value="5000.00" step="0.01" disabled>
            <small>Read-only from Compensation module</small>
          </div>
        </div>
        <div class="form-row full">
          <div class="form-group">
            <label>Gross Monthly Salary</label>
            <input type="number" value="55000.00" step="0.01" disabled>
            <small>Automatic calculation: Basic + Allowances</small>
          </div>
        </div>
      </div>

      <!-- Tax Status -->
      <div class="form-section">
        <h4>Tax Status</h4>
        <div class="form-row full">
          <div class="form-group">
            <label>Tax Exemption Status</label>
            <select name="tax_status" required>
              <option value="">-- Select Tax Status --</option>
              <option value="taxable" selected>Taxable</option>
              <option value="exempt">Tax Exempt</option>
            </select>
            <small>Determines if employee is subject to withholding tax</small>
          </div>
        </div>
      </div>

      <!-- Government Contributions -->
      <div class="form-section">
        <h4>Government Contributions & Benefits</h4>
        <div class="form-row">
          <div class="form-group">
            <label>SSS Status</label>
            <select name="sss_status">
              <option value="">-- Select --</option>
              <option value="enrolled" selected>Enrolled</option>
              <option value="not_enrolled">Not Enrolled</option>
              <option value="exempt">Exempt</option>
            </select>
            <small>Social Security System enrollment status</small>
          </div>
          <div class="form-group">
            <label>SSS Number</label>
            <input type="text" name="sss_number" placeholder="e.g., XX-XXXXXXX-X" value="">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>PhilHealth Status</label>
            <select name="philhealth_status">
              <option value="">-- Select --</option>
              <option value="enrolled" selected>Enrolled</option>
              <option value="not_enrolled">Not Enrolled</option>
              <option value="exempt">Exempt</option>
            </select>
            <small>Philippine Health Insurance Corporation status</small>
          </div>
          <div class="form-group">
            <label>PhilHealth Number</label>
            <input type="text" name="philhealth_number" placeholder="e.g., XX-XXXXXXXXXX-X" value="">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Pag-IBIG Status</label>
            <select name="pagibig_status">
              <option value="">-- Select --</option>
              <option value="enrolled" selected>Enrolled</option>
              <option value="not_enrolled">Not Enrolled</option>
              <option value="exempt">Exempt</option>
            </select>
            <small>Home Development Mutual Fund status</small>
          </div>
          <div class="form-group">
            <label>Pag-IBIG Member Number</label>
            <input type="text" name="pagibig_number" placeholder="e.g., XXXX-XXXX-XXXX" value="">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>TIN (Tax Identification Number)</label>
            <input type="text" name="tin" placeholder="e.g., XXX-XXX-XXX-XXX" value="">
            <small>Bureau of Internal Revenue TIN for tax purposes</small>
          </div>
        </div>
      </div>

      <!-- Bank Account Details -->
      <div class="form-section">
        <h4>Bank Account & Disbursement</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Bank Name <span style="color: #ef4444;">*</span></label>
            <select name="bank_name" required>
              <option value="">-- Select Bank --</option>
              <option value="bdo" selected>BDO</option>
              <option value="bpi">BPI</option>
              <option value="metrobank">Metrobank</option>
              <option value="pnb">PNB</option>
              <option value="ucpb">UCPB</option>
            </select>
          </div>
          <div class="form-group">
            <label>Account Type <span style="color: #ef4444;">*</span></label>
            <select name="account_type" required>
              <option value="">-- Select Type --</option>
              <option value="savings" selected>Savings</option>
              <option value="checking">Checking</option>
            </select>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank Account Number <span style="color: #ef4444;">*</span></label>
            <input type="text" name="bank_account_number" placeholder="Bank account number for salary deposits" required value="">
            <small>Account must be in employee's name</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Account Holder Name</label>
            <input type="text" name="account_holder_name" value="John Doe" disabled>
            <small>Read-only verification field</small>
          </div>
          <div class="form-group">
            <label>Account Status</label>
            <select name="account_status">
              <option value="">-- Select --</option>
              <option value="active" selected>Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <small>Inactive accounts will not receive salary deposits</small>
          </div>
        </div>
      </div>

      <!-- Alerts -->
      <div class="alert alert-warning">
        <strong>⚠️ Important Rules:</strong>
        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
          <li>Salary values are read-only and linked to Compensation module</li>
          <li>Employees without a payroll profile are excluded from payroll runs</li>
          <li>Changes to this profile affect future payroll periods only</li>
          <li>Bank account details must be verified before payroll processing</li>
        </ul>
      </div>

      <!-- Action Buttons -->
      <div class="btn-group">
        <button type="submit" class="btn btn-primary">Save Profile</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="employee_id" value="EMP-001">
          <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this payroll profile? The employee will be excluded from payroll.');">Delete Profile</button>
        </form>
      </div>
    </form>
  </div>

</div>
