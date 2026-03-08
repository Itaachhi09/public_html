<?php
/**
 * Payroll Runs - Manage payroll periods and processing
 */

if (!defined('SYSTEM_INIT')) {
    http_response_code(403);
    die('No direct access allowed');
}

$appRoot = dirname(dirname(dirname(__DIR__)));
require_once $appRoot . '/config/BaseConfig.php';
require_once $appRoot . '/config/Database.php';
require_once __DIR__ . '/../models/PayrollRun.php';

$payrollRunModel = new PayrollRun();
$db = new Database();

// Get all active payroll runs (not archived)
$payrollRuns = $db->query("SELECT * FROM payroll_runs WHERE status != 'Archived' ORDER BY updated_at DESC");

// Get archived payroll runs for the modal
$archivedRuns = $db->query("SELECT * FROM payroll_runs WHERE status = 'Archived' ORDER BY updated_at DESC");
?>

<div style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; margin: 0;">Payroll Runs</h1>
            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Create and manage payroll periods</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button onclick="openArchiveModal()" style="padding: 0.75rem 1.5rem; background: #f59e0b; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">📦 Archive</button>
            <button onclick="toggleCreateForm()" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                + New Payroll Run
            </button>
        </div>
    </div>

    <!-- Create New Payroll Run Form -->
    <div id="createFormDiv" style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: none;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #10b981; padding-bottom: 0.5rem;">Create New Payroll Run</h2>
        <form id="createPayrollForm" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Period Name *</label>
                <input type="text" id="periodName" placeholder="e.g., January 1-15, 2026" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Branch</label>
                <select id="branch" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="">All Branches</option>
                    <option value="HQ">HQ</option>
                    <option value="Branch A">Branch A</option>
                    <option value="Branch B">Branch B</option>
                </select>
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Start Date *</label>
                <input type="date" id="startDate" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">End Date *</label>
                <input type="date" id="endDate" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Pay Date *</label>
                <input type="date" id="payDate" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>

            <div style="grid-column: 1 / -1; display: flex; gap: 1rem;">
                <button type="button" onclick="submitCreatePayrollForm()" style="flex: 1; padding: 0.75rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Create Payroll Run</button>
                <button type="button" onclick="toggleCreateForm()" style="flex: 1; padding: 0.75rem; background: #9ca3af; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <?php if (!empty($payrollRuns)): 
        $totalRuns = count($payrollRuns);
        $processedCount = count(array_filter($payrollRuns, function($r) { return $r['status'] === 'Processed'; }));
        $draftCount = count(array_filter($payrollRuns, function($r) { return $r['status'] === 'Draft'; }));
    ?>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Runs</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;"><?php echo $totalRuns; ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Draft</div>
            <div style="font-size: 2rem; font-weight: 700; color: #d97706;"><?php echo $draftCount; ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Processed</div>
            <div style="font-size: 2rem; font-weight: 700; color: #059669;"><?php echo $processedCount; ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Archived</div>
            <div style="font-size: 2rem; font-weight: 700; color: #ef4444;"><?php echo count($archivedRuns); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payroll Runs Table -->
    <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #3b82f6; padding-bottom: 0.5rem;">
            Payroll Run History
        </h2>

        <?php if (empty($payrollRuns)): ?>
            <p style="color: #9ca3af; text-align: center; padding: 2rem;">No payroll runs yet. Create one to get started.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f3f4f6;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Period Name</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Employees</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Gross Pay</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Deductions</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Net Pay</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Pay Date</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Status</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payrollRuns as $run): 
                            $empCount = $db->query("SELECT COUNT(*) as cnt FROM payroll_run_employees WHERE payroll_run_id = " . (int)$run['id']);
                            $empCount = $empCount[0]['cnt'] ?? 0;
                            
                            $totals = $db->query("SELECT SUM(gross_pay) as total_gross, SUM(total_deductions) as total_deductions, SUM(net_pay) as total_net FROM payroll_run_employees WHERE payroll_run_id = " . (int)$run['id']);
                            $totals = $totals[0] ?? ['total_gross' => 0, 'total_deductions' => 0, 'total_net' => 0];
                            
                            $statusColor = $run['status'] === 'Draft' ? '#fbbf24' : ($run['status'] === 'Processed' ? '#10b981' : '#4f46e5');
                            $statusBgColor = $run['status'] === 'Draft' ? '#fef3c7' : ($run['status'] === 'Processed' ? '#d1fae5' : '#e0e7ff');
                        ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem; font-weight: 500; color: #1f2937;"><?php echo htmlspecialchars($run['period_name']); ?></td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #e0e7ff; color: #3730a3; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo (int)$empCount; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #059669;">
                                    ₱<?php echo number_format($totals['total_gross'] ?? 0, 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #ef4444;">
                                    ₱<?php echo number_format($totals['total_deductions'] ?? 0, 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 700; color: #1f2937;">
                                    ₱<?php echo number_format($totals['total_net'] ?? 0, 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280;">
                                    <?php echo date('M d, Y', strtotime($run['pay_date'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: <?php echo $statusBgColor; ?>; color: <?php echo $statusColor; ?>; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($run['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <button onclick="viewPayrollRun(<?php echo (int)$run['id']; ?>, '<?php echo htmlspecialchars(addslashes($run['period_name'])); ?>', <?php echo (int)$empCount; ?>, <?php echo $totals['total_gross'] ?? 0; ?>, <?php echo $totals['total_deductions'] ?? 0; ?>, <?php echo $totals['total_net'] ?? 0; ?>, '<?php echo htmlspecialchars($run['pay_date']); ?>', '<?php echo htmlspecialchars($run['status']); ?>')" style="padding: 0.5rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; margin-right: 0.25rem;">View</button>
                                    <button onclick="deletePayrollRun(<?php echo (int)$run['id']; ?>, '<?php echo htmlspecialchars($run['period_name']); ?>')" style="padding: 0.5rem 0.75rem; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payroll Run Details Modal -->
<div id="viewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; padding: 2rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 25px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 1rem;">
            <h2 style="font-size: 1.5rem; margin: 0; color: #1f2937;">Payroll Run Details</h2>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">✕</button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div>
                <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Period Name</label>
                <div style="font-size: 1.125rem; font-weight: 600; color: #1f2937;" id="modalPeriodName">—</div>
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Status</label>
                <div id="modalStatus" style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">—</div>
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Pay Date</label>
                <div style="font-size: 1.125rem; font-weight: 600; color: #1f2937;" id="modalPayDate">—</div>
            </div>

            <div>
                <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Total Employees</label>
                <div style="font-size: 1.125rem; font-weight: 600; color: #3b82f6;" id="modalEmployeeCount">0</div>
            </div>
        </div>

        <div style="background: #f9fafb; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
            <h3 style="font-weight: 600; color: #1f2937; margin-bottom: 1rem;">Financial Summary</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div>
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Gross Pay</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #059669;" id="modalGrossPay">₱0.00</div>
                </div>
                <div>
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Deductions</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444;" id="modalDeductions">₱0.00</div>
                </div>
                <div style="grid-column: 1 / -1; border-top: 2px solid #e5e7eb; padding-top: 1rem;">
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Net Pay</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;" id="modalNetPay">₱0.00</div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button onclick="closeModal()" style="flex: 1; padding: 0.75rem; background: #e5e7eb; color: #1f2937; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Close</button>
            <button onclick="printPayrollReport()" style="flex: 1; padding: 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Print Report</button>
        </div>
    </div>
</div>

<!-- Print Report Section (Hidden) -->
<div id="printSection" style="display: none;">
    <div style="padding: 2rem; background: white;">
        <div style="text-align: center; border-bottom: 3px solid #1f2937; padding-bottom: 1.5rem; margin-bottom: 2rem;">
            <h1 style="margin: 0; font-size: 1.75rem; color: #1f2937;">PAYROLL REPORT</h1>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;" id="printPeriodName">Payroll Period: —</p>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;" id="printPayDate">Pay Date: —</p>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;" id="printGeneratedDate">Date Generated: —</p>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
            <thead>
                <tr style="background: #f3f4f6; border-bottom: 2px solid #1f2937;">
                    <th style="padding: 0.75rem; text-align: left; font-weight: 700; border: 1px solid #d1d5db;">Employee ID</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 700; border: 1px solid #d1d5db;">Employee Name</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 700; border: 1px solid #d1d5db;">Position</th>
                    <th style="padding: 0.75rem; text-align: right; font-weight: 700; border: 1px solid #d1d5db;">Gross Pay</th>
                    <th style="padding: 0.75rem; text-align: right; font-weight: 700; border: 1px solid #d1d5db;">Deductions</th>
                    <th style="padding: 0.75rem; text-align: right; font-weight: 700; border: 1px solid #d1d5db;">Net Pay</th>
                </tr>
            </thead>
            <tbody id="printTableBody">
                <!-- Populated via JavaScript -->
            </tbody>
            <tfoot>
                <tr style="background: #f9fafb; font-weight: 700; border-top: 2px solid #1f2937; border-bottom: 2px solid #1f2937;">
                    <td colspan="3" style="padding: 0.75rem; border: 1px solid #d1d5db; text-align: right;">TOTAL:</td>
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db; text-align: right; color: #059669;" id="printTotalGross">₱0.00</td>
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db; text-align: right; color: #ef4444;" id="printTotalDeductions">₱0.00</td>
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db; text-align: right; font-weight: 700;" id="printTotalNet">₱0.00</td>
                </tr>
            </tfoot>
        </table>

        <div style="text-align: center; color: #6b7280; font-size: 0.875rem; border-top: 1px solid #d1d5db; padding-top: 1rem;">
            <p>Healthcare HR - Management System | Payroll Module</p>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div id="archiveModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; padding: 2rem; max-width: 1000px; width: 95%; max-height: 85vh; overflow-y: auto; box-shadow: 0 20px 25px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 1rem;">
            <h2 style="font-size: 1.5rem; margin: 0; color: #1f2937;">Archived Payroll Runs</h2>
            <button onclick="closeArchiveModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">✕</button>
        </div>

        <div id="archiveContent" style="max-height: 70vh; overflow-y: auto;">
            <!-- Archive records will be loaded here -->
        </div>
    </div>
</div>

<script>
let currentPayrollRunId = null;

function toggleCreateForm() {
    const form = document.getElementById('createFormDiv');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function submitCreatePayrollForm() {
    const periodName = document.getElementById('periodName').value.trim();
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const payDate = document.getElementById('payDate').value;
    
    if (!periodName || !startDate || !endDate || !payDate) {
        alert('Please fill in all required fields');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start Date cannot be after End Date');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'createPayrollRun');
    formData.append('period_name', periodName);
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);
    formData.append('pay_date', payDate);
    
    fetch('modules/payroll/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payroll run created successfully!');
            // Clear the form
            document.getElementById('createPayrollForm').reset();
            // Close the form
            toggleCreateForm();
            // Refresh the page
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to create payroll run'));
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

function viewPayrollRun(id, periodName, empCount, grossPay, deductions, netPay, payDate, status) {
    currentPayrollRunId = id;
    
    // Populate modal with data
    document.getElementById('modalPeriodName').textContent = periodName;
    document.getElementById('modalEmployeeCount').textContent = empCount;
    document.getElementById('modalGrossPay').textContent = '₱' + parseFloat(grossPay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('modalDeductions').textContent = '₱' + parseFloat(deductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('modalNetPay').textContent = '₱' + parseFloat(netPay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('modalPayDate').textContent = new Date(payDate).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'});
    
    // Set status badge color
    const statusDiv = document.getElementById('modalStatus');
    if (status === 'Draft') {
        statusDiv.style.background = '#fef3c7';
        statusDiv.style.color = '#92400e';
    } else if (status === 'Processed') {
        statusDiv.style.background = '#d1fae5';
        statusDiv.style.color = '#065f46';
    } else {
        statusDiv.style.background = '#e0e7ff';
        statusDiv.style.color = '#3730a3';
    }
    statusDiv.textContent = status;
    
    // Show modal
    document.getElementById('viewModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('viewModal').style.display = 'none';
}

function printPayrollReport() {
    if (!currentPayrollRunId) {
        alert('No payroll run selected');
        return;
    }
    
    // Fetch employee salary data for this payroll run
    fetch('modules/payroll/api.php?action=getPayrollRunPayslips&payroll_run_id=' + currentPayrollRunId)
        .then(response => response.json())
        .then(data => {
            if (data.payslips && data.payslips.length > 0) {
                populatePrintReport(data);
                
                // Close the modal
                closeModal();
                
                // Wait a bit then print
                setTimeout(() => {
                    window.print();
                }, 100);
            } else {
                alert('No payslip data found for this payroll run');
            }
        })
        .catch(error => {
            alert('Error fetching payroll data: ' + error);
        });
}

function populatePrintReport(data) {
    const payslips = data.payslips;
    const payrollRunData = data.payroll_run;
    
    // Set report header info
    document.getElementById('printPeriodName').textContent = 'Payroll Period: ' + payrollRunData.period_name;
    document.getElementById('printPayDate').textContent = 'Pay Date: ' + new Date(payrollRunData.pay_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
    document.getElementById('printGeneratedDate').textContent = 'Date Generated: ' + new Date().toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'});
    
    // Populate table rows
    const tableBody = document.getElementById('printTableBody');
    tableBody.innerHTML = '';
    
    let totalGross = 0;
    let totalDeductions = 0;
    let totalNet = 0;
    
    payslips.forEach(payslip => {
        const row = document.createElement('tr');
        row.style.borderBottom = '1px solid #d1d5db';
        
        const gross = parseFloat(payslip.gross_pay) || 0;
        const deductions = parseFloat(payslip.total_deductions) || 0;
        const net = parseFloat(payslip.net_pay) || 0;
        
        totalGross += gross;
        totalDeductions += deductions;
        totalNet += net;
        
        row.innerHTML = `
            <td style="padding: 0.5rem; border: 1px solid #d1d5db;">${payslip.employee_id}</td>
            <td style="padding: 0.5rem; border: 1px solid #d1d5db;">${payslip.first_name} ${payslip.last_name}</td>
            <td style="padding: 0.5rem; border: 1px solid #d1d5db;">${payslip.position || '—'}</td>
            <td style="padding: 0.5rem; border: 1px solid #d1d5db; text-align: right;">₱${gross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td style="padding: 0.5rem; border: 1px solid #d1d5db; text-align: right;">₱${deductions.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            <td style="padding: 0.5rem; border: 1px solid #d1d5db; text-align: right;">₱${net.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        `;
        tableBody.appendChild(row);
    });
    
    // Update totals
    document.getElementById('printTotalGross').textContent = '₱' + totalGross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('printTotalDeductions').textContent = '₱' + totalDeductions.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('printTotalNet').textContent = '₱' + totalNet.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Close modal when clicking outside
document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

function archivePayrollRun(id, name) {
    if (confirm(`Archive payroll run "${name}"? This will move it to archive.`)) {
        fetch('modules/payroll/api.php?action=archivePayrollRun&id=' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payroll run archived successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => alert('Error: ' + error));
    }
}

function deletePayrollRun(id, name) {
    if (confirm(`Archive payroll run "${name}"? You can restore it later from the Archive section.`)) {
        fetch('modules/payroll/api.php?action=archivePayrollRun&id=' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payroll run archived successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => alert('Error: ' + error));
    }
}

function restorePayrollRun(id, name) {
    if (confirm(`Restore payroll run "${name}" to active records?`)) {
        fetch('modules/payroll/api.php?action=restorePayrollRun&id=' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payroll run restored successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => alert('Error: ' + error));
    }
}

function permanentlyDeletePayrollRun(id, name) {
    if (confirm(`PERMANENTLY DELETE payroll run "${name}"? This action CANNOT be undone.`)) {
        fetch('modules/payroll/api.php?action=deletePayrollRun&id=' + id, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payroll run permanently deleted!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => alert('Error: ' + error));
    }
}

// Archive Modal Functions
function openArchiveModal() {
    document.getElementById('archiveModal').style.display = 'flex';
    loadArchivedPayrollRuns();
}

function closeArchiveModal() {
    document.getElementById('archiveModal').style.display = 'none';
}

function loadArchivedPayrollRuns() {
    const archiveContent = document.getElementById('archiveContent');
    archiveContent.innerHTML = '<p style="text-align: center; color: #6b7280;">Loading archived records...</p>';

    fetch('modules/payroll/api.php?action=getArchivedPayrollRuns', {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.records.length > 0) {
            let html = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">';
            html += `<thead>
                <tr style="background: #f3f4f6; border-bottom: 2px solid #d1d5db;">
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #1f2937; border: 1px solid #d1d5db;">Period</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #1f2937; border: 1px solid #d1d5db;">Pay Date</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #1f2937; border: 1px solid #d1d5db;">Status</th>
                    <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: #1f2937; border: 1px solid #d1d5db;">Archived On</th>
                    <th style="padding: 0.75rem; text-align: center; font-weight: 600; color: #1f2937; border: 1px solid #d1d5db;">Actions</th>
                </tr>
            </thead>
            <tbody>`;

            data.records.forEach(record => {
                const payDate = new Date(record.pay_date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                const archivedDate = new Date(record.archived_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });

                const statusBadge = `<span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.875rem; font-weight: 500; background: ${record.status === 'completed' ? '#d1fae5' : '#fef3c7'}; color: ${record.status === 'completed' ? '#065f46' : '#92400e'}">${record.status.charAt(0).toUpperCase() + record.status.slice(1)}</span>`;

                html += `<tr style="border-bottom: 1px solid #e5e7eb; hover-background: #f9fafb;">
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db; color: #1f2937;">${record.period_name}</td>
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db; color: #1f2937;">${payDate}</td>
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db;">${statusBadge}</td>
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db; color: #1f2937;">${archivedDate}</td>
                    <td style="padding: 0.75rem; border: 1px solid #d1d5db; text-align: center;">
                        <button onclick="restorePayrollRun(${record.id}, '${record.period_name.replace(/'/g, "\\'")}'); closeArchiveModal();" style="padding: 0.25rem 0.75rem; margin-right: 0.5rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">Restore</button>
                        <button onclick="permanentlyDeletePayrollRun(${record.id}, '${record.period_name.replace(/'/g, "\\'")}'); closeArchiveModal();" style="padding: 0.25rem 0.75rem; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">Delete</button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            archiveContent.innerHTML = html;
        } else {
            archiveContent.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 2rem;">No archived payroll runs found.</p>';
        }
    })
    .catch(error => {
        archiveContent.innerHTML = '<p style="text-align: center; color: #dc2626;">Error loading archived records: ' + error + '</p>';
    });
}

// Close archive modal when clicking outside
document.getElementById('archiveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeArchiveModal();
    }
});

// Apply print styles when printing
window.addEventListener('beforeprint', function() {
    document.getElementById('printSection').style.display = 'block';
    document.querySelectorAll('body > *:not(#printSection)').forEach(el => el.style.display = 'none');
});

window.addEventListener('afterprint', function() {
    document.getElementById('printSection').style.display = 'none';
    document.querySelectorAll('body > *:not(#printSection)').forEach(el => el.style.display = '');
});
</script>

