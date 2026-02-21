<?php
/**
 * HMO Plan Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/HMOPlanModel.php';

class HMOPlanController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new HMOPlanModel();
    }

    /**
     * Get all HMO plans
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $plans = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $plans,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'HMO plans retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving HMO plans: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single plan
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $plan = $this->model->find($id);
            
            if (!$plan) {
                $this->respondError('HMO plan not found', 404);
                return;
            }

            $this->respondSuccess($plan, 'HMO plan retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving HMO plan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new plan
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'HMO Manager']);

            $data = [
                'provider_id' => $_POST['provider_id'] ?? null,
                'plan_code' => $_POST['plan_code'] ?? '',
                'plan_name' => $_POST['plan_name'] ?? '',
                'plan_type' => $_POST['plan_type'] ?? 'Standard',
                'description' => $_POST['description'] ?? null,
                'annual_premium_per_employee' => $_POST['annual_premium_per_employee'] ?? 0,
                'annual_premium_per_dependent' => $_POST['annual_premium_per_dependent'] ?? 0,
                'monthly_premium' => $_POST['monthly_premium'] ?? 0,
                'out_of_pocket_limit' => $_POST['out_of_pocket_limit'] ?? 0,
                'deductible_amount' => $_POST['deductible_amount'] ?? 0,
                'copay_percentage' => $_POST['copay_percentage'] ?? 20,
                'in_network_doctors' => $_POST['in_network_doctors'] ?? 0,
                'in_network_hospitals' => $_POST['in_network_hospitals'] ?? 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 1,
                'plan_launch_date' => $_POST['plan_launch_date'] ?? date('Y-m-d')
            ];

            if (empty($data['provider_id']) || empty($data['plan_code']) || empty($data['plan_name'])) {
                $this->respondError('Provider ID, plan code, and plan name are required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create HMO plan', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'HMO plan created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating HMO plan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update plan
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'HMO Manager']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('HMO plan not found', 404);
                return;
            }

            $data = [
                'plan_name' => $_POST['plan_name'] ?? $existing['plan_name'],
                'plan_type' => $_POST['plan_type'] ?? $existing['plan_type'],
                'description' => $_POST['description'] ?? $existing['description'],
                'annual_premium_per_employee' => $_POST['annual_premium_per_employee'] ?? $existing['annual_premium_per_employee'],
                'annual_premium_per_dependent' => $_POST['annual_premium_per_dependent'] ?? $existing['annual_premium_per_dependent'],
                'monthly_premium' => $_POST['monthly_premium'] ?? $existing['monthly_premium'],
                'out_of_pocket_limit' => $_POST['out_of_pocket_limit'] ?? $existing['out_of_pocket_limit'],
                'deductible_amount' => $_POST['deductible_amount'] ?? $existing['deductible_amount'],
                'copay_percentage' => $_POST['copay_percentage'] ?? $existing['copay_percentage'],
                'in_network_doctors' => $_POST['in_network_doctors'] ?? $existing['in_network_doctors'],
                'in_network_hospitals' => $_POST['in_network_hospitals'] ?? $existing['in_network_hospitals'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'HMO plan updated successfully');
            } else {
                $this->respondError('Failed to update HMO plan', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating HMO plan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete plan
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('HMO plan not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'HMO plan deleted successfully');
            } else {
                $this->respondError('Failed to delete HMO plan', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting HMO plan: ' . $e->getMessage(), 500);
        }
    }
}
?>
