
<main class="main-content onboarding-container">
  <div class="onboarding-layout">
    <!-- Left Column: Main Content -->
    <div class="onboarding-left-column">
      <!-- KPI Cards - Progress Focus -->
      <section class="onboarding-kpi-section">
        <div class="onboarding-kpi-grid">
          <div class="onb-kpi-card onb-card-total">
            <div class="kpi-icon">📋</div>
            <div class="kpi-label">Total Onboarding</div>
            <h2 class="kpi-value" id="totalOnboard">0</h2>
            <p class="kpi-subtext">All records</p>
          </div>

          <div class="onb-kpi-card onb-card-active">
            <div class="kpi-icon">⏳</div>
            <div class="kpi-label">In Progress</div>
            <h2 class="kpi-value" id="inProgressOnboard">0</h2>
            <p class="kpi-subtext">Active onboarding</p>
          </div>

          <div class="onb-kpi-card onb-card-completed">
            <div class="kpi-icon">✓</div>
            <div class="kpi-label">Completed</div>
            <h2 class="kpi-value" id="completedOnboard">0</h2>
            <p class="kpi-subtext">Finished</p>
          </div>

          <div class="onb-kpi-card onb-card-overdue">
            <div class="kpi-icon">⚠️</div>
            <div class="kpi-label">Overdue</div>
            <h2 class="kpi-value" id="overdueOnboard">0</h2>
            <p class="kpi-subtext">Past deadline</p>
          </div>
        </div>
      </section>

      <!-- Search with Quick Filter Chips -->
      <section class="onboarding-filters-section">
        <div class="card">
          <div style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <!-- Main Search -->
            <div class="form-group" style="flex: 1; min-width: 250px;">
              <label class="form-label">Search</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Search employees, department..." style="padding-left: 36px;">
              </div>
            </div>

            <!-- Quick Filter Chips -->
            <div class="quick-filter-chips">
              <button class="filter-chip" data-filter="in-progress" onclick="window.toggleOnboardingFilter('in-progress')">
                <span style="color: #f59e0b;">⏳</span> In Progress
              </button>
              <button class="filter-chip" data-filter="completed" onclick="window.toggleOnboardingFilter('completed')">
                <span style="color: #22c55e;">✓</span> Completed
              </button>
              <button class="filter-chip" data-filter="overdue" onclick="window.toggleOnboardingFilter('overdue')">
                <span style="color: #ef4444;">⚠️</span> Overdue
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Onboarding Table Section -->
      <section class="onboarding-table-section">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Onboarding Progress</h3>
            <button class="btn btn-outline btn-sm" onclick="window.resetOnboardingFilters()" style="color: var(--text-light);">↻ Reset</button>
          </div>

          <div class="table-container" style="overflow-x: auto;">
            <table class="onboarding-table">
              <thead class="table-head-sticky">
                <tr>
                  <th style="width: 18%; text-align: left;">Employee</th>
                  <th style="width: 12%; text-align: left;">Department</th>
                  <th style="width: 10%; text-align: center;">Start Date</th>
                  <th style="width: 25%; text-align: left;">Progress</th>
                  <th style="width: 12%; text-align: left;">Status</th>
                  <th style="width: 8%; text-align: center;">Days</th>
                  <th style="width: 15%; text-align: center;">Actions</th>
                </tr>
              </thead>
              <tbody id="onboardingList">
                <tr>
                  <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">👋</div>
                      <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No onboarding records yet</p>
                      <p style="font-size: 14px; margin-bottom: 1.5rem;">Start onboarding to prepare new hires for their first day.</p>
                      <button class="btn btn-primary" onclick="window.openOnboardingModal()">+ Add Onboarding</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Right Column: Workflow Panel -->
    <aside class="onboarding-right-column">
      <div class="onb-side-panel">
        <!-- Quick Actions -->
        <div class="onb-side-card">
          <div class="onb-side-action">
            <button class="btn btn-primary" style="flex: 1;" onclick="window.openOnboardingModal()">+ Add Onboarding</button>
          </div>
        </div>

        <!-- Employees Starting This Week -->
        <div class="onb-side-card">
          <p class="onb-side-subtitle">Starting This Week</p>
          <div id="startingThisWeekList" style="max-height: 150px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>

        <!-- Overdue Tasks Alert -->
        <div class="onb-side-card urgent">
          <p class="onb-side-subtitle" style="color: #ef4444;">⚠️ Overdue Tasks</p>
          <div style="padding: 0.75rem; border-radius: 6px; background: white;">
            <div style="font-size: 24px; font-weight: 700; color: #ef4444;" id="overdueCount">0</div>
            <div style="font-size: 12px; color: var(--text-light); margin-top: 0.25rem;">Tasks past deadline</div>
          </div>
          <button class="btn btn-primary" style="width: 100%; margin-top: 0.75rem; font-size: 12px;" onclick="document.querySelector('.filter-chip[data-filter=\"overdue\"]').click(); document.querySelector('.filter-chip[data-filter=\"overdue\"]').classList.add('active'); window.loadOnboarding();">View Overdue</button>
        </div>

        <!-- Missing Requirements -->
        <div class="onb-side-card">
          <p class="onb-side-subtitle">Missing Requirements</p>
          <div id="missingRequirementsList" style="max-height: 140px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">None pending</p>
          </div>
        </div>

        <!-- Recently Completed -->
        <div class="onb-side-card">
          <p class="onb-side-subtitle">Recently Completed</p>
          <div id="recentlyCompletedList" style="max-height: 140px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">None yet</p>
          </div>
        </div>
      </div>
    </aside>
  </div>
</main>

<!-- Onboarding Modal -->
<div id="onboardingModal" class="modal">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Onboarding</h2>
      <button class="modal-close" onclick="window.closeOnboardingModal()">&times;</button>
    </div>
    <form id="onboardingForm">
      <div class="form-group">
        <label class="form-label">Employee *</label>
        <select name="employee_id" class="form-select" required>
          <option value="">Select employee...</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Department *</label>
        <input type="text" name="department" class="form-input" placeholder="Department assignment..." required>
      </div>
      <div class="form-group">
        <label class="form-label">Start Date *</label>
        <input type="date" name="start_date" class="form-input" required>
      </div>
      <div class="form-group">
        <label class="form-label">Status *</label>
        <select name="status" class="form-select" required>
          <option value="In Progress">In Progress</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">HR Owner / Mentor</label>
        <input type="text" name="assigned_mentor" class="form-input" placeholder="Name of mentor or HR owner...">
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-input" placeholder="Onboarding notes..." rows="3"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="window.closeOnboardingModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>



<script>
  (function() {
    window.loadOnboarding = function() {
      const searchQuery = document.getElementById('searchInput')?.value || '';
      
      // Get active filters
      const activeChips = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.filter)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        filter: activeChips
      });

      fetch(`modules/hr_core/api.php?action=getOnboarding&${params}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data && data.data.onboarding) {
            window.displayOnboarding(data.data.onboarding);
            if (data.data.stats) {
              document.getElementById('totalOnboard').textContent = data.data.stats.total || 0;
              document.getElementById('inProgressOnboard').textContent = data.data.stats.in_progress || 0;
              document.getElementById('completedOnboard').textContent = data.data.stats.completed || 0;
              document.getElementById('overdueOnboard').textContent = data.data.stats.overdue || 0;
            }
            // Populate side panels
            window.loadStartingThisWeek(data.data.starting_this_week || []);
            window.loadMissingRequirements(data.data.missing_requirements || []);
            window.loadRecentlyCompleted(data.data.recently_completed || []);
            // Update overdue count
            document.getElementById('overdueCount').textContent = data.data.stats?.overdue || 0;
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.displayOnboarding = function(onboarding) {
      const tbody = document.getElementById('onboardingList');
      tbody.innerHTML = '';

      if (!onboarding || onboarding.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">👋</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No onboarding records yet</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Start onboarding to prepare new hires for their first day.</p>
                <button class="btn btn-primary" onclick="window.openOnboardingModal()">+ Add Onboarding</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      onboarding.forEach(record => {
        // Determine status and styling
        const status = record.status || 'Pending';
        let statusBadge = '';
        let rowClass = '';
        
        if (status === 'Completed') {
          statusBadge = '✓ Completed';
          rowClass = 'onb-row-completed';
        } else if (status === 'In Progress') {
          statusBadge = '⏳ In Progress';
          rowClass = 'onb-row-in-progress';
        } else if (status === 'Overdue') {
          statusBadge = '⚠️ Overdue';
          rowClass = 'onb-row-overdue';
        } else {
          statusBadge = '⏳ Pending';
          rowClass = '';
        }

        const row = `
          <tr class="${rowClass}" style="border-left: 4px solid ${status === 'Completed' ? '#10b981' : status === 'Overdue' ? '#ef4444' : '#f59e0b'};">
            <td style="text-align: left; font-weight: 500;">${record.employee_name || '-'}</td>
            <td style="text-align: left;">${record.checklist_item || '-'}</td>
            <td style="text-align: center;">${record.responsible_department || '-'}</td>
            <td style="text-align: left; font-size: 13px;">${record.due_date || '-'}</td>
            <td style="text-align: center;">
              <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; background: ${status === 'Completed' ? '#d1fae5' : status === 'Overdue' ? '#fee2e2' : '#fef3c7'}; color: ${status === 'Completed' ? '#065f46' : status === 'Overdue' ? '#7f1d1d' : '#92400e'};">
                ${statusBadge}
              </span>
            </td>
            <td style="text-align: left; font-size: 13px; color: var(--text-light);">${record.remarks || '-'}</td>
            <td style="text-align: center;">
              <button class="action-btn action-btn-primary" onclick="window.viewOnboarding(${record.id})" title="View">👁 View</button>
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    };

    window.openOnboardingModal = function() {
      document.getElementById('onboardingForm').reset();
      delete document.getElementById('onboardingForm').dataset.id;
      document.querySelector('#onboardingModal .modal-title').textContent = 'Add Onboarding';
      document.getElementById('onboardingModal').classList.add('active');
    };

    window.closeOnboardingModal = function() {
      document.getElementById('onboardingModal').classList.remove('active');
    };

    window.toggleOnboardingFilter = function(filter) {
      const chip = document.querySelector(`.filter-chip[data-filter="${filter}"]`);
      chip.classList.toggle('active');
      window.loadOnboarding();
    };

    window.resetOnboardingFilters = function() {
      document.getElementById('searchInput').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      window.loadOnboarding();
    };

    window.editOnboarding = function(id) {
      fetch(`modules/hr_core/api.php?action=getOnboardingById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const record = data.data;
            const form = document.getElementById('onboardingForm');
            if (form) {
              form.querySelector('select[name="employee_id"]').value = record.employee_id || '';
              form.querySelector('input[name="department"]').value = record.department || '';
              form.querySelector('input[name="start_date"]').value = record.start_date || '';
              form.querySelector('select[name="status"]').value = record.status || 'In Progress';
              form.querySelector('input[name="assigned_mentor"]').value = record.assigned_mentor || '';
              form.querySelector('textarea[name="notes"]').value = record.notes || '';
              form.dataset.id = id;
              document.querySelector('#onboardingModal .modal-title').textContent = 'Edit Onboarding';
              window.openOnboardingModal();
            }
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.viewOnboarding = function(id) {
      fetch(`modules/hr_core/api.php?action=getOnboardingById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const record = data.data;
            alert(`Employee: ${record.employee_name}\nDepartment: ${record.department}\nStart Date: ${record.start_date}\nStatus: ${record.status}\nProgress: ${record.completion_percentage || 0}%\nNotes: ${record.notes || 'None'}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.loadStartingThisWeek = function(employees) {
      const list = document.getElementById('startingThisWeekList');
      if (!list) return; // Element might not be in DOM
      
      if (!employees || employees.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: #22c55e; text-align: center; padding: 1rem;">✓ None this week</p>';
        return;
      }
      list.innerHTML = employees.slice(0, 5).map(emp => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px; cursor: pointer;" onclick="window.editOnboarding(${emp.id})">
          <div style="font-weight: 600; color: var(--text-dark);">${emp.employee_name}</div>
          <div style="color: var(--text-light); font-size: 11px; margin-top: 0.25rem;">Starts ${new Date(emp.start_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})}</div>
        </div>
      `).join('');
    };

    window.loadMissingRequirements = function(requirements) {
      const list = document.getElementById('missingRequirementsList');
      if (!list) return; // Element might not be in DOM
      
      if (!requirements || requirements.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: #22c55e; text-align: center; padding: 1rem;">✓ All set</p>';
        return;
      }
      list.innerHTML = requirements.slice(0, 5).map(req => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px;">
          <div style="font-weight: 600; color: var(--text-dark);">${req.checklist_item || req.requirement}</div>
          <div style="color: var(--text-light); font-size: 11px; margin-top: 0.25rem;">${req.employee_name} • ${req.responsible_department || 'HR'}</div>
        </div>
      `).join('');
    };

    window.loadRecentlyCompleted = function(completed) {
      const list = document.getElementById('recentlyCompletedList');
      if (!list) return; // Element might not be in DOM
      
      if (!completed || completed.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">None yet</p>';
        return;
      }
      list.innerHTML = completed.slice(0, 5).map(record => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px;">
          <div style="font-weight: 600; color: var(--text-dark);">${record.employee_name}</div>
          <div style="color: var(--text-light); font-size: 11px; margin-top: 0.25rem;">Completed on ${new Date(record.completed_date || record.updated_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})}</div>
        </div>
      `).join('');
    };

    function attachEventListeners() {
      // Search
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('input', () => window.loadOnboarding());
      }

      // Filter chips
      document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', (e) => {
          e.preventDefault();
          const filter = chip.dataset.filter;
          chip.classList.toggle('active');
          window.loadOnboarding();
        });
      });

      // Modal close
      const modal = document.getElementById('onboardingModal');
      if (modal) {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) window.closeOnboardingModal();
        });
      }

      // Form submission
      const form = document.getElementById('onboardingForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const id = this.dataset.id || null;
          const formData = new FormData(this);
          const action = id ? 'updateOnboarding' : 'createOnboarding';
          const data = {
            employee_id: formData.get('employee_id'),
            department: formData.get('department'),
            start_date: formData.get('start_date'),
            status: formData.get('status'),
            assigned_mentor: formData.get('assigned_mentor'),
            notes: formData.get('notes')
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
              alert(result.message || (id ? 'Onboarding updated' : 'Onboarding created'));
              window.closeOnboardingModal();
              window.loadOnboarding();
            } else {
              alert('Error: ' + (result.message || 'Unknown error'));
            }
          })
          .catch(error => console.error('Error:', error));
        });
      }

      // Initial load
      if (!window.onboardingLoadedOnce) {
        window.onboardingLoadedOnce = true;
        window.loadOnboarding();
      }
    }

    // Attach listeners - will be called when module loads
    attachEventListeners();

    // Reattach listeners after dynamic updates
    const originalLoadOnboarding = window.loadOnboarding;
    window.loadOnboarding = function() {
      originalLoadOnboarding();
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
  .onboarding-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .onboarding-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .onboarding-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .onboarding-right-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  /* KPI Cards */
  .onboarding-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .onb-kpi-card {
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

  .onb-kpi-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-color: var(--primary);
  }

  .onb-kpi-card .kpi-icon {
    font-size: 32px;
    margin-bottom: 0.5rem;
  }

  .onb-kpi-card .kpi-label {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }

  .onb-kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
  }

  .onb-kpi-card .kpi-subtext {
    font-size: 11px;
    color: var(--text-lighter);
    margin-top: 0.5rem;
  }

  .onb-card-total .kpi-icon { color: var(--text-dark); }
  .onb-card-total .kpi-value { color: var(--text-dark); }

  .onb-card-active {
    border: 2px solid var(--warning);
    background: linear-gradient(135deg, #fffbeb 0%, white 100%);
  }
  .onb-card-active .kpi-icon { color: var(--warning); font-size: 36px; }
  .onb-card-active .kpi-value { color: var(--warning); font-size: 32px; font-weight: 800; }
  .onb-card-active .kpi-label { color: var(--warning); }

  .onb-card-completed .kpi-icon { color: var(--success); }
  .onb-card-completed .kpi-value { color: var(--success); }

  .onb-card-overdue {
    border: 2px solid var(--danger);
    background: #fef2f2;
  }
  .onb-card-overdue .kpi-icon { color: var(--danger); }
  .onb-card-overdue .kpi-value { color: var(--danger); }
  .onb-card-overdue .kpi-label { color: var(--danger); }

  /* Filters */
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

  /* Search Bar */
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

  /* Table */
  .onboarding-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  .onboarding-table thead th {
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

  .onboarding-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }

  .onboarding-table tbody tr {
    transition: all 0.2s ease;
  }

  .onboarding-table tbody tr:hover {
    background: var(--bg-lighter);
  }

  /* Row Emphasis */
  .onb-row-overdue {
    background: #fef2f2;
    border-left: 3px solid var(--danger);
  }

  .onb-row-overdue:hover {
    background: #fee2e2;
  }

  .onb-row-completed {
    opacity: 0.7;
    background: var(--bg-light);
  }

  .onb-row-completed:hover {
    opacity: 0.85;
  }

  /* Status Badges */
  .onb-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
  }

  .onb-badge-in-progress {
    background: #fef3c7;
    color: #92400e;
  }

  .onb-badge-completed {
    background: #dcfce7;
    color: #166534;
  }

  .onb-badge-overdue {
    background: #fee2e2;
    color: #991b1b;
  }

  /* Action Buttons */
  .onb-action-btn {
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

  .onb-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .onb-action-continue:hover { background: var(--primary); color: white; }
  .onb-action-view:hover { background: #06b6d4; color: white; }
  .onb-action-resolve:hover { background: var(--success); color: white; }
  .onb-action-more:hover { background: #8b5cf6; color: white; }

  /* Side Panel */
  .onb-side-panel {
    position: sticky;
    top: 20px;
    height: fit-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .onb-side-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .onb-side-card.urgent {
    border-color: var(--danger);
    background: #fef2f2;
  }

  .onb-side-subtitle {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
  }

  .onb-side-action {
    display: flex;
    gap: 0.5rem;
  }

  .onb-side-action .btn {
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

  /* Forms */
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

  textarea.form-input {
    resize: vertical;
    min-height: 80px;
  }

  /* Responsive */
  @media (max-width: 1200px) {
    .onboarding-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .onboarding-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .onboarding-layout {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .onboarding-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .quick-filter-chips {
      flex-wrap: wrap;
    }

    .filter-chip {
      font-size: 12px;
      padding: 0.4rem 0.8rem;
    }

    .onb-side-panel {
      position: static;
    }

    .onboarding-table {
      font-size: 12px;
    }

    .onboarding-table thead th,
    .onboarding-table tbody td {
      padding: 0.75rem;
    }

    .onb-action-btn {
      width: 28px;
      height: 28px;
      font-size: 14px;
    }
  }

  @media (max-width: 480px) {
    .onboarding-container {
      padding: 0 0.75rem;
    }

    .onboarding-kpi-grid {
      grid-template-columns: 1fr;
    }

    .quick-filter-chips {
      gap: 0.5rem;
    }

    .filter-chip {
      font-size: 11px;
      padding: 0.35rem 0.7rem;
    }

    .onboarding-table {
      font-size: 11px;
    }

    .onboarding-table thead th,
    .onboarding-table tbody td {
      padding: 0.5rem;
    }

    .onb-side-card {
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
</style>
