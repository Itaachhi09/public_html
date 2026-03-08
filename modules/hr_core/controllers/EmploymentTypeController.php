<?php
/**
 * Employment Type Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/EmploymentTypeModel.php';

class EmploymentTypeController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new EmploymentTypeModel();
    }

    /**
     * Get all employment types
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $employmentTypes = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $employmentTypes,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Employment types retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving employment types: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single employment type
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $employmentType = $this->model->find($id);
            
            if (!$employmentType) {
                $this->respondError('Employment type not found', 404);
                return;
            }

            $this->respondSuccess($employmentType, 'Employment type retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving employment type: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new employment type
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'HR']);

            $data = [
                'type_name' => $_POST['type_name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'probation_period_days' => $_POST['probation_period_days'] ?? 90,
                'contract_duration_months' => $_POST['contract_duration_months'] ?? null,
                'benefits_eligible' => isset($_POST['benefits_eligible']) ? (int)$_POST['benefits_eligible'] : 1,
                'status' => $_POST['status'] ?? 'Active'
            ];

            if (empty($data['type_name'])) {
                $this->respondError('Employment type name is required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create employment type', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'Employment type created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating employment type: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update employment type
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Employment type not found', 404);
                return;
            }

            $data = [
                'type_name' => $_POST['type_name'] ?? $existing['type_name'],
                'description' => $_POST['description'] ?? $existing['description'],
                'probation_period_days' => $_POST['probation_period_days'] ?? $existing['probation_period_days'],
                'contract_duration_months' => $_POST['contract_duration_months'] ?? $existing['contract_duration_months'],
                'benefits_eligible' => isset($_POST['benefits_eligible']) ? (int)$_POST['benefits_eligible'] : $existing['benefits_eligible'],
                'status' => $_POST['status'] ?? $existing['status']
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'Employment type updated successfully');
            } else {
                $this->respondError('Failed to update employment type', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating employment type: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete employment type
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Employment type not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Employment type deleted successfully');
            } else {
                $this->respondError('Failed to delete employment type', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting employment type: ' . $e->getMessage(), 500);
        }
    }
}
?>
