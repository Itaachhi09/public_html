<?php
/**
 * Benefit Definition Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/BenefitDefinitionModel.php';

class BenefitDefinitionController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new BenefitDefinitionModel();
    }

    /**
     * Get all benefits
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $benefits = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $benefits,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Benefits retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving benefits: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single benefit
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $benefit = $this->model->find($id);
            
            if (!$benefit) {
                $this->respondError('Benefit not found', 404);
                return;
            }

            $this->respondSuccess($benefit, 'Benefit retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving benefit: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new benefit
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'Compensation Manager']);

            $data = [
                'code' => $_POST['code'] ?? '',
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'taxable' => isset($_POST['taxable']) ? 1 : 0,
                'eligible_roles' => $_POST['eligible_roles'] ?? 'All',
                'effective_from' => $_POST['effective_from'] ?? date('Y-m-d'),
                'effective_to' => $_POST['effective_to'] ?? null,
                'attach_to' => $_POST['attach_to'] ?? 'role',
                'is_active' => isset($_POST['is_active']) ? 1 : 1
            ];

            if (empty($data['code']) || empty($data['name'])) {
                $this->respondError('Code and name are required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create benefit', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'Benefit created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating benefit: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update benefit
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'Compensation Manager']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Benefit not found', 404);
                return;
            }

            $data = [
                'name' => $_POST['name'] ?? $existing['name'],
                'description' => $_POST['description'] ?? $existing['description'],
                'taxable' => isset($_POST['taxable']) ? 1 : 0,
                'eligible_roles' => $_POST['eligible_roles'] ?? $existing['eligible_roles'],
                'effective_to' => $_POST['effective_to'] ?? $existing['effective_to'],
                'attach_to' => $_POST['attach_to'] ?? $existing['attach_to'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'Benefit updated successfully');
            } else {
                $this->respondError('Failed to update benefit', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating benefit: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete benefit
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Benefit not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Benefit deleted successfully');
            } else {
                $this->respondError('Failed to delete benefit', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting benefit: ' . $e->getMessage(), 500);
        }
    }
}
?>
