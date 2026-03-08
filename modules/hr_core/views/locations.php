<!-- Work Locations Management View -->
<main class="main-content loc-container">
  <div class="loc-layout">
    <div class="loc-left-column">
      <!-- KPI Cards -->
      <div class="loc-kpi-grid">
        <div class="loc-kpi-card loc-card-total">
          <div class="kpi-icon">📍</div>
          <div class="kpi-label">Total Locations</div>
          <div class="kpi-value" id="totalLocCount">0</div>
          <div class="kpi-subtext">Work locations defined</div>
        </div>
        <div class="loc-kpi-card loc-card-active">
          <div class="kpi-icon">✓</div>
          <div class="kpi-label">Active Locations</div>
          <div class="kpi-value" id="activeLocCount">0</div>
          <div class="kpi-subtext">Operating facilities</div>
        </div>
        <div class="loc-kpi-card loc-card-staffed">
          <div class="kpi-icon">👥</div>
          <div class="kpi-label">Staff Assigned</div>
          <div class="kpi-value" id="staffAssignedCount">0</div>
          <div class="kpi-subtext">Total employees</div>
        </div>
        <div class="loc-kpi-card loc-card-unstaffed">
          <div class="kpi-icon">⚠️</div>
          <div class="kpi-label">Locations Without Staff</div>
          <div class="kpi-value" id="noStaffCount">0</div>
          <div class="kpi-subtext">Operational gap</div>
        </div>
      </div>

      <!-- Search & Quick Filters -->
      <div class="loc-filters-container">
        <div class="loc-search-bar">
          <input type="text" id="searchInput" class="form-input" placeholder="Search by city or facility...">
        </div>
        <div class="quick-filter-chips">
          <button class="filter-chip" data-filter="city">
            <span>📍</span> <span>By City</span>
          </button>
          <button class="filter-chip" data-filter="type">
            <span>🏢</span> <span>By Type</span>
          </button>
          <button class="filter-chip active" data-filter="active">
            <span>✓</span> <span>Active Only</span>
          </button>
          <button class="filter-chip" onclick="window.resetLocFilters()">Reset</button>
        </div>
      </div>

      <!-- Locations Table -->
      <div class="loc-table-card">
        <div class="loc-table-header">
          <h3 class="loc-table-title">Work Locations</h3>
        </div>

        <div style="overflow-x: auto;">
          <table class="loc-table">
            <thead>
              <tr>
                <th>Location Name</th>
                <th>Type</th>
                <th>Facility</th>
                <th>City</th>
                <th style="text-align: right; padding-right: 2rem;">Staff Count</th>
                <th style="text-align: center;">Rules</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: center;">Actions</th>
              </tr>
            </thead>
            <tbody id="locationsList">
              <tr>
                <td colspan="8" style="text-align: center; padding: 3rem 2rem;">
                  <div style="color: var(--text-light);">
                    <div style="font-size: 48px; margin-bottom: 1rem;">📍</div>
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No locations found</p>
                    <p style="font-size: 14px; margin-bottom: 1.5rem;">Add work locations to assign staff and schedules.</p>
                    <button class="btn btn-primary" onclick="window.openLocationModal()">+ Add Location</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="loc-right-column">
      <div class="loc-side-panel">
        <!-- Quick Add -->
        <div class="loc-side-card">
          <div class="loc-side-action">
            <button class="btn btn-primary" onclick="window.openLocationModal()" style="flex: 1;">+ Add Location</button>
          </div>
        </div>

        <!-- Locations Without Staff -->
        <div class="loc-side-card alert">
          <div class="loc-side-subtitle">⚠️ Locations Without Staff</div>
          <div id="noStaffList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Recently Added -->
        <div class="loc-side-card">
          <div class="loc-side-subtitle">Recently Added</div>
          <div id="recentlyAddedList" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
            <p style="color: var(--text-lighter); margin: 0;">Loading...</p>
          </div>
        </div>

        <!-- Location Types Reference -->
        <div class="loc-side-card">
          <div class="loc-side-subtitle">📚 Location Types</div>
          <div style="font-size: 11px; line-height: 1.6;">
            <div><span class="loc-type-badge loc-type-main">Main Hospital</span></div>
            <div><span class="loc-type-badge loc-type-satellite">Satellite Clinic</span></div>
            <div><span class="loc-type-badge loc-type-office">Admin Office</span></div>
            <div><span class="loc-type-badge loc-type-training">Training Facility</span></div>
            <div><span class="loc-type-badge loc-type-remote">Remote/Field</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</main>

<!-- Add/Edit Location Modal -->
<div id="locationModal" class="modal">
  <div class="modal-content" style="max-width: 700px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Work Location</h2>
      <button class="modal-close" onclick="window.closeLocationModal()">&times;</button>
    </div>

    <form id="locationForm">
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 1.5rem;">
        <div class="form-group">
          <label class="form-label">Location Name *</label>
          <input type="text" name="location_name" class="form-input" required placeholder="e.g., Main Hospital">
        </div>
        <div class="form-group">
          <label class="form-label">Location Type *</label>
          <select name="location_type" class="form-select" required>
            <option value="">Select type...</option>
            <option value="main_hospital">Main Hospital</option>
            <option value="satellite_clinic">Satellite Clinic</option>
            <option value="admin_office">Administrative Office</option>
            <option value="training_facility">Training Facility</option>
            <option value="remote_field">Remote or Field</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Facility/Building</label>
          <input type="text" name="facility_name" class="form-input" placeholder="e.g., Building A, East Wing">
        </div>
        <div class="form-group">
          <label class="form-label">City *</label>
          <input type="text" name="city" class="form-input" required placeholder="e.g., Manila">
        </div>
        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Address *</label>
          <input type="text" name="address" class="form-input" required placeholder="Full address">
        </div>
        <div class="form-group">
          <label class="form-label">Admin Contact Number</label>
          <input type="tel" name="contact_number" class="form-input" placeholder="+63-2-XXXX-XXXX">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" placeholder="location@hospital.com">
        </div>

        <!-- Location Rules -->
        <div class="form-group" style="grid-column: 1 / -1; border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
          <label class="form-label" style="margin-bottom: 0.75rem;">📋 Location Rules</label>
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <label class="form-checkbox">
              <input type="checkbox" name="payroll_eligible" value="1"> Payroll Eligible
            </label>
            <label class="form-checkbox">
              <input type="checkbox" name="scheduling_enabled" value="1"> Scheduling Enabled
            </label>
            <label class="form-checkbox">
              <input type="checkbox" name="allow_overtime" value="1"> Allow Overtime
            </label>
            <label class="form-checkbox">
              <input type="checkbox" name="allow_night_shift" value="1"> Allow Night Shift
            </label>
          </div>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-input" placeholder="Additional information..."></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="window.closeLocationModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Location</button>
      </div>
    </form>
  </div>
</div>


<script>
  (function() {
    window.loadLocations = function() {
      const searchQuery = document.getElementById('searchInput')?.value || '';
      
      // Get active filters
      const activeFilters = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.filter)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        filter: activeFilters
      });

      fetch(`modules/hr_core/api.php?action=getLocations&${params}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data && data.data.locations) {
            window.displayLocations(data.data.locations);
            
            // Calculate and update KPI counts
            const locations = data.data.locations;
            document.getElementById('totalLocCount').textContent = locations.length;
            document.getElementById('activeLocCount').textContent = locations.length;
            document.getElementById('staffAssignedCount').textContent = locations.length;
            document.getElementById('noStaffCount').textContent = 0;
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.displayLocations = function(locations) {
      const tbody = document.getElementById('locationsList');
      tbody.innerHTML = '';

      if (!locations || locations.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">📍</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No locations found</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Add work locations to assign staff and schedules.</p>
                <button class="btn btn-primary" onclick="window.openLocationModal()">+ Add Location</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      locations.forEach(loc => {
        // Location type badge with color
        let typeBadge = '';
        let typeClass = '';
        const typeMap = {
          'main_hospital': { label: 'Main Hospital', class: 'loc-type-main' },
          'satellite_clinic': { label: 'Satellite Clinic', class: 'loc-type-satellite' },
          'admin_office': { label: 'Admin Office', class: 'loc-type-office' },
          'training_facility': { label: 'Training Facility', class: 'loc-type-training' },
          'remote_field': { label: 'Remote/Field', class: 'loc-type-remote' }
        };
        
        const typeInfo = typeMap[loc.location_type] || { label: loc.location_type, class: 'loc-type-other' };
        typeBadge = `<span class="loc-type-badge ${typeInfo.class}">${typeInfo.label}</span>`;

        // Rules flags - show as small indicators
        const rules = [];
        if (loc.payroll_eligible) rules.push('💰');
        if (loc.scheduling_enabled) rules.push('📅');
        if (loc.allow_overtime) rules.push('⏱️');
        if (loc.allow_night_shift) rules.push('🌙');
        const rulesDisplay = rules.length > 0 ? rules.join('') : '—';

        // Status badge
        const statusBadge = loc.status === 'active' ? 'loc-status-active' : 'loc-status-inactive';

        // Staff count highlighting
        const staffCount = loc.employee_count || 0;
        const staffClass = staffCount === 0 ? 'loc-staff-zero' : '';
        const staffStyle = staffClass ? 'font-weight: bold; color: var(--danger);' : '';

        // Context-aware actions
        let actionButtons = '';
        const hasStaff = staffCount > 0;
        
        actionButtons = `
          <button class="loc-action-btn loc-action-edit" onclick="window.editLocation(${loc.location_id})" title="Edit">✏</button>
          <button class="loc-action-btn loc-action-more" onclick="alert('More options')" title="More">⋯</button>
        `;
        
        // Delete guard - disable if staff assigned
        if (!hasStaff) {
          actionButtons += `
            <button class="loc-action-btn loc-action-delete" onclick="window.deleteLocation(${loc.location_id})" title="Delete">🗑</button>
          `;
        } else {
          actionButtons += `
            <button class="loc-action-btn loc-action-deactivate" onclick="window.deactivateLocation(${loc.location_id})" title="Deactivate instead of delete">🔒</button>
          `;
        }

        const row = `
          <tr ${staffClass ? 'style="background-color: rgba(239, 68, 68, 0.05);"' : ''} ${loc.status !== 'active' ? 'style="opacity: 0.6;"' : ''}>
            <td style="font-weight: 600; color: var(--text-dark);">${loc.location_name || '-'}</td>
            <td style="text-align: center;">${typeBadge}</td>
            <td style="font-size: 13px;">${loc.facility_name || '-'}</td>
            <td style="font-size: 13px;">${loc.city || '-'}</td>
            <td style="text-align: right; padding-right: 2rem; ${staffStyle}" class="${staffClass}">${staffCount}</td>
            <td style="text-align: center; font-size: 14px;">${rulesDisplay}</td>
            <td style="text-align: center;">
              <span class="loc-status-badge ${statusBadge}">
                ${loc.status === 'active' ? '✓ Active' : '✕ Inactive'}
              </span>
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

    window.openLocationModal = function() {
      document.getElementById('locationForm').reset();
      delete document.getElementById('locationForm').dataset.id;
      document.querySelector('#locationModal .modal-title').textContent = 'Add Work Location';
      document.getElementById('locationModal').classList.add('active');
    };

    window.closeLocationModal = function() {
      document.getElementById('locationModal').classList.remove('active');
    };

    window.editLocation = function(id) {
      fetch(`modules/hr_core/api.php?action=getLocationById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const loc = data.data;
            const form = document.getElementById('locationForm');
            
            form.querySelector('input[name="location_name"]').value = loc.location_name || '';
            form.querySelector('select[name="location_type"]').value = loc.location_type || '';
            form.querySelector('input[name="facility_name"]').value = loc.facility_name || '';
            form.querySelector('input[name="city"]').value = loc.city || '';
            form.querySelector('input[name="address"]').value = loc.address || '';
            form.querySelector('input[name="contact_number"]').value = loc.contact_number || '';
            form.querySelector('input[name="email"]').value = loc.email || '';
            form.querySelector('textarea[name="notes"]').value = loc.notes || '';
            
            // Set rule checkboxes
            form.querySelector('input[name="payroll_eligible"]').checked = loc.payroll_eligible === 1 || loc.payroll_eligible === true;
            form.querySelector('input[name="scheduling_enabled"]').checked = loc.scheduling_enabled === 1 || loc.scheduling_enabled === true;
            form.querySelector('input[name="allow_overtime"]').checked = loc.allow_overtime === 1 || loc.allow_overtime === true;
            form.querySelector('input[name="allow_night_shift"]').checked = loc.allow_night_shift === 1 || loc.allow_night_shift === true;
            
            form.dataset.id = id;
            document.querySelector('#locationModal .modal-title').textContent = 'Edit Work Location';
            window.openLocationModal();
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.deleteLocation = function(id) {
      if (confirm('Are you sure you want to delete this location?')) {
        fetch(`modules/hr_core/api.php?action=deleteLocation&id=${id}`, {
          method: 'POST'
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            alert('Location deleted successfully');
            window.loadLocations();
          } else {
            alert('Error: ' + (result.message || 'Could not delete location'));
          }
        })
        .catch(error => console.error('Error:', error));
      }
    };

    window.deactivateLocation = function(id) {
      if (confirm('Deactivate this location? It will no longer be available for scheduling.')) {
        fetch(`modules/hr_core/api.php?action=updateLocation`, {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({id: id, status: 'inactive'})
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            alert('Location deactivated');
            window.loadLocations();
          }
        })
        .catch(error => console.error('Error:', error));
      }
    };

    window.toggleLocFilter = function(filter) {
      const chip = document.querySelector(`.filter-chip[data-filter="${filter}"]`);
      if (chip) {
        chip.classList.toggle('active');
        window.loadLocations();
      }
    };

    window.resetLocFilters = function() {
      document.getElementById('searchInput').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      document.querySelector('.filter-chip[data-filter="active"]').classList.add('active');
      window.loadLocations();
    };

    window.loadLocationsWithoutStaff = function(locations) {
      const list = document.getElementById('noStaffList');
      
      if (!locations || locations.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-lighter); margin: 0;">All locations staffed ✓</p>';
        return;
      }

      list.innerHTML = locations.map(loc => `
        <div style="padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
          <div style="font-weight: 600; color: var(--text-dark);">${loc.location_name}</div>
          <div style="font-size: 11px; color: var(--text-light);">${loc.city}</div>
        </div>
      `).join('');
    };

    window.loadRecentlyAdded = function(locations) {
      const list = document.getElementById('recentlyAddedList');
      
      if (!locations || locations.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-lighter); margin: 0;">No recent additions</p>';
        return;
      }

      list.innerHTML = locations.map(loc => `
        <div style="padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
          <div style="font-weight: 600; color: var(--text-dark);">${loc.location_name}</div>
          <div style="font-size: 11px; color: var(--text-light);">${loc.city}</div>
        </div>
      `).join('');
    };

    function attachEventListeners() {
      // Search
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('input', () => window.loadLocations());
      }

      // Filter chips
      document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', (e) => {
          if (chip.textContent.includes('Reset')) return; // Skip reset button
          e.preventDefault();
          const filter = chip.dataset.filter;
          chip.classList.toggle('active');
          window.loadLocations();
        });
      });

      // Modal close
      const modal = document.getElementById('locationModal');
      if (modal) {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) window.closeLocationModal();
        });
      }

      // Form submission
      const form = document.getElementById('locationForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const id = this.dataset.id || null;
          const formData = new FormData(this);
          const action = id ? 'updateLocation' : 'createLocation';
          
          const data = {
            location_name: formData.get('location_name'),
            location_type: formData.get('location_type'),
            facility_name: formData.get('facility_name'),
            city: formData.get('city'),
            address: formData.get('address'),
            contact_number: formData.get('contact_number'),
            email: formData.get('email'),
            notes: formData.get('notes'),
            payroll_eligible: formData.get('payroll_eligible') ? 1 : 0,
            scheduling_enabled: formData.get('scheduling_enabled') ? 1 : 0,
            allow_overtime: formData.get('allow_overtime') ? 1 : 0,
            allow_night_shift: formData.get('allow_night_shift') ? 1 : 0
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
              alert(result.message || (id ? 'Location updated' : 'Location created'));
              window.closeLocationModal();
              window.loadLocations();
            } else {
              alert('Error: ' + (result.message || 'Unknown error'));
            }
          })
          .catch(error => console.error('Error:', error));
        });
      }
    }

    // Attach listeners on page load
    document.addEventListener('DOMContentLoaded', () => {
      window.loadLocations();
      attachEventListeners();
    });
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
  .loc-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .loc-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .loc-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .loc-right-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  /* KPI Cards */
  .loc-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .loc-kpi-card {
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

  .loc-kpi-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-color: var(--primary);
  }

  .loc-kpi-card .kpi-icon {
    font-size: 32px;
    margin-bottom: 0.5rem;
  }

  .loc-kpi-card .kpi-label {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }

  .loc-kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
  }

  .loc-kpi-card .kpi-subtext {
    font-size: 11px;
    color: var(--text-lighter);
    margin-top: 0.5rem;
  }

  .loc-card-total .kpi-icon { color: #3b82f6; }
  .loc-card-total .kpi-value { color: #3b82f6; }

  .loc-card-active .kpi-icon { color: #22c55e; }
  .loc-card-active .kpi-value { color: #22c55e; }

  .loc-card-staffed .kpi-icon { color: #06b6d4; }
  .loc-card-staffed .kpi-value { color: #06b6d4; }

  .loc-card-unstaffed .kpi-icon { color: #ef4444; }
  .loc-card-unstaffed .kpi-value { color: #ef4444; }

  /* Filters */
  .loc-filters-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .loc-search-bar {
    display: flex;
    gap: 0.75rem;
  }

  .loc-search-bar input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 14px;
    background: white;
    transition: all 0.2s ease;
  }

  .loc-search-bar input:focus {
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
  .loc-table-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
  }

  .loc-table-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .loc-table-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: var(--text-dark);
  }

  .loc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .loc-table thead th {
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

  .loc-table tbody td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }

  .loc-table tbody tr {
    transition: all 0.2s ease;
  }

  .loc-table tbody tr:hover {
    background: var(--bg-lighter);
  }

  .loc-table tbody tr.loc-staff-zero {
    background-color: rgba(239, 68, 68, 0.05);
  }

  .loc-staff-zero {
    font-weight: bold;
    color: var(--danger);
  }

  /* Location Type Badges */
  .loc-type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
  }

  .loc-type-main {
    background: #dbeafe;
    color: #1e40af;
  }

  .loc-type-satellite {
    background: #dcfce7;
    color: #166534;
  }

  .loc-type-office {
    background: #fef3c7;
    color: #92400e;
  }

  .loc-type-training {
    background: #ede9fe;
    color: #6b21a8;
  }

  .loc-type-remote {
    background: #cffafe;
    color: #0e7490;
  }

  .loc-type-other {
    background: #f3f4f6;
    color: #1f2937;
  }

  /* Status Badges */
  .loc-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
  }

  .loc-status-active {
    background: #dcfce7;
    color: #166534;
  }

  .loc-status-inactive {
    background: #f3f4f6;
    color: #9ca3af;
  }

  /* Action Buttons */
  .loc-action-btn {
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

  .loc-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .loc-action-edit:hover { background: var(--primary); color: white; }
  .loc-action-view:hover { background: #06b6d4; color: white; }
  .loc-action-delete:hover { background: var(--danger); color: white; }
  .loc-action-deactivate:hover { background: var(--warning); color: white; }
  .loc-action-more:hover { background: #8b5cf6; color: white; }

  /* Side Panel */
  .loc-side-panel {
    position: sticky;
    top: 20px;
    height: fit-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .loc-side-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .loc-side-card.alert {
    border-color: var(--danger);
    background: #fef2f2;
  }

  .loc-side-subtitle {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
  }

  .loc-side-action {
    display: flex;
    gap: 0.5rem;
  }

  .loc-side-action .btn {
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

  /* Form Checkbox */
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
  }

  .modal.active {
    display: flex;
  }

  .modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.15);
    max-width: 700px;
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
    margin-bottom: 1rem;
  }

  /* Responsive */
  @media (max-width: 1200px) {
    .loc-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .loc-kpi-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  @media (max-width: 768px) {
    .loc-layout {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .loc-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .quick-filter-chips {
      flex-wrap: wrap;
    }

    .filter-chip {
      font-size: 12px;
      padding: 0.4rem 0.8rem;
    }

    .loc-side-panel {
      position: static;
    }

    .loc-table {
      font-size: 11px;
    }

    .loc-table thead th,
    .loc-table tbody td {
      padding: 0.5rem;
    }

    .loc-action-btn {
      width: 24px;
      height: 24px;
      font-size: 12px;
    }

    .loc-table-header {
      padding: 0.75rem;
    }

    .loc-table-title {
      font-size: 14px;
    }
  }

  @media (max-width: 480px) {
    .loc-container {
      padding: 0 0.75rem;
    }

    .loc-kpi-grid {
      grid-template-columns: 1fr;
    }

    .quick-filter-chips {
      gap: 0.5rem;
    }

    .filter-chip {
      font-size: 11px;
      padding: 0.35rem 0.7rem;
    }

    .loc-table {
      font-size: 10px;
    }

    .loc-table thead th,
    .loc-table tbody td {
      padding: 0.4rem;
    }

    .loc-side-card {
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

    .loc-kpi-card {
      padding: 1rem;
    }

    .loc-kpi-card .kpi-icon {
      font-size: 24px;
    }

    .loc-kpi-card .kpi-value {
      font-size: 20px;
    }
  }
</style>
</div>
</main>
