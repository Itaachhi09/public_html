<!-- Salaries Management Content -->
<main class="main-content employee-directory-container">
  <div class="directory-layout">
    <div class="directory-left-column">
      <!-- KPI Cards -->
      <section class="kpi-section">
        <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #dbeafe; border-radius: 8px; flex-shrink: 0;">💰</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Total Employees</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="totalEmployees">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">With salary records</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #d1fae5; border-radius: 8px; flex-shrink: 0;">✓</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Payroll Eligible</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="eligibleEmployees">0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">Active for payroll</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #fef3c7; border-radius: 8px; flex-shrink: 0;">📊</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Average Salary</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="avgSalary">₱0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">Monthly average</p>
            </div>
          </div>

          <div class="kpi-card" style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 2.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #fce7f3; border-radius: 8px; flex-shrink: 0;">📈</div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 12px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">Total Payroll</p>
              <h2 style="margin: 0.25rem 0; font-size: 2rem; font-weight: 700; color: #1f2937;" id="totalPayroll">₱0</h2>
              <p style="margin: 0; font-size: 11px; color: #9ca3af;">Monthly total</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Search and Filters -->
      <section class="filters-section">
        <div class="card">
          <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
            <div class="form-group">
              <label class="form-label">Search Employee</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Name, Code..." style="padding-left: 36px;">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Salary Type</label>
              <select id="salaryTypeFilter" class="form-select" style="min-width: 120px;">
                <option value="">All Types</option>
                <option value="Hourly">Hourly</option>
                <option value="Daily">Daily</option>
                <option value="Monthly">Monthly</option>
                <option value="Annual">Annual</option>
              </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-primary btn-sm" onclick="window.addEmployeeSalary()">+ Add Salary</button>
              <button class="btn btn-outline btn-sm" onclick="window.refreshSalaries()">↻ Refresh</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Salaries Table -->
      <section class="table-section">
        <div class="card">
          <div class="card-header">
            <div>
              <h3 class="card-title">Employee Salaries</h3>
              <p class="card-subtitle">Showing <strong id="totalCount">0</strong> salary records</p>
            </div>
          </div>

          <div class="table-container">
            <table class="table table-striped" id="salariesTable">
              <thead class="table-head-fixed">
                <tr>
                  <th style="width: 10%;">Code</th>
                  <th style="width: 20%;">Employee</th>
                  <th style="width: 12%;">Salary Type</th>
                  <th style="width: 15%;">Basic Rate</th>
                  <th style="width: 12%;">Tax Status</th>
                  <th style="width: 10%;">Dependents</th>
                  <th style="width: 10%;">Effective Date</th>
                  <th style="width: 11%;">Actions</th>
                </tr>
              </thead>
              <tbody id="salariesList">
                <tr>
                  <td colspan="8" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">⏳</div>
                      <p style="font-size: 14px; font-weight: 500;">Loading salaries...</p>
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

  <!-- Add/Edit Salary Modal -->
<div id="salaryModal" class="modal" style="display: none;">
  <div class="modal-content" style="max-width: 700px;">
    <div class="modal-header">
      <h3 id="salaryModalTitle">Add Employee Salary</h3>
      <button class="modal-close" onclick="window.closeSalaryModal()">&times;</button>
    </div>
    <div class="modal-body">
      <form id="salaryForm">
        <input type="hidden" id="salaryId">
        <div class="form-group">
          <label class="form-label">Employee *</label>
          <select id="employeeId" class="form-select" required>
            <option value="">Select Employee</option>
          </select>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Salary Type *</label>
            <select id="salaryType" class="form-select" required>
              <option value="Hourly">Hourly</option>
              <option value="Daily">Daily</option>
              <option value="Monthly" selected>Monthly</option>
              <option value="Annual">Annual</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Basic Rate *</label>
            <input type="number" id="basicRate" class="form-input" step="0.01" required>
          </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Tax Status *</label>
            <select id="taxStatus" class="form-select" required>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Married with 1 Dependent">Married with 1 Dependent</option>
              <option value="Married with 2 Dependents">Married with 2 Dependents</option>
              <option value="Married with 3 Dependents">Married with 3 Dependents</option>
              <option value="Married with 4+ Dependents">Married with 4+ Dependents</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Dependents Count</label>
            <input type="number" id="dependentsCount" class="form-input" min="0" value="0">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Bank Name</label>
            <input type="text" id="bankName" class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">Account Number</label>
            <input type="text" id="bankAccount" class="form-input">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Payout Method</label>
            <select id="payoutMethod" class="form-select">
              <option value="Bank Transfer" selected>Bank Transfer</option>
              <option value="Cash">Cash</option>
              <option value="Check">Check</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Effective Date *</label>
            <input type="date" id="effectiveDate" class="form-input" required>
          </div>
        </div>
        <div class="form-group">
          <label>
            <input type="checkbox" id="payrollEligible" checked> Payroll Eligible
          </label>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="window.closeSalaryModal()">Cancel</button>
      <button class="btn btn-primary" onclick="window.saveSalary()">Save Salary</button>
    </div>
  </div>
</div>
</main>

<script>
// Use namespaced object to avoid redeclaration errors
if (typeof window.salariesModule === 'undefined') {
  window.salariesModule = {
    employees: [],
    salaries: []
  };
}

// Load on page ready
(function() {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      loadEmployees();
      loadSalaries();
    });
  } else {
    loadEmployees();
    loadSalaries();
  }
})();

// Load employees for dropdown (use Payroll module so Salaries page works for payroll/finance)
async function loadEmployees() {
  try {
    const token = localStorage.getItem('token');
    if (!token) {
      console.warn('No token found, API calls may fail');
    }
    
    const headers = {};
    if (token) {
      headers['Authorization'] = 'Bearer ' + token;
    }
    
    const response = await fetch('modules/payroll/controllers/PayrollController.php?action=getEmployeeList&limit=1000', {
      headers
    });
    
    if (!response.ok) {
      if (response.status === 401) {
        console.warn('Unauthorized - token may be missing or expired');
        return;
      }
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    
    if (result.status === 200 && result.data) {
      window.salariesModule.employees = result.data.employees || [];
      const select = document.getElementById('employeeId');
      if (select) {
        const list = window.salariesModule.employees;
        if (list.length === 0) {
          select.innerHTML = '<option value="">Select Employee</option><option value="" disabled>— No employees in system. Add employees in HR Core → Employees first.</option>';
        } else {
          select.innerHTML = '<option value="">Select Employee</option>' +
            list.map(emp =>
              `<option value="${emp.employee_id}">${emp.employee_code} - ${emp.first_name} ${emp.last_name}</option>`
            ).join('');
        }
      }
    }
  } catch (error) {
    console.error('Error loading employees:', error);
  }
}

// Load salaries
async function loadSalaries() {
  try {
    // This would need an endpoint to get all salaries
    // For now, we'll load from employee list
    const token = localStorage.getItem('token');
    if (!token) {
      console.warn('No token found, API calls may fail');
      displaySalaries([]);
      updateKPIs([]);
      return;
    }
    
    const headers = {};
    if (token) {
      headers['Authorization'] = 'Bearer ' + token;
    }
    
    const response = await fetch('modules/payroll/controllers/PayrollController.php?action=getEmployeeList&limit=1000', {
      headers
    });
    
    if (!response.ok) {
      if (response.status === 401) {
        console.warn('Unauthorized - token may be missing or expired');
        displaySalaries([]);
        updateKPIs([]);
        return;
      }
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    const result = await response.json();
    
    if (result.status === 200 && result.data) {
      const employeeList = result.data.employees || [];
      
      // Load salary for each employee
      const salaryPromises = employeeList.map(async (emp) => {
        try {
          const salaryResponse = await fetch(`modules/payroll/controllers/PayrollController.php?action=getEmployeeSalary&employee_id=${emp.employee_id}`, {
            headers: { 'Authorization': 'Bearer ' + token }
          });
          const salaryResult = await salaryResponse.json();
          
          if (salaryResult.status === 200) {
            return {
              ...salaryResult.data,
              employee_code: emp.employee_code,
              employee_name: `${emp.first_name} ${emp.last_name}`
            };
          }
          return null;
        } catch (e) {
          return null;
        }
      });
      
      window.salariesModule.salaries = (await Promise.all(salaryPromises)).filter(s => s !== null);
      displaySalaries(window.salariesModule.salaries);
      updateKPIs(window.salariesModule.salaries);
    }
  } catch (error) {
    console.error('Error loading salaries:', error);
  }
}

// Display salaries
function displaySalaries(salaryList) {
  const tbody = document.getElementById('salariesList');
  if (!tbody) return;
  
  const searchInput = document.getElementById('searchInput');
  const typeFilterEl = document.getElementById('salaryTypeFilter');
  const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
  const typeFilter = typeFilterEl ? typeFilterEl.value : '';
  
  let filtered = salaryList.filter(s => {
    const matchSearch = !searchTerm || 
      s.employee_code.toLowerCase().includes(searchTerm) ||
      s.employee_name.toLowerCase().includes(searchTerm);
    const matchType = !typeFilter || s.salary_type === typeFilter;
    return matchSearch && matchType;
  });
  
  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 3rem;">No salary records found</td></tr>';
    return;
  }
  
  tbody.innerHTML = filtered.map(salary => `
    <tr>
      <td><strong>${salary.employee_code}</strong></td>
      <td>${salary.employee_name}</td>
      <td>${salary.salary_type}</td>
      <td style="text-align: right;">${formatCurrency(salary.basic_rate)}</td>
      <td>${salary.tax_status || 'Single'}</td>
      <td style="text-align: center;">${salary.dependents_count || 0}</td>
      <td>${formatDate(salary.effective_date)}</td>
      <td style="text-align: center;">
        <button class="btn btn-sm btn-outline" onclick="editSalary(${salary.employee_id}, ${salary.salary_id})">Edit</button>
        <button class="btn btn-sm btn-outline" onclick="viewSalaryHistory(${salary.employee_id})">History</button>
      </td>
    </tr>
  `).join('');
  
  const totalCountEl = document.getElementById('totalCount');
  if (totalCountEl) {
    totalCountEl.textContent = filtered.length;
  }
}

// Update KPIs
function updateKPIs(salaryList) {
  const eligible = salaryList.filter(s => s.payroll_eligible).length;
  const total = salaryList.reduce((sum, s) => sum + (parseFloat(s.basic_rate) || 0), 0);
  const avg = salaryList.length > 0 ? total / salaryList.length : 0;
  
  document.getElementById('totalEmployees').textContent = salaryList.length;
  document.getElementById('eligibleEmployees').textContent = eligible;
  document.getElementById('avgSalary').textContent = formatCurrency(avg);
  document.getElementById('totalPayroll').textContent = formatCurrency(total);
}

// Add employee salary
function addEmployeeSalary() {
  // Wait a bit for DOM to be ready
  setTimeout(function() {
    const modalTitle = document.getElementById('salaryModalTitle');
    const modal = document.getElementById('salaryModal');
    const form = document.getElementById('salaryForm');
    const salaryId = document.getElementById('salaryId');
    const effectiveDate = document.getElementById('effectiveDate');
    
    if (!modal) {
      console.warn('Salary modal not found - may not be loaded yet');
      return;
    }
    
    if (modalTitle) modalTitle.textContent = 'Add Employee Salary';
    if (form) form.reset();
    if (salaryId) salaryId.value = '';
    if (effectiveDate) effectiveDate.value = new Date().toISOString().split('T')[0];
    modal.style.display = 'flex';
    // Ensure employee list is loaded (in case initial load ran before DOM or failed)
    loadEmployees();
  }, 100);
}

// Edit salary
async function editSalary(employeeId, salaryId) {
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=getEmployeeSalary&employee_id=${employeeId}`, {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      const salary = result.data;
      document.getElementById('salaryModalTitle').textContent = 'Edit Employee Salary';
      document.getElementById('salaryId').value = salary.salary_id || '';
      document.getElementById('employeeId').value = salary.employee_id;
      document.getElementById('salaryType').value = salary.salary_type;
      document.getElementById('basicRate').value = salary.basic_rate;
      document.getElementById('taxStatus').value = salary.tax_status || 'Single';
      document.getElementById('dependentsCount').value = salary.dependents_count || 0;
      document.getElementById('bankName').value = salary.bank_name || '';
      document.getElementById('bankAccount').value = salary.bank_account_number || '';
      document.getElementById('payoutMethod').value = salary.payout_method || 'Bank Transfer';
      document.getElementById('effectiveDate').value = salary.effective_date || '';
      document.getElementById('payrollEligible').checked = salary.payroll_eligible !== false;
      const salaryModal = document.getElementById('salaryModal');
      if (salaryModal) {
        salaryModal.style.display = 'flex';
      }
    }
  } catch (error) {
    console.error('Error loading salary:', error);
    alert('Error loading salary details');
  }
}

// Save salary
async function saveSalary() {
  const form = document.getElementById('salaryForm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  
  try {
    const token = localStorage.getItem('token');
    const data = {
      employee_id: document.getElementById('employeeId').value,
      salary_type: document.getElementById('salaryType').value,
      basic_rate: document.getElementById('basicRate').value,
      tax_status: document.getElementById('taxStatus').value,
      dependents_count: document.getElementById('dependentsCount').value,
      bank_name: document.getElementById('bankName').value,
      bank_account_number: document.getElementById('bankAccount').value,
      payout_method: document.getElementById('payoutMethod').value,
      effective_date: document.getElementById('effectiveDate').value,
      payroll_eligible: document.getElementById('payrollEligible').checked
    };
    
    const response = await fetch('modules/payroll/controllers/PayrollController.php?action=saveEmployeeSalary', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + token,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.status === 201 || result.status === 200) {
      alert('Salary saved successfully');
      closeSalaryModal();
      loadSalaries();
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error saving salary:', error);
    alert('Error saving salary');
  }
}

// View salary history
async function viewSalaryHistory(employeeId) {
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`modules/payroll/controllers/PayrollController.php?action=getSalaryHistory&employee_id=${employeeId}`, {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    
    const result = await response.json();
    
    if (result.status === 200) {
      const history = result.data.history || [];
      let historyHtml = '<h4>Salary History</h4><table class="table"><thead><tr><th>Effective Date</th><th>End Date</th><th>Type</th><th>Rate</th></tr></thead><tbody>';
      history.forEach(h => {
        historyHtml += `<tr><td>${formatDate(h.effective_date)}</td><td>${formatDate(h.end_date)}</td><td>${h.salary_type}</td><td>${formatCurrency(h.basic_rate)}</td></tr>`;
      });
      historyHtml += '</tbody></table>';
      
      alert(historyHtml); // In production, use a proper modal
    }
  } catch (error) {
    console.error('Error loading salary history:', error);
  }
}

// Close salary modal
function closeSalaryModal() {
  const modal = document.getElementById('salaryModal');
  if (modal) {
    modal.style.display = 'none';
  }
}

// Refresh salaries
function refreshSalaries() {
  loadSalaries();
}

// Event listeners
(function() {
  const searchInput = document.getElementById('searchInput');
  const salaryTypeFilter = document.getElementById('salaryTypeFilter');
  
  if (searchInput) {
    searchInput.addEventListener('input', () => displaySalaries(window.salariesModule.salaries));
  }
  if (salaryTypeFilter) {
    salaryTypeFilter.addEventListener('change', () => displaySalaries(window.salariesModule.salaries));
  }
})();

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
window.addEmployeeSalary = addEmployeeSalary;
window.editSalary = editSalary;
window.saveSalary = saveSalary;
window.viewSalaryHistory = viewSalaryHistory;
window.closeSalaryModal = closeSalaryModal;
window.refreshSalaries = refreshSalaries;
</script>
