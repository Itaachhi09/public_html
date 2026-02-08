-- Payroll Module Schema
-- Employee salaries, payroll runs, run details, and payslips.

-- Employee salaries (current rate per employee)
CREATE TABLE IF NOT EXISTS employee_salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    salary_type ENUM('Hourly', 'Daily', 'Monthly', 'Annual') NOT NULL DEFAULT 'Monthly',
    basic_rate DECIMAL(12, 2) NOT NULL,
    effective_date DATE NOT NULL,
    tax_status VARCHAR(50) DEFAULT 'Single',
    payroll_eligible TINYINT(1) DEFAULT 1,
    bank_name VARCHAR(100) NULL,
    bank_account VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_effective_date (effective_date),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payroll runs (periods)
CREATE TABLE IF NOT EXISTS payroll_runs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    pay_date DATE NOT NULL,
    status ENUM('Draft', 'Processed', 'Closed') NOT NULL DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payroll run employees (one row per employee per run; payslip data)
CREATE TABLE IF NOT EXISTS payroll_run_employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_run_id INT NOT NULL,
    employee_id INT NOT NULL,
    basic_pay DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    gross_pay DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_deductions DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    net_pay DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    payslip_number VARCHAR(50) NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_run_employee (payroll_run_id, employee_id),
    INDEX idx_payroll_run_id (payroll_run_id),
    INDEX idx_employee_id (employee_id),
    FOREIGN KEY (payroll_run_id) REFERENCES payroll_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
