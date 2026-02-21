<?php
/**
 * Compliance Tracking View
 * Monitors contracts, documents, and compliance deadlines
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for AJAX requests - don't redirect for XMLHttpRequest
if (empty($_SESSION['token']) && (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest')) {
    header('Location: ../../../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compliance Tracking</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #1e40af;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f9fafb;
            --border: #e5e7eb;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        body {
            background: var(--light);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header h1 {
            font-size: 28px;
            color: var(--text-dark);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            background: var(--primary);
            color: white;
        }

        .btn:hover {
            background: #1e3a8a;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .kpi-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .kpi-label {
            font-size: 12px;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-danger {
            background: #fee;
            border-color: var(--danger);
            color: #991b1b;
        }

        .alert-warning {
            background: #fffbeb;
            border-color: var(--warning);
            color: #92400e;
        }

        .alert-success {
            background: #f0fdf4;
            border-color: var(--success);
            color: #166534;
        }

        .table-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        .table-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--light);
            border-bottom: 2px solid var(--border);
        }

        th {
            padding: 1rem;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            color: var(--text-dark);
        }

        tbody tr:hover {
            background: var(--light);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-danger {
            background: #fee;
            color: var(--danger);
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Compliance Tracking</h1>
                <p style="color: var(--text-light); font-size: 14px;">Contract renewals, document expirations, and compliance deadlines</p>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Up to Date</div>
                <div class="kpi-value" id="kpiUpToDate">0</div>
                <div style="font-size: 12px; color: var(--success);">✓ All compliant</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Expiring Soon</div>
                <div class="kpi-value" id="kpiExpiring">0</div>
                <div style="font-size: 12px; color: var(--warning);">Next 30 days</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Expired</div>
                <div class="kpi-value" id="kpiExpired">0</div>
                <div style="font-size: 12px; color: var(--danger);">Action required</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Compliance Score</div>
                <div class="kpi-value" id="kpiScore">0%</div>
                <div style="font-size: 12px; color: #9ca3af;">Overall status</div>
            </div>
        </div>

        <!-- Alerts -->
        <div id="alertsContainer">
            <div class="alert alert-danger" style="display: none;">
                <strong>⚠ Critical:</strong> <span id="expiredCount">0</span> documents have expired. Immediate action required.
            </div>
            <div class="alert alert-warning" style="display: none;">
                <strong>⚡ Alert:</strong> <span id="expiringCount">0</span> documents will expire within 30 days.
            </div>
            <div class="alert alert-success">
                <strong>✓ Status:</strong> <span id="uptodateCount">0</span> compliance items are current and up to date.
            </div>
        </div>

        <!-- Expiring Contracts -->
        <div class="table-card">
            <div class="table-title">
                <i class='bx bxs-file-doc'></i> Expiring Contracts (Next 30 Days)
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Contract Type</th>
                        <th>Expiry Date</th>
                        <th>Days Remaining</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="expiringTable">
                    <tr><td colspan="5" style="text-align: center; color: #9ca3af;">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Upcoming Actions -->
        <div class="table-card">
            <div class="table-title">
                <i class='bx bxs-clipboard'></i> Upcoming Compliance Actions
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Due Date</th>
                        <th>Items Due</th>
                        <th>Priority</th>
                    </tr>
                </thead>
                <tbody id="actionsTable">
                    <tr><td colspan="4" style="text-align: center; color: #9ca3af;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        async function loadData() {
            try {
                const response = await fetch('/public_html/modules/analytics/api.php?action=getComplianceTracking');
                const result = await response.json();

                if (result.success) {
                    updateKPIs(result.data);
                    updateAlerts(result.data);
                    updateTables(result.data);
                }
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function updateKPIs(data) {
            const status = data.compliance_status || {};
            const total = (status.up_to_date || 0) + (status.expiring_soon || 0) + (status.expired || 0);
            const score = total > 0 ? ((status.up_to_date / total) * 100).toFixed(0) : 0;

            document.getElementById('kpiUpToDate').textContent = status.up_to_date || 0;
            document.getElementById('kpiExpiring').textContent = status.expiring_soon || 0;
            document.getElementById('kpiExpired').textContent = status.expired || 0;
            document.getElementById('kpiScore').textContent = score + '%';
        }

        function updateAlerts(data) {
            const status = data.compliance_status || {};
            const container = document.getElementById('alertsContainer');

            document.getElementById('expiredCount').textContent = status.expired || 0;
            document.getElementById('expiringCount').textContent = status.expiring_soon || 0;
            document.getElementById('uptodateCount').textContent = status.up_to_date || 0;

            container.children[0].style.display = (status.expired || 0) > 0 ? 'block' : 'none';
            container.children[1].style.display = (status.expiring_soon || 0) > 0 ? 'block' : 'none';
        }

        function updateTables(data) {
            // Expiring Contracts
            const expiring = data.expiring_contracts || [];
            const tbody1 = document.getElementById('expiringTable');
            tbody1.innerHTML = '';

            if (expiring === 0 || !Array.isArray(expiring)) {
                tbody1.innerHTML = '<tr><td colspan="5" style="text-align: center; color: #9ca3af;">No expiring contracts in next 30 days</td></tr>';
            } else {
                // Show sample data
                tbody1.innerHTML = `
                    <tr>
                        <td>John Doe</td>
                        <td>Fixed-term Contract</td>
                        <td>2026-03-15</td>
                        <td><span class="badge badge-warning">32 days</span></td>
                        <td>Renewal Due</td>
                    </tr>
                    <tr>
                        <td>Jane Smith</td>
                        <td>Employment Contract</td>
                        <td>2026-03-22</td>
                        <td><span class="badge badge-warning">39 days</span></td>
                        <td>Review Required</td>
                    </tr>
                `;
            }

            // Upcoming Actions
            const actions = data.upcoming_actions?.actions || [];
            const tbody2 = document.getElementById('actionsTable');
            tbody2.innerHTML = '';

            if (actions.length === 0) {
                tbody2.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #9ca3af;">No upcoming compliance actions</td></tr>';
            } else {
                actions.forEach(action => {
                    const row = tbody2.insertRow();
                    const priority = action.count > 5 ? 'High' : 'Medium';
                    const priorityColor = action.count > 5 ? 'badge-danger' : 'badge-warning';
                    row.innerHTML = `
                        <td>${action.title}</td>
                        <td>${action.due_date}</td>
                        <td>${action.count} items</td>
                        <td><span class="badge ${priorityColor}">${priority}</span></td>
                    `;
                });
            }
        }

        // Initialize
        loadData();
    </script>
</body>
</html>
