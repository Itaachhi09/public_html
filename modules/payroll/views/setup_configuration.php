<?php
/**
 * Payroll Setup & Configuration Module
 * Manages payroll rules, calendars, components, and settings
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayrollConfiguration.php';
require_once __DIR__ . '/../models/PayrollComponent.php';
require_once __DIR__ . '/../models/SSSContribution.php';
require_once __DIR__ . '/../models/PhilHealthPremium.php';
require_once __DIR__ . '/../models/PagIbigContribution.php';
require_once __DIR__ . '/../models/BIRTaxBracket.php';

$configuration = new PayrollConfiguration();
$payrollComponent = new PayrollComponent();
$sssContribution = new SSSContribution();
$philhealthPremium = new PhilHealthPremium();
$pagibigContribution = new PagIbigContribution();
$birTaxBracket = new BIRTaxBracket();

// Base URL for handler so form actions work when view is loaded via fetch into dashboard
$handlerBase = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$handlerUrl = rtrim($handlerBase, '/') . '/setup_configuration_handler.php';
$dashboardUrl = dirname(dirname($handlerBase)) . '/dashboard.php';

// Fetch configuration and components (with error handling for missing tables)
$configs = [];
$components = [];
try {
    $configs = $configuration->getActive() ?? [];
} catch (Exception $e) {
    // Tables might not exist yet - continue gracefully
    error_log('PayrollConfiguration query failed: ' . $e->getMessage());
}

try {
    $components = $payrollComponent->getAll() ?? [];
} catch (Exception $e) {
    // Tables might not exist yet - continue gracefully
    error_log('PayrollComponent query failed: ' . $e->getMessage());
}
$totalConfigs = count($configs ?? []);
$totalComponents = count($components ?? []);
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

  /* Statutory modals – pop-up overlay (no JS, uses :target) */
  .statutory-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
  }
  .statutory-modal:target {
    display: flex;
  }
  .statutory-modal-inner {
    background: white;
    border-radius: 10px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 480px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
  }
  .statutory-modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
    border-radius: 10px 10px 0 0;
  }
  .statutory-modal-header h4 {
    margin: 0;
    font-size: 1.125rem;
    color: #1f2937;
  }
  .statutory-modal-close {
    display: inline-block;
    width: 32px;
    height: 32px;
    line-height: 32px;
    text-align: center;
    text-decoration: none;
    color: #6b7280;
    font-size: 1.5rem;
    border-radius: 6px;
    background: #e5e7eb;
  }
  .statutory-modal-close:hover {
    background: #d1d5db;
    color: #1f2937;
  }
  .statutory-modal-body {
    padding: 1.5rem;
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

  .alert-success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
  }

  .alert-error {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
  }

  .table-container {
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin-bottom: 1rem;
  }

  .table-container table {
    width: 100%;
    border-collapse: collapse;
    background: white;
  }

  .table-container thead {
    background: #f3f4f6;
    border-bottom: 2px solid #e5e7eb;
  }

  .table-container th {
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    font-size: 14px;
  }

  .table-container td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
  }

  .table-container tbody tr:hover {
    background: #f9fafb;
  }

  .btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 13px;
    min-width: 60px;
  }

  .btn-danger {
    background: #ef4444;
    color: white;
    border: none;
    padding: 0.625rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
  }

  .btn-danger:hover {
    background: #dc2626;
  }

  .btn-danger:active {
    background: #b91c1c;
  }

  .statutory-input, .statutory-input:focus {
    width: 100%;
    padding: 0.625rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
  }

  .statutory-input:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .statutory-fields div {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  #statutoryMessage {
    padding: 0.75rem;
    border-radius: 4px;
    font-size: 14px;
    display: none;
  }

  #statutoryMessage.success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
  }

  #statutoryMessage.error {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
  }

  .table-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .table-actions .btn-secondary, .table-actions .btn-danger {
    padding: 0.375rem 0.75rem;
    font-size: 12px;
    flex: 1;
    min-width: 50px;
  }
</style>

<div class="main-content">
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
      <div class="tab active" onclick="window.switchTab(event, 'pay-frequency')">
        <i class='bx bx-calendar'></i> Pay Frequency
      </div>
      <div class="tab" onclick="window.switchTab(event, 'payroll-calendar')">
        <i class='bx bx-calendar-event'></i> Payroll Calendar
      </div>
      <div class="tab" onclick="window.switchTab(event, 'salary-components')">
        <i class='bx bx-list-check'></i> Salary Components
      </div>
      <div class="tab" onclick="window.switchTab(event, 'statutory-tables')">
        <i class='bx bx-table'></i> Statutory Tables
      </div>
      <div class="tab" onclick="window.switchTab(event, 'bank-settings')">
        <i class='bx bx-building'></i> Bank Settings
      </div>
    </div>
  </div>

  <!-- TAB 1: PAY FREQUENCY -->
  <div id="pay-frequency" class="tab-content active">
    <div class="setup-section">
      <h3 class="section-header">Configure Pay Frequency</h3>
      
      <div class="info-box" id="payFrequencyInfo">
        <strong>Loading calendar data...</strong>
      </div>

      <div id="payFrequencyTable" style="display: none;">
        <div class="form-row">
          <div class="form-group">
            <label>Payroll Year</label>
            <input type="number" id="displayYear" readonly style="background-color: #f3f4f6;">
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
                <th>Frequency</th>
              </tr>
            </thead>
            <tbody id="payFrequencyTableBody">
              <tr>
                <td colspan="5" style="text-align: center; padding: 2rem;">No calendar data available</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="alert alert-info">
          <strong>ℹ️ Note:</strong> This pay frequency configuration is based on the payroll calendar you configured. Changes to the calendar will be reflected here automatically.
        </div>

        <div class="btn-group">
          <button type="button" onclick="window.reloadPayFrequencyData()" class="btn-secondary">Refresh</button>
        </div>
      </div>

      <div id="payFrequencyNoData" style="display: none;">
        <div class="alert alert-warning">
          <strong>⚠️ No Calendar Configured:</strong> Please configure the payroll calendar first in the "Payroll Calendar" tab to set up pay frequency.
        </div>
      </div>
    </div>
  </div>

  <!-- TAB 2: PAYROLL CALENDAR -->
  <div id="payroll-calendar" class="tab-content">
    <div class="setup-section">
      <h3 class="section-header">Configure Payroll Calendar</h3>

      <form id="payrollCalendarForm" method="POST" action="#">
        <input type="hidden" name="action" value="save_calendar">
        
        <div class="form-row full">
          <div class="form-group">
            <label>Payroll Year</label>
            <input type="number" id="payroll_year" name="payroll_year" value="<?php echo date('Y'); ?>" min="2024">
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
                  <input type="date" name="cutoff_start_1" id="cutoff_start_1" value="<?php echo date('Y-m-01'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="cutoff_end_1" id="cutoff_end_1" value="<?php echo date('Y-m-15'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="pay_date_1" id="pay_date_1" value="<?php echo date('Y-m-07', strtotime('first day of next month')); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td><span class="badge badge-success">Active</span></td>
              </tr>
              <tr>
                <td>Period 2</td>
                <td>
                  <input type="date" name="cutoff_start_2" id="cutoff_start_2" value="<?php echo date('Y-m-16'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="cutoff_end_2" id="cutoff_end_2" value="<?php echo date('Y-m-t'); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td>
                  <input type="date" name="pay_date_2" id="pay_date_2" value="<?php echo date('Y-m-22', strtotime('first day of next month')); ?>" style="width: 100%; padding: 0.5rem;">
                </td>
                <td><span class="badge badge-success">Active</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div id="calendar-message" style="margin-bottom: 1rem; display: none;"></div>

        <div class="btn-group">
          <button type="submit" class="btn-primary" id="saveCalendarBtn">Save Calendar</button>
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
        
        <div id="earnings-container">
          <p style="color: #9ca3af; padding: 1rem; text-align: center;">Loading earnings components...</p>
        </div>

        <button type="button" class="btn-primary add-row-btn" id="addEarningBtn">
          <i class='bx bx-plus'></i> Add Earning Component
        </button>
      </div>

      <!-- DEDUCTIONS -->
      <div>
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">📉 Deductions</h4>
        
        <div id="deductions-container">
          <p style="color: #9ca3af; padding: 1rem; text-align: center;">Loading deductions components...</p>
        </div>

        <button type="button" class="btn-primary add-row-btn" id="addDeductionBtn">
          <i class='bx bx-plus'></i> Add Deduction Component
        </button>
      </div>
    </div>
  </div>

  <!-- MODAL: ADD/EDIT COMPONENT -->
  <div id="componentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h3 id="componentModalTitle" style="margin: 0;">Add Component</h3>
        <button type="button" onclick="window.closeComponentModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">×</button>
      </div>

      <form id="componentForm" style="display: flex; flex-direction: column; gap: 1rem;">
        <input type="hidden" id="componentId" value="">
        <input type="hidden" id="componentType" value="">

        <div>
          <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Component Code *</label>
          <input type="text" id="componentCode" placeholder="e.g., BASIC_PAY, SSS_CONTRIB" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; font-family: monospace;" required>
          <small style="color: #9ca3af;"><span id="codeHint">Auto-generated after first save</span></small>
        </div>

        <div>
          <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Component Name *</label>
          <input type="text" id="componentName" placeholder="e.g., Basic Pay" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;" required>
        </div>

        <div>
          <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Description</label>
          <textarea id="componentDesc" placeholder="Brief description of the component" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; min-height: 80px; resize: vertical;"></textarea>
        </div>

        <div>
          <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">Category</label>
          <input type="text" id="componentCategory" placeholder="e.g., Allowance, Tax, Contribution" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
        </div>

        <div id="componentMessage" style="padding: 0.75rem; border-radius: 4px; display: none;"></div>

        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
          <button type="submit" class="btn-primary" style="flex: 1;">Save Component</button>
          <button type="button" class="btn-secondary" onclick="window.closeComponentModal()" style="flex: 1;">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: DELETE CONFIRMATION -->
  <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 400px;">
      <h3 style="margin-top: 0;">Confirm Delete</h3>
      <p id="deleteMessage">Are you sure you want to delete this component?</p>
      
      <div style="display: flex; gap: 1rem; margin-top: 2rem;">
        <button type="button" id="confirmDeleteBtn" class="btn-danger" style="flex: 1;">Delete</button>
        <button type="button" class="btn-secondary" onclick="window.closeDeleteModal()" style="flex: 1;">Cancel</button>
      </div>
    </div>
  </div>

  <!-- TAB 4: STATUTORY TABLES -->
  <div id="statutory-tables" class="tab-content">
    <div class="setup-section">
      <h3 class="section-header">Maintain Statutory Tables</h3>

      <?php
      $currentYear = date('Y');
      $message = '';
      $messageType = '';
      $validationError = $_SESSION['statutory_validation_error'] ?? '';
      $validationFormData = $_SESSION['statutory_form_data'] ?? null;
      $validationTableType = $_SESSION['statutory_table_type'] ?? '';
      $validationEditId = isset($_SESSION['statutory_edit_id']) ? $_SESSION['statutory_edit_id'] : null;
      $editMode = !empty($_SESSION['statutory_edit_mode']);
      $editData = $_SESSION['statutory_edit_data'] ?? null;
      $table_type = $_SESSION['statutory_table_type'] ?? '';
      $deleteConfirmId = isset($_SESSION['delete_confirm_id']) ? (int) $_SESSION['delete_confirm_id'] : (isset($_GET['delete_id']) ? (int) $_GET['delete_id'] : null);
      $deleteConfirmTable = isset($_SESSION['delete_confirm_table']) ? trim($_SESSION['delete_confirm_table']) : (isset($_GET['delete_table']) ? trim($_GET['delete_table']) : '');

      if ($validationError !== '') {
        $editMode = !empty($_SESSION['statutory_edit_mode']);
        $editData = $_SESSION['statutory_edit_data'] ?? $validationFormData;
      }
      if (!$editMode || $validationError === '') {
        if ($editMode) {
          unset($_SESSION['statutory_edit_mode']);
          unset($_SESSION['statutory_edit_data']);
          unset($_SESSION['statutory_table_type']);
        }
      }
      if ($validationError !== '') {
        unset($_SESSION['statutory_validation_error']);
        unset($_SESSION['statutory_form_data']);
        unset($_SESSION['statutory_edit_id']);
        unset($_SESSION['statutory_edit_mode']);
        unset($_SESSION['statutory_edit_data']);
        unset($_SESSION['statutory_table_type']);
      }

      if (!empty($_SESSION['payroll_message'])) {
        $message = $_SESSION['payroll_message'];
        $messageType = $_SESSION['payroll_message_type'] ?? 'info';
        unset($_SESSION['payroll_message']);
        unset($_SESSION['payroll_message_type']);
      }

      // Load statutory contribution data with error handling
      $sssData = [];
      $philhealthData = [];
      $pagibigData = [];
      $birData = [];
      
      try {
        $sssData = $sssContribution->getByYear($currentYear) ?? [];
      } catch (Exception $e) {
        error_log('SSS contribution query failed: ' . $e->getMessage());
      }
      
      try {
        $philhealthData = $philhealthPremium->getByYear($currentYear) ?? [];
      } catch (Exception $e) {
        error_log('PhilHealth query failed: ' . $e->getMessage());
      }
      
      try {
        $pagibigData = $pagibigContribution->getByYear($currentYear) ?? [];
      } catch (Exception $e) {
        error_log('Pag-IBIG query failed: ' . $e->getMessage());
      }
      
      try {
        $birData = $birTaxBracket->getByYear($currentYear) ?? [];
      } catch (Exception $e) {
        error_log('BIR tax bracket query failed: ' . $e->getMessage());
      }
      ?>

      <?php if ($message): ?>
      <div class="message <?php echo $messageType === 'error' ? 'error' : ($messageType === 'success' ? 'success' : 'info'); ?>" style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 6px; background: <?php echo $messageType === 'error' ? '#fee2e2' : ($messageType === 'success' ? '#d1fae5' : '#dbeafe'); ?>; color: <?php echo $messageType === 'error' ? '#991b1b' : ($messageType === 'success' ? '#065f46' : '#1e40af'); ?>;">
        <?php echo htmlspecialchars($message); ?>
      </div>
      <?php endif; ?>

      <?php if ($validationError !== ''): ?>
      <div id="statutory-validation-error" style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 6px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
        <?php echo htmlspecialchars($validationError); ?>
      </div>
      <?php endif; ?>

      <?php if ($deleteConfirmId && $deleteConfirmTable): ?>
      <div id="modal-delete" class="statutory-modal">
        <div class="statutory-modal-inner">
          <div class="statutory-modal-header">
            <h4>Confirm delete</h4>
            <a href="#statutory-tables" class="statutory-modal-close" title="Close">&times;</a>
          </div>
          <div class="statutory-modal-body">
            <p style="margin: 0 0 1rem 0; color: #374151;">Are you sure you want to delete this bracket? This action cannot be undone.</p>
            <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline-block; margin-right: 0.5rem;">
              <input type="hidden" name="action" value="delete_statutory_bracket">
              <input type="hidden" name="table_type" value="<?php echo htmlspecialchars($deleteConfirmTable); ?>">
              <input type="hidden" name="id" value="<?php echo $deleteConfirmId; ?>">
              <input type="hidden" name="confirmed" value="1">
              <button type="submit" class="btn-danger btn-sm">Yes, delete</button>
            </form>
            <a href="<?php echo htmlspecialchars($handlerUrl); ?>?cancel_delete=1" class="btn-secondary btn-sm" style="display: inline-block; text-decoration: none; padding: 0.375rem 0.75rem;">Cancel</a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- SSS TABLE -->
      <div style="margin-bottom: 2rem;">
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">SSS Contribution Table (<?php echo $currentYear; ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Monthly Contribution</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($sssData) > 0): ?>
                <?php foreach ($sssData as $row): ?>
                <tr>
                  <td><?php echo number_format($row['salary_from'], 2); ?></td>
                  <td><?php echo number_format($row['salary_to'], 2); ?></td>
                  <td><?php echo number_format($row['monthly_contribution'], 2); ?></td>
                  <td style="display: flex; gap: 0.5rem;">
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="edit_bracket">
                      <input type="hidden" name="table_type" value="sss">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-secondary btn-sm">Edit</button>
                    </form>
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="delete_statutory_bracket">
                      <input type="hidden" name="table_type" value="sss">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-danger btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center; color: #9ca3af; padding: 1rem;">No SSS brackets configured</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <p style="margin-top: 1rem;"><a href="#modal-add-sss" class="btn-primary add-row-btn" style="display: inline-block; text-decoration: none;">Add SSS Range</a></p>

        <?php if (!$editMode || $table_type !== 'sss'): ?>
        <div id="modal-add-sss" class="statutory-modal">
          <div class="statutory-modal-inner">
            <div class="statutory-modal-header">
              <h4>Add New SSS Bracket</h4>
              <a href="#statutory-tables" class="statutory-modal-close" title="Close">&times;</a>
            </div>
            <div class="statutory-modal-body">
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="add_statutory_bracket">
                <input type="hidden" name="table_type" value="sss">
                <div class="form-group">
                  <label>Salary Range (From) *</label>
                  <input type="number" name="salary_from" step="0.01" min="0" value="<?php echo ($validationTableType === 'sss' && $validationFormData) ? htmlspecialchars($validationFormData['salary_from'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Salary Range (To) *</label>
                  <input type="number" name="salary_to" step="0.01" min="0" value="<?php echo ($validationTableType === 'sss' && $validationFormData) ? htmlspecialchars($validationFormData['salary_to'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Monthly Contribution *</label>
                  <input type="number" name="monthly_contribution" step="0.01" min="0" value="<?php echo ($validationTableType === 'sss' && $validationFormData) ? htmlspecialchars($validationFormData['monthly_contribution'] ?? '') : ''; ?>" required>
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Save Bracket</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if ($editMode): ?>
        <div id="modal-edit" class="statutory-modal">
          <div class="statutory-modal-inner">
            <div class="statutory-modal-header">
              <h4>Edit <?php echo $table_type === 'sss' ? 'SSS' : ($table_type === 'philhealth' ? 'PhilHealth' : ($table_type === 'pagibig' ? 'Pag-IBIG' : 'Tax')); ?> Bracket</h4>
              <a href="#statutory-tables" class="statutory-modal-close" title="Close">&times;</a>
            </div>
            <div class="statutory-modal-body">
              <?php if ($table_type === 'sss' && $editData): ?>
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="update_statutory_bracket">
                <input type="hidden" name="table_type" value="sss">
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <div class="form-group">
                  <label>Salary Range (From) *</label>
                  <input type="number" name="salary_from" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['salary_from']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Salary Range (To) *</label>
                  <input type="number" name="salary_to" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['salary_to']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Monthly Contribution *</label>
                  <input type="number" name="monthly_contribution" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['monthly_contribution']); ?>" required>
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Update Bracket</button>
                  <a href="#statutory-tables" class="btn-secondary" style="display: inline-block; text-decoration: none; padding: 0.5rem 1rem;">Cancel</a>
                </div>
              </form>
              <?php endif; ?>
              <?php if ($table_type === 'philhealth' && $editData): ?>
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="update_statutory_bracket">
                <input type="hidden" name="table_type" value="philhealth">
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <div class="form-group">
                  <label>Salary Range (From) *</label>
                  <input type="number" name="salary_from" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['salary_from']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Salary Range (To) *</label>
                  <input type="number" name="salary_to" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['salary_to']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Monthly Premium *</label>
                  <input type="number" name="monthly_premium" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['monthly_premium']); ?>" required>
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Update Bracket</button>
                  <a href="#statutory-tables" class="btn-secondary" style="display: inline-block; text-decoration: none; padding: 0.5rem 1rem;">Cancel</a>
                </div>
              </form>
              <?php endif; ?>
              <?php if ($table_type === 'pagibig' && $editData): ?>
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="update_statutory_bracket">
                <input type="hidden" name="table_type" value="pagibig">
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <div class="form-group">
                  <label>Salary Range (From) *</label>
                  <input type="number" name="salary_from" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['salary_from']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Salary Range (To) *</label>
                  <input type="number" name="salary_to" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['salary_to']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Monthly Contribution *</label>
                  <input type="number" name="monthly_contribution" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['monthly_contribution']); ?>" required>
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Update Bracket</button>
                  <a href="#statutory-tables" class="btn-secondary" style="display: inline-block; text-decoration: none; padding: 0.5rem 1rem;">Cancel</a>
                </div>
              </form>
              <?php endif; ?>
              <?php if ($table_type === 'bir' && $editData): ?>
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="update_statutory_bracket">
                <input type="hidden" name="table_type" value="bir">
                <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                <div class="form-group">
                  <label>Annual Salary (From) *</label>
                  <input type="number" name="annual_salary_from" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['annual_salary_from']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Annual Salary (To) *</label>
                  <input type="number" name="annual_salary_to" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['annual_salary_to']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Tax Rate (%) *</label>
                  <input type="number" name="tax_rate" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($editData['tax_rate']); ?>" required>
                </div>
                <div class="form-group">
                  <label>Deductible Amount</label>
                  <input type="number" name="deductible_amount" step="0.01" min="0" value="<?php echo htmlspecialchars($editData['deductible_amount'] ?? ''); ?>">
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Update Bracket</button>
                  <a href="#statutory-tables" class="btn-secondary" style="display: inline-block; text-decoration: none; padding: 0.5rem 1rem;">Cancel</a>
                </div>
              </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- PHILHEALTH TABLE -->
      <div style="margin-bottom: 2rem;">
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">PhilHealth Premium Table (<?php echo $currentYear; ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Monthly Premium</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($philhealthData) > 0): ?>
                <?php foreach ($philhealthData as $row): ?>
                <tr>
                  <td><?php echo number_format($row['salary_from'], 2); ?></td>
                  <td><?php echo number_format($row['salary_to'], 2); ?></td>
                  <td><?php echo number_format($row['monthly_premium'], 2); ?></td>
                  <td style="display: flex; gap: 0.5rem;">
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="edit_bracket">
                      <input type="hidden" name="table_type" value="philhealth">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-secondary btn-sm">Edit</button>
                    </form>
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="delete_statutory_bracket">
                      <input type="hidden" name="table_type" value="philhealth">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-danger btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center; color: #9ca3af; padding: 1rem;">No PhilHealth brackets configured</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <p style="margin-top: 1rem;"><a href="#modal-add-philhealth" class="btn-primary add-row-btn" style="display: inline-block; text-decoration: none;">Add PhilHealth Range</a></p>

        <?php if (!$editMode || $table_type !== 'philhealth'): ?>
        <div id="modal-add-philhealth" class="statutory-modal">
          <div class="statutory-modal-inner">
            <div class="statutory-modal-header">
              <h4>Add New PhilHealth Bracket</h4>
              <a href="#statutory-tables" class="statutory-modal-close" title="Close">&times;</a>
            </div>
            <div class="statutory-modal-body">
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="add_statutory_bracket">
                <input type="hidden" name="table_type" value="philhealth">
                <div class="form-group">
                  <label>Salary Range (From) *</label>
                  <input type="number" name="salary_from" step="0.01" min="0" value="<?php echo ($validationTableType === 'philhealth' && $validationFormData) ? htmlspecialchars($validationFormData['salary_from'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Salary Range (To) *</label>
                  <input type="number" name="salary_to" step="0.01" min="0" value="<?php echo ($validationTableType === 'philhealth' && $validationFormData) ? htmlspecialchars($validationFormData['salary_to'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Monthly Premium *</label>
                  <input type="number" name="monthly_premium" step="0.01" min="0" value="<?php echo ($validationTableType === 'philhealth' && $validationFormData) ? htmlspecialchars($validationFormData['monthly_premium'] ?? '') : ''; ?>" required>
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Save Bracket</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- PAG-IBIG TABLE -->
      <div style="margin-bottom: 2rem;">
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">Pag-IBIG Contribution Table (<?php echo $currentYear; ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Monthly Contribution</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($pagibigData) > 0): ?>
                <?php foreach ($pagibigData as $row): ?>
                <tr>
                  <td><?php echo number_format($row['salary_from'], 2); ?></td>
                  <td><?php echo number_format($row['salary_to'], 2); ?></td>
                  <td><?php echo number_format($row['monthly_contribution'], 2); ?></td>
                  <td style="display: flex; gap: 0.5rem;">
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="edit_bracket">
                      <input type="hidden" name="table_type" value="pagibig">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-secondary btn-sm">Edit</button>
                    </form>
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="delete_statutory_bracket">
                      <input type="hidden" name="table_type" value="pagibig">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-danger btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center; color: #9ca3af; padding: 1rem;">No Pag-IBIG brackets configured</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <p style="margin-top: 1rem;"><a href="#modal-add-pagibig" class="btn-primary add-row-btn" style="display: inline-block; text-decoration: none;">Add Pag-IBIG Range</a></p>

        <?php if (!$editMode || $table_type !== 'pagibig'): ?>
        <div id="modal-add-pagibig" class="statutory-modal">
          <div class="statutory-modal-inner">
            <div class="statutory-modal-header">
              <h4>Add New Pag-IBIG Bracket</h4>
              <a href="#statutory-tables" class="statutory-modal-close" title="Close">&times;</a>
            </div>
            <div class="statutory-modal-body">
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="add_statutory_bracket">
                <input type="hidden" name="table_type" value="pagibig">
                <div class="form-group">
                  <label>Salary Range (From) *</label>
                  <input type="number" name="salary_from" step="0.01" min="0" value="<?php echo ($validationTableType === 'pagibig' && $validationFormData) ? htmlspecialchars($validationFormData['salary_from'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Salary Range (To) *</label>
                  <input type="number" name="salary_to" step="0.01" min="0" value="<?php echo ($validationTableType === 'pagibig' && $validationFormData) ? htmlspecialchars($validationFormData['salary_to'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Monthly Contribution *</label>
                  <input type="number" name="monthly_contribution" step="0.01" min="0" value="<?php echo ($validationTableType === 'pagibig' && $validationFormData) ? htmlspecialchars($validationFormData['monthly_contribution'] ?? '') : ''; ?>" required>
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Save Bracket</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- BIR WITHHOLDING TAX TABLE -->
      <div>
        <h4 style="color: #1f2937; margin-bottom: 1rem; font-size: 16px;">BIR Withholding Tax Table (<?php echo $currentYear; ?>)</h4>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Annual Salary (From)</th>
                <th>Annual Salary (To)</th>
                <th>Tax Rate (%)</th>
                <th>Deductible Amount</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($birData) > 0): ?>
                <?php foreach ($birData as $row): ?>
                <tr>
                  <td><?php echo number_format($row['annual_salary_from'], 2); ?></td>
                  <td><?php echo number_format($row['annual_salary_to'], 2); ?></td>
                  <td><?php echo number_format($row['tax_rate'], 2); ?>%</td>
                  <td><?php echo number_format($row['deductible_amount'], 2); ?></td>
                  <td style="display: flex; gap: 0.5rem;">
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="edit_bracket">
                      <input type="hidden" name="table_type" value="bir">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-secondary btn-sm">Edit</button>
                    </form>
                    <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display: inline;">
                      <input type="hidden" name="action" value="delete_statutory_bracket">
                      <input type="hidden" name="table_type" value="bir">
                      <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                      <button type="submit" class="btn-danger btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="text-align: center; color: #9ca3af; padding: 1rem;">No tax brackets configured</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <p style="margin-top: 1rem;"><a href="#modal-add-bir" class="btn-primary add-row-btn" style="display: inline-block; text-decoration: none;">Add Tax Bracket</a></p>

        <?php if (!$editMode || $table_type !== 'bir'): ?>
        <div id="modal-add-bir" class="statutory-modal">
          <div class="statutory-modal-inner">
            <div class="statutory-modal-header">
              <h4>Add New Tax Bracket</h4>
              <a href="#statutory-tables" class="statutory-modal-close" title="Close">&times;</a>
            </div>
            <div class="statutory-modal-body">
              <form method="POST" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="form-row full">
                <input type="hidden" name="action" value="add_statutory_bracket">
                <input type="hidden" name="table_type" value="bir">
                <div class="form-group">
                  <label>Annual Salary (From) *</label>
                  <input type="number" name="annual_salary_from" step="0.01" min="0" value="<?php echo ($validationTableType === 'bir' && $validationFormData) ? htmlspecialchars($validationFormData['annual_salary_from'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Annual Salary (To) *</label>
                  <input type="number" name="annual_salary_to" step="0.01" min="0" value="<?php echo ($validationTableType === 'bir' && $validationFormData) ? htmlspecialchars($validationFormData['annual_salary_to'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Tax Rate (%) *</label>
                  <input type="number" name="tax_rate" step="0.01" min="0" max="100" value="<?php echo ($validationTableType === 'bir' && $validationFormData) ? htmlspecialchars($validationFormData['tax_rate'] ?? '') : ''; ?>" required>
                </div>
                <div class="form-group">
                  <label>Deductible Amount</label>
                  <input type="number" name="deductible_amount" step="0.01" min="0" value="<?php echo ($validationTableType === 'bir' && $validationFormData) ? htmlspecialchars($validationFormData['deductible_amount'] ?? '') : ''; ?>">
                </div>
                <div class="btn-group" style="margin-top: 1rem;">
                  <button type="submit" class="btn-primary">Save Bracket</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TAB 5: BANK SETTINGS -->
  <div id="bank-settings" class="tab-content">
    <div class="setup-section">
      <h3 class="section-header">Configure Bank Settings</h3>

      <form id="bankSettingsForm" method="POST" action="#">
        <div class="form-row full">
          <div class="form-group">
            <label>Payroll Bank Account</label>
            <input type="text" name="payroll_bank_account" id="payroll_bank_account" placeholder="e.g., 1234567890" required>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Bank account number used for payroll disbursement</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank Name</label>
            <input type="text" name="bank_name" id="bank_name" placeholder="e.g., BDO, BPI, Metrobank" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Bank Code</label>
            <input type="text" name="bank_code" id="bank_code" placeholder="e.g., 006 for BDO" required>
          </div>
          <div class="form-group">
            <label>Bank Branch Code</label>
            <input type="text" name="branch_code" id="branch_code" placeholder="Branch code">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank File Format</label>
            <select name="bank_file_format" id="bank_file_format" required>
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
            <input type="text" name="batch_reference_pattern" id="batch_reference_pattern" placeholder="e.g., PAYROLL-YYYY-MM-###" required>
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Pattern for batch reference numbers. Use: YYYY (year), MM (month), ### (sequence)</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Next Batch Reference Number</label>
            <input type="number" name="next_batch_number" id="next_batch_number" value="1" min="1">
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank Contact Email</label>
            <input type="email" name="bank_contact_email" id="bank_contact_email" placeholder="email@bank.com">
            <small style="color: #6b7280; font-size: 12px; margin-top: 0.25rem; display: block;">Contact email for bank inquiries</small>
          </div>
        </div>

        <div class="form-row full">
          <div class="form-group">
            <label>Bank Contact Person</label>
            <input type="text" name="bank_contact_person" id="bank_contact_person" placeholder="Name of bank contact">
          </div>
        </div>

        <div class="alert alert-warning">
          <strong>⚠️ Important:</strong> Bank settings are critical for payroll processing. Ensure accuracy before saving. Changes will affect future salary transfers.
        </div>

        <div id="bank-message" style="margin-bottom: 1rem; display: none;"></div>

        <div class="btn-group">
          <button type="submit" class="btn-primary" id="saveBankBtn">Save Bank Settings</button>
          <button type="reset" class="btn-secondary">Reset</button>
        </div>
      </form>
    </div>
  </div>

</div>
</div>

<script>
  // Tab switching is now globally defined in dashboard.php
  // All other setup-specific functions below

  // Payroll Calendar Form Handler
  document.getElementById('payrollCalendarForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const year = document.getElementById('payroll_year').value;
    const messageDiv = document.getElementById('calendar-message');
    const submitBtn = document.getElementById('saveCalendarBtn');

    // Prepare data for both periods
    const calendarData = {
      action: 'save_calendar',
      payroll_year: year,
      periods: [
        {
          period_number: 1,
          cutoff_start: document.getElementById('cutoff_start_1').value,
          cutoff_end: document.getElementById('cutoff_end_1').value,
          pay_date: document.getElementById('pay_date_1').value,
          frequency: 'Bi-weekly',
          status: 'Active'
        },
        {
          period_number: 2,
          cutoff_start: document.getElementById('cutoff_start_2').value,
          cutoff_end: document.getElementById('cutoff_end_2').value,
          pay_date: document.getElementById('pay_date_2').value,
          frequency: 'Bi-weekly',
          status: 'Active'
        }
      ]
    };

    try {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';

      const response = await fetch('/public_html/modules/payroll/setup_configuration_handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(calendarData)
      });

      const result = await response.json();

      if (result.success) {
        messageDiv.innerHTML = '<div style="padding: 1rem; background-color: #d1fae5; color: #065f46; border-radius: 4px; border: 1px solid #a7f3d0;">✓ Calendar saved successfully! Your payroll calendar for ' + year + ' is now active.</div>';
        messageDiv.style.display = 'block';
        
        // Reload pay frequency display
        setTimeout(() => {
          reloadPayFrequencyData(year);
          messageDiv.style.display = 'none';
        }, 1500);
      } else {
        messageDiv.innerHTML = '<div style="padding: 1rem; background-color: #fee2e2; color: #991b1b; border-radius: 4px; border: 1px solid #fca5a5;">✗ Error: ' + (result.message || 'Failed to save calendar') + '</div>';
        messageDiv.style.display = 'block';
        console.error('Calendar save error:', result);
      }
    } catch (error) {
      console.error('Calendar submission error details:', error);
      let errorMsg = error.message;
      
      // Provide helpful error message
      if (errorMsg.includes('Unexpected token')) {
        errorMsg = 'Server returned invalid response. Check browser console (F12) or visit: http://localhost/public_html/modules/payroll/test-calendar.php';
      }
      
      messageDiv.innerHTML = '<div style="padding: 1rem; background-color: #fee2e2; color: #991b1b; border-radius: 4px; border: 1px solid #fca5a5;">✗ Error: ' + errorMsg + '</div>';
      messageDiv.style.display = 'block';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Save Calendar';
    }
  });

  // Load and display pay frequency from calendar
  window.reloadPayFrequencyData = async function(year = null) {
    // Get current year if not specified
    if (!year) {
      year = new Date().getFullYear();
    }

    try {
      const response = await fetch('/public_html/modules/payroll/setup_configuration_handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          action: 'get_calendar',
          year: year
        })
      });

      const result = await response.json();

      const infoBox = document.getElementById('payFrequencyInfo');
      const table = document.getElementById('payFrequencyTable');
      const noData = document.getElementById('payFrequencyNoData');
      const tableBody = document.getElementById('payFrequencyTableBody');
      const yearDisplay = document.getElementById('displayYear');

      if (result.success && result.periods && result.periods.length > 0) {
        // Show table, hide no-data message
        table.style.display = 'block';
        noData.style.display = 'none';
        
        yearDisplay.value = year;
        
        // Update info box
        infoBox.innerHTML = '<strong>Current Setup:</strong> ' + result.periods.length + ' pay period(s) configured for ' + year;
        
        // Clear table body
        tableBody.innerHTML = '';
        
        // Populate table with calendar data
        result.periods.forEach(period => {
          const row = document.createElement('tr');
          
          // Format dates for display
          const cutoffStart = new Date(period.cutoff_start).toLocaleDateString();
          const cutoffEnd = new Date(period.cutoff_end).toLocaleDateString();
          const payDate = new Date(period.pay_date).toLocaleDateString();
          
          row.innerHTML = `
            <td>Period ${period.period_number}</td>
            <td>${cutoffStart}</td>
            <td>${cutoffEnd}</td>
            <td>${payDate}</td>
            <td><span style="background-color: #dcfce7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 12px;">${period.frequency}</span></td>
          `;
          tableBody.appendChild(row);
        });
      } else {
        // Show no-data message, hide table
        table.style.display = 'none';
        noData.style.display = 'block';
        infoBox.innerHTML = '<strong>No calendar data:</strong> Configure the payroll calendar first to set up pay frequency.';
      }
    } catch (error) {
      console.error('Error loading pay frequency:', error);
      const noData = document.getElementById('payFrequencyNoData');
      noData.style.display = 'block';
    }
  }

  // Bank Settings Form Handler
  document.getElementById('bankSettingsForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const messageDiv = document.getElementById('bank-message');
    const submitBtn = document.getElementById('saveBankBtn');

    // Prepare bank settings data
    const bankData = {
      action: 'save_config',
      config_key: 'bank_settings',
      config_value: JSON.stringify({
        payroll_bank_account: document.getElementById('payroll_bank_account').value,
        bank_name: document.getElementById('bank_name').value,
        bank_code: document.getElementById('bank_code').value,
        branch_code: document.getElementById('branch_code').value,
        bank_file_format: document.getElementById('bank_file_format').value,
        batch_reference_pattern: document.getElementById('batch_reference_pattern').value,
        next_batch_number: document.getElementById('next_batch_number').value,
        bank_contact_email: document.getElementById('bank_contact_email').value,
        bank_contact_person: document.getElementById('bank_contact_person').value
      })
    };

    try {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';

      const response = await fetch('/public_html/modules/payroll/setup_configuration_handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(bankData)
      });

      const result = await response.json();

      if (result.success) {
        messageDiv.innerHTML = '<div style="padding: 1rem; background-color: #d1fae5; color: #065f46; border-radius: 4px; border: 1px solid #a7f3d0;">✓ Bank settings saved successfully!</div>';
        messageDiv.style.display = 'block';
        
        setTimeout(() => {
          messageDiv.style.display = 'none';
        }, 3000);
      } else {
        messageDiv.innerHTML = '<div style="padding: 1rem; background-color: #fee2e2; color: #991b1b; border-radius: 4px; border: 1px solid #fca5a5;">✗ Error: ' + (result.message || 'Failed to save bank settings') + '</div>';
        messageDiv.style.display = 'block';
        console.error('Bank settings save error:', result);
      }
    } catch (error) {
      console.error('Bank settings submission error:', error);
      let errorMsg = error.message;
      
      if (errorMsg.includes('Unexpected token')) {
        errorMsg = 'Server returned invalid response. Check browser console (F12) or visit: http://localhost/public_html/modules/payroll/test-calendar.php';
      }
      
      messageDiv.innerHTML = '<div style="padding: 1rem; background-color: #fee2e2; color: #991b1b; border-radius: 4px; border: 1px solid #fca5a5;">✗ Error: ' + errorMsg + '</div>';
      messageDiv.style.display = 'block';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Save Bank Settings';
    }
  });

  // Initialize pay frequency display when page loads
  window.loadSetup = function() {
    reloadPayFrequencyData();
    loadComponentsDisplay('salary');
    loadComponentsDisplay('deduction');
  };

  // ===== COMPONENT MANAGEMENT FUNCTIONS =====
  
  window.loadComponentsDisplay = async function(type) {
    const container = type === 'salary' ? document.getElementById('earnings-container') : document.getElementById('deductions-container');
    
    if (!container) return;
    
    try {
      const response = await fetch('/public_html/modules/payroll/setup_configuration_handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          action: 'get_components',
          type: type
        })
      });

      const result = await response.json();

      if (result.success && result.components && result.components.length > 0) {
        let html = '';
        result.components.forEach(comp => {
          html += `
            <div class="component-card" data-component-id="${comp.id}">
              <div class="component-info">
                <h4>${comp.name}</h4>
                <p>${comp.description || 'No description'}</p>
              </div>
              <div class="component-actions">
                <button type="button" class="btn-secondary btn-sm" onclick="window.editComponent(${comp.id}, '${type}')">Edit</button>
                <button type="button" class="btn-danger btn-sm" onclick="window.deleteComponentPrompt(${comp.id})">Delete</button>
              </div>
            </div>
          `;
        });
        container.innerHTML = html;
      } else {
        container.innerHTML = '<p style="color: #9ca3af; padding: 1rem; text-align: center;">No components configured yet.</p>';
      }
    } catch (error) {
      console.error('Error loading components:', error);
      container.innerHTML = '<p style="color: #ef4444; padding: 1rem; text-align: center;">Error loading components</p>';
    }
  }

  window.openComponentModal = function(type, isEdit = false, componentId = null) {
    const modal = document.getElementById('componentModal');
    const form = document.getElementById('componentForm');
    const title = document.getElementById('componentModalTitle');
    const codeField = document.getElementById('componentCode');
    const codeHint = document.getElementById('codeHint');
    const nameField = document.getElementById('componentName');
    const descField = document.getElementById('componentDesc');
    const categoryField = document.getElementById('componentCategory');
    const typeField = document.getElementById('componentType');
    const idField = document.getElementById('componentId');
    
    // Set component type
    typeField.value = type;
    
    if (!isEdit) {
      // Add mode
      title.textContent = 'Add ' + (type === 'salary' ? 'Earning' : 'Deduction') + ' Component';
      codeField.readOnly = false;
      codeField.style.backgroundColor = '#ffffff';
      codeHint.textContent = 'Enter a unique code (e.g., BASIC_PAY, SSS_CONTRIB)';
      codeField.value = '';
      nameField.value = '';
      descField.value = '';
      categoryField.value = '';
      idField.value = '';
      form.reset();
    } else {
      // Edit mode
      title.textContent = 'Edit Component';
      codeField.readOnly = true;
      codeField.style.backgroundColor = '#f3f4f6';
      codeHint.textContent = 'Component code cannot be changed';
      idField.value = componentId || '';
      // Component data will be loaded in editComponent function
    }
    
    modal.style.display = 'flex';
  }

  window.closeComponentModal = function() {
    document.getElementById('componentModal').style.display = 'none';
    document.getElementById('componentMessage').style.display = 'none';
    document.getElementById('componentForm').reset();
  }

  window.closeDeleteModal = function() {
    document.getElementById('deleteModal').style.display = 'none';
  };

  window.editComponent = function(componentId, type) {
    window.openComponentModal(type, true, componentId);
    
    // Fetch component data and populate form
    fetch('/public_html/modules/payroll/setup_configuration_handler.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        action: 'get_component',
        id: componentId
      })
    })
    .then(response => response.json())
    .then(result => {
      if (result.success && result.component) {
        const comp = result.component;
        document.getElementById('componentCode').value = comp.code;
        document.getElementById('componentName').value = comp.name;
        document.getElementById('componentDesc').value = comp.description || '';
        document.getElementById('componentCategory').value = comp.category || '';
      }
    })
    .catch(error => {
      console.error('Error loading component data:', error);
      alert('Error loading component details');
    });
  }

  window.deleteComponentPrompt = function(componentId) {
    window.pendingDeleteId = componentId;
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete this component? This action cannot be undone.';
    document.getElementById('deleteModal').style.display = 'flex';
  };

  // Component form submission
  document.getElementById('componentForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const componentId = document.getElementById('componentId').value;
    const componentType = document.getElementById('componentType').value;
    const componentCode = document.getElementById('componentCode').value;
    const componentName = document.getElementById('componentName').value;
    const componentDesc = document.getElementById('componentDesc').value;
    const componentCategory = document.getElementById('componentCategory').value;
    const messageDiv = document.getElementById('componentMessage');
    const submitBtn = this.querySelector('button[type="submit"]');

    if (!componentCode || !componentName) {
      messageDiv.innerHTML = '<div style="background-color: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 4px; border: 1px solid #fca5a5;">Code and name are required</div>';
      messageDiv.style.display = 'block';
      return;
    }

    try {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';

      const payload = {
        code: componentCode,
        name: componentName,
        description: componentDesc,
        component_type: componentType,
        category: componentCategory
      };

      const action = componentId ? 'edit_component' : 'add_component';
      if (componentId) {
        payload.id = componentId;
      }

      const response = await fetch('/public_html/modules/payroll/setup_configuration_handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({...payload, action: action})
      });

      const result = await response.json();

      if (result.success) {
        messageDiv.innerHTML = '<div style="background-color: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 4px; border: 1px solid #a7f3d0;">✓ ' + result.message + '</div>';
        messageDiv.style.display = 'block';
        
        setTimeout(() => {
          window.closeComponentModal();
          window.loadComponentsDisplay(componentType);
        }, 1500);
      } else {
        messageDiv.innerHTML = '<div style="background-color: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 4px; border: 1px solid #fca5a5;">✗ ' + (result.message || 'Error saving component') + '</div>';
        messageDiv.style.display = 'block';
      }
    } catch (error) {
      console.error('Component save error:', error);
      messageDiv.innerHTML = '<div style="background-color: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 4px; border: 1px solid #fca5a5;">✗ Error: ' + error.message + '</div>';
      messageDiv.style.display = 'block';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = componentId ? 'Update Component' : 'Save Component';
    }
  });

  // Delete confirmation
  document.getElementById('confirmDeleteBtn')?.addEventListener('click', async function() {
    const componentId = window.pendingDeleteId;
    
    if (!componentId) return;

    try {
      this.disabled = true;
      this.textContent = 'Deleting...';

      const response = await fetch('/public_html/modules/payroll/setup_configuration_handler.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          action: 'delete_component',
          id: componentId
        })
      });

      const result = await response.json();

      if (result.success) {
        window.closeDeleteModal();
        window.loadComponentsDisplay('salary');
        window.loadComponentsDisplay('deduction');
      } else {
        alert('Error: ' + (result.message || 'Failed to delete component'));
      }
    } catch (error) {
      console.error('Component delete error:', error);
      alert('Error deleting component: ' + error.message);
    } finally {
      this.disabled = false;
      this.textContent = 'Delete';
    }
  });

  // Add component button handlers
  document.getElementById('addEarningBtn')?.addEventListener('click', function() {
    window.openComponentModal('salary', false);
  });

  document.getElementById('addDeductionBtn')?.addEventListener('click', function() {
    window.openComponentModal('deduction', false);
  });

  // Initialize setup configuration on page load
  window.loadSetup = function() {
    // Load initial data if needed
    console.log('Setup configuration loaded');
  };

  // Initialize on page load
  window.addEventListener('load', function() {
    window.loadSetup();
  });

</script>
