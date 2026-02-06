<!-- Payroll Processing Content -->
<main class="main-content employee-directory-container">
  <div class="directory-layout">
    <!-- Left Column: Main Content -->
    <div class="directory-left-column">
      <!-- KPI Cards Section -->
      <section class="kpi-section">
        <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #dbeafe; border-radius: 8px; flex-shrink: 0;">📅</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Open Periods</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="openPeriods">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">Available for processing</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #fef3c7; border-radius: 8px; flex-shrink: 0;">⏳</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Draft Runs</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="draftRuns">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">Pending calculation</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #d1fae5; border-radius: 8px; flex-shrink: 0;">✓</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Validated</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="validatedRuns">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">Awaiting approval</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #fce7f3; border-radius: 8px; flex-shrink: 0;">🔒</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Locked</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="lockedRuns">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">Finalized</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Filters Section -->
      <section class="filters-section">
        <div class="card">
          <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
            <div class="form-group">
              <label class="form-label">Frequency</label>
              <select id="frequencyFilter" class="form-select" style="min-width: 140px;">
                <option value="">All</option>
                <option value="Weekly">Weekly</option>
                <option value="Semi-Monthly">Semi-Monthly</option>
                <option value="Monthly">Monthly</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Status</label>
              <select id="statusFilter" class="form-select" style="min-width: 120px;">
                <option value="">All Status</option>
                <option value="Draft">Draft</option>
                <option value="Validated">Validated</option>
                <option value="Approved">Approved</option>
                <option value="Locked">Locked</option>
              </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-primary btn-sm" onclick="window.createPayrollRun()">+ New Payroll Run</button>
              <button class="btn btn-outline btn-sm" onclick="window.refreshPayrollRuns()">↻ Refresh</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Payroll Runs Table -->
      <section class="table-section">
        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Payroll Runs</h3>
              <p class="card-subtitle">Showing <strong id="totalCount">0</strong> payroll runs</p>
            </div>
          </div>

          <div class="table-container">
            <table class="table table-striped" id="payrollRunsTable">
              <thead class="table-head-fixed">
                <tr>
                  <th style="width: 12%;">Run Number</th>
                  <th style="width: 15%;">Period</th>
                  <th style="width: 12%;">Frequency</th>
                  <th style="width: 10%;">Pay Date</th>
                  <th style="width: 10%;">Employees</th>
                  <th style="width: 12%;">Gross Pay</th>
                  <th style="width: 12%;">Net Pay</th>
                  <th style="width: 10%;">Status</th>
                  <th style="width: 7%;">Actions</th>
                </tr>
              </thead>
              <tbody id="payrollRunsList">
                <tr>
                  <td colspan="9" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">⏳</div>
                      <p style="font-size: 14px; font-weight: 500;">Loading payroll runs...</p>
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

  <!-- Create Payroll Run Modal -->
<div id="createPayrollModal" class="modal" style="display: none;">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
      <h3>Create New Payroll Run</h3>
      <button class="modal-close" onclick="window.closeCreateModal()">&times;</button>
    </div>
    <div class="modal-body">
      <form id="createPayrollForm">
        <div class="form-group">
          <label class="form-label">Period Start *</label>
          <input type="date" id="periodStart" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Period End *</label>
          <input type="date" id="periodEnd" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Pay Date *</label>
          <input type="date" id="payDate" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Notes</label>
          <textarea id="notes" class="form-input" rows="3"></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="window.closeCreateModal()">Cancel</button>
      <button class="btn btn-primary" onclick="window.submitCreatePayroll()">Create Payroll Run</button>
    </div>
  </div>
</div>

<!-- Payroll Run Details Modal -->
<div id="payrollDetailsModal" class="modal" style="display: none;">
  <div class="modal-content" style="max-width: 900px;">
    <div class="modal-header">
      <h3 id="payrollDetailsTitle">Payroll Run Details</h3>
      <button class="modal-close" onclick="window.closeDetailsModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div id="payrollDetailsContent">
        <!-- Content loaded dynamically -->
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="window.closeDetailsModal()">Close</button>
      <button id="calculateBtn" class="btn btn-primary" onclick="window.calculatePayroll()" style="display: none;">Calculate</button>
      <button id="validateBtn" class="btn btn-primary" onclick="window.validatePayroll()" style="display: none;">Validate</button>
      <button id="approveBtn" class="btn btn-success" onclick="window.approvePayroll()" style="display: none;">Approve</button>
      <button id="lockBtn" class="btn btn-warning" onclick="window.lockPayroll()" style="display: none;">Lock</button>
    </div>
  </div>
</div>
</main>

<script>
// Use namespaced object to avoid redeclaration errors
if (typeof window.payrollProcessing === 'undefined') {
  window.payrollProcessing = {
    currentPayrollRunId: null,
    payrollRuns: []
  };
}

// Load payroll runs on page load
(function() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      loadPayrollRuns();
      loadOpenPeriodsCount();
    });
  } else {
    loadPayrollRuns();
    loadOpenPeriodsCount();
  }
})();

// Load payroll runs
function redirectToLoginIfUnauthorized(response) {
  if (response && response.status === 401) {
    localStorage.removeItem('token');
    window.location.href = 'index.php?expired=1';
    return true;
  }
  return false;
}

async function loadPayrollRuns() {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      window.location.href = 'index.php?expired=1';
      return;
    }
    
    const frequency = document.getElementById('frequencyFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    
    let url = 'modules/payroll/controllers/PayrollController.php?action=getPayrollRuns';
    if (frequency) url += '&frequency=' + frequency;
    if (status) url += '&status=' + status;
    
    const headers = {};
    if (token) {
      headers['Authorization'] = 'Bearer ' + token;
    }
    
    const response = await fetch(url, { headers });
    
    if (redirectToLoginIfUnauthorized(response)) return;
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    
    if (result.status === 200) {
      window.payrollProcessing.payrollRuns = result.data.runs || [];
      displayPayrollRuns(window.payrollProcessing.payrollRuns);
      updateKPIs(result.data.runs || []);
      const totalCountEl = document.getElementById('totalCount');
      if (totalCountEl) {
        totalCountEl.textContent = result.data.total || window.payrollProcessing.payrollRuns.length;
      }
    } else {
      console.error('API Error:', result.message);
      displayPayrollRuns([]);
      updateKPIs([]);
      const totalCountEl = document.getElementById('totalCount');
      if (totalCountEl) {
        totalCountEl.textContent = '0';
      }
    }
  } catch (error) {
    console.error('Error loading payroll runs:', error);
    const tbody = document.getElementById('payrollRunsList');
    if (tbody) {
      let errorMessage = error.message || 'Unknown error';
      let helpText = 'Please check your connection and try again.';
      
      if (errorMessage.includes('401') || errorMessage.includes('Unauthorized')) {
        helpText = 'Authentication required. Please log in again.';
      } else if (errorMessage.includes('500') || errorMessage.includes('Internal')) {
        helpText = 'Database tables may not be set up. Please run the payroll_schema.sql file.';
      }
      
      tbody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 3rem;">
        <div style="font-size: 48px; margin-bottom: 1rem;">⚠️</div>
        <p style="color: #ef4444; font-weight: 500; margin-bottom: 0.5rem;">Error loading payroll runs</p>
        <p style="font-size: 12px; color: #6b7280; margin-bottom: 1rem;">${errorMessage}</p>
        <p style="font-size: 12px; color: #9ca3af;">${helpText}</p>
      </td></tr>`;
    }
    updateKPIs([]);
  }
}

// Display payroll runs
function displayPayrollRuns(runs) {
  const tbody = document.getElementById('payrollRunsList');
  if (!tbody) return;
  
  if (!runs || runs.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="9" style="text-align: center; padding: 3rem 2rem;">
          <div style="color: var(--text-light);">
            <div style="font-size: 48px; margin-bottom: 1rem;">📋</div>
            <p style="font-size: 14px; font-weight: 500; margin-bottom: 0.5rem;">No payroll runs found</p>
            <p style="font-size: 12px; color: #9ca3af;">Create a new payroll run to get started</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = runs.map(run => `
    <tr>
      <td><strong>${run.run_number || 'N/A'}</strong></td>
      <td>${formatDate(run.period_start)} - ${formatDate(run.period_end)}</td>
      <td>${run.payroll_frequency || 'N/A'}</td>
      <td>${formatDate(run.pay_date)}</td>
      <td style="text-align: center;">${run.total_employees || 0}</td>
      <td style="text-align: right;">${formatCurrency(run.total_gross_pay || 0)}</td>
      <td style="text-align: right;">${formatCurrency(run.total_net_pay || 0)}</td>
      <td><span class="status-badge status-${(run.status || 'draft').toLowerCase()}">${run.status || 'Draft'}</span></td>
      <td style="text-align: center;">
        <button class="btn btn-sm btn-outline" onclick="viewPayrollRun(${run.payroll_run_id})">View</button>
      </td>
    </tr>
  `).join('');
}

// Update KPIs
function updateKPIs(runs) {
  if (!runs) runs = [];
  
  const draftRuns = runs.filter(r => r.status === 'Draft').length;
  const validated = runs.filter(r => r.status === 'Validated').length;
  const locked = runs.filter(r => r.status === 'Locked').length;
  
  // Open Periods should come from calendar, but for now use draft runs as placeholder
  const openPeriodsEl = document.getElementById('openPeriods');
  if (openPeriodsEl) openPeriodsEl.textContent = draftRuns;
  
  const draftRunsEl = document.getElementById('draftRuns');
  if (draftRunsEl) draftRunsEl.textContent = draftRuns;
  
  const validatedRunsEl = document.getElementById('validatedRuns');
  if (validatedRunsEl) validatedRunsEl.textContent = validated;
  
  const lockedRunsEl = document.getElementById('lockedRuns');
  if (lockedRunsEl) lockedRunsEl.textContent = locked;
}

// Load open periods count separately
async function loadOpenPeriodsCount() {
  try {
    const token = localStorage.getItem('token');
    const headers = {};
    if (token) {
      headers['Authorization'] = 'Bearer ' + token;
    }
    
    const response = await fetch('modules/payroll/controllers/PayrollController.php?action=getPayrollCalendar', {
      headers
    });
    
    if (redirectToLoginIfUnauthorized(response)) return;
    if (response.ok) {
      const result = await response.json();
      if (result.status === 200) {
        const openPeriods = (result.data.periods || []).filter(p => p.status === 'Open').length;
        const openPeriodsEl = document.getElementById('openPeriods');
        if (openPeriodsEl) openPeriodsEl.textContent = openPeriods;
      }
    }
  } catch (error) {
    console.error('Error loading open periods:', error);
  }
}

// View payroll run details
async function viewPayrollRun(runId) {
  window.payrollProcessing.currentPayrollRunId = runId;
  
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=getPayrollRun&id=${runId}`, {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      let run = result.data.run;
      let employees = result.data.employees || [];
      // If run has no employees and is Draft, backfill eligible employees
      if (employees.length === 0 && run.status === 'Draft') {
        const addResp = await fetch(`modules/payroll/controllers/PayrollController.php?action=addEmployeesToRun&id=${runId}`, {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token }
        });
        const addResult = await addResp.json();
        if (addResult.status === 200 && addResult.data) {
          run = addResult.data.run || run;
          employees = addResult.data.employees || [];
        }
      }
      
      document.getElementById('payrollDetailsTitle').textContent = `Payroll Run: ${run.run_number}`;
      
      let content = `
        <div style="margin-bottom: 2rem;">
          <h4>Run Information</h4>
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 1rem;">
            <div><strong>Period:</strong> ${formatDate(run.period_start)} - ${formatDate(run.period_end)}</div>
            <div><strong>Pay Date:</strong> ${formatDate(run.pay_date)}</div>
            <div><strong>Frequency:</strong> ${run.payroll_frequency}</div>
            <div><strong>Status:</strong> <span class="status-badge status-${run.status.toLowerCase()}">${run.status}</span></div>
            <div><strong>Total Employees:</strong> ${run.total_employees || 0}</div>
            <div><strong>Total Gross:</strong> ${formatCurrency(run.total_gross_pay || 0)}</div>
            <div><strong>Total Deductions:</strong> ${formatCurrency(run.total_deductions || 0)}</div>
            <div><strong>Total Net:</strong> ${formatCurrency(run.total_net_pay || 0)}</div>
          </div>
        </div>
        
        <div>
          <h4>Employees (${employees.length})</h4>
          <div class="table-container" style="max-height: 400px; overflow-y: auto; margin-top: 1rem;">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Employee</th>
                  <th>Basic Pay</th>
                  <th>Gross Pay</th>
                  <th>Deductions</th>
                  <th>Net Pay</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                ${employees.map(emp => `
                  <tr>
                    <td>${emp.employee_name || emp.employee_code}</td>
                    <td style="text-align: right;">${formatCurrency(emp.basic_pay || 0)}</td>
                    <td style="text-align: right;">${formatCurrency(emp.gross_pay || 0)}</td>
                    <td style="text-align: right;">${formatCurrency(emp.total_deductions || 0)}</td>
                    <td style="text-align: right;">${formatCurrency(emp.net_pay || 0)}</td>
                    <td><span class="status-badge status-${(emp.status || 'pending').toLowerCase()}">${emp.status || 'Pending'}</span></td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
      
      document.getElementById('payrollDetailsContent').innerHTML = content;
      
      // Show/hide action buttons based on status
      const calculateBtn = document.getElementById('calculateBtn');
      const validateBtn = document.getElementById('validateBtn');
      const approveBtn = document.getElementById('approveBtn');
      const lockBtn = document.getElementById('lockBtn');
      const payrollDetailsModal = document.getElementById('payrollDetailsModal');
      
      if (calculateBtn) calculateBtn.style.display = run.status === 'Draft' ? 'inline-block' : 'none';
      if (validateBtn) validateBtn.style.display = run.status === 'Draft' ? 'inline-block' : 'none';
      if (approveBtn) approveBtn.style.display = run.status === 'Validated' ? 'inline-block' : 'none';
      if (lockBtn) lockBtn.style.display = run.status === 'Approved' ? 'inline-block' : 'none';
      
      if (payrollDetailsModal) {
        payrollDetailsModal.style.display = 'flex';
      }
    }
  } catch (error) {
    console.error('Error loading payroll run:', error);
    alert('Error loading payroll run details');
  }
}

// Create payroll run
function createPayrollRun() {
  // Wait a bit for DOM to be ready
  setTimeout(function() {
    const modal = document.getElementById('createPayrollModal');
    if (modal) {
      modal.style.display = 'flex';
    } else {
      console.warn('Create payroll modal not found - may not be loaded yet');
    }
  }, 100);
}

// Close create modal
function closeCreateModal() {
  const modal = document.getElementById('createPayrollModal');
  if (modal) {
    modal.style.display = 'none';
  }
}

// Submit create payroll
async function submitCreatePayroll() {
  const form = document.getElementById('createPayrollForm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  
  const token = localStorage.getItem('token');
  if (!token) {
    alert('Session expired. Please log in again.');
    window.location.href = 'index.php?expired=1';
    return;
  }
  
  try {
    const data = {
      period_start: document.getElementById('periodStart').value,
      period_end: document.getElementById('periodEnd').value,
      pay_date: document.getElementById('payDate').value,
      notes: document.getElementById('notes').value
    };
    
    const response = await fetch('modules/payroll/controllers/PayrollController.php?action=createPayrollRun', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });
    
    if (redirectToLoginIfUnauthorized(response)) return;
    
    const result = await response.json();
    
    if (result.status === 201) {
      alert('Payroll run created successfully');
      closeCreateModal();
      loadPayrollRuns();
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error creating payroll run:', error);
    alert('Error creating payroll run');
  }
}

// Calculate payroll
async function calculatePayroll() {
  if (!window.payrollProcessing.currentPayrollRunId) return;
  
  if (!confirm('Calculate payroll for this run? This will compute earnings and deductions for all employees.')) {
    return;
  }
  
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=calculatePayroll&id=${window.payrollProcessing.currentPayrollRunId}`, {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      alert('Payroll calculated successfully');
      viewPayrollRun(window.payrollProcessing.currentPayrollRunId);
      loadPayrollRuns();
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error calculating payroll:', error);
    alert('Error calculating payroll');
  }
}

// Validate payroll
async function validatePayroll() {
  if (!window.payrollProcessing.currentPayrollRunId) return;
  
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=validatePayroll&id=${window.payrollProcessing.currentPayrollRunId}`, {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      alert('Payroll validated successfully');
      viewPayrollRun(window.payrollProcessing.currentPayrollRunId);
      loadPayrollRuns();
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error validating payroll:', error);
    alert('Error validating payroll');
  }
}

// Approve payroll
async function approvePayroll() {
  if (!window.payrollProcessing.currentPayrollRunId) return;
  
  if (!confirm('Approve this payroll run? This will generate payslips for all employees.')) {
    return;
  }
  
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=approvePayroll&id=${window.payrollProcessing.currentPayrollRunId}`, {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      alert('Payroll approved and payslips generated');
      viewPayrollRun(window.payrollProcessing.currentPayrollRunId);
      loadPayrollRuns();
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error approving payroll:', error);
    alert('Error approving payroll');
  }
}

// Lock payroll
async function lockPayroll() {
  if (!window.payrollProcessing.currentPayrollRunId) return;
  
  if (!confirm('Lock this payroll run? This action cannot be undone.')) {
    return;
  }
  
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=lockPayroll&id=${window.payrollProcessing.currentPayrollRunId}`, {
      method: 'POST',
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      alert('Payroll locked successfully');
      viewPayrollRun(window.payrollProcessing.currentPayrollRunId);
      loadPayrollRuns();
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error locking payroll:', error);
    alert('Error locking payroll');
  }
}

// Close details modal
function closeDetailsModal() {
  const modal = document.getElementById('payrollDetailsModal');
  if (modal) {
    modal.style.display = 'none';
  }
  window.payrollProcessing.currentPayrollRunId = null;
}

// Load payroll calendar
async function loadPayrollCalendar() {
  try {
    const token = localStorage.getItem('token');
    const headers = {};
    if (token) {
      headers['Authorization'] = 'Bearer ' + token;
    }
    
    const response = await fetch('modules/payroll/controllers/PayrollController.php?action=getPayrollCalendar', {
      headers
    });
    
    if (redirectToLoginIfUnauthorized(response)) return;
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    
    if (result.status === 200) {
      const select = document.getElementById('calendarId');
      if (select) {
        select.innerHTML = '<option value="">Select Period</option>' +
          (result.data.periods || []).map(p => 
            `<option value="${p.calendar_id}" data-start="${p.start_date}" data-end="${p.end_date}" data-pay="${p.pay_date}" data-freq="${p.payroll_frequency}">${p.period_name} (${p.payroll_frequency})</option>`
          ).join('');
        
        // Auto-fill dates when calendar is selected
        select.addEventListener('change', function() {
          const option = this.options[this.selectedIndex];
          if (option.value) {
            const periodStart = document.getElementById('periodStart');
            const periodEnd = document.getElementById('periodEnd');
            const payDate = document.getElementById('payDate');
            const payrollFrequency = document.getElementById('payrollFrequency');
            
            if (periodStart) periodStart.value = option.dataset.start;
            if (periodEnd) periodEnd.value = option.dataset.end;
            if (payDate) payDate.value = option.dataset.pay;
            if (payrollFrequency) payrollFrequency.value = option.dataset.freq;
          }
        });
      }
    }
  } catch (error) {
    console.error('Error loading payroll calendar:', error);
    // Don't show error to user, just log it
  }
}

// Refresh payroll runs
function refreshPayrollRuns() {
  loadPayrollRuns();
}

// Filter change handlers
document.getElementById('frequencyFilter').addEventListener('change', loadPayrollRuns);
document.getElementById('statusFilter').addEventListener('change', loadPayrollRuns);

// Utility functions
function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(amount || 0);
}

// Export functions to window
window.createPayrollRun = createPayrollRun;
window.closeCreateModal = closeCreateModal;
window.submitCreatePayroll = submitCreatePayroll;
window.viewPayrollRun = viewPayrollRun;
window.calculatePayroll = calculatePayroll;
window.validatePayroll = validatePayroll;
window.approvePayroll = approvePayroll;
window.lockPayroll = lockPayroll;
window.closeDetailsModal = closeDetailsModal;
window.refreshPayrollRuns = refreshPayrollRuns;
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
.status-validated { background: #dbeafe; color: #1e40af; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-locked { background: #e5e7eb; color: #374151; }
</style>
