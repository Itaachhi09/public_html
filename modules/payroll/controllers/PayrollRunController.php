<?php
/**
 * PayrollRunController
 * Handles payroll run operations: preview, process, approval workflow
 * No direct file calls - all routing through dashboard.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/BaseConfig.php';
require_once __DIR__ . '/../models/PayrollRun.php';
require_once __DIR__ . '/../models/PayrollRunEmployee.php';
require_once __DIR__ . '/../models/PayrollApproval.php';

class PayrollRunController {
    private $db;
    private $payrollRun;
    private $payrollRunEmployee;
    private $payrollApproval;

    public function __construct() {
        // Use singleton instance to ensure database connection is established
        $this->db = Database::getInstance();
        $this->payrollRun = new PayrollRun();
        $this->payrollRunEmployee = new PayrollRunEmployee();
        $this->payrollApproval = new PayrollApproval();
    }

    /**
     * Preview payroll details before approval
     * Fetches payroll run data with all computed earnings and deductions
     * @param int $payroll_id The payroll run ID
     * @return array Data for view with success/error status
     */
    public function previewPayroll($payroll_id) {
        $payroll_id = (int) $payroll_id;
        
        // Validate payroll exists
        $payrollData = $this->payrollRun->find($payroll_id);
        if (!$payrollData) {
            return [
                'success' => false,
                'error' => 'Payroll run not found.',
                'payroll_id' => $payroll_id
            ];
        }

        // Fetch employee details for this payroll
        $employees = $this->payrollRunEmployee->getByRunWithEmployee($payroll_id);
        
        // Compute totals
        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        
        foreach ($employees as $emp) {
            $totalGross += (float) $emp['gross_pay'];
            $totalDeductions += (float) $emp['total_deductions'];
            $totalNet += (float) $emp['net_pay'];
        }

        // Check if already has filled approval record
        $approvalRecord = $this->db->query(
            "SELECT * FROM payroll_approvals WHERE payroll_run_id = ? LIMIT 1",
            [$payroll_id]
        );

        $approvalStatus = null;
        if (!empty($approvalRecord)) {
            $approvalStatus = $approvalRecord[0]['approval_status'] ?? null;
        }

        return [
            'success' => true,
            'payroll_id' => $payroll_id,
            'payroll_data' => $payrollData,
            'employees' => $employees,
            'totals' => [
                'gross' => $totalGross,
                'deductions' => $totalDeductions,
                'net' => $totalNet,
                'employee_count' => count($employees)
            ],
            'approval_status' => $approvalStatus
        ];
    }

    /**
     * Process payroll run - moves status from DRAFT to FOR_APPROVAL
     * Validates eligibility, logs action, updates status
     * @param int $payroll_id The payroll run ID
     * @return array Success/error response
     */
    public function processPayroll($payroll_id) {
        $payroll_id = (int) $payroll_id;
        $user_id = $_SESSION['user_id'] ?? 0;
        
        // Fetch payroll
        $payroll = $this->payrollRun->find($payroll_id);
        if (!$payroll) {
            return [
                'success' => false,
                'error' => 'Payroll run not found.'
            ];
        }

        // Validate status is DRAFT
        if ($payroll['status'] !== 'Draft' && $payroll['status'] !== 'Processed') {
            return [
                'success' => false,
                'error' => 'Payroll can only be processed from Draft or Processed status.'
            ];
        }

        // Check if already has employees
        $employeeCount = $this->db->query(
            "SELECT COUNT(*) as count FROM payroll_run_employees WHERE payroll_run_id = ?",
            [$payroll_id]
        );
        
        if (empty($employeeCount) || (int) $employeeCount[0]['count'] === 0) {
            return [
                'success' => false,
                'error' => 'Payroll has no employees. Cannot process empty payroll.'
            ];
        }

        // Update payroll status
        $this->payrollRun->update($payroll_id, [
            'status' => 'For Approval'
        ]);

        // Log action
        $this->logAction($payroll_id, $user_id, 'SUBMITTED_FOR_APPROVAL', 
            'Payroll submitted for HR approval');

        return [
            'success' => true,
            'message' => 'Payroll moved to approval workflow.',
            'payroll_id' => $payroll_id
        ];
    }

    /**
     * Approve payroll at HR level
     * @param int $payroll_id Payroll run ID
     * @param string $notes Review notes
     * @return array Success/error response
     */
    public function approveHR($payroll_id, $notes = '') {
        $payroll_id = (int) $payroll_id;
        $user_id = $_SESSION['user_id'] ?? 0;

        try {
            // Find or create approval record
            $record = $this->db->query(
                "SELECT * FROM payroll_approvals WHERE payroll_run_id = ?",
                [$payroll_id]
            );

            if (empty($record)) {
                $this->db->query(
                    "INSERT INTO payroll_approvals (payroll_run_id, approval_level, approver_id, approval_status, comments, approval_date) 
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$payroll_id, 1, $user_id, 'approved', $notes, date('Y-m-d H:i:s')]
                );
            } else {
                $this->db->query(
                    "UPDATE payroll_approvals SET approver_id = ?, approval_status = ?, comments = ?, approval_date = ?
                     WHERE payroll_run_id = ?",
                    [$user_id, 'approved', $notes, date('Y-m-d H:i:s'), $payroll_id]
                );
            }

            // Update payroll status to indicate HR approval
            $this->payrollRun->update($payroll_id, ['status' => 'Processed']);

            $this->logAction($payroll_id, $user_id, 'HR_APPROVED', 'HR Manager approved payroll with notes: ' . $notes);

            // Set session message for display
            $_SESSION['payroll_message'] = [
                'type' => 'success',
                'text' => 'Payroll successfully approved by HR Manager. Awaiting Finance Manager approval.'
            ];
            $_SESSION['payroll_message_payroll_id'] = $payroll_id;
            
            // Clear preview state to return to list view
            unset($_SESSION['preview_payroll_id']);

            return [
                'success' => true,
                'message' => 'Payroll approved by HR. Awaiting Finance approval.',
                'payroll_id' => $payroll_id
            ];
        } catch (Exception $e) {
            error_log("HR Approval Error: " . $e->getMessage());
            $_SESSION['payroll_message'] = [
                'type' => 'error',
                'text' => 'Error approving payroll: ' . $e->getMessage()
            ];
            return [
                'success' => false,
                'error' => 'Error approving payroll',
                'payroll_id' => $payroll_id
            ];
        }
    }

    /**
     * Reject payroll at HR level
     * @param int $payroll_id Payroll run ID
     * @param string $reason Rejection reason
     * @return array Success/error response
     */
    public function rejectHR($payroll_id, $reason = '') {
        $payroll_id = (int) $payroll_id;
        $user_id = $_SESSION['user_id'] ?? 0;

        try {
            $record = $this->db->query(
                "SELECT * FROM payroll_approvals WHERE payroll_run_id = ?",
                [$payroll_id]
            );

            if (empty($record)) {
                $this->db->query(
                    "INSERT INTO payroll_approvals (payroll_run_id, approval_level, approver_id, approval_status, comments, approval_date) 
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$payroll_id, 1, $user_id, 'rejected', $reason, date('Y-m-d H:i:s')]
                );
            } else {
                $this->db->query(
                    "UPDATE payroll_approvals SET approver_id = ?, approval_status = ?, comments = ?, approval_date = ?
                     WHERE payroll_run_id = ?",
                    [$user_id, 'rejected', $reason, date('Y-m-d H:i:s'), $payroll_id]
                );
            }

            // Reset payroll to Draft status
            $this->payrollRun->update($payroll_id, ['status' => 'Draft']);
            
            $this->logAction($payroll_id, $user_id, 'HR_REJECTED', 'HR Manager rejected payroll - reason: ' . $reason);

            // Set session message for display
            $_SESSION['payroll_message'] = [
                'type' => 'warning',
                'text' => 'Payroll rejected by HR Manager. Returned to Payroll Officer for corrections.'
            ];
            $_SESSION['payroll_message_payroll_id'] = $payroll_id;
            
            // Clear preview state to return to list view
            unset($_SESSION['preview_payroll_id']);

            return [
                'success' => true,
                'message' => 'Payroll rejected. Returned to Draft status.',
                'payroll_id' => $payroll_id
            ];
        } catch (Exception $e) {
            error_log("HR Rejection Error: " . $e->getMessage());
            $_SESSION['payroll_message'] = [
                'type' => 'error',
                'text' => 'Error rejecting payroll: ' . $e->getMessage()
            ];
            return [
                'success' => false,
                'error' => 'Error rejecting payroll',
                'payroll_id' => $payroll_id
            ];
        }
    }

    /**
     * Log payroll action for audit trail
     * @param int $payroll_id Payroll run ID
     * @param int $user_id User ID performing action
     * @param string $action Action type
     * @param string $description Action description
     */
    private function logAction($payroll_id, $user_id, $action, $description) {
        try {
            $this->db->query(
                "INSERT INTO payroll_audit_log (payroll_run_id, user_id, action, description, timestamp) 
                 VALUES (?, ?, ?, ?, ?)",
                [$payroll_id, $user_id, $action, $description, date('Y-m-d H:i:s')]
            );
        } catch (Exception $e) {
            error_log("Failed to log payroll action: " . $e->getMessage());
        }
    }

    /**
     * Route controller actions based on GET/POST parameters
     * Called by view to handle business logic
     * @return array Data for view
     */
    public static function route() {
        $controller = new self();
        $action = $_GET['action'] ?? $_POST['action'] ?? null;
        $payroll_id = $_GET['payroll_id'] ?? $_POST['payroll_id'] ?? 0;

        $result = [
            'action' => $action,
            'payroll_id' => $payroll_id,
            'data' => null
        ];

        // Handle actions - check both POST and GET since form posts become GET parameters after redirect
        if ($action) {
            switch ($action) {
                case 'preview':
                    $result['data'] = $controller->previewPayroll($payroll_id);
                    $_SESSION['preview_payroll_id'] = (int) $payroll_id;
                    break;
                case 'process':
                    $result['data'] = $controller->processPayroll($payroll_id);
                    break;
                case 'cancel_preview':
                    unset($_SESSION['preview_payroll_id']);
                    $result['data'] = ['success' => true, 'message' => 'Preview cancelled'];
                    break;
                case 'approve_hr':
                    $notes = trim($_GET['hr_notes'] ?? $_POST['hr_notes'] ?? '');
                    $result['data'] = $controller->approveHR($payroll_id, $notes);
                    break;
                case 'reject_hr':
                    $reason = trim($_GET['hr_reason'] ?? $_POST['hr_reason'] ?? '');
                    $result['data'] = $controller->rejectHR($payroll_id, $reason);
                    break;
            }
        } else {
            // No specific action, just return empty result for normal page load
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // If POST with no action, still try to process
                $result['data'] = ['info' => 'Form submitted without action parameter'];
            }
        }

        return $result;
    }
}

// If this file is accessed directly (for initial request), route the action
$controllerData = PayrollRunController::route();
?>
