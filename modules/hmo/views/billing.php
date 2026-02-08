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

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-box {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1.5rem;
      border-radius: 8px;
      text-align: center;
    }

    .stat-box h3 {
      margin: 0;
      font-size: 14px;
      font-weight: 500;
      opacity: 0.9;
    }

    .stat-box .value {
      font-size: 28px;
      font-weight: 700;
      margin-top: 0.5rem;
    }

    /* Table Styles */
    .table-container {
      overflow-x: auto;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    th {
      background: #f9fafb;
      padding: 1rem;
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      color: #374151;
      border-bottom: 2px solid #e5e7eb;
    }

    td {
      padding: 1rem;
      border-bottom: 1px solid #e5e7eb;
      font-size: 14px;
      color: #4b5563;
    }

    tr:hover {
      background: #f9fafb;
    }

    /* Badge Styles */
    .badge {
      display: inline-block;
      padding: 0.35rem 0.75rem;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }

    .badge-success {
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

    .modal {
      background: white;
      border-radius: 8px;
      padding: 2rem;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      border-bottom: 1px solid #e5e7eb;
      padding-bottom: 1rem;
    }

    .modal-title {
      font-size: 18px;
      font-weight: 600;
      color: #1f2937;
      margin: 0;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #9ca3af;
    }

    .close-btn:hover {
      color: #1f2937;
    }

    /* Button Styles */
    .btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
    }

    .btn-primary {
      background: #667eea;
      color: white;
    }

    .btn-primary:hover {
      background: #5568d3;
    }

    .btn-secondary {
      background: #e5e7eb;
      color: #1f2937;
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
    <div class="stat-box">
      <h3>Total Reconciliations</h3>
      <div class="value" id="stat-total">0</div>
    </div>
    <div class="stat-box">
      <h3>Pending Review</h3>
      <div class="value" id="stat-pending">0</div>
    </div>
    <div class="stat-box">
      <h3>Total Variance</h3>
      <div class="value" id="stat-variance">$0</div>
    </div>
    <div class="stat-box">
      <h3>Approved</h3>
      <div class="value" id="stat-approved">0</div>
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
  <div class="modal-overlay" id="modal-overlay" onclick="closeSideModal()"></div>
  <div class="modal" id="reconciliation-modal" style="display: none;">
    <div class="modal-header">
      <h2 class="modal-title" id="modal-title">Reconciliation Details</h2>
      <button class="close-btn" onclick="closeReconciliationModal()">×</button>
    </div>
    <div id="modal-body" class="modal-body">
      <!-- Content injected here -->
    </div>
  </div>
</div>

<script>
  // Global State
  if (typeof allReconciliations === 'undefined') { var allReconciliations = []; }
  allReconciliations = [];
  if (typeof currentTab === 'undefined') { var currentTab = 'all'; }
  currentTab = 'all';

  // ============================================
  // Initialize on page load (runs immediately for dynamic loading)
  loadReconciliations();
  updateCounts();

  document.getElementById('modal-overlay').addEventListener('click', closeSideModal);

  window.onclick = function(event) {
    if (event.target.id === 'modal-overlay') {
      closeSideModal();
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
      <table>
        <thead>
          <tr>
            <th>Billing Month</th>
            <th>Claims Total</th>
            <th>Payment Total</th>
            <th>Variance</th>
            <th>Status</th>
            <th>Discrepancies</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
    `;

    allReconciliations.forEach(reconciliation => {
      const variance = parseFloat(reconciliation.variance_amount || 0);
      const varianceClass = variance > 0 ? 'badge-warning' : variance < 0 ? 'badge-danger' : 'badge-success';
      const statusBadge = reconciliation.status === 'approved' ? 'badge-success' : 
                         reconciliation.status === 'pending' ? 'badge-warning' : 'badge-info';

      html += `
        <tr>
          <td>${reconciliation.billing_month || 'N/A'}</td>
          <td>$${parseFloat(reconciliation.claims_total || 0).toFixed(2)}</td>
          <td>$${parseFloat(reconciliation.payment_total || 0).toFixed(2)}</td>
          <td class="badge ${varianceClass}">$${variance.toFixed(2)}</td>
          <td><span class="badge ${statusBadge}">${reconciliation.status || 'pending'}</span></td>
          <td>${reconciliation.discrepancy_count || 0}</td>
          <td>
            <button class="btn btn-primary" onclick="viewReconciliationDetails(${reconciliation.id})">View</button>
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
    modal.style.display = 'block';
  }

  function viewReconciliationDetails(reconciliationId) {
    const reconciliation = allReconciliations.find(r => r.id === reconciliationId);
    if (!reconciliation) return;

    const modal = document.getElementById('reconciliation-modal');
    const overlay = document.getElementById('modal-overlay');
    const title = document.getElementById('modal-title');
    const body = document.getElementById('modal-body');

    title.textContent = `Reconciliation - ${reconciliation.billing_month}`;
    body.innerHTML = `
      <div class="form-group">
        <label>Billing Month</label>
        <input type="text" value="${reconciliation.billing_month || 'N/A'}" readonly>
      </div>
      <div class="form-group">
        <label>Claims Total</label>
        <input type="text" value="$${parseFloat(reconciliation.claims_total || 0).toFixed(2)}" readonly>
      </div>
      <div class="form-group">
        <label>Payment Total</label>
        <input type="text" value="$${parseFloat(reconciliation.payment_total || 0).toFixed(2)}" readonly>
      </div>
      <div class="form-group">
        <label>Variance</label>
        <input type="text" value="$${parseFloat(reconciliation.variance_amount || 0).toFixed(2)}" readonly>
      </div>
      <div class="form-group">
        <label>Status</label>
        <input type="text" value="${reconciliation.status || 'pending'}" readonly>
      </div>
      <div class="form-group">
        <label>Discrepancies Found</label>
        <input type="text" value="${reconciliation.discrepancy_count || 0}" readonly>
      </div>
      <div style="display: flex; gap: 1rem;">
        <button class="btn btn-primary" onclick="approveBillingReconciliation(${reconciliationId})">Approve</button>
        <button class="btn btn-secondary" onclick="closeReconciliationModal()">Close</button>
      </div>
    `;

    overlay.classList.add('active');
    modal.style.display = 'block';
  }

  function closeReconciliationModal() {
    const modal = document.getElementById('reconciliation-modal');
    const overlay = document.getElementById('modal-overlay');
    modal.style.display = 'none';
    overlay.classList.remove('active');
  }

  function closeSideModal() {
    closeReconciliationModal();
  }

  // ============================================
  // Action Functions
  // ============================================

  function approveBillingReconciliation(reconciliationId) {
    if (!confirm('Approve this reconciliation?')) return;

    fetch('modules/hmo/api.php?action=approveBillingReconciliation', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: reconciliationId })
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
      .catch(error => console.error('Error:', error));
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
    const pending = allReconciliations.filter(r => r.status === 'pending').length;
    const approved = allReconciliations.filter(r => r.status === 'approved').length;
    const variances = allReconciliations.reduce((sum, r) => sum + parseFloat(r.variance_amount || 0), 0);

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-pending').textContent = pending;
    document.getElementById('stat-approved').textContent = approved;
    document.getElementById('stat-variance').textContent = '$' + variances.toFixed(2);
  }
</script>
