<?php
/**
 * Payroll Component Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/PayrollComponentModel.php';

class PayrollComponentController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new PayrollComponentModel();
    }

    /**
     * Get all payroll components
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $components = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $components,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Payroll components retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving payroll components: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single component
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $component = $this->model->find($id);
            
            if (!$component) {
                $this->respondError('Payroll component not found', 404);
                return;
            }

            $this->respondSuccess($component, 'Payroll component retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving payroll component: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new component
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'Payroll Manager']);

            // CORRECTED: Use database field names from payroll_components table schema
            // Fields: code, name, description, component_type, category, is_active
            $data = [
                'code' => $_POST['code'] ?? '',
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'component_type' => $_POST['component_type'] ?? 'salary',  // salary, deduction, tax, benefit
                'category' => $_POST['category'] ?? '',
                'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1
            ];

            if (empty($data['code']) || empty($data['name'])) {
                $this->respondError('Component code and name are required', 400);
                return;
            }

            if (!in_array($data['component_type'], ['salary', 'deduction', 'tax', 'benefit'])) {
                $this->respondError('Invalid component type. Must be: salary, deduction, tax, or benefit', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create payroll component', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'Payroll component created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating payroll component: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update component
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'Payroll Manager']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Payroll component not found', 404);
                return;
            }

            // CORRECTED: Use database field names from payroll_components table schema
            $data = [
                'code' => $_POST['code'] ?? $existing['code'],
                'name' => $_POST['name'] ?? $existing['name'],
                'description' => $_POST['description'] ?? $existing['description'],
                'component_type' => $_POST['component_type'] ?? $existing['component_type'],
                'category' => $_POST['category'] ?? $existing['category'],
                'is_active' => isset($_POST['is_active']) ? (int)$_POST['is_active'] : $existing['is_active']
            ];

            if (!in_array($data['component_type'], ['salary', 'deduction', 'tax', 'benefit'])) {
                $this->respondError('Invalid component type. Must be: salary, deduction, tax, or benefit', 400);
                return;
            }

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'Payroll component updated successfully');
            } else {
                $this->respondError('Failed to update payroll component', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating payroll component: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete component
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Payroll component not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Payroll component deleted successfully');
            } else {
                $this->respondError('Failed to delete payroll component', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting payroll component: ' . $e->getMessage(), 500);
        }
    }
}
?>
