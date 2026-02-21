<?php
/**
 * HMO Provider Management View
 * Comprehensive provider management with contracts, coverage rules, and financial integration
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
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1.25rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      padding: 1.25rem 1rem;
      text-align: center;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
      transition: all 0.2s ease;
    }

    .stat-card:hover {
      border-color: #d1d5db;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .stat-card.accent-active {
      border-left: 4px solid #10b981;
    }

    .stat-card.accent-expiring {
      border-left: 4px solid #f59e0b;
    }

    .stat-card.accent-expired {
      border-left: 4px solid #ef4444;
    }

    .stat-icon {
      font-size: 24px;
      margin-bottom: 0.5rem;
      opacity: 0.7;
    }

    .stat-number {
      font-size: 28px;
      font-weight: 700;
      color: #111827;
      margin: 0.25rem 0;
    }

    .stat-label {
      font-size: 11px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      font-weight: 500;
      line-height: 1.3;
    }

    .stat-subtitle {
      font-size: 11px;
      color: #9ca3af;
      margin-top: 0.25rem;
      font-weight: 400;
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

    /* ===== TABS / SEGMENTED CONTROL ===== */
    .filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      background: #fafbfc;
      gap: 1.5rem;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .search-box {
      flex: 0 1 280px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 0.625rem 0.875rem 0.625rem 2.25rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 13px;
      transition: all 0.2s ease;
    }

    .search-box input:focus {
      outline: none;
      border-color: #1e40af;
      box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .search-icon {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: 14px;
    }

    .segmented-control {
      display: flex;
      gap: 0.5rem;
      padding: 0.4rem;
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      flex-shrink: 0;
    }

    .segment-btn {
      padding: 0.5rem 1rem;
      border: none;
      background: transparent;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      color: #6b7280;
      border-radius: 6px;
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .segment-btn:hover {
      color: #374151;
      background: #f3f4f6;
    }

    .segment-btn.active {
      background: #1e40af;
      color: white;
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
      padding: 0.875rem 1.5rem;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .hmo-table tbody tr {
      border-bottom: 1px solid #e5e7eb;
      transition: background 0.15s ease;
      cursor: pointer;
      line-height: 1.6;
    }

    .hmo-table tbody tr:hover {
      background: #f9fafb;
    }

    .hmo-table td {
      padding: 1rem 1.5rem;
      color: #374151;
      font-size: 14px;
    }

    .provider-cell {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .provider-avatar {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      background: #e0e7ff;
      color: #1e40af;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 13px;
      flex-shrink: 0;
    }

    .provider-info strong {
      display: block;
      font-weight: 700;
      color: #111827;
    }

    .provider-code {
      font-size: 12px;
      color: #9ca3af;
    }

    .contract-period {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 13px;
      color: #6b7280;
    }

    .contract-divider {
      color: #d1d5db;
      font-weight: 300;
    }

    .contract-days-left {
      display: block;
      font-size: 12px;
      color: #ef4444;
      font-weight: 500;
      margin-top: 0.25rem;
    }

    .td-muted {
      color: #6b7280;
      font-size: 13px;
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
      white-space: nowrap;
    }

    .badge-active {
      background: #10b981;
      color: white;
    }

    .badge-suspended {
      background: #f59e0b;
      color: white;
    }

    .badge-expired {
      background: #ef4444;
      color: white;
    }

    .badge-expiring {
      background: #f59e0b;
      color: white;
    }

    .badge-corporate {
      background: #dbeafe;
      color: #1e40af;
    }

    .badge-government {
      background: #dcfce7;
      color: #166534;
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
      gap: 0.5rem;
      align-items: center;
      justify-content: center;
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
      font-size: 11px;
      opacity: 0;
      transition: opacity 0.2s ease;
    }

    .action-tooltip:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }

    /* ===== SIDE MODAL ===== */
    .side-modal {
      display: none;
      position: fixed;
      right: 0;
      top: 0;
      width: 420px;
      height: 100vh;
      background: white;
      box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
      z-index: 1001;
      overflow-y: auto;
      animation: slideIn 0.3s ease;
    }

    .side-modal.show {
      display: block;
    }

    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.3);
      z-index: 1000;
    }

    .modal-overlay.show {
      display: block;
    }

    .side-modal-header {
      padding: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .side-modal-title {
      font-size: 18px;
      font-weight: 600;
      color: #111827;
    }

    .modal-close-btn {
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
      color: #6b7280;
      padding: 0.5rem;
    }

    .modal-close-btn:hover {
      color: #111827;
    }

    .side-modal-content {
      padding: 1.5rem;
    }

    .modal-section {
      margin-bottom: 2rem;
    }

    .modal-section-title {
      font-size: 12px;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 1rem;
    }

    .modal-detail {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 0.75rem 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .modal-detail:last-child {
      border-bottom: none;
    }

    .modal-detail-label {
      font-size: 13px;
      color: #6b7280;
      font-weight: 500;
    }

    .modal-detail-value {
      font-size: 14px;
      color: #111827;
      font-weight: 500;
      text-align: right;
    }

    .modal-actions {
      padding-top: 1.5rem;
      border-top: 1px solid #e5e7eb;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .btn-modal {
      padding: 0.75rem 1rem;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-modal-primary {
      background: #1e40af;
      color: white;
    }

    .btn-modal-primary:hover {
      background: #1e3a8a;
    }

    .btn-modal-secondary {
      background: #f3f4f6;
      color: #374151;
    }

    .btn-modal-secondary:hover {
      background: #e5e7eb;
    }

    .btn-modal-danger {
      background: #fee2e2;
      color: #991b1b;
    }

    .btn-modal-danger:hover {
      background: #fecaca;
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
  </style>

  <!-- Header Stats -->
  <div class="stats-grid">
    <div class="stat-card accent-active">
      <div class="stat-icon">🏢</div>
      <div class="stat-number" id="total-providers">0</div>
      <div class="stat-label">Total Providers</div>
      <div class="stat-subtitle">All providers</div>
    </div>
    <div class="stat-card accent-active">
      <div class="stat-icon">✓</div>
      <div class="stat-number" id="active-providers">0</div>
      <div class="stat-label">Active</div>
      <div class="stat-subtitle">Active providers</div>
    </div>
    <div class="stat-card accent-expiring">
      <div class="stat-icon">⏱</div>
      <div class="stat-number" id="expiring-providers">0</div>
      <div class="stat-label">Expiring Soon</div>
      <div class="stat-subtitle">Next 30 days</div>
    </div>
    <div class="stat-card accent-expired">
      <div class="stat-icon">✕</div>
      <div class="stat-number" id="expired-providers">0</div>
      <div class="stat-label">Expired</div>
      <div class="stat-subtitle">Requires renewal</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Providers</h3>
      <button class="btn btn-primary" onclick="showAddProviderModal()">
        <span>+</span> Add Provider
      </button>
    </div>

    <!-- Filter Bar (Sticky) -->
    <div class="filter-bar">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="providerSearch" placeholder="Search providers..." onkeyup="filterProviders()">
      </div>
      <div class="segmented-control">
        <button class="segment-btn active" onclick="switchProviderTab(event, 'all')">All</button>
        <button class="segment-btn" onclick="switchProviderTab(event, 'active')">Active</button>
        <button class="segment-btn" onclick="switchProviderTab(event, 'expiring')">Expiring</button>
        <button class="segment-btn" onclick="switchProviderTab(event, 'expired')">Expired</button>
      </div>
    </div>

    <!-- All Providers Table -->
    <div id="all-tab" style="display: block;">
      <table class="hmo-table" id="hmo-providers-table">
        <thead>
          <tr>
            <th style="width: 35%;">Provider</th>
            <th style="width: 15%;">Type</th>
            <th style="width: 25%;">Contract Period</th>
            <th style="width: 15%;">Status</th>
            <th style="width: 10%; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody id="providers-tbody">
          <tr class="skeleton-row">
            <td colspan="5"><div class="skeleton-text" style="width: 100%;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Active Tab -->
    <div id="active-tab" style="display: none;">
      <table class="hmo-table">
        <thead>
          <tr>
            <th style="width: 35%;">Provider</th>
            <th style="width: 15%;">Type</th>
            <th style="width: 25%;">Contract Expires</th>
            <th style="width: 15%;">Status</th>
            <th style="width: 10%; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody id="active-tbody">
          <tr class="skeleton-row">
            <td colspan="5"><div class="skeleton-text" style="width: 100%;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Expiring Tab -->
    <div id="expiring-tab" style="display: none;">
      <table class="hmo-table">
        <thead>
          <tr>
            <th style="width: 35%;">Provider</th>
            <th style="width: 20%;">Days Left</th>
            <th style="width: 20%;">Expires</th>
            <th style="width: 15%;">Status</th>
            <th style="width: 10%; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody id="expiring-tbody">
          <tr class="skeleton-row">
            <td colspan="5"><div class="skeleton-text" style="width: 100%;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Expired Tab -->
    <div id="expired-tab" style="display: none;">
      <table class="hmo-table">
        <thead>
          <tr>
            <th style="width: 40%;">Provider</th>
            <th style="width: 25%;">Expired Date</th>
            <th style="width: 15%;">Status</th>
            <th style="width: 10%; text-align: center;">Actions</th>
          </tr>
        </thead>
        <tbody id="expired-tbody">
          <tr class="skeleton-row">
            <td colspan="4"><div class="skeleton-text" style="width: 100%;"></div></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Side Modal -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeSideModal()"></div>
<div class="side-modal" id="sideModal">
  <div class="side-modal-header">
    <h2 class="side-modal-title">Provider Details</h2>
    <button class="modal-close-btn" onclick="closeSideModal()">✕</button>
  </div>
  <div class="side-modal-content">
    <!-- Provider Overview -->
    <div class="modal-section">
      <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
        <div class="provider-avatar" style="width: 56px; height: 56px; font-size: 18px;" id="modalProviderInitials">--</div>
        <div style="flex: 1;">
          <h3 id="modalProviderName" style="margin: 0 0 0.25rem 0; font-size: 16px; font-weight: 700;">--</h3>
          <p id="modalProviderCode" style="margin: 0; font-size: 13px; color: #6b7280;">--</p>
        </div>
      </div>
    </div>

    <!-- Contract Details -->
    <div class="modal-section">
      <h4 class="modal-section-title">Contract</h4>
      <div class="modal-detail">
        <span class="modal-detail-label">Start Date</span>
        <span class="modal-detail-value" id="modalContractStart">--</span>
      </div>
      <div class="modal-detail">
        <span class="modal-detail-label">End Date</span>
        <span class="modal-detail-value" id="modalContractEnd">--</span>
      </div>
      <div class="modal-detail">
        <span class="modal-detail-label">Days Left</span>
        <span class="modal-detail-value" id="modalDaysLeft">--</span>
      </div>
      <div class="modal-detail">
        <span class="modal-detail-label">Grace Period</span>
        <span class="modal-detail-value" id="modalGracePeriod">--</span>
      </div>
    </div>

    <!-- Coverage Info  -->
    <div class="modal-section">
      <h4 class="modal-section-title">Coverage</h4>
      <div class="modal-detail">
        <span class="modal-detail-label">Type</span>
        <span class="modal-detail-value" id="modalProviderType">--</span>
      </div>
      <div class="modal-detail">
        <span class="modal-detail-label">Max Dependents</span>
        <span class="modal-detail-value" id="modalMaxDependents">--</span>
      </div>
      <div class="modal-detail">
        <span class="modal-detail-label">Enrolled Employees</span>
        <span class="modal-detail-value" id="modalEnrolledCount">--</span>
      </div>
    </div>

    <!-- Contact Info -->
    <div class="modal-section">
      <h4 class="modal-section-title">Contact</h4>
      <div class="modal-detail">
        <span class="modal-detail-label">Account Manager</span>
        <span class="modal-detail-value" id="modalAccountManager">--</span>
      </div>
      <div class="modal-detail">
        <span class="modal-detail-label">Email</span>
        <span class="modal-detail-value" id="modalEmail" style="word-break: break-all;">--</span>
      </div>
      <div class="modal-detail">
        <span class="modal-detail-label">Phone</span>
        <span class="modal-detail-value" id="modalPhone">--</span>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="modal-actions">
      <button class="btn-modal btn-modal-primary" onclick="editProvider(currentProviderId)">✏️ Edit Provider</button>
      <button class="btn-modal btn-modal-secondary" onclick="viewProviderCatalog(currentProviderId)">📋 Manage Plans</button>
      <button class="btn-modal btn-modal-secondary" onclick="renewContract(currentProviderId)">🔄 Renew Contract</button>
      <button class="btn-modal btn-modal-danger" onclick="deactivateProvider(currentProviderId)">⊘ Deactivate</button>
    </div>
  </div>
</div>

<!-- Edit Form Modal -->
<div class="modal-overlay" id="editFormOverlay" onclick="closeEditModal()"></div>
<div class="edit-form-modal" id="editFormModal">
  <div class="edit-form-header">
    <h2 id="providerFormTitle">Add Provider</h2>
    <button class="modal-close-btn" onclick="closeEditModal()">✕</button>
  </div>
  <div class="edit-form-content">
    <form id="providerForm">
      <div class="form-group">
        <label>Provider Code *</label>
        <input type="text" id="provider_code" placeholder="e.g., PHH001" required>
      </div>
      
      <div class="form-group">
        <label>Provider Name *</label>
        <input type="text" id="provider_name" placeholder="e.g., Philhealth" required>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea id="description" placeholder="Provider description..."></textarea>
      </div>

      <div class="form-group">
        <label>Type</label>
        <select id="provider_type">
          <option value="Corporate">Corporate</option>
          <option value="Government">Government</option>
          <option value="NGO">NGO</option>
        </select>
      </div>

      <div class="form-group">
        <label>Status</label>
        <select id="provider_status">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Suspended">Suspended</option>
        </select>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Contract Start Date</label>
          <input type="date" id="contract_start_date">
        </div>
        <div class="form-group">
          <label>Contract End Date</label>
          <input type="date" id="contract_end_date">
        </div>
      </div>

      <div class="form-group">
        <label>Head Office Address</label>
        <input type="text" id="head_office_address" placeholder="Address...">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Contact Email</label>
          <input type="email" id="contact_email" placeholder="email@provider.com">
        </div>
        <div class="form-group">
          <label>Contact Phone</label>
          <input type="tel" id="contact_phone" placeholder="+63 2 XXXX XXXX">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Primary Contact Person</label>
          <input type="text" id="primary_contact_person" placeholder="Name...">
        </div>
        <div class="form-group">
          <label>Contact Title</label>
          <input type="text" id="primary_contact_title" placeholder="Title...">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Website</label>
          <input type="url" id="website" placeholder="https://...">
        </div>
        <div class="form-group">
          <label>Accreditation Number</label>
          <input type="text" id="accreditation_number" placeholder="Accreditation number...">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Established Year</label>
          <input type="number" id="established_year" placeholder="YYYY">
        </div>
        <div class="form-group">
          <label>Payment Terms (days)</label>
          <input type="number" id="payment_terms_days" value="30">
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
        <button type="button" id="providerFormButton" class="btn btn-primary" onclick="submitProviderForm()">Add Provider</button>
      </div>
    </form>
  </div>
</div>

<style>
  /* Edit Form Modal Styles */
  .edit-form-modal {
    position: fixed;
    right: -500px;
    top: 0;
    width: 500px;
    height: 100vh;
    background: white;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
    z-index: 1001;
    display: flex;
    flex-direction: column;
    transition: right 0.3s ease;
    overflow-y: auto;
  }

  .edit-form-modal.show {
    right: 0;
  }

  .edit-form-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f9fafb;
  }

  .edit-form-header h2 {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    color: #111827;
  }

  .edit-form-content {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
  }

  .form-group {
    margin-bottom: 1.5rem;
  }

  .form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 13px;
    font-family: inherit;
  }

  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .form-group textarea {
    min-height: 80px;
    resize: vertical;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .form-actions {
    display: flex;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
  }

  .form-actions .btn {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .form-actions .btn-primary {
    background: #3b82f6;
    color: white;
  }

  .form-actions .btn-primary:hover {
    background: #2563eb;
  }

  .form-actions .btn-secondary {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
  }

  .form-actions .btn-secondary:hover {
    background: #f3f4f6;
  }

  @media (max-width: 768px) {
    .edit-form-modal {
      width: 100%;
      right: -100%;
    }

    .form-row {
      grid-template-columns: 1fr;
    }
  }
</style>

  </div>
</div>

<script>
  if (typeof currentProviderId === 'undefined') { var currentProviderId = null; }
  currentProviderId = null;
  if (typeof currentTab === 'undefined') { var currentTab = 'all'; }
  currentTab = 'all';
  if (typeof allProvidersData === 'undefined') { var allProvidersData = []; }
  allProvidersData = [];

  function switchProviderTab(event, tabName) {
    event.preventDefault();
    
    // Update current tab
    currentTab = tabName;
    
    // Hide all tabs
    document.getElementById('all-tab').style.display = 'none';
    document.getElementById('active-tab').style.display = 'none';
    document.getElementById('expiring-tab').style.display = 'none';
    document.getElementById('expired-tab').style.display = 'none';

    // Remove active class from all buttons
    document.querySelectorAll('.segment-btn').forEach(btn => btn.classList.remove('active'));

    // Show selected tab and mark button as active
    document.getElementById(tabName + '-tab').style.display = 'block';
    event.target.classList.add('active');

    // Load data for the selected tab
    if (tabName === 'all') {
      loadAllProviders();
    } else if (tabName === 'active') {
      loadActiveProviders();
    } else if (tabName === 'expiring') {
      loadExpiringProviders();
    } else if (tabName === 'expired') {
      loadExpiredProviders();
    }
  }

  function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
  }

  function getProviderTypeBadge(type) {
    return type === 'corporate' ? 'badge-corporate' : 'badge-government';
  }

  function getStatusBadge(status) {
    const lowerStatus = (status || '').toLowerCase();
    const badges = {
      'active': 'badge-active',
      'suspended': 'badge-suspended',
      'expired': 'badge-expired',
      'inactive': 'badge-expired'
    };
    return badges[lowerStatus] || 'badge-active';
  }

  function createActionButtons(providerId, context = 'all') {
    const buttons = [];

    buttons.push(`
      <div class="action-tooltip">
        <button class="btn-icon" onclick="event.stopPropagation(); viewProviderModal(${providerId})" title="View Details">
          👁
        </button>
        <span class="tooltip-text">View</span>
      </div>
    `);

    buttons.push(`
      <div class="action-tooltip">
        <button class="btn-icon" onclick="event.stopPropagation(); editProvider(${providerId})" title="Edit Provider">
          ✏️
        </button>
        <span class="tooltip-text">Edit</span>
      </div>
    `);

    buttons.push(`
      <div class="action-tooltip">
        <button class="btn-icon" onclick="event.stopPropagation(); deactivateProvider(${providerId})" title="Deactivate Provider">
          ⊘
        </button>
        <span class="tooltip-text">Disable</span>
      </div>
    `);

    return `<div class="actions-cell">${buttons.join('')}</div>`;
  }

  function loadAllProviders() {
    fetch('modules/hmo/api.php?action=getProviders')
      .then(response => response.json())
      .then(data => {
        const tbody = document.getElementById('providers-tbody');
        if (data.success && data.data.length > 0) {
          allProvidersData = data.data;
          updateProviderStats(data.data);

          tbody.innerHTML = data.data.map(provider => {
            const startDate = new Date(provider.contract_start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            const endDate = new Date(provider.contract_end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const initials = getInitials(provider.provider_name);
            
            return `
              <tr onclick="viewProviderModal(${provider.id})" style="cursor: pointer;">
                <td>
                  <div class="provider-cell">
                    <div class="provider-avatar">${initials}</div>
                    <div>
                      <strong class="provider-info" style="color: #111827;">${provider.provider_name}</strong>
                      <span class="provider-code">${provider.provider_code || 'N/A'}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge ${getProviderTypeBadge(provider.provider_type)}">
                    ${provider.provider_type === 'corporate' ? 'Corporate' : 'Government'}
                  </span>
                </td>
                <td>
                  <div class="contract-period">
                    <span>${startDate}</span>
                    <span class="contract-divider">→</span>
                    <span>${endDate}</span>
                  </div>
                </td>
                <td>
                  <span class="badge ${getStatusBadge(provider.provider_status)}">
                    ${(provider.provider_status || '').toLowerCase() === 'active' ? '✓ Active' : (provider.provider_status || '').toLowerCase() === 'suspended' ? '⚠ Suspended' : '✕ Expired'}
                  </span>
                </td>
                <td style="text-align: center;">
                  ${createActionButtons(provider.id, 'all')}
                </td>
              </tr>
            `;
          }).join('');
        } else {
          tbody.innerHTML = `
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="empty-state-icon">📭</div>
                  <div class="empty-state-title">No HMO providers yet</div>
                  <div class="empty-state-message">Start by adding your first HMO provider</div>
                  <button class="btn btn-primary empty-state-action" onclick="showAddProviderModal()">+ Add Provider</button>
                </div>
              </td>
            </tr>
          `;
        }
      })
      .catch(error => {
        console.error('Error loading providers:', error);
        document.getElementById('providers-tbody').innerHTML = `
          <tr>
            <td colspan="5">
              <div class="error-state">
                <div class="error-state-icon">⚠️</div>
                <div class="error-state-message">Failed to load providers. Please try again.</div>
                <button class="btn btn-primary" onclick="loadAllProviders()">Retry</button>
              </div>
            </td>
          </tr>
        `;
      });
  }

  function loadActiveProviders() {
    fetch('modules/hmo/api.php?action=getProvidersByStatus&status=active')
      .then(response => response.json())
      .then(data => {
        const tbody = document.getElementById('active-tbody');
        if (data.success && data.data.length > 0) {
          tbody.innerHTML = data.data.map(provider => {
            const endDate = new Date(provider.contract_end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const initials = getInitials(provider.provider_name);
            
            return `
              <tr onclick="viewProviderModal(${provider.id})" style="cursor: pointer;">
                <td>
                  <div class="provider-cell">
                    <div class="provider-avatar">${initials}</div>
                    <div>
                      <strong class="provider-info" style="color: #111827;">${provider.provider_name}</strong>
                      <span class="provider-code">${provider.provider_code || 'N/A'}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge ${getProviderTypeBadge(provider.provider_type)}">
                    ${provider.provider_type === 'corporate' ? 'Corporate' : 'Government'}
                  </span>
                </td>
                <td class="td-muted">${endDate}</td>
                <td>
                  <span class="badge badge-active">✓ Active</span>
                </td>
                <td style="text-align: center;">
                  ${createActionButtons(provider.id, 'active')}
                </td>
              </tr>
            `;
          }).join('');
        } else {
          tbody.innerHTML = `
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="empty-state-icon">📭</div>
                  <div class="empty-state-title">No active providers</div>
                  <div class="empty-state-message">All providers are currently inactive</div>
                </div>
              </td>
            </tr>
          `;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('active-tbody').innerHTML = `
          <tr>
            <td colspan="5">
              <div class="error-state">
                <div class="error-state-icon">⚠️</div>
                <div class="error-state-message">Failed to load active providers</div>
                <button class="btn btn-primary" onclick="loadActiveProviders()">Retry</button>
              </div>
            </td>
          </tr>
        `;
      });
  }

  function loadExpiringProviders() {
    fetch('modules/hmo/api.php?action=getExpiringContracts&days=30')
      .then(response => response.json())
      .then(data => {
        const tbody = document.getElementById('expiring-tbody');
        if (data.success && data.data.length > 0) {
          tbody.innerHTML = data.data.map(provider => {
            const endDate = new Date(provider.contract_end_date);
            const endDateFormatted = endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const today = new Date();
            const daysLeft = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));
            const initials = getInitials(provider.provider_name);
            
            let statusColor = '#10b981'; // green
            if (daysLeft <= 7) statusColor = '#ef4444'; // red
            else if (daysLeft <= 14) statusColor = '#f59e0b'; // orange
            
            return `
              <tr onclick="viewProviderModal(${provider.id})" style="cursor: pointer;">
                <td>
                  <div class="provider-cell">
                    <div class="provider-avatar">${initials}</div>
                    <div>
                      <strong class="provider-info" style="color: #111827;">${provider.provider_name}</strong>
                      <span class="provider-code">${provider.provider_code || 'N/A'}</span>
                    </div>
                  </div>
                </td>
                <td style="color: ${statusColor}; font-weight: 700; font-size: 15px;">${daysLeft} days</td>
                <td class="td-muted">${endDateFormatted}</td>
                <td>
                  <span class="badge badge-expiring">⏱ Expiring</span>
                </td>
                <td style="text-align: center;">
                  ${createActionButtons(provider.id, 'expiring')}
                </td>
              </tr>
            `;
          }).join('');
        } else {
          tbody.innerHTML = `
            <tr>
              <td colspan="5">
                <div class="empty-state">
                  <div class="empty-state-icon">✨</div>
                  <div class="empty-state-title">All contracts are healthy</div>
                  <div class="empty-state-message">No contracts expiring in the next 30 days</div>
                </div>
              </td>
            </tr>
          `;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('expiring-tbody').innerHTML = `
          <tr>
            <td colspan="5">
              <div class="error-state">
                <div class="error-state-icon">⚠️</div>
                <div class="error-state-message">Failed to load expiring contracts</div>
                <button class="btn btn-primary" onclick="loadExpiringProviders()">Retry</button>
              </div>
            </td>
          </tr>
        `;
      });
  }

  function loadExpiredProviders() {
    fetch('modules/hmo/api.php?action=getProvidersByStatus&status=expired')
      .then(response => response.json())
      .then(data => {
        const tbody = document.getElementById('expired-tbody');
        if (data.success && data.data.length > 0) {
          tbody.innerHTML = data.data.map(provider => {
            const endDate = new Date(provider.contract_end_date);
            const endDateFormatted = endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const today = new Date();
            const daysExpired = Math.ceil((today - endDate) / (1000 * 60 * 60 * 24));
            const initials = getInitials(provider.provider_name);
            
            return `
              <tr onclick="viewProviderModal(${provider.id})" style="cursor: pointer;">
                <td>
                  <div class="provider-cell">
                    <div class="provider-avatar">${initials}</div>
                    <div>
                      <strong class="provider-info" style="color: #111827;">${provider.provider_name}</strong>
                      <span class="provider-code">${provider.provider_code || 'N/A'}</span>
                    </div>
                  </div>
                </td>
                <td class="td-muted">${endDateFormatted}</td>
                <td>
                  <span class="badge badge-expired">✕ Expired</span>
                </td>
                <td style="text-align: center;">
                  ${createActionButtons(provider.id, 'expired')}
                </td>
              </tr>
            `;
          }).join('');
        } else {
          tbody.innerHTML = `
            <tr>
              <td colspan="4">
                <div class="empty-state">
                  <div class="empty-state-icon">🎉</div>
                  <div class="empty-state-title">No expired contracts</div>
                  <div class="empty-state-message">All provider contracts are current or active</div>
                </div>
              </td>
            </tr>
          `;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('expired-tbody').innerHTML = `
          <tr>
            <td colspan="4">
              <div class="error-state">
                <div class="error-state-icon">⚠️</div>
                <div class="error-state-message">Failed to load expired providers</div>
                <button class="btn btn-primary" onclick="loadExpiredProviders()">Retry</button>
              </div>
            </td>
          </tr>
        `;
      });
  }

  function updateProviderStats(providers) {
    const today = new Date();
    const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);

    const totalCount = providers.length;
    const activeCount = providers.filter(p => p.provider_status === 'active').length;
    const expiringCount = providers.filter(p => {
      const contractEnd = new Date(p.contract_end_date);
      return contractEnd > today && contractEnd <= thirtyDaysFromNow;
    }).length;
    const expiredCount = providers.filter(p => p.provider_status === 'expired').length;

    document.getElementById('total-providers').textContent = totalCount;
    document.getElementById('active-providers').textContent = activeCount;
    document.getElementById('expiring-providers').textContent = expiringCount;
    document.getElementById('expired-providers').textContent = expiredCount;
  }

  function viewProviderModal(providerId) {
    currentProviderId = providerId;
    const provider = allProvidersData.find(p => p.id === providerId);
    
    if (!provider) {
      // If not in cached data, fetch it
      fetch(`modules/hmo/api.php?action=getProviderDetails&id=${providerId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data) {
            populateSideModal(data.data);
          }
        });
    } else {
      populateSideModal(provider);
    }
  }

  function populateSideModal(provider) {
    try {
      const initials = getInitials(provider.provider_name);
      const startDate = new Date(provider.contract_start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      const endDate = new Date(provider.contract_end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
      const today = new Date();
      const contractEnd = new Date(provider.contract_end_date);
      const daysLeft = Math.ceil((contractEnd - today) / (1000 * 60 * 60 * 24));

      const modalProviderInitials = document.getElementById('modalProviderInitials');
      const modalProviderName = document.getElementById('modalProviderName');
      const modalProviderCode = document.getElementById('modalProviderCode');
      const modalContractStart = document.getElementById('modalContractStart');
      const modalContractEnd = document.getElementById('modalContractEnd');
      const modalDaysLeft = document.getElementById('modalDaysLeft');
      const modalGracePeriod = document.getElementById('modalGracePeriod');
      const modalProviderType = document.getElementById('modalProviderType');
      const modalMaxDependents = document.getElementById('modalMaxDependents');
      const modalEnrolledCount = document.getElementById('modalEnrolledCount');
      const modalAccountManager = document.getElementById('modalAccountManager');
      const modalEmail = document.getElementById('modalEmail');
      const modalPhone = document.getElementById('modalPhone');
      const sideModal = document.getElementById('sideModal');
      const modalOverlay = document.getElementById('modalOverlay');

      // Check if all elements exist
      if (!modalProviderInitials) {
        console.error('Missing modalProviderInitials element');
      }
      if (!sideModal) {
        console.error('Missing sideModal element');
        console.log('Available elements:', document.body.innerHTML.substring(0, 500));
      }
      
      if (!modalProviderInitials || !sideModal) {
        console.error('Modal elements not found in DOM - sideModal:', !!sideModal, 'initials:', !!modalProviderInitials);
        return;
      }

      if (modalProviderInitials) modalProviderInitials.textContent = initials;
      if (modalProviderName) modalProviderName.textContent = provider.provider_name;
      if (modalProviderCode) modalProviderCode.textContent = provider.provider_code || 'N/A';
      if (modalContractStart) modalContractStart.textContent = startDate;
      if (modalContractEnd) modalContractEnd.textContent = endDate;
      if (modalDaysLeft) modalDaysLeft.textContent = daysLeft > 0 ? `${daysLeft} days` : 'Expired';
      if (modalGracePeriod) modalGracePeriod.textContent = `${provider.grace_period_days || 0} days`;
      if (modalProviderType) modalProviderType.textContent = provider.provider_type === 'corporate' ? 'Corporate' : 'Government';
      if (modalMaxDependents) modalMaxDependents.textContent = provider.max_dependents || 'Unlimited';
      if (modalEnrolledCount) modalEnrolledCount.textContent = provider.enrolled_count || '0';
      if (modalAccountManager) modalAccountManager.textContent = provider.primary_account_manager || 'Not assigned';
      if (modalEmail) modalEmail.textContent = provider.email || 'N/A';
      if (modalPhone) modalPhone.textContent = provider.phone || 'N/A';

      // Show modal
      if (sideModal) sideModal.classList.add('show');
      if (modalOverlay) modalOverlay.classList.add('show');
    } catch (error) {
      console.error('Error populating side modal:', error);
    }
  }

  function closeSideModal() {
    try {
      const sideModal = document.getElementById('sideModal');
      const modalOverlay = document.getElementById('modalOverlay');

      if (sideModal) sideModal.classList.remove('show');
      if (modalOverlay) modalOverlay.classList.remove('show');
      currentProviderId = null;
    } catch (error) {
      console.error('Error closing side modal:', error);
    }
  }

  function filterProviders() {
    const searchTerm = document.getElementById('providerSearch').value.toLowerCase();
    const rows = document.querySelectorAll(`#${currentTab}-tab table tbody tr`);
    
    rows.forEach(row => {
      const text = row.innerText.toLowerCase();
      row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
  }

  function showAddProviderModal() {
    try {
      currentProviderId = null;
      const providerFormTitle = document.getElementById('providerFormTitle');
      const providerFormButton = document.getElementById('providerFormButton');
      const providerForm = document.getElementById('providerForm');
      const editFormModal = document.getElementById('editFormModal');
      const editFormOverlay = document.getElementById('editFormOverlay');

      if (!editFormModal) {
        console.error('Edit form modal elements not found');
        return;
      }

      if (providerFormTitle) providerFormTitle.textContent = 'Add Provider';
      if (providerFormButton) providerFormButton.textContent = 'Add Provider';
      if (providerForm) providerForm.reset();
      
      editFormModal.classList.add('show');
      if (editFormOverlay) editFormOverlay.classList.add('show');
    } catch (error) {
      console.error('Error showing add provider modal:', error);
    }
  }

  function viewProviderDetails(id) {
    viewProviderModal(id);
  }

  function viewProviderCatalog(id) {
    window.location.href = '?module=hmo&section=plans&provider_id=' + id;
  }

  function editProvider(id) {
    try {
      currentProviderId = id;
      const provider = allProvidersData.find(p => p.id === id);
      
      if (!provider) {
        // Fetch provider details
        fetch(`modules/hmo/api.php?action=getProviderDetails&provider_id=${id}`)
          .then(response => response.json())
          .then(data => {
            if (data.success && data.data) {
              const providerData = data.data.provider || data.data;
              populateProviderForm(providerData);
              showEditModal();
            } else {
              console.error('Failed to fetch provider:', data.error);
            }
          })
          .catch(error => {
            console.error('Error fetching provider:', error);
          });
      } else {
        populateProviderForm(provider);
        showEditModal();
      }
    } catch (error) {
      console.error('Error in editProvider:', error);
    }
  }

  function populateProviderForm(provider) {
    try {
      const providerFormTitle = document.getElementById('providerFormTitle');
      const providerFormButton = document.getElementById('providerFormButton');
      const provider_code = document.getElementById('provider_code');
      const provider_name = document.getElementById('provider_name');
      const description = document.getElementById('description');
      const head_office_address = document.getElementById('head_office_address');
      const contact_phone = document.getElementById('contact_phone');
      const contact_email = document.getElementById('contact_email');
      const primary_contact_person = document.getElementById('primary_contact_person');
      const primary_contact_title = document.getElementById('primary_contact_title');
      const website = document.getElementById('website');
      const accreditation_number = document.getElementById('accreditation_number');
      const established_year = document.getElementById('established_year');
      const provider_status = document.getElementById('provider_status');
      const provider_type = document.getElementById('provider_type');
      const contract_start_date = document.getElementById('contract_start_date');
      const contract_end_date = document.getElementById('contract_end_date');
      const payment_terms_days = document.getElementById('payment_terms_days');

      if (!provider_code) {
        console.error('Form elements not found in DOM');
        console.error('provider_code:', !!provider_code);
        console.error('provider_name:', !!provider_name);
        console.error('Form #providerForm:', !!document.getElementById('providerForm'));
        return;
      }

      if (providerFormTitle) providerFormTitle.textContent = 'Edit Provider';
      if (providerFormButton) providerFormButton.textContent = 'Update Provider';
      
      if (provider_code) provider_code.value = provider.provider_code || '';
      if (provider_name) provider_name.value = provider.provider_name || '';
      if (description) description.value = provider.description || '';
      if (head_office_address) head_office_address.value = provider.head_office_address || '';
      if (contact_phone) contact_phone.value = provider.contact_phone || '';
      if (contact_email) contact_email.value = provider.contact_email || '';
      if (primary_contact_person) primary_contact_person.value = provider.primary_contact_person || '';
      if (primary_contact_title) primary_contact_title.value = provider.primary_contact_title || '';
      if (website) website.value = provider.website || '';
      if (accreditation_number) accreditation_number.value = provider.accreditation_number || '';
      if (established_year) established_year.value = provider.established_year || '';
      if (provider_status) provider_status.value = provider.provider_status || 'Active';
      if (provider_type) provider_type.value = provider.provider_type || 'Corporate';
      if (contract_start_date) contract_start_date.value = provider.contract_start_date || '';
      if (contract_end_date) contract_end_date.value = provider.contract_end_date || '';
      if (payment_terms_days) payment_terms_days.value = provider.payment_terms_days || '30';
    } catch (error) {
      console.error('Error populating provider form:', error);
    }
  }

  function showEditModal() {
    try {
      const editFormModal = document.getElementById('editFormModal');
      const editFormOverlay = document.getElementById('editFormOverlay');

      if (editFormModal) editFormModal.classList.add('show');
      if (editFormOverlay) editFormOverlay.classList.add('show');
    } catch (error) {
      console.error('Error showing edit modal:', error);
    }
  }

  function closeEditModal() {
    try {
      const editFormModal = document.getElementById('editFormModal');
      const editFormOverlay = document.getElementById('editFormOverlay');

      if (editFormModal) editFormModal.classList.remove('show');
      if (editFormOverlay) editFormOverlay.classList.remove('show');
      currentProviderId = null;
    } catch (error) {
      console.error('Error closing edit modal:', error);
    }
  }

  function submitProviderForm() {
    try {
      const providerId = currentProviderId;
      
      const provider_code_el = document.getElementById('provider_code');
      const provider_name_el = document.getElementById('provider_name');
      
      if (!provider_code_el || !provider_name_el) {
        alert('Form elements not found');
        return;
      }
      
      const formData = {
        provider_code: provider_code_el.value,
        provider_name: provider_name_el.value,
        description: document.getElementById('description')?.value || '',
        head_office_address: document.getElementById('head_office_address')?.value || '',
        contact_phone: document.getElementById('contact_phone')?.value || '',
        contact_email: document.getElementById('contact_email')?.value || '',
        primary_contact_person: document.getElementById('primary_contact_person')?.value || '',
        primary_contact_title: document.getElementById('primary_contact_title')?.value || '',
        website: document.getElementById('website')?.value || '',
        accreditation_number: document.getElementById('accreditation_number')?.value || '',
        established_year: document.getElementById('established_year')?.value || '',
        provider_status: document.getElementById('provider_status')?.value || 'Active',
        provider_type: document.getElementById('provider_type')?.value || 'Corporate',
        contract_start_date: document.getElementById('contract_start_date')?.value || '',
        contract_end_date: document.getElementById('contract_end_date')?.value || '',
        payment_terms_days: document.getElementById('payment_terms_days')?.value || '30'
      };

    if (!formData.provider_code || !formData.provider_name) {
      alert('Provider code and name are required');
      return;
    }

    const url = providerId 
      ? `modules/hmo/api.php?action=updateProvider&id=${providerId}`
      : 'modules/hmo/api.php?action=createProvider';

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(providerId ? 'Provider updated successfully' : 'Provider created successfully');
        closeEditModal();
        loadAllProviders();
      } else {
        alert('Error: ' + data.error);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to save provider');
    });
    } catch (error) {
      console.error('Error in submitProviderForm:', error);
      alert('An error occurred while submitting the form');
    }
  }

  function renewContract(id) {
    alert('Renew contract for provider ' + id + ' coming soon');
  }

  function deactivateProvider(id) {
    try {
      if (!confirm('Are you sure you want to deactivate this provider?')) {
        return;
      }

      fetch(`modules/hmo/api.php?action=deleteProvider&id=${id}`, {
        method: 'GET'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Provider deactivated successfully');
          loadAllProviders();
        } else {
          alert('Error: ' + data.error);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to deactivate provider');
      });
    } catch (error) {
      console.error('Error in deactivateProvider:', error);
      alert('An error occurred while deactivating the provider');
    }
  }

  // Load providers on page load - wrap in DOM ready
  function initHMOProvider() {
    try {
      loadAllProviders();

      // Close modal when clicking outside
      const modalOverlay = document.getElementById('modalOverlay');
      if (modalOverlay) {
        modalOverlay.addEventListener('click', closeSideModal);
      }
    } catch (error) {
      console.error('Error initializing HMO Provider:', error);
    }
  }

  // Verify modals exist in DOM
  function verifyModalsInDOM() {
    const sideModal = document.getElementById('sideModal');
    const editFormModal = document.getElementById('editFormModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const editFormOverlay = document.getElementById('editFormOverlay');
    const providerForm = document.getElementById('providerForm');
    
    if (!sideModal) console.warn('sideModal (#sideModal) not found in DOM');
    if (!editFormModal) console.warn('editFormModal (#editFormModal) not found in DOM');
    if (!modalOverlay) console.warn('modalOverlay (#modalOverlay) not found in DOM');
    if (!editFormOverlay) console.warn('editFormOverlay (#editFormOverlay) not found in DOM');
    if (!providerForm) console.warn('providerForm (#providerForm) not found in DOM');
    
    return !!(sideModal && editFormModal && modalOverlay && editFormOverlay && providerForm);
  }

  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(function() {
        console.log('DOM ready, verifying modal elements...');
        if (verifyModalsInDOM()) {
          console.log('All modal elements found in DOM');
          initHMOProvider();
        } else {
          console.error('Some modal elements are missing from the DOM');
          // Still try to initialize anyway
          initHMOProvider();
        }
      }, 100);
    });
  } else {
    setTimeout(function() {
      console.log('Document already loaded, verifying modal elements...');
      if (verifyModalsInDOM()) {
        console.log('All modal elements found in DOM');
        initHMOProvider();
      } else {
        console.error('Some modal elements are missing from the DOM');
        // Still try to initialize anyway
        initHMOProvider();
      }
    }, 100);
  }
</script>
