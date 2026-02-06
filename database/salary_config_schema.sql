-- ====================================================================
-- Hospital Payroll - Salary Configuration Schema
-- Salary templates by position/role, special pay dates, audit_logs
-- Allowed frequencies: Weekly, Semi-Monthly, Monthly (Annual NOT allowed)
-- ====================================================================

-- Audit logs (required by AuditLogger)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `entity_id` INT,
    `user_id` INT,
    `old_values` JSON,
    `new_values` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_action` (`action`),
    KEY `idx_entity_id` (`entity_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salary templates by position and role (hospital: Doctor, Nurse, etc.)
CREATE TABLE IF NOT EXISTS `salary_templates` (
    `template_id` INT AUTO_INCREMENT PRIMARY KEY,
    `position` VARCHAR(100) NOT NULL,
    `role` VARCHAR(100) NOT NULL,
    `employment_type` ENUM('Per shift', 'Hourly', 'Monthly') NOT NULL DEFAULT 'Monthly',
    `salary_type` ENUM('Per shift', 'Hourly', 'Daily', 'Monthly') NOT NULL,
    `shift_duration_hours` DECIMAL(5, 2) NULL COMMENT 'e.g. 24 for doctor, 8 for nurse',
    `basic_rate` DECIMAL(12, 2) NOT NULL,
    `rate_per_hour_reference` DECIMAL(12, 2) NULL COMMENT 'Computed hourly ref e.g. 125',
    `payroll_frequency` ENUM('Weekly', 'Semi-Monthly', 'Monthly') NOT NULL DEFAULT 'Semi-Monthly',
    `payout_day_1` TINYINT NULL COMMENT 'e.g. 7 for 7th',
    `payout_day_2` TINYINT NULL COMMENT 'e.g. 22 for 22nd, NULL if not semi-monthly',
    `cutoff_start_day` TINYINT NULL COMMENT 'e.g. 1 for period 1-15',
    `cutoff_end_day` TINYINT NULL COMMENT 'e.g. 15 for period 1-15',
    `double_pay_addon` DECIMAL(12, 2) DEFAULT 0.00 COMMENT 'Add on when date is special e.g. 3000',
    `incentives_in_payroll` BOOLEAN DEFAULT TRUE,
    `overtime_outside_base` BOOLEAN DEFAULT FALSE,
    `night_differential_applied` BOOLEAN DEFAULT FALSE,
    `incentive_rules` JSON NULL COMMENT 'e.g. ["ER admissions","ER consultations","Medical procedures"]',
    `benefits` TEXT NULL COMMENT 'e.g. Free meals three times daily',
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `effective_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_position_role_effective` (`position`, `role`, `effective_date`),
    KEY `idx_position` (`position`),
    KEY `idx_role` (`role`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Special pay dates (double pay applies on these dates)
CREATE TABLE IF NOT EXISTS `special_pay_dates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL UNIQUE,
    `label` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
