<?php
/**
 * Disbursement and Bank Files Module
 * Generate and manage bank files for payroll fund disbursement
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/Disbursement.php';
require_once __DIR__ . '/../models/PayrollRun.php';

$disbursement = new Disbursement();
$payrollRun = new PayrollRun();

// Check if this is an AJAX modal request
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
$modal = isset($_GET['modal']) ? $_GET['modal'] : null;

// Fetch disbursement data
$allDisbursements = $disbursement->getAll();
$transmitted = $disbursement->getByStatus('transmitted');
$failed = $disbursement->getByStatus('failed');

$statsTotal = count($allDisbursements ?? []);
$statsTransmitted = count($transmitted ?? []);
$statsConfirmed = count(array_filter($allDisbursements ?? [], fn($d) => $d['status'] === 'confirmed'));
$statsPending = count(array_filter($allDisbursements ?? [], fn($d) => $d['status'] === 'pending'));
$statsFailed = count($failed ?? []);

// Handle AJAX modal request
if ($isAjax && $modal === 'view'):
    $batchRef = isset($_GET['batch_ref']) ? $_GET['batch_ref'] : '';
    
    // Sample batch data - in production, fetch from database
    $batchData = [
        'BATCH-2026-02-001' => ['period' => 'Feb 2026 Period 1', 'date' => 'February 8, 2026 10:30 AM', 'bank' => 'BDO Bank', 'format' => 'TXT', 'amount' => '58,355.00', 'records' => 8, 'status' => 'transmitted'],
        'BATCH-2026-01-02' => ['period' => 'Jan 2026 Period 2', 'date' => 'February 1, 2026 09:15 AM', 'bank' => 'Metrobank', 'format' => 'CSV', 'amount' => '54,230.00', 'records' => 8, 'status' => 'confirmed'],
        'BATCH-2026-01-01' => ['period' => 'Jan 2026 Period 1', 'date' => 'January 24, 2026 11:00 AM', 'bank' => 'BDO Bank', 'format' => 'TXT', 'amount' => '57,890.00', 'records' => 8, 'status' => 'confirmed'],
    ];
    
    $batch = $batchData[$batchRef] ?? null;
    
    header('Content-Type: text/html');
    ob_start();
    ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Bank File Details</h3>
                <button type="button" class="modal-close-btn" onclick="window.closeDisbursementModal()">✕</button>
            </div>
            <div class="modal-content">
                <?php if ($batch): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Batch Reference</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($batchRef); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Payroll Period</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($batch['period']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Bank</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($batch['bank']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Format</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($batch['format']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Total Amount</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;">₱<?php echo htmlspecialchars($batch['amount']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Records</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($batch['records']); ?></div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                            <label style="font-size: 12px; color: #6b7280; font-weight: 500; display: block; margin-bottom: 0.5rem;">File Preview</label>
                            <pre style="background: #f3f4f6; padding: 0.75rem; border-radius: 4px; font-size: 11px; max-height: 200px; overflow-y: auto; color: #1f2937;">101,2026020100000058355.00,BDO,20260222,<?php echo htmlspecialchars($batchRef); ?>     ,,,,,,
521,20260222,123456789,1234567890,JOHN DOE,,,7650.00,PAYROLL-FEB1,,,
522,20260222,123456790,9876543210,JANE SMITH,,,7300.00,PAYROLL-FEB1,,,
523,20260222,123456791,5555555555,MICHAEL JOHNSON,,,8050.00,PAYROLL-FEB1,,,
524,20260222,123456792,4444444444,SARAH WILLIAMS,,,6400.00,PAYROLL-FEB1,,,
525,20260222,123456793,3333333333,ROBERT BROWN,,,6250.00,PAYROLL-FEB1,,,
526,20260222,123456794,2222222222,EMILY DAVIS,,,7425.00,PAYROLL-FEB1,,,
527,20260222,123456795,1111111111,DAVID MARTINEZ,,,6470.00,PAYROLL-FEB1,,,
528,20260222,123456796,0000000000,JESSICA WILSON,,,8810.00,PAYROLL-FEB1,,,
900,8,58355.00,<?php echo htmlspecialchars($batchRef); ?>,20260222,,,,,,,</pre>
                        </div>
                        
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #dbeafe; border-radius: 4px; border-left: 4px solid #3b82f6;">
                            <div style="color: #1e40af; font-size: 12px;">
                                <strong>Status:</strong> <span style="text-transform: uppercase; font-weight: 600;"><?php echo htmlspecialchars($batch['status']); ?></span>
                            </div>
                            <div style="color: #1e40af; font-size: 12px; margin-top: 0.5rem;">
                                <strong>Generated:</strong> <?php echo htmlspecialchars($batch['date']); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="padding: 1rem; background: #fee2e2; border-radius: 4px; color: #991b1b;">
                        <strong>Error:</strong> Batch file not found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
    exit;
endif;
?>

<style>
  .disbursement-container {
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

  .btn-primary:disabled {
    background: #d1d5db;
    color: #9ca3af;
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

  .badge-generated {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-transmitted {
    background: #dbeafe;
    color: #1e40af;
  }

  .badge-confirmed {
    background: #d1f0c5;
    color: #166534;
  }

  .badge-failed {
    background: #fee2e2;
    color: #991b1b;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
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

  .summary-card.success {
    border-left-color: #22c55e;
  }

  .summary-card.danger {
    border-left-color: #ef4444;
  }

  .summary-card.warning {
    border-left-color: #f59e0b;
  }

  .code-block {
    background: #1f2937;
    color: #e5e7eb;
    padding: 1rem;
    border-radius: 4px;
    overflow-x: auto;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.5;
  }

  .bank-file-preview {
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.6;
    max-height: 400px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-break: break-all;
  }

  .bank-info-box {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    border-left: 4px solid #3b82f6;
  }

  .bank-info-box h5 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 13px;
    font-weight: 600;
  }

  .bank-info-box p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .exception-card {
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-radius: 4px;
    padding: 1rem;
    margin-bottom: 1rem;
  }

  .exception-card h5 {
    margin: 0 0 0.5rem 0;
    color: #92400e;
    font-size: 13px;
    font-weight: 600;
  }

  .exception-card p {
    margin: 0.25rem 0;
    color: #78350f;
    font-size: 12px;
  }

  .exception-card .amount {
    color: #b45309;
    font-weight: 600;
  }

  .tab-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
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
  }

  .tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
  }

  .tab:hover {
    color: #373151;
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

  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
  }

  .modal-box {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 650px;
    max-height: 90vh;
    width: 100%;
    display: flex;
    flex-direction: column;
    animation: slideIn 0.3s ease-out;
  }

  @keyframes slideIn {
    from {
      transform: translateY(-50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
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
    color: #1f2937;
    font-size: 16px;
  }

  .modal-close-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
  }

  .modal-close-btn:hover {
    background: #f3f4f6;
    color: #1f2937;
  }

  .modal-content {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
  }

  .modal-overlay.active {
    display: flex !important;
  }
</style>

<div class="disbursement-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Disbursement & Bank Files</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Generate bank files for payroll fund disbursement. Supports multiple bank formats, exception handling (cash/cheque payouts), and comprehensive audit trail.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Features:</strong> Bank file generation (TXT/CSV/XML), batch reference management, exception handling, transmission tracking, and re-generation audit controls.
    </div>
  </div>

  <!-- Generate Bank Files -->
  <div class="section">
    <h3 class="section-header">✉️ Generate Bank File</h3>

    <form method="POST" action="../disbursement_banker_files_handler.php">
      <div class="form-section">
        <h4>Select Payroll Run</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Approved Payroll Run <span style="color: #ef4444;">*</span></label>
            <select name="payroll_run" required>
              <option value="">-- Select Payroll --</option>
              <option value="PAYROLL-2026-02-01" selected>February 2026 Period 1 (Feb 1-15) - Approved</option>
              <option value="PAYROLL-2026-01-16">January 2026 Period 2 (Jan 16-31) - Approved</option>
              <option value="PAYROLL-2026-01-01">January 2026 Period 1 (Jan 1-15) - Approved</option>
            </select>
            <small>Select an approved payroll run to generate disbursement file</small>
          </div>
          <div class="form-group">
            <label>Disbursement Date</label>
            <input type="date" name="disbursement_date" value="2026-02-22" readonly style="background: #f3f4f6;">
          </div>
        </div>
      </div>

      <div class="form-section">
        <h4>Bank File Configuration</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Bank Account <span style="color: #ef4444;">*</span></label>
            <select name="bank_account" required>
              <option value="">-- Select Bank --</option>
              <option value="BDO" selected>BDO Bank (Account: 123-456-789-0)</option>
              <option value="METROBANK">Metrobank (Account: 987-654-321-0)</option>
              <option value="BPI">BPI (Account: 555-123-456-0)</option>
            </select>
            <small>Payroll holding account for disbursement</small>
          </div>
          <div class="form-group">
            <label>File Format <span style="color: #ef4444;">*</span></label>
            <select name="file_format" required>
              <option value="">-- Select Format --</option>
              <option value="TXT" selected>TXT Format (Bank Standard)</option>
              <option value="CSV">CSV Format (Excel Compatible)</option>
              <option value="XML">XML Format (Structured)</option>
            </select>
            <small>Select format based on bank requirements</small>
          </div>
          <div class="form-group">
            <label>Batch Reference</label>
            <input type="text" name="batch_reference" value="BATCH-2026-02-001" readonly style="background: #f3f4f6;">
            <small>Auto-generated unique identifier</small>
          </div>
        </div>
      </div>

      <div class="alert alert-info">
        Selected payroll: 8 employees, Total net payable ₱58,355.00. File size estimate: ~2.5 KB. Bank processing time: 1-2 business days.
      </div>

      <div class="btn-group">
        <button type="submit" name="action" value="preview" class="btn btn-secondary">Preview File</button>
        <button type="submit" name="action" value="generate" class="btn btn-primary">Generate & Download</button>
      </div>
    </form>
  </div>

  <!-- Bank File Preview -->
  <div class="section">
    <h3 class="section-header">👁️ Bank File Preview - TXT Format</h3>

    <div class="alert alert-info">
      This is a preview of the TXT bank file that will be generated. Review for accuracy before final generation.
    </div>

    <div class="bank-file-preview">101,2026020100000058355.00,BDO,20260222,BATCH-2026-02-001     ,,,,,,
521,20260222,123456789,1234567890,JOHN DOE,,,7650.00,PAYROLL-FEB1,,,
522,20260222,123456790,9876543210,JANE SMITH,,,7300.00,PAYROLL-FEB1,,,
523,20260222,123456791,5555555555,MICHAEL JOHNSON,,,8050.00,PAYROLL-FEB1,,,
524,20260222,123456792,4444444444,SARAH WILLIAMS,,,6400.00,PAYROLL-FEB1,,,
525,20260222,123456793,3333333333,ROBERT BROWN,,,6250.00,PAYROLL-FEB1,,,
526,20260222,123456794,2222222222,EMILY DAVIS,,,7425.00,PAYROLL-FEB1,,,
527,20260222,123456795,1111111111,DAVID MARTINEZ,,,6470.00,PAYROLL-FEB1,,,
528,20260222,123456796,0000000000,JESSICA WILSON,,,8810.00,PAYROLL-FEB1,,,
900,8,58355.00,BATCH-2026-02-001,20260222,,,,,,,
    </div>

    <div style="margin-top: 1rem; padding: 1rem; background: #f9fafb; border-radius: 4px;">
      <h5 style="margin: 0 0 0.5rem 0; color: #1f2937;">File Structure Explanation</h5>
      <ul style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 12px; line-height: 1.8;">
        <li><strong>Line 1 (101):</strong> Header - File type (101), Amount, Bank, Date, Batch Reference</li>
        <li><strong>Lines 2-9 (52x):</strong> Detail records - Account info, Employee name, Amount, Reference</li>
        <li><strong>Line 10 (900):</strong> Trailer - Record count (8), Total amount, Batch reference, Date</li>
      </ul>
    </div>
  </div>

  <!-- Bank Files Management -->
  <div class="section">
    <h3 class="section-header">📁 Bank Files History</h3>

    <div class="summary-cards">
      <div class="summary-card">
        <label>Total Generated</label>
        <div class="value"><?php echo (int) $statsTotal; ?></div>
      </div>
      <div class="summary-card success">
        <label>Transmitted</label>
        <div class="value"><?php echo (int) $statsTransmitted; ?></div>
      </div>
      <div class="summary-card success">
        <label>Confirmed</label>
        <div class="value"><?php echo (int) $statsConfirmed; ?></div>
      </div>
      <div class="summary-card warning">
        <label>Pending</label>
        <div class="value"><?php echo (int) $statsPending; ?></div>
      </div>
      <div class="summary-card danger">
        <label>Failed/Exceptions</label>
        <div class="value"><?php echo (int) $statsFailed; ?></div>
      </div>
    </div>

    <div class="tab-container">
      <button class="tab active" onclick="switchTab(event, 'all-files')">All Files</button>
      <button class="tab" onclick="switchTab(event, 'transmitted-files')">Transmitted</button>
      <button class="tab" onclick="switchTab(event, 'failed-files')">Failed/Exceptions</button>
    </div>

    <!-- All Files Tab -->
    <div id="all-files" class="tab-content active">
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Batch Reference</th>
              <th>Payroll Period</th>
              <th>Generated Date</th>
              <th>Bank</th>
              <th>Format</th>
              <th>Total Amount</th>
              <th>Records</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>BATCH-2026-02-001</td>
              <td>Feb 2026 Period 1</td>
              <td>February 8, 2026 10:30 AM</td>
              <td>BDO Bank</td>
              <td>TXT</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱58,355.00</td>
              <td style="text-align: center;">8</td>
              <td><span class="badge badge-transmitted">Transmitted</span></td>
              <td>
                <button type="button" onclick="window.openDisbursementModal('BATCH-2026-02-001')" class="btn btn-secondary btn-sm">View</button>
              </td>
            </tr>
            <tr>
              <td>BATCH-2026-01-02</td>
              <td>Jan 2026 Period 2</td>
              <td>February 1, 2026 09:15 AM</td>
              <td>Metrobank</td>
              <td>CSV</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱54,230.00</td>
              <td style="text-align: center;">8</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
              <td>
                <button type="button" onclick="window.openDisbursementModal('BATCH-2026-01-02')" class="btn btn-secondary btn-sm">View</button>
              </td>
            </tr>
            <tr>
              <td>BATCH-2026-01-01</td>
              <td>Jan 2026 Period 1</td>
              <td>January 24, 2026 11:00 AM</td>
              <td>BDO Bank</td>
              <td>TXT</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱57,890.00</td>
              <td style="text-align: center;">8</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
              <td>
                <button type="button" onclick="window.openDisbursementModal('BATCH-2026-01-01')" class="btn btn-secondary btn-sm">View</button>
              </td>
            </tr>
            <tr style="background: #fee2e2;">
              <td>BATCH-2025-12-02</td>
              <td>Dec 2025 Period 2</td>
              <td>January 10, 2026 02:30 PM</td>
              <td>BPI</td>
              <td>CSV</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱62,450.00</td>
              <td style="text-align: center;">8</td>
              <td><span class="badge badge-failed">Failed</span></td>
              <td>
                <button type="button" onclick="window.openDisbursementModal('BATCH-2025-12-02')" class="btn btn-secondary btn-sm">View</button>
              </td>
            </tr>
            <tr>
              <td>BATCH-2025-12-01</td>
              <td>Dec 2025 Period 1</td>
              <td>December 23, 2025 10:15 AM</td>
              <td>Metrobank</td>
              <td>TXT</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱59,100.00</td>
              <td style="text-align: center;">8</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
              <td>
                <button type="button" onclick="window.openDisbursementModal('BATCH-2025-12-01')" class="btn btn-secondary btn-sm">View</button>
              </td>
            </tr>
            <tr>
              <td>BATCH-2025-11-02</td>
              <td>Nov 2025 Period 2</td>
              <td>December 8, 2025 09:45 AM</td>
              <td>BDO Bank</td>
              <td>TXT</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱56,780.00</td>
              <td style="text-align: center;">8</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
              <td>
                <button type="button" onclick="window.openDisbursementModal('BATCH-2025-11-02')" class="btn btn-secondary btn-sm">View</button>
              </td>
            </tr>
            <tr>
              <td>BATCH-2025-11-01</td>
              <td>Nov 2025 Period 1</td>
              <td>November 24, 2025 10:30 AM</td>
              <td>BPI</td>
              <td>CSV</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱55,220.00</td>
              <td style="text-align: center;">8</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
              <td>
                <button type="button" onclick="window.openDisbursementModal('BATCH-2025-11-01')" class="btn btn-secondary btn-sm">View</button>
                </form>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Transmitted Files Tab -->
    <div id="transmitted-files" class="tab-content">
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Batch Reference</th>
              <th>Transmitted Date</th>
              <th>Bank Confirmation #</th>
              <th>Confirmation Date</th>
              <th>Total Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>BATCH-2026-02-001</td>
              <td>February 8, 2026 10:43 AM</td>
              <td>BDO-TXN-20260208-001</td>
              <td>February 8, 2026 10:45 AM</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱58,355.00</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
            </tr>
            <tr>
              <td>BATCH-2026-01-02</td>
              <td>February 1, 2026 09:30 AM</td>
              <td>MTB-TXN-20260201-045</td>
              <td>February 1, 2026 10:00 AM</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱54,230.00</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
            </tr>
            <tr>
              <td>BATCH-2026-01-01</td>
              <td>January 24, 2026 11:15 AM</td>
              <td>BDO-TXN-20260124-523</td>
              <td>January 24, 2026 11:30 AM</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱57,890.00</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
            </tr>
            <tr>
              <td>BATCH-2025-12-01</td>
              <td>December 23, 2025 10:30 AM</td>
              <td>MTB-TXN-20251223-089</td>
              <td>December 23, 2025 11:15 AM</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱59,100.00</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
            </tr>
            <tr>
              <td>BATCH-2025-11-02</td>
              <td>December 8, 2025 10:00 AM</td>
              <td>BDO-TXN-20251208-456</td>
              <td>December 8, 2025 10:15 AM</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱56,780.00</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
            </tr>
            <tr>
              <td>BATCH-2025-11-01</td>
              <td>November 24, 2025 10:45 AM</td>
              <td>BPI-TXN-20251124-234</td>
              <td>November 24, 2025 11:00 AM</td>
              <td style="text-align: right; font-family: 'Courier New', monospace;">₱55,220.00</td>
              <td><span class="badge badge-confirmed">Confirmed</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Failed Files Tab -->
    <div id="failed-files" class="tab-content">
      <div class="exception-card">
        <h5>❌ BATCH-2025-12-02 - File Rejected (Transmission Failed)</h5>
        <p>Batch Reference: BATCH-2025-12-02</p>
        <p>Payroll: Dec 2025 Period 2</p>
        <p>Generated: January 10, 2026 02:30 PM</p>
        <p class="amount">Amount: ₱62,450.00 (8 employees)</p>
        <p><strong>Error Code:</strong> BPI-ERR-0045</p>
        <p><strong>Error Message:</strong> "Invalid account number format in detail record 3 (EMP-003). Expected 12 digits, received 11."</p>
        <p><strong>Resolution:</strong> Account number for Michael Johnson (EMP-003) needs correction. Contact Employee Payroll Profile to update account information.</p>
        <div style="margin-top: 1rem;">
          <button type="submit" class="btn btn-primary btn-sm" onclick="alert('This requires admin approval for re-generation')">Regenerate (Requires Approval)</button>
        </div>
      </div>

      <div style="margin-bottom: 1rem; padding: 1rem; background: #f9fafb; border-radius: 4px;">
        <h5 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 13px; font-weight: 600;">How to Resolve Failed Batches</h5>
        <ol style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 12px; line-height: 1.8;">
          <li>Review error message to identify problematic employee records</li>
          <li>Update employee information in <strong>Employee Payroll Profile</strong> (e.g., bank account numbers, employee names)</li>
          <li>Run batch regeneration (requires admin approval)</li>
          <li>Resubmit to bank for processing</li>
        </ol>
      </div>
    </div>
  </div>

  <!-- Exception Handling -->
  <div class="section">
    <h3 class="section-header">⚠️ Exception Handling & Special Payouts</h3>

    <div class="form-section">
      <h4>Manual Payment Exceptions</h4>
      <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Handle employees not included in bank file due to special disbursement methods (cash, cheque, etc.)</p>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Employee</th>
              <th>Payroll Period</th>
              <th>Net Pay</th>
              <th>Payout Method</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>None Currently</td>
              <td>-</td>
              <td>-</td>
              <td>-</td>
              <td><span class="badge badge-transmitted">All via Bank File</span></td>
              <td>-</td>
            </tr>
          </tbody>
        </table>
      </div>

      <form method="POST" action="../disbursement_banker_files_handler.php">
        <div class="form-row">
          <div class="form-group">
            <label>Employee Name <span style="color: #ef4444;">*</span></label>
            <select name="exception_employee" required>
              <option value="">-- Select Employee --</option>
              <option value="EMP-001">John Doe (EMP-001) - ₱7,650.00</option>
              <option value="EMP-002">Jane Smith (EMP-002) - ₱7,300.00</option>
              <option value="EMP-003">Michael Johnson (EMP-003) - ₱8,050.00</option>
              <option value="EMP-004">Sarah Williams (EMP-004) - ₱6,400.00</option>
              <option value="EMP-005">Robert Brown (EMP-005) - ₱6,250.00</option>
              <option value="EMP-006">Emily Davis (EMP-006) - ₱7,425.00</option>
              <option value="EMP-007">David Martinez (EMP-007) - ₱6,470.00</option>
              <option value="EMP-008">Jessica Wilson (EMP-008) - ₱8,810.00</option>
            </select>
          </div>
          <div class="form-group">
            <label>Payout Method <span style="color: #ef4444;">*</span></label>
            <select name="payout_method" required>
              <option value="">-- Select Method --</option>
              <option value="CASH">Cash Payout</option>
              <option value="CHEQUE">Cheque Payout</option>
              <option value="ALTERNATIVE">Alternative Bank Account</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Amount <span style="color: #ef4444;">*</span></label>
            <input type="number" name="exception_amount" placeholder="0.00" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label>Remarks</label>
            <input type="text" name="exception_remarks" placeholder="Reason for special payout">
          </div>
        </div>

        <div class="btn-group">
          <button type="submit" name="action" value="add_exception" class="btn btn-primary">Add Exception</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Failed Transfer Reconciliation -->
  <div class="section">
    <h3 class="section-header">🔄 Failed Transfer Reconciliation</h3>

    <div class="alert alert-info">
      Fund transfers that fail at the bank are logged here with reconciliation status. Finance team must investigate and resolve.
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Batch Reference</th>
            <th>Bank Account</th>
            <th>Amount</th>
            <th>Failure Date</th>
            <th>Failure Reason</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Sarah Williams (EMP-004)</td>
            <td>BATCH-2026-01-01</td>
            <td>•••••••...4592</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱6,400.00</td>
            <td>January 24, 2026 02:30 PM</td>
            <td>Insufficient funds in receiving account (Closed)</td>
            <td><span class="badge badge-pending">Pending Reconciliation</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="employee_id" value="EMP-004">
                <button type="submit" class="btn btn-secondary btn-sm">Reconcile</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>Robert Brown (EMP-005)</td>
            <td>BATCH-2025-12-02</td>
            <td>•••••••...3333</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱6,250.00</td>
            <td>January 10, 2026 11:15 AM</td>
            <td>Account number mismatch</td>
            <td><span class="badge badge-pending">Pending Reconciliation</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="employee_id" value="EMP-005">
                <button type="submit" class="btn btn-secondary btn-sm">Reconcile</button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <form method="POST" action="../disbursement_banker_files_handler.php" style="margin-top: 2rem;">
      <div class="form-section">
        <h4>Reconcile Failed Transfer</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Employee</label>
            <input type="text" value="Sarah Williams (EMP-004) - ₱6,400.00" readonly style="background: #f3f4f6;">
          </div>
          <div class="form-group">
            <label>Resolution Method <span style="color: #ef4444;">*</span></label>
            <select name="resolution_method" required>
              <option value="">-- Select --</option>
              <option value="RETRY">Retry Transfer to New Account</option>
              <option value="MANUAL">Manual Payout (Cash/Cheque)</option>
              <option value="HOLD">Hold for Employee Inquiry</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>New Bank Account (if retrying)</label>
            <input type="text" name="new_bank_account" placeholder="123-456-789-0">
          </div>
          <div class="form-group">
            <label>Reconciliation Notes</label>
            <textarea name="reconciliation_notes" placeholder="Document resolution steps taken" style="resize: vertical; min-height: 80px;"></textarea>
          </div>
        </div>

        <div class="btn-group">
          <button type="submit" class="btn btn-primary">Submit Reconciliation</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Re-generation & Audit Trail -->
  <div class="section">
    <h3 class="section-header">🔐 Bank File Re-generation (Admin Approval Required)</h3>

    <div class="alert alert-warning">
      <strong>⚠️ Important:</strong> Re-generating bank files requires Finance Manager approval. Each re-generation is logged with reason and approver details.
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Batch Reference</th>
            <th>Original Generated</th>
            <th>Re-generation Date</th>
            <th>Reason</th>
            <th>Approved By</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>BATCH-2025-12-02</td>
            <td>January 10, 2026 02:30 PM</td>
            <td>January 15, 2026 03:00 PM</td>
            <td>File rejected by bank - Account number error corrected</td>
            <td>Maria Garcia (Finance Manager)</td>
            <td><span class="badge badge-generated">Re-generated</span></td>
          </tr>
          <tr>
            <td>BATCH-2025-11-01</td>
            <td>November 24, 2025 10:30 AM</td>
            <td>December 1, 2025 09:15 AM</td>
            <td>Employee request - Change to alternative bank account</td>
            <td>Juan Santos (Finance Manager)</td>
            <td><span class="badge badge-generated">Re-generated</span></td>
          </tr>
        </tbody>
      </table>
    </div>

    <form method="POST" action="../disbursement_banker_files_handler.php">
      <div class="form-section">
        <h4>Request Bank File Re-generation</h4>
        <div class="form-row">
          <div class="form-group">
            <label>Batch Reference <span style="color: #ef4444;">*</span></label>
            <select name="regen_batch" required>
              <option value="">-- Select Batch --</option>
              <option value="BATCH-2026-02-001">BATCH-2026-02-001 (Generated: Feb 8, 2026)</option>
              <option value="BATCH-2026-01-02">BATCH-2026-01-02 (Generated: Feb 1, 2026)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Re-generation Reason <span style="color: #ef4444;">*</span></label>
            <select name="regen_reason" required>
              <option value="">-- Select Reason --</option>
              <option value="CORRECTION">Employee data correction (account, name, etc.)</option>
              <option value="EMPLOYEE_REQUEST">Employee request (account change, etc.)</option>
              <option value="BANK_REJECTION">Bank rejected file - error fixed</option>
              <option value="SYSTEM_ERROR">System error in original file</option>
              <option value="OTHER">Other (please explain)</option>
            </select>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Detailed Explanation <span style="color: #ef4444;">*</span></label>
            <textarea name="regen_explanation" placeholder="Explain why re-generation is needed. Finance Manager will review." style="min-height: 100px; resize: vertical;"></textarea>
          </div>
        </div>

        <div class="alert alert-info">
          <strong>ℹ️ Approval Process:</strong> Your re-generation request will be reviewed by Finance Manager. You'll receive approval confirmation via email. Average approval time: 2-4 hours.
        </div>

        <div class="btn-group">
          <button type="submit" class="btn btn-primary">Submit Re-generation Request</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Rules & Compliance -->
  <div class="section">
    <h3 class="section-header">📋 Disbursement Rules & Compliance</h3>

    <div class="alert alert-success">
      <strong>✓ Single Bank File Per Payroll:</strong> Only one approved bank file can be generated per payroll run. Re-generation requires administrator approval.
    </div>

    <div class="alert alert-warning">
      <strong>⚠️ Critical Compliance Rules:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>One file per payroll run:</strong> Cannot generate multiple files for same payroll period</li>
        <li><strong>Bank file immutable after transmission:</strong> Cannot edit or regenerate without explicit approval</li>
        <li><strong>Exception tracking:</strong> All cash/cheque payouts must be logged and reconciled</li>
        <li><strong>Failed transfer resolution:</strong> Failures must be investigated and resolved within 5 business days</li>
        <li><strong>Audit trail required:</strong> All file generations, transmissions, and re-generations are logged</li>
        <li><strong>Security:</strong> Bank files contain sensitive employee banking information - handle with care</li>
      </ul>
    </div>

    <div class="alert alert-info">
      <strong>ℹ️ Supported Bank File Formats:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>TXT Format:</strong> Standard fixed-width text format compatible with most Philippine banks</li>
        <li><strong>CSV Format:</strong> Comma-separated values, Excel-compatible for manual review</li>
        <li><strong>XML Format:</strong> Structured XML for enterprise banking systems</li>
      </ul>
    </div>

    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 0;">
      <h4 style="margin: 0 0 1rem 0; color: #1f2937;">Disbursement Status Definitions</h4>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div>
          <p style="margin: 0 0 0.5rem 0; font-size: 12px; font-weight: 600; color: #1f2937;">Generated</p>
          <p style="margin: 0; font-size: 12px; color: #6b7280;">File created and ready for transmission. Not yet sent to bank.</p>
        </div>
        <div>
          <p style="margin: 0 0 0.5rem 0; font-size: 12px; font-weight: 600; color: #1f2937;">Transmitted</p>
          <p style="margin: 0; font-size: 12px; color: #6b7280;">File sent to bank. Awaiting bank confirmation of processing.</p>
        </div>
        <div>
          <p style="margin: 0 0 0.5rem 0; font-size: 12px; font-weight: 600; color: #1f2937;">Confirmed</p>
          <p style="margin: 0; font-size: 12px; color: #6b7280;">Bank confirmed receipt and successfully processed all transfers.</p>
        </div>
        <div>
          <p style="margin: 0 0 0.5rem 0; font-size: 12px; font-weight: 600; color: #1f2937;">Failed</p>
          <p style="margin: 0; font-size: 12px; color: #6b7280;">Bank rejected file. Errors must be corrected before re-submission.</p>
        </div>
      </div>
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

// Modal functions
window.openDisbursementModal = function(batchRef) {
  // Fetch modal content via AJAX without page refresh
  let url = 'dashboard.php?module=payroll&view=disbursement_bank_files&ajax=1&modal=view&batch_ref=' + encodeURIComponent(batchRef);
  
  fetch(url)
    .then(response => response.text())
    .then(html => {
      // Create a temporary container to parse the response
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const modalOverlay = temp.querySelector('.modal-overlay');
      
      if (modalOverlay) {
        // Remove old modals if any
        document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
        // Add new modal to page
        document.body.appendChild(modalOverlay);
        // Add the active class to display modal
        modalOverlay.classList.add('active');
      } else {
        console.error('Modal overlay not found in response');
        console.log('Response HTML:', html.substring(0, 500));
      }
    })
    .catch(error => console.error('Error loading modal:', error));
};

window.closeDisbursementModal = function() {
  const overlay = document.querySelector('.modal-overlay');
  if (overlay) {
    overlay.classList.remove('active');
    overlay.remove();
  }
};

// Close modal when clicking outside
document.addEventListener('click', function(event) {
  const modal = document.querySelector('.modal-box');
  const overlay = document.querySelector('.modal-overlay');
  if (overlay && event.target === overlay && modal) {
    window.closeDisbursementModal();
  }
});
</script>
