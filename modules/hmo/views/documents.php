<?php
/**
 * HMO Documents & IDs View
 * 
 * Centralized HMO documents and IDs management
 * 
 * Features:
 * - HMO cards with expiry tracking
 * - Policy documents and contracts
 * - Coverage verification
 * - Renewal management
 * - Document downloads
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMO Documents & IDs</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #6b7280;
            font-size: 14px;
        }

        /* Stat Cards */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-card.primary {
            border-left-color: #3b82f6;
        }

        .stat-card.success {
            border-left-color: #10b981;
        }

        .stat-card.warning {
            border-left-color: #f59e0b;
        }

        .stat-card.danger {
            border-left-color: #ef4444;
        }

        .stat-label {
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .stat-subtitle {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Action Bar */
        .action-bar {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

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

        .btn.primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn.primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        /* Filter Bar */
        .filter-bar {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: #f9fafb;
        }

        .search-box input::placeholder {
            color: #9ca3af;
        }

        .search-box svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: #9ca3af;
        }

        .filter-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }

        .tab-group {
            display: inline-flex;
            background: #f3f4f6;
            padding: 4px;
            border-radius: 6px;
            gap: 4px;
            margin-left: auto;
        }

        .tab-btn {
            padding: 8px 16px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            border-radius: 4px;
            white-space: nowrap;
        }

        .tab-btn.active {
            background: white;
            color: #1f2937;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            background: #f3f4f6;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            margin-left: 6px;
        }

        .tab-btn.active .tab-badge {
            background: #3b82f6;
            color: white;
        }

        /* Content Area */
        .content-area {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Table */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        thead th {
            padding: 14px 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.2s;
            cursor: pointer;
        }

        tbody tr:hover {
            background: #f0f9ff;
        }

        tbody td {
            padding: 14px 16px;
            font-size: 14px;
        }

        .employee-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
        }

        .doc-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            width: fit-content;
        }

        .doc-type-badge.hmo_card {
            background: #fce7f3;
            color: #9f1239;
        }

        .doc-type-badge.policy_document {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .doc-type-badge.provider_contract {
            background: #dcfce7;
            color: #166534;
        }

        .doc-type-badge.plan_endorsement {
            background: #fef3c7;
            color: #92400e;
        }

        .doc-type-badge.renewal_notice {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            width: fit-content;
        }

        .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.expired {
            background: #fee2e2;
            color: #991b1b;
        }

        .expiry-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .expiry-badge.critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .expiry-badge.warning {
            background: #fef3c7;
            color: #92400e;
        }

        .expiry-badge.good {
            background: #d1fae5;
            color: #065f46;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .icon-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        /* Empty State */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #9ca3af;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .empty-state-text {
            font-size: 14px;
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
        }

        .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: #f3f4f6;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #e5e7eb;
        }

        .modal-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .modal-section {
            margin-bottom: 24px;
        }

        .modal-section-title {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .modal-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .modal-label {
            color: #6b7280;
            font-size: 13px;
        }

        .modal-value {
            font-weight: 600;
            color: #1f2937;
        }

        .modal-actions {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="page-header">
            <h1>HMO Documents & IDs</h1>
            <p>Centralized HMO documents, insurance cards, and coverage verification</p>
        </div>

        <!-- Stat Cards -->
        <div class="stat-cards">
            <div class="stat-card primary">
                <div class="stat-label">Active Documents</div>
                <div class="stat-value" id="activeCount">0</div>
                <div class="stat-subtitle">Current coverage</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">Expiring Soon</div>
                <div class="stat-value" id="expiringCount">0</div>
                <div class="stat-subtitle">Within 30 days</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-label">Expired</div>
                <div class="stat-value" id="expiredCount">0</div>
                <div class="stat-subtitle">Renewal needed</div>
            </div>
            <div class="stat-card success">
                <div class="stat-label">HMO Cards</div>
                <div class="stat-value" id="cardsCount">0</div>
                <div class="stat-subtitle">Active cards</div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <button class="btn primary" onclick="uploadDocument()">
                <span>📤</span> Upload Document
            </button>
            <button class="btn" onclick="refreshDocuments()">
                <span>🔄</span> Refresh Documents
            </button>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-box">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.003-.004.006-.009.009-.014l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85-.007-.007a6.5 6.5 0 0 0 .433-.433Z"/>
                </svg>
                <input type="text" placeholder="Search by employee, document..." onkeyup="filterDocuments()">
            </div>
            <select class="filter-select" id="docTypeFilter" onchange="filterDocuments()">
                <option value="">All Documents</option>
                <option value="hmo_card">HMO Card</option>
                <option value="policy_document">Policy Document</option>
                <option value="provider_contract">Provider Contract</option>
                <option value="plan_endorsement">Plan Endorsement</option>
                <option value="renewal_notice">Renewal Notice</option>
            </select>
            <select class="filter-select" id="statusFilter" onchange="filterDocuments()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
            </select>
            <div class="tab-group">
                <button class="tab-btn active" onclick="switchTab(event, 'all')">
                    All Documents
                    <span class="tab-badge" id="all-count">0</span>
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'expiring')">
                    Expiring
                    <span class="tab-badge" id="expiring-count">0</span>
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'expired')">
                    Expired
                    <span class="tab-badge" id="expired-count">0</span>
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'cards')">
                    My Cards
                    <span class="tab-badge" id="cards-count">0</span>
                </button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Document Type</th>
                            <th>Issue Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="documents-tbody">
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📄</div>
                                    <div class="empty-state-text">No documents found</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Document Details Modal -->
    <div class="modal-overlay" id="detailsOverlay" onclick="closeSideModal()"></div>
    <div class="side-modal" id="detailsModal">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">Document Details</div>
            <button class="modal-close" onclick="closeSideModal()">✕</button>
        </div>
        <div class="modal-content">
            <div class="modal-section" id="docSection"></div>
            <div class="modal-section" id="coverageSection"></div>
        </div>
        <div class="modal-actions" id="modalActions"></div>
    </div>

    <script>
        // Global State
        if (typeof allDocuments === 'undefined') { var allDocuments = []; }
        allDocuments = [];
        if (typeof currentTab === 'undefined') { var currentTab = localStorage.getItem('selectedTab-documents') || 'all'; }
        currentTab = localStorage.getItem('selectedTab-documents') || 'all';

        // Initialize
        // Initialize on page load (runs immediately for dynamic loading)
        loadDocuments();

        // Load Documents
        function loadDocuments() {
            console.log('Loading documents...');
            fetch('modules/hmo/api.php?action=getHMODocuments')
                .then(res => res.json())
                .then(data => {
                    console.log('Documents data:', data);
                    if (data.success && data.documents) {
                        allDocuments = data.documents || [];
                        console.log('Rendering', allDocuments.length, 'documents');
                        renderDocuments();
                        updateCounts();
                    } else {
                        console.log('No success or empty documents:', data);
                    }
                })
                .catch(err => {
                    console.error('Error loading documents:', err);
                    showErrorState('documents-tbody', 'Failed to load documents');
                });
        }

        // Render Documents
        function renderDocuments() {
            const tbody = document.getElementById('documents-tbody');

            if (!allDocuments || allDocuments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">📄</div>
                                <div class="empty-state-text">No documents found</div>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = allDocuments.map(doc => {
                const docInitials = getInitials(doc.employee_first_name, doc.employee_last_name);
                const expiryStatus = getExpiryStatus(doc.expiry_date);
                const docType = doc.document_type ? doc.document_type.replace(/_/g, ' ').toUpperCase() : 'N/A';
                const docStatus = (doc.document_status || doc.status || 'N/A').toUpperCase();

                return `
                    <tr onclick="viewDocumentDetails('${doc.id}', event)">
                        <td>
                            <div class="employee-cell">
                                <div class="avatar">${docInitials}</div>
                                <div>
                                    <strong>${doc.employee_name || 'N/A'}</strong><br>
                                    <small style="color: #9ca3af;">Doc #${doc.document_number || doc.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="doc-type-badge ${doc.document_type || 'unknown'}">
                                ${docType}
                            </span>
                        </td>
                        <td>${formatDate(doc.issue_date)}</td>
                        <td>
                            ${formatDate(doc.expiry_date)}
                            ${doc.expiry_date ? `<div class="expiry-badge ${expiryStatus.class}" style="margin-top: 4px;">${expiryStatus.text}</div>` : ''}
                        </td>
                        <td>
                            <span class="status-badge ${(doc.document_status || doc.status || 'active').toLowerCase()}">
                                ${docStatus}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button class="icon-btn" onclick="downloadDocument('${doc.id}', event)" title="Download">⬇️</button>
                            <button class="icon-btn" onclick="viewDocumentDetails('${doc.id}', event)" title="View Details">👁️</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // View Document Details
        function viewDocumentDetails(docId, event) {
            event.stopPropagation();
            
            fetch(`modules/hmo/api.php?action=getHMODocumentDetail&id=${docId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        populateDetailsModal(data.document);
                        openSideModal();
                    }
                })
                .catch(err => console.error('Error loading details:', err));
        }

        // Populate Details Modal
        function populateDetailsModal(doc) {
            const expiryStatus = getExpiryStatus(doc.expiry_date);
            const docType = doc.document_type ? doc.document_type.replace(/_/g, ' ').toUpperCase() : 'N/A';
            
            document.getElementById('modalTitle').textContent = `${docType} - ${doc.employee_name}`;

            const docHtml = `
                <div class="modal-section-title">Document Information</div>
                <div class="modal-section-content">
                    <div class="modal-row">
                        <span class="modal-label">Document Type</span>
                        <span class="modal-value">${docType}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Document Number</span>
                        <span class="modal-value">${doc.document_number || 'N/A'}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Issue Date</span>
                        <span class="modal-value">${formatDate(doc.issue_date)}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Expiry Date</span>
                        <span class="modal-value">${formatDate(doc.expiry_date)} (${expiryStatus.text})</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Status</span>
                        <span class="modal-value">${(doc.document_status || doc.status || 'N/A').toUpperCase()}</span>
                    </div>
                </div>
            `;

            const coverageHtml = `
                <div class="modal-section-title">Coverage Information</div>
                <div class="modal-section-content">
                    <div class="modal-row">
                        <span class="modal-label">Plan</span>
                        <span class="modal-value">${doc.plan_name || 'N/A'}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Provider</span>
                        <span class="modal-value">${doc.provider_name || 'N/A'}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Description</span>
                        <span class="modal-value">${doc.description || 'No description'}</span>
                    </div>
                </div>
            `;

            document.getElementById('docSection').innerHTML = docHtml;
            document.getElementById('coverageSection').innerHTML = coverageHtml;

            document.getElementById('modalActions').innerHTML = `
                <button class="btn" onclick="closeSideModal()">Close</button>
                <button class="btn primary" onclick="downloadDocument('${doc.id}')">Download</button>
            `;
        }

        // Switch Tab
        function switchTab(event, tabName) {
            event.preventDefault();
            currentTab = tabName;
            localStorage.setItem('selectedTab-documents', tabName);

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.tab-btn').classList.add('active');

            filterDocuments();
        }

        // Filter Documents
        function filterDocuments() {
            const searchTerm = document.querySelector('.search-box input').value.toLowerCase();
            const docTypeFilter = document.getElementById('docTypeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('#documents-tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                let show = !searchTerm || text.includes(searchTerm);
                show = show && (!docTypeFilter || text.includes(docTypeFilter));
                show = show && (!statusFilter || text.includes(statusFilter));

                row.style.display = show ? '' : 'none';
            });
        }

        // Update Counts
        function updateCounts() {
            const now = new Date();
            // Use document_status instead of status (correct database column name)
            const active = allDocuments.filter(d => (d.document_status || d.status) === 'active').length;
            const expiring = allDocuments.filter(d => {
                if (!d.expiry_date) return false;
                const expDate = new Date(d.expiry_date);
                const daysUntil = Math.floor((expDate - now) / (1000 * 60 * 60 * 24));
                return daysUntil >= 0 && daysUntil <= 30 && (d.document_status || d.status) === 'active';
            }).length;
            const expired = allDocuments.filter(d => (d.document_status || d.status) === 'expired').length;
            // Check for Insurance_Card type (from database)
            const cards = allDocuments.filter(d => 
                (d.document_type === 'Insurance_Card' || d.document_type === 'hmo_card') && 
                (d.document_status || d.status) === 'active'
            ).length;

            document.getElementById('activeCount').textContent = active;
            document.getElementById('expiringCount').textContent = expiring;
            document.getElementById('expiredCount').textContent = expired;
            document.getElementById('cardsCount').textContent = cards;

            document.getElementById('all-count').textContent = allDocuments.length;
            document.getElementById('expiring-count').textContent = expiring;
            document.getElementById('expired-count').textContent = expired;
            document.getElementById('cards-count').textContent = cards;
        }

        // Download Document
        function downloadDocument(docId, event) {
            if (event) event.stopPropagation();
            window.location.href = `modules/hmo/api.php?action=downloadHMODocument&id=${docId}`;
        }

        // Side Modal
        function openSideModal() {
            document.getElementById('detailsOverlay').classList.add('active');
            document.getElementById('detailsModal').classList.add('active');
        }

        function closeSideModal() {
            document.getElementById('detailsOverlay').classList.remove('active');
            document.getElementById('detailsModal').classList.remove('active');
        }

        // Helper Functions
        function getInitials(firstName, lastName) {
            return `${(firstName || 'J').charAt(0)}${(lastName || 'D').charAt(0)}`.toUpperCase();
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('en-KE', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function getExpiryStatus(expiryDate) {
            if (!expiryDate) return { text: 'No expiry', class: 'good' };

            const now = new Date();
            const expDate = new Date(expiryDate);
            const daysUntil = Math.floor((expDate - now) / (1000 * 60 * 60 * 24));

            if (daysUntil < 0) {
                return { text: 'Expired', class: 'critical' };
            } else if (daysUntil <= 7) {
                return { text: `${daysUntil} days left`, class: 'critical' };
            } else if (daysUntil <= 14) {
                return { text: `${daysUntil} days left`, class: 'warning' };
            } else if (daysUntil <= 30) {
                return { text: `${daysUntil} days left`, class: 'warning' };
            } else {
                return { text: `${daysUntil} days left`, class: 'good' };
            }
        }

        function uploadDocument() {
            alert('Document upload feature coming soon');
        }

        function refreshDocuments() {
            loadDocuments();
        }

        function showErrorState(tbodyId, message) {
            document.getElementById(tbodyId).innerHTML = `
                <tr>
                    <td colspan="100%" style="padding: 40px 20px; text-align: center; color: #ef4444;">
                        ⚠️ ${message}
                    </td>
                </tr>
            `;
        }
    </script>
</body>

</html>
