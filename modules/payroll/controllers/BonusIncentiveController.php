<?php
/**
 * Bonus & Incentive Controller
 */

require_once __DIR__ . '/../../../config/BaseController.php';
require_once __DIR__ . '/../models/BonusIncentive.php';

class BonusIncentiveController extends BaseController {
    private $bonusIncentiveModel;

    public function __construct() {
        parent::__construct();
        $this->bonusIncentiveModel = new BonusIncentive();
    }

    /**
     * Get all bonuses/incentives
     */
    public function getAll() {
        try {
            $bonuses = $this->bonusIncentiveModel->getAll();
            return [
                'success' => true,
                'data' => $bonuses
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get by payroll run
     */
    public function getByPayrollRun($payrollRunId) {
        try {
            $bonuses = $this->bonusIncentiveModel->getByPayrollRun($payrollRunId);
            return [
                'success' => true,
                'data' => $bonuses
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create bonus/incentive
     */
    public function create() {
        try {
            $payrollRunId = $_POST['payroll_run_id'] ?? null;
            // Convert empty string to NULL for optional foreign key
            if ($payrollRunId === '' || $payrollRunId === 0) {
                $payrollRunId = null;
            }
            
            $data = [
                'employee_id' => $_POST['employee_id'] ?? null,
                'payroll_run_id' => $payrollRunId,
                'type' => $_POST['type'] ?? 'Bonus',
                'amount' => $_POST['amount'] ?? 0,
                'description' => $_POST['description'] ?? '',
                'created_by' => $this->user['id'] ?? 0
            ];

            if (empty($data['employee_id']) || $data['amount'] <= 0) {
                return [
                    'success' => false,
                    'error' => 'Employee and amount are required'
                ];
            }

            $id = $this->bonusIncentiveModel->create($data);
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Bonus/Incentive created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update bonus/incentive
     */
    public function update($id) {
        try {
            $existing = $this->bonusIncentiveModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Bonus/Incentive not found'
                ];
            }

            $data = [
                'type' => $_POST['type'] ?? $existing['type'],
                'amount' => $_POST['amount'] ?? $existing['amount'],
                'description' => $_POST['description'] ?? $existing['description']
            ];

            $this->bonusIncentiveModel->update($id, $data);
            return [
                'success' => true,
                'message' => 'Bonus/Incentive updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete bonus/incentive
     */
    public function delete($id) {
        try {
            $existing = $this->bonusIncentiveModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Bonus/Incentive not found'
                ];
            }

            $this->bonusIncentiveModel->delete($id);
            return [
                'success' => true,
                'message' => 'Bonus/Incentive deleted successfully'
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
