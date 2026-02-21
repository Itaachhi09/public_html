<?php
/**
 * HR Core Module Router
 * Main entry point for HR Core CRUD operations
 */

session_start();

// Include required files
require_once dirname(__DIR__, 2) . '/config/Database.php';
require_once dirname(__DIR__, 2) . '/config/BaseModel.php';
require_once dirname(__DIR__, 2) . '/config/BaseController.php';
require_once dirname(__DIR__, 2) . '/config/Response.php';
require_once dirname(__DIR__, 2) . '/config/Auth.php';

// Include models
require_once __DIR__ . '/models/EmployeeModel.php';
require_once __DIR__ . '/models/DepartmentModel.php';
require_once __DIR__ . '/models/JobTitleModel.php';
require_once __DIR__ . '/models/LocationModel.php';
require_once __DIR__ . '/models/EmploymentTypeModel.php';

// Include controllers
require_once __DIR__ . '/controllers/EmployeeController.php';
require_once __DIR__ . '/controllers/DepartmentController.php';
require_once __DIR__ . '/controllers/JobTitleController.php';
require_once __DIR__ . '/controllers/LocationController.php';
require_once __DIR__ . '/controllers/EmploymentTypeController.php';

// Get action and entity from request
$action = $_GET['action'] ?? 'list';
$entity = $_GET['entity'] ?? 'employee';
$id = $_GET['id'] ?? null;

// Initialize models for views
$employeeModel = new EmployeeModel();
$departmentModel = new DepartmentModel();
$jobTitleModel = new JobTitleModel();
$locationModel = new LocationModel();
$employmentTypeModel = new EmploymentTypeModel();

// Handle actions
try {
    // Create GET requests return JSON (REST API)
    // Form POST requests redirect with messages (Web Form)
    
    $json_endpoints = $_SERVER['HTTP_ACCEPT'] === 'application/json' || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    
    switch ($action) {
        case 'list':
            handleList($entity);
            break;
        case 'view':
            handleView($entity, $id);
            break;
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                handleCreateForm($entity);
            } else {
                handleCreateSubmit($entity);
            }
            break;
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                handleEditForm($entity, $id);
            } else {
                handleUpdate($entity, $id);
            }
            break;
        case 'update':
            handleUpdate($entity, $id);
            break;
        case 'delete':
            handleDelete($entity, $id);
            break;
        case 'search':
            handleSearch($entity);
            break;
        default:
            handleList($entity);
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: /modules/hr_core/?action=list&entity=' . $entity);
    exit();
}

/**
 * Handle list action
 */
function handleList($entity) {
    global $employeeModel;

    // Load list view based on entity
    $limit = $_GET['limit'] ?? 10;
    $offset = $_GET['offset'] ?? 0;

    switch ($entity) {
        case 'employee':
            $employees = $employeeModel->getAllPaginated($limit, $offset);
            include __DIR__ . '/views/employee_list.php';
            break;
        default:
            include __DIR__ . '/views/dashboard.php';
    }
}

/**
 * Handle view action
 */
function handleView($entity, $id) {
    global $employeeModel;

    if (!$id) {
        $_SESSION['error'] = 'Invalid ID';
        header('Location: /modules/hr_core/?action=list&entity=' . $entity);
        exit();
    }

    switch ($entity) {
        case 'employee':
            $employee = $employeeModel->findWithDetails($id);
            if (!$employee) {
                $_SESSION['error'] = 'Employee not found';
                header('Location: /modules/hr_core/?action=list&entity=' . $entity);
                exit();
            }
            include __DIR__ . '/views/employee_view.php';
            break;
        default:
            $_SESSION['error'] = 'Invalid entity';
            header('Location: /modules/hr_core/');
            exit();
    }
}

/**
 * Handle create form display
 */
function handleCreateForm($entity) {
    global $employeeModel, $departmentModel, $jobTitleModel, $locationModel, $employmentTypeModel;

    switch ($entity) {
        case 'employee':
            $employee = [];
            include __DIR__ . '/views/employee_form.php';
            break;
        default:
            $_SESSION['error'] = 'Invalid entity';
            header('Location: /modules/hr_core/');
            exit();
    }
}

/**
 * Handle create form submission
 */
function handleCreateSubmit($entity) {
    global $employeeModel;

    switch ($entity) {
        case 'employee':
            $data = [
                'employee_code' => $_POST['employee_code'] ?? '',
                'first_name' => $_POST['first_name'] ?? '',
                'middle_name' => $_POST['middle_name'] ?? null,
                'last_name' => $_POST['last_name'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'gender' => $_POST['gender'] ?? 'Other',
                'marital_status' => $_POST['marital_status'] ?? 'Single',
                'email' => $_POST['email'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'address' => $_POST['address'] ?? null,
                'city' => $_POST['city'] ?? null,
                'state' => $_POST['state'] ?? null,
                'postal_code' => $_POST['postal_code'] ?? null,
                'country' => $_POST['country'] ?? null,
                'nationality' => $_POST['nationality'] ?? null,
                'job_title_id' => $_POST['job_title_id'] ?? null,
                'department_id' => $_POST['department_id'] ?? null,
                'employment_type_id' => $_POST['employment_type_id'] ?? null,
                'location_id' => $_POST['location_id'] ?? null,
                'supervisor_id' => $_POST['supervisor_id'] ?? null,
                'date_of_joining' => $_POST['date_of_joining'] ?? date('Y-m-d'),
                'employment_status' => $_POST['employment_status'] ?? 'Active'
            ];

            if (empty($data['employee_code']) || empty($data['first_name']) || empty($data['last_name'])) {
                $_SESSION['error'] = 'Employee code, first name, and last name are required';
                $_SESSION['form_data'] = $data;
                header('Location: /modules/hr_core/?action=create&entity=' . $entity);
                exit();
            }

            $id = $employeeModel->create($data);
            
            if ($id) {
                $_SESSION['success'] = 'Employee created successfully';
                header('Location: /modules/hr_core/?action=view&entity=' . $entity . '&id=' . $id);
            } else {
                $_SESSION['error'] = 'Failed to create employee';
                header('Location: /modules/hr_core/?action=create&entity=' . $entity);
            }
            exit();
            break;
    }
}

/**
 * Handle edit form display
 */
function handleEditForm($entity, $id) {
    global $employeeModel, $departmentModel, $jobTitleModel, $locationModel, $employmentTypeModel;

    if (!$id) {
        $_SESSION['error'] = 'Invalid ID';
        header('Location: /modules/hr_core/?action=list&entity=' . $entity);
        exit();
    }

    switch ($entity) {
        case 'employee':
            $employee = $employeeModel->find($id);
            if (!$employee) {
                $_SESSION['error'] = 'Employee not found';
                header('Location: /modules/hr_core/?action=list&entity=' . $entity);
                exit();
            }
            include __DIR__ . '/views/employee_form.php';
            break;
        default:
            $_SESSION['error'] = 'Invalid entity';
            header('Location: /modules/hr_core/');
            exit();
    }
}

/**
 * Handle update action
 */
function handleUpdate($entity, $id) {
    global $employeeModel;

    if (!$id) {
        $_SESSION['error'] = 'Invalid ID';
        header('Location: /modules/hr_core/?action=list&entity=' . $entity);
        exit();
    }

    switch ($entity) {
        case 'employee':
            $existing = $employeeModel->find($id);
            if (!$existing) {
                $_SESSION['error'] = 'Employee not found';
                header('Location: /modules/hr_core/?action=list&entity=' . $entity);
                exit();
            }

            $data = [
                'first_name' => $_POST['first_name'] ?? $existing['first_name'],
                'middle_name' => $_POST['middle_name'] ?? $existing['middle_name'],
                'last_name' => $_POST['last_name'] ?? $existing['last_name'],
                'date_of_birth' => $_POST['date_of_birth'] ?? $existing['date_of_birth'],
                'gender' => $_POST['gender'] ?? $existing['gender'],
                'marital_status' => $_POST['marital_status'] ?? $existing['marital_status'],
                'email' => $_POST['email'] ?? $existing['email'],
                'phone' => $_POST['phone'] ?? $existing['phone'],
                'address' => $_POST['address'] ?? $existing['address'],
                'city' => $_POST['city'] ?? $existing['city'],
                'state' => $_POST['state'] ?? $existing['state'],
                'postal_code' => $_POST['postal_code'] ?? $existing['postal_code'],
                'country' => $_POST['country'] ?? $existing['country'],
                'nationality' => $_POST['nationality'] ?? $existing['nationality'],
                'job_title_id' => $_POST['job_title_id'] ?? $existing['job_title_id'],
                'department_id' => $_POST['department_id'] ?? $existing['department_id'],
                'employment_type_id' => $_POST['employment_type_id'] ?? $existing['employment_type_id'],
                'location_id' => $_POST['location_id'] ?? $existing['location_id'],
                'supervisor_id' => $_POST['supervisor_id'] ?? $existing['supervisor_id'],
                'employment_status' => $_POST['employment_status'] ?? $existing['employment_status'],
                'last_working_day' => $_POST['last_working_day'] ?? $existing['last_working_day']
            ];

            if ($employeeModel->update($id, $data)) {
                $_SESSION['success'] = 'Employee updated successfully';
                header('Location: /modules/hr_core/?action=view&entity=' . $entity . '&id=' . $id);
            } else {
                $_SESSION['error'] = 'Failed to update employee';
                header('Location: /modules/hr_core/?action=edit&entity=' . $entity . '&id=' . $id);
            }
            exit();
            break;
    }
}

/**
 * Handle delete action
 */
function handleDelete($entity, $id) {
    global $employeeModel;

    if (!$id) {
        $_SESSION['error'] = 'Invalid ID';
        header('Location: /modules/hr_core/?action=list&entity=' . $entity);
        exit();
    }

    switch ($entity) {
        case 'employee':
            $existing = $employeeModel->find($id);
            if (!$existing) {
                $_SESSION['error'] = 'Employee not found';
                header('Location: /modules/hr_core/?action=list&entity=' . $entity);
                exit();
            }

            if ($employeeModel->update($id, ['employment_status' => 'Terminated'])) {
                $_SESSION['success'] = 'Employee deleted successfully (marked as terminated)';
                header('Location: /modules/hr_core/?action=list&entity=' . $entity);
            } else {
                $_SESSION['error'] = 'Failed to delete employee';
                header('Location: /modules/hr_core/?action=list&entity=' . $entity);
            }
            exit();
            break;
    }
}

/**
 * Handle search action
 */
function handleSearch($entity) {
    global $employeeModel;

    $term = $_GET['q'] ?? '';
    
    if (empty($term)) {
        $_SESSION['error'] = 'Search term required';
        header('Location: /modules/hr_core/?action=list&entity=' . $entity);
        exit();
    }

    switch ($entity) {
        case 'employee':
            $employees = $employeeModel->search($term);
            include __DIR__ . '/views/employee_list.php';
            break;
        default:
            header('Location: /modules/hr_core/?action=list&entity=' . $entity);
            exit();
    }
}
?>
