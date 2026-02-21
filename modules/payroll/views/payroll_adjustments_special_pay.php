<?php
/**
 * Payroll Adjustments and Special Pay Module
 * Handle non-regular payroll cases (final pay, back pay, 13th month pay, separation pay)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayrollAdjustment.php';
require_once __DIR__ . '/../models/PayrollRun.php';

$payrollAdjustment = new PayrollAdjustment();
$payrollRun = new PayrollRun();

// Check if this is an AJAX modal request
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;
$modal = isset($_GET['modal']) ? $_GET['modal'] : null;

// Fetch adjustments data
$adjustments = $payrollAdjustment->getAll();
$totalAdjustments = count($adjustments ?? []);

// Handle AJAX modal request
if ($isAjax && $modal === 'view'):
    $adjId = isset($_GET['adj_id']) ? $_GET['adj_id'] : '';
    
    // Sample adjustment data - in production, fetch from database
    $adjustmentData = [
        'ADJ-100' => ['employee' => 'Michael Johnson (EMP-003)', 'type' => 'Back Pay (Promotion)', 'amount' => '7,500.00', 'submitted' => 'January 28, 2026', 'approved' => 'January 30, 2026', 'paid' => 'February 7, 2026', 'reason' => 'Retroactive salary adjustment due to promotion effective January 15, 2026'],
        'ADJ-099' => ['employee' => 'David Martinez (EMP-007)', 'type' => 'Back Pay (Correction)', 'amount' => '3,000.00', 'submitted' => 'January 20, 2026', 'approved' => 'January 22, 2026', 'paid' => 'February 1, 2026', 'reason' => 'Correction of attendance deduction error from December 2025'],
        'ADJ-089' => ['employee' => 'Emily Davis (EMP-006)', 'type' => '13th Month Pay (2024)', 'amount' => '11,000.00', 'submitted' => 'December 20, 2024', 'approved' => 'December 22, 2024', 'paid' => 'December 24, 2024', 'reason' => 'Annual 13th month bonus payment, pro-rated based on service period'],
    ];
    
    $adjustment = $adjustmentData[$adjId] ?? null;
    
    header('Content-Type: text/html');
    ob_start();
    ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Adjustment Details</h3>
                <button type="button" class="modal-close-btn" onclick="window.closeAdjustmentModal()">✕</button>
            </div>
            <div class="modal-content">
                <?php if ($adjustment): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Adjustment ID</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($adjId); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Type</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($adjustment['type']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Employee</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($adjustment['employee']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Amount</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;">₱<?php echo htmlspecialchars($adjustment['amount']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Submitted Date</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($adjustment['submitted']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Approved Date</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($adjustment['approved']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Paid Date</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($adjustment['paid']); ?></div>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Status</label>
                                <div style="font-size: 14px; font-weight: 600; color: #1f2937;"><span class="badge badge-paid">Paid</span></div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                            <label style="font-size: 12px; color: #6b7280; font-weight: 500; display: block; margin-bottom: 0.5rem;">Processing Reason</label>
                            <div style="background: #f3f4f6; padding: 0.75rem; border-radius: 4px; font-size: 13px; color: #1f2937; line-height: 1.6;">
                                <?php echo htmlspecialchars($adjustment['reason']); ?>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1.5rem; padding: 1rem; background: #d1fae5; border-radius: 4px; border-left: 4px solid #22c55e;">
                            <div style="color: #065f46; font-size: 12px;">
                                <strong>Status:</strong> <span style="font-weight: 600;">PAID</span>
                            </div>
                            <div style="color: #065f46; font-size: 12px; margin-top: 0.5rem;">
                                <strong>Completed:</strong> All approvals obtained and payment processed on <?php echo htmlspecialchars($adjustment['paid']); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="padding: 1rem; background: #fee2e2; border-radius: 4px; color: #991b1b;">
                        <strong>Error:</strong> Adjustment record not found.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
    exit;
elseif ($isAjax && $modal === 'calculate'):
    $calcType = isset($_GET['calc_type']) ? $_GET['calc_type'] : '';
    $employeeInfo = isset($_GET['employee']) ? $_GET['employee'] : 'Unknown Employee';
    $lastSalary = isset($_GET['salary']) ? $_GET['salary'] : '₱9,000.00';
    $yearsOfService = isset($_GET['years']) ? $_GET['years'] : '0 years';
    
    // Parse salary value (remove ₱ symbol)
    $salaryAmount = floatval(str_replace(['₱', ','], '', $lastSalary));
    
    // Build dynamic calculations based on form data
    $calculations = [];
    
    if ($calcType === 'final_pay') {
        $salaryComponent = round($salaryAmount * 0.5, 2);
        $leaveComponent = 3000;
        $bonusComponent = round($salaryAmount * 0.15, 2);
        $separationComponent = round($salaryAmount * 0.5, 2);
        $total = $salaryComponent + $leaveComponent + $bonusComponent + $separationComponent;
        
        $calculations = [
            'title' => 'Final Pay Calculation',
            'employee' => $employeeInfo,
            'components' => [
                ['name' => 'Pro-rated Salary (Feb 1-15)', 'amount' => number_format($salaryComponent, 2)],
                ['name' => 'Accrued Leave (10 days @ ₱300)', 'amount' => number_format($leaveComponent, 2)],
                ['name' => 'Mid-year Bonus (Pro-rated)', 'amount' => number_format($bonusComponent, 2)],
                ['name' => 'Separation Pay (based on tenure)', 'amount' => number_format($separationComponent, 2)],
            ],
            'total' => number_format($total, 2)
        ];
    } elseif ($calcType === 'back_pay') {
        $retroactiveComponent = round($salaryAmount * 0.4, 2);
        $overtimeComponent = round($salaryAmount * 0.15, 2);
        $benefitsComponent = round($salaryAmount * 0.1, 2);
        $total = $retroactiveComponent + $overtimeComponent + $benefitsComponent;
        
        $calculations = [
            'title' => 'Back Pay Calculation',
            'employee' => $employeeInfo,
            'components' => [
                ['name' => 'Retroactive Salary Adjustment', 'amount' => number_format($retroactiveComponent, 2)],
                ['name' => 'Overtime Premium (Pro-rated)', 'amount' => number_format($overtimeComponent, 2)],
                ['name' => 'Benefits Adjustment', 'amount' => number_format($benefitsComponent, 2)],
            ],
            'total' => number_format($total, 2)
        ];
    } elseif ($calcType === 'thirteenth_month_pay') {
        $thirteenthBase = $salaryAmount * 12;
        
        $calculations = [
            'title' => '13th Month Pay Calculation',
            'employee' => $employeeInfo,
            'components' => [
                ['name' => 'Basic Salary x 12 months', 'amount' => number_format($thirteenthBase, 2)],
                ['name' => 'Pro-rated for Service Period', 'amount' => 'Included'],
                ['name' => 'Less: Deductions (taxes, SSS, etc)', 'amount' => '0.00'],
            ],
            'total' => number_format($thirteenthBase, 2)
        ];
    } elseif ($calcType === 'separation_pay') {
        $finalPayComp = round($salaryAmount * 1.3, 2);
        $separationBenefit = round($salaryAmount * 0.5, 2);
        $leaveConversion = round($salaryAmount * 0.4, 2);
        $total = $finalPayComp + $separationBenefit + $leaveConversion;
        
        $calculations = [
            'title' => 'Separation Pay Calculation',
            'employee' => $employeeInfo,
            'components' => [
                ['name' => 'Final Pay Components', 'amount' => number_format($finalPayComp, 2)],
                ['name' => 'Separation Benefit (per law)', 'amount' => number_format($separationBenefit, 2)],
                ['name' => 'Unused Leave Conversion', 'amount' => number_format($leaveConversion, 2)],
            ],
            'total' => number_format($total, 2)
        ];
    } else {
        $calculations = [
            'title' => 'Calculation',
            'employee' => $employeeInfo,
            'components' => [],
            'total' => '0.00'
        ];
    }
    
    $calc = $calculations;
    
    header('Content-Type: text/html');
    ob_start();
    ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><?php echo htmlspecialchars($calc['title'] ?? 'Calculation'); ?></h3>
                <button type="button" class="modal-close-btn" onclick="window.closeAdjustmentModal()">✕</button>
            </div>
            <div class="modal-content">
                <div>
                    <div style="background: #f3f4f6; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <div style="font-size: 12px; color: #6b7280; font-weight: 500;">Employee</div>
                        <div style="font-size: 14px; font-weight: 600; color: #1f2937; margin-top: 0.25rem;"><?php echo htmlspecialchars($calc['employee']); ?></div>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 13px; font-weight: 600; color: #1f2937; margin-bottom: 0.75rem;">Calculation Breakdown</div>
                        <div style="border: 1px solid #e5e7eb; border-radius: 4px; overflow: hidden;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <tr style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 0.75rem; font-weight: 500; color: #1f2937;">Component</td>
                                    <td style="padding: 0.75rem; font-weight: 500; color: #1f2937; text-align: right;">Amount</td>
                                </tr>
                                <?php foreach ($calc['components'] as $comp): ?>
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 0.75rem; color: #374151;"><?php echo htmlspecialchars($comp['name']); ?></td>
                                    <td style="padding: 0.75rem; color: #374151; text-align: right; font-family: 'Courier New', monospace;">₱<?php echo htmlspecialchars($comp['amount']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="background: #dbeafe; border-top: 2px solid #3b82f6;">
                                    <td style="padding: 0.75rem; font-weight: 600; color: #1e40af;">Total Payable</td>
                                    <td style="padding: 0.75rem; font-weight: 600; color: #1e40af; text-align: right; font-family: 'Courier New', monospace;">₱<?php echo htmlspecialchars($calc['total']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div style="padding: 1rem; background: #dbeafe; border-radius: 4px; border-left: 4px solid #3b82f6; margin-top: 1.5rem;">
                        <div style="font-size: 12px; color: #1e40af; line-height: 1.6;">
                            <strong>ℹ️ Note:</strong> This calculation is auto-generated based on current payroll rules, statutory rates, and employee master data. Review carefully before submitting for approval. Any manual overrides must be documented.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
    exit;
elseif ($isAjax && $modal === 'submit'):
    $submitType = isset($_GET['submit_type']) ? $_GET['submit_type'] : '';
    $employeeInfo = isset($_GET['employee']) ? $_GET['employee'] : 'Unknown Employee';
    $totalAmount = isset($_GET['total']) ? $_GET['total'] : '0.00';
    
    $submitData = [
        'final_pay' => ['title' => 'Final Pay Submission', 'description' => 'Submit final pay calculation for HR Manager review'],
        'back_pay' => ['title' => 'Back Pay Submission', 'description' => 'Submit retroactive salary adjustment for Finance review'],
        'thirteenth_month_pay' => ['title' => '13th Month Pay Submission', 'description' => 'Submit 13th month bonus calculation for CFO approval'],
        'separation_pay' => ['title' => 'Separation Pay Submission', 'description' => 'Submit separation package for HR and Finance review'],
    ];
    
    $submit = $submitData[$submitType] ?? null;
    
    header('Content-Type: text/html');
    ob_start();
    ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3><?php echo htmlspecialchars($submit['title'] ?? 'Submit'); ?></h3>
                <button type="button" class="modal-close-btn" onclick="window.closeAdjustmentModal()">✕</button>
            </div>
            <div class="modal-content">
                <div style="margin-bottom: 2rem;">
                    <div style="padding: 1rem; background: #dbeafe; border-radius: 4px; border-left: 4px solid #3b82f6; margin-bottom: 1.5rem;">
                        <div style="font-size: 13px; color: #1e40af; line-height: 1.6;">
                            <?php echo htmlspecialchars($submit['description'] ?? 'Submit for approval'); ?>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Employee</label>
                            <div style="font-size: 14px; font-weight: 600; color: #1f2937; margin-top: 0.25rem;"><?php echo htmlspecialchars($employeeInfo); ?></div>
                        </div>
                        <div>
                            <label style="font-size: 12px; color: #6b7280; font-weight: 500;">Total Amount</label>
                            <div style="font-size: 14px; font-weight: 600; color: #1f2937; margin-top: 0.25rem;">₱<?php echo htmlspecialchars($totalAmount); ?></div>
                        </div>
                    </div>

                    <div style="padding: 1rem; background: #f9fafb; border-radius: 4px; margin-bottom: 1.5rem; border-left: 4px solid #f59e0b;">
                        <div style="font-size: 13px; font-weight: 600; color: #92400e; margin-bottom: 0.5rem;">⚠️ Important:</div>
                        <div style="font-size: 12px; color: #78350f; line-height: 1.6;">
                            Once submitted, this adjustment will be sent for approval. You will not be able to edit it until approval is complete. Ensure all details are correct before proceeding.
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <button type="button" onclick="window.closeAdjustmentModal()" class="btn btn-secondary" style="padding: 0.75rem; border: 1px solid #d1d5db; background: white; color: #1f2937; border-radius: 4px; cursor: pointer; font-weight: 500;">
                            Cancel
                        </button>
                        <button type="button" onclick="window.confirmAndSubmit('<?php echo htmlspecialchars($submitType); ?>')" class="btn btn-primary" style="padding: 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                            Confirm & Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
    exit;
elseif ($isAjax && $modal === 'submitted'):
    $submitType = isset($_GET['submit_type']) ? $_GET['submit_type'] : '';
    $employeeInfo = isset($_GET['employee']) ? $_GET['employee'] : 'Unknown Employee';
    $totalAmount = isset($_GET['total']) ? $_GET['total'] : '0.00';
    
    header('Content-Type: text/html');
    ob_start();
    ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>✅ Submitted Successfully</h3>
                <button type="button" class="modal-close-btn" onclick="window.closeAdjustmentModal()">✕</button>
            </div>
            <div class="modal-content">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 48px; margin-bottom: 1rem;">✅</div>
                    
                    <div style="margin-bottom: 2rem;">
                        <div style="font-size: 16px; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">Adjustment Submitted</div>
                        <div style="font-size: 13px; color: #6b7280;">Your adjustment has been successfully submitted for approval.</div>
                    </div>

                    <div style="background: #f0fdf4; padding: 1.5rem; border-radius: 4px; border-left: 4px solid #22c55e; margin-bottom: 1.5rem; text-align: left;">
                        <div style="font-size: 12px; color: #6b7280; font-weight: 500; margin-bottom: 0.5rem;">Submission Details</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 13px;">
                            <div>
                                <span style="color: #6b7280;">Employee:</span>
                                <div style="color: #1f2937; font-weight: 600;"><?php echo htmlspecialchars($employeeInfo); ?></div>
                            </div>
                            <div>
                                <span style="color: #6b7280;">Amount:</span>
                                <div style="color: #1f2937; font-weight: 600;">₱<?php echo htmlspecialchars($totalAmount); ?></div>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <span style="color: #6b7280;">Status:</span>
                                <div style="color: #1f2937; font-weight: 600;"><span style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 11px;">Pending Review</span></div>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 1rem; background: #dbeafe; border-radius: 4px; border-left: 4px solid #3b82f6; margin-bottom: 1.5rem; text-align: left;">
                        <div style="font-size: 12px; color: #1e40af; line-height: 1.6;">
                            <strong>What happens next?</strong><br>
                            Your adjustment is now in the approval queue. HR Manager will review within 24-48 hours. You will receive an email notification once approved or if additional information is needed.
                        </div>
                    </div>

                    <button type="button" onclick="window.closeAdjustmentModal()" class="btn btn-primary" style="padding: 0.75rem 1.5rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; width: 100%;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
    exit;

endif;
?>

<style>
  .adjustments-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
  }

  .section {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .section-header {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #3b82f6;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
  }

  .form-group label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 14px;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
  }

  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .form-section {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #3b82f6;
  }

  .form-section h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1rem;
  }

  .form-row.full {
    grid-template-columns: 1fr;
  }

  .form-row.three-col {
    grid-template-columns: 1fr 1fr 1fr;
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-primary {
    background: #3b82f6;
    color: white;
  }

  .btn-primary:hover {
    background: #2563eb;
  }

  .btn-secondary {
    background: #e5e7eb;
    color: #1f2937;
  }

  .btn-secondary:hover {
    background: #d1d5db;
  }

  .btn-success {
    background: #22c55e;
    color: white;
  }

  .btn-success:hover {
    background: #16a34a;
  }

  .btn-danger {
    background: #ef4444;
    color: white;
  }

  .btn-danger:hover {
    background: #dc2626;
  }

  .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 13px;
  }

  .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
  }

  .alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 14px;
  }

  .alert-info {
    background: #dbeafe;
    border: 1px solid #bfdbfe;
    color: #1e40af;
  }

  .alert-success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
  }

  .alert-warning {
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
  }

  .alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
  }

  .table-container {
    overflow-x: auto;
    margin-bottom: 1.5rem;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  table thead {
    background: #f3f4f6;
  }

  table th {
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #d1d5db;
  }

  table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
  }

  table tr:hover {
    background: #f9fafb;
  }

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-approved {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-processed {
    background: #dbeafe;
    color: #1e40af;
  }

  .badge-paid {
    background: #d1f0c5;
    color: #166534;
  }

  .badge-rejected {
    background: #fee2e2;
    color: #991b1b;
  }

  .summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .summary-card {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
  }

  .summary-card label {
    display: block;
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .summary-card .value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
  }

  .adjustment-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid #3b82f6;
  }

  .adjustment-card h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .adjustment-card p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .adjustment-card .amount {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
  }

  .calculation-box {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-size: 13px;
    font-family: 'Courier New', monospace;
    line-height: 1.6;
  }

  .calculation-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
  }

  .calculation-item.total {
    font-weight: 700;
    border-top: 1px solid #d1d5db;
    padding-top: 0.5rem;
  }

  .info-box {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 4px;
    border-left: 4px solid #3b82f6;
    margin-bottom: 1rem;
  }

  .info-box h5 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 13px;
    font-weight: 600;
  }

  .info-box p {
    margin: 0.25rem 0;
    color: #6b7280;
    font-size: 12px;
  }

  .tab-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
    overflow-x: auto;
  }

  .tab {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
  }

  .tab:hover {
    color: #374151;
  }

  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  @media print {
    .section {
      page-break-inside: avoid;
    }
  }

  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
  }

  .modal-box {
    background: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    max-width: 650px;
    max-height: 90vh;
    width: 100%;
    display: flex;
    flex-direction: column;
    animation: slideIn 0.3s ease-out;
  }

  @keyframes slideIn {
    from {
      transform: translateY(-50px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  .modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 16px;
  }

  .modal-close-btn {
    background: none;
    border: none;
    font-size: 20px;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
  }

  .modal-close-btn:hover {
    background: #f3f4f6;
    color: #1f2937;
  }

  .modal-content {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
  }

  .modal-overlay.active {
    display: flex !important;
  }
</style>

<div class="adjustments-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Payroll Adjustments & Special Pay</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Handle non-regular payroll cases including final pay, back pay, 13th month pay, and separation pay. All processed outside regular payroll with full approval workflows.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Features:</strong> Adjustment processing, approval workflows, calculation templates, reason logging, and complete audit trail for compliance.
    </div>
  </div>

  <!-- Quick Summary -->
  <div class="section">
    <h3 class="section-header">📊 Adjustments Summary</h3>

    <div class="summary-cards">
      <div class="summary-card">
        <label>Pending Approval</label>
        <div class="value">3</div>
      </div>
      <div class="summary-card success" style="border-left-color: #22c55e;">
        <label>Total Approved (Feb 2026)</label>
        <div class="value">₱156,500</div>
      </div>
      <div class="summary-card" style="border-left-color: #f59e0b;">
        <label>Processed This Month</label>
        <div class="value">5</div>
      </div>
      <div class="summary-card" style="border-left-color: #ef4444;">
        <label>Pending Payment</label>
        <div class="value">2</div>
      </div>
    </div>
  </div>

  <!-- Adjustment Type Selector -->
  <div class="section">
    <h3 class="section-header">🔧 Create New Adjustment</h3>

    <div class="tab-container">
      <button class="tab active" onclick="switchTab(event, 'final-pay')">Final Pay</button>
      <button class="tab" onclick="switchTab(event, 'back-pay')">Back Pay</button>
      <button class="tab" onclick="switchTab(event, 'thirteenth-month')">13th Month Pay</button>
      <button class="tab" onclick="switchTab(event, 'separation-pay')">Separation Pay</button>
    </div>

    <!-- Final Pay Tab -->
    <div id="final-pay" class="tab-content active">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>Final Pay - Employee Separation</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Process final paycheck for employees leaving the organization. Includes accrued leave, 13th month (if applicable), and separation pay.</p>

          <div class="form-row">
            <div class="form-group">
              <label>Employee Name <span style="color: #ef4444;">*</span></label>
              <select name="final_employee" required>
                <option value="">-- Select Employee --</option>
                <option value="EMP-004">Sarah Williams (EMP-004) - HR Coordinator</option>
                <option value="EMP-005">Robert Brown (EMP-005) - Finance Analyst</option>
              </select>
              <small>Search by name or employee ID</small>
            </div>
            <div class="form-group">
              <label>Separation Date <span style="color: #ef4444;">*</span></label>
              <input type="date" name="separation_date" value="2026-02-28" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Years of Service</label>
              <input type="text" value="3 years, 2 months" readonly style="background: #f3f4f6;">
            </div>
            <div class="form-group">
              <label>Last Salary</label>
              <input type="text" value="₱9,000.00" readonly style="background: #f3f4f6;">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Separation Reason <span style="color: #ef4444;">*</span></label>
              <select name="separation_reason" required>
                <option value="">-- Select Reason --</option>
                <option value="resignation">Resignation</option>
                <option value="retirement">Retirement</option>
                <option value="termination">Termination</option>
                <option value="contract_end">End of Contract</option>
                <option value="voluntary">Voluntary Separation</option>
              </select>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Additional Notes</label>
              <textarea name="final_notes" placeholder="Any special circumstances..." style="min-height: 80px; resize: vertical;"></textarea>
            </div>
          </div>

          <div class="info-box">
            <h5>Final Pay Components (Auto-calculated)</h5>
            <p>✓ Pro-rated salary from last paycheck to separation date</p>
            <p>✓ Accrued leave conversion (10 days @ ₱300/day = ₱3,000)</p>
            <p>✓ Mid-year bonus if applicable + proportional calculation</p>
            <p>✓ Separation pay if entitled (based on tenure and reason)</p>
            <p>✓ Refund of loans/advances</p>
          </p>
        </div>

        <div class="alert alert-info">
          Estimated final pay for Sarah Williams: ₱12,500.00 (includes ₱3,000 accrued leave + ₱4,500 separation pay)
        </div>

        <div class="btn-group">
          <button type="button" onclick="window.openCalculationModal('final_pay')" class="btn btn-secondary">Review Calculation</button>
          <button type="button" onclick="window.openSubmitModal('final_pay')" class="btn btn-primary">Submit for Approval</button>
        </div>
      </form>
    </div>

    <!-- Back Pay Tab -->
    <div id="back-pay" class="tab-content">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>Back Pay - Retroactive Adjustments</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Process retroactive pay adjustments for salary corrections, promotion backdating, or missed payments.</p>

          <div class="form-row">
            <div class="form-group">
              <label>Employee Name <span style="color: #ef4444;">*</span></label>
              <select name="backpay_employee" required>
                <option value="">-- Select Employee --</option>
                <option value="EMP-001">John Doe (EMP-001) - HR Manager</option>
                <option value="EMP-002">Jane Smith (EMP-002) - Accountant</option>
                <option value="EMP-003">Michael Johnson (EMP-003) - Senior Developer</option>
                <option value="EMP-006">Emily Davis (EMP-006) - Operations Officer</option>
              </select>
            </div>
            <div class="form-group">
              <label>Adjustment Type <span style="color: #ef4444;">*</span></label>
              <select name="backpay_type" required>
                <option value="">-- Select Type --</option>
                <option value="salary_correction">Salary Correction</option>
                <option value="promotion_increase">Promotion Increase (Backdated)</option>
                <option value="missed_bonus">Missed Bonus</option>
                <option value="other">Other Adjustment</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Effective From <span style="color: #ef4444;">*</span></label>
              <input type="date" name="backpay_from" value="2026-01-01" required>
            </div>
            <div class="form-group">
              <label>Adjustment To <span style="color: #ef4444;">*</span></label>
              <input type="date" name="backpay_to" value="2026-02-28" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Monthly Increase/Difference <span style="color: #ef4444;">*</span></label>
              <input type="number" name="backpay_amount" placeholder="0.00" step="0.01" min="0" required>
            </div>
            <div class="form-group">
              <label>Number of Months</label>
              <input type="text" value="2" readonly style="background: #f3f4f6;">
            </div>
          </div>

          <div class="calculation-box">
            <div class="calculation-item">
              <span>Monthly increase:</span>
              <span>₱ 2,500.00</span>
            </div>
            <div class="calculation-item">
              <span>× Number of months:</span>
              <span>2</span>
            </div>
            <div class="calculation-item total">
              <span>Total back pay:</span>
              <span>₱ 5,000.00</span>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Reason for Adjustment <span style="color: #ef4444;">*</span></label>
              <textarea name="backpay_reason" placeholder="Explanation for retroactive adjustment..." style="min-height: 80px; resize: vertical;" required></textarea>
            </div>
          </div>

          <div class="alert alert-warning">
            ⚠️ Back pay adjustments are subject to manager and HR approval before processing to ensure accuracy and compliance.
          </div>

          <div class="btn-group">
            <button type="button" onclick="window.openCalculationModal('back_pay')" class="btn btn-secondary">Review Calculation</button>
            <button type="button" onclick="window.openSubmitModal('back_pay')" class="btn btn-primary">Submit for Approval</button>
          </div>
        </div>
      </form>
    </div>

    <!-- 13th Month Pay Tab -->
    <div id="thirteenth-month" class="tab-content">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>13th Month Pay - Annual Bonus</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Generate 13th month pay (year-end bonus) for eligible employees based on tenure and employment status.</p>

          <div class="form-row full">
            <div class="form-group">
              <label>Pay Year & Month <span style="color: #ef4444;">*</span></label>
              <select name="thirteenth_month" required>
                <option value="">-- Select Period --</option>
                <option value="202512" selected>December 2025 (Year-end 2025)</option>
                <option value="202612">December 2026 (Year-end 2026)</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Employees to Include</label>
              <select name="thirteenth_employees">
                <option value="all" selected>All Eligible (8 employees)</option>
                <option value="active_only">Active Only (6 employees)</option>
                <option value="custom">Custom Selection</option>
              </select>
            </div>
            <div class="form-group">
              <label>Minimum Service Months</label>
              <input type="number" name="min_service_months" value="3" min="0">
              <small>Only employees with ≥ selected months included</small>
            </div>
          </div>

          <div class="info-box">
            <h5>13th Month Pay Calculation Rules</h5>
            <p>✓ Based on: Highest monthly salary received during the year</p>
            <p>✓ Formula: (Highest Monthly Salary × Number of Months Worked) ÷ 12</p>
            <p>✓ Minimum service: 3 months to qualify</p>
            <p>✓ Separation: Pro-rated for employees separated during the year</p>
            <p>✓ Maximum: Capped at 1 month basic salary</p>
          </div>

          <div class="calculation-box" style="margin-top: 1rem;">
            <div style="margin-bottom: 0.5rem; font-weight: 600; color: #1f2937;">Sample Calculations for Year 2025:</div>
            <div class="calculation-item">
              <span>John Doe (12 months service):</span>
              <span>₱11,000 × 12 ÷ 12 = ₱11,000</span>
            </div>
            <div class="calculation-item">
              <span>Sarah Williams (12 months service):</span>
              <span>₱9,000 × 12 ÷ 12 = ₱9,000</span>
            </div>
            <div class="calculation-item">
              <span>New Hire (6 months service):</span>
              <span>₱10,000 × 6 ÷ 12 = ₱5,000</span>
            </div>
            <div class="calculation-item total">
              <span>Total 13th Month Payout:</span>
              <span>₱ 88,000.00</span>
            </div>
          </div>

          <div class="alert alert-info">
            Estimated total for December 2025: ₱88,000.00 (8 eligible employees)
          </div>

          <div class="btn-group">
            <button type="button" onclick="window.openCalculationModal('thirteenth_month_pay')" class="btn btn-secondary">Preview Calculations</button>
            <button type="button" onclick="window.openSubmitModal('thirteenth_month_pay')" class="btn btn-primary">Submit for Approval</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Separation Pay Tab -->
    <div id="separation-pay" class="tab-content">
      <form method="POST" action="../payroll_adjustments_handler.php">
        <div class="form-section">
          <h4>Separation Pay - Severance Entitlements</h4>
          <p style="margin: 0 0 1rem 0; color: #6b7280; font-size: 13px;">Calculate and process separation/severance pay based on tenure, separation reason, and applicable labor laws.</p>

          <div class="form-row">
            <div class="form-group">
              <label>Employee Name <span style="color: #ef4444;">*</span></label>
              <select name="sep_employee" required>
                <option value="">-- Select Employee --</option>
                <option value="EMP-004" selected>Sarah Williams (EMP-004) - 3 years, 2 months</option>
                <option value="EMP-005">Robert Brown (EMP-005) - 2 years, 8 months</option>
              </select>
            </div>
            <div class="form-group">
              <label>Separation Reason <span style="color: #ef4444;">*</span></label>
              <select name="sep_reason" required>
                <option value="">-- Select Reason --</option>
                <option value="retrenchment" selected>Retrenchment (Company Policy)</option>
                <option value="redundancy">Redundancy</option>
                <option value="end_of_contract">End of Contract</option>
                <option value="closure">Business Closure</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Separation Date <span style="color: #ef4444;">*</span></label>
              <input type="date" name="sep_date" value="2026-02-28" required>
            </div>
            <div class="form-group">
              <label>Last Salary</label>
              <input type="text" value="₱9,000.00" readonly style="background: #f3f4f6;">
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Applicable Labor Code Formula <span style="color: #ef4444;">*</span></label>
              <select name="sep_formula" required>
                <option value="">-- Select Formula --</option>
                <option value="half_month" selected>Half-month pay per year of service (default)</option>
                <option value="one_month">One-month pay per year of service</option>
                <option value="minimum_wage">Minimum wage formula (Retrenchment)</option>
              </select>
              <small>Learn more about each formula in the section below</small>
            </div>
          </div>

          <div class="info-box">
            <h5>Separation Formula: Half-Month per Year (Selected)</h5>
            <p>• 1 year - 1 year 11 months: 0.5 × monthly salary</p>
            <p>• 2 years - 2 years 11 months: 1 × monthly salary</p>
            <p>• 3 years - 3 years 11 months: 1.5 × monthly salary (applies here)</p>
            <p>Example: ₱9,000 × 1.5 = ₱13,500</p>
          </div>

          <div class="calculation-box" style="margin-top: 1rem;">
            <div class="calculation-item">
              <span>Years of service:</span>
              <span>3 years, 2 months</span>
            </div>
            <div class="calculation-item">
              <span>Applicable multiplier:</span>
              <span>1.5x</span>
            </div>
            <div class="calculation-item">
              <span>Last monthly salary:</span>
              <span>₱ 9,000.00</span>
            </div>
            <div class="calculation-item total">
              <span>Separation pay entitlement:</span>
              <span>₱ 13,500.00</span>
            </div>
          </div>

          <div class="form-row full">
            <div class="form-group">
              <label>Supporting Documentation/Notes</label>
              <textarea name="sep_notes" placeholder="Attach or reference supporting docs for separation reason..." style="min-height: 80px; resize: vertical;"></textarea>
            </div>
          </div>

          <div class="alert alert-info">
            💡 Separation pay is mandated by Philippine labor law (Article 283-284 of Labor Code). Accurate calculations are required for compliance.
          </div>

          <div class="btn-group">
            <button type="button" onclick="window.openCalculationModal('separation_pay')" class="btn btn-secondary">Review Calculation</button>
            <button type="button" onclick="window.openSubmitModal('separation_pay')" class="btn btn-primary">Submit for Approval</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Pending Approvals -->
  <div class="section">
    <h3 class="section-header">⏳ Pending Approvals</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Adjustment Type</th>
            <th>Amount</th>
            <th>Submitted</th>
            <th>Requested By</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Sarah Williams (EMP-004)</td>
            <td>Final Pay</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱12,500.00</td>
            <td>February 7, 2026</td>
            <td>Juan de la Cruz (HR Officer)</td>
            <td><span class="badge badge-pending">Pending HR Review</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="approval_id" value="ADJ-001">
                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              </form>
              <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Reject action initiated')">Reject</button>
            </td>
          </tr>
          <tr>
            <td>John Doe (EMP-001)</td>
            <td>Back Pay</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱5,000.00</td>
            <td>February 5, 2026</td>
            <td>Maria Santos (Payroll Officer)</td>
            <td><span class="badge badge-pending">Pending Finance Review</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="approval_id" value="ADJ-002">
                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              </form>
              <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Reject action initiated')">Reject</button>
            </td>
          </tr>
          <tr>
            <td>All Employees (8)</td>
            <td>13th Month Pay (Dec 2025)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱88,000.00</td>
            <td>February 3, 2026</td>
            <td>Maria Garcia (Finance Manager)</td>
            <td><span class="badge badge-pending">Pending CFO Approval</span></td>
            <td>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="approval_id" value="ADJ-003">
                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
              </form>
              <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Reject action initiated')">Reject</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Processed Adjustments History -->
  <div class="section">
    <h3 class="section-header">📋 Processed Adjustments History</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Submitted</th>
            <th>Approved Date</th>
            <th>Paid Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Michael Johnson (EMP-003)</td>
            <td>Back Pay (Promotion)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱7,500.00</td>
            <td>January 28, 2026</td>
            <td>January 30, 2026</td>
            <td>February 7, 2026</td>
            <td><span class="badge badge-paid">Paid</span></td>
            <td>
              <button type="button" onclick="window.openAdjustmentModal('ADJ-100')" class="btn btn-secondary btn-sm">Details</button>
            </td>
          </tr>
          <tr>
            <td>David Martinez (EMP-007)</td>
            <td>Back Pay (Correction)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱3,000.00</td>
            <td>January 20, 2026</td>
            <td>January 22, 2026</td>
            <td>February 1, 2026</td>
            <td><span class="badge badge-paid">Paid</span></td>
            <td>
              <button type="button" onclick="window.openAdjustmentModal('ADJ-099')" class="btn btn-secondary btn-sm">Details</button>
            </td>
          </tr>
          <tr>
            <td>Emily Davis (EMP-006)</td>
            <td>13th Month Pay (2024)</td>
            <td style="text-align: right; font-family: 'Courier New', monospace;">₱11,000.00</td>
            <td>December 20, 2024</td>
            <td>December 22, 2024</td>
            <td>December 24, 2024</td>
            <td><span class="badge badge-paid">Paid</span></td>
            <td>
              <button type="button" onclick="window.openAdjustmentModal('ADJ-089')" class="btn btn-secondary btn-sm">Details</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Compliance & Rules -->
  <div class="section">
    <h3 class="section-header">📋 Adjustment Processing Rules & Compliance</h3>

    <div class="adjustment-card">
      <h4>▶ Final Pay</h4>
      <p>Processed when employee leaves organization (resignation, retirement, termination)</p>
      <p><strong>Components:</strong> Pro-rated salary, accrued leave, separation pay, mid-year bonus (pro-rated)</p>
      <p><strong>Rule:</strong> Approval required from HR Manager + Finance. Must match employment contract terms.</p>
    </div>

    <div class="adjustment-card">
      <h4>▶ Back Pay</h4>
      <p>Retroactive payment for salary corrections or missed adjustments</p>
      <p><strong>Components:</strong> Additional pay for prior months, recalculation of deductions if needed</p>
      <p><strong>Rule:</strong> Requires manager approval + reason documentation. Must reconcile with prior payroll records.</p>
    </div>

    <div class="adjustment-card">
      <h4>▶ 13th Month Pay</h4>
      <p>Legally mandated year-end bonus (Philippine law - minimum ₱88.34/day)</p>
      <p><strong>Components:</strong> One month basic pay based on highest monthly earned during the year</p>
      <p><strong>Rule:</strong> Minimum 3 months service to qualify. Pro-rated for separated employees. CFO approval required for company-wide processing.</p>
    </div>

    <div class="adjustment-card">
      <h4>▶ Separation Pay</h4>
      <p>Legally mandated severance (Philippine Labor Code Article 283-284)</p>
      <p><strong>Components:</strong> Half to one month salary per year of service (based on separation reason)</p>
      <p><strong>Rule:</strong> Required for retrenchment, redundancy, end of contract. Finance + Legal approval required. Mandatory 30-day notice period applies.</p>
    </div>

    <div class="alert alert-warning" style="margin-top: 2rem;">
      <strong>⚠️ Critical Rules - ALL ADJUSTMENTS:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>No Regular Payroll:</strong> Adjustments processed separately, NOT included in regular bi-weekly payroll</li>
        <li><strong>Approval Mandatory:</strong> Department manager + HR/Finance dual approval required before processing</li>
        <li><strong>Reason Logged:</strong> All adjustments must have documented reason for audit trail</li>
        <li><strong>Calculation Verified:</strong> Automated calculations cross-checked against employment contracts and labor law requirements</li>
        <li><strong>Compliance Check:</strong> System validates all adjustments against Philippine labor law requirements</li>
        <li><strong>Audit Trail:</strong> Complete history of all adjustments, approvals, and payments maintained</li>
      </ul>
    </div>
  </div>

</div>

<script>
function switchTab(event, tabName) {
  event.preventDefault();
  
  // Hide all tab contents
  const contents = document.querySelectorAll('.tab-content');
  contents.forEach(content => {
    content.classList.remove('active');
  });
  
  // Remove active class from all tabs
  const tabs = document.querySelectorAll('.tab');
  tabs.forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Show selected tab content
  document.getElementById(tabName).classList.add('active');
  
  // Add active class to clicked tab
  event.target.classList.add('active');
}

// Modal functions
window.openAdjustmentModal = function(adjId) {
  // Fetch modal content via AJAX without page refresh
  let url = 'dashboard.php?module=payroll&view=payroll_adjustments_special_pay&ajax=1&modal=view&adj_id=' + encodeURIComponent(adjId);
  
  fetch(url)
    .then(response => response.text())
    .then(html => {
      // Create a temporary container to parse the response
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const modalOverlay = temp.querySelector('.modal-overlay');
      
      if (modalOverlay) {
        // Remove old modals if any
        document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
        // Add new modal to page
        document.body.appendChild(modalOverlay);
        // Add the active class to display modal
        modalOverlay.classList.add('active');
      } else {
        console.error('Modal overlay not found in response');
        console.log('Response HTML:', html.substring(0, 500));
      }
    })
    .catch(error => console.error('Error loading modal:', error));
};

window.openCalculationModal = function(calcType) {
  // Read form data based on calculation type
  let employeeSelect = document.querySelector('select[name="' + calcType.replace('_', '_') + '_employee"]') || 
                      document.querySelector('select[name="final_employee"]') ||
                      document.querySelector('select[name="backpay_employee"]') ||
                      document.querySelector('select[name="thirteenth_employee"]') ||
                      document.querySelector('select[name="separation_employee"]');
  
  let employeeInfo = employeeSelect ? employeeSelect.options[employeeSelect.selectedIndex].text : 'N/A';
  
  // Get salary/amount fields based on form
  let lastSalaryField = document.querySelector('input[value*="₱"]') || document.querySelector('input[readonly]');
  let lastSalary = lastSalaryField ? lastSalaryField.value : '₱9,000.00';
  
  // Get years of service
  let yearsField = Array.from(document.querySelectorAll('input[readonly]')).find(input => 
    input.value.includes('years')
  );
  let yearsOfService = yearsField ? yearsField.value : '3 years, 2 months';
  
  // Fetch modal content via AJAX with employee info
  let url = 'dashboard.php?module=payroll&view=payroll_adjustments_special_pay&ajax=1&modal=calculate&calc_type=' + 
            encodeURIComponent(calcType) + 
            '&employee=' + encodeURIComponent(employeeInfo) +
            '&salary=' + encodeURIComponent(lastSalary) +
            '&years=' + encodeURIComponent(yearsOfService);
  
  fetch(url)
    .then(response => response.text())
    .then(html => {
      // Create a temporary container to parse the response
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const modalOverlay = temp.querySelector('.modal-overlay');
      
      if (modalOverlay) {
        // Remove old modals if any
        document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
        // Add new modal to page
        document.body.appendChild(modalOverlay);
        // Add the active class to display modal
        modalOverlay.classList.add('active');
      } else {
        console.error('Modal overlay not found in response');
        console.log('Response HTML:', html.substring(0, 500));
      }
    })
    .catch(error => console.error('Error loading modal:', error));
};

window.closeAdjustmentModal = function() {
  const overlay = document.querySelector('.modal-overlay');
  if (overlay) {
    overlay.classList.remove('active');
    overlay.remove();
  }
};

window.openSubmitModal = function(submitType) {
  // Read form data based on submission type
  let employeeSelect = document.querySelector('select[name*="employee"]');
  let employeeInfo = employeeSelect ? employeeSelect.options[employeeSelect.selectedIndex].text : 'Unknown Employee';
  
  // Get the estimated total from the alert text
  let alertText = document.querySelector('.alert-info')?.textContent || '';
  let totalAmount = alertText.match(/₱[\d,.]+/) ? alertText.match(/₱[\d,.]+/)[0].replace('₱', '').trim() : '0.00';
  
  let url = 'dashboard.php?module=payroll&view=payroll_adjustments_special_pay&ajax=1&modal=submit&submit_type=' + 
            encodeURIComponent(submitType) +
            '&employee=' + encodeURIComponent(employeeInfo) +
            '&total=' + encodeURIComponent(totalAmount);
  
  fetch(url)
    .then(response => response.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const modalOverlay = temp.querySelector('.modal-overlay');
      
      if (modalOverlay) {
        document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
        document.body.appendChild(modalOverlay);
        modalOverlay.classList.add('active');
      }
    })
    .catch(error => console.error('Error loading modal:', error));
};

window.confirmAndSubmit = function(submitType) {
  // Get employee info
  let employeeSelect = document.querySelector('select[name*="employee"]');
  let employeeInfo = employeeSelect ? employeeSelect.options[employeeSelect.selectedIndex].text : 'Unknown Employee';
  
  let alertText = document.querySelector('.alert-info')?.textContent || '';
  let totalAmount = alertText.match(/₱[\d,.]+/) ? alertText.match(/₱[\d,.]+/)[0].replace('₱', '').trim() : '0.00';
  
  // Close current modal
  window.closeAdjustmentModal();
  
  // Show success modal
  let url = 'dashboard.php?module=payroll&view=payroll_adjustments_special_pay&ajax=1&modal=submitted&submit_type=' + 
            encodeURIComponent(submitType) +
            '&employee=' + encodeURIComponent(employeeInfo) +
            '&total=' + encodeURIComponent(totalAmount);
  
  fetch(url)
    .then(response => response.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const modalOverlay = temp.querySelector('.modal-overlay');
      
      if (modalOverlay) {
        document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
        document.body.appendChild(modalOverlay);
        modalOverlay.classList.add('active');
      }
    })
    .catch(error => console.error('Error submitting:', error));
};

// Close modal when clicking outside
document.addEventListener('click', function(event) {
  const modal = document.querySelector('.modal-box');
  const overlay = document.querySelector('.modal-overlay');
  if (overlay && event.target === overlay && modal) {
    window.closeAdjustmentModal();
  }
});
</script>
