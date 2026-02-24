<!-- Work Shifts Management View -->
<main class="main-content shift-container">
  <div class="shift-layout">
    <div class="shift-left-column">
      <!-- KPI Cards -->
      <div class="shift-kpi-grid">
        <div class="shift-kpi-card shift-card-total">
          <div class="kpi-icon">⏰</div>
          <div class="kpi-label">Total Shifts</div>
          <div class="kpi-value" id="totalShiftCount">0</div>
          <div class="kpi-subtext">Shift definitions</div>
        </div>
        <div class="shift-kpi-card shift-card-night">
          <div class="kpi-icon">🌙</div>
          <div class="kpi-label">Night Shifts</div>
          <div class="kpi-value" id="nightShiftCount">0</div>
          <div class="kpi-subtext">Requires differential</div>
        </div>
        <div class="shift-kpi-card shift-card-oncall">
          <div class="kpi-icon">📞</div>
          <div class="kpi-label">On Call</div>
          <div class="kpi-value" id="onCallCount">0</div>
          <div class="kpi-subtext">As-needed coverage</div>
        </div>
        <div class="shift-kpi-card shift-card-overtime">
          <div class="kpi-icon">⏱️</div>
          <div class="kpi-label">Overtime Eligible</div>
          <div class="kpi-value" id="overtimeCount">0</div>
          <div class="kpi-subtext">Premium pay shifts</div>
        </div>
      </div>

      <!-- Search & Quick Filters -->
      <div class="shift-filters-container">
        <div class="shift-search-bar">
          <input type="text" id="searchInput" class="form-input" placeholder="Search shifts...">
        </div>
        <div class="quick-filter-chips">
          <button class="filter-chip active" data-filter="all">
            <span>All Shifts</span>
          </button>
          <button class="filter-chip" data-filter="night">
            <span>🌙</span> <span>Night</span>
          </button>
          <button class="filter-chip" data-filter="active">
            <span>✓</span> <span>Active</span>
          </button>
          <button class="filter-chip" data-filter="understaffed">
            <span>⚠️</span> <span>Understaffed</span>
          </button>
          <button class="filter-chip" id="resetFilter">Reset</button>
        </div>
      </div>

      <!-- Shifts Table -->
      <div class="shift-table-card">
        <div class="shift-table-header">
          <h3 class="shift-table-title">Scheduling & Payroll Rules</h3>
        </div>

        <div style="overflow-x: auto;">
          <table class="shift-table">
            <thead>
              <tr>
                <th>Shift Name</th>
                <th style="text-align: center;">Type</th>
                <th style="text-align: center;">Time</th>
                <th style="text-align: center;">Duration</th>
                <th style="text-align: center;">Break</th>
                <th style="text-align: center;">Night Diff</th>
                <th style="text-align: center;">Overtime</th>
                <th style="text-align: center;">Coverage</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody id="shiftsList">
              <tr>
                <td colspan="10" style="text-align: center; padding: 3rem 2rem;">
                  <div style="color: var(--text-light);">
                    <div style="font-size: 48px; margin-bottom: 1rem;">⏰</div>
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No shifts defined</p>
                    <p style="font-size: 14px; margin-bottom: 1.5rem;">Add shifts to support scheduling and payroll.</p>
                    <button class="btn btn-primary" onclick="window.openShiftModal()">+ Add Shift</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="shift-right-column">
      <div class="shift-side-panel">
        <!-- Quick Add -->
        <div class="shift-side-card">
          <div class="shift-side-action">
            <button class="btn btn-primary" onclick="window.openShiftModal()" style="flex: 1;">+ Add Shift</button>
          </div>
        </div>

        <!-- Night Shifts -->
        <div class="shift-side-card alert">
          <div class="shift-side-subtitle">🌙 Night Shifts</div>
          <div id="nightShiftsList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Shifts Without Assignments -->
        <div class="shift-side-card">
          <div class="shift-side-subtitle">Unassigned Shifts</div>
          <div id="unassignedShiftsList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Recently Modified -->
        <div class="shift-side-card">
          <div class="shift-side-subtitle">Recently Modified</div>
          <div id="recentlyModifiedList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Shift Types Reference -->
        <div class="shift-side-card">
          <div class="shift-side-subtitle">📚 Shift Types</div>
          <div style="font-size: 11px; line-height: 1.8;">
            <div><span class="shift-type-badge shift-type-morning">Morning</span></div>
            <div><span class="shift-type-badge shift-type-afternoon">Afternoon</span></div>
            <div><span class="shift-type-badge shift-type-night">Night</span></div>
            <div><span class="shift-type-badge shift-type-rotating">Rotating</span></div>
            <div><span class="shift-type-badge shift-type-oncall">On Call</span></div>
            <div><span class="shift-type-badge shift-type-ojt">OJT Shift</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</main>

<!-- Add/Edit Shift Modal -->
<div id="shiftModal" class="modal">
  <div class="modal-content" style="max-width: 750px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Shift</h2>
      <button class="modal-close" onclick="window.closeShiftModal()">&times;</button>
    </div>

    <form id="shiftForm">
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1.5rem;">
        <div class="form-group">
          <label class="form-label">Shift Name *</label>
          <input type="text" name="shift_name" class="form-input" required placeholder="e.g., Morning Shift">
        </div>
        <div class="form-group">
          <label class="form-label">Shift Type *</label>
          <select name="shift_type" id="shiftTypeSelect" class="form-select" required onchange="window.updateShiftDefaults(this.value)">
            <option value="">Select type...</option>
            <option value="Regular">Regular</option>
            <option value="Weekend">Weekend</option>
            <option value="Special">Special</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Start Time *</label>
          <input type="time" name="start_time" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">End Time *</label>
          <input type="time" name="end_time" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Break Duration (minutes) *</label>
          <input type="number" name="break_duration" class="form-input" required placeholder="e.g., 60" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Required Staff Count</label>
          <input type="number" name="required_count" class="form-input" placeholder="e.g., 5" min="0">
        </div>
        
        <!-- Payroll Rules -->
        <div class="form-group" style="grid-column: 1 / -1; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
          <label class="form-label" style="margin-bottom: 0.75rem;">💰 Payroll Rules</label>
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <label class="form-checkbox">
              <input type="checkbox" name="night_differential_eligible" value="1"> Night Differential
            </label>
            <label class="form-checkbox">
              <input type="checkbox" name="overtime_eligible" value="1"> Overtime Eligible
            </label>
            <label class="form-checkbox">
              <input type="checkbox" name="is_night_shift" value="1" style="color: #ef4444;"> Night Shift (Late)
            </label>
          </div>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Notes</label>
          <textarea name="description" class="form-input" placeholder="Shift details, restrictions, or notes..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="window.closeShiftModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Shift</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function() {
  // Shift Type Presets with defaults - mapped to database shift types
  window.SHIFT_PRESETS = {
    'Regular': { start: '06:00', end: '14:00', break: 60, night_differential: 0, overtime_eligible: 1, is_night: 0, label: 'Regular' },
    'Weekend': { start: '08:00', end: '16:00', break: 60, night_differential: 0, overtime_eligible: 1, is_night: 0, label: 'Weekend' },
    'Special': { start: '08:00', end: '20:00', break: 60, night_differential: 1, overtime_eligible: 1, is_night: 1, label: 'Special' }
  };

  // Load shifts data and populate KPIs
  window.loadShifts = async function() {
    try {
      const response = await fetch('modules/hr_core/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_shifts_stats' })
      });
      const result = await response.json();
      if (result.success) {
        window.shiftsData = result.data || [];
        window.shiftsStats = result.stats || {};
        window.displayShifts();
        setTimeout(window.attachEventListeners, 50);
        setTimeout(window.loadSidePanels, 75);
      }
    } catch (error) {
      console.error('Error loading shifts:', error);
    }
  };

  // Display shifts in table with type badges, coverage signals, payroll flags
  window.displayShifts = function() {
    const tbody = document.getElementById('shiftsList');
    const emptyState = document.querySelector('.empty-state-container');
    
    if (!window.shiftsData || window.shiftsData.length === 0) {
      if (!tbody) return;
      tbody.innerHTML = '';
      if (emptyState) {
        emptyState.style.display = 'flex';
      }
      const totalCount = document.getElementById('totalShiftCount');
      const nightCount = document.getElementById('nightShiftCount');
      const oncallCount = document.getElementById('onCallCount');
      const overtimeCount = document.getElementById('overtimeCount');
      
      if (totalCount) totalCount.textContent = '0';
      if (nightCount) nightCount.textContent = '0';
      if (oncallCount) oncallCount.textContent = '0';
      if (overtimeCount) overtimeCount.textContent = '0';
      return;
    }
    
    if (emptyState) {
      emptyState.style.display = 'none';
    }

    // Update KPI counts
    document.getElementById('totalShiftCount').textContent = window.shiftsStats.total || 0;
    document.getElementById('nightShiftCount').textContent = window.shiftsStats.night_shifts || 0;
    document.getElementById('onCallCount').textContent = window.shiftsStats.oncall_shifts || 0;
    document.getElementById('overtimeCount').textContent = window.shiftsStats.overtime_eligible || 0;

    if (!tbody) return;

    tbody.innerHTML = window.shiftsData.map((shift, idx) => {
      const typeClass = 'shift-type-' + (shift.shift_type || 'custom').toLowerCase();
      const typeLabel = shift.shift_type ? shift.shift_type.charAt(0).toUpperCase() + shift.shift_type.slice(1) : 'Custom';
      
      // Calculate duration
      const [startH, startM] = (shift.start_time || '00:00').split(':').map(Number);
      const [endH, endM] = (shift.end_time || '00:00').split(':').map(Number);
      let durationHours = endH - startH;
      let isOvernight = false;
      if (durationHours < 0) {
        durationHours += 24;
        isOvernight = true;
      }
      
      // Overnight indicator
      const timeDisplay = `${shift.start_time}${isOvernight ? ' → ' + shift.end_time + ' (Next)' : ' - ' + shift.end_time}`;
      
      // Coverage signal
      const assigned = shift.assigned_count || 0;
      const required = shift.required_count || 0;
      const coverageClass = assigned < required ? 'coverage-warning' : '';
      const coverageBadge = assigned < required ? ' ⚠️' : '';
      
      // Night shift row highlighting
      const nightClass = shift.is_night_shift ? 'shift-row-night' : '';
      
      return `
        <tr class="shift-row ${nightClass}" data-shift-id="${shift.shift_id}">
          <td><strong>${shift.shift_name}</strong></td>
          <td style="text-align: center;">
            <span class="shift-type-badge ${typeClass}">${typeLabel}</span>
          </td>
          <td style="text-align: center; font-size: 0.9rem;">
            <span>${timeDisplay}</span>
          </td>
          <td style="text-align: center;">${durationHours}h</td>
          <td style="text-align: center;">${shift.break_duration || 0}m</td>
          <td style="text-align: center;">
            <span class="payroll-badge ${shift.night_differential_eligible ? 'badge-yes' : 'badge-no'}">
              ${shift.night_differential_eligible ? '✓ Yes' : '✕ No'}
            </span>
          </td>
          <td style="text-align: center;">
            <span class="payroll-badge ${shift.overtime_eligible ? 'badge-yes' : 'badge-no'}">
              ${shift.overtime_eligible ? '✓ Yes' : '✕ No'}
            </span>
          </td>
          <td style="text-align: center;" class="${coverageClass}">
            <span class="coverage-signal">${assigned}/${required}${coverageBadge}</span>
          </td>
          <td style="text-align: center;">
            <span class="status-badge status-${shift.status || 'active'}">${(shift.status || 'Active').charAt(0).toUpperCase() + (shift.status || 'Active').slice(1)}</span>
          </td>
          <td style="text-align: center; white-space: nowrap;">
            <div style="position: relative; display: inline-block;">
              <button class="action-menu-btn" onclick="window.toggleActionMenu('shift-${shift.shift_id}')" title="Actions">⋮</button>
              <div class="action-menu" id="shift-${shift.shift_id}" style="display: none;">
                <button class="action-menu-item" onclick="window.viewShift(${shift.shift_id})">👁 View</button>
                <button class="action-menu-item" onclick="window.editShift(${shift.shift_id})">✏️ Edit</button>
                <button class="action-menu-item action-menu-danger" ${assigned > 0 ? 'disabled' : ''} onclick="window.deleteShift(${shift.shift_id})">🗑 Delete</button>
              </div>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  };

  // Update form defaults based on shift type
  window.updateShiftDefaults = function(shiftType) {
    if (!shiftType || !window.SHIFT_PRESETS[shiftType]) return;
    
    const preset = window.SHIFT_PRESETS[shiftType];
    const form = document.getElementById('shiftForm');
    
    form.start_time.value = preset.start;
    form.end_time.value = preset.end;
    form.break_duration.value = preset.break;
    form.night_differential_eligible.checked = preset.night_differential === 1;
    form.overtime_eligible.checked = preset.overtime_eligible === 1;
    form.is_night_shift.checked = preset.is_night === 1;
    
    // Enable all fields for database-driven types
    form.night_differential_eligible.disabled = false;
    form.overtime_eligible.disabled = false;
  };

  // Open modal for add/edit
  window.openShiftModal = function(shiftId = null) {
    const modal = document.getElementById('shiftModal');
    const form = document.getElementById('shiftForm');
    const title = document.querySelector('.modal-title');
    
    form.reset();
    form.shift_type.value = '';
    
    if (shiftId) {
      title.textContent = 'Edit Shift';
      const shift = window.shiftsData.find(s => s.id === shiftId);
      if (shift) {
        form.shift_name.value = shift.shift_name;
        form.shift_type.value = shift.shift_type || '';
        form.start_time.value = shift.start_time;
        form.end_time.value = shift.end_time;
        form.break_duration.value = shift.break_duration || 0;
        form.required_count.value = shift.required_count || 0;
        form.night_differential_eligible.checked = shift.night_differential_eligible === 1;
        form.overtime_eligible.checked = shift.overtime_eligible === 1;
        form.is_night_shift.checked = shift.is_night_shift === 1;
        form.description.value = shift.description || '';
      }
    } else {
      title.textContent = 'Add Shift';
    }
    
    modal.classList.add('active');
  };

  window.closeShiftModal = function() {
    document.getElementById('shiftModal').classList.remove('active');
  };

  // Delete shift with action guard
  window.deleteShift = async function(shiftId) {
    const shift = window.shiftsData.find(s => s.id === shiftId);
    if (!shift) return;
    
    if (shift.assigned_count > 0) {
      alert('Cannot delete: ' + shift.assigned_count + ' employees assigned to this shift. Deactivate instead.');
      return;
    }
    
    if (!confirm('Delete "' + shift.shift_name + '"?')) return;
    
    try {
      const response = await fetch('modules/hr_core/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_shift', shift_id: shiftId })
      });
      const result = await response.json();
      if (result.success) {
        window.loadShifts();
      }
    } catch (error) {
      console.error('Error deleting shift:', error);
    }
  };

  // Edit shift
  window.editShift = function(shiftId) {
    window.openShiftModal(shiftId);
  };

  // Load side panels
  window.loadSidePanels = async function() {
    try {
      const response = await fetch('modules/hr_core/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_shift_panels' })
      });
      const result = await response.json();
      if (result.success) {
        window.loadNightShifts(result.night_shifts || []);
        window.loadUnassignedShifts(result.unassigned_shifts || []);
        window.loadRecentlyModified(result.recently_modified || []);
      }
    } catch (error) {
      console.error('Error loading side panels:', error);
    }
  };

  // Populate night shifts panel
  window.loadNightShifts = function(nightShifts) {
    const container = document.getElementById('nightShiftsList');
    if (!container) return; // Element might not be in DOM
    
    if (!nightShifts || nightShifts.length === 0) {
      container.innerHTML = '<div style="color: #6b7280; font-size: 0.9rem;">No night shifts defined</div>';
      return;
    }
    container.innerHTML = nightShifts.map(shift => `
      <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.9rem;">
        <strong>${shift.shift_name}</strong><br>
        <span style="color: #6b7280;">${shift.start_time} - ${shift.end_time}</span><br>
        <span style="color: #ef4444;">Requires Differential</span>
      </div>
    `).join('');
  };

  // Populate unassigned shifts panel
  window.loadUnassignedShifts = function(unassignedShifts) {
    const container = document.getElementById('unassignedShiftsList');
    if (!container) return; // Element might not be in DOM
    
    if (!unassignedShifts || unassignedShifts.length === 0) {
      container.innerHTML = '<div style="color: #6b7280; font-size: 0.9rem;">All shifts assigned</div>';
      return;
    }
    container.innerHTML = unassignedShifts.map(shift => `
      <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.9rem;">
        <strong>${shift.shift_name}</strong><br>
        <span style="color: #f59e0b;">⚠️ No assignments</span>
      </div>
    `).join('');
  };

  // Populate recently modified panel
  window.loadRecentlyModified = function(recentlyModified) {
    const container = document.getElementById('recentlyModifiedList');
    if (!container) return; // Element might not be in DOM
    
    if (!recentlyModified || recentlyModified.length === 0) {
      container.innerHTML = '<div style="color: #6b7280; font-size: 0.9rem;">No recent changes</div>';
      return;
    }
    container.innerHTML = recentlyModified.map(shift => `
      <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.85rem;">
        <strong>${shift.shift_name}</strong><br>
        <span style="color: #6b7280;">Modified ${shift.modified_date || 'recently'}</span>
      </div>
    `).join('');
  };

  // Form submission - attach listeners when module loads
  function attachShiftEventListeners() {
    const form = document.getElementById('shiftForm');
    if (form) {
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const shiftId = document.querySelector('[data-shift-id]')?.dataset?.shiftId;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = shiftId ? 'update_shift' : 'create_shift';
        if (shiftId) data.shift_id = shiftId;
        
        try {
          const response = await fetch('modules/hr_core/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
          });
          const result = await response.json();
          if (result.success) {
            window.closeShiftModal();
            window.loadShifts();
          }
        } catch (error) {
          console.error('Error saving shift:', error);
        }
      });
    }

    // Modal backdrop close
    const modal = document.getElementById('shiftModal');
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) window.closeShiftModal();
      });
    }

    // Quick filter chips
    document.querySelectorAll('[data-filter]').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        window.applyFilter(this.dataset.filter);
      });
    });

    // Reset filter
    document.getElementById('resetFilter')?.addEventListener('click', function() {
      document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
      document.querySelector('[data-filter="all"]').classList.add('active');
      window.displayShifts();
    });

    // Add shift button
    document.getElementById('addShiftBtn')?.addEventListener('click', function() {
      window.openShiftModal();
    });

    // Load data on page load
    window.loadShifts();
  }

  // Call event listeners when module loads
  setTimeout(attachShiftEventListeners, 50);

  // Apply filter
  window.applyFilter = function(filter) {
    const tbody = document.getElementById('shiftsList');
    if (!tbody) return; // Element might not exist
    
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
      row.style.display = 'table-row';
      
      if (filter === 'night') {
        const hasNightBadge = row.querySelector('.shift-type-night');
        row.style.display = hasNightBadge ? 'table-row' : 'none';
      } else if (filter === 'active') {
        const status = row.querySelector('.status-badge');
        row.style.display = status?.textContent.toLowerCase().includes('active') ? 'table-row' : 'none';
      } else if (filter === 'understaffed') {
        const coverageClass = row.querySelector('.coverage-signal')?.parentElement.classList;
        row.style.display = coverageClass?.contains('coverage-warning') ? 'table-row' : 'none';
      }
    });
  };

  window.attachEventListeners = function() {
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
      if (!event.target.closest('.action-menu-btn') && !event.target.closest('.action-menu')) {
        document.querySelectorAll('.action-menu').forEach(menu => {
          menu.style.display = 'none';
        });
      }
    });
  };

  // Toggle action menu
  window.toggleActionMenu = function(id) {
    // Close all other menus
    document.querySelectorAll('.action-menu').forEach(menu => {
      if (menu.id !== id) {
        menu.style.display = 'none';
      }
    });
    
    // Toggle current menu
    const menu = document.getElementById(id);
    if (menu) {
      menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
  };

  // View shift details
  window.viewShift = function(id) {
    const shift = window.shiftsData.find(s => s.shift_id === id);
    if (!shift) return;
    
    alert('Shift: ' + shift.shift_name + '\n' + shift.start_time + ' - ' + shift.end_time);
  };
  })();
</script>

<style>
/* ===== SHIFT MODULE BASE STYLING ===== */

:root {
  --primary: #3b82f6;
  --success: #22c55e;
  --warning: #f59e0b;
  --danger: #ef4444;
  --border: #e5e7eb;
  --text-light: #6b7280;
  --text-lighter: #9ca3af;
  --light: #f9fafb;
  --dark: #1f2937;
}

/* Base Layout */
.shift-container {
  width: 100%;
  max-width: 1420px;
  margin: 0 auto;
  padding: 1.5rem;
}

.shift-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.5rem;
}

.shift-left-column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.shift-right-column {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.shift-side-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* KPI Grid */
.shift-kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.shift-kpi-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1.25rem;
  text-align: center;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.shift-kpi-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.kpi-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.kpi-label {
  font-size: 0.9rem;
  color: var(--text-light);
  font-weight: 600;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark);
  margin-bottom: 0.25rem;
}

.kpi-subtext {
  font-size: 0.8rem;
  color: var(--text-lighter);
}

.shift-card-night {
  border-left: 4px solid #ef4444;
}

.shift-card-oncall {
  border-left: 4px solid #3b82f6;
}

.shift-card-overtime {
  border-left: 4px solid #f59e0b;
}

/* Filters & Search */
.shift-filters-container {
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.shift-search-bar {
  display: flex;
}

.shift-search-bar input {
  flex: 1;
  padding: 0.5rem 1rem;
  border: 1px solid var(--border);
  border-radius: 0.375rem;
  font-size: 0.9rem;
}

.quick-filter-chips {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-chip {
  padding: 0.4rem 0.8rem;
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  background: white;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-chip:hover {
  border-color: var(--primary);
  background: #eff6ff;
  color: var(--primary);
}

.filter-chip.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

/* Table */
.shift-table-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.shift-table-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
  background: var(--light);
}

.shift-table-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark);
}

.shift-table {
  width: 100%;
  border-collapse: collapse;
}

.shift-table thead tr {
  background: var(--light);
  border-bottom: 2px solid var(--border);
}

.shift-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--dark);
}

.shift-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.9rem;
}

.shift-table tbody tr {
  transition: background 0.2s ease;
}

.shift-table tbody tr:hover {
  background: var(--light);
}

/* Form */
.form-input, .form-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 0.375rem;
  font-size: 0.9rem;
  font-family: inherit;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-label {
  display: block;
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
  color: var(--dark);
}

.form-group {
  margin-bottom: 1rem;
}

/* Modal */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  animation: fadeIn 0.2s ease;
}

.modal.active {
  display: flex;
  align-items: center;
  justify-content: center;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--dark);
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--text-light);
  padding: 0;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close:hover {
  color: var(--dark);
}

.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid var(--border);
  background: var(--light);
}

/* Buttons */
.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
  box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
}

.btn-outline {
  background: white;
  color: var(--dark);
  border: 1px solid var(--border);
}

.btn-outline:hover {
  background: var(--light);
  border-color: var(--primary);
  color: var(--primary);
}

/* Shift Type Badge Styling */
.shift-type-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.shift-type-morning {
  background-color: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
}

.shift-type-afternoon {
  background-color: #dbeafe;
  color: #0c4a6e;
  border: 1px solid #93c5fd;
}

.shift-type-night {
  background-color: #e9d5ff;
  color: #5b21b6;
  border: 1px solid #d8b4fe;
}

.shift-type-rotating {
  background-color: #cffafe;
  color: #164e63;
  border: 1px solid #67e8f9;
}

.shift-type-oncall {
  background-color: #fee2e2;
  color: #7f1d1d;
  border: 1px solid #fca5a5;
}

.shift-type-ojt {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.shift-type-custom {
  background-color: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

/* Payroll Badge Styling */
.payroll-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.payroll-badge.badge-yes {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.payroll-badge.badge-no {
  background-color: #f3f4f6;
  color: #4b5563;
  border: 1px solid #d1d5db;
}

/* Coverage Signal Styling */
.coverage-warning {
  background-color: #fff7ed !important;
  border-left: 3px solid #f59e0b;
}

.coverage-signal {
  display: inline-block;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  background-color: #f3f4f6;
  color: #374151;
}

.coverage-warning .coverage-signal {
  background-color: #fed7aa;
  color: #92400e;
  border: 1px solid #fdba74;
}

/* Night Shift Row Highlighting */
.shift-row-night {
  background-color: #f0f4ff;
  border-left: 3px solid #4f46e5;
}

.shift-row-night:hover {
  background-color: #e6ebff;
}

/* Status Badge Styling */
.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: capitalize;
  letter-spacing: 0.5px;
}

.status-badge.status-active {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.status-badge.status-inactive {
  background-color: #e5e7eb;
  color: #4b5563;
  border: 1px solid #d1d5db;
}

/* Modal Styling */
.modal-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding: 1.5rem;
  border-top: 1px solid var(--border);
  background-color: #f9fafb;
}

.form-checkbox {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-size: 0.95rem;
  user-select: none;
}

.form-checkbox input[type="checkbox"] {
  width: 1.1rem;
  height: 1.1rem;
  cursor: pointer;
  accent-color: #3b82f6;
}

/* Right Sidebar Shift Card Styling */
.shift-side-card {
  background-color: #ffffff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.shift-side-card.alert {
  border: 2px solid #ef4444;
  background-color: #fef2f2;
}

.shift-side-subtitle {
  font-size: 0.95rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.75rem;
}

.shift-side-card.alert .shift-side-subtitle {
  color: #dc2626;
}

/* Night Shifts List Styling */
#nightShiftsList {
  max-height: 250px;
  overflow-y: auto;
}

#nightShiftsList > div:last-child {
  border-bottom: none;
}

#unassignedShiftsList {
  max-height: 250px;
  overflow-y: auto;
}

#unassignedShiftsList > div:last-child {
  border-bottom: none;
}

#recentlyModifiedList {
  max-height: 250px;
  overflow-y: auto;
}

#recentlyModifiedList > div:last-child {
  border-bottom: none;
}

/* Filter Chip Styling */
.shift-filter-chips {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
}

[data-filter] {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  background-color: #f3f4f6;
  color: #374151;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

[data-filter]:hover {
  border-color: #3b82f6;
  background-color: #eff6ff;
  color: #3b82f6;
}

[data-filter].active {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

#resetFilter {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border);
  border-radius: 1.5rem;
  background-color: white;
  color: #6b7280;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

#resetFilter:hover {
  border-color: #ef4444;
  color: #ef4444;
  background-color: #fef2f2;
}

/* Button Icon Styling */
.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 0.25rem 0.5rem;
  transition: all 0.2s ease;
  border-radius: 0.25rem;
}

.btn-icon:hover {
  background-color: #f3f4f6;
  transform: scale(1.1);
}

.btn-icon:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  transform: none;
}

.btn-icon.btn-danger:hover:not(:disabled) {
  background-color: #fee2e2;
  color: #dc2626;
}

/* Empty State Styling */
.empty-state-container {
  display: none;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  color: #6b7280;
  grid-column: 1 / -1;
}

.empty-state-container > * {
  margin-bottom: 1rem;
}

.empty-state-container .empty-state-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
}

.empty-state-container .empty-state-text {
  font-size: 0.95rem;
  color: #6b7280;
  max-width: 500px;
}

/* Action Menu Styles */
.action-menu-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  font-size: 1.2rem;
  color: #6b7280;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.action-menu-btn:hover {
  background: #f3f4f6;
  color: #1f2937;
}

.action-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  min-width: 140px;
}

.action-menu-item {
  display: block;
  width: 100%;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  text-align: left;
  cursor: pointer;
  font-size: 0.9rem;
  color: #1f2937;
  transition: all 0.2s ease;
}

.action-menu-item:hover:not(:disabled) {
  background: #f3f4f6;
}

.action-menu-item:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-menu-item.action-menu-danger {
  color: #ef4444;
}

.action-menu-item.action-menu-danger:hover {
  background: #fee2e2;
}

/* Responsive Adjustments */
@media (max-width: 1200px) {
  .shift-type-badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
  }

  [data-filter] {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
  }
}

@media (max-width: 768px) {
  .shift-container {
    padding: 1rem;
  }

  .shift-layout {
    gap: 1rem;
  }

  .shift-kpi-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .shift-side-card {
    padding: 0.75rem;
    font-size: 0.9rem;
  }

  .shift-type-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }

  .payroll-badge {
    font-size: 0.75rem;
    padding: 0.15rem 0.35rem;
  }

  [data-filter] {
    padding: 0.35rem 0.6rem;
    font-size: 0.8rem;
  }

  .btn-icon {
    font-size: 0.9rem;
  }

  .shift-filter-chips {
    gap: 0.5rem;
  }
}

@media (max-width: 480px) {
  .shift-type-badge {
    display: block;
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
  }

  .payroll-badge {
    font-size: 0.7rem;
    padding: 0.1rem 0.25rem;
  }

  [data-filter] {
    padding: 0.3rem 0.5rem;
    font-size: 0.75rem;
  }

  #resetFilter {
    padding: 0.3rem 0.5rem;
    font-size: 0.75rem;
  }
}
</style>
</main>
