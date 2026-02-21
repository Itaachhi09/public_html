<?php
/**
 * Analytics Module Index
 * Central hub for all analytics views and reports
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

$userRole = $_SESSION['role'] ?? 'hr';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Module</title>
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
            --secondary: #6c757d;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --border: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .layout {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
        }

        .sidebar {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .sidebar-header {
            background: var(--dark);
            color: white;
            padding: 1.5rem;
            font-weight: 600;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            border-bottom: 1px solid var(--border);
        }

        .sidebar-menu li:last-child {
            border-bottom: none;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s;
        }

        .sidebar-menu a:hover {
            background: var(--light);
            color: var(--primary);
            padding-left: 2rem;
        }

        .sidebar-menu i {
            font-size: 1.2rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }

        .card-header {
            height: 120px;
            background: linear-gradient(135deg, var(--primary), #0052a3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .card-header.success {
            background: linear-gradient(135deg, var(--success), #1e7e34);
        }

        .card-header.warning {
            background: linear-gradient(135deg, var(--warning), #e0a800);
        }

        .card-header.danger {
            background: linear-gradient(135deg, var(--danger), #bd2130);
        }

        .card-header.info {
            background: linear-gradient(135deg, var(--info), #117a8b);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .card-description {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .card-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--light);
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
        }

        .card-badge.new {
            background: #fff3cd;
            color: #856404;
        }

        .card-badge.ai {
            background: #e7f3ff;
            color: var(--primary);
        }

        .section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255,255,255,0.95);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        @media (max-width: 1024px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-menu a {
                padding: 0.8rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class='bx bxs-bar-chart-alt-2'></i> Analytics Hub</h1>
            <p>Comprehensive insights and predictive analytics for HR management</p>
        </div>

        <div class="layout">
            <!-- Sidebar Navigation -->
            <div class="sidebar">
                <div class="sidebar-header">Quick Access</div>
                <ul class="sidebar-menu">
                    <li><a href="views/dashboard.php"><i class='bx bxs-home'></i> Main Dashboard</a></li>
                    <li><a href="views/metrics.php"><i class='bx bxs-chart'></i> Metrics</a></li>
                    <li><a href="views/reports.php"><i class='bx bxs-file'></i> Reports</a></li>
                    <li><a href="views/predictive_insights.php"><i class='bx bxs-magic-wand'></i> AI Insights</a></li>
                    <li><a href="views/attrition_risk.php"><i class='bx bxs-wave-bottom'></i> Attrition Risk</a></li>
                    <li><a href="views/promotion_candidates.php"><i class='bx bxs-star'></i> Promotions</a></li>
                    <li><a href="views/payroll_anomalies.php"><i class='bx bxs-error-alt'></i> Anomalies</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div>
                <!-- Dashboard Section -->
                <div class="section">
                    <div class="section-title">Dashboard & Overview</div>
                    <div class="content-grid">
                        <a href="views/dashboard.php" class="card">
                            <div class="card-header"><i class='bx bxs-home'></i></div>
                            <div class="card-body">
                                <div class="card-title">Main Dashboard</div>
                                <div class="card-description">Real-time system visibility with key metrics</div>
                                <span class="card-badge">Updated Live</span>
                            </div>
                        </a>

                        <a href="views/metrics.php" class="card">
                            <div class="card-header info"><i class='bx bxs-chart'></i></div>
                            <div class="card-body">
                                <div class="card-title">KPI Metrics</div>
                                <div class="card-description">Standardized performance indicators</div>
                                <span class="card-badge">Graphical</span>
                            </div>
                        </a>

                        <a href="views/reports.php" class="card">
                            <div class="card-header success"><i class='bx bxs-file'></i></div>
                            <div class="card-body">
                                <div class="card-title">Reports</div>
                                <div class="card-description">Detailed records for audit & analysis</div>
                                <span class="card-badge">Exportable</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- AI Predictive Analytics Section -->
                <div class="section">
                    <div class="section-title">Predictive Analytics (Powered by AI)</div>
                    <div class="content-grid">
                        <a href="views/predictive_insights.php" class="card">
                            <div class="card-header warning"><i class='bx bxs-magic-wand'></i></div>
                            <div class="card-body">
                                <div class="card-title">Predictive Insights</div>
                                <div class="card-description">Combined AI predictions dashboard</div>
                                <span class="card-badge ai">ML Powered</span>
                            </div>
                        </a>

                        <a href="views/attrition_risk.php" class="card">
                            <div class="card-header danger"><i class='bx bxs-wave-bottom'></i></div>
                            <div class="card-body">
                                <div class="card-title">Attrition Risk</div>
                                <div class="card-description">Employee retention risk predictions</div>
                                <span class="card-badge new">AI Model</span>
                            </div>
                        </a>

                        <a href="views/promotion_candidates.php" class="card">
                            <div class="card-header success"><i class='bx bxs-star'></i></div>
                            <div class="card-body">
                                <div class="card-title">Promotion Candidates</div>
                                <div class="card-description">Ready-to-promote employee identification</div>
                                <span class="card-badge new">AI Model</span>
                            </div>
                        </a>

                        <a href="views/payroll_anomalies.php" class="card">
                            <div class="card-header danger"><i class='bx bxs-error-alt'></i></div>
                            <div class="card-body">
                                <div class="card-title">Payroll Anomalies</div>
                                <div class="card-description">Detect suspicious payroll transactions</div>
                                <span class="card-badge new">AI Model</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Business Analytics by Domain -->
                <div class="section">
                    <div class="section-title">Business Analytics by Domain</div>
                    <div class="content-grid">
                        <!-- Compensation Analysis Card based on dashboard views -->
                        <div class="card">
                            <div class="card-header"><i class='bx bxs-wallet'></i></div>
                            <div class="card-body">
                                <div class="card-title">Compensation Analysis</div>
                                <div class="card-description">Salary trends, cost breakdown, equity analysis</div>
                                <span class="card-badge">In Metrics</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header info"><i class='bx bxs-user-circle'></i></div>
                            <div class="card-body">
                                <div class="card-title">Headcount Analytics</div>
                                <div class="card-description">Workforce distribution and movements</div>
                                <span class="card-badge">In Metrics</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header success"><i class='bx bxs-health'></i></div>
                            <div class="card-body">
                                <div class="card-title">HMO Insights</div>
                                <div class="card-description">Health insurance enrollment and costs</div>
                                <span class="card-badge">In Metrics</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header warning"><i class='bx bxs-badge-check'></i></div>
                            <div class="card-body">
                                <div class="card-title">Compliance Tracking</div>
                                <div class="card-description">Document and contract expiry monitoring</div>
                                <span class="card-badge">In Dashboard</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header danger"><i class='bx bxs-bar-chart'></i></div>
                            <div class="card-body">
                                <div class="card-title">Cost Analysis</div>
                                <div class="card-description">Payroll, HMO, and benefits cost trends</div>
                                <span class="card-badge">In Metrics</span>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><i class='bx bxs-trending-up'></i></div>
                            <div class="card-body">
                                <div class="card-title">Movement Analysis</div>
                                <div class="card-description">Hiring, transfers, and attrition trends</div>
                                <span class="card-badge">In Metrics</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
