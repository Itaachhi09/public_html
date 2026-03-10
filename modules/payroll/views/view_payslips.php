<?php
/**
 * View Payslips - Generate and manage employee payslips
 */

if (!defined('SYSTEM_INIT')) {
    http_response_code(403);
    die('No direct access allowed');
}

$appRoot = dirname(dirname(dirname(__DIR__)));
require_once $appRoot . '/config/BaseConfig.php';
require_once $appRoot . '/config/Database.php';
require_once __DIR__ . '/../models/Payslip.php';

$payslipModel = new Payslip();
$db = new Database();

// Get selected payroll run
$selectedRunId = isset($_GET['payroll_run_id']) ? (int)$_GET['payroll_run_id'] : null;
$payrollRuns = $db->query("SELECT id, period_name, start_date, end_date, pay_date FROM payroll_runs ORDER BY start_date DESC");

// If no run selected, get the latest
if (!$selectedRunId && !empty($payrollRuns)) {
    $selectedRunId = $payrollRuns[0]['id'];
}

$payslips = [];
if ($selectedRunId) {
    $payslips = $payslipModel->getByPayrollRun($selectedRunId);
}
?>

<div style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; margin: 0;">Payslips</h1>
            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Generate and manage employee payslips</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button onclick="downloadPayslips()" style="padding: 0.75rem 1.5rem; background: #6366f1; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                ⬇ Download All
            </button>
            <button onclick="generatePayslips()" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                + Generate Payslips
            </button>
        </div>
    </div>

    <!-- Payroll Run Selector -->
    <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="font-weight: 600; margin-bottom: 1rem; color: #1f2937;">Select Payroll Run</h3>
        <div style="display: flex; gap: 1rem; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Payroll Run *</label>
                <select id="payroll_run_select" onchange="selectPayrollRun(this.value)" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="">Select a payroll run</option>
                    <?php foreach ($payrollRuns as $run): ?>
                        <option value="<?php echo (int)$run['id']; ?>" <?php echo $selectedRunId === $run['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($run['period_name']); ?> (<?php echo $run['start_date']; ?> - <?php echo $run['end_date']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <?php if ($selectedRunId && !empty($payslips)): 
        $totalGross = array_sum(array_column($payslips, 'gross_pay'));
        $totalDeductions = array_sum(array_column($payslips, 'total_deductions'));
        $totalNet = array_sum(array_column($payslips, 'net_pay'));
    ?>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Payslips</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;"><?php echo count($payslips); ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Gross Pay</div>
            <div style="font-size: 2rem; font-weight: 700; color: #059669;">₱<?php echo number_format($totalGross, 2); ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Deductions</div>
            <div style="font-size: 2rem; font-weight: 700; color: #ef4444;">₱<?php echo number_format($totalDeductions, 2); ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Net Pay</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">₱<?php echo number_format($totalNet, 2); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payslips Table -->
    <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #3b82f6; padding-bottom: 0.5rem;">
            Payslip Records
        </h2>

        <?php if ($selectedRunId && empty($payslips)): ?>
            <div style="text-align: center; padding: 2rem; color: #9ca3af;">
                <p>No payslips generated for this payroll run yet.</p>
                <button onclick="generatePayslips()" style="margin-top: 1rem; padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                    Generate Payslips Now
                </button>
            </div>
        <?php elseif (empty($payslips)): ?>
            <p style="color: #9ca3af;">Select a payroll run to view payslips.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f3f4f6;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Employee</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Code</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Gross Pay</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Deductions</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Net Pay</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Status</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payslips as $payslip): ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 500; color: #1f2937;"><?php echo htmlspecialchars($payslip['first_name'] . ' ' . $payslip['last_name']); ?></div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">ID: <?php echo (int)$payslip['employee_id']; ?></div>
                                </td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($payslip['employee_code']); ?></td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #059669;">
                                    ₱<?php echo number_format($payslip['gross_pay'], 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #ef4444;">
                                    ₱<?php echo number_format($payslip['total_deductions'], 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 700; color: #1f2937;">
                                    ₱<?php echo number_format($payslip['net_pay'], 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #d1fae5; color: #065f46; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($payslip['status'] ?? 'Generated'); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <button onclick="viewPayslip(<?php echo (int)$payslip['id']; ?>)" style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem; margin-right: 0.25rem;">View</button>
                                    <button onclick="downloadPayslip(<?php echo (int)$payslip['id']; ?>)" style="padding: 0.5rem 1rem; background: #6366f1; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.875rem;">Download</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Payslip Details Modal -->
<div id="payslipModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: flex-start; overflow-y: auto; padding-top: 20px; padding-bottom: 20px;">
    <div style="background: white; border-radius: 8px; max-width: 900px; width: 95%; box-shadow: 0 20px 25px rgba(0,0,0,0.15);">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 2rem; border-bottom: 2px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10;">
            <h2 style="font-size: 1.5rem; margin: 0; color: #1f2937;">Payslip Details</h2>
            <button onclick="closePayslipModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; flex-shrink: 0;">✕</button>
        </div>

        <div style="padding: 2rem; overflow-y: auto; max-height: calc(100vh - 150px);">
            <!-- Employee Information Section -->
            <div style="background: #f9fafb; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                <h3 style="font-weight: 600; color: #1f2937; margin-top: 0; margin-bottom: 1rem; font-size: 1rem;">👤 Employee Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Employee Name</label>
                        <div style="font-size: 1rem; font-weight: 600; color: #1f2937;" id="modalEmployeeName">—</div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Employee ID</label>
                        <div style="font-size: 1rem; font-weight: 600; color: #1f2937;" id="modalEmployeeId">—</div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Department</label>
                        <div style="font-size: 0.95rem; color: #1f2937;" id="modalDepartment">—</div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Position</label>
                        <div style="font-size: 0.95rem; color: #1f2937;" id="modalPosition">—</div>
                    </div>
                </div>
            </div>

            <!-- Payroll Period Section -->
            <div style="background: #eff6ff; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #3b82f6;">
                <h3 style="font-weight: 600; color: #1f2937; margin-top: 0; margin-bottom: 1rem; font-size: 1rem;">📅 Payroll Period</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Period</label>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #1f2937;" id="modalPeriodName">—</div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Pay Period</label>
                        <div style="font-size: 0.95rem; color: #1f2937;" id="modalPayPeriod">—</div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Pay Date</label>
                        <div style="font-size: 0.95rem; font-weight: 600; color: #1f2937;" id="modalPayDate">—</div>
                    </div>
                </div>
            </div>

            <!-- Earnings Section -->
            <div style="background: #f0fdf4; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #22c55e;">
                <h3 style="font-weight: 600; color: #1f2937; margin-top: 0; margin-bottom: 1rem; font-size: 1rem;">💰 Earnings</h3>
                <div id="earningsContainer" style="display: block;">
                    <!-- Earnings items will be populated here -->
                </div>
                <div style="border-top: 2px solid #d1fae5; margin-top: 1rem; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; color: #1f2937;">Total Earnings</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #059669;" id="totalEarnings">₱0.00</div>
                </div>
            </div>

            <!-- Deductions Section -->
            <div style="background: #fef2f2; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #ef4444;">
                <h3 style="font-weight: 600; color: #1f2937; margin-top: 0; margin-bottom: 1rem; font-size: 1rem;">📋 Deductions</h3>
                <div id="deductionsContainer" style="display: block;">
                    <!-- Deduction items will be populated here -->
                </div>
                <div style="border-top: 2px solid #fecaca; margin-top: 1rem; padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; color: #1f2937;">Total Deductions</span>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #ef4444;" id="totalDeductions">₱0.00</div>
                </div>
            </div>

            <!-- Salary Summary Section -->
            <div style="background: linear-gradient(135deg, #1f2937 0%, #374151 100%); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; color: white;">
                <h3 style="font-weight: 600; margin-top: 0; margin-bottom: 1.5rem; font-size: 1rem;">📊 Salary Summary</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 2rem;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #d1d5db; margin-bottom: 0.5rem;">Gross Pay</label>
                        <div style="font-size: 1.75rem; font-weight: 700; color: #10b981;" id="modalGrossPay">₱0.00</div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #d1d5db; margin-bottom: 0.5rem;">Total Deductions</label>
                        <div style="font-size: 1.75rem; font-weight: 700; color: #f87171;" id="summaryDeductions">₱0.00</div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; color: #d1d5db; margin-bottom: 0.5rem;">Net Pay (Take-home)</label>
                        <div style="font-size: 1.75rem; font-weight: 700; color: #fbbf24;" id="modalNetPay">₱0.00</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 1rem; padding-top: 1rem; border-top: 2px solid #e5e7eb;">
                <button onclick="downloadPayslip(currentPayslipId)" style="flex: 1; padding: 0.75rem; background: #6366f1; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">⬇ Download PDF</button>
                <button onclick="closePayslipModal()" style="flex: 1; padding: 0.75rem; background: #e5e7eb; color: #1f2937; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPayslipId = null;

function selectPayrollRun(runId) {
    if (!runId) return;
    
    // Fetch the payslips view with the new payroll_run_id parameter
    const viewUrl = `dashboard.php?module=payroll&view=view_payslips&payroll_run_id=${runId}`;
    
    // Use the parent dashboard's content loader to load the new view
    if (typeof fetch !== 'undefined') {
        const loader = document.getElementById('content-loader');
        const contentArea = document.getElementById('content-area');
        
        if (loader) loader.style.display = 'block';
        
        fetch(viewUrl)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.text();
            })
            .then(html => {
                // Extract scripts BEFORE parsing
                const scriptRegex = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
                const scripts = [];
                let match;
                while ((match = scriptRegex.exec(html)) !== null) {
                    const scriptTag = match[0];
                    const scriptContent = scriptTag.replace(/<script[^>]*>/i, '').replace(/<\/script>/i, '');
                    scripts.push(scriptContent);
                }
                
                // Remove scripts from HTML
                html = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
                
                // Update content area
                if (contentArea) {
                    contentArea.innerHTML = html;
                    
                    // Execute scripts
                    scripts.forEach(script => {
                        try {
                            eval(script);
                        } catch (e) {
                            console.error('Script execution error:', e);
                        }
                    });
                }
                
                // Update URL
                window.history.pushState({}, '', viewUrl);
                
                if (loader) loader.style.display = 'none';
            })
            .catch(error => {
                console.error('Error loading payslips:', error);
                if (loader) loader.style.display = 'none';
            });
    }
}

function generatePayslips() {
    const runId = new URLSearchParams(window.location.search).get('payroll_run_id');
    if (!runId) {
        alert('Please select a payroll run first');
        return;
    }
    
    if (confirm('Generate payslips for this payroll run? This will compute gross pay, deductions, and net pay for all employees.')) {
        fetch('modules/payroll/api.php?action=generatePayslips&payroll_run_id=' + runId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payslips generated successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => alert('Error: ' + error));
    }
}

function viewPayslip(id) {
    currentPayslipId = id;
    
    // Fetch payslip details from API
    fetch('modules/payroll/api.php?action=getPayslipDetails&id=' + id, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.payslip) {
            const payslip = data.payslip;
            const earnings = data.earnings || [];
            const deductions = data.deductions || [];
            
            // Populate Employee Information
            document.getElementById('modalEmployeeName').textContent = payslip.first_name + ' ' + payslip.last_name;
            document.getElementById('modalEmployeeId').textContent = '#' + payslip.employee_code + ' (ID: ' + payslip.employee_id + ')';
            document.getElementById('modalPosition').textContent = payslip.position || '—';
            document.getElementById('modalDepartment').textContent = payslip.department || '—';
            
            // Populate Payroll Period
            document.getElementById('modalPeriodName').textContent = payslip.period_name;
            const startDate = new Date(payslip.start_date).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'});
            const endDate = new Date(payslip.end_date).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'});
            document.getElementById('modalPayPeriod').textContent = startDate + ' to ' + endDate;
            document.getElementById('modalPayDate').textContent = new Date(payslip.pay_date).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'});
            
            // Populate Earnings Container
            const earningsContainer = document.getElementById('earningsContainer');
            let totalEarnings = 0;
            
            earningsContainer.innerHTML = '';
            
            if (earnings.length > 0) {
                // Display each earnings record individually
                earnings.forEach(earning => {
                    const amount = parseFloat(earning.amount);
                    totalEarnings += amount;
                    
                    const item = document.createElement('div');
                    item.style.cssText = 'display: flex; justify-content: space-between; align-items: flex-start; padding: 0.75rem; background: white; border-radius: 4px; margin-bottom: 0.5rem; border-left: 4px solid #22c55e;';
                    
                    const label = earning.earning_type || earning.earning_code || 'Earning';
                    const description = earning.description || '—';
                    const formattedAmount = amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    item.innerHTML = `
                        <div style="flex: 1;">
                            <div style="color: #1f2937; font-weight: 500; font-size: 0.95rem;">${label}</div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">${description}</div>
                        </div>
                        <div style="color: #059669; font-weight: 600; font-size: 0.95rem; margin-left: 1rem; white-space: nowrap;">₱${formattedAmount}</div>
                    `;
                    earningsContainer.appendChild(item);
                });
            } else {
                // No detailed earnings data
                const emptyItem = document.createElement('div');
                emptyItem.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: white; border-radius: 4px; color: #6b7280; font-style: italic;';
                emptyItem.textContent = 'No detailed earnings breakdown available';
                earningsContainer.appendChild(emptyItem);
                totalEarnings = parseFloat(payslip.gross_pay);
            }
            
            document.getElementById('totalEarnings').textContent = '₱' + parseFloat(totalEarnings).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Populate Deductions Container
            const deductionsContainer = document.getElementById('deductionsContainer');
            let totalDeductions = 0;
            
            deductionsContainer.innerHTML = '';
            
            if (deductions.length > 0) {
                // Display each deduction record individually
                deductions.forEach(deduction => {
                    const amount = parseFloat(deduction.amount);
                    totalDeductions += amount;
                    
                    const item = document.createElement('div');
                    item.style.cssText = 'display: flex; justify-content: space-between; align-items: flex-start; padding: 0.75rem; background: white; border-radius: 4px; margin-bottom: 0.5rem; border-left: 4px solid #ef4444;';
                    
                    const label = deduction.type || 'Deduction';
                    const description = deduction.description || '—';
                    const formattedAmount = amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    
                    item.innerHTML = `
                        <div style="flex: 1;">
                            <div style="color: #1f2937; font-weight: 500; font-size: 0.95rem;">${label}</div>
                            <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">${description}</div>
                        </div>
                        <div style="color: #ef4444; font-weight: 600; font-size: 0.95rem; margin-left: 1rem; white-space: nowrap;">₱${formattedAmount}</div>
                    `;
                    deductionsContainer.appendChild(item);
                });
            } else {
                // No detailed deductions data
                const emptyItem = document.createElement('div');
                emptyItem.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: white; border-radius: 4px; color: #6b7280; font-style: italic;';
                emptyItem.textContent = 'No deductions recorded';
                deductionsContainer.appendChild(emptyItem);
                totalDeductions = parseFloat(payslip.total_deductions);
            }
            
            document.getElementById('totalDeductions').textContent = '₱' + parseFloat(totalDeductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summaryDeductions').textContent = '₱' + parseFloat(totalDeductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Populate financial summary
            document.getElementById('modalGrossPay').textContent = '₱' + parseFloat(payslip.gross_pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('modalNetPay').textContent = '₱' + parseFloat(payslip.net_pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Show modal
            const payslipModal = document.getElementById('payslipModal');
            if (payslipModal) payslipModal.style.display = 'flex';
        } else {
            alert('Error: ' + (data.error || 'Failed to load payslip details'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error: ' + error);
    });
}



function closePayslipModal() {
    const payslipModal = document.getElementById('payslipModal');
    if (payslipModal) payslipModal.style.display = 'none';
    currentPayslipId = null;
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const payslipModal = document.getElementById('payslipModal');
    if (payslipModal) {
        payslipModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closePayslipModal();
            }
        });
    }
});


function downloadPayslip(id) {
    window.location.href = 'modules/payroll/api.php?action=downloadPayslip&id=' + id;
}

function downloadPayslips() {
    const runId = new URLSearchParams(window.location.search).get('payroll_run_id');
    if (!runId) {
        alert('Please select a payroll run first');
        return;
    }
    
    window.location.href = 'modules/payroll/api.php?action=downloadAllPayslips&payroll_run_id=' + runId;
}
</script>
