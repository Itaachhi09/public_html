<?php
/**
 * Billing & Reconciliation View
 * 
 * Manage billing reconciliation, discrepancies, and adjustments
 * Features:
 * - Billing reconciliation tracking
 * - Claims vs payments reconciliation
 * - Discrepancy identification and resolution
 * - Billing adjustments and retro computations
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
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

    .stat-card.accent-reconciliations {
      border-left-color: #3b82f6;
    }

    .stat-card.accent-approved {
      border-left-color: #10b981;
    }

    .stat-card.accent-pending {
      border-left-color: #f59e0b;
    }

    .stat-card.accent-variance {
      border-left-color: #ef4444;
    }

    .stat-card:hover {
      border-color: #d1d5db;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .stat-icon {
      font-size: 24px;
      margin-bottom: 0.5rem;
      opacity: 0.7;
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: #111827;
      margin: 0.25rem 0 0.5rem 0;
    }

    .stat-label {
      font-size: 12px;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    /* Table Styles */
    .table-container {
      overflow-x: auto;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
    }

    .billing-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }

    .billing-table thead {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
    }

    .billing-table th {
      padding: 1rem;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .billing-table tbody tr {
      border-bottom: 1px solid #e5e7eb;
      transition: background 0.15s ease;
      cursor: pointer;
    }

    .billing-table tbody tr:hover {
      background: #f0f9ff;
    }

    .billing-table td {
      padding: 1.25rem 1rem;
      color: #374151;
      font-size: 14px;
    }

    .billing-table td:last-child {
      text-align: center;
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

    .badge-warning {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-danger {
      background: #fee2e2;
      color: #991b1b;
    }

    .badge-info {
      background: #dbeafe;
      color: #1e40af;
    }

    .badge-success {
      background: #dcfce7;
      color: #166534;
    }

    /* Loading indicator */
    .loading {
      text-align: center;
      padding: 3rem 1rem;
      color: #9ca3af;
    }

    .spinner {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid #e5e7eb;
      border-top-color: #667eea;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
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
    }

    .modal-overlay.active {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Side Modal Styles */
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

    .modal-title {
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

    /* Button Styles */
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
      background: #3b82f6;
      color: white;
    }

    .btn-primary:hover {
      background: #2563eb;
    }

    .btn-secondary {
      background: #e5e7eb;
      color: #374151;
    }

    .btn-secondary:hover {
      background: #d1d5db;
    }

    .btn-danger {
      background: #ef4444;
      color: white;
    }

    .btn-danger:hover {
      background: #dc2626;
    }

    .btn-small {
      padding: 0.35rem 0.75rem;
      font-size: 12px;
    }

    .btn-group {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
      align-items: center;
    }

    /* Form Styles */
    .form-group {
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #374151;
      font-size: 14px;
    }

    input[type="text"],
    input[type="date"],
    input[type="month"],
    input[type="number"],
    input[type="email"],
    select,
    textarea {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #d1d5db;
      border-radius: 4px;
      font-size: 14px;
      font-family: inherit;
    }

    textarea {
      min-height: 80px;
      resize: vertical;
    }

    input:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Error & Success Messages */
    .alert {
      padding: 1rem;
      border-radius: 4px;
      margin-bottom: 1.5rem;
      font-size: 14px;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #86efac;
    }

    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fca5a5;
    }

    .alert-warning {
      background: #fef3c7;
      color: #92400e;
      border: 1px solid #fde68a;
    }
  </style>

  <!-- Page Header -->
  <div class="card">
    <div class="card-header">
      <h1 class="card-title">Billing & Reconciliation</h1>
      <button class="btn btn-primary" onclick="openReconciliationModal()">New Reconciliation</button>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card accent-reconciliations">
      <div class="stat-icon">📋</div>
      <div class="stat-value" id="stat-total">0</div>
      <div class="stat-label">Total Reconciliations</div>
    </div>
    <div class="stat-card accent-pending">
      <div class="stat-icon">⏳</div>
      <div class="stat-value" id="stat-pending">0</div>
      <div class="stat-label">Pending Review</div>
    </div>
    <div class="stat-card accent-variance">
      <div class="stat-icon">💰</div>
      <div class="stat-value" id="stat-variance">$0</div>
      <div class="stat-label">Total Variance</div>
    </div>
    <div class="stat-card accent-approved">
      <div class="stat-icon">✓</div>
      <div class="stat-value" id="stat-approved">0</div>
      <div class="stat-label">Approved</div>
    </div>
  </div>

  <!-- Reconciliations Table -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Billing Reconciliations</h2>
    </div>
    <div class="card-content">
      <div id="reconciliations-table" class="table-container">
        <div class="loading">
          <div class="spinner"></div>
          <p>Loading reconciliations...</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for reconciliation details -->
  <div class="modal-overlay" id="modal-overlay"></div>
  <div class="side-modal" id="reconciliation-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="modal-title">Reconciliation Details</h2>
        <button class="close-btn" onclick="closeReconciliationModal()">✕</button>
      </div>
      <div id="modal-body" class="modal-body">
        <!-- Content injected here -->
      </div>
    </div>
  </div>
</div>

<script>
  // Global State
  var allReconciliations = [];
  var currentReconciliationId = null;

  // ============================================
  // Initialize on page load (runs immediately for dynamic loading)
  loadReconciliations();
  updateCounts();

  // Modal event listeners
  window.modalOverlay = document.getElementById('modal-overlay');
  if (window.modalOverlay) {
    window.modalOverlay.addEventListener('click', closeReconciliationModal);
  }

  window.onclick = function(event) {
    if (event.target && event.target.id === 'modal-overlay') {
      closeReconciliationModal();
    }
  };

  // ============================================
  // Data Loading Functions
  // ============================================

  function loadReconciliations() {
    const container = document.getElementById('reconciliations-table');
    if (!container) return;

    container.innerHTML = '<div class="loading"><div class="spinner"></div><p>Loading reconciliations...</p></div>';

    fetch('modules/hmo/api.php?action=getBillingReconciliations')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allReconciliations = data.reconciliations || [];
          renderReconciliationsTable();
          updateCounts();
        } else {
          container.innerHTML = '<div class="alert alert-error">Failed to load reconciliations: ' + (data.error || 'Unknown error') + '</div>';
        }
      })
      .catch(error => {
        console.error('Error loading reconciliations:', error);
        container.innerHTML = '<div class="alert alert-error">Error loading reconciliations: ' + error.message + '</div>';
      });
  }

  // ============================================
  // Render Functions
  // ============================================

  function renderReconciliationsTable() {
    const container = document.getElementById('reconciliations-table');
    if (!container) return;

    if (!allReconciliations || allReconciliations.length === 0) {
      container.innerHTML = '<div class="alert alert-warning">No reconciliations found</div>';
      return;
    }

    let html = `
      <table class="billing-table">
        <thead>
          <tr>
            <th>Billing Month</th>
            <th>Claims Total</th>
            <th>Payment Total</th>
            <th>Variance</th>
            <th>Status</th>
            <th>Discrepancies</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
    `;

    allReconciliations.forEach(reconciliation => {
      const variance = parseFloat(reconciliation.variance_amount || 0);
      const varianceClass = variance > 0 ? 'badge-warning' : variance < 0 ? 'badge-danger' : 'badge-success';
      const statusBadge = reconciliation.reconciliation_status === 'approved' ? 'badge-approved' : 
                         reconciliation.reconciliation_status === 'pending' ? 'badge-pending' : 'badge-info';

      html += `
        <tr>
          <td><strong>${reconciliation.billing_month || 'N/A'}</strong></td>
          <td>PHP ${parseFloat(reconciliation.total_claims_submitted || 0).toFixed(2)}</td>
          <td>PHP ${parseFloat(reconciliation.total_payments_made || 0).toFixed(2)}</td>
          <td><span class="badge ${varianceClass}">PHP ${variance.toFixed(2)}</span></td>
          <td><span class="badge ${statusBadge}">${(reconciliation.reconciliation_status || 'pending').toUpperCase()}</span></td>
          <td><span class="badge badge-warning">${reconciliation.discrepancy_count || 0}</span></td>
          <td style="text-align: center;">
            <button class="btn btn-primary btn-small" onclick="viewReconciliationDetails(${reconciliation.id})">👁️ View</button>
          </td>
        </tr>
      `;
    });

    html += `
        </tbody>
      </table>
    `;

    container.innerHTML = html;
  }

  // ============================================
  // Modal Functions
  // ============================================

  function openReconciliationModal() {
    const modal = document.getElementById('reconciliation-modal');
    const overlay = document.getElementById('modal-overlay');
    const title = document.getElementById('modal-title');
    const body = document.getElementById('modal-body');

    if (!modal || !overlay || !title || !body) {
      console.error('Modal elements not found');
      return;
    }

    title.textContent = 'New Reconciliation';
    body.innerHTML = `
      <form onsubmit="createReconciliation(event)">
        <div class="form-group">
          <label for="billing-month">Billing Month</label>
          <input type="month" id="billing-month" required>
        </div>
        <div class="form-group">
          <label for="claims-total">Claims Total ($)</label>
          <input type="number" id="claims-total" step="0.01" required>
        </div>
        <div class="form-group">
          <label for="payment-total">Payment Total ($)</label>
          <input type="number" id="payment-total" step="0.01" required>
        </div>
        <div class="form-group">
          <label for="notes">Notes</label>
          <textarea id="notes"></textarea>
        </div>
        <div style="display: flex; gap: 1rem;">
          <button type="submit" class="btn btn-primary">Create</button>
          <button type="button" class="btn btn-secondary" onclick="closeReconciliationModal()">Cancel</button>
        </div>
      </form>
    `;

    overlay.classList.add('active');
    modal.classList.add('active');
  }

  function viewReconciliationDetails(reconciliationId) {
    const reconciliation = allReconciliations.find(r => r.id === reconciliationId);
    if (!reconciliation) {
      console.error('Reconciliation not found:', reconciliationId);
      return;
    }

    const modal = document.getElementById('reconciliation-modal');
    const overlay = document.getElementById('modal-overlay');
    const title = document.getElementById('modal-title');
    const body = document.getElementById('modal-body');

    if (!modal || !overlay || !title || !body) {
      console.error('Modal elements not found');
      return;
    }

    title.textContent = `Reconciliation - ${reconciliation.billing_month}`;
    body.innerHTML = `
      <div class="form-group">
        <label>Billing Month</label>
        <input type="text" value="${reconciliation.billing_month || 'N/A'}" readonly>
      </div>
      <div class="form-group">
        <label>Claims Total</label>
        <input type="text" value="PHP ${parseFloat(reconciliation.total_claims_submitted || 0).toFixed(2)}" readonly>
      </div>
      <div class="form-group">
        <label>Payment Total</label>
        <input type="text" value="PHP ${parseFloat(reconciliation.total_payments_made || 0).toFixed(2)}" readonly>
      </div>
      <div class="form-group">
        <label>Variance</label>
        <input type="text" value="PHP ${parseFloat(reconciliation.variance_amount || 0).toFixed(2)}" readonly>
      </div>
      <div class="form-group">
        <label>Status</label>
        <input type="text" value="${(reconciliation.reconciliation_status || 'pending').toUpperCase()}" readonly>
      </div>
      <div class="form-group">
        <label>Discrepancies Found</label>
        <input type="text" value="${reconciliation.discrepancy_count || 0}" readonly>
      </div>
      ${reconciliation.reconciliation_status && reconciliation.reconciliation_status.toLowerCase() === 'pending' ? `
      <div style="display: flex; gap: 1rem; margin-top: 2rem;">
        <button class="btn btn-primary" style="flex: 1;" onclick="approveBillingReconciliation(${reconciliation.id}, event)">Approve</button>
        <button class="btn btn-secondary" style="flex: 1;" onclick="closeReconciliationModal()">Close</button>
      </div>
      ` : `
      <div style="display: flex; gap: 1rem; margin-top: 2rem;">
        <button class="btn btn-secondary" style="flex: 1;" onclick="closeReconciliationModal()">Close</button>
      </div>
      `}
    `;

    overlay.classList.add('active');
    modal.classList.add('active');
  }

  function closeReconciliationModal() {
    const modal = document.getElementById('reconciliation-modal');
    const overlay = document.getElementById('modal-overlay');

    if (!modal || !overlay) return;

    modal.classList.remove('active');
    overlay.classList.remove('active');
  }

  function closeSideModal() {
    closeReconciliationModal();
  }

  // ============================================
  // Action Functions
  // ============================================

  function approveBillingReconciliation(reconciliationId, event) {
    if (event) event.stopPropagation();
    
    if (!confirm('Are you sure you want to approve this reconciliation?')) return;

    fetch(`modules/hmo/api.php?action=approveBillingReconciliation&id=${reconciliationId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({})
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Reconciliation approved successfully');
          loadReconciliations();
          closeReconciliationModal();
        } else {
          alert('Error: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error approving reconciliation');
      });
  }

  function createReconciliation(event) {
    event.preventDefault();
    alert('Create function not yet implemented');
  }

  // ============================================
  // Update Functions
  // ============================================

  function updateCounts() {
    const total = allReconciliations.length;
    const pending = allReconciliations.filter(r => r.reconciliation_status === 'pending').length;
    const approved = allReconciliations.filter(r => r.reconciliation_status === 'approved').length;
    const variances = allReconciliations.reduce((sum, r) => sum + parseFloat(r.variance_amount || 0), 0);

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-pending').textContent = pending;
    document.getElementById('stat-approved').textContent = approved;
    document.getElementById('stat-variance').textContent = 'PHP ' + variances.toFixed(2);
  }
</script>
