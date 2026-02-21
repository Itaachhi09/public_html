<?php
/**
 * PayrollModel
 * Handles earnings calculations from approved Compensation records
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class PayrollModel extends BaseModel {
    protected $table = 'employee_salaries';
    protected $primaryKey = 'id';
    protected $fillable = ['employee_id', 'basic_rate', 'salary_type', 'effective_date', 'tax_status', 'payroll_eligible', 'bank_name', 'bank_account'];

    /**
     * Initialize employee salaries for all active employees (run once)
     */
    public function initializeEmployeeSalaries() {
        // First, get all active employees that don't have salary records yet
        $query = "SELECT e.employee_id FROM employees e 
                WHERE (e.employment_status = 'Active' OR e.employment_status = 'employed')
                AND e.employee_id NOT IN (SELECT DISTINCT employee_id FROM employee_salaries)
                LIMIT 100";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($employees)) {
            foreach ($employees as $emp) {
                // Create salary record with all required fields
                $this->create([
                    'employee_id' => $emp['employee_id'],
                    'basic_rate' => 15000.00,
                    'salary_type' => 'Monthly',
                    'effective_date' => date('Y-m-d'),
                    'tax_status' => 'Single',
                    'payroll_eligible' => 1,
                ]);
            }
        }
        
        // Also enable payroll_eligible for all existing employee_salaries records
        $updateQuery = "UPDATE employee_salaries SET payroll_eligible = 1 WHERE payroll_eligible IS NULL OR payroll_eligible = 0";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->execute();
        
        return count($employees ?? []);
    }

    /**
     * Get earnings list with filters
     * Filters: search, department_id, compensation_status
     */
    public function getEarningsList($filters = []) {
        $query = "SELECT 
                    es.id,
                    es.employee_id,
                    es.basic_rate,
                    e.employee_code,
                    e.first_name,
                    e.last_name,
                    e.department_id,
                    d.department_name,
                    es.payroll_eligible,
                    0 as incentives,
                    0 as double_pay,
                    0 as hazard_pay,
                    es.basic_rate as gross_earnings,
                    'Approved' as compensation_status
                FROM employee_salaries es
                JOIN employees e ON es.employee_id = e.employee_id
                LEFT JOIN departments d ON e.department_id = d.department_id
                WHERE es.payroll_eligible = 1";

        $params = [];

        // Search filter
        if (!empty($filters['search'])) {
            $query .= " AND (e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // Department filter
        if (!empty($filters['department'])) {
            $query .= " AND e.department_id = ?";
            $params[] = $filters['department'];
        }

        $query .= " ORDER BY e.employee_code ASC";

        return !empty($params) ? $this->query($query, $params) : $this->query($query);
    }

    /**
     * Get specific employee earnings data
     */
    public function getEarningsByEmployee($employee_id) {
        $query = "SELECT 
                    es.id,
                    es.employee_id,
                    es.basic_rate,
                    e.employee_code,
                    e.first_name,
                    e.last_name,
                    e.department_id,
                    d.department_name,
                    es.payroll_eligible,
                    0 as incentives,
                    0 as double_pay,
                    0 as hazard_pay,
                    es.basic_rate as gross_earnings,
                    'Approved' as compensation_status
                FROM employee_salaries es
                JOIN employees e ON es.employee_id = e.employee_id
                LEFT JOIN departments d ON e.department_id = d.department_id
                WHERE es.employee_id = ? AND es.payroll_eligible = 1";

        return $this->queryOne($query, [$employee_id]);
    }

    /**
     * Calculate gross earnings for an employee
     * Gross Earnings = Base Pay + Incentives + Double Pay + Hazard Pay
     */
    public function calculateGrossEarnings($employee_id) {
        $employee = $this->getEarningsByEmployee($employee_id);
        
        if (!$employee) {
            return null;
        }

        // Get compensation-related data
        $basePay = (float) ($employee['basic_rate'] ?? 0);
        $incentives = (float) ($employee['incentives'] ?? 0);
        $doublePay = (float) ($employee['double_pay'] ?? 0);
        $hazardPay = (float) ($employee['hazard_pay'] ?? 0);

        $grossEarnings = $basePay + $incentives + $doublePay + $hazardPay;

        return [
            'employee_id' => $employee_id,
            'base_pay' => $basePay,
            'incentives' => $incentives,
            'double_pay' => $doublePay,
            'hazard_pay' => $hazardPay,
            'gross_earnings' => $grossEarnings,
            'employee_code' => $employee['employee_code'],
            'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
            'department_name' => $employee['department_name'] ?? 'N/A',
            'compensation_status' => $employee['compensation_status']
        ];
    }

    /**
     * Get total count of employees with earnings
     */
    public function getTotalEmployeesWithEarnings($filters = []) {
        $query = "SELECT COUNT(*) as count
                FROM employee_salaries es
                JOIN employees e ON es.employee_id = e.employee_id
                WHERE es.payroll_eligible = 1";

        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        if (!empty($filters['department'])) {
            $query .= " AND e.department_id = ?";
            $params[] = $filters['department'];
        }

        $result = !empty($params) ? $this->queryOne($query, $params) : $this->queryOne($query);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get total gross earnings for filtered employees
     */
    public function getTotalGrossEarnings($filters = []) {
        $earnings = $this->getEarningsList($filters);
        $total = 0;
        foreach ($earnings as $emp) {
            $total += (float) ($emp['gross_earnings'] ?? 0);
        }
        return $total;
    }

    /**
     * Get all departments for filter dropdown
     */
    public function getAllDepartments() {
        $query = "SELECT department_id, department_name 
                FROM departments 
                WHERE status = 'Active'
                ORDER BY department_name ASC";
        return $this->query($query);
    }

    /**
     * Get pending compensation count
     */
    public function getPendingCompensationCount() {
        // This would query the compensation module for pending records
        // For now, return 0 as placeholder
        return 0;
    }

    /**
     * Get deduction summary list with filters
     */
    public function getDeductionSummary($filters = []) {
        $query = "SELECT 
                    es.employee_id,
                    es.basic_rate as gross_pay,
                    e.employee_code,
                    e.first_name,
                    e.last_name,
                    d.department_name,
                    0 as total_statutory,
                    0 as total_other,
                    0 as total_deductions,
                    es.basic_rate as net_pay,
                    0 as percentage_of_gross,
                    'Approved' as status
                FROM employee_salaries es
                JOIN employees e ON es.employee_id = e.employee_id
                LEFT JOIN departments d ON e.department_id = d.department_id
                WHERE es.payroll_eligible = 1";

        $params = [];

        if (!empty($filters['search'])) {
            $query .= " AND (e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['department'])) {
            $query .= " AND e.department_id = ?";
            $params[] = $filters['department'];
        }

        $query .= " ORDER BY e.employee_code ASC";
        return !empty($params) ? $this->query($query, $params) : $this->query($query);
    }

    /**
     * Get detailed deduction breakdown for an employee
     */
    public function getDeductionBreakdown($employee_id) {
        $employee = $this->getEarningsByEmployee($employee_id);
        
        if (!$employee) {
            return null;
        }

        // Calculate statutory deductions
        $grossPay = (float) ($employee['basic_rate'] ?? 0);
        $withholdingTax = $grossPay * 0.05; // 5% withholding tax
        $sss = $grossPay * 0.0363; // 3.63% SSS
        $philhealth = $grossPay * 0.0225; // 2.25% PhilHealth
        $pagibig = $grossPay * 0.0161; // 1.61% Pag-IBIG
        $totalStatutory = $withholdingTax + $sss + $philhealth + $pagibig;

        // Other deductions (placeholder)
        $hmo = 0;
        $loans = 500; // Example loan deduction
        $manual = 0;
        $totalOther = $hmo + $loans + $manual;

        // Calculate net pay
        $totalDeductions = $totalStatutory + $totalOther;
        $netPay = $grossPay - $totalDeductions;
        $percentageOfGross = ($totalDeductions / $grossPay) * 100;

        return [
            'employee_id' => $employee_id,
            'employee_code' => $employee['employee_code'],
            'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
            'gross_pay' => $grossPay,
            'withholding_tax' => $withholdingTax,
            'sss' => $sss,
            'philhealth' => $philhealth,
            'pagibig' => $pagibig,
            'total_statutory' => $totalStatutory,
            'hmo' => $hmo,
            'loans' => $loans,
            'manual' => $manual,
            'total_other' => $totalOther,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'percentage_of_gross' => $percentageOfGross,
            'status' => 'Approved'
        ];
    }

    /**
     * Get employee info by ID
     */
    public function getEmployeeById($employee_id) {
        $query = "SELECT 
                    e.employee_id,
                    e.employee_code,
                    e.first_name,
                    e.last_name,
                    e.department_id,
                    d.department_name,
                    es.basic_rate
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.department_id
                LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
                WHERE e.employee_id = ?";

        return $this->queryOne($query, [$employee_id]);
    }

    /**
     * Add manual deduction
     */
    public function addManualDeduction($data) {
        // Insert into a manual_deductions table
        $query = "INSERT INTO manual_deductions 
                (employee_id, deduction_type, description, amount, remarks, status, created_date)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $data['employee_id'],
            $data['deduction_type'] ?? 'other',
            $data['description'] ?? '',
            $data['amount'] ?? 0,
            $data['remarks'] ?? '',
            'Pending'
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Approve manual deduction
     */
    public function approveManualDeduction($id) {
        $query = "UPDATE manual_deductions SET status = 'Approved' WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Reject manual deduction
     */
    public function rejectManualDeduction($id) {
        $query = "UPDATE manual_deductions SET status = 'Rejected' WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>
