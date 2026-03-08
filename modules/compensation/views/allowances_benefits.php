<?php
/**
 * Allowances and Benefits - Modern Design
 * Manage non-salary compensation benefits
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/BenefitDefinition.php';

$benefitModel = new BenefitDefinition();
$allBenefits = $benefitModel->getAll(false);

// Calculate status counts
$activeCount = count(array_filter($allBenefits, fn($b) => !empty($b['is_active'])));
$inactiveCount = count(array_filter($allBenefits, fn($b) => empty($b['is_active'])));

$handlerUrl = 'modules/compensation/allowances_benefits_handler.php';
$pageTitle = 'Allowances & Benefits';
require __DIR__ . '/partials/header.php';

// Helper to read enum options from DB
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
.page-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px;
}

.msg-bar {
  padding: 12px 24px;
  margin-bottom: 24px;
  border-radius: 8px;
  display: flex;
  gap: 8px;
}

.msg-bar .msg {
  background: #d1fae5;
  color: #065f46;
  padding: 12px 16px;
  border-radius: 8px;
  flex: 1;
}

.msg-bar .err {
  background: #fee2e2;
  color: #991b1b;
  padding: 12px 16px;
  border-radius: 8px;
  flex: 1;
}

.page-header {
  background: white;
  padding: 24px;
  margin-bottom: 24px;
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.header-content h1 {
  margin: 0 0 4px 0;
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

.header-content p {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.header-actions button {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
}

.header-actions button:hover {
  background: #2563eb;
  box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

.search-filters {
  background: white;
  padding: 16px 24px;
  margin-bottom: 24px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 250px;
  position: relative;
}

.search-box input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.search-box input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.filter-tabs {
  display: flex;
  gap: 12px;
}

.filter-btn {
  padding: 8px 16px;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  font-size: 13px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #f0f9ff;
}

.filter-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.section-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e5e7eb;
}

.section-card-title {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.table-responsive {
  overflow-x: auto;
}

.benefits-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.benefits-table thead {
  background: #f9fafb;
}

.benefits-table th {
  padding: 12px 16px;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.benefits-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}

.benefits-table tbody tr:hover {
  background: #f9fafb;
}

.benefits-table .code-badge {
  background: #f3f4f6;
  padding: 4px 8px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 12px;
  font-weight: 600;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.status-active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.status-inactive {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.action-btn {
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
}

.empty-message {
  text-align: center;
  padding: 48px 24px;
  color: #9ca3af;
  font-size: 14px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 13px;
}

.add-form {
  display: none;
  background: #f9fafb;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  margin-bottom: 20px;
}

.add-form.visible {
  display: block;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
  position: relative;
  z-index: 100004;
}

.form-row.full {
  grid-template-columns: 1fr;
}

.form-row.three {
  grid-template-columns: 1fr 1fr 1fr;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  position: relative;
  z-index: 100005;
}

.form-label {
  font-weight: 600;
  color: #374151;
  font-size: 13px;
}

.required {
  color: #ef4444;
}

.form-input,
.form-select,
.form-textarea {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  width: 100%;
  box-sizing: border-box;
  pointer-events: auto;
  position: relative;
  z-index: 100010;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.checkbox-inline {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  font-size: 14px;
}

.checkbox-inline input[type="checkbox"] {
  cursor: pointer;
  width: 18px;
  height: 18px;
}

.info-box {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  padding: 12px 16px;
  border-radius: 4px;
  margin-bottom: 16px;
  font-size: 13px;
  color: #1e40af;
}
</style>

<?php 
$msg = $_GET['msg'] ?? $_SESSION['allowances_benefits_msg'] ?? null;
$err = $_GET['err'] ?? $_SESSION['allowances_benefits_err'] ?? null;
?>
<?php if (!empty($msg) || !empty($err)): ?>
<div class="page-container">
  <div class="msg-bar">
    <?php if (!empty($msg)): ?>
    <div class="msg"><?php echo htmlspecialchars(is_string($msg) && strpos($msg, '%') !== false ? urldecode($msg) : $msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($err)): ?>
    <div class="err"><?php echo htmlspecialchars(is_string($err) && strpos($err, '%') !== false ? urldecode($err) : $err); ?></div>
    <?php endif; ?>
  </div>
</div>
<?php
// Clear session variables after display
unset($_SESSION['allowances_benefits_msg'], $_SESSION['allowances_benefits_err']);
?>
<?php endif; ?>

<div class="page-container">

  <!-- Page Header -->
  <div class="page-header">
    <div class="header-content">
      <h1>Allowances & Benefits</h1>
      <p>Define benefits that attach to duties or roles</p>
    </div>
    <div class="header-actions">
      <button onclick="openBenefitModal(); return false;">+ Add Benefit</button>
    </div>
  </div>

  <!-- Info Box -->
  <div class="info-box">
    <strong>📋 Info:</strong> Benefits attach to duty or role. Payroll reads as reference only.
  </div>

  <!-- Search & Filters -->
  <div class="search-filters">
    <div class="search-box">
      <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
      <input id="comp-search" type="text" placeholder="Search benefits, codes, roles..." onkeypress="if(event.key==='Enter') performCompSearch();">
    </div>
    <div class="filter-tabs">
      <button class="filter-btn active" onclick="window.filterAllowancesBenefits('all'); return false;">All (<?php echo count($allBenefits); ?>)</button>
      <button class="filter-btn" onclick="window.filterAllowancesBenefits('active'); return false;">Active (<?php echo $activeCount; ?>)</button>
      <button class="filter-btn" onclick="window.filterAllowancesBenefits('inactive'); return false;">Inactive (<?php echo $inactiveCount; ?>)</button>
    </div>
  </div>

  <!-- Benefit Definitions Table -->
  <div class="section-card">
    <div class="section-card-header">
      <h3 class="section-card-title">Benefit Definitions</h3>
    </div>

    <?php if (empty($allBenefits)): ?>
    <div class="empty-message">No benefits defined yet. Create one to get started.</div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="benefits-table" id="benefits-table-body">
        <thead>
          <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Taxable</th>
            <th>Eligible Roles</th>
            <th>Period</th>
            <th>Attach To</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allBenefits as $b): 
            $roles = $b['eligible_roles'] ?? 'All';
            $roleParts = explode(',', $roles);
            $firstRole = trim($roleParts[0]);
            $moreCount = count($roleParts) - 1;
          ?>
          <tr class="benefit-row" data-active="<?php echo empty($b['is_active']) ? 'no' : 'yes'; ?>" data-benefit-id="<?php echo (int)$b['id']; ?>">
            <td><span class="code-badge"><?php echo htmlspecialchars($b['code']); ?></span></td>
            <td><?php echo htmlspecialchars($b['name']); ?></td>
            <td><?php echo !empty($b['taxable']) ? '✓ Yes' : 'No'; ?></td>
            <td>
              <?php if ($moreCount > 0): ?>
              <span title="<?php echo htmlspecialchars($roles); ?>"><?php echo htmlspecialchars($firstRole); ?> +<?php echo $moreCount; ?></span>
              <?php else: ?>
              <?php echo htmlspecialchars($firstRole); ?>
              <?php endif; ?>
            </td>
            <td><?php 
              $period = htmlspecialchars($b['effective_from']);
              if (!empty($b['effective_to'])) {
                $period .= ' – ' . htmlspecialchars($b['effective_to']);
              }
              echo $period;
            ?></td>
            <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $b['attach_to']))); ?></td>
            <td>
              <span class="status-badge status-<?php echo !empty($b['is_active']) ? 'active' : 'inactive'; ?>">
                <?php echo !empty($b['is_active']) ? '✓ Active' : '✕ Inactive'; ?>
              </span>
            </td>
            <td class="action-buttons">
              <?php if (!empty($b['is_active'])): ?>
              <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" class="ajax-form" style="display: inline;">
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                <button type="submit" class="action-btn" title="Deactivate">Deactivate</button>
              </form>
              <?php else: ?>
              <span style="color: #d1d5db; font-size: 13px;">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Benefit Modal -->
<div id="benefitModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: flex-start; overflow-y: auto; padding-top: 20px; padding-bottom: 20px;">
    <div style="background: white; border-radius: 8px; max-width: 700px; width: 95%; box-shadow: 0 20px 25px rgba(0,0,0,0.15); position: relative; z-index: 100000;">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 2rem; border-bottom: 2px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 100001;">
            <h2 style="font-size: 1.5rem; margin: 0; color: #1f2937;">+ Add Benefit</h2>
            <button onclick="closeBenefitModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; flex-shrink: 0; z-index: 100002;">✕</button>
        </div>

        <!-- Modal Body -->
        <div style="padding: 2rem; overflow-y: auto; max-height: calc(100vh - 200px); position: relative; z-index: 100000;">
            <form id="benefitForm" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" onsubmit="return handleBenefitSubmit(event)" style="position: relative; z-index: 100000;">
                <input type="hidden" name="action" value="create">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Code <span class="required">*</span></label>
                        <input type="text" name="code" required class="form-input" placeholder="FREE_MEALS" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Name <span class="required">*</span></label>
                        <input type="text" name="name" required class="form-input" placeholder="Free Meals Allowance" maxlength="255">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Attach To <span class="required">*</span></label>
                        <select name="attach_to" required class="form-select" onchange="toggleRoleField()">
                            <option value="">Select</option>
                            <?php
                            $attachOpts = getEnumOptions('benefit_definitions', 'attach_to');
                            if (!empty($attachOpts)) {
                                foreach ($attachOpts as $ao) {
                                    echo '<option value="' . htmlspecialchars($ao) . '">' . htmlspecialchars(ucwords(str_replace('_',' ',$ao))) . '</option>';
                                }
                            } else {
                                ?>
                                <option value="duty">Duty</option>
                                <option value="role">Role</option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group" id="roles-field">
                        <label class="form-label">Eligible Roles</label>
                        <input type="text" name="eligible_roles" class="form-input" placeholder="Nurse, Doctor or All" maxlength="500" value="All">
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

                <div class="form-row full">
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" placeholder="e.g., Free meals three times daily on duty"></textarea>
                    </div>
                </div>

                <div class="form-row full">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="taxable" value="1">
                        Taxable Income
                    </label>
                </div>

                <!-- Modal Footer -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="btn btn-sm" onclick="closeBenefitModal(); return false;" style="background: #e5e7eb; color: #1f2937; flex: 0 0 auto;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="flex: 0 0 auto;">Create Benefit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open Benefit Modal
function openBenefitModal() {
    const modal = document.getElementById('benefitModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Close Benefit Modal
function closeBenefitModal() {
    const modal = document.getElementById('benefitModal');
    if (modal) {
        modal.style.display = 'none';
        // Reset form
        const form = document.getElementById('benefitForm');
        if (form) form.reset();
    }
}

// Handle form submission with AJAX
function handleBenefitSubmit(event) {
    event.preventDefault();
    
    const form = document.getElementById('benefitForm');
    const formData = new FormData(form);
    
    fetch('<?php echo htmlspecialchars($handlerUrl); ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message and close modal
            alert('✓ ' + data.message);
            closeBenefitModal();
            
            // Reload the page to update the table
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            // Show error message
            alert('✕ ' + (data.message || 'An error occurred'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: ' + error);
    });
    
    return false;
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('benefitModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeBenefitModal();
            }
        });
    }
});

function toggleRoleField() {
    const attachTo = document.querySelector('select[name="attach_to"]').value;
    const rolesField = document.getElementById('roles-field');
    rolesField.style.display = (attachTo === 'duty') ? 'none' : 'flex';
}

function filterAllowancesBenefits(status) {
    const rows = document.querySelectorAll('.benefit-row');
    rows.forEach(row => {
        const isActive = row.dataset.active === 'yes';
        if (status === 'all') {
            row.style.display = '';
        } else if (status === 'active' && isActive) {
            row.style.display = '';
        } else if (status === 'inactive' && !isActive) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update filter button states
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

// Expose to window for HTML onclick handlers
window.filterAllowancesBenefits = filterAllowancesBenefits;

function filterBenefits(status) {
    filterAllowancesBenefits(status);
}

function performCompSearch(){
    var q = document.getElementById('comp-search')?.value.toLowerCase() || '';
    var rows = document.querySelectorAll('.benefit-row');
    
    rows.forEach(row => {
        var text = row.textContent.toLowerCase();
        if (q === '' || text.indexOf(q) !== -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Handle all allowances & benefits forms with AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation to handle forms dynamically
    document.addEventListener('submit', async function(e) {
        const form = e.target;
        
        // Only handle forms with ajax-form class and correct action
        if (!form.classList.contains('ajax-form')) return;
        if (!form.action.includes('allowances_benefits_handler.php')) return;
        
        e.preventDefault();
        
        const formData = new FormData(form);
        const action = formData.get('action');
        
        try {
            const response = await fetch(form.action, {
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
                messageHtml = `<div style="background: #d1fae5; border: 1px solid #6ee7b7; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; color: #065f46; display: flex; justify-content: space-between; align-items: center; animation: slideDown 0.3s ease;">
                    <div><strong>✓ Success:</strong> ${data.message || (action === 'create' ? 'Benefit created successfully!' : 'Benefit deactivated successfully!')}</div>
                    <button type="button" onclick="this.closest('div').remove();" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #065f46;">×</button>
                </div>`;
            } else {
                messageHtml = `<div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; color: #991b1b; display: flex; justify-content: space-between; align-items: center; animation: slideDown 0.3s ease;">
                    <div><strong>✕ Error:</strong> ${data.message || data.error || 'An error occurred'}</div>
                    <button type="button" onclick="this.closest('div').remove();" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #991b1b;">×</button>
                </div>`;
            }
            
            // Find the page header or first section for message placement
            let messageContainer = document.querySelector('.page-header');
            if (!messageContainer) {
                messageContainer = document.querySelector('.search-filters');
            }
            if (!messageContainer) {
                messageContainer = document.querySelector('.section-card');
            }
            
            if (messageContainer && messageContainer.parentNode) {
                // Remove old messages
                const oldMessages = document.querySelectorAll('div[style*="background: #d1fae5"], div[style*="background: #fee2e2"]');
                oldMessages.forEach(msg => msg.remove());
                
                // Insert new message before target container
                messageContainer.insertAdjacentHTML('beforebegin', messageHtml);
                messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                // Fallback: show at top of body
                document.body.insertAdjacentHTML('afterbegin', messageHtml);
            }
            
            // Reset form and hide it on success
            if (data.success) {
                form.reset();
                
                // Hide the form
                const formContainer = form.closest('.add-form') || form.closest('.edit-form');
                if (formContainer) {
                    formContainer.style.display = 'none';
                }
                
                // For deactivate: update the UI immediately
                if (action === 'deactivate') {
                    const benefitId = formData.get('id');
                    if (benefitId) {
                        // Find and update the row
                        const rows = document.querySelectorAll('[data-benefit-id]');
                        rows.forEach(row => {
                            if (row.dataset.benefitId == benefitId) {
                                // Update status badge
                                const statusBadge = row.querySelector('.status-badge');
                                if (statusBadge) {
                                    statusBadge.className = 'status-badge status-inactive';
                                    statusBadge.innerHTML = '✕ Inactive';
                                }
                                
                                // Hide deactivate button
                                const deactivateForm = row.querySelector('form');
                                if (deactivateForm) {
                                    deactivateForm.style.display = 'none';
                                }
                                
                                // Show placeholder
                                const actionButtons = row.querySelector('.action-buttons');
                                if (actionButtons && !actionButtons.querySelector('span')) {
                                    const placeholder = document.createElement('span');
                                    placeholder.style.cssText = 'color: #d1d5db; font-size: 13px;';
                                    placeholder.textContent = '—';
                                    actionButtons.appendChild(placeholder);
                                }
                            }
                        });
                    }
                } else if (action === 'create') {
                    // For create: reload to show the new benefit in the table
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            }
            
        } catch (error) {
            console.error('Error:', error);
            const errorHtml = `<div style="background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; padding: 12px 16px; margin-bottom: 16px; color: #991b1b; display: flex; justify-content: space-between; align-items: center;">
                <div><strong>✕ Error:</strong> An error occurred while processing your request. Please try again.</div>
                <button type="button" onclick="this.closest('div').remove();" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #991b1b;">×</button>
            </div>`;
            document.body.insertAdjacentHTML('afterbegin', errorHtml);
        }
    });
});

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
