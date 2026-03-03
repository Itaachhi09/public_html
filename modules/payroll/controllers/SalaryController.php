<?php
/**
 * Salary Controller
 */

require_once __DIR__ . '/../../../config/BaseController.php';
require_once __DIR__ . '/../models/Salary.php';

class SalaryController extends BaseController {
    private $salaryModel;

    public function __construct() {
        parent::__construct();
        $this->salaryModel = new Salary();
    }

    /**
     * Get all salaries
     */
    public function getAll() {
        try {
            $salaries = $this->salaryModel->getAllWithEmployee();
            return [
                'success' => true,
                'data' => $salaries
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get current salary for employee
     */
    public function getForEmployee($employeeId) {
        try {
            $salary = $this->salaryModel->getCurrentForEmployee($employeeId);
            if (!$salary) {
                return [
                    'success' => false,
                    'error' => 'No salary record found for this employee'
                ];
            }
            return [
                'success' => true,
                'data' => $salary
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get payroll-eligible employees
     */
    public function getPayrollEligible() {
        try {
            $employees = $this->salaryModel->getPayrollEligible();
            return [
                'success' => true,
                'data' => $employees
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create salary
     */
    public function create() {
        try {
            $data = [
                'employee_id' => $_POST['employee_id'] ?? null,
                'basic_rate' => $_POST['basic_rate'] ?? 0,
                'salary_type' => $_POST['salary_type'] ?? 'Monthly',
                'effective_date' => $_POST['effective_date'] ?? date('Y-m-d'),
                'tax_status' => $_POST['tax_status'] ?? 'Single',
                'payroll_eligible' => isset($_POST['payroll_eligible']) ? 1 : 0
            ];

            if (empty($data['employee_id']) || $data['basic_rate'] <= 0) {
                return [
                    'success' => false,
                    'error' => 'Employee and basic rate are required'
                ];
            }

            $id = $this->salaryModel->create($data);
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Salary record created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update salary
     */
    public function update($id) {
        try {
            $existing = $this->salaryModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Salary record not found'
                ];
            }

            $data = [
                'basic_rate' => $_POST['basic_rate'] ?? $existing['basic_rate'],
                'salary_type' => $_POST['salary_type'] ?? $existing['salary_type'],
                'effective_date' => $_POST['effective_date'] ?? $existing['effective_date'],
                'tax_status' => $_POST['tax_status'] ?? $existing['tax_status'],
                'payroll_eligible' => isset($_POST['payroll_eligible']) ? 1 : 0
            ];

            $this->salaryModel->update($id, $data);
            return [
                'success' => true,
                'message' => 'Salary record updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete salary
     */
    public function delete($id) {
        try {
            $existing = $this->salaryModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Salary record not found'
                ];
            }

            $this->salaryModel->delete($id);
            return [
                'success' => true,
                'message' => 'Salary record deleted successfully'
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
