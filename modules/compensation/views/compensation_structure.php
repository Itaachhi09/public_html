<?php
/**
 * Compensation Structure - Modern Tab-Based Design
 * Following Payroll module's top-level tab pattern
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/SalaryComponentDefinition.php';
require_once __DIR__ . '/../models/IncentiveType.php';
require_once __DIR__ . '/../models/BenefitDefinition.php';

$salaryComp = new SalaryComponentDefinition();
$incentiveType = new IncentiveType();
$benefitDef = new BenefitDefinition();

$salaryComponents = $salaryComp->getAll(false);
$incentiveComponents = $incentiveType->getAll(false);
$benefits = $benefitDef->getAll(false);

// Calculate statistics
$baseCompCount = count(array_filter($salaryComponents, fn($c) => $c['component_type'] === 'base'));
$allowanceCount = count(array_filter($salaryComponents, fn($c) => $c['component_type'] === 'allowance'));
$deductionCount = count(array_filter($salaryComponents, fn($c) => $c['component_type'] === 'deduction'));
$activeIncentives = count(array_filter($incentiveComponents, fn($i) => ($i['status'] ?? 'active') === 'active'));
$activeBenefits = count(array_filter($benefits, fn($b) => !empty($b['is_active'])));

$handlerUrl = 'modules/compensation/compensation_structure_handler.php';
$pageTitle = 'Compensation Structure';
require __DIR__ . '/partials/header.php';

// Helper function for enum options
function getEnumOptions($table, $column) {
    try {
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['COLUMN_TYPE'])) return [];
        if (preg_match("/^enum\\((.*)\\)$/i", $row['COLUMN_TYPE'], $m)) {
            preg_match_all("/'((?:[^']|\\\\')*)'/", $m[1], $matches);
            return $matches[1] ?? [];
        }
    } catch (Exception $e) {
        return [];
    }
    return [];
}
?>

<style>
  .setup-container {
    max-width: 1400px;
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
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

  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
  }

  .kpi-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
  }

  .kpi-card.salary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .kpi-card.incentive { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .kpi-card.benefits { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

  .kpi-value {
    font-size: 32px;
    font-weight: bold;
    margin: 8px 0;
  }

  .kpi-label {
    font-size: 13px;
    opacity: 0.9;
  }

  .table-container {
    overflow-x: auto;
    margin-bottom: 16px;
  }

  .table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .table thead {
    background: #f3f4f6;
  }

  .table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #1f2937;
    border-bottom: 2px solid #e5e7eb;
  }

  .table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
  }

  .table tbody tr:hover {
    background: #f9fafb;
  }

  .code {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
  }

  .badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
  }

  .badge-active {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-inactive {
    background: #fee2e2;
    color: #991b1b;
  }

  .btn {
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .btn-primary {
    background: #3b82f6;
    color: white;
  }

  .btn-primary:hover {
    background: #2563eb;
  }

  .btn-outline {
    border: 1px solid #d1d5db;
    background: white;
    color: #6b7280;
  }

  .btn-outline:hover {
    background: #f9fafb;
  }

  .btn-sm {
    padding: 4px 8px;
    font-size: 12px;
    height: 28px;
  }

  .btn-icon {
    padding: 4px 8px;
    min-width: auto;
  }

  .add-form {
    display: none;
    background: #f9fafb;
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    margin-bottom: 16px;
  }

  .add-form.visible {
    display: block;
  }

  .form-group {
    margin-bottom: 12px;
  }

  .form-group label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 4px;
    font-size: 13px;
  }

  .form-input,
  .form-select,
  .form-textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13px;
    font-family: inherit;
  }

  .form-input:focus,
  .form-select:focus,
  .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
  }

  .form-row.full {
    grid-template-columns: 1fr;
  }

  .form-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
  }

  .inline-form {
    display: contents;
  }

  .empty-state {
    padding: 32px;
    text-align: center;
    color: #6b7280;
    background: #f9fafb;
    border-radius: 4px;
  }

  .section-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .required {
    color: #ef4444;
  }
</style>

<main class="setup-container">
  <!-- Messages from session -->
  <?php
  $msg = $_SESSION['compensation_msg'] ?? null;
  $err = $_SESSION['compensation_err'] ?? null;
  ?>
  <?php if ($msg): ?>
    <div style="background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; color: #065f46;">
      <strong>✓ Success:</strong> <?php echo htmlspecialchars($msg); ?>
    </div>
    <?php unset($_SESSION['compensation_msg']); ?>
  <?php endif; ?>
  
  <?php if ($err): ?>
    <div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; color: #991b1b;">
      <strong>✕ Error:</strong> <?php echo htmlspecialchars($err); ?>
    </div>
    <?php unset($_SESSION['compensation_err']); ?>
  <?php endif; ?>

  <!-- KPI Cards -->
  <div class="kpi-grid">
    <div class="kpi-card salary">
      <div class="kpi-label">Salary Components</div>
      <div class="kpi-value"><?php echo count($salaryComponents); ?></div>
    </div>
    <div class="kpi-card incentive">
      <div class="kpi-label">Incentive Types</div>
      <div class="kpi-value"><?php echo count($incentiveComponents); ?></div>
    </div>
    <div class="kpi-card benefits">
      <div class="kpi-label">Benefits</div>
      <div class="kpi-value"><?php echo count($benefits); ?></div>
    </div>
  </div>

  <!-- Tabs Navigation -->
  <div class="setup-section" style="padding: 0; background: transparent; box-shadow: none;">
    <div class="tabs">
      <div class="tab active" onclick="window.switchTab(event, 'salary-tab')">
        💰 Salary Components
      </div>
      <div class="tab" onclick="window.switchTab(event, 'incentive-tab')">
        🎯 Incentive Types
      </div>
      <div class="tab" onclick="window.switchTab(event, 'benefits-tab')">
        🎁 Benefits
      </div>
    </div>
  </div>

  <!-- TAB 1: SALARY COMPONENTS -->
  <div id="salary-tab" class="tab-content active">
    <div class="setup-section">
      <div class="section-card-header">
        <h3 class="section-header" style="margin: 0; border: none; padding: 0;">Salary Components</h3>
        <button class="btn btn-primary btn-sm" onclick="window.toggleCompensationStructureForm('add-salary-form'); return false;">+ Add Component</button>
      </div>

      <!-- Add Form -->
      <div id="add-salary-form" class="add-form">
        <h4 style="margin: 0 0 12px 0;">New Salary Component</h4>
        <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
          <input type="hidden" name="action" value="create_salary_component">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Code <span class="required">*</span></label>
              <input type="text" name="code" required class="form-input" placeholder="BASE_PAY" maxlength="50">
            </div>
            <div class="form-group">
              <label class="form-label">Name <span class="required">*</span></label>
              <input type="text" name="name" required class="form-input" placeholder="Base Pay" maxlength="255">
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label class="form-label">Description <span class="required">*</span></label>
              <textarea name="description" required class="form-textarea" placeholder="Describe this component..." maxlength="500" style="min-height: 60px; resize: vertical;"></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Type</label>
              <select name="component_type" class="form-select">
                <option value="base">Base</option>
                <option value="allowance" selected>Allowance</option>
                <option value="deduction">Deduction</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">
                <input type="checkbox" name="taxable" value="1" style="width: auto; margin-right: 4px;">
                <span>Taxable</span>
              </label>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-sm">Create Component</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="window.toggleCompensationStructureForm('add-salary-form'); return false;">Cancel</button>
          </div>
        </form>
      </div>

      <!-- Salary Components Table -->
      <?php if (empty($salaryComponents)): ?>
      <div class="empty-state">No salary components defined. Add your first component to get started.</div>
      <?php else: ?>
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 12%;">Code</th>
              <th style="width: 20%;">Name</th>
              <th style="width: 35%;">Description</th>
              <th style="width: 12%;">Type</th>
              <th style="width: 10%;">Taxable</th>
              <th style="width: 11%;">Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($salaryComponents as $s): ?>
          <tr>
            <td><span class="code"><?php echo htmlspecialchars($s['code']); ?></span></td>
            <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
            <td style="color: #6b7280; font-size: 12px;"><?php echo htmlspecialchars(substr($s['description'] ?? '', 0, 60)); ?><?php if (strlen($s['description'] ?? '') > 60) echo '...'; ?></td>
            <td><span style="background: #f0f9ff; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $s['component_type'] ?? 'Allowance'))); ?></span></td>
            <td><?php echo !empty($s['taxable']) ? '<span style="color: #10b981;">✓ Yes</span>' : '<span style="color: #9ca3af;">✗ No</span>'; ?></td>
            <td><span class="badge badge-<?php echo !empty($s['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($s['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- TAB 2: INCENTIVE TYPES -->
  <div id="incentive-tab" class="tab-content">
    <div class="setup-section">
      <div class="section-card-header">
        <h3 class="section-header" style="margin: 0; border: none; padding: 0;">Incentive Types</h3>
        <button class="btn btn-primary btn-sm" onclick="window.toggleCompensationStructureForm('add-incentive-form'); return false;">+ Add Incentive</button>
      </div>

      <!-- Add Form -->
      <div id="add-incentive-form" class="add-form">
        <h4 style="margin: 0 0 12px 0;">New Incentive Type</h4>
        <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
          <input type="hidden" name="action" value="create_incentive_component">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Code <span class="required">*</span></label>
              <input type="text" name="code" required class="form-input" placeholder="ADMISSION" maxlength="50">
            </div>
            <div class="form-group">
              <label class="form-label">Name <span class="required">*</span></label>
              <input type="text" name="name" required class="form-input" placeholder="Admission Incentive" maxlength="255">
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label class="form-label">Description <span class="required">*</span></label>
              <textarea name="description" required class="form-textarea" placeholder="Describe this incentive..." style="min-height: 60px; resize: vertical;"></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Rate Type</label>
              <select name="rate_type" class="form-select">
                <option value="per_case" selected>Per Case</option>
                <option value="fixed_amount">Fixed Amount</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Default Rate (₱)</label>
              <input type="number" name="default_rate" class="form-input" placeholder="0.00" step="0.01" min="0">
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-sm">Create Incentive</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="window.toggleCompensationStructureForm('add-incentive-form'); return false;">Cancel</button>
          </div>
        </form>
      </div>

      <!-- Incentive Types Table -->
      <?php if (empty($incentiveComponents)): ?>
      <div class="empty-state">No incentive types defined. Add your first incentive type to get started.</div>
      <?php else: ?>
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 12%;">Code</th>
              <th style="width: 25%;">Name</th>
              <th style="width: 30%;">Description</th>
              <th style="width: 15%;">Rate Type</th>
              <th style="width: 10%;">Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($incentiveComponents as $i): 
            $status = $i['status'] ?? 'active';
            $statusClass = $status === 'archived' ? 'archived' : ($status === 'inactive' ? 'inactive' : 'active');
          ?>
          <tr>
            <td><span class="code"><?php echo htmlspecialchars($i['code']); ?></span></td>
            <td><strong><?php echo htmlspecialchars($i['name']); ?></strong></td>
            <td style="color: #6b7280; font-size: 12px;"><?php echo htmlspecialchars(substr($i['description'] ?? '', 0, 60)); ?><?php if (strlen($i['description'] ?? '') > 60) echo '...'; ?></td>
            <td><span style="background: #f0f9ff; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $i['rate_type'] ?? 'Per Case'))); ?></span></td>
            <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- TAB 3: BENEFITS -->
  <div id="benefits-tab" class="tab-content">
    <div class="setup-section">
      <div class="section-card-header">
        <h3 class="section-header" style="margin: 0; border: none; padding: 0;">Benefits</h3>
        <button class="btn btn-primary btn-sm" onclick="window.toggleCompensationStructureForm('add-benefit-form'); return false;">+ Add Benefit</button>
      </div>

      <!-- Add Form -->
      <div id="add-benefit-form" class="add-form">
        <h4 style="margin: 0 0 12px 0;">New Benefit</h4>
        <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
          <input type="hidden" name="action" value="create_benefit">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Code <span class="required">*</span></label>
              <input type="text" name="code" required class="form-input" placeholder="FREE_MEALS_TID" maxlength="50">
            </div>
            <div class="form-group">
              <label class="form-label">Name <span class="required">*</span></label>
              <input type="text" name="name" required class="form-input" placeholder="Free Meals" maxlength="255">
            </div>
          </div>
          <div class="form-row full">
            <div class="form-group">
              <label class="form-label">Description <span class="required">*</span></label>
              <textarea name="description" required class="form-textarea" placeholder="Describe this benefit..." style="min-height: 60px; resize: vertical;"></textarea>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Effective From <span class="required">*</span></label>
              <input type="date" name="effective_from" required class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Effective To</label>
              <input type="date" name="effective_to" class="form-input">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">
                <input type="checkbox" name="taxable" value="1" style="width: auto; margin-right: 4px;">
                <span>Taxable</span>
              </label>
            </div>
            <div class="form-group">
              <label class="form-label">Attach To</label>
              <select name="attach_to" class="form-select">
                <option value="role" selected>Role</option>
                <option value="duty">Duty</option>
              </select>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-sm">Create Benefit</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="window.toggleCompensationStructureForm('add-benefit-form'); return false;">Cancel</button>
          </div>
        </form>
      </div>

      <!-- Benefits Table -->
      <?php if (empty($benefits)): ?>
      <div class="empty-state">No benefits defined. Add your first benefit to get started.</div>
      <?php else: ?>
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 12%;">Code</th>
              <th style="width: 20%;">Name</th>
              <th style="width: 28%;">Description</th>
              <th style="width: 12%;">Category</th>
              <th style="width: 10%;">Taxable</th>
              <th style="width: 10%;">Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($benefits as $b): ?>
          <tr>
            <td><span class="code"><?php echo htmlspecialchars($b['code']); ?></span></td>
            <td><strong><?php echo htmlspecialchars($b['name']); ?></strong></td>
            <td style="color: #6b7280; font-size: 12px;"><?php echo htmlspecialchars(substr($b['description'] ?? '', 0, 60)); ?><?php if (strlen($b['description'] ?? '') > 60) echo '...'; ?></td>
            <td><span style="background: #f3e8ff; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $b['benefit_category'] ?? 'Non Cash'))); ?></span></td>
            <td><?php echo !empty($b['taxable']) ? '<span style="color: #10b981;">✓ Yes</span>' : '<span style="color: #9ca3af;">✗ No</span>'; ?></td>
            <td><span class="badge badge-<?php echo !empty($b['is_active']) ? 'active' : 'inactive'; ?>"><?php echo !empty($b['is_active']) ? 'Active' : 'Inactive'; ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
// Toggle form visibility - namespaced to window to prevent collisions
window.toggleCompensationStructureForm = function(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('visible');
        if (form.classList.contains('visible')) {
            setTimeout(() => form.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100);
        }
    }
};

// Tab switching function - matches Payroll module pattern
window.switchTab = function(event, tabName) {
    if (event && event.preventDefault) {
        event.preventDefault();
    }
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tabs .tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Add active class to clicked tab
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
    
    // Hide all tab content
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById(tabName);
    if (selectedContent) {
        selectedContent.classList.add('active');
    }
};

// Handle all compensation structure forms with AJAX
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[action="<?php echo htmlspecialchars($handlerUrl); ?>"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                // Show message
                let messageHtml = '';
                if (data.success) {
                    messageHtml = `<div style="background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; color: #065f46; display: flex; justify-content: space-between; align-items: center;">
                        <div><strong>✓ Success:</strong> ${data.message}</div>
                        <button type="button" onclick="this.closest('div').remove();" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #065f46;">×</button>
                    </div>`;
                } else {
                    messageHtml = `<div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; color: #991b1b; display: flex; justify-content: space-between; align-items: center;">
                        <div><strong>✕ Error:</strong> ${data.message}</div>
                        <button type="button" onclick="this.closest('div').remove();" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #991b1b;">×</button>
                    </div>`;
                }
                
                // Insert message at top of main
                const main = document.querySelector('main.setup-container');
                if (main) {
                    // Remove old messages
                    const oldMessages = main.querySelectorAll('div[style*="background: #d1fae5"], div[style*="background: #fee2e2"]');
                    oldMessages.forEach(msg => msg.remove());
                    
                    // Insert new message
                    main.insertAdjacentHTML('afterbegin', messageHtml);
                    main.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                
                // Reset form and hide it on success
                if (data.success) {
                    this.reset();
                    // Hide the form
                    const formContainer = this.closest('.add-form');
                    if (formContainer) {
                        formContainer.classList.remove('visible');
                    }
                    
                    // Optionally reload page after 2 seconds to refresh the data
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
                
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
