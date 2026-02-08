<?php
/**
 * HMO Plan Management View
 * Define, configure, and manage insurance plans with benefits, limits, and network restrictions
 */
?>
<div class="main-content">
  <style>
    /* ===== PAGE HEADER ===== */
    .page-header {
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 28px;
      font-weight: 600;
      color: #111827;
      margin: 0 0 0.5rem 0;
    }

    .page-subtitle {
      font-size: 13px;
      color: #6b7280;
      margin: 0 0 1rem 0;
    }

    .page-meta {
      font-size: 12px;
      color: #9ca3af;
      margin-top: 0.5rem;
    }

    /* ===== STAT CARDS ===== */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1rem;
      margin-bottom: 2.5rem;
    }

    .stat-card {
      background: white;
      border: 1px solid #e5e7eb;
      border-left: 4px solid #999;
      border-radius: 8px;
      padding: 1.25rem;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
      transition: all 0.2s ease;
    }

    .stat-card.accent-active {
      border-left-color: #10b981;
    }

    .stat-card.accent-inactive {
      border-left-color: #ef4444;
    }

    .stat-card.accent-providers {
      border-left-color: #3b82f6;
    }

    .stat-card:hover {
      border-color: #d1d5db;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .stat-icon {
      font-size: 20px;
      margin-bottom: 0.5rem;
      opacity: 0.7;
    }

    .stat-number {
      font-size: 28px;
      font-weight: 700;
      color: #111827;
      margin: 0.25rem 0 0.5rem 0;
    }

    .stat-subtitle {
      font-size: 11px;
      color: #6b7280;
      margin-bottom: 0.25rem;
      font-weight: 400;
    }

    .stat-label {
      font-size: 11px;
      color: #9ca3af;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 500;
    }

    /* ===== CARD HEADER ===== */
    .card {
      background: white;
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      overflow: hidden;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
    }

    .card-title {
      font-size: 18px;
      font-weight: 600;
      color: #111827;
      margin: 0;
    }

    /* ===== BUTTONS ===== */
    .btn {
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      height: 40px;
    }

    .btn-primary {
      background: #1e40af;
      color: white;
      padding: 0.625rem 1.25rem;
    }

    .btn-primary:hover {
      background: #1e3a8a;
      box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
    }

    .btn-secondary {
      background: #6b7280;
      color: white;
      padding: 0.625rem 1.25rem;
    }

    .btn-secondary:hover {
      background: #4b5563;
    }

    .btn-icon {
      background: transparent;
      color: #6b7280;
      width: 36px;
      height: 36px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      font-size: 16px;
    }

    .btn-icon:hover {
      background: #f3f4f6;
      color: #1e40af;
    }

    /* ===== FILTER BAR & TABS ===== */
    .filter-bar {
      position: sticky;
      top: 0;
      display: flex;
      gap: 1rem;
      align-items: center;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      background: white;
      z-index: 10;
    }

    .search-box {
      flex: 1;
      max-width: 300px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 0.5rem 1rem 0.5rem 2.25rem;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      font-size: 13px;
      transition: border-color 0.2s ease;
    }

    .search-box input:focus {
      outline: none;
      border-color: #1e40af;
    }

    .search-box-icon {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: 14px;
    }

    .tabs {
      display: flex;
      gap: 0.5rem;
      flex: 1;
    }

    .tab-btn {
      padding: 0.5rem 1rem;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      color: #6b7280;
      border-radius: 6px;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .tab-btn:hover {
      color: #374151;
      background: #f3f4f6;
    }

    .tab-btn.active {
      background: white;
      color: #1e40af;
      border-color: #1e40af;
    }

    .tab-count {
      background: #f3f4f6;
      padding: 0.125rem 0.5rem;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
      color: #6b7280;
    }

    .tab-btn.active .tab-count {
      background: #dbeafe;
      color: #1e40af;
    }

    /* ===== TABLE ===== */
    .hmo-table {
      width: 100%;
      border-collapse: collapse;
    }

    .hmo-table thead {
      background: #f9fafb;
      border-bottom: 1px solid #e5e7eb;
    }

    .hmo-table th {
      padding: 1rem 1.5rem;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .hmo-table tbody tr {
      border-bottom: 1px solid #e5e7eb;
      transition: background 0.15s ease;
      cursor: pointer;
    }

    .hmo-table tbody tr:hover {
      background: #f0f9ff;
    }

    .hmo-table td {
      padding: 1.25rem 1.5rem;
      color: #374151;
      font-size: 14px;
    }

    .td-muted {
      color: #6b7280;
      font-size: 13px;
    }

    /* ===== PROVIDER CELL WITH AVATAR ===== */
    .provider-cell {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .provider-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 600;
      flex-shrink: 0;
    }

    .provider-info strong {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #111827;
    }

    .provider-info .provider-code {
      font-size: 12px;
      color: #9ca3af;
    }

    /* ===== CLICKABLE NUMBERS ===== */
    .clickable-number {
      cursor: pointer;
      font-weight: 600;
      color: #1e40af;
      text-decoration: none;
      transition: color 0.2s ease;
    }

    .clickable-number:hover {
      color: #1e3a8a;
      text-decoration: underline;
    }

    /* ===== STACKED TAGS ===== */
    .stacked-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .stacked-tags .badge {
      margin: 0;
    }

    /* ===== BADGES ===== */
    .badge {
      display: inline-flex;
      align-items: center;
      padding: 0.375rem 0.875rem;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      gap: 0.5rem;
    }

    .badge-active {
      background: #dcfce7;
      color: #166534;
    }

    .badge-inactive {
      background: #fee2e2;
      color: #991b1b;
    }

    .badge-phased {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-inpatient {
      background: #dbeafe;
      color: #1e40af;
    }

    .badge-outpatient {
      background: #fce7f3;
      color: #be185d;
    }

    .badge-emergency {
      background: #fef2f2;
      color: #991b1b;
    }

    .badge-dental {
      background: #e0e7ff;
      color: #3730a3;
    }

    .badge-optical {
      background: #f0fdf4;
      color: #166534;
    }

    .badge-maternity {
      background: #fdf2f8;
      color: #831843;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
      text-align: center;
      padding: 3rem 1.5rem;
      color: #6b7280;
    }

    .empty-state-icon {
      font-size: 48px;
      margin-bottom: 1rem;
    }

    .empty-state-title {
      font-size: 16px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
    }

    .empty-state-message {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 1.5rem;
    }

    .empty-state-action {
      display: inline-block;
    }

    /* ===== ERROR STATE ===== */
    .error-state {
      text-align: center;
      padding: 3rem 1.5rem;
      background: #fef3c7;
      border: 1px solid #fcd34d;
      border-radius: 8px;
      margin: 1.5rem;
    }

    .error-state-icon {
      font-size: 40px;
      margin-bottom: 1rem;
    }

    .error-state-message {
      font-size: 14px;
      color: #92400e;
      margin-bottom: 1rem;
    }

    /* ===== ACTIONS CELL ===== */
    .actions-cell {
      display: flex;
      gap: 0.75rem;
      align-items: center;
    }

    .action-tooltip {
      position: relative;
    }

    .action-tooltip .tooltip-text {
      visibility: hidden;
      background-color: #374151;
      color: white;
      text-align: center;
      padding: 0.5rem 0.75rem;
      border-radius: 4px;
      position: absolute;
      z-index: 1000;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      white-space: nowrap;
      font-size: 12px;
      opacity: 0;
      transition: opacity 0.2s ease;
    }

    .action-tooltip:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }

    /* ===== SIDE MODAL ===== */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.4);
      z-index: 999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .side-modal {
      position: fixed;
      top: 0;
      right: 0;
      width: 420px;
      height: 100vh;
      background: white;
      box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      overflow-y: auto;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }

    .side-modal.active {
      transform: translateX(0);
    }

    .modal-header {
      padding: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-close {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: #6b7280;
      padding: 0;
    }

    .modal-close:hover {
      color: #111827;
    }

    .modal-content {
      padding: 1.5rem;
    }

    .modal-detail {
      margin-bottom: 1.5rem;
    }

    .modal-detail-title {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 0.75rem;
      letter-spacing: 0.5px;
    }

    .modal-detail-content {
      font-size: 14px;
      color: #374151;
      line-height: 1.6;
    }

    .modal-detail-content strong {
      display: block;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .modal-detail-list {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .modal-detail-item {
      padding: 0.75rem;
      background: #f9fafb;
      border-radius: 6px;
      border-left: 3px solid #1e40af;
    }

    .modal-detail-item-title {
      font-weight: 600;
      font-size: 13px;
      color: #111827;
      margin-bottom: 0.25rem;
    }

    .modal-detail-item-meta {
      font-size: 12px;
      color: #6b7280;
      display: flex;
      gap: 1rem;
    }

    .modal-actions {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      padding: 1.5rem 0 0;
      border-top: 1px solid #e5e7eb;
      margin-top: 1.5rem;
    }

    .modal-actions .btn {
      width: 100%;
      justify-content: center;
    }

    /* ===== LOADING STATE ===== */
    .skeleton-row {
      animation: skeleton-loading 1s linear infinite alternate;
    }

    @keyframes skeleton-loading {
      0% {
        background-color: #f3f4f6;
      }
      100% {
        background-color: #e5e7eb;
      }
    }

    .skeleton-text {
      height: 12px;
      background-color: #e5e7eb;
      border-radius: 4px;
      margin: 0.5rem 0;
    }

    /* ===== COVERAGE GRID ===== */
    .coverage-badges {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
  </style>

  <!-- Page Header -->
  <div class="page-header">
    <h1 class="page-title">HMO Plans</h1>
    <p class="page-subtitle">Define and manage insurance plans with benefits, limits, and network restrictions</p>
    <p class="page-meta">📅 <?php echo date('M d, Y'); ?></p>
  </div>

  <!-- Header Stats -->
  <div class="stats-grid">
    <div class="stat-card accent-active">
      <div class="stat-icon">✓</div>
      <div class="stat-number" id="active-plans">0</div>
      <div class="stat-subtitle">Active plans</div>
      <div class="stat-label">Currently Enabled</div>
    </div>
    <div class="stat-card accent-inactive">
      <div class="stat-icon">✕</div>
      <div class="stat-number" id="inactive-plans">0</div>
      <div class="stat-subtitle">Inactive plans</div>
      <div class="stat-label">Disabled or Archived</div>
    </div>
    <div class="stat-card accent-providers">
      <div class="stat-icon">🏥</div>
      <div class="stat-number" id="providers-count">0</div>
      <div class="stat-subtitle">Linked Providers</div>
      <div class="stat-label">Network Partners</div>
    </div>
    <div class="stat-card accent-active">
      <div class="stat-icon">📋</div>
      <div class="stat-number" id="total-plans">0</div>
      <div class="stat-subtitle">Total plans</div>
      <div class="stat-label">All Configurations</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Plans</h3>
      <button class="btn btn-primary" onclick="showAddPlanModal()">
        <span>+</span> Add Plan
      </button>
    </div>

    <!-- Filter Bar & Tab Navigation -->
    <div class="filter-bar">
      <div class="search-box">
        <span class="search-box-icon">🔍</span>
        <input type="text" id="search-plans" placeholder="Search by plan name..." onkeyup="filterPlans()">
      </div>
      <div class="tabs">
        <button class="tab-btn active" onclick="switchPlanTab(event, 'by-provider')">By Provider <span class="tab-count" id="count-provider">0</span></button>
        <button class="tab-btn" onclick="switchPlanTab(event, 'active')">Active <span class="tab-count" id="count-active">0</span></button>
        <button class="tab-btn" onclick="switchPlanTab(event, 'by-coverage')">Coverage <span class="tab-count" id="count-coverage">0</span></button>
      </div>
    </div>

    <!-- By Provider Table -->
    <div id="by-provider-tab" style="display: block;">
      <table class="hmo-table">
        <thead>
          <tr>
            <th>Provider</th>
            <th>Plans Offered</th>
            <th>Active Plans</th>
            <th>Coverage Options</th>
            <th style="text-align: center; width: 100px;">Actions</th>
          </tr>
        </thead>
        <tbody id="by-provider-tbody">
          <tr class="skeleton-row">
            <td colspan="5"><div class="skeleton-text" style="width: 100%;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Active Plans Table -->
    <div id="active-tab" style="display: none;">
      <table class="hmo-table">
        <thead>
          <tr>
            <th>Plan Name</th>
            <th>Provider</th>
            <th>Plan Type</th>
            <th>Annual Premium</th>
            <th>Hospital Network</th>
            <th>Doctor Network</th>
            <th style="text-align: center; width: 100px;">Actions</th>
          </tr>
        </thead>
        <tbody id="active-plans-tbody">
          <tr class="skeleton-row">
            <td colspan="7"><div class="skeleton-text" style="width: 100%;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- By Coverage Table -->
    <div id="by-coverage-tab" style="display: none;">
      <table class="hmo-table">
        <thead>
          <tr>
            <th>Coverage Type</th>
            <th>Plans Offering</th>
            <th>Providers</th>
            <th>Average ABL</th>
            <th style="text-align: center; width: 100px;">Actions</th>
          </tr>
        </thead>
        <tbody id="by-coverage-tbody">
          <tr class="skeleton-row">
            <td colspan="5"><div class="skeleton-text" style="width: 100%;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Side Modal for Provider Details -->
  <div class="modal-overlay" id="modal-overlay" onclick="closeSideModal()"></div>
  <div class="side-modal" id="side-modal">
    <div class="modal-header">
      <h2 id="modal-provider-name" style="margin: 0; font-size: 18px; font-weight: 600;"></h2>
      <button class="modal-close" onclick="closeSideModal()">✕</button>
    </div>
    <div class="modal-content">
      <!-- Provider Status -->
      <div class="modal-detail">
        <div class="modal-detail-title">Status</div>
        <div class="modal-detail-content">
          <span id="modal-provider-status" class="badge"></span>
        </div>
      </div>

      <!-- Plans Under Provider -->
      <div class="modal-detail">
        <div class="modal-detail-title">Plans Under Provider</div>
        <div class="modal-detail-list" id="modal-plans-list">
          <!-- Plans will be populated here -->
        </div>
      </div>

      <!-- Employee Enrollment -->
      <div class="modal-detail">
        <div class="modal-detail-title">Enrollment Summary</div>
        <div class="modal-detail-content">
          <div>Total Employees Covered: <strong id="modal-enrollment-count">0</strong></div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="modal-actions">
        <button class="btn btn-primary" onclick="editProvider()">✏️ Edit Provider</button>
        <button class="btn btn-secondary" onclick="viewProviderDetails()">👁️ View Details</button>
        <button class="btn btn-secondary" onclick="deactivateProvider()">🚫 Deactivate</button>
      </div>
    </div>
  </div>
</div>

<script>
  if (typeof COVERAGE_TYPES === 'undefined') {
    var COVERAGE_TYPES = {
      'inpatient': { label: 'In-patient', badge: 'badge-inpatient', icon: '🏥' },
      'outpatient': { label: 'Out-patient', badge: 'badge-outpatient', icon: '🚑' },
      'emergency': { label: 'Emergency', badge: 'badge-emergency', icon: '🚨' },
      'dental': { label: 'Dental', badge: 'badge-dental', icon: '🦷' },
      'optical': { label: 'Optical', badge: 'badge-optical', icon: '👁️' },
      'maternity': { label: 'Maternity', badge: 'badge-maternity', icon: '🤰' }
    };
  }

  if (typeof allProviders === 'undefined') { var allProviders = []; }
  if (typeof allPlans === 'undefined') { var allPlans = []; }
  // Don't reset arrays here - they'll be populated by fetch calls

  function getInitials(name) {
    if (!name) return '?';
    return name
      .split(' ')
      .slice(0, 2)
      .map(word => word[0])
      .join('')
      .toUpperCase();
  }

  function switchPlanTab(event, tabName) {
    event.preventDefault();
    
    // Hide all tabs
    document.getElementById('active-tab').style.display = 'none';
    document.getElementById('by-provider-tab').style.display = 'none';
    document.getElementById('by-coverage-tab').style.display = 'none';

    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    // Show selected tab and mark button as active
    document.getElementById(tabName + '-tab').style.display = 'block';
    event.target.classList.add('active');

    // Persist tab selection
    localStorage.setItem('selectedPlanTab', tabName);

    // Load data for the selected tab
    if (tabName === 'active') {
      loadActivePlans();
    } else if (tabName === 'by-provider') {
      loadPlansByProvider();
    } else if (tabName === 'by-coverage') {
      loadPlansByCoverage();
    }
  }

  function getStatusBadge(status) {
    const badges = {
      'active': 'badge-active',
      'inactive': 'badge-inactive',
      'phased': 'badge-phased'
    };
    return badges[status] || 'badge-active';
  }

  function formatCurrency(value) {
    return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(value || 0);
  }

  function createCoverageBadges(coverageTypes) {
    if (!coverageTypes) return '';
    
    const types = Array.isArray(coverageTypes) ? coverageTypes : [coverageTypes];
    return types.slice(0, 3).map(type => {
      const coverage = COVERAGE_TYPES[type.toLowerCase()] || { label: type, badge: 'badge-inpatient', icon: '📋' };
      return `<span class="badge ${coverage.badge}">${coverage.icon} ${coverage.label}</span>`;
    }).join('');
  }

  function closeSideModal() {
    document.getElementById('side-modal').classList.remove('active');
    document.getElementById('modal-overlay').classList.remove('active');
  }

  function viewProviderModal(providerId) {
    const provider = allProviders.find(p => p.id === providerId);
    if (provider) {
      populateSideModal(provider);
      document.getElementById('side-modal').classList.add('active');
      document.getElementById('modal-overlay').classList.add('active');
    }
  }

  function populateSideModal(provider) {
    document.getElementById('modal-provider-name').textContent = provider.provider_name;
    
    const statusBadge = provider.is_active ? 
      '<span class="badge badge-active">✓ Active</span>' : 
      '<span class="badge badge-inactive">✕ Inactive</span>';
    document.getElementById('modal-provider-status').innerHTML = statusBadge;

    const providerPlans = allPlans.filter(p => p.provider_id === provider.id);
    if (providerPlans.length > 0) {
      const plansList = providerPlans.map(plan => `
        <div class="modal-detail-item">
          <div class="modal-detail-item-title">${plan.plan_name}</div>
          <div class="modal-detail-item-meta">
            <span>${createCoverageBadges(plan.coverage_type)}</span>
          </div>
        </div>
      `).join('');
      document.getElementById('modal-plans-list').innerHTML = plansList;
    }

    document.getElementById('modal-enrollment-count').textContent = provider.enrolled_employees || 0;
  }

  function filterPlans() {
    const searchTerm = document.getElementById('search-plans').value.toLowerCase();
    const currentTab = document.querySelector('.tab-btn.active').getAttribute('onclick').match(/'([^']+)'/)[1];
    
    if (currentTab === 'by-provider') {
      const filtered = allProviders.filter(p => 
        p.provider_name.toLowerCase().includes(searchTerm)
      );
      renderByProviderTable(filtered);
    } else if (currentTab === 'active') {
      const filtered = allPlans.filter(p => 
        p.is_active && p.plan_name.toLowerCase().includes(searchTerm)
      );
      renderActivePlansTable(filtered);
    }
  }

  function renderByProviderTable(providers) {
    const tbody = document.getElementById('by-provider-tbody');
    if (providers.length > 0) {
      tbody.innerHTML = providers.map(provider => {
        const providerPlans = allPlans.filter(p => p.provider_id === provider.id);
        const activePlans = providerPlans.filter(p => p.is_active).length;
        const totalPlans = providerPlans.length;
        const planTypes = [...new Set(providerPlans.map(p => p.plan_type))];
        
        return `
          <tr onclick="viewProviderModal(${provider.id})">
            <td>
              <div class="provider-cell">
                <div class="provider-avatar">${getInitials(provider.provider_name)}</div>
                <div class="provider-info">
                  <strong>${provider.provider_name}</strong>
                  <span class="provider-code">${provider.provider_code || '-'}</span>
                </div>
              </div>
            </td>
            <td>
              <span class="clickable-number" onclick="event.stopPropagation(); alert('View ${totalPlans} plans')">${totalPlans}</span>
            </td>
            <td>
              <span class="badge badge-active">${activePlans}</span>
            </td>
            <td>
              <div class="stacked-tags">
                ${planTypes.map(type => `<span class="badge badge-${type?.toLowerCase() || 'basic'}">${type || 'Basic'}</span>`).join('')}
              </div>
            </td>
            <td style="text-align: center;">
              <div class="actions-cell">
                <div class="action-tooltip">
                  <button class="btn-icon" onclick="event.stopPropagation(); viewProviderModal(${provider.id})" title="View">
                    👁️
                  </button>
                  <span class="tooltip-text">View</span>
                </div>
                <div class="action-tooltip">
                  <button class="btn-icon" onclick="event.stopPropagation(); alert('Edit provider')" title="Edit">
                    ✏️
                  </button>
                  <span class="tooltip-text">Edit</span>
                </div>
                <div class="action-tooltip">
                  <button class="btn-icon" onclick="event.stopPropagation(); alert('Deactivate provider')" title="Deactivate">
                    🚫
                  </button>
                  <span class="tooltip-text">Deactivate</span>
                </div>
              </div>
            </td>
          </tr>
        `;
      }).join('');
    } else {
      tbody.innerHTML = `
        <tr>
          <td colspan="5">
            <div class="empty-state">
              <div class="empty-state-icon">🔍</div>
              <div class="empty-state-title">No providers found</div>
              <div class="empty-state-message">Try adjusting your search</div>
            </div>
          </td>
        </tr>
      `;
    }
  }

  function renderActivePlansTable(plans) {
    const tbody = document.getElementById('active-plans-tbody');
    if (plans.length > 0) {
      tbody.innerHTML = plans.map(plan => `
        <tr>
          <td><strong>${plan.plan_name}</strong></td>
          <td class="td-muted">${plan.provider_name || '-'}</td>
          <td>
            <span class="badge badge-${plan.plan_type?.toLowerCase() || 'standard'}">
              ${plan.plan_type || 'Standard'}
            </span>
          </td>
          <td><strong>${formatCurrency(plan.annual_premium_per_employee)}</strong></td>
          <td class="td-muted">${plan.in_network_hospitals || '0'} hospitals</td>
          <td class="td-muted">${plan.in_network_doctors || '0'} doctors</td>
          <td style="text-align: center;">
            <div class="actions-cell">
              <div class="action-tooltip">
                <button class="btn-icon" onclick="alert('View plan details')" title="View">
                  👁️
                </button>
                <span class="tooltip-text">View</span>
              </div>
              <div class="action-tooltip">
                <button class="btn-icon" onclick="alert('Edit plan')" title="Edit">
                  ✏️
                </button>
                <span class="tooltip-text">Edit</span>
              </div>
            </div>
          </td>
        </tr>
      `).join('');
    } else {
      tbody.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-state-icon">✨</div>
              <div class="empty-state-title">No active plans found</div>
            </div>
          </td>
        </tr>
      `;
    }
  }

  function loadActivePlans() {
    const tbody = document.getElementById('active-plans-tbody');
    // Show loading state
    tbody.innerHTML = `
      <tr>
        <td colspan="7" style="text-align: center; padding: 2rem;">
          <div class="spinner"></div>
          <p>Loading plans...</p>
        </td>
      </tr>
    `;
    
    fetch('modules/hmo/api.php?action=getPlans')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data.length > 0) {
          allPlans = data.data;
          renderActivePlansTable(data.data);
          document.getElementById('count-active').textContent = data.data.length;
        } else {
          document.getElementById('active-plans-tbody').innerHTML = `
            <tr>
              <td colspan="7">
                <div class="empty-state">
                  <div class="empty-state-icon">✨</div>
                  <div class="empty-state-title">No active plans</div>
                </div>
              </td>
            </tr>
          `;
        }
      })
      .catch(error => {
        console.error('Error loading active plans:', error);
        document.getElementById('active-plans-tbody').innerHTML = `
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <div class="empty-state-icon">⚠️</div>
                <div class="empty-state-title">Error loading plans</div>
              </div>
            </td>
          </tr>
        `;
      });
  }

  function loadPlansByProvider() {
    const tbody = document.getElementById('by-provider-tbody');
    // Show loading state
    tbody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; padding: 2rem;">
          <div class="spinner"></div>
          <p>Loading plans by provider...</p>
        </td>
      </tr>
    `;
    
    fetch('modules/hmo/api.php?action=getPlans')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data.length > 0) {
          allPlans = data.data;
          renderByProviderTable(data.data);
          document.getElementById('count-provider').textContent = data.data.length;
        } else {
          document.getElementById('by-provider-tbody').innerHTML = `
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="empty-state-icon">📭</div>
                  <div class="empty-state-title">No plans configured</div>
                </div>
              </td>
            </tr>
          `;
        }
      })
      .catch(error => {
        console.error('Error loading plans by provider:', error);
        document.getElementById('by-provider-tbody').innerHTML = `
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <div class="empty-state-icon">⚠️</div>
                <div class="empty-state-title">Error loading plans</div>
              </div>
            </td>
          </tr>
        `;
      });
  }

  function loadPlansByCoverage() {
    const tbody = document.getElementById('by-coverage-tbody');
    // Show loading state
    tbody.innerHTML = `
      <tr>
        <td colspan="5" style="text-align: center; padding: 2rem;">
          <div class="spinner"></div>
          <p>Loading coverage data...</p>
        </td>
      </tr>
    `;
    
    fetch('modules/hmo/api.php?action=getPlans')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data.length > 0) {
          tbody.innerHTML = data.data.map(coverage => {
            const avgABL = coverage.plans ? 
              coverage.plans.reduce((sum, p) => sum + (p.annual_benefit_limit || 0), 0) / coverage.plans.length : 0;
            
            return `
              <tr>
                <td>
                  <span class="badge ${COVERAGE_TYPES[coverage.coverage_type]?.badge || 'badge-inpatient'}">
                    ${COVERAGE_TYPES[coverage.coverage_type]?.icon} ${COVERAGE_TYPES[coverage.coverage_type]?.label || coverage.coverage_type}
                  </span>
                </td>
                <td class="td-muted">${coverage.plans ? coverage.plans.length : 0} plans</td>
                <td class="td-muted">${coverage.providers ? coverage.providers.length : 0} providers</td>
                <td><strong>${formatCurrency(avgABL)}</strong></td>
                <td style="text-align: center;">
                  <div class="action-tooltip">
                    <button class="btn-icon" onclick="alert('View coverage plans')" title="View Plans">
                      📋
                    </button>
                    <span class="tooltip-text">View</span>
                  </div>
                </td>
              </tr>
            `;
          }).join('');
          document.getElementById('count-coverage').textContent = data.data.length;
        } else {
          tbody.innerHTML = `
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="empty-state-icon">📭</div>
                  <div class="empty-state-title">No coverage data</div>
                </div>
              </td>
            </tr>
          `;
        }
      })
      .catch(error => {
        console.error('Error loading coverage data:', error);
        tbody.innerHTML = `
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <div class="empty-state-icon">⚠️</div>
                <div class="empty-state-title">Error loading coverage data</div>
              </div>
            </td>
          </tr>
        `;
      });
  }

  function loadStats() {
    fetch('modules/hmo/api.php?action=getPlans')
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data.length > 0) {
          allPlans = data.data;
          const totalCount = data.data.length;
          const activeCount = data.data.filter(p => p.is_active).length;
          const inactiveCount = data.data.filter(p => !p.is_active).length;
          const providersSet = new Set(data.data.map(p => p.provider_id));

          document.getElementById('total-plans').textContent = totalCount;
          document.getElementById('active-plans').textContent = activeCount;
          document.getElementById('inactive-plans').textContent = inactiveCount;
          document.getElementById('providers-count').textContent = providersSet.size;
        }
      })
      .catch(error => console.error('Error loading stats:', error));
  }

  function showAddPlanModal() {
    alert('Add plan modal coming soon');
  }

  function editProvider() {
    alert('Edit provider coming soon');
    closeSideModal();
  }

  function viewProviderDetails() {
    alert('View provider details coming soon');
    closeSideModal();
  }

  function deactivateProvider() {
    alert('Deactivate provider coming soon');
    closeSideModal();
  }

  // Close modal when clicking overlay
  // Initialize on page load (runs immediately for dynamic loading)
  function initializePlans() {
    // Restore previously selected tab or default to 'by-provider'
    const savedTab = localStorage.getItem('selectedPlanTab') || 'by-provider';
    
    // Hide all tabs first
    document.getElementById('active-tab').style.display = 'none';
    document.getElementById('by-provider-tab').style.display = 'none';
    document.getElementById('by-coverage-tab').style.display = 'none';
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(savedTab + '-tab').style.display = 'block';
    const tabBtn = document.querySelector(`.tab-btn[onclick="switchPlanTab(event, '${savedTab}')"]`);
    if (tabBtn) tabBtn.classList.add('active');
    
    // Load data for the tab
    if (savedTab === 'active') {
      loadActivePlans();
    } else if (savedTab === 'by-provider') {
      loadPlansByProvider();
    } else if (savedTab === 'by-coverage') {
      loadPlansByCoverage();
    }
    
    // Always load stats for counters
    loadStats();
  }
  
  // Initialize on page load
  setTimeout(() => {
    try {
      initializePlans();
    } catch (e) {
      console.error('Error initializing plans:', e);
      // Fallback to default
      loadStats();
      loadPlansByProvider();
    }
  }, 100);
</script>
