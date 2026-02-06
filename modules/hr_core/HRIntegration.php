<?php
/**
 * HR Core Integration Module
 * Handles API integrations with Payroll, Compensation, HMO, and Analytics modules
 */

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/Response.php';

class HRIntegration {
    private $db;
    private $currentUser;

    public function __construct() {
        $dbInstance = new Database();
        $this->db = $dbInstance->connect();
        $this->currentUser = $_SESSION['user_id'] ?? null;
    }

    /**
     * Route integration requests
     */
    public function route() {
        $action = $_GET['action'] ?? 'index';
        
        if (method_exists($this, $action)) {
            return $this->$action();
        }
        
        return Response::notFound('Integration action not found');
    }

    // ==================== PAYROLL INTEGRATION ====================

    /**
     * Sync employee data to Payroll module
     * POST ?action=syncToPayroll&emp_id=1
     */
    public function syncToPayroll() {
        $this->checkRole(['admin', 'hr', 'payroll']);
        
        $input = $this->getInput();
        $emp_id = $input['emp_id'] ?? null;

        if (!$emp_id) {
            return Response::error('Employee ID is required', 400);
        }

        try {
            // Get employee data
            $query = "
                SELECT e.*, jt.title as job_title, et.type_name as employment_type
                FROM employees e
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
                WHERE e.employee_id = ?
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$emp_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                return Response::error('Employee not found', 404);
            }

            // Sync payload
            $payload = [
                'employee_id' => $employee['employee_id'],
                'employee_code' => $employee['employee_code'],
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'email' => $employee['email'],
                'department_id' => $employee['department_id'],
                'job_title' => $employee['job_title'],
                'employment_type' => $employee['employment_type'],
                'date_of_joining' => $employee['date_of_joining'],
                'employment_status' => $employee['employment_status'],
                'last_working_day' => $employee['last_working_day'],
                'sync_timestamp' => date('Y-m-d H:i:s')
            ];

            // Send to Payroll API
            $result = $this->sendToPayrollAPI($payload);

            if ($result['success']) {
                // Log sync
                $this->logIntegration('PAYROLL_SYNC', 'Employee synced to Payroll', $emp_id, $result);
                return Response::success(['sync_id' => $result['sync_id'] ?? null], 'Employee synced to Payroll successfully');
            } else {
                return Response::error('Failed to sync to Payroll: ' . ($result['message'] ?? 'Unknown error'), 500);
            }
        } catch (Exception $e) {
            return Response::error('Error syncing to Payroll: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sync all active employees to Payroll
     * POST ?action=syncAllToPayroll
     */
    public function syncAllToPayroll() {
        $this->checkRole(['admin', 'hr']);
        
        try {
            $query = "
                SELECT e.employee_id FROM employees e
                WHERE e.employment_status = 'Active'
            ";
            $result = $this->db->query($query);
            $employees = $result->fetchAll(PDO::FETCH_ASSOC);

            $successful = 0;
            $failed = 0;
            $errors = [];

            foreach ($employees as $emp) {
                $input = ['emp_id' => $emp['employee_id']];
                $_GET['emp_id'] = $emp['employee_id'];
                
                try {
                    $this->syncToPayroll();
                    $successful++;
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = 'Employee ' . $emp['employee_id'] . ': ' . $e->getMessage();
                }
            }

            $this->logIntegration('PAYROLL_SYNC_BATCH', 'Batch sync to Payroll', null, [
                'successful' => $successful,
                'failed' => $failed
            ]);

            return Response::success([
                'successful' => $successful,
                'failed' => $failed,
                'errors' => $errors
            ], 'Batch sync completed');
        } catch (Exception $e) {
            return Response::error('Error in batch sync: ' . $e->getMessage(), 500);
        }
    }

    // ==================== COMPENSATION INTEGRATION ====================

    /**
     * Sync employee compensation data
     * POST ?action=syncToCompensation&emp_id=1
     */
    public function syncToCompensation() {
        $this->checkRole(['admin', 'hr', 'compensation']);
        
        $input = $this->getInput();
        $emp_id = $input['emp_id'] ?? null;

        if (!$emp_id) {
            return Response::error('Employee ID is required', 400);
        }

        try {
            // Get compensation data
            $query = "
                SELECT e.*, jt.salary_grade, d.department_name
                FROM employees e
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                LEFT JOIN departments d ON e.department_id = d.department_id
                WHERE e.employee_id = ?
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$emp_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                return Response::error('Employee not found', 404);
            }

            // Get employee movements for salary adjustments
            $query = "
                SELECT * FROM employee_movements
                WHERE employee_id = ? AND movement_type IN ('Promotion', 'Salary Adjustment')
                AND status = 'Approved'
                ORDER BY movement_date DESC
                LIMIT 5
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$emp_id]);
            $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $payload = [
                'employee_id' => $employee['employee_id'],
                'employee_code' => $employee['employee_code'],
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'salary_grade' => $employee['salary_grade'],
                'department' => $employee['department_name'],
                'job_title_id' => $employee['job_title_id'],
                'recent_movements' => $movements,
                'employment_status' => $employee['employment_status'],
                'date_of_joining' => $employee['date_of_joining'],
                'sync_timestamp' => date('Y-m-d H:i:s')
            ];

            // Send to Compensation API
            $result = $this->sendToCompensationAPI($payload);

            if ($result['success']) {
                $this->logIntegration('COMPENSATION_SYNC', 'Employee compensation synced', $emp_id, $result);
                return Response::success(['sync_id' => $result['sync_id'] ?? null], 'Compensation synced successfully');
            } else {
                return Response::error('Failed to sync compensation: ' . ($result['message'] ?? 'Unknown error'), 500);
            }
        } catch (Exception $e) {
            return Response::error('Error syncing compensation: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sync benefits/allowances data
     * POST ?action=syncBenefits&emp_id=1
     */
    public function syncBenefits() {
        $this->checkRole(['admin', 'hr', 'compensation']);
        
        $input = $this->getInput();
        $emp_id = $input['emp_id'] ?? null;

        if (!$emp_id) {
            return Response::error('Employee ID is required', 400);
        }

        try {
            // Get employee benefit allocation
            $query = "
                SELECT be.*, b.benefit_name, b.benefit_type
                FROM employee_benefits be
                JOIN benefits b ON be.benefit_id = b.benefit_id
                WHERE be.employee_id = ? AND be.status = 'Active'
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$emp_id]);
            $benefits = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $payload = [
                'employee_id' => $emp_id,
                'benefits' => $benefits,
                'sync_timestamp' => date('Y-m-d H:i:s')
            ];

            $result = $this->sendToCompensationAPI($payload, 'benefits');

            if ($result['success']) {
                $this->logIntegration('BENEFITS_SYNC', 'Employee benefits synced', $emp_id, $result);
                return Response::success(null, 'Benefits synced successfully');
            } else {
                return Response::error('Failed to sync benefits: ' . ($result['message'] ?? 'Unknown error'), 500);
            }
        } catch (Exception $e) {
            return Response::error('Error syncing benefits: ' . $e->getMessage(), 500);
        }
    }

    // ==================== HMO/INSURANCE INTEGRATION ====================

    /**
     * Sync employee health/HMO enrollment
     * POST ?action=syncToHMO&emp_id=1
     */
    public function syncToHMO() {
        $this->checkRole(['admin', 'hr']);
        
        $input = $this->getInput();
        $emp_id = $input['emp_id'] ?? null;

        if (!$emp_id) {
            return Response::error('Employee ID is required', 400);
        }

        try {
            // Get employee and dependent data
            $query = "
                SELECT e.*, d.department_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                WHERE e.employee_id = ?
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$emp_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                return Response::error('Employee not found', 404);
            }

            // Get employee dependents
            $query = "
                SELECT * FROM employee_dependents
                WHERE employee_id = ? AND status = 'Active'
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$emp_id]);
            $dependents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get HMO enrollment
            $query = "
                SELECT hmo.*, h.hmo_name, h.provider_name
                FROM employee_hmo eh
                JOIN hmo_plans h ON eh.hmo_id = h.hmo_id
                WHERE eh.employee_id = ? AND eh.status = 'Active'
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$emp_id]);
            $hmoEnrollment = $stmt->fetch(PDO::FETCH_ASSOC);

            $payload = [
                'employee_id' => $employee['employee_id'],
                'employee_code' => $employee['employee_code'],
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'date_of_birth' => $employee['date_of_birth'],
                'gender' => $employee['gender'],
                'email' => $employee['email'],
                'phone' => $employee['phone'],
                'department' => $employee['department_name'],
                'employment_status' => $employee['employment_status'],
                'date_of_joining' => $employee['date_of_joining'],
                'dependents' => $dependents,
                'hmo_enrollment' => $hmoEnrollment,
                'sync_timestamp' => date('Y-m-d H:i:s')
            ];

            $result = $this->sendToHMOAPI($payload);

            if ($result['success']) {
                $this->logIntegration('HMO_SYNC', 'Employee HMO enrollment synced', $emp_id, $result);
                return Response::success(['sync_id' => $result['sync_id'] ?? null], 'HMO enrollment synced successfully');
            } else {
                return Response::error('Failed to sync HMO: ' . ($result['message'] ?? 'Unknown error'), 500);
            }
        } catch (Exception $e) {
            return Response::error('Error syncing HMO: ' . $e->getMessage(), 500);
        }
    }

    // ==================== ANALYTICS INTEGRATION ====================

    /**
     * Sync HR metrics to Analytics module
     * POST ?action=syncToAnalytics
     */
    public function syncToAnalytics() {
        $this->checkRole(['admin', 'hr']);
        
        try {
            // Get current metrics
            $metrics = [
                'total_employees' => $this->getTotalEmployees(),
                'active_employees' => $this->getActiveEmployees(),
                'terminated_this_month' => $this->getTerminatedThisMonth(),
                'new_hires_this_month' => $this->getNewHiresThisMonth(),
                'by_department' => $this->getEmployeesByDepartment(),
                'by_employment_type' => $this->getEmployeesByType(),
                'by_status' => $this->getEmployeesByStatus(),
                'average_tenure' => $this->calculateAverageTenure(),
                'turnover_rate' => $this->calculateTurnoverRate(),
                'headcount_by_location' => $this->getHeadcountByLocation(),
                'report_date' => date('Y-m-d'),
                'sync_timestamp' => date('Y-m-d H:i:s')
            ];

            // Send to Analytics API
            $result = $this->sendToAnalyticsAPI($metrics);

            if ($result['success']) {
                $this->logIntegration('ANALYTICS_SYNC', 'HR metrics synced to Analytics', null, $result);
                return Response::success(['sync_id' => $result['sync_id'] ?? null], 'Analytics sync completed successfully');
            } else {
                return Response::error('Failed to sync analytics: ' . ($result['message'] ?? 'Unknown error'), 500);
            }
        } catch (Exception $e) {
            return Response::error('Error syncing analytics: ' . $e->getMessage(), 500);
        }
    }

    // ==================== INTEGRATION HELPER METHODS ====================

    /**
     * Send data to Payroll API
     */
    private function sendToPayrollAPI($payload) {
        // Implementation would depend on actual Payroll API endpoint
        // This is a mock implementation
        return [
            'success' => true,
            'sync_id' => uniqid('payroll_sync_'),
            'message' => 'Data sent successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send data to Compensation API
     */
    private function sendToCompensationAPI($payload, $endpoint = 'compensation') {
        // Implementation would depend on actual Compensation API endpoint
        // This is a mock implementation
        return [
            'success' => true,
            'sync_id' => uniqid('comp_sync_'),
            'message' => 'Data sent successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send data to HMO API
     */
    private function sendToHMOAPI($payload) {
        // Implementation would depend on actual HMO API endpoint
        // This is a mock implementation
        return [
            'success' => true,
            'sync_id' => uniqid('hmo_sync_'),
            'message' => 'Data sent successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Send data to Analytics API
     */
    private function sendToAnalyticsAPI($payload) {
        // Implementation would depend on actual Analytics API endpoint
        // This is a mock implementation
        return [
            'success' => true,
            'sync_id' => uniqid('analytics_sync_'),
            'message' => 'Data sent successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Log integration sync activity
     */
    private function logIntegration($action, $description, $entity_id, $result) {
        try {
            $query = "
                INSERT INTO integration_logs 
                (action, description, entity_id, user_id, result, sync_timestamp, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $action,
                $description,
                $entity_id,
                $this->currentUser,
                json_encode($result),
                date('Y-m-d H:i:s'),
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            // Log error silently
        }
    }

    /**
     * Get total active employees
     */
    private function getTotalEmployees() {
        $query = "SELECT COUNT(*) as count FROM employees WHERE employment_status = 'Active'";
        $result = $this->db->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }

    /**
     * Get active employees
     */
    private function getActiveEmployees() {
        return $this->getTotalEmployees();
    }

    /**
     * Get employees terminated this month
     */
    private function getTerminatedThisMonth() {
        $query = "
            SELECT COUNT(*) as count FROM employees
            WHERE employment_status = 'Terminated'
            AND MONTH(last_working_day) = MONTH(NOW())
            AND YEAR(last_working_day) = YEAR(NOW())
        ";
        $result = $this->db->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }

    /**
     * Get new hires this month
     */
    private function getNewHiresThisMonth() {
        $query = "
            SELECT COUNT(*) as count FROM employees
            WHERE MONTH(date_of_joining) = MONTH(NOW())
            AND YEAR(date_of_joining) = YEAR(NOW())
        ";
        $result = $this->db->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }

    /**
     * Get employees by department
     */
    private function getEmployeesByDepartment() {
        $query = "
            SELECT d.department_id, d.department_name, COUNT(e.employee_id) as count
            FROM departments d
            LEFT JOIN employees e ON d.department_id = e.department_id AND e.employment_status = 'Active'
            GROUP BY d.department_id, d.department_name
        ";
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employees by employment type
     */
    private function getEmployeesByType() {
        $query = "
            SELECT et.employment_type_id, et.type_name, COUNT(e.employee_id) as count
            FROM employment_types et
            LEFT JOIN employees e ON et.employment_type_id = e.employment_type_id AND e.employment_status = 'Active'
            GROUP BY et.employment_type_id, et.type_name
        ";
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employees by status
     */
    private function getEmployeesByStatus() {
        $query = "
            SELECT employment_status, COUNT(*) as count FROM employees
            GROUP BY employment_status
        ";
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate average tenure in months
     */
    private function calculateAverageTenure() {
        $query = "
            SELECT AVG(DATEDIFF(NOW(), date_of_joining)/30) as avg_months
            FROM employees
            WHERE employment_status = 'Active'
        ";
        $result = $this->db->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return round($row['avg_months'] ?? 0, 2);
    }

    /**
     * Calculate turnover rate
     */
    private function calculateTurnoverRate() {
        $query = "
            SELECT 
                (
                    SELECT COUNT(*) FROM employees WHERE employment_status = 'Terminated' AND last_working_day >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                ) / 
                (
                    SELECT COUNT(*) FROM employees WHERE employment_status = 'Active'
                ) * 100 as turnover_rate
        ";
        $result = $this->db->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return round($row['turnover_rate'] ?? 0, 2);
    }

    /**
     * Get headcount by location
     */
    private function getHeadcountByLocation() {
        $query = "
            SELECT l.location_id, l.location_name, COUNT(e.employee_id) as count
            FROM locations l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            GROUP BY l.location_id, l.location_name
        ";
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check role-based access
     */
    private function checkRole($allowedRoles = []) {
        $userRole = $_SESSION['role'] ?? null;
        if (!in_array($userRole, $allowedRoles)) {
            Response::error('Unauthorized access', 403);
            exit;
        }
    }

    /**
     * Get input from GET or POST
     */
    private function getInput() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return json_decode(file_get_contents('php://input'), true) ?? $_POST;
        }
        return $_GET;
    }
}

// Route the request
$integration = new HRIntegration();
echo $integration->route();
?>
