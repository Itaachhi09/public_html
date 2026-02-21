<?php
/**
 * PayslipRecord Model
 * Handles database operations for payslip records
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayslipRecord extends BaseModel
{
    protected $table = 'payslip_records';
    protected $fillable = [
        'payroll_run_id',
        'employee_id', 
        'gross_pay',
        'total_deductions',
        'net_pay',
        'payslip_number',
        'generated_by',
        'pdf_path',
        'status'
    ];

    /**
     * Get payslip record by ID
     */
    public function getById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = ?", [$id], true);
    }

    /**
     * Get payslips by payroll run ID
     */
    public function getByPayrollRun($payroll_run_id)
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE payroll_run_id = ? ORDER BY employee_id ASC",
            [$payroll_run_id]
        );
    }

    /**
     * Get payslip by payroll and employee
     */
    public function getByPayrollAndEmployee($payroll_run_id, $employee_id)
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE payroll_run_id = ? AND employee_id = ?",
            [$payroll_run_id, $employee_id],
            true
        );
    }

    /**
     * Check if payslip exists
     */
    public function exists($payroll_run_id, $employee_id)
    {
        $result = $this->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE payroll_run_id = ? AND employee_id = ?",
            [$payroll_run_id, $employee_id],
            true
        );
        return isset($result['count']) && $result['count'] > 0;
    }

    /**
     * Create payslip record
     */
    public function create($data)
    {
        $conn = (new Database())->connect();
        
        $query = "INSERT INTO {$this->table} 
                  (payroll_run_id, employee_id, gross_pay, total_deductions, net_pay, payslip_number, generated_at, generated_by)
                  VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        
        $stmt = $conn->prepare($query);
        return $stmt->execute([
            $data['payroll_run_id'],
            $data['employee_id'],
            $data['gross_pay'],
            $data['total_deductions'],
            $data['net_pay'],
            $data['payslip_number'],
            $data['generated_by'] ?? 1
        ]);
    }

    /**
     * Update payslip record
     */
    public function update($id, $data)
    {
        $conn = (new Database())->connect();
        
        $update_fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $update_fields[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        
        if (empty($update_fields)) {
            return false;
        }
        
        $values[] = $id;
        $query = "UPDATE {$this->table} SET " . implode(', ', $update_fields) . " WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        return $stmt->execute($values);
    }

    /**
     * Delete payslip record
     */
    public function delete($id)
    {
        $conn = (new Database())->connect();
        
        $query = "UPDATE {$this->table} SET status = 'deleted' WHERE id = ?";
        $stmt = $conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Get payslip count by status
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
     * Archive old payslips
     */
    public function archive($payroll_run_id)
    {
        $conn = (new Database())->connect();
        
        $query = "UPDATE {$this->table} SET status = 'archived' WHERE payroll_run_id = ? AND status = 'generated'";
        $stmt = $conn->prepare($query);
        return $stmt->execute([$payroll_run_id]);
    }
}
