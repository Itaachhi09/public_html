<?php
/**
 * Payroll Run Controller
 */

require_once __DIR__ . '/../../../config/BaseController.php';
require_once __DIR__ . '/../models/PayrollRun.php';

class PayrollRunController extends BaseController {
    private $payrollRunModel;

    public function __construct() {
        parent::__construct();
        $this->payrollRunModel = new PayrollRun();
    }

    /**
     * Get all payroll runs
     */
    public function getAll() {
        try {
            $runs = $this->payrollRunModel->getAll();
            return [
                'success' => true,
                'data' => $runs
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payroll run by status
     */
    public function getByStatus($status) {
        try {
            $runs = $this->payrollRunModel->getByStatus($status);
            return [
                'success' => true,
                'data' => $runs
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get single payroll run
     */
    public function get($id) {
        try {
            $run = $this->payrollRunModel->getWithEmployees($id);
            if (!$run) {
                return [
                    'success' => false,
                    'error' => 'Payroll run not found'
                ];
            }
            return [
                'success' => true,
                'data' => $run
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create payroll run
     */
    public function create() {
        try {
            $data = [
                'period_name' => $_POST['period_name'] ?? '',
                'start_date' => $_POST['start_date'] ?? '',
                'end_date' => $_POST['end_date'] ?? '',
                'pay_date' => $_POST['pay_date'] ?? '',
                'status' => 'Draft',
                'created_by' => $this->user['id'] ?? 0
            ];

            if (empty($data['period_name']) || empty($data['start_date']) || empty($data['end_date']) || empty($data['pay_date'])) {
                return [
                    'success' => false,
                    'error' => 'All fields are required'
                ];
            }

            $id = $this->payrollRunModel->create($data);
            
            // Automatically process payroll run after creation
            $this->processPayrollRun($id);
            
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Payroll run created and processed successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process payroll run - calculates payslips for all eligible employees
     */
    private function processPayrollRun($payrollRunId) {
        $db = new Database();
        
        // Get all active employees with eligible salaries
        $employees = $db->query("
            SELECT DISTINCT e.employee_id, e.first_name, e.last_name 
            FROM employees e
            INNER JOIN employee_salaries es ON e.employee_id = es.employee_id
            WHERE e.employment_status = 'Active' AND es.payroll_eligible = 1
        ");
        
        if ($employees && count($employees) > 0) {
            foreach ($employees as $emp) {
                $empId = $emp['employee_id'];
                
                // Get current basic salary
                $salary = $db->query(
                    "SELECT basic_rate FROM employee_salaries WHERE employee_id = ? AND payroll_eligible = 1 ORDER BY effective_date DESC LIMIT 1",
                    [$empId]
                );
                
                $gross = isset($salary[0]) ? floatval($salary[0]['basic_rate']) : 0;
                $deduct = 0;
                $net = $gross - $deduct;
                
                // Insert into payroll_run_employees
                $db->query(
                    "INSERT INTO payroll_run_employees (payroll_run_id, employee_id, basic_pay, gross_pay, total_deductions, net_pay, status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'Processed') 
                     ON DUPLICATE KEY UPDATE gross_pay=VALUES(gross_pay), total_deductions=VALUES(total_deductions), net_pay=VALUES(net_pay), status='Processed'",
                    [$payrollRunId, $empId, $gross, $gross, $deduct, $net]
                );
                
                // Generate payslip
                $pnum = 'PS-' . $payrollRunId . '-' . $empId;
                $db->query(
                    "INSERT INTO payslip_records (payroll_run_id, employee_id, gross_pay, total_deductions, net_pay, payslip_number, status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'generated') 
                     ON DUPLICATE KEY UPDATE gross_pay=VALUES(gross_pay), total_deductions=VALUES(total_deductions), net_pay=VALUES(net_pay)",
                    [$payrollRunId, $empId, $gross, $deduct, $net, $pnum]
                );
            }
        }
        
        // Update payroll run status to Processed
        $db->query("UPDATE payroll_runs SET status = 'Processed' WHERE id = ?", [$payrollRunId]);
    }

    /**
     * Update payroll run
     */
    public function update($id) {
        try {
            $existing = $this->payrollRunModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Payroll run not found'
                ];
            }

            $data = [
                'period_name' => $_POST['period_name'] ?? $existing['period_name'],
                'start_date' => $_POST['start_date'] ?? $existing['start_date'],
                'end_date' => $_POST['end_date'] ?? $existing['end_date'],
                'pay_date' => $_POST['pay_date'] ?? $existing['pay_date'],
                'status' => $_POST['status'] ?? $existing['status']
            ];

            $this->payrollRunModel->update($id, $data);
            return [
                'success' => true,
                'message' => 'Payroll run updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete payroll run
     */
    public function delete($id) {
        try {
            $existing = $this->payrollRunModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Payroll run not found'
                ];
            }

            $this->payrollRunModel->delete($id);
            return [
                'success' => true,
                'message' => 'Payroll run deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
