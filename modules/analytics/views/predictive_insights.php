<?php
/**
 * Analytics Predictive Insights View
 * Comprehensive dashboard combining all AI predictions
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
    <title>Predictive Insights Dashboard</title>
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
            --info: #17a2b8;
            --border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
            color: var(--text-dark);
        }

        .insights-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .page-header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            background: #d4edda;
            color: #155724;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .status-indicator.offline {
            background: #f8d7da;
            color: #721c24;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #155724;
        }

        .status-indicator.offline .status-dot {
            background: #721c24;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .grid-3col {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .widget {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .widget-header {
            padding: 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .widget-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .widget-action {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .widget-action:hover {
            background: #0052a3;
        }

        .widget-body {
            padding: 1.5rem;
        }

        .stat-group {
            display: flex;
            justify-content: space-around;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .stat-group:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            margin-top: 0.3rem;
        }

        .tag {
            display: inline-block;
            padding: 0.3rem 0.7rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .tag.critical {
            background: #ffe0e0;
            color: var(--danger);
        }

        .tag.high {
            background: #fff3cd;
            color: #856404;
        }

        .tag.info {
            background: #d1ecf1;
            color: var(--info);
        }

        .mini-list {
            list-style: none;
        }

        .mini-list li {
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mini-list li:last-child {
            border-bottom: none;
        }

        .employee-name {
            font-weight: 500;
            color: var(--text-dark);
        }

        .risk-score {
            font-weight: 600;
        }

        .chart-container {
            position: relative;
            height: 200px;
        }

        .alert-banner {
            background: #fff3cd;
            border-left: 4px solid var(--warning);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-banner i {
            font-size: 1.5rem;
            color: var(--warning);
        }

        .alert-banner p {
            margin: 0;
            color: #856404;
        }

        .loading {
            text-align: center;
            padding: 2rem;
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
            margin-bottom: 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .navigation-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.2rem;
            background: white;
            color: var(--primary);
            border: 1px solid var(--border);
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        @media (max-width: 1024px) {
            .grid-3col {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .insights-container {
                padding: 1rem;
            }

            .grid-2col,
            .grid-3col {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="insights-container">
        <div class="page-header">
            <h1><i class='bx bxs-magic-wand'></i> Predictive Analytics Dashboard</h1>
            <p>AI-powered insights for strategic HR decisions</p>
            <div class="status-indicator" id="aiStatus">
                <div class="status-dot"></div>
                <span>AI Service: Connecting...</span>
            </div>
        </div>

        <div class="navigation-links">
            <a href="attrition_risk.php" class="nav-link">
                <i class='bx bxs-wave-bottom'></i> Attrition Risk
            </a>
            <a href="promotion_candidates.php" class="nav-link">
                <i class='bx bxs-star'></i> Promotion Candidates
            </a>
            <a href="payroll_anomalies.php" class="nav-link">
                <i class='bx bxs-error-alt'></i> Payroll Anomalies
            </a>
        </div>

        <div id="alertContainer"></div>

        <div class="grid-2col">
            <!-- Attrition Risk Summary -->
            <div class="widget">
                <div class="widget-header">
                    <h3><i class='bx bxs-wave-bottom'></i> Attrition Risk</h3>
                    <button class="widget-action" onclick="goTo('attrition_risk.php')">View All</button>
                </div>
                <div class="widget-body">
                    <div class="stat-group">
                        <div class="stat">
                            <div class="stat-value" id="attritionCritical" style="color: var(--danger);">0</div>
                            <div class="stat-label">Critical</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value" id="attritionHigh" style="color: var(--warning);">0</div>
                            <div class="stat-label">High</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value" id="attritionTotal">0</div>
                            <div class="stat-label">At Risk</div>
                        </div>
                    </div>
                    <ul class="mini-list" id="attritionList">
                        <li><span class="loading"><div class="spinner" style="width: 20px; height: 20px; margin-bottom: 0;"></div></span></li>
                    </ul>
                </div>
            </div>

            <!-- Promotion Candidates Summary -->
            <div class="widget">
                <div class="widget-header">
                    <h3><i class='bx bxs-star'></i> Promotion Ready</h3>
                    <button class="widget-action" onclick="goTo('promotion_candidates.php')">View All</button>
                </div>
                <div class="widget-body">
                    <div class="stat-group">
                        <div class="stat">
                            <div class="stat-value" id="promotionCandidates">0</div>
                            <div class="stat-label">Candidates</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value" id="promotionAvgScore">0%</div>
                            <div class="stat-label">Avg Score</div>
                        </div>
                    </div>
                    <ul class="mini-list" id="promotionList">
                        <li><span class="loading"><div class="spinner" style="width: 20px; height: 20px; margin-bottom: 0;"></div></span></li>
                    </ul>
                </div>
            </div>

            <!-- Payroll Anomalies Summary -->
            <div class="widget">
                <div class="widget-header">
                    <h3><i class='bx bxs-error-alt'></i> Payroll Anomalies</h3>
                    <button class="widget-action" onclick="goTo('payroll_anomalies.php')">View All</button>
                </div>
                <div class="widget-body">
                    <div class="stat-group">
                        <div class="stat">
                            <div class="stat-value" id="anomalyCritical" style="color: var(--danger);">0</div>
                            <div class="stat-label">Critical</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value" id="anomalyTotal">0</div>
                            <div class="stat-label">Detected</div>
                        </div>
                    </div>
                    <ul class="mini-list" id="anomalyList">
                        <li><span class="loading"><div class="spinner" style="width: 20px; height: 20px; margin-bottom: 0;"></div></span></li>
                    </ul>
                </div>
            </div>

            <!-- Pay Forecast -->
            <div class="widget">
                <div class="widget-header">
                    <h3><i class='bx bxs-bar-chart-alt-2'></i> Pay Forecast</h3>
                    <button class="widget-action">3 Months</button>
                </div>
                <div class="widget-body">
                    <div class="chart-container">
                        <canvas id="forecastChart"></canvas>
                    </div>
                    <div class="stat-group" style="margin-top: 1rem;">
                        <div class="stat">
                            <div class="stat-value" id="forecastMonth1" style="font-size: 1.2rem;">-</div>
                            <div class="stat-label">Month 1</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value" id="forecastMonth2" style="font-size: 1.2rem;">-</div>
                            <div class="stat-label">Month 2</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Model Information -->
        <div class="widget">
            <div class="widget-header">
                <h3><i class='bx bxs-chip'></i> AI Models & Status</h3>
            </div>
            <div class="widget-body">
                <div class="stat-group" id="modelsInfo">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading model information...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadInsights() {
            try {
                // Check AI server health
                const healthResponse = await fetch('./api.php?action=getAIServerHealth', {
                    headers: {
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                    }
                });

                if (!healthResponse.ok) throw new Error('Health check failed');

                const healthData = await healthResponse.json();
                updateStatusIndicator(healthData.data.status === 'online');

                if (healthData.data.status === 'offline') {
                    showAlert('AI service is offline. Predictions unavailable.', 'warning');
                    return;
                }

                // Get predictive insights
                const insightsResponse = await fetch('./api.php?action=getPredictiveInsights', {
                    headers: {
                        'Authorization': 'Bearer ' + (localStorage.getItem('token') || '')
                    }
                });

                if (!insightsResponse.ok) throw new Error('Failed to fetch insights');

                const insightsData = await insightsResponse.json();
                displayInsights(insightsData.data);

                // Display AI models
                displayModels(healthData.data.models);

            } catch (error) {
                console.error('Error:', error);
                showAlert('Error loading predictive data: ' + error.message, 'danger');
                updateStatusIndicator(false);
            }
        }

        function updateStatusIndicator(isOnline) {
            const indicator = document.getElementById('aiStatus');
            if (isOnline) {
                indicator.innerHTML = '<div class="status-dot"></div><span>AI Service: Online</span>';
                indicator.classList.remove('offline');
            } else {
                indicator.innerHTML = '<div class="status-dot"></div><span>AI Service: Offline</span>';
                indicator.classList.add('offline');
            }
        }

        function displayInsights(data) {
            // Attrition Risk
            const attrRisk = data.attrition_risk || [];
            document.getElementById('attritionCritical').textContent = 
                attrRisk.filter(e => e.risk_level === 'Critical').length;
            document.getElementById('attritionHigh').textContent = 
                attrRisk.filter(e => e.risk_level === 'High').length;
            document.getElementById('attritionTotal').textContent = attrRisk.length;

            let attrList = '';
            attrRisk.slice(0, 3).forEach(emp => {
                const riskClass = emp.risk_level.toLowerCase();
                attrList += `<li>
                    <span class="employee-name">${emp.name}</span>
                    <span class="tag ${riskClass}">${emp.attrition_risk}%</span>
                </li>`;
            });
            document.getElementById('attritionList').innerHTML = attrList || '<li><span class="text-light">No at-risk employees</span></li>';

            // Promotion Candidates
            const promotion = data.promotion_candidates || [];
            document.getElementById('promotionCandidates').textContent = promotion.length;
            const avgScore = promotion.length > 0 
                ? Math.round(promotion.reduce((sum, e) => sum + e.promotion_probability, 0) / promotion.length)
                : 0;
            document.getElementById('promotionAvgScore').textContent = avgScore + '%';

            let promList = '';
            promotion.slice(0, 3).forEach(emp => {
                promList += `<li>
                    <span class="employee-name">${emp.name}</span>
                    <span class="tag info">${emp.promotion_probability}%</span>
                </li>`;
            });
            document.getElementById('promotionList').innerHTML = promList || '<li><span class="text-light">No candidates</span></li>';

            // Payroll Anomalies
            const anomalies = data.payroll_anomalies || [];
            document.getElementById('anomalyCritical').textContent = 
                anomalies.filter(a => a.severity === 'Critical').length;
            document.getElementById('anomalyTotal').textContent = anomalies.length;

            let anomList = '';
            anomalies.slice(0, 3).forEach(anom => {
                const sevClass = anom.severity.toLowerCase();
                anomList += `<li>
                    <span class="employee-name">${anom.employee_name}</span>
                    <span class="tag ${sevClass}">${anom.anomaly_score}%</span>
                </li>`;
            });
            document.getElementById('anomalyList').innerHTML = anomList || '<li><span class="text-light">No anomalies detected</span></li>';

            // Pay Forecast
            const forecast = data.pay_forecast || {};
            document.getElementById('forecastMonth1').textContent = formatCurrency(forecast.month_1 || 0);
            document.getElementById('forecastMonth2').textContent = formatCurrency(forecast.month_2 || 0);

            // Draw forecast chart if data available
            if (forecast.month_1) {
                drawForecastChart([forecast.month_1, forecast.month_2, forecast.month_3]);
            }
        }

        function displayModels(models) {
            let html = '<div class="stat-group">';
            for (let model in models) {
                html += `<div class="stat">
                    <div class="stat-label">${model.replace(/_/g, ' ')}</div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-top: 0.5rem;">${models[model]}</div>
                </div>`;
            }
            html += '</div>';
            document.getElementById('modelsInfo').innerHTML = html;
        }

        function drawForecastChart(data) {
            const ctx = document.getElementById('forecastChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Month 1', 'Month 2', 'Month 3'],
                    datasets: [{
                        label: 'Forecasted Payroll',
                        data: data,
                        backgroundColor: 'rgba(0, 102, 204, 0.6)',
                        borderColor: 'rgba(0, 102, 204, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function formatCurrency(value) {
            return '₱' + (value || 0).toLocaleString('en-PH', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        }

        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert-banner alert-' + type;
            alertDiv.innerHTML = `
                <i class='bx ${type === 'warning' ? 'bxs-info-circle' : type === 'danger' ? 'bxs-error-circle' : 'bxs-check-circle'}'></i>
                <p>${message}</p>
            `;
            document.getElementById('alertContainer').appendChild(alertDiv);

            setTimeout(() => alertDiv.remove(), 5000);
        }

        function goTo(page) {
            window.open(page, '_blank');
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadInsights);
    </script>
</body>
</html>
