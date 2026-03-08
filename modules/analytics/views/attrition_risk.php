<?php
/**
 * Analytics Attrition Risk View
 * Displays employee attrition risk predictions from AI model
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
    <title>Attrition Risk Analysis</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
            --success: #28a745;
            --border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
            color: var(--text-dark);
        }

        .attrition-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .page-header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .filter-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
        }

        .filter-group select {
            padding: 0.6rem 0.8rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .btn-refresh {
            padding: 0.6rem 1.2rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            align-self: flex-end;
        }

        .btn-refresh:hover {
            background: #0052a3;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }

        .card.critical {
            border-left-color: var(--danger);
        }

        .card.high {
            border-left-color: var(--warning);
        }

        .card-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .card-subtitle {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        .risk-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .table-header {
            padding: 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border);
        }

        .table-header h3 {
            font-size: 1.1rem;
            margin: 0;
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

        .risk-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .risk-badge.critical {
            background: #ffe0e0;
            color: var(--danger);
        }

        .risk-badge.high {
            background: #fff3cd;
            color: #856404;
        }

        .risk-badge.medium {
            background: #e7f3ff;
            color: var(--primary);
        }

        .risk-badge.low {
            background: #d4edda;
            color: var(--success);
        }

        .recommendation {
            font-size: 0.9rem;
            color: var(--text-light);
            max-width: 400px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s;
        }

        .progress-fill.critical {
            background: var(--danger);
            width: 80%;
        }

        .progress-fill.high {
            background: var(--warning);
            width: 60%;
        }

        .progress-fill.medium {
            background: var(--primary);
            width: 40%;
        }

        .progress-fill.low {
            background: var(--success);
            width: 20%;
        }

        .loading {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
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
    <div class="attrition-container">
        <div class="page-header">
            <h1><i class='bx bxs-wave-bottom'></i> Attrition Risk Analysis</h1>
            <p>AI-powered employee attrition risk predictions with retention recommendations</p>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label>Department</label>
                <select id="departmentFilter">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Risk Level</label>
                <select id="riskLevelFilter">
                    <option value="">All Levels</option>
                    <option value="Critical">Critical</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
            </div>
            <button class="btn-refresh" onclick="loadAttritionData()">
                <i class='bx bx-refresh'></i> Refresh
            </button>
        </div>

        <div class="summary-cards">
            <div class="card critical">
                <div class="card-title">Critical Risk</div>
                <div class="card-value" id="criticalCount">0</div>
                <div class="card-subtitle">Immediate action needed</div>
            </div>
            <div class="card high">
                <div class="card-title">High Risk</div>
                <div class="card-value" id="highCount">0</div>
                <div class="card-subtitle">Monitor closely</div>
            </div>
            <div class="card">
                <div class="card-title">Total at Risk</div>
                <div class="card-value" id="totalAtRisk">0</div>
                <div class="card-subtitle">Employees requiring attention</div>
            </div>
        </div>

        <div class="risk-table">
            <div class="table-header">
                <h3>At-Risk Employees</h3>
            </div>
            <div id="tableContainer">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading data...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadAttritionData() {
            const department = document.getElementById('departmentFilter').value;
            const riskLevel = document.getElementById('riskLevelFilter').value;

            try {
                const response = await fetch('./api.php?action=getAttritionRisk&department=' + department + '&limit=50', {
                    headers: {
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch data');

                const data = await response.json();
                
                if (data.success && data.data) {
                    displayAttritionData(data.data);
                } else {
                    document.getElementById('tableContainer').innerHTML = 
                        '<div class="loading"><p>AI Server is offline. Please ensure the AI service is running.</p></div>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('tableContainer').innerHTML = 
                    '<div class="loading"><p>Error loading data: ' + error.message + '</p></div>';
            }
        }

        function displayAttritionData(data) {
            const summary = data.summary || {};
            document.getElementById('totalAtRisk').textContent = summary.total_at_risk || 0;
            document.getElementById('criticalCount').textContent = summary.critical_count || 0;
            document.getElementById('highCount').textContent = summary.high_count || 0;

            let html = `<table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Attrition Risk</th>
                        <th>Risk Level</th>
                        <th>Promotion Chance</th>
                        <th>Recommendation</th>
                    </tr>
                </thead>
                <tbody>`;

            (data.data || []).forEach(emp => {
                const riskPercent = emp.attrition_risk || 0;
                const riskLevel = emp.risk_level || 'Unknown';
                const riskClass = riskLevel.toLowerCase();
                
                html += `<tr>
                    <td><strong>${emp.name}</strong></td>
                    <td>${emp.department}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill ${riskClass}"></div>
                        </div>
                        <span>${riskPercent}%</span>
                    </td>
                    <td><span class="risk-badge ${riskClass}">${riskLevel}</span></td>
                    <td>${(emp.promotion_chance || 0)}%</td>
                    <td class="recommendation">${emp.recommendation}</td>
                </tr>`;
            });

            html += `</tbody></table>`;
            document.getElementById('tableContainer').innerHTML = html;
        }

        // Load data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAttritionData();
        });
    </script>
</body>
</html>
