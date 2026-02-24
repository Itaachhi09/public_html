<!-- Work Schedules Management View -->
<main class="main-content schedule-container">
  <div class="schedule-layout">
    <div class="schedule-left-column">
      <!-- KPI Cards -->
      <div class="schedule-kpi-grid">
        <div class="schedule-kpi-card schedule-card-active">
          <div class="kpi-icon">✓</div>
          <div class="kpi-label">Active Schedules</div>
          <div class="kpi-value" id="activeScheduleCount">0</div>
          <div class="kpi-subtext">In use now</div>
        </div>
        <div class="schedule-kpi-card schedule-card-assigned">
          <div class="kpi-icon">👥</div>
          <div class="kpi-label">Assigned Employees</div>
          <div class="kpi-value" id="assignedEmployeeCount">0</div>
          <div class="kpi-subtext">Total assignments</div>
        </div>
        <div class="schedule-kpi-card schedule-card-unassigned">
          <div class="kpi-icon">⚠️</div>
          <div class="kpi-label">Unassigned Schedules</div>
          <div class="kpi-value" id="unassignedScheduleCount">0</div>
          <div class="kpi-subtext">No employees yet</div>
        </div>
        <div class="schedule-kpi-card schedule-card-night">
          <div class="kpi-icon">🌙</div>
          <div class="kpi-label">Night Shift Schedules</div>
          <div class="kpi-value" id="nightScheduleCount">0</div>
          <div class="kpi-subtext">Requires differential</div>
        </div>
      </div>

      <!-- Search & Quick Filters -->
      <div class="schedule-filters-container">
        <div class="schedule-search-bar">
          <input type="text" id="searchInput" class="form-input" placeholder="Search schedules...">
        </div>
        <div class="quick-filter-chips">
          <button class="filter-chip active" data-filter="all">
            <span>All Schedules</span>
          </button>
          <button class="filter-chip" data-filter="active">
            <span>✓</span> <span>Active</span>
          </button>
          <button class="filter-chip" data-filter="unassigned">
            <span>⚠️</span> <span>Unassigned</span>
          </button>
          <button class="filter-chip" data-filter="night">
            <span>🌙</span> <span>Night</span>
          </button>
          <button class="filter-chip" id="resetFilter">Reset</button>
        </div>
      </div>

      <!-- Schedules Table -->
      <div class="schedule-table-card">
        <div class="schedule-table-header">
          <h3 class="schedule-table-title">Schedule Assignment & Coverage</h3>
        </div>

        <div style="overflow-x: auto;">
          <table class="schedule-table">
            <thead>
              <tr>
                <th>Schedule Name</th>
                <th style="text-align: center;">Type</th>
                <th style="text-align: center;">Shift Pattern</th>
                <th style="text-align: center;">Hours/Day</th>
                <th style="text-align: center;">Weekly Hrs</th>
                <th style="text-align: center;">Assigned</th>
                <th style="text-align: center;">Coverage</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody id="schedulesList" class="schedules-tbody">
              <tr>
                <td colspan="9" style="text-align: center; padding: 3rem 2rem;">
                  <div style="color: var(--text-light);">
                    <div style="font-size: 48px; margin-bottom: 1rem;">📅</div>
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No schedules defined</p>
                    <p style="font-size: 14px; margin-bottom: 1.5rem;">Create schedules to assign shifts and compute attendance.</p>
                    <button class="btn btn-primary" onclick="window.openScheduleModal()">+ Add Schedule</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="schedule-right-column">
      <div class="schedule-side-panel">
        <!-- Quick Add -->
        <div class="schedule-side-card">
          <div class="schedule-side-action">
            <button class="btn btn-primary" onclick="window.openScheduleModal()" style="flex: 1;">+ Add Schedule</button>
          </div>
        </div>

        <!-- Unassigned Schedules -->
        <div class="schedule-side-card alert">
          <div class="schedule-side-subtitle">⚠️ Unassigned Schedules</div>
          <div id="unassignedSchedulesList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Night Shift Schedules -->
        <div class="schedule-side-card">
          <div class="schedule-side-subtitle">🌙 Night Shift Schedules</div>
          <div id="nightSchedulesList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Recently Modified -->
        <div class="schedule-side-card">
          <div class="schedule-side-subtitle">Recently Modified</div>
          <div id="recentlyModifiedList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Schedule Types Reference -->
        <div class="schedule-side-card">
          <div class="schedule-side-subtitle">📚 Schedule Types</div>
          <div style="font-size: 11px; line-height: 1.8;">
            <div><span class="schedule-type-badge schedule-type-fixed">Fixed Weekly</span></div>
            <div><span class="schedule-type-badge schedule-type-rotating">Rotating</span></div>
            <div><span class="schedule-type-badge schedule-type-flexible">Flexible</span></div>
            <div><span class="schedule-type-badge schedule-type-ojt">OJT Schedule</span></div>
            <div><span class="schedule-type-badge schedule-type-oncall">On Call</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<!-- Add/Edit Schedule Modal -->
<div id="scheduleModal" class="modal">
  <div class="modal-content" style="max-width: 800px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Schedule</h2>
      <button class="modal-close" onclick="window.closeScheduleModal()">&times;</button>
    </div>

    <form id="scheduleForm">
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1.5rem;">
        <div class="form-group">
          <label class="form-label">Schedule Name *</label>
          <input type="text" name="schedule_name" class="form-input" required placeholder="e.g., Standard 5-Day">
        </div>
        <div class="form-group">
          <label class="form-label">Schedule Type *</label>
          <select name="schedule_type" class="form-select" required onchange="window.updateScheduleDefaults(this.value)">
            <option value="">Select type...</option>
            <option value="Fixed">Fixed Weekly</option>
            <option value="Rotating">Rotating</option>
            <option value="Flexible">Flexible</option>
            <option value="Part-time">Part-time</option>
          </select>
        </div>

        <div class="form-group" style="grid-column: 1 / -1; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
          <label class="form-label">⏰ Time Configuration</label>
        </div>

        <div class="form-group">
          <label class="form-label">Hours Per Day *</label>
          <input type="number" name="hours_per_day" class="form-input" required step="0.5" min="0" placeholder="8" onchange="window.calculateWeeklyHours()">
        </div>
        <div class="form-group">
          <label class="form-label">Work Days Per Week *</label>
          <input type="number" name="work_days_per_week" class="form-input" required min="0" max="7" placeholder="5" onchange="window.calculateWeeklyHours()">
        </div>

        <div class="form-group">
          <label class="form-label">Weekly Total Hours (auto-calculated)</label>
          <input type="number" name="weekly_hours" class="form-input" readonly placeholder="40" style="background: #f9fafb; color: #6b7280;">
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Shift Pattern (Morning/Afternoon/Night)</label>
          <input type="text" name="shift_pattern" class="form-input" placeholder="e.g., Morning, Morning, Afternoon, Afternoon, Night, Rest, Rest">
        </div>

        <!-- Payroll & Coverage -->
        <div class="form-group" style="grid-column: 1 / -1; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
          <label class="form-label">💰 Payroll & Coverage Rules</label>
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 0.75rem;">
            <label class="form-checkbox">
              <input type="checkbox" name="has_night_shift" value="1"> Has Night Shift
            </label>
            <label class="form-checkbox">
              <input type="checkbox" name="is_trainee" value="1"> Trainee (OJT)
            </label>
            <label class="form-checkbox">
              <input type="checkbox" name="is_active" value="1" checked> Active Schedule
            </label>
          </div>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-input" placeholder="Schedule details, restrictions, or notes..."></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Effective Start Date</label>
          <input type="date" name="start_date" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label">End Date (if applicable)</label>
          <input type="date" name="end_date" class="form-input">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="window.closeScheduleModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Schedule</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function() {
  // Schedule Type Presets - mapped to database schedules
  window.SCHEDULE_PRESETS = {
    'Fixed': { hours_per_day: 8, work_days_per_week: 5, shift_pattern: 'M-F 8hrs', has_night: 0, is_trainee: 0 },
    'Rotating': { hours_per_day: 8, work_days_per_week: 7, shift_pattern: '3-shift rotation', has_night: 1, is_trainee: 0 },
    'Flexible': { hours_per_day: 8, work_days_per_week: 5, shift_pattern: 'Flexible', has_night: 0, is_trainee: 0 },
    'Part-time': { hours_per_day: 4, work_days_per_week: 5, shift_pattern: 'Variable', has_night: 0, is_trainee: 0 }
  };

  // Load schedules
  window.loadSchedules = async function() {
    try {
      const response = await fetch('modules/hr_core/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_schedules_stats' })
      });
      const result = await response.json();
      if (result.success) {
        window.schedulesData = result.data || [];
        window.schedulesStats = result.stats || {};
        window.displaySchedules();
        setTimeout(window.attachEventListeners, 50);
        setTimeout(window.loadSidePanels, 75);
      }
    } catch (error) {
      console.error('Error loading schedules:', error);
    }
  };

  // Display schedules
  window.displaySchedules = function() {
    const tbody = document.querySelector('.schedules-tbody');
    const emptyState = document.querySelector('.main-content');
    
    if (!window.schedulesData || window.schedulesData.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 3rem 2rem;"><div style="color: var(--text-light);"><div style="font-size: 48px; margin-bottom: 1rem;">📅</div><p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No schedules defined</p><p style="font-size: 14px; margin-bottom: 1.5rem;">Create schedules to assign shifts and compute attendance.</p><button class="btn btn-primary" onclick="window.openScheduleModal()">+ Add Schedule</button></div></td></tr>';
      document.getElementById('activeScheduleCount').textContent = '0';
      document.getElementById('assignedEmployeeCount').textContent = '0';
      document.getElementById('unassignedScheduleCount').textContent = '0';
      document.getElementById('nightScheduleCount').textContent = '0';
      return;
    }

    // Update KPIs
    document.getElementById('activeScheduleCount').textContent = window.schedulesStats.active_count || 0;
    document.getElementById('assignedEmployeeCount').textContent = window.schedulesStats.total_assigned || 0;
    document.getElementById('unassignedScheduleCount').textContent = window.schedulesStats.unassigned_count || 0;
    document.getElementById('nightScheduleCount').textContent = window.schedulesStats.night_schedule_count || 0;

    tbody.innerHTML = window.schedulesData.map((sched) => {
      const typeClass = 'schedule-type-' + (sched.schedule_type || 'custom').toLowerCase();
      const typeLabel = sched.schedule_type ? sched.schedule_type.charAt(0).toUpperCase() + sched.schedule_type.slice(1) : 'Custom';
      const assigned = sched.assigned_count || 0;
      const required = sched.required_count || 0;
      const coverageClass = assigned < required ? 'coverage-warning' : '';
      const coverageBadge = assigned < required ? ' ⚠️' : '';
      const unassignedClass = assigned === 0 ? 'schedule-row-unassigned' : '';
      const nightClass = sched.has_night_shift ? 'schedule-row-night' : '';
      const traineeClass = sched.is_trainee ? 'schedule-row-trainee' : '';

      return `
        <tr class="schedule-row ${unassignedClass} ${nightClass} ${traineeClass}" data-schedule-id="${sched.id}">
          <td><strong>${sched.schedule_name}</strong>${sched.is_trainee ? ' <span class="trainee-badge">🎓 Trainee</span>' : ''}</td>
          <td style="text-align: center;">
            <span class="schedule-type-badge ${typeClass}">${typeLabel}</span>
          </td>
          <td style="text-align: center; font-size: 0.9rem;">
            <span title="Shift pattern">${sched.shift_pattern || '-'}</span>
          </td>
          <td style="text-align: right; font-weight: 600;">${sched.hours_per_day || 0}h</td>
          <td style="text-align: right; font-weight: 600;">${sched.weekly_hours || 0}h</td>
          <td style="text-align: center; font-weight: 600;">${assigned}</td>
          <td style="text-align: center;" class="${coverageClass}">
            <span class="coverage-signal">${assigned}/${required}${coverageBadge}</span>
          </td>
          <td style="text-align: center;">
            <span class="status-badge status-${sched.status || 'active'}">${(sched.status || 'Active').charAt(0).toUpperCase() + (sched.status || 'Active').slice(1)}</span>
          </td>
          <td style="text-align: center; white-space: nowrap;">
            <div style="position: relative; display: inline-block;">
              <button class="action-menu-btn" onclick="window.toggleActionMenu('schedule-${sched.id}')" title="Actions">⋮</button>
              <div class="action-menu" id="schedule-${sched.id}" style="display: none;">
                <button class="action-menu-item" onclick="window.viewSchedule(${sched.id})">👁 View</button>
                <button class="action-menu-item" onclick="window.editSchedule(${sched.id})">✏️ Edit</button>
                <button class="action-menu-item action-menu-danger" ${assigned > 0 ? 'disabled' : ''} onclick="window.deleteSchedule(${sched.id})">🗑 Delete</button>
              </div>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  };

  // Update schedule defaults
  window.updateScheduleDefaults = function(scheduleType) {
    if (!scheduleType || !window.SCHEDULE_PRESETS[scheduleType]) return;
    
    const preset = window.SCHEDULE_PRESETS[scheduleType];
    const form = document.getElementById('scheduleForm');
    
    form.hours_per_day.value = preset.hours_per_day;
    form.work_days_per_week.value = preset.work_days_per_week;
    form.shift_pattern.value = preset.shift_pattern;
    form.has_night_shift.checked = preset.has_night === 1;
    form.is_trainee.checked = preset.is_trainee === 1;

    // OJT specific rules
    if (scheduleType === 'ojt') {
      form.has_night_shift.checked = false;
      form.has_night_shift.disabled = true;
      form.is_trainee.checked = true;
      form.is_trainee.disabled = true;
    } else {
      form.has_night_shift.disabled = false;
      form.is_trainee.disabled = false;
    }

    window.calculateWeeklyHours();
  };

  // Calculate weekly hours
  window.calculateWeeklyHours = function() {
    const form = document.getElementById('scheduleForm');
    const hoursPerDay = parseFloat(form.hours_per_day.value) || 0;
    const workDaysPerWeek = parseInt(form.work_days_per_week.value) || 0;
    const weeklyHours = hoursPerDay * workDaysPerWeek;
    form.weekly_hours.value = weeklyHours.toFixed(1);
  };

  // Open modal
  window.openScheduleModal = function(scheduleId = null) {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    const title = document.querySelector('.modal-title');
    
    form.reset();
    form.schedule_type.value = '';
    form.is_active.checked = true;
    
    if (scheduleId) {
      title.textContent = 'Edit Schedule';
      const schedule = window.schedulesData.find(s => s.id === scheduleId);
      if (schedule) {
        form.schedule_name.value = schedule.schedule_name;
        form.schedule_type.value = schedule.schedule_type || '';
        form.hours_per_day.value = schedule.hours_per_day || 8;
        form.work_days_per_week.value = schedule.work_days_per_week || 5;
        form.weekly_hours.value = schedule.weekly_hours || 40;
        form.shift_pattern.value = schedule.shift_pattern || '';
        form.has_night_shift.checked = schedule.has_night_shift === 1;
        form.is_trainee.checked = schedule.is_trainee === 1;
        form.is_active.checked = schedule.status === 'Active';
        form.description.value = schedule.description || '';
        form.start_date.value = schedule.start_date || '';
        form.end_date.value = schedule.end_date || '';
      }
    } else {
      title.textContent = 'Add Schedule';
    }
    
    modal.classList.add('active');
  };

  window.closeScheduleModal = function() {
    document.getElementById('scheduleModal').classList.remove('active');
  };

  // Delete schedule
  window.deleteSchedule = async function(scheduleId) {
    const schedule = window.schedulesData.find(s => s.id === scheduleId);
    if (!schedule) return;
    
    if (schedule.assigned_count > 0) {
      alert('Cannot delete: ' + schedule.assigned_count + ' employees assigned to this schedule. Deactivate instead.');
      return;
    }
    
    if (!confirm('Delete "' + schedule.schedule_name + '"?')) return;
    
    try {
      const response = await fetch('modules/hr_core/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_schedule', schedule_id: scheduleId })
      });
      const result = await response.json();
      if (result.success) {
        window.loadSchedules();
      }
    } catch (error) {
      console.error('Error deleting schedule:', error);
    }
  };

  // Edit schedule
  window.editSchedule = function(scheduleId) {
    window.openScheduleModal(scheduleId);
  };

  // Load side panels
  window.loadSidePanels = async function() {
    try {
      const response = await fetch('modules/hr_core/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_schedule_panels' })
      });
      const result = await response.json();
      if (result.success) {
        window.loadUnassignedSchedules(result.unassigned_schedules || []);
        window.loadNightSchedules(result.night_schedules || []);
        window.loadRecentlyModified(result.recently_modified || []);
      }
    } catch (error) {
      console.error('Error loading side panels:', error);
    }
  };

  // Populate unassigned schedules
  window.loadUnassignedSchedules = function(unassignedSchedules) {
    const container = document.getElementById('unassignedSchedulesList');
    if (!container) return; // Element might not be in DOM
    
    if (!unassignedSchedules || unassignedSchedules.length === 0) {
      container.innerHTML = '<div style="color: #6b7280; font-size: 0.9rem;">All schedules assigned</div>';
      return;
    }
    container.innerHTML = unassignedSchedules.map(sched => `
      <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.9rem;">
        <strong>${sched.schedule_name}</strong><br>
        <span style="color: #f59e0b;">⚠️ No assignments</span>
      </div>
    `).join('');
  };

  // Populate night schedules
  window.loadNightSchedules = function(nightSchedules) {
    const container = document.getElementById('nightSchedulesList');
    if (!container) return; // Element might not be in DOM
    
    if (!nightSchedules || nightSchedules.length === 0) {
      container.innerHTML = '<div style="color: #6b7280; font-size: 0.9rem;">No night schedules</div>';
      return;
    }
    container.innerHTML = nightSchedules.map(sched => `
      <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.9rem;">
        <strong>${sched.schedule_name}</strong><br>
        <span style="color: #6b7280;">${sched.hours_per_day}h/day • ${sched.work_days_per_week}d/week</span>
      </div>
    `).join('');
  };

  // Populate recently modified
  window.loadRecentlyModified = function(recentlyModified) {
    const container = document.getElementById('recentlyModifiedList');
    if (!container) return; // Element might not be in DOM
    
    if (!recentlyModified || recentlyModified.length === 0) {
      container.innerHTML = '<div style="color: #6b7280; font-size: 0.9rem;">No recent changes</div>';
      return;
    }
    container.innerHTML = recentlyModified.map(sched => `
      <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.85rem;">
        <strong>${sched.schedule_name}</strong><br>
        <span style="color: #6b7280;">Modified ${sched.modified_date || 'recently'}</span>
      </div>
    `).join('');
  };

  // Form submission - attach listeners when module loads
  function attachScheduleEventListeners() {
    const form = document.getElementById('scheduleForm');
    if (form) {
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const scheduleId = document.querySelector('[data-schedule-id]')?.dataset?.scheduleId;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.action = scheduleId ? 'update_schedule' : 'create_schedule';
        if (scheduleId) data.schedule_id = scheduleId;
        
        try {
          const response = await fetch('modules/hr_core/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
          });
          const result = await response.json();
          if (result.success) {
            window.closeScheduleModal();
            window.loadSchedules();
          }
        } catch (error) {
          console.error('Error saving schedule:', error);
        }
      });
    }

    // Modal backdrop close
    const modal = document.getElementById('scheduleModal');
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === modal) window.closeScheduleModal();
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
      window.displaySchedules();
    });

    // Load data
    window.loadSchedules();
  }

  // Call event listeners when module loads
  setTimeout(attachScheduleEventListeners, 50);

  // Apply filter
  window.applyFilter = function(filter) {
    const tbody = document.querySelector('.schedules-tbody');
    if (!tbody) return; // Element might not exist
    
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
      row.style.display = 'table-row';
      
      if (filter === 'active') {
        const status = row.querySelector('.status-badge');
        row.style.display = status?.textContent.toLowerCase().includes('active') ? 'table-row' : 'none';
      } else if (filter === 'unassigned') {
        row.style.display = row.classList.contains('schedule-row-unassigned') ? 'table-row' : 'none';
      } else if (filter === 'night') {
        row.style.display = row.classList.contains('schedule-row-night') ? 'table-row' : 'none';
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

  // View schedule details
  window.viewSchedule = function(id) {
    const schedule = window.schedulesData.find(s => s.id === id);
    if (!schedule) return;
    
    alert('Schedule: ' + schedule.schedule_name + '\n' + schedule.hours_per_day + 'h/day • ' + schedule.work_days_per_week + 'd/week');
  };
  })();
</script>

<style>
/* ===== SCHEDULE MODULE BASE STYLING ===== */

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
.schedule-container {
  width: 100%;
  max-width: 1420px;
  margin: 0 auto;
  padding: 1.5rem;
}

.schedule-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.5rem;
}

.schedule-left-column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.schedule-right-column {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.schedule-side-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* KPI Grid */
.schedule-kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.schedule-kpi-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1.25rem;
  text-align: center;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.schedule-kpi-card:hover {
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

.schedule-card-unassigned {
  border-left: 4px solid #f59e0b;
}

.schedule-card-night {
  border-left: 4px solid #ef4444;
}

/* Filters & Search */
.schedule-filters-container {
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.schedule-search-bar {
  display: flex;
}

.schedule-search-bar input {
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
.schedule-table-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.schedule-table-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
  background: var(--light);
}

.schedule-table-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark);
}

.schedule-table {
  width: 100%;
  border-collapse: collapse;
}

.schedule-table thead tr {
  background: var(--light);
  border-bottom: 2px solid var(--border);
}

.schedule-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.9rem;
  color: var(--dark);
}

.schedule-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.9rem;
}

.schedule-table tbody tr {
  transition: background 0.2s ease;
}

.schedule-table tbody tr:hover {
  background: var(--light);
}

/* Schedule Type Badge */
.schedule-type-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.schedule-type-fixed {
  background-color: #dbeafe;
  color: #0c4a6e;
  border: 1px solid #93c5fd;
}

.schedule-type-rotating {
  background-color: #cffafe;
  color: #164e63;
  border: 1px solid #67e8f9;
}

.schedule-type-flexible {
  background-color: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
}

.schedule-type-ojt {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.schedule-type-oncall {
  background-color: #fee2e2;
  color: #7f1d1d;
  border: 1px solid #fca5a5;
}

/* Coverage & Row Highlighting */
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

.schedule-row-unassigned {
  background-color: #fef2f2;
  border-left: 3px solid #ef4444;
}

.schedule-row-night {
  background-color: #f0f4ff;
  border-left: 3px solid #4f46e5;
}

.schedule-row-trainee {
  background-color: #f0fdf4;
  border-left: 3px solid #22c55e;
}

.trainee-badge {
  display: inline-block;
  padding: 0.15rem 0.4rem;
  background: #dcfce7;
  color: #15803d;
  border-radius: 0.25rem;
  font-size: 0.8rem;
  font-weight: 600;
  margin-left: 0.5rem;
}

/* Status Badge */
.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: capitalize;
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

/* Form Elements */
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
  accent-color: var(--primary);
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
}

.modal.active {
  display: flex;
  align-items: center;
  justify-content: center;
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

/* Right Sidebar */
.schedule-side-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
  margin-bottom: 1rem;
}

.schedule-side-card.alert {
  border: 2px solid #ef4444;
  background-color: #fef2f2;
}

.schedule-side-subtitle {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 0.75rem;
}

.schedule-side-card.alert .schedule-side-subtitle {
  color: #dc2626;
}

.schedule-side-action {
  display: flex;
  gap: 0.5rem;
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

/* Responsive */
@media (max-width: 1200px) {
  .schedule-layout {
    grid-template-columns: 1fr;
  }

  .schedule-kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .schedule-container {
    padding: 1rem;
  }

  .schedule-kpi-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .schedule-table {
    font-size: 0.85rem;
  }

  .schedule-table th,
  .schedule-table td {
    padding: 0.5rem 0.75rem;
  }

  .schedule-type-badge {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
  }

  .filter-chip {
    padding: 0.35rem 0.6rem;
    font-size: 0.8rem;
  }
}

@media (max-width: 480px) {
  .schedule-kpi-grid {
    grid-template-columns: 1fr;
  }

  .schedule-table {
    font-size: 0.8rem;
  }

  .schedule-table th,
  .schedule-table td {
    padding: 0.35rem 0.5rem;
  }

  .schedule-type-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
  }

  .filter-chip {
    padding: 0.3rem 0.5rem;
    font-size: 0.75rem;
  }
}
</style>
</main>
