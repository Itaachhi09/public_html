<?php
/**
 * HMO Provider Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/HMOProviderModel.php';

class HMOProviderController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new HMOProviderModel();
    }

    /**
     * Get all HMO providers
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $providers = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $providers,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'HMO providers retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving HMO providers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single provider
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $provider = $this->model->find($id);
            
            if (!$provider) {
                $this->respondError('HMO provider not found', 404);
                return;
            }

            $this->respondSuccess($provider, 'HMO provider retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving HMO provider: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new provider
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'HMO Manager']);

            $data = [
                'provider_code' => $_POST['provider_code'] ?? '',
                'provider_name' => $_POST['provider_name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'head_office_address' => $_POST['head_office_address'] ?? null,
                'contact_phone' => $_POST['contact_phone'] ?? null,
                'contact_email' => $_POST['contact_email'] ?? null,
                'primary_contact_person' => $_POST['primary_contact_person'] ?? null,
                'primary_contact_title' => $_POST['primary_contact_title'] ?? null,
                'website' => $_POST['website'] ?? null,
                'accreditation_number' => $_POST['accreditation_number'] ?? null,
                'established_year' => $_POST['established_year'] ?? null,
                'provider_status' => $_POST['provider_status'] ?? 'Active',
                'provider_type' => $_POST['provider_type'] ?? 'Corporate',
                'contract_start_date' => $_POST['contract_start_date'] ?? null,
                'contract_end_date' => $_POST['contract_end_date'] ?? null,
                'payment_terms_days' => $_POST['payment_terms_days'] ?? 30,
                'is_active' => isset($_POST['is_active']) ? 1 : 1
            ];

            if (empty($data['provider_code']) || empty($data['provider_name'])) {
                $this->respondError('Provider code and name are required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create HMO provider', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'HMO provider created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating HMO provider: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update provider
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'HMO Manager']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('HMO provider not found', 404);
                return;
            }

            $data = [
                'provider_name' => $_POST['provider_name'] ?? $existing['provider_name'],
                'description' => $_POST['description'] ?? $existing['description'],
                'head_office_address' => $_POST['head_office_address'] ?? $existing['head_office_address'],
                'contact_phone' => $_POST['contact_phone'] ?? $existing['contact_phone'],
                'contact_email' => $_POST['contact_email'] ?? $existing['contact_email'],
                'primary_contact_person' => $_POST['primary_contact_person'] ?? $existing['primary_contact_person'],
                'primary_contact_title' => $_POST['primary_contact_title'] ?? $existing['primary_contact_title'],
                'website' => $_POST['website'] ?? $existing['website'],
                'accreditation_number' => $_POST['accreditation_number'] ?? $existing['accreditation_number'],
                'established_year' => $_POST['established_year'] ?? $existing['established_year'],
                'provider_status' => $_POST['provider_status'] ?? $existing['provider_status'],
                'provider_type' => $_POST['provider_type'] ?? $existing['provider_type'],
                'contract_start_date' => $_POST['contract_start_date'] ?? $existing['contract_start_date'],
                'contract_end_date' => $_POST['contract_end_date'] ?? $existing['contract_end_date'],
                'payment_terms_days' => $_POST['payment_terms_days'] ?? $existing['payment_terms_days'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'HMO provider updated successfully');
            } else {
                $this->respondError('Failed to update HMO provider', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating HMO provider: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete provider
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('HMO provider not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'HMO provider deleted successfully');
            } else {
                $this->respondError('Failed to delete HMO provider', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting HMO provider: ' . $e->getMessage(), 500);
        }
    }
}
?>
