-- ====================================================================
-- Payroll Module - Complete Database Schema
-- Database: hospital_hr_system
-- ====================================================================

-- Drop existing tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS `payslip_reissues`;
DROP TABLE IF EXISTS `payslips`;
DROP TABLE IF EXISTS `payroll_deductions`;
DROP TABLE IF EXISTS `payroll_earnings`;
DROP TABLE IF EXISTS `payroll_run_employees`;
DROP TABLE IF EXISTS `payroll_runs`;
DROP TABLE IF EXISTS `payroll_calendar`;
DROP TABLE IF EXISTS `employee_deduction_rules`;
DROP TABLE IF EXISTS `employee_allowances`;
DROP TABLE IF EXISTS `employee_salaries`;

-- ====================================================================
-- 1. EMPLOYEE SALARIES TABLE
-- ====================================================================
CREATE TABLE `employee_salaries` (
    `salary_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `salary_type` ENUM('Hourly', 'Daily', 'Monthly', 'Annual') NOT NULL DEFAULT 'Monthly',
    `basic_rate` DECIMAL(12, 2) NOT NULL,
    `salary_grade_id` INT,
    `tax_status` ENUM('Single', 'Married', 'Married with 1 Dependent', 'Married with 2 Dependents', 'Married with 3 Dependents', 'Married with 4+ Dependents') DEFAULT 'Single',
    `dependents_count` INT DEFAULT 0,
    `bank_name` VARCHAR(255),
    `bank_account_number` VARCHAR(100),
    `payout_method` ENUM('Bank Transfer', 'Cash', 'Check') DEFAULT 'Bank Transfer',
    `payroll_eligible` BOOLEAN DEFAULT TRUE,
    `effective_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_effective_date` (`effective_date`),
    KEY `idx_salary_grade_id` (`salary_grade_id`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`salary_grade_id`) REFERENCES `salary_grades` (`salary_grade_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 2. EMPLOYEE ALLOWANCES TABLE
-- ====================================================================
CREATE TABLE `employee_allowances` (
    `allowance_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `allowance_type` VARCHAR(100) NOT NULL,
    `allowance_name` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `is_taxable` BOOLEAN DEFAULT TRUE,
    `effective_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_effective_date` (`effective_date`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 3. EMPLOYEE DEDUCTION RULES TABLE
-- ====================================================================
CREATE TABLE `employee_deduction_rules` (
    `deduction_rule_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `deduction_type` VARCHAR(100) NOT NULL,
    `deduction_name` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(12, 2),
    `percentage` DECIMAL(5, 2),
    `calculation_base` ENUM('Basic Pay', 'Gross Pay', 'Fixed Amount') DEFAULT 'Basic Pay',
    `priority_order` INT DEFAULT 0,
    `effective_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_effective_date` (`effective_date`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 4. PAYROLL CALENDAR TABLE
-- ====================================================================
CREATE TABLE `payroll_calendar` (
    `calendar_id` INT AUTO_INCREMENT PRIMARY KEY,
    `payroll_frequency` ENUM('Weekly', 'Semi-Monthly', 'Monthly', 'Annual') NOT NULL,
    `period_name` VARCHAR(255) NOT NULL,
    `period_code` VARCHAR(50) NOT NULL UNIQUE,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `cutoff_date` DATE NOT NULL,
    `pay_date` DATE NOT NULL,
    `status` ENUM('Open', 'Closed', 'Locked') DEFAULT 'Open',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_start_date` (`start_date`),
    KEY `idx_end_date` (`end_date`),
    KEY `idx_pay_date` (`pay_date`),
    KEY `idx_status` (`status`),
    KEY `idx_period_code` (`period_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 5. PAYROLL RUNS TABLE
-- ====================================================================
CREATE TABLE `payroll_runs` (
    `payroll_run_id` INT AUTO_INCREMENT PRIMARY KEY,
    `calendar_id` INT NOT NULL,
    `run_number` VARCHAR(50) NOT NULL UNIQUE,
    `run_date` DATE NOT NULL,
    `payroll_frequency` ENUM('Weekly', 'Semi-Monthly', 'Monthly', 'Annual') NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `pay_date` DATE NOT NULL,
    `total_employees` INT DEFAULT 0,
    `total_gross_pay` DECIMAL(15, 2) DEFAULT 0.00,
    `total_deductions` DECIMAL(15, 2) DEFAULT 0.00,
    `total_net_pay` DECIMAL(15, 2) DEFAULT 0.00,
    `status` ENUM('Draft', 'Validated', 'Approved', 'Locked', 'Cancelled') DEFAULT 'Draft',
    `validated_by` INT NULL,
    `validated_at` TIMESTAMP NULL,
    `approved_by` INT NULL,
    `approved_at` TIMESTAMP NULL,
    `locked_at` TIMESTAMP NULL,
    `notes` TEXT,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_calendar_id` (`calendar_id`),
    KEY `idx_run_number` (`run_number`),
    KEY `idx_status` (`status`),
    KEY `idx_period_start` (`period_start`),
    KEY `idx_period_end` (`period_end`),
    UNIQUE KEY `unique_period_frequency` (`period_start`, `period_end`, `payroll_frequency`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_validated_by` (`validated_by`),
    KEY `idx_approved_by` (`approved_by`),
    FOREIGN KEY (`calendar_id`) REFERENCES `payroll_calendar` (`calendar_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign keys to users table if it exists (run separately if users table exists)
-- ALTER TABLE `payroll_runs` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT;
-- ALTER TABLE `payroll_runs` ADD FOREIGN KEY (`validated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
-- ALTER TABLE `payroll_runs` ADD FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

-- ====================================================================
-- 6. PAYROLL RUN EMPLOYEES TABLE
-- ====================================================================
CREATE TABLE `payroll_run_employees` (
    `run_employee_id` INT AUTO_INCREMENT PRIMARY KEY,
    `payroll_run_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `regular_hours` DECIMAL(8, 2) DEFAULT 0.00,
    `overtime_hours` DECIMAL(8, 2) DEFAULT 0.00,
    `night_differential_hours` DECIMAL(8, 2) DEFAULT 0.00,
    `holiday_hours` DECIMAL(8, 2) DEFAULT 0.00,
    `basic_pay` DECIMAL(12, 2) DEFAULT 0.00,
    `gross_pay` DECIMAL(12, 2) DEFAULT 0.00,
    `total_deductions` DECIMAL(12, 2) DEFAULT 0.00,
    `net_pay` DECIMAL(12, 2) DEFAULT 0.00,
    `status` ENUM('Pending', 'Calculated', 'Validated', 'Approved') DEFAULT 'Pending',
    `validation_errors` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_payroll_run_id` (`payroll_run_id`),
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_status` (`status`),
    UNIQUE KEY `unique_run_employee` (`payroll_run_id`, `employee_id`),
    FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`payroll_run_id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 7. PAYROLL EARNINGS TABLE
-- ====================================================================
CREATE TABLE `payroll_earnings` (
    `earning_id` INT AUTO_INCREMENT PRIMARY KEY,
    `run_employee_id` INT NOT NULL,
    `payroll_run_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `earning_type` VARCHAR(100) NOT NULL,
    `earning_name` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `is_taxable` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_run_employee_id` (`run_employee_id`),
    KEY `idx_payroll_run_id` (`payroll_run_id`),
    KEY `idx_employee_id` (`employee_id`),
    FOREIGN KEY (`run_employee_id`) REFERENCES `payroll_run_employees` (`run_employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`payroll_run_id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 8. PAYROLL DEDUCTIONS TABLE
-- ====================================================================
CREATE TABLE `payroll_deductions` (
    `deduction_id` INT AUTO_INCREMENT PRIMARY KEY,
    `run_employee_id` INT NOT NULL,
    `payroll_run_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `deduction_type` VARCHAR(100) NOT NULL,
    `deduction_name` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `employer_contribution` DECIMAL(12, 2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_run_employee_id` (`run_employee_id`),
    KEY `idx_payroll_run_id` (`payroll_run_id`),
    KEY `idx_employee_id` (`employee_id`),
    FOREIGN KEY (`run_employee_id`) REFERENCES `payroll_run_employees` (`run_employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`payroll_run_id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 9. PAYSLIPS TABLE
-- ====================================================================
CREATE TABLE `payslips` (
    `payslip_id` INT AUTO_INCREMENT PRIMARY KEY,
    `payroll_run_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `run_employee_id` INT NOT NULL,
    `payslip_number` VARCHAR(50) NOT NULL UNIQUE,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `pay_date` DATE NOT NULL,
    `basic_pay` DECIMAL(12, 2) DEFAULT 0.00,
    `gross_pay` DECIMAL(12, 2) DEFAULT 0.00,
    `total_deductions` DECIMAL(12, 2) DEFAULT 0.00,
    `net_pay` DECIMAL(12, 2) DEFAULT 0.00,
    `status` ENUM('Draft', 'Generated', 'Issued', 'Reissued') DEFAULT 'Draft',
    `issued_at` TIMESTAMP NULL,
    `pdf_path` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_payroll_run_id` (`payroll_run_id`),
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_payslip_number` (`payslip_number`),
    KEY `idx_period_start` (`period_start`),
    KEY `idx_period_end` (`period_end`),
    FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`payroll_run_id`) ON DELETE RESTRICT,
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`run_employee_id`) REFERENCES `payroll_run_employees` (`run_employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 10. PAYSLIP REISSUES TABLE
-- ====================================================================
CREATE TABLE `payslip_reissues` (
    `reissue_id` INT AUTO_INCREMENT PRIMARY KEY,
    `payslip_id` INT NOT NULL,
    `reason` TEXT NOT NULL,
    `reissued_by` INT NOT NULL,
    `reissued_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_payslip_id` (`payslip_id`),
    KEY `idx_reissued_by` (`reissued_by`),
    FOREIGN KEY (`payslip_id`) REFERENCES `payslips` (`payslip_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key to users table if it exists (run separately if users table exists)
-- ALTER TABLE `payslip_reissues` ADD FOREIGN KEY (`reissued_by`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT;

-- ====================================================================
-- SAMPLE DATA INSERTION
-- ====================================================================

-- Insert Payroll Calendar (Sample Monthly periods for 2024)
INSERT INTO `payroll_calendar` (`payroll_frequency`, `period_name`, `period_code`, `start_date`, `end_date`, `cutoff_date`, `pay_date`, `status`) VALUES
('Monthly', 'January 2024', '2024-01', '2024-01-01', '2024-01-31', '2024-01-25', '2024-02-05', 'Closed'),
('Monthly', 'February 2024', '2024-02', '2024-02-01', '2024-02-29', '2024-02-25', '2024-03-05', 'Closed'),
('Monthly', 'March 2024', '2024-03', '2024-03-01', '2024-03-31', '2024-03-25', '2024-04-05', 'Closed'),
('Monthly', 'April 2024', '2024-04', '2024-04-01', '2024-04-30', '2024-04-25', '2024-05-05', 'Closed'),
('Monthly', 'May 2024', '2024-05', '2024-05-01', '2024-05-31', '2024-05-25', '2024-06-05', 'Closed'),
('Monthly', 'June 2024', '2024-06', '2024-06-01', '2024-06-30', '2024-06-25', '2024-07-05', 'Closed'),
('Monthly', 'July 2024', '2024-07', '2024-07-01', '2024-07-31', '2024-07-25', '2024-08-05', 'Closed'),
('Monthly', 'August 2024', '2024-08', '2024-08-01', '2024-08-31', '2024-08-25', '2024-09-05', 'Closed'),
('Monthly', 'September 2024', '2024-09', '2024-09-01', '2024-09-30', '2024-09-25', '2024-10-05', 'Closed'),
('Monthly', 'October 2024', '2024-10', '2024-10-01', '2024-10-31', '2024-10-25', '2024-11-05', 'Closed'),
('Monthly', 'November 2024', '2024-11', '2024-11-01', '2024-11-30', '2024-11-25', '2024-12-05', 'Closed'),
('Monthly', 'December 2024', '2024-12', '2024-12-01', '2024-12-31', '2024-12-25', '2025-01-05', 'Open'),
('Semi-Monthly', 'Jan 1-15 2026', '2026-01-A', '2026-01-01', '2026-01-15', '2026-01-15', '2026-01-22', 'Open'),
('Semi-Monthly', 'Jan 16-30 2026', '2026-01-B', '2026-01-16', '2026-01-30', '2026-01-30', '2026-02-07', 'Open'),
('Semi-Monthly', 'Feb 1-15 2026', '2026-02-A', '2026-02-01', '2026-02-15', '2026-02-15', '2026-02-22', 'Open'),
('Semi-Monthly', 'Feb 16-28 2026', '2026-02-B', '2026-02-16', '2026-02-28', '2026-02-28', '2026-03-07', 'Open'),
('Monthly', 'January 2026', '2026-01', '2026-01-01', '2026-01-31', '2026-01-25', '2026-02-05', 'Open'),
('Monthly', 'February 2026', '2026-02', '2026-02-01', '2026-02-28', '2026-02-25', '2026-03-05', 'Open'),
('Monthly', 'March 2026', '2026-03', '2026-03-01', '2026-03-31', '2026-03-25', '2026-04-05', 'Open');

-- Insert Employee Salaries (Sample data for first 5 employees)
INSERT INTO `employee_salaries` (`employee_id`, `salary_type`, `basic_rate`, `salary_grade_id`, `tax_status`, `dependents_count`, `bank_name`, `bank_account_number`, `payout_method`, `payroll_eligible`, `effective_date`) VALUES
(1, 'Monthly', 85000.00, 4, 'Married', 2, 'Bank of America', '1234567890', 'Bank Transfer', TRUE, '2020-01-15'),
(2, 'Monthly', 45000.00, 2, 'Single', 0, 'Chase Bank', '2345678901', 'Bank Transfer', TRUE, '2021-06-01'),
(3, 'Monthly', 75000.00, 4, 'Married', 1, 'Wells Fargo', '3456789012', 'Bank Transfer', TRUE, '2019-03-20'),
(4, 'Monthly', 30000.00, 1, 'Single', 0, 'Citibank', '4567890123', 'Bank Transfer', TRUE, '2022-01-10'),
(5, 'Monthly', 60000.00, 3, 'Married', 3, 'Bank of America', '5678901234', 'Bank Transfer', TRUE, '2018-11-01');

-- Insert Employee Allowances (Sample)
INSERT INTO `employee_allowances` (`employee_id`, `allowance_type`, `allowance_name`, `amount`, `is_taxable`, `effective_date`, `status`) VALUES
(1, 'Transportation', 'Transportation Allowance', 5000.00, TRUE, '2020-01-15', 'Active'),
(1, 'Meal', 'Meal Allowance', 3000.00, TRUE, '2020-01-15', 'Active'),
(2, 'Transportation', 'Transportation Allowance', 3000.00, TRUE, '2021-06-01', 'Active'),
(3, 'Transportation', 'Transportation Allowance', 4000.00, TRUE, '2019-03-20', 'Active'),
(3, 'Housing', 'Housing Allowance', 10000.00, TRUE, '2019-03-20', 'Active'),
(4, 'Transportation', 'Transportation Allowance', 2000.00, TRUE, '2022-01-10', 'Active'),
(5, 'Transportation', 'Transportation Allowance', 4000.00, TRUE, '2018-11-01', 'Active');

-- Insert Employee Deduction Rules (Sample - Government contributions)
INSERT INTO `employee_deduction_rules` (`employee_id`, `deduction_type`, `deduction_name`, `amount`, `percentage`, `calculation_base`, `priority_order`, `effective_date`, `status`) VALUES
(1, 'SSS', 'SSS Contribution', NULL, 11.00, 'Basic Pay', 1, '2020-01-15', 'Active'),
(1, 'PhilHealth', 'PhilHealth Contribution', NULL, 3.00, 'Basic Pay', 2, '2020-01-15', 'Active'),
(1, 'PagIBIG', 'Pag-IBIG Contribution', NULL, 2.00, 'Basic Pay', 3, '2020-01-15', 'Active'),
(1, 'Tax', 'Withholding Tax', NULL, NULL, 'Gross Pay', 4, '2020-01-15', 'Active'),
(2, 'SSS', 'SSS Contribution', NULL, 11.00, 'Basic Pay', 1, '2021-06-01', 'Active'),
(2, 'PhilHealth', 'PhilHealth Contribution', NULL, 3.00, 'Basic Pay', 2, '2021-06-01', 'Active'),
(2, 'PagIBIG', 'Pag-IBIG Contribution', NULL, 2.00, 'Basic Pay', 3, '2021-06-01', 'Active'),
(2, 'Tax', 'Withholding Tax', NULL, NULL, 'Gross Pay', 4, '2021-06-01', 'Active');
