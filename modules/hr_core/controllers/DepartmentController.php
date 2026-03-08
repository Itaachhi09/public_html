<?php
/**
 * Department Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/DepartmentModel.php';

class DepartmentController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new DepartmentModel();
    }

    /**
     * Get all departments
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $departments = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->getTotal();

            $this->respondSuccess([
                'data' => $departments,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Departments retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving departments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single department
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $department = $this->model->find($id);
            
            if (!$department) {
                $this->respondError('Department not found', 404);
                return;
            }

            $this->respondSuccess($department, 'Department retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving department: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new department
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'HR']);

            $data = [
                'department_name' => $_POST['department_name'] ?? '',
                'name' => $_POST['name'] ?? null,
                'code' => $_POST['code'] ?? null,
                'head_id' => $_POST['head_id'] ?? null,
                'parent_id' => $_POST['parent_id'] ?? null,
                'description' => $_POST['description'] ?? null,
                'status' => $_POST['status'] ?? 'Active'
            ];

            // Validate required fields
            if (empty($data['department_name'])) {
                $this->respondError('Department name is required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create department', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'Department created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating department: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update department
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Department not found', 404);
                return;
            }

            $data = [
                'department_name' => $_POST['department_name'] ?? $existing['department_name'],
                'name' => $_POST['name'] ?? $existing['name'],
                'code' => $_POST['code'] ?? $existing['code'],
                'head_id' => $_POST['head_id'] ?? $existing['head_id'],
                'parent_id' => $_POST['parent_id'] ?? $existing['parent_id'],
                'description' => $_POST['description'] ?? $existing['description'],
                'status' => $_POST['status'] ?? $existing['status']
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'Department updated successfully');
            } else {
                $this->respondError('Failed to update department', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating department: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete department
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Department not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Department deleted successfully');
            } else {
                $this->respondError('Failed to delete department', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting department: ' . $e->getMessage(), 500);
        }
    }
}
?>
