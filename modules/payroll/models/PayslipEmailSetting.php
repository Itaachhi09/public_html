<?php
/**
 * PayslipEmailSetting Model
 * Handles database operations for payslip email settings
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayslipEmailSetting extends BaseModel
{
    protected $table = 'payslip_email_settings';
    protected $fillable = [
        'setting_key',
        'setting_value',
        'updated_by'
    ];

    /**
     * Get setting by key
     */
    public function getSetting($key)
    {
        return $this->query(
            "SELECT setting_value FROM {$this->table} WHERE setting_key = ?",
            [$key],
            true
        );
    }

    /**
     * Get all settings
     */
    public function getAll()
    {
        return $this->query("SELECT * FROM {$this->table} ORDER BY setting_key ASC");
    }

    /**
     * Update or insert setting
     */
    public function upsert($key, $value, $updated_by = 1)
    {
        $conn = (new Database())->connect();
        
        $query = "INSERT INTO {$this->table} (setting_key, setting_value, updated_at, updated_by)
                  VALUES (?, ?, NOW(), ?)
                  ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value),
                    updated_at = NOW(),
                    updated_by = VALUES(updated_by)";
        
        $stmt = $conn->prepare($query);
        return $stmt->execute([$key, $value, $updated_by]);
    }

    /**
     * Get default settings
     */
    public static function getDefaults()
    {
        return [
            'email_from' => 'payroll@healthcare.com',
            'email_subject' => 'Your Payslip for [PERIOD]',
            'email_template' => "Dear [EMPLOYEE_NAME],\n\nYour payslip for [PAY_PERIOD] has been generated and is ready for download.\n\nGross Pay: ₱[GROSS_PAY]\nTotal Deductions: ₱[TOTAL_DEDUCTIONS]\nNet Pay: ₱[NET_PAY]\n\nYou can view and download your payslip using the following link:\n[PAYSLIP_PORTAL_LINK]\n\nFor payroll inquiries, contact: payroll@healthcare.com\n\n---\nHealthcare Hospital Inc.\nPayroll Department",
            'auto_email' => '1'
        ];
    }
}
