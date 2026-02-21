<?php
/**
 * Employee HMO Enrollment View
 * Manage employee plan assignments, enrollment tracking, waiting periods, and plan changes
 * 
 * Features:
 * - Plan assignment & coverage tracking
 * - Enrollment status management (Pending, Active, Waiting Period, Suspended, Terminated)
 * - Waiting period auto-activation
 * - Plan changes & upgrades/downgrades
 * - Dependent management
 * - Payroll deduction tracking
 * - HR Core integration for separations & suspensions
 * - Activity logs and audit trail
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
      position: relative;
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

    .tab-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      color: #d1d5db;
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
    .enrollment-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    .enrollment-table thead {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
    }

    .enrollment-table th {
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .enrollment-table tbody tr {
      border-bottom: 1px solid #e5e7eb;
      transition: background 0.15s ease;
      cursor: pointer;
    }

    .enrollment-table tbody tr:hover {
      background: #f0f9ff;
    }

    .enrollment-table td {
      padding: 1.25rem 1rem;
      color: #374151;
      font-size: 14px;
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

    .provider-muted {
      color: #9ca3af;
      font-size: 13px;
      font-weight: 400;
    }

    .coverage-tag {
      display: inline-flex;
      padding: 0.35rem 0.85rem;
      background: #dbeafe;
      color: #1e40af;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }

    .premium-value {
      text-align: right;
      font-weight: 600;
      color: #111827;
    }

    .effective-date {
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }

    .relative-date {
      font-size: 12px;
      color: #9ca3af;
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

    .badge-pending {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-waiting {
      background: #e0e7ff;
      color: #3730a3;
    }

    .badge-suspended {
      background: #fed7aa;
      color: #92400e;
    }

    .badge-terminated {
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

    .btn-success {
      background: #059669;
      color: white;
    }

    .btn-success:hover {
      background: #047857;
    }

    .btn-danger {
      background: #dc2626;
      color: white;
    }

    .btn-danger:hover {
      background: #b91c1c;
    }

    .btn-sm {
      padding: 0.35rem 0.75rem;
      font-size: 12px;
    }

    .action-buttons {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }

    /* Enrollment Details Card */
    .enrollment-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 1rem;
    }

    .detail-item {
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 1rem;
      background: #f9fafb;
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
      font-size: 16px;
      font-weight: 500;
      color: #1f2937;
      word-break: break-word;
    }

    /* Alerts */
    .alert {
      padding: 1rem 1.5rem;
      border-radius: 6px;
      margin-bottom: 1rem;
      border-left: 4px solid transparent;
    }

    .alert-info {
      background: #ecf0ff;
      border-left-color: #1e40af;
      color: #1e3a8a;
    }

    .alert-warning {
      background: #fffbeb;
      border-left-color: #d97706;
      color: #92400e;
    }

    .alert-danger {
      background: #fef2f2;
      border-left-color: #dc2626;
      color: #991b1b;
    }

    /* Modal Styles */
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

    .modal.show,
    .modal.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background-color: white;
      padding: 2rem;
      border-radius: 8px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 28px;
      cursor: pointer;
      color: #6b7280;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    /* Action Button Styling */
    .action-tooltip {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .action-tooltip .tooltip-text {
      visibility: hidden;
      width: 60px;
      background-color: #111827;
      color: #fff;
      text-align: center;
      padding: 0.375rem 0.5rem;
      border-radius: 4px;
      position: absolute;
      z-index: 1;
      bottom: 125%;
      left: 50%;
      margin-left: -30px;
      opacity: 0;
      transition: opacity 0.3s;
      font-size: 11px;
      font-weight: 500;
      white-space: nowrap;
    }

    .action-tooltip:hover .tooltip-text {
      visibility: visible;
      opacity: 1;
    }

    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
    }

    .form-control {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
    }

    .form-control:focus {
      outline: none;
      border-color: #1e40af;
      box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
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

    .stat-card.accent-waiting {
      border-left-color: #3b82f6;
    }

    .stat-card.accent-suspended {
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

    /* Error State */
    .error-state {
      text-align: center;
      padding: 2rem 1rem;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 8px;
      color: #991b1b;
    }

    .error-title {
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .error-message {
      font-size: 14px;
      margin-bottom: 1rem;
    }

    .load-time {
      font-size: 12px;
      color: #6b7280;
      margin-top: 0.5rem;
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

    .modal-actions {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      padding-top: 1.5rem;
    }

    .modal-actions .btn {
      width: 100%;
      justify-content: center;
    }
  </style>

  <!-- Stats Section -->
  <div class="stats-grid" id="statsGrid">
    <div class="stat-card accent-active">
      <div class="stat-icon">✓</div>
      <div class="stat-value" id="activeCount">0</div>
      <div class="stat-subtitle">Active enrollments</div>
      <div class="stat-label">Current coverage</div>
    </div>
    <div class="stat-card accent-pending">
      <div class="stat-icon">⏳</div>
      <div class="stat-value" id="pendingCount">0</div>
      <div class="stat-subtitle">Pending enrollments</div>
      <div class="stat-label">Awaiting activation</div>
    </div>
    <div class="stat-card accent-waiting">
      <div class="stat-icon">⏸</div>
      <div class="stat-value" id="waitingCount">0</div>
      <div class="stat-subtitle">Waiting period</div>
      <div class="stat-label">Activation pending</div>
    </div>
    <div class="stat-card accent-suspended">
      <div class="stat-icon">⚠</div>
      <div class="stat-value" id="suspendedCount">0</div>
      <div class="stat-subtitle">Suspended</div>
      <div class="stat-label">Temporarily inactive</div>
    </div>
  </div>

  <!-- Main Card -->
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">Employee HMO Enrollment Management</h3>
        <p style="text-align: left; font-size: 12px; color: #6b7280; margin-top: 0.25rem;">Track and manage employee HMO plan assignments, coverage, and enrollment status</p>
      </div>
      <button class="btn btn-primary" onclick="openAddEnrollmentModal()">+ New Enrollment</button>
    </div>

    <div class="card-content">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="search-box">
          <span class="search-box-icon">🔍</span>
          <input type="text" id="search-employee" placeholder="Search employee..." onkeyup="filterEnrollments()">
        </div>
        <select class="filter-select" id="filter-provider" onchange="filterEnrollments()">
          <option value="">All Providers</option>
        </select>
        <select class="filter-select" id="filter-coverage" onchange="filterEnrollments()">
          <option value="">All Coverage</option>
          <option value="employee_only">Employee Only</option>
          <option value="with_dependents">With Dependents</option>
        </select>
        <div class="tabs">
          <button class="tab-btn active" onclick="switchTab(event, 'active')">Active <span class="tab-count" id="count-active">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'pending')">Pending <span class="tab-count" id="count-pending">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'waiting')">Waiting <span class="tab-count" id="count-waiting">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'suspended')">Suspended <span class="tab-count" id="count-suspended">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'terminated')">Terminated <span class="tab-count" id="count-terminated">0</span></button>
        </div>
      </div>

      <!-- Active Enrollments Tab -->
      <div id="active-tab" class="tab-content" style="display: block;">
        <table class="enrollment-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Provider</th>
              <th>Coverage Type</th>
              <th>Employee Premium</th>
              <th>Effective Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="active-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pending Tab -->
      <div id="pending-tab" class="tab-content" style="display: none;">
        <table class="enrollment-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Provider</th>
              <th>Coverage Type</th>
              <th>Enrollment Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="pending-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Waiting Period Tab -->
      <div id="waiting-tab" class="tab-content" style="display: none;">
        <div class="alert alert-info">
          ℹ️ Enrollments automatically activate when waiting period ends. HR can override using the Actions menu.
        </div>
        <table class="enrollment-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Provider</th>
              <th>Days Remaining</th>
              <th>Period Ends</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="waiting-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Suspended Tab -->
      <div id="suspended-tab" class="tab-content" style="display: none;">
        <table class="enrollment-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Provider</th>
              <th>Suspension Reason</th>
              <th>Since</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="suspended-tbody">
            <tr>
              <td colspan="7" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Terminated Tab -->
      <div id="terminated-tab" class="tab-content" style="display: none;">
        <table class="enrollment-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Plan</th>
              <th>Provider</th>
              <th>Terminated Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="terminated-tbody">
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
      <h2 class="modal-title" id="modal-employee-name"></h2>
      <button class="modal-close" onclick="closeSideModal()">✕</button>
    </div>
    <div class="modal-content">
      <!-- Employee Summary -->
      <div class="modal-section">
        <div class="section-title">Status</div>
        <div class="section-content">
          <span id="modal-enrollment-status" class="badge"></span>
        </div>
      </div>

      <!-- Current Plan & Provider -->
      <div class="modal-section">
        <div class="section-title">Current Plan</div>
        <div class="section-content">
          <strong id="modal-plan-name"></strong>
          <div style="color: #9ca3af; font-size: 13px; margin-top: 0.25rem;" id="modal-provider-name"></div>
        </div>
      </div>

      <!-- Coverage Details -->
      <div class="modal-section">
        <div class="section-title">Coverage Information</div>
        <div class="section-content">
          <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
            <div>
              <div style="font-size: 12px; color: #9ca3af;">Coverage Type</div>
              <strong id="modal-coverage-type"></strong>
            </div>
            <div>
              <div style="font-size: 12px; color: #9ca3af;">Dependents</div>
              <strong id="modal-dependents-count">0</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Premium Breakdown -->
      <div class="modal-section">
        <div class="section-title">Premium Breakdown</div>
        <div class="section-content">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span>Employee Premium:</span>
            <strong id="modal-employee-premium">PHP 0</strong>
          </div>
          <div style="display: flex; justify-content: space-between; border-top: 1px solid #e5e7eb; padding-top: 0.5rem;">
            <span>Monthly Cost:</span>
            <strong id="modal-total-premium">PHP 0</strong>
          </div>
        </div>
      </div>

      <!-- Status Timeline -->
      <div class="modal-section">
        <div class="section-title">Enrollment Timeline</div>
        <div class="section-content">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
            <div>
              <div style="font-size: 12px; color: #9ca3af;">Enrollment Date</div>
              <strong id="modal-enrollment-date"></strong>
            </div>
            <div>
              <div style="font-size: 12px; color: #9ca3af;">Effective Date</div>
              <strong id="modal-effective-date"></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="modal-actions">
        <button class="btn btn-secondary btn-sm" onclick="suspendEnrollment()">⏸ Suspend</button>
        <button class="btn btn-danger btn-sm" onclick="terminateEnrollment()">🚫 Terminate</button>
        <button class="btn btn-primary" onclick="changePlan()">📋 Change Plan</button>
      </div>
    </div>
  </div>
</div>

<!-- View/Edit Enrollment Modal -->
<div id="enrollmentModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 style="margin: 0;">Enrollment Details</h3>
      <button class="close-btn" onclick="closeModal('enrollmentModal')">&times;</button>
    </div>
    
    <div class="enrollment-details" id="enrollmentDetails">
      <!-- Auto-populated -->
    </div>

    <div style="margin-top: 1.5rem;">
      <h4 style="font-size: 14px; font-weight: 600; margin-top: 1.5rem; margin-bottom: 1rem;">Dependent Coverage</h4>
      <div id="dependentsList" style="font-size: 13px; color: #6b7280;">
        <p>No dependents</p>
      </div>
      <button class="btn btn-sm btn-secondary" onclick="openAddDependentModal()" style="margin-top: 1rem;">+ Add Dependent</button>
    </div>

    <div style="margin-top: 1.5rem;">
      <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 1rem;">Activity Log</h4>
      <div id="activityLog" style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 4px; padding: 0.75rem; font-size: 12px;">
        <p style="color: #9ca3af;">Loading...</p>
      </div>
    </div>

    <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
      <button class="btn btn-primary btn-sm" onclick="openPlanChangeModal()">Change Plan</button>
      <button class="btn btn-secondary btn-sm" onclick="closeModal('enrollmentModal')">Close</button>
    </div>
  </div>
</div>

<!-- Plan Change Modal -->
<div id="planChangeModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 style="margin: 0;">Request Plan Change</h3>
      <button class="close-btn" onclick="closeModal('planChangeModal')">&times;</button>
    </div>

    <div class="form-group">
      <label class="form-label">New Plan</label>
      <select id="newPlanSelect" class="form-control">
        <option value="">Select a plan...</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Effective Date</label>
      <input type="date" id="changeEffectiveDate" class="form-control">
    </div>

    <div class="form-group">
      <label class="form-label">Reason for Change</label>
      <textarea id="changeReason" class="form-control" rows="3" placeholder="e.g., Employee requested upgrade..."></textarea>
    </div>

    <div style="display: flex; gap: 0.5rem;">
      <button class="btn btn-primary btn-sm" onclick="submitPlanChange()">Request Change</button>
      <button class="btn btn-secondary btn-sm" onclick="closeModal('planChangeModal')">Cancel</button>
    </div>
  </div>
</div>

<!-- Add Dependent Modal -->
<div id="addDependentModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 style="margin: 0;">Add Dependent</h3>
      <button class="close-btn" onclick="closeModal('addDependentModal')">&times;</button>
    </div>

    <div class="form-group">
      <label class="form-label">Dependent Name</label>
      <input type="text" id="dependentName" class="form-control" placeholder="Full name">
    </div>

    <div class="form-group">
      <label class="form-label">Relationship</label>
      <select id="dependentRelationship" class="form-control">
        <option value="">Select relationship...</option>
        <option value="spouse">Spouse</option>
        <option value="child">Child</option>
        <option value="parent">Parent</option>
        <option value="other">Other</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Date of Birth</label>
      <input type="date" id="dependentDOB" class="form-control">
    </div>

    <div style="display: flex; gap: 0.5rem;">
      <button class="btn btn-primary btn-sm" onclick="submitAddDependent()">Add Dependent</button>
      <button class="btn btn-secondary btn-sm" onclick="closeModal('addDependentModal')">Cancel</button>
    </div>
  </div>
</div>

<!-- New Enrollment Modal -->
<div id="newEnrollmentModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 style="margin: 0;">Create New Enrollment</h3>
      <button class="close-btn" onclick="closeModal('newEnrollmentModal')">&times;</button>
    </div>

    <div class="form-group">
      <label class="form-label">Employee</label>
      <input type="text" id="employeeSearch" class="form-control" placeholder="Search employee...">
      <input type="hidden" id="selectedEmployeeId">
      <div id="employeeSearchResults" style="max-height: 150px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 4px; margin-top: 0.5rem; display: none;"></div>
    </div>

    <div class="form-group">
      <label class="form-label">HMO Provider</label>
      <select id="providerSelect" class="form-control"></select>
    </div>

    <div class="form-group">
      <label class="form-label">Plan</label>
      <select id="planSelect" class="form-control"></select>
    </div>

    <div class="form-group">
      <label class="form-label">Coverage Type</label>
      <select id="coverageTypeSelect" class="form-control">
        <option value="employee_only">Employee Only</option>
        <option value="with_dependents">With Dependents</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Effective Date</label>
      <input type="date" id="enrollmentEffectiveDate" class="form-control">
    </div>

    <div style="display: flex; gap: 0.5rem;">
      <button class="btn btn-primary btn-sm" onclick="submitNewEnrollment()">Create Enrollment</button>
      <button class="btn btn-secondary btn-sm" onclick="closeModal('newEnrollmentModal')">Cancel</button>
    </div>
  </div>
</div>

<script>
  // ============================================
  // Global State & Variables
  // ============================================
  if (typeof currentEnrollmentId === 'undefined') { var currentEnrollmentId = null; }
  currentEnrollmentId = null;
  if (typeof allEnrollments === 'undefined') {
    var allEnrollments = {
      active: [],
      pending: [],
      waiting: [],
      suspended: [],
      terminated: []
    };
  } else {
    allEnrollments = { active: [], pending: [], waiting: [], suspended: [], terminated: [] };
  }
  if (typeof allProviders === 'undefined') { var allProviders = []; }
  allProviders = [];
  if (typeof currentTab === 'undefined') { var currentTab = 'active'; }
  currentTab = 'active';

  // ============================================
  // Helper Functions
  // ============================================
  
  /**
   * Generate initials from first and last name
   */
  function getInitials(firstName, lastName) {
    return ((firstName || '')[0] + (lastName || '')[0]).toUpperCase();
  }

  /**
   * Format relative date display
   * Returns format like "Starts in 3 days" or "Expires in 5 days"
   */
  function formatRelativeDate(dateString, referenceDate = new Date()) {
    const date = new Date(dateString);
    const now = new Date(referenceDate);
    const diffTime = date - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
      return `Expired ${Math.abs(diffDays)} day${Math.abs(diffDays) !== 1 ? 's' : ''} ago`;
    } else if (diffDays === 0) {
      return 'Today';
    } else if (diffDays === 1) {
      return 'Tomorrow';
    } else {
      return `Starts in ${diffDays} day${diffDays !== 1 ? 's' : ''}`;
    }
  }

  /**
   * Get status badge HTML
   */
  function getStatusBadge(status) {
    const badgeClass = `badge-${status}`;
    const statusLabel = status.replace(/_/g, ' ').toUpperCase();
    return `<span class="badge ${badgeClass}">${statusLabel}</span>`;
  }

  /**
   * Format currency values
   */
  function formatCurrency(amount) {
    const numAmount = parseFloat(amount || 0);
    return 'PHP ' + numAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  /**
   * Format date for display
   */
  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
  }

  // ============================================
  // Tab Switching with localStorage
  // ============================================

  function switchTab(event, tabName) {
    event.preventDefault();
    
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
      tab.style.display = 'none';
    });

    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.classList.remove('active');
    });

    // Show selected tab and mark button as active
    const tabElement = document.getElementById(tabName + '-tab');
    if (tabElement) {
      tabElement.style.display = 'block';
    }
    event.target.classList.add('active');

    // Save preference to localStorage
    localStorage.setItem('selectedTab-enrollment', tabName);
    currentTab = tabName;

    // Load data for the selected tab
    loadTabData(tabName);
  }

  function loadTabData(tabName) {
    switch(tabName) {
      case 'active':
        loadActiveEnrollments();
        break;
      case 'pending':
        loadPendingEnrollments();
        break;
      case 'waiting':
        loadWaitingEnrollments();
        break;
      case 'suspended':
        loadSuspendedEnrollments();
        break;
      case 'terminated':
        loadTerminatedEnrollments();
        break;
    }
  }

  // ============================================
  // Enrollment Data Loading
  // ============================================

  function loadActiveEnrollments() {
    console.log('Loading active enrollments...');
    fetch('modules/hmo/api.php?action=getActiveEnrollments')
      .then(response => response.json())
      .then(data => {
        console.log('Enrollment data received:', data);
        if (data.success && data.data) {
          allEnrollments.active = data.data || [];
          console.log('Rendering', allEnrollments.active.length, 'enrollments');
          renderTable(allEnrollments.active, 'active-tbody', 'active');
          updateCounts();
        } else {
          console.log('No success or empty data:', data);
          showErrorState('active-tbody', 'Failed to load active enrollments');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showErrorState('active-tbody', 'Error loading active enrollments');
      });
  }

  function loadPendingEnrollments() {
    fetch('modules/hmo/api.php?action=getPendingEnrollments')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allEnrollments.pending = data.data || [];
          renderTable(allEnrollments.pending, 'pending-tbody', 'pending');
          updateCounts();
        } else {
          showErrorState('pending-tbody', 'Failed to load pending enrollments');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showErrorState('pending-tbody', 'Error loading pending enrollments');
      });
  }

  function loadWaitingEnrollments() {
    fetch('modules/hmo/api.php?action=getWaitingPeriodEnrollments')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allEnrollments.waiting = data.data || [];
          renderTable(allEnrollments.waiting, 'waiting-tbody', 'waiting');
          updateCounts();
        } else {
          showErrorState('waiting-tbody', 'Failed to load waiting enrollments');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showErrorState('waiting-tbody', 'Error loading waiting enrollments');
      });
  }

  function loadSuspendedEnrollments() {
    fetch('modules/hmo/api.php?action=getSuspendedEnrollments')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allEnrollments.suspended = data.data || [];
          renderTable(allEnrollments.suspended, 'suspended-tbody', 'suspended');
          updateCounts();
        } else {
          showErrorState('suspended-tbody', 'Failed to load suspended enrollments');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showErrorState('suspended-tbody', 'Error loading suspended enrollments');
      });
  }

  function loadTerminatedEnrollments() {
    fetch('modules/hmo/api.php?action=getTerminatedEnrollments')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allEnrollments.terminated = data.data || [];
          renderTable(allEnrollments.terminated, 'terminated-tbody', 'terminated');
          updateCounts();
        } else {
          showErrorState('terminated-tbody', 'Failed to load terminated enrollments');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showErrorState('terminated-tbody', 'Error loading terminated enrollments');
      });
  }

  // ============================================
  // Table Rendering with New Structure
  // ============================================

  function renderTable(enrollments, tbodyId, status) {
    const tbody = document.getElementById(tbodyId);
    
    if (!enrollments || enrollments.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-icon">📋</div>
              <div class="empty-title">No ${status} enrollments</div>
              <div class="empty-message">Create a new enrollment to get started</div>
            </div>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = enrollments.map(enroll => {
      const firstName = enroll.first_name || '';
      const lastName = enroll.last_name || '';
      const initials = getInitials(firstName, lastName);
      const avatar = createAvatar(initials);
      
      // Generate action buttons based on enrollment status
      let actionButtons = '';
      if (status === 'suspended') {
        actionButtons = `
          <button class="btn-icon" onclick="viewEnrollment(${enroll.id}, event)" title="View Details">👁️</button>
          <button class="btn-icon" onclick="unsuspendEnrollment(${enroll.id}, event)" title="Restore">🔄</button>
          <button class="btn-icon" onclick="terminateEnrollment(${enroll.id}, event)" title="Terminate">✕</button>
        `;
      } else if (status === 'terminated' || status === 'expired') {
        actionButtons = `
          <button class="btn-icon" onclick="viewEnrollment(${enroll.id}, event)" title="View Details">👁️</button>
        `;
      } else {
        // active, pending, waiting
        actionButtons = `
          <button class="btn-icon" onclick="viewEnrollment(${enroll.id}, event)" title="View Details">👁️</button>
          <button class="btn-icon" onclick="suspendEnrollment(${enroll.id}, event)" title="Suspend">⏸</button>
          <button class="btn-icon" onclick="terminateEnrollment(${enroll.id}, event)" title="Terminate">✕</button>
        `;
      }
      
      return `
        <tr onclick="openSideModal(${enroll.id}, event)">
          <td>
            <div class="employee-cell">
              <div class="employee-avatar" title="${firstName} ${lastName}">${initials}</div>
              <div class="employee-info">
                <strong>${firstName} ${lastName}</strong>
                <div class="employee-id">${enroll.employee_code || 'N/A'}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="plan-badge">${enroll.plan_name || 'N/A'}</span>
          </td>
          <td>
            <span class="provider-muted">${enroll.provider_name || 'N/A'}</span>
          </td>
          <td>
            <span class="coverage-tag">${(enroll.coverage_type || 'N/A').replace(/_/g, ' ')}</span>
          </td>
          <td class="premium-value">
            ${formatCurrency(enroll.employee_premium_amount || 0)}
          </td>
          <td>
            <div class="effective-date">
              <span>${formatDate(enroll.effective_date || enroll.enrollment_date)}</span>
              <span class="relative-date">${formatRelativeDate(enroll.effective_date || enroll.enrollment_date)}</span>
            </div>
          </td>
          <td>
            <div class="actions-cell">
              ${actionButtons}
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  /**
   * Create avatar with gradient background
   */
  function createAvatar(initials) {
    const colors = [
      '#667eea',
      '#764ba2', 
      '#f093fb',
      '#4facfe',
      '#00f2fe',
      '#fa709a',
      '#fee140'
    ];
    const colorIndex = initials.charCodeAt(0) % colors.length;
    const bgColor = colors[colorIndex];
    return `<div class="employee-avatar" style="background: ${bgColor};">${initials}</div>`;
  }

  // ============================================
  // Side Modal Functions
  // ============================================

  function openSideModal(enrollmentId, event) {
    event.stopPropagation();
    currentEnrollmentId = enrollmentId;

    fetch(`modules/hmo/api.php?action=getEnrollmentDetails&id=${enrollmentId}`)
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

  function populateSideModal(enroll) {
    const firstName = enroll.first_name || '';
    const lastName = enroll.last_name || '';

    // Set header
    document.getElementById('modal-employee-name').textContent = `${firstName} ${lastName}`;

    // Set status badge
    const statusBadge = getStatusBadge(enroll.enrollment_status || 'active');
    document.getElementById('modal-enrollment-status').innerHTML = statusBadge;

    // Set plan & provider
    document.getElementById('modal-plan-name').textContent = enroll.plan_name || 'N/A';
    document.getElementById('modal-provider-name').textContent = enroll.provider_name || 'N/A';

    // Set coverage information
    document.getElementById('modal-coverage-type').textContent = (enroll.coverage_type || 'N/A').replace(/_/g, ' ');
    document.getElementById('modal-dependents-count').textContent = enroll.dependents_count || '0';

    // Set premium breakdown
    document.getElementById('modal-employee-premium').textContent = formatCurrency(enroll.employee_premium_amount || 0);
    const totalPremium = (parseFloat(enroll.employee_premium_amount || 0) + parseFloat(enroll.employer_share_amount || 0));
    document.getElementById('modal-total-premium').textContent = formatCurrency(totalPremium);

    // Set enrollment timeline
    document.getElementById('modal-enrollment-date').textContent = formatDate(enroll.enrollment_date);
    document.getElementById('modal-effective-date').textContent = formatDate(enroll.effective_date);
  }

  function closeSideModal() {
    document.getElementById('modal-overlay').classList.remove('active');
    document.getElementById('side-modal').classList.remove('active');
  }

  // ============================================
  // Enrollment Actions
  // ============================================

  function viewEnrollment(id, event) {
    event.stopPropagation();
    openSideModal(id, { stopPropagation: () => {} });
  }

  function suspendEnrollment(enrollmentId = null, event) {
    if (event) event.stopPropagation();
    if (enrollmentId) currentEnrollmentId = enrollmentId;
    
    if (!currentEnrollmentId) return;
    
    if (!confirm('Are you sure you want to suspend this enrollment?')) return;
    
    fetch('modules/hmo/api.php?action=suspendEnrollment&id=' + currentEnrollmentId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Enrollment suspended successfully');
        closeSideModal();
        loadTabData(currentTab);
      } else {
        alert('Error: ' + (data.error || 'Failed to suspend enrollment'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error suspending enrollment');
    });
  }

  function terminateEnrollment(enrollmentId = null, event) {
    if (event) event.stopPropagation();
    if (enrollmentId) currentEnrollmentId = enrollmentId;
    
    if (!currentEnrollmentId) return;
    
    if (!confirm('Are you sure? This will terminate the enrollment.')) return;
    
    fetch('modules/hmo/api.php?action=terminateEnrollment&id=' + currentEnrollmentId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Enrollment terminated successfully');
        closeSideModal();
        loadTabData(currentTab);
      } else {
        alert('Error: ' + (data.error || 'Failed to terminate enrollment'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error terminating enrollment');
    });
  }

  function unsuspendEnrollment(enrollmentId = null, event) {
    if (event) event.stopPropagation();
    if (enrollmentId) currentEnrollmentId = enrollmentId;
    
    if (!currentEnrollmentId) return;
    
    if (!confirm('Restore this suspended enrollment to active?')) return;
    
    fetch('modules/hmo/api.php?action=unsuspendEnrollment&id=' + currentEnrollmentId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Enrollment restored successfully');
        closeSideModal();
        loadTabData(currentTab);
        updateCounts();
      } else {
        alert('Error: ' + (data.error || 'Failed to restore enrollment'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error restoring enrollment');
    });
  }

  function changePlan() {
    if (!currentEnrollmentId) return;
    
    // Load plans for dropdown
    fetch('modules/hmo/api.php?action=getPlans')
      .then(response => response.json())
      .then(data => {
        const select = document.getElementById('newPlanSelect');
        select.innerHTML = '<option value="">Select a plan...</option>';
        if (data.success && data.data) {
          data.data.forEach(plan => {
            select.innerHTML += `<option value="${plan.id}">${plan.plan_name} (${formatCurrency(plan.annual_premium_per_employee || plan.premium_amount || 0)})</option>`;
          });
        }
      });
    
    openModal('planChangeModal');
  }

  function submitPlanChange() {
    if (!currentEnrollmentId) {
      alert('No enrollment selected');
      return;
    }
    const newPlanId = document.getElementById('newPlanSelect').value;
    const effectiveDate = document.getElementById('changeEffectiveDate').value;
    const reason = document.getElementById('changeReason').value;

    if (!newPlanId) {
      alert('Please select a plan');
      return;
    }

    fetch('modules/hmo/api.php?action=requestPlanChange&enrollment_id=' + currentEnrollmentId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        new_plan_id: newPlanId,
        effective_date: effectiveDate,
        reason: reason
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Plan change requested successfully');
        closeModal('planChangeModal');
        loadTabData(currentTab);
      } else {
        alert('Error: ' + (data.error || 'Failed to request plan change'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error requesting plan change');
    });
  }

  function openAddEnrollmentModal() {
    openModal('newEnrollmentModal');
    // Load providers
    fetch('modules/hmo/api.php?action=getProviders')
      .then(response => response.json())
      .then(data => {
        const select = document.getElementById('providerSelect');
        select.innerHTML = '<option value="">Select provider...</option>';
        if (data.success && data.data) {
          data.data.forEach(provider => {
            select.innerHTML += `<option value="${provider.id}">${provider.provider_name}</option>`;
          });
        }
      });
  }

  function openAddDependentModal() {
    openModal('addDependentModal');
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove('show');
      modal.classList.remove('active');
    }
  }

  function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add('show');
    }
  }

  function submitAddDependent() {
    const name = document.getElementById('dependentName').value;
    const relationship = document.getElementById('dependentRelationship').value;
    const dob = document.getElementById('dependentDOB').value;

    if (!name || !relationship || !dob) {
      alert('Please fill all fields');
      return;
    }

    fetch('modules/hmo/api.php?action=addDependent&enrollment_id=' + currentEnrollmentId, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name,
        relationship: relationship,
        date_of_birth: dob
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Dependent added successfully');
        closeModal('addDependentModal');
        loadTabData(currentTab);
      } else {
        alert('Error: ' + (data.error || 'Failed to add dependent'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error adding dependent');
    });
  }

  function submitNewEnrollment() {
    alert('Create new enrollment coming soon');
  }

  // ============================================
  // Search & Filtering
  // ============================================

  function filterEnrollments() {
    const searchValue = document.getElementById('search-employee').value.toLowerCase();
    const providerValue = document.getElementById('filter-provider').value;
    const coverageValue = document.getElementById('filter-coverage').value;

    // Get current tab data
    const enrollments = allEnrollments[currentTab] || [];

    // Filter data
    const filtered = enrollments.filter(enroll => {
      const matchesName = (enroll.first_name + ' ' + enroll.last_name).toLowerCase().includes(searchValue);
      const matchesProvider = !providerValue || enroll.provider_id == providerValue;
      const matchesCoverage = !coverageValue || enroll.coverage_type === coverageValue;
      
      return matchesName && matchesProvider && matchesCoverage;
    });

    // Render filtered results
    const tbodyId = `${currentTab}-tbody`;
    renderTable(filtered, tbodyId, currentTab);
  }

  // ============================================
  // Count Badge Updates
  // ============================================

  function updateCounts() {
    document.getElementById('count-active').textContent = (allEnrollments.active || []).length;
    document.getElementById('count-pending').textContent = (allEnrollments.pending || []).length;
    document.getElementById('count-waiting').textContent = (allEnrollments.waiting || []).length;
    document.getElementById('count-suspended').textContent = (allEnrollments.suspended || []).length;
    document.getElementById('count-terminated').textContent = (allEnrollments.terminated || []).length;

    // Update stat cards
    document.getElementById('activeCount').textContent = (allEnrollments.active || []).length;
    document.getElementById('pendingCount').textContent = (allEnrollments.pending || []).length;
    document.getElementById('waitingCount').textContent = (allEnrollments.waiting || []).length;
    document.getElementById('suspendedCount').textContent = (allEnrollments.suspended || []).length;
  }

  // ============================================
  // Error State Display
  // ============================================

  function showErrorState(tbodyId, message) {
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = `
      <tr>
        <td colspan="7">
          <div class="error-state">
            <div class="error-title">⚠️ Error loading data</div>
            <div class="error-message">${message}</div>
            <div class="load-time">Last updated: ${new Date().toLocaleTimeString()}</div>
          </div>
        </td>
      </tr>
    `;
  }

  // ============================================
  // Load Providers for Filter Dropdown
  // ============================================

  function loadProvidersForFilter() {
    fetch('modules/hmo/api.php?action=getProviders')
      .then(response => response.json())
      .then(data => {
        const select = document.getElementById('filter-provider');
        if (data.success && data.data) {
          allProviders = data.data;
          data.data.forEach(provider => {
            const option = document.createElement('option');
            option.value = provider.id;
            option.textContent = provider.provider_name;
            select.appendChild(option);
          });
        }
      });
  }

  // ============================================
  // Initialization
  // ============================================

  // Initialize on page load (runs immediately for dynamic loading)
  // Load providers for filter dropdown
  loadProvidersForFilter();

  // Load all enrollment data to populate KPI board
  loadActiveEnrollments();
  loadPendingEnrollments();
  loadWaitingEnrollments();
  loadSuspendedEnrollments();
  loadTerminatedEnrollments();
  updateCounts();

  // Restore tab preference from localStorage
  const savedTab = localStorage.getItem('selectedTab-enrollment') || 'active';
  
  // Set default to active tab
  currentTab = 'active';

  // Close modals when clicking overlay
  const overlayModal = document.getElementById('modal-overlay');
  if (overlayModal) {
      overlayModal.addEventListener('click', closeSideModal);
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
      const modals = document.querySelectorAll('.modal');
      modals.forEach(modal => {
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    };
</script>
