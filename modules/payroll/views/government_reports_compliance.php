<?php
/**
 * Government Reports and Compliance Module
 * Generate statutory reports for government agencies (SSS, PhilHealth, Pag-IBIG, BIR)
 */
?>

<style>
  .compliance-container {
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

  .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
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

  .form-row.three-col {
    grid-template-columns: 1fr 1fr 1fr;
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

  .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
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

  .badge-submitted {
    background: #dbeafe;
    color: #1e40af;
  }

  .badge-verified {
    background: #d1f0c5;
    color: #166534;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
  }

  .report-preview {
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 1rem;
    font-size: 12px;
    line-height: 1.6;
  }

  .report-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #d1d5db;
  }

  .report-header h3 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 16px;
    font-weight: 700;
  }

  .report-header p {
    margin: 0.25rem 0;
    color: #6b7280;
  }

  .report-details {
    margin-bottom: 1.5rem;
  }

  .detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f3f4f6;
  }

  .detail-row label {
    color: #6b7280;
    font-weight: 500;
  }

  .detail-row value {
    color: #1f2937;
    font-weight: 500;
  }

  .report-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
  }

  .report-table thead {
    background: #f3f4f6;
  }

  .report-table th {
    padding: 0.5rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #d1d5db;
    font-size: 11px;
  }

  .report-table td {
    padding: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
    font-size: 11px;
  }

  .report-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    background: #f9fafb;
    padding: 1rem;
    border-radius: 4px;
  }

  .summary-item {
    text-align: center;
  }

  .summary-item label {
    display: block;
    color: #6b7280;
    font-size: 11px;
    font-weight: 500;
    margin-bottom: 0.25rem;
  }

  .summary-item value {
    display: block;
    color: #1f2937;
    font-size: 14px;
    font-weight: 700;
  }

  .tab-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
    overflow-x: auto;
  }

  .tab {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
  }

  .tab:hover {
    color: #374151;
  }

  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  .report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .report-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .report-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  }

  .report-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .report-card p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .report-card .due-date {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    color: #1e40af;
    font-size: 12px;
    font-weight: 500;
  }

  @media print {
    .section {
      page-break-inside: avoid;
    }
  }
</style>

<div class="compliance-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Government Reports & Compliance</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Generate statutory reports for government agencies. Supports SSS, PhilHealth, Pag-IBIG, and BIR (1601-C, 2316, Alphalist). Export as CSV, PDF, or printable HTML.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Features:</strong> Multi-format export (CSV/PDF/HTML), deadline tracking, submission history, compliance verification, and audit trails.
    </div>
  </div>

  <!-- Available Reports Summary -->
  <div class="section">
    <h3 class="section-header">📊 Available Reports & Deadlines</h3>

    <div class="report-grid">
      <div class="report-card">
        <h4>🏢 SSS Report</h4>
        <p>Social Security System contributions and membership</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Employee ID, monthly contributions, salary</p>
        <div class="report-card due-date">Monthly by 10th working day</div>
      </div>

      <div class="report-card">
        <h4>🏥 PhilHealth Report</h4>
        <p>Philippine Health Insurance contributions</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Member data, premium contributions, salaries</p>
        <div class="report-card due-date">Monthly by 10th working day</div>
      </div>

      <div class="report-card">
        <h4>🏦 Pag-IBIG Report</h4>
        <p>Pag-IBIG Fund contributions and loan tracking</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Contribution records, loan details, salary</p>
        <div class="report-card due-date">Monthly by 10th working day</div>
      </div>

      <div class="report-card">
        <h4>📋 BIR 1601-C</h4>
        <p>Annual Withholding Tax Report</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Yearly tax withheld by income class</p>
        <div class="report-card due-date">Annually by January 31</div>
      </div>

      <div class="report-card">
        <h4>📄 BIR 2316</h4>
        <p>Certificate of Withholding Tax</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Individual tax certificates for employees</p>
        <div class="report-card due-date">Annually by January 31</div>
      </div>

      <div class="report-card">
        <h4>📑 BIR Alphalist</h4>
        <p>BIR registered employees with tax data</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Complete payroll roster with TIN and tax info</p>
        <div class="report-card due-date">Annually by January 31</div>
      </div>
    </div>
  </div>

  <!-- Report Generation Center -->
  <div class="section">
    <h3 class="section-header">🔄 Generate Reports</h3>

    <div class="tab-container">
      <button class="tab active" onclick="switchTab(event, 'sss')">SSS</button>
      <button class="tab" onclick="switchTab(event, 'philhealth')">PhilHealth</button>
      <button class="tab" onclick="switchTab(event, 'pagibig')">Pag-IBIG</button>
      <button class="tab" onclick="switchTab(event, 'bir1601')">BIR 1601-C</button>
      <button class="tab" onclick="switchTab(event, 'bir2316')">BIR 2316</button>
      <button class="tab" onclick="switchTab(event, 'alphalist')">Alphalist</button>
    </div>

    <!-- SSS Tab -->
    <div id="sss" class="tab-content active">
      <form method="POST" action="">
        <div class="form-section">
          <h4>SSS Report Parameters</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Report Period From <span style="color: #ef4444;">*</span></label>
              <input type="month" name="sss_from" value="2026-02" required>
            </div>
            <div class="form-group">
              <label>Report Period To <span style="color: #ef4444;">*</span></label>
              <input type="month" name="sss_to" value="2026-02" required>
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Employees to Include</label>
              <select name="sss_employees">
                <option value="all" selected>All Active Employees</option>
                <option value="department">Filter by Department</option>
                <option value="custom">Custom Selection</option>
              </select>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          Selected period: February 2026. Total employees: 8. Total contributions: ₱3,250.12
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="preview_sss" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_sss_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_sss_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_sss" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- PhilHealth Tab -->
    <div id="philhealth" class="tab-content">
      <form method="POST" action="">
        <div class="form-section">
          <h4>PhilHealth Report Parameters</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Report Period From <span style="color: #ef4444;">*</span></label>
              <input type="month" name="ph_from" value="2026-02" required>
            </div>
            <div class="form-group">
              <label>Report Period To <span style="color: #ef4444;">*</span></label>
              <input type="month" name="ph_to" value="2026-02" required>
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Employees to Include</label>
              <select name="ph_employees">
                <option value="all" selected>All Active Employees</option>
                <option value="department">Filter by Department</option>
                <option value="custom">Custom Selection</option>
              </select>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          Selected period: February 2026. Total employees: 8. Total contributions: ₱1,980.00
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="preview_ph" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_ph_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_ph_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_ph" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- Pag-IBIG Tab -->
    <div id="pagibig" class="tab-content">
      <form method="POST" action="">
        <div class="form-section">
          <h4>Pag-IBIG Report Parameters</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Report Period From <span style="color: #ef4444;">*</span></label>
              <input type="month" name="pagibig_from" value="2026-02" required>
            </div>
            <div class="form-group">
              <label>Report Period To <span style="color: #ef4444;">*</span></label>
              <input type="month" name="pagibig_to" value="2026-02" required>
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Employees to Include</label>
              <select name="pagibig_employees">
                <option value="all" selected>All Active Employees</option>
                <option value="department">Filter by Department</option>
                <option value="custom">Custom Selection</option>
              </select>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          Selected period: February 2026. Total employees: 8. Total contributions: ₱1,600.00
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="preview_pagibig" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_pagibig_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_pagibig_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_pagibig" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- BIR 1601-C Tab -->
    <div id="bir1601" class="tab-content">
      <form method="POST" action="">
        <div class="form-section">
          <h4>BIR 1601-C Parameters</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Tax Year <span style="color: #ef4444;">*</span></label>
              <input type="number" name="bir1601_year" value="2025" min="2020" max="2030" required>
            </div>
            <div class="form-group">
              <label>Filing Status</label>
              <select name="bir1601_status">
                <option value="original">Original Filing</option>
                <option value="amended">Amended Return</option>
              </select>
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Employees to Include</label>
              <select name="bir1601_employees">
                <option value="all" selected>All Employees (2025)</option>
                <option value="separated">Include Separated Employees</option>
                <option value="custom">Custom Selection</option>
              </select>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          Tax year: 2025. Total employees: 8. Total income reported: ₱528,000.00. Total tax withheld: ₱0.00
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="preview_bir1601" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_bir1601_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_bir1601_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_bir1601" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- BIR 2316 Tab -->
    <div id="bir2316" class="tab-content">
      <form method="POST" action="">
        <div class="form-section">
          <h4>BIR 2316 Certificate Parameters</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Tax Year <span style="color: #ef4444;">*</span></label>
              <input type="number" name="bir2316_year" value="2025" min="2020" max="2030" required>
            </div>
            <div class="form-group">
              <label>Select Employee</label>
              <select name="bir2316_employee">
                <option value="">-- Select Employee --</option>
                <option value="EMP-001">John Doe (EMP-001)</option>
                <option value="EMP-002">Jane Smith (EMP-002)</option>
                <option value="EMP-003">Michael Johnson (EMP-003)</option>
                <option value="EMP-004">Sarah Williams (EMP-004)</option>
                <option value="EMP-005">Robert Brown (EMP-005)</option>
                <option value="EMP-006">Emily Davis (EMP-006)</option>
                <option value="EMP-007">David Martinez (EMP-007)</option>
                <option value="EMP-008">Jessica Wilson (EMP-008)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          Generate individual 2316 certificates for each employee. Can be exported as PDF for distribution.
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="preview_bir2316" class="btn btn-secondary">Preview Certificate</button>
          <button type="submit" name="action" value="export_bir2316_csv" class="btn btn-primary">Export All as CSV</button>
          <button type="submit" name="action" value="export_bir2316_pdf" class="btn btn-primary">Export All as PDF</button>
          <button type="submit" name="action" value="print_bir2316" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- Alphalist Tab -->
    <div id="alphalist" class="tab-content">
      <form method="POST" action="">
        <div class="form-section">
          <h4>Alphalist Parameters</h4>
          <div class="form-row">
            <div class="form-group">
              <label>Tax Year <span style="color: #ef4444;">*</span></label>
              <input type="number" name="alphalist_year" value="2025" min="2020" max="2030" required>
            </div>
            <div class="form-group">
              <label>BIR Office</label>
              <input type="text" name="alphalist_bir_office" value="BIR District Office - Makati" readonly style="background: #f3f4f6;">
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label>Employees to Include</label>
              <select name="alphalist_employees">
                <option value="all" selected>All Employees (2025)</option>
                <option value="separated">Include Separated Employees</option>
                <option value="department">By Department</option>
              </select>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          Alphalist for year 2025. Total employees: 8. Format: TXT (BIR Standard)
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="preview_alphalist" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_alphalist_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_alphalist_txt" class="btn btn-primary">Export as TXT (BIR Format)</button>
          <button type="submit" name="action" value="print_alphalist" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Sample Report Preview -->
  <div class="section">
    <h3 class="section-header">👁️ Sample Report - SSS Report (February 2026)</h3>

    <div class="alert alert-info">
      This is a sample preview of the SSS report format. Actual report will include current data from selected period.
    </div>

    <div class="report-preview">
      <div class="report-header">
        <h3>SOCIAL SECURITY SYSTEM (SSS) CONTRIBUTION REPORT</h3>
        <p>Healthcare Hospital Inc. | BIR TIN: 012-345-678</p>
        <p>Period: February 1-29, 2026</p>
      </div>

      <div class="report-details">
        <div class="detail-row">
          <label>Employer Name:</label>
          <value>Healthcare Hospital Inc.</value>
        </div>
        <div class="detail-row">
          <label>Business Address:</label>
          <value>123 Hospital Avenue, Makati City</value>
        </div>
        <div class="detail-row">
          <label>SSS Account Number:</label>
          <value>08-1234567-8</value>
        </div>
        <div class="detail-row">
          <label>Report Period:</label>
          <value>February 2026</value>
        </div>
      </div>

      <table class="report-table">
        <thead>
          <tr>
            <th>Seq #</th>
            <th>SSS ID Number</th>
            <th>Employee Name</th>
            <th>Employee Share</th>
            <th>Employer Share</th>
            <th>Total Contribution</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>04-1234567-8</td>
            <td>John Doe</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱406.24</td>
          </tr>
          <tr>
            <td>2</td>
            <td>04-2345678-9</td>
            <td>Jane Smith</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱406.24</td>
          </tr>
          <tr>
            <td>3</td>
            <td>04-3456789-0</td>
            <td>Michael Johnson</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱406.24</td>
          </tr>
          <tr>
            <td>4</td>
            <td>04-4567890-1</td>
            <td>Sarah Williams</td>
            <td style="text-align: right;">₱182.12</td>
            <td style="text-align: right;">₱182.12</td>
            <td style="text-align: right;">₱364.24</td>
          </tr>
          <tr>
            <td>5</td>
            <td>04-5678901-2</td>
            <td>Robert Brown</td>
            <td style="text-align: right;">₱182.12</td>
            <td style="text-align: right;">₱182.12</td>
            <td style="text-align: right;">₱364.24</td>
          </tr>
          <tr>
            <td>6</td>
            <td>04-6789012-3</td>
            <td>Emily Davis</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱203.12</td>
            <td style="text-align: right;">₱406.24</td>
          </tr>
          <tr>
            <td>7</td>
            <td>04-7890123-4</td>
            <td>David Martinez</td>
            <td style="text-align: right;">₱194.62</td>
            <td style="text-align: right;">₱194.62</td>
            <td style="text-align: right;">₱389.24</td>
          </tr>
          <tr>
            <td>8</td>
            <td>04-8901234-5</td>
            <td>Jessica Wilson</td>
            <td style="text-align: right;">₱219.12</td>
            <td style="text-align: right;">₱219.12</td>
            <td style="text-align: right;">₱438.24</td>
          </tr>
          <tr style="font-weight: 700; background: #f3f4f6;">
            <td colspan="4" style="text-align: right;">TOTAL:</td>
            <td style="text-align: right;">₱1,591.56</td>
            <td style="text-align: right;">₱1,591.56</td>
            <td style="text-align: right;">₱3,183.12</td>
          </tr>
        </tbody>
      </table>

      <div class="report-summary">
        <div class="summary-item">
          <label>Total Employees</label>
          <value>8</value>
        </div>
        <div class="summary-item">
          <label>Total Employee Share</label>
          <value>₱1,591.56</value>
        </div>
        <div class="summary-item">
          <label>Total Employer Share</label>
          <value>₱1,591.56</value>
        </div>
        <div class="summary-item">
          <label>Total Contribution</label>
          <value>₱3,183.12</value>
        </div>
      </div>

      <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #d1d5db; color: #6b7280; font-size: 11px;">
        <p style="margin: 0.25rem 0;"><strong>Certified by:</strong> Juan dela Cruz, Payroll Officer</p>
        <p style="margin: 0.25rem 0;"><strong>Generated:</strong> February 8, 2026 10:30 AM</p>
        <p style="margin: 0.25rem 0;">This is a computer-generated report. Original signatures not required.</p>
      </div>
    </div>
  </div>

  <!-- Submission History -->
  <div class="section">
    <h3 class="section-header">📈 Submission History & Compliance</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Report Type</th>
            <th>Period/Year</th>
            <th>Generated Date</th>
            <th>Submitted Date</th>
            <th>Recipient</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>SSS Report</td>
            <td>January 2026</td>
            <td>February 1, 2026</td>
            <td>February 3, 2026</td>
            <td>SSS</td>
            <td><span class="badge badge-verified">Verified</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="report_id" value="SSS-202601">
                <button type="submit" class="btn btn-secondary btn-sm">Download</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>PhilHealth Report</td>
            <td>January 2026</td>
            <td>February 1, 2026</td>
            <td>February 3, 2026</td>
            <td>PhilHealth</td>
            <td><span class="badge badge-verified">Verified</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="report_id" value="PH-202601">
                <button type="submit" class="btn btn-secondary btn-sm">Download</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>Pag-IBIG Report</td>
            <td>January 2026</td>
            <td>February 1, 2026</td>
            <td>February 3, 2026</td>
            <td>Pag-IBIG</td>
            <td><span class="badge badge-verified">Verified</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="report_id" value="PAGIBIG-202601">
                <button type="submit" class="btn btn-secondary btn-sm">Download</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>BIR 1601-C</td>
            <td>2025</td>
            <td>January 15, 2026</td>
            <td>January 28, 2026</td>
            <td>BIR</td>
            <td><span class="badge badge-verified">Verified</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="report_id" value="BIR1601-2025">
                <button type="submit" class="btn btn-secondary btn-sm">Download</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>BIR 2316 Certificates</td>
            <td>2025</td>
            <td>January 20, 2026</td>
            <td>January 29, 2026</td>
            <td>Employees</td>
            <td><span class="badge badge-verified">Verified</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="report_id" value="BIR2316-2025">
                <button type="submit" class="btn btn-secondary btn-sm">Download</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>BIR Alphalist</td>
            <td>2025</td>
            <td>January 25, 2026</td>
            <td>January 31, 2026</td>
            <td>BIR</td>
            <td><span class="badge badge-submitted">Submitted</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="report_id" value="ALPHALIST-2025">
                <button type="submit" class="btn btn-secondary btn-sm">Download</button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Compliance Deadlines -->
  <div class="section">
    <h3 class="section-header">⏰ Compliance Deadlines & Requirements</h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
        <h5 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 13px;">Monthly Reports (Due: 10th Working Day)</h5>
        <ul style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 12px; line-height: 1.8;">
          <li><strong>SSS Contribution Report:</strong> Employee and employer contributions</li>
          <li><strong>PhilHealth Monthly Report:</strong> Premium contributions and member data</li>
          <li><strong>Pag-IBIG Contribution Report:</strong> Fund contributions and loan tracking</li>
        </ul>
      </div>

      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #f59e0b;">
        <h5 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 13px;">Annual Reports (Due: January 31)</h5>
        <ul style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 12px; line-height: 1.8;">
          <li><strong>BIR 1601-C:</strong> Annual withholding tax summary by income class</li>
          <li><strong>BIR 2316:</strong> Individual withholding tax certificates for all employees</li>
          <li><strong>BIR Alphalist:</strong> Employee roster with TIN and income data</li>
        </ul>
      </div>

      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #22c55e;">
        <h5 style="margin: 0 0 0.75rem 0; color: #1f2937; font-size: 13px;">Required Information</h5>
        <ul style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 12px; line-height: 1.8;">
          <li>Valid TIN (Tax Identification Number) for company</li>
          <li>SSS Account Number</li>
          <li>PhilHealth Account Number</li>
          <li>Pag-IBIG Account Number</li>
          <li>BIR Registration with correct classification</li>
        </ul>
      </div>
    </div>
  </div>

  <!-- System Information -->
  <div class="section">
    <h3 class="section-header">ℹ️ System Configuration & Rules</h3>

    <div class="alert alert-success">
      <strong>✓ Company Information on File:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li>Company: Healthcare Hospital Inc.</li>
        <li>BIR TIN: 012-345-678</li>
        <li>SSS Account: 08-1234567-8</li>
        <li>PhilHealth PIN: 1-001-2345678-00</li>
        <li>Pag-IBIG Account: 123-456789-0</li>
      </ul>
    </div>

    <div class="alert alert-info">
      <strong>ℹ️ Report Export Formats:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>CSV:</strong> For spreadsheet editing and manual review</li>
        <li><strong>PDF:</strong> For archival, email distribution, and formal submission</li>
        <li><strong>HTML:</strong> For screen viewing and browser printing</li>
        <li><strong>TXT:</strong> For electronic filing with government agencies</li>
      </ul>
    </div>

    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ef4444; margin-bottom: 0;">
      <h4 style="margin: 0 0 1rem 0; color: #1f2937;">Critical Compliance Rules</h4>
      <ul style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 13px; line-height: 1.8;">
        <li><strong>Accuracy Required:</strong> All reports must match bank deposits and payroll records</li>
        <li><strong>Timely Filing:</strong> Monthly reports due 10th working day; annual reports by January 31</li>
        <li><strong>Employee Data Verification:</strong> All SSS/PhilHealth/Pag-IBIG numbers must be current and valid</li>
        <li><strong>Tax Computation Verification:</strong> BIR reports must reconcile with actual tax withheld from payroll</li>
        <li><strong>Audit Trail:</strong> All generated and submitted reports are logged with timestamps</li>
        <li><strong>Corrections:</strong> Amended reports require appropriate notation and supporting documentation</li>
      </ul>
    </div>
  </div>

</div>

<script>
function switchTab(event, tabName) {
  event.preventDefault();
  
  // Hide all tab contents
  const contents = document.querySelectorAll('.tab-content');
  contents.forEach(content => {
    content.classList.remove('active');
  });
  
  // Remove active class from all tabs
  const tabs = document.querySelectorAll('.tab');
  tabs.forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Show selected tab content
  document.getElementById(tabName).classList.add('active');
  
  // Add active class to clicked tab
  event.target.classList.add('active');
}
</script>
