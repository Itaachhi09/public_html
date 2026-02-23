<?php
/**
 * Government Reports and Compliance Module
 * Generate statutory reports for government agencies (SSS, PhilHealth, Pag-IBIG, BIR)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/GovernmentReport.php';
require_once __DIR__ . '/../models/PayrollRunEmployee.php';
require_once __DIR__ . '/../../hr_core/models/EmployeeModel.php';

$payrollRunEmployee = new PayrollRunEmployee();
$employeeModel = new EmployeeModel();

$governmentReport = new GovernmentReport();

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
$modal = isset($_GET['modal']) ? $_GET['modal'] : null;

// Handle AJAX requests for downloads
if ($isAjax && $modal === 'download') {
    $reportId = isset($_GET['report_id']) ? $_GET['report_id'] : '';
    
    // Create sample report file content based on report type
    $filename = 'report_' . $reportId . '_' . date('Y-m-d') . '.pdf';
    $reportContent = "Healthcare Hospital Inc. - Government Report\n";
    $reportContent .= "Report ID: " . $reportId . "\n";
    $reportContent .= "Generated: " . date('Y-m-d H:i:s') . "\n";
    $reportContent .= "======================================\n";
    $reportContent .= "This is a sample government report.\n";
    $reportContent .= "Actual content would include detailed employee data and contributions.\n";
    
    // Set response headers for file download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($reportContent));
    
    echo $reportContent;
    exit;
}

// Handle AJAX preview: generate report preview from DB if payroll_run_id provided
// otherwise fall back to sample preview
if ($isAjax && $modal === 'preview') {
    $reportType = isset($_GET['type']) ? $_GET['type'] : '';
    $period = isset($_GET['period']) ? $_GET['period'] : date('F Y');
    $payrollRunId = isset($_GET['payroll_run_id']) ? (int)$_GET['payroll_run_id'] : 0;

    $rows = [];
    $totalEmployees = 0;
    $totalContributions = 0.00;

    if ($payrollRunId > 0) {
        // Decide which column to use based on report type
        $colMap = [
            'sss' => 'sss_contribution',
            'philhealth' => 'philhealth_contribution',
            'pagibig' => 'pagibig_contribution',
            'bir' => 'withholding_tax',
        ];
        $key = strtolower($reportType);
        $col = $colMap[$key] ?? null;

        if ($col) {
            $recs = $payrollRunEmployee->query("SELECT pre.*, e.first_name, e.last_name, e.employee_code FROM payroll_run_employees pre JOIN employees e ON pre.employee_id = e.employee_id WHERE pre.payroll_run_id = ?", [$payrollRunId]);
            foreach ($recs as $r) {
                $amount = floatval($r[$col] ?? 0);
                if ($amount <= 0) continue;
                $rows[] = [
                    'name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                    'amount' => $amount
                ];
                $totalContributions += $amount;
            }
            $totalEmployees = count($rows);
        }
    }

    // If no db rows, provide a minimal sample preview
    if (empty($rows)) {
        $rows = [
            ['name' => 'Sample Employee A', 'amount' => 450.00],
            ['name' => 'Sample Employee B', 'amount' => 380.00],
            ['name' => 'Sample Employee C', 'amount' => 420.00],
        ];
        foreach ($rows as $r) $totalContributions += $r['amount'];
        $totalEmployees = count($rows);
    }

    // Render preview HTML
    ?>
    <div class="modal-overlay active" style="display: flex;">
      <div class="modal-box">
        <div class="modal-header">
          <h3>Report Preview - <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $reportType))); ?></h3>
          <button type="button" class="modal-close" onclick="window.closeGovernmentModal()">&times;</button>
        </div>
        <div class="modal-body">
          <div class="report-preview">
            <div style="text-align: center; margin-bottom: 1.5rem;">
              <h4 style="margin: 0 0 0.5rem 0;">Healthcare Hospital Inc.</h4>
              <p style="margin: 0; color: #666; font-size: 13px;">BIR TIN: 012-345-678</p>
              <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 13px;">Period: <?php echo htmlspecialchars($period); ?></p>
            </div>

            <div style="background: #f9fafb; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                <div>
                  <label style="font-size: 12px; color: #666;">Total Employees</label>
                  <p style="margin: 0; font-weight: 600; font-size: 16px;"><?php echo (int)$totalEmployees; ?></p>
                </div>
                <div>
                  <label style="font-size: 12px; color: #666;">Total Contributions</label>
                  <p style="margin: 0; font-weight: 600; font-size: 16px;">₱<?php echo number_format($totalContributions, 2); ?></p>
                </div>
              </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 1rem;">
              <thead>
                <tr style="background: #f3f4f6; border-bottom: 1px solid #e5e7eb;">
                  <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Employee Name</th>
                  <th style="padding: 0.75rem; text-align: center; font-weight: 600;">Amount</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r): ?>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                  <td style="padding: 0.75rem;"><?php echo htmlspecialchars($r['name']); ?></td>
                  <td style="padding: 0.75rem; text-align: center;">₱<?php echo number_format($r['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background: #f3f4f6; border-bottom: 1px solid #e5e7eb;">
                  <td style="padding: 0.75rem; font-weight: 600;">Total</td>
                  <td style="padding: 0.75rem; text-align: center; font-weight: 600;">₱<?php echo number_format($totalContributions, 2); ?></td>
                </tr>
              </tbody>
            </table>

            <div class="alert alert-info" style="background: #dbeafe; border: 1px solid #7dd3fc; padding: 0.75rem; border-radius: 4px; font-size: 13px; color: #0369a1; margin: 0;">
              ℹ️ This preview uses data from the selected payroll run when available.
            </div>
          </div>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
          <button type="button" onclick="window.closeGovernmentModal()" class="btn btn-secondary" style="margin: 0;">Close</button>
          <button type="button" class="btn btn-primary" style="margin: 0;">Export Now</button>
        </div>
      </div>
    </div>
    <?php
    exit;
}

// Fetch government reports
$reports = $governmentReport->getAll();
$totalReports = count($reports ?? []);

// Compute latest payroll run aggregates (used in tab summaries)
$latestRun = $payrollRunEmployee->query("SELECT payroll_run_id FROM payroll_run_employees ORDER BY payroll_run_id DESC LIMIT 1", []);
$latestPayrollRunId = isset($latestRun[0]['payroll_run_id']) ? (int)$latestRun[0]['payroll_run_id'] : 0;
$ph_total = 0.00;
$sss_total = 0.00;
$pagibig_total = 0.00;
$total_employees = 0;
if ($latestPayrollRunId > 0) {
  $agg = $payrollRunEmployee->query("SELECT COUNT(*) AS cnt, COALESCE(SUM(sss_contribution),0) AS sss_total, COALESCE(SUM(philhealth_contribution),0) AS ph_total, COALESCE(SUM(pagibig_contribution),0) AS pagibig_total FROM payroll_run_employees WHERE payroll_run_id = ?", [$latestPayrollRunId]);
  if (!empty($agg)) {
    $total_employees = (int)($agg[0]['cnt'] ?? 0);
    $sss_total = floatval($agg[0]['sss_total'] ?? 0);
    $ph_total = floatval($agg[0]['ph_total'] ?? 0);
    $pagibig_total = floatval($agg[0]['pagibig_total'] ?? 0);
  }
}
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

  /* Modal Styles */
  .modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    padding: 1rem;
  }

  .modal-overlay.active {
    display: flex !important;
    animation: slideIn 0.3s ease;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .modal-box {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-width: 650px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
  }

  .modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
  }

  .modal-close:hover {
    background: #f3f4f6;
    color: #1f2937;
  }

  .modal-body {
    padding: 1.5rem;
  }

  .modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
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
      <div class="report-card" role="button" tabindex="0" onclick="window.openGovernmentModal('sss', <?php echo $latestPayrollRunId; ?>)">
        <h4>🏢 SSS Report</h4>
        <p>Social Security System contributions and membership</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Employee ID, monthly contributions, salary</p>
        <div class="report-card due-date">Monthly by 10th working day</div>
      </div>

      <div class="report-card" role="button" tabindex="0" onclick="window.openGovernmentModal('philhealth', <?php echo $latestPayrollRunId; ?>)">
        <h4>🏥 PhilHealth Report</h4>
        <p>Philippine Health Insurance contributions</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Member data, premium contributions, salaries</p>
        <div class="report-card due-date">Monthly by 10th working day</div>
      </div>

      <div class="report-card" role="button" tabindex="0" onclick="window.openGovernmentModal('pagibig', <?php echo $latestPayrollRunId; ?>)">
        <h4>🏦 Pag-IBIG Report</h4>
        <p>Pag-IBIG Fund contributions and loan tracking</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Contribution records, loan details, salary</p>
        <div class="report-card due-date">Monthly by 10th working day</div>
      </div>

      <div class="report-card" role="button" tabindex="0" onclick="window.openGovernmentModal('bir1601', <?php echo $latestPayrollRunId; ?>)">
        <h4>📋 BIR 1601-C</h4>
        <p>Annual Withholding Tax Report</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Yearly tax withheld by income class</p>
        <div class="report-card due-date">Annually by January 31</div>
      </div>

      <div class="report-card" role="button" tabindex="0" onclick="window.openGovernmentModal('bir2316', <?php echo $latestPayrollRunId; ?>)">
        <h4>📄 BIR 2316</h4>
        <p>Certificate of Withholding Tax</p>
        <p style="font-size: 11px; color: #9ca3af;">Covers: Individual tax certificates for employees</p>
        <div class="report-card due-date">Annually by January 31</div>
      </div>

      <div class="report-card" role="button" tabindex="0" onclick="window.openGovernmentModal('alphalist', <?php echo $latestPayrollRunId; ?>)">
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
      <form method="POST" action="../government_reports_compliance_handler.php">
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
          <button type="button" onclick="window.openGovernmentModal('sss')" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_sss_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_sss_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_sss" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- PhilHealth Tab -->
    <div id="philhealth" class="tab-content">
      <form method="POST" action="../government_reports_compliance_handler.php">
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
          Selected period: <?php echo date('F Y'); ?>. Total employees: <?php echo (int)$total_employees; ?>. Total contributions: ₱<?php echo number_format($ph_total,2); ?>
        </div>

        <div class="btn-group">
          <button type="button" onclick="window.openGovernmentModal('philhealth')" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_ph_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_ph_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_ph" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- Pag-IBIG Tab -->
    <div id="pagibig" class="tab-content">
      <form method="POST" action="../government_reports_compliance_handler.php">
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
          Selected period: <?php echo date('F Y'); ?>. Total employees: <?php echo (int)$total_employees; ?>. Total contributions: ₱<?php echo number_format($pagibig_total,2); ?>
        </div>

        <div class="btn-group">
          <button type="button" onclick="window.openGovernmentModal('pagibig')" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_pagibig_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_pagibig_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_pagibig" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- BIR 1601-C Tab -->
    <div id="bir1601" class="tab-content">
      <form method="POST" action="../government_reports_compliance_handler.php">
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
          <button type="button" onclick="window.openGovernmentModal('bir1601')" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_bir1601_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_bir1601_pdf" class="btn btn-primary">Export as PDF</button>
          <button type="submit" name="action" value="print_bir1601" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- BIR 2316 Tab -->
    <div id="bir2316" class="tab-content">
      <form method="POST" action="../government_reports_compliance_handler.php">
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
          <button type="button" onclick="window.openGovernmentModal('bir2316')" class="btn btn-secondary">Preview Certificate</button>
          <button type="submit" name="action" value="export_bir2316_csv" class="btn btn-primary">Export All as CSV</button>
          <button type="submit" name="action" value="export_bir2316_pdf" class="btn btn-primary">Export All as PDF</button>
          <button type="submit" name="action" value="print_bir2316" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>

    <!-- Alphalist Tab -->
    <div id="alphalist" class="tab-content">
      <form method="POST" action="../government_reports_compliance_handler.php">
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
          <button type="button" onclick="window.openGovernmentModal('alphalist')" class="btn btn-secondary">Preview Report</button>
          <button type="submit" name="action" value="export_alphalist_csv" class="btn btn-primary">Export as CSV</button>
          <button type="submit" name="action" value="export_alphalist_txt" class="btn btn-primary">Export as TXT (BIR Format)</button>
          <button type="submit" name="action" value="print_alphalist" class="btn btn-secondary">Print HTML</button>
        </div>
      </form>
    </div>
  </div>

  <!-- SSS Report Preview (DB-driven) -->
  <div class="section">
    <h3 class="section-header">👁️ SSS Report Preview</h3>

    <div class="alert alert-info">
      This preview shows SSS contribution data from the latest payroll run (falls back to sample if none).
    </div>

    <div class="report-preview">
      <div class="report-header">
        <h3>SOCIAL SECURITY SYSTEM (SSS) CONTRIBUTION REPORT</h3>
        <p>Healthcare Hospital Inc. | BIR TIN: 012-345-678</p>
        <p>Period: <?php echo date('F Y'); ?></p>
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
          <value><?php echo date('F Y'); ?></value>
        </div>
      </div>

      <?php
      // Fetch latest payroll_run_id
      $latestRun = $payrollRunEmployee->query("SELECT payroll_run_id FROM payroll_run_employees ORDER BY payroll_run_id DESC LIMIT 1", []);
      $payrollRunId = isset($latestRun[0]['payroll_run_id']) ? (int)$latestRun[0]['payroll_run_id'] : 0;

      $rows = [];
      $totalEmployeeShare = 0.0;
      $totalEmployerShare = 0.0;

      if ($payrollRunId > 0) {
          $recs = $payrollRunEmployee->query("SELECT pre.*, e.first_name, e.last_name, e.employee_code FROM payroll_run_employees pre JOIN employees e ON pre.employee_id = e.employee_id WHERE pre.payroll_run_id = ?", [$payrollRunId]);
          foreach ($recs as $r) {
              $total = floatval($r['sss_contribution'] ?? 0);
              // Prefer explicit split columns if present
              $empShare = null;
              $erShare = null;
              if (isset($r['sss_employee_share'])) $empShare = floatval($r['sss_employee_share']);
              if (isset($r['sss_employer_share'])) $erShare = floatval($r['sss_employer_share']);
              if ($empShare === null && $erShare === null) {
                  // split evenly
                  $empShare = $total / 2.0;
                  $erShare = $total / 2.0;
              } elseif ($empShare === null) {
                  $empShare = max(0, $total - $erShare);
              } elseif ($erShare === null) {
                  $erShare = max(0, $total - $empShare);
              }

              if ($total <= 0) continue;

              $rows[] = [
                  'code' => $r['employee_code'] ?? '',
                  'sss_id' => $r['sss_id'] ?? '',
                  'name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                  'employee_share' => $empShare,
                  'employer_share' => $erShare,
                  'total' => ($empShare + $erShare)
              ];

              $totalEmployeeShare += $empShare;
              $totalEmployerShare += $erShare;
          }
      }

      // If no data, show sample rows
      if (empty($rows)) {
          $rows = [
              ['code' => 'EMP-001', 'sss_id' => '04-1234567-8', 'name' => 'John Doe', 'employee_share' => 203.12, 'employer_share' => 203.12, 'total' => 406.24],
              ['code' => 'EMP-002', 'sss_id' => '04-2345678-9', 'name' => 'Jane Smith', 'employee_share' => 203.12, 'employer_share' => 203.12, 'total' => 406.24],
              ['code' => 'EMP-003', 'sss_id' => '04-3456789-0', 'name' => 'Michael Johnson', 'employee_share' => 203.12, 'employer_share' => 203.12, 'total' => 406.24],
          ];
          foreach ($rows as $r) {
              $totalEmployeeShare += $r['employee_share'];
              $totalEmployerShare += $r['employer_share'];
          }
      }

      $totalContribution = $totalEmployeeShare + $totalEmployerShare;
      ?>

      <table class="report-table">
        <thead>
          <tr>
            <th>Seq #</th>
            <th>SSS ID Number</th>
            <th>Employee Name</th>
            <th style="text-align: right;">Employee Share</th>
            <th style="text-align: right;">Employer Share</th>
            <th style="text-align: right;">Total Contribution</th>
          </tr>
        </thead>
        <tbody>
          <?php $seq = 1; foreach ($rows as $r): ?>
          <tr>
            <td><?php echo $seq++; ?></td>
            <td><?php echo htmlspecialchars($r['sss_id'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td style="text-align: right;">₱<?php echo number_format($r['employee_share'], 2); ?></td>
            <td style="text-align: right;">₱<?php echo number_format($r['employer_share'], 2); ?></td>
            <td style="text-align: right;">₱<?php echo number_format($r['total'], 2); ?></td>
          </tr>
          <?php endforeach; ?>
          <tr style="font-weight: 700; background: #f3f4f6;">
            <td colspan="3" style="text-align: right;">TOTAL:</td>
            <td style="text-align: right;">₱<?php echo number_format($totalEmployeeShare, 2); ?></td>
            <td style="text-align: right;">₱<?php echo number_format($totalEmployerShare, 2); ?></td>
            <td style="text-align: right;">₱<?php echo number_format($totalContribution, 2); ?></td>
          </tr>
        </tbody>
      </table>

      <div class="report-summary">
        <div class="summary-item">
          <label>Total Employees</label>
          <value><?php echo count($rows); ?></value>
        </div>
        <div class="summary-item">
          <label>Total Employee Share</label>
          <value>₱<?php echo number_format($totalEmployeeShare, 2); ?></value>
        </div>
        <div class="summary-item">
          <label>Total Employer Share</label>
          <value>₱<?php echo number_format($totalEmployerShare, 2); ?></value>
        </div>
        <div class="summary-item">
          <label>Total Contribution</label>
          <value>₱<?php echo number_format($totalContribution, 2); ?></value>
        </div>
      </div>

      <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #d1d5db; color: #6b7280; font-size: 11px;">
        <p style="margin: 0.25rem 0;"><strong>Certified by:</strong> Juan dela Cruz, Payroll Officer</p>
        <p style="margin: 0.25rem 0;"><strong>Generated:</strong> <?php echo date('F j, Y g:i A'); ?></p>
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
              <button type="button" onclick="window.downloadGovernmentReport('SSS-202601')" class="btn btn-secondary btn-sm">Download</button>
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
              <button type="button" onclick="window.downloadGovernmentReport('PH-202601')" class="btn btn-secondary btn-sm">Download</button>
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
              <button type="button" onclick="window.downloadGovernmentReport('PAGIBIG-202601')" class="btn btn-secondary btn-sm">Download</button>
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
              <button type="button" onclick="window.downloadGovernmentReport('BIR1601-2025')" class="btn btn-secondary btn-sm">Download</button>
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
              <button type="button" onclick="window.downloadGovernmentReport('BIR2316-2025')" class="btn btn-secondary btn-sm">Download</button>
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
              <button type="button" onclick="window.downloadGovernmentReport('ALPHALIST-2025')" class="btn btn-secondary btn-sm">Download</button>
            </td>
          </tr>
        </tbody>
      </table>
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

window.openGovernmentModal = function(reportType, payrollRunId) {
  // Get report details (period left for server-side preview)
  let period = new Date().toLocaleString('en-US', { month: 'long', year: 'numeric' });

  // Map for different report types (fallback labels)
  const reportMap = {
    'sss': { name: 'SSS Report' },
    'philhealth': { name: 'PhilHealth Report' },
    'pagibig': { name: 'Pag-IBIG Report' },
    'bir1601': { name: 'BIR 1601-C Report' },
    'bir2316': { name: 'BIR 2316 Certificate' },
    'alphalist': { name: 'BIR Alphalist' }
  };

  let url = 'dashboard.php?module=payroll&view=government_reports_compliance&ajax=1&modal=preview&type=' + encodeURIComponent(reportType) + '&period=' + encodeURIComponent(period);
  if (typeof payrollRunId !== 'undefined' && payrollRunId) {
    url += '&payroll_run_id=' + encodeURIComponent(payrollRunId);
  }

  fetch(url)
    .then(response => response.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const modalOverlay = temp.querySelector('.modal-overlay');

      if (modalOverlay) {
        document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
        document.body.appendChild(modalOverlay);
        modalOverlay.classList.add('active');
      }
    })
    .catch(error => console.error('Error loading modal:', error));
};

window.closeGovernmentModal = function() {
  const overlay = document.querySelector('.modal-overlay');
  if (overlay) {
    overlay.classList.remove('active');
    overlay.remove();
  }
};

window.downloadGovernmentReport = function(reportId) {
  // Create download link
  let url = 'dashboard.php?module=payroll&view=government_reports_compliance&ajax=1&modal=download&report_id=' + 
            encodeURIComponent(reportId);
  
  // Trigger download by opening in new tab/window (browser will handle download)
  let link = document.createElement('a');
  link.href = url;
  link.download = true;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Close modal when clicking outside
document.addEventListener('click', function(event) {
  const modal = document.querySelector('.modal-box');
  const overlay = document.querySelector('.modal-overlay');
  if (overlay && event.target === overlay && modal) {
    window.closeGovernmentModal();
  }
});

</script>
