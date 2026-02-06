<!-- Payslips Content -->
<main class="main-content employee-directory-container">
  <div class="directory-layout">
    <div class="directory-left-column">
      <!-- KPI Cards -->
      <section class="kpi-section">
        <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #dbeafe; border-radius: 8px; flex-shrink: 0;">📄</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Total Payslips</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="totalPayslips">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">All periods</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #d1fae5; border-radius: 8px; flex-shrink: 0;">✓</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Generated</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="generatedPayslips">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">This month</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #fef3c7; border-radius: 8px; flex-shrink: 0;">💰</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Total Net Pay</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="totalNetPay">₱0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">This month</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #fce7f3; border-radius: 8px; flex-shrink: 0;">🔄</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Reissued</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="reissuedPayslips">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">This month</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Filters -->
      <section class="filters-section">
        <div class="card">
          <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
            <div class="form-group">
              <label class="form-label">Search</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Employee name, payslip number..." style="padding-left: 36px;">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Period</label>
              <input type="month" id="periodFilter" class="form-input" value="<?php echo date('Y-m'); ?>">
            </div>

            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-outline btn-sm" onclick="window.refreshPayslips()">↻ Refresh</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Payslips Table -->
      <section class="table-section">
        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Payslips</h3>
              <p class="card-subtitle">Showing <strong id="totalCount">0</strong> payslips</p>
            </div>
          </div>

          <div class="table-container">
            <table class="table table-striped" id="payslipsTable">
              <thead class="table-head-fixed">
                <tr>
                  <th style="width: 12%;">Payslip #</th>
                  <th style="width: 15%;">Employee</th>
                  <th style="width: 12%;">Period</th>
                  <th style="width: 10%;">Pay Date</th>
                  <th style="width: 12%;">Gross Pay</th>
                  <th style="width: 12%;">Deductions</th>
                  <th style="width: 12%;">Net Pay</th>
                  <th style="width: 10%;">Status</th>
                  <th style="width: 5%;">Actions</th>
                </tr>
              </thead>
              <tbody id="payslipsList">
                <tr>
                  <td colspan="9" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">⏳</div>
                      <p style="font-size: 14px; font-weight: 500;">Loading payslips...</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Payslip Details Modal -->
<div id="payslipDetailsModal" class="modal" style="display: none;">
  <div class="modal-content" style="max-width: 800px;">
    <div class="modal-header">
      <h3 id="payslipDetailsTitle">Payslip Details</h3>
      <button class="modal-close" onclick="window.closePayslipDetails()">&times;</button>
    </div>
    <div class="modal-body">
      <div id="payslipDetailsContent">
        <!-- Content loaded dynamically -->
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="window.closePayslipDetails()">Close</button>
      <button class="btn btn-primary" onclick="window.downloadPayslipPDF()" id="downloadBtn">Download PDF</button>
      <button class="btn btn-warning" onclick="window.reissuePayslip()" id="reissueBtn" style="display: none;">Reissue</button>
    </div>
  </div>
</div>

<!-- Reissue Payslip Modal -->
<div id="reissueModal" class="modal" style="display: none;">
  <div class="modal-content" style="max-width: 500px;">
    <div class="modal-header">
      <h3>Reissue Payslip</h3>
      <button class="modal-close" onclick="window.closeReissueModal()">&times;</button>
    </div>
    <div class="modal-body">
      <form id="reissueForm">
        <div class="form-group">
          <label class="form-label">Reason for Reissue *</label>
          <textarea id="reissueReason" class="form-input" rows="4" required placeholder="Enter reason for reissuing this payslip..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="window.closeReissueModal()">Cancel</button>
      <button class="btn btn-warning" onclick="window.submitReissue()">Reissue Payslip</button>
    </div>
  </div>
</div>
</main>

<script>
// Use namespaced object to avoid redeclaration errors
if (typeof window.payslipsModule === 'undefined') {
  window.payslipsModule = {
    payslips: [],
    currentPayslipId: null,
    currentEmployeeId: null
  };
}

// Load on page ready
(function() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      loadPayslips();
    });
  } else {
    loadPayslips();
  }
})();

// Load payslips
async function loadPayslips() {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      console.warn('No token found, API calls may fail');
    }
    
    const periodFilterEl = document.getElementById('periodFilter');
    const period = periodFilterEl ? periodFilterEl.value : '';
    
    const tbody = document.getElementById('payslipsList');
    if (!tbody) return;
    
    // Get current user's employee ID if employee role
    // For admin/hr, show all payslips
    // This would need to be determined from user session
    
    // For now, we'll need an endpoint to get all payslips for a period
    // Since we don't have that, we'll show a message
    // In production, this would fetch from: getPayslipsByPeriod endpoint
    
    tbody.innerHTML = `
      <tr>
        <td colspan="9" style="text-align: center; padding: 3rem;">
          <div style="color: var(--text-light);">
            <div style="font-size: 48px; margin-bottom: 1rem;">📋</div>
            <p style="font-size: 14px; font-weight: 500; margin-bottom: 0.5rem;">No payslips found</p>
            <p style="font-size: 12px; color: #9ca3af;">Payslips are generated automatically when payroll runs are approved.</p>
          </div>
        </td>
      </tr>
    `;
    
  } catch (error) {
    console.error('Error loading payslips:', error);
    const tbody = document.getElementById('payslipsList');
    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" style="text-align: center; padding: 3rem;">
            <div style="color: #ef4444;">
              <div style="font-size: 48px; margin-bottom: 1rem;">⚠️</div>
              <p>Error loading payslips: ${error.message}</p>
            </div>
          </td>
        </tr>
      `;
    }
  }
}

// View payslip details
async function viewPayslip(payslipId, employeeId) {
  window.payslipsModule.currentPayslipId = payslipId;
  window.payslipsModule.currentEmployeeId = employeeId;
  
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=getPayslip&id=${payslipId}&employee_id=${employeeId}`, {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      const payslip = result.data;
      
      document.getElementById('payslipDetailsTitle').textContent = `Payslip: ${payslip.payslip_number}`;
      
      let content = `
        <div style="margin-bottom: 2rem;">
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
            <div>
              <strong>Employee:</strong> ${payslip.employee_name}<br>
              <strong>Code:</strong> ${payslip.employee_code}<br>
              <strong>Department:</strong> ${payslip.department_name || 'N/A'}
            </div>
            <div>
              <strong>Period:</strong> ${formatDate(payslip.period_start)} - ${formatDate(payslip.period_end)}<br>
              <strong>Pay Date:</strong> ${formatDate(payslip.pay_date)}<br>
              <strong>Status:</strong> <span class="status-badge status-${payslip.status.toLowerCase()}">${payslip.status}</span>
            </div>
          </div>
        </div>
        
        <div style="margin-bottom: 2rem;">
          <h4>Earnings</h4>
          <table class="table" style="margin-top: 0.5rem;">
            <thead>
              <tr>
                <th>Type</th>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
              </tr>
            </thead>
            <tbody>
              ${(payslip.earnings || []).map(e => `
                <tr>
                  <td>${e.earning_type}</td>
                  <td>${e.earning_name}</td>
                  <td style="text-align: right;">${formatCurrency(e.amount)}</td>
                </tr>
              `).join('')}
              <tr style="font-weight: bold; border-top: 2px solid #ddd;">
                <td colspan="2">Total Gross Pay</td>
                <td style="text-align: right;">${formatCurrency(payslip.gross_pay)}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div style="margin-bottom: 2rem;">
          <h4>Deductions</h4>
          <table class="table" style="margin-top: 0.5rem;">
            <thead>
              <tr>
                <th>Type</th>
                <th>Description</th>
                <th style="text-align: right;">Amount</th>
              </tr>
            </thead>
            <tbody>
              ${(payslip.deductions || []).map(d => `
                <tr>
                  <td>${d.deduction_type}</td>
                  <td>${d.deduction_name}</td>
                  <td style="text-align: right;">${formatCurrency(d.amount)}</td>
                </tr>
              `).join('')}
              <tr style="font-weight: bold; border-top: 2px solid #ddd;">
                <td colspan="2">Total Deductions</td>
                <td style="text-align: right;">${formatCurrency(payslip.total_deductions)}</td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div style="background: #f5f5f5; padding: 1rem; border-radius: 8px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Net Pay</h3>
            <h2 style="margin: 0; color: #059669;">${formatCurrency(payslip.net_pay)}</h2>
          </div>
        </div>
      `;
      
      document.getElementById('payslipDetailsContent').innerHTML = content;
      
      // Show reissue button for admin/payroll
      const userRole = localStorage.getItem('userRole') || '';
      const reissueBtn = document.getElementById('reissueBtn');
      if (reissueBtn) {
        reissueBtn.style.display = 
          (userRole === 'admin' || userRole === 'payroll') ? 'inline-block' : 'none';
      }
      
      const payslipDetailsModal = document.getElementById('payslipDetailsModal');
      if (payslipDetailsModal) {
        payslipDetailsModal.style.display = 'flex';
      }
    }
  } catch (error) {
    console.error('Error loading payslip:', error);
    alert('Error loading payslip details');
  }
}

// Download payslip PDF
function downloadPayslipPDF() {
  if (!window.payslipsModule.currentPayslipId) return;
  
  // In production, this would generate and download PDF
  // For now, show alert
  alert('PDF download functionality would be implemented here');
  
  // Example implementation:
  // window.open(`modules/payroll/controllers/PayrollController.php?action=downloadPayslipPDF&id=${window.payslipsModule.currentPayslipId}`, '_blank');
}

// Reissue payslip
function reissuePayslip() {
  if (!window.payslipsModule.currentPayslipId) return;
  const reissueModal = document.getElementById('reissueModal');
  if (reissueModal) {
    reissueModal.style.display = 'flex';
  }
}

// Submit reissue
async function submitReissue() {
  const form = document.getElementById('reissueForm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  
  if (!window.payslipsModule.currentPayslipId) return;
  
  try {
    const token = localStorage.getItem('token');
    const data = {
      id: window.payslipsModule.currentPayslipId,
      reason: document.getElementById('reissueReason').value
    };
    
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=reissuePayslip&id=${window.payslipsModule.currentPayslipId}`, {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ reason: document.getElementById('reissueReason').value })
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      alert('Payslip reissued successfully');
      closeReissueModal();
      viewPayslip(window.payslipsModule.currentPayslipId, window.payslipsModule.currentEmployeeId);
      loadPayslips();
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error reissuing payslip:', error);
    alert('Error reissuing payslip');
  }
}

// Close modals
function closePayslipDetails() {
  const modal = document.getElementById('payslipDetailsModal');
  if (modal) {
    modal.style.display = 'none';
  }
  window.payslipsModule.currentPayslipId = null;
  window.payslipsModule.currentEmployeeId = null;
}

function closeReissueModal() {
  const modal = document.getElementById('reissueModal');
  const form = document.getElementById('reissueForm');
  if (modal) {
    modal.style.display = 'none';
  }
  if (form) {
    form.reset();
  }
}

// Refresh payslips
function refreshPayslips() {
  loadPayslips();
}

// Event listeners
document.getElementById('searchInput').addEventListener('input', () => {
  // Filter payslips
});
document.getElementById('periodFilter').addEventListener('change', loadPayslips);

// Utility functions
function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(amount || 0);
}

// Export functions
window.viewPayslip = viewPayslip;
window.downloadPayslipPDF = downloadPayslipPDF;
window.reissuePayslip = reissuePayslip;
window.submitReissue = submitReissue;
window.closePayslipDetails = closePayslipDetails;
window.closeReissueModal = closeReissueModal;
window.refreshPayslips = refreshPayslips;
</script>

<style>
.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  display: inline-block;
}
.status-draft { background: #fef3c7; color: #92400e; }
.status-generated { background: #dbeafe; color: #1e40af; }
.status-issued { background: #d1fae5; color: #065f46; }
.status-reissued { background: #fce7f3; color: #9f1239; }
</style>
