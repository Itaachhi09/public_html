<?php
/**
 * Payslip Controller
 * Handles payslip generation, email distribution, and email settings management
 */

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/BaseConfig.php';
require_once __DIR__ . '/../../../config/ReliableEmailService.php';
require_once __DIR__ . '/../models/PayrollRun.php';
require_once __DIR__ . '/../models/PayrollRunEmployee.php';
require_once __DIR__ . '/../models/EmployeeSalary.php';
require_once __DIR__ . '/../models/PayslipRecord.php';
require_once __DIR__ . '/../models/PayslipEmailSetting.php';
require_once __DIR__ . '/../models/PayslipEmailLog.php';

class PayslipController
{
    private $db;
    private $payrollRun;
    private $payrollRunEmployee;
    private $employeeSalary;
    private $payslipRecord;
    private $payslipEmailSetting;
    private $payslipEmailLog;

    public function __construct()
    {
        $this->db = new Database();
        $this->payrollRun = new PayrollRun();
        $this->payrollRunEmployee = new PayrollRunEmployee();
        $this->employeeSalary = new EmployeeSalary();
        $this->payslipRecord = new PayslipRecord();
        $this->payslipEmailSetting = new PayslipEmailSetting();
        $this->payslipEmailLog = new PayslipEmailLog();
    }

    /**
     * Route payslip actions
     */
    public static function route()
    {
        $controller = new self();
        $action = $_POST['action'] ?? '';
        $payroll_id = (int) ($_POST['payroll_id'] ?? 0);

        try {
            switch ($action) {
                case 'generate_all':
                    return $controller->generateAll($payroll_id);
                
                case 'generate_and_email':
                    return $controller->generateAndEmail($payroll_id);
                
                case 'update_email_settings':
                    return $controller->updateEmailSettings();
                
                case 'reset_email_settings':
                    return $controller->resetEmailSettings();
                
                default:
                    return [
                        'success' => false,
                        'message' => 'Unknown action'
                    ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate all payslips for a payroll run
     */
    public function generateAll($payroll_id)
    {
        if (!$payroll_id) {
            throw new Exception('Payroll ID is required');
        }

        $conn = $this->db->connect();

        // Validate payroll exists and is approved
        $query = "SELECT * FROM payroll_runs WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$payroll_id]);
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payroll) {
            throw new Exception('Payroll not found');
        }

        // Validate payroll status - must be approved/Processed
        if ($payroll['status'] !== 'Processed' && $payroll['status'] !== 'Approved') {
            throw new Exception('Only approved payroll can generate payslips. Current status: ' . $payroll['status']);
        }

        // Check if payslips already locked/closed
        if ($payroll['status'] === 'Closed') {
            throw new Exception('Cannot generate payslips for closed payroll');
        }

        // Fetch all employees for this payroll run
        $query = "SELECT pre.*, e.first_name, e.last_name, e.email, e.employee_id
                  FROM payroll_run_employees pre
                  JOIN employees e ON pre.employee_id = e.employee_id
                  WHERE pre.payroll_run_id = ?
                  ORDER BY e.employee_code ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$payroll_id]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($employees)) {
            throw new Exception('No employees found for this payroll');
        }

        $generated_count = 0;
        $skipped_count = 0;

        foreach ($employees as $employee) {
            try {
                // Check if payslip already exists
                if ($this->payslipRecord->exists($payroll_id, $employee['employee_id'])) {
                    $skipped_count++;
                    continue;
                }

                // Generate payslip number
                $payslip_number = $this->generatePayslipNumber($payroll_id, $employee['employee_id']);

                // Insert payslip record
                $payslip_data = [
                    'payroll_run_id' => $payroll_id,
                    'employee_id' => $employee['employee_id'],
                    'gross_pay' => $employee['gross_pay'],
                    'total_deductions' => $employee['total_deductions'],
                    'net_pay' => $employee['net_pay'],
                    'payslip_number' => $payslip_number,
                    'generated_by' => $_SESSION['user_id'] ?? 1
                ];
                
                $this->payslipRecord->create($payslip_data);

                // Log in audit trail
                $log_query = "INSERT INTO payroll_audit_trail 
                             (payroll_run_id, user_id, action, description, timestamp)
                             VALUES (?, ?, 'PAYSLIP_GENERATED', ?, NOW())";
                $log_stmt = $conn->prepare($log_query);
                $log_stmt->execute([
                    $payroll_id,
                    $_SESSION['user_id'] ?? 1,
                    "Payslip generated for employee {$employee['employee_id']} - {$payslip_number}"
                ]);

                $generated_count++;
            } catch (Exception $e) {
                // Log error but continue with next employee
                error_log("Error generating payslip for employee {$employee['employee_id']}: " . $e->getMessage());
                continue;
            }
        }

        // Set session message
        $_SESSION['payslip_message'] = [
            'type' => 'success',
            'text' => "Payslips generated successfully! Generated: $generated_count, Skipped (already exist): $skipped_count"
        ];
        $_SESSION['payslip_message_payroll_id'] = $payroll_id;

        return [
            'success' => true,
            'generated' => $generated_count,
            'skipped' => $skipped_count,
            'message' => "Generated $generated_count payslip(s)"
        ];
    }

    /**
     * Generate payslips and send via email
     */
    public function generateAndEmail($payroll_id)
    {
        if (!$payroll_id) {
            throw new Exception('Payroll ID is required');
        }

        $conn = $this->db->connect();

        // First generate all payslips
        $generate_result = $this->generateAll($payroll_id);

        if (!$generate_result['success']) {
            return $generate_result;
        }

        // Fetch payroll info
        $query = "SELECT * FROM payroll_runs WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$payroll_id]);
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch payslips with employee info
        $query = "SELECT pr.*, e.first_name, e.last_name, e.email, e.employee_id
                  FROM payslip_records pr
                  JOIN employees e ON pr.employee_id = e.employee_id
                  WHERE pr.payroll_run_id = ? AND pr.generated_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                  ORDER BY e.employee_code ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$payroll_id]);
        $payslips = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch email settings
        $settings = $this->getEmailSettings();

        $sent_count = 0;
        $failed_count = 0;

        foreach ($payslips as $payslip) {
            try {
                // Build email content
                $email_body = $settings['template'];
                $email_body = str_replace('[EMPLOYEE_NAME]', $payslip['first_name'] . ' ' . $payslip['last_name'], $email_body);
                $email_body = str_replace('[PAY_PERIOD]', $payroll['period_name'] ?? 'Current Period', $email_body);
                $email_body = str_replace('[GROSS_PAY]', number_format($payslip['gross_pay'], 2), $email_body);
                $email_body = str_replace('[TOTAL_DEDUCTIONS]', number_format($payslip['total_deductions'], 2), $email_body);
                $email_body = str_replace('[NET_PAY]', number_format($payslip['net_pay'], 2), $email_body);
                $email_body = str_replace('[PAYSLIP_PORTAL_LINK]', 'https://healthcare-system.com/employee-portal/payslips', $email_body);

                // Send email
                $email_service = new ReliableEmailService();
                $result = $email_service->send(
                    $payslip['email'],
                    $settings['subject'],
                    $email_body,
                    $settings['from_address']
                );

                if ($result) {
                    // Log email sent
                    $log_data = [
                        'payslip_id' => $payslip['id'],
                        'employee_id' => $payslip['employee_id'],
                        'payroll_run_id' => $payroll_id,
                        'email_address' => $payslip['email'],
                        'status' => 'sent'
                    ];
                    $this->payslipEmailLog->create($log_data);

                    $sent_count++;
                } else {
                    // Log email failed
                    $log_data = [
                        'payslip_id' => $payslip['id'],
                        'employee_id' => $payslip['employee_id'],
                        'payroll_run_id' => $payroll_id,
                        'email_address' => $payslip['email'],
                        'status' => 'failed',
                        'error_message' => 'Email service returned false'
                    ];
                    $this->payslipEmailLog->create($log_data);

                    $failed_count++;
                }
            } catch (Exception $e) {
                error_log("Error sending payslip email to {$payslip['email']}: " . $e->getMessage());
                
                try {
                    $log_data = [
                        'payslip_id' => $payslip['id'] ?? null,
                        'employee_id' => $payslip['employee_id'],
                        'payroll_run_id' => $payroll_id,
                        'email_address' => $payslip['email'],
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ];
                    $this->payslipEmailLog->create($log_data);
                } catch (Exception $log_err) {
                    error_log("Failed to log email error: " . $log_err->getMessage());
                }
                
                $failed_count++;
            }
        }

        // Set session message
        $_SESSION['payslip_message'] = [
            'type' => 'success',
            'text' => "Payslips generated and emails sent! Sent: $sent_count, Failed: $failed_count"
        ];
        $_SESSION['payslip_message_payroll_id'] = $payroll_id;

        return [
            'success' => true,
            'generated' => $generate_result['generated'],
            'sent' => $sent_count,
            'failed' => $failed_count,
            'message' => "Generated $generate_result[generated] payslip(s) and sent $sent_count email(s)"
        ];
    }

    /**
     * Update email settings
     */
    public function updateEmailSettings()
    {
        $email_from = $_POST['email_from'] ?? 'payroll@healthcare.com';
        $email_subject = $_POST['email_subject'] ?? 'Your Payslip for [PERIOD]';
        $email_template = $_POST['email_template'] ?? '';
        $auto_email = $_POST['auto_email'] ?? 0;

        try {
            $this->payslipEmailSetting->upsert('email_from', $email_from, $_SESSION['user_id'] ?? 1);
            $this->payslipEmailSetting->upsert('email_subject', $email_subject, $_SESSION['user_id'] ?? 1);
            $this->payslipEmailSetting->upsert('email_template', $email_template, $_SESSION['user_id'] ?? 1);
            $this->payslipEmailSetting->upsert('auto_email', $auto_email, $_SESSION['user_id'] ?? 1);

            // Log action
            $conn = $this->db->connect();
            $log_query = "INSERT INTO payroll_audit_trail 
                         (user_id, action, description, timestamp)
                         VALUES (?, 'EMAIL_SETTINGS_UPDATED', 'Email settings updated by user', NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->execute([$_SESSION['user_id'] ?? 1]);

            $_SESSION['payslip_message'] = [
                'type' => 'success',
                'text' => 'Email settings updated successfully'
            ];

            return [
                'success' => true,
                'message' => 'Email settings updated successfully'
            ];
        } catch (Exception $e) {
            throw new Exception('Error updating email settings: ' . $e->getMessage());
        }
    }

    /**
     * Reset email settings to defaults
     */
    public function resetEmailSettings()
    {
        $defaults = PayslipEmailSetting::getDefaults();

        try {
            foreach ($defaults as $key => $value) {
                $this->payslipEmailSetting->upsert($key, $value, $_SESSION['user_id'] ?? 1);
            }

            // Log action
            $conn = $this->db->connect();
            $log_query = "INSERT INTO payroll_audit_trail 
                         (user_id, action, description, timestamp)
                         VALUES (?, 'EMAIL_SETTINGS_RESET', 'Email settings reset to defaults by user', NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->execute([$_SESSION['user_id'] ?? 1]);

            $_SESSION['payslip_message'] = [
                'type' => 'success',
                'text' => 'Email settings reset to defaults'
            ];

            return [
                'success' => true,
                'message' => 'Email settings reset to defaults'
            ];
        } catch (Exception $e) {
            throw new Exception('Error resetting email settings: ' . $e->getMessage());
        }
    }

    /**
     * Get email settings
     */
    private function getEmailSettings()
    {
        $defaults = PayslipEmailSetting::getDefaults();
        $settings = $this->payslipEmailSetting->getAll();

        $result = $defaults;
        
        foreach ($settings as $setting) {
            if (isset($defaults[$setting['setting_key']])) {
                $result[$setting['setting_key']] = $setting['setting_value'];
            }
        }

        return [
            'from_address' => $result['email_from'] ?? $defaults['email_from'],
            'subject' => $result['email_subject'] ?? $defaults['email_subject'],
            'template' => $result['email_template'] ?? $defaults['email_template']
        ];
    }

    /**
     * Generate unique payslip number
     */
    private function generatePayslipNumber($payroll_id, $employee_id)
    {
        $date = date('Ymd');
        return "PS-{$date}-{$payroll_id}-{$employee_id}";
    }
}

