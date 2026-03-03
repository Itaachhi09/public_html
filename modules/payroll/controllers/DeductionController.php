<?php
/**
 * Deduction Controller
 */

require_once __DIR__ . '/../../../config/BaseController.php';
require_once __DIR__ . '/../models/Deduction.php';

class DeductionController extends BaseController {
    private $deductionModel;

    public function __construct() {
        parent::__construct();
        $this->deductionModel = new Deduction();
    }

    /**
     * Get all deductions
     */
    public function getAll() {
        try {
            $deductions = $this->deductionModel->getAll();
            return [
                'success' => true,
                'data' => $deductions
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
            $deductions = $this->deductionModel->getByPayrollRun($payrollRunId);
            return [
                'success' => true,
                'data' => $deductions
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create deduction
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
                'type' => $_POST['type'] ?? 'Other',
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

            $id = $this->deductionModel->create($data);
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Deduction created successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update deduction
     */
    public function update($id) {
        try {
            $existing = $this->deductionModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Deduction not found'
                ];
            }

            $data = [
                'type' => $_POST['type'] ?? $existing['type'],
                'amount' => $_POST['amount'] ?? $existing['amount'],
                'description' => $_POST['description'] ?? $existing['description']
            ];

            $this->deductionModel->update($id, $data);
            return [
                'success' => true,
                'message' => 'Deduction updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete deduction
     */
    public function delete($id) {
        try {
            $existing = $this->deductionModel->find($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'error' => 'Deduction not found'
                ];
            }

            $this->deductionModel->delete($id);
            return [
                'success' => true,
                'message' => 'Deduction deleted successfully'
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
