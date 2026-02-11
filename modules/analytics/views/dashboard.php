<?php
/**
 * Analytics Dashboard View
 * Main analytics dashboard interface - Instant system visibility for decision makers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    header('Location: ../../../index.php');
    exit;
}

require_once __DIR__ . '/../dashboard_handler.php';

// Get user role for visibility rules
$userRole = $_SESSION['role'] ?? 'hr';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .analytics-dashboard {
            width: 100%;
            padding: 1.5rem;
            background: var(--light);
        }

        /* ===== PAGE HEADER ===== */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .dashboard-header-left h2 {
            margin: 0 0 0.5rem 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .dashboard-header-left p {
            margin: 0;
            color: var(--text-light);
            font-size: 14px;
        }

        .dashboard-filters {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
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
            letter-spacing: 0.5px;
        }

        .filter-group select,
        .date-range-input {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: white;
            font-size: 14px;
            color: var(--text-dark);
            cursor: pointer;
            min-width: 140px;
            transition: all 0.2s ease;
        }

        .filter-group select:hover,
        .date-range-input:hover {
            border-color: var(--primary);
        }

        .filter-group select:focus,
        .date-range-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .date-range-display {
            padding: 0.5rem 0.75rem;
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            color: var(--text-dark);
            font-weight: 500;
            min-width: 160px;
            text-align: center;
        }

        /* ===== SUMMARY CARDS SECTION ===== */
        .summary-cards-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
            border-color: var(--primary-light);
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .summary-card:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .card-title {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-icon {
            width: 40px;
            height: 40px;
            background: rgba(30, 64, 175, 0.1);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0.5rem 0 1rem 0;
        }

        .card-subvalues {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .subvalue {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .subvalue-label {
            font-size: 12px;
            color: var(--text-light);
        }

        .subvalue-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .trend-arrow {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 13px;
            font-weight: 600;
        }

        .trend-up {
            color: var(--success);
        }

        .trend-down {
            color: var(--danger);
        }

        .card-action {
            font-size: 12px;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-action:hover {
            color: var(--primary-dark);
        }

        /* ===== CHARTS SECTION ===== */
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .chart-toggle {
            display: flex;
            gap: 0.5rem;
            background: var(--light);
            padding: 0.25rem;
            border-radius: 6px;
        }

        .toggle-btn {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: var(--text-light);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .toggle-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1rem;
        }

        /* ===== OPERATIONS SNAPSHOT ===== */
        .operations-snapshot {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .operation-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .operation-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .operation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .operation-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        .operation-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-light);
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .mini-chart-container {
            height: 120px;
            margin-bottom: 1rem;
        }

        /* ===== ACTION REQUIRED PANEL ===== */
        .action-panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .action-panel-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .action-panel-icon {
            width: 32px;
            height: 32px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--danger);
            font-size: 16px;
        }

        .action-panel-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .action-items {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .action-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-item:last-child {
            border-bottom: none;
        }

        .action-item:hover {
            background: var(--light);
        }

        .action-item-info {
            flex: 1;
        }

        .action-item-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 0.25rem 0;
        }

        .action-item-detail {
            font-size: 13px;
            color: var(--text-light);
            margin: 0;
        }

        .action-item-due {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }

        .due-date {
            font-size: 13px;
            font-weight: 600;
            color: var(--danger);
        }

        .action-link {
            padding: 0.5rem 1rem;
            background: rgba(30, 64, 175, 0.1);
            color: var(--primary);
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .action-link:hover {
            background: rgba(30, 64, 175, 0.2);
            color: var(--primary-dark);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .charts-row {
                grid-template-columns: 1fr;
            }

            .dashboard-filters {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .analytics-dashboard {
                padding: 1rem;
            }

            .summary-cards-row {
                grid-template-columns: 1fr;
            }

            .operations-snapshot {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                flex-direction: column;
            }

            .dashboard-filters {
                flex-direction: column;
                width: 100%;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select,
            .date-range-input {
                width: 100%;
            }
        }

        /* Role-based visibility */
        .hide-for-executive {
            display: block;
        }

        .hide-for-finance {
            display: block;
        }

        .hide-for-manager {
            display: block;
        }

        body.role-executive .hide-for-executive,
        body.role-finance .hide-for-finance,
        body.role-manager .hide-for-manager {
            display: none !important;
        }

        /* Simplified charts for executive */
        body.role-executive .chart-toggle {
            display: none;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .no-data {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }
    </style>
</head>
<body class="role-<?php echo strtolower($userRole); ?>">
    <div class="analytics-dashboard">
        
        <!-- PAGE HEADER -->
        <div class="dashboard-header">
            <div class="dashboard-header-left">
                <h2>Analytics Dashboard</h2>
                <p>Real-time system visibility for informed decision making</p>
            </div>
            <div class="dashboard-filters">
                <div class="filter-group">
                    <label for="date-range">Period</label>
                    <select id="date-range" onchange="applyFilters()">
                        <option value="30">Last 30 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last 12 Months</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="filter-group" id="custom-date-group" style="display: none;">
                    <label>From</label>
                    <input type="date" id="custom-date-from" class="date-range-input" onchange="applyFilters()">
                </div>
                <div class="filter-group" id="custom-date-to-group" style="display: none;">
                    <label>To</label>
                    <input type="date" id="custom-date-to" class="date-range-input" onchange="applyFilters()">
                </div>
                <div class="filter-group hide-for-manager">
                    <label for="department-filter">Department</label>
                    <select id="department-filter" onchange="applyFilters()">
                        <option value="">All Departments</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="employment-type-filter">Employment Type</label>
                    <select id="employment-type-filter" onchange="applyFilters()">
                        <option value="">All Types</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ANALYTICS NAVIGATION -->
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <a href="dashboard.php" class="analytics-nav-btn" style="background: var(--primary); color: white;">
                <i class='bx bxs-dashboard'></i> Overview
            </a>
            <a href="payroll_trends.php" class="analytics-nav-btn">
                <i class='bx bxs-bar-chart'></i> Payroll Trends
            </a>
            <a href="compensation_analysis.php" class="analytics-nav-btn">
                <i class='bx bxs-wallet'></i> Compensation
            </a>
            <a href="headcount_analytics.php" class="analytics-nav-btn">
                <i class='bx bxs-user-detail'></i> Headcount
            </a>
            <a href="hmo_insights.php" class="analytics-nav-btn">
                <i class='bx bxs-heart'></i> HMO Insights
            </a>
            <a href="movement_analytics.php" class="analytics-nav-btn">
                <i class='bx bxs-transfer'></i> Movement
            </a>
            <a href="cost_analysis.php" class="analytics-nav-btn">
                <i class='bx bxs-calculator'></i> Cost Analysis
            </a>
            <a href="compliance_tracking.php" class="analytics-nav-btn">
                <i class='bx bxs-check-square'></i> Compliance
            </a>
        </div>

        <style>
            .analytics-nav-btn {
                padding: 0.6rem 1rem;
                border: 1px solid var(--border);
                border-radius: 6px;
                background: white;
                color: var(--text-dark);
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.2s;
                cursor: pointer;
            }
            .analytics-nav-btn:hover {
                background: var(--light);
                border-color: var(--primary);
                color: var(--primary);
            }
        </style>

        <!-- SECTION 1: SUMMARY CARDS -->
        <div class="summary-cards-row">
            <!-- Card 1: Total Headcount -->
            <div class="summary-card" onclick="drillToMetrics(event, 'headcount')">
                <div class="card-header">
                    <p class="card-title">Total Headcount</p>
                    <div class="card-icon"><i class='bx bxs-user'></i></div>
                </div>
                <p class="card-value" id="headcount-value">-</p>
                <div class="card-subvalues">
                    <div class="subvalue">
                        <span class="subvalue-label">Active</span>
                        <span class="subvalue-value" id="headcount-active">-</span>
                    </div>
                    <div class="subvalue">
                        <span class="subvalue-label">Inactive</span>
                        <span class="subvalue-value" id="headcount-inactive">-</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="trend-arrow trend-up" id="headcount-trend">
                        <i class='bx bx-up-arrow-alt'></i>
                        <span id="headcount-trend-pct">+2.5%</span>
                    </div>
                    <a href="#" class="card-action">View Details <i class='bx bx-chevron-right'></i></a>
                </div>
            </div>

            <!-- Card 2: Movement -->
            <div class="summary-card" onclick="drillToMetrics(event, 'movement')">
                <div class="card-header">
                    <p class="card-title">Movement</p>
                    <div class="card-icon"><i class='bx bxs-user-check'></i></div>
                </div>
                <p class="card-value" id="movement-value">-</p>
                <div class="card-subvalues">
                    <div class="subvalue">
                        <span class="subvalue-label">New Hires</span>
                        <span class="subvalue-value" id="movement-new">-</span>
                    </div>
                    <div class="subvalue">
                        <span class="subvalue-label">Resignations</span>
                        <span class="subvalue-value" id="movement-resignations">-</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div id="movement-bar" style="flex: 1; height: 8px; background: var(--light); border-radius: 4px; position: relative; overflow: hidden;">
                        <div style="position: absolute; left: 0; top: 0; height: 100%; background: var(--success); width: 65%;" title="Positive movement"></div>
                    </div>
                    <a href="#" class="card-action" style="margin-left: 1rem;">View Details <i class='bx bx-chevron-right'></i></a>
                </div>
            </div>

            <!-- Card 3: Payroll Cost -->
            <div class="summary-card hide-for-manager" onclick="drillToMetrics(event, 'payroll')">
                <div class="card-header">
                    <p class="card-title">Payroll Cost</p>
                    <div class="card-icon"><i class='bx bxs-wallet'></i></div>
                </div>
                <p class="card-value" id="payroll-value">-</p>
                <div class="card-subvalues">
                    <div class="subvalue">
                        <span class="subvalue-label">Avg Salary</span>
                        <span class="subvalue-value" id="payroll-avg">-</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div style="flex: 1; height: 30px;" id="payroll-sparkline"></div>
                    <a href="#" class="card-action">View Details <i class='bx bx-chevron-right'></i></a>
                </div>
            </div>

            <!-- Card 4: Attendance Rate -->
            <div class="summary-card">
                <div class="card-header">
                    <p class="card-title">Attendance Rate</p>
                    <div class="card-icon"><i class='bx bxs-calendar-check'></i></div>
                </div>
                <p class="card-value" id="attendance-value">-</p>
                <div class="card-subvalues">
                    <div class="subvalue">
                        <span class="subvalue-label">Absent Today</span>
                        <span class="subvalue-value" id="attendance-absent">-</span>
                    </div>
                </div>
                <div class="card-footer">
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
                        <canvas id="attendance-ring" width="60" height="60"></canvas>
                    </div>
                    <a href="#" class="card-action">View Details <i class='bx bx-chevron-right'></i></a>
                </div>
            </div>
        </div>

        <!-- SECTION 2: CHARTS -->
        <div class="charts-row hide-for-executive">
            <!-- Left Chart: Headcount Trend -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Headcount Trend</h3>
                    <div class="chart-toggle">
                        <button class="toggle-btn active" onclick="switchHeadcountView(event, 'monthly')">Monthly</button>
                        <button class="toggle-btn" onclick="switchHeadcountView(event, 'quarterly')">Quarterly</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="headcount-trend-chart"></canvas>
                </div>
            </div>

            <!-- Right Chart: Payroll Cost Breakdown -->
            <div class="chart-card hide-for-finance hide-for-manager">
                <div class="chart-header">
                    <h3 class="chart-title">Payroll Cost Breakdown</h3>
                </div>
                <div class="chart-container">
                    <canvas id="payroll-breakdown-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- SECTION 3: OPERATIONS SNAPSHOT -->
        <div class="operations-snapshot hide-for-executive">
            <!-- HMO Overview -->
            <div class="operation-card" onclick="drillToMetrics(event, 'hmo')">
                <div class="operation-header">
                    <h3 class="operation-title">HMO Overview</h3>
                    <i class='bx bx-health' style="font-size: 24px; color: var(--primary);"></i>
                </div>
                <div class="operation-stats">
                    <div class="stat-item">
                        <span class="stat-label">Enrolled</span>
                        <span class="stat-value" id="hmo-enrolled">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Avg Cost/Emp</span>
                        <span class="stat-value" id="hmo-avg-cost">-</span>
                    </div>
                </div>
                <div class="mini-chart-container">
                    <canvas id="hmo-donut-chart"></canvas>
                </div>
            </div>

            <!-- Overtime Usage -->
            <div class="operation-card" onclick="drillToMetrics(event, 'overtime')">
                <div class="operation-header">
                    <h3 class="operation-title">Overtime Usage</h3>
                    <i class='bx bx-time' style="font-size: 24px; color: var(--warning);"></i>
                </div>
                <div class="operation-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Hours</span>
                        <span class="stat-value" id="overtime-hours">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Cost</span>
                        <span class="stat-value" id="overtime-cost">-</span>
                    </div>
                </div>
                <div class="mini-chart-container">
                    <canvas id="overtime-chart"></canvas>
                </div>
            </div>

            <!-- Compliance Alerts -->
            <div class="operation-card" onclick="drillToMetrics(event, 'compliance')">
                <div class="operation-header">
                    <h3 class="operation-title">Compliance Alerts</h3>
                    <i class='bx bx-error-circle' style="font-size: 24px; color: var(--danger);"></i>
                </div>
                <div class="operation-stats">
                    <div class="stat-item">
                        <span class="stat-label">Expiring Docs</span>
                        <span class="stat-value" id="compliance-docs" style="color: var(--danger);">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Expiring Contracts</span>
                        <span class="stat-value" id="compliance-contracts" style="color: var(--danger);">-</span>
                    </div>
                </div>
                <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(239, 68, 68, 0.1); border-radius: 4px; text-align: center;">
                    <span id="compliance-total" style="color: var(--danger); font-weight: 600;">- Total Alerts</span>
                </div>
            </div>
        </div>

        <!-- SECTION 4: ACTION REQUIRED PANEL -->
        <div class="action-panel hide-for-executive">
            <div class="action-panel-header">
                <div class="action-panel-icon"><i class='bx bx-error-circle'></i></div>
                <h3 class="action-panel-title">Action Required</h3>
            </div>
            <div class="action-items" id="action-items">
                <div style="padding: 2rem; text-align: center; color: var(--text-light);">
                    <div class="loading-spinner" style="margin: 0 auto 1rem auto;"></div>
                    Loading action items...
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPTS -->
    <script>
        // ===== FILTER HANDLING =====
        document.getElementById('date-range').addEventListener('change', function() {
            const customGroup = document.getElementById('custom-date-group');
            const customGroupTo = document.getElementById('custom-date-to-group');
            if (this.value === 'custom') {
                customGroup.style.display = 'flex';
                customGroupTo.style.display = 'flex';
            } else {
                customGroup.style.display = 'none';
                customGroupTo.style.display = 'none';
            }
        });

        function applyFilters() {
            loadDashboardData();
        }

        // ===== DRILL DOWN FUNCTIONS =====
        function drillToMetrics(event, metric) {
            event.preventDefault();
            event.stopPropagation();
            
            // Get current filters
            const dateRange = document.getElementById('date-range').value;
            const department = document.getElementById('department-filter').value;
            const employmentType = document.getElementById('employment-type-filter').value;
            
            // Navigate to metrics view with filters
            window.location.href = 'metrics.php?metric=' + metric + 
                                  '&dateRange=' + dateRange + 
                                  '&department=' + department + 
                                  '&employmentType=' + employmentType;
        }

        function switchHeadcountView(event, viewType) {
            event.preventDefault();
            
            // Update button styling
            const buttons = document.querySelectorAll('.chart-toggle .toggle-btn');
            buttons.forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase() === viewType.toLowerCase()) {
                    btn.classList.add('active');
                }
            });
            
            // Reload chart with new view
            if (headcountTrendChart) {
                headcountTrendChart.destroy();
                headcountTrendChart = null;
            }
            
            // Fetch data for selected view and reload chart
            loadHeadcountTrendChart();
        }

        // ===== CHART VARIABLES =====
        let headcountTrendChart = null;
        let payrollBreakdownChart = null;
        let hmoDonutChart = null;
        let overtimeChart = null;
        let attendanceRingChart = null;

        // ===== LOAD DASHBOARD DATA =====
        function loadDashboardData() {
            // Get filter values
            const dateRange = document.getElementById('date-range').value;
            const department = document.getElementById('department-filter').value;
            const employmentType = document.getElementById('employment-type-filter').value;
            
            // Build query string
            let url = '../api.php?action=getDashboardData&dateRange=' + encodeURIComponent(dateRange);
            if (department && department !== '') {
                url += '&department=' + encodeURIComponent(department);
            }
            if (employmentType && employmentType !== '') {
                url += '&employmentType=' + encodeURIComponent(employmentType);
            }
            
            // Fetch from API with filters
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboard(data.data);
                    } else {
                        console.error('Failed to load dashboard data:', data.error);
                    }
                })
                .catch(error => console.error('API Error:', error));
        }

        function updateDashboard(data) {
            // Update summary cards
            document.getElementById('headcount-value').textContent = (data.summary?.headcount?.total || 0).toLocaleString();
            document.getElementById('headcount-active').textContent = (data.summary?.headcount?.active || 0).toLocaleString();
            document.getElementById('headcount-inactive').textContent = (data.summary?.headcount?.inactive || 0).toLocaleString();
            
            // Calculate trend
            const headcountTrend = data.summary?.headcount?.active > 0 ? 
                ((data.summary?.movement?.net_movement / data.summary?.headcount?.active) * 100).toFixed(1) : 0;
            document.getElementById('headcount-trend-pct').textContent = 
                (headcountTrend >= 0 ? '+' : '') + headcountTrend + '%';
            
            // Update movement card
            const movement = (data.summary?.movement?.new_hires || 0) - (data.summary?.movement?.resignations || 0);
            document.getElementById('movement-value').textContent = (movement >= 0 ? '+' : '') + movement.toLocaleString();
            document.getElementById('movement-new').textContent = (data.summary?.movement?.new_hires || 0).toLocaleString();
            document.getElementById('movement-resignations').textContent = (data.summary?.movement?.resignations || 0).toLocaleString();
            
            // Update payroll card
            document.getElementById('payroll-value').textContent = 'PKR ' + (data.summary?.payroll?.gross || 0).toLocaleString();
            document.getElementById('payroll-avg').textContent = 'PKR ' + (data.summary?.payroll?.net || 0).toLocaleString();
            
            // Update attendance card
            document.getElementById('attendance-rate').textContent = (data.summary?.attendance?.rate || 0).toFixed(1) + '%';
            document.getElementById('attendance-absent').textContent = (data.summary?.attendance?.absent_count || 0).toLocaleString();
            
            // HMO Snapshot
            document.getElementById('hmo-enrolled').textContent = (data.hmo?.enrolled || 0).toLocaleString();
            document.getElementById('hmo-not-enrolled').textContent = ((data.summary?.headcount?.total || 0) - (data.hmo?.enrolled || 0)).toLocaleString();
            document.getElementById('hmo-cost').textContent = 'PKR ' + (data.hmo?.total_cost || 0).toLocaleString();
            
            // Overtime
            document.getElementById('overtime-hours').textContent = (data.overtime?.hours || 0).toFixed(1) + ' hrs';
            document.getElementById('overtime-cost').textContent = 'PKR ' + (data.overtime?.cost || 0).toLocaleString();
            
            // Compliance
            document.getElementById('compliance-contracts').textContent = (data.compliance?.expiring_contracts || 0).toLocaleString();
            document.getElementById('compliance-documents').textContent = (data.compliance?.expiring_documents || 0).toLocaleString();
            
            // Update department dropdown
            if (data.departments && data.departments.length > 0) {
                const deptFilter = document.getElementById('department-filter');
                const currentValue = deptFilter.value;
                deptFilter.innerHTML = '<option value="">All Departments</option>' +
                    data.departments.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
                deptFilter.value = currentValue;
            }
            
            // Update employment type dropdown
            if (data.employment_types && data.employment_types.length > 0) {
                const empTypeFilter = document.getElementById('employment-type-filter');
                const currentValue = empTypeFilter.value;
                empTypeFilter.innerHTML = '<option value="">All Types</option>' +
                    data.employment_types.map(et => `<option value="${et.id}">${et.name}</option>`).join('');
                empTypeFilter.value = currentValue;
            }
            
            // Load charts
            loadHeadcountTrendChart();
            loadPayrollBreakdownChart();
            loadHMODonutChart();
            loadOvertimeChart();
        }

        // ===== CHART LOADING FUNCTIONS =====
        function loadHeadcountTrendChart() {
            const ctx = document.getElementById('headcount-trend-chart').getContext('2d');
            
            // Sample data - replace with API call
            const labels = period === 'monthly' 
                ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
                : ['Q1', 'Q2', 'Q3', 'Q4'];
            
            const activeData = period === 'monthly'
                ? [450, 465, 480, 495, 510, 525]
                : [465, 495, 530, 560];
            
            const inactiveData = period === 'monthly'
                ? [15, 14, 13, 12, 11, 10]
                : [14, 12, 10, 8];

            if (headcountTrendChart) {
                headcountTrendChart.destroy();
            }

            headcountTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Active',
                            data: activeData,
                            borderColor: '#22c55e',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#22c55e',
                            pointBorderColor: 'white',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Inactive',
                            data: inactiveData,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.05)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointBackgroundColor: '#ef4444',
                            pointBorderColor: 'white',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: { size: 12, weight: 600 },
                                color: '#6b7280',
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 14, weight: 600 },
                            bodyFont: { size: 13 },
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: { color: '#9ca3af', font: { size: 12 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9ca3af', font: { size: 12 } }
                        }
                    }
                }
            });
        }

        function loadPayrollBreakdownChart() {
            const ctx = document.getElementById('payroll-breakdown-chart').getContext('2d');
            
            const data = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Basic Pay',
                        data: [250000, 255000, 260000, 265000, 270000, 275000],
                        backgroundColor: '#1e40af',
                        borderRadius: 0
                    },
                    {
                        label: 'Overtime',
                        data: [25000, 28000, 31000, 29000, 32000, 35000],
                        backgroundColor: '#f59e0b',
                        borderRadius: 0
                    },
                    {
                        label: 'Allowances',
                        data: [75000, 75000, 75000, 75000, 75000, 75000],
                        backgroundColor: '#22c55e',
                        borderRadius: 0
                    }
                ]
            };

            if (payrollBreakdownChart) {
                payrollBreakdownChart.destroy();
            }

            payrollBreakdownChart = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: undefined,
                    scales: {
                        x: {
                            stacked: true,
                            grid: { display: false },
                            ticks: { color: '#9ca3af', font: { size: 12 } }
                        },
                        y: {
                            stacked: true,
                            grid: { color: '#f3f4f6' },
                            ticks: { color: '#9ca3af', font: { size: 12 } }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: 12, weight: 600 },
                                color: '#6b7280',
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function loadHMODonutChart() {
            const ctx = document.getElementById('hmo-donut-chart').getContext('2d');
            
            if (hmoDonutChart) {
                hmoDonutChart.destroy();
            }

            hmoDonutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Enrolled', 'Not Enrolled'],
                    datasets: [{
                        data: [420, 105],
                        backgroundColor: ['#1e40af', '#e5e7eb'],
                        borderColor: ['white', 'white'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 12 },
                                color: '#6b7280',
                                padding: 12
                            }
                        }
                    }
                }
            });
        }

        function loadOvertimeChart() {
            const ctx = document.getElementById('overtime-chart').getContext('2d');
            
            if (overtimeChart) {
                overtimeChart.destroy();
            }

            overtimeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing', 'IT', 'Finance', 'HR'],
                    datasets: [{
                        label: 'Overtime Hours',
                        data: [240, 180, 90, 60, 30],
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: { color: '#9ca3af', font: { size: 11 } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: '#9ca3af', font: { size: 12 } }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        function loadAttendanceRingChart() {
            const ctx = document.getElementById('attendance-ring').getContext('2d');
            
            if (attendanceRingChart) {
                attendanceRingChart.destroy();
            }

            attendanceRingChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [95, 5],
                        backgroundColor: ['#22c55e', '#fee2e2'],
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        }

        // ===== INITIALIZE ON PAGE LOAD =====

        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });
    </script>
</body>
</html>
