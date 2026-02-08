-- Allowances and Benefits (Compensation submodule)
-- Manage non-salary compensation. Define benefits with taxable flag, eligible roles, effective dates.
-- Benefits attach to duty or role; do not affect salary bands. Data for Payroll reference.

CREATE TABLE IF NOT EXISTS benefit_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    taxable TINYINT(1) DEFAULT 0 COMMENT '1=taxable',
    eligible_roles VARCHAR(500) NOT NULL DEFAULT 'All' COMMENT 'Comma-separated e.g. ER Staff,Nurse,Doctor',
    effective_from DATE NOT NULL,
    effective_to DATE NULL COMMENT 'NULL = no end date',
    attach_to ENUM('duty', 'role') NOT NULL DEFAULT 'role' COMMENT 'Benefit attaches to duty or role',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_effective (effective_from, effective_to),
    INDEX idx_attach_to (attach_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
