<?php
/**
 * HR Core Module - Comprehensive Controller
 * Handles all HR Core operations including employees, departments, documents, movements, onboarding
 */
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
set_exception_handler(function (Throwable $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => 'Internal Server Error', 'data' => [], 'error' => $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
    exit;
});

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Auth.php';
require_once __DIR__ . '/../../config/Response.php';
require_once __DIR__ . '/../../config/Request.php';
require_once __DIR__ . '/../../config/BaseController.php';
require_once __DIR__ . '/../../config/BaseModel.php';
require_once __DIR__ . '/../../config/AuditLogger.php';

// Import all models
require_once __DIR__ . '/models/Employee.php';
require_once __DIR__ . '/models/Department.php';
require_once __DIR__ . '/models/JobTitle.php';
require_once __DIR__ . '/models/EmploymentType.php';
require_once __DIR__ . '/models/Location.php';
require_once __DIR__ . '/models/EmployeeDocument.php';
require_once __DIR__ . '/models/EmployeeMovement.php';
require_once __DIR__ . '/models/OnboardingChecklist.php';
require_once __DIR__ . '/models/EmployeeRole.php';

class HRCoreController extends BaseController {
    protected $employeeModel;
    protected $departmentModel;
    protected $jobTitleModel;
    protected $employmentTypeModel;
    protected $locationModel;
    protected $documentModel;
    protected $movementModel;
    protected $onboardingModel;
    protected $roleModel;
    protected $auditLogger;

    public function __construct() {
        parent::__construct();
        $this->employeeModel = new Employee();
        $this->departmentModel = new Department();
        $this->jobTitleModel = new JobTitle();
        $this->employmentTypeModel = new EmploymentType();
        $this->locationModel = new Location();
        $this->documentModel = new EmployeeDocument();
        $this->movementModel = new EmployeeMovement();
        $this->onboardingModel = new OnboardingChecklist();
        $this->roleModel = new EmployeeRole();
        $this->auditLogger = new AuditLogger();
    }

    // ===================== EMPLOYEE MANAGEMENT - COMPREHENSIVE CRUD =====================

    /**
     * Get all employees with pagination and filters
     * GET ?action=getEmployees&limit=10&offset=0&dept_id=1&status=Active
     */
    public function getEmployees() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager', 'payroll', 'finance']);

        $limit = (int) ($this->getInput('limit') ?? 10);
        $offset = (int) ($this->getInput('offset') ?? 0);
        $deptId = $this->getInput('dept_id');
        $status = $this->getInput('status');

        $filters = [];
        if ($deptId) $filters['department_id'] = $deptId;
        if ($status) $filters['employment_status'] = $status;

        try {
            $employees = $this->employeeModel->getAllWithDetails($limit, $offset, $filters);
            $total = $this->employeeModel->getTotalCount($filters);
            $this->respondSuccess([
                'employees' => is_array($employees) ? $employees : [],
                'total' => (int) $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        } catch (Throwable $e) {
            $this->respondError('Failed to load employees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get single employee with full details
     * GET ?action=getEmployee&id=1
     */
    public function getEmployee() {
        $this->checkRole(['admin', 'hr', 'manager', 'employee']);
        
        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $employee = $this->employeeModel->getWithDetails($id);
        if (!$employee) {
            $this->respondError('Employee not found', 404);
            return;
        }

        // Log audit trail
        $this->auditLogger->log('employee_view', 'Employee viewed', $id, $this->user->user_id);

        $this->respondSuccess($employee);
    }

    /**
     * Create new employee with complete profile
     * POST ?action=createEmployee
     */
    public function createEmployee() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $errors = [];

        // Validate required fields
        if (!$input['employee_code']) $errors['employee_code'] = 'Employee code required';
        if (!$input['first_name']) $errors['first_name'] = 'First name required';
        if (!$input['last_name']) $errors['last_name'] = 'Last name required';
        if (!$input['email']) $errors['email'] = 'Email required';
        if (!$input['department_id']) $errors['department_id'] = 'Department required';
        if (!$input['job_title_id']) $errors['job_title_id'] = 'Job title required';

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        // Check for duplicate employee code
        if ($this->employeeModel->findByCode($input['employee_code'])) {
            $this->respondError('Employee code already exists', 400);
            return;
        }

        $data = [
            'employee_code' => $input['employee_code'],
            'first_name' => $input['first_name'],
            'middle_name' => $input['middle_name'] ?? null,
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'date_of_birth' => $input['date_of_birth'] ?? null,
            'gender' => $input['gender'] ?? null,
            'nationality' => $input['nationality'] ?? null,
            'address' => $input['address'] ?? null,
            'city' => $input['city'] ?? null,
            'state' => $input['state'] ?? null,
            'postal_code' => $input['postal_code'] ?? null,
            'country' => $input['country'] ?? null,
            'job_title_id' => $input['job_title_id'],
            'department_id' => $input['department_id'],
            'employment_type_id' => $input['employment_type_id'] ?? null,
            'location_id' => $input['location_id'] ?? null,
            'supervisor_id' => $input['supervisor_id'] ?? null,
            'date_of_joining' => $input['date_of_joining'] ?? date('Y-m-d'),
            'employment_status' => 'Active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $employeeId = $this->employeeModel->create($data);
        
        if (!$employeeId) {
            $this->respondError('Failed to create employee', 500);
            return;
        }

        // Log audit trail
        $this->auditLogger->log('employee_create', 'Employee created: ' . $input['first_name'] . ' ' . $input['last_name'], $employeeId, $this->user->user_id);

        $this->respondSuccess(['employee_id' => $employeeId], 'Employee created successfully', 201);
    }

    /**
     * Update employee information
     * PUT ?action=updateEmployee&id=1
     */
    public function updateEmployee() {
        $this->checkRole(['admin', 'hr']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        // Verify employee exists
        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            $this->respondError('Employee not found', 404);
            return;
        }

        $input = $this->getInput();
        $data = [
            'first_name' => $input['first_name'] ?? null,
            'middle_name' => $input['middle_name'] ?? null,
            'last_name' => $input['last_name'] ?? null,
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'date_of_birth' => $input['date_of_birth'] ?? null,
            'gender' => $input['gender'] ?? null,
            'address' => $input['address'] ?? null,
            'city' => $input['city'] ?? null,
            'state' => $input['state'] ?? null,
            'postal_code' => $input['postal_code'] ?? null,
            'job_title_id' => $input['job_title_id'] ?? null,
            'department_id' => $input['department_id'] ?? null,
            'location_id' => $input['location_id'] ?? null,
            'supervisor_id' => $input['supervisor_id'] ?? null,
            'employment_status' => $input['employment_status'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Remove null values
        $data = array_filter($data, fn($val) => $val !== null);

        if (empty($data)) {
            $this->respondError('No data to update', 400);
            return;
        }

        $this->employeeModel->update($id, $data);
        
        // Log audit trail
        $this->auditLogger->log('employee_update', 'Employee updated', $id, $this->user->user_id);
        
        $this->respondSuccess(null, 'Employee updated successfully');
    }

    /**
     * Delete employee (soft delete - mark as terminated)
     * DELETE ?action=deleteEmployee&id=1
     */
    public function deleteEmployee() {
        $this->checkRole(['admin']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            $this->respondError('Employee not found', 404);
            return;
        }

        $data = [
            'employment_status' => 'Terminated',
            'last_working_day' => date('Y-m-d'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->employeeModel->update($id, $data);
        
        // Log audit trail
        $this->auditLogger->log('employee_delete', 'Employee terminated', $id, $this->user->user_id);
        
        $this->respondSuccess(null, 'Employee terminated successfully');
    }

    /**
     * Search employees by keyword
     * GET ?action=searchEmployees&keyword=john&limit=10
     */
    public function searchEmployees() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $keyword = $this->getInput('keyword');
        $limit = $this->getInput('limit') ?? 10;

        if (!$keyword) {
            $this->respondError('Search keyword required', 400);
            return;
        }

        $results = $this->employeeModel->search($keyword, $limit);
        $this->respondSuccess(['employees' => $results]);
    }

    /**
     * Get employees by department
     * GET ?action=getEmployeesByDepartment&dept_id=1
     */
    public function getEmployeesByDepartment() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $deptId = $this->getInput('dept_id');
        if (!$deptId) {
            $this->respondError('Department ID required', 400);
            return;
        }

        $employees = $this->employeeModel->getByDepartment($deptId);
        $this->respondSuccess(['employees' => $employees]);
    }

    /**
     * Get employees by job title
     * GET ?action=getEmployeesByPosition&pos_id=1
     */
    public function getEmployeesByPosition() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $posId = $this->getInput('pos_id');
        if (!$posId) {
            $this->respondError('Position ID required', 400);
            return;
        }

        $employees = $this->employeeModel->getByPosition($posId);
        $this->respondSuccess(['employees' => $employees]);
    }

    /**
     * Get employee lifecycle timeline (joining, movements, termination)
     * GET ?action=getEmployeeLifecycle&emp_id=1
     */
    public function getEmployeeLifecycle() {
        $this->checkRole(['admin', 'hr']);

        $empId = $this->getInput('emp_id');
        if (!$empId) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $employee = $this->employeeModel->find($empId);
        if (!$employee) {
            $this->respondError('Employee not found', 404);
            return;
        }

        // Get employee movements
        $movements = $this->movementModel->getByEmployee($empId);
        
        // Get onboarding status
        $onboarding = $this->onboardingModel->getOnboardingStatus($empId);

        $lifecycle = [
            'joining_date' => $employee['date_of_joining'],
            'current_status' => $employee['employment_status'],
            'movements' => $movements,
            'onboarding_status' => $onboarding
        ];

        if ($employee['employment_status'] === 'Terminated') {
            $lifecycle['termination_date'] = $employee['last_working_day'] ?? null;
        }

        $this->respondSuccess($lifecycle);
    }

    // ===================== DEPARTMENT MANAGEMENT - COMPREHENSIVE CRUD =====================

    /**
     * Get all departments with hierarchy
     * GET ?action=getDepartments
     */
    public function getDepartments() {
        $this->checkRole(['admin', 'hr']);

        $departments = $this->departmentModel->getAllWithHierarchy();
        $this->respondSuccess(['departments' => $departments]);
    }

    /**
     * Get single department
     * GET ?action=getDepartment&id=1
     */
    public function getDepartment() {
        $this->checkRole(['admin', 'hr']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Department ID required', 400);
            return;
        }

        $department = $this->departmentModel->getWithHead($id);
        $this->respondSuccess($department);
    }

    /**
     * Create department
     * POST ?action=createDepartment
     */
    public function createDepartment() {
        $this->checkRole(['admin']);

        $input = $this->getInput();
        $errors = [];

        if (!$input['department_name']) $errors['department_name'] = 'Department name required';

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        $data = [
            'department_name' => $input['department_name'],
            'parent_department_id' => $input['parent_department_id'] ?? null,
            'department_head_id' => $input['department_head_id'] ?? null,
            'description' => $input['description'] ?? null,
            'cost_center' => $input['cost_center'] ?? null,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $deptId = $this->departmentModel->create($data);
        
        // Log audit trail
        $this->auditLogger->log('department_create', 'Department created: ' . $input['department_name'], $deptId, $this->user->user_id);
        
        $this->respondSuccess(['department_id' => $deptId], 'Department created successfully', 201);
    }

    /**
     * Update department
     * PUT ?action=updateDepartment&id=1
     */
    public function updateDepartment() {
        $this->checkRole(['admin']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Department ID required', 400);
            return;
        }

        $department = $this->departmentModel->find($id);
        if (!$department) {
            $this->respondError('Department not found', 404);
            return;
        }

        $input = $this->getInput();
        $data = [
            'department_name' => $input['department_name'] ?? null,
            'parent_department_id' => $input['parent_department_id'] ?? null,
            'department_head_id' => $input['department_head_id'] ?? null,
            'description' => $input['description'] ?? null,
            'cost_center' => $input['cost_center'] ?? null,
            'status' => $input['status'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $data = array_filter($data, fn($val) => $val !== null);

        if (empty($data)) {
            $this->respondError('No data to update', 400);
            return;
        }

        $this->departmentModel->update($id, $data);
        
        // Log audit trail
        $this->auditLogger->log('department_update', 'Department updated', $id, $this->user->user_id);
        
        $this->respondSuccess(null, 'Department updated successfully');
    }

    /**
     * Delete department
     * DELETE ?action=deleteDepartment&id=1
     */
    public function deleteDepartment() {
        $this->checkRole(['admin']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Department ID required', 400);
            return;
        }

        $data = ['status' => 'Inactive', 'updated_at' => date('Y-m-d H:i:s')];
        $this->departmentModel->update($id, $data);
        
        // Log audit trail
        $this->auditLogger->log('department_delete', 'Department deactivated', $id, $this->user->user_id);
        
        $this->respondSuccess(null, 'Department deleted successfully');
    }

    // ===================== DOCUMENTS MANAGEMENT - COMPREHENSIVE =====================

    /**
     * Get employee documents
     * GET ?action=getEmployeeDocuments&emp_id=1
     */
    public function getEmployeeDocuments() {
        $this->checkRole(['admin', 'hr', 'employee']);

        $empId = $this->getInput('emp_id');
        if (!$empId) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $documents = $this->documentModel->getByEmployee($empId);
        $this->respondSuccess(['documents' => $documents]);
    }

    /**
     * Upload/Create employee document
     * POST ?action=uploadDocument
     */
    public function uploadDocument() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $errors = [];

        if (!$input['employee_id']) $errors['employee_id'] = 'Employee ID required';
        if (!$input['document_type']) $errors['document_type'] = 'Document type required';
        if (!$input['document_name']) $errors['document_name'] = 'Document name required';

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        $data = [
            'employee_id' => $input['employee_id'],
            'document_type' => $input['document_type'],
            'document_name' => $input['document_name'],
            'file_path' => $input['file_path'] ?? null,
            'issue_date' => $input['issue_date'] ?? null,
            'expiry_date' => $input['expiry_date'] ?? null,
            'remarks' => $input['remarks'] ?? null,
            'version' => 1,
            'uploaded_by' => $this->user->user_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $docId = $this->documentModel->create($data);
        
        // Log audit trail
        $this->auditLogger->log('document_upload', 'Document uploaded: ' . $input['document_name'], $docId, $this->user->user_id);
        
        $this->respondSuccess(['document_id' => $docId], 'Document uploaded successfully', 201);
    }

    /**
     * Get expiring documents
     * GET ?action=getExpiringDocuments&days=30
     */
    public function getExpiringDocuments() {
        $this->checkRole(['admin', 'hr']);

        $days = $this->getInput('days') ?? 30;
        $documents = $this->documentModel->getExpiringDocuments($days);
        $this->respondSuccess(['documents' => $documents]);
    }

    /**
     * Get expired documents
     * GET ?action=getExpiredDocuments
     */
    public function getExpiredDocuments() {
        $this->checkRole(['admin', 'hr']);

        $documents = $this->documentModel->getExpiredDocuments();
        $this->respondSuccess(['documents' => $documents]);
    }

    // ===================== EMPLOYEE MOVEMENTS =====================

    /**
     * Get employee movements/history
     * GET ?action=getEmployeeMovements&emp_id=1
     */
    public function getEmployeeMovements() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $empId = $this->getInput('emp_id');
        if (!$empId) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $movements = $this->movementModel->getByEmployee($empId);
        $this->respondSuccess(['movements' => $movements]);
    }

    /**
     * Create employee movement (promotion/transfer/etc)
     * POST ?action=createMovement
     */
    public function createMovement() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $input = $this->getInput();
        $errors = [];

        if (!$input['employee_id']) $errors['employee_id'] = 'Employee ID required';
        if (!$input['movement_type']) $errors['movement_type'] = 'Movement type required';
        if (!$input['effective_date']) $errors['effective_date'] = 'Effective date required';

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        $data = [
            'employee_id' => $input['employee_id'],
            'movement_type' => $input['movement_type'],
            'effective_date' => $input['effective_date'],
            'from_job_title_id' => $input['from_job_title_id'] ?? null,
            'to_job_title_id' => $input['to_job_title_id'] ?? null,
            'from_department_id' => $input['from_department_id'] ?? null,
            'to_department_id' => $input['to_department_id'] ?? null,
            'from_location_id' => $input['from_location_id'] ?? null,
            'to_location_id' => $input['to_location_id'] ?? null,
            'from_salary' => $input['from_salary'] ?? null,
            'to_salary' => $input['to_salary'] ?? null,
            'reason' => $input['reason'] ?? null,
            'status' => 'Pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $movementId = $this->movementModel->create($data);
        
        // Log audit trail
        $this->auditLogger->log('movement_create', 'Employee movement created', $movementId, $this->user->user_id);
        
        $this->respondSuccess(['movement_id' => $movementId], 'Movement request created successfully', 201);
    }

    /**
     * Get pending movements
     * GET ?action=getPendingMovements
     */
    public function getPendingMovements() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $movements = $this->movementModel->getPendingApprovals();
        $this->respondSuccess(['movements' => $movements]);
    }

    /**
     * Approve movement
     * PUT ?action=approveMovement&id=1
     */
    public function approveMovement() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Movement ID required', 400);
            return;
        }

        $this->movementModel->approve($id, $this->user->user_id);
        
        // Log audit trail
        $this->auditLogger->log('movement_approve', 'Movement approved', $id, $this->user->user_id);
        
        $this->respondSuccess(null, 'Movement approved successfully');
    }

    /**
     * Reject movement
     * PUT ?action=rejectMovement&id=1
     */
    public function rejectMovement() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Movement ID required', 400);
            return;
        }

        $this->movementModel->reject($id);
        
        // Log audit trail
        $this->auditLogger->log('movement_reject', 'Movement rejected', $id, $this->user->user_id);
        
        $this->respondSuccess(null, 'Movement rejected successfully');
    }

    // ===================== ONBOARDING =====================

    /**
     * Get onboarding checklist
     * GET ?action=getOnboardingChecklist&emp_id=1
     */
    public function getOnboardingChecklist() {
        $this->checkRole(['admin', 'hr']);

        $empId = $this->getInput('emp_id');
        if (!$empId) {
            $this->respondError('Employee ID required', 400);
            return;
        }

        $checklist = $this->onboardingModel->getByEmployee($empId);
        $status = $this->onboardingModel->getOnboardingStatus($empId);

        $this->respondSuccess([
            'checklist' => $checklist,
            'status' => $status
        ]);
    }

    /**
     * Create onboarding checklist item
     * POST ?action=createChecklistItem
     */
    public function createChecklistItem() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $errors = [];

        if (!$input['employee_id']) $errors['employee_id'] = 'Employee ID required';
        if (!$input['checklist_item']) $errors['checklist_item'] = 'Checklist item required';

        if (!empty($errors)) {
            Response::validationError($errors);
            return;
        }

        $data = [
            'employee_id' => $input['employee_id'],
            'checklist_item' => $input['checklist_item'],
            'responsible_department' => $input['responsible_department'] ?? null,
            'due_date' => $input['due_date'] ?? null,
            'status' => 'Pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $itemId = $this->onboardingModel->create($data);
        
        // Log audit trail
        $this->auditLogger->log('onboarding_create', 'Onboarding item created', $itemId, $this->user->user_id);
        
        $this->respondSuccess(['checklist_item_id' => $itemId], 'Checklist item created successfully', 201);
    }

    /**
     * Complete checklist item
     * PUT ?action=completeChecklistItem&id=1
     */
    public function completeChecklistItem() {
        $this->checkRole(['admin', 'hr']);

        $id = $this->getInput('id');
        if (!$id) {
            $this->respondError('Checklist item ID required', 400);
            return;
        }

        $this->onboardingModel->completeItem($id, $this->user->user_id);
        
        // Log audit trail
        $this->auditLogger->log('onboarding_complete', 'Onboarding item completed', $id, $this->user->user_id);
        
        $this->respondSuccess(null, 'Checklist item completed successfully');
    }

    /**
     * Get overdue onboarding items
     * GET ?action=getOverdueOnboarding
     */
    public function getOverdueOnboarding() {
        $this->checkRole(['admin', 'hr']);

        $items = $this->onboardingModel->getOverdueItems();
        $this->respondSuccess(['items' => $items]);
    }

    // ===================== JOB TITLES & POSITIONS =====================

    /**
     * Get all job titles
     * GET ?action=getJobTitles&limit=20&offset=0
     */
    public function getJobTitles() {
        $this->checkRole(['admin', 'hr']);

        $limit = $this->getInput('limit') ?? 20;
        $offset = $this->getInput('offset') ?? 0;

        $titles = $this->jobTitleModel->getAllWithDetails($limit, $offset);
        $this->respondSuccess(['job_titles' => $titles]);
    }

    /**
     * Create job title
     * POST ?action=createJobTitle
     */
    public function createJobTitle() {
        $this->checkRole(['admin']);

        $input = $this->getInput();
        if (!$input['title']) {
            $this->respondError('Job title required', 400);
            return;
        }

        $data = [
            'title' => $input['title'],
            'description' => $input['description'] ?? null,
            'department_id' => $input['department_id'] ?? null,
            'salary_grade_id' => $input['salary_grade_id'] ?? null,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $titleId = $this->jobTitleModel->create($data);
        
        // Log audit trail
        $this->auditLogger->log('job_title_create', 'Job title created: ' . $input['title'], $titleId, $this->user->user_id);
        
        $this->respondSuccess(['job_title_id' => $titleId], 'Job title created successfully', 201);
    }

    // ===================== LOCATIONS =====================

    /**
     * Get all locations
     * GET ?action=getLocations
     */
    public function getLocations() {
        $this->checkRole(['admin', 'hr']);

        $locations = $this->locationModel->getAllWithDetails();
        $this->respondSuccess(['locations' => $locations]);
    }

    /**
     * Create location
     * POST ?action=createLocation
     */
    public function createLocation() {
        $this->checkRole(['admin']);

        $input = $this->getInput();
        if (!$input['location_name']) {
            $this->respondError('Location name required', 400);
            return;
        }

        $data = [
            'location_code' => $input['location_code'] ?? strtoupper(substr($input['location_name'], 0, 3)),
            'location_name' => $input['location_name'],
            'address' => $input['address'] ?? null,
            'city' => $input['city'] ?? null,
            'state' => $input['state'] ?? null,
            'country' => $input['country'] ?? null,
            'postal_code' => $input['postal_code'] ?? null,
            'phone' => $input['phone'] ?? null,
            'email' => $input['email'] ?? null,
            'manager_id' => $input['manager_id'] ?? null,
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $locId = $this->locationModel->create($data);
        
        // Log audit trail
        $this->auditLogger->log('location_create', 'Location created: ' . $input['location_name'], $locId, $this->user->user_id);
        
        $this->respondSuccess(['location_id' => $locId], 'Location created successfully', 201);
    }

    // ===================== ROLES & PERMISSIONS =====================

    /**
     * Get user roles
     * GET ?action=getUserRoles&user_id=1
     */
    public function getUserRoles() {
        $this->checkRole(['admin', 'hr']);

        $userId = $this->getInput('user_id');
        if (!$userId) {
            $this->respondError('User ID required', 400);
            return;
        }

        $roles = $this->roleModel->getByUser($userId);
        $permissions = $this->roleModel->getPermissionsByUser($userId);

        $this->respondSuccess([
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    /**
     * Assign role to user
     * POST ?action=assignRole
     */
    public function assignRole() {
        $this->checkRole(['admin']);

        $input = $this->getInput();
        if (!$input['user_id'] || !$input['role_id']) {
            $this->respondError('User ID and Role ID required', 400);
            return;
        }

        $this->roleModel->assignRole($input['user_id'], $input['role_id'], $this->user->user_id);
        
        // Log audit trail
        $this->auditLogger->log('role_assign', 'Role assigned', $input['user_id'], $this->user->user_id);
        
        $this->respondSuccess(null, 'Role assigned successfully', 201);
    }

    /**
     * Revoke role from user
     * DELETE ?action=revokeRole&user_id=1&role_id=2
     */
    public function revokeRole() {
        $this->checkRole(['admin']);

        $userId = $this->getInput('user_id');
        $roleId = $this->getInput('role_id');

        if (!$userId || !$roleId) {
            $this->respondError('User ID and Role ID required', 400);
            return;
        }

        $this->roleModel->revokeRole($userId, $roleId);
        
        // Log audit trail
        $this->auditLogger->log('role_revoke', 'Role revoked', $userId, $this->user->user_id);
        
        $this->respondSuccess(null, 'Role revoked successfully');
    }

    // ===================== HR REPORTS & ANALYTICS =====================

    /**
     * Get HR dashboard metrics
     * GET ?action=getHRMetrics
     */
    public function getHRMetrics() {
        $this->checkRole(['admin', 'hr', 'hr_chief', 'manager']);

        $dbInstance = new Database();
        $db = $dbInstance->connect();

        // Total employees
        $result = $db->query("SELECT COUNT(*) as count FROM employees WHERE employment_status = 'Active'");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $totalEmps = $row['count'] ?? 0;

        // New hires this month
        $result = $db->query("SELECT COUNT(*) as count FROM employees WHERE MONTH(date_of_joining) = MONTH(NOW()) AND YEAR(date_of_joining) = YEAR(NOW())");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $newHires = $row['count'] ?? 0;

        // Employees by department
        $result = $db->query("SELECT d.department_name, COUNT(e.employee_id) as count FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id WHERE e.employment_status = 'Active' GROUP BY e.department_id");
        $byDept = $result->fetchAll(PDO::FETCH_ASSOC);

        // Employees by employment type
        $result = $db->query("SELECT et.type_name, COUNT(e.employee_id) as count FROM employees e LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id WHERE e.employment_status = 'Active' GROUP BY e.employment_type_id");
        $byType = $result->fetchAll(PDO::FETCH_ASSOC);

        // Attrition rate
        $result = $db->query("SELECT COUNT(*) as count FROM employees WHERE employment_status = 'Terminated' AND YEAR(CURDATE()) = YEAR(last_working_day)");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $terminated = $row['count'] ?? 0;
        $attritionRate = $totalEmps > 0 ? round(($terminated / $totalEmps) * 100, 2) : 0;

        $this->respondSuccess([
            'total_employees' => $totalEmps,
            'new_hires_this_month' => $newHires,
            'attrition_rate' => $attritionRate . '%',
            'employees_by_department' => $byDept,
            'employees_by_employment_type' => $byType
        ]);
    }

    /**
     * Get headcount report
     * GET ?action=getHeadcountReport
     */
    public function getHeadcountReport() {
        $this->checkRole(['admin', 'hr']);

        $dbInstance = new Database();
        $db = $dbInstance->connect();
        $query = "
            SELECT 
                d.department_id,
                d.department_name,
                COUNT(CASE WHEN e.employment_status = 'Active' THEN 1 END) as active_count,
                COUNT(CASE WHEN e.employment_status = 'Inactive' THEN 1 END) as inactive_count,
                COUNT(CASE WHEN e.employment_status = 'On Leave' THEN 1 END) as on_leave_count,
                COUNT(CASE WHEN e.employment_status = 'Terminated' THEN 1 END) as terminated_count,
                COUNT(e.employee_id) as total_count
            FROM departments d
            LEFT JOIN employees e ON d.department_id = e.department_id
            GROUP BY d.department_id, d.department_name
            ORDER BY d.department_name
        ";
        
        $result = $db->query($query);
        $report = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->respondSuccess(['report' => $report]);
    }

    /**
     * Get employee status summary
     * GET ?action=getEmployeeStatusSummary
     */
    public function getEmployeeStatusSummary() {
        $this->checkRole(['admin', 'hr']);

        $dbInstance = new Database();
        $db = $dbInstance->connect();
        $query = "
            SELECT 
                employment_status,
                COUNT(*) as count
            FROM employees
            GROUP BY employment_status
        ";

        $result = $db->query($query);
        $summary = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->respondSuccess(['summary' => $summary]);
    }

    /**
     * Get turnover analysis
     * GET ?action=getTurnoverAnalysis
     */
    public function getTurnoverAnalysis() {
        $this->checkRole(['admin', 'hr']);

        $dbInstance = new Database();
        $db = $dbInstance->connect();
        $query = "
            SELECT 
                MONTH(last_working_day) as month,
                YEAR(last_working_day) as year,
                COUNT(*) as terminated_count,
                GROUP_CONCAT(CONCAT(first_name, ' ', last_name) SEPARATOR ', ') as employee_names
            FROM employees
            WHERE employment_status = 'Terminated'
            AND last_working_day >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(last_working_day), MONTH(last_working_day)
            ORDER BY year DESC, month DESC
        ";

        $result = $db->query($query);
        $analysis = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->respondSuccess(['analysis' => $analysis]);
    }

    // ===================== AUDIT & COMPLIANCE =====================

    /**
     * Get audit log
     * GET ?action=getAuditLog&limit=50&offset=0&action_type=employee_create
     */
    public function getAuditLog() {
        $this->checkRole(['admin', 'hr']);

        $limit = $this->getInput('limit') ?? 50;
        $offset = $this->getInput('offset') ?? 0;
        $actionType = $this->getInput('action_type');

        $logs = $this->auditLogger->getLog($limit, $offset, $actionType);
        $total = $this->auditLogger->getTotalCount($actionType);

        $this->respondSuccess([
            'logs' => $logs,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Export employee data to CSV
     * GET ?action=exportEmployeesCsv
     */
    public function exportEmployeesCsv() {
        $this->checkRole(['admin', 'hr']);

        $employees = $this->employeeModel->all();

        // CSV headers
        $headers = ['Employee Code', 'First Name', 'Last Name', 'Email', 'Phone', 'Department', 'Job Title', 'Status'];
        
        // Create CSV content
        $csv = implode(',', $headers) . "\n";
        foreach ($employees as $emp) {
            $csv .= implode(',', [
                $emp['employee_code'],
                $emp['first_name'],
                $emp['last_name'],
                $emp['email'],
                $emp['phone'],
                $emp['department_id'],
                $emp['job_title_id'],
                $emp['employment_status']
            ]) . "\n";
        }

        // Return CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=employees.csv');
        echo $csv;
        exit;
    }

    /**
     * Action router
     */
    public function route() {
        $action = $_GET['action'] ?? $_POST['action'] ?? null;

        if (!$action) {
            $this->respondError('Action not specified', 400);
            return;
        }

        if (method_exists($this, $action)) {
            $this->{$action}();
        } else {
            $this->respondError('Action not found', 404);
        }
    }
}

// Instantiate and route
try {
    $controller = new HRCoreController();
    $controller->route();
} catch (Throwable $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => 'Internal Server Error',
        'data' => [],
        'error' => $e->getMessage()
    ]);
    exit;
}
