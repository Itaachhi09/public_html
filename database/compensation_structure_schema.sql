-- Compensation Structure and Setup (Compensation submodule)
-- Master definition of all pay components. No payroll computation. Readable by other submodules and Payroll.
-- System context: Primary hospital, 30 beds, no ICU, ER focused.

-- Salary component definitions (e.g. ER Duty Pay = 24 hour ER duty base pay)
CREATE TABLE IF NOT EXISTS salary_component_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    component_type ENUM('base', 'allowance', 'deduction') NOT NULL DEFAULT 'allowance',
    taxable TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
