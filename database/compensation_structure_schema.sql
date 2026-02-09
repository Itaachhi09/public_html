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
    effective_from DATE NULL COMMENT 'When component becomes effective',
    effective_to DATE NULL COMMENT 'When component expires; NULL=no end date',
    used_by_payroll TINYINT(1) DEFAULT 0 COMMENT 'Read-only flag: used in active payroll period',
    configured_by_role VARCHAR(100) NULL COMMENT 'Role that configured this component',
    last_updated_by_id INT NULL COMMENT 'User ID who last updated',
    last_updated_reason VARCHAR(500) NULL COMMENT 'Reason for last change (e.g. deactivation reason)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_effective (effective_from, effective_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhance existing table with new columns if not exists
ALTER TABLE salary_component_definitions ADD COLUMN IF NOT EXISTS effective_from DATE NULL COMMENT 'When component becomes effective';
ALTER TABLE salary_component_definitions ADD COLUMN IF NOT EXISTS effective_to DATE NULL COMMENT 'When component expires; NULL=no end date';
ALTER TABLE salary_component_definitions ADD COLUMN IF NOT EXISTS used_by_payroll TINYINT(1) DEFAULT 0 COMMENT 'Read-only flag: used in active payroll period';
ALTER TABLE salary_component_definitions ADD COLUMN IF NOT EXISTS configured_by_role VARCHAR(100) NULL COMMENT 'Role that configured this component';
ALTER TABLE salary_component_definitions ADD COLUMN IF NOT EXISTS last_updated_by_id INT NULL COMMENT 'User ID who last updated';
ALTER TABLE salary_component_definitions ADD COLUMN IF NOT EXISTS last_updated_reason VARCHAR(500) NULL COMMENT 'Reason for last change';
ALTER TABLE salary_component_definitions ADD INDEX IF NOT EXISTS idx_effective (effective_from, effective_to);
