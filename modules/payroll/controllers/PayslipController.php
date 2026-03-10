<?php
/**
 * Payslip Controller
 */

require_once __DIR__ . '/../../../config/BaseController.php';
require_once __DIR__ . '/../models/Payslip.php';

class PayslipController extends BaseController {
    private $payslipModel;

    public function __construct() {
        parent::__construct();
        $this->payslipModel = new Payslip();
    }

    /**
     * Get all payslips
     */
    public function getAll() {
        try {
            $payslips = $this->payslipModel->getAll();
            return [
                'success' => true,
                'data' => $payslips
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payslips by payroll run
     */
    public function getByPayrollRun($payrollRunId) {
        try {
            $payslips = $this->payslipModel->getByPayrollRun($payrollRunId);
            return [
                'success' => true,
                'data' => $payslips
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payslip for employee
     */
    public function getEmployeePayslip($payrollRunId, $employeeId) {
        try {
            $payslip = $this->payslipModel->getEmployeePayslip($payrollRunId, $employeeId);
            if (!$payslip) {
                return [
                    'success' => false,
                    'error' => 'Payslip not found'
                ];
            }
            return [
                'success' => true,
                'data' => $payslip
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payslip history for employee
     */
    public function getEmployeeHistory($employeeId) {
        try {
            $payslips = $this->payslipModel->getEmployeeHistory($employeeId);
            return [
                'success' => true,
                'data' => $payslips
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create payslip (used when processing payroll run)
     */
    public function create() {
        try {
            $data = [
                'payroll_run_id' => $_POST['payroll_run_id'] ?? null,
                'employee_id' => $_POST['employee_id'] ?? null,
                'gross_pay' => $_POST['gross_pay'] ?? 0,
                'total_deductions' => $_POST['total_deductions'] ?? 0,
                'net_pay' => $_POST['net_pay'] ?? 0,
                'status' => 'Generated'
            ];

            if (empty($data['payroll_run_id']) || empty($data['employee_id'])) {
                return [
                    'success' => false,
                    'error' => 'Payroll run and employee are required'
                ];
            }

            $id = $this->payslipModel->create($data);
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Payslip created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Static route method for dashboard integration
     */
    public static function route() {
        $controller = new self();
        $action = $_POST['action'] ?? $_GET['action'] ?? null;
        
        // Route to appropriate method based on action
        $result = [];
        if (method_exists($controller, $action)) {
            $result = $controller->$action();
        } else {
            $result = ['success' => false, 'error' => 'Unknown action: ' . $action];
        }
        
        return $result;
    }
}
?>

