<?php
/**
 * Employee Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/EmployeeModel.php';

class EmployeeController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new EmployeeModel();
    }

    /**
     * Get all employees
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $employees = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->getTotal();

            $this->respondSuccess([
                'data' => $employees,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Employees retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving employees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single employee
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $employee = $this->model->findWithDetails($id);
            
            if (!$employee) {
                $this->respondError('Employee not found', 404);
                return;
            }

            $this->respondSuccess($employee, 'Employee retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new employee
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'HR']);

            $data = [
                'employee_code' => $_POST['employee_code'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'middle_name' => $_POST['middle_name'] ?? null,
                'last_name' => $_POST['last_name'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'gender' => $_POST['gender'] ?? 'Other',
                'marital_status' => $_POST['marital_status'] ?? 'Single',
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'address' => $_POST['address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'postal_code' => $_POST['postal_code'] ?? null,
                'country' => $_POST['country'] ?? null,
                'nationality' => $_POST['nationality'] ?? null,
                'job_title_id' => $_POST['job_title_id'] ?? null,
                'department_id' => $_POST['department_id'] ?? null,
                'employment_type_id' => $_POST['employment_type_id'] ?? null,
                'location_id' => $_POST['location_id'] ?? null,
                'supervisor_id' => $_POST['supervisor_id'] ?? null,
                'date_of_joining' => $_POST['date_of_joining'] ?? date('Y-m-d'),
                'employment_status' => $_POST['employment_status'] ?? 'Active'
            ];

            // Validate required fields
            if (empty($data['employee_code']) || empty($data['first_name']) || empty($data['last_name'])) {
                $this->respondError('Employee code, first name, and last name are required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create employee', 500);
                return;
            }

            $created = $this->model->findWithDetails($id);
            $this->respondSuccess($created, 'Employee created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update employee
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Employee not found', 404);
                return;
            }

            $data = [
                'first_name' => $_POST['first_name'] ?? $existing['first_name'],
                'middle_name' => $_POST['middle_name'] ?? $existing['middle_name'],
                'last_name' => $_POST['last_name'] ?? $existing['last_name'],
                'date_of_birth' => $_POST['date_of_birth'] ?? $existing['date_of_birth'],
                'gender' => $_POST['gender'] ?? $existing['gender'],
                'marital_status' => $_POST['marital_status'] ?? $existing['marital_status'],
                'email' => $_POST['email'] ?? $existing['email'],
                'phone' => $_POST['phone'] ?? $existing['phone'],
                'address' => $_POST['address'] ?? $existing['address'],
                'city' => $_POST['city'] ?? $existing['city'],
                'state' => $_POST['state'] ?? $existing['state'],
                'postal_code' => $_POST['postal_code'] ?? $existing['postal_code'],
                'country' => $_POST['country'] ?? $existing['country'],
                'nationality' => $_POST['nationality'] ?? $existing['nationality'],
                'job_title_id' => $_POST['job_title_id'] ?? $existing['job_title_id'],
                'department_id' => $_POST['department_id'] ?? $existing['department_id'],
                'employment_type_id' => $_POST['employment_type_id'] ?? $existing['employment_type_id'],
                'location_id' => $_POST['location_id'] ?? $existing['location_id'],
                'supervisor_id' => $_POST['supervisor_id'] ?? $existing['supervisor_id'],
                'employment_status' => $_POST['employment_status'] ?? $existing['employment_status'],
                'last_working_day' => $_POST['last_working_day'] ?? $existing['last_working_day']
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->findWithDetails($id);
                $this->respondSuccess($updated, 'Employee updated successfully');
            } else {
                $this->respondError('Failed to update employee', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete employee (soft delete via status)
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Employee not found', 404);
                return;
            }

            if ($this->model->update($id, ['employment_status' => 'Terminated'])) {
                $this->respondSuccess([], 'Employee deleted successfully (marked as terminated)');
            } else {
                $this->respondError('Failed to delete employee', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting employee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search employees
     */
    public function search() {
        try {
            $term = $_GET['q'] ?? '';
            
            if (empty($term)) {
                $this->respondError('Search term required', 400);
                return;
            }

            $employees = $this->model->search($term);
            $this->respondSuccess(['data' => $employees], 'Search results');
        } catch (Exception $e) {
            $this->respondError('Error searching employees: ' . $e->getMessage(), 500);
        }
    }
}
?>
