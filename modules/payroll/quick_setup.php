<?php
/**
 * Quick Payroll Setup Script
 * Run: php c:\NEWXAMPP\htdocs\public_html\modules\payroll\quick_setup.php
 */

$appRoot = 'c:\NEWXAMPP\htdocs\public_html';
require_once $appRoot . '/config/BaseConfig.php';
require_once $appRoot . '/config/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Creating payroll tables...\n";
    
    // Payroll Runs Table
    $conn->exec("CREATE TABLE IF NOT EXISTS payroll_runs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        period_name VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        pay_date DATE NOT NULL,
        status ENUM('Draft', 'Processed', 'Closed') DEFAULT 'Draft',
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_period (period_name),
        INDEX idx_created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[✓] payroll_runs table created\n";
    
    // Employee Salaries Table
    $conn->exec("CREATE TABLE IF NOT EXISTS employee_salaries (
        id INT PRIMARY KEY AUTO_INCREMENT,
        employee_id INT NOT NULL,
        basic_rate DECIMAL(12, 2) NOT NULL,
        salary_type ENUM('Monthly', 'Hourly', 'Daily', 'Annual') DEFAULT 'Monthly',
        effective_date DATE NOT NULL,
        tax_status VARCHAR(50),
        payroll_eligible TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
        INDEX idx_employee_effective (employee_id, effective_date),
        INDEX idx_payroll_eligible (payroll_eligible, effective_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[✓] employee_salaries table created\n";
    
    // Bonus & Incentives Table
    $conn->exec("CREATE TABLE IF NOT EXISTS bonus_incentives (
        id INT PRIMARY KEY AUTO_INCREMENT,
        employee_id INT NOT NULL,
        payroll_run_id INT,
        type ENUM('Bonus', 'Incentive', 'Performance Reward', 'Attendance Bonus', 'Other') DEFAULT 'Other',
        amount DECIMAL(12, 2) NOT NULL,
        description TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
        FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE SET NULL,
        INDEX idx_employee (employee_id),
        INDEX idx_payroll_run (payroll_run_id),
        INDEX idx_created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[✓] bonus_incentives table created\n";
    
    // Deductions Table
    $conn->exec("CREATE TABLE IF NOT EXISTS deductions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        employee_id INT NOT NULL,
        payroll_run_id INT,
        type ENUM('SSS', 'PhilHealth', 'Pag-IBIG', 'Tax', 'Loan', 'HMO', 'Absence', 'Late', 'Other') DEFAULT 'Other',
        amount DECIMAL(12, 2) NOT NULL,
        description TEXT,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
        FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE SET NULL,
        INDEX idx_employee (employee_id),
        INDEX idx_payroll_run (payroll_run_id),
        INDEX idx_type (type),
        INDEX idx_created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[✓] deductions table created\n";
    
    // Payslip Records Table
    $conn->exec("CREATE TABLE IF NOT EXISTS payslip_records (
        id INT PRIMARY KEY AUTO_INCREMENT,
        payroll_run_id INT NOT NULL,
        employee_id INT NOT NULL,
        gross_pay DECIMAL(12, 2) NOT NULL,
        total_deductions DECIMAL(12, 2) DEFAULT 0,
        net_pay DECIMAL(12, 2) NOT NULL,
        status ENUM('Generated', 'Sent', 'Viewed') DEFAULT 'Generated',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
        UNIQUE KEY unique_payslip (payroll_run_id, employee_id),
        INDEX idx_employee (employee_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[✓] payslip_records table created\n";
    
    // Payroll Run Employees Table
    $conn->exec("CREATE TABLE IF NOT EXISTS payroll_run_employees (
        id INT PRIMARY KEY AUTO_INCREMENT,
        payroll_run_id INT NOT NULL,
        employee_id INT NOT NULL,
        gross_pay DECIMAL(12, 2),
        total_deductions DECIMAL(12, 2),
        net_pay DECIMAL(12, 2),
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
        FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
        UNIQUE KEY unique_run_employee (payroll_run_id, employee_id),
        INDEX idx_payroll_run (payroll_run_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[✓] payroll_run_employees table created\n";
    
    echo "\n✓ All payroll tables created successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
