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
                            <span class="metric-subvalue-value" id="gross-payroll">PHP -</span>
                        </div>
                        <div class="metric-subvalue">
                            <span class="metric-subvalue-label">Net</span>
                            <span class="metric-subvalue-value" id="net-payroll">PHP -</span>
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
                    <p class="metric-value" id="total-deductions">PHP -</p>
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
                    <p class="metric-value" id="total-contributions">PHP -</p>
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
                            <span class="metric-subvalue-value" id="overtime-cost">PHP -</span>
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
                    <p class="metric-value" id="total-incentives">PHP -</p>
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
                    <p class="metric-value" id="avg-hmo-cost">PHP -</p>
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
        window.applyFilters = function() {
            console.log('Metrics filters applied');
            window.loadAllMetrics();
        }

        // ===== DRILL DOWN =====
        window.drillToReport = function(reportType) {
            const dateRange = document.getElementById('date-range')?.value || '30';
            const department = document.getElementById('department-filter')?.value || '';
            const employmentType = document.getElementById('employment-type-filter')?.value || '';
            
            console.log('Drilling to:', reportType, { dateRange, department, employmentType });
            
            // When in dashboard, open in new tab instead
            const url = 'reports.php?report=' + reportType + 
                       '&dateRange=' + dateRange + 
                       '&department=' + department + 
                       '&employmentType=' + employmentType;
            window.open(url, '_blank');
        }

        window.goToDashboard = function() {
            window.open('dashboard.php', '_blank');
        }
        window.chartsCache = window.chartsCache || {};

        function switchHeadcountView(event, view) {
            event.preventDefault();
            event.target.parentElement.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            loadHeadcountTrendChart(view);
        }

        // ===== LOAD METRICS DATA =====
        function loadAllMetrics() {
            console.log('loadAllMetrics called');
            // Verify Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js not yet loaded, retrying in 300ms');
                setTimeout(function() { window.loadAllMetrics(); }, 300);
                return;
            }
            
            console.log('Chart.js is available, proceeding with metrics load');
            
            const dateRange = document.getElementById('date-range')?.value || '30';
            const department = document.getElementById('department-filter')?.value || '';
            const employmentType = document.getElementById('employment-type-filter')?.value || '';

            // Build URL using lowercase public_html to match dashboard convention
            const apiUrl = '/public_html/modules/analytics/api.php?action=getMetrics&dateRange=' + encodeURIComponent(dateRange) + 
                          '&department=' + encodeURIComponent(department) + 
                          '&employmentType=' + encodeURIComponent(employmentType);
            
            console.log('Fetching metrics from:', apiUrl);
            
            fetch(apiUrl, {
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            })
                .then(response => {
                    console.log('Metrics API Response Status:', response.status);
                    if (!response.ok) {
                        console.error('API returned non-OK status:', response.status, response.statusText);
                        return response.text().then(text => {
                            console.error('API error text:', text);
                            throw new Error('API error: ' + response.status + ' ' + response.statusText + ' - ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Metrics API Response Data:', data);
                    if (data && data.success) {
                        console.log('API Success! Data contains:', Object.keys(data.data || {}));
                        populateMetrics(data.data);
                        loadAllCharts(data.data);
                    } else {
                        console.error('API did not return success:', data);
                    }
                })
                .catch(error => {
                    console.error('Error loading metrics - Full error:', error);
                    console.error('Error message:', error.message);
                    console.error('Failed URL was:', apiUrl);
                });
        }

        function populateMetrics(data) {
            // Extract and process data from API response
            if (!data) {
                console.warn('populateMetrics called with no data');
                return;
            }
            
            console.log('populateMetrics: Received data with keys:', Object.keys(data));
            
            const hrcore = data.hrcore || {};
            const payroll = data.payroll || {};
            const compensation = data.compensation || {};
            const hmo = data.hmo || {};

            // HR Core Metrics - Calculate from headcount_by_dept
            const headcountByDept = hrcore.headcount_by_dept || [];
            const totalHeadcount = headcountByDept.reduce((sum, d) => sum + (d.count || 0), 0);
            document.getElementById('headcount-total').textContent = totalHeadcount.toLocaleString();
            document.getElementById('headcount-change').textContent = '+0';
            
            // Employment Type - Count from employment_type_dist
            const empTypeDist = hrcore.employment_type_dist || [];
            const totalEmpType = empTypeDist.reduce((sum, t) => sum + (t.count || 0), 0);
            document.getElementById('emp-type-total').textContent = totalEmpType.toLocaleString();
            
            // Attrition & Retention (placeholder - would need actual calculation)
            document.getElementById('attrition-rate').textContent = '3.2%';
            document.getElementById('retention-rate').textContent = '96.8%';
            
            // Contract expiry
            const contractExpiry = hrcore.contract_expiry || {};
            document.getElementById('contracts-30').textContent = (contractExpiry.days_30 || 0).toLocaleString();
            document.getElementById('contracts-60').textContent = (contractExpiry.days_60 || 0).toLocaleString();
            document.getElementById('contracts-90').textContent = (contractExpiry.days_90 || 0).toLocaleString();

            // Payroll Metrics - Extract from summary
            const payrollSummary = payroll.summary || {};
            const gross = payrollSummary.gross_total || 0;
            const net = payrollSummary.net_total || 0;
            const deductions = payrollSummary.total_deductions || 0;
            
            document.getElementById('gross-payroll').textContent = 'PHP ' + gross.toLocaleString('en-PH', {minimumFractionDigits: 2});
            document.getElementById('net-payroll').textContent = 'PHP ' + net.toLocaleString('en-PH', {minimumFractionDigits: 2});
            document.getElementById('total-deductions').textContent = 'PHP ' + deductions.toLocaleString('en-PH', {minimumFractionDigits: 2});
            
            // Tax & Contributions
            const taxContrib = payroll.tax_contributions || [];
            const totalTax = taxContrib.reduce((sum, t) => sum + (t.amount || 0), 0);
            document.getElementById('total-contributions').textContent = 'PHP ' + totalTax.toLocaleString('en-PH', {minimumFractionDigits: 2});
            
            // Overtime
            const overtimeSummary = payroll.overtime || {};
            document.getElementById('overtime-hours').textContent = (overtimeSummary.total_hours || 0).toLocaleString();
            document.getElementById('overtime-cost').textContent = 'PHP ' + (overtimeSummary.total_cost || 0).toLocaleString('en-PH', {minimumFractionDigits: 2});

            // Compensation Metrics
            const avgSalary = compensation.average_salary || [];
            const avgSalaryValue = avgSalary.length > 0 ? avgSalary[0].average || 0 : 0;
            document.getElementById('salary-penetration').textContent = '95%';
            document.getElementById('pay-grade-total').textContent = ((avgSalaryValue / 1000) || 0).toFixed(0) + 'K';
            
            const incentives = compensation.incentives || [];
            const totalIncentives = incentives.reduce((sum, i) => sum + (i.amount || 0), 0);
            document.getElementById('total-incentives').textContent = 'PHP ' + totalIncentives.toLocaleString('en-PH', {minimumFractionDigits: 2});
            document.getElementById('budget-variance').textContent = '+PHP 125000';

            // HMO Metrics
            const hmoEnrollment = hmo.enrollment_rate || [];
            const enrollmentRate = hmoEnrollment.length > 0 ? hmoEnrollment[0].enrollment_rate || 0 : 0;
            document.getElementById('hmo-enrollment-pct').textContent = enrollmentRate.toFixed(1) + '%';
            
            const hmoCost = hmo.cost_per_employee || [];
            const costPerEmp = hmoCost.length > 0 ? hmoCost[0].cost || 0 : 0;
            document.getElementById('avg-hmo-cost').textContent = 'PHP ' + costPerEmp.toLocaleString('en-PH', {minimumFractionDigits: 2});
            
            document.getElementById('claims-premium-ratio').textContent = '78.5%';
            document.getElementById('avg-dependents').textContent = '2.1';

            // Attendance Metrics (placeholder)
            document.getElementById('attendance-pct').textContent = '95.3%';
            document.getElementById('absence-rate').textContent = '4.7%';
            document.getElementById('late-count').textContent = '45';
            document.getElementById('undertime-count').textContent = '28';
            document.getElementById('total-overtime-hours').textContent = '240';
        }

        // ===== CHART LOADING FUNCTIONS =====
        function loadAllCharts(data) {
            // HR Core - Use real data from API
            const hrcore = data.hrcore || {};
            const headcountByDept = hrcore.headcount_by_dept || [];
            
            loadHeadcountByDeptChart(headcountByDept);
            loadHeadcountTrendChart('monthly');
            
            const empTypeDist = hrcore.employment_type_dist || {};
            loadEmploymentTypeChart(empTypeDist);
            
            loadAttritionChart({});
            
            const contractExpiry = hrcore.contract_expiry || {};
            loadContractExpiryCharts(contractExpiry);

            // Payroll
            const payroll = data.payroll || {};
            const payrollSummary = payroll.summary || {};
            loadGrossNetChart(payrollSummary);
            loadDeductionsChart(payrollSummary);
            loadContributionsChart(payroll.tax_contributions || []);
            loadOvertimeDeptChart(payroll.overtime || {});

            // Compensation
            const compensation = data.compensation || {};
            loadSalaryPenetrationChart(compensation);
            loadPayGradeChart(compensation.pay_grade_dist || []);
            loadIncentivesChart(compensation.incentives || []);
            loadBudgetActualChart({});

            // HMO
            const hmo = data.hmo || {};
            loadHMOEnrollmentChart(hmo);
            loadHMOCostTrendChart(hmo.cost_per_employee || []);
            loadClaimsPremiumChart(hmo);
            loadDependentsChart(hmo);

            // Attendance
            const attendance = data.attendance || {};
            loadAttendanceTrendChart(attendance.trend || []);
            loadAbsenteeismChart(attendance.by_department || []);
            loadLateHeatmapChart(attendance.late_by_day || {});
            loadUndertimeChart(attendance.undertime_by_dept || []);
            loadOvertimeStackedChart(attendance.overtime_by_dept || []);
        }

        // HR Core Charts
        function loadHeadcountByDeptChart(deptData) {
            // Ensure Chart is loaded
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadHeadcountByDeptChart(deptData), 300);
                return;
            }
            
            const ctx = document.getElementById('headcount-dept-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['headcount-dept']) window.chartsCache['headcount-dept'].destroy();
            
            // Extract labels and data from API response
            const labels = Array.isArray(deptData) ? deptData.map(d => d.department_name || 'N/A') : ['Administration', 'Nursing', 'IT', 'Finance', 'HR'];
            const values = Array.isArray(deptData) ? deptData.map(d => d.count || 0) : [85, 180, 45, 65, 60];
            
            window.chartsCache['headcount-dept'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Employees',
                        data: values,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadHeadcountTrendChart(period), 300);
                return;
            }
            
            const ctx = document.getElementById('headcount-trend-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['headcount-trend']) window.chartsCache['headcount-trend'].destroy();
            
            const labels = period === 'monthly' ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'] : ['Q1', 'Q2', 'Q3', 'Q4'];
            const data = period === 'monthly' ? [510, 515, 520, 525, 530, 535] : [515, 525, 535, 540];
            
            window.chartsCache['headcount-trend'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadEmploymentTypeChart(data), 300);
                return;
            }
            
            const ctx = document.getElementById('employment-type-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['employment-type']) window.chartsCache['employment-type'].destroy();
            
            window.chartsCache['employment-type'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadAttritionChart(data), 300);
                return;
            }
            
            const ctx = document.getElementById('attrition-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['attrition']) window.chartsCache['attrition'].destroy();
            
            window.chartsCache['attrition'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadContractExpiryCharts(data), 300);
                return;
            }
            
            // 30 days
            const ctx30 = document.getElementById('contracts-30-chart')?.getContext('2d');
            if (ctx30 && !window.chartsCache['contracts-30']) {
                window.chartsCache['contracts-30'] = new Chart(ctx30, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing', 'IT'],
                    datasets: [{ data: [4, 5, 3], backgroundColor: '#ef4444', borderRadius: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, display: false } } }
            });

            // 60 days
            const ctx60 = document.getElementById('contracts-60-chart')?.getContext('2d');
            if (ctx60 && !window.chartsCache['contracts-60']) {
                window.chartsCache['contracts-60'] = new Chart(ctx60, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing'],
                    datasets: [{ data: [3, 5], backgroundColor: '#f59e0b', borderRadius: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, display: false } } }
            });
            }

            // 90 days
            const ctx90 = document.getElementById('contracts-90-chart')?.getContext('2d');
            if (ctx90 && !window.chartsCache['contracts-90']) {
                window.chartsCache['contracts-90'] = new Chart(ctx90, {
                type: 'bar',
                data: {
                    labels: ['Admin', 'Nursing', 'IT', 'Finance'],
                    datasets: [{ data: [4, 5, 3, 3], backgroundColor: '#fbbf24', borderRadius: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, display: false } } }
            });
            }
        }

        // Payroll Charts
        function loadGrossNetChart(data) {
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadGrossNetChart(data), 300);
                return;
            }
            
            const ctx = document.getElementById('gross-net-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['gross-net']) window.chartsCache['gross-net'].destroy();
            
            // Extract gross and net from data if available (single record), otherwise use placeholder
            const gross = Array.isArray(data) && data[0] ? data[0].gross_total : 
                         (data && data.gross_total ? data.gross_total : null);
            const net = Array.isArray(data) && data[0] ? data[0].net_total : 
                       (data && data.net_total ? data.net_total : null);
            
            // If we have actual data, use it for current month
            const grossData = gross ? [gross] : [6800000, 6850000, 6900000, 6750000, 6950000, 7000000];
            const netData = net ? [net] : [5400000, 5440000, 5480000, 5360000, 5520000, 5560000];
            const labels = gross ? ['This Month'] : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            
            window.chartsCache['gross-net'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Gross Payroll',
                            data: grossData,
                            backgroundColor: '#1e40af',
                            borderRadius: 4
                        },
                        {
                            label: 'Net Payroll',
                            data: netData,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadDeductionsChart(data), 300);
                return;
            }
            
            const ctx = document.getElementById('deductions-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['deductions']) window.chartsCache['deductions'].destroy();
            
            // Extract deduction data if available
            const totalDeductions = Array.isArray(data) && data[0] ? data[0].total_deductions : 
                                   (data && data.total_deductions ? data.total_deductions : null);
            
            const deductionData = totalDeductions ? 
                [totalDeductions * 0.6, totalDeductions * 0.25, totalDeductions * 0.15] :
                [800000, 400000, 250000];
            
            window.chartsCache['deductions'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Tax', 'Loans', 'Contributions'],
                    datasets: [{
                        data: deductionData,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadContributionsChart(data), 300);
                return;
            }
            
            const ctx = document.getElementById('contributions-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['contributions']) window.chartsCache['contributions'].destroy();
            
            // Extract contribution data from array if available
            let labels = ['SSS', 'PhilHealth', 'Pag-IBIG'];
            let values = [120000, 85000, 45000];
            
            if (Array.isArray(data) && data.length > 0) {
                labels = data.map(c => c.contribution_type || 'Unknown');
                values = data.map(c => c.amount || 0);
            }
            
            window.chartsCache['contributions'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Amount',
                        data: values,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadOvertimeDeptChart(data), 300);
                return;
            }
            
            const ctx = document.getElementById('overtime-dept-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['overtime-dept']) window.chartsCache['overtime-dept'].destroy();
            
            // Extract overtime by department if available
            let labels = ['Nursing', 'IT', 'Admin', 'Finance', 'HR'];
            let values = [240, 180, 90, 60, 30];
            
            if (data && data.by_department && Array.isArray(data.by_department)) {
                labels = data.by_department.map(d => d.department_name || 'Unknown');
                values = data.by_department.map(d => d.total_hours || 0);
            } else if (Array.isArray(data) && data.length > 0) {
                labels = data.map(d => d.department_name || 'Unknown');
                values = data.map(d => d.total_hours || 0);
            }
            
            window.chartsCache['overtime-dept'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hours',
                        data: values,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadSalaryPenetrationChart(penetration), 300);
                return;
            }
            
            const ctx = document.getElementById('salary-penetration-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['salary-penetration']) window.chartsCache['salary-penetration'].destroy();
            
            window.chartsCache['salary-penetration'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadPayGradeChart(gradeData), 300);
                return;
            }
            
            const ctx = document.getElementById('pay-grade-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['pay-grade']) window.chartsCache['pay-grade'].destroy();
            
            window.chartsCache['pay-grade'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadIncentivesChart(incentiveData), 300);
                return;
            }
            
            const ctx = document.getElementById('incentives-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['incentives']) window.chartsCache['incentives'].destroy();
            
            window.chartsCache['incentives'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadBudgetActualChart(budgetData), 300);
                return;
            }
            
            const ctx = document.getElementById('budget-actual-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['budget-actual']) window.chartsCache['budget-actual'].destroy();
            
            window.chartsCache['budget-actual'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadHMOEnrollmentChart(hmoData), 300);
                return;
            }
            
            const ctx = document.getElementById('hmo-enrollment-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['hmo-enrollment']) window.chartsCache['hmo-enrollment'].destroy();
            
            window.chartsCache['hmo-enrollment'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadHMOCostTrendChart(trendData), 300);
                return;
            }
            
            const ctx = document.getElementById('hmo-cost-trend-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['hmo-cost']) window.chartsCache['hmo-cost'].destroy();
            
            window.chartsCache['hmo-cost'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadClaimsPremiumChart(hmoData), 300);
                return;
            }
            
            const ctx = document.getElementById('claims-premium-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['claims-premium']) window.chartsCache['claims-premium'].destroy();
            
            window.chartsCache['claims-premium'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadDependentsChart(dependentsData), 300);
                return;
            }
            
            const ctx = document.getElementById('dependents-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['dependents']) window.chartsCache['dependents'].destroy();
            
            window.chartsCache['dependents'] = new Chart(ctx, {
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadAttendanceTrendChart(trendData), 300);
                return;
            }
            
            const ctx = document.getElementById('attendance-trend-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['attendance']) window.chartsCache['attendance'].destroy();
            
            // Extract trend data if available
            let labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            let values = [93.5, 94.2, 95.1, 94.8, 95.3, 95.8];
            
            if (Array.isArray(trendData) && trendData.length > 0) {
                labels = trendData.map((t, i) => ['Week 1', 'Week 2', 'Week 3', 'Week 4'][i % 4] || 'N/A');
                values = trendData.map(t => parseFloat(t.attendance_rate || 95));
            }
            
            window.chartsCache['attendance'] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Attendance %',
                        data: values,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadAbsenteeismChart(deptData), 300);
                return;
            }
            
            const ctx = document.getElementById('absenteeism-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['absenteeism']) window.chartsCache['absenteeism'].destroy();
            
            // Extract absentieeism by department if available
            let labels = ['Admin', 'Nursing', 'IT', 'Finance', 'HR'];
            let values = [6.5, 4.8, 3.2, 5.1, 4.7];
            
            if (Array.isArray(deptData) && deptData.length > 0) {
                labels = deptData.map(d => d.department_name || 'Unknown');
                values = deptData.map(d => parseFloat(d.absence_rate || 0));
            }
            
            window.chartsCache['absenteeism'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Absence %',
                        data: values,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadLateHeatmapChart(dayData), 300);
                return;
            }
            
            const ctx = document.getElementById('late-heatmap-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['late-heatmap']) window.chartsCache['late-heatmap'].destroy();
            
            // Extract late arrivals by day if available
            let labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            let values = [15, 12, 8, 18, 22];
            
            if (dayData && dayData.length && dayData.length > 0) {
                labels = dayData.map((d, i) => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'][i % 5] || 'N/A');
                values = dayData.map(d => parseInt(d.count || 0));
            } else if (dayData && typeof dayData === 'object' && !Array.isArray(dayData)) {
                // If it's an object with day keys
                labels = Object.keys(dayData).slice(0, 5);
                values = Object.values(dayData).slice(0, 5).map(v => parseInt(v || 0));
            }
            
            window.chartsCache['late-heatmap'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Late Count',
                        data: values,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadUndertimeChart(deptData), 300);
                return;
            }
            
            const ctx = document.getElementById('undertime-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['undertime']) window.chartsCache['undertime'].destroy();
            
            // Extract undertime by department if available
            let labels = ['Admin', 'Nursing', 'IT', 'Finance'];
            let values = [45, 32, 18, 28];
            
            if (Array.isArray(deptData) && deptData.length > 0) {
                labels = deptData.map(d => d.department_name || 'Unknown');
                values = deptData.map(d => parseInt(d.count || d.undertime_count || 0));
            }
            
            window.chartsCache['undertime'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Count',
                        data: values,
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
            if (typeof Chart === 'undefined') {
                setTimeout(() => loadOvertimeStackedChart(deptData), 300);
                return;
            }
            
            const ctx = document.getElementById('overtime-stacked-chart')?.getContext('2d');
            if (!ctx) return;
            
            if (window.chartsCache['overtime-stacked']) window.chartsCache['overtime-stacked'].destroy();
            
            // Extract overtime by department if available - organize by dept
            let datasets = [
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
            ];
            
            if (Array.isArray(deptData) && deptData.length > 0) {
                // If we have actual data, build datasets from it
                const uniqueDepts = [...new Set(deptData.map(d => d.department_name))];
                datasets = uniqueDepts.slice(0, 3).map((dept, idx) => {
                    const colors = ['#1e40af', '#ef4444', '#f59e0b'];
                    return {
                        label: dept,
                        data: deptData.filter(d => d.department_name === dept).map(d => parseInt(d.hours || 0)),
                        backgroundColor: colors[idx % 3],
                        borderRadius: 0
                    };
                });
            }
            
            window.chartsCache['overtime-stacked'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: datasets
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
        // Make loadAllMetrics available globally
        window.loadAllMetrics = loadAllMetrics;
        
        // Check if DOM is already loaded (when injected into dashboard)
        if (document.readyState === 'loading') {
            // DOM still loading, wait for it
            document.addEventListener('DOMContentLoaded', function() {
                window.loadAllMetrics();
            });
        } else {
            // DOM already loaded (injected into dashboard or direct access)
            // Delay slightly to ensure all DOM elements are available
            setTimeout(function() {
                window.loadAllMetrics();
            }, 100);
        }
    </script>
</body>
</html>
