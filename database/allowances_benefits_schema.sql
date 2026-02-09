-- Allowances and Benefits (Compensation submodule)
-- Manage non-salary compensation. Define benefits with taxable flag, eligible roles, effective dates.
-- Benefits attach to duty or role; do not affect salary bands. Data for Payroll reference.

CREATE TABLE IF NOT EXISTS benefit_definitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    benefit_category ENUM('non_cash', 'cash_equivalent') DEFAULT 'non_cash' COMMENT 'Classification for tax handling',
    payroll_impact ENUM('informational', 'included_in_payroll') DEFAULT 'informational' COMMENT 'Impact on payroll processing',
    taxable TINYINT(1) DEFAULT 0 COMMENT '1=taxable',
    taxable_lock TINYINT(1) DEFAULT 0 COMMENT '1=locked once used in payroll, no changes',
    eligible_roles VARCHAR(500) NOT NULL DEFAULT 'All' COMMENT 'Comma-separated e.g. ER Staff,Nurse,Doctor. Multi-select on UI.',
    effective_from DATE NOT NULL,
    effective_to DATE NULL COMMENT 'NULL = no end date',
    attach_to ENUM('duty', 'role') NOT NULL DEFAULT 'role' COMMENT 'Benefit attaches to duty or role',
    is_active TINYINT(1) DEFAULT 1,
    hidden_when_inactive TINYINT(1) DEFAULT 1 COMMENT '1=hide from UI when inactive',
    configured_by_role VARCHAR(100) NULL COMMENT 'Role that configured this benefit',
    last_updated_by_id INT NULL COMMENT 'User ID who last updated',
    last_updated_reason VARCHAR(500) NULL COMMENT 'Reason for last change',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_effective (effective_from, effective_to),
    INDEX idx_attach_to (attach_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhance existing table with new columns if not exists
ALTER TABLE benefit_definitions ADD COLUMN IF NOT EXISTS benefit_category ENUM('non_cash', 'cash_equivalent') DEFAULT 'non_cash' COMMENT 'Classification for tax handling';
ALTER TABLE benefit_definitions ADD COLUMN IF NOT EXISTS payroll_impact ENUM('informational', 'included_in_payroll') DEFAULT 'informational' COMMENT 'Impact on payroll processing';
ALTER TABLE benefit_definitions ADD COLUMN IF NOT EXISTS taxable_lock TINYINT(1) DEFAULT 0 COMMENT '1=locked once used in payroll';
ALTER TABLE benefit_definitions ADD COLUMN IF NOT EXISTS hidden_when_inactive TINYINT(1) DEFAULT 1 COMMENT '1=hide from UI when inactive';
ALTER TABLE benefit_definitions ADD COLUMN IF NOT EXISTS configured_by_role VARCHAR(100) NULL COMMENT 'Role that configured this benefit';
ALTER TABLE benefit_definitions ADD COLUMN IF NOT EXISTS last_updated_by_id INT NULL COMMENT 'User ID who last updated';
ALTER TABLE benefit_definitions ADD COLUMN IF NOT EXISTS last_updated_reason VARCHAR(500) NULL COMMENT 'Reason for last change';
