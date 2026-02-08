<?php
/**
 * Analytics Service - CORRECTED FOR ACTUAL DATABASE SCHEMA
 * Core business logic for analytics - queries live data from all source modules
 * All calculations happen here, no data duplication
 * 
 * TABLE CORRECTIONS FROM ACTUAL SCHEMA:
 * - employees.employment_status (not employee_status)
 * - employment_types.type_name (not employment_type_name)
 * - employee_movements (not movements_changes)
 * - payroll_run_employees (not payroll_items)
 * - employee_hmo_enrollments (not hmo_enrollments)
 * - hmo_enrollment_dependents (not hmo_dependents)
 */

require_once __DIR__ . '/../../config/Database.php';

class AnalyticsService
{
    private $db;
    private $dateFormat = 'Y-m-d';

    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * ===== HR CORE METRICS =====
     */

    public function getHeadcountSummary($department = null, $employmentType = null)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN employment_status = 'Active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN employment_status != 'Active' THEN 1 ELSE 0 END) as inactive
                FROM employees
                WHERE 1=1";
        
        $params = [];
        if ($department) {
            $sql .= " AND department_id = ?";
            $params[] = $department;
        }
        if ($employmentType) {
            $sql .= " AND employment_type_id = ?";
            $params[] = $employmentType;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'total' => (int)($row['total'] ?? 0),
                'active' => (int)($row['active'] ?? 0),
                'inactive' => (int)($row['inactive'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log('Analytics Error - getHeadcountSummary: ' . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
    }

    public function getHeadcountByDepartment()
    {
        $sql = "SELECT d.department_id, d.department_name, COUNT(e.employee_id) as count
                FROM departments d
                LEFT JOIN employees e ON d.department_id = e.department_id AND e.employment_status = 'Active'
                GROUP BY d.department_id, d.department_name
                ORDER BY count DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getHeadcountByDepartment: ' . $e->getMessage());
            return [];
        }
    }

    public function getEmploymentTypeDistribution()
    {
        $sql = "SELECT et.employment_type_id, et.type_name, COUNT(e.employee_id) as count
                FROM employment_types et
                LEFT JOIN employees e ON et.employment_type_id = e.employment_type_id AND e.employment_status = 'Active'
                GROUP BY et.employment_type_id, et.type_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getEmploymentTypeDistribution: ' . $e->getMessage());
            return [];
        }
    }

    public function getMovementData($days = 30, $department = null)
    {
        $startDate = date($this->dateFormat, strtotime("-$days days"));
        
        $sql = "SELECT 
                    SUM(CASE WHEN movement_type = 'Transfer' THEN 1 ELSE 0 END) as transfers,
                    SUM(CASE WHEN movement_type = 'Promotion' THEN 1 ELSE 0 END) as promotions,
                    SUM(CASE WHEN movement_type = 'Demotion' THEN 1 ELSE 0 END) as demotions,
                    COUNT(*) as total_movements
                FROM employee_movements
                WHERE effective_date >= ? AND effective_date <= NOW()
                AND status = 'Completed'";
        
        $params = [$startDate];
        if ($department) {
            $sql .= " AND (from_department_id = ? OR to_department_id = ?)";
            $params[] = $department;
            $params[] = $department;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'transfers' => (int)($row['transfers'] ?? 0),
                'promotions' => (int)($row['promotions'] ?? 0),
                'demotions' => (int)($row['demotions'] ?? 0),
                'new_hires' => 0, // Would need separate query for new hires by hire date
                'resignations' => 0 // Would need to query from last_working_day
            ];
        } catch (Exception $e) {
            error_log('Analytics Error - getMovementData: ' . $e->getMessage());
            return ['transfers' => 0, 'promotions' => 0, 'demotions' => 0, 'new_hires' => 0, 'resignations' => 0];
        }
    }

    public function getContractExpiryData()
    {
        $sql = "SELECT 
                    SUM(CASE WHEN DATEDIFF(end_date, CURDATE()) BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as expiring_30,
                    SUM(CASE WHEN DATEDIFF(end_date, CURDATE()) BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as expiring_60,
                    SUM(CASE WHEN DATEDIFF(end_date, CURDATE()) BETWEEN 61 AND 90 THEN 1 ELSE 0 END) as expiring_90,
                    SUM(CASE WHEN end_date < CURDATE() THEN 1 ELSE 0 END) as expired
                FROM pay_contracts
                WHERE status = 'Active'";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'expiring_30' => (int)($row['expiring_30'] ?? 0),
                'expiring_60' => (int)($row['expiring_60'] ?? 0),
                'expiring_90' => (int)($row['expiring_90'] ?? 0),
                'expired' => (int)($row['expired'] ?? 0),
                'total' => ((int)($row['expiring_30'] ?? 0)) + ((int)($row['expiring_60'] ?? 0)) + ((int)($row['expiring_90'] ?? 0))
            ];
        } catch (Exception $e) {
            error_log('Analytics Error - getContractExpiryData: ' . $e->getMessage());
            return ['expiring_30' => 0, 'expiring_60' => 0, 'expiring_90' => 0, 'expired' => 0, 'total' => 0];
        }
    }

    public function getExpiringDocumentsData()
    {
        $sql = "SELECT COUNT(*) as count
                FROM employee_documents
                WHERE expiry_date IS NOT NULL
                AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['count'] ?? 0);
        } catch (Exception $e) {
            error_log('Analytics Error - getExpiringDocumentsData: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * ===== PAYROLL METRICS =====
     */

    public function getPayrollSummary($days = 30, $department = null)
    {
        $startDate = date($this->dateFormat, strtotime("-$days days"));
        
        $sql = "SELECT 
                    SUM(pre.gross_pay) as gross_total,
                    SUM(pre.total_deductions) as deductions,
                    SUM(pre.net_pay) as net_total,
                    COUNT(DISTINCT pre.payroll_run_id) as payroll_runs,
                    COUNT(DISTINCT pre.employee_id) as employees
                FROM payroll_run_employees pre
                JOIN payroll_runs pr ON pre.payroll_run_id = pr.id
                WHERE pr.start_date >= ? AND pr.end_date <= NOW()
                AND pr.status IN ('Processed', 'Closed')";
        
        $params = [$startDate];
        if ($department) {
            $sql .= " AND e.department_id = ?";
            $params[] = $department;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $gross = (int)($row['gross_total'] ?? 0);
            $deductions = (int)($row['deductions'] ?? 0);
            $net = (int)($row['net_total'] ?? 0);
            
            return [
                'gross' => $gross,
                'deductions' => $deductions,
                'net' => $net,
                'active_runs' => (int)($row['payroll_runs'] ?? 0),
                'employees' => (int)($row['employees'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log('Analytics Error - getPayrollSummary: ' . $e->getMessage());
            return ['gross' => 0, 'deductions' => 0, 'net' => 0, 'active_runs' => 0, 'employees' => 0];
        }
    }

    public function getOvertimeSummary($days = 30, $department = null)
    {
        // Placeholder - needs overtime table which may not be in current schema
        return ['hours' => 0, 'cost' => 0];
    }

    public function getTaxAndContributions($days = 30, $department = null)
    {
        // Placeholder - needs tax contributions table which may not be in current schema
        return ['tax' => 0, 'sss' => 0, 'philhealth' => 0, 'pagibig' => 0, 'total' => 0];
    }

    /**
     * ===== COMPENSATION METRICS =====
     */

    public function getAverageSalary($department = null)
    {
        $sql = "SELECT AVG(es.basic_rate) as avg_salary, MIN(es.basic_rate) as min_salary, MAX(es.basic_rate) as max_salary
                FROM employee_salaries es
                JOIN employees e ON es.employee_id = e.employee_id
                WHERE e.employment_status = 'Active'";
        
        $params = [];
        if ($department) {
            $sql .= " AND e.department_id = ?";
            $params[] = $department;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'average' => (int)($row['avg_salary'] ?? 0),
                'minimum' => (int)($row['min_salary'] ?? 0),
                'maximum' => (int)($row['max_salary'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log('Analytics Error - getAverageSalary: ' . $e->getMessage());
            return ['average' => 0, 'minimum' => 0, 'maximum' => 0];
        }
    }

    public function getPayGradeDistribution()
    {
        // This needs the actual salary structure tables from compensation module
        return [];
    }

    public function getIncentivesPayout($days = 30)
    {
        // This needs incentive_events table
        return ['total' => 0, 'count' => 0, 'recipients' => 0];
    }

    /**
     * ===== HMO METRICS =====
     */

    public function getHMOSnapshot($department = null)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT ehe.id) as enrolled,
                    COUNT(DISTINCT CASE WHEN ehe.enrollment_status = 'active' THEN ehe.id END) as active,
                    SUM(hp.monthly_premium) as total_cost,
                    AVG(ehe.employer_share_amount) as avg_employer_share
                FROM employee_hmo_enrollments ehe
                LEFT JOIN hmo_plans hp ON ehe.plan_id = hp.id
                WHERE ehe.enrollment_status IN ('active', 'waiting_period')";
        
        $params = [];
        if ($department) {
            $sql .= " AND ehe.employee_id IN (SELECT employee_id FROM employees WHERE department_id = ?)";
            $params[] = $department;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'enrolled' => (int)($row['enrolled'] ?? 0),
                'active' => (int)($row['active'] ?? 0),
                'total_cost' => (int)($row['total_cost'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log('Analytics Error - getHMOSnapshot: ' . $e->getMessage());
            return ['enrolled' => 0, 'active' => 0, 'total_cost' => 0];
        }
    }

    public function getHMOEnrollmentRate()
    {
        try {
            $sql1 = "SELECT COUNT(*) as count FROM employees WHERE employment_status = 'Active'";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute();
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $totalEmployees = (int)($row1['count'] ?? 1);

            $sql2 = "SELECT COUNT(DISTINCT employee_id) as count FROM employee_hmo_enrollments WHERE enrollment_status IN ('active', 'waiting_period')";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute();
            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $enrolledEmployees = (int)($row2['count'] ?? 0);

            return $totalEmployees > 0 ? round(($enrolledEmployees / $totalEmployees) * 100, 2) : 0;
        } catch (Exception $e) {
            error_log('Analytics Error - getHMOEnrollmentRate: ' . $e->getMessage());
            return 0;
        }
    }

    public function getHMOCostPerEmployee()
    {
        try {
            $sql1 = "SELECT SUM(hp.monthly_premium) as total FROM employee_hmo_enrollments ehe
                     LEFT JOIN hmo_plans hp ON ehe.plan_id = hp.id
                     WHERE ehe.enrollment_status IN ('active', 'waiting_period')";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute();
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $totalCost = (int)($row1['total'] ?? 0);

            $sql2 = "SELECT COUNT(*) as count FROM employees WHERE employment_status = 'Active'";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute();
            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $totalEmployees = (int)($row2['count'] ?? 1);

            return $totalEmployees > 0 ? round($totalCost / $totalEmployees, 2) : 0;
        } catch (Exception $e) {
            error_log('Analytics Error - getHMOCostPerEmployee: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * ===== UTILITY METHODS =====
     */

    public function getDepartmentsList()
    {
        $sql = "SELECT department_id as id, department_name as name FROM departments ORDER BY department_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getDepartmentsList: ' . $e->getMessage());
            return [];
        }
    }

    public function getEmploymentTypesList()
    {
        $sql = "SELECT employment_type_id as id, type_name as name FROM employment_types ORDER BY type_name";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getEmploymentTypesList: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ===== REPORT DATA METHODS =====
     */

    public function getEmployeeMasterReport($department, $employmentType, $status, $limit, $offset)
    {
        $sql = "SELECT e.employee_id, e.first_name, e.last_name, e.email, e.phone,
                       d.department_name, jt.title as designation, e.date_of_joining, et.type_name, e.employment_status
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
                LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
                WHERE 1=1";
        
        $params = [];
        if ($department) { $sql .= " AND e.department_id = ?"; $params[] = $department; }
        if ($employmentType) { $sql .= " AND e.employment_type_id = ?"; $params[] = $employmentType; }
        if ($status) { $sql .= " AND e.employment_status = ?"; $params[] = $status; }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getEmployeeMasterReport: ' . $e->getMessage());
            return [];
        }
    }

    public function getPayrollSummaryReport($startDate, $endDate, $department, $limit, $offset)
    {
        $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                       SUM(pre.basic_pay) as basic_pay,
                       SUM(pre.gross_pay) as total_earnings,
                       SUM(pre.total_deductions) as deductions,
                       SUM(pre.net_pay) as net_pay
                FROM payroll_run_employees pre
                JOIN payroll_runs pr ON pre.payroll_run_id = pr.id
                JOIN employees e ON pre.employee_id = e.employee_id
                WHERE pr.start_date >= ? AND pr.end_date <= ?
                AND pr.status IN ('Processed', 'Closed')";
        
        $params = [$startDate, $endDate];
        if ($department) { $sql .= " AND e.department_id = ?"; $params[] = $department; }

        $sql .= " GROUP BY e.employee_id LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getPayrollSummaryReport: ' . $e->getMessage());
            return [];
        }
    }

    public function getComplianceReport($startDate, $endDate, $department, $limit, $offset)
    {
        // Placeholder - needs tax contributions table which may not be in current schema
        return [];
    }

    public function getCompensationCostReport($department, $limit, $offset)
    {
        $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                       'N/A' as pay_grade, es.basic_rate,
                       'N/A' as incentives, 'N/A' as allowances
                FROM employees e
                LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
                WHERE e.employment_status = 'Active'";
        
        $params = [];
        if ($department) { $sql .= " AND e.department_id = ?"; $params[] = $department; }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getCompensationCostReport: ' . $e->getMessage());
            return [];
        }
    }

    public function getHMOReport($department, $limit, $offset)
    {
        $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                       hp.plan_name as hmo_plan, ehe.enrollment_date,
                       COALESCE((SELECT COUNT(*) FROM hmo_enrollment_dependents WHERE enrollment_id = ehe.id), 0) as dependents,
                       hp.monthly_premium as monthly_cost,
                       (hp.monthly_premium * 12) as annual_cost
                FROM employees e
                LEFT JOIN employee_hmo_enrollments ehe ON e.employee_id = ehe.employee_id AND ehe.enrollment_status IN ('active', 'waiting_period')
                LEFT JOIN hmo_plans hp ON ehe.plan_id = hp.id
                WHERE 1=1";
        
        $params = [];
        if ($department) { $sql .= " AND e.department_id = ?"; $params[] = $department; }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Analytics Error - getHMOReport: ' . $e->getMessage());
            return [];
        }
    }
}
?>
