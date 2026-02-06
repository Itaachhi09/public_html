
<main class="main-content departments-container">
  <div class="departments-layout">
    <!-- Left Column: Main Content -->
    <div class="departments-left-column">
      <!-- KPI Cards - Operational Metrics -->
      <section class="department-kpi-section">
        <div class="department-kpi-grid">
          <div class="card" style="text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
              <div style="font-size: 32px;">🏢</div>
              <p style="font-size: 12px; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin: 0; font-weight: 600;">Total Departments</p>
              <h2 style="font-size: 36px; font-weight: 700; color: var(--text-dark); margin: 0;" id="totalDepts">0</h2>
              <p style="font-size: 12px; color: var(--text-light); margin: 0;">All units</p>
            </div>
          </div>

          <div class="card" style="text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
              <div style="font-size: 32px; color: var(--success);">✓</div>
              <p style="font-size: 12px; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin: 0; font-weight: 600;">Operational</p>
              <h2 style="font-size: 36px; font-weight: 700; color: var(--success); margin: 0;" id="activeDepts">0</h2>
              <p style="font-size: 12px; color: var(--text-light); margin: 0;">Units</p>
            </div>
          </div>

          <div class="card" style="text-align: center;">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
              <div style="font-size: 32px;">👥</div>
              <p style="font-size: 12px; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin: 0; font-weight: 600;">Staff Assigned</p>
              <h2 style="font-size: 36px; font-weight: 700; color: var(--primary); margin: 0;" id="totalStaff">0</h2>
              <p style="font-size: 12px; color: var(--text-light); margin: 0;">All departments</p>
            </div>
          </div>

          <div class="card" style="text-align: center; border-color: var(--danger); background: rgba(239, 68, 68, 0.02);">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
              <div style="font-size: 32px; color: var(--danger);">⚠️</div>
              <p style="font-size: 12px; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin: 0; font-weight: 600;">No Manager</p>
              <h2 style="font-size: 36px; font-weight: 700; color: var(--danger); margin: 0;" id="noManagerDepts">0</h2>
              <p style="font-size: 12px; color: var(--text-light); margin: 0;">Needs assignment</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Search with Quick Filter Chips -->
      <section class="department-filters-section">
        <div class="card">
          <div style="display: flex; gap: 1.5rem; align-items: flex-end; flex-wrap: wrap;">
            <!-- Main Search -->
            <div style="flex: 1; min-width: 250px;">
              <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-dark); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.3px;">Search</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 16px;">🔍</span>
                <input type="text" id="searchInput" placeholder="Search departments, code..." style="width: 100%; padding: 0.75rem 0.75rem 0.75rem 36px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; background: white; transition: all 0.2s ease;">
              </div>
            </div>

            <!-- Quick Filter Chips -->
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
              <button class="filter-chip" data-filter="with-manager" onclick="window.toggleManagerFilter('with-manager')" style="padding: 0.5rem 0.875rem; border: 1px solid var(--border); border-radius: 20px; background: white; cursor: pointer; font-size: 13px; font-weight: 500; color: var(--text-dark); transition: all 0.2s ease; white-space: nowrap;">
                <span style="color: #22c55e; margin-right: 0.25rem;">✓</span> With Manager
              </button>
              <button class="filter-chip" data-filter="no-manager" onclick="window.toggleManagerFilter('no-manager')" style="padding: 0.5rem 0.875rem; border: 1px solid var(--border); border-radius: 20px; background: white; cursor: pointer; font-size: 13px; font-weight: 500; color: var(--text-dark); transition: all 0.2s ease; white-space: nowrap;">
                <span style="color: #ef4444; margin-right: 0.25rem;">⚠️</span> No Manager
              </button>
              <button class="filter-chip" data-filter="active" onclick="window.toggleManagerFilter('active')" style="padding: 0.5rem 0.875rem; border: 1px solid var(--border); border-radius: 20px; background: white; cursor: pointer; font-size: 13px; font-weight: 500; color: var(--text-dark); transition: all 0.2s ease; white-space: nowrap;">
                <span style="color: #3b82f6; margin-right: 0.25rem;">✓</span> Active
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Departments Table Section -->
      <section class="department-table-section">
        <div class="card">
          <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 16px; color: var(--text-dark); font-weight: 600; margin: 0;">Department Directory</h3>
            <button onclick="window.resetFilters()" style="padding: 0.5rem 1rem; border: 1px solid var(--border); border-radius: 6px; background: white; cursor: pointer; font-size: 13px; color: var(--text-light); font-weight: 500; transition: all 0.2s ease;">↻ Reset</button>
          </div>

          <div style="overflow-x: auto; margin-top: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
              <thead style="background: rgba(30, 64, 175, 0.02); border-bottom: 2px solid var(--border);">
                <tr>
                  <th style="width: 15%; text-align: left; padding: 0.75rem 1rem; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-dark);">Name</th>
                  <th style="width: 10%; text-align: left; padding: 0.75rem 1rem; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-dark);">Code</th>
                  <th style="width: 18%; text-align: left; padding: 0.75rem 1rem; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-dark);">Type</th>
                  <th style="width: 20%; text-align: left; padding: 0.75rem 1rem; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-dark);">Manager</th>
                  <th style="width: 10%; text-align: center; padding: 0.75rem 1rem; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-dark);">Staff</th>
                  <th style="width: 10%; text-align: center; padding: 0.75rem 1rem; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-dark);">Status</th>
                  <th style="width: 17%; text-align: center; padding: 0.75rem 1rem; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-dark);">Actions</th>
                </tr>
              </thead>
              <tbody id="departmentsList">
                <tr>
                  <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">🏢</div>
                      <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No departments found</p>
                      <p style="font-size: 14px; margin-bottom: 1.5rem;">Create departments to organize hospital operations.</p>
                      <button class="btn btn-primary" onclick="window.openDepartmentModal()">+ Add Department</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Right Column: Context Panel -->
    <aside class="departments-right-column">
      <div class="card" style="padding: 1.5rem;">
        <h3 style="font-size: 14px; color: var(--text-dark); font-weight: 600; margin: 0 0 1.25rem 0; text-transform: uppercase; letter-spacing: 0.3px;">Management</h3>

        <!-- Quick Actions -->
        <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
          <button onclick="window.openDepartmentModal()" style="padding: 0.75rem; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <span>➕</span> Add Department
          </button>
          <button onclick="alert('Assign managers feature coming soon')" style="padding: 0.75rem; background: white; color: var(--text-dark); border: 1px solid var(--border); border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <span>👤</span> Assign Manager
          </button>
        </div>

        <!-- Departments Without Manager -->
        <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
          <p style="font-size: 11px; font-weight: 600; color: var(--danger); text-transform: uppercase; letter-spacing: 0.3px; margin: 0 0 0.75rem 0;">Needs Manager Assignment</p>
          <div id="noManagerList" style="max-height: 140px; overflow-y: auto; font-size: 12px;">
            <p style="color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>

        <!-- Low Staffing Departments -->
        <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border);">
          <p style="font-size: 11px; font-weight: 600; color: var(--warning); text-transform: uppercase; letter-spacing: 0.3px; margin: 0 0 0.75rem 0;">Low Staffing Alert</p>
          <div id="lowStaffingList" style="max-height: 140px; overflow-y: auto; font-size: 12px;">
            <p style="color: var(--text-light); text-align: center; padding: 1rem;">All departments adequately staffed</p>
          </div>
        </div>

        <!-- Recently Added -->
        <div>
          <p style="font-size: 11px; font-weight: 600; color: var(--text-dark); text-transform: uppercase; letter-spacing: 0.3px; margin: 0 0 0.75rem 0;">Recently Added</p>
          <div id="recentDeptsList" style="max-height: 140px; overflow-y: auto; font-size: 12px;">
            <p style="color: var(--text-light); text-align: center; padding: 1rem;">None yet</p>
          </div>
        </div>
      </div>
    </aside>
  </div>
</main>

<!-- Department Modal -->
<div id="departmentModal" class="modal">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Department</h2>
      <button class="modal-close" onclick="closeDepartmentModal()">&times;</button>
    </div>
    <form id="departmentForm">
      <div class="form-group">
        <label class="form-label">Name *</label>
        <input type="text" name="name" class="form-input" required>
      </div>
      <div class="form-group">
        <label class="form-label">Code *</label>
        <input type="text" name="code" class="form-input" placeholder="e.g., HR, FIN" required>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-input" placeholder="Department description..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Manager</label>
        <select name="manager_id" class="form-select">
          <option value="">Select manager...</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeDepartmentModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function() {
    window.loadDepartments = function() {
      console.log('[Departments] Loading departments...');
      
      const searchQuery = document.getElementById('searchInput')?.value || '';
      
      // Get active manager filters
      const activeChips = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.filter)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        filter: activeChips
      });
      
      // Show loading state
      const tbody = document.getElementById('departmentsList');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="100%" style="text-align: center; padding: 2rem;"><div style="display: inline-block;"><div class="spinner" style="margin: 0 auto 1rem; border: 4px solid rgba(30, 64, 175, 0.2); border-top: 4px solid #3b82f6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div><p>Loading...</p></div></td></tr>';
      }

      fetch(`modules/hr_core/api.php?action=getDepartments&${params}`)
        .then(res => res.json())
        .then(response => {
          if (!response.success) {
            console.error('[Departments] API error:', response.message);
            if (tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align: center; padding: 2rem; color: #ef4444;"><strong>Error:</strong> ${response.message}</td></tr>`;
            return;
          }
          
          if (response.data && response.data.departments) {
            window.displayDepartments(response.data.departments);
            
            if (response.data.stats) {
              const updateElement = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value || 0;
              };
              updateElement('totalDepts', response.data.stats.total);
              updateElement('activeDepts', response.data.stats.active);
              updateElement('totalStaff', response.data.stats.total_staff);
              updateElement('noManagerDepts', response.data.stats.no_manager);
            }
            
            window.loadNoManagerDepartments?.(response.data.no_manager_depts || []);
            window.loadLowStaffingDepartments?.(response.data.low_staffing || []);
            window.loadRecentDepartments?.(response.data.recent_depts || []);
            
            console.log('[Departments] Loaded successfully');
          } else {
            if (tbody) tbody.innerHTML = '<tr><td colspan="100%" style="text-align: center; padding: 3rem;">🏢 No department records found</td></tr>';
          }
        })
        .catch(error => {
          console.error('[Departments] Error:', error);
          if (tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align: center; padding: 2rem; color: #ef4444;"><strong>Error:</strong> Failed to load departments</td></tr>`;
        });
    };

    window.displayDepartments = function(departments) {
      const tbody = document.getElementById('departmentsList');
      tbody.innerHTML = '';

      if (!departments || departments.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">🏢</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No departments found</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Create departments to organize hospital operations.</p>
                <button class="btn btn-primary" onclick="window.openDepartmentModal()">+ Add Department</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      departments.forEach(dept => {
        const hasManager = dept.head_name || dept.manager_id;
        const rowClass = !hasManager ? 'dept-row-no-manager' : '';
        
        const managerDisplay = hasManager 
          ? `<span style="font-weight: 600; color: #22c55e;">✓ ${dept.head_name || 'Assigned'}</span>`
          : `<span class="dept-warning-badge">⚠️ Unassigned</span>`;

        const deptType = dept.type || 'Administrative';
        const staffCount = dept.staff_count || '0';
        const status = dept.status || 'Active';

        const row = `
          <tr style="border-bottom: 1px solid var(--border); transition: all 0.2s ease;" onmouseover="this.style.background='rgba(30, 64, 175, 0.02)'" onmouseout="this.style.background='white'">
            <td style="padding: 1rem; font-weight: 600; color: var(--text-dark);">${dept.name || '-'}</td>
            <td style="padding: 1rem; color: var(--text-dark);"><code style="background: var(--bg-light); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">${dept.code || '-'}</code></td>
            <td style="padding: 1rem; color: var(--text-dark);"><span style="display: inline-block; padding: 4px 10px; border-radius: 4px; background: rgba(59, 130, 246, 0.1); color: var(--primary); font-size: 12px; font-weight: 600;">${deptType}</span></td>
            <td style="padding: 1rem; color: var(--text-dark);">
              ${managerDisplay}
            </td>
            <td style="padding: 1rem; text-align: center; font-weight: 600; color: var(--text-dark);">${staffCount}</td>
            <td style="padding: 1rem; text-align: center;">
              <span style="display: inline-block; padding: 4px 10px; border-radius: 4px; background: rgba(34, 197, 94, 0.1); color: var(--success); font-size: 11px; font-weight: 600;">✓ ${status}</span>
            </td>
            <td style="padding: 1rem; text-align: center;">
              <div style="display: flex; gap: 0.5rem; justify-content: center;">
                <button onclick="window.editDepartment(${dept.id})" title="Edit" style="padding: 0.5rem; background: rgba(30, 64, 175, 0.1); border: none; border-radius: 4px; cursor: pointer; font-size: 14px; color: var(--primary); transition: all 0.2s ease;">✏</button>
                <button onclick="window.viewDepartment(${dept.id})" title="View" style="padding: 0.5rem; background: rgba(30, 64, 175, 0.1); border: none; border-radius: 4px; cursor: pointer; font-size: 14px; color: var(--primary); transition: all 0.2s ease;">👁</button>
                <button onclick="alert('More options')" title="More" style="padding: 0.5rem; background: rgba(30, 64, 175, 0.1); border: none; border-radius: 4px; cursor: pointer; font-size: 14px; color: var(--primary); transition: all 0.2s ease;">⋯</button>
              </div>
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    };

    window.openDepartmentModal = function() {
      document.getElementById('departmentForm').reset();
      delete document.getElementById('departmentForm').dataset.id;
      document.querySelector('#departmentModal .modal-title').textContent = 'Add Department';
      document.getElementById('departmentModal').classList.add('active');
    };

    window.closeDepartmentModal = function() {
      document.getElementById('departmentModal').classList.remove('active');
    };

    window.toggleManagerFilter = function(filter) {
      const chip = document.querySelector(`.filter-chip[data-filter="${filter}"]`);
      chip.classList.toggle('active');
      window.loadDepartments();
    };

    window.resetFilters = function() {
      document.getElementById('searchInput').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      window.loadDepartments();
    };

    window.editDepartment = function(id) {
      fetch(`modules/hr_core/api.php?action=getDepartmentById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const dept = data.data;
            const form = document.getElementById('departmentForm');
            if (form) {
              form.querySelector('input[name="name"]').value = dept.name || '';
              form.querySelector('input[name="code"]').value = dept.code || '';
              form.querySelector('textarea[name="description"]').value = dept.description || '';
              form.querySelector('select[name="manager_id"]').value = dept.manager_id || '';
              form.dataset.id = id;
              document.querySelector('#departmentModal .modal-title').textContent = 'Edit Department';
              window.openDepartmentModal();
            }
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.viewDepartment = function(id) {
      fetch(`modules/hr_core/api.php?action=getDepartmentById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const dept = data.data;
            alert(`Department: ${dept.name}\nCode: ${dept.code}\nType: ${dept.type || 'Admin'}\nManager: ${dept.head_name || 'Not assigned'}\nDescription: ${dept.description || 'None'}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.loadNoManagerDepartments = function(depts) {
      const list = document.getElementById('noManagerList');
      if (!list) return; // Element might not be in DOM
      
      if (!depts || depts.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: #22c55e; text-align: center; padding: 1rem;">✓ All managed</p>';
        return;
      }
      list.innerHTML = depts.slice(0, 5).map(dept => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px; cursor: pointer;" onclick="window.editDepartment(${dept.id})">
          <div style="font-weight: 600; color: var(--text-dark);">${dept.name}</div>
          <div style="color: var(--text-light); font-size: 11px;">${dept.code} • No assignment</div>
        </div>
      `).join('');
    };

    window.loadLowStaffingDepartments = function(depts) {
      const list = document.getElementById('lowStaffingList');
      if (!list) return; // Element might not be in DOM
      
      if (!depts || depts.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: #22c55e; text-align: center; padding: 1rem;">✓ All adequately staffed</p>';
        return;
      }
      list.innerHTML = depts.slice(0, 5).map(dept => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px;">
          <div style="font-weight: 600; color: var(--text-dark);">${dept.name}</div>
          <div style="color: var(--text-light); font-size: 11px;">Staff: ${dept.staff_count || 0}/${dept.target_count || '?'}</div>
        </div>
      `).join('');
    };

    window.loadRecentDepartments = function(depts) {
      const list = document.getElementById('recentDeptsList');
      if (!list) return; // Element might not be in DOM
      
      if (!depts || depts.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">None yet</p>';
        return;
      }
      list.innerHTML = depts.slice(0, 5).map(dept => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px;">
          <div style="font-weight: 600; color: var(--text-dark);">${dept.name}</div>
          <div style="color: var(--text-light); font-size: 11px;">Added ${dept.created_at}</div>
        </div>
      `).join('');
    };

    function attachEventListeners() {
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('keyup', () => window.loadDepartments());
      }

      const form = document.getElementById('departmentForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          const isEdit = this.dataset.id;
          const action = isEdit ? 'updateDepartment' : 'createDepartment';
          const data = {
            name: formData.get('name'),
            code: formData.get('code'),
            description: formData.get('description'),
            manager_id: formData.get('manager_id')
          };
          if (isEdit) data.id = isEdit;

          fetch(`modules/hr_core/api.php?action=${action}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
          })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              alert(isEdit ? 'Department updated' : 'Department created');
              window.closeDepartmentModal();
              window.loadDepartments();
            }
          })
          .catch(error => console.error('Error:', error));
        });
      }

      // Initial load
      window.loadDepartments();
    }

    setTimeout(attachEventListeners, 50);
  })();
</script>

<style>
  :root {
    --primary: #3b82f6;
    --primary-dark: #1e40af;
    --success: #22c55e;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --border: #e5e7eb;
    --bg-light: #f9fafb;
    --bg-lighter: #f3f4f6;
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --text-lighter: #9ca3af;
  }

  /* Main Layout */
  .departments-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .departments-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .departments-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .departments-right-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  /* KPI Cards */
  .department-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .dept-kpi-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: all 0.2s ease;
  }

  .dept-kpi-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-color: var(--primary);
  }

  .dept-kpi-card .kpi-icon {
    font-size: 32px;
    margin-bottom: 0.5rem;
  }

  .dept-kpi-card .kpi-label {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }

  .dept-kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
  }

  .dept-card-total .kpi-icon { color: var(--text-dark); }
  .dept-card-total .kpi-value { color: var(--text-dark); }

  .dept-card-active .kpi-icon { color: var(--success); }
  .dept-card-active .kpi-value { color: var(--success); }

  .dept-card-staff .kpi-icon { color: var(--primary); }
  .dept-card-staff .kpi-value { color: var(--primary); }

  .dept-card-warning .kpi-icon { color: var(--danger); }
  .dept-card-warning .kpi-value { color: var(--danger); }
  .dept-card-warning { border-color: var(--danger); background: #fef2f2; }

  /* Filters */
  .dept-filter-container {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
  }

  .dept-filter-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid var(--border);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    background: white;
    color: var(--text-dark);
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .filter-chip:hover {
    border-color: var(--primary);
    background: #f0f9ff;
  }

  .filter-chip.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
  }

  .filter-chip.active::before {
    content: '✓';
    font-weight: 700;
  }

  /* Search Bar */
  .dept-search-container {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
  }

  #searchInput {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 14px;
    background: white;
    transition: all 0.2s ease;
  }

  #searchInput:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .dept-search-reset {
    padding: 0.75rem 1.25rem;
    background: var(--bg-light);
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .dept-search-reset:hover {
    background: var(--bg-lighter);
    border-color: var(--text-light);
  }

  /* Table */
  .dept-table-container {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
  }

  .department-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  .department-table thead th {
    background: var(--bg-light);
    border-bottom: 2px solid var(--border);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .department-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }

  .department-table tbody tr {
    transition: all 0.2s ease;
  }

  .department-table tbody tr:hover {
    background: var(--bg-lighter);
  }

  .dept-row-no-manager {
    background: #fef2f2;
    border-left: 3px solid var(--danger);
  }

  .dept-row-no-manager:hover {
    background: #fee2e2;
  }

  /* Badge Styles */
  .dept-type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .dept-type-clinical {
    background: #dbeafe;
    color: #0c4a6e;
  }

  .dept-type-administrative {
    background: #e0e7ff;
    color: #3730a3;
  }

  .dept-type-support {
    background: #f3e8ff;
    color: #6b21a8;
  }

  .dept-warning-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 4px 10px;
    border-radius: 12px;
    background: #fee2e2;
    color: #991b1b;
    font-size: 12px;
    font-weight: 600;
  }

  .dept-status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #dcfce7;
    color: #166534;
  }

  /* Action Buttons */
  .dept-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    background: var(--bg-light);
    color: var(--text-dark);
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s ease;
  }

  .dept-action-btn:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .dept-action-edit:hover { background: #3b82f6; }
  .dept-action-view:hover { background: #06b6d4; }
  .dept-action-more:hover { background: #8b5cf6; }

  /* Side Panel */
  .dept-side-panel {
    position: sticky;
    top: 20px;
    height: fit-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .dept-side-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .dept-side-card.urgent {
    border-color: var(--warning);
    background: #fffbeb;
  }

  .dept-side-card.danger {
    border-color: var(--danger);
    background: #fef2f2;
  }

  .dept-side-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
  }

  .dept-side-card.urgent .dept-side-title { color: var(--warning); }
  .dept-side-card.danger .dept-side-title { color: var(--danger); }

  .dept-side-action {
    display: flex;
    gap: 0.5rem;
  }

  .dept-side-action .btn {
    flex: 1;
    padding: 0.5rem 0.75rem;
    font-size: 12px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
  }

  .btn-primary {
    background: var(--primary);
    color: white;
  }

  .btn-primary:hover {
    background: var(--primary-dark);
  }

  .btn-outline {
    background: white;
    color: var(--primary);
    border: 1px solid var(--primary);
  }

  .btn-outline:hover {
    background: #f0f9ff;
  }

  .dept-side-section {
    border-bottom: 1px solid var(--border);
    padding-bottom: 0.75rem;
  }

  .dept-side-section:last-child {
    border-bottom: none;
  }

  .dept-side-subtitle {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
  }

  .dept-side-item {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .dept-side-item:last-child {
    border-bottom: none;
  }

  .dept-side-item:hover {
    background: var(--bg-light);
  }

  .dept-side-item-name {
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
  }

  .dept-side-item-meta {
    color: var(--text-light);
    font-size: 11px;
  }

  /* Modal Styles */
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
  }

  .modal.active {
    display: flex;
  }

  .modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
  }

  .modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-light);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-close:hover {
    color: var(--text-dark);
  }

  .modal-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  .form-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
  }

  .form-input,
  .form-select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s ease;
  }

  .form-input:focus,
  .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  /* Responsive */
  @media (max-width: 1200px) {
    .departments-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .department-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .departments-layout {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .department-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .dept-filter-container {
      flex-wrap: wrap;
    }

    .filter-chip {
      font-size: 12px;
      padding: 0.4rem 0.8rem;
    }

    .dept-side-panel {
      position: static;
    }

    .department-table {
      font-size: 12px;
    }

    .department-table thead th,
    .department-table tbody td {
      padding: 0.75rem;
    }

    .dept-action-btn {
      width: 28px;
      height: 28px;
      font-size: 14px;
    }
  }

  @media (max-width: 480px) {
    .departments-container {
      padding: 0 0.75rem;
    }

    .department-kpi-grid {
      grid-template-columns: 1fr;
    }

    .dept-filter-container {
      gap: 0.5rem;
    }

    .filter-chip {
      font-size: 11px;
      padding: 0.35rem 0.7rem;
    }

    .department-table {
      font-size: 11px;
    }

    .department-table thead th,
    .department-table tbody td {
      padding: 0.5rem;
    }

    .dept-side-card {
      padding: 0.75rem;
    }

    .modal-content {
      width: 95%;
    }

    .modal-header,
    .modal-footer {
      padding: 1rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }
  }

  /* Empty State */
  .dept-empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-light);
  }

  .dept-empty-state-icon {
    font-size: 48px;
    margin-bottom: 1rem;
  }

  .dept-empty-state-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
  }

  .dept-empty-state-text {
    font-size: 14px;
    margin-bottom: 1.5rem;
  }
</style>
