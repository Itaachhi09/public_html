<?php
/**
 * HMO Documents & IDs View
 * 
 * Centralized HMO documents and IDs management
 * Features:
 * - HMO cards with expiry tracking
 * - Policy documents and contracts
 * - Coverage verification
 * - Renewal management
 * - Document downloads
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

    .stat-card.accent-documents {
      border-left-color: #3b82f6;
    }

    .stat-card.accent-active {
      border-left-color: #10b981;
    }

    .stat-card.accent-expiring {
      border-left-color: #f59e0b;
    }

    .stat-card.accent-expired {
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

    /* Buttons */
    .btn {
      padding: 10px 16px;
      border: 1px solid #d1d5db;
      background: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn:hover {
      background: #f9fafb;
      border-color: #9ca3af;
    }

    .btn-primary {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
    }

    .btn-primary:hover {
      background: #2563eb;
      border-color: #2563eb;
    }

    .btn-small {
      padding: 6px 12px;
      font-size: 13px;
    }

    .btn-secondary {
      background: #10b981;
      color: white;
      border-color: #10b981;
    }

    .btn-secondary:hover {
      background: #059669;
      border-color: #059669;
    }

    /* Filter Bar */
    .filter-bar {
      background: #f9fafb;
      border-radius: 6px;
      padding: 12px 16px;
      margin-bottom: 20px;
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .search-box {
      flex: 1;
      min-width: 250px;
      position: relative;
    }

    .search-box-icon {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 16px;
    }

    .search-box input {
      width: 100%;
      padding: 10px 12px 10px 36px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      background: white;
    }

    .search-box input::placeholder {
      color: #9ca3af;
    }

    .search-box input:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .filter-select {
      padding: 10px 12px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      background: white;
      cursor: pointer;
    }

    .filter-select:focus {
      outline: none;
      border-color: #3b82f6;
    }

    /* Tabs */
    .tabs {
      display: inline-flex;
      gap: 4px;
      margin-left: auto;
    }

    .tab-btn {
      padding: 8px 16px;
      border: 1px solid #d1d5db;
      background: white;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      color: #6b7280;
      border-radius: 6px;
      white-space: nowrap;
      transition: all 0.2s;
    }

    .tab-btn:hover {
      background: #f3f4f6;
      border-color: #9ca3af;
    }

    .tab-btn.active {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
    }

    .tab-count {
      display: inline-block;
      margin-left: 6px;
      font-weight: 600;
    }

    /* Table */
    .documents-table {
      width: 100%;
      border-collapse: collapse;
    }

    .documents-table thead {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
    }

    .documents-table thead th {
      padding: 12px 16px;
      text-align: left;
      font-size: 12px;
      font-weight: 600;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .documents-table tbody tr {
      border-bottom: 1px solid #f3f4f6;
      transition: background 0.2s;
    }

    .documents-table tbody tr:hover {
      background: #f9fafb;
    }

    .documents-table tbody td {
      padding: 12px 16px;
      font-size: 14px;
      color: #1f2937;
    }

    /* Tab Content */
    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    /* Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      z-index: 100;
      opacity: 0;
      transition: opacity 0.2s;
    }

    .modal-overlay.active {
      display: block;
      opacity: 1;
    }

    .side-modal {
      position: fixed;
      right: -420px;
      top: 0;
      width: 420px;
      height: 100vh;
      background: white;
      box-shadow: -2px 0 8px rgba(0, 0, 0, 0.15);
      transition: right 0.3s ease;
      display: flex;
      flex-direction: column;
      z-index: 101;
      overflow: hidden;
    }

    .side-modal.active {
      right: 0;
    }

    .modal-header {
      padding: 20px;
      border-bottom: 1px solid #e5e7eb;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-shrink: 0;
    }

    .modal-title {
      font-size: 16px;
      font-weight: 600;
      color: #1f2937;
      margin: 0;
    }

    .modal-body {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
    }

    .modal-section {
      margin-bottom: 24px;
    }

    .modal-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .modal-label {
      font-size: 13px;
      color: #6b7280;
      font-weight: 500;
    }

    .modal-value {
      font-size: 14px;
      font-weight: 600;
      color: #1f2937;
    }

    .close-btn {
      width: 32px;
      height: 32px;
      border: none;
      background: #f3f4f6;
      border-radius: 6px;
      cursor: pointer;
      font-size: 18px;
      color: #6b7280;
      transition: all 0.2s;
    }

    .close-btn:hover {
      background: #e5e7eb;
      color: #1f2937;
    }

    .modal-actions {
      padding: 20px;
      border-top: 1px solid #e5e7eb;
      display: flex;
      gap: 12px;
      flex-shrink: 0;
    }
  </style>

  <!-- Page Header -->
  <div class="card">
    <div class="card-header">
      <h1 class="card-title">HMO Documents & IDs</h1>
      <button class="btn btn-primary" onclick="uploadDocument()">Upload Document</button>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card accent-documents">
      <div class="stat-icon">📄</div>
      <div class="stat-value" id="stat-total">0</div>
      <div class="stat-label">Total Documents</div>
    </div>
    <div class="stat-card accent-active">
      <div class="stat-icon">✓</div>
      <div class="stat-value" id="stat-active">0</div>
      <div class="stat-label">Active</div>
    </div>
    <div class="stat-card accent-expiring">
      <div class="stat-icon">⏰</div>
      <div class="stat-value" id="stat-expiring">0</div>
      <div class="stat-label">Expiring Soon</div>
    </div>
    <div class="stat-card accent-expired">
      <div class="stat-icon">✕</div>
      <div class="stat-value" id="stat-expired">0</div>
      <div class="stat-label">Expired</div>
    </div>
  </div>

  <!-- Documents Table -->
  <div class="card">
    <div class="card-content">
      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="search-box">
          <span class="search-box-icon">🔍</span>
          <input type="text" id="search-document" placeholder="Search documents..." onkeyup="filterDocuments()">
        </div>
        <select class="filter-select" id="filter-type" onchange="filterDocuments()">
          <option value="">All Types</option>
          <option value="card">HMO Card</option>
          <option value="policy">Policy Document</option>
          <option value="contract">Contract</option>
          <option value="verification">Verification</option>
          <option value="other">Other</option>
        </select>
        <div class="tabs">
          <button class="tab-btn active" onclick="switchTab(event, 'all-documents')">All <span class="tab-count" id="count-all">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'active-documents')">Active <span class="tab-count" id="count-active">0</span></button>
          <button class="tab-btn" onclick="switchTab(event, 'expiring-documents')">Expiring <span class="tab-count" id="count-expiring">0</span></button>
        </div>
      </div>

      <!-- All Documents Tab -->
      <div id="all-documents-tab" class="tab-content" style="display: block;">
        <table class="documents-table">
          <thead>
            <tr>
              <th>Document Name</th>
              <th>Type</th>
              <th>Holder</th>
              <th>Expiry Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="all-documents-tbody">
            <tr>
              <td colspan="6" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Active Documents Tab -->
      <div id="active-documents-tab" class="tab-content" style="display: none;">
        <table class="documents-table">
          <thead>
            <tr>
              <th>Document Name</th>
              <th>Type</th>
              <th>Holder</th>
              <th>Expiry Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="active-documents-tbody">
            <tr>
              <td colspan="6" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Expiring Documents Tab -->
      <div id="expiring-documents-tab" class="tab-content" style="display: none;">
        <table class="documents-table">
          <thead>
            <tr>
              <th>Document Name</th>
              <th>Type</th>
              <th>Holder</th>
              <th>Expiry Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="expiring-documents-tbody">
            <tr>
              <td colspan="6" style="text-align: center; padding: 2rem; color: #9ca3af;">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal for document details -->
  <div class="modal-overlay" id="modal-overlay"></div>
  <div class="side-modal" id="document-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="modal-title">Document Details</h2>
        <button class="close-btn" onclick="closeDocumentModal()">✕</button>
      </div>
      <div id="modal-body" class="modal-body">
        <!-- Content injected here -->
      </div>
    </div>
  </div>
</div>

<script>
  // Global State
  var allDocuments = [];
  var currentDocumentId = null;

  // ============================================
  // Initialize on page load
  loadDocuments();
  updateCounts();

  // Modal event listeners
  window.modalOverlay = document.getElementById('modal-overlay');
  if (window.modalOverlay) {
    window.modalOverlay.addEventListener('click', closeDocumentModal);
  }

  // ============================================
  // Data Loading Functions
  // ============================================

  function loadDocuments() {
    const tbody = document.getElementById('all-documents-tbody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;"><div class="loading"><div class="spinner"></div><p>Loading documents...</p></div></td></tr>';

    fetch('modules/hmo/api.php?action=getHMODocuments')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          allDocuments = data.documents || [];
          renderDocumentsTables();
          updateCounts();
        } else {
          tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: #ef4444;">Failed to load documents: ' + (data.error || 'Unknown error') + '</td></tr>';
        }
      })
      .catch(error => {
        console.error('Error loading documents:', error);
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: #ef4444;">Error loading documents</td></tr>';
      });
  }

  // ============================================
  // Render Functions
  // ============================================

  function renderDocumentsTables() {
    renderTable(allDocuments, 'all-documents-tbody');
    renderTable(allDocuments.filter(d => d.status === 'active'), 'active-documents-tbody');
    renderTable(allDocuments.filter(d => d.status === 'expiring'), 'expiring-documents-tbody');
  }

  function renderTable(documents, tableId) {
    const tbody = document.getElementById(tableId);
    if (!tbody) return;

    if (!documents || documents.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem; color: #9ca3af;">No documents found</td></tr>';
      return;
    }

    let html = '';
    documents.forEach(doc => {
      const statusBadge = doc.status === 'active' ? 'badge-active' : 
                         doc.status === 'expiring' ? 'badge-expiring' : 'badge-expired';

      html += `
        <tr>
          <td><span class="document-name">${doc.name || 'N/A'}</span></td>
          <td><span class="document-type">${(doc.document_type || 'other').toUpperCase()}</span></td>
          <td>${doc.holder_name || 'N/A'}</td>
          <td>${doc.expiry_date ? formatDate(doc.expiry_date) : 'N/A'}</td>
          <td><span class="badge ${statusBadge}">${(doc.status || 'unknown').toUpperCase()}</span></td>
          <td style="text-align: center;">
            <button class="btn btn-primary btn-small" onclick="viewDocumentDetails(${doc.id})">👁️ View</button>
            <button class="btn btn-secondary btn-small" onclick="downloadDocument(${doc.id})">⬇️ Download</button>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
  }

  // ============================================
  // Modal Functions
  // ============================================

  function viewDocumentDetails(documentId) {
    const document = allDocuments.find(d => d.id === documentId);
    if (!document) {
      console.error('Document not found:', documentId);
      return;
    }

    const modal = document.getElementById('document-modal');
    const overlay = document.getElementById('modal-overlay');
    const title = document.getElementById('modal-title');
    const body = document.getElementById('modal-body');

    if (!modal || !overlay || !title || !body) {
      console.error('Modal elements not found');
      return;
    }

    title.textContent = document.name || 'Document Details';
    body.innerHTML = `
      <div class="form-group">
        <label>Document Name</label>
        <input type="text" value="${document.name || 'N/A'}" readonly>
      </div>
      <div class="form-group">
        <label>Document Type</label>
        <input type="text" value="${(document.document_type || 'N/A').toUpperCase()}" readonly>
      </div>
      <div class="form-group">
        <label>Holder Name</label>
        <input type="text" value="${document.holder_name || 'N/A'}" readonly>
      </div>
      <div class="form-group">
        <label>Expiry Date</label>
        <input type="text" value="${document.expiry_date ? formatDate(document.expiry_date) : 'N/A'}" readonly>
      </div>
      <div class="form-group">
        <label>Status</label>
        <input type="text" value="${(document.status || 'N/A').toUpperCase()}" readonly>
      </div>
      <div class="form-group">
        <label>Upload Date</label>
        <input type="text" value="${document.created_at ? formatDate(document.created_at) : 'N/A'}" readonly>
      </div>
      <div style="display: flex; gap: 1rem; margin-top: 2rem;">
        <button class="btn btn-primary" style="flex: 1;" onclick="downloadDocument(${document.id})">⬇️ Download</button>
        <button class="btn btn-secondary" style="flex: 1;" onclick="closeDocumentModal()">Close</button>
      </div>
    `;

    overlay.classList.add('active');
    modal.classList.add('active');
  }

  function closeDocumentModal() {
    const modal = document.getElementById('document-modal');
    const overlay = document.getElementById('modal-overlay');

    if (!modal || !overlay) return;

    modal.classList.remove('active');
    overlay.classList.remove('active');
  }

  // ============================================
  // Action Functions
  // ============================================

  function downloadDocument(documentId) {
    if (!confirm('Download this document?')) return;

    fetch(`modules/hmo/api.php?action=downloadHMODocument&id=${documentId}`, {
      method: 'GET'
    })
      .then(response => response.blob())
      .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `document_${documentId}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error downloading document');
      });
  }

  function uploadDocument() {
    alert('Upload functionality coming soon');
  }

  // ============================================
  // Tab Switching
  // ============================================

  function switchTab(event, tabName) {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => btn.classList.remove('active'));
    tabContents.forEach(content => content.style.display = 'none');

    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById(tabName + '-tab').style.display = 'block';
  }

  // ============================================
  // Filter & Search
  // ============================================

  function filterDocuments() {
    const searchTerm = document.getElementById('search-document')?.value.toLowerCase() || '';
    const typeFilter = document.getElementById('filter-type')?.value || '';

    let filtered = allDocuments.filter(doc => {
      const matchesSearch = !searchTerm || 
        (doc.name && doc.name.toLowerCase().includes(searchTerm)) ||
        (doc.holder_name && doc.holder_name.toLowerCase().includes(searchTerm));
      
      const matchesType = !typeFilter || doc.document_type === typeFilter;

      return matchesSearch && matchesType;
    });

    renderTable(filtered, 'all-documents-tbody');
  }

  // ============================================
  // Helper Functions
  // ============================================

  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  }

  function updateCounts() {
    const total = allDocuments.length;
    const active = allDocuments.filter(d => d.status === 'active').length;
    const expiring = allDocuments.filter(d => d.status === 'expiring').length;
    const expired = allDocuments.filter(d => d.status === 'expired').length;

    document.getElementById('stat-total').textContent = total;
    document.getElementById('stat-active').textContent = active;
    document.getElementById('stat-expiring').textContent = expiring;
    document.getElementById('stat-expired').textContent = expired;

    document.getElementById('count-all').textContent = total;
    document.getElementById('count-active').textContent = active;
    document.getElementById('count-expiring').textContent = expiring;
  }
</script>

