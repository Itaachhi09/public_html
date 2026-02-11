<?php
/**
 * Payroll Adjustments and Special Pay Module
 * Handle non-regular payroll cases (final pay, back pay, 13th month pay, separation pay)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayrollAdjustment.php';
require_once __DIR__ . '/../models/PayrollRun.php';

$payrollAdjustment = new PayrollAdjustment();
$payrollRun = new PayrollRun();

// Fetch adjustments data
$adjustments = $payrollAdjustment->getAll();
$totalAdjustments = count($adjustments ?? []);
?>

<style>
  .adjustments-container {
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
  .form-group select,
  .form-group textarea {
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

  .alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
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

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-approved {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-processed {
    background: #dbeafe;
    color: #1e40af;
  }

  .badge-paid {
    background: #d1f0c5;
    color: #166534;
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
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
  }

  .adjustment-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid #3b82f6;
  }

  .adjustment-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .adjustment-card p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .adjustment-card .amount {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
  }

  .calculation-box {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-size: 13px;
    font-family: 'Courier New', monospace;
    line-height: 1.6;
  }

  .calculation-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
  }

  .calculation-item.total {
    font-weight: 700;
    border-top: 1px solid #d1d5db;
    padding-top: 0.5rem;
  }

  .info-box {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 4px;
    border-left: 4px solid #3b82f6;
    margin-bottom: 1rem;
  }

  .info-box h5 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 13px;
    font-weight: 600;
  }

  .info-box p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .tab-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
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

  @media print {
    .section {
      page-break-inside: avoid;
    }
  }
</style>

<div class="adjustments-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Payroll Adjustments & Special Pay</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Handle non-regular payroll cases including final pay, back pay, 13th month pay, and separation pay. All processed outside regular payroll with full approval workflows.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Features:</strong> Adjustment processing, approval workflows, calculation templates, reason logging, and complete audit trail for compliance.
    </div>
  </div>

  <!-- Quick Summary -->
  <div class="section">
    <h3 class="section-header">📊 Adjustments Summary</h3>

    <div class="summary-cards">
      <div class="summary-card">
        <label>Pending Approval</label>
        <div class="value">3</div>
      </div>
      <div class="summary-card success" style="border-left-color: #22c55e;">
        <label>Total Approved (Feb 2026)</label>
        <div class="value">₱156,500</div>
      </div>
      <div class="summary-card" style="border-left-color: #f59e0b;">
        <label>Processed This Month</label>
        <div class="value">5</div>
      </div>
      <div class="summary-card" style="border-left-color: #ef4444;">
        <label>Pending Payment</label>
        <div class="value">2</div>
      </div>
    </div>
  </div>

  <!-- Adjustment Type Selector -->
  <div class="section">
    <h3 class="section-header">🔧 Create New Adjustment</h3>

    <div class="tab-container">
      <button class="tab active" onclick="switchTab(event, 'final-pay')">Final Pay</button>
      <button class="tab" onclick="switchTab(event, 'back-pay')">Back Pay</button>
      <button class="tab" onclick="switchTab(event, 'thirteenth-month')">13th Month Pay</button>
      <button class="tab" onclick="switchTab(event, 'separation-pay')">Separation Pay</button>
    </div>

    <!-- Final Pay Tab -->
    <div id="final-pay" class="tab-content active">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>Final Pay - Employee Separation</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Process final paycheck for employees leaving the organization. Includes accrued leave, 13th month (if applicable), and separation pay.</p>

          <div class="form-row">
            <div class="form-group">
              <label>Employee Name <span style="color: #ef4444;">*</span></label>
              <select name="final_employee" required>
                <option value="">-- Select Employee --</option>
                <option value="EMP-004">Sarah Williams (EMP-004) - HR Coordinator</option>
                <option value="EMP-005">Robert Brown (EMP-005) - Finance Analyst</option>
              </select>
              <small>Search by name or employee ID</small>
            </div>
            <div class="form-group">
              <label>Separation Date <span style="color: #ef4444;">*</span></label>
              <input type="date" name="separation_date" value="2026-02-28" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Years of Service</label>
              <input type="text" value="3 years, 2 months" readonly style="background: #f3f4f6;">
            </div>
            <div class="form-group">
              <label>Last Salary</label>
              <input type="text" value="₱9,000.00" readonly style="background: #f3f4f6;">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Separation Reason <span style="color: #ef4444;">*</span></label>
              <select name="separation_reason" required>
                <option value="">-- Select Reason --</option>
                <option value="resignation">Resignation</option>
                <option value="retirement">Retirement</option>
                <option value="termination">Termination</option>
                <option value="contract_end">End of Contract</option>
                <option value="voluntary">Voluntary Separation</option>
              </select>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Additional Notes</label>
              <textarea name="final_notes" placeholder="Any special circumstances..." style="min-height: 80px; resize: vertical;"></textarea>
            </div>
          </div>

          <div class="info-box">
            <h5>Final Pay Components (Auto-calculated)</h5>
            <p>✓ Pro-rated salary from last paycheck to separation date</p>
            <p>✓ Accrued leave conversion (10 days @ ₱300/day = ₱3,000)</p>
            <p>✓ Mid-year bonus if applicable + proportional calculation</p>
            <p>✓ Separation pay if entitled (based on tenure and reason)</p>
            <p>✓ Refund of loans/advances</p>
          </p>
        </div>

        <div class="alert alert-info">
          Estimated final pay for Sarah Williams: ₱12,500.00 (includes ₱3,000 accrued leave + ₱4,500 separation pay)
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="calculate_final" class="btn btn-secondary">Review Calculation</button>
          <button type="submit" name="action" value="submit_final" class="btn btn-primary">Submit for Approval</button>
        </div>
      </form>
    </div>

    <!-- Back Pay Tab -->
    <div id="back-pay" class="tab-content">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>Back Pay - Retroactive Adjustments</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Process retroactive pay adjustments for salary corrections, promotion backdating, or missed payments.</p>

          <div class="form-row">
            <div class="form-group">
              <label>Employee Name <span style="color: #ef4444;">*</span></label>
              <select name="backpay_employee" required>
                <option value="">-- Select Employee --</option>
                <option value="EMP-001">John Doe (EMP-001) - HR Manager</option>
                <option value="EMP-002">Jane Smith (EMP-002) - Accountant</option>
                <option value="EMP-003">Michael Johnson (EMP-003) - Senior Developer</option>
                <option value="EMP-006">Emily Davis (EMP-006) - Operations Officer</option>
              </select>
            </div>
            <div class="form-group">
              <label>Adjustment Type <span style="color: #ef4444;">*</span></label>
              <select name="backpay_type" required>
                <option value="">-- Select Type --</option>
                <option value="salary_correction">Salary Correction</option>
                <option value="promotion_increase">Promotion Increase (Backdated)</option>
                <option value="missed_bonus">Missed Bonus</option>
                <option value="other">Other Adjustment</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Effective From <span style="color: #ef4444;">*</span></label>
              <input type="date" name="backpay_from" value="2026-01-01" required>
            </div>
            <div class="form-group">
              <label>Adjustment To <span style="color: #ef4444;">*</span></label>
              <input type="date" name="backpay_to" value="2026-02-28" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Monthly Increase/Difference <span style="color: #ef4444;">*</span></label>
              <input type="number" name="backpay_amount" placeholder="0.00" step="0.01" min="0" required>
            </div>
            <div class="form-group">
              <label>Number of Months</label>
              <input type="text" value="2" readonly style="background: #f3f4f6;">
            </div>
          </div>

          <div class="calculation-box">
            <div class="calculation-item">
              <span>Monthly increase:</span>
              <span>₱ 2,500.00</span>
            </div>
            <div class="calculation-item">
              <span>× Number of months:</span>
              <span>2</span>
            </div>
            <div class="calculation-item total">
              <span>Total back pay:</span>
              <span>₱ 5,000.00</span>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Reason for Adjustment <span style="color: #ef4444;">*</span></label>
              <textarea name="backpay_reason" placeholder="Explanation for retroactive adjustment..." style="min-height: 80px; resize: vertical;" required></textarea>
            </div>
          </div>

          <div class="alert alert-warning">
            ⚠️ Back pay adjustments are subject to manager and HR approval before processing to ensure accuracy and compliance.
          </div>

          <div class="btn-group">
            <button type="submit" name="action" value="review_backpay" class="btn btn-secondary">Review Calculation</button>
            <button type="submit" name="action" value="submit_backpay" class="btn btn-primary">Submit for Approval</button>
          </div>
        </div>
      </form>
    </div>

    <!-- 13th Month Pay Tab -->
    <div id="thirteenth-month" class="tab-content">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>13th Month Pay - Annual Bonus</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Generate 13th month pay (year-end bonus) for eligible employees based on tenure and employment status.</p>

          <div class="form-row full">
            <div class="form-group">
              <label>Pay Year & Month <span style="color: #ef4444;">*</span></label>
              <select name="thirteenth_month" required>
                <option value="">-- Select Period --</option>
                <option value="202512" selected>December 2025 (Year-end 2025)</option>
                <option value="202612">December 2026 (Year-end 2026)</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Employees to Include</label>
              <select name="thirteenth_employees">
                <option value="all" selected>All Eligible (8 employees)</option>
                <option value="active_only">Active Only (6 employees)</option>
                <option value="custom">Custom Selection</option>
              </select>
            </div>
            <div class="form-group">
              <label>Minimum Service Months</label>
              <input type="number" name="min_service_months" value="3" min="0">
              <small>Only employees with ≥ selected months included</small>
            </div>
          </div>

          <div class="info-box">
            <h5>13th Month Pay Calculation Rules</h5>
            <p>✓ Based on: Highest monthly salary received during the year</p>
            <p>✓ Formula: (Highest Monthly Salary × Number of Months Worked) ÷ 12</p>
            <p>✓ Minimum service: 3 months to qualify</p>
            <p>✓ Separation: Pro-rated for employees separated during the year</p>
            <p>✓ Maximum: Capped at 1 month basic salary</p>
          </div>

          <div class="calculation-box" style="margin-top: 1rem;">
            <div style="margin-bottom: 0.5rem; font-weight: 600; color: #1f2937;">Sample Calculations for Year 2025:</div>
            <div class="calculation-item">
              <span>John Doe (12 months service):</span>
              <span>₱11,000 × 12 ÷ 12 = ₱11,000</span>
            </div>
            <div class="calculation-item">
              <span>Sarah Williams (12 months service):</span>
              <span>₱9,000 × 12 ÷ 12 = ₱9,000</span>
            </div>
            <div class="calculation-item">
              <span>New Hire (6 months service):</span>
              <span>₱10,000 × 6 ÷ 12 = ₱5,000</span>
            </div>
            <div class="calculation-item total">
              <span>Total 13th Month Payout:</span>
              <span>₱ 88,000.00</span>
            </div>
          </div>

          <div class="alert alert-info">
            Estimated total for December 2025: ₱88,000.00 (8 eligible employees)
          </div>

          <div class="btn-group">
            <button type="submit" name="action" value="preview_thirteenth" class="btn btn-secondary">Preview Calculations</button>
            <button type="submit" name="action" value="submit_thirteenth" class="btn btn-primary">Submit for Approval</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Separation Pay Tab -->
    <div id="separation-pay" class="tab-content">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>Separation Pay - Severance Entitlements</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Calculate and process separation/severance pay based on tenure, separation reason, and applicable labor laws.</p>

          <div class="form-row">
            <div class="form-group">
              <label>Employee Name <span style="color: #ef4444;">*</span></label>
              <select name="sep_employee" required>
                <option value="">-- Select Employee --</option>
                <option value="EMP-004" selected>Sarah Williams (EMP-004) - 3 years, 2 months</option>
                <option value="EMP-005">Robert Brown (EMP-005) - 2 years, 8 months</option>
              </select>
            </div>
            <div class="form-group">
              <label>Separation Reason <span style="color: #ef4444;">*</span></label>
              <select name="sep_reason" required>
                <option value="">-- Select Reason --</option>
                <option value="retrenchment" selected>Retrenchment (Company Policy)</option>
                <option value="redundancy">Redundancy</option>
                <option value="end_of_contract">End of Contract</option>
                <option value="closure">Business Closure</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Separation Date <span style="color: #ef4444;">*</span></label>
              <input type="date" name="sep_date" value="2026-02-28" required>
            </div>
            <div class="form-group">
              <label>Last Salary</label>
              <input type="text" value="₱9,000.00" readonly style="background: #f3f4f6;">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Applicable Labor Code Formula <span style="color: #ef4444;">*</span></label>
              <select name="sep_formula" required>
                <option value="">-- Select Formula --</option>
                <option value="half_month" selected>Half-month pay per year of service (default)</option>
                <option value="one_month">One-month pay per year of service</option>
                <option value="minimum_wage">Minimum wage formula (Retrenchment)</option>
              </select>
              <small>Learn more about each formula in the section below</small>
            </div>
          </div>

          <div class="info-box">
            <h5>Separation Formula: Half-Month per Year (Selected)</h5>
            <p>• 1 year - 1 year 11 months: 0.5 × monthly salary</p>
            <p>• 2 years - 2 years 11 months: 1 × monthly salary</p>
            <p>• 3 years - 3 years 11 months: 1.5 × monthly salary (applies here)</p>
            <p>Example: ₱9,000 × 1.5 = ₱13,500</p>
          </div>

          <div class="calculation-box" style="margin-top: 1rem;">
            <div class="calculation-item">
              <span>Years of service:</span>
              <span>3 years, 2 months</span>
            </div>
            <div class="calculation-item">
              <span>Applicable multiplier:</span>
              <span>1.5x</span>
            </div>
            <div class="calculation-item">
              <span>Last monthly salary:</span>
              <span>₱ 9,000.00</span>
            </div>
            <div class="calculation-item total">
              <span>Separation pay entitlement:</span>
              <span>₱ 13,500.00</span>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Supporting Documentation/Notes</label>
              <textarea name="sep_notes" placeholder="Attach or reference supporting docs for separation reason..." style="min-height: 80px; resize: vertical;"></textarea>
            </div>
          </div>

          <div class="alert alert-info">
            💡 Separation pay is mandated by Philippine labor law (Article 283-284 of Labor Code). Accurate calculations are required for compliance.
          </div>

          <div class="btn-group">
            <button type="submit" name="action" value="calculate_separation" class="btn btn-secondary">Review Calculation</button>
            <button type="submit" name="action" value="submit_separation" class="btn btn-primary">Submit for Approval</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Pending Approvals -->
  <div class="section">
    <h3 class="section-header">⏳ Pending Approvals</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Adjustment Type</th>
            <th>Amount</th>
            <th>Submitted</th>
            <th>Requested By</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Sarah Williams (EMP-004)</td>
            <td>Final Pay</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱12,500.00</td>
            <td>February 7, 2026</td>
            <td>Juan de la Cruz (HR Officer)</td>
            <td><span class="badge badge-pending">Pending HR Review</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="approval_id" value="ADJ-001">
                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              </form>
              <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Reject action initiated')">Reject</button>
            </td>
          </tr>
          <tr>
            <td>John Doe (EMP-001)</td>
            <td>Back Pay</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱5,000.00</td>
            <td>February 5, 2026</td>
            <td>Maria Santos (Payroll Officer)</td>
            <td><span class="badge badge-pending">Pending Finance Review</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="approval_id" value="ADJ-002">
                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              </form>
              <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Reject action initiated')">Reject</button>
            </td>
          </tr>
          <tr>
            <td>All Employees (8)</td>
            <td>13th Month Pay (Dec 2025)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱88,000.00</td>
            <td>February 3, 2026</td>
            <td>Maria Garcia (Finance Manager)</td>
            <td><span class="badge badge-pending">Pending CFO Approval</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="approval_id" value="ADJ-003">
                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              </form>
              <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Reject action initiated')">Reject</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Processed Adjustments History -->
  <div class="section">
    <h3 class="section-header">📋 Processed Adjustments History</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Submitted</th>
            <th>Approved Date</th>
            <th>Paid Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Michael Johnson (EMP-003)</td>
            <td>Back Pay (Promotion)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱7,500.00</td>
            <td>January 28, 2026</td>
            <td>January 30, 2026</td>
            <td>February 7, 2026</td>
            <td><span class="badge badge-paid">Paid</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="adj_id" value="ADJ-100">
                <button type="submit" class="btn btn-secondary btn-sm">Details</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>David Martinez (EMP-007)</td>
            <td>Back Pay (Correction)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱3,000.00</td>
            <td>January 20, 2026</td>
            <td>January 22, 2026</td>
            <td>February 1, 2026</td>
            <td><span class="badge badge-paid">Paid</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="adj_id" value="ADJ-099">
                <button type="submit" class="btn btn-secondary btn-sm">Details</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>Emily Davis (EMP-006)</td>
            <td>13th Month Pay (2024)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱11,000.00</td>
            <td>December 20, 2024</td>
            <td>December 22, 2024</td>
            <td>December 24, 2024</td>
            <td><span class="badge badge-paid">Paid</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="adj_id" value="ADJ-089">
                <button type="submit" class="btn btn-secondary btn-sm">Details</button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Compliance & Rules -->
  <div class="section">
    <h3 class="section-header">📋 Adjustment Processing Rules & Compliance</h3>

    <div class="adjustment-card">
      <h4>▶ Final Pay</h4>
      <p>Processed when employee leaves organization (resignation, retirement, termination)</p>
      <p><strong>Components:</strong> Pro-rated salary, accrued leave, separation pay, mid-year bonus (pro-rated)</p>
      <p><strong>Rule:</strong> Approval required from HR Manager + Finance. Must match employment contract terms.</p>
    </div>

    <div class="adjustment-card">
      <h4>▶ Back Pay</h4>
      <p>Retroactive payment for salary corrections or missed adjustments</p>
      <p><strong>Components:</strong> Additional pay for prior months, recalculation of deductions if needed</p>
      <p><strong>Rule:</strong> Requires manager approval + reason documentation. Must reconcile with prior payroll records.</p>
    </div>

    <div class="adjustment-card">
      <h4>▶ 13th Month Pay</h4>
      <p>Legally mandated year-end bonus (Philippine law - minimum ₱88.34/day)</p>
      <p><strong>Components:</strong> One month basic pay based on highest monthly earned during the year</p>
      <p><strong>Rule:</strong> Minimum 3 months service to qualify. Pro-rated for separated employees. CFO approval required for company-wide processing.</p>
    </div>

    <div class="adjustment-card">
      <h4>▶ Separation Pay</h4>
      <p>Legally mandated severance (Philippine Labor Code Article 283-284)</p>
      <p><strong>Components:</strong> Half to one month salary per year of service (based on separation reason)</p>
      <p><strong>Rule:</strong> Required for retrenchment, redundancy, end of contract. Finance + Legal approval required. Mandatory 30-day notice period applies.</p>
    </div>

    <div class="alert alert-warning" style="margin-top: 2rem;">
      <strong>⚠️ Critical Rules - ALL ADJUSTMENTS:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>No Regular Payroll:</strong> Adjustments processed separately, NOT included in regular bi-weekly payroll</li>
        <li><strong>Approval Mandatory:</strong> Department manager + HR/Finance dual approval required before processing</li>
        <li><strong>Reason Logged:</strong> All adjustments must have documented reason for audit trail</li>
        <li><strong>Calculation Verified:</strong> Automated calculations cross-checked against employment contracts and labor law requirements</li>
        <li><strong>Compliance Check:</strong> System validates all adjustments against Philippine labor law requirements</li>
        <li><strong>Audit Trail:</strong> Complete history of all adjustments, approvals, and payments maintained</li>
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
