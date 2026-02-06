
<main class="main-content movements-container">
  <div class="movements-layout">
    <!-- Left Column: Main Content -->
    <div class="movements-left-column">
      <!-- KPI Cards with Status Emphasis -->
      <section class="movement-kpi-section">
        <div class="movement-kpi-grid">
          <div class="move-card move-card-approved">
            <div class="move-card-icon">✓</div>
            <div class="move-card-content">
              <p class="move-card-label">Approved</p>
              <h2 class="move-card-value" id="approvedMovements">0</h2>
              <p class="move-card-subtext">Processed</p>
            </div>
          </div>

          <div class="move-card move-card-pending">
            <div class="move-card-icon">⏳</div>
            <div class="move-card-content">
              <p class="move-card-label">Pending</p>
              <h2 class="move-card-value move-card-pending-emphasis" id="pendingMovements">0</h2>
              <p class="move-card-subtext">Awaiting action</p>
            </div>
          </div>

          <div class="move-card move-card-rejected">
            <div class="move-card-icon">✕</div>
            <div class="move-card-content">
              <p class="move-card-label">Rejected</p>
              <h2 class="move-card-value" id="rejectedMovements">0</h2>
              <p class="move-card-subtext">Denied</p>
            </div>
          </div>

          <div class="move-card move-card-total">
            <div class="move-card-icon">📋</div>
            <div class="move-card-content">
              <p class="move-card-label">Total</p>
              <h2 class="move-card-value" id="totalMovements">0</h2>
              <p class="move-card-subtext">All records</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Filters with Quick Chips -->
      <section class="movement-filters-section">
        <div class="card">
          <div style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <!-- Main Search -->
            <div class="form-group" style="flex: 1; min-width: 250px;">
              <label class="form-label">Search</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Search employee, type..." style="padding-left: 36px;">
              </div>
            </div>

            <!-- Quick Filter Chips - Status -->
            <div class="quick-filter-chips">
              <button class="filter-chip" data-status="Pending" onclick="window.toggleStatusFilter('Pending')">
                <span style="color: #f59e0b;">⏳</span> Pending
              </button>
              <button class="filter-chip" data-status="Approved" onclick="window.toggleStatusFilter('Approved')">
                <span style="color: #22c55e;">✓</span> Approved
              </button>
              <button class="filter-chip" data-status="Rejected" onclick="window.toggleStatusFilter('Rejected')">
                <span style="color: #9ca3af;">✕</span> Rejected
              </button>
            </div>

            <!-- Movement Type Filter -->
            <div class="form-group" style="min-width: 140px;">
              <label class="form-label">Type</label>
              <select id="typeFilter" class="form-select">
                <option value="">All Types</option>
                <option value="promotion">Promotion</option>
                <option value="transfer">Transfer</option>
                <option value="demotion">Demotion</option>
                <option value="resignation">Resignation</option>
                <option value="retirement">Retirement</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <!-- Movements Table Section -->
      <section class="movement-table-section">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Employee Movements</h3>
            <button class="btn btn-outline btn-sm" onclick="window.resetFilters()" style="color: var(--text-light);">↻ Reset</button>
          </div>

          <div class="table-container" style="overflow-x: auto;">
            <table class="movement-table">
              <thead class="table-head-sticky">
                <tr>
                  <th style="width: 18%; text-align: left;">Employee</th>
                  <th style="width: 14%; text-align: left;">Type</th>
                  <th style="width: 12%; text-align: left;">From</th>
                  <th style="width: 12%; text-align: left;">To</th>
                  <th style="width: 12%; text-align: left;">Effective Date</th>
                  <th style="width: 10%; text-align: center;">Status</th>
                  <th style="width: 22%; text-align: center;">Actions</th>
                </tr>
              </thead>
              <tbody id="movementsList">
                <tr>
                  <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">📋</div>
                      <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No movements found</p>
                      <p style="font-size: 14px; margin-bottom: 1.5rem;">Pending approvals will appear here.</p>
                      <button class="btn btn-primary" onclick="window.openMovementModal()">+ Add Movement</button>
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
    <aside class="movements-right-column">
      <div class="move-side-panel">
        <div class="move-side-panel-header">
          <h3 class="move-side-panel-title">Approvals</h3>
        </div>

        <!-- Action Button -->
        <div class="move-side-section">
          <button class="move-side-btn move-side-btn-primary" onclick="window.openMovementModal()">
            <span class="move-side-btn-icon">➕</span>
            <span>Add Movement</span>
          </button>
        </div>

        <!-- Pending Approvals Count -->
        <div class="move-side-section move-side-urgent">
          <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
            <span style="font-size: 20px;">⏳</span>
            <div>
              <p class="move-side-label">Pending Approvals</p>
              <p class="move-side-count" id="pendingCount">0</p>
            </div>
          </div>
          <button class="btn btn-outline btn-sm" onclick="window.filterByStatus('Pending')" style="width: 100%;">Review Now</button>
        </div>

        <!-- My Approvals -->
        <div class="move-side-section">
          <p class="move-side-subtitle">My Approvals</p>
          <div id="myApprovalsList" style="max-height: 160px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>

        <!-- Recently Approved -->
        <div class="move-side-section">
          <p class="move-side-subtitle">Recently Approved</p>
          <div id="recentlyApprovedList" style="max-height: 160px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>

        <!-- Overdue Approvals -->
        <div class="move-side-section move-side-danger">
          <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
            <span style="font-size: 20px;">🔴</span>
            <div>
              <p class="move-side-label">Overdue</p>
              <p class="move-side-count" id="overdueCount">0</p>
            </div>
          </div>
          <button class="btn btn-outline btn-sm" onclick="window.filterByStatus('Pending')" style="width: 100%;">View</button>
        </div>
      </div>
    </aside>
  </div>
</main>

<!-- Movement Modal -->
<div id="movementModal" class="modal">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Movement</h2>
      <button class="modal-close" onclick="closeMovementModal()">&times;</button>
    </div>
    <form id="movementForm">
      <div class="form-group">
        <label class="form-label">Employee *</label>
        <select name="employee_id" class="form-select" required>
          <option value="">Select employee...</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Movement Type *</label>
        <select name="movement_type" class="form-select" required>
          <option value="">Select type...</option>
          <option value="promotion">Promotion</option>
          <option value="transfer">Transfer</option>
          <option value="demotion">Demotion</option>
          <option value="resignation">Resignation</option>
          <option value="retirement">Retirement</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Effective Date *</label>
        <input type="date" name="effective_date" class="form-input" required>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-input" placeholder="Details about the movement..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="approval_status" class="form-select">
          <option value="Pending">Pending</option>
          <option value="Approved">Approved</option>
          <option value="Rejected">Rejected</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeMovementModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function() {
    window.loadMovements = function() {
      console.log('[Movements] Loading movements...');
      
      const searchQuery = document.getElementById('searchInput')?.value || '';
      const typeFilter = document.getElementById('typeFilter')?.value || '';
      
      // Get active status chips
      const activeChips = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.status)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        type: typeFilter,
        status: activeChips
      });
      
      // Show loading state
      const tbody = document.getElementById('movementsList');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="100%" style="text-align: center; padding: 2rem;"><div style="display: inline-block;"><div class="spinner" style="margin: 0 auto 1rem; border: 4px solid rgba(30, 64, 175, 0.2); border-top: 4px solid #3b82f6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div><p>Loading...</p></div></td></tr>';
      }

      fetch(`modules/hr_core/api.php?action=getMovements&${params}`)
        .then(res => res.json())
        .then(response => {
          if (!response.success) {
            console.error('[Movements] API error:', response.message);
            if (tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align: center; padding: 2rem; color: #ef4444;"><strong>Error:</strong> ${response.message}</td></tr>`;
            return;
          }
          
          if (response.data && response.data.movements) {
            window.displayMovements(response.data.movements);
            
            if (response.data.stats) {
              const updateElement = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value || 0;
              };
              updateElement('totalMovements', response.data.stats.total);
              updateElement('approvedMovements', response.data.stats.approved);
              updateElement('pendingMovements', response.data.stats.pending);
              updateElement('rejectedMovements', response.data.stats.rejected);
              updateElement('pendingCount', response.data.stats.pending);
              updateElement('overdueCount', response.data.stats.pending);
            }
            
            window.loadMyApprovals?.();
            window.loadRecentlyApproved?.();
            
            console.log('[Movements] Loaded successfully');
          } else {
            if (tbody) tbody.innerHTML = '<tr><td colspan="100%" style="text-align: center; padding: 3rem;">📋 No movement records found</td></tr>';
          }
        })
        .catch(error => {
          console.error('[Movements] Error:', error);
          if (tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align: center; padding: 2rem; color: #ef4444;"><strong>Error:</strong> Failed to load movements</td></tr>`;
        });
    };

    window.displayMovements = function(movements) {
      const tbody = document.getElementById('movementsList');
      tbody.innerHTML = '';

      if (!movements || movements.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">📋</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No movements found</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Pending approvals will appear here.</p>
                <button class="btn btn-primary" onclick="window.openMovementModal()">+ Add Movement</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      movements.forEach(mov => {
        const status = mov.status || mov.approval_status || 'Pending';
        let rowClass = '';
        let statusBadge = '';
        let actionButtons = '';

        if (status === 'Approved') {
          rowClass = 'move-row-approved';
          statusBadge = '✓ Approved';
          actionButtons = `
            <div style="display: flex; gap: 0.5rem; justify-content: center;">
              <button class="move-action-btn move-action-view" onclick="window.viewMovement(${mov.id})" title="View">👁</button>
              <button class="move-action-btn move-action-more" onclick="alert('Edit/Delete options')" title="More">⋯</button>
            </div>
          `;
        } else if (status === 'Rejected') {
          rowClass = 'move-row-rejected';
          statusBadge = '✕ Rejected';
          actionButtons = `
            <div style="display: flex; gap: 0.5rem; justify-content: center;">
              <button class="move-action-btn move-action-view" onclick="window.viewMovement(${mov.id})" title="View">👁</button>
              <button class="move-action-btn move-action-more" onclick="alert('Edit/Delete options')" title="More">⋯</button>
            </div>
          `;
        } else {
          // Pending
          rowClass = 'move-row-pending';
          statusBadge = '⏳ Pending';
          actionButtons = `
            <div style="display: flex; gap: 0.5rem; justify-content: center;">
              <button class="move-action-btn move-action-approve" onclick="window.approveMovement(${mov.id})" title="Approve">✓ Approve</button>
              <button class="move-action-btn move-action-reject" onclick="window.rejectMovement(${mov.id})" title="Reject">✕ Reject</button>
              <button class="move-action-btn move-action-view" onclick="window.viewMovement(${mov.id})" title="View details">👁</button>
            </div>
          `;
        }

        const fromTitle = mov.from_job_title || '-';
        const toTitle = mov.to_job_title || '-';

        const row = `
          <tr class="${rowClass}">
            <td><strong>${mov.employee_name || '-'}</strong></td>
            <td>${mov.movement_type || mov.type || '-'}</td>
            <td style="font-size: 12px;">${fromTitle}</td>
            <td style="font-size: 12px;">${toTitle}</td>
            <td>${mov.effective_date || '-'}</td>
            <td style="text-align: center;">
              <span class="move-status-badge" style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                ${statusBadge}
              </span>
            </td>
            <td style="text-align: center;">
              ${actionButtons}
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    };

    window.openMovementModal = function() {
      document.getElementById('movementForm').reset();
      delete document.getElementById('movementForm').dataset.id;
      document.querySelector('#movementModal .modal-title').textContent = 'Add Movement';
      document.getElementById('movementModal').classList.add('active');
    };

    window.closeMovementModal = function() {
      document.getElementById('movementModal').classList.remove('active');
    };

    window.toggleStatusFilter = function(status) {
      const chip = document.querySelector(`.filter-chip[data-status="${status}"]`);
      chip.classList.toggle('active');
      window.loadMovements();
    };

    window.resetFilters = function() {
      document.getElementById('searchInput').value = '';
      document.getElementById('typeFilter').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      window.loadMovements();
    };

    window.filterByStatus = function(status) {
      document.getElementById('searchInput').value = '';
      document.getElementById('typeFilter').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      const chip = document.querySelector(`.filter-chip[data-status="${status}"]`);
      if (chip) chip.classList.add('active');
      window.loadMovements();
    };

    window.viewMovement = function(id) {
      fetch(`modules/hr_core/api.php?action=getMovementById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const mov = data.data;
            alert(`Movement: ${mov.movement_type}\nEmployee: ${mov.employee_name}\nEffective: ${mov.effective_date}\nStatus: ${mov.status || mov.approval_status}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.approveMovement = function(id) {
      if (confirm('Approve this movement?')) {
        fetch(`modules/hr_core/api.php?action=approveMovement&id=${id}`, {method: 'POST'})
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Movement approved');
              window.loadMovements();
            }
          })
          .catch(error => console.error('Error:', error));
      }
    };

    window.rejectMovement = function(id) {
      if (confirm('Reject this movement?')) {
        fetch(`modules/hr_core/api.php?action=rejectMovement&id=${id}`, {method: 'POST'})
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Movement rejected');
              window.loadMovements();
            }
          })
          .catch(error => console.error('Error:', error));
      }
    };

    window.loadMyApprovals = function() {
      const list = document.getElementById('myApprovalsList');
      if (!list) return; // Element might not be in DOM if side panel not visible
      
      // Mock data - replace with API call
      const approvals = [
        { employee: 'John Doe', type: 'Promotion', date: '2026-02-15' },
        { employee: 'Jane Smith', type: 'Transfer', date: '2026-02-10' }
      ];
      list.innerHTML = approvals.map(item => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px;">
          <div style="font-weight: 600; color: var(--text-dark);">${item.employee}</div>
          <div style="color: var(--text-light); font-size: 11px;">${item.type} • ${item.date}</div>
        </div>
      `).join('') || '<p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">None pending</p>';
    };

    window.loadRecentlyApproved = function() {
      const list = document.getElementById('recentlyApprovedList');
      if (!list) return; // Element might not be in DOM if side panel not visible
      
      // Mock data - replace with API call
      const recent = [
        { employee: 'Alice Johnson', type: 'Promotion', date: '2026-02-04' },
        { employee: 'Bob Wilson', type: 'Transfer', date: '2026-02-03' }
      ];
      list.innerHTML = recent.map(item => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px;">
          <div style="font-weight: 600; color: var(--text-dark);">${item.employee}</div>
          <div style="color: var(--text-light); font-size: 11px;">${item.type} • ${item.date}</div>
        </div>
      `).join('');
    };

    function attachEventListeners() {
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('keyup', () => window.loadMovements());
      }

      ['typeFilter'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', () => window.loadMovements());
      });

      const form = document.getElementById('movementForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          const isEdit = this.dataset.id;
          const action = isEdit ? 'updateMovement' : 'createMovement';
          const data = {
            employee_id: formData.get('employee_id'),
            movement_type: formData.get('movement_type'),
            effective_date: formData.get('effective_date'),
            description: formData.get('description'),
            approval_status: formData.get('approval_status')
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
              alert(isEdit ? 'Movement updated' : 'Movement created');
              window.closeMovementModal();
              window.loadMovements();
            }
          })
          .catch(error => console.error('Error:', error));
        });
      }
    }

    setTimeout(attachEventListeners, 50);
    window.loadMovements();
  })();
</script>

<style>
  /* Movements Layout */
  .movements-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .movements-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    align-items: start;
  }

  .movements-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .movements-right-column {
    position: sticky;
    top: 20px;
    height: fit-content;
  }

  /* Movement KPI Cards */
  .movement-kpi-section {
    margin-bottom: 0;
  }

  .movement-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
  }

  .move-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .move-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }

  .move-card-icon {
    font-size: 32px;
    line-height: 1;
    flex-shrink: 0;
  }

  .move-card-content {
    flex: 1;
  }

  .move-card-label {
    margin: 0;
    font-size: 11px;
    color: var(--text-light);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .move-card-value {
    margin: 0.5rem 0 0 0;
    font-size: 28px;
    font-weight: 400;
    line-height: 1;
    color: var(--text-dark);
  }

  .move-card-pending-emphasis {
    font-weight: 700;
    font-size: 32px;
    color: #f59e0b;
  }

  .move-card-subtext {
    margin: 0.25rem 0 0 0;
    font-size: 12px;
    color: var(--text-light);
  }

  .move-card-approved { border-color: #dcfce7; }
  .move-card-approved:hover { border-color: #22c55e; }

  .move-card-pending { 
    border: 2px solid #fef3c7;
    background: linear-gradient(135deg, #fffbeb 0%, white 100%);
  }
  .move-card-pending:hover { border-color: #f59e0b; }

  .move-card-rejected { border-color: #fee2e2; }
  .move-card-rejected:hover { border-color: #ef4444; }

  .move-card-total { border-color: var(--border); }

  /* Filters */
  .movement-filters-section {
    margin-bottom: 0;
  }

  .quick-filter-chips {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .filter-chip {
    background: white;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--text-dark);
    white-space: nowrap;
  }

  .filter-chip:hover {
    border-color: var(--primary);
    background: var(--bg-light);
  }

  .filter-chip.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
  }

  /* Movement Table */
  .movement-table-section {
    margin-bottom: 0;
  }

  .movement-table {
    width: 100%;
    border-collapse: collapse;
  }

  .table-head-sticky {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  .movement-table thead th {
    padding: 14px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: var(--text-light);
    border-bottom: 2px solid var(--border);
  }

  .movement-table tbody tr {
    border-bottom: 1px solid var(--border);
    height: 60px;
    transition: background-color 0.2s ease;
  }

  .movement-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
  }

  .move-row-pending {
    background-color: #fffbeb;
  }

  .move-row-pending:hover {
    background-color: #fef3c7;
  }

  .move-row-approved {
    opacity: 0.75;
  }

  .move-row-rejected {
    opacity: 0.6;
  }

  .movement-table tbody td {
    padding: 14px 16px;
    font-size: 13px;
    vertical-align: middle;
  }

  .move-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
  }

  .move-row-pending .move-status-badge {
    background-color: #fef3c7;
    color: #92400e;
  }

  .move-row-approved .move-status-badge {
    background-color: #dcfce7;
    color: #166534;
  }

  .move-row-rejected .move-status-badge {
    background-color: #e5e7eb;
    color: #6b7280;
  }

  /* Action Buttons */
  .move-action-btn {
    background: none;
    border: 1px solid var(--border);
    font-size: 13px;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 4px;
    transition: all 0.2s ease;
    color: var(--text-dark);
    white-space: nowrap;
    font-weight: 500;
  }

  .move-action-btn:hover {
    background: rgba(0, 0, 0, 0.05);
  }

  .move-action-approve {
    border-color: #22c55e;
    color: #22c55e;
  }

  .move-action-approve:hover {
    background: #dcfce7;
  }

  .move-action-reject {
    border-color: #ef4444;
    color: #ef4444;
  }

  .move-action-reject:hover {
    background: #fee2e2;
  }

  .move-action-view {
    border-color: var(--primary);
    color: var(--primary);
  }

  .move-action-view:hover {
    background: var(--bg-light);
  }

  .move-action-more {
    border-color: var(--border);
  }

  .move-action-more:hover {
    border-color: var(--text-dark);
  }

  /* Side Panel */
  .move-side-panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
  }

  .move-side-panel-header {
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, var(--bg-light) 0%, var(--bg-lighter) 100%);
  }

  .move-side-panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
  }

  .move-side-section {
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
  }

  .move-side-section:last-child {
    border-bottom: none;
  }

  .move-side-urgent {
    background-color: #fffbeb;
    border-bottom: 2px solid #fef3c7;
  }

  .move-side-danger {
    background-color: #fef2f2;
    border-bottom: 2px solid #fee2e2;
  }

  .move-side-subtitle {
    margin: 0 0 1rem 0;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .move-side-label {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
  }

  .move-side-count {
    margin: 0.25rem 0 0 0;
    font-size: 22px;
    font-weight: 700;
    color: #f59e0b;
  }

  .move-side-danger .move-side-count {
    color: #ef4444;
  }

  /* Side Panel Buttons */
  .move-side-btn {
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

  .move-side-btn:last-of-type {
    margin-bottom: 0;
  }

  .move-side-btn:hover {
    background: var(--bg-light);
    border-color: var(--primary);
    color: var(--primary);
  }

  .move-side-btn-primary {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
  }

  .move-side-btn-primary:hover {
    opacity: 0.9;
  }

  .move-side-btn-icon {
    font-size: 16px;
    flex-shrink: 0;
  }

  /* Responsive */
  @media (max-width: 1200px) {
    .movements-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .movement-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }
  }

  @media (max-width: 768px) {
    .movements-layout {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .movements-right-column {
      position: static;
    }

    .movement-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 0.75rem;
    }

    .move-card {
      padding: 1rem;
      gap: 0.75rem;
    }

    .move-card-icon {
      font-size: 24px;
    }

    .move-card-value {
      font-size: 24px;
    }

    .quick-filter-chips {
      order: 3;
      flex-basis: 100%;
    }

    .movement-table tbody tr {
      height: auto;
    }
  }

  @media (max-width: 480px) {
    .movement-kpi-grid {
      grid-template-columns: 1fr;
    }

    .quick-filter-chips {
      flex-direction: column;
    }

    .filter-chip {
      width: 100%;
      justify-content: center;
    }

    .movement-table thead th,
    .movement-table tbody td {
      padding: 8px;
      font-size: 12px;
    }

    .move-card-value {
      font-size: 20px;
    }

    .move-action-btn {
      padding: 4px 6px;
      font-size: 11px;
    }
  }
</style>
