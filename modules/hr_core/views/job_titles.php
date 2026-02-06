
<main class="main-content jobtitle-container">
  <div class="jobtitle-layout">
    <!-- Left Column: Main Content -->
    <div class="jobtitle-left-column">
      <!-- KPI Cards - Vacancy Focus -->
      <section class="jobtitle-kpi-section">
        <div class="jobtitle-kpi-grid">
          <div class="jt-kpi-card jt-card-total">
            <div class="kpi-icon">💼</div>
            <div class="kpi-label">Total Titles</div>
            <h2 class="kpi-value" id="totalTitles">0</h2>
            <p class="kpi-subtext">Active roles</p>
          </div>

          <div class="jt-kpi-card jt-card-vacant">
            <div class="kpi-icon">📢</div>
            <div class="kpi-label">Vacant Positions</div>
            <h2 class="kpi-value" id="vacantPositions">0</h2>
            <p class="kpi-subtext">Open to hire</p>
          </div>

          <div class="jt-kpi-card jt-card-filled">
            <div class="kpi-icon">✓</div>
            <div class="kpi-label">Filled Positions</div>
            <h2 class="kpi-value" id="filledPositions">0</h2>
            <p class="kpi-subtext">Currently assigned</p>
          </div>

          <div class="jt-kpi-card jt-card-missing">
            <div class="kpi-icon">⚠️</div>
            <div class="kpi-label">Missing Salary Range</div>
            <h2 class="kpi-value" id="missingSalary">0</h2>
            <p class="kpi-subtext">Data quality issue</p>
          </div>
        </div>
      </section>

      <!-- Search with Quick Filter Chips -->
      <section class="jobtitle-filters-section">
        <div class="card">
          <div style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <!-- Main Search -->
            <div class="form-group" style="flex: 1; min-width: 250px;">
              <label class="form-label">Search Job Title</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Search titles, department..." style="padding-left: 36px;">
              </div>
            </div>

            <!-- Quick Filter Chips -->
            <div class="quick-filter-chips">
              <button class="filter-chip" data-filter="vacant-only" onclick="window.toggleJTFilter('vacant-only')">
                <span style="color: #ef4444;">📢</span> Vacant Only
              </button>
              <button class="filter-chip" data-filter="filled" onclick="window.toggleJTFilter('filled')">
                <span style="color: #22c55e;">✓</span> Fully Filled
              </button>
              <button class="filter-chip" data-filter="active" onclick="window.toggleJTFilter('active')">
                <span style="color: #3b82f6;">✓</span> Active
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Job Titles Table Section -->
      <section class="jobtitle-table-section">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Job Title Directory</h3>
            <button class="btn btn-outline btn-sm" onclick="window.resetJTFilters()" style="color: var(--text-light);">↻ Reset</button>
          </div>

          <div class="table-container" style="overflow-x: auto;">
            <table class="jobtitle-table">
              <thead class="table-head-sticky">
                <tr>
                  <th style="width: 18%; text-align: left;">Title</th>
                  <th style="width: 12%; text-align: left;">Department</th>
                  <th style="width: 12%; text-align: center;">Total Slots</th>
                  <th style="width: 12%; text-align: center;">Occupied</th>
                  <th style="width: 12%; text-align: center;">Vacant</th>
                  <th style="width: 15%; text-align: right;">Salary Range</th>
                  <th style="width: 10%; text-align: center;">Status</th>
                  <th style="width: 15%; text-align: center;">Actions</th>
                </tr>
              </thead>
              <tbody id="jobTitlesList">
                <tr>
                  <td colspan="8" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">💼</div>
                      <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No job titles found</p>
                      <p style="font-size: 14px; margin-bottom: 1.5rem;">Add job titles to define roles and salary structure.</p>
                      <button class="btn btn-primary" onclick="window.openJobTitleModal()">+ Add Job Title</button>
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
    <aside class="jobtitle-right-column">
      <div class="jt-side-panel">
        <!-- Quick Actions -->
        <div class="jt-side-card">
          <div class="jt-side-action">
            <button class="btn btn-primary" style="flex: 1;" onclick="window.openJobTitleModal()">+ Add Job Title</button>
          </div>
        </div>

        <!-- Vacant Roles -->
        <div class="jt-side-card urgent">
          <p class="jt-side-subtitle" style="color: #ef4444;">🚨 Vacant Roles</p>
          <div id="vacantRolesList" style="max-height: 150px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>

        <!-- Missing Salary Ranges -->
        <div class="jt-side-card warning">
          <p class="jt-side-subtitle" style="color: #f59e0b;">⚠️ Missing Salary Ranges</p>
          <div id="missingSalaryList" style="max-height: 140px; overflow-y: auto;">
            <p style="font-size: 12px; color: #22c55e; text-align: center; padding: 1rem;">✓ All configured</p>
          </div>
        </div>

        <!-- Recently Added -->
        <div class="jt-side-card">
          <p class="jt-side-subtitle">Recently Added</p>
          <div id="recentlyAddedList" style="max-height: 140px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">None yet</p>
          </div>
        </div>
      </div>
    </aside>
  </div>
</main>

<!-- Add/Edit Job Title Modal -->
<div id="jobTitleModal" class="modal">
  <div class="modal-content" style="max-width: 700px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Job Title</h2>
      <button class="modal-close" onclick="window.closeJobTitleModal()">&times;</button>
    </div>

    <form id="jobTitleForm">
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Job Title *</label>
          <input type="text" name="title" class="form-input" required placeholder="e.g., Senior Nurse">
        </div>
        <div class="form-group">
          <label class="form-label">Department *</label>
          <select name="department" class="form-select" required>
            <option value="">Select Department</option>
            <option value="clinical">Clinical</option>
            <option value="support">Support Services</option>
            <option value="admin">Administration</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Total Positions *</label>
          <input type="number" name="total_positions" class="form-input" min="1" required placeholder="e.g., 5">
        </div>
        <div class="form-group">
          <label class="form-label">Salary Grade</label>
          <input type="text" name="salary_grade" class="form-input" placeholder="e.g., Grade 5">
        </div>

        <div class="form-group">
          <label class="form-label">Minimum Salary (₱)</label>
          <input type="number" name="min_salary" class="form-input" placeholder="Minimum salary">
        </div>
        <div class="form-group">
          <label class="form-label">Maximum Salary (₱)</label>
          <input type="number" name="max_salary" class="form-input" placeholder="Maximum salary">
        </div>

        <!-- Hospital Specific Fields -->
        <div class="form-group">
          <label class="form-label">
            <input type="checkbox" name="license_required" style="margin-right: 0.5rem;">
            License/Certification Required
          </label>
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select name="job_type" class="form-select">
            <option value="clinical">Clinical</option>
            <option value="non-clinical">Non-Clinical</option>
            <option value="support">Support</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Shift Dependent</label>
          <select name="shift_dependent" class="form-select">
            <option value="no">No</option>
            <option value="yes">Yes</option>
          </select>
        </div>

        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-input" placeholder="Job description and responsibilities..." rows="3"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="window.closeJobTitleModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Job Title</button>
      </div>
    </form>
  </div>
</div>



<script>
  (function() {
    window.loadJobTitles = function() {
      const searchQuery = document.getElementById('searchInput')?.value || '';
      
      // Get active filters
      const activeChips = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.filter)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        filter: activeChips
      });

      fetch(`modules/hr_core/api.php?action=getJobTitles&${params}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data && data.data.job_titles) {
            const titles = data.data.job_titles;
            window.displayJobTitles(titles);
            
            // Calculate and update stats
            const total = titles.length;
            document.getElementById('totalTitles').textContent = total;
            document.getElementById('vacantPositions').textContent = 0;
            document.getElementById('filledPositions').textContent = 0;
            document.getElementById('missingSalary').textContent = 0;
          }
        })
        .catch(error => console.error('Error loading job titles:', error));
    };

    window.displayJobTitles = function(titles) {
      const tbody = document.getElementById('jobTitlesList');
      tbody.innerHTML = '';

      if (!titles || titles.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">💼</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No job titles found</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Add job titles to define roles and salary structure.</p>
                <button class="btn btn-primary" onclick="window.openJobTitleModal()">+ Add Job Title</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      titles.forEach(title => {
        const totalSlots = title.total_positions || 0;
        const occupied = title.filled_count || 0;
        const vacant = totalSlots - occupied;
        const isFullyVacant = occupied === 0 && totalSlots > 0;
        const isFullyFilled = vacant === 0 && totalSlots > 0;
        
        // Row emphasis based on vacancy
        const rowClass = isFullyVacant ? 'jt-row-fully-vacant' : (vacant > 0 ? 'jt-row-partial-vacant' : '');
        
        // Salary range with monospace formatting
        let salaryDisplay = '-';
        if (title.min_salary || title.max_salary) {
          const min = title.min_salary ? title.min_salary.toLocaleString() : '-';
          const max = title.max_salary ? title.max_salary.toLocaleString() : '-';
          salaryDisplay = `<code style="font-family: 'Courier New', monospace; font-size: 12px; letter-spacing: 0.5px;">₱${min} - ₱${max}</code>`;
        }

        // Vacancy badge
        let vacancyBadge = '';
        if (vacant > 0) {
          if (isFullyVacant) {
            vacancyBadge = `<span class="jt-badge jt-badge-fully-vacant">${vacant} / ${totalSlots}</span>`;
          } else {
            vacancyBadge = `<span class="jt-badge jt-badge-partial-vacant">${occupied} / ${totalSlots}</span>`;
          }
        } else {
          vacancyBadge = `<span class="jt-badge jt-badge-filled">${occupied} / ${totalSlots}</span>`;
        }

        // Status badge
        const statusBadge = title.status === 'active' ? 'jt-status-active' : 'jt-status-inactive';

        // Context-aware actions
        let actionButtons = '';
        if (vacant > 0) {
          actionButtons = `
            <button class="jt-action-btn jt-action-hire" onclick="window.editJobTitle(${title.id})" title="Assign/Hire">💼</button>
            <button class="jt-action-btn jt-action-edit" onclick="window.editJobTitle(${title.id})" title="Edit">✏</button>
            <button class="jt-action-btn jt-action-more" onclick="alert('More options')" title="More">⋯</button>
          `;
        } else {
          actionButtons = `
            <button class="jt-action-btn jt-action-view" onclick="window.viewJobTitle(${title.id})" title="View">👁</button>
            <button class="jt-action-btn jt-action-edit" onclick="window.editJobTitle(${title.id})" title="Edit">✏</button>
            <button class="jt-action-btn jt-action-more" onclick="alert('More options')" title="More">⋯</button>
          `;
        }

        const row = `
          <tr class="${rowClass}">
            <td style="font-weight: 600; color: var(--text-dark);">${title.title || '-'}</td>
            <td style="font-size: 13px; color: var(--text-light);">${title.department || '-'}</td>
            <td style="text-align: center; font-weight: 600;">${totalSlots}</td>
            <td style="text-align: center; font-weight: 600; color: #22c55e;">${occupied}</td>
            <td style="text-align: center;">
              ${vacancyBadge}
            </td>
            <td style="text-align: right;">
              ${salaryDisplay}
            </td>
            <td style="text-align: center;">
              <span class="jt-status-badge ${statusBadge}">
                ${title.status === 'active' ? '✓' : '✕'} ${title.status || 'Active'}
              </span>
            </td>
            <td style="text-align: center;">
              <div style="display: flex; gap: 0.35rem; justify-content: center;">
                ${actionButtons}
              </div>
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    };

    window.openJobTitleModal = function() {
      document.getElementById('jobTitleForm').reset();
      delete document.getElementById('jobTitleForm').dataset.id;
      document.querySelector('#jobTitleModal .modal-title').textContent = 'Add Job Title';
      document.getElementById('jobTitleModal').classList.add('active');
    };

    window.closeJobTitleModal = function() {
      document.getElementById('jobTitleModal').classList.remove('active');
    };

    window.toggleJTFilter = function(filter) {
      const chip = document.querySelector(`.filter-chip[data-filter="${filter}"]`);
      chip.classList.toggle('active');
      window.loadJobTitles();
    };

    window.resetJTFilters = function() {
      document.getElementById('searchInput').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      window.loadJobTitles();
    };

    window.editJobTitle = function(id) {
      fetch(`modules/hr_core/api.php?action=getJobTitleById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const title = data.data;
            const form = document.getElementById('jobTitleForm');
            if (form) {
              form.querySelector('input[name="title"]').value = title.title || '';
              form.querySelector('select[name="department"]').value = title.department || '';
              form.querySelector('input[name="total_positions"]').value = title.total_positions || '';
              form.querySelector('input[name="salary_grade"]').value = title.salary_grade || '';
              form.querySelector('input[name="min_salary"]').value = title.min_salary || '';
              form.querySelector('input[name="max_salary"]').value = title.max_salary || '';
              form.querySelector('input[name="license_required"]').checked = title.license_required || false;
              form.querySelector('select[name="job_type"]').value = title.job_type || 'non-clinical';
              form.querySelector('select[name="shift_dependent"]').value = title.shift_dependent || 'no';
              form.querySelector('textarea[name="description"]').value = title.description || '';
              form.dataset.id = id;
              document.querySelector('#jobTitleModal .modal-title').textContent = 'Edit Job Title';
              window.openJobTitleModal();
            }
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.viewJobTitle = function(id) {
      fetch(`modules/hr_core/api.php?action=getJobTitleById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const title = data.data;
            const salaryRange = title.min_salary || title.max_salary ? `₱${title.min_salary || '-'} - ₱${title.max_salary || '-'}` : 'Not set';
            alert(`Title: ${title.title}\nDepartment: ${title.department}\nPositions: ${title.total_positions}\nFilled: ${title.filled_count}\nSalary: ${salaryRange}\nDescription: ${title.description || 'None'}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.loadVacantRoles = function(roles) {
      const list = document.getElementById('vacantRolesList');
      if (!roles || roles.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: #22c55e; text-align: center; padding: 1rem;">✓ No vacancies</p>';
        return;
      }
      list.innerHTML = roles.slice(0, 5).map(role => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px; cursor: pointer;" onclick="window.editJobTitle(${role.id})">
          <div style="font-weight: 600; color: var(--text-dark);">${role.title}</div>
          <div style="color: var(--text-light); font-size: 11px; margin-top: 0.25rem;">${role.vacant_count} open • ${role.department}</div>
        </div>
      `).join('');
    };

    window.loadMissingSalaryRanges = function(items) {
      const list = document.getElementById('missingSalaryList');
      if (!items || items.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: #22c55e; text-align: center; padding: 1rem;">✓ All configured</p>';
        return;
      }
      list.innerHTML = items.slice(0, 5).map(item => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px; cursor: pointer;" onclick="window.editJobTitle(${item.id})">
          <div style="font-weight: 600; color: var(--text-dark);">${item.title}</div>
          <div style="color: var(--text-light); font-size: 11px; margin-top: 0.25rem;">Missing min/max salary</div>
        </div>
      `).join('');
    };

    window.loadRecentlyAddedTitles = function(titles) {
      const list = document.getElementById('recentlyAddedList');
      if (!titles || titles.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">None yet</p>';
        return;
      }
      list.innerHTML = titles.slice(0, 5).map(title => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px;">
          <div style="font-weight: 600; color: var(--text-dark);">${title.title}</div>
          <div style="color: var(--text-light); font-size: 11px; margin-top: 0.25rem;">${title.total_positions} positions • ${title.department}</div>
        </div>
      `).join('');
    };

    function attachEventListeners() {
      // Search
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('input', () => window.loadJobTitles());
      }

      // Filter chips
      document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', (e) => {
          e.preventDefault();
          const filter = chip.dataset.filter;
          chip.classList.toggle('active');
          window.loadJobTitles();
        });
      });

      // Modal close
      const modal = document.getElementById('jobTitleModal');
      if (modal) {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) window.closeJobTitleModal();
        });
      }

      // Form submission
      const form = document.getElementById('jobTitleForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const id = this.dataset.id || null;
          const formData = new FormData(this);
          const action = id ? 'updateJobTitle' : 'createJobTitle';
          const data = {
            title: formData.get('title'),
            department: formData.get('department'),
            total_positions: formData.get('total_positions'),
            salary_grade: formData.get('salary_grade'),
            min_salary: formData.get('min_salary'),
            max_salary: formData.get('max_salary'),
            license_required: formData.get('license_required') ? 1 : 0,
            job_type: formData.get('job_type'),
            shift_dependent: formData.get('shift_dependent'),
            description: formData.get('description')
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
              alert(result.message || (id ? 'Job title updated' : 'Job title created'));
              window.closeJobTitleModal();
              window.loadJobTitles();
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
      window.loadJobTitles();
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
  .jobtitle-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .jobtitle-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .jobtitle-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .jobtitle-right-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  /* KPI Cards */
  .jobtitle-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .jt-kpi-card {
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

  .jt-kpi-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-color: var(--primary);
  }

  .jt-kpi-card .kpi-icon {
    font-size: 32px;
    margin-bottom: 0.5rem;
  }

  .jt-kpi-card .kpi-label {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }

  .jt-kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
  }

  .jt-kpi-card .kpi-subtext {
    font-size: 11px;
    color: var(--text-lighter);
    margin-top: 0.5rem;
  }

  .jt-card-total .kpi-icon { color: var(--text-dark); }
  .jt-card-total .kpi-value { color: var(--text-dark); }

  .jt-card-vacant {
    border: 2px solid var(--danger);
    background: linear-gradient(135deg, #fef2f2 0%, white 100%);
  }
  .jt-card-vacant .kpi-icon { color: var(--danger); font-size: 36px; }
  .jt-card-vacant .kpi-value { color: var(--danger); font-size: 32px; font-weight: 800; }
  .jt-card-vacant .kpi-label { color: var(--danger); }

  .jt-card-filled .kpi-icon { color: var(--success); }
  .jt-card-filled .kpi-value { color: var(--success); }

  .jt-card-missing {
    border: 1px solid var(--warning);
    background: #fffbeb;
  }
  .jt-card-missing .kpi-icon { color: var(--warning); }
  .jt-card-missing .kpi-value { color: var(--warning); }
  .jt-card-missing .kpi-label { color: var(--warning); }

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
  .jobtitle-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
  }

  .jobtitle-table thead th {
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

  .jobtitle-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }

  .jobtitle-table tbody tr {
    transition: all 0.2s ease;
  }

  .jobtitle-table tbody tr:hover {
    background: var(--bg-lighter);
  }

  /* Row Emphasis */
  .jt-row-fully-vacant {
    background: #fef2f2;
    border-left: 3px solid var(--danger);
  }

  .jt-row-fully-vacant:hover {
    background: #fee2e2;
  }

  .jt-row-partial-vacant {
    background: #fffbeb;
  }

  .jt-row-partial-vacant:hover {
    background: #fef3c7;
  }

  /* Vacancy Badges */
  .jt-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
  }

  .jt-badge-filled {
    background: #dcfce7;
    color: #166534;
  }

  .jt-badge-partial-vacant {
    background: #fef3c7;
    color: #92400e;
  }

  .jt-badge-fully-vacant {
    background: #fee2e2;
    color: #991b1b;
  }

  /* Status Badges */
  .jt-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
  }

  .jt-status-active {
    background: #dcfce7;
    color: #166534;
  }

  .jt-status-inactive {
    background: #f3f4f6;
    color: #9ca3af;
  }

  /* Action Buttons */
  .jt-action-btn {
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

  .jt-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .jt-action-hire:hover { background: var(--danger); color: white; }
  .jt-action-edit:hover { background: var(--primary); color: white; }
  .jt-action-view:hover { background: #06b6d4; color: white; }
  .jt-action-more:hover { background: #8b5cf6; color: white; }

  /* Side Panel */
  .jt-side-panel {
    position: sticky;
    top: 20px;
    height: fit-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .jt-side-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .jt-side-card.urgent {
    border-color: var(--danger);
    background: #fef2f2;
  }

  .jt-side-card.warning {
    border-color: var(--warning);
    background: #fffbeb;
  }

  .jt-side-subtitle {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    margin-bottom: 0.75rem;
  }

  .jt-side-action {
    display: flex;
    gap: 0.5rem;
  }

  .jt-side-action .btn {
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
    .jobtitle-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .jobtitle-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .jobtitle-layout {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .jobtitle-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
    }

    .quick-filter-chips {
      flex-wrap: wrap;
    }

    .filter-chip {
      font-size: 12px;
      padding: 0.4rem 0.8rem;
    }

    .jt-side-panel {
      position: static;
    }

    .jobtitle-table {
      font-size: 12px;
    }

    .jobtitle-table thead th,
    .jobtitle-table tbody td {
      padding: 0.75rem;
    }

    .jt-action-btn {
      width: 28px;
      height: 28px;
      font-size: 14px;
    }
  }

  @media (max-width: 480px) {
    .jobtitle-container {
      padding: 0 0.75rem;
    }

    .jobtitle-kpi-grid {
      grid-template-columns: 1fr;
    }

    .quick-filter-chips {
      gap: 0.5rem;
    }

    .filter-chip {
      font-size: 11px;
      padding: 0.35rem 0.7rem;
    }

    .jobtitle-table {
      font-size: 11px;
    }

    .jobtitle-table thead th,
    .jobtitle-table tbody td {
      padding: 0.5rem;
    }

    .jt-side-card {
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

  code {
    background: var(--bg-light);
    padding: 2px 4px;
    border-radius: 3px;
  }
</style>
