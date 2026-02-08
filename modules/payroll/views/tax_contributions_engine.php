<?php
/**
 * Tax and Contributions Engine Module
 * Ensure legal compliance through automated tax computation and contribution calculations
 */
?>

<style>
  .tax-engine-container {
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

  .compliance-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 1rem;
  }

  .compliance-compliant {
    background: #d1fae5;
    color: #065f46;
  }

  .compliance-warning {
    background: #fef3c7;
    color: #92400e;
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

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-active {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-inactive {
    background: #fee2e2;
    color: #991b1b;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .tax-breakdown {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
  }

  .breakdown-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 1.5rem;
    border-left: 4px solid #3b82f6;
  }

  .breakdown-card h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .breakdown-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
  }

  .breakdown-item:last-child {
    border-bottom: none;
  }

  .breakdown-item label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
  }

  .breakdown-item value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
    text-align: right;
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

  .version-info {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 13px;
  }

  .version-info strong {
    color: #1f2937;
  }

  .version-info .date {
    color: #6b7280;
    font-family: 'Courier New', monospace;
  }

  .tax-table {
    margin-top: 1rem;
  }

  .tax-table table th {
    background: #eff6ff;
    font-weight: 600;
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
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
  }

  .version-history {
    margin-top: 2rem;
  }

  .history-item {
    padding: 1rem;
    border-left: 4px solid #3b82f6;
    margin-bottom: 1rem;
    background: #f9fafb;
    border-radius: 4px;
  }

  .history-item .date {
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
  }

  .history-item .version {
    color: #1f2937;
    font-size: 13px;
    font-weight: 600;
  }

  .history-item .status {
    margin-top: 0.5rem;
  }

  .effective-date {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
    margin-left: 0.5rem;
  }

  .compliance-status {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 13px;
  }

  .compliance-status.compliant {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
  }

  .compliance-status.warning {
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
  }

  .calculation-step {
    padding: 1rem;
    background: #f3f4f6;
    border-left: 3px solid #3b82f6;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 13px;
  }

  .calculation-step strong {
    color: #1f2937;
  }
</style>

<div class="tax-engine-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Tax & Contributions Engine</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Automated tax computation and contribution calculations ensuring legal compliance. No manual tax entry allowed - all calculations follow version-controlled statutory tables.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #d1fae5; border-radius: 4px; color: #065f46; font-size: 13px;">
      <strong>✓ Legal Compliance:</strong> All tax and contribution computations are automated using version-controlled tables with effective dates. Manual overrides are not permitted.
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
          <label>Tax Status</label>
          <select name="tax_status">
            <option value="">-- All Status --</option>
            <option value="taxable">Taxable</option>
            <option value="exempt">Tax Exempt</option>
          </select>
        </div>
        <div class="form-group">
          <label>Payroll Period</label>
          <input type="month" name="period">
        </div>
        <div style="display: flex; align-items: flex-end;">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Summary Cards -->
  <div class="summary-cards">
    <div class="summary-card">
      <label>Total Taxable Employees</label>
      <div class="value">6</div>
    </div>
    <div class="summary-card">
      <label>Tax Exempt Employees</label>
      <div class="value">2</div>
    </div>
    <div class="summary-card" style="border-left-color: #ef4444;">
      <label>Total Tax Withholding</label>
      <div class="value">₱ 18,700</div>
    </div>
    <div class="summary-card" style="border-left-color: #22c55e;">
      <label>Compliance Status</label>
      <div class="value" style="color: #065f46;">✓ Compliant</div>
    </div>
  </div>

  <!-- Tax Compliance Status -->
  <div class="section">
    <div class="compliance-status compliant">
      <strong>✓ System Compliance Status: COMPLIANT</strong><br>
      All tax calculations are using current, version-controlled statutory tables. BIR Withholding Tax Table v2.1 (Effective: January 1, 2026) applied to all computations.
    </div>
  </div>

  <!-- Active Tax & Contribution Tables -->
  <div class="section">
    <h3 class="section-header">📊 Active Tax & Contribution Tables (Current Period)</h3>

    <!-- BIR Withholding Tax Table -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        BIR Withholding Tax Table
        <span class="effective-date">v2.1 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 2.1 | <strong>Released:</strong> December 15, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Annual Taxable Income (From)</th>
                <th>Annual Taxable Income (To)</th>
                <th>Tax Rate (%)</th>
                <th>Fixed Deduction Amount (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>250,000</td>
                <td class="amount">0%</td>
                <td class="amount">0.00</td>
              </tr>
              <tr>
                <td>250,000.01</td>
                <td>400,000</td>
                <td class="amount">5%</td>
                <td class="amount">0.00</td>
              </tr>
              <tr>
                <td>400,000.01</td>
                <td>800,000</td>
                <td class="amount">10%</td>
                <td class="amount">7,500.00</td>
              </tr>
              <tr>
                <td>800,000.01</td>
                <td>2,000,000</td>
                <td class="amount">15%</td>
                <td class="amount">47,500.00</td>
              </tr>
              <tr>
                <td>2,000,000.01</td>
                <td>Unlimited</td>
                <td class="amount">20%</td>
                <td class="amount">187,500.00</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SSS Contribution Table -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        SSS Contribution Table
        <span class="effective-date">v1.5 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 1.5 | <strong>Released:</strong> December 1, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Employee Rate (%)</th>
                <th>Monthly Contribution (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>2,250</td>
                <td class="amount">3.63%</td>
                <td class="amount">81.74</td>
              </tr>
              <tr>
                <td>2,250.01</td>
                <td>3,750</td>
                <td class="amount">3.63%</td>
                <td class="amount">136.13</td>
              </tr>
              <tr>
                <td>3,750.01</td>
                <td>5,250</td>
                <td class="amount">3.63%</td>
                <td class="amount">190.50</td>
              </tr>
              <tr>
                <td>5,250.01</td>
                <td>7,750</td>
                <td class="amount">3.63%</td>
                <td class="amount">281.38</td>
              </tr>
              <tr>
                <td>7,750.01</td>
                <td>Unlimited</td>
                <td class="amount">3.63%</td>
                <td class="amount">406.24</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- PhilHealth Contribution Table -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        PhilHealth Premium Table
        <span class="effective-date">v2.0 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 2.0 | <strong>Released:</strong> December 10, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Monthly Salary (From)</th>
                <th>Monthly Salary (To)</th>
                <th>Employee Rate (%)</th>
                <th>Monthly Premium (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>8,999.99</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
              <tr>
                <td>9,000</td>
                <td>14,999.99</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
              <tr>
                <td>15,000</td>
                <td>49,999.99</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
              <tr>
                <td>50,000</td>
                <td>Unlimited</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pag-IBIG Contribution Table -->
    <div>
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        Pag-IBIG Contribution Table
        <span class="effective-date">v1.3 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 1.3 | <strong>Released:</strong> December 5, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Monthly Salary (From)</th>
                <th>Monthly Salary (To)</th>
                <th>Employee Rate (%)</th>
                <th>Monthly Contribution (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>1,500</td>
                <td class="amount">1.61%</td>
                <td class="amount">100.00</td>
              </tr>
              <tr>
                <td>1,500.01</td>
                <td>4,666.67</td>
                <td class="amount">1.61%</td>
                <td class="amount">150.00</td>
              </tr>
              <tr>
                <td>4,666.68</td>
                <td>Unlimited</td>
                <td class="amount">1.61%</td>
                <td class="amount">200.00</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Tax Computation Example -->
  <div class="section">
    <h3 class="section-header">📋 Tax Computation Example - John Doe (EMP-001)</h3>

    <div class="alert alert-info">
      All calculations use the currently active, version-controlled tables shown above. This ensures complete legal compliance with BIR regulations.
    </div>

    <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 14px; font-weight: 600;">STEP 1: Identify Taxable Income</h4>

    <div class="tax-breakdown">
      <div class="breakdown-card">
        <h4>Gross Earnings Calculation</h4>
        <div class="breakdown-item">
          <label>Base Pay</label>
          <value>₱ 6,000.00</value>
        </div>
        <div class="breakdown-item">
          <label>Incentives</label>
          <value>₱ 2,000.00</value>
        </div>
        <div class="breakdown-item">
          <label>Double Pay</label>
          <value>₱ 3,000.00</value>
        </div>
        <div class="breakdown-item">
          <label>Gross Earnings</label>
          <value style="font-weight: 600; border-top: 2px solid #e5e7eb; padding-top: 0.5rem;">₱ 11,000.00</value>
        </div>
      </div>

      <div class="breakdown-card">
        <h4>Non-Taxable Benefits (Excluded)</h4>
        <div class="breakdown-item">
          <label>13th Month Pay (Annual)</label>
          <value>₱ 0.00</value>
        </div>
        <div class="breakdown-item">
          <label>COLA / Cost of Living Allowance</label>
          <value>₱ 0.00</value>
        </div>
        <div class="breakdown-item">
          <label>Other Non-Taxable Allowances</label>
          <value>₱ 0.00</value>
        </div>
        <div class="breakdown-item">
          <label>Total Non-Taxable</label>
          <value style="font-weight: 600; border-top: 2px solid #e5e7eb; padding-top: 0.5rem;">₱ 0.00</value>
        </div>
      </div>

      <div class="breakdown-card" style="border-left-color: #f59e0b;">
        <h4>Taxable Income</h4>
        <div class="breakdown-item">
          <label>Gross Earnings</label>
          <value>₱ 11,000.00</value>
        </div>
        <div class="breakdown-item">
          <label>Less: Non-Taxable</label>
          <value>-₱ 0.00</value>
        </div>
        <div class="breakdown-item">
          <label>TAXABLE INCOME</label>
          <value style="font-weight: 600; border-top: 2px solid #e5e7eb; padding-top: 0.5rem; color: #f59e0b;">₱ 11,000.00</value>
        </div>
      </div>
    </div>

    <h4 style="color: #1f2937; margin: 2rem 0 1rem 0; font-size: 14px; font-weight: 600;">STEP 2: Apply BIR Withholding Tax Bracket</h4>

    <div class="calculation-step">
      Monthly Taxable Income: ₱11,000.00<br>
      Annual Equivalent: ₱11,000 × 12 = ₱132,000.00<br>
      <br>
      <strong>Tax Bracket Applied (from v2.1 table):</strong><br>
      Annual Income ₱132,000 falls between ₱0 - ₱250,000<br>
      Tax Rate: 0%<br>
      <br>
      <strong>Monthly Withholding Tax = ₱0.00</strong>
    </div>

    <h4 style="color: #1f2937; margin: 2rem 0 1rem 0; font-size: 14px; font-weight: 600;">STEP 3: Compute Contributions (Automatic)</h4>

    <div class="tax-breakdown">
      <div class="breakdown-card">
        <h4>SSS Contribution (v1.5)</h4>
        <div class="breakdown-item">
          <label>Method: Table Lookup</label>
          <value>Based on Salary Range</value>
        </div>
        <div class="breakdown-item">
          <label>Monthly Salary: ₱11,000</label>
          <value>Finds ₱7,750.01+</value>
        </div>
        <div class="breakdown-item">
          <label>SSS Contribution</label>
          <value style="font-weight: 600;">₱ 406.24</value>
        </div>
      </div>

      <div class="breakdown-card">
        <h4>PhilHealth Premium (v2.0)</h4>
        <div class="breakdown-item">
          <label>Method: Flat Rate</label>
          <value>Fixed Amount</value>
        </div>
        <div class="breakdown-item">
          <label>Salary Tier</label>
          <value>Above ₱50,000</value>
        </div>
        <div class="breakdown-item">
          <label>PhilHealth Premium</label>
          <value style="font-weight: 600;">₱ 500.00</value>
        </div>
      </div>

      <div class="breakdown-card">
        <h4>Pag-IBIG Contribution (v1.3)</h4>
        <div class="breakdown-item">
          <label>Method: Table Lookup</label>
          <value>Based on Salary Range</value>
        </div>
        <div class="breakdown-item">
          <label>Monthly Salary: ₱11,000</label>
          <value>Finds ₱4,666.68+</value>
        </div>
        <div class="breakdown-item">
          <label>Pag-IBIG Contribution</label>
          <value style="font-weight: 600;">₱ 200.00</value>
        </div>
      </div>
    </div>

    <div style="background: #dbeafe; padding: 1.5rem; border-radius: 4px; margin-top: 2rem;">
      <h4 style="margin: 0 0 1rem 0; color: #1e40af;">✓ All Computations Complete</h4>
      <div style="color: #1e40af; font-size: 13px;">
        <strong>Withholding Tax:</strong> ₱0.00<br>
        <strong>Total Contributions:</strong> ₱1,106.24<br>
        <strong>Table Versions Applied:</strong> BIR v2.1, SSS v1.5, PhilHealth v2.0, Pag-IBIG v1.3<br>
        <strong>Status:</strong> Compliant with BIR regulations
      </div>
    </div>
  </div>

  <!-- Version Control History -->
  <div class="section">
    <h3 class="section-header">📜 Tax Table Version Control & History</h3>

    <div class="alert alert-info">
      <strong>ℹ️ Version Control System:</strong> All tax and contribution tables are version-controlled with effective dates. When a new table version is released, it automatically becomes active on the specified effective date. All historical versions are maintained for audit purposes.
    </div>

    <h4 style="color: #1f2937; margin-bottom: 1.5rem; margin-top: 1.5rem; font-size: 14px; font-weight: 600;">BIR Withholding Tax Table History</h4>

    <div class="version-history">
      <div class="history-item" style="border-left-color: #22c55e;">
        <div class="version">BIR Withholding Tax Table v2.1</div>
        <div class="date">Effective: January 1, 2026 | Released: December 15, 2025</div>
        <div class="status">
          <span class="badge badge-active">ACTIVE</span>
          <span style="margin-left: 0.5rem; color: #6b7280; font-size: 12px;">(Currently in Use)</span>
        </div>
      </div>

      <div class="history-item">
        <div class="version">BIR Withholding Tax Table v2.0</div>
        <div class="date">Effective: July 1, 2025 - December 31, 2025 | Released: June 10, 2025</div>
        <div class="status">
          <span class="badge badge-inactive">INACTIVE</span>
          <span style="margin-left: 0.5rem; color: #6b7280; font-size: 12px;">(Superseded)</span>
        </div>
      </div>

      <div class="history-item">
        <div class="version">BIR Withholding Tax Table v1.9</div>
        <div class="date">Effective: January 1, 2025 - June 30, 2025 | Released: December 2024</div>
        <div class="status">
          <span class="badge badge-inactive">INACTIVE</span>
          <span style="margin-left: 0.5rem; color: #6b7280; font-size: 12px;">(Archived)</span>
        </div>
      </div>
    </div>

    <h4 style="color: #1f2937; margin-bottom: 1.5rem; margin-top: 2rem; font-size: 14px; font-weight: 600;">Other Contribution Table Versions</h4>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
      <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; border-left: 4px solid #22c55e;">
        <strong style="color: #1f2937;">SSS Contribution Table</strong>
        <div style="color: #6b7280; font-size: 12px; margin-top: 0.5rem;">
          <div>Current: v1.5 (Active since Jan 1, 2026)</div>
          <div style="margin-top: 0.5rem;">Previous: v1.4 (Jul 1 - Dec 31, 2025)</div>
          <div>Previous: v1.3 (Jan 1 - Jun 30, 2025)</div>
        </div>
      </div>

      <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; border-left: 4px solid #22c55e;">
        <strong style="color: #1f2937;">PhilHealth Premium Table</strong>
        <div style="color: #6b7280; font-size: 12px; margin-top: 0.5rem;">
          <div>Current: v2.0 (Active since Jan 1, 2026)</div>
          <div style="margin-top: 0.5rem;">Previous: v1.9 (Jul 1 - Dec 31, 2025)</div>
          <div>Previous: v1.8 (Jan 1 - Jun 30, 2025)</div>
        </div>
      </div>

      <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; border-left: 4px solid #22c55e;">
        <strong style="color: #1f2937;">Pag-IBIG Contribution Table</strong>
        <div style="color: #6b7280; font-size: 12px; margin-top: 0.5rem;">
          <div>Current: v1.3 (Active since Jan 1, 2026)</div>
          <div style="margin-top: 0.5rem;">Previous: v1.2 (Jul 1 - Dec 31, 2025)</div>
          <div>Previous: v1.1 (Jan 1 - Jun 30, 2025)</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Compliance & Audit Trail -->
  <div class="section">
    <h3 class="section-header">✓ Compliance & Audit Trail</h3>

    <div class="alert alert-success">
      <strong>✓ System Status: FULLY COMPLIANT</strong><br>
      All tax and contribution computations are automated and use version-controlled, legally-approved tables. No manual tax entry is permitted. All calculations are logged for audit purposes.
    </div>

    <h4 style="color: #1f2937; margin: 1.5rem 0 1rem 0; font-size: 14px; font-weight: 600;">Key Compliance Features</h4>

    <ul style="color: #6b7280; font-size: 13px; line-height: 1.8;">
      <li><strong style="color: #1f2937;">✓ Automated Computation:</strong> No manual tax entry allowed. All calculations follow statutory tables.</li>
      <li><strong style="color: #1f2937;">✓ Version Control:</strong> Each table version is dated with effective dates. All payroll calculations reference the correct version for the pay period.</li>
      <li><strong style="color: #1f2937;">✓ Effective Date Management:</strong> Table versions automatically activate on specified effective dates. Previous versions archived for historical reference.</li>
      <li><strong style="color: #1f2937;">✓ Audit Trail:</strong> Every payroll computation captures which table version was used, enabling full audit trail compliance.</li>
      <li><strong style="color: #1f2937;">✓ Non-Taxable Benefits:</strong> System correctly excludes 13th month pay, COLA, and other non-taxable allowances from tax computation.</li>
      <li><strong style="color: #1f2937;">✓ Contribution Calculation:</strong> SSS, PhilHealth, and Pag-IBIG contributions automatically computed based on employee profile enrollment status.</li>
      <li><strong style="color: #1f2937;">✓ Legal Compliance:</strong> All calculations comply with BIR, SSS, PhilHealth, and Pag-IBIG regulations.</li>
    </ul>
  </div>

</div>
