-- Incentives and Bonuses (Compensation submodule)
-- Record workload-based earnings. Log incentive events per ER duty; only approved types; unapproved stay inactive.

-- Approved incentive (procedure) types – only these can be used when logging events
CREATE TABLE IF NOT EXISTS incentive_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_approved TINYINT(1) DEFAULT 1 COMMENT '1=approved, only these allowed when logging',
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active' COMMENT 'Status instead of binary approved flag',
    rate_type ENUM('fixed_amount', 'per_case') DEFAULT 'per_case' COMMENT 'How rate is calculated',
    default_rate DECIMAL(10, 2) NULL COMMENT 'Default rate for payroll reference',
    used_by_roles VARCHAR(500) NULL COMMENT 'Roles that use this incentive',
    configured_by_role VARCHAR(100) NULL COMMENT 'Role that configured this',
    last_updated_by_id INT NULL COMMENT 'User ID who last updated',
    last_updated_reason VARCHAR(500) NULL COMMENT 'Reason for last change',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_approved (is_approved),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhance existing table with new columns if not exists
ALTER TABLE incentive_types ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'archived') DEFAULT 'active' COMMENT 'Status instead of binary approved flag';
ALTER TABLE incentive_types ADD COLUMN IF NOT EXISTS rate_type ENUM('fixed_amount', 'per_case') DEFAULT 'per_case' COMMENT 'How rate is calculated';
ALTER TABLE incentive_types ADD COLUMN IF NOT EXISTS default_rate DECIMAL(10, 2) NULL COMMENT 'Default rate for payroll reference';
ALTER TABLE incentive_types ADD COLUMN IF NOT EXISTS used_by_roles VARCHAR(500) NULL COMMENT 'Roles that use this incentive';
ALTER TABLE incentive_types ADD COLUMN IF NOT EXISTS configured_by_role VARCHAR(100) NULL COMMENT 'Role that configured this';
ALTER TABLE incentive_types ADD COLUMN IF NOT EXISTS last_updated_by_id INT NULL COMMENT 'User ID who last updated';
ALTER TABLE incentive_types ADD COLUMN IF NOT EXISTS last_updated_reason VARCHAR(500) NULL COMMENT 'Reason for last change';
ALTER TABLE incentive_types ADD INDEX IF NOT EXISTS idx_status (status);

-- Incentive events: each event links to one ER duty (employee + duty_date); multiple procedures per duty allowed
CREATE TABLE IF NOT EXISTS incentive_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    duty_date DATE NOT NULL,
    procedure_type_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    approved_by INT NULL COMMENT 'User/approver who approved',
    status ENUM('pending', 'approved') DEFAULT 'pending' COMMENT 'Unapproved = inactive for payroll',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_duty_date (duty_date),
    INDEX idx_employee_duty (employee_id, duty_date),
    INDEX idx_status (status),
    INDEX idx_procedure_type_id (procedure_type_id),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (procedure_type_id) REFERENCES incentive_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
