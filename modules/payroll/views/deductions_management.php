<?php
/**
 * Deductions Management Module
 * Compute statutory and other deductions, apply them correctly per setup tables
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
require_once __DIR__ . '/../models/PayrollModel.php';

// Load controller data
require_once __DIR__ . '/../controllers/PayrollController.php';

// Check for modal request from query string
$modal = $_GET['modal'] ?? null;
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department = isset($_GET['department']) ? trim($_GET['department']) : '';
$deduction_status = isset($_GET['deduction_status']) ? trim($_GET['deduction_status']) : '';

// Get data from database instead of controller
$db = new Database();
$conn = $db->connect();

// Validate status filter - only allow valid values AND check if data exists
$valid_statuses = ['active', 'processed', 'pending', 'calculated'];  
if (!empty($deduction_status) && in_array($deduction_status, $valid_statuses)) {
    // Check if there's actual data with this status
    $statusCheck = $conn->prepare("SELECT COUNT(*) FROM employee_deductions WHERE status = ?");
    $statusCheck->execute([$deduction_status]);
    if ($statusCheck->fetch(PDO::FETCH_COLUMN) == 0) {
        $deduction_status = ''; // No data with this status, reset to default
    }
} else {
    $deduction_status = ''; // Invalid status value
}

// Validate department filter - only allow departments that exist
if (!empty($department)) {
    $deptStmt = $conn->prepare("SELECT COUNT(*) FROM departments WHERE department_name = ?");
    $deptStmt->execute([$department]);
    if ($deptStmt->fetch(PDO::FETCH_COLUMN) == 0) {
        $department = ''; // Reset invalid department
    }
}

// Build WHERE clause for filters
$whereConditions = [];
$params = [];

// Department filter
if (!empty($department)) {
    $whereConditions[] = "d.department_name = ?";
    $params[] = $department;
}

// Deduction status filter
if (!empty($deduction_status)) {
    $whereConditions[] = "ed.status = ?";
    $params[] = $deduction_status;
} else {
    // Default: show active and processed
    $whereConditions[] = "ed.status IN ('active', 'processed')";
}

// Search filter (by employee code, first name, or last name)
if (!empty($search)) {
    $whereConditions[] = "(e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Fetch deductions with filters
$sql = "
    SELECT 
        ed.id,
        ed.employee_id,
        ed.deduction_code,
        ed.deduction_type,
        ed.amount,
        ed.status,
        ed.is_mandatory,
        e.employee_code,
        e.first_name,
        e.last_name,
        es.basic_rate as gross_pay,
        d.department_name
    FROM employee_deductions ed
    JOIN employees e ON ed.employee_id = e.employee_id
    LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
    LEFT JOIN departments d ON e.department_id = d.department_id
    {$whereClause}
    ORDER BY ed.created_at DESC
";

$deductionsQuery = $conn->prepare($sql);
$deductionsQuery->execute($params);
$deductions = $deductionsQuery->fetchAll(PDO::FETCH_ASSOC);

// Calculate summary statistics (without filters for overall totals)
$statutoryResult = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total
    FROM employee_deductions
    WHERE is_mandatory = 1 AND status IN ('active', 'processed')
")->fetch(PDO::FETCH_ASSOC);
$totalStatutory = (float) $statutoryResult['total'];

$otherResult = $conn->query("
    SELECT COALESCE(SUM(amount), 0) as total
    FROM employee_deductions
    WHERE is_mandatory = 0 AND status IN ('active', 'processed')
")->fetch(PDO::FETCH_ASSOC);
$totalOther = (float) $otherResult['total'];

$totalDeductions = $totalStatutory + $totalOther;

$pendingResult = $conn->query("
    SELECT COUNT(*) as count
    FROM employee_deductions
    WHERE status = 'pending'
")->fetch(PDO::FETCH_ASSOC);
$pendingApprovals = (int) $pendingResult['count'];

$departments = [];
$filters = [];

// For modal view, get detailed deduction breakdown
$deductionBreakdown = null;
$employeeInfo = null;
if ($modal === 'view' && $employee_id) {
    $deductionBreakdownStmt = $conn->prepare("
        SELECT 
            ed.id,
            ed.deduction_code,
            ed.deduction_type,
            ed.amount,
            ed.status,
            ed.is_mandatory,
            ed.frequency,
            ed.notes,
            ed.created_at
        FROM employee_deductions
        WHERE employee_id = ?
        ORDER BY ed.created_at DESC
    ");
    $deductionBreakdownStmt->execute([$employee_id]);
    $deductionBreakdown = $deductionBreakdownStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $employeeInfoStmt = $conn->prepare("
        SELECT 
            e.employee_id,
            e.employee_code,
            e.first_name,
            e.last_name,
            d.department_name,
            j.job_title,
            es.basic_rate
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.department_id
        LEFT JOIN positions j ON e.job_title_id = j.position_id
        LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
        WHERE e.employee_id = ?
    ");
    $employeeInfoStmt->execute([$employee_id]);
    $employeeInfo = $employeeInfoStmt->fetch(PDO::FETCH_ASSOC);
}

// CSS Styles - only output for non-AJAX requests
if (!($isAjax && $modal)):
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

  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    padding: 1rem;
  }

  .modal-overlay.active {
    display: flex;
  }

  .modal-box {
    background: white;
    width: 100%;
    max-width: 650px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
  }

  .modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 18px;
    font-weight: 600;
  }

  .modal-close-btn {
    background: #e5e7eb;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 20px;
    font-weight: bold;
    color: #6b7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .modal-close-btn:hover {
    background: #d1d5db;
    color: #1f2937;
  }

  .modal-content {
    color: #374151;
  }

  .modal-section {
    margin-bottom: 1.5rem;
  }

  .modal-section-title {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .modal-formula {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 6px;
    margin-top: 1rem;
  }

  .modal-formula-title {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
  }

  .modal-formula-text {
    color: #6b7280;
    font-size: 12px;
    font-family: 'Courier New', monospace;
    line-height: 1.6;
  }

  .modal-footer {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
  }

  .modal-footer a {
    text-decoration: none;
    color: #3b82f6;
    font-weight: 500;
    font-size: 13px;
  }

  .modal-footer a:hover {
    text-decoration: underline;
  }

  /* Form Modal Styles */
  .modal-form-section {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #3b82f6;
  }

  .modal-form-section h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .modal-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1rem;
  }

  .modal-form-row.full {
    grid-template-columns: 1fr;
  }

  .modal-form-group input,
  .modal-form-group select,
  .modal-form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
  }

  .modal-form-group small {
    color: #6b7280;
    font-size: 12px;
    margin-top: 0.25rem;
    display: block;
  }

  .modal-btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    justify-content: center;
  }
</style>

<script>
  window.openDeductionsModal = function(empIdOrMode) {
    // Fetch modal content via AJAX without page refresh
    let url = '<?= BASE_URL ?>dashboard.php?module=payroll&view=deductions_management&ajax=1';
    if (empIdOrMode === 'add') {
      url += '&modal=add';
    } else {
      url += '&modal=view&employee_id=' + empIdOrMode;
    }
    
    console.log('Fetching modal from:', url);
    
    fetch(url)
      .then(response => response.text())
      .then(html => {
        console.log('Response received, length:', html.length);
        // Create a temporary container to parse the response
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const modalOverlay = temp.querySelector('.modal-overlay');
        
        if (modalOverlay) {
          console.log('Modal overlay found');
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

  window.closeDeductionsModal = function() {
    // Remove all modal overlays
    document.querySelectorAll('.modal-overlay').forEach(m => {
      m.classList.remove('active');
      setTimeout(() => m.remove(), 300);
    });
    // Reset URL back to main dashboard
    window.history.replaceState({}, '', 'dashboard.php');
  };
</script>

<?php
endif;

// If AJAX request, only output the modal HTML and exit
if ($isAjax && $modal):
    header('Content-Type: text/html; charset=utf-8');
    
    // View modal
    if ($modal === 'view' && $employee_id && $deductionBreakdown): ?>
<div class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <h3>📊 Deduction Breakdown</h3>
      <button type="button" onclick="window.closeDeductionsModal()" class="modal-close-btn">×</button>
    </div>
    <div class="modal-content">
      <div class="modal-section">
        <div class="modal-section-title">Employee Information</div>
        <div style="padding: 0.75rem 0;">
          <strong><?php echo htmlspecialchars($employeeInfo['first_name'] . ' ' . $employeeInfo['last_name'] ?? 'N/A'); ?></strong> (<?php echo htmlspecialchars($employeeInfo['employee_code'] ?? 'N/A'); ?>)
        </div>
        <div style="padding: 0.75rem 0; color: #6b7280; font-size: 13px;">
          Gross Pay: <strong>₱ <?php echo number_format($employeeInfo['basic_rate'] ?? 0, 2); ?></strong>
        </div>
      </div>

      <div class="modal-section">
        <div class="modal-section-title">📋 Deductions</div>
        <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
          <tr style="border-bottom: 1px solid #e5e7eb; background: #f3f4f6;">
            <th style="padding: 0.5rem 0; text-align: left;">Deduction Type</th>
            <th style="padding: 0.5rem 0; text-align: right;">Amount</th>
            <th style="padding: 0.5rem 0; text-align: center;">Status</th>
          </tr>
          <?php 
          $totalDed = 0;
          foreach ($deductionBreakdown as $ded): 
            $totalDed += (float)$ded['amount'];
          ?>
          <tr style="border-bottom: 1px solid #e5e7eb;">
            <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($ded['deduction_type'] ?? 'N/A'); ?></td>
            <td style="padding: 0.5rem 0; text-align: right;">₱ <?php echo number_format($ded['amount'] ?? 0, 2); ?></td>
            <td style="padding: 0.5rem 0; text-align: center;"><span class="badge <?php echo $ded['status'] === 'pending' ? 'badge-pending' : 'badge-approved'; ?>"><?php echo ucfirst($ded['status']); ?></span></td>
          </tr>
          <?php endforeach; ?>
          <tr style="border-top: 2px solid #3b82f6; font-weight: 600;">
            <td style="padding: 0.75rem 0;">Total Deductions</td>
            <td style="padding: 0.75rem 0; text-align: right;">₱ <?php echo number_format($totalDed, 2); ?></td>
            <td></td>
          </tr>
        </table>
      </div>

      <div class="modal-btn-group">
        <button type="button" onclick="window.closeDeductionsModal()" class="btn btn-secondary">Close</button>
      </div>
    </div>
  </div>
</div>
    <?php elseif ($modal === 'view' && $employee_id): ?>
<div class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <h3>Employee Deductions</h3>
      <button type="button" onclick="window.closeDeductionsModal()" class="modal-close-btn">×</button>
    </div>
    <div class="modal-content">
      <p style="text-align: center; color: #6b7280; padding: 2rem 0;">No deduction data found for this employee.</p>
      <div class="modal-btn-group">
        <button type="button" onclick="window.closeDeductionsModal()" class="btn btn-secondary">Close</button>
      </div>
    </div>
  </div>
</div>
    <?php elseif ($modal === 'add'): ?>
<div class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <h3>➕ Add Manual Deduction</h3>
      <button type="button" onclick="window.closeDeductionsModal()" class="modal-close-btn">×</button>
    </div>
    <div class="modal-content">
      <div class="modal-form-section">
        <h4>Deduction Details</h4>
        <div class="modal-form-row">
          <div style="display: flex; flex-direction: column;">
            <label style="font-weight: 500; color: #374151; margin-bottom: 0.5rem; font-size: 14px;">Employee ID <span style="color: #ef4444;">*</span></label>
            <select style="padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; font-family: inherit;">
              <option value="">-- Select Employee --</option>
            </select>
          </div>
        </div>
      </div>
      <div class="alert alert-warning">
        <strong>⚠️ Important:</strong> Manual deductions require approval before payroll processing. Ensure the amount does not cause total deductions to exceed the employee's gross pay.
      </div>
      <div class="modal-btn-group">
        <button type="button" onclick="window.closeDeductionsModal()" class="btn btn-secondary">Cancel</button>
        <button type="button" class="btn btn-primary">Submit for Approval</button>
      </div>
    </div>
  </div>
</div>
    <?php endif;
    exit;
endif;

// Main deductions management page (non-AJAX view)
?>

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
    <form method="GET" action="<?= BASE_URL ?>dashboard.php">
      <input type="hidden" name="ref" value="payroll">
      <input type="hidden" name="page" value="deductions_management">
      <div class="filter-section">
        <div class="form-group">
          <label>Search Employee</label>
          <input type="text" name="search" placeholder="Employee ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="form-group">
          <label>Department</label>
          <select name="department">
            <option value="">-- All Departments --</option>
            <option value="Human Resources" <?php echo $department === 'Human Resources' ? 'selected' : ''; ?>>Human Resources</option>
            <option value="Nursing" <?php echo $department === 'Nursing' ? 'selected' : ''; ?>>Nursing</option>
            <option value="Emergency Room" <?php echo $department === 'Emergency Room' ? 'selected' : ''; ?>>Emergency Room</option>
            <option value="Laboratory" <?php echo $department === 'Laboratory' ? 'selected' : ''; ?>>Laboratory</option>
            <option value="Radiology" <?php echo $department === 'Radiology' ? 'selected' : ''; ?>>Radiology</option>
            <option value="Pharmacy" <?php echo $department === 'Pharmacy' ? 'selected' : ''; ?>>Pharmacy</option>
            <option value="Administration" <?php echo $department === 'Administration' ? 'selected' : ''; ?>>Administration</option>
            <option value="Finance" <?php echo $department === 'Finance' ? 'selected' : ''; ?>>Finance</option>
            <option value="Outpatient Department" <?php echo $department === 'Outpatient Department' ? 'selected' : ''; ?>>Outpatient Department</option>
          </select>
        </div>
        <div class="form-group">
          <label>Deduction Status</label>
          <select name="deduction_status">
            <option value="">-- All Status --</option>
            <option value="active" <?php echo $deduction_status === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="processed" <?php echo $deduction_status === 'processed' ? 'selected' : ''; ?>>Processed</option>
            <option value="pending" <?php echo $deduction_status === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
            <option value="calculated" <?php echo $deduction_status === 'calculated' ? 'selected' : ''; ?>>Calculated</option>
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
      <div class="value">₱ <?php echo number_format($totalStatutory, 2); ?></div>
    </div>
    <div class="summary-card">
      <label>Other Deductions</label>
      <div class="value">₱ <?php echo number_format($totalOther, 2); ?></div>
    </div>
    <div class="summary-card danger">
      <label>Total All Deductions</label>
      <div class="value">₱ <?php echo number_format($totalDeductions, 2); ?></div>
    </div>
    <div class="summary-card warning">
      <label>Pending Manual Approvals</label>
      <div class="value"><?php echo $pendingApprovals; ?></div>
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
          <?php
          // Group deductions by employee
          $empDeductionsSummary = [];
          foreach ($deductions as $ded) {
              $empId = $ded['employee_id'];
              if (!isset($empDeductionsSummary[$empId])) {
                  $empDeductionsSummary[$empId] = [
                      'code' => $ded['employee_code'],
                      'name' => $ded['first_name'] . ' ' . $ded['last_name'],
                      'gross' => (float)$ded['gross_pay'] ?: 0,
                      'stat' => 0,
                      'other' => 0,
                      'status' => 'approved'
                  ];
              }
              if ($ded['is_mandatory']) {
                  $empDeductionsSummary[$empId]['stat'] += (float)$ded['amount'];
              } else {
                  $empDeductionsSummary[$empId]['other'] += (float)$ded['amount'];
              }
              if ($ded['status'] === 'pending') {
                  $empDeductionsSummary[$empId]['status'] = 'pending';
              }
          }
          ?>
          <?php if (empty($empDeductionsSummary)): ?>
          <tr><td colspan="10" style="text-align: center; padding: 2rem;">No deduction data available</td></tr>
          <?php else: foreach ($empDeductionsSummary as $empId => $emp):
              $tot = $emp['stat'] + $emp['other'];
              $net = max(0, $emp['gross'] - $tot);
              $pct = $emp['gross'] > 0 ? round(($tot / $emp['gross']) * 100, 1) : 0;
              $badge = $emp['status'] === 'pending' ? 'badge-pending' : 'badge-approved';
              $text = $emp['status'] === 'pending' ? 'Pending Review' : 'Approved';
          ?>
          <tr>
            <td><?php echo htmlspecialchars($emp['code']); ?></td>
            <td><?php echo htmlspecialchars($emp['name']); ?></td>
            <td class="amount"><?php echo number_format($emp['gross'], 2); ?></td>
            <td class="amount"><?php echo number_format($emp['stat'], 2); ?></td>
            <td class="amount"><?php echo number_format($emp['other'], 2); ?></td>
            <td class="amount amount-total"><?php echo number_format($tot, 2); ?></td>
            <td class="amount"><?php echo number_format($net, 2); ?></td>
            <td style="text-align: center;"><?php echo $pct; ?>%</td>
            <td><span class="badge <?php echo $badge; ?>"><?php echo $text; ?></span></td>
            <td><button type="button" onclick="window.openDeductionsModal(<?php echo $empId; ?>)" class="btn btn-secondary btn-sm">View</button></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Manual Deduction Button Section -->
  <div class="section" style="text-align: center;">
    <button type="button" onclick="window.openDeductionsModal('add')" class="btn btn-primary">➕ Add Manual Deduction</button>
  </div>

</div>
