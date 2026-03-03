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

<script>
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
    alert('View payslip ' + id + ' - To be implemented');
}

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
