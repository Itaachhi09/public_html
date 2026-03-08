<?php
/**
 * Location Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/LocationModel.php';

class LocationController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new LocationModel();
    }

    /**
     * Get all locations
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $locations = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $locations,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Locations retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving locations: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single location
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $location = $this->model->find($id);
            
            if (!$location) {
                $this->respondError('Location not found', 404);
                return;
            }

            $this->respondSuccess($location, 'Location retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new location
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'HR']);

            $data = [
                'location_name' => $_POST['location_name'] ?? '',
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'country' => $_POST['country'] ?? null,
                'address' => $_POST['address'] ?? null,
                'postal_code' => $_POST['postal_code'] ?? null,
                'status' => $_POST['status'] ?? 'Active'
            ];

            if (empty($data['location_name'])) {
                $this->respondError('Location name is required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create location', 500);
                return;
            }

            $created = $this->model->find($id);
            $this->respondSuccess($created, 'Location created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update location
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Location not found', 404);
                return;
            }

            $data = [
                'location_name' => $_POST['location_name'] ?? $existing['location_name'],
                'city' => $_POST['city'] ?? $existing['city'],
                'state' => $_POST['state'] ?? $existing['state'],
                'country' => $_POST['country'] ?? $existing['country'],
                'address' => $_POST['address'] ?? $existing['address'],
                'postal_code' => $_POST['postal_code'] ?? $existing['postal_code'],
                'status' => $_POST['status'] ?? $existing['status']
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->find($id);
                $this->respondSuccess($updated, 'Location updated successfully');
            } else {
                $this->respondError('Failed to update location', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete location
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Location not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Location deleted successfully');
            } else {
                $this->respondError('Failed to delete location', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting location: ' . $e->getMessage(), 500);
        }
    }
}
?>
