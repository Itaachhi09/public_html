<?php
/**
 * Claims and Utilization Management View
 * 
 * Monitor HMO claims, benefit usage, and cost trends
 * Features:
 * - Claim records and status tracking
 * - Annual benefit limit monitoring
 * - High utilization alerts
 * - Provider cost comparison
 * - Service type analysis
 * - Cost trend reporting
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

    .stat-card.accent-claims {
      border-left-color: #3b82f6;
    }

    .stat-card.accent-approved {
      border-left-color: #10b981;
    }

    .stat-card.accent-pending {
      border-left-color: #f59e0b;
    }

    .stat-card.accent-utilization {
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

    /* Filter Bar */
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
      flex-wrap: wrap;
    }

    .search-box {
      position: relative;
      flex: 1;
      min-width: 200px;
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
      min-width: 300px;
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
      white-space: nowrap;
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

    /* Tables */
    .claims-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    .claims-table thead {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
    }

    .claims-table th {
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .claims-table tbody tr {
      border-bottom: 1px solid #e5e7eb;
      transition: background 0.15s ease;
      cursor: pointer;
    }

    .claims-table tbody tr:hover {
      background: #f0f9ff;
    }

    .claims-table td {
      padding: 1.25rem 1rem;
      color: #374151;
      font-size: 14px;
    }

    .claims-table td:last-child {
      padding: 1.25rem 1rem;
      text-align: center;
    }

    .employee-cell {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .employee-avatar {
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

    .employee-info strong {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #111827;
    }

    .employee-info .employee-id {
      font-size: 12px;
      color: #9ca3af;
    }

    .claim-amount {
      font-weight: 600;
      color: #111827;
    }

    .service-type-badge {
      display: inline-flex;
      padding: 0.35rem 0.85rem;
      background: #e0e7ff;
      color: #3730a3;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }

    /* Badge Styles */
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

    .badge-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-approved {
      background: #dcfce7;
      color: #166534;
    }

    .badge-rejected {
      background: #fee2e2;
      color: #991b1b;
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

    .btn-icon {
      background: transparent;
      color: #6b7280;
      width: 40px;
      height: 40px;
      padding: 4px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      font-size: 18px;
      cursor: pointer;
      border: 1px solid transparent;
      transition: all 0.2s ease;
      min-width: 40px;
      line-height: 1;
      vertical-align: middle;
    }

    .btn-icon:hover {
      background: #f3f4f6;
      color: #1e40af;
      border-color: #d1d5db;
    }

    .btn-icon:active {
      transform: scale(0.95);
    }

    .btn-icon:focus {
      outline: 2px solid #1e40af;
      outline-offset: 2px;
    }

    .actions-cell {
      display: flex;
      gap: 0.5rem;
      align-items: center;
      justify-content: center;
      width: 80px;
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

    /* Chart Container */
    .chart-container {
      margin-top: 2rem;
      padding: 1.5rem;
      background: #f9fafb;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
      min-height: 300px;
    }

    .chart-title {
      font-size: 14px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 1rem;
    }

    /* Modal Styles */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .modal-overlay.active {
      display: block;
      opacity: 1;
      visibility: visible;
    }

    .side-modal {
      display: none;
      position: fixed;
      top: 0;
      right: 0;
      width: 450px;
      height: 100vh;
      background: white;
      box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
      z-index: 1001;
      transform: translateX(500px);
      transition: transform 0.3s ease;
      overflow-y: auto;
    }

    .side-modal.active {
      display: block;
      transform: translateX(0);
    }

    .modal-content {
      position: relative;
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    .modal-header {
      padding: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-shrink: 0;
    }

    .modal-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      color: #111827;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 20px;
      color: #6b7280;
      cursor: pointer;
      padding: 0.5rem;
      transition: color 0.2s ease;
    }

    .close-btn:hover {
      color: #111827;
    }

    .modal-body {
      padding: 1.5rem;
      overflow-y: auto;
      flex-grow: 1;
    }

    .edit-form-modal {
      display: none;
      position: fixed;
      top: 20px;
      right: 20px;
      width: 450px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 1001;
      max-height: calc(100vh - 40px);
      overflow-y: auto;
    }

    .edit-form-modal.show {
      display: flex;
      flex-direction: column;
    }

    #reject-modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    #reject-modal-overlay.show {
      display: block;
      opacity: 1;
      visibility: visible;
    }

    /* Form Styles */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
      font-size: 14px;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      font-family: inherit;
      transition: border-color 0.2s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: #1e40af;
      box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 120px;
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid #e5e7eb;
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 14px;
    }

    .btn-primary {
      background: #1e40af;
      color: white;
    }

    .btn-primary:hover {
      background: #1e3a8a;
    }

    .btn-secondary {
      background: #e5e7eb;
      color: #374151;
    }

    .btn-secondary:hover {
      background: #d1d5db;
    }

    /* Detail Lists */
    .detail-group {
      margin-bottom: 1.5rem;
    }

    .detail-label {
      font-size: 12px;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 0.25rem;
    }

    .detail-value {
      font-size: 14px;
      color: #111827;
    }

    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
    }

    .badge-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-approved {
      background: #dcfce7;
      color: #166534;
    }

    .badge-rejected {
      background: #fee2e2;
      color: #b91c1c;
    }

    .badge-active {
      background: #dcfce7;
      color: #166534;
    }

    .badge-inactive {
      background: #f3f4f6;
      color: #6b7280;
    }
  </style>

  <!-- Stats Section -->
  <div class="stats-grid" id="statsGrid">
    <div class="stat-card accent-claims">
      <div class="stat-icon">📋</div>
      <div class="stat-value" id="totalClaims">0</div>
      <div class="stat-subtitle">Total claims</div>
      <div class="stat-label">Current year</div>
    </div>
    <div class="stat-card accent-approved">
      <div class="stat-icon">✓</div>
      <div class="stat-value" id="approvedClaims">0</div>
      <div class="stat-subtitle">Approved claims</div>
      <div class="stat-label">Processed</div>
    </div>
    <div class="stat-card accent-pending">
      <div class="stat-icon">⏳</div>
      <div class="stat-value" id="pendingClaims">0</div>
      <div class="stat-subtitle">Pending claims</div>
      <div class="stat-label">Awaiting review</div>
    </div>
    <div class="stat-card accent-utilization">
      <div class="stat-icon">📊</div>
      <div class="stat-value" id="claimAmount">PHP 0</div>
      <div class="stat-subtitle">Total claims amount</div>
      <div class="stat-label">Cost exposure</div>
    </div>
  </div>

  <!-- Main Card -->
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">Claims and Utilization Management</h3>
        <p style="text-align: left; font-size: 12px; color: #6b7280; margin-top: 0.25rem;">Monitor HMO claims, usage patterns, and benefit exhaustion</p>
      </div>
    </div>

    <div class="card-content">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="search-box">
          <span class="search-box-icon">🔍</span>
          <input type="text" id="search-employee" placeholder="Search employee..." onkeyup="filterClaims()">
        </div>
        <select class="filter-select" id="filter-service-type" onchange="filterClaims()">
          <option value="">All Services</option>
          <option value="inpatient">Inpatient</option>
          <option value="outpatient">Outpatient</option>
          <option value="emergency">Emergency</option>
          <option value="dental">Dental</option>
          <option value="other">Other</option>
        </select>
        <select class="filter-select" id="filter-status" onchange="filterClaims()">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <div class="tabs">
          <button class="tab-btn active" onclick="switchTab(event, 'all-claims')">All <span class="tab-count" id="count-all">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'high-utilization')">High Utilization <span class="tab-count" id="count-high">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'provider-comparison')">Providers <span class="tab-count" id="count-providers">0</span></button>
        </div>
      </div>

      <!-- All Claims Tab -->
      <div id="all-claims-tab" class="tab-content" style="display: block;">
        <table class="claims-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Service Type</th>
              <th>Date of Service</th>
              <th>Claim Amount</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="all-claims-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- High Utilization Tab -->
      <div id="high-utilization-tab" class="tab-content" style="display: none;">
        <div style="padding: 1rem; background: #ecf0ff; border-left: 4px solid #1e40af; border-radius: 4px; margin-bottom: 1rem;">
          <strong style="color: #1e3a8a;">ℹ️ High Utilization Alert</strong>
          <p style="color: #1e3a8a; font-size: 12px; margin-top: 0.25rem;">Employees with benefit usage exceeding 80% of annual limits</p>
        </div>
        <table class="claims-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Annual Limit</th>
              <th>Amount Used</th>
              <th>Utilization %</th>
              <th>Remaining</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="high-utilization-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Provider Comparison Tab -->
      <div id="provider-comparison-tab" class="tab-content" style="display: none;">
        <table class="claims-table">
          <thead>
            <tr>
              <th>Provider</th>
              <th>Total Claims</th>
              <th>Claim Count</th>
              <th>Average Claim</th>
              <th>Market Share</th>
              <th>Unique Patients</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="provider-comparison-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Side Modal for Viewing Claim Details -->
    <div class="modal-overlay" id="modal-overlay"></div>
    <div class="side-modal" id="side-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Claim Details</h3>
          <button class="close-btn" onclick="closeSideModal()">✕</button>
        </div>
        <div class="modal-body">
          <div id="claim-details-content">Loading...</div>
        </div>
      </div>
    </div>

    <!-- Modal for Rejecting Claims -->
    <div id="reject-modal-overlay" class="modal-overlay"></div>
    <div class="edit-form-modal" id="reject-claim-modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Reject Claim</h3>
          <button class="close-btn" onclick="closeRejectModal()">✕</button>
        </div>
        <div class="modal-body">
          <form onsubmit="submitRejectClaim(event)">
            <div class="form-group">
              <label>Rejection Reason</label>
              <textarea id="rejection-reason" placeholder="Provide reason for rejection" required></textarea>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
              <button type="submit" class="btn btn-primary">Reject Claim</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Global variables
  var currentClaimId = null;
  if (typeof allClaims === 'undefined') { var allClaims = []; }
  allClaims = [];
  if (typeof allHighUtilization === 'undefined') { var allHighUtilization = []; }
  allHighUtilization = [];
  if (typeof allProviders === 'undefined') { var allProviders = []; }
  allProviders = [];
  if (typeof currentTab === 'undefined') { var currentTab = 'all-claims'; }
  currentTab = 'all-claims';

  // ============================================
  // Helper Functions
  // ============================================
  
  function getInitials(firstName, lastName) {
    return ((firstName || '')[0] + (lastName || '')[0]).toUpperCase();
  }

  function formatCurrency(amount) {
    const numAmount = parseFloat(amount || 0);
    return 'PHP ' + numAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
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

    const tabElement = document.getElementById(tabName + '-tab');
    if (tabElement) {
      tabElement.style.display = 'block';
    }
    event.target.closest('.tab-btn').classList.add('active');

    localStorage.setItem('selectedTab-claims', tabName);
    currentTab = tabName;

    loadTabData(tabName);
  }

  function loadTabData(tabName) {
    switch(tabName) {
      case 'all-claims':
        loadAllClaims();
        break;
      case 'high-utilization':
        loadHighUtilizationEmployees();
        break;
      case 'provider-comparison':
        loadProviderComparison();
        break;
    }
  }

  // ============================================
  // Data Loading Functions
  // ============================================

  function loadAllClaims() {
    console.log('Loading claims...');
    fetch('modules/hmo/api.php?action=getAllClaims')
      .then(response => response.json())
      .then(data => {
        console.log('Claims data received:', data);
        if (data.success && data.data) {
          allClaims = data.data || [];
          console.log('Rendering', allClaims.length, 'claims');
          renderClaimsTable(allClaims, 'all-claims-tbody');
          updateCounts();
        } else {
          console.log('No success or empty data:', data);
        }
      })
      .catch(error => console.error('Error:', error));
  }

  function loadHighUtilizationEmployees() {
    fetch('modules/hmo/api.php?action=getHighUtilizationEmployees')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allHighUtilization = data.data || [];
          renderHighUtilization(allHighUtilization, 'high-utilization-tbody');
        }
      })
      .catch(error => console.error('Error:', error));
  }

  function loadProviderComparison() {
    fetch('modules/hmo/api.php?action=getProviderComparison')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allProviders = data.data || [];
          renderProviderComparison(allProviders, 'provider-comparison-tbody');
        }
      })
      .catch(error => console.error('Error:', error));
  }

  // ============================================
  // Table Rendering
  // ============================================

  function renderClaimsTable(claims, tbodyId) {
    const tbody = document.getElementById(tbodyId);
    
    if (!claims || claims.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-icon">📋</div>
              <div class="empty-title">No claims found</div>
              <div class="empty-message">No claims recorded for the selected criteria</div>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = claims.map(claim => {
      const firstName = claim.employee_name ? claim.employee_name.split(' ')[0] : '';
      const lastName = claim.employee_name ? claim.employee_name.split(' ')[1] || '' : '';
      const initials = getInitials(firstName, lastName);

      return `
        <tr>
          <td onclick="viewClaimDetails(${claim.id}, event)">
            <div class="employee-cell">
              <div class="employee-avatar">${initials}</div>
              <div class="employee-info">
                <strong>${claim.employee_name || 'N/A'}</strong>
                <div class="employee-id">${claim.employee_code || 'N/A'}</div>
              </div>
            </div>
          </td>
          <td onclick="viewClaimDetails(${claim.id}, event)">${claim.plan_name || 'N/A'}</td>
          <td onclick="viewClaimDetails(${claim.id}, event)">
            <span class="service-type-badge">${(claim.service_type || 'N/A').replace(/_/g, ' ')}</span>
          </td>
          <td onclick="viewClaimDetails(${claim.id}, event)">${formatDate(claim.service_date)}</td>
          <td class="claim-amount" onclick="viewClaimDetails(${claim.id}, event)">${formatCurrency(claim.claim_amount)}</td>
          <td onclick="viewClaimDetails(${claim.id}, event)">
            <span class="badge badge-${claim.claim_status || 'pending'}">${claim.claim_status ? claim.claim_status.toUpperCase() : 'PENDING'}</span>
          </td>
          <td>
            <div class="actions-cell">
              <button class="btn-icon" style="display: inline-flex !important;" onclick="viewClaimDetails(${claim.id}, event)" title="View Details">👁️</button>
              ${claim.claim_status && claim.claim_status.toLowerCase() === 'pending' ? `<button class="btn-icon" style="display: inline-flex !important;" onclick="openRejectModal(${claim.id}, event)" title="Reject Claim">✕</button>` : ''}
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderHighUtilization(employees, tbodyId) {
    const tbody = document.getElementById(tbodyId);
    
    if (!employees || employees.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-icon">✓</div>
              <div class="empty-title">No high utilization alerts</div>
              <div class="empty-message">All employees are within normal usage limits</div>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = employees.map(emp => {
      const initials = getInitials(emp.employee_name.split(' ')[0], emp.employee_name.split(' ')[1] || '');
      const utilizationColor = emp.utilization_percent >= 100 ? '#ef4444' : emp.utilization_percent >= 90 ? '#f59e0b' : '#3b82f6';

      return `
        <tr>
          <td>
            <div class="employee-cell">
              <div class="employee-avatar">${initials}</div>
              <div class="employee-info">
                <strong>${emp.employee_name}</strong>
                <div class="employee-id">${emp.employee_code}</div>
              </div>
            </div>
          </td>
          <td>${emp.plan_name}</td>
          <td>${formatCurrency(emp.benefit_limit)}</td>
          <td>${formatCurrency(emp.amount_used)}</td>
          <td>
            <div style="color: ${utilizationColor}; font-weight: 600;">
              ${emp.utilization_percent}%
            </div>
          </td>
          <td>${formatCurrency(emp.remaining_balance)}</td>
          <td>
            <button class="btn-icon" onclick="viewEmployeeUtilization(${emp.id})" title="Details">📊</button>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderProviderComparison(providers, tbodyId) {
    const tbody = document.getElementById(tbodyId);
    
    if (!providers || providers.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-icon">🏥</div>
              <div class="empty-title">No provider data</div>
              <div class="empty-message">No claims data available for provider comparison</div>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = providers.map(provider => {
      return `
        <tr>
          <td><strong>${provider.provider_name}</strong></td>
          <td>${formatCurrency(provider.total_claims)}</td>
          <td>${provider.claim_count}</td>
          <td>${formatCurrency(provider.average_claim)}</td>
          <td>
            <strong>${provider.market_share}%</strong>
          </td>
          <td>${provider.unique_patients}</td>
          <td>
            <button class="btn-icon" onclick="viewProviderDetails(${provider.id})" title="Details">📊</button>
          </td>
        </tr>
      `;
    }).join('');
  }

  // ============================================
  // Count Badge Updates
  // ============================================

  function updateCounts() {
    document.getElementById('count-all').textContent = (allClaims || []).length;
    document.getElementById('count-high').textContent = (allHighUtilization || []).length;
    document.getElementById('count-providers').textContent = (allProviders || []).length;

    // Update stat cards
    const stats = {
      total_claims: allClaims.length,
      approved_claims: allClaims.filter(c => c.claim_status === 'approved').length,
      pending_claims: allClaims.filter(c => c.claim_status === 'pending').length,
      total_amount: allClaims.reduce((sum, c) => sum + (parseFloat(c.claim_amount) || 0), 0)
    };

    document.getElementById('totalClaims').textContent = stats.total_claims;
    document.getElementById('approvedClaims').textContent = stats.approved_claims;
    document.getElementById('pendingClaims').textContent = stats.pending_claims;
    document.getElementById('claimAmount').textContent = formatCurrency(stats.total_amount);
  }

  // ============================================
  // Filtering
  // ============================================

  function filterClaims() {
    const searchValue = document.getElementById('search-employee').value.toLowerCase();
    const serviceType = document.getElementById('filter-service-type').value;
    const status = document.getElementById('filter-status').value;

    const filtered = allClaims.filter(claim => {
      const matchesName = (claim.employee_name || '').toLowerCase().includes(searchValue);
      const matchesService = !serviceType || claim.service_type === serviceType;
      const matchesStatus = !status || claim.claim_status === status;
      
      return matchesName && matchesService && matchesStatus;
    });

    renderClaimsTable(filtered, 'all-claims-tbody');
  }

  // ============================================
  // Action Functions
  // ============================================

  function viewClaimDetails(claimId, event) {
    if (event) event.stopPropagation();
    currentClaimId = claimId;

    fetch(`modules/hmo/api.php?action=getClaimDetail&id=${claimId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.data) {
          populateSideModal(data.data);
          
          const overlay = document.getElementById('modal-overlay');
          const modal = document.getElementById('side-modal');
          
          if (overlay) overlay.classList.add('active');
          if (modal) modal.classList.add('active');
        } else {
          alert('Error loading claim details');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error loading claim details');
      });
  }

  function populateSideModal(claim) {
    const contentElement = document.getElementById('claim-details-content');
    if (!contentElement) {
      console.error('claim-details-content element not found');
      return;
    }

    const content = `
      <div class="detail-group">
        <div class="detail-label">Claim Number</div>
        <div class="detail-value">${claim.claim_number || claim.id || 'N/A'}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Employee</div>
        <div class="detail-value">${claim.employee_name || 'N/A'}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Employee ID</div>
        <div class="detail-value">${claim.employee_code || 'N/A'}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Plan</div>
        <div class="detail-value">${claim.plan_name || 'N/A'}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Service Type</div>
        <div class="detail-value">${(claim.service_type || 'N/A').replace(/_/g, ' ').toUpperCase()}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Date of Service</div>
        <div class="detail-value">${formatDate(claim.service_date)}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Hospital/Provider</div>
        <div class="detail-value">${claim.hospital_name || claim.provider_name || 'N/A'}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Doctor Name</div>
        <div class="detail-value">${claim.doctor_name || 'N/A'}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Claim Amount</div>
        <div class="detail-value" style="font-weight: 600; color: #1e40af;">${formatCurrency(claim.claim_amount)}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Approved Amount</div>
        <div class="detail-value" style="font-weight: 600; color: #10b981;">${formatCurrency(claim.approved_amount)}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Status</div>
        <div class="detail-value">
          <span class="badge badge-${claim.claim_status || 'pending'}">${(claim.claim_status || 'pending').toUpperCase()}</span>
        </div>
      </div>
      ${claim.rejection_reason ? `
      <div class="detail-group">
        <div class="detail-label">Rejection Reason</div>
        <div class="detail-value">${claim.rejection_reason}</div>
      </div>
      ` : ''}
      ${claim.notes ? `
      <div class="detail-group">
        <div class="detail-label">Notes</div>
        <div class="detail-value">${claim.notes}</div>
      </div>
      ` : ''}
      <div class="detail-group">
        <div class="detail-label">Submitted Date</div>
        <div class="detail-value">${formatDate(claim.created_at)}</div>
      </div>
      <div class="detail-group">
        <div class="detail-label">Last Updated</div>
        <div class="detail-value">${formatDate(claim.updated_at)}</div>
      </div>
      ${claim.claim_status && claim.claim_status.toLowerCase() === 'pending' ? `
      <div style="display: flex; gap: 1rem; margin-top: 2rem;">
        <button class="btn btn-primary" style="flex: 1;" onclick="approveClaim(${claim.id}, event)">Approve Claim</button>
        <button class="btn btn-secondary" style="flex: 1;" onclick="openRejectModal(${claim.id}, event)">Reject Claim</button>
      </div>
      ` : ''}
    `;
    
    contentElement.innerHTML = content;
  }

  function approveClaim(claimId, event) {
    if (event) event.stopPropagation();
    
    if (!confirm('Are you sure you want to approve this claim?')) {
      return;
    }

    fetch(`modules/hmo/api.php?action=approveClaim&id=${claimId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Claim approved successfully');
        closeSideModal();
        loadAllClaims();
      } else {
        alert('Error: ' + (data.error || 'Failed to approve claim'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error approving claim');
    });
  }

  function openRejectModal(claimId, event) {
    if (event) event.stopPropagation();
    currentClaimId = claimId;
    
    const reasonInput = document.getElementById('rejection-reason');
    const overlay = document.getElementById('reject-modal-overlay');
    const modal = document.getElementById('reject-claim-modal');
    
    if (reasonInput) reasonInput.value = '';
    if (overlay) overlay.classList.add('show');
    if (modal) modal.classList.add('show');
  }

  function closeRejectModal() {
    const overlay = document.getElementById('reject-modal-overlay');
    const modal = document.getElementById('reject-claim-modal');
    
    if (overlay) overlay.classList.remove('show');
    if (modal) modal.classList.remove('show');
  }

  function submitRejectClaim(event) {
    event.preventDefault();
    
    const reasonElement = document.getElementById('rejection-reason');
    if (!reasonElement) {
      alert('Error: Unable to find rejection reason field');
      return;
    }
    
    const reason = reasonElement.value.trim();
    if (!reason) {
      alert('Please provide a rejection reason');
      return;
    }

    fetch(`modules/hmo/api.php?action=rejectClaim&id=${currentClaimId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Claim rejected successfully');
        closeRejectModal();
        closeSideModal();
        loadAllClaims();
      } else {
        alert('Error: ' + (data.error || 'Failed to reject claim'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error rejecting claim');
    });
  }

  function closeSideModal() {
    const overlay = document.getElementById('modal-overlay');
    const modal = document.getElementById('side-modal');
    
    if (overlay) overlay.classList.remove('active');
    if (modal) modal.classList.remove('active');
  }

  function viewEmployeeUtilization(employeeId, event) {
    if (event) event.stopPropagation();
    alert('Employee utilization details for ID: ' + employeeId);
  }

  function viewProviderDetails(providerId, event) {
    if (event) event.stopPropagation();
    alert('Provider details for ID: ' + providerId);
  }

  // ============================================
  // Initialization
  // ============================================

  // Initialize on page load (runs immediately for dynamic loading)
  loadAllClaims();
  updateCounts();

  // Modal event listeners - with null checks
  window.modalOverlay = document.getElementById('modal-overlay');
  window.rejectModalOverlay = document.getElementById('reject-modal-overlay');
  
  if (window.modalOverlay) {
    window.modalOverlay.addEventListener('click', closeSideModal);
  }
  if (window.rejectModalOverlay) {
    window.rejectModalOverlay.addEventListener('click', closeRejectModal);
  }

  window.onclick = function(event) {
    if (event.target && event.target.id === 'modal-overlay') {
      closeSideModal();
    }
    if (event.target && event.target.id === 'reject-modal-overlay') {
      closeRejectModal();
    }
  };
</script>
