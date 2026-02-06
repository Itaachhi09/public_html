<?php
/**
 * HR Core API Router
 * Handles all API requests for the HR Core module using database
 */

// Suppress all output except our JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Set proper headers FIRST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Catch any errors and convert to JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP Error',
        'error' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    ob_end_clean();
    exit;
});

// Catch any exceptions
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Exception',
        'error' => $e->getMessage()
    ]);
    ob_end_clean();
    exit;
});

// Clear output buffer from includes
ob_end_clean();

// Include database class from config
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/ModuleHelpers.php';

$dbInstance = new Database();
$db = $dbInstance->connect();

class Response {
    public static function success($data, $message = 'Success') {
        return json_encode(ModuleHelpers::success($data, $message));
    }

    public static function error($message, $data = null) {
        return json_encode(ModuleHelpers::error($message, 'API_ERROR', $data));
    }
}

// Get action from GET or POST
$action = isset($_GET['action']) ? $_GET['action'] : '';

// If no GET action, try POST body
if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}

// Debug: Check if database connected
if (!$db) {
    echo json_encode(ModuleHelpers::databaseError('Database connection failed'));
    exit;
}

try {
    switch ($action) {
        // EMPLOYEES
        case 'getEmployees':
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $department = isset($_GET['department_id']) ? $_GET['department_id'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';

            $query = "SELECT e.*, d.department_name as department, e.employee_id as id, e.employee_code as code, e.date_of_joining as hire_date FROM employees e 
                     LEFT JOIN departments d ON e.department_id = d.department_id WHERE 1=1";
            $params = [];
            
            if ($search) {
                $query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            }
            if ($department) {
                $query .= " AND e.department_id = ?";
                $params[] = $department;
            }
            if ($status) {
                $query .= " AND e.employment_status = ?";
                $params[] = $status;
            }

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $employees = $result ?: [];

            // Get high-level stats (overall, not filtered)
            $statsQuery = "SELECT 
                          COUNT(*) as total,
                          SUM(CASE WHEN employment_status = 'Active' THEN 1 ELSE 0 END) as active,
                          SUM(CASE WHEN employment_status = 'On Leave' THEN 1 ELSE 0 END) as on_leave,
                          SUM(CASE WHEN employment_status = 'Terminated' THEN 1 ELSE 0 END) as resigned
                          FROM employees";
            $statsResult = $db->query($statsQuery);
            $stats = $statsResult->fetch(PDO::FETCH_ASSOC) ?: [
                'total' => 0,
                'active' => 0,
                'on_leave' => 0,
                'resigned' => 0
            ];

            echo Response::success([
                'employees' => $employees,
                'total' => count($employees),
                'stats' => $stats
            ]);
            break;

        case 'getEmployeeById':
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $stmt = $db->prepare("SELECT e.*, d.department_name as department, e.employee_id as id, e.employee_code as code, e.date_of_joining as hire_date FROM employees e LEFT JOIN departments d ON e.department_id = d.department_id WHERE e.employee_id = ?");
            $stmt->execute([$id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                echo Response::success($employee);
            } else {
                echo Response::error('Employee not found');
            }
            break;

        case 'createEmployee':
            $input = json_decode(file_get_contents('php://input'), true);
            $code = $input['employee_code'] ?? '';
            $first_name = $input['first_name'] ?? '';
            $last_name = $input['last_name'] ?? '';
            $email = $input['email'] ?? '';
            $phone = $input['phone'] ?? '';
            $position = $input['position'] ?? '';
            $hire_date = $input['hire_date'] ?? '';

            $query = "INSERT INTO employees (code, first_name, last_name, email, phone, position, hire_date, employment_status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$code, $first_name, $last_name, $email, $phone, $position, $hire_date])) {
                echo Response::success(['id' => $db->lastInsertId()], 'Employee created successfully');
            } else {
                echo Response::error('Failed to create employee');
            }
            break;

        case 'updateEmployee':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? $input['employee_id'] ?? 0);
            $first_name = $input['first_name'] ?? '';
            $last_name = $input['last_name'] ?? '';
            $email = $input['email'] ?? '';

            $query = "UPDATE employees SET first_name=?, last_name=?, email=? WHERE employee_id=?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$first_name, $last_name, $email, $id])) {
                echo Response::success(null, 'Employee updated successfully');
            } else {
                echo Response::error('Failed to update employee');
            }
            break;

        case 'deleteEmployee':
            $id = intval($_GET['id'] ?? $_GET['employee_id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM employees WHERE employee_id = ?");
            if ($stmt->execute([$id])) {
                echo Response::success(null, 'Employee deleted successfully');
            } else {
                echo Response::error('Failed to delete employee');
            }
            break;

        // DOCUMENTS
        case 'getDocuments':
            $query = "SELECT d.*, d.document_id as id, e.first_name, e.last_name, 
                      CONCAT(e.first_name, ' ', e.last_name) as employee_name FROM employee_documents d 
                     LEFT JOIN employees e ON d.employee_id = e.employee_id ORDER BY d.created_at DESC";
            
            $result = $db->query($query);
            $documents = $result->fetchAll(PDO::FETCH_ASSOC);

            // Calculate stats
            $expired = 0;
            $expiring = 0;
            $valid = 0;
            $expiring_soon = [];
            $today_time = time();
            
            foreach ($documents as $doc) {
                if ($doc['expiry_date']) {
                    $exp_date = strtotime($doc['expiry_date']);
                    $diff_days = ceil(($exp_date - $today_time) / (60 * 60 * 24));
                    
                    if ($diff_days < 0) {
                        $expired++;
                    } elseif ($diff_days <= 30) {
                        $expiring++;
                        $expiring_soon[] = $doc;
                    } else {
                        $valid++;
                    }
                } else {
                    $valid++;
                }
            }

            $stats = [
                'total' => count($documents),
                'expired' => $expired,
                'expiring' => $expiring,
                'valid' => $valid
            ];

            echo Response::success([
                'documents' => $documents,
                'stats' => $stats,
                'expiring_soon' => array_slice($expiring_soon, 0, 5)
            ]);
            break;

        case 'getDocumentById':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT d.*, d.document_id as id FROM employee_documents d WHERE d.document_id = ?");
            $stmt->execute([$id]);
            $document = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($document) {
                echo Response::success($document);
            } else {
                echo Response::error('Document not found');
            }
            break;

        case 'createDocument':
            $input = json_decode(file_get_contents('php://input'), true);
            $employee_id = intval($input['employee_id'] ?? 0);
            $document_type = $input['document_type'] ?? '';
            $upload_date = $input['upload_date'] ?? date('Y-m-d');
            $expiry_date = $input['expiry_date'] ?? null;
            $notes = $input['notes'] ?? '';
            $status = $input['status'] ?? 'valid';

            $query = "INSERT INTO employee_documents (employee_id, document_type, upload_date, expiry_date, notes, status, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$employee_id, $document_type, $upload_date, $expiry_date, $notes, $status])) {
                echo Response::success(['id' => $db->lastInsertId()], 'Document created successfully');
            } else {
                echo Response::error('Failed to create document');
            }
            break;

        case 'updateDocument':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['document_id'] ?? 0);
            $document_type = $input['document_type'] ?? '';
            $expiry_date = $input['expiry_date'] ?? null;
            $remarks = $input['remarks'] ?? '';

            $query = "UPDATE employee_documents SET document_type=?, expiry_date=?, remarks=?, updated_at=NOW() WHERE document_id=?";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$document_type, $expiry_date, $remarks, $id])) {
                echo Response::success(null, 'Document updated successfully');
            } else {
                echo Response::error('Failed to update document');
            }
            break;

        case 'deleteDocument':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM employee_documents WHERE document_id = ?");
            if ($stmt->execute([$id])) {
                echo Response::success(null, 'Document deleted successfully');
            } else {
                echo Response::error('Failed to delete document');
            }
            break;

        // MOVEMENTS
        case 'getMovements':
            $query = "SELECT m.*, m.movement_id as id, e.first_name, e.last_name, e.employee_id,
                      CONCAT(e.first_name, ' ', e.last_name) as employee_name FROM employee_movements m 
                     LEFT JOIN employees e ON m.employee_id = e.employee_id";
            
            $result = $db->query($query);
            $movements = $result->fetchAll(PDO::FETCH_ASSOC);

            $statsQuery = "SELECT 
                          COUNT(*) as total,
                          SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                          SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                          SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
                          FROM employee_movements";
            $statsResult = $db->query($statsQuery);
            $stats = $statsResult->fetch(PDO::FETCH_ASSOC);

            echo Response::success([
                'movements' => $movements,
                'stats' => $stats
            ]);
            break;

        case 'getMovementById':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT m.*, m.movement_id as id FROM employee_movements m WHERE m.movement_id = ?");
            $stmt->execute([$id]);
            $movement = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($movement) {
                echo Response::success($movement);
            } else {
                echo Response::error('Movement not found');
            }
            break;

        case 'createMovement':
            $input = json_decode(file_get_contents('php://input'), true);
            $employee_id = intval($input['employee_id'] ?? 0);
            $movement_type = $input['movement_type'] ?? '';
            $effective_date = $input['effective_date'] ?? date('Y-m-d');
            $reason = $input['reason'] ?? '';
            $status = $input['status'] ?? 'Pending';

            $query = "INSERT INTO employee_movements (employee_id, movement_type, effective_date, reason, status) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$employee_id, $movement_type, $effective_date, $reason, $status])) {
                echo Response::success(['id' => $db->lastInsertId()], 'Movement created successfully');
            } else {
                echo Response::error('Failed to create movement');
            }
            break;

        case 'updateMovement':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $movement_type = $input['movement_type'] ?? '';
            $effective_date = $input['effective_date'] ?? '';
            $reason = $input['reason'] ?? '';
            $status = $input['status'] ?? '';

            $query = "UPDATE employee_movements SET movement_type=?, effective_date=?, reason=?, status=? WHERE movement_id=?";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$movement_type, $effective_date, $reason, $status, $id])) {
                echo Response::success(null, 'Movement updated successfully');
            } else {
                echo Response::error('Failed to update movement');
            }
            break;

        case 'deleteMovement':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM employee_movements WHERE movement_id = ?");
            if ($stmt->execute([$id])) {
                echo Response::success(null, 'Movement deleted successfully');
            } else {
                echo Response::error('Failed to delete movement');
            }
            break;

        // DEPARTMENTS
        case 'getDepartments':
            $query = "SELECT d.*, 
                      d.department_id as id, 
                      d.department_name as name,
                      CONCAT(e.first_name, ' ', e.last_name) as head_name,
                      COUNT(DISTINCT emp.employee_id) as staff_count
                      FROM departments d
                      LEFT JOIN employees e ON d.head_id = e.employee_id
                      LEFT JOIN employees emp ON d.department_id = emp.department_id AND emp.employment_status = 'Active'
                      GROUP BY d.department_id
                      ORDER BY d.department_name ASC";
            
            $result = $db->query($query);
            $departments = $result->fetchAll(PDO::FETCH_ASSOC);

            $statsQuery = "SELECT 
                          COUNT(*) as total,
                          SUM(CASE WHEN d.head_id IS NOT NULL THEN 1 ELSE 0 END) as active,
                          SUM(CASE WHEN d.head_id IS NULL THEN 1 ELSE 0 END) as no_manager,
                          COUNT(DISTINCT emp.employee_id) as total_staff
                          FROM departments d
                          LEFT JOIN employees emp ON d.department_id = emp.department_id AND emp.employment_status = 'Active'";
            $statsResult = $db->query($statsQuery);
            $stats = $statsResult->fetch(PDO::FETCH_ASSOC);

            // Get departments with no manager
            $noManagerQuery = "SELECT d.*, 
                            d.department_id as id, 
                            d.department_name as name,
                            COUNT(DISTINCT emp.employee_id) as staff_count
                            FROM departments d
                            LEFT JOIN employees emp ON d.department_id = emp.department_id AND emp.employment_status = 'Active'
                            WHERE d.head_id IS NULL
                            GROUP BY d.department_id
                            ORDER BY d.department_name ASC
                            LIMIT 5";
            $noManagerResult = $db->query($noManagerQuery);
            $no_manager_depts = $noManagerResult->fetchAll(PDO::FETCH_ASSOC);

            // Get departments with low staffing (less than 2 active staff)
            $lowStaffQuery = "SELECT d.*, 
                           d.department_id as id, 
                           d.department_name as name,
                           COUNT(DISTINCT emp.employee_id) as staff_count
                           FROM departments d
                           LEFT JOIN employees emp ON d.department_id = emp.department_id AND emp.employment_status = 'Active'
                           GROUP BY d.department_id
                           HAVING COUNT(DISTINCT emp.employee_id) < 2
                           ORDER BY staff_count ASC
                           LIMIT 5";
            $lowStaffResult = $db->query($lowStaffQuery);
            $low_staffing = $lowStaffResult->fetchAll(PDO::FETCH_ASSOC);

            // Get recently created departments
            $recentQuery = "SELECT d.*, 
                          d.department_id as id, 
                          d.department_name as name,
                          CONCAT(e.first_name, ' ', e.last_name) as head_name,
                          COUNT(DISTINCT emp.employee_id) as staff_count
                          FROM departments d
                          LEFT JOIN employees e ON d.head_id = e.employee_id
                          LEFT JOIN employees emp ON d.department_id = emp.department_id AND emp.employment_status = 'Active'
                          GROUP BY d.department_id
                          ORDER BY d.created_at DESC
                          LIMIT 5";
            $recentResult = $db->query($recentQuery);
            $recent_depts = $recentResult->fetchAll(PDO::FETCH_ASSOC);

            echo Response::success([
                'departments' => $departments,
                'stats' => $stats,
                'no_manager_depts' => $no_manager_depts,
                'low_staffing' => $low_staffing,
                'recent_depts' => $recent_depts
            ]);
            break;

        case 'getDepartmentById':
            $id = intval($_GET['id'] ?? $_GET['department_id'] ?? 0);
            $stmt = $db->prepare("SELECT d.*, d.department_id as id, d.department_name as name FROM departments d WHERE d.department_id = ?");
            $stmt->execute([$id]);
            $department = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($department) {
                echo Response::success($department);
            } else {
                echo Response::error('Department not found');
            }
            break;

        case 'createDepartment':
            $input = json_decode(file_get_contents('php://input'), true);
            $name = $input['name'] ?? '';
            $code = $input['code'] ?? '';
            $description = $input['description'] ?? '';
            $manager_id = intval($input['manager_id'] ?? 0);

            // Align with departments schema used in SELECTs above (department_name/head_id)
            $query = "INSERT INTO departments (department_name, code, description, head_id, created_at) 
                     VALUES (?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$name, $code, $description, $manager_id > 0 ? $manager_id : null])) {
                echo Response::success(['id' => $db->lastInsertId()], 'Department created successfully');
            } else {
                echo Response::error('Failed to create department');
            }
            break;

        case 'updateDepartment':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $name = $input['name'] ?? '';
            $code = $input['code'] ?? '';
            $description = $input['description'] ?? '';
            $manager_id = intval($input['manager_id'] ?? 0);

            $query = "UPDATE departments 
                      SET department_name = ?, code = ?, description = ?, head_id = ? 
                      WHERE department_id = ?";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$name, $code, $description, $manager_id > 0 ? $manager_id : null, $id])) {
                echo Response::success(null, 'Department updated successfully');
            } else {
                echo Response::error('Failed to update department');
            }
            break;

        case 'deleteDepartment':
            $id = intval($_GET['id'] ?? 0);
            // Hard delete is potentially dangerous; for now, keep behavior but target correct PK
            $stmt = $db->prepare("DELETE FROM departments WHERE department_id = ?");
            if ($stmt->execute([$id])) {
                echo Response::success(null, 'Department deleted successfully');
            } else {
                echo Response::error('Failed to delete department');
            }
            break;

        // ONBOARDING
        case 'getOnboarding':
            $query = "SELECT o.*, o.onboarding_checklist_id as id, e.first_name, e.last_name, e.employee_id, e.department_id,
                      CONCAT(e.first_name, ' ', e.last_name) as employee_name FROM onboarding_checklists o 
                     LEFT JOIN employees e ON o.employee_id = e.employee_id
                     ORDER BY o.due_date DESC";
            
            $result = $db->query($query);
            $onboarding = $result->fetchAll(PDO::FETCH_ASSOC);

            // Get comprehensive stats
            $statsQuery = "SELECT 
                          COUNT(*) as total,
                          SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                          SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                          SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                          SUM(CASE WHEN DATE(due_date) < CURDATE() AND status != 'Completed' THEN 1 ELSE 0 END) as overdue
                          FROM onboarding_checklists";
            $statsResult = $db->query($statsQuery);
            $stats = $statsResult->fetch(PDO::FETCH_ASSOC);

            // Get employees starting this week
            $startingQuery = "SELECT o.onboarding_checklist_id as id, o.created_at as start_date, CONCAT(e.first_name, ' ', e.last_name) as employee_name 
                            FROM onboarding_checklists o 
                            LEFT JOIN employees e ON o.employee_id = e.employee_id
                            WHERE YEARWEEK(o.created_at) = YEARWEEK(CURDATE())
                            ORDER BY o.created_at ASC
                            LIMIT 5";
            $startingResult = $db->query($startingQuery);
            $starting_this_week = $startingResult->fetchAll(PDO::FETCH_ASSOC);

            // Get missing requirements (in progress, no due date, or overdue)
            $missingQuery = "SELECT o.onboarding_checklist_id as id, o.due_date, o.status, 
                            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                            o.responsible_department,
                            o.checklist_item
                            FROM onboarding_checklists o 
                            LEFT JOIN employees e ON o.employee_id = e.employee_id
                            WHERE o.status != 'Completed' AND (o.due_date IS NULL OR DATE(o.due_date) < CURDATE())
                            ORDER BY o.due_date ASC
                            LIMIT 5";
            $missingResult = $db->query($missingQuery);
            $missing_requirements = $missingResult->fetchAll(PDO::FETCH_ASSOC);

            // Get recently completed
            $recentlyQuery = "SELECT o.onboarding_checklist_id as id, o.updated_at as completed_date,
                            CONCAT(e.first_name, ' ', e.last_name) as employee_name
                            FROM onboarding_checklists o 
                            LEFT JOIN employees e ON o.employee_id = e.employee_id
                            WHERE o.status = 'Completed' AND o.updated_at IS NOT NULL
                            ORDER BY o.updated_at DESC
                            LIMIT 5";
            $recentlyResult = $db->query($recentlyQuery);
            $recently_completed = $recentlyResult->fetchAll(PDO::FETCH_ASSOC);

            echo Response::success([
                'onboarding' => $onboarding,
                'stats' => $stats,
                'starting_this_week' => $starting_this_week,
                'missing_requirements' => $missing_requirements,
                'recently_completed' => $recently_completed
            ]);
            break;

        case 'getOnboardingById':
            $id = intval($_GET['id'] ?? $_GET['onboarding_checklist_id'] ?? 0);
            $stmt = $db->prepare("SELECT o.*, o.onboarding_checklist_id as id FROM onboarding_checklists o WHERE o.onboarding_checklist_id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                echo Response::success($record);
            } else {
                echo Response::error('Onboarding record not found');
            }
            break;

        case 'createOnboarding':
            $input = json_decode(file_get_contents('php://input'), true);
            $employee_id = intval($input['employee_id'] ?? 0);
            $start_date = $input['start_date'] ?? date('Y-m-d');
            $status = $input['status'] ?? 'In Progress';
            $notes = $input['notes'] ?? '';

            $query = "INSERT INTO onboarding_checklists (employee_id, start_date, status, notes) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$employee_id, $start_date, $status, $notes])) {
                echo Response::success(['id' => $db->lastInsertId()], 'Onboarding record created successfully');
            } else {
                echo Response::error('Failed to create onboarding record');
            }
            break;

        case 'updateOnboarding':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = intval($input['id'] ?? 0);
            $status = $input['status'] ?? '';
            $notes = $input['notes'] ?? '';

            $query = "UPDATE onboarding_checklists SET status=?, remarks=? WHERE onboarding_checklist_id=?";
            $stmt = $db->prepare($query);

            if ($stmt->execute([$status, $notes, $id])) {
                echo Response::success(null, 'Onboarding record updated successfully');
            } else {
                echo Response::error('Failed to update onboarding record');
            }
            break;

        case 'deleteOnboarding':
            $id = intval($_GET['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM onboarding_checklists WHERE onboarding_checklist_id = ?");
            if ($stmt->execute([$id])) {
                echo Response::success(null, 'Onboarding record deleted successfully');
            } else {
                echo Response::error('Failed to delete onboarding record');
            }
            break;

        // MASTER DATA
        case 'getJobTitles':
            $result = $db->query("SELECT * FROM job_titles");
            $titles = $result->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success(['job_titles' => $titles]);
            break;

        case 'getEmploymentTypes':
            // Return employment types with basic derived fields for frontend consumption
            $sql = "SELECT et.*, COUNT(e.employee_id) as employee_count
                    FROM employment_types et
                    LEFT JOIN employees e ON et.employment_type_id = e.employment_type_id
                    GROUP BY et.employment_type_id
                    ORDER BY et.type_name ASC";
            $result = $db->query($sql);
            $rows = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];

            $types = [];
            foreach ($rows as $row) {
                // Derive duration_type/duration_months from contract_duration_months when available
                $durationType = 'permanent';
                $durationMonths = null;
                if (!empty($row['contract_duration_months'])) {
                    $m = (int) $row['contract_duration_months'];
                    $durationMonths = $m;
                    if ($m === 3) {
                        $durationType = 'fixed_3m';
                    } elseif ($m === 6) {
                        $durationType = 'fixed_6m';
                    } elseif ($m === 12) {
                        $durationType = 'fixed_12m';
                    } else {
                        $durationType = 'custom';
                    }
                }

                $row['duration_type'] = $durationType;
                $row['duration_months'] = $durationMonths;

                $types[] = $row;
            }

            echo Response::success(['employment_types' => $types]);
            break;

        case 'getEmploymentTypeById':
            $id = intval($_GET['id'] ?? $_GET['employment_type_id'] ?? 0);
            if ($id <= 0) {
                echo Response::error('Employment type ID is required');
                break;
            }

            $stmt = $db->prepare("SELECT * FROM employment_types WHERE employment_type_id = ?");
            $stmt->execute([$id]);
            $type = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($type) {
                echo Response::success($type);
            } else {
                echo Response::error('Employment type not found');
            }
            break;

        case 'createEmploymentType':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $typeName = trim($input['type_name'] ?? '');

            if ($typeName === '') {
                echo Response::error('Type name is required');
                break;
            }

            $description = $input['description'] ?? null;

            // Map duration into contract_duration_months when provided
            $durationType = $input['duration_type'] ?? 'permanent';
            $durationMonths = isset($input['duration_months']) ? (int) $input['duration_months'] : null;
            $contractMonths = null;
            if ($durationType === 'fixed_3m') {
                $contractMonths = 3;
            } elseif ($durationType === 'fixed_6m') {
                $contractMonths = 6;
            } elseif ($durationType === 'fixed_12m') {
                $contractMonths = 12;
            } elseif ($durationType === 'custom' && $durationMonths > 0) {
                $contractMonths = $durationMonths;
            }

            $benefitsEligible = $input['benefits_eligible'] ?? 'no';
            $status = strtolower($input['status'] ?? 'active') === 'inactive' ? 'Inactive' : 'Active';

            $stmt = $db->prepare("INSERT INTO employment_types 
                (type_name, description, probation_period_days, contract_duration_months, benefits_eligible, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");

            $probationDays = null; // can be extended later

            if ($stmt->execute([$typeName, $description, $probationDays, $contractMonths, $benefitsEligible, $status])) {
                echo Response::success(['id' => $db->lastInsertId()], 'Employment type created successfully');
            } else {
                echo Response::error('Failed to create employment type');
            }
            break;

        case 'updateEmploymentType':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = intval($input['id'] ?? $input['employment_type_id'] ?? 0);
            if ($id <= 0) {
                echo Response::error('Employment type ID is required');
                break;
            }

            // Load existing to avoid wiping fields when only status is updated
            $stmt = $db->prepare("SELECT * FROM employment_types WHERE employment_type_id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                echo Response::error('Employment type not found');
                break;
            }

            $typeName = trim($input['type_name'] ?? $existing['type_name']);
            $description = array_key_exists('description', $input) ? $input['description'] : $existing['description'];

            // Duration mapping
            $durationType = $input['duration_type'] ?? null;
            $durationMonths = isset($input['duration_months']) ? (int) $input['duration_months'] : null;
            $contractMonths = $existing['contract_duration_months'];

            if ($durationType !== null) {
                if ($durationType === 'permanent') {
                    $contractMonths = null;
                } elseif ($durationType === 'fixed_3m') {
                    $contractMonths = 3;
                } elseif ($durationType === 'fixed_6m') {
                    $contractMonths = 6;
                } elseif ($durationType === 'fixed_12m') {
                    $contractMonths = 12;
                } elseif ($durationType === 'custom' && $durationMonths > 0) {
                    $contractMonths = $durationMonths;
                }
            }

            $benefitsEligible = $input['benefits_eligible'] ?? $existing['benefits_eligible'];
            $statusInput = $input['status'] ?? $existing['status'];
            $status = strtolower($statusInput) === 'inactive' ? 'Inactive' : 'Active';

            $probationDays = $existing['probation_period_days'];

            $updateSql = "UPDATE employment_types 
                          SET type_name = ?, description = ?, probation_period_days = ?, 
                              contract_duration_months = ?, benefits_eligible = ?, status = ?, updated_at = NOW()
                          WHERE employment_type_id = ?";

            $updateStmt = $db->prepare($updateSql);
            if ($updateStmt->execute([$typeName, $description, $probationDays, $contractMonths, $benefitsEligible, $status, $id])) {
                echo Response::success(null, 'Employment type updated successfully');
            } else {
                echo Response::error('Failed to update employment type');
            }
            break;

        case 'getLocations':
            $result = $db->query("SELECT * FROM locations");
            $locations = $result->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate stats
            $total = count($locations);
            $active = count(array_filter($locations, fn($l) => ($l['status'] ?? 'active') === 'active'));
            $staff_assigned = 0;
            $no_staff = 0;
            
            // Count staff in each location
            foreach ($locations as $loc) {
                $locId = $loc['location_id'];
                $staffQuery = $db->prepare("SELECT COUNT(*) as count FROM employees WHERE location_id = ?");
                $staffQuery->execute([$locId]);
                $staffCount = $staffQuery->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                if ($staffCount > 0) {
                    $staff_assigned++;
                } else {
                    $no_staff++;
                }
            }
            
            echo Response::success([
                'locations' => $locations,
                'stats' => [
                    'total' => $total,
                    'active' => $active,
                    'staff_assigned' => $staff_assigned,
                    'no_staff' => $no_staff
                ]
            ]);
            break;

        case 'getRoles':
            $result = $db->query("SELECT * FROM roles");
            $roles = $result->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate stats
            $total = count($roles);
            $highPrivilege = count(array_filter($roles, fn($r) => in_array(strtolower($r['role_category'] ?? ''), ['system', 'management'])));
            $inUse = count(array_filter($roles, fn($r) => ($r['status'] ?? 'active') === 'active'));
            $disabled = count(array_filter($roles, fn($r) => ($r['status'] ?? 'active') !== 'active'));
            
            // Get high privilege roles
            $highPrivilegeRoles = array_filter($roles, fn($r) => in_array(strtolower($r['role_category'] ?? ''), ['system', 'management']));
            
            // Get unused roles (not assigned to any users - based on user_count)
            $unusedRoles = array_filter($roles, fn($r) => ($r['user_count'] ?? 0) == 0);
            
            // Get recently modified
            $recentlyModified = array_slice(array_reverse($roles), 0, 5);
            
            echo Response::success([
                'roles' => $roles,
                'stats' => [
                    'total' => $total,
                    'high_privilege' => $highPrivilege,
                    'in_use' => $inUse,
                    'disabled' => $disabled
                ],
                'high_privilege_roles' => array_values($highPrivilegeRoles),
                'unused_roles' => array_values($unusedRoles),
                'recently_modified' => array_values($recentlyModified)
            ]);
            break;

        case 'get_shifts_stats':
            // Load Shift model
            require_once __DIR__ . '/../../config/BaseModel.php';
            require_once __DIR__ . '/models/Shift.php';
            $shiftModel = new Shift();
            
            $stats = $shiftModel->getShiftStats();
            $shifts = $shiftModel->getAll(true);
            
            // Return data structure expected by shifts.php
            echo json_encode([
                'success' => true,
                'message' => 'Shifts loaded successfully',
                'data' => $shifts,
                'stats' => $stats,
                'error' => null
            ]);
            break;

        case 'get_shift_panels':
            // Load Shift model
            require_once __DIR__ . '/../../config/BaseModel.php';
            require_once __DIR__ . '/models/Shift.php';
            $shiftModel = new Shift();
            
            $nightShifts = $shiftModel->getNightShifts();
            $unassignedShifts = $shiftModel->getUnassignedShifts();
            $recentlyModified = $shiftModel->getRecentlyModified(5);
            
            // Return data structure expected by shifts.php
            echo json_encode([
                'success' => true,
                'message' => 'Shift panels loaded successfully',
                'night_shifts' => $nightShifts,
                'unassigned_shifts' => $unassignedShifts,
                'recently_modified' => $recentlyModified,
                'error' => null
            ]);
            break;

        case 'create_shift':
            $data = json_decode(file_get_contents('php://input'), true);
            
            $query = "INSERT INTO shifts (shift_name, shift_type, start_time, end_time, break_duration, 
                     required_staff_count, night_differential_eligible, overtime_eligible, is_night_shift, 
                     description, status, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW(), NOW())";
            
            $stmt = $db->prepare($query);
            if ($stmt->execute([
                $data['shift_name'] ?? '',
                $data['shift_type'] ?? 'custom',
                $data['start_time'] ?? '',
                $data['end_time'] ?? '',
                $data['break_duration'] ?? 0,
                $data['required_count'] ?? 0,
                isset($data['night_differential_eligible']) && $data['night_differential_eligible'] ? 1 : 0,
                isset($data['overtime_eligible']) && $data['overtime_eligible'] ? 1 : 0,
                isset($data['is_night_shift']) && $data['is_night_shift'] ? 1 : 0,
                $data['description'] ?? ''
            ])) {
                echo Response::success(['id' => $db->lastInsertId()], 'Shift created successfully');
            } else {
                echo Response::error('Failed to create shift');
            }
            break;

        case 'update_shift':
            $data = json_decode(file_get_contents('php://input'), true);
            $shiftId = $data['shift_id'] ?? 0;
            
            if (!$shiftId) {
                echo Response::error('Shift ID required');
                break;
            }
            
            $query = "UPDATE shifts SET shift_name = ?, shift_type = ?, start_time = ?, end_time = ?, 
                     break_duration = ?, required_staff_count = ?, night_differential_eligible = ?, 
                     overtime_eligible = ?, is_night_shift = ?, description = ?, updated_at = NOW() 
                     WHERE id = ?";
            
            $stmt = $db->prepare($query);
            if ($stmt->execute([
                $data['shift_name'] ?? '',
                $data['shift_type'] ?? 'custom',
                $data['start_time'] ?? '',
                $data['end_time'] ?? '',
                $data['break_duration'] ?? 0,
                $data['required_count'] ?? 0,
                isset($data['night_differential_eligible']) && $data['night_differential_eligible'] ? 1 : 0,
                isset($data['overtime_eligible']) && $data['overtime_eligible'] ? 1 : 0,
                isset($data['is_night_shift']) && $data['is_night_shift'] ? 1 : 0,
                $data['description'] ?? '',
                $shiftId
            ])) {
                echo Response::success(null, 'Shift updated successfully');
            } else {
                echo Response::error('Failed to update shift');
            }
            break;

        case 'delete_shift':
            $data = json_decode(file_get_contents('php://input'), true);
            $shiftId = $data['shift_id'] ?? 0;
            
            if (!$shiftId) {
                echo Response::error('Shift ID required');
                break;
            }
            
            // Check if shift has employee assignments
            $checkQuery = "SELECT COUNT(*) as count FROM employee_shifts WHERE shift_id = ? AND status = 'Active'";
            $stmt = $db->prepare($checkQuery);
            $stmt->execute([$shiftId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                echo Response::error('Cannot delete: Shift has active employee assignments. Deactivate instead.');
                break;
            }
            
            $query = "DELETE FROM shifts WHERE id = ?";
            $stmt = $db->prepare($query);
            if ($stmt->execute([$shiftId])) {
                echo Response::success(null, 'Shift deleted successfully');
            } else {
                echo Response::error('Failed to delete shift');
            }
            break;

        case 'getShifts':
            $result = $db->query("SELECT * FROM shifts");
            $shifts = $result->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success(['shifts' => $shifts]);
            break;

        case 'getSchedules':
            // Get schedules with stats
            $result = $db->query("
                SELECT ws.*, 
                       COALESCE(COUNT(DISTINCT ea.employee_id), 0) as assigned_count,
                       COALESCE(MAX(COALESCE(ws.required_count, 1)), 1) as required_count
                FROM work_schedules ws
                LEFT JOIN employee_assignments ea ON ws.id = ea.schedule_id AND ea.status = 'Active'
                WHERE ws.status = 'Active'
                GROUP BY ws.id
                ORDER BY ws.schedule_name ASC
            ");
            $schedules = $result->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($schedules);
            break;

        case 'get_schedules_stats':
            // Get schedule statistics
            $stats = $db->query("
                SELECT 
                    COALESCE(COUNT(CASE WHEN status = 'Active' THEN 1 END), 0) as active_count,
                    COALESCE(COUNT(CASE WHEN has_night_shift = 1 AND status = 'Active' THEN 1 END), 0) as night_schedule_count,
                    COALESCE(SUM(COALESCE((SELECT COUNT(*) FROM employee_assignments ea WHERE ea.schedule_id = ws.schedule_id AND ea.status = 'Active'), 0)), 0) as total_assigned,
                    COALESCE(COUNT(CASE WHEN schedule_id NOT IN (SELECT DISTINCT schedule_id FROM employee_assignments WHERE status = 'Active') AND status = 'Active' THEN 1 END), 0) as unassigned_count
                FROM work_schedules ws
            ")->fetch(PDO::FETCH_ASSOC);

            // Get all schedules with stats
            $schedules_result = $db->query("
                SELECT ws.*, 
                       COALESCE(COUNT(DISTINCT ea.employee_id), 0) as assigned_count,
                       COALESCE(MAX(COALESCE(ws.required_count, 1)), 1) as required_count
                FROM work_schedules ws
                LEFT JOIN employee_assignments ea ON ws.schedule_id = ea.schedule_id AND ea.status = 'Active'
                WHERE ws.status = 'Active'
                GROUP BY ws.schedule_id
                ORDER BY ws.schedule_name ASC
            ");
            $schedules = $schedules_result->fetchAll(PDO::FETCH_ASSOC);

            // Return data structure expected by schedules.php
            echo json_encode([
                'success' => true,
                'message' => 'Schedules loaded successfully',
                'data' => $schedules,
                'stats' => $stats,
                'error' => null
            ]);
            break;

        case 'get_schedule_panels':
            // Get data for right sidebar panels
            $unassigned = $db->query("
                SELECT ws.* FROM work_schedules ws
                WHERE ws.status = 'Active'
                AND ws.schedule_id NOT IN (SELECT DISTINCT schedule_id FROM employee_assignments WHERE status = 'Active')
                ORDER BY ws.schedule_name ASC LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            $night_schedules = $db->query("
                SELECT ws.* FROM work_schedules ws
                WHERE ws.status = 'Active' AND ws.has_night_shift = 1
                ORDER BY ws.schedule_name ASC LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            $recently_modified = $db->query("
                SELECT ws.* FROM work_schedules ws
                WHERE ws.status = 'Active'
                ORDER BY ws.updated_at DESC LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'unassigned_schedules' => $unassigned,
                'night_schedules' => $night_schedules,
                'recently_modified' => $recently_modified
            ]);
            break;

        case 'create_schedule':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("
                INSERT INTO work_schedules 
                (schedule_name, schedule_type, shift_pattern, hours_per_day, work_days_per_week, 
                 weekly_hours, has_night_shift, is_trainee, description, status, start_date, end_date, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?, ?, NOW(), NOW())
            ");
            
            $weekly_hours = ($input['hours_per_day'] ?? 0) * ($input['work_days_per_week'] ?? 0);
            $success = $stmt->execute([
                $input['schedule_name'] ?? '',
                $input['schedule_type'] ?? '',
                $input['shift_pattern'] ?? '',
                $input['hours_per_day'] ?? 0,
                $input['work_days_per_week'] ?? 0,
                $weekly_hours,
                $input['has_night_shift'] ? 1 : 0,
                $input['is_trainee'] ? 1 : 0,
                $input['description'] ?? '',
                $input['start_date'] ?? null,
                $input['end_date'] ?? null
            ]);

            echo json_encode(['success' => $success, 'message' => $success ? 'Schedule created' : 'Failed to create']);
            break;

        case 'update_schedule':
            $input = json_decode(file_get_contents('php://input'), true);
            $stmt = $db->prepare("
                UPDATE work_schedules 
                SET schedule_name = ?, schedule_type = ?, shift_pattern = ?, hours_per_day = ?, 
                    work_days_per_week = ?, weekly_hours = ?, has_night_shift = ?, is_trainee = ?, 
                    description = ?, status = ?, start_date = ?, end_date = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $weekly_hours = ($input['hours_per_day'] ?? 0) * ($input['work_days_per_week'] ?? 0);
            $success = $stmt->execute([
                $input['schedule_name'] ?? '',
                $input['schedule_type'] ?? '',
                $input['shift_pattern'] ?? '',
                $input['hours_per_day'] ?? 0,
                $input['work_days_per_week'] ?? 0,
                $weekly_hours,
                $input['has_night_shift'] ? 1 : 0,
                $input['is_trainee'] ? 1 : 0,
                $input['description'] ?? '',
                $input['is_active'] ? 'Active' : 'Inactive',
                $input['start_date'] ?? null,
                $input['end_date'] ?? null,
                $input['schedule_id'] ?? 0
            ]);

            echo json_encode(['success' => $success, 'message' => $success ? 'Schedule updated' : 'Failed to update']);
            break;

        case 'delete_schedule':
            $input = json_decode(file_get_contents('php://input'), true);
            $schedule_id = $input['schedule_id'] ?? 0;
            
            // Check if has assignments
            $check = $db->prepare("SELECT COUNT(*) as cnt FROM employee_assignments WHERE schedule_id = ? AND status = 'Active'");
            $check->execute([$schedule_id]);
            $result = $check->fetch(PDO::FETCH_ASSOC);
            
            if ($result['cnt'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete: schedule has active assignments']);
                break;
            }
            
            $stmt = $db->prepare("DELETE FROM work_schedules WHERE id = ?");
            $success = $stmt->execute([$schedule_id]);
            echo json_encode(['success' => $success, 'message' => $success ? 'Schedule deleted' : 'Failed to delete']);
            break;

        // DOCUMENT REQUIREMENTS
        case 'getDocumentCategories':
            $stmt = $db->prepare("
                SELECT * FROM document_categories
                ORDER BY sort_order ASC
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($categories, 'Document categories retrieved');
            break;

        case 'getDocumentRequirements':
            $categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : null;
            
            if ($categoryId) {
                $stmt = $db->prepare("
                    SELECT dr.*, dc.category_name
                    FROM document_requirements dr
                    INNER JOIN document_categories dc ON dr.category_id = dc.category_id
                    WHERE dr.category_id = ?
                    ORDER BY dr.document_type ASC
                ");
                $stmt->execute([$categoryId]);
            } else {
                $stmt = $db->prepare("
                    SELECT dr.*, dc.category_name, dc.sort_order
                    FROM document_requirements dr
                    INNER JOIN document_categories dc ON dr.category_id = dc.category_id
                    ORDER BY dc.sort_order ASC, dr.document_type ASC
                ");
                $stmt->execute();
            }
            
            $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($requirements, 'Document requirements retrieved');
            break;

        case 'getEmployeeDocumentChecklist':
            $employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : 0;
            
            if (!$employeeId) {
                echo Response::error('Employee ID required');
                break;
            }
            
            // Get the full checklist with all details
            $stmt = $db->prepare("
                SELECT 
                    edc.*,
                    dr.document_type,
                    dr.document_description,
                    dr.is_mandatory,
                    dr.requires_expiry_date,
                    dr.expiry_alert_days,
                    dc.category_name,
                    dc.sort_order,
                    ed.file_path,
                    ed.expiry_date as document_expiry_date,
                    CONCAT(e.first_name, ' ', e.last_name) as verified_by_name
                FROM employee_document_checklist edc
                INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
                INNER JOIN document_categories dc ON dr.category_id = dc.category_id
                LEFT JOIN employee_documents ed ON edc.document_id = ed.document_id
                LEFT JOIN employees e ON edc.verified_by = e.employee_id
                WHERE edc.employee_id = ?
                ORDER BY dc.sort_order ASC, dr.document_type ASC
            ");
            $stmt->execute([$employeeId]);
            $checklist = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get summary stats
            $sumStmt = $db->prepare("
                SELECT 
                    COUNT(*) as total_required,
                    SUM(CASE WHEN status = 'Verified' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
                    SUM(CASE WHEN status = 'Not Submitted' THEN 1 ELSE 0 END) as not_submitted,
                    SUM(CASE WHEN status = 'Expired' THEN 1 ELSE 0 END) as expired,
                    SUM(CASE WHEN status = 'Pending Renewal' THEN 1 ELSE 0 END) as pending_renewal,
                    ROUND((SUM(CASE WHEN status IN ('Verified', 'Submitted') THEN 1 ELSE 0 END) / COUNT(*) * 100), 2) as completion_percentage
                FROM employee_document_checklist
                WHERE employee_id = ?
            ");
            $sumStmt->execute([$employeeId]);
            $summary = $sumStmt->fetch(PDO::FETCH_ASSOC);
            
            echo Response::success([
                'checklist' => $checklist,
                'summary' => $summary
            ], 'Employee document checklist retrieved');
            break;

        case 'getDocumentChecklistByCategory':
            $employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : 0;
            $categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : 0;
            
            if (!$employeeId || !$categoryId) {
                echo Response::error('Employee ID and Category ID required');
                break;
            }
            
            $stmt = $db->prepare("
                SELECT 
                    edc.*,
                    dr.document_type,
                    dr.document_description,
                    dr.is_mandatory,
                    dc.category_name,
                    ed.file_path,
                    ed.expiry_date as document_expiry_date
                FROM employee_document_checklist edc
                INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
                INNER JOIN document_categories dc ON dr.category_id = dc.category_id
                LEFT JOIN employee_documents ed ON edc.document_id = ed.document_id
                WHERE edc.employee_id = ? AND dr.category_id = ?
                ORDER BY dr.document_type ASC
            ");
            $stmt->execute([$employeeId, $categoryId]);
            $checklist = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo Response::success($checklist, 'Document checklist by category retrieved');
            break;

        case 'submitEmployeeDocument':
            $input = json_decode(file_get_contents('php://input'), true);
            $employeeId = $input['employee_id'] ?? 0;
            $requirementId = $input['requirement_id'] ?? 0;
            $documentId = $input['document_id'] ?? 0;
            
            if (!$employeeId || !$requirementId || !$documentId) {
                echo Response::error('Missing required fields');
                break;
            }
            
            $stmt = $db->prepare("
                UPDATE employee_document_checklist
                SET document_id = ?,
                    submitted_date = CURDATE(),
                    is_submitted = TRUE,
                    status = 'Submitted'
                WHERE employee_id = ? AND requirement_id = ?
            ");
            $success = $stmt->execute([$documentId, $employeeId, $requirementId]);
            echo json_encode(['success' => $success, 'message' => $success ? 'Document submitted' : 'Failed to submit']);
            break;

        case 'verifyEmployeeDocument':
            $input = json_decode(file_get_contents('php://input'), true);
            $checklistId = $input['checklist_id'] ?? 0;
            $verifiedBy = $input['verified_by'] ?? 0;
            
            if (!$checklistId || !$verifiedBy) {
                echo Response::error('Missing required fields');
                break;
            }
            
            $stmt = $db->prepare("
                UPDATE employee_document_checklist
                SET is_verified = TRUE,
                    verified_by = ?,
                    verified_date = CURDATE(),
                    status = 'Verified'
                WHERE checklist_id = ?
            ");
            $success = $stmt->execute([$verifiedBy, $checklistId]);
            echo json_encode(['success' => $success, 'message' => $success ? 'Document verified' : 'Failed to verify']);
            break;

        case 'getPendingDocumentsReview':
            $stmt = $db->prepare("
                SELECT 
                    edc.*,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    e.employee_id,
                    d.department_name,
                    dr.document_type,
                    dr.document_description
                FROM employee_document_checklist edc
                INNER JOIN employees e ON edc.employee_id = e.employee_id
                INNER JOIN departments d ON e.department_id = d.department_id
                INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
                WHERE edc.status = 'Submitted' AND edc.is_verified = FALSE
                ORDER BY edc.submitted_date ASC
            ");
            $stmt->execute();
            $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($pending, 'Pending documents for review');
            break;

        case 'getExpiringDocuments':
            $daysThreshold = isset($_GET['days']) ? (int)$_GET['days'] : 30;
            
            $stmt = $db->prepare("
                SELECT 
                    edc.*,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    e.employee_id,
                    d.department_name,
                    dr.document_type,
                    ed.expiry_date,
                    DATEDIFF(ed.expiry_date, CURDATE()) as days_until_expiry
                FROM employee_document_checklist edc
                INNER JOIN employees e ON edc.employee_id = e.employee_id
                INNER JOIN departments d ON e.department_id = d.department_id
                INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
                LEFT JOIN employee_documents ed ON edc.document_id = ed.document_id
                WHERE ed.expiry_date IS NOT NULL
                AND DATEDIFF(ed.expiry_date, CURDATE()) BETWEEN 0 AND ?
                AND edc.status IN ('Verified', 'Submitted')
                ORDER BY ed.expiry_date ASC
            ");
            $stmt->execute([$daysThreshold]);
            $expiring = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo Response::success($expiring, 'Expiring documents within threshold');
            break;

        case 'initializeEmployeeDocumentChecklist':
            $input = json_decode(file_get_contents('php://input'), true);
            $employeeId = $input['employee_id'] ?? 0;
            $employmentStatus = $input['employment_status'] ?? 'Permanent';
            
            if (!$employeeId) {
                echo Response::error('Employee ID required');
                break;
            }
            
            try {
                // Get all requirements applicable to this employment status
                $reqStmt = $db->prepare("
                    SELECT requirement_id FROM document_requirements
                    WHERE employment_status = ? OR employment_status IS NULL
                ");
                $reqStmt->execute([$employmentStatus]);
                $requirements = $reqStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $insertCount = 0;
                foreach ($requirements as $req) {
                    $insStmt = $db->prepare("
                        INSERT INTO employee_document_checklist (employee_id, requirement_id, status)
                        VALUES (?, ?, 'Not Submitted')
                        ON DUPLICATE KEY UPDATE status = 'Not Submitted'
                    ");
                    if ($insStmt->execute([$employeeId, $req['requirement_id']])) {
                        $insertCount++;
                    }
                }
                
                echo Response::success(['inserted' => $insertCount], 'Employee document checklist initialized');
            } catch (Exception $e) {
                echo Response::error('Error initializing checklist: ' . $e->getMessage());
            }
            break;

        default:
            echo Response::error('Invalid action');
            break;
    }
} catch (Exception $e) {
    echo Response::error('Error: ' . $e->getMessage());
}
