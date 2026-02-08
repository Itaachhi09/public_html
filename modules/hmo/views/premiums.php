<?php
/**
 * Premium and Cost Sharing Management View
 * 
 * Manage premium structures, cost sharing rules, payroll deductions, and adjustments
 * Features:
 * - Premium configuration per plan
 * - Cost sharing rules (fixed/percentage based)
 * - Employer vs employee sharing
 * - Coverage type variations
 * - Payroll deduction tracking
 * - Premium adjustments & retro computations
 */
?>
<div class="main-content">
  <style>
    /* Layout & Container Styles */
    .card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
    }

    .card-header {
      padding: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card-title {
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
      margin: 0;
    }

    .card-content {
      padding: 1.5rem;
    }

    /* Filter Bar & Tabs */
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
      margin: 0 -1.5rem 0 -1.5rem;
      padding-left: calc(1.5rem + 1rem);
      padding-right: calc(1.5rem + 1rem);
    }

    .search-box {
      position: relative;
      flex: 1;
      max-width: 280px;
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

    .filter-select {
      padding: 0.5rem 1rem;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      font-size: 13px;
      background: white;
      cursor: pointer;
      transition: border-color 0.2s ease;
    }

    .filter-select:focus {
      outline: none;
      border-color: #1e40af;
    }

    .tabs {
      display: flex;
      gap: 0.5rem;
      flex: 1;
      margin: 0;
      border-bottom: none;
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
      margin: 0;
    }

    .tab-btn:hover:not(:disabled) {
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

    /* Statistics */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
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

    .stat-card.accent-pending {
      border-left-color: #f59e0b;
    }

    .stat-card.accent-info {
      border-left-color: #3b82f6;
    }

    .stat-card.accent-warning {
      border-left-color: #ef4444;
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

    .stat-value {
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
      font-weight: 600;
      color: #9ca3af;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Tables */
    .premium-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    .premium-table thead {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
    }

    .premium-table th {
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .premium-table tbody tr {
      border-bottom: 1px solid #e5e7eb;
      transition: background 0.15s ease;
      cursor: pointer;
    }

    .premium-table tbody tr:hover {
      background: #f0f9ff;
    }

    .premium-table td {
      padding: 1.25rem 1rem;
      color: #374151;
      font-size: 14px;
    }

    /* Plan Cell with Badge */
    .plan-cell {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .plan-badge {
      display: inline-flex;
      padding: 0.35rem 0.85rem;
      background: #e0e7ff;
      color: #3730a3;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }

    .premium-value {
      font-weight: 600;
      color: #111827;
      font-family: monospace;
      font-size: 14px;
    }

    .percentage-value {
      color: #6b7280;
      font-size: 13px;
    }

    .sharing-breakdown {
      display: flex;
      gap: 0.5rem;
      flex-direction: column;
    }

    .sharing-item {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      padding: 0.25rem 0;
    }

    .btn-icon {
      background: transparent;
      color: #6b7280;
      width: 32px;
      height: 32px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      border: none;
      transition: all 0.2s ease;
    }

    .btn-icon:hover {
      background: #f3f4f6;
      color: #1e40af;
    }

    .actions-cell {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    /* Badges */
    .badge {
      display: inline-flex;
      align-items: center;
      padding: 0.35rem 0.85rem;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
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

    .badge-percentage {
      background: #fef3c7;
      color: #92400e;
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: #6b7280;
    }

    .empty-icon {
      font-size: 48px;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .empty-title {
      font-size: 16px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
    }

    .empty-message {
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 1.5rem;
    }

    /* Buttons */
    .btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .btn-primary {
      background: #1e40af;
      color: white;
    }

    .btn-primary:hover {
      background: #1e3a8a;
    }

    .btn-secondary {
      background: #6b7280;
      color: white;
    }

    .btn-secondary:hover {
      background: #4b5563;
    }

    .btn-sm {
      padding: 0.35rem 0.75rem;
      font-size: 12px;
    }

    /* Side Modal */
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

    .modal-title {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      color: #111827;
    }

    .modal-close {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: #6b7280;
      transition: color 0.2s ease;
      padding: 0;
    }

    .modal-close:hover {
      color: #111827;
    }

    .modal-content {
      padding: 1.5rem;
    }

    .modal-section {
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
    }

    .modal-section:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .section-title {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 0.75rem;
      letter-spacing: 0.5px;
    }

    .section-content {
      font-size: 14px;
      color: #374151;
      line-height: 1.6;
    }

    .section-content strong {
      display: block;
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .section-content .label {
      font-size: 12px;
      color: #9ca3af;
      margin-bottom: 0.25rem;
    }

    .premium-breakdown {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .breakdown-item {
      padding: 0.75rem;
      background: #f9fafb;
      border-radius: 6px;
    }

    .breakdown-label {
      font-size: 11px;
      color: #6b7280;
      font-weight: 500;
      margin-bottom: 0.25rem;
    }

    .breakdown-value {
      font-size: 16px;
      font-weight: 600;
      color: #1f2937;
      font-family: monospace;
    }

    .modal-actions {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      padding-top: 1.5rem;
    }

    .modal-actions .btn {
      width: 100%;
      text-align: center;
    }
  </style>

  <!-- Stats Section -->
  <div class="stats-grid" id="statsGrid">
    <div class="stat-card accent-active">
      <div class="stat-icon">💰</div>
      <div class="stat-value" id="totalPremiums">0</div>
      <div class="stat-subtitle">Total premiums</div>
      <div class="stat-label">Configured plans</div>
    </div>
    <div class="stat-card accent-info">
      <div class="stat-icon">📊</div>
      <div class="stat-value" id="activeCount">0</div>
      <div class="stat-subtitle">Active premiums</div>
      <div class="stat-label">Currently managed</div>
    </div>
    <div class="stat-card accent-pending">
      <div class="stat-icon">💳</div>
      <div class="stat-value" id="deductionCount">0</div>
      <div class="stat-subtitle">Payroll deductions</div>
      <div class="stat-label">Active deductions</div>
    </div>
    <div class="stat-card accent-warning">
      <div class="stat-icon">⚙️</div>
      <div class="stat-value" id="adjustmentCount">0</div>
      <div class="stat-subtitle">Adjustments</div>
      <div class="stat-label">Recorded this year</div>
    </div>
  </div>

  <!-- Main Card -->
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">Premium and Cost Sharing Management</h3>
        <p style="text-align: left; font-size: 12px; color: #6b7280; margin-top: 0.25rem;">Configure premium amounts, employer/employee sharing, and payroll deductions</p>
      </div>
      <button class="btn btn-primary" onclick="openAddPremiumModal()">+ New Premium</button>
    </div>

    <div class="card-content">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="search-box">
          <span class="search-box-icon">🔍</span>
          <input type="text" id="search-plan" placeholder="Search plan..." onkeyup="filterPremiums()">
        </div>
        <select class="filter-select" id="filter-status" onchange="filterPremiums()">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <select class="filter-select" id="filter-type" onchange="filterPremiums()">
          <option value="">All Types</option>
          <option value="percentage">Percentage Based</option>
          <option value="fixed">Fixed Amount</option>
        </select>
        <div class="tabs">
          <button class="tab-btn active" onclick="switchTab(event, 'premiums')">Premiums <span class="tab-count" id="count-premiums">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'deductions')">Deductions <span class="tab-count" id="count-deductions">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'adjustments')">Adjustments <span class="tab-count" id="count-adjustments">0</span></button>
        </div>
      </div>

      <!-- Premiums Tab -->
      <div id="premiums-tab" class="tab-content" style="display: block;">
        <table class="premium-table">
          <thead>
            <tr>
              <th>Plan Name</th>
              <th>Total Premium</th>
              <th>Employer Share</th>
              <th>Employee Share</th>
              <th>Type</th>
              <th>Enrollments</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="premiums-tbody">
            <tr>
              <td colspan="8" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Deductions Tab -->
      <div id="deductions-tab" class="tab-content" style="display: none;">
        <table class="premium-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Monthly Deduction</th>
              <th>Deduction Date</th>
              <th>Payroll Code</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="deductions-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Adjustments Tab -->
      <div id="adjustments-tab" class="tab-content" style="display: none;">
        <table class="premium-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Type</th>
              <th>Original Amount</th>
              <th>Adjusted Amount</th>
              <th>Reason</th>
              <th>Effective Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="adjustments-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Overlay & Side Modal -->
  <div class="modal-overlay" id="modal-overlay" onclick="closeSideModal()"></div>
  <div class="side-modal" id="side-modal">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-plan-name"></h2>
      <button class="modal-close" onclick="closeSideModal()">✕</button>
    </div>
    <div class="modal-content">
      <!-- Premium Summary -->
      <div class="modal-section">
        <div class="section-title">Premium Configuration</div>
        <div class="premium-breakdown">
          <div class="breakdown-item">
            <div class="breakdown-label">Total Premium</div>
            <div class="breakdown-value" id="modal-total-premium">KES 0</div>
          </div>
          <div class="breakdown-item">
            <div class="breakdown-label">Employer Share</div>
            <div class="breakdown-value" id="modal-employer-share">KES 0</div>
          </div>
          <div class="breakdown-item">
            <div class="breakdown-label">Employee Share</div>
            <div class="breakdown-value" id="modal-employee-share">KES 0</div>
          </div>
          <div class="breakdown-item">
            <div class="breakdown-label">Type</div>
            <div class="breakdown-value" id="modal-premium-type">Fixed</div>
          </div>
        </div>
      </div>

      <!-- Cost Sharing Rules -->
      <div class="modal-section">
        <div class="section-title">Cost Sharing Rules</div>
        <div class="section-content">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>Employee Only:</span>
            <strong id="modal-employee-only-rate">0%</strong>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>With Dependents:</span>
            <strong id="modal-with-dependents-rate">0%</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Dependent Addition:</span>
            <strong id="modal-dependent-rate">0%</strong>
          </div>
        </div>
      </div>

      <!-- Enrollment Statistics -->
      <div class="modal-section">
        <div class="section-title">Plan Statistics</div>
        <div class="section-content">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <span>Total Enrollments:</span>
            <strong id="modal-total-enrollments">0</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Monthly Cost Impact:</span>
            <strong id="modal-monthly-impact">KES 0</strong>
          </div>
        </div>
      </div>

      <!-- Payroll Deduction Info -->
      <div class="modal-section">
        <div class="section-title">Payroll Integration</div>
        <div class="section-content">
          <div style="margin-bottom: 0.75rem;">
            <div class="label">Deduction Code</div>
            <strong id="modal-payroll-code">-</strong>
          </div>
          <div style="margin-bottom: 0.75rem;">
            <div class="label">Deduction Frequency</div>
            <strong id="modal-deduction-frequency">-</strong>
          </div>
          <div>
            <div class="label">Active Deductions</div>
            <strong id="modal-active-deductions">0</strong>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="modal-actions">
        <button class="btn btn-secondary" onclick="editPremium()">✏️ Edit Premium</button>
        <button class="btn btn-primary" onclick="manageCostRules()">📋 Cost Rules</button>
      </div>
    </div>
  </div>
</div>

<script>
  // ============================================
  // Global State
  // ============================================
  if (typeof currentTab === 'undefined') { var currentTab = 'premiums'; }
  currentTab = 'premiums';
  if (typeof currentPremiumId === 'undefined') { var currentPremiumId = null; }
  currentPremiumId = null;
  if (typeof allPremiums === 'undefined') { var allPremiums = []; }
  allPremiums = [];
  if (typeof allDeductions === 'undefined') { var allDeductions = []; }
  allDeductions = [];
  if (typeof allAdjustments === 'undefined') { var allAdjustments = []; }
  allAdjustments = [];

  // ============================================
  // Helper Functions
  // ============================================

  function formatCurrency(amount) {
    const numAmount = parseFloat(amount || 0);
    return 'KES ' + numAmount.toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-KE', { year: 'numeric', month: 'short', day: 'numeric' });
  }

  // ============================================
  // Tab Switching
  // ============================================

  function switchTab(event, tabName) {
    event.preventDefault();
    
    document.querySelectorAll('.tab-content').forEach(tab => {
      tab.style.display = 'none';
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.classList.remove('active');
    });

    document.getElementById(tabName + '-tab').style.display = 'block';
    event.target.closest('.tab-btn').classList.add('active');

    localStorage.setItem('selectedTab-premium', tabName);
    currentTab = tabName;

    loadTabData(tabName);
  }

  function loadTabData(tabName) {
    if (tabName === 'premiums') {
      loadPremiums();
    } else if (tabName === 'deductions') {
      loadDeductions();
    } else if (tabName === 'adjustments') {
      loadAdjustments();
    }
  }

  // ============================================
  // Premium Data Loading
  // ============================================

  function loadPremiums() {
    fetch('modules/hmo/api.php?action=getPlans')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allPremiums = data.data || [];
          renderPremiumsTable(allPremiums);
          updateCounts();
        }
      })
      .catch(error => console.error('Error:', error));
  }

  function loadDeductions() {
    fetch('modules/hmo/api.php?action=getPayrollDeductions')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allDeductions = data.data || [];
          renderDeductionsTable(allDeductions);
        }
      })
      .catch(error => console.error('Error:', error));
  }

  function loadAdjustments() {
    fetch('modules/hmo/api.php?action=getPremiumAdjustments')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allAdjustments = data.data || [];
          renderAdjustmentsTable(allAdjustments);
        }
      })
      .catch(error => console.error('Error:', error));
  }

  // ============================================
  // Table Rendering
  // ============================================

  function renderPremiumsTable(premiums) {
    const tbody = document.getElementById('premiums-tbody');
    
    if (!premiums || premiums.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="8">
            <div class="empty-state">
              <div class="empty-icon">💰</div>
              <div class="empty-title">No premiums configured</div>
              <div class="empty-message">Create a premium configuration to get started</div>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = premiums.map(premium => `
      <tr onclick="openSideModal(${premium.id}, event)">
        <td>
          <span class="plan-badge">${premium.plan_name || 'N/A'}</span>
        </td>
        <td class="premium-value">${formatCurrency(premium.total_premium)}</td>
        <td class="premium-value">${formatCurrency(premium.employer_share)}</td>
        <td class="premium-value">${formatCurrency(premium.employee_share)}</td>
        <td>
          <span class="badge ${premium.is_percentage ? 'badge-percentage' : 'badge-active'}">
            ${premium.is_percentage ? '%' : '₭'}
          </span>
        </td>
        <td style="text-align: center; font-weight: 600;">${premium.total_enrollments || 0}</td>
        <td>
          <span class="badge ${premium.is_active ? 'badge-active' : 'badge-inactive'}">
            ${premium.is_active ? 'Active' : 'Inactive'}
          </span>
        </td>
        <td>
          <div class="actions-cell">
            <button class="btn-icon" onclick="viewPremium(${premium.id}, event)" title="View">👁️</button>
            <button class="btn-icon" onclick="editPremium(${premium.id}, event)" title="Edit">✏️</button>
            <button class="btn-icon" onclick="togglePremium(${premium.id}, event)" title="Toggle">${premium.is_active ? '⏸' : '▶️'}</button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function renderDeductionsTable(deductions) {
    const tbody = document.getElementById('deductions-tbody');
    
    if (!deductions || deductions.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💳</div>No deductions</div></td></tr>';
      return;
    }

    tbody.innerHTML = deductions.map(ded => `
      <tr>
        <td>${ded.employee_name || 'N/A'}</td>
        <td>${ded.plan_name || 'N/A'}</td>
        <td class="premium-value">${formatCurrency(ded.deduction_amount)}</td>
        <td>${formatDate(ded.deduction_date)}</td>
        <td><code>${ded.payroll_code || '-'}</code></td>
        <td><span class="badge ${ded.is_active ? 'badge-active' : 'badge-inactive'}">${ded.is_active ? 'Active' : 'Inactive'}</span></td>
        <td><button class="btn-icon" onclick="viewDeduction(${ded.id}, event)" title="View">👁️</button></td>
      </tr>
    `).join('');
  }

  function renderAdjustmentsTable(adjustments) {
    const tbody = document.getElementById('adjustments-tbody');
    
    if (!adjustments || adjustments.length === 0) {
      tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">⚙️</div>No adjustments</div></td></tr>';
      return;
    }

    tbody.innerHTML = adjustments.map(adj => `
      <tr>
        <td>${adj.employee_name || 'N/A'}</td>
        <td>${adj.adjustment_type || 'N/A'}</td>
        <td class="premium-value">${formatCurrency(adj.original_amount)}</td>
        <td class="premium-value">${formatCurrency(adj.adjusted_amount)}</td>
        <td>${adj.reason || '-'}</td>
        <td>${formatDate(adj.effective_date)}</td>
        <td><button class="btn-icon" onclick="viewAdjustment(${adj.id}, event)" title="View">👁️</button></td>
      </tr>
    `).join('');
  }

  // ============================================
  // Side Modal
  // ============================================

  function openSideModal(premiumId, event) {
    event.stopPropagation();
    currentPremiumId = premiumId;

    fetch(`modules/hmo/api.php?action=getPremiumDetails&id=${premiumId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data) {
          populateSideModal(data.data);
          document.getElementById('modal-overlay').classList.add('active');
          document.getElementById('side-modal').classList.add('active');
        }
      })
      .catch(error => console.error('Error:', error));
  }

  function populateSideModal(premium) {
    document.getElementById('modal-plan-name').textContent = premium.plan_name || 'Premium';
    document.getElementById('modal-total-premium').textContent = formatCurrency(premium.total_premium);
    document.getElementById('modal-employer-share').textContent = formatCurrency(premium.employer_share);
    document.getElementById('modal-employee-share').textContent = formatCurrency(premium.employee_share);
    document.getElementById('modal-premium-type').textContent = premium.is_percentage ? 'Percentage Based' : 'Fixed Amount';
    document.getElementById('modal-employee-only-rate').textContent = premium.employee_only_rate + '%';
    document.getElementById('modal-with-dependents-rate').textContent = premium.with_dependents_rate + '%';
    document.getElementById('modal-dependent-rate').textContent = premium.dependent_rate + '%';
    document.getElementById('modal-total-enrollments').textContent = premium.total_enrollments || 0;
    document.getElementById('modal-monthly-impact').textContent = formatCurrency((premium.total_premium * (premium.total_enrollments || 0)));
    document.getElementById('modal-payroll-code').textContent = premium.payroll_code || '-';
    document.getElementById('modal-deduction-frequency').textContent = premium.deduction_frequency || '-';
    document.getElementById('modal-active-deductions').textContent = premium.active_deductions || 0;
  }

  function closeSideModal() {
    document.getElementById('modal-overlay').classList.remove('active');
    document.getElementById('side-modal').classList.remove('active');
  }

  // ============================================
  // Actions
  // ============================================

  function openAddPremiumModal() {
    alert('Add premium modal coming soon');
  }

  function viewPremium(id, event) {
    event.stopPropagation();
    openSideModal(id, { stopPropagation: () => {} });
  }

  function editPremium(id = null, event = null) {
    if (event) event.stopPropagation();
    alert('Edit premium coming soon');
  }

  function togglePremium(id, event) {
    event.stopPropagation();
    alert('Toggle premium coming soon');
  }

  function viewDeduction(id, event) {
    event.stopPropagation();
    alert('View deduction coming soon');
  }

  function viewAdjustment(id, event) {
    event.stopPropagation();
    alert('View adjustment coming soon');
  }

  function manageCostRules() {
    alert('Manage cost rules coming soon');
  }

  // ============================================
  // Search & Filter
  // ============================================

  function filterPremiums() {
    const searchValue = document.getElementById('search-plan').value.toLowerCase();
    const statusValue = document.getElementById('filter-status').value;
    const typeValue = document.getElementById('filter-type').value;

    const filtered = allPremiums.filter(premium => {
      const matchesName = (premium.plan_name || '').toLowerCase().includes(searchValue);
      const matchesStatus = !statusValue || (statusValue === 'active') === premium.is_active;
      const matchesType = !typeValue || (typeValue === 'percentage') === premium.is_percentage;
      
      return matchesName && matchesStatus && matchesType;
    });

    renderPremiumsTable(filtered);
  }

  // ============================================
  // Count Updates
  // ============================================

  function updateCounts() {
    document.getElementById('count-premiums').textContent = (allPremiums || []).length;
    document.getElementById('totalPremiums').textContent = (allPremiums || []).length;
    document.getElementById('activeCount').textContent = (allPremiums || []).filter(p => p.is_active).length;
    document.getElementById('deductionCount').textContent = (allDeductions || []).filter(d => d.is_active).length;
    document.getElementById('adjustmentCount').textContent = (allAdjustments || []).length;
  }

  // ============================================
  // Initialization
  // ============================================

  // Initialize on page load (runs immediately for dynamic loading)
  currentTab = 'premiums';
  loadPremiums();
  updateCounts();

  document.getElementById('modal-overlay').addEventListener('click', closeSideModal);

  window.onclick = function(event) {
    if (event.target.id === 'modal-overlay') {
      closeSideModal();
    }
  };
</script>
