<?php
/**
 * Analytics Reports View
 * Detailed records for audit, analysis and submission - Table and export driven
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    header('Location: ../../../index.php');
    exit;
}

require_once __DIR__ . '/../reports_handler.php';

// Get report type from query parameters
$reportType = $_GET['report'] ?? '';
$dateRange = $_GET['dateRange'] ?? '30';
$department = $_GET['department'] ?? '';
$employmentType = $_GET['employmentType'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Reports</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .analytics-reports {
            width: 100%;
            height: 100%;
            background: var(--light);
            display: flex;
            flex-direction: column;
        }

        /* ===== TOP BAR ===== */
        .reports-top-bar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .reports-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .reports-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .control-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .control-group select,
        .control-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: white;
            font-size: 13px;
            color: var(--text-dark);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .control-group select:hover,
        .control-btn:hover {
            border-color: var(--primary);
        }

        .control-group select:focus,
        .control-btn:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 0.75rem;
        }

        .btn-primary {
            padding: 0.5rem 1.25rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            box-shadow: 0 2px 8px rgba(30, 64, 175, 0.25);
        }

        .btn-secondary {
            padding: 0.5rem 1.25rem;
            background: white;
            color: var(--primary);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: var(--light);
            border-color: var(--primary);
        }

        /* ===== MAIN CONTAINER ===== */
        .reports-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* ===== LEFT SIDEBAR ===== */
        .reports-sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--border);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .reports-sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-title {
            margin: 0 0 1rem 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .reports-list {
            flex: 1;
            overflow-y: auto;
        }

        .report-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .report-item:hover {
            background: var(--light);
        }

        .report-item.active {
            background: rgba(30, 64, 175, 0.05);
            border-left: 3px solid var(--primary);
            padding-left: calc(1.5rem - 3px);
        }

        .report-item-icon {
            font-size: 20px;
            color: var(--primary);
            flex-shrink: 0;
            margin-top: 0.25rem;
        }

        .report-item-content {
            flex: 1;
        }

        .report-item-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0 0 0.25rem 0;
        }

        .report-item-desc {
            font-size: 12px;
            color: var(--text-light);
            margin: 0;
        }

        .report-item-source {
            font-size: 11px;
            color: var(--text-lighter);
            margin-top: 0.5rem;
            font-style: italic;
        }

        /* ===== RIGHT PANEL ===== */
        .reports-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--light);
        }

        .report-config-panel {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .config-section {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .config-section-title {
            margin: 0 0 1rem 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-icon {
            font-size: 18px;
            color: var(--primary);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-item label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .filter-item select {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: white;
            font-size: 13px;
            color: var(--text-dark);
            cursor: pointer;
        }

        /* ===== FIELDS SECTION ===== */
        .fields-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .fields-column {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .fields-column-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .field-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 13px;
            color: var(--text-dark);
            cursor: move;
        }

        .field-item.dragging {
            opacity: 0.5;
        }

        .field-checkbox {
            cursor: pointer;
        }

        .field-drag-handle {
            color: var(--text-light);
            cursor: grab;
        }

        .field-drag-handle:active {
            cursor: grabbing;
        }

        /* ===== PREVIEW TABLE ===== */
        .preview-section {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .preview-section-title {
            margin: 0 0 1rem 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-container {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .preview-table thead {
            background: var(--light);
            position: sticky;
            top: 0;
        }

        .preview-table th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            border-bottom: 2px solid var(--border);
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }

        .preview-table th:hover {
            background: rgba(30, 64, 175, 0.05);
        }

        .preview-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-dark);
            white-space: nowrap;
        }

        .preview-table tbody tr:hover {
            background: var(--light);
        }

        .sort-indicator {
            font-size: 10px;
            margin-left: 0.5rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
            font-size: 12px;
        }

        .pagination-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            background: white;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .pagination-btn:hover:not(:disabled) {
            border-color: var(--primary);
            color: var(--primary);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ===== NO SELECTION STATE ===== */
        .no-selection {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-light);
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .no-selection-icon {
            font-size: 48px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .no-selection p {
            margin: 0 0 0.5rem 0;
        }

        /* ===== MODALS ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-light);
            cursor: pointer;
            padding: 0;
        }

        .modal-close:hover {
            color: var(--text-dark);
        }

        .modal-section {
            margin-bottom: 1.5rem;
        }

        .modal-section-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            display: block;
        }

        .modal-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
        }

        .option-btn {
            padding: 0.75rem;
            border: 1px solid var(--border);
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            text-align: center;
            transition: all 0.2s ease;
        }

        .option-btn:hover {
            border-color: var(--primary);
            background: rgba(30, 64, 175, 0.05);
        }

        .option-btn.selected {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .modal-form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .modal-form-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .modal-form-group input,
        .modal-form-group select,
        .modal-form-group textarea {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .btn-cancel {
            padding: 0.5rem 1.25rem;
            background: white;
            color: var(--text-dark);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: var(--light);
        }

        /* ===== TABS ===== */
        .tabs-container {
            display: flex;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.5rem;
            padding: 0 1.5rem;
        }

        .tab-btn {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
            padding: 1.5rem;
        }

        .tab-content.active {
            display: block;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1200px) {
            .reports-sidebar {
                width: 250px;
            }

            .fields-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .reports-container {
                flex-direction: column;
            }

            .reports-sidebar {
                width: 100%;
                max-height: 30vh;
                border-right: none;
                border-bottom: 1px solid var(--border);
            }

            .reports-top-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .modal-content {
                width: 95%;
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
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
<body>
    <div class="analytics-reports">
        
        <!-- TOP BAR -->
        <div class="reports-top-bar">
            <h1 class="reports-title">Reports</h1>
            <div class="reports-controls">
                <div class="action-buttons">
                    <button class="btn-primary" onclick="showExportModal()">
                        <i class='bx bx-download'></i>
                        Export
                    </button>
                    <button class="btn-secondary" onclick="showScheduleModal()">
                        <i class='bx bx-calendar'></i>
                        Schedule
                    </button>
                    <button class="btn-secondary" onclick="showComparisonModal()">
                        <i class='bx bx-git-compare'></i>
                        Compare
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN CONTAINER -->
        <div class="reports-container">
            
            <!-- LEFT SIDEBAR: REPORT SELECTOR -->
            <div class="reports-sidebar">
                <div class="reports-sidebar-header">
                    <h3 class="sidebar-title">Reports</h3>
                </div>
                <div class="reports-list" id="reports-list"></div>
            </div>

            <!-- RIGHT PANEL: CONFIGURATION AND PREVIEW -->
            <div class="reports-main">
                <div class="report-config-panel" id="config-panel">
                    <div class="no-selection">
                        <div class="no-selection-icon"><i class='bx bx-file-blank'></i></div>
                        <p><strong>Select a Report</strong></p>
                        <p>Choose a report from the list to configure filters and preview data</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPORT MODAL -->
    <div class="modal" id="export-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Export Report</h3>
                <button class="modal-close" onclick="closeModal('export-modal')"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-section">
                <label class="modal-section-label">Select Format</label>
                <div class="modal-options">
                    <button class="option-btn selected" onclick="selectExportFormat(event, 'excel')">
                        <i class='bx bxs-file-doc'></i><br>
                        <small>Excel</small>
                    </button>
                    <button class="option-btn" onclick="selectExportFormat(event, 'pdf')">
                        <i class='bx bxs-file-pdf'></i><br>
                        <small>PDF</small>
                    </button>
                    <button class="option-btn" onclick="selectExportFormat(event, 'csv')">
                        <i class='bx bxs-file-txt'></i><br>
                        <small>CSV</small>
                    </button>
                </div>
            </div>
            <div class="modal-form-group">
                <label>Include Applied Filters</label>
                <input type="checkbox" id="include-filters" checked> Yes, include filter information
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal('export-modal')">Cancel</button>
                <button class="btn-primary" onclick="exportReport()">
                    <i class='bx bx-download'></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- SCHEDULE MODAL -->
    <div class="modal" id="schedule-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Schedule Report</h3>
                <button class="modal-close" onclick="closeModal('schedule-modal')"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-section">
                <label class="modal-section-label">Frequency</label>
                <div class="modal-options">
                    <button class="option-btn selected" onclick="selectScheduleFreq(event, 'weekly')">
                        <i class='bx bx-calendar-week'></i><br>
                        <small>Weekly</small>
                    </button>
                    <button class="option-btn" onclick="selectScheduleFreq(event, 'monthly')">
                        <i class='bx bx-calendar-month'></i><br>
                        <small>Monthly</small>
                    </button>
                    <button class="option-btn" onclick="selectScheduleFreq(event, 'daily')">
                        <i class='bx bx-calendar-check'></i><br>
                        <small>Daily</small>
                    </button>
                </div>
            </div>
            <div class="modal-form-group">
                <label>Email Recipients (comma-separated)</label>
                <textarea id="schedule-recipients" placeholder="user@company.com, manager@company.com" rows="3"></textarea>
            </div>
            <div class="modal-form-group">
                <label>Format</label>
                <select id="schedule-format">
                    <option value="excel">Excel</option>
                    <option value="pdf">PDF</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal('schedule-modal')">Cancel</button>
                <button class="btn-primary" onclick="scheduleReport()">
                    <i class='bx bx-check'></i> Schedule
                </button>
            </div>
        </div>
    </div>

    <!-- COMPARISON MODAL -->
    <div class="modal" id="comparison-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Comparison Analysis</h3>
                <button class="modal-close" onclick="closeModal('comparison-modal')"><i class='bx bx-x'></i></button>
            </div>
            <div class="modal-section">
                <label class="modal-section-label">Comparison Type</label>
                <div class="modal-options">
                    <button class="option-btn selected" onclick="selectComparisonType(event, 'period')">
                        <i class='bx bx-git-compare'></i><br>
                        <small>Period</small>
                    </button>
                    <button class="option-btn" onclick="selectComparisonType(event, 'department')">
                        <i class='bx bx-building'></i><br>
                        <small>Department</small>
                    </button>
                </div>
            </div>
            <div id="period-comparison" style="display: block;">
                <div class="modal-form-group">
                    <label>Period 1</label>
                    <select id="compare-period-1">
                        <option value="30">Last 30 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <div class="modal-form-group">
                    <label>Period 2</label>
                    <select id="compare-period-2">
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                    </select>
                </div>
            </div>
            <div id="department-comparison" style="display: none;">
                <div class="modal-form-group">
                    <label>Department 1</label>
                    <select id="compare-dept-1"></select>
                </div>
                <div class="modal-form-group">
                    <label>Department 2</label>
                    <select id="compare-dept-2"></select>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal('comparison-modal')">Cancel</button>
                <button class="btn-primary" onclick="runComparison()">
                    <i class='bx bx-check'></i> Compare
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
        // ===== STANDARD REPORTS ===== 
        const standardReports = [
            {
                id: 'employee-master',
                name: 'Employee Master List',
                icon: 'bx-user',
                description: 'Complete employee directory with all details',
                source: 'HR Core',
                filters: ['department', 'employment_type', 'status'],
                fields: ['employee_id', 'first_name', 'last_name', 'email', 'phone', 'department', 'designation', 'hire_date', 'employment_type', 'status']
            },
            {
                id: 'payroll-summary',
                name: 'Payroll Summary',
                icon: 'bx-wallet',
                description: 'Monthly payroll with gross, deductions, and net',
                source: 'Payroll',
                filters: ['department', 'employment_type', 'period'],
                fields: ['employee_id', 'employee_name', 'gross_pay', 'basic_pay', 'overtime', 'allowances', 'deductions', 'tax', 'net_pay']
            },
            {
                id: 'government-compliance',
                name: 'Government Compliance',
                icon: 'bx-file',
                description: 'Tax, SSS, PhilHealth and Pag-IBIG summaries',
                source: 'Payroll, HR Core',
                filters: ['department', 'period'],
                fields: ['employee_id', 'employee_name', 'sss', 'philhealth', 'pagibig', 'tax', 'total_contributions']
            },
            {
                id: 'compensation-cost',
                name: 'Compensation Cost Report',
                icon: 'bx-bar-chart',
                description: 'Salary, incentives, and total compensation analysis',
                source: 'Compensation',
                filters: ['department', 'pay_grade', 'period'],
                fields: ['employee_id', 'employee_name', 'pay_grade', 'basic_salary', 'incentives', 'allowances', 'total_compensation']
            },
            {
                id: 'hmo-report',
                name: 'HMO Enrollment & Cost',
                icon: 'bx-health',
                description: 'HMO coverage, enrollment status, and cost analysis',
                source: 'HMO',
                filters: ['department', 'hmo_plan', 'enrollment_status'],
                fields: ['employee_id', 'employee_name', 'hmo_plan', 'enrollment_date', 'dependents', 'monthly_cost', 'annual_cost']
            },
            {
                id: 'attendance-productivity',
                name: 'Attendance & Productivity',
                icon: 'bx-calendar-check',
                description: 'Attendance rates, absences, and overtime analysis',
                source: 'Attendance',
                filters: ['department', 'period'],
                fields: ['employee_id', 'employee_name', 'present_days', 'absent_days', 'late_count', 'undertime_count', 'overtime_hours', 'attendance_rate']
            }
        ];

        // ===== MODAL FUNCTIONS =====
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function showExportModal() {
            const selectedReport = document.querySelector('.report-item.active');
            if (!selectedReport) {
                alert('Please select a report first');
                return;
            }
            showModal('export-modal');
        }

        function showScheduleModal() {
            const selectedReport = document.querySelector('.report-item.active');
            if (!selectedReport) {
                alert('Please select a report first');
                return;
            }
            showModal('schedule-modal');
        }

        function showComparisonModal() {
            const selectedReport = document.querySelector('.report-item.active');
            if (!selectedReport) {
                alert('Please select a report first');
                return;
            }
            showModal('comparison-modal');
        }

        function selectExportFormat(event, format) {
            document.querySelectorAll('#export-modal .option-btn').forEach(btn => btn.classList.remove('selected'));
            event.target.closest('.option-btn').classList.add('selected');
        }

        function selectScheduleFreq(event, freq) {
            document.querySelectorAll('#schedule-modal .option-btn').forEach(btn => btn.classList.remove('selected'));
            event.target.closest('.option-btn').classList.add('selected');
        }

        function selectComparisonType(event, type) {
            document.querySelectorAll('#comparison-modal .option-btn').forEach(btn => btn.classList.remove('selected'));
            event.target.closest('.option-btn').classList.add('selected');

            if (type === 'period') {
                document.getElementById('period-comparison').style.display = 'block';
                document.getElementById('department-comparison').style.display = 'none';
            } else {
                document.getElementById('period-comparison').style.display = 'none';
                document.getElementById('department-comparison').style.display = 'block';
            }
        }

        function exportReport() {
            alert('Report export functionality coming soon');
            closeModal('export-modal');
        }

        function scheduleReport() {
            alert('Report scheduling functionality coming soon');
            closeModal('schedule-modal');
        }

        function runComparison() {
            alert('Report comparison functionality coming soon');
            closeModal('comparison-modal');
        }

        // ===== REPORT LOADING =====
        function loadReportsList() {
            console.log('loadReportsList called');
            console.log('standardReports:', standardReports);
            
            const list = document.getElementById('reports-list');
            console.log('reports-list element:', list);
            
            if (!list) {
                console.error('reports-list element not found');
                return;
            }
            
            try {
                const html = standardReports.map(report => `
                    <div class="report-item" onclick="selectReport(event, '${report.id}')">
                        <div class="report-item-icon"><i class='bx ${report.icon}'></i></div>
                        <div class="report-item-content">
                            <p class="report-item-name">${report.name}</p>
                            <p class="report-item-desc">${report.description}</p>
                            <p class="report-item-source">Source: ${report.source}</p>
                        </div>
                    </div>
                `).join('');
                
                console.log('Generated HTML:', html);
                list.innerHTML = html;
                console.log('Reports list populated successfully');
            } catch (error) {
                console.error('Error in loadReportsList:', error);
                list.innerHTML = '<div style="padding: 1rem; color: red;">Error loading reports: ' + error.message + '</div>';
            }
        }

        function selectReport(event, reportId) {
            // Remove active from all
            document.querySelectorAll('.report-item').forEach(item => item.classList.remove('active'));
            
            // Add active to clicked
            event.currentTarget.classList.add('active');
            
            // Store selected report
            window.selectedReportId = reportId;
            
            // Load report configuration
            const report = standardReports.find(r => r.id === reportId);
            if (report) {
                loadReportConfig(report);
            }
        }

        function loadReportConfig(report) {
            const configPanel = document.getElementById('config-panel');
            
            let filtersHTML = '<div class="filters-grid">';
            report.filters.forEach(filter => {
                let filterLabel = filter.replace(/_/g, ' ').toUpperCase();
                let filterSelect = 'department-filter';
                let filterOptions = '<option value="">All ' + filterLabel + '</option>';
                
                // Add actual values for filter dropdowns
                if (filter === 'department') {
                    filterSelect = 'report-department-filter';
                    filterOptions = '<option value="">All Departments</option>';
                    // Departments will be loaded from API
                } else if (filter === 'employment_type') {
                    filterSelect = 'report-employment-type-filter';
                    filterOptions = '<option value="">All Employment Types</option>';
                    // Employment types will be loaded from API
                } else if (filter === 'period') {
                    filterSelect = 'report-period-filter';
                    filterOptions = `
                        <option value="">All Periods</option>
                        <option value="30">Last 30 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                    `;
                } else if (filter === 'status') {
                    filterSelect = 'report-status-filter';
                    filterOptions = `
                        <option value="">All Statuses</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    `;
                }
                
                filtersHTML += `
                    <div class="filter-item">
                        <label>${filterLabel}</label>
                        <select id="${filterSelect}" onchange="loadReportData()">
                            ${filterOptions}
                        </select>
                    </div>
                `;
            });
            filtersHTML += '</div>';

            let fieldsHTML = '<div class="fields-container">';
            const midPoint = Math.ceil(report.fields.length / 2);
            
            fieldsHTML += '<div class="fields-column">';
            fieldsHTML += '<div class="fields-column-title">Selected Fields</div>';
            report.fields.slice(0, midPoint).forEach(field => {
                fieldsHTML += `
                    <div class="field-item">
                        <input type="checkbox" class="field-checkbox report-field-visible" data-field="${field}" checked>
                        <i class='bx bx-dots-vertical-rounded field-drag-handle'></i>
                        <span>${field.replace(/_/g, ' ').toUpperCase()}</span>
                    </div>
                `;
            });
            fieldsHTML += '</div>';

            fieldsHTML += '<div class="fields-column">';
            report.fields.slice(midPoint).forEach(field => {
                fieldsHTML += `
                    <div class="field-item">
                        <input type="checkbox" class="field-checkbox report-field-visible" data-field="${field}" checked>
                        <i class='bx bx-dots-vertical-rounded field-drag-handle'></i>
                        <span>${field.replace(/_/g, ' ').toUpperCase()}</span>
                    </div>
                `;
            });
            fieldsHTML += '</div>';
            fieldsHTML += '</div>';

            const previewHTML = `
                <div class="table-container" id="report-table-container">
                    <div class="no-data">Loading report data...</div>
                </div>
                <div class="pagination" id="report-pagination">
                    <button class="pagination-btn" onclick="previousPage()">← Previous</button>
                    <span>Page <strong id="current-page">1</strong> of <strong id="total-pages">1</strong> (<strong id="total-records">0</strong> records)</span>
                    <button class="pagination-btn" onclick="nextPage()">Next →</button>
                </div>
            `;

            configPanel.innerHTML = `
                <div class="config-section">
                    <div class="config-section-title">
                        <i class='bx bx-filter section-icon'></i>
                        Filters
                    </div>
                    ${filtersHTML}
                </div>

                <div class="config-section">
                    <div class="config-section-title">
                        <i class='bx bx-list-check section-icon'></i>
                        Fields & Display
                    </div>
                    ${fieldsHTML}
                </div>

                <div class="preview-section">
                    <div class="preview-section-title">
                        <i class='bx bx-table section-icon'></i>
                        Preview (First 20 Rows)
                    </div>
                    ${previewHTML}
                </div>
            `;
            
            // Load dropdown data from API
            loadFilterDropdowns();
            
            // Load report data
            loadReportData();
        }

        function loadFilterDropdowns() {
            // Load departments
            fetch('../api.php?action=getDashboardData')
                .then(response => response.json())
                .then(data => {
                    if (data.departments) {
                        const deptSelect = document.getElementById('report-department-filter');
                        if (deptSelect) {
                            const currentValue = deptSelect.value;
                            let html = '<option value="">All Departments</option>';
                            data.departments.forEach(dept => {
                                html += `<option value="${dept.id}">${dept.name}</option>`;
                            });
                            deptSelect.innerHTML = html;
                            deptSelect.value = currentValue;
                        }
                    }
                    
                    if (data.employment_types) {
                        const empSelect = document.getElementById('report-employment-type-filter');
                        if (empSelect) {
                            const currentValue = empSelect.value;
                            let html = '<option value="">All Employment Types</option>';
                            data.employment_types.forEach(emp => {
                                html += `<option value="${emp.id}">${emp.name}</option>`;
                            });
                            empSelect.innerHTML = html;
                            empSelect.value = currentValue;
                        }
                    }
                })
                .catch(error => console.error('Error loading filter dropdowns:', error));
        }

        function loadReportData() {
            if (!window.selectedReportId) {
                return;
            }
            
            // Get filter values
            const department = document.getElementById('report-department-filter')?.value || '';
            const employmentType = document.getElementById('report-employment-type-filter')?.value || '';
            
            // Build API URL
            let url = '../api.php?action=getReportData&reportId=' + encodeURIComponent(window.selectedReportId);
            url += '&limit=20&offset=0';
            if (department) url += '&department=' + encodeURIComponent(department);
            if (employmentType) url += '&employmentType=' + encodeURIComponent(employmentType);
            
            // Fetch data
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        displayReportData(data.data);
                    } else {
                        document.getElementById('report-table-container').innerHTML = 
                            '<div class="no-data">No data available for this report</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading report data:', error);
                    document.getElementById('report-table-container').innerHTML = 
                        '<div class="no-data">Error loading report data</div>';
                });
        }

        function displayReportData(rows) {
            if (!rows || rows.length === 0) {
                document.getElementById('report-table-container').innerHTML = 
                    '<div class="no-data">No data available</div>';
                return;
            }
            
            // Get visible fields
            const visibleFields = Array.from(document.querySelectorAll('.report-field-visible:checked'))
                .map(cb => cb.dataset.field);
            
            if (visibleFields.length === 0) {
                document.getElementById('report-table-container').innerHTML = 
                    '<div class="no-data">Please select at least one field to display</div>';
                return;
            }
            
            // Build table
            let html = '<table class="preview-table"><thead><tr>';
            visibleFields.forEach(field => {
                html += `<th onclick="sortTable(event)">${field.replace(/_/g, ' ').toUpperCase()}<span class="sort-indicator"></span></th>`;
            });
            html += '</tr></thead><tbody>';
            
            rows.forEach(row => {
                html += '<tr>';
                visibleFields.forEach(field => {
                    const value = row[field] || '-';
                    html += `<td>${sanitizeValue(value)}</td>`;
                });
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            document.getElementById('report-table-container').innerHTML = html;
            
            // Update pagination
            document.getElementById('current-page').textContent = '1';
            document.getElementById('total-pages').textContent = '1';
            document.getElementById('total-records').textContent = rows.length;
        }

        function sanitizeValue(value) {
            if (value === null || value === undefined) return '-';
            if (typeof value === 'number') return value.toLocaleString();
            return String(value).substring(0, 100);
        }

        function previousPage() {
            alert('Pagination coming soon');
        }

        function nextPage() {
            alert('Pagination coming soon');
        }

        function sortTable(event) {
            alert('Column sorting coming soon');
        }

        // ===== INITIALIZE =====
        function initializePage() {
            console.log('Initializing Reports page...');
            loadReportsList();
            
            // Close modals on outside click
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        this.classList.remove('active');
                    }
                });
            });
            
            console.log('Reports page initialized successfully');
        }

        // Run initialization when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializePage);
        } else {
            initializePage();
        }
    </script>
</body>
</html>
