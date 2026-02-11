<?php
/**
 * HMO Insights View
 * Analytics on HMO enrollments, providers, and claims
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
    <title>HMO Insights</title>
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
                <h1>HMO Insights</h1>
                <p style="color: var(--text-light); font-size: 14px;">HMO enrollment trends, provider analysis, and healthcare costs</p>
            </div>
            <div>
                <button class="btn" onclick="exportReport('hmo-insights', 'csv')">
                    <i class='bx bxs-download'></i> Export CSV
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">HMO Enrollment Rate</div>
                <div class="kpi-value" id="kpiEnrollmentRate">0%</div>
                <div style="font-size: 12px; color: var(--text-light);">Of total employees</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Active Enrollments</div>
                <div class="kpi-value" id="kpiActive">0</div>
                <div style="font-size: 12px; color: var(--text-light);">Current members</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Cost Per Employee</div>
                <div class="kpi-value" id="kpiCostPerEmp">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Monthly average</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Active Providers</div>
                <div class="kpi-value" id="kpiProviders">0</div>
                <div style="font-size: 12px; color: var(--text-light);">Healthcare partners</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-pie-chart-alt'></i> Provider Market Share
                </div>
                <div class="chart-container">
                    <canvas id="providerChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart-alt-2'></i> Enrollment by Provider
                </div>
                <div class="chart-container">
                    <canvas id="enrollmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Provider Summary Table -->
        <div class="table-card">
            <div class="table-title">
                <i class='bx bxs-hospital'></i> HMO Provider Analysis
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Enrollments</th>
                        <th>Monthly Premium</th>
                        <th>Market Share</th>
                    </tr>
                </thead>
                <tbody id="providerTable">
                    <tr><td colspan="4" style="text-align: center; color: #9ca3af;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let charts = {};

        async function loadData() {
            try {
                const response = await fetch('../api.php?action=getHMOInsights');
                const result = await response.json();

                if (result.success) {
                    const data = result.data;
                    updateKPIs(data);
                    updateCharts(data);
                    updateProviderTable(data.provider_analysis);
                }
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function updateKPIs(data) {
            const enrollmentRate = data.enrollment_summary?.active || 0;
            const costPerEmp = data.cost_per_employee || 0;
            const providers = data.provider_analysis?.length || 0;

            document.getElementById('kpiEnrollmentRate').textContent = enrollmentRate + '%';
            document.getElementById('kpiActive').textContent = data.enrollment_summary?.total_enrollments || 0;
            document.getElementById('kpiCostPerEmp').textContent = '₱' + (costPerEmp || 0).toLocaleString();
            document.getElementById('kpiProviders').textContent = providers;
        }

        function updateCharts(data) {
            const providers = data.provider_analysis || [];
            const byProvider = data.enrollment_by_provider || [];

            // Provider Market Share
            if (charts.provider) charts.provider.destroy();
            
            const ctx1 = document.getElementById('providerChart').getContext('2d');
            charts.provider = new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: providers.map(p => p.provider_name || 'N/A'),
                    datasets: [{
                        data: providers.map(p => p.total_premium || 0),
                        backgroundColor: ['#1e40af', '#0ea5e9', '#06b6d4', '#10b981', '#f59e0b', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Enrollments by Provider
            if (charts.enrollment) charts.enrollment.destroy();
            
            const ctx2 = document.getElementById('enrollmentChart').getContext('2d');
            charts.enrollment = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: providers.map(p => p.provider_name || 'N/A'),
                    datasets: [{
                        label: 'Active Enrollments',
                        data: providers.map(p => p.enrollment_count || 0),
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

        function updateProviderTable(providers) {
            const tbody = document.getElementById('providerTable');
            tbody.innerHTML = '';

            if (!providers || providers.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: #9ca3af;">No data available</td></tr>';
                return;
            }

            let totalEnrollments = 0;
            providers.forEach(p => totalEnrollments += p.enrollment_count || 0);

            providers.forEach(provider => {
                const marketShare = totalEnrollments > 0 ? 
                    ((provider.enrollment_count / totalEnrollments) * 100).toFixed(1) : 0;
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td><strong>${provider.provider_name || 'N/A'}</strong></td>
                    <td>${provider.enrollment_count || 0}</td>
                    <td>₱${(provider.total_premium || 0).toLocaleString()}</td>
                    <td>${marketShare}%</td>
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
