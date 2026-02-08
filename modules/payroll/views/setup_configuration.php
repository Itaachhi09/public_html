<?php
/**
 * Payroll Setup & Configuration Module
 * Manages payroll rules, calendars, components, and settings
 */
?>

<style>
  .setup-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }

  .setup-section {
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

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
  }

  .form-row.full {
    grid-template-columns: 1fr;
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

  .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
  }

  button {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
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

  .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 13px;
  }

  .add-row-btn {
    display: inline-block;
    margin-top: 1rem;
  }

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
  }

  .badge-success {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-warning {
    background: #fef3c7;
    color: #92400e;
  }

  .info-box {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 14px;
    color: #1e40af;
  }

  .tabs {
    display: flex;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 2rem;
  }

  .tab {
    padding: 1rem 1.5rem;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.3s ease;
  }

  .tab:hover {
    color: #3b82f6;
  }

  .tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
  }

  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  .component-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 1rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .component-info h4 {
    margin: 0 0 0.25rem 0;
    color: #1f2937;
    font-size: 14px;
  }

  .component-info p {
    margin: 0;
    color: #6b7280;
    font-size: 13px;
  }

  .component-actions {
    display: flex;
    gap: 0.5rem;
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
</style>

<div class="setup-container">
  <!-- Header -->
  <div class="setup-section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Payroll Setup & Configuration</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Configure fixed payroll rules used by all payroll runs. Changes affect future payroll runs only.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #fef3c7; border-radius: 4px; color: #92400e; font-size: 13px;">
      <strong>⚠️ Admin Only:</strong> Only administrators can modify payroll setup and configuration.
    </div>
  </div>

  <!-- Tabs Navigation -->
  <div class="setup-section" style="padding: 0; background: transparent; box-shadow: none;">
    <div class="tabs">
      <div class="tab active" onclick="switchTab(event, 'pay-frequency')">
        <i class='bx bx-calendar'></i> Pay Frequency
      </div>
      <div class="tab" onclick="switchTab(event, 'payroll-calendar')">
        <i class='bx bx-calendar-event'></i> Payroll Calendar
      </div>
      <div class="tab" onclick="switchTab(event, 'salary-components')">
        <i class='bx bx-list-check'></i> Salary Components
      </div>
      <div class="tab" onclick="switchTab(event, 'statutory-tables')">
        <i class='bx bx-table'></i> Statutory Tables
      </div>
      <div class="tab" onclick="switchTab(event, 'bank-settings')">
        <i class='bx bx-building'></i> Bank Settings
      </div>
    </div>
  </div>

  <!-- TAB 1: PAY FREQUENCY -->
  <div id="pay-frequency" class="tab-content active">
    <div class="setup-section">
      <h3 class="section-header">Configure Pay Frequency</h3>
      
      <div class="info-box">
        <strong>Current Setup:</strong> Semi-monthly payroll (15th and 30th cut-off dates, 7th and 22nd pay dates)
      </div>

      <form method="POST" action="">
        <div class="form-row">
          <div class="form-group">
            <label>Cutoff Date 1 (of month)</label>
            <input type="number" name="cutoff_date_1" value="15" min="1" max="31" readonly>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">First cutoff date</small>
          </div>
          <div class="form-group">
            <label>Pay Date 1 (of following month)</label>
            <input type="number" name="pay_date_1" value="7" min="1" max="31" readonly>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Associated pay date</small>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Cutoff Date 2 (of month)</label>
            <input type="number" name="cutoff_date_2" value="30" min="1" max="31" readonly>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Second cutoff date</small>
          </div>
          <div class="form-group">
            <label>Pay Date 2 (of following month)</label>
            <input type="number" name="pay_date_2" value="22" min="1" max="31" readonly>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Associated pay date</small>
          </div>
        </div>

        <div class="alert alert-info">
          <strong>ℹ️ Note:</strong> Pay frequency is currently locked at semi-monthly (twice per month). To change frequency, contact system administrator.
        </div>

        <div class="btn-group">
          <button type="button" onclick="alert('Pay frequency configuration locked for current system.')" class="btn-primary">View Details</button>
        </div>
      </form>
    </div>
  </div>

  <!-- TAB 2: PAYROLL CALENDAR -->
  <div id="payroll-calendar" class="tab-content">
    <div class="setup-section">
      <h3 class="section-header">Configure Payroll Calendar</h3>

      <form method="POST" action="">
        <div class="form-row full">
          <div class="form-group">
            <label>Payroll Year</label>
            <input type="number" name="payroll_year" value="<?php echo date('Y'); ?>" min="2024">
          </div>
        </div>

        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Period</th>
                <th>Cutoff Start</th>
                <th>Cutoff End</th>
                <th>Pay Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Period 1</td>
                <td>
                  <input type="date" name="cutoff_start_1" value="<?php echo date('Y-m-01'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="cutoff_end_1" value="<?php echo date('Y-m-15'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="pay_date_1" value="<?php echo date('Y-m-07', strtotime('first day of next month')); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td><span class="badge badge-success">Active</span></td>
              </tr>
              <tr>
                <td>Period 2</td>
                <td>
                  <input type="date" name="cutoff_start_2" value="<?php echo date('Y-m-16'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="cutoff_end_2" value="<?php echo date('Y-m-t'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="pay_date_2" value="<?php echo date('Y-m-22', strtotime('first day of next month')); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td><span class="badge badge-success">Active</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="btn-group">
          <button type="submit" class="btn-primary">Save Calendar</button>
          <button type="reset" class="btn-secondary">Reset</button>
        </div>
      </form>
    </div>
  </div>

  <!-- TAB 3: SALARY COMPONENTS -->
  <div id="salary-components" class="tab-content">
    <div class="setup-section">
      <h3 class="section-header">Define Salary Components</h3>

      <!-- EARNINGS -->
      <div style="margin-bottom: 2rem;">
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">📈 Earnings</h4>
        
        <div class="component-card">
          <div class="component-info">
            <h4>Basic Pay</h4>
            <p>Base monthly salary for all employees</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit Basic Pay component')">Edit</button>
            <button type="button" class="btn-danger btn-sm" onclick="alert('Cannot delete basic pay')" disabled>Delete</button>
          </div>
        </div>

        <div class="component-card">
          <div class="component-info">
            <h4>Incentives</h4>
            <p>Performance-based bonus and incentives</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit Incentives component')">Edit</button>
            <button type="button" class="btn-danger btn-sm" onclick="alert('Remove Incentives component')">Delete</button>
          </div>
        </div>

        <div class="component-card">
          <div class="component-info">
            <h4>Allowances</h4>
            <p>Housing, meal, transportation, and other allowances</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit Allowances component')">Edit</button>
            <button type="button" class="btn-danger btn-sm" onclick="alert('Remove Allowances component')">Delete</button>
          </div>
        </div>

        <button type="button" class="btn-primary add-row-btn" onclick="alert('Add new earning component')">
          <i class='bx bx-plus'></i> Add Earning Component
        </button>
      </div>

      <!-- DEDUCTIONS -->
      <div>
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">📉 Deductions</h4>
        
        <div class="component-card">
          <div class="component-info">
            <h4>Income Tax (BIR)</h4>
            <p>Monthly withholding tax based on BIR tables</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit Income Tax component')">Edit</button>
          </div>
        </div>

        <div class="component-card">
          <div class="component-info">
            <h4>SSS Contribution</h4>
            <p>Social Security System employee contribution</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit SSS component')">Edit</button>
          </div>
        </div>

        <div class="component-card">
          <div class="component-info">
            <h4>PhilHealth Premium</h4>
            <p>Philippine Health Insurance Corporation premium</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit PhilHealth component')">Edit</button>
          </div>
        </div>

        <div class="component-card">
          <div class="component-info">
            <h4>Pag-IBIG Contribution</h4>
            <p>Home Development Mutual Fund contribution</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit Pag-IBIG component')">Edit</button>
          </div>
        </div>

        <div class="component-card">
          <div class="component-info">
            <h4>Loan Deduction</h4>
            <p>Employee loan amortization and deductions</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit Loan Deduction component')">Edit</button>
          </div>
        </div>

        <div class="component-card">
          <div class="component-info">
            <h4>HMO Premium</h4>
            <p>Health Maintenance Organization premium deduction</p>
          </div>
          <div class="component-actions">
            <button type="button" class="btn-secondary btn-sm" onclick="alert('Edit HMO component')">Edit</button>
          </div>
        </div>

        <button type="button" class="btn-primary add-row-btn" onclick="alert('Add new deduction component')">
          <i class='bx bx-plus'></i> Add Deduction Component
        </button>
      </div>
    </div>
  </div>

  <!-- TAB 4: STATUTORY TABLES -->
  <div id="statutory-tables" class="tab-content">
    <div class="setup-section">
      <h3 class="section-header">Maintain Statutory Tables</h3>

      <!-- SSS TABLE -->
      <div style="margin-bottom: 2rem;">
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">SSS Contribution Table (<?php echo date('Y'); ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Monthly Contribution</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="number" value="0" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="2250" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="135.00" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><button type="button" class="btn-secondary btn-sm">Edit</button></td>
              </tr>
              <tr>
                <td><input type="number" value="2250.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="2749.99" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="157.50" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><button type="button" class="btn-secondary btn-sm">Edit</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn-primary add-row-btn">Add SSS Range</button>
      </div>

      <!-- PHILHEALTH TABLE -->
      <div style="margin-bottom: 2rem;">
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">PhilHealth Premium Table (<?php echo date('Y'); ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Monthly Premium</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="number" value="0" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="8999.99" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="500.00" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><button type="button" class="btn-secondary btn-sm">Edit</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn-primary add-row-btn">Add PhilHealth Range</button>
      </div>

      <!-- PAG-IBIG TABLE -->
      <div style="margin-bottom: 2rem;">
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">Pag-IBIG Contribution Table (<?php echo date('Y'); ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Monthly Contribution</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="number" value="0" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="1500" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="100.00" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><button type="button" class="btn-secondary btn-sm">Edit</button></td>
              </tr>
              <tr>
                <td><input type="number" value="1500.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="4666.67" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="150.00" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><button type="button" class="btn-secondary btn-sm">Edit</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn-primary add-row-btn">Add Pag-IBIG Range</button>
      </div>

      <!-- BIR WITHHOLDING TAX TABLE -->
      <div>
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">BIR Withholding Tax Table (<?php echo date('Y'); ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Annual Salary (Php)</th>
                <th>Tax Rate (%)</th>
                <th>Deductible Amount</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="number" value="0" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="0" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="0" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><button type="button" class="btn-secondary btn-sm">Edit</button></td>
              </tr>
              <tr>
                <td><input type="number" value="250000" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="5" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><input type="number" value="0" step="0.01" style="width: 100%; padding: 0.5rem;"></td>
                <td><button type="button" class="btn-secondary btn-sm">Edit</button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn-primary add-row-btn">Add Tax Bracket</button>
      </div>
    </div>
  </div>

  <!-- TAB 5: BANK SETTINGS -->
  <div id="bank-settings" class="tab-content">
    <div class="setup-section">
      <h3 class="section-header">Configure Bank Settings</h3>

      <form method="POST" action="">
        <div class="form-row full">
          <div class="form-group">
            <label>Payroll Bank Account</label>
            <input type="text" name="payroll_bank_account" placeholder="e.g., 1234567890" required>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Bank account number used for payroll disbursement</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank Name</label>
            <input type="text" name="bank_name" placeholder="e.g., BDO, BPI, Metrobank" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Bank Code</label>
            <input type="text" name="bank_code" placeholder="e.g., 006 for BDO" required>
          </div>
          <div class="form-group">
            <label>Bank Branch Code</label>
            <input type="text" name="branch_code" placeholder="Branch code">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank File Format</label>
            <select name="bank_file_format" required>
              <option value="">-- Select File Format --</option>
              <option value="txt">Plain Text (TXT)</option>
              <option value="csv">Comma Separated (CSV)</option>
              <option value="xml">XML Format</option>
              <option value="custom">Custom Bank Format</option>
            </select>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Format required by your bank for salary transfer files</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Batch Reference Numbering Pattern</label>
            <input type="text" name="batch_reference_pattern" placeholder="e.g., PAYROLL-YYYY-MM-###" required>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Pattern for batch reference numbers. Use: YYYY (year), MM (month), ### (sequence)</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Next Batch Reference Number</label>
            <input type="number" name="next_batch_number" value="1" min="1">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank Contact Email</label>
            <input type="email" name="bank_contact_email" placeholder="email@bank.com">
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Contact email for bank inquiries</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank Contact Person</label>
            <input type="text" name="bank_contact_person" placeholder="Name of bank contact">
          </div>
        </div>

        <div class="alert alert-warning">
          <strong>⚠️ Important:</strong> Bank settings are critical for payroll processing. Ensure accuracy before saving. Changes will affect future salary transfers.
        </div>

        <div class="btn-group">
          <button type="submit" class="btn-primary">Save Bank Settings</button>
          <button type="reset" class="btn-secondary">Reset</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
  function switchTab(event, tabId) {
    event.preventDefault();

    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
      content.classList.remove('active');
    });

    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
      tab.classList.remove('active');
    });

    // Show selected tab content
    document.getElementById(tabId).classList.add('active');

    // Add active class to clicked tab
    event.target.closest('.tab').classList.add('active');
  }
</script>
