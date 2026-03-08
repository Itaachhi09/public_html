<?php
/**
 * Job Title Controller
 */

require_once dirname(__DIR__, 3) . '/config/BaseController.php';
require_once dirname(__DIR__, 3) . '/config/Response.php';
require_once dirname(__DIR__, 3) . '/config/Auth.php';
require_once dirname(__DIR__) . '/models/JobTitleModel.php';

class JobTitleController extends BaseController {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new JobTitleModel();
    }

    /**
     * Get all job titles
     */
    public function list() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $jobTitles = $this->model->getAllPaginated($limit, $offset);
            $total = $this->model->count();

            $this->respondSuccess([
                'data' => $jobTitles,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ], 'Job titles retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving job titles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single job title
     */
    public function get($id) {
        try {
            $id = (int)$id;
            $jobTitle = $this->model->findWithDepartment($id);
            
            if (!$jobTitle) {
                $this->respondError('Job title not found', 404);
                return;
            }

            $this->respondSuccess($jobTitle, 'Job title retrieved successfully');
        } catch (Exception $e) {
            $this->respondError('Error retrieving job title: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create new job title
     */
    public function create() {
        try {
            $this->checkRole(['Admin', 'HR']);

            $data = [
                'job_title_name' => $_POST['job_title_name'] ?? '',
                'description' => $_POST['description'] ?? null,
                'level' => $_POST['level'] ?? null,
                'department_id' => $_POST['department_id'] ?? null,
                'status' => $_POST['status'] ?? 'Active'
            ];

            if (empty($data['job_title_name'])) {
                $this->respondError('Job title name is required', 400);
                return;
            }

            $id = $this->model->create($data);
            
            if (!$id) {
                $this->respondError('Failed to create job title', 500);
                return;
            }

            $created = $this->model->findWithDepartment($id);
            $this->respondSuccess($created, 'Job title created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating job title: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update job title
     */
    public function update($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Job title not found', 404);
                return;
            }

            $data = [
                'job_title_name' => $_POST['job_title_name'] ?? $existing['job_title_name'],
                'description' => $_POST['description'] ?? $existing['description'],
                'level' => $_POST['level'] ?? $existing['level'],
                'department_id' => $_POST['department_id'] ?? $existing['department_id'],
                'status' => $_POST['status'] ?? $existing['status']
            ];

            if ($this->model->update($id, $data)) {
                $updated = $this->model->findWithDepartment($id);
                $this->respondSuccess($updated, 'Job title updated successfully');
            } else {
                $this->respondError('Failed to update job title', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error updating job title: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete job title
     */
    public function delete($id) {
        try {
            $this->checkRole(['Admin', 'HR']);

            $id = (int)$id;
            $existing = $this->model->find($id);
            
            if (!$existing) {
                $this->respondError('Job title not found', 404);
                return;
            }

            if ($this->model->delete($id)) {
                $this->respondSuccess([], 'Job title deleted successfully');
            } else {
                $this->respondError('Failed to delete job title', 500);
            }
        } catch (Exception $e) {
            $this->respondError('Error deleting job title: ' . $e->getMessage(), 500);
        }
    }
}
?>
