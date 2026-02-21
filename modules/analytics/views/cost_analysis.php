<?php
/**
 * Cost Analysis View
 * Total organizational cost breakdown and trends
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
    <title>Cost Analysis</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Cost Analysis</h1>
                <p style="color: var(--text-light); font-size: 14px;">Total HR costs by category and monthly trends</p>
            </div>
            <div>
                <button class="btn" onclick="exportReport('cost-analysis', 'csv')">
                    <i class='bx bxs-download'></i> Export CSV
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Payroll (30d)</div>
                <div class="kpi-value" id="kpiPayroll">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Gross salary</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">HMO Cost (Annual)</div>
                <div class="kpi-value" id="kpiHMO">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Healthcare benefits</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Cost Per Employee</div>
                <div class="kpi-value" id="kpiCostPerEmp">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">Monthly average</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Monthly Cost</div>
                <div class="kpi-value" id="kpiTotal">₱0</div>
                <div style="font-size: 12px; color: var(--text-light);">All categories</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-pie-chart-alt'></i> Cost Breakdown by Category
                </div>
                <div class="chart-container">
                    <canvas id="costBreakdownChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-line-chart'></i> Cost Trend (Last 6 Months)
                </div>
                <div class="chart-container">
                    <canvas id="costTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use global charts cache to avoid redeclaration conflicts when injected into dashboard
        window.chartsCost = window.chartsCost || {};

        async function loadData() {
            try {
                const response = await fetch('/public_html/modules/analytics/api.php?action=getCostAnalysis');
                const result = await response.json();

                if (result.success) {
                    updateKPIs(result.data);
                    updateCharts(result.data);
                }
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function updateKPIs(data) {
            const payroll = data.total_payroll?.gross_total || 0;
            const hmo = data.hmo_cost || 0;
            const costPerEmp = payroll > 0 ? (payroll / 100) : 0; // Rough estimate
            const total = payroll + (hmo / 12); // Annualize HMO to monthly

            document.getElementById('kpiPayroll').textContent = '₱' + (payroll || 0).toLocaleString();
            document.getElementById('kpiHMO').textContent = '₱' + (hmo || 0).toLocaleString();
            document.getElementById('kpiCostPerEmp').textContent = '₱' + (costPerEmp || 0).toLocaleString();
            document.getElementById('kpiTotal').textContent = '₱' + (total || 0).toLocaleString();
        }

        function updateCharts(data) {
            const breakdown = data.cost_breakdown_by_category || {};
            const trends = data.cost_trends || [];

            // Cost Breakdown Chart
            if (window.chartsCost.breakdown) window.chartsCost.breakdown.destroy();
            
            const ctx1 = document.getElementById('costBreakdownChart').getContext('2d');
            window.chartsCost.breakdown = new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: ['Payroll', 'HMO', 'Compliance', 'Recruitment'],
                    datasets: [{
                        data: [
                            breakdown.payroll_cost || 0,
                            breakdown.hmo_cost || 0,
                            breakdown.compliance_training || 0,
                            breakdown.recruitment || 0
                        ],
                        backgroundColor: ['#1e40af', '#0ea5e9', '#f59e0b', '#22c55e']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Cost Trend Chart
            if (window.chartsCost.trend) window.chartsCost.trend.destroy();
            
            const ctx2 = document.getElementById('costTrendChart').getContext('2d');
            window.chartsCost.trend = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'],
                    datasets: [{
                        label: 'Monthly Payroll Cost',
                        data: trends.map(t => t.payroll_cost || 0),
                        borderColor: '#1e40af',
                        backgroundColor: 'rgba(30, 64, 175, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
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
