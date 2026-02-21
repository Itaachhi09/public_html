<?php
/**
 * Headcount Analytics View
 * Visualizes employee distribution, movement, and headcount trends
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
    <title>Headcount Analytics</title>
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

        .kpi-trend {
            font-size: 12px;
            color: var(--success);
        }

        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Headcount Analytics</h1>
                <p style="color: var(--text-light); font-size: 14px;">Employee distribution by department, location, and employment type</p>
            </div>
            <div>
                <button class="btn" onclick="exportReport('headcount-analytics', 'csv')">
                    <i class='bx bxs-download'></i> Export CSV
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Headcount</div>
                <div class="kpi-value" id="kpiTotal">0</div>
                <div class="kpi-trend">Active employees</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Active Employees</div>
                <div class="kpi-value" id="kpiActive">0</div>
                <div class="kpi-trend">Currently employed</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Inactive Employees</div>
                <div class="kpi-value" id="kpiInactive">0</div>
                <div class="kpi-trend">On leave or suspended</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Departments</div>
                <div class="kpi-value" id="kpiDepts">0</div>
                <div class="kpi-trend">Organization units</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-pie-chart-alt'></i> Headcount by Department
                </div>
                <div class="chart-container">
                    <canvas id="headcountDeptChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart-alt-2'></i> Employment Type Distribution
                </div>
                <div class="chart-container">
                    <canvas id="employmentTypeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart'></i> Headcount by Location
                </div>
                <div class="chart-container">
                    <canvas id="locationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Department Distribution Table -->
        <div class="table-card">
            <div class="table-title">
                <i class='bx bxs-building'></i> Department Headcount
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Headcount</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody id="deptHeadcountTable">
                    <tr><td colspan="3" style="text-align: center; color: #9ca3af;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Use global charts cache to avoid redeclaration conflicts when injected into dashboard
        window.chartsHeadcount = window.chartsHeadcount || {};

        async function loadData() {
            try {
                const response = await fetch('/public_html/modules/analytics/api.php?action=getHeadcountAnalytics');
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    updateKPIs(data);
                    updateCharts(data);
                    updateDeptTable(data.by_department);
                }
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function updateKPIs(data) {
            const deptData = data.by_department || [];
            let total = 0;
            deptData.forEach(d => total += d.count || 0);

            document.getElementById('kpiTotal').textContent = total;
            document.getElementById('kpiActive').textContent = total;
            document.getElementById('kpiInactive').textContent = 0;
            document.getElementById('kpiDepts').textContent = deptData.length;
        }

        function updateCharts(data) {
            const deptData = data.by_department || [];
            const typeData = data.by_employment_type || [];
            const locData = data.by_location || [];

            // Department Chart
            if (window.chartsHeadcount.deptChart) window.chartsHeadcount.deptChart.destroy();
            
            const ctx1 = document.getElementById('headcountDeptChart').getContext('2d');
            window.chartsHeadcount.deptChart = new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: deptData.map(d => d.department_name || 'N/A'),
                    datasets: [{
                        data: deptData.map(d => d.count || 0),
                        backgroundColor: ['#1e40af', '#0ea5e9', '#06b6d4', '#10b981', '#f59e0b', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Employment Type Chart
            if (window.chartsHeadcount.typeChart) window.chartsHeadcount.typeChart.destroy();
            
            const ctx2 = document.getElementById('employmentTypeChart').getContext('2d');
            window.chartsHeadcount.typeChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: typeData.map(t => t.type_name || 'N/A'),
                    datasets: [{
                        label: 'Employee Count',
                        data: typeData.map(t => t.count || 0),
                        backgroundColor: '#22c55e'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y'
                }
            });

            // Location Chart
            if (window.chartsHeadcount.locChart) window.chartsHeadcount.locChart.destroy();
            
            const ctx3 = document.getElementById('locationChart').getContext('2d');
            window.chartsHeadcount.locChart = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: locData.map(l => l.location_name || 'N/A'),
                    datasets: [{
                        label: 'Headcount',
                        data: locData.map(l => l.headcount || 0),
                        backgroundColor: '#f59e0b'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y'
                }
            });
        }

        function updateDeptTable(deptData) {
            const tbody = document.getElementById('deptHeadcountTable');
            tbody.innerHTML = '';

            if (!deptData || deptData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #9ca3af;">No data available</td></tr>';
                return;
            }

            let total = 0;
            deptData.forEach(d => total += d.count || 0);

            deptData.forEach(dept => {
                const percentage = total > 0 ? ((dept.count / total) * 100).toFixed(1) : 0;
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td><strong>${dept.department_name || 'N/A'}</strong></td>
                    <td>${dept.count || 0}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="flex: 1; height: 20px; background: #e5e7eb; border-radius: 10px; overflow: hidden;">
                                <div style="height: 100%; background: #1e40af; width: ${percentage}%;"></div>
                            </div>
                            ${percentage}%
                        </div>
                    </td>
                `;
            });
        }

        async function exportReport(reportType, format) {
            try {
                const response = await fetch(`../api.php?action=exportAnalytics&reportType=${reportType}&format=${format}`);
                const result = await response.json();
                
                if (result.success) {
                    const a = document.createElement('a');
                    a.href = result.downloadUrl;
                    a.download = 'report.csv';
                    a.click();
                }
            } catch (error) {
                alert('Error exporting report: ' + error.message);
            }
        }

        // Initialize
        loadData();
    </script>
</body>
</html>
