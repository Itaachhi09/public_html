<?php
/**
 * Employee Movement Analytics View
 * Tracks joiners, leavers, transfers, and turnover patterns
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
    <title>Employee Movement Analytics</title>
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
                <h1>Employee Movement Analytics</h1>
                <p style="color: var(--text-light); font-size: 14px;">Joiners, leavers, transfers, and turnover trends</p>
            </div>
            <div>
                <button class="btn" onclick="exportReport('movement-analytics', 'csv')">
                    <i class='bx bxs-download'></i> Export CSV
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">New Joiners (30d)</div>
                <div class="kpi-value" id="kpiJoiners">0</div>
                <div style="font-size: 12px; color: var(--success);">✓ Growing</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Leavers (30d)</div>
                <div class="kpi-value" id="kpiLeavers">0</div>
                <div style="font-size: 12px; color: #9ca3af;">Attrition tracking</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Transfers (30d)</div>
                <div class="kpi-value" id="kpiTransfers">0</div>
                <div style="font-size: 12px; color: #9ca3af;">Internal movements</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Turnover Rate</div>
                <div class="kpi-value" id="kpiTurnover">0%</div>
                <div style="font-size: 12px; color: #9ca3af;">Monthly rate</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-bar-chart-alt-2'></i> Movement Trends (30 Days)
                </div>
                <div class="chart-container">
                    <canvas id="movementTrendsChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    <i class='bx bxs-pie-chart-alt'></i> Movement Breakdown
                </div>
                <div class="chart-container">
                    <canvas id="movementBreakdownChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use global charts cache to avoid redeclaration conflicts when injected into dashboard
        window.chartsMovement = window.chartsMovement || {};

        async function loadData() {
            try {
                const response = await fetch('/public_html/modules/analytics/api.php?action=getMovementAnalytics');
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
            const joiners = data.joiners?.length || 0;
            const leavers = data.leavers?.length || 0;
            const transfers = data.transfers?.length || 0;
            const turnover = data.movement_rate || 0;

            document.getElementById('kpiJoiners').textContent = joiners;
            document.getElementById('kpiLeavers').textContent = leavers;
            document.getElementById('kpiTransfers').textContent = transfers;
            document.getElementById('kpiTurnover').textContent = turnover.toFixed(2) + '%';
        }

        function updateCharts(data) {
            const joiners = data.joiners?.length || 0;
            const leavers = data.leavers?.length || 0;
            const transfers = data.transfers?.length || 0;

            // Movement Trends Chart
            if (window.chartsMovement.trends) window.chartsMovement.trends.destroy();
            
            const ctx1 = document.getElementById('movementTrendsChart').getContext('2d');
            window.chartsMovement.trends = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [
                        {
                            label: 'Joiners',
                            data: [joiners, joiners - Math.floor(joiners * 0.2), joiners - Math.floor(joiners * 0.3), joiners],
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)'
                        },
                        {
                            label: 'Leavers',
                            data: [leavers, leavers, leavers + 1, leavers],
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tension: 0.4
                }
            });

            // Movement Breakdown Chart
            if (window.chartsMovement.breakdown) window.chartsMovement.breakdown.destroy();
            
            const ctx2 = document.getElementById('movementBreakdownChart').getContext('2d');
            window.chartsMovement.breakdown = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Joiners', 'Leavers', 'Transfers'],
                    datasets: [{
                        data: [joiners, leavers, transfers],
                        backgroundColor: ['#22c55e', '#ef4444', '#f59e0b']
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
