<!-- Roles & Permissions Management View -->
<main class="main-content role-container">
  <div class="role-layout">
    <div class="role-left-column">
      <!-- KPI Cards -->
      <div class="role-kpi-grid">
        <div class="role-kpi-card role-card-total">
          <div class="kpi-icon">👤</div>
          <div class="kpi-label">Total Roles</div>
          <div class="kpi-value" id="totalRoleCount">0</div>
          <div class="kpi-subtext">Defined access levels</div>
        </div>
        <div class="role-kpi-card role-card-privilege">
          <div class="kpi-icon">⚠️</div>
          <div class="kpi-label">High Privilege Roles</div>
          <div class="kpi-value" id="highPrivilegeCount">0</div>
          <div class="kpi-subtext">Admin + System</div>
        </div>
        <div class="role-kpi-card role-card-inuse">
          <div class="kpi-icon">✓</div>
          <div class="kpi-label">Roles In Use</div>
          <div class="kpi-value" id="rolesInUseCount">0</div>
          <div class="kpi-subtext">Assigned to users</div>
        </div>
        <div class="role-kpi-card role-card-disabled">
          <div class="kpi-icon">🔒</div>
          <div class="kpi-label">Disabled Roles</div>
          <div class="kpi-value" id="disabledRoleCount">0</div>
          <div class="kpi-subtext">Inactive access levels</div>
        </div>
      </div>

      <!-- Search & Quick Filters -->
      <div class="role-filters-container">
        <div class="role-search-bar">
          <input type="text" id="searchInput" class="form-input" placeholder="Search roles...">
        </div>
        <div class="quick-filter-chips">
          <button class="filter-chip active" data-filter="all">
            <span>All Roles</span>
          </button>
          <button class="filter-chip" data-filter="high-privilege">
            <span>⚠️</span> <span>High Privilege</span>
          </button>
          <button class="filter-chip" data-filter="active">
            <span>✓</span> <span>Active</span>
          </button>
          <button class="filter-chip" data-filter="unused">
            <span>Unused</span>
          </button>
          <button class="filter-chip" onclick="window.resetRoleFilters()">Reset</button>
        </div>
      </div>

      <!-- Roles Table -->
      <div class="role-table-card">
        <div class="role-table-header">
          <h3 class="role-table-title">Access Control Roles</h3>
        </div>

        <div style="overflow-x: auto;">
          <table class="role-table">
            <thead>
              <tr>
                <th>Role Name</th>
                <th style="text-align: center;">Category</th>
                <th style="text-align: center;">Permissions</th>
                <th style="text-align: center;">User Count</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Risk</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody id="rolesList">
              <tr>
                <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
                  <div style="color: var(--text-light);">
                    <div style="font-size: 48px; margin-bottom: 1rem;">🔐</div>
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No roles defined</p>
                    <p style="font-size: 14px; margin-bottom: 1.5rem;">Create roles to control system access securely.</p>
                    <button class="btn btn-primary" onclick="window.openRoleModal()">+ Add Role</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="role-right-column">
      <div class="role-side-panel">
        <!-- Quick Add -->
        <div class="role-side-card">
          <div class="role-side-action">
            <button class="btn btn-primary" onclick="window.openRoleModal()" style="flex: 1;">+ Add Role</button>
          </div>
        </div>

        <!-- High Privilege Roles -->
        <div class="role-side-card alert">
          <div class="role-side-subtitle">⚠️ High Privilege Roles</div>
          <div id="highPrivilegeList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Roles Without Users -->
        <div class="role-side-card">
          <div class="role-side-subtitle">Roles Without Users</div>
          <div id="unusedRolesList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Recently Modified -->
        <div class="role-side-card">
          <div class="role-side-subtitle">Recently Modified</div>
          <div id="recentlyModifiedList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Role Categories Reference -->
        <div class="role-side-card">
          <div class="role-side-subtitle">📚 Role Categories</div>
          <div style="font-size: 11px; line-height: 1.8;">
            <div><span class="role-cat-badge role-cat-system">System</span> Admin roles</div>
            <div><span class="role-cat-badge role-cat-management">Management</span> HR/Department</div>
            <div><span class="role-cat-badge role-cat-operational">Operational</span> Payroll/Finance</div>
            <div><span class="role-cat-badge role-cat-enduser">End User</span> Employee/OJT</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</main>

<!-- Add/Edit Role Modal -->
<div id="roleModal" class="modal">
  <div class="modal-content" style="max-width: 800px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Role</h2>
      <button class="modal-close" onclick="window.closeRoleModal()">&times;</button>
    </div>

    <form id="roleForm">
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1.5rem;">
        <div class="form-group">
          <label class="form-label">Role Name *</label>
          <input type="text" name="role_name" class="form-input" required placeholder="e.g., HR Manager">
        </div>
        <div class="form-group">
          <label class="form-label">Role Category *</label>
          <select name="role_category" class="form-select" required>
            <option value="">Select category...</option>
            <option value="system">System (Admin roles)</option>
            <option value="management">Management (HR/Department)</option>
            <option value="operational">Operational (Payroll/Finance)</option>
            <option value="enduser">End User (Employee/OJT)</option>
          </select>
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-input" placeholder="Role purpose and responsibilities..."></textarea>
        </div>

        <!-- Permission Groups -->
        <div class="form-group" style="grid-column: 1 / -1; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
          <label class="form-label" style="margin-bottom: 1rem;">🔑 Permissions</label>
          
          <!-- Employee Management -->
          <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.75rem; font-size: 12px; text-transform: uppercase;">👥 Employee Management</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="employee_view"> View Employees
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="employee_create"> Create Employees
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="employee_edit"> Edit Employees
              </label>
              <label class="form-checkbox role-danger">
                <input type="checkbox" name="permission" value="employee_delete"> ⚠️ Delete Employees
              </label>
            </div>
          </div>

          <!-- Document Management -->
          <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.75rem; font-size: 12px; text-transform: uppercase;">📄 Document Management</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="document_view"> View Documents
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="document_create"> Upload Documents
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="document_edit"> Edit Documents
              </label>
              <label class="form-checkbox role-danger">
                <input type="checkbox" name="permission" value="document_delete"> ⚠️ Delete Documents
              </label>
            </div>
          </div>

          <!-- Movement Management -->
          <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.75rem; font-size: 12px; text-transform: uppercase;">📊 Movement & Approvals</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="movement_view"> View Movements
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="movement_create"> Create Movements
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="movement_approve"> Approve Movements
              </label>
              <label class="form-checkbox role-danger">
                <input type="checkbox" name="permission" value="movement_delete"> ⚠️ Delete Movements
              </label>
            </div>
          </div>

          <!-- Payroll & Finance -->
          <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.75rem; font-size: 12px; text-transform: uppercase;">💰 Payroll & Finance</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="payroll_view"> View Payroll
              </label>
              <label class="form-checkbox role-danger">
                <input type="checkbox" name="permission" value="payroll_process"> ⚠️ Process Payroll
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="finance_view"> View Finance Reports
              </label>
              <label class="form-checkbox role-danger">
                <input type="checkbox" name="permission" value="finance_edit"> ⚠️ Edit Finance Data
              </label>
            </div>
          </div>

          <!-- System & Administration -->
          <div style="margin-bottom: 0;">
            <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.75rem; font-size: 12px; text-transform: uppercase;">⚙️ System & Administration</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="reports_view"> View Reports
              </label>
              <label class="form-checkbox role-danger">
                <input type="checkbox" name="permission" value="roles_manage"> ⚠️ Manage Roles
              </label>
              <label class="form-checkbox">
                <input type="checkbox" name="permission" value="audit_view"> View Audit Logs
              </label>
              <label class="form-checkbox role-danger">
                <input type="checkbox" name="permission" value="user_delete"> ⚠️ Delete Users
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="window.closeRoleModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Role</button>
      </div>
    </form>
  </div>
</div>


<script>
  (function() {
    // System roles that cannot be edited or deleted
    const SYSTEM_ROLES = ['admin', 'super_admin', 'employee'];
    
    // High privilege permissions
    const HIGH_RISK_PERMS = ['employee_delete', 'document_delete', 'payroll_process', 'finance_edit', 'roles_manage', 'user_delete', 'movement_delete'];

    window.loadRoles = function() {
      const searchQuery = document.getElementById('searchInput')?.value || '';
      const activeFilters = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.filter)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        filter: activeFilters
      });

      fetch(`modules/hr_core/api.php?action=getRoles&${params}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data && data.data.roles) {
            window.displayRoles(data.data.roles);
            
            if (data.data.stats) {
              document.getElementById('totalRoleCount').textContent = data.data.stats.total || 0;
              document.getElementById('highPrivilegeCount').textContent = data.data.stats.high_privilege || 0;
              document.getElementById('rolesInUseCount').textContent = data.data.stats.in_use || 0;
              document.getElementById('disabledRoleCount').textContent = data.data.stats.disabled || 0;
            }

            window.loadHighPrivilegeRoles(data.data.high_privilege_roles || []);
            window.loadUnusedRoles(data.data.unused_roles || []);
            window.loadRecentlyModified(data.data.recently_modified || []);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.displayRoles = function(roles) {
      const tbody = document.getElementById('rolesList');
      tbody.innerHTML = '';

      if (!roles || roles.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">🔐</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No roles defined</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Create roles to control system access securely.</p>
                <button class="btn btn-primary" onclick="window.openRoleModal()">+ Add Role</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      roles.forEach(role => {
        // Role category badge
        const categoryMap = {
          'system': { label: 'System', class: 'role-cat-system' },
          'management': { label: 'Management', class: 'role-cat-management' },
          'operational': { label: 'Operational', class: 'role-cat-operational' },
          'enduser': { label: 'End User', class: 'role-cat-enduser' }
        };
        
        const catInfo = categoryMap[role.role_category] || { label: role.role_category, class: 'role-cat-other' };
        const categoryBadge = `<span class="role-cat-badge ${catInfo.class}">${catInfo.label}</span>`;

        // Permission details
        const permissions = role.permissions ? role.permissions.split(',') : [];
        const highRiskPerms = permissions.filter(p => HIGH_RISK_PERMS.includes(p)).length;
        const permissionDisplay = `${permissions.length} perms`;
        const hasHighRisk = highRiskPerms > 0 ? `<span style="color: var(--danger); margin-left: 0.25rem;">⚠️${highRiskPerms}</span>` : '';

        // User count with warning badge
        const userCount = role.user_count || 0;
        const userCountDisplay = userCount > 0 ? `<strong style="color: var(--warning);">${userCount}</strong>` : userCount;
        const userWarning = userCount > 5 ? '<span style="margin-left: 0.25rem; color: var(--danger);">⚠️</span>' : '';

        // Status badge
        const statusBadge = role.status === 'active' ? 'role-status-active' : 'role-status-inactive';
        const isSystemRole = SYSTEM_ROLES.includes(role.role_name?.toLowerCase());
        const isHighPrivilege = highRiskPerms > 0;

        // Risk level
        let riskDisplay = '—';
        if (isHighPrivilege && userCount > 0) {
          riskDisplay = '<span style="color: var(--danger); font-weight: bold;">🔴 Critical</span>';
        } else if (isHighPrivilege) {
          riskDisplay = '<span style="color: var(--warning); font-weight: bold;">🟠 High</span>';
        } else if (userCount > 0) {
          riskDisplay = '<span style="color: #22c55e;">🟢 Normal</span>';
        }

        // Action buttons with guards
        let actionButtons = '';
        
        if (isSystemRole) {
          // System roles are locked
          actionButtons = `
            <button class="role-action-btn role-action-view" onclick="window.viewRole(${role.role_id})" title="View (locked)">👁</button>
            <button class="role-action-btn role-action-lock" title="System role - Edit disabled">🔒</button>
          `;
        } else {
          // Custom roles
          actionButtons = `
            <button class="role-action-btn role-action-edit" onclick="window.editRole(${role.role_id})" title="Edit">✏</button>
            <button class="role-action-btn role-action-duplicate" onclick="window.duplicateRole(${role.role_id})" title="Duplicate">📋</button>
          `;
          
          // Delete guard - disable if users assigned
          if (userCount === 0) {
            actionButtons += `
              <button class="role-action-btn role-action-delete" onclick="window.deleteRole(${role.role_id})" title="Delete">🗑</button>
            `;
          } else {
            actionButtons += `
              <button class="role-action-btn role-action-deactivate" onclick="window.deactivateRole(${role.role_id})" title="Deactivate instead of delete">🔒</button>
            `;
          }
        }

        const row = `
          <tr ${role.status !== 'active' ? 'style="opacity: 0.6;"' : ''}>
            <td style="font-weight: 600; color: var(--text-dark);">${role.role_name || '-'}</td>
            <td style="text-align: center;">${categoryBadge}</td>
            <td style="text-align: center; font-size: 12px;">
              <span>${permissionDisplay}</span>${hasHighRisk}
              <div style="font-size: 10px; margin-top: 0.25rem;">
                <button class="role-view-perms-link" onclick="window.viewRolePermissions(${role.role_id})">view</button>
              </div>
            </td>
            <td style="text-align: center;">
              ${userCountDisplay}${userWarning}
            </td>
            <td style="text-align: center;">
              <span class="role-status-badge ${statusBadge}">
                ${role.status === 'active' ? '✓ Active' : '✕ Inactive'}
              </span>
            </td>
            <td style="text-align: center;">
              ${riskDisplay}
            </td>
            <td style="text-align: center;">
              <div style="display: flex; gap: 0.25rem; justify-content: center;">
                ${actionButtons}
              </div>
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    };

    window.openRoleModal = function() {
      document.getElementById('roleForm').reset();
      delete document.getElementById('roleForm').dataset.id;
      document.querySelector('#roleModal .modal-title').textContent = 'Add Role';
      document.getElementById('roleModal').classList.add('active');
    };

    window.closeRoleModal = function() {
      document.getElementById('roleModal').classList.remove('active');
    };

    window.viewRole = function(id) {
      fetch(`modules/hr_core/api.php?action=getRoleById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const role = data.data;
            const perms = role.permissions ? role.permissions.split(',') : [];
            alert(`Role: ${role.role_name}\nCategory: ${role.role_category}\nUsers: ${role.user_count}\nPermissions: ${perms.length}\n\n${role.description || 'No description'}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.viewRolePermissions = function(id) {
      fetch(`modules/hr_core/api.php?action=getRoleById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const role = data.data;
            const perms = role.permissions ? role.permissions.split(',') : [];
            const permNames = perms.map(p => '✓ ' + p.replace(/_/g, ' ').toUpperCase()).join('\n');
            alert(`${role.role_name}\n\nPermissions (${perms.length}):\n\n${permNames}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.editRole = function(id) {
      fetch(`modules/hr_core/api.php?action=getRoleById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const role = data.data;
            const form = document.getElementById('roleForm');
            
            form.querySelector('input[name="role_name"]').value = role.role_name || '';
            form.querySelector('select[name="role_category"]').value = role.role_category || '';
            form.querySelector('textarea[name="description"]').value = role.description || '';
            
            // Clear all permissions first
            document.querySelectorAll('input[name="permission"]').forEach(cb => {
              cb.checked = false;
            });
            
            // Check selected permissions
            if (role.permissions) {
              const perms = role.permissions.split(',');
              document.querySelectorAll('input[name="permission"]').forEach(cb => {
                if (perms.includes(cb.value)) {
                  cb.checked = true;
                }
              });
            }
            
            form.dataset.id = id;
            document.querySelector('#roleModal .modal-title').textContent = 'Edit Role';
            window.openRoleModal();
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.duplicateRole = function(id) {
      fetch(`modules/hr_core/api.php?action=getRoleById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const role = data.data;
            const form = document.getElementById('roleForm');
            
            form.querySelector('input[name="role_name"]').value = `${role.role_name} (Copy)`;
            form.querySelector('select[name="role_category"]').value = role.role_category || '';
            form.querySelector('textarea[name="description"]').value = role.description || '';
            
            // Clear all permissions first
            document.querySelectorAll('input[name="permission"]').forEach(cb => {
              cb.checked = false;
            });
            
            // Check same permissions
            if (role.permissions) {
              const perms = role.permissions.split(',');
              document.querySelectorAll('input[name="permission"]').forEach(cb => {
                if (perms.includes(cb.value)) {
                  cb.checked = true;
                }
              });
            }
            
            delete form.dataset.id; // Create new, not edit
            document.querySelector('#roleModal .modal-title').textContent = 'Duplicate Role';
            window.openRoleModal();
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.deleteRole = function(id) {
      if (confirm('Delete this role? This action cannot be undone.')) {
        fetch(`modules/hr_core/api.php?action=deleteRole&id=${id}`, {
          method: 'POST'
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            alert('Role deleted');
            window.loadRoles();
          } else {
            alert('Error: ' + (result.message || 'Could not delete role'));
          }
        })
        .catch(error => console.error('Error:', error));
      }
    };

    window.deactivateRole = function(id) {
      if (confirm('Deactivate this role? Users with this role will lose access.')) {
        fetch(`modules/hr_core/api.php?action=updateRole`, {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({id: id, status: 'inactive'})
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            alert('Role deactivated');
            window.loadRoles();
          }
        })
        .catch(error => console.error('Error:', error));
      }
    };

    window.toggleRoleFilter = function(filter) {
      const chip = document.querySelector(`.filter-chip[data-filter="${filter}"]`);
      if (chip) {
        chip.classList.toggle('active');
        window.loadRoles();
      }
    };

    window.resetRoleFilters = function() {
      document.getElementById('searchInput').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      document.querySelector('.filter-chip[data-filter="all"]').classList.add('active');
      window.loadRoles();
    };

    window.loadHighPrivilegeRoles = function(roles) {
      const list = document.getElementById('highPrivilegeList');
      
      if (!roles || roles.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-lighter); margin: 0;">None configured</p>';
        return;
      }

      list.innerHTML = roles.map(role => `
        <div style="padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
          <div style="font-weight: 600; color: var(--text-dark);">${role.role_name}</div>
          <div style="font-size: 11px; color: var(--text-light);">${role.user_count} users assigned</div>
        </div>
      `).join('');
    };

    window.loadUnusedRoles = function(roles) {
      const list = document.getElementById('unusedRolesList');
      
      if (!roles || roles.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-lighter); margin: 0;">All roles in use</p>';
        return;
      }

      list.innerHTML = roles.map(role => `
        <div style="padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
          <div style="font-weight: 600; color: var(--text-dark);">${role.role_name}</div>
          <div style="font-size: 11px; color: var(--text-light);">${role.role_category}</div>
        </div>
      `).join('');
    };

    window.loadRecentlyModified = function(roles) {
      const list = document.getElementById('recentlyModifiedList');
      
      if (!roles || roles.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-lighter); margin: 0;">No recent changes</p>';
        return;
      }

      list.innerHTML = roles.map(role => `
        <div style="padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
          <div style="font-weight: 600; color: var(--text-dark);">${role.role_name}</div>
          <div style="font-size: 11px; color: var(--text-light);">${role.updated_at || 'Recently updated'}</div>
        </div>
      `).join('');
    };

    function attachEventListeners() {
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('input', () => window.loadRoles());
      }

      document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', (e) => {
          if (chip.textContent.includes('Reset')) return;
          e.preventDefault();
          const filter = chip.dataset.filter;
          if (filter === 'all') {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
          } else {
            chip.classList.toggle('active');
          }
          window.loadRoles();
        });
      });

      const modal = document.getElementById('roleModal');
      if (modal) {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) window.closeRoleModal();
        });
      }

      const form = document.getElementById('roleForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const id = this.dataset.id || null;
          const formData = new FormData(this);
          const action = id ? 'updateRole' : 'createRole';
          
          const permissions = [];
          document.querySelectorAll('input[name="permission"]:checked').forEach(cb => {
            permissions.push(cb.value);
          });
          
          const data = {
            role_name: formData.get('role_name'),
            role_category: formData.get('role_category'),
            description: formData.get('description'),
            permissions: permissions.join(',')
          };
          if (id) data.id = id;

          fetch(`modules/hr_core/api.php?action=${action}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
          })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              alert(result.message || (id ? 'Role updated' : 'Role created'));
              window.closeRoleModal();
              window.loadRoles();
            } else {
              alert('Error: ' + (result.message || 'Unknown error'));
            }
          })
          .catch(error => console.error('Error:', error));
        });
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      window.loadRoles();
      attachEventListeners();
    });

    const originalLoadRoles = window.loadRoles;
    window.loadRoles = function() {
      originalLoadRoles();
      setTimeout(() => attachEventListeners(), 50);
    };
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
  .role-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .role-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .role-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .role-right-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  /* KPI Cards */
  .role-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .role-kpi-card {
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

  .role-kpi-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-color: var(--primary);
  }

  .role-kpi-card .kpi-icon {
    font-size: 32px;
    margin-bottom: 0.5rem;
  }

  .role-kpi-card .kpi-label {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }

  .role-kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
  }

  .role-kpi-card .kpi-subtext {
    font-size: 11px;
    color: var(--text-lighter);
    margin-top: 0.5rem;
  }

  .role-card-total .kpi-icon { color: var(--primary); }
  .role-card-total .kpi-value { color: var(--primary); }

  .role-card-privilege .kpi-icon { color: var(--danger); }
  .role-card-privilege .kpi-value { color: var(--danger); }

  .role-card-inuse .kpi-icon { color: #22c55e; }
  .role-card-inuse .kpi-value { color: #22c55e; }

  .role-card-disabled .kpi-icon { color: var(--text-light); }
  .role-card-disabled .kpi-value { color: var(--text-light); }

  /* Filters */
  .role-filters-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .role-search-bar {
    display: flex;
    gap: 0.75rem;
  }

  .role-search-bar input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 14px;
    background: white;
    transition: all 0.2s ease;
  }

  .role-search-bar input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .quick-filter-chips {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
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

  /* Table */
  .role-table-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
  }

  .role-table-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
  }

  .role-table-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: var(--text-dark);
  }

  .role-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .role-table thead th {
    background: var(--bg-light);
    border-bottom: 2px solid var(--border);
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    color: var(--text-dark);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .role-table tbody td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }

  .role-table tbody tr {
    transition: all 0.2s ease;
  }

  .role-table tbody tr:hover {
    background: var(--bg-lighter);
  }

  /* Role Category Badges */
  .role-cat-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
  }

  .role-cat-system {
    background: #fee2e2;
    color: #991b1b;
  }

  .role-cat-management {
    background: #dbeafe;
    color: #1e40af;
  }

  .role-cat-operational {
    background: #fef3c7;
    color: #92400e;
  }

  .role-cat-enduser {
    background: #dcfce7;
    color: #166534;
  }

  .role-cat-other {
    background: #f3f4f6;
    color: #1f2937;
  }

  /* Status Badges */
  .role-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
  }

  .role-status-active {
    background: #dcfce7;
    color: #166534;
  }

  .role-status-inactive {
    background: #f3f4f6;
    color: #9ca3af;
  }

  /* View Permissions Link */
  .role-view-perms-link {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    font-size: 10px;
    text-decoration: underline;
    padding: 0;
  }

  .role-view-perms-link:hover {
    color: var(--primary-dark);
  }

  /* Action Buttons */
  .role-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 6px;
    background: var(--bg-light);
    color: var(--text-dark);
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s ease;
  }

  .role-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .role-action-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .role-action-edit:hover { background: var(--primary); color: white; }
  .role-action-view:hover { background: #06b6d4; color: white; }
  .role-action-delete:hover { background: var(--danger); color: white; }
  .role-action-deactivate:hover { background: var(--warning); color: white; }
  .role-action-duplicate:hover { background: #8b5cf6; color: white; }
  .role-action-lock { opacity: 0.5; cursor: not-allowed; }

  /* Side Panel */
  .role-side-panel {
    position: sticky;
    top: 20px;
    height: fit-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .role-side-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .role-side-card.alert {
    border-color: var(--danger);
    background: #fef2f2;
  }

  .role-side-subtitle {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
  }

  .role-side-action {
    display: flex;
    gap: 0.5rem;
  }

  .role-side-action .btn {
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

  /* Form Elements */
  .form-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 13px;
    cursor: pointer;
  }

  .form-checkbox input {
    cursor: pointer;
    width: 16px;
    height: 16px;
    accent-color: var(--primary);
  }

  .form-checkbox.role-danger {
    color: var(--danger);
  }

  .form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 0.4rem;
  }

  .form-input,
  .form-select {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 13px;
    font-family: inherit;
    transition: all 0.2s ease;
  }

  .form-input:focus,
  .form-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  textarea.form-input {
    resize: vertical;
    min-height: 70px;
  }

  /* Modal */
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
    overflow-y: auto;
  }

  .modal.active {
    display: flex;
  }

  .modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    margin: auto;
  }

  .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
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
    position: sticky;
    bottom: 0;
    background: white;
  }

  .form-group {
    margin-bottom: 1rem;
  }

  .role-danger {
    color: var(--danger);
  }

  /* Responsive */
  @media (max-width: 1200px) {
    .role-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .role-kpi-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (max-width: 768px) {
    .role-layout {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .role-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .quick-filter-chips {
      flex-wrap: wrap;
    }

    .filter-chip {
      font-size: 12px;
      padding: 0.4rem 0.8rem;
    }

    .role-side-panel {
      position: static;
    }

    .role-table {
      font-size: 11px;
    }

    .role-table thead th,
    .role-table tbody td {
      padding: 0.5rem;
    }

    .role-action-btn {
      width: 24px;
      height: 24px;
      font-size: 12px;
    }

    .role-table-header {
      padding: 0.75rem;
    }

    .role-table-title {
      font-size: 14px;
    }
  }

  @media (max-width: 480px) {
    .role-container {
      padding: 0 0.75rem;
    }

    .role-kpi-grid {
      grid-template-columns: 1fr;
    }

    .quick-filter-chips {
      gap: 0.5rem;
    }

    .filter-chip {
      font-size: 11px;
      padding: 0.35rem 0.7rem;
    }

    .role-table {
      font-size: 10px;
    }

    .role-table thead th,
    .role-table tbody td {
      padding: 0.4rem;
    }

    .role-side-card {
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
      margin-bottom: 0.75rem;
    }

    .role-kpi-card {
      padding: 1rem;
    }

    .role-kpi-card .kpi-icon {
      font-size: 24px;
    }

    .role-kpi-card .kpi-value {
      font-size: 20px;
    }
  }
</style>
</div>
</main>
