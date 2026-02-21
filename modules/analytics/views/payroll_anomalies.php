<?php
/**
 * Analytics Payroll Anomalies View
 * Displays AI-detected payroll anomalies and suspicious transactions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    header('Location: ../../../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Anomalies</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --dark: #1a1a2e;
            --light: #f4f6f9;
            --primary: #0066cc;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
        }

        .anomalies-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .control-group label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .control-group select {
            padding: 0.6rem 0.8rem;
            border: 1px solid var(--border);
            border-radius: 4px;
        }

        .btn {
            padding: 0.6rem 1.2rem;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #c82333;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .stat-card.critical {
            border-left: 4px solid var(--danger);
        }

        .stat-card.high {
            border-left: 4px solid var(--warning);
        }

        .stat-card.total {
            border-left: 4px solid var(--info);
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .table-wrapper {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .table-header {
            padding: 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            background: #f8f9fa;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        tr:hover {
            background: #f8f9fa;
        }

        .severity-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .severity-badge.critical {
            background: #ffe0e0;
            color: var(--danger);
        }

        .severity-badge.high {
            background: #fff3cd;
            color: #856404;
        }

        .severity-badge.medium {
            background: #d1ecf1;
            color: var(--info);
        }

        .severity-badge.low {
            background: #d4edda;
            color: #155724;
        }

        .amount {
            font-weight: 600;
            color: var(--dark);
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.4rem 0.8rem;
            border: 1px solid var(--border);
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background: #f0f0f0;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .loading {
            text-align: center;
            padding: 2rem;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="anomalies-container">
        <div class="header">
            <h1><i class='bx bxs-error-alt'></i> Payroll Anomalies</h1>
            <p>AI-detected suspicious transactions and payroll anomalies requiring review</p>
        </div>

        <div class="controls">
            <div class="control-group">
                <label>Department</label>
                <select id="departmentFilter">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="control-group">
                <label>Severity</label>
                <select id="severityFilter">
                    <option value="">All Levels</option>
                    <option value="Critical">Critical</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                </select>
            </div>
            <button class="btn" onclick="loadAnomalies()">
                <i class='bx bx-refresh'></i> Refresh
            </button>
        </div>

        <div class="stats">
            <div class="stat-card critical">
                <div class="stat-label">Critical Anomalies</div>
                <div class="stat-value" id="criticalCount">0</div>
            </div>
            <div class="stat-card high">
                <div class="stat-label">High Risk</div>
                <div class="stat-value" id="highCount">0</div>
            </div>
            <div class="stat-card total">
                <div class="stat-label">Total Anomalies</div>
                <div class="stat-value" id="totalCount">0</div>
            </div>
        </div>

        <div class="table-wrapper">
            <div class="table-header">
                <h3>Anomaly Detection Results</h3>
            </div>
            <div id="tableContainer">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading anomalies...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadAnomalies() {
            const department = document.getElementById('departmentFilter').value;
            const severity = document.getElementById('severityFilter').value;

            try {
                let url = './api.php?action=getPayrollAnomalies&dateRange=30';
                if (department) url += '&department=' + department;

                const response = await fetch(url, {
                    headers: {
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch anomalies');

                const data = await response.json();
                
                if (data.success && data.data) {
                    displayAnomalies(data.data, severity);
                } else {
                    document.getElementById('tableContainer').innerHTML = 
                        '<div class="empty-state"><p>No anomalies detected or AI service unavailable</p></div>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('tableContainer').innerHTML = 
                    '<div class="empty-state"><i class="bx bx-error"></i><p>Error loading data: ' + error.message + '</p></div>';
            }
        }

        function displayAnomalies(anomalies, severityFilter) {
            let filtered = anomalies;
            if (severityFilter) {
                filtered = anomalies.filter(a => a.severity === severityFilter);
            }

            document.getElementById('totalCount').textContent = filtered.length;
            document.getElementById('criticalCount').textContent = 
                filtered.filter(a => a.severity === 'Critical').length;
            document.getElementById('highCount').textContent = 
                filtered.filter(a => a.severity === 'High').length;

            if (filtered.length === 0) {
                document.getElementById('tableContainer').innerHTML = 
                    '<div class="empty-state"><i class="bx bx-check-circle"></i><p>No anomalies found</p></div>';
                return;
            }

            let html = `<table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Gross Pay</th>
                        <th>Net Pay</th>
                        <th>Anomaly Score</th>
                        <th>Severity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>`;

            filtered.forEach(anomaly => {
                const severityClass = (anomaly.severity || 'Low').toLowerCase();
                
                html += `<tr>
                    <td><strong>${anomaly.employee_name}</strong></td>
                    <td>${anomaly.department}</td>
                    <td class="amount">₱${parseFloat(anomaly.gross_pay).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td class="amount">₱${parseFloat(anomaly.net_pay).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                    <td>${anomaly.anomaly_score}%</td>
                    <td><span class="severity-badge ${severityClass}">${anomaly.severity}</span></td>
                    <td>
                        <div class="actions">
                            <button class="action-btn" onclick="reviewAnomaly(${anomaly.employee_id})">Review</button>
                            <button class="action-btn" onclick="markAsResolved(${anomaly.employee_id})">Resolve</button>
                        </div>
                    </td>
                </tr>`;
            });

            html += `</tbody></table>`;
            document.getElementById('tableContainer').innerHTML = html;
        }

        function reviewAnomaly(employeeId) {
            alert('Opening review for employee ' + employeeId);
            // TODO: Open review modal
        }

        function markAsResolved(employeeId) {
            alert('Marked as resolved for employee ' + employeeId);
            // TODO: Update database
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadAnomalies);
    </script>
</body>
</html>
