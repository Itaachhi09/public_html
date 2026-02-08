<?php
/**
 * HR Core Report Generator
 * Generates various HR reports with PDF export capability
 */

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/Response.php';

class HRReportGenerator {
    private $db;
    private $currentUser;

    public function __construct() {
        $dbInstance = new Database();
        $this->db = $dbInstance->connect();
        $this->currentUser = $_SESSION['user_id'] ?? null;
    }

    /**
     * Route report requests
     */
    public function route() {
        $action = $_GET['action'] ?? 'index';
        
        if (method_exists($this, $action)) {
            return $this->$action();
        }
        
        return Response::notFound('Report action not found');
    }

    // ==================== EMPLOYEE REPORTS ====================

    /**
     * Generate complete employee list report
     * GET ?action=employeeListReport&dept_id=1&export=csv
     */
    public function employeeListReport() {
        $this->checkRole(['admin', 'hr', 'manager']);
        
        $input = $_GET;
        $dept_id = $input['dept_id'] ?? null;
        $status = $input['status'] ?? 'Active';
        $export = $input['export'] ?? null;

        try {
            $query = "
                SELECT 
                    e.employee_id,
                    e.employee_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    e.email,
                    e.phone,
                    d.department_name,
                    jt.title as job_title,
                    et.type_name as employment_type,
                    l.location_name,
                    e.date_of_joining,
                    e.employment_status,
                    DATEDIFF(NOW(), e.date_of_joining) as days_employed
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
                LEFT JOIN locations l ON e.location_id = l.location_id
                WHERE 1=1
            ";

            $params = [];
            
            if ($dept_id) {
                $query .= " AND e.department_id = ?";
                $params[] = $dept_id;
            }
            
            if ($status) {
                $query .= " AND e.employment_status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY e.first_name ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($export === 'csv') {
                return $this->exportToCSV('employees', $employees, [
                    'Employee Code', 'Name', 'Email', 'Phone', 'Department', 'Job Title',
                    'Employment Type', 'Location', 'Join Date', 'Status', 'Days Employed'
                ]);
            }

            return Response::success([
                'report_name' => 'Employee List Report',
                'generated_date' => date('Y-m-d H:i:s'),
                'total_employees' => count($employees),
                'data' => $employees
            ], 'Employee list report generated');
        } catch (Exception $e) {
            return Response::error('Error generating employee list report: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate employee directory (read-only public view)
     * GET ?action=employeeDirectory&dept_id=1
     */
    public function employeeDirectory() {
        // This is accessible by all authenticated users
        $input = $_GET;
        $dept_id = $input['dept_id'] ?? null;
        $search = $input['search'] ?? null;

        try {
            $query = "
                SELECT 
                    e.employee_id,
                    e.employee_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    e.email,
                    e.phone,
                    d.department_name,
                    jt.title as job_title,
                    l.location_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                LEFT JOIN locations l ON e.location_id = l.location_id
                WHERE e.employment_status = 'Active'
            ";

            $params = [];
            
            if ($dept_id) {
                $query .= " AND e.department_id = ?";
                $params[] = $dept_id;
            }
            
            if ($search) {
                $query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $query .= " ORDER BY e.first_name ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $directory = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return Response::success([
                'directory' => $directory,
                'total_employees' => count($directory)
            ], 'Employee directory retrieved');
        } catch (Exception $e) {
            return Response::error('Error retrieving directory: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate department organization chart
     * GET ?action=orgChart&dept_id=1
     */
    public function orgChart() {
        $this->checkRole(['admin', 'hr', 'manager']);
        
        $input = $_GET;
        $dept_id = $input['dept_id'] ?? null;

        try {
            // Get department with head
            $query = "
                SELECT d.*, 
                       CONCAT(e.first_name, ' ', e.last_name) as head_name,
                       e.employee_code as head_code,
                       e.email as head_email
                FROM departments d
                LEFT JOIN employees e ON d.head_employee_id = e.employee_id
                WHERE d.department_id = ?
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$dept_id]);
            $department = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$department) {
                return Response::error('Department not found', 404);
            }

            // Get all employees in department with supervisors
            $query = "
                SELECT 
                    e.employee_id,
                    e.employee_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    e.job_title_id,
                    jt.title as job_title,
                    e.supervisor_id,
                    CONCAT(s.first_name, ' ', s.last_name) as supervisor_name
                FROM employees e
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                LEFT JOIN employees s ON e.supervisor_id = s.employee_id
                WHERE e.department_id = ? AND e.employment_status = 'Active'
                ORDER BY e.supervisor_id, e.first_name ASC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$dept_id]);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Build hierarchy
            $orgChart = [
                'department' => $department,
                'employees' => $this->buildHierarchy($employees)
            ];

            return Response::success($orgChart, 'Organization chart generated');
        } catch (Exception $e) {
            return Response::error('Error generating org chart: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate headcount report by various dimensions
     * GET ?action=headcountReport&dimension=department|location|status
     */
    public function headcountReport() {
        $this->checkRole(['admin', 'hr', 'manager']);
        
        $input = $_GET;
        $dimension = $input['dimension'] ?? 'department';
        $export = $input['export'] ?? null;

        try {
            $query = "
                SELECT 
                    CASE 
            ";

            if ($dimension === 'department') {
                $query .= "
                    WHEN d.department_id IS NOT NULL THEN d.department_name
                    ELSE 'Unassigned'
                    END as category,
                ";
            } elseif ($dimension === 'location') {
                $query .= "
                    WHEN l.location_id IS NOT NULL THEN l.location_name
                    ELSE 'Unassigned'
                    END as category,
                ";
            } elseif ($dimension === 'status') {
                $query .= "
                    e.employment_status as category,
                ";
            } else {
                return Response::error('Invalid dimension', 400);
            }

            $query .= "
                    COUNT(e.employee_id) as total_count,
                    SUM(CASE WHEN e.employment_status = 'Active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN e.employment_status = 'On Leave' THEN 1 ELSE 0 END) as on_leave,
                    SUM(CASE WHEN e.employment_status = 'Terminated' THEN 1 ELSE 0 END) as terminated
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN locations l ON e.location_id = l.location_id
                GROUP BY category
                ORDER BY total_count DESC
            ";

            $stmt = $this->db->query($query);
            $report = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($export === 'csv') {
                return $this->exportToCSV('headcount', $report, ['Category', 'Total', 'Active', 'On Leave', 'Terminated']);
            }

            return Response::success([
                'report_name' => 'Headcount Report - ' . ucfirst($dimension),
                'dimension' => $dimension,
                'generated_date' => date('Y-m-d H:i:s'),
                'data' => $report
            ], 'Headcount report generated');
        } catch (Exception $e) {
            return Response::error('Error generating headcount report: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate turnover analysis report
     * GET ?action=turnoverReport&months=12&export=csv
     */
    public function turnoverReport() {
        $this->checkRole(['admin', 'hr']);
        
        $input = $_GET;
        $months = intval($input['months'] ?? 12);
        $export = $input['export'] ?? null;

        try {
            $query = "
                SELECT 
                    DATE_FORMAT(e.last_working_day, '%Y-%m') as month,
                    COUNT(e.employee_id) as terminated_count,
                    GROUP_CONCAT(CONCAT(e.employee_code, ' - ', e.first_name, ' ', e.last_name) SEPARATOR '; ') as employee_names
                FROM employees e
                WHERE e.employment_status = 'Terminated'
                AND e.last_working_day >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(e.last_working_day, '%Y-%m')
                ORDER BY month DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$months]);
            $turnover = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate metrics
            $totalTerminated = array_sum(array_column($turnover, 'terminated_count'));
            $avgMonthly = count($turnover) > 0 ? $totalTerminated / count($turnover) : 0;

            if ($export === 'csv') {
                return $this->exportToCSV('turnover', $turnover, ['Month', 'Terminated Count', 'Employee Names']);
            }

            return Response::success([
                'report_name' => 'Turnover Analysis Report',
                'period_months' => $months,
                'total_terminated' => $totalTerminated,
                'average_monthly_terminations' => round($avgMonthly, 2),
                'generated_date' => date('Y-m-d H:i:s'),
                'data' => $turnover
            ], 'Turnover report generated');
        } catch (Exception $e) {
            return Response::error('Error generating turnover report: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate employee tenure analysis
     * GET ?action=tenureReport&export=csv
     */
    public function tenureReport() {
        $this->checkRole(['admin', 'hr']);
        
        $input = $_GET;
        $export = $input['export'] ?? null;

        try {
            $query = "
                SELECT 
                    e.employee_id,
                    e.employee_code,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    d.department_name,
                    jt.title as job_title,
                    e.date_of_joining,
                    DATEDIFF(NOW(), e.date_of_joining) as days_employed,
                    FLOOR(DATEDIFF(NOW(), e.date_of_joining) / 365.25) as years_employed,
                    e.employment_status
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                ORDER BY e.date_of_joining ASC
            ";
            $stmt = $this->db->query($query);
            $tenure = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($export === 'csv') {
                return $this->exportToCSV('tenure', $tenure, [
                    'Employee Code', 'Name', 'Department', 'Job Title', 'Join Date', 'Years', 'Status'
                ]);
            }

            return Response::success([
                'report_name' => 'Employee Tenure Report',
                'total_employees' => count($tenure),
                'generated_date' => date('Y-m-d H:i:s'),
                'data' => $tenure
            ], 'Tenure report generated');
        } catch (Exception $e) {
            return Response::error('Error generating tenure report: ' . $e->getMessage(), 500);
        }
    }

    // ==================== DOCUMENT & COMPLIANCE REPORTS ====================

    /**
     * Generate document expiry report
     * GET ?action=documentExpiryReport&days=30&export=csv
     */
    public function documentExpiryReport() {
        $this->checkRole(['admin', 'hr']);
        
        $input = $_GET;
        $days = intval($input['days'] ?? 30);
        $export = $input['export'] ?? null;

        try {
            $query = "
                SELECT 
                    ed.document_id,
                    ed.document_type,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    e.employee_code,
                    ed.file_path,
                    ed.expiry_date,
                    DATEDIFF(ed.expiry_date, NOW()) as days_until_expiry,
                    CASE 
                        WHEN DATEDIFF(ed.expiry_date, NOW()) < 0 THEN 'Expired'
                        WHEN DATEDIFF(ed.expiry_date, NOW()) <= 7 THEN 'Critical'
                        WHEN DATEDIFF(ed.expiry_date, NOW()) <= ? THEN 'Warning'
                        ELSE 'Active'
                    END as status
                FROM employee_documents ed
                JOIN employees e ON ed.employee_id = e.employee_id
                WHERE ed.expiry_date IS NOT NULL
                AND ed.expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
                ORDER BY ed.expiry_date ASC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$days, $days]);
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($export === 'csv') {
                return $this->exportToCSV('document_expiry', $documents, [
                    'Document Type', 'Employee', 'Employee Code', 'Expiry Date', 'Days Until Expiry', 'Status'
                ]);
            }

            return Response::success([
                'report_name' => 'Document Expiry Report',
                'warning_days' => $days,
                'total_documents' => count($documents),
                'generated_date' => date('Y-m-d H:i:s'),
                'data' => $documents
            ], 'Document expiry report generated');
        } catch (Exception $e) {
            return Response::error('Error generating document expiry report: ' . $e->getMessage(), 500);
        }
    }

    // ==================== HELPER METHODS ====================

    /**
     * Build hierarchy from flat employee list
     */
    private function buildHierarchy($employees) {
        $hierarchy = [];
        
        // Group by supervisor
        foreach ($employees as $emp) {
            $supervisorId = $emp['supervisor_id'] ?? 'head';
            if (!isset($hierarchy[$supervisorId])) {
                $hierarchy[$supervisorId] = [];
            }
            $hierarchy[$supervisorId][] = $emp;
        }
        
        return $hierarchy;
    }

    /**
     * Export data to CSV
     */
    private function exportToCSV($filename, $data, $headers) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        fputcsv($output, $headers);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, array_values($row));
        }
        
        fclose($output);
        exit;
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
}

// Route the request
$reportGenerator = new HRReportGenerator();
echo $reportGenerator->route();
?>
