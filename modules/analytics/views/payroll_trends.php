<?php
/**
 * Payroll Trends Analytics View
 * Visualizes payroll trends, costs, and earning patterns
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
    <title>Payroll Trends Analytics</title>
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

        .header-actions {
            display: flex;
            gap: 0.5rem;
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
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #1e3a8a;
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
            gap: 0.5rem;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
        }

        .filter-group select {
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: white;
            cursor: pointer;
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
            display: flex;
            align-items: center;
            gap: 0.25rem;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
        }

        .error {
            background: #fee;
            color: var(--danger);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Payroll Trends Analytics</h1>
                <p style="color: var(--text-light); font-size: 14px;">Monthly payroll costs, distribution, and earning patterns</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="exportReport('payroll-trends', 'csv')">
                    <i class='bx bxs-download'></i> Export CSV
                </button>
            </div>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label>Department</label>
                <select id="departmentFilter" onchange="loadData()">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Date Range</label>
                <select id="dateRangeFilter" onchange="loadData()">
                    <option value="30">Last 30 Days</option>
                    <option value="60">Last 60 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Gross Payroll (30d)</div>
                <div class="kpi-value" id="kpiGross">₱0</div>
                <div class="kpi-trend"><i class='bx bxs-up-arrow'></i> +2% vs last period</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Deductions (30d)</div>
                <div class="kpi-value" id="kpiDeductions">₱0</div>
                <div class="kpi-trend">Stable</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Net Payroll (30d)</div>
                <div class="kpi-value" id="kpiNet">₱0</div>
                <div class="kpi-trend">Average payment</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Active Employees</div>
                <div class="kpi-value" id="kpiEmployees">0</div>
                <div class="kpi-trend">On payroll</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart-alt-2'></i> Monthly Payroll Trends
                </div>
                <div class="chart-container">
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-pie-chart-alt'></i> Payroll Cost Breakdown
                </div>
                <div class="chart-container">
                    <canvas id="costBreakdownChart"></canvas>
                </div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart'></i> Salary Distribution
                </div>
                <div class="chart-container">
                    <canvas id="salaryDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Earners Table -->
        <div class="table-card">
            <div class="table-title">
                <i class='bx bxs-crown'></i> Top 10 Earners
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Department</th>
                        <th>Monthly Salary</th>
                        <th>YTD Earning</th>
                    </tr>
                </thead>
                <tbody id="topEarnersTable">
                    <tr><td colspan="4" class="loading">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let charts = {};

        async function loadData() {
            const department = document.getElementById('departmentFilter').value;
            const dateRange = document.getElementById('dateRangeFilter').value;

            try {
                const response = await fetch(`../api.php?action=getPayrollTrends&department=${department}&dateRange=${dateRange}`);
                const result = await response.json();

                if (result.success) {
                    updateKPIs(result.data);
                    updateCharts(result.data);
                    updateTopEarnersTable(result.data.top_earners);
                } else {
                    console.error('API Error:', result.error);
                }
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function updateKPIs(data) {
            const breakdown = data.cost_breakdown;
            document.getElementById('kpiGross').textContent = 
                '₱' + (breakdown.basic_salary + breakdown.allowances || 0).toLocaleString();
            document.getElementById('kpiDeductions').textContent = 
                '₱' + (breakdown.deductions || 0).toLocaleString();
            document.getElementById('kpiNet').textContent = 
                '₱' + ((breakdown.basic_salary + breakdown.allowances - breakdown.deductions) || 0).toLocaleString();
            document.getElementById('kpiEmployees').textContent = breakdown.employee_count || 0;
        }

        function updateCharts(data) {
            // Monthly Trends Chart
            const monthlyData = data.monthly_gross || [];
            if (charts.monthlyTrends) charts.monthlyTrends.destroy();
            
            const ctx1 = document.getElementById('monthlyTrendsChart').getContext('2d');
            charts.monthlyTrends = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: monthlyData.map(m => m.month || 'N/A'),
                    datasets: [{
                        label: 'Gross Payroll',
                        data: monthlyData.map(m => m.gross_total || 0),
                        borderColor: '#1e40af',
                        backgroundColor: 'rgba(30, 64, 175, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } }
                }
            });

            // Cost Breakdown Chart
            const breakdown = data.cost_breakdown;
            if (charts.costBreakdown) charts.costBreakdown.destroy();
            
            const ctx2 = document.getElementById('costBreakdownChart').getContext('2d');
            charts.costBreakdown = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Basic Salary', 'Allowances', 'Deductions'],
                    datasets: [{
                        data: [breakdown.basic_salary || 0, breakdown.allowances || 0, breakdown.deductions || 0],
                        backgroundColor: ['#1e40af', '#f59e0b', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Salary Distribution Chart
            const distribution = data.salary_distribution || [];
            if (charts.salaryDistribution) charts.salaryDistribution.destroy();
            
            const ctx3 = document.getElementById('salaryDistributionChart').getContext('2d');
            charts.salaryDistribution = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: distribution.map(d => d.salary_range || 'N/A'),
                    datasets: [{
                        label: 'Number of Employees',
                        data: distribution.map(d => d.employee_count || 0),
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

        function updateTopEarnersTable(topEarners) {
            const tbody = document.getElementById('topEarnersTable');
            tbody.innerHTML = '';

            if (!topEarners || topEarners.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #9ca3af;">No data available</td></tr>';
                return;
            }

            topEarners.forEach(earner => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td>${earner.name || 'N/A'}</td>
                    <td>${earner.department_name || 'N/A'}</td>
                    <td>₱${(earner.basic_rate || 0).toLocaleString()}</td>
                    <td>₱${(earner.total_earned || 0).toLocaleString()}</td>
                `;
            });
        }

        async function loadDepartments() {
            try {
                const response = await fetch('../api.php?action=getDashboardData');
                const result = await response.json();
                
                if (result.success) {
                    const select = document.getElementById('departmentFilter');
                    result.data.departments?.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = dept.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading departments:', error);
            }
        }

        async function exportReport(reportType, format) {
            try {
                const response = await fetch(`../api.php?action=exportAnalytics&reportType=${reportType}&format=${format}`);
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = result.downloadUrl;
                } else {
                    alert('Export failed: ' + result.error);
                }
            } catch (error) {
                alert('Error exporting report: ' + error.message);
            }
        }

        // Initialize
        loadDepartments();
        loadData();
    </script>
</body>
</html>
