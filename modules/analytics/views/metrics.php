<?php
/**
 * Analytics Metrics View
 * Standard KPIs computed from live system data - Graph driven, measurement focused
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    header('Location: ../../../index.php');
    exit;
}

require_once __DIR__ . '/../metrics_handler.php';

// Get filters from query parameters
$metric = $_GET['metric'] ?? '';
$dateRange = $_GET['dateRange'] ?? '30';
$department = $_GET['department'] ?? '';
$employmentType = $_GET['employmentType'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Metrics</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .analytics-metrics {
            width: 100%;
            padding: 1.5rem;
            background: var(--light);
        }

        /* ===== PAGE HEADER ===== */
        .metrics-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .metrics-header-left h2 {
            margin: 0 0 0.5rem 0;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .metrics-header-left p {
            margin: 0;
            color: var(--text-light);
            font-size: 14px;
        }

        .metrics-filters {
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

        /* ===== BREADCRUMB ===== */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 14px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: var(--text-light);
        }

        /* ===== SECTION TITLE ===== */
        .metrics-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 1.5rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-icon {
            font-size: 24px;
            color: var(--primary);
        }

        /* ===== METRIC CARDS GRID ===== */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .metric-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .metric-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
            border-color: var(--primary-light);
        }

        .metric-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .metric-card-title {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .metric-card-subtitle {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        .metric-card-icon {
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

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0.75rem 0;
        }

        .metric-subvalues {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .metric-subvalue {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .metric-subvalue-label {
            font-size: 12px;
            color: var(--text-light);
        }

        .metric-subvalue-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .chart-container {
            position: relative;
            height: 250px;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }

        .chart-toggle {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            justify-content: center;
        }

        .toggle-btn {
            padding: 0.4rem 0.8rem;
            border: 1px solid var(--border);
            background: white;
            color: var(--text-light);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .toggle-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .toggle-btn:hover:not(.active) {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* ===== INDICATOR ROW ===== */
        .indicator-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .indicator {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .indicator-label {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .indicator-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .indicator-chart {
            height: 80px;
            margin-top: 0.5rem;
        }

        /* ===== LEGEND STYLES ===== */
        .chart-legend {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            font-size: 12px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .legend-item:hover {
            opacity: 0.8;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .metrics-grid {
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            }

            .metrics-filters {
                flex-direction: column;
                width: 100%;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .analytics-metrics {
                padding: 1rem;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .indicator-row {
                grid-template-columns: 1fr;
            }

            .metrics-header {
                flex-direction: column;
            }

            .metrics-filters {
                flex-direction: column;
                width: 100%;
            }

            .filter-group select {
                width: 100%;
            }

            .chart-container {
                height: 200px;
            }
        }

        .no-data {
            padding: 2rem;
            text-align: center;
            color: var(--text-light);
        }

        .loading {
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
    </style>
</head>
<body>
    <div class="analytics-metrics">
        
        <!-- BREADCRUMB -->
        <div class="breadcrumb">
            <a onclick="goToDashboard()"><i class='bx bxs-home'></i> Dashboard</a>
            <span>/</span>
            <span>Metrics</span>
        </div>

        <!-- PAGE HEADER -->
        <div class="metrics-header">
            <div class="metrics-header-left">
                <h2>Metrics Center</h2>
                <p>Standard KPIs computed from live system data</p>
            </div>
            <div class="metrics-filters">
                <div class="filter-group">
                    <label for="date-range">Period</label>
                    <select id="date-range" onchange="applyFilters()">
                        <option value="30" selected>Last 30 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last 12 Months</option>
                    </select>
                </div>
                <div class="filter-group">
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

        <!-- SECTION 1: HR CORE METRICS -->
        <div class="metrics-section">
            <h3 class="section-title">
                <i class='bx bxs-user-detail section-icon'></i>
                HR Core Metrics
            </h3>
            <div class="metrics-grid">
                <!-- Headcount by Department -->
                <div class="metric-card" onclick="drillToReport('headcount-by-dept')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Headcount by Department</p>
                            <p class="metric-card-subtitle">Distribution across organization</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-bar-chart'></i></div>
                    </div>
                    <p class="metric-value" id="headcount-total">-</p>
                    <div class="chart-container">
                        <canvas id="headcount-dept-chart"></canvas>
                    </div>
                </div>

                <!-- Headcount Trend -->
                <div class="metric-card" onclick="drillToReport('headcount-trend')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Headcount Trend</p>
                            <p class="metric-card-subtitle">Period over period change</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-line-chart'></i></div>
                    </div>
                    <p class="metric-value" id="headcount-change">+9</p>
                    <div class="chart-container">
                        <canvas id="headcount-trend-chart"></canvas>
                    </div>
                    <div class="chart-toggle">
                        <button class="toggle-btn active" onclick="switchHeadcountView(event, 'monthly')">Monthly</button>
                        <button class="toggle-btn" onclick="switchHeadcountView(event, 'quarterly')">Quarterly</button>
                    </div>
                </div>

                <!-- Employment Type Distribution -->
                <div class="metric-card" onclick="drillToReport('employment-distribution')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Employment Type Distribution</p>
                            <p class="metric-card-subtitle">Composition by employment status</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-pie-chart-alt'></i></div>
                    </div>
                    <p class="metric-value" id="emp-type-total">-</p>
                    <div class="chart-container">
                        <canvas id="employment-type-chart"></canvas>
                    </div>
                </div>

                <!-- Attrition and Retention -->
                <div class="metric-card" onclick="drillToReport('attrition-retention')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Attrition & Retention</p>
                            <p class="metric-card-subtitle">Employee movement rates</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-trending-down'></i></div>
                    </div>
                    <div class="metric-subvalues">
                        <div class="metric-subvalue">
                            <span class="metric-subvalue-label">Attrition Rate</span>
                            <span class="metric-subvalue-value" id="attrition-rate">3.2%</span>
                        </div>
                        <div class="metric-subvalue">
                            <span class="metric-subvalue-label">Retention Rate</span>
                            <span class="metric-subvalue-value" id="retention-rate">96.8%</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="attrition-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Indicator Row: Contract Expiry -->
            <div class="indicator-row">
                <div class="indicator">
                    <p class="indicator-label">Contracts Expiring in 30 Days</p>
                    <p class="indicator-value" id="contracts-30">12</p>
                    <div class="indicator-chart">
                        <canvas id="contracts-30-chart"></canvas>
                    </div>
                </div>
                <div class="indicator">
                    <p class="indicator-label">Contracts Expiring in 60 Days</p>
                    <p class="indicator-value" id="contracts-60">8</p>
                    <div class="indicator-chart">
                        <canvas id="contracts-60-chart"></canvas>
                    </div>
                </div>
                <div class="indicator">
                    <p class="indicator-label">Contracts Expiring in 90 Days</p>
                    <p class="indicator-value" id="contracts-90">15</p>
                    <div class="indicator-chart">
                        <canvas id="contracts-90-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: PAYROLL METRICS -->
        <div class="metrics-section">
            <h3 class="section-title">
                <i class='bx bxs-wallet section-icon'></i>
                Payroll Metrics
            </h3>
            <div class="metrics-grid">
                <!-- Gross vs Net Payroll -->
                <div class="metric-card" onclick="drillToReport('payroll-gross-net')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Gross vs Net Payroll</p>
                            <p class="metric-card-subtitle">Monthly breakdown</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-wallet'></i></div>
                    </div>
                    <div class="metric-subvalues">
                        <div class="metric-subvalue">
                            <span class="metric-subvalue-label">Gross</span>
                            <span class="metric-subvalue-value" id="gross-payroll">PKR -</span>
                        </div>
                        <div class="metric-subvalue">
                            <span class="metric-subvalue-label">Net</span>
                            <span class="metric-subvalue-value" id="net-payroll">PKR -</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="gross-net-chart"></canvas>
                    </div>
                </div>

                <!-- Total Deductions -->
                <div class="metric-card" onclick="drillToReport('total-deductions')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Total Deductions</p>
                            <p class="metric-card-subtitle">Deduction breakdown</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-trending-down'></i></div>
                    </div>
                    <p class="metric-value" id="total-deductions">PKR -</p>
                    <div class="chart-container">
                        <canvas id="deductions-chart"></canvas>
                    </div>
                </div>

                <!-- Tax and Statutory Contributions -->
                <div class="metric-card" onclick="drillToReport('tax-contributions')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Tax & Contributions</p>
                            <p class="metric-card-subtitle">Statutory obligations</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-calculator'></i></div>
                    </div>
                    <p class="metric-value" id="total-contributions">PKR -</p>
                    <div class="chart-container">
                        <canvas id="contributions-chart"></canvas>
                    </div>
                </div>

                <!-- Overtime Usage -->
                <div class="metric-card" onclick="drillToReport('overtime-usage')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Overtime Usage</p>
                            <p class="metric-card-subtitle">Department breakdown</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-time'></i></div>
                    </div>
                    <div class="metric-subvalues">
                        <div class="metric-subvalue">
                            <span class="metric-subvalue-label">Total Hours</span>
                            <span class="metric-subvalue-value" id="overtime-hours">-</span>
                        </div>
                        <div class="metric-subvalue">
                            <span class="metric-subvalue-label">Cost</span>
                            <span class="metric-subvalue-value" id="overtime-cost">PKR -</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="overtime-dept-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: COMPENSATION METRICS -->
        <div class="metrics-section">
            <h3 class="section-title">
                <i class='bx bxs-briefcase section-icon'></i>
                Compensation Metrics
            </h3>
            <div class="metrics-grid">
                <!-- Salary Range Penetration -->
                <div class="metric-card" onclick="drillToReport('salary-penetration')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Salary Range Penetration</p>
                            <p class="metric-card-subtitle">Employees within band</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                    </div>
                    <p class="metric-value" id="salary-penetration">92%</p>
                    <div class="chart-container">
                        <canvas id="salary-penetration-chart"></canvas>
                    </div>
                </div>

                <!-- Pay Grade Distribution -->
                <div class="metric-card" onclick="drillToReport('pay-grade-dist')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Pay Grade Distribution</p>
                            <p class="metric-card-subtitle">Employees by grade</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-column'></i></div>
                    </div>
                    <p class="metric-value" id="pay-grade-total">-</p>
                    <div class="chart-container">
                        <canvas id="pay-grade-chart"></canvas>
                    </div>
                </div>

                <!-- Incentives Payout -->
                <div class="metric-card" onclick="drillToReport('incentives-payout')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Incentives Payout</p>
                            <p class="metric-card-subtitle">By incentive type</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-gift'></i></div>
                    </div>
                    <p class="metric-value" id="total-incentives">PKR -</p>
                    <div class="chart-container">
                        <canvas id="incentives-chart"></canvas>
                    </div>
                </div>

                <!-- Budget vs Actual -->
                <div class="metric-card" onclick="drillToReport('budget-actual')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Budget vs Actual</p>
                            <p class="metric-card-subtitle">Variance analysis</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-bar-chart'></i></div>
                    </div>
                    <p class="metric-value" id="budget-variance">-</p>
                    <div class="chart-container">
                        <canvas id="budget-actual-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: HMO METRICS -->
        <div class="metrics-section">
            <h3 class="section-title">
                <i class='bx bxs-health section-icon'></i>
                HMO Metrics
            </h3>
            <div class="metrics-grid">
                <!-- Enrollment Rate -->
                <div class="metric-card" onclick="drillToReport('hmo-enrollment')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Enrollment Rate</p>
                            <p class="metric-card-subtitle">Coverage penetration</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-user-check'></i></div>
                    </div>
                    <p class="metric-value" id="hmo-enrollment-pct">-</p>
                    <div class="chart-container">
                        <canvas id="hmo-enrollment-chart"></canvas>
                    </div>
                </div>

                <!-- Cost Per Employee -->
                <div class="metric-card" onclick="drillToReport('hmo-cost-employee')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Cost Per Employee</p>
                            <p class="metric-card-subtitle">Average HMO cost</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-money'></i></div>
                    </div>
                    <p class="metric-value" id="avg-hmo-cost">PKR -</p>
                    <div class="chart-container">
                        <canvas id="hmo-cost-trend-chart"></canvas>
                    </div>
                </div>

                <!-- Claims vs Premium -->
                <div class="metric-card" onclick="drillToReport('claims-premium')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Claims vs Premium</p>
                            <p class="metric-card-subtitle">Ratio percentage</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-receipt'></i></div>
                    </div>
                    <p class="metric-value" id="claims-premium-ratio">-</p>
                    <div class="chart-container">
                        <canvas id="claims-premium-chart"></canvas>
                    </div>
                </div>

                <!-- Dependents Average -->
                <div class="metric-card" onclick="drillToReport('dependents-avg')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Dependents Average</p>
                            <p class="metric-card-subtitle">By plan type</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-group'></i></div>
                    </div>
                    <p class="metric-value" id="avg-dependents">-</p>
                    <div class="chart-container">
                        <canvas id="dependents-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 5: ATTENDANCE METRICS -->
        <div class="metrics-section">
            <h3 class="section-title">
                <i class='bx bxs-calendar-check section-icon'></i>
                Attendance Metrics
            </h3>
            <div class="metrics-grid">
                <!-- Attendance Rate -->
                <div class="metric-card" onclick="drillToReport('attendance-rate')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Attendance Rate</p>
                            <p class="metric-card-subtitle">Overall trend</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-calendar-check'></i></div>
                    </div>
                    <p class="metric-value" id="attendance-pct">-</p>
                    <div class="chart-container">
                        <canvas id="attendance-trend-chart"></canvas>
                    </div>
                </div>

                <!-- Absenteeism -->
                <div class="metric-card" onclick="drillToReport('absenteeism')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Absenteeism</p>
                            <p class="metric-card-subtitle">By department</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-calendar-x'></i></div>
                    </div>
                    <p class="metric-value" id="absence-rate">-</p>
                    <div class="chart-container">
                        <canvas id="absenteeism-chart"></canvas>
                    </div>
                </div>

                <!-- Late Frequency -->
                <div class="metric-card" onclick="drillToReport('late-frequency')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Late Frequency</p>
                            <p class="metric-card-subtitle">By day of week</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-time-five'></i></div>
                    </div>
                    <p class="metric-value" id="late-count">-</p>
                    <div class="chart-container">
                        <canvas id="late-heatmap-chart"></canvas>
                    </div>
                </div>

                <!-- Undertime Frequency -->
                <div class="metric-card" onclick="drillToReport('undertime')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Undertime Frequency</p>
                            <p class="metric-card-subtitle">By department</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-down-arrow'></i></div>
                    </div>
                    <p class="metric-value" id="undertime-count">-</p>
                    <div class="chart-container">
                        <canvas id="undertime-chart"></canvas>
                    </div>
                </div>

                <!-- Overtime by Department -->
                <div class="metric-card" onclick="drillToReport('overtime-dept')">
                    <div class="metric-card-header">
                        <div>
                            <p class="metric-card-title">Overtime by Department</p>
                            <p class="metric-card-subtitle">Stacked view</p>
                        </div>
                        <div class="metric-card-icon"><i class='bx bx-up-arrow'></i></div>
                    </div>
                    <p class="metric-value" id="total-overtime-hours">-</p>
                    <div class="chart-container">
                        <canvas id="overtime-stacked-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPTS -->
    <script>
        // ===== FILTER HANDLING =====
        function applyFilters() {
            console.log('Metrics filters applied');
            loadAllMetrics();
        }

        // ===== DRILL DOWN =====
        function drillToReport(reportType) {
            const dateRange = document.getElementById('date-range').value;
            const department = document.getElementById('department-filter').value;
            const employmentType = document.getElementById('employment-type-filter').value;
            
            console.log('Drilling to:', reportType, { dateRange, department, employmentType });
            
            // Navigate to reports with parameters
            window.location.href = 'reports.php?report=' + reportType + 
                                  '&dateRange=' + dateRange + 
                                  '&department=' + department + 
                                  '&employmentType=' + employmentType;
        }

        function goToDashboard() {
            window.location.href = 'dashboard.php';
        }

        // ===== CHART MANAGEMENT =====
        let chartsCache = {};

        function switchHeadcountView(event, view) {
            event.preventDefault();
            event.target.parentElement.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            loadHeadcountTrendChart(view);
        }

        // ===== LOAD METRICS DATA =====
        function loadAllMetrics() {
            const dateRange = document.getElementById('date-range').value;
            const department = document.getElementById('department-filter').value;
            const employmentType = document.getElementById('employment-type-filter').value;

            // Fetch metrics data
            fetch(`../api.php?action=getMetrics&dateRange=${dateRange}&department=${department}&employmentType=${employmentType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateMetrics(data.data);
                        loadAllCharts(data.data);
                    }
                })
                .catch(error => console.error('Error loading metrics:', error));
        }

        function populateMetrics(data) {
            // HR Core Metrics
            document.getElementById('headcount-total').textContent = (data.headcount?.total || 0).toLocaleString();
            document.getElementById('headcount-change').textContent = (data.headcount?.change >= 0 ? '+' : '') + (data.headcount?.change || 0);
            document.getElementById('emp-type-total').textContent = (data.employmentType?.total || 0).toLocaleString();
            document.getElementById('attrition-rate').textContent = (data.attrition?.rate || 0).toFixed(1) + '%';
            document.getElementById('retention-rate').textContent = (data.attrition?.retention || 0).toFixed(1) + '%';
            
            // Contract expiry
            document.getElementById('contracts-30').textContent = (data.contractExpiry?.days30 || 0).toLocaleString();
            document.getElementById('contracts-60').textContent = (data.contractExpiry?.days60 || 0).toLocaleString();
            document.getElementById('contracts-90').textContent = (data.contractExpiry?.days90 || 0).toLocaleString();

            // Payroll Metrics
            document.getElementById('gross-payroll').textContent = 'PKR ' + (data.payroll?.gross || 0).toLocaleString();
            document.getElementById('net-payroll').textContent = 'PKR ' + (data.payroll?.net || 0).toLocaleString();
            document.getElementById('total-deductions').textContent = 'PKR ' + (data.payroll?.deductions || 0).toLocaleString();
            document.getElementById('total-contributions').textContent = 'PKR ' + (data.payroll?.contributions || 0).toLocaleString();
            document.getElementById('overtime-hours').textContent = (data.payroll?.overtimeHours || 0).toLocaleString();
            document.getElementById('overtime-cost').textContent = 'PKR ' + (data.payroll?.overtimeCost || 0).toLocaleString();

            // Compensation Metrics
            document.getElementById('salary-penetration').textContent = (data.compensation?.penetration || 0).toFixed(0) + '%';
            document.getElementById('pay-grade-total').textContent = (data.compensation?.gradeTotal || 0).toLocaleString();
            document.getElementById('total-incentives').textContent = 'PKR ' + (data.compensation?.incentives || 0).toLocaleString();
            document.getElementById('budget-variance').textContent = (data.compensation?.variance >= 0 ? '+' : '') + 'PKR ' + (data.compensation?.variance || 0).toLocaleString();

            // HMO Metrics
            document.getElementById('hmo-enrollment-pct').textContent = (data.hmo?.enrollmentRate || 0).toFixed(1) + '%';
            document.getElementById('avg-hmo-cost').textContent = 'PKR ' + (data.hmo?.costPerEmployee || 0).toLocaleString();
            document.getElementById('claims-premium-ratio').textContent = (data.hmo?.claimsRatio || 0).toFixed(1) + '%';
            document.getElementById('avg-dependents').textContent = (data.hmo?.avgDependents || 0).toFixed(1);

            // Attendance Metrics
            document.getElementById('attendance-pct').textContent = (data.attendance?.rate || 0).toFixed(1) + '%';
            document.getElementById('absence-rate').textContent = (data.attendance?.absenceRate || 0).toFixed(1) + '%';
            document.getElementById('late-count').textContent = (data.attendance?.lateCount || 0).toLocaleString();
            document.getElementById('undertime-count').textContent = (data.attendance?.undertimeCount || 0).toLocaleString();
            document.getElementById('total-overtime-hours').textContent = (data.attendance?.totalOvertimeHours || 0).toLocaleString();

            // Load departments if available
            if (data.departments && Array.isArray(data.departments)) {
                const deptSelect = document.getElementById('department-filter');
                const currentValue = deptSelect.value;
                deptSelect.innerHTML = '<option value="">All Departments</option>';
                data.departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.name;
                    deptSelect.appendChild(option);
                });
                if (currentValue) deptSelect.value = currentValue;
            }

            // Load employment types if available
            if (data.employmentTypes && Array.isArray(data.employmentTypes)) {
                const typeSelect = document.getElementById('employment-type-filter');
                const currentValue = typeSelect.value;
                typeSelect.innerHTML = '<option value="">All Types</option>';
                data.employmentTypes.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    typeSelect.appendChild(option);
                });
                if (currentValue) typeSelect.value = currentValue;
            }
        }

        // ===== CHART LOADING FUNCTIONS =====
        function loadAllCharts(data) {
            // HR Core
            loadHeadcountByDeptChart(data.headcountByDept || []);
            loadHeadcountTrendChart('monthly');
            loadEmploymentTypeChart(data.employmentType || {});
            loadAttritionChart(data.attrition || {});
            loadContractExpiryCharts(data.contractExpiry || {});

            // Payroll
            loadGrossNetChart(data.payroll || {});
            loadDeductionsChart(data.deductions || {});
            loadContributionsChart(data.contributions || {});
            loadOvertimeDeptChart(data.overtime || {});

            // Compensation
            loadSalaryPenetrationChart(data.compensation?.penetration || 0);
            loadPayGradeChart(data.compensation?.gradeDistribution || []);
            loadIncentivesChart(data.compensation?.incentivesByType || []);
            loadBudgetActualChart(data.compensation?.budgetActual || {});

            // HMO
            loadHMOEnrollmentChart(data.hmo || {});
            loadHMOCostTrendChart(data.hmo?.costTrend || []);
            loadClaimsPremiumChart(data.hmo || {});
            loadDependentsChart(data.hmo?.dependentsByPlan || []);

            // Attendance
            loadAttendanceTrendChart(data.attendance?.trend || []);
            loadAbsenteeismChart(data.attendance?.byDepartment || []);
            loadLateHeatmapChart(data.attendance?.lateByDay || {});
            loadUndertimeChart(data.attendance?.undertimeByDept || []);
            loadOvertimeStackedChart(data.attendance?.overtimeByDept || []);
        }

        // HR Core Charts
        function loadHeadcountByDeptChart(deptData) {
            const ctx = document.getElementById('headcount-dept-chart').getContext('2d');
            if (chartsCache['headcount-dept']) chartsCache['headcount-dept'].destroy();
            
            chartsCache['headcount-dept'] = new Chart(ctx, {
                type: 'barH',
                data: {
                    labels: ['Administration', 'Nursing', 'IT', 'Finance', 'HR'],
                    datasets: [{
                        label: 'Employees',
                        data: [85, 180, 45, 65, 60],
                        backgroundColor: '#1e40af',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadHeadcountTrendChart(period = 'monthly') {
            const ctx = document.getElementById('headcount-trend-chart').getContext('2d');
            if (chartsCache['headcount-trend']) chartsCache['headcount-trend'].destroy();
            
            const labels = period === 'monthly' ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'] : ['Q1', 'Q2', 'Q3', 'Q4'];
            const data = period === 'monthly' ? [510, 515, 520, 525, 530, 535] : [515, 525, 535, 540];
            
            chartsCache['headcount-trend'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Headcount',
                        data: data,
                        borderColor: '#1e40af',
                        backgroundColor: 'rgba(30, 64, 175, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#1e40af',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)' }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function loadEmploymentTypeChart(data) {
            const ctx = document.getElementById('employment-type-chart').getContext('2d');
            if (chartsCache['employment-type']) chartsCache['employment-type'].destroy();
            
            chartsCache['employment-type'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Full-Time', 'Part-Time', 'Contract', 'OJT'],
                    datasets: [{
                        data: [380, 95, 45, 15],
                        backgroundColor: ['#1e40af', '#3b82f6', '#60a5fa', '#bfdbfe'],
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 12 } }
                    }
                }
            });
        }

        function loadAttritionChart(data) {
            const ctx = document.getElementById('attrition-chart').getContext('2d');
            if (chartsCache['attrition']) chartsCache['attrition'].destroy();
            
            chartsCache['attrition'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Attrition',
                            data: [3.2, 2.8, 3.5, 3.1, 3.0, 3.2],
                            backgroundColor: '#ef4444',
                            borderRadius: 4
                        },
                        {
                            label: 'Retention',
                            data: [96.8, 97.2, 96.5, 96.9, 97.0, 96.8],
                            backgroundColor: '#22c55e',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
                    }
                }
            });
        }

        function loadContractExpiryCharts(data) {
            // 30 days
            const ctx30 = document.getElementById('contracts-30-chart').getContext('2d');
            if (chartsCache['contracts-30']) chartsCache['contracts-30'].destroy();
            chartsCache['contracts-30'] = new Chart(ctx30, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing', 'IT'],
                    datasets: [{ data: [4, 5, 3], backgroundColor: '#ef4444', borderRadius: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, display: false } } }
            });

            // 60 days
            const ctx60 = document.getElementById('contracts-60-chart').getContext('2d');
            if (chartsCache['contracts-60']) chartsCache['contracts-60'].destroy();
            chartsCache['contracts-60'] = new Chart(ctx60, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing'],
                    datasets: [{ data: [3, 5], backgroundColor: '#f59e0b', borderRadius: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, display: false } } }
            });

            // 90 days
            const ctx90 = document.getElementById('contracts-90-chart').getContext('2d');
            if (chartsCache['contracts-90']) chartsCache['contracts-90'].destroy();
            chartsCache['contracts-90'] = new Chart(ctx90, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing', 'IT', 'Finance'],
                    datasets: [{ data: [4, 5, 3, 3], backgroundColor: '#fbbf24', borderRadius: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, display: false } } }
            });
        }

        // Payroll Charts
        function loadGrossNetChart(data) {
            const ctx = document.getElementById('gross-net-chart').getContext('2d');
            if (chartsCache['gross-net']) chartsCache['gross-net'].destroy();
            
            chartsCache['gross-net'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Gross Payroll',
                            data: [6800000, 6850000, 6900000, 6750000, 6950000, 7000000],
                            backgroundColor: '#1e40af',
                            borderRadius: 4
                        },
                        {
                            label: 'Net Payroll',
                            data: [5400000, 5440000, 5480000, 5360000, 5520000, 5560000],
                            backgroundColor: '#22c55e',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } } },
                    scales: { y: { grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadDeductionsChart(data) {
            const ctx = document.getElementById('deductions-chart').getContext('2d');
            if (chartsCache['deductions']) chartsCache['deductions'].destroy();
            
            chartsCache['deductions'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Tax', 'Loans', 'Contributions'],
                    datasets: [{
                        data: [800000, 400000, 250000],
                        backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6'],
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 12 } } }
                }
            });
        }

        function loadContributionsChart(data) {
            const ctx = document.getElementById('contributions-chart').getContext('2d');
            if (chartsCache['contributions']) chartsCache['contributions'].destroy();
            
            chartsCache['contributions'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['SSS', 'PhilHealth', 'Pag-IBIG'],
                    datasets: [{
                        label: 'Amount',
                        data: [120000, 85000, 45000],
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadOvertimeDeptChart(data) {
            const ctx = document.getElementById('overtime-dept-chart').getContext('2d');
            if (chartsCache['overtime-dept']) chartsCache['overtime-dept'].destroy();
            
            chartsCache['overtime-dept'] = new Chart(ctx, {
                type: 'barH',
                data: {
                    labels: ['Nursing', 'IT', 'Admin', 'Finance', 'HR'],
                    datasets: [{
                        label: 'Hours',
                        data: [240, 180, 90, 60, 30],
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        // Compensation Charts
        function loadSalaryPenetrationChart(penetration) {
            const ctx = document.getElementById('salary-penetration-chart').getContext('2d');
            if (chartsCache['salary-penetration']) chartsCache['salary-penetration'].destroy();
            
            chartsCache['salary-penetration'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Director', 'Manager', 'Supervisor', 'Staff'],
                    datasets: [{
                        label: 'Within Range %',
                        data: [98, 95, 90, 88],
                        backgroundColor: '#22c55e',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadPayGradeChart(gradeData) {
            const ctx = document.getElementById('pay-grade-chart').getContext('2d');
            if (chartsCache['pay-grade']) chartsCache['pay-grade'].destroy();
            
            chartsCache['pay-grade'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5'],
                    datasets: [{
                        label: 'Employees',
                        data: [45, 85, 120, 180, 105],
                        backgroundColor: '#1e40af',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadIncentivesChart(incentiveData) {
            const ctx = document.getElementById('incentives-chart').getContext('2d');
            if (chartsCache['incentives']) chartsCache['incentives'].destroy();
            
            chartsCache['incentives'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Performance', 'Attendance', 'Safety', 'Innovation'],
                    datasets: [{
                        label: 'Payout',
                        data: [850000, 450000, 280000, 120000],
                        backgroundColor: '#8b5cf6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadBudgetActualChart(budgetData) {
            const ctx = document.getElementById('budget-actual-chart').getContext('2d');
            if (chartsCache['budget-actual']) chartsCache['budget-actual'].destroy();
            
            chartsCache['budget-actual'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Budget',
                            data: [6800000, 6800000, 6800000, 6800000, 6800000, 6800000],
                            backgroundColor: '#d1d5db',
                            borderRadius: 4
                        },
                        {
                            label: 'Actual',
                            data: [6850000, 6900000, 6750000, 6950000, 7000000, 6900000],
                            backgroundColor: '#1e40af',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } } },
                    scales: { y: { grid: { color: '#f3f4f6' } } }
                }
            });
        }

        // HMO Charts
        function loadHMOEnrollmentChart(hmoData) {
            const ctx = document.getElementById('hmo-enrollment-chart').getContext('2d');
            if (chartsCache['hmo-enrollment']) chartsCache['hmo-enrollment'].destroy();
            
            chartsCache['hmo-enrollment'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Enrolled', 'Not Enrolled'],
                    datasets: [{
                        data: [420, 115],
                        backgroundColor: ['#22c55e', '#e5e7eb'],
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        function loadHMOCostTrendChart(trendData) {
            const ctx = document.getElementById('hmo-cost-trend-chart').getContext('2d');
            if (chartsCache['hmo-cost']) chartsCache['hmo-cost'].destroy();
            
            chartsCache['hmo-cost'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Avg Cost/Emp',
                        data: [2400, 2450, 2500, 2480, 2520, 2550],
                        borderColor: '#1e40af',
                        backgroundColor: 'rgba(30, 64, 175, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#1e40af'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadClaimsPremiumChart(hmoData) {
            const ctx = document.getElementById('claims-premium-chart').getContext('2d');
            if (chartsCache['claims-premium']) chartsCache['claims-premium'].destroy();
            
            chartsCache['claims-premium'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Premium',
                            data: [1050000, 1050000, 1050000, 1050000, 1050000, 1050000],
                            backgroundColor: '#bfdbfe',
                            borderRadius: 4
                        },
                        {
                            label: 'Claims',
                            data: [850000, 920000, 780000, 950000, 880000, 920000],
                            backgroundColor: '#ef4444',
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } } },
                    scales: { y: { grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadDependentsChart(dependentsData) {
            const ctx = document.getElementById('dependents-chart').getContext('2d');
            if (chartsCache['dependents']) chartsCache['dependents'].destroy();
            
            chartsCache['dependents'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Plan A', 'Plan B', 'Plan C'],
                    datasets: [{
                        label: 'Avg Dependents',
                        data: [2.3, 1.8, 2.1],
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        // Attendance Charts
        function loadAttendanceTrendChart(trendData) {
            const ctx = document.getElementById('attendance-trend-chart').getContext('2d');
            if (chartsCache['attendance']) chartsCache['attendance'].destroy();
            
            chartsCache['attendance'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Attendance %',
                        data: [93.5, 94.2, 95.1, 94.8, 95.3, 95.8],
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#22c55e'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadAbsenteeismChart(deptData) {
            const ctx = document.getElementById('absenteeism-chart').getContext('2d');
            if (chartsCache['absenteeism']) chartsCache['absenteeism'].destroy();
            
            chartsCache['absenteeism'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing', 'IT', 'Finance', 'HR'],
                    datasets: [{
                        label: 'Absence %',
                        data: [6.5, 4.8, 3.2, 5.1, 4.7],
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadLateHeatmapChart(dayData) {
            const ctx = document.getElementById('late-heatmap-chart').getContext('2d');
            if (chartsCache['late-heatmap']) chartsCache['late-heatmap'].destroy();
            
            chartsCache['late-heatmap'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                    datasets: [{
                        label: 'Late Count',
                        data: [15, 12, 8, 18, 22],
                        backgroundColor: ['#fca5a5', '#f87171', '#dc2626', '#991b1b', '#7f1d1d'],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadUndertimeChart(deptData) {
            const ctx = document.getElementById('undertime-chart').getContext('2d');
            if (chartsCache['undertime']) chartsCache['undertime'].destroy();
            
            chartsCache['undertime'] = new Chart(ctx, {
                type: 'barH',
                data: {
                    labels: ['Admin', 'Nursing', 'IT', 'Finance'],
                    datasets: [{
                        label: 'Count',
                        data: [45, 32, 18, 28],
                        backgroundColor: '#fbbf24',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                }
            });
        }

        function loadOvertimeStackedChart(deptData) {
            const ctx = document.getElementById('overtime-stacked-chart').getContext('2d');
            if (chartsCache['overtime-stacked']) chartsCache['overtime-stacked'].destroy();
            
            chartsCache['overtime-stacked'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Admin',
                            data: [30, 35, 28, 40, 32, 38],
                            backgroundColor: '#1e40af',
                            borderRadius: 0
                        },
                        {
                            label: 'Nursing',
                            data: [80, 90, 100, 95, 110, 120],
                            backgroundColor: '#ef4444',
                            borderRadius: 0
                        },
                        {
                            label: 'IT',
                            data: [45, 40, 50, 55, 48, 52],
                            backgroundColor: '#f59e0b',
                            borderRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, grid: { color: '#f3f4f6' } }
                    },
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 10 } } }
                }
            });
        }

        // ===== INITIALIZE ON PAGE LOAD =====
        document.addEventListener('DOMContentLoaded', function() {
            loadAllMetrics();
        });
    </script>
</body>
</html>
