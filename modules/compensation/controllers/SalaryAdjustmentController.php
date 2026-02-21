<?php
/**
 * Salary Adjustment Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/SalaryAdjustmentModel.php';

class SalaryAdjustmentController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new SalaryAdjustmentModel();
    }

    /**
     * Get all salary adjustments
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $adjustments = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $adjustments,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Salary adjustments retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving adjustments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single adjustment
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $adjustment = $this->model->find($id);
            
            if (!$adjustment) {
                $this->respondError('Salary adjustment not found', 404);
                return;
            }

            $this->respondSuccess($adjustment, 'Salary adjustment retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving adjustment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new salary adjustment
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'Compensation Manager']);

            // CORRECTED: Use database field names from salary_adjustments table schema
            $data = [
                'employee_id' => $_POST['employee_id'] ?? null,
                'adjustment_type' => $_POST['adjustment_type'] ?? 'Permanent',  // Temporary or Permanent
                'trigger_type' => $_POST['trigger_type'] ?? null,  // Promotion, Approved exception, Policy change
                'previous_amount' => $_POST['previous_amount'] ?? 0,
                'new_amount' => $_POST['new_amount'] ?? 0,
                'effective_date' => $_POST['effective_date'] ?? date('Y-m-d'),
                'end_date' => $_POST['end_date'] ?? null,  // For temporary adjustments
                'reason' => $_POST['reason'] ?? '',
                'status' => $_POST['status'] ?? 'pending',  // pending, approved, rejected
                'approved_by' => $_POST['approved_by'] ?? null,
                'salary_band_id' => $_POST['salary_band_id'] ?? null
            ];

            if (empty($data['employee_id']) || empty($data['new_amount']) || empty($data['trigger_type'])) {
                $this->respondError('Employee ID, new amount, and trigger type are required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create salary adjustment', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'Salary adjustment created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating adjustment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update salary adjustment
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'Compensation Manager']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Salary adjustment not found', 404);
                return;
            }

            // CORRECTED: Use database field names from salary_adjustments table schema
            $data = [
                'adjustment_type' => $_POST['adjustment_type'] ?? $existing['adjustment_type'],
                'trigger_type' => $_POST['trigger_type'] ?? $existing['trigger_type'],
                'previous_amount' => $_POST['previous_amount'] ?? $existing['previous_amount'],
                'new_amount' => $_POST['new_amount'] ?? $existing['new_amount'],
                'effective_date' => $_POST['effective_date'] ?? $existing['effective_date'],
                'end_date' => $_POST['end_date'] ?? $existing['end_date'],
                'reason' => $_POST['reason'] ?? $existing['reason'],
                'status' => $_POST['status'] ?? $existing['status'],
                'approved_by' => $_POST['approved_by'] ?? $existing['approved_by'],
                'salary_band_id' => $_POST['salary_band_id'] ?? $existing['salary_band_id']
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'Salary adjustment updated successfully');
            } else {
                $this->respondError('Failed to update adjustment', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating adjustment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete salary adjustment
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Salary adjustment not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Salary adjustment deleted successfully');
            } else {
                $this->respondError('Failed to delete adjustment', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting adjustment: ' . $e->getMessage(), 500);
        }
    }
}
?>
