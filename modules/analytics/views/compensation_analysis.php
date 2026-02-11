<?php
/**
 * Compensation Analysis View
 * Analyzes compensation costs, salary equity, and pay structure
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
    <title>Compensation Analysis</title>
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
                <h1>Compensation Analysis</h1>
                <p style="color: var(--text-light); font-size: 14px;">Salary distribution, cost by department, and compensation insights</p>
            </div>
            <div>
                <button class="btn" onclick="exportReport('compensation-analysis', 'csv')">
                    <i class='bx bxs-download'></i> Export CSV
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Average Salary</div>
                <div class="kpi-value" id="kpiAvgSalary">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Across all active employees</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Minimum Salary</div>
                <div class="kpi-value" id="kpiMinSalary">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Entry level</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Maximum Salary</div>
                <div class="kpi-value" id="kpiMaxSalary">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Senior level</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Salary Range</div>
                <div class="kpi-value" id="kpiRange">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Max - Min spread</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart-alt-2'></i> Salary Cost by Department
                </div>
                <div class="chart-container">
                    <canvas id="costByDeptChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart'></i> Headcount by Department
                </div>
                <div class="chart-container">
                    <canvas id="headcountByDeptChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Department Compensation Table -->
        <div class="table-card">
            <div class="table-title">
                <i class='bx bxs-building'></i> Department Compensation Summary
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Headcount</th>
                        <th>Total Salary</th>
                        <th>Average Salary</th>
                        <th>Min Salary</th>
                        <th>Max Salary</th>
                    </tr>
                </thead>
                <tbody id="deptCompensationTable">
                    <tr><td colspan="6" style="text-align: center; color: #9ca3af;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let charts = {};

        async function loadData() {
            try {
                const response = await fetch('../api.php?action=getCompensationAnalysis');
                const result = await response.json();

                if (result.success) {
                    updateKPIs(result.data);
                    updateCharts(result.data);
                    updateDeptTable(result.data.cost_by_department);
                }
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function updateKPIs(data) {
            const avgSalary = data.salary_equity?.average || 0;
            const minSalary = data.salary_equity?.minimum || 0;
            const maxSalary = data.salary_equity?.maximum || 0;
            
            document.getElementById('kpiAvgSalary').textContent = '₱' + (avgSalary || 0).toLocaleString();
            document.getElementById('kpiMinSalary').textContent = '₱' + (minSalary || 0).toLocaleString();
            document.getElementById('kpiMaxSalary').textContent = '₱' + (maxSalary || 0).toLocaleString();
            document.getElementById('kpiRange').textContent = '₱' + ((maxSalary - minSalary) || 0).toLocaleString();
        }

        function updateCharts(data) {
            const costByDept = data.cost_by_department || [];

            // Cost by Department Chart
            if (charts.costByDept) charts.costByDept.destroy();
            
            const ctx1 = document.getElementById('costByDeptChart').getContext('2d');
            charts.costByDept = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: costByDept.map(d => d.department_name || 'N/A'),
                    datasets: [{
                        label: 'Total Salary Cost',
                        data: costByDept.map(d => d.total_salary || 0),
                        backgroundColor: '#1e40af'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y'
                }
            });

            // Headcount by Department Chart
            if (charts.headcountByDept) charts.headcountByDept.destroy();
            
            const ctx2 = document.getElementById('headcountByDeptChart').getContext('2d');
            charts.headcountByDept = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: costByDept.map(d => d.department_name || 'N/A'),
                    datasets: [{
                        label: 'Headcount',
                        data: costByDept.map(d => d.headcount || 0),
                        backgroundColor: '#22c55e'
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
            const tbody = document.getElementById('deptCompensationTable');
            tbody.innerHTML = '';

            if (!deptData || deptData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #9ca3af;">No data available</td></tr>';
                return;
            }

            deptData.forEach(dept => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td><strong>${dept.department_name || 'N/A'}</strong></td>
                    <td>${dept.headcount || 0}</td>
                    <td>₱${(dept.total_salary || 0).toLocaleString()}</td>
                    <td>₱${(dept.avg_salary || 0).toLocaleString()}</td>
                    <td style="color: #ef4444;">₱${(dept.min_salary || 0).toLocaleString()}</td>
                    <td style="color: #22c55e;">₱${(dept.max_salary || 0).toLocaleString()}</td>
                `;
            });
        }

        async function exportReport(reportType, format) {
            try {
                const response = await fetch(`../api.php?action=exportAnalytics&reportType=${reportType}&format=${format}`);
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = result.downloadUrl;
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
