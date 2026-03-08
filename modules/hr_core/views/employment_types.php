
<main class="main-content emptype-container">
  <div class="emptype-layout">
    <!-- Left Column: Main Content -->
    <div class="emptype-left-column">
      <!-- KPI Cards - Policy Focus -->
      <section class="emptype-kpi-section">
        <div class="emptype-kpi-grid">
          <div class="et-kpi-card et-card-payroll">
            <div class="kpi-icon">💰</div>
            <div class="kpi-label">Payroll Eligible</div>
            <h2 class="kpi-value" id="payrollEligibleCount">0</h2>
            <p class="kpi-subtext">Types with pay</p>
          </div>

          <div class="et-kpi-card et-card-non-payroll">
            <div class="kpi-icon">⏸️</div>
            <div class="kpi-label">Non-Payroll</div>
            <h2 class="kpi-value" id="nonPayrollCount">0</h2>
            <p class="kpi-subtext">Allowance/volunteer</p>
          </div>

          <div class="et-kpi-card et-card-temporary">
            <div class="kpi-icon">📅</div>
            <div class="kpi-label">Temporary Types</div>
            <h2 class="kpi-value" id="temporaryCount">0</h2>
            <p class="kpi-subtext">Fixed-term contracts</p>
          </div>
        </div>
      </section>

      <!-- Search with Quick Filter Chips -->
      <section class="emptype-filters-section">
        <div class="card">
          <div style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <!-- Main Search -->
            <div class="form-group" style="flex: 1; min-width: 250px;">
              <label class="form-label">Search Employment Type</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Search types, rules..." style="padding-left: 36px;">
              </div>
            </div>

            <!-- Quick Filter Chips -->
            <div class="quick-filter-chips">
              <button class="filter-chip" data-filter="payroll" onclick="window.toggleETFilter('payroll')">
                <span style="color: #22c55e;">💰</span> Payroll
              </button>
              <button class="filter-chip" data-filter="temporary" onclick="window.toggleETFilter('temporary')">
                <span style="color: #f59e0b;">📅</span> Temporary
              </button>
              <button class="filter-chip" data-filter="trainee" onclick="window.toggleETFilter('trainee')">
                <span style="color: #8b5cf6;">🎓</span> Trainee
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Employment Types Table Section -->
      <section class="emptype-table-section">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Employment Type Rules</h3>
            <button class="btn btn-outline btn-sm" onclick="window.resetETFilters()" style="color: var(--text-light);">↻ Reset</button>
          </div>

          <div class="table-container" style="overflow-x: auto;">
            <table class="emptype-table">
              <thead class="table-head-sticky">
                <tr>
                  <th style="width: 14%; text-align: left;">Type Name</th>
                  <th style="width: 12%; text-align: center;">Duration</th>
                  <th style="width: 10%; text-align: center;">Payroll</th>
                  <th style="width: 10%; text-align: center;">Benefits</th>
                  <th style="width: 10%; text-align: center;">Leave</th>
                  <th style="width: 10%; text-align: center;">Gov. Cont.</th>
                  <th style="width: 12%; text-align: left;">Description</th>
                  <th style="width: 12%; text-align: center;">Status</th>
                  <th style="width: 14%; text-align: center;">Actions</th>
                </tr>
              </thead>
              <tbody id="employmentTypesList">
                <tr>
                  <td colspan="9" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">⚙️</div>
                      <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No employment types found</p>
                      <p style="font-size: 14px; margin-bottom: 1.5rem;">Define employment types to control payroll and benefits rules.</p>
                      <button class="btn btn-primary" onclick="window.openEmploymentTypeModal()">+ Add Type</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Right Column: Compliance Panel -->
    <aside class="emptype-right-column">
      <div class="et-side-panel">
        <!-- Quick Actions -->
        <div class="et-side-card">
          <div class="et-side-action">
            <button class="btn btn-primary" style="flex: 1;" onclick="window.openEmploymentTypeModal()">+ Add Type</button>
          </div>
        </div>

        <!-- System Types (Read-Only) -->
        <div class="et-side-card">
          <p class="et-side-subtitle">System Types</p>
          <div id="systemTypesList" style="max-height: 180px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Standard system types</p>
          </div>
        </div>

        <!-- Compliance Rules -->
        <div class="et-side-card compliance">
          <p class="et-side-subtitle" style="color: #059669;">✓ Compliance Rules</p>
          <div style="font-size: 11px; line-height: 1.6; color: var(--text-dark); space-y: 0.5rem;">
            <div style="margin-bottom: 0.75rem;">
              <strong>OJT/Trainee:</strong>
              <div style="color: var(--text-light); margin-top: 0.25rem;">No payroll • No benefits • Duration required</div>
            </div>
            <div style="margin-bottom: 0.75rem;">
              <strong>Probationary:</strong>
              <div style="color: var(--text-light); margin-top: 0.25rem;">Max 3-6 months • Payroll eligible • Limited benefits</div>
            </div>
            <div>
              <strong>Permanent:</strong>
              <div style="color: var(--text-light); margin-top: 0.25rem;">Full benefits • Gov contributions • Eligible for all leave</div>
            </div>
          </div>
        </div>

        <!-- Data Integrity Note -->
        <div class="et-side-card alert">
          <p class="et-side-subtitle" style="color: #ea580c;">⚠️ Important</p>
          <p style="font-size: 11px; line-height: 1.5; color: var(--text-dark);">Employment types cannot be deleted if employees are assigned. Deactivate instead to preserve compliance records.</p>
        </div>
      </div>
    </aside>
  </div>
</main>

<!-- Add/Edit Employment Type Modal -->
<div id="employmentTypeModal" class="modal">
  <div class="modal-content" style="max-width: 700px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Employment Type</h2>
      <button class="modal-close" onclick="window.closeEmploymentTypeModal()">&times;</button>
    </div>

    <form id="employmentTypeForm">
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
        <!-- Type Name -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Type Name *</label>
          <select name="type_name" class="form-select" required onchange="window.updateFormDefaults(this.value)">
            <option value="">Select or create new...</option>
            <option value="full_time">Full Time</option>
            <option value="part_time">Part Time</option>
            <option value="contract">Contract</option>
            <option value="probationary">Probationary</option>
            <option value="ojt_trainee">OJT / Trainee</option>
            <option value="intern">Intern</option>
            <option value="consultant">Consultant</option>
            <option value="custom">Custom...</option>
          </select>
          <input type="text" name="custom_type_name" class="form-input" placeholder="Custom type name..." style="display:none; margin-top: 0.5rem;">
        </div>

        <!-- Duration -->
        <div class="form-group">
          <label class="form-label">Duration *</label>
          <select name="duration_type" class="form-select" required>
            <option value="permanent">Permanent</option>
            <option value="fixed_3m">3 Months</option>
            <option value="fixed_6m">6 Months</option>
            <option value="fixed_12m">12 Months</option>
            <option value="custom">Custom (months)</option>
          </select>
          <input type="number" name="duration_months" class="form-input" placeholder="Months" style="display:none; margin-top: 0.5rem;">
        </div>

        <!-- Payroll Eligible -->
        <div class="form-group">
          <label class="form-label">Payroll Eligible *</label>
          <select name="payroll_eligible" class="form-select" required>
            <option value="yes">Yes - Regular Salary</option>
            <option value="allowance_only">Allowance Only</option>
            <option value="no">No</option>
          </select>
        </div>

        <!-- Benefits Eligible -->
        <div class="form-group">
          <label class="form-label">Benefits Eligible *</label>
          <select name="benefits_eligible" class="form-select" required>
            <option value="full">Full Benefits</option>
            <option value="limited">Limited Benefits</option>
            <option value="no">No Benefits</option>
          </select>
        </div>

        <!-- Leave Eligible -->
        <div class="form-group">
          <label class="form-label">Leave Eligible *</label>
          <select name="leave_eligible" class="form-select" required>
            <option value="full">Full Leave Entitlements</option>
            <option value="limited">Limited Leave</option>
            <option value="no">No Leave</option>
          </select>
        </div>

        <!-- Government Contributions -->
        <div class="form-group">
          <label class="form-label">Government Contributions *</label>
          <select name="gov_contributions" class="form-select" required>
            <option value="yes">Yes (SSS, PhilHealth, PagIBIG)</option>
            <option value="no">No</option>
          </select>
        </div>

        <!-- Description -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">Description & Rules</label>
          <textarea name="description" class="form-input" placeholder="Rules, restrictions, or special notes for this employment type..." rows="3"></textarea>
        </div>

        <!-- Include in Headcount -->
        <div class="form-group" style="grid-column: 1 / -1;">
          <label class="form-label">
            <input type="checkbox" name="include_in_headcount" checked style="margin-right: 0.5rem;">
            Include in Headcount (Uncheck for trainees/OJT)
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="window.closeEmploymentTypeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Type</button>
      </div>
    </form>
  </div>
</div>



<script>
  (function() {
    window.loadEmploymentTypes = function() {
      const searchQuery = document.getElementById('searchInput')?.value || '';
      
      // Get active filters
      const activeChips = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.filter)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        filter: activeChips
      });

      fetch(`modules/hr_core/api.php?action=getEmploymentTypes&${params}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data && data.data.employment_types) {
            window.displayEmploymentTypes(data.data.employment_types);
            
            // Calculate and update stats
            const types = data.data.employment_types;
            document.getElementById('payrollEligibleCount').textContent = types.length;
            document.getElementById('nonPayrollCount').textContent = 0;
            document.getElementById('temporaryCount').textContent = 0;
          }
        })
        .catch(error => console.error('Error loading employment types:', error));
    };

    window.displayEmploymentTypes = function(types) {
      const tbody = document.getElementById('employmentTypesList');
      tbody.innerHTML = '';

      if (!types || types.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="9" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">⚙️</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No employment types found</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Define employment types to control payroll and benefits rules.</p>
                <button class="btn btn-primary" onclick="window.openEmploymentTypeModal()">+ Add Type</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      types.forEach(type => {
        const isTrainee = type.type_name?.toLowerCase().includes('trainee') || type.type_name?.toLowerCase().includes('ojt') || type.type_name?.toLowerCase().includes('intern');
        const isTemporary = type.duration_type === 'fixed_3m' || type.duration_type === 'fixed_6m' || type.duration_type === 'fixed_12m';
        const isSystemType = ['full_time', 'part_time', 'contract', 'probationary', 'ojt_trainee', 'intern', 'consultant'].includes(type.type_name?.toLowerCase());
        
        // Duration badge
        let durationDisplay = 'Permanent';
        if (type.duration_type === 'fixed_3m') durationDisplay = '3 Months';
        else if (type.duration_type === 'fixed_6m') durationDisplay = '6 Months';
        else if (type.duration_type === 'fixed_12m') durationDisplay = '12 Months';
        else if (type.duration_type === 'custom') durationDisplay = `${type.duration_months || '-'} Mo.`;

        // Policy badges with specific styling
        const payrollBadge = type.payroll_eligible ? (type.payroll_eligible === 'allowance_only' ? 'et-badge-allowance' : 'et-badge-yes') : 'et-badge-no';
        const benefitsBadge = type.benefits_eligible && type.benefits_eligible !== 'no' ? 'et-badge-yes' : 'et-badge-no';
        const leaveBadge = type.leave_eligible && type.leave_eligible !== 'no' ? 'et-badge-yes' : 'et-badge-no';
        const govBadge = type.gov_contributions ? 'et-badge-yes' : 'et-badge-no';

        // Type badge for trainee
        let typeBadge = '';
        if (isTrainee) {
          typeBadge = `<span class="et-type-badge et-type-trainee">🎓 Trainee</span>`;
        } else if (isTemporary) {
          typeBadge = `<span class="et-type-badge et-type-temporary">📅 Fixed</span>`;
        }

        // Status badge
        const statusBadge = type.status === 'active' ? 'et-status-active' : 'et-status-inactive';

        // Context-aware actions (with delete guard)
        let actionButtons = '';
        const hasEmployees = type.employee_count > 0;
        if (!isSystemType) {
          actionButtons = `
            <button class="et-action-btn et-action-edit" onclick="window.editEmploymentType(${type.id})" title="Edit">✏</button>
            <button class="et-action-btn et-action-more ${hasEmployees ? 'disabled' : ''}" 
              onclick="alert('More options')" title="More">⋯</button>
          `;
        } else {
          actionButtons = `
            <button class="et-action-btn et-action-view" onclick="window.viewEmploymentType(${type.id})" title="View">👁</button>
            <button class="et-action-btn et-action-more" onclick="alert('System type - Edit via form only')" title="More">⋯</button>
          `;
        }

        // Deactivate if in use
        if (hasEmployees) {
          actionButtons += `
            <button class="et-action-btn et-action-deactivate" onclick="window.deactivateEmploymentType(${type.id})" title="Deactivate">🔒</button>
          `;
        }

        const row = `
          <tr>
            <td style="font-weight: 600; color: var(--text-dark);">
              <div>${type.type_name || '-'}</div>
              ${typeBadge}
            </td>
            <td style="text-align: center; font-size: 12px;">
              <span class="et-duration-badge">${durationDisplay}</span>
            </td>
            <td style="text-align: center;">
              <span class="et-policy-badge ${payrollBadge}">
                ${type.payroll_eligible ? (type.payroll_eligible === 'allowance_only' ? '⏸️' : '💰') : '✕'}
              </span>
            </td>
            <td style="text-align: center;">
              <span class="et-policy-badge ${benefitsBadge}">
                ${type.benefits_eligible && type.benefits_eligible !== 'no' ? '✓' : '✕'}
              </span>
            </td>
            <td style="text-align: center;">
              <span class="et-policy-badge ${leaveBadge}">
                ${type.leave_eligible && type.leave_eligible !== 'no' ? '✓' : '✕'}
              </span>
            </td>
            <td style="text-align: center;">
              <span class="et-policy-badge ${govBadge}">
                ${type.gov_contributions ? '✓' : '✕'}
              </span>
            </td>
            <td style="font-size: 12px; color: var(--text-light);">${type.description ? type.description.substring(0, 30) + '...' : '-'}</td>
            <td style="text-align: center;">
              <span class="et-status-badge ${statusBadge}">
                ${type.status === 'active' ? '✓' : '✕'} ${type.status === 'active' ? 'Active' : 'Inactive'}
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

    window.openEmploymentTypeModal = function() {
      document.getElementById('employmentTypeForm').reset();
      delete document.getElementById('employmentTypeForm').dataset.id;
      document.querySelector('#employmentTypeModal .modal-title').textContent = 'Add Employment Type';
      document.getElementById('employmentTypeModal').classList.add('active');
    };

    window.closeEmploymentTypeModal = function() {
      document.getElementById('employmentTypeModal').classList.remove('active');
    };

    window.updateFormDefaults = function(selectedType) {
      const customInput = document.querySelector('input[name="custom_type_name"]');
      const durationSelect = document.querySelector('select[name="duration_type"]');
      const payrollSelect = document.querySelector('select[name="payroll_eligible"]');
      const benefitsSelect = document.querySelector('select[name="benefits_eligible"]');
      const leaveSelect = document.querySelector('select[name="leave_eligible"]');
      const govSelect = document.querySelector('select[name="gov_contributions"]');
      const headcountCheck = document.querySelector('input[name="include_in_headcount"]');

      // Show/hide custom type name input
      if (customInput) {
        customInput.style.display = selectedType === 'custom' ? 'block' : 'none';
      }

      // Apply defaults based on type
      const defaults = {
        full_time: {
          duration: 'permanent',
          payroll: 'yes',
          benefits: 'full',
          leave: 'full',
          gov: 'yes',
          headcount: true
        },
        part_time: {
          duration: 'permanent',
          payroll: 'yes',
          benefits: 'limited',
          leave: 'limited',
          gov: 'no',
          headcount: true
        },
        contract: {
          duration: 'fixed_12m',
          payroll: 'yes',
          benefits: 'limited',
          leave: 'limited',
          gov: 'no',
          headcount: true
        },
        probationary: {
          duration: 'fixed_6m',
          payroll: 'yes',
          benefits: 'limited',
          leave: 'limited',
          gov: 'yes',
          headcount: true
        },
        ojt_trainee: {
          duration: 'fixed_3m',
          payroll: 'allowance_only',
          benefits: 'no',
          leave: 'no',
          gov: 'no',
          headcount: false
        },
        intern: {
          duration: 'fixed_6m',
          payroll: 'allowance_only',
          benefits: 'no',
          leave: 'limited',
          gov: 'no',
          headcount: false
        },
        consultant: {
          duration: 'fixed_12m',
          payroll: 'yes',
          benefits: 'no',
          leave: 'no',
          gov: 'no',
          headcount: true
        }
      };

      if (defaults[selectedType]) {
        const d = defaults[selectedType];
        durationSelect.value = d.duration;
        payrollSelect.value = d.payroll;
        benefitsSelect.value = d.benefits;
        leaveSelect.value = d.leave;
        govSelect.value = d.gov ? 'yes' : 'no';
        headcountCheck.checked = d.headcount;
      }
    };

    window.toggleETFilter = function(filter) {
      const chip = document.querySelector(`.filter-chip[data-filter="${filter}"]`);
      chip.classList.toggle('active');
      window.loadEmploymentTypes();
    };

    window.resetETFilters = function() {
      document.getElementById('searchInput').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      window.loadEmploymentTypes();
    };

    window.editEmploymentType = function(id) {
      fetch(`modules/hr_core/api.php?action=getEmploymentTypeById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const type = data.data;
            const form = document.getElementById('employmentTypeForm');
            if (form) {
              form.querySelector('select[name="type_name"]').value = type.type_name?.toLowerCase() || '';
              form.querySelector('input[name="custom_type_name"]').value = type.type_name || '';
              form.querySelector('select[name="duration_type"]').value = type.duration_type || 'permanent';
              form.querySelector('input[name="duration_months"]').value = type.duration_months || '';
              form.querySelector('select[name="payroll_eligible"]').value = type.payroll_eligible || 'no';
              form.querySelector('select[name="benefits_eligible"]').value = type.benefits_eligible || 'no';
              form.querySelector('select[name="leave_eligible"]').value = type.leave_eligible || 'no';
              form.querySelector('select[name="gov_contributions"]').value = type.gov_contributions ? 'yes' : 'no';
              form.querySelector('textarea[name="description"]').value = type.description || '';
              form.querySelector('input[name="include_in_headcount"]').checked = type.include_in_headcount !== false;
              form.dataset.id = id;
              document.querySelector('#employmentTypeModal .modal-title').textContent = 'Edit Employment Type';
              window.openEmploymentTypeModal();
            }
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.viewEmploymentType = function(id) {
      fetch(`modules/hr_core/api.php?action=getEmploymentTypeById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const type = data.data;
            alert(`Type: ${type.type_name}\nDuration: ${type.duration_type}\nPayroll: ${type.payroll_eligible}\nBenefits: ${type.benefits_eligible}\nLeave: ${type.leave_eligible}\nDescription: ${type.description || 'None'}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.deactivateEmploymentType = function(id) {
      if (confirm('Deactivate this employment type? It will no longer appear for new hires but existing records remain.')) {
        fetch(`modules/hr_core/api.php?action=updateEmploymentType`, {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({id: id, status: 'inactive'})
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            alert('Employment type deactivated');
            window.loadEmploymentTypes();
          }
        })
        .catch(error => console.error('Error:', error));
      }
    };

    window.loadSystemTypes = function(types) {
      const list = document.getElementById('systemTypesList');
      const systemTypes = ['Full Time', 'Part Time', 'Contract', 'Probationary', 'OJT/Trainee', 'Intern', 'Consultant'];
      const activeCount = types?.length || 0;
      
      if (activeCount === 0) {
        list.innerHTML = '<p style="font-size: 11px; color: var(--text-light); text-align: center; padding: 1rem;">No custom types yet</p>';
        return;
      }
      
      list.innerHTML = `<div style="font-size: 11px; color: var(--text-dark);">
        <div style="margin-bottom: 0.5rem;">
          <strong>Standard Types:</strong>
          <div style="color: var(--text-light); font-size: 10px; margin-top: 0.25rem;">${systemTypes.join(' • ')}</div>
        </div>
        <div>
          <strong>Custom Types:</strong>
          <div style="color: var(--text-light); font-size: 10px; margin-top: 0.25rem;">${types.map(t => t.type_name).join(' • ')}</div>
        </div>
      </div>`;
    };

    function attachEventListeners() {
      // Search
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('input', () => window.loadEmploymentTypes());
      }

      // Filter chips
      document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', (e) => {
          e.preventDefault();
          const filter = chip.dataset.filter;
          chip.classList.toggle('active');
          window.loadEmploymentTypes();
        });
      });

      // Modal close
      const modal = document.getElementById('employmentTypeModal');
      if (modal) {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) window.closeEmploymentTypeModal();
        });
      }

      // Form submission
      const form = document.getElementById('employmentTypeForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const id = this.dataset.id || null;
          const formData = new FormData(this);
          const action = id ? 'updateEmploymentType' : 'createEmploymentType';
          
          const typeName = formData.get('type_name') === 'custom' ? formData.get('custom_type_name') : formData.get('type_name');
          
          const data = {
            type_name: typeName,
            duration_type: formData.get('duration_type'),
            duration_months: formData.get('duration_months'),
            payroll_eligible: formData.get('payroll_eligible'),
            benefits_eligible: formData.get('benefits_eligible'),
            leave_eligible: formData.get('leave_eligible'),
            gov_contributions: formData.get('gov_contributions') === 'yes' ? 1 : 0,
            description: formData.get('description'),
            include_in_headcount: formData.get('include_in_headcount') ? 1 : 0
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
              alert(result.message || (id ? 'Employment type updated' : 'Employment type created'));
              window.closeEmploymentTypeModal();
              window.loadEmploymentTypes();
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
      window.loadEmploymentTypes();
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
  .emptype-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .emptype-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .emptype-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .emptype-right-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  /* KPI Cards */
  .emptype-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .et-kpi-card {
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

  .et-kpi-card:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    border-color: var(--primary);
  }

  .et-kpi-card .kpi-icon {
    font-size: 32px;
    margin-bottom: 0.5rem;
  }

  .et-kpi-card .kpi-label {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
  }

  .et-kpi-card .kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--text-dark);
  }

  .et-kpi-card .kpi-subtext {
    font-size: 11px;
    color: var(--text-lighter);
    margin-top: 0.5rem;
  }

  .et-card-payroll .kpi-icon { color: #22c55e; }
  .et-card-payroll .kpi-value { color: #22c55e; }

  .et-card-non-payroll .kpi-icon { color: var(--warning); }
  .et-card-non-payroll .kpi-value { color: var(--warning); }

  .et-card-temporary .kpi-icon { color: var(--info); }
  .et-card-temporary .kpi-value { color: var(--info); }

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
  .emptype-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  .emptype-table thead th {
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

  .emptype-table tbody td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
  }

  .emptype-table tbody tr {
    transition: all 0.2s ease;
  }

  .emptype-table tbody tr:hover {
    background: var(--bg-lighter);
  }

  /* Duration Badge */
  .et-duration-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    background: var(--bg-light);
    color: var(--text-dark);
    font-size: 10px;
    font-weight: 600;
  }

  /* Policy Badges */
  .et-policy-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
  }

  .et-badge-yes {
    background: #dcfce7;
    color: #166534;
  }

  .et-badge-no {
    background: #fee2e2;
    color: #991b1b;
  }

  .et-badge-allowance {
    background: #fef3c7;
    color: #92400e;
  }

  /* Type Badges */
  .et-type-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
    margin-top: 0.25rem;
    margin-left: 0;
  }

  .et-type-trainee {
    background: #e9d5ff;
    color: #6b21a8;
  }

  .et-type-temporary {
    background: #cffafe;
    color: #0e7490;
  }

  /* Status Badges */
  .et-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
  }

  .et-status-active {
    background: #dcfce7;
    color: #166534;
  }

  .et-status-inactive {
    background: #f3f4f6;
    color: #9ca3af;
  }

  /* Action Buttons */
  .et-action-btn {
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

  .et-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .et-action-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .et-action-edit:hover { background: var(--primary); color: white; }
  .et-action-view:hover { background: #06b6d4; color: white; }
  .et-action-deactivate:hover { background: var(--warning); color: white; }
  .et-action-more:hover { background: #8b5cf6; color: white; }

  /* Side Panel */
  .et-side-panel {
    position: sticky;
    top: 20px;
    height: fit-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .et-side-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .et-side-card.compliance {
    border-color: #22c55e;
    background: #f0fdf4;
  }

  .et-side-card.alert {
    border-color: var(--danger);
    background: #fef2f2;
  }

  .et-side-subtitle {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
  }

  .et-side-action {
    display: flex;
    gap: 0.5rem;
  }

  .et-side-action .btn {
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
    margin-bottom: 1rem;
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

  /* Responsive */
  @media (max-width: 1200px) {
    .emptype-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .emptype-kpi-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 768px) {
    .emptype-layout {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .emptype-kpi-grid {
      grid-template-columns: repeat(3, 1fr);
    }

    .quick-filter-chips {
      flex-wrap: wrap;
    }

    .filter-chip {
      font-size: 12px;
      padding: 0.4rem 0.8rem;
    }

    .et-side-panel {
      position: static;
    }

    .emptype-table {
      font-size: 11px;
    }

    .emptype-table thead th,
    .emptype-table tbody td {
      padding: 0.5rem;
    }

    .et-action-btn {
      width: 24px;
      height: 24px;
      font-size: 12px;
    }
  }

  @media (max-width: 480px) {
    .emptype-container {
      padding: 0 0.75rem;
    }

    .emptype-kpi-grid {
      grid-template-columns: 1fr;
    }

    .quick-filter-chips {
      gap: 0.5rem;
    }

    .filter-chip {
      font-size: 11px;
      padding: 0.35rem 0.7rem;
    }

    .emptype-table {
      font-size: 10px;
    }

    .emptype-table thead th,
    .emptype-table tbody td {
      padding: 0.4rem;
    }

    .et-side-card {
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
  }
</style>
