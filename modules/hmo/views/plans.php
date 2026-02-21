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

    .modal-overlay.show {
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

    .side-modal.show {
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

    /* ===== MODAL SECTIONS ===== */
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
      text-transform: uppercase;
      color: #6b7280;
      margin-bottom: 1rem;
      letter-spacing: 0.5px;
    }

    .modal-detail {
      margin-bottom: 1rem;
    }

    .modal-detail-label {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      color: #9ca3af;
      margin-bottom: 0.25rem;
      display: block;
    }

    .modal-detail-value {
      font-size: 14px;
      color: #111827;
      font-weight: 500;
      display: block;
    }

    .modal-actions {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      margin-top: 2rem;
      padding-top: 2rem;
      border-top: 1px solid #e5e7eb;
    }

    .btn-modal {
      padding: 0.75rem 1rem;
      border: none;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      text-align: center;
    }

    .btn-modal-primary {
      background: #3b82f6;
      color: white;
    }

    .btn-modal-primary:hover {
      background: #2563eb;
    }

    .btn-modal-secondary {
      background: #f3f4f6;
      color: #374151;
      border: 1px solid #d1d5db;
    }

    .btn-modal-secondary:hover {
      background: #e5e7eb;
    }

    .btn-modal-danger {
      background: #fee2e2;
      color: #dc2626;
      border: 1px solid #fca5a5;
    }

    .btn-modal-danger:hover {
      background: #fecaca;
    }

    /* ===== EDIT FORM MODAL ===== */

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

    .edit-form-modal.active {
      right: 0;
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

  <!-- Plan View Modal -->
  <div class="modal-overlay" id="planModalOverlay" onclick="closePlanModal()"></div>
  <div class="side-modal" id="planSideModal">
    <div class="side-modal-header">
      <h2 class="side-modal-title">Plan Details</h2>
      <button class="modal-close-btn" onclick="closePlanModal()">✕</button>
    </div>
    <div class="side-modal-content">
      <div class="modal-section">
        <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
          <div class="provider-avatar" style="width: 60px; height: 60px; font-size: 18px;" id="planModalInitials">--</div>
          <div style="flex: 1;">
            <h3 id="planModalName" style="margin: 0 0 0.25rem 0; font-size: 16px; font-weight: 700;">--</h3>
            <p id="planModalCode" style="margin: 0; font-size: 13px; color: #6b7280;">--</p>
          </div>
        </div>
      </div>

      <div class="modal-section">
        <h4 class="modal-section-title">Provider</h4>
        <div class="modal-detail">
          <span class="modal-detail-label">Provider</span>
          <span class="modal-detail-value" id="planModalProvider">--</span>
        </div>
      </div>

      <div class="modal-section">
        <h4 class="modal-section-title">Plan Details</h4>
        <div class="modal-detail">
          <span class="modal-detail-label">Type</span>
          <span class="modal-detail-value" id="planModalType">--</span>
        </div>
        <div class="modal-detail">
          <span class="modal-detail-label">Status</span>
          <span class="modal-detail-value" id="planModalStatus">--</span>
        </div>
        <div class="modal-detail">
          <span class="modal-detail-label">Description</span>
          <span class="modal-detail-value" id="planModalDescription">--</span>
        </div>
      </div>

      <div class="modal-section">
        <h4 class="modal-section-title">Premiums</h4>
        <div class="modal-detail">
          <span class="modal-detail-label">Annual (Employee)</span>
          <span class="modal-detail-value" id="planModalAnnualEmp">--</span>
        </div>
        <div class="modal-detail">
          <span class="modal-detail-label">Annual (Dependent)</span>
          <span class="modal-detail-value" id="planModalAnnualDep">--</span>
        </div>
        <div class="modal-detail">
          <span class="modal-detail-label">Monthly Premium</span>
          <span class="modal-detail-value" id="planModalMonthly">--</span>
        </div>
      </div>

      <div class="modal-section">
        <h4 class="modal-section-title">Coverage Limits</h4>
        <div class="modal-detail">
          <span class="modal-detail-label">Out-of-Pocket Limit</span>
          <span class="modal-detail-value" id="planModalOOPLimit">--</span>
        </div>
        <div class="modal-detail">
          <span class="modal-detail-label">Deductible</span>
          <span class="modal-detail-value" id="planModalDeductible">--</span>
        </div>
        <div class="modal-detail">
          <span class="modal-detail-label">Copay %</span>
          <span class="modal-detail-value" id="planModalCopay">--</span>
        </div>
      </div>

      <div class="modal-section">
        <h4 class="modal-section-title">Network</h4>
        <div class="modal-detail">
          <span class="modal-detail-label">Doctors</span>
          <span class="modal-detail-value" id="planModalDoctors">--</span>
        </div>
        <div class="modal-detail">
          <span class="modal-detail-label">Hospitals</span>
          <span class="modal-detail-value" id="planModalHospitals">--</span>
        </div>
      </div>

      <div class="modal-actions">
        <button class="btn-modal btn-modal-primary" onclick="editPlan(currentPlanId)">✏️ Edit Plan</button>
        <button class="btn-modal btn-modal-danger" onclick="deactivatePlan(currentPlanId)">⊘ Delete</button>
      </div>
    </div>
  </div>

  <!-- Plan Edit Form Modal -->
  <div class="modal-overlay" id="planEditFormOverlay" onclick="closePlanEditModal()"></div>
  <div class="edit-form-modal" id="planEditFormModal">
    <div class="edit-form-header">
      <h2 id="planFormTitle">Add Plan</h2>
      <button class="modal-close-btn" onclick="closePlanEditModal()">✕</button>
    </div>
    <div class="edit-form-content">
      <form id="planForm">
        <div class="form-group">
          <label>Plan Name *</label>
          <input type="text" id="plan_name" placeholder="e.g., Standard Plan" required>
        </div>
        
        <div class="form-group">
          <label>Plan Code *</label>
          <input type="text" id="plan_code" placeholder="e.g., PLN001" required>
        </div>

        <div class="form-group">
          <label>Provider *</label>
          <select id="provider_id" required>
            <option value="">Select Provider</option>
          </select>
        </div>

        <div class="form-group">
          <label>Plan Type</label>
          <select id="plan_type">
            <option value="Basic">Basic</option>
            <option value="Standard">Standard</option>
            <option value="Premium">Premium</option>
            <option value="Executive">Executive</option>
          </select>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea id="description" placeholder="Plan description" rows="3"></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Annual Premium (Employee)</label>
            <input type="number" id="annual_premium_per_employee" placeholder="0.00" step="0.01">
          </div>
          <div class="form-group">
            <label>Annual Premium (Dependent)</label>
            <input type="number" id="annual_premium_per_dependent" placeholder="0.00" step="0.01">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Monthly Premium</label>
            <input type="number" id="monthly_premium" placeholder="0.00" step="0.01">
          </div>
          <div class="form-group">
            <label>Out-of-Pocket Limit</label>
            <input type="number" id="out_of_pocket_limit" placeholder="0.00" step="0.01">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Deductible Amount</label>
            <input type="number" id="deductible_amount" placeholder="0.00" step="0.01">
          </div>
          <div class="form-group">
            <label>Copay Percentage (%)</label>
            <input type="number" id="copay_percentage" placeholder="20" min="0" max="100">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>In-Network Doctors</label>
            <input type="number" id="in_network_doctors" placeholder="0">
          </div>
          <div class="form-group">
            <label>In-Network Hospitals</label>
            <input type="number" id="in_network_hospitals" placeholder="0">
          </div>
        </div>

        <div class="form-group">
          <label>Plan Launch Date</label>
          <input type="date" id="plan_launch_date">
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" id="is_active">
            Plan is Active
          </label>
        </div>

        <div class="form-actions">
          <button type="button" class="btn btn-secondary" onclick="closePlanEditModal()">Cancel</button>
          <button type="button" id="planFormButton" class="btn btn-primary" onclick="submitPlanForm()">Add Plan</button>
        </div>
      </form>
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
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value || 0);
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

  // Plan Modal Functions
  var currentPlanId = null;
  var allProviders = [];

  function closePlanModal() {
    try {
      const planSideModal = document.getElementById('planSideModal');
      const planModalOverlay = document.getElementById('planModalOverlay');
      if (planSideModal) planSideModal.classList.remove('show');
      if (planModalOverlay) planModalOverlay.classList.remove('show');
      currentPlanId = null;
    } catch (error) {
      console.error('Error closing plan modal:', error);
    }
  }

  function closePlanEditModal() {
    try {
      const planEditFormModal = document.getElementById('planEditFormModal');
      const planEditFormOverlay = document.getElementById('planEditFormOverlay');
      if (planEditFormModal) planEditFormModal.classList.remove('show');
      if (planEditFormOverlay) planEditFormOverlay.classList.remove('show');
    } catch (error) {
      console.error('Error closing plan edit modal:', error);
    }
  }

  function viewPlanModal(planId) {
    console.log('viewPlanModal called with ID:', planId);
    try {
      currentPlanId = planId;
      const plan = allPlans.find(p => p.id == planId);
      
      if (!plan) {
        console.log('Plan not in allPlans, fetching from API');
        fetch(`modules/hmo/api.php?action=getPlanDetail&id=${planId}`)
          .then(response => response.json())
          .then(data => {
            console.log('Plan data from API:', data);
            if (data.success && data.data) {
              populatePlanModal(data.data);
            } else {
              alert('Error loading plan details');
            }
          })
          .catch(error => {
            console.error('Error fetching plan:', error);
            alert('Error loading plan details');
          });
      } else {
        console.log('Plan found in allPlans:', plan);
        populatePlanModal(plan);
      }
    } catch (error) {
      console.error('Error in viewPlanModal:', error);
      alert('Error viewing plan');
    }
  }

  function populatePlanModal(plan) {
    console.log('populatePlanModal called with:', plan);
    try {
      const initials = (plan.plan_name || '').substring(0, 2).toUpperCase();
      const planSideModal = document.getElementById('planSideModal');
      const planModalOverlay = document.getElementById('planModalOverlay');
      
      if (!planSideModal) {
        console.error('planSideModal element not found in DOM!');
        alert('Modal element not found');
        return;
      }

      // Set all the text values
      const elements = {
        'planModalInitials': initials,
        'planModalName': plan.plan_name || '--',
        'planModalCode': plan.plan_code || 'N/A',
        'planModalProvider': plan.provider_name || 'N/A',
        'planModalType': plan.plan_type || 'Standard',
        'planModalStatus': (plan.is_active === 1 || plan.is_active === true) ? '✓ Active' : '✕ Inactive',
        'planModalDescription': plan.description || '-',
        'planModalAnnualEmp': 'PHP ' + (parseFloat(plan.annual_premium_per_employee || 0).toLocaleString()),
        'planModalAnnualDep': 'PHP ' + (parseFloat(plan.annual_premium_per_dependent || 0).toLocaleString()),
        'planModalMonthly': 'PHP ' + (parseFloat(plan.monthly_premium || 0).toLocaleString()),
        'planModalOOPLimit': 'PHP ' + (parseFloat(plan.out_of_pocket_limit || 0).toLocaleString()),
        'planModalDeductible': 'PHP ' + (parseFloat(plan.deductible_amount || 0).toLocaleString()),
        'planModalCopay': (plan.copay_percentage || 0) + '%',
        'planModalDoctors': (plan.in_network_doctors || 0).toLocaleString() + ' doctors',
        'planModalHospitals': (plan.in_network_hospitals || 0).toLocaleString() + ' hospitals'
      };

      // Populate all elements
      for (const [elemId, value] of Object.entries(elements)) {
        const elem = document.getElementById(elemId);
        if (elem) {
          elem.textContent = value;
        } else {
          console.warn(`Element ${elemId} not found`);
        }
      }

      // Show the modals
      console.log('Showing plan modal...');
      if (planSideModal) planSideModal.classList.add('show');
      if (planModalOverlay) planModalOverlay.classList.add('show');
      console.log('Plan modal should now be visible');
    } catch (error) {
      console.error('Error populating plan modal:', error);
      alert('Error displaying plan details: ' + error.message);
    }
  }

  function editPlan(planId) {
    console.log('editPlan called with ID:', planId);
    try {
      currentPlanId = planId;
      const plan = allPlans.find(p => p.id == planId);
      
      if (!plan) {
        console.log('Plan not in allPlans, fetching from API');
        fetch(`modules/hmo/api.php?action=getPlanDetail&id=${planId}`)
          .then(response => response.json())
          .then(data => {
            console.log('Plan data from API:', data);
            if (data.success && data.data) {
              populatePlanForm(data.data);
              showPlanEditModal();
            } else {
              alert('Error loading plan for editing');
            }
          })
          .catch(error => {
            console.error('Error fetching plan:', error);
            alert('Error loading plan for editing');
          });
      } else {
        console.log('Plan found in allPlans:', plan);
        populatePlanForm(plan);
        showPlanEditModal();
      }
    } catch (error) {
      console.error('Error in editPlan:', error);
      alert('Error editing plan');
    }
  }

  function showPlanEditModal() {
    try {
      const planEditFormModal = document.getElementById('planEditFormModal');
      const planEditFormOverlay = document.getElementById('planEditFormOverlay');
      const provider_id_select = document.getElementById('provider_id');
      
      // Load providers for dropdown
      if (provider_id_select && provider_id_select.options.length === 1) {  // Only load if not already loaded
        fetch('modules/hmo/api.php?action=getProviders')
          .then(response => response.json())
          .then(data => {
            if (data.success && data.data.length > 0) {
              provider_id_select.innerHTML = '<option value="">Select Provider</option>' +
                data.data.map(p => `<option value="${p.id}">${p.provider_name}</option>`).join('');
            }
          })
          .catch(error => console.error('Error loading providers:', error));
      }
      
      if (planEditFormModal) planEditFormModal.classList.add('show');
      if (planEditFormOverlay) planEditFormOverlay.classList.add('show');
    } catch (error) {
      console.error('Error showing plan edit modal:', error);
    }
  }

  function populatePlanForm(plan) {
    try {
      const planFormTitle = document.getElementById('planFormTitle');
      const planFormButton = document.getElementById('planFormButton');
      const plan_name = document.getElementById('plan_name');
      const plan_code = document.getElementById('plan_code');
      const provider_id = document.getElementById('provider_id');
      const plan_type = document.getElementById('plan_type');
      const description = document.getElementById('description');
      const annual_premium_per_employee = document.getElementById('annual_premium_per_employee');
      const annual_premium_per_dependent = document.getElementById('annual_premium_per_dependent');
      const monthly_premium = document.getElementById('monthly_premium');
      const out_of_pocket_limit = document.getElementById('out_of_pocket_limit');
      const deductible_amount = document.getElementById('deductible_amount');
      const copay_percentage = document.getElementById('copay_percentage');
      const in_network_doctors = document.getElementById('in_network_doctors');
      const in_network_hospitals = document.getElementById('in_network_hospitals');
      const plan_launch_date = document.getElementById('plan_launch_date');
      const is_active = document.getElementById('is_active');

      if (!plan_name) {
        console.error('Form elements not found in DOM');
        return;
      }

      if (planFormTitle) planFormTitle.textContent = 'Edit Plan';
      if (planFormButton) planFormButton.textContent = 'Update Plan';
      
      if (plan_name) plan_name.value = plan.plan_name || '';
      if (plan_code) plan_code.value = plan.plan_code || '';
      if (provider_id) provider_id.value = plan.provider_id || '';
      if (plan_type) plan_type.value = plan.plan_type || 'Standard';
      if (description) description.value = plan.description || '';
      if (annual_premium_per_employee) annual_premium_per_employee.value = plan.annual_premium_per_employee || '';
      if (annual_premium_per_dependent) annual_premium_per_dependent.value = plan.annual_premium_per_dependent || '';
      if (monthly_premium) monthly_premium.value = plan.monthly_premium || '';
      if (out_of_pocket_limit) out_of_pocket_limit.value = plan.out_of_pocket_limit || '';
      if (deductible_amount) deductible_amount.value = plan.deductible_amount || '';
      if (copay_percentage) copay_percentage.value = plan.copay_percentage || '20';
      if (in_network_doctors) in_network_doctors.value = plan.in_network_doctors || '';
      if (in_network_hospitals) in_network_hospitals.value = plan.in_network_hospitals || '';
      if (plan_launch_date && plan.plan_launch_date) plan_launch_date.value = plan.plan_launch_date;
      if (is_active) is_active.checked = plan.is_active === 1 || plan.is_active === true;
    } catch (error) {
      console.error('Error populating plan form:', error);
    }
  }

  function showAddPlanModal() {
    try {
      currentPlanId = null;
      const planFormTitle = document.getElementById('planFormTitle');
      const planFormButton = document.getElementById('planFormButton');
      const planForm = document.getElementById('planForm');
      const planEditFormModal = document.getElementById('planEditFormModal');
      const planEditFormOverlay = document.getElementById('planEditFormOverlay');
      const provider_id_select = document.getElementById('provider_id');

      if (!planEditFormModal) {
        console.error('Plan edit form modal not found');
        return;
      }

      if (planFormTitle) planFormTitle.textContent = 'Add Plan';
      if (planFormButton) planFormButton.textContent = 'Add Plan';
      if (planForm) planForm.reset();
      
      // Load providers for dropdown
      if (provider_id_select) {
        fetch('modules/hmo/api.php?action=getProviders')
          .then(response => response.json())
          .then(data => {
            if (data.success && data.data.length > 0) {
              provider_id_select.innerHTML = '<option value="">Select Provider</option>' +
                data.data.map(p => `<option value="${p.id}">${p.provider_name}</option>`).join('');
            }
          })
          .catch(error => console.error('Error loading providers:', error));
      }
      
      planEditFormModal.classList.add('show');
      if (planEditFormOverlay) planEditFormOverlay.classList.add('show');
    } catch (error) {
      console.error('Error showing add plan modal:', error);
    }
  }

  function submitPlanForm() {
    try {
      const plan_name = document.getElementById('plan_name')?.value || '';
      const plan_code = document.getElementById('plan_code')?.value || '';
      const provider_id = document.getElementById('provider_id')?.value || '';
      const plan_type = document.getElementById('plan_type')?.value || 'Standard';
      const description = document.getElementById('description')?.value || '';
      const annual_premium_per_employee = document.getElementById('annual_premium_per_employee')?.value || '0';
      const annual_premium_per_dependent = document.getElementById('annual_premium_per_dependent')?.value || '0';
      const monthly_premium = document.getElementById('monthly_premium')?.value || '0';
      const out_of_pocket_limit = document.getElementById('out_of_pocket_limit')?.value || '0';
      const deductible_amount = document.getElementById('deductible_amount')?.value || '0';
      const copay_percentage = document.getElementById('copay_percentage')?.value || '20';
      const in_network_doctors = document.getElementById('in_network_doctors')?.value || '0';
      const in_network_hospitals = document.getElementById('in_network_hospitals')?.value || '0';
      const plan_launch_date = document.getElementById('plan_launch_date')?.value || '';
      const is_active = document.getElementById('is_active')?.checked ? 1 : 0;

      const payload = {
        plan_name, plan_code, provider_id, plan_type, description,
        annual_premium_per_employee, annual_premium_per_dependent, monthly_premium,
        out_of_pocket_limit, deductible_amount, copay_percentage,
        in_network_doctors, in_network_hospitals, plan_launch_date, is_active
      };

      const action = currentPlanId ? 'updatePlan' : 'createPlan';
      const url = currentPlanId ? `modules/hmo/api.php?action=${action}&id=${currentPlanId}` : `modules/hmo/api.php?action=${action}`;

      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          closePlanEditModal();
          loadActivePlans();
          loadPlansByProvider();
          loadPlansByCoverage();
        } else {
          alert('Error: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to save plan');
      });
    } catch (error) {
      console.error('Error in submitPlanForm:', error);
    }
  }

  function deactivatePlan(planId) {
    if (!confirm('Are you sure you want to delete this plan?')) return;

    try {
      fetch(`modules/hmo/api.php?action=deletePlan&id=${planId}`, { method: 'GET' })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            closePlanModal();
            loadActivePlans();
            loadPlansByProvider();
            loadPlansByCoverage();
          } else {
            alert('Error: ' + (data.error || 'Unknown error'));
          }
        })
        .catch(error => console.error('Error:', error));
    } catch (error) {
      console.error('Error in deactivatePlan:', error);
    }
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

  function renderByProviderTable(providers, plans) {
    const tbody = document.getElementById('by-provider-tbody');
    if (providers && providers.length > 0) {
      tbody.innerHTML = providers.map(provider => {
        const providerPlans = plans.filter(p => p.provider_id === provider.id);
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
              <span class="clickable-number">${totalPlans}</span>
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
                  <button class="btn-icon" onclick="event.stopPropagation(); viewProviderModal(${provider.id})" title="View Provider">
                    👁️
                  </button>
                  <span class="tooltip-text">View</span>
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
        <tr onclick="viewPlanModal(${plan.id})" style="cursor: pointer;">
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
                <button class="btn-icon" onclick="event.stopPropagation(); viewPlanModal(${plan.id})" title="View">
                  👁️
                </button>
                <span class="tooltip-text">View</span>
              </div>
              <div class="action-tooltip">
                <button class="btn-icon" onclick="event.stopPropagation(); editPlan(${plan.id})" title="Edit">
                  ✏️
                </button>
                <span class="tooltip-text">Edit</span>
              </div>
              <div class="action-tooltip">
                <button class="btn-icon" onclick="event.stopPropagation(); deactivatePlan(${plan.id})" title="Delete">
                  🗑️
                </button>
                <span class="tooltip-text">Delete</span>
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
          console.log('allPlans updated with', data.data.length, 'plans:', allPlans);
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
    
    // First fetch all providers
    fetch('modules/hmo/api.php?action=getProviders')
      .then(response => response.json())
      .then(providerData => {
        if (!providerData.success) throw new Error('Failed to fetch providers');
        
        allProviders = providerData.data || [];
        
        // Then fetch all plans
        return fetch('modules/hmo/api.php?action=getPlans');
      })
      .then(response => response.json())
      .then(planData => {
        if (planData.success && planData.data.length > 0) {
          allPlans = planData.data;
          console.log('allPlans updated with', planData.data.length, 'plans from loadPlansByProvider:', allPlans);
          renderByProviderTable(allProviders, allPlans);
          document.getElementById('count-provider').textContent = allProviders.length;
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


  // Initialize on page load (runs immediately for dynamic loading)
  function initializePlans() {
    console.log('initializePlans called');
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
</div>
