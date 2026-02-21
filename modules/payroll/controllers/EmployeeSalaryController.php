<?php
/**
 * Employee Salary Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/EmployeeSalaryModel.php';

class EmployeeSalaryController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new EmployeeSalaryModel();
    }

    /**
     * Get all employee salaries
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $salaries = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $salaries,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Employee salaries retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving salaries: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single salary
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $salary = $this->model->find($id);
            
            if (!$salary) {
                $this->respondError('Employee salary not found', 404);
                return;
            }

            $this->respondSuccess($salary, 'Employee salary retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving salary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new salary
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'Payroll Manager']);

            $data = [
                'employee_id' => $_POST['employee_id'] ?? null,
                'salary_type' => $_POST['salary_type'] ?? 'Monthly',
                'basic_rate' => $_POST['basic_rate'] ?? 0,
                'effective_date' => $_POST['effective_date'] ?? date('Y-m-d'),
                'tax_status' => $_POST['tax_status'] ?? 'Single',
                'payroll_eligible' => isset($_POST['payroll_eligible']) ? 1 : 0,
                'bank_name' => $_POST['bank_name'] ?? null,
                'bank_account' => $_POST['bank_account'] ?? null
            ];

            if (empty($data['employee_id']) || empty($data['basic_rate'])) {
                $this->respondError('Employee ID and basic rate are required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create salary', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'Employee salary created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating salary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update salary
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'Payroll Manager']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Employee salary not found', 404);
                return;
            }

            $data = [
                'salary_type' => $_POST['salary_type'] ?? $existing['salary_type'],
                'basic_rate' => $_POST['basic_rate'] ?? $existing['basic_rate'],
                'effective_date' => $_POST['effective_date'] ?? $existing['effective_date'],
                'tax_status' => $_POST['tax_status'] ?? $existing['tax_status'],
                'payroll_eligible' => isset($_POST['payroll_eligible']) ? 1 : 0,
                'bank_name' => $_POST['bank_name'] ?? $existing['bank_name'],
                'bank_account' => $_POST['bank_account'] ?? $existing['bank_account']
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'Employee salary updated successfully');
            } else {
                $this->respondError('Failed to update salary', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating salary: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete salary
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Employee salary not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Employee salary deleted successfully');
            } else {
                $this->respondError('Failed to delete salary', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting salary: ' . $e->getMessage(), 500);
        }
    }
}
?>
