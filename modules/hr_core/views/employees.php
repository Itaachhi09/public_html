<!-- Employee Directory Content -->
<main class="main-content employee-directory-container">
  <div class="directory-layout">
    <!-- Left Column: Main Content -->
    <div class="directory-left-column">
      <!-- KPI Cards Section -->
      <section class="kpi-section">
        <div class="kpi-grid">
          <div class="kpi-card kpi-card-total" style="transition: all 0.3s ease;">
            <div class="kpi-icon">👥</div>
            <div class="kpi-content">
              <p class="kpi-label">Total</p>
              <h2 class="kpi-value" id="totalEmployees">0</h2>
            </div>
            <p class="kpi-description">All employees</p>
          </div>

          <div class="kpi-card kpi-card-active" style="transition: all 0.3s ease;">
            <div class="kpi-icon">✓</div>
            <div class="kpi-content">
              <p class="kpi-label">Active</p>
              <h2 class="kpi-value" id="activeCount">0</h2>
            </div>
            <p class="kpi-description">Working</p>
          </div>

          <div class="kpi-card kpi-card-leave" style="transition: all 0.3s ease;">
            <div class="kpi-icon">⏸</div>
            <div class="kpi-content">
              <p class="kpi-label">On Leave</p>
              <h2 class="kpi-value" id="onLeaveCount">0</h2>
            </div>
            <p class="kpi-description">Absent</p>
          </div>

          <div class="kpi-card kpi-card-resigned" style="transition: all 0.3s ease;">
            <div class="kpi-icon">✕</div>
            <div class="kpi-content">
              <p class="kpi-label">Resigned</p>
              <h2 class="kpi-value" id="resignedCount">0</h2>
            </div>
            <p class="kpi-description">Left</p>
          </div>
        </div>
      </section>

      <!-- Search and Filters Section -->
      <section class="filters-section">
        <div class="card">
          <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 1rem; align-items: end;">
            <!-- Main Search -->
            <div class="form-group">
              <label class="form-label">Search</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Name, ID, Email..." style="padding-left: 36px;">
              </div>
            </div>

            <!-- Quick Filters -->
            <div class="filters-row">
              <div class="form-group">
                <label class="form-label">Department</label>
                <select id="deptFilter" class="form-select" style="min-width: 140px;">
                  <option value="">All</option>
                  <option value="1">Human Resources</option>
                  <option value="2">Finance</option>
                  <option value="3">Operations</option>
                  <option value="4">Clinical</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Status</label>
                <select id="statusFilter" class="form-select" style="min-width: 120px;">
                  <option value="">All Status</option>
                  <option value="active">Active</option>
                  <option value="on_leave">On Leave</option>
                  <option value="resigned">Resigned</option>
                </select>
              </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-outline btn-sm" onclick="window.expandFilters()" id="expandBtn" title="More filters">⚙️</button>
              <button class="btn btn-outline btn-sm" onclick="window.resetFilters()" title="Reset all filters">↻ Reset</button>
            </div>
          </div>

          <!-- Advanced Filters (Hidden by default) -->
          <div id="advancedFilters" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
              <div class="form-group">
                <label class="form-label">Position</label>
                <select id="posFilter" class="form-select">
                  <option value="">All Positions</option>
                  <option value="manager">Manager</option>
                  <option value="nurse">Nurse</option>
                  <option value="doctor">Doctor</option>
                  <option value="staff">Staff</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Hire Date From</label>
                <input type="date" id="hiredFromFilter" class="form-input">
              </div>

              <div class="form-group">
                <label class="form-label">Hire Date To</label>
                <input type="date" id="hiredToFilter" class="form-input">
              </div>
            </div>
          </div>

          <!-- Active Filters Display -->
          <div id="activeFiltersDisplay" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
            <p style="font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem;">Active Filters:</p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="activeFiltersTags"></div>
          </div>
        </div>
      </section>

      <!-- Employee Table Section -->
      <section class="table-section">
        <div class="card">
          <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
              <h3 class="card-title">Employee Directory</h3>
              <p class="card-subtitle">Showing <strong id="totalCount">0</strong> of <strong id="grandTotal">0</strong> employees</p>
            </div>
            <button class="btn btn-outline" onclick="window.openArchiveModal()" title="View archived employees" style="white-space: nowrap; position: relative;">📦 Archive <span id="archiveCount" style="display: inline-block; background: var(--danger); color: white; border-radius: 50%; width: 20px; height: 20px; line-height: 20px; text-align: center; font-size: 12px; font-weight: 600; margin-left: 0.5rem;">0</span></button>
          </div>

          <!-- Table Container with Fixed Header -->
          <div class="table-container">
            <table class="table table-striped" id="employeesTable">
              <thead class="table-head-fixed">
                <tr>
                  <th style="width: 10%; text-align: left;">Code</th>
                  <th style="width: 22%; text-align: left;">Name</th>
                  <th style="width: 20%; text-align: left;">Email</th>
                  <th style="width: 16%; text-align: left;">Department</th>
                  <th style="width: 12%; text-align: left;">Hire Date</th>
                  <th style="width: 12%; text-align: center;">Status</th>
                  <th style="width: 8%; text-align: center;">Action</th>
                </tr>
              </thead>
              <tbody id="employeesList">
                <tr>
                  <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">⏳</div>
                      <p style="font-size: 14px; font-weight: 500;">Loading employees...</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="pagination-container">
            <div>
              <p style="font-size: 12px; color: var(--text-light);">
                Page <strong id="currentPage">1</strong> | <strong id="pageInfo">0</strong> results
              </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
              <button class="btn btn-outline btn-sm" onclick="window.previousPage()" id="prevBtn" disabled>← Previous</button>
              <button class="btn btn-outline btn-sm" onclick="window.nextPage()" id="nextBtn">Next →</button>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Right Column: Quick Actions Panel -->
    <aside class="directory-right-column">
      <div class="side-panel">
        <div class="side-panel-header">
          <h3 class="side-panel-title">Quick Actions</h3>
          <button class="side-panel-toggle" onclick="window.toggleSidePanel()" title="Toggle panel">✕</button>
        </div>

        <!-- Action Buttons -->
        <div class="side-panel-section">
          <button class="side-action-btn" onclick="window.triggerImportDialog()">
            <span class="side-action-icon">📥</span>
            <span>Import</span>
          </button>
          <input type="file" id="importFileInput" accept=".csv,.xlsx" style="display: none;">
          <button class="side-action-btn" onclick="window.exportToCSV()">
            <span class="side-action-icon">📊</span>
            <span>Export CSV</span>
          </button>
        </div>

        <!-- Insights -->
        <div class="side-panel-section">
          <p class="side-panel-subtitle">Insights</p>
          <div class="insight-item insight-new-hires">
            <div class="insight-icon">🆕</div>
            <div class="insight-content">
              <div class="insight-label">New Hires</div>
              <div class="insight-value" id="insight-newHires">0</div>
              <div class="insight-description" id="insight-newHires-desc">this month</div>
            </div>
          </div>
          <div class="insight-item insight-on-leave">
            <div class="insight-icon">📋</div>
            <div class="insight-content">
              <div class="insight-label">On Leave Today</div>
              <div class="insight-value" id="insight-onLeaveToday">0</div>
              <div class="insight-description" id="insight-onLeaveToday-desc">employees</div>
            </div>
          </div>
          <div class="insight-item insight-incomplete">
            <div class="insight-icon">⚠️</div>
            <div class="insight-content">
              <div class="insight-label">Incomplete Profiles</div>
              <div class="insight-value" id="insight-incompleteProfiles">0</div>
              <div class="insight-description" id="insight-incompleteProfiles-desc">missing requirements</div>
            </div>
          </div>
          <div class="insight-item insight-expiring">
            <div class="insight-icon">⏰</div>
            <div class="insight-content">
              <div class="insight-label">Expiring Soon</div>
              <div class="insight-value" id="insight-expiringSoon">0</div>
              <div class="insight-description" id="insight-expiringSoon-desc">contracts ending</div>
            </div>
          </div>
        </div>

        <!-- Recently Added -->
        <div class="side-panel-section">
          <p class="side-panel-subtitle">Recent Hires</p>
          <div id="recentEmployeesList" style="max-height: 280px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>
      </div>
    </aside>
  </div>
</main>

  <!-- Action Menu for Employee Row -->
  <div id="actionMenu" class="action-menu" style="display: none; position: absolute; background: white; border: 1px solid var(--border); border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000;">
    <button class="action-menu-item" onclick="window.viewEmployee(window.currentActionId)">👁 View Details</button>
  </div>

  <script>
    (function() {
      let currentPage = 0;
      const pageSize = 10;
      let allFilters = {};

      window.loadEmployees = function() {
        const searchQuery = document.getElementById('searchInput')?.value || '';
        const departmentFilter = document.getElementById('deptFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';

        allFilters = {
          search: searchQuery,
          department_id: departmentFilter,
          status: statusFilter
        };

        const params = new URLSearchParams({
          page: currentPage,
          limit: pageSize,
          search: searchQuery,
          department_id: departmentFilter,
          status: statusFilter
        });

        const apiUrl = `modules/hr_core/api.php?action=getEmployees&${params}`;
        
        fetch(apiUrl)
          .then(response => {
            if (!response.ok) throw new Error('API Error: ' + response.status);
            return response.json();
          })
          .then(data => {
            if (data.success && data.data?.employees) {
              window.displayEmployees(data.data.employees);
              document.getElementById('totalCount').textContent = data.data.employees.length;
              document.getElementById('grandTotal').textContent = data.data.total || data.data.employees.length;
              document.getElementById('currentPage').textContent = currentPage + 1;

              const stats = data.data.stats || {total: 0, active: 0, on_leave: 0, resigned: 0};
              document.getElementById('totalEmployees').textContent = stats.total || 0;
              document.getElementById('activeCount').textContent = stats.active || 0;
              document.getElementById('onLeaveCount').textContent = stats.on_leave || 0;
              document.getElementById('resignedCount').textContent = stats.resigned || 0;

              // Update side panel stats
              window.updateSidePanelStats(stats);

              window.updateActiveFiltersDisplay();
              window.updatePaginationButtons();
            } else {
              window.displayEmptyState();
            }
          })
          .catch(error => {
            console.error('Fetch Error:', error);
            window.displayErrorState(error.message);
          });
      };

      window.displayEmployees = function(employees) {
        const tbody = document.getElementById('employeesList');
        if (!tbody) return;
        
        tbody.innerHTML = '';

        if (!employees || employees.length === 0) {
          window.displayEmptyState();
          return;
        }

        employees.forEach((emp) => {
          const statusClass = emp.employment_status?.toLowerCase() === 'active' ? 'status-active' : 
                             emp.employment_status?.toLowerCase() === 'on_leave' ? 'status-leave' : 
                             'status-inactive';
          
          const row = `
            <tr class="table-row-hover">
              <td style="text-align: left;"><code style="background: var(--bg-light); padding: 2px 6px; border-radius: 3px; font-size: 12px;">${emp.code || emp.employee_code || ''}</code></td>
              <td style="text-align: left; font-weight: 500;">${(emp.first_name || '') + ' ' + (emp.last_name || '')}</td>
              <td style="text-align: left; font-size: 14px; color: var(--text-light);">${emp.email || '-'}</td>
              <td style="text-align: left;">${emp.department || '-'}</td>
              <td style="text-align: left; font-size: 13px;">${emp.hire_date || '-'}</td>
              <td style="text-align: center;">
                <span class="status-pill ${statusClass}">
                  ${emp.employment_status || '-'}
                </span>
              </td>
              <td style="text-align: center;">
                <div style="position: relative; display: inline-block;">
                  <button class="action-menu-btn" onclick="window.toggleActionMenu(${emp.id})" title="Actions">⋮</button>
                  <div class="action-menu" id="menu-${emp.id}" style="display: none;">
                    <button class="action-menu-item" onclick="window.viewEmployee(${emp.id})">👁 View</button>
                    <button class="action-menu-item" onclick="window.editEmployee(${emp.id})">✏️ Edit</button>
                    <button class="action-menu-item action-menu-danger" onclick="window.deleteEmployee(${emp.id})">🗑 Delete</button>
                  </div>
                </div>
              </td>
            </tr>
          `;
          tbody.innerHTML += row;
        });
      };

      window.displayEmptyState = function() {
        const tbody = document.getElementById('employeesList');
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 4rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 64px; margin-bottom: 1rem;">👥</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No employees found</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Get started by adding your first employee to the system.</p>
                <button class="btn btn-primary" onclick="window.openAddModal()">+ Add Your First Employee</button>
              </div>
            </td>
          </tr>
        `;
      };

      window.displayErrorState = function(error) {
        const tbody = document.getElementById('employeesList');
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 4rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 64px; margin-bottom: 1rem;">⚠️</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">Unable to Load Employees</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">${error || 'An error occurred while loading employees.'}</p>
                <button class="btn btn-primary" onclick="window.loadEmployees()">↻ Try Again</button>
              </div>
            </td>
          </tr>
        `;
      };

      window.updateActiveFiltersDisplay = function() {
        const display = document.getElementById('activeFiltersDisplay');
        const tags = document.getElementById('activeFiltersTags');
        
        if (!display || !tags) {
          console.warn('Filter display elements not found');
          return;
        }
        
        const hasActiveFilters = Object.values(allFilters).some(v => v);

        if (!hasActiveFilters) {
          display.style.display = 'none';
          return;
        }

        display.style.display = 'block';
        tags.innerHTML = '';

        if (allFilters.search) {
          tags.innerHTML += `<span class="filter-tag">Search: "${allFilters.search}" <button onclick="document.getElementById('searchInput').value=''; window.loadEmployees();" style="margin-left: 0.5rem; background: none; border: none; cursor: pointer; color: inherit;">✕</button></span>`;
        }
        if (allFilters.department_id) {
          const deptName = document.querySelector(`#deptFilter option[value="${allFilters.department_id}"]`)?.textContent || allFilters.department_id;
          tags.innerHTML += `<span class="filter-tag">${deptName} <button onclick="document.getElementById('deptFilter').value=''; window.loadEmployees();" style="margin-left: 0.5rem; background: none; border: none; cursor: pointer; color: inherit;">✕</button></span>`;
        }
        if (allFilters.status) {
          const statusName = document.querySelector(`#statusFilter option[value="${allFilters.status}"]`)?.textContent || allFilters.status;
          tags.innerHTML += `<span class="filter-tag">${statusName} <button onclick="document.getElementById('statusFilter').value=''; window.loadEmployees();" style="margin-left: 0.5rem; background: none; border: none; cursor: pointer; color: inherit;">✕</button></span>`;
        }
      };

      window.updatePaginationButtons = function() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        prevBtn.disabled = currentPage === 0;
      };

      window.expandFilters = function() {
        const advanced = document.getElementById('advancedFilters');
        const btn = document.getElementById('expandBtn');
        
        if (!advanced || !btn) {
          console.warn('Filter elements not found');
          return;
        }
        
        if (advanced.style.display === 'none') {
          advanced.style.display = 'block';
          btn.style.opacity = '0.7';
        } else {
          advanced.style.display = 'none';
          btn.style.opacity = '1';
        }
      };

      window.resetFilters = function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('deptFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('posFilter').value = '';
        document.getElementById('hiredFromFilter').value = '';
        document.getElementById('hiredToFilter').value = '';
        currentPage = 0;
        window.loadEmployees();
      };

      window.toggleActionMenu = function(id) {
        // Close all other menus
        document.querySelectorAll('.action-menu').forEach(menu => {
          if (menu.id !== `menu-${id}`) {
            menu.style.display = 'none';
          }
        });
        
        // Toggle current menu
        const menu = document.getElementById(`menu-${id}`);
        if (menu) {
          menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
      };

      // Close menu when clicking outside
      document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-menu-btn') && !event.target.closest('.action-menu')) {
          document.querySelectorAll('.action-menu').forEach(menu => {
            menu.style.display = 'none';
          });
        }
      });

      window.viewEmployee = function(id) {
        fetch(`modules/hr_core/api.php?action=getEmployeeById&id=${id}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const emp = data.data;
              const content = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                  <div>
                    <div style="margin-bottom: 1.5rem;">
                      <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600;">First Name</label>
                      <p style="font-size: 16px; color: var(--text-dark); font-weight: 500;">${emp.first_name || '-'}</p>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                      <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600;">Last Name</label>
                      <p style="font-size: 16px; color: var(--text-dark); font-weight: 500;">${emp.last_name || '-'}</p>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                      <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600;">Email</label>
                      <p style="font-size: 16px; color: var(--text-dark); font-weight: 500;">${emp.email || '-'}</p>
                    </div>
                  </div>
                  <div>
                    <div style="margin-bottom: 1.5rem;">
                      <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600;">Employee Code</label>
                      <p style="font-size: 16px; color: var(--text-dark); font-weight: 500;">${emp.employee_code || '-'}</p>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                      <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                      <p style="font-size: 16px; color: var(--text-dark); font-weight: 500;">${emp.employment_status || '-'}</p>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                      <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600;">Department</label>
                      <p style="font-size: 16px; color: var(--text-dark); font-weight: 500;">${emp.department || '-'}</p>
                    </div>
                  </div>
                </div>
              `;
              window.showModal('View Employee', content, 'view');
            }
          })
          .catch(error => console.error('Error:', error));
      };

      window.editEmployee = function(id) {
        fetch(`modules/hr_core/api.php?action=getEmployeeById&id=${id}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const emp = data.data;
              const content = `
                <form id="editEmployeeForm" onsubmit="window.submitEditEmployee(event, ${id})">
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                      <label style="display: block; font-size: 12px; color: var(--text-dark); margin-bottom: 0.5rem; font-weight: 600;">First Name</label>
                      <input type="text" name="first_name" value="${emp.first_name || ''}" placeholder="First name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 14px;">
                    </div>
                    <div>
                      <label style="display: block; font-size: 12px; color: var(--text-dark); margin-bottom: 0.5rem; font-weight: 600;">Last Name</label>
                      <input type="text" name="last_name" value="${emp.last_name || ''}" placeholder="Last name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 14px;">
                    </div>
                    <div>
                      <label style="display: block; font-size: 12px; color: var(--text-dark); margin-bottom: 0.5rem; font-weight: 600;">Email</label>
                      <input type="email" name="email" value="${emp.email || ''}" placeholder="Email" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 14px;">
                    </div>
                    <div>
                      <label style="display: block; font-size: 12px; color: var(--text-dark); margin-bottom: 0.5rem; font-weight: 600;">Status</label>
                      <select name="employment_status" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 14px;">
                        <option value="Active" ${emp.employment_status === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="On Leave" ${emp.employment_status === 'On Leave' ? 'selected' : ''}>On Leave</option>
                        <option value="Resigned" ${emp.employment_status === 'Resigned' ? 'selected' : ''}>Resigned</option>
                      </select>
                    </div>
                  </div>
                  <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="window.closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">💾 Update Employee</button>
                  </div>
                </form>
              `;
              window.showModal('Edit Employee', content, 'edit');
            }
          })
          .catch(error => console.error('Error:', error));
      };

      window.submitEditEmployee = function(event, id) {
        event.preventDefault();
        const form = event.target;
        const data = {
          id: id,
          first_name: form.querySelector('[name="first_name"]').value,
          last_name: form.querySelector('[name="last_name"]').value,
          email: form.querySelector('[name="email"]').value,
          employment_status: form.querySelector('[name="employment_status"]').value
        };

        fetch(`modules/hr_core/api.php?action=updateEmployee`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              alert('Employee updated successfully');
              window.closeModal();
              window.loadEmployees();
            } else {
              alert('Error: ' + (result.message || 'Failed to update employee'));
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error updating employee: ' + error.message);
          });
      };

      window.deleteEmployee = function(id) {
        const content = `
          <div style="text-align: center; padding: 2rem;">
            <div style="font-size: 48px; margin-bottom: 1rem;">⚠️</div>
            <h3 style="font-size: 18px; color: var(--text-dark); margin-bottom: 0.5rem;">Delete Employee?</h3>
            <p style="color: var(--text-light); margin-bottom: 2rem;">This action cannot be undone. The employee record will be permanently deleted from the system.</p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
              <button class="btn btn-outline" onclick="window.closeModal()">Cancel</button>
              <button class="btn btn-danger" onclick="window.confirmDelete(${id})">🗑 Delete Employee</button>
            </div>
          </div>
        `;
        window.showModal('Delete Employee', content, 'delete');
      };

      window.confirmDelete = function(id) {
        fetch(`modules/hr_core/api.php?action=deleteEmployee&id=${id}`, {
          method: 'DELETE'
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Employee deleted successfully');
              window.closeModal();
              window.loadEmployees();
            } else {
              alert('Error: ' + (data.message || 'Failed to delete employee'));
            }
          })
          .catch(error => console.error('Error:', error));
      };

      window.showModal = function(title, content, type) {
        let modal = document.getElementById('actionModal');
        if (!modal) {
          modal = document.createElement('div');
          modal.id = 'actionModal';
          modal.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" onclick="if(event.target === this) window.closeModal()">
              <div style="background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); max-width: 90%; width: 1000px; max-height: 85vh; overflow-y: auto; display: flex; flex-direction: column;">
                <div style="padding: 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; flex-shrink: 0;">
                  <h2 id="modalTitle" style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0;"></h2>
                  <button onclick="window.closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-light);">✕</button>
                </div>
                <div id="modalContent" style="padding: 2rem; flex: 1; overflow-y: auto;"></div>
              </div>
            </div>
          `;
          document.body.appendChild(modal);
        }

        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalContent').innerHTML = content;
        modal.style.display = 'flex';
      };

      window.closeModal = function() {
        const modal = document.getElementById('actionModal');
        if (modal) {
          modal.style.display = 'none';
        }
      };

      window.previousPage = function() {
        if (currentPage > 0) {
          currentPage--;
          window.loadEmployees();
        }
      };

      window.nextPage = function() {
        currentPage++;
        window.loadEmployees();
      };

      window.expandFilters = function() {
        const advanced = document.getElementById('advancedFilters');
        const btn = document.getElementById('expandBtn');
        if (advanced.style.display === 'none') {
          advanced.style.display = 'block';
          btn.style.opacity = '0.7';
        } else {
          advanced.style.display = 'none';
          btn.style.opacity = '1';
        }
      };

      window.resetFilters = function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('deptFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('posFilter').value = '';
        document.getElementById('hiredFromFilter').value = '';
        document.getElementById('hiredToFilter').value = '';
        currentPage = 0;
        window.loadEmployees();
      };

      window.toggleSidePanel = function() {
        const panel = document.querySelector('.directory-right-column');
        panel.classList.toggle('panel-collapsed');
      };

      window.exportToCSV = function() {
        const employees = [];
        const rows = document.querySelectorAll('#employeesTable tbody tr');
        
        if (rows.length === 0 || rows[0].cells[0].textContent.includes('No employees')) {
          alert('No employees to export');
          return;
        }

        // Get headers
        const headers = ['Code', 'Name', 'Email', 'Department', 'Hire Date', 'Status'];
        
        // Get data rows
        rows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length >= 6) {
            employees.push({
              code: cells[0].textContent.trim(),
              name: cells[1].textContent.trim(),
              email: cells[2].textContent.trim(),
              department: cells[3].textContent.trim(),
              hireDate: cells[4].textContent.trim(),
              status: cells[5].textContent.trim()
            });
          }
        });

        if (employees.length === 0) {
          alert('No employee data to export');
          return;
        }

        // Generate CSV
        let csv = headers.join(',') + '\n';
        employees.forEach(emp => {
          csv += [emp.code, emp.name, emp.email, emp.department, emp.hireDate, emp.status].map(field => `"${field}"`).join(',') + '\n';
        });

        // Download CSV
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `employees_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      };

      window.loadRecentEmployees = function() {
        const recentList = document.getElementById('recentEmployeesList');
        // For now, show a message. In a real scenario, you'd fetch from an API
        recentList.innerHTML = `
          <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">
            Data synced from HR 1
          </p>
        `;
      };

      window.updateSidePanelStats = function(stats) {
        // Calculate new hires this month
        const today = new Date();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();
        const newHiresThisMonth = stats.new_hires_this_month || 0;
        document.getElementById('insight-newHires').textContent = newHiresThisMonth;
        document.getElementById('insight-newHires-desc').textContent = new Date(currentYear, currentMonth).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

        // Employees on leave today
        const onLeaveTodayCount = stats.on_leave_today || stats.on_leave || 0;
        document.getElementById('insight-onLeaveToday').textContent = onLeaveTodayCount;
        document.getElementById('insight-onLeaveToday-desc').textContent = onLeaveTodayCount === 1 ? 'employee' : 'employees';

        // Incomplete profiles (missing critical fields)
        const incompleteProfiles = stats.incomplete_profiles || 0;
        document.getElementById('insight-incompleteProfiles').textContent = incompleteProfiles;
        document.getElementById('insight-incompleteProfiles-desc').textContent = incompleteProfiles === 1 ? 'need attention' : 'need attention';

        // Expiring contracts or upcoming regularization
        const expiringSoon = stats.expiring_contracts || 0;
        document.getElementById('insight-expiringSoon').textContent = expiringSoon;
        document.getElementById('insight-expiringSoon-desc').textContent = expiringSoon === 1 ? 'contract ending' : 'contracts ending';
      };

      window.triggerImportDialog = function() {
        document.getElementById('importFileInput').click();
      };

      document.getElementById('importFileInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        const validTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!validTypes.includes(file.type) && !file.name.endsWith('.csv') && !file.name.endsWith('.xlsx')) {
          alert('Please select a CSV or Excel file');
          return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'importEmployees');

        fetch('modules/hr_core/api.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(`Import successful! ${data.imported || 0} employees imported.`);
            window.loadEmployees();
          } else {
            alert(`Import failed: ${data.message || 'Unknown error'}`);
          }
        })
        .catch(error => {
          console.error('Import error:', error);
          alert('Import failed: ' + error.message);
        })
        .finally(() => {
          // Reset file input
          e.target.value = '';
        });
      });

      function attachEventListeners() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
          searchInput.addEventListener('keyup', function() {
            currentPage = 0;
            if (this.value.length > 2 || this.value.length === 0) {
              window.loadEmployees();
            }
          });
        }

        [document.getElementById('deptFilter'), document.getElementById('statusFilter')].forEach(el => {
          if (el) {
            el.addEventListener('change', function() {
              currentPage = 0;
              window.loadEmployees();
            });
          }
        });

        // Close action menu on outside click
        document.addEventListener('click', function(e) {
          if (!e.target.closest('.action-btn-primary')) {
            document.getElementById('actionMenu').style.display = 'none';
          }
        });
      }

      window.updateArchiveCount = function() {
        fetch(`modules/hr_core/api.php?action=getArchivedEmployees`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const count = (data.data || []).length;
              const countBadge = document.getElementById('archiveCount');
              if (countBadge) {
                countBadge.textContent = count;
                countBadge.style.display = count > 0 ? 'inline-block' : 'none';
              }
            }
          })
          .catch(error => console.error('Error updating archive count:', error));
      };

      window.openArchiveModal = function() {
        fetch(`modules/hr_core/api.php?action=getArchivedEmployees`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const employees = data.data || [];
              let content = '';

              if (employees.length === 0) {
                content = `
                  <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 48px; margin-bottom: 1rem;">📦</div>
                    <h3 style="font-size: 18px; color: var(--text-dark); margin-bottom: 0.5rem;">No Archived Employees</h3>
                    <p style="color: var(--text-light);">No deleted or archived employees at this time.</p>
                  </div>
                `;
              } else {
                content = `
                  <div style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-striped" style="width: 100%;">
                      <thead class="table-head-fixed">
                        <tr>
                          <th style="width: 15%; text-align: left;">Code</th>
                          <th style="width: 25%; text-align: left;">Name</th>
                          <th style="width: 25%; text-align: left;">Email</th>
                          <th style="width: 20%; text-align: left;">Department</th>
                          <th style="width: 15%; text-align: center;">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        ${employees.map(emp => `
                          <tr>
                            <td><code style="background: var(--bg-light); padding: 2px 6px; border-radius: 3px; font-size: 12px;">${emp.code || emp.employee_code || ''}</code></td>
                            <td style="font-weight: 500;">${(emp.first_name || '') + ' ' + (emp.last_name || '')}</td>
                            <td style="font-size: 14px; color: var(--text-light);">${emp.email || '-'}</td>
                            <td>${emp.department || '-'}</td>
                            <td style="text-align: center; display: flex; justify-content: center; gap: 0.5rem;">
                              <button class="btn btn-sm" style="background: var(--success); color: white; padding: 0.4rem 0.8rem; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="window.restoreEmployee(${emp.id})">↺ Restore</button>
                              <button class="btn btn-sm" style="background: var(--danger); color: white; padding: 0.4rem 0.8rem; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="window.permanentlyDeleteEmployee(${emp.id})">🗑 Delete</button>
                            </td>
                          </tr>
                        `).join('')}
                      </tbody>
                    </table>
                  </div>
                `;
              }

              window.showModal('Archived Employees', content, 'archive');
            } else {
              alert('Error loading archived employees: ' + (data.message || 'Unknown error'));
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error loading archived employees: ' + error.message);
          });
      };

      window.restoreEmployee = function(id) {
        if (!confirm('Restore this employee?')) return;

        fetch(`modules/hr_core/api.php?action=restoreEmployee&id=${id}`, {
          method: 'POST'
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Employee restored successfully');
              window.openArchiveModal();
              window.loadEmployees();
              window.updateArchiveCount();
            } else {
              alert('Error: ' + (data.message || 'Failed to restore employee'));
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error restoring employee: ' + error.message);
          });
      };

      window.permanentlyDeleteEmployee = function(id) {
        const content = `
          <div style="text-align: center; padding: 2rem;">
            <div style="font-size: 48px; margin-bottom: 1rem;">⚠️</div>
            <h3 style="font-size: 18px; color: var(--text-dark); margin-bottom: 0.5rem;">Permanently Delete Employee?</h3>
            <p style="color: var(--text-light); margin-bottom: 2rem;">This action cannot be undone. The employee record will be permanently removed from the database. <strong>Admin authentication required.</strong></p>
            
            <form id="permanentDeleteForm" onsubmit="window.confirmPermanentDelete(event, ${id})" style="margin-bottom: 1.5rem;">
              <div style="margin-bottom: 1.5rem; text-align: left; max-width: 300px; margin-left: auto; margin-right: auto;">
                <label style="display: block; font-size: 12px; color: var(--text-dark); margin-bottom: 0.5rem; font-weight: 600;">Enter Admin Password</label>
                <input type="password" id="adminPassword" name="password" placeholder="Admin password" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; box-sizing: border-box;">
              </div>
              
              <div style="display: flex; gap: 1rem; justify-content: center;">
                <button type="button" class="btn btn-outline" onclick="window.closeModal()">Cancel</button>
                <button type="submit" class="btn btn-danger" style="background: var(--danger); color: white; border: none;">🗑 Permanently Delete</button>
              </div>
            </form>
          </div>
        `;
        window.showModal('Permanent Delete Employee', content, 'delete');
        setTimeout(() => {
          const passwordInput = document.getElementById('adminPassword');
          if (passwordInput) passwordInput.focus();
        }, 100);
      };

      window.confirmPermanentDelete = function(event, id) {
        event.preventDefault();
        const password = document.getElementById('adminPassword').value;

        if (!password) {
          alert('Please enter the admin password');
          return;
        }

        fetch(`modules/hr_core/api.php?action=permanentlyDeleteEmployee&id=${id}`, {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ password: password })
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Employee permanently deleted');
              window.closeModal();
              window.openArchiveModal();
              window.updateArchiveCount();
            } else {
              alert('Error: ' + (data.message || 'Failed to delete employee'));
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error deleting employee: ' + error.message);
          });
      };

      setTimeout(attachEventListeners, 100);
      window.loadEmployees();
      window.updateArchiveCount();
    })();
  </script>

  <!-- Styles for new components -->
  <style>
    /* Container and Layout */
    .employee-directory-container {
      max-width: 1320px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    .directory-layout {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 2rem;
      align-items: start;
    }

    .directory-left-column {
      display: flex;
      flex-direction: column;
      gap: 2rem;
    }

    .directory-right-column {
      position: sticky;
      top: 20px;
      height: fit-content;
      transition: all 0.3s ease;
    }

    /* KPI Section */
    .kpi-section {
      margin-bottom: 0;
    }

    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }

    .kpi-card {
      background: white;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 1.25rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      border-color: var(--primary);
    }

    .kpi-icon {
      font-size: 28px;
      line-height: 1;
    }

    .kpi-content {
      flex: 1;
    }

    .kpi-label {
      margin: 0;
      font-size: 11px;
      color: var(--text-light);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .kpi-value {
      margin: 0.25rem 0 0 0;
      font-size: 28px;
      font-weight: 700;
      line-height: 1;
      color: var(--text-dark);
    }

    .kpi-description {
      margin: 0.25rem 0 0 0;
      font-size: 11px;
      color: var(--text-light);
    }

    .kpi-card-active .kpi-value { color: #22c55e; }
    .kpi-card-leave .kpi-value { color: #f59e0b; }
    .kpi-card-resigned .kpi-value { color: #ef4444; }

    /* Filters */
    .filters-section {
      margin-bottom: 0;
    }

    .filters-row {
      display: flex;
      gap: 1rem;
    }

    .filters-row .form-group {
      margin: 0;
    }

    /* Table */
    .table-section {
      margin-bottom: 0;
    }

    .table-container {
      overflow-x: auto;
      border-top: 1px solid var(--border);
      margin-top: 1rem;
    }

    .table-head-fixed {
      position: sticky;
      top: 0;
      background: white;
      z-index: 10;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .table tbody tr {
      height: 56px;
      border-bottom: 1px solid var(--border);
    }

    .table tbody tr:nth-child(odd) {
      background-color: rgba(0, 0, 0, 0.01);
    }

    .table tbody tr:hover {
      background-color: var(--bg-light);
      transition: background-color 0.2s ease;
    }

    .table tbody tr td {
      padding: 14px 16px;
      vertical-align: middle;
    }

    .status-pill {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      text-transform: capitalize;
    }

    .status-active {
      background-color: #dcfce7;
      color: #166534;
    }

    .status-leave {
      background-color: #fef3c7;
      color: #92400e;
    }

    .status-inactive {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .action-menu-btn {
      background: white;
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 8px 12px;
      cursor: pointer;
      font-size: 18px;
      transition: all 0.2s ease;
      color: var(--text-dark);
      font-weight: bold;
      min-width: 40px;
    }

    .action-menu-btn:hover {
      background: var(--bg-light);
      border-color: var(--primary);
      color: var(--primary);
    }

    .action-menu {
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      border: 1px solid var(--border);
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      min-width: 150px;
      z-index: 100;
      margin-top: 0.5rem;
      overflow: hidden;
    }

    .action-menu-item {
      display: block;
      width: 100%;
      padding: 0.75rem 1rem;
      background: none;
      border: none;
      text-align: left;
      cursor: pointer;
      font-size: 14px;
      color: var(--text-dark);
      transition: all 0.2s ease;
      border-bottom: 1px solid var(--border);
    }

    .action-menu-item:last-child {
      border-bottom: none;
    }

    .action-menu-item:hover {
      background: var(--bg-light);
      color: var(--primary);
    }

    .action-menu-item.action-menu-danger:hover {
      background: #fee2e2;
      color: var(--danger);
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      opacity: 0.9;
    }

    .btn-outline {
      background: white;
      border: 1px solid var(--border);
      color: var(--text-dark);
    }

    .btn-outline:hover {
      background: var(--bg-light);
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .btn-danger:hover {
      opacity: 0.9;
    }

    .action-btn {
      background: var(--bg-light);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 6px 10px;
      cursor: pointer;
      font-size: 13px;
      transition: all 0.2s ease;
      min-width: 60px;
    }

    .action-btn:hover {
      background: var(--primary);
      border-color: var(--primary);
      color: white;
    }

    .action-btn-primary {
      background: var(--primary);
      border-color: var(--primary);
      color: white;
    }

    .action-btn-primary:hover {
      opacity: 0.9;
    }

    .action-btn-secondary {
      background: var(--warning);
      border-color: var(--warning);
      color: white;
    }

    .action-btn-secondary:hover {
      opacity: 0.9;
    }

    .action-btn-danger {
      background: var(--danger);
      border-color: var(--danger);
      color: white;
    }

    .action-btn-danger:hover {
      opacity: 0.9;
    }

    .pagination-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-top: 1px solid var(--border);
    }

    .filter-tag {
      display: inline-block;
      background: var(--bg-light);
      border: 1px solid var(--border);
      border-radius: 4px;
      padding: 4px 10px;
      font-size: 12px;
      white-space: nowrap;
    }

    /* Side Panel */
    .side-panel {
      background: white;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 0;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      overflow: hidden;
    }

    .side-panel-header {
      padding: 1.25rem;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(135deg, var(--bg-light) 0%, var(--bg-lighter) 100%);
    }

    .side-panel-title {
      margin: 0;
      font-size: 14px;
      font-weight: 600;
      color: var(--text-dark);
    }

    .side-panel-toggle {
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: var(--text-light);
      padding: 0;
      width: 24px;
      height: 24px;
      display: none;
      align-items: center;
      justify-content: center;
      transition: color 0.2s ease;
    }

    .side-panel-toggle:hover {
      color: var(--text-dark);
    }

    .side-panel-section {
      padding: 1.25rem;
      border-bottom: 1px solid var(--border);
    }

    .side-panel-section:last-child {
      border-bottom: none;
    }

    .side-panel-subtitle {
      margin: 0 0 1rem 0;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-light);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Side Panel Action Buttons */
    .side-action-btn {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 0.75rem;
      border: 1px solid var(--border);
      background: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      color: var(--text-dark);
      transition: all 0.2s ease;
    }

    .side-action-btn:last-of-type {
      margin-bottom: 0;
    }

    .side-action-btn:hover {
      background: var(--bg-light);
      border-color: var(--primary);
      color: var(--primary);
    }

    .side-action-primary {
      background: var(--primary);
      border-color: var(--primary);
      color: white;
    }

    .side-action-primary:hover {
      background: #0056b3;
      border-color: #0056b3;
    }

    .side-action-icon {
      font-size: 16px;
    }

    /* Stats Indicators */
    .stat-indicator {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      font-size: 11px;
      font-weight: bold;
    }

    .stat-active {
      background-color: #dcfce7;
      color: #166534;
    }

    .stat-leave {
      background-color: #fef3c7;
      color: #92400e;
    }

    .stat-resigned {
      background-color: #fee2e2;
      color: #991b1b;
    }

    /* Insights */
    .insight-item {
      display: flex;
      gap: 0.75rem;
      padding: 0.875rem;
      margin-bottom: 0.75rem;
      border-radius: 6px;
      border-left: 3px solid transparent;
      transition: all 0.2s ease;
    }

    .insight-item:last-child {
      margin-bottom: 0;
    }

    .insight-item:hover {
      background: rgba(0, 0, 0, 0.02);
      transform: translateX(2px);
    }

    .insight-new-hires {
      background-color: #f0fdf4;
      border-left-color: #22c55e;
    }

    .insight-on-leave {
      background-color: #fffbeb;
      border-left-color: #f59e0b;
    }

    .insight-incomplete {
      background-color: #fef2f2;
      border-left-color: #ef4444;
    }

    .insight-expiring {
      background-color: #faf5ff;
      border-left-color: #a855f7;
    }

    .insight-icon {
      font-size: 20px;
      line-height: 1;
      flex-shrink: 0;
    }

    .insight-content {
      flex: 1;
      min-width: 0;
    }

    .insight-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.25rem;
    }

    .insight-value {
      font-size: 20px;
      font-weight: 700;
      color: var(--primary);
      line-height: 1;
      margin-bottom: 0.25rem;
    }

    .insight-description {
      font-size: 11px;
      color: var(--text-light);
    }

    /* Side Panel Stats */
    .side-stat-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--border);
    }

    .side-stat-item:last-child {
      border-bottom: none;
    }

    .side-stat-label {
      font-size: 13px;
      color: var(--text-light);
      font-weight: 500;
    }

    .side-stat-value {
      font-size: 20px;
      font-weight: 700;
      color: var(--primary);
    }

    /* Recent Employees List */
    #recentEmployeesList {
      max-height: 280px;
      overflow-y: auto;
    }

    .recent-employee-item {
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--border);
      font-size: 12px;
    }

    .recent-employee-item:last-child {
      border-bottom: none;
    }

    .recent-employee-name {
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 0.25rem;
    }

    .recent-employee-date {
      font-size: 11px;
      color: var(--text-light);
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .directory-layout {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      .directory-right-column {
        position: static;
      }

      .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .employee-directory-container {
        padding: 0 0.75rem;
      }

      .kpi-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
      }

      .kpi-card {
        padding: 1rem;
      }

      .kpi-value {
        font-size: 24px;
      }

      .filters-row {
        flex-direction: column;
      }

      .table tbody tr {
        height: auto;
      }

      .table tbody tr td {
        padding: 10px 8px;
        font-size: 13px;
      }

      .side-panel-toggle {
        display: flex;
      }

      .directory-right-column.panel-collapsed {
        display: none;
      }

      .side-panel-toggle[data-collapsed="true"]::before {
        content: "⊞";
      }
    }

    @media (max-width: 480px) {
      .directory-layout {
        gap: 1rem;
      }

      .kpi-card {
        padding: 0.75rem;
        gap: 0.5rem;
      }

      .kpi-icon {
        font-size: 20px;
      }

      .kpi-value {
        font-size: 20px;
      }

      .kpi-label,
      .kpi-description {
        font-size: 10px;
      }

      .table thead {
        font-size: 12px;
      }

      .table tbody tr td {
        padding: 8px 4px;
        font-size: 12px;
      }

      .action-btn {
        padding: 4px 6px;
        font-size: 12px;
        min-width: auto;
      }
    }
  </style>

```
