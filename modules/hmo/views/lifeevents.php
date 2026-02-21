<?php
/**
 * Eligibility & Life Events View
 * 
 * Control when HMO changes are allowed based on life events
 * 
 * Features:
 * - Record life events (marriage, birth, resignation, termination, reinstatement)
 * - Validate eligibility based on life events
 * - Auto-process events with enrollment status updates
 * - Set change windows for enrollment modifications
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligibility & Life Events</title>
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

        .stat-card.warning {
            border-left-color: #f59e0b;
        }

        .stat-card.success {
            border-left-color: #10b981;
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

        .event-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            width: fit-content;
        }

        .event-badge.marriage {
            background: #fce7f3;
            color: #9f1239;
        }

        .event-badge.birth {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .event-badge.resignation {
            background: #fee2e2;
            color: #7c2d12;
        }

        .event-badge.termination {
            background: #fecaca;
            color: #7c2d12;
        }

        .event-badge.reinstatement {
            background: #d1fae5;
            color: #065f46;
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

        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.processed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .window-cell {
            font-size: 13px;
            color: #6b7280;
        }

        .window-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            width: fit-content;
            margin-top: 4px;
        }

        .window-status.open {
            background: #d1fae5;
            color: #065f46;
        }

        .window-status.closed {
            background: #fee2e2;
            color: #991b1b;
        }

        .window-status.pending {
            background: #fef3c7;
            color: #92400e;
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
            margin-bottom: 12px;
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

        /* Event Form Modal */
        .event-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 102;
            width: 90%;
            max-width: 500px;
        }

        .event-modal.active {
            display: block;
        }

        .modal-form-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-form-header h2 {
            font-size: 16px;
            font-weight: 600;
        }

        .modal-form-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #1f2937;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-form-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="page-header">
            <h1>Eligibility & Life Events</h1>
            <p>Control when HMO changes are allowed based on employee life events</p>
        </div>

        <!-- Stat Cards -->
        <div class="stat-cards">
            <div class="stat-card primary">
                <div class="stat-label">Pending Events</div>
                <div class="stat-value" id="pendingCount">0</div>
                <div class="stat-subtitle">Awaiting processing</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">Action Required</div>
                <div class="stat-value" id="actionCount">0</div>
                <div class="stat-subtitle">Outside change window</div>
            </div>
            <div class="stat-card success">
                <div class="stat-label">Processed Events</div>
                <div class="stat-value" id="processedCount">0</div>
                <div class="stat-subtitle">Successfully processed</div>
            </div>
            <div class="stat-card danger">
                <div class="stat-label">Rejected Events</div>
                <div class="stat-value" id="rejectedCount">0</div>
                <div class="stat-subtitle">Not eligible</div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <button class="btn primary" onclick="openEventModal()">
                <span>➕</span> Record Life Event
            </button>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-box">
                <svg viewBox="0 0 16 16" fill="currentColor">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.003-.004.006-.009.009-.014l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85-.007-.007a6.5 6.5 0 0 0 .433-.433Z"/>
                </svg>
                <input type="text" placeholder="Search by employee, event..." onkeyup="filterEvents()">
            </div>
            <select class="filter-select" id="eventTypeFilter" onchange="filterEvents()">
                <option value="">All Events</option>
                <option value="marriage">Marriage</option>
                <option value="birth">Birth</option>
                <option value="resignation">Resignation</option>
                <option value="termination">Termination</option>
                <option value="reinstatement">Reinstatement</option>
            </select>
            <select class="filter-select" id="statusFilter" onchange="filterEvents()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="processed">Processed</option>
                <option value="rejected">Rejected</option>
            </select>
            <div class="tab-group">
                <button class="tab-btn active" onclick="switchTab(event, 'pending')">
                    Pending
                    <span class="tab-badge" id="pending-count">0</span>
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'action')">
                    Action Required
                    <span class="tab-badge" id="action-count">0</span>
                </button>
                <button class="tab-btn" onclick="switchTab(event, 'processed')">
                    Processed
                    <span class="tab-badge" id="processed-count">0</span>
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
                            <th>Event Type</th>
                            <th>Event Date</th>
                            <th>Change Window</th>
                            <th>Status</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="events-tbody">
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📅</div>
                                    <div class="empty-state-text">No life events recorded</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal-overlay" id="detailsOverlay" onclick="closeSideModal()"></div>
    <div class="side-modal" id="detailsModal">
        <div class="modal-header">
            <div class="modal-title" id="modalTitle">Event Details</div>
            <button class="modal-close" onclick="closeSideModal()">✕</button>
        </div>
        <div class="modal-content">
            <div class="modal-section" id="eventSection"></div>
            <div class="modal-section" id="windowSection"></div>
            <div class="modal-section" id="documentSection"></div>
        </div>
        <div class="modal-actions" id="modalActions"></div>
    </div>

    <!-- Event Form Modal -->
    <div class="modal-overlay" id="formOverlay" onclick="closeEventModal()"></div>
    <div class="event-modal" id="eventFormModal">
        <div class="modal-form-header">
            <h2>Record Life Event</h2>
        </div>
        <div class="modal-form-body">
            <div class="form-group">
                <label>Employee</label>
                <input type="search" id="employeeSearch" placeholder="Search employee..." required>
                <input type="hidden" id="employeeId">
            </div>
            <div class="form-group">
                <label>Event Type</label>
                <select id="eventType" required>
                    <option value="">Select event type</option>
                    <option value="marriage">Marriage</option>
                    <option value="birth">Birth</option>
                    <option value="resignation">Resignation</option>
                    <option value="termination">Termination</option>
                    <option value="reinstatement">Reinstatement</option>
                </select>
            </div>
            <div class="form-group">
                <label>Event Date</label>
                <input type="date" id="eventDate" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="description" placeholder="Additional details..."></textarea>
            </div>
            <div class="form-group">
                <label>Supporting Document</label>
                <input type="file" id="supportingDoc">
            </div>
        </div>
        <div class="modal-form-footer">
            <button class="btn" onclick="closeEventModal()">Cancel</button>
            <button class="btn primary" onclick="submitLifeEvent()">Record Event</button>
        </div>
    </div>

    <script>
        // Global State
        if (typeof allEvents === 'undefined') { var allEvents = []; }
        allEvents = [];
        if (typeof currentTab === 'undefined') { var currentTab = localStorage.getItem('selectedTab-lifeevents') || 'pending'; }
        currentTab = localStorage.getItem('selectedTab-lifeevents') || 'pending';

        // Initialize on page load (runs immediately for dynamic loading)
        loadLifeEvents();

        // Load Life Events
        function loadLifeEvents() {
            fetch('modules/hmo/api.php?action=getLifeEvents')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        allEvents = data.events || [];
                        renderEvents();
                        updateCounts();
                    }
                })
                .catch(err => {
                    console.error('Error loading life events:', err);
                    showErrorState('events-tbody', 'Failed to load life events');
                });
        }

        // Render Events
        function renderEvents() {
            const tbody = document.getElementById('events-tbody');

            if (!allEvents || allEvents.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">📅</div>
                                <div class="empty-state-text">No life events recorded</div>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = allEvents.map(event => {
                const eventInitials = getInitials(event.employee_first_name, event.employee_last_name);
                const windowStatus = getWindowStatus(event.change_window_start, event.change_window_end);

                return `
                    <tr onclick="viewEventDetails('${event.id}', event)">
                        <td>
                            <div class="employee-cell">
                                <div class="avatar">${eventInitials}</div>
                                <div>
                                    <strong>${event.employee_name || 'N/A'}</strong><br>
                                    <small style="color: #9ca3af;">${event.employee_code || ''}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="event-badge ${event.event_type}">
                                ${event.event_type.replace(/_/g, ' ').toUpperCase()}
                            </span>
                        </td>
                        <td>${formatDate(event.event_date)}</td>
                        <td>
                            <div class="window-cell">
                                ${formatDate(event.change_window_start)} to ${formatDate(event.change_window_end)}
                                <div class="window-status ${windowStatus.class}">
                                    ${windowStatus.text}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge ${event.status}">
                                ${event.status.toUpperCase()}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <button class="icon-btn" onclick="viewEventDetails('${event.id}', event)" title="View Details">👁️</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // View Event Details
        function viewEventDetails(eventId, event) {
            event.stopPropagation();
            
            fetch(`modules/hmo/api.php?action=getLifeEventDetail&id=${eventId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        populateDetailsModal(data.event);
                        openSideModal();
                    }
                })
                .catch(err => console.error('Error loading event details:', err));
        }

        // Populate Details Modal
        function populateDetailsModal(event) {
            const windowStatus = getWindowStatus(event.change_window_start, event.change_window_end);

            document.getElementById('modalTitle').textContent = `${event.event_type.toUpperCase()} - ${event.employee_name}`;

            const eventHtml = `
                <div class="modal-section-title">Event Information</div>
                <div class="modal-section-content">
                    <div class="modal-row">
                        <span class="modal-label">Event Type</span>
                        <span class="modal-value">${event.event_type.toUpperCase()}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Event Date</span>
                        <span class="modal-value">${formatDate(event.event_date)}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Status</span>
                        <span class="modal-value">${event.status.toUpperCase()}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Description</span>
                        <span class="modal-value">${event.description || 'N/A'}</span>
                    </div>
                </div>
            `;

            const windowHtml = `
                <div class="modal-section-title">Change Window</div>
                <div class="modal-section-content">
                    <div class="modal-row">
                        <span class="modal-label">Start Date</span>
                        <span class="modal-value">${formatDate(event.change_window_start)}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">End Date</span>
                        <span class="modal-value">${formatDate(event.change_window_end)}</span>
                    </div>
                    <div class="modal-row">
                        <span class="modal-label">Status</span>
                        <span class="modal-value" style="color: ${windowStatus.color};">
                            ${windowStatus.text}
                        </span>
                    </div>
                </div>
            `;

            document.getElementById('eventSection').innerHTML = eventHtml;
            document.getElementById('windowSection').innerHTML = windowHtml;

            // Modal actions
            let actionsHtml = '<button class="btn" onclick="closeSideModal()">Close</button>';
            if (event.status === 'pending') {
                actionsHtml += `<button class="btn primary" onclick="processEvent('${event.id}')">Process Event</button>`;
            }
            document.getElementById('modalActions').innerHTML = actionsHtml;
        }

        // Switch Tab
        function switchTab(event, tabName) {
            event.preventDefault();
            currentTab = tabName;
            localStorage.setItem('selectedTab-lifeevents', tabName);

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.tab-btn').classList.add('active');

            filterEvents();
        }

        // Filter Events
        function filterEvents() {
            const searchTerm = document.querySelector('.search-box input').value.toLowerCase();
            const eventTypeFilter = document.getElementById('eventTypeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;

            const rows = document.querySelectorAll('#events-tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                let show = !searchTerm || text.includes(searchTerm);
                show = show && (!eventTypeFilter || text.includes(eventTypeFilter));
                show = show && (!statusFilter || text.includes(statusFilter));

                row.style.display = show ? '' : 'none';
            });
        }

        // Update Counts
        function updateCounts() {
            const pending = allEvents.filter(e => e.status === 'pending').length;
            const action = allEvents.filter(e => {
                const now = new Date();
                const endDate = new Date(e.change_window_end);
                return now > endDate && e.status === 'pending';
            }).length;
            const processed = allEvents.filter(e => e.status === 'processed').length;
            const rejected = allEvents.filter(e => e.status === 'rejected').length;

            document.getElementById('pendingCount').textContent = pending;
            document.getElementById('actionCount').textContent = action;
            document.getElementById('processedCount').textContent = processed;
            document.getElementById('rejectedCount').textContent = rejected;

            document.getElementById('pending-count').textContent = pending;
            document.getElementById('action-count').textContent = action;
            document.getElementById('processed-count').textContent = processed;
        }

        // Process Event
        function processEvent(eventId) {
            if (confirm('Process this life event?')) {
                fetch('modules/hmo/api.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'processLifeEvent',
                        id: eventId
                    }),
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeSideModal();
                        loadLifeEvents();
                    } else {
                        alert('Error: ' + (data.error || 'Processing failed'));
                    }
                });
            }
        }

        // Event Form Functions
        function openEventModal() {
            document.getElementById('eventFormModal').classList.add('active');
            document.getElementById('formOverlay').classList.add('active');
        }

        function closeEventModal() {
            document.getElementById('eventFormModal').classList.remove('active');
            document.getElementById('formOverlay').classList.remove('active');
            document.getElementById('eventType').value = '';
            document.getElementById('eventDate').value = '';
            document.getElementById('description').value = '';
        }

        function submitLifeEvent() {
            const eventType = document.getElementById('eventType').value;
            const eventDate = document.getElementById('eventDate').value;
            const description = document.getElementById('description').value;
            const employeeId = document.getElementById('employeeId').value;

            if (!eventType || !eventDate || !employeeId) {
                alert('Please fill in all required fields');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'createLifeEvent');
            formData.append('employee_id', employeeId);
            formData.append('event_type', eventType);
            formData.append('event_date', eventDate);
            formData.append('description', description);

            const file = document.getElementById('supportingDoc').files[0];
            if (file) {
                formData.append('supporting_document', file);
            }

            fetch('modules/hmo/api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Life event recorded successfully');
                    closeEventModal();
                    loadLifeEvents();
                } else {
                    alert('Error: ' + (data.error || 'Failed to record event'));
                }
            })
            .catch(err => alert('Error: ' + err.message));
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
            return new Date(dateString).toLocaleDateString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function getWindowStatus(startDate, endDate) {
            const now = new Date();
            const start = new Date(startDate);
            const end = new Date(endDate);

            if (now < start) {
                return { text: 'Not Yet Open', class: 'pending', color: '#f59e0b' };
            } else if (now > end) {
                return { text: 'Closed', class: 'closed', color: '#ef4444' };
            } else {
                return { text: 'Open', class: 'open', color: '#10b981' };
            }
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

</html>
