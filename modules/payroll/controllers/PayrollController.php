<?php
/**
 * PayrollController
 * Handles routing for Payroll module pages
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/BaseConfig.php';
require_once __DIR__ . '/../models/PayrollModel.php';

class PayrollController {
    private $payrollModel;
    private $db;

    public function __construct() {
        // Use singleton instance to ensure database connection is established
        $this->db = Database::getInstance();
        $this->payrollModel = new PayrollModel();
    }

    /**
     * Handle earnings list view
     */
    public function earnings_list() {
        // Initialize employee salaries on first view
        $this->payrollModel->initializeEmployeeSalaries();
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'department' => $_GET['department'] ?? '',
            'compensation_status' => $_GET['compensation_status'] ?? ''
        ];

        $earnings = $this->payrollModel->getEarningsList($filters);
        $totalEmployees = $this->payrollModel->getTotalEmployeesWithEarnings($filters);
        $totalGross = $this->payrollModel->getTotalGrossEarnings($filters);
        $departments = $this->payrollModel->getAllDepartments();
        $pendingCompensation = $this->payrollModel->getPendingCompensationCount();

        // Set variables for view
        $_data = [
            'earnings' => $earnings,
            'totalEmployees' => $totalEmployees,
            'totalGross' => $totalGross,
            'departments' => $departments,
            'pendingCompensation' => $pendingCompensation,
            'filters' => $filters,
            'action' => 'list'
        ];

        return $_data;
    }

    /**
     * Handle view earnings details for specific employee
     */
    public function view_earnings() {
        $employee_id = (int) ($_GET['employee_id'] ?? 0);

        if (!$employee_id) {
            return ['error' => 'Employee ID not provided'];
        }

        $employeeEarnings = $this->payrollModel->calculateGrossEarnings($employee_id);

        if (!$employeeEarnings) {
            return ['error' => 'Employee earnings not found'];
        }

        // Get departments for filters
        $departments = $this->payrollModel->getAllDepartments();

        $_data = [
            'employeeEarnings' => $employeeEarnings,
            'departments' => $departments,
            'action' => 'view',
            'employee_id' => $employee_id
        ];

        return $_data;
    }

    /**
     * Handle filter earnings
     */
    public function filter_earnings() {
        // This just redirects back to list with filter parameters
        // Same as earnings_list, so we call that
        return $this->earnings_list();
    }

    /**
     * Handle deductions list view
     */
    public function deductions_management() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'department' => $_GET['department'] ?? '',
            'deduction_status' => $_GET['deduction_status'] ?? ''
        ];

        $deductions = $this->payrollModel->getDeductionSummary($filters);
        $totalDeductions = count($deductions ?? []);
        $totalStatutory = 0;
        $totalOther = 0;
        $pendingApprovals = 0;

        // Calculate totals
        foreach ($deductions as $emp) {
            $breakdown = $this->payrollModel->getDeductionBreakdown($emp['employee_id']);
            if ($breakdown) {
                $totalStatutory += $breakdown['total_statutory'];
                $totalOther += $breakdown['total_other'];
            }
        }

        $departments = $this->payrollModel->getAllDepartments();

        // Set variables for view
        $_data = [
            'deductions' => $deductions,
            'totalDeductions' => $totalDeductions,
            'totalStatutory' => $totalStatutory,
            'totalOther' => $totalOther,
            'pendingApprovals' => $pendingApprovals,
            'departments' => $departments,
            'filters' => $filters,
            'action' => 'list'
        ];

        return $_data;
    }

    /**
     * Handle add manual deduction
     */
    public function addManualDeduction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $data = [
            'employee_id' => $_POST['employee_id'] ?? null,
            'deduction_type' => $_POST['deduction_type'] ?? 'other',
            'description' => $_POST['description'] ?? '',
            'amount' => $_POST['amount'] ?? 0,
            'remarks' => $_POST['remarks'] ?? ''
        ];

        // Validate
        if (!$data['employee_id'] || !$data['description'] || $data['amount'] <= 0) {
            return ['error' => 'Missing required fields'];
        }

        // Add deduction
        $result = $this->payrollModel->addManualDeduction($data);

        if ($result) {
            header("Location: " . BASE_URL . "dashboard.php?ref=payroll&page=deductions_management&modal=add&success=1");
            exit;
        } else {
            return ['error' => 'Failed to add deduction'];
        }
    }

    /**
     * Route to appropriate controller method
     */
    public static function route() {
        $controller = new self();
        $action = $_GET['action'] ?? 'list';
        $page = $_GET['page'] ?? null;

        // Handle deductions_management page
        if ($page === 'deductions_management') {
            return $controller->deductions_management();
        }

        // Handle earnings_management page (default to earnings_list)
        if ($page === 'earnings_management' || !$page) {
            $action = $_GET['action'] ?? 'earnings_list';
            
            // Map actions to methods
            if (method_exists($controller, $action)) {
                return $controller->$action();
            }
            return $controller->earnings_list();
        }

        return $controller->earnings_list();
    }
}

// Return controller data
$controllerData = PayrollController::route();
?>
