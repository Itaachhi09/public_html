<?php
/**
 * PayslipEmailLog Model
 * Handles database operations for payslip email logs
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayslipEmailLog extends BaseModel
{
    protected $table = 'payslip_email_logs';
    protected $fillable = [
        'payslip_id',
        'employee_id',
        'payroll_run_id',
        'email_address',
        'status',
        'error_message'
    ];

    /**
     * Create email log entry
     */
    public function create($data)
    {
        $conn = (new Database())->connect();
        
        $query = "INSERT INTO {$this->table} 
                  (payslip_id, employee_id, payroll_run_id, email_address, sent_at, status, error_message)
                  VALUES (?, ?, ?, ?, NOW(), ?, ?)";
        
        $stmt = $conn->prepare($query);
        return $stmt->execute([
            $data['payslip_id'] ?? null,
            $data['employee_id'],
            $data['payroll_run_id'],
            $data['email_address'],
            $data['status'] ?? 'sent',
            $data['error_message'] ?? null
        ]);
    }

    /**
     * Get logs by payroll run
     */
    public function getByPayrollRun($payroll_run_id)
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE payroll_run_id = ? ORDER BY sent_at DESC",
            [$payroll_run_id]
        );
    }

    /**
     * Get logs by status
     */
    public function getByStatus($payroll_run_id, $status)
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE payroll_run_id = ? AND status = ? ORDER BY sent_at DESC",
            [$payroll_run_id, $status]
        );
    }

    /**
     * Count emails by status
     */
    public function countByStatus($payroll_run_id, $status = null)
    {
        $conn = (new Database())->connect();
        
        if ($status) {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE payroll_run_id = ? AND status = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$payroll_run_id, $status]);
        } else {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE payroll_run_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$payroll_run_id]);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Get email status summary
     */
    public function getSummary($payroll_run_id)
    {
        $conn = (new Database())->connect();
        
        $query = "SELECT status, COUNT(*) as count FROM {$this->table} 
                  WHERE payroll_run_id = ? GROUP BY status";
        $stmt = $conn->prepare($query);
        $stmt->execute([$payroll_run_id]);
        
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['status']] = $row['count'];
        }
        return $result;
    }
}
