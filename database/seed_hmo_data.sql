-- ====================================================================
-- HMO Module - Schema and Mock Data
-- Integrates with HR Core employees and top 5 Philippines HMO providers
-- Database: public_html
-- ====================================================================

-- Drop existing HMO tables if they exist
DROP TABLE IF EXISTS `hmo_premium_payments`;
DROP TABLE IF EXISTS `hmo_claims`;
DROP TABLE IF EXISTS `hmo_life_events`;
DROP TABLE IF EXISTS `hmo_enrollment_dependents`;
DROP TABLE IF EXISTS `hmo_plan_coverage_details`;
DROP TABLE IF EXISTS `employee_hmo_enrollments`;
DROP TABLE IF EXISTS `hmo_plans`;
DROP TABLE IF EXISTS `hmo_providers`;

-- ====================================================================
-- 1. HMO PROVIDERS TABLE - Top 5 Philippines HMO Companies
-- ====================================================================
CREATE TABLE `hmo_providers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_code` VARCHAR(50) NOT NULL UNIQUE,
    `provider_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `head_office_address` VARCHAR(500),
    `contact_phone` VARCHAR(20),
    `contact_email` VARCHAR(100),
    `website` VARCHAR(255),
    `accreditation_number` VARCHAR(100),
    `established_year` INT,
    `total_members` INT,
    `network_hospitals` INT,
    `provider_status` ENUM('Active', 'Inactive', 'Suspended') DEFAULT 'Active',
    `payment_terms_days` INT DEFAULT 30,
    `employer_premium_contribution_rate` DECIMAL(5, 2) DEFAULT 75.00,
    `employee_premium_contribution_rate` DECIMAL(5, 2) DEFAULT 25.00,
    `is_active` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_provider_code` (`provider_code`),
    KEY `idx_status` (`provider_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 2. HMO PLANS TABLE - Plan Details
-- ====================================================================
CREATE TABLE `hmo_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_id` INT NOT NULL,
    `plan_code` VARCHAR(50) NOT NULL UNIQUE,
    `plan_name` VARCHAR(255) NOT NULL,
    `plan_type` ENUM('Basic', 'Standard', 'Premium', 'Executive') DEFAULT 'Standard',
    `description` TEXT,
    `annual_premium_per_employee` DECIMAL(12, 2) NOT NULL,
    `annual_premium_per_dependent` DECIMAL(12, 2),
    `monthly_premium` DECIMAL(12, 2),
    `out_of_pocket_limit` DECIMAL(12, 2),
    `deductible_amount` DECIMAL(12, 2),
    `copay_percentage` INT DEFAULT 20,
    `coverage_details` JSON,
    `in_network_doctors` INT,
    `in_network_hospitals` INT,
    `is_active` TINYINT DEFAULT 1,
    `plan_launch_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_provider_id` (`provider_id`),
    KEY `idx_plan_code` (`plan_code`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`provider_id`) REFERENCES `hmo_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 3. EMPLOYEE HMO ENROLLMENTS TABLE
-- ====================================================================
CREATE TABLE `employee_hmo_enrollments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `provider_id` INT NOT NULL,
    `enrollment_date` DATE NOT NULL,
    `effective_date` DATE NOT NULL,
    `coverage_end_date` DATE,
    `renewal_date` DATE,
    `termination_date` DATE,
    `enrollment_status` ENUM('pending', 'active', 'waiting_period', 'suspended', 'terminated', 'expired') DEFAULT 'active',
    `coverage_type` ENUM('employee_only', 'with_dependents') DEFAULT 'employee_only',
    `waiting_period_days` INT DEFAULT 30,
    `waiting_period_start_date` DATE,
    `waiting_period_end_date` DATE,
    `is_primary_enrollment` TINYINT DEFAULT 1,
    `employee_premium_amount` DECIMAL(12, 2),
    `employer_share_amount` DECIMAL(12, 2),
    `total_premium_amount` DECIMAL(12, 2),
    `suspension_reason` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_plan_id` (`plan_id`),
    KEY `idx_provider_id` (`provider_id`),
    KEY `idx_enrollment_status` (`enrollment_status`),
    KEY `idx_effective_date` (`effective_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 4. HMO ENROLLMENT DEPENDENTS TABLE
-- ====================================================================
CREATE TABLE `hmo_enrollment_dependents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `enrollment_id` INT NOT NULL,
    `dependent_name` VARCHAR(255) NOT NULL,
    `relationship` ENUM('Spouse', 'Child', 'Parent', 'Sibling', 'Other') NOT NULL,
    `date_of_birth` DATE,
    `gender` ENUM('Male', 'Female', 'Other'),
    `is_active` TINYINT DEFAULT 1,
    `added_date` DATE,
    `removed_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_enrollment_id` (`enrollment_id`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`enrollment_id`) REFERENCES `employee_hmo_enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 5. HMO CLAIMS TABLE
-- ====================================================================
CREATE TABLE `hmo_claims` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `enrollment_id` INT NOT NULL,
    `employee_id` INT NOT NULL,
    `dependent_id` INT,
    `plan_id` INT NOT NULL,
    `provider_id` INT NOT NULL,
    `claim_number` VARCHAR(50) NOT NULL UNIQUE,
    `service_type` VARCHAR(100),
    `service_date` DATE NOT NULL,
    `hospital_name` VARCHAR(255),
    `doctor_name` VARCHAR(255),
    `claim_amount` DECIMAL(12, 2),
    `approved_amount` DECIMAL(12, 2),
    `claim_status` ENUM('pending', 'approved', 'rejected', 'paid', 'appeal') DEFAULT 'pending',
    `approval_date` DATE,
    `payment_date` DATE,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_claim_number` (`claim_number`),
    KEY `idx_claim_status` (`claim_status`),
    KEY `idx_service_date` (`service_date`),
    FOREIGN KEY (`enrollment_id`) REFERENCES `employee_hmo_enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 6. HMO LIFE EVENTS TABLE
-- ====================================================================
CREATE TABLE `hmo_life_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `enrollment_id` INT,
    `event_type` ENUM('Birth', 'Marriage', 'Divorce', 'Adoption', 'Dependent_Loss', 'Retirement', 'Rehire') NOT NULL,
    `event_date` DATE NOT NULL,
    `description` TEXT,
    `change_window_start` DATE,
    `change_window_end` DATE,
    `status` ENUM('pending', 'approved', 'rejected', 'processed') DEFAULT 'pending',
    `processed_at` DATETIME,
    `supporting_documents` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_event_date` (`event_date`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 7. HMO PREMIUM PAYMENTS TABLE
-- ====================================================================
CREATE TABLE `hmo_premium_payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `enrollment_id` INT NOT NULL,
    `provider_id` INT NOT NULL,
    `payment_month` DATE NOT NULL,
    `premium_amount` DECIMAL(12, 2),
    `employee_contribution` DECIMAL(12, 2),
    `employer_contribution` DECIMAL(12, 2),
    `payment_status` ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    `payment_date` DATE,
    `payment_method` VARCHAR(50),
    `reference_number` VARCHAR(100),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_enrollment_id` (`enrollment_id`),
    KEY `idx_provider_id` (`provider_id`),
    KEY `idx_payment_month` (`payment_month`),
    KEY `idx_payment_status` (`payment_status`),
    FOREIGN KEY (`enrollment_id`) REFERENCES `employee_hmo_enrollments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- ====================================================================
-- MOCK DATA INSERTS
-- ====================================================================
-- ====================================================================

-- ====================================================================
-- Insert Top 5 Philippines HMO Providers
-- ====================================================================
INSERT INTO `hmo_providers` 
(`provider_code`, `provider_name`, `description`, `head_office_address`, `contact_phone`, `contact_email`, `website`, `accreditation_number`, `established_year`, `total_members`, `network_hospitals`, `provider_status`, `employer_premium_contribution_rate`) 
VALUES
('PHH001', 'Philhealth', 'Philippine Health Insurance Corporation - Government HMO', '1125 East Avenue, Diliman, Quezon City', '+63 2 8441-1500', 'inquiry@philhealth.gov.ph', 'www.philhealth.gov.ph', 'GOV-001', 1995, 950000, 850, 'Active', 80.00),
('ATE001', 'Aetna Health Philippines', 'International HMO provider with comprehensive coverage', '32nd Floor Asean Tower, 6 ADB Avenue, Ortigas Center, Pasig', '+63 2 7706-0001', 'claims@aetna.com.ph', 'www.aetna.com.ph', 'PRC-2024-001', 2005, 180000, 320, 'Active', 75.00),
('INS001', 'Insular Health', 'Leading local HMO provider in Philippines', '39F Philamlife Tower, 8767 Paseo de Roxas, Makati City', '+63 2 8741-1111', 'support@insularhealth.com.ph', 'www.insularhealth.com.ph', 'PRC-2024-002', 1998, 220000, 410, 'Active', 75.00),
('MED001', 'Medicard', 'Healthcare provider with extensive hospital network', 'Medicard Center, 123 Uniform Street, Ayala Avenue, Makati City', '+63 2 8815-2000', 'info@medicard.com.ph', 'www.medicard.com.ph', 'PRC-2024-003', 2001, 195000, 380, 'Active', 75.00),
('STL001', 'St. Luke''s Health Plan', 'Premium healthcare network provider', 'Heritage House, 231 Sen. Gil Puyat Avenue, Makati City', '+63 2 8888-5555', 'support@stlukeshealthplan.com.ph', 'www.stlukeshealthplan.com.ph', 'PRC-2024-004', 2003, 165000, 285, 'Active', 75.00);

-- ====================================================================
-- Insert Best Plans for Each Provider
-- ====================================================================
INSERT INTO `hmo_plans` 
(`provider_id`, `plan_code`, `plan_name`, `plan_type`, `description`, `annual_premium_per_employee`, `annual_premium_per_dependent`, `monthly_premium`, `out_of_pocket_limit`, `deductible_amount`, `copay_percentage`, `in_network_doctors`, `in_network_hospitals`, `is_active`, `plan_launch_date`) 
VALUES
-- Philhealth Plans
(1, 'PHH-STD-2026', 'Philhealth Standard Plan 2026', 'Standard', 'Basic government HMO coverage with essential services', 15000.00, 7500.00, 1250.00, 50000.00, 0.00, 20, 3500, 420, 1, '2026-01-01'),
(1, 'PHH-PREM-2026', 'Philhealth Premium Plan 2026', 'Premium', 'Enhanced coverage with specialist access and preventive care', 24000.00, 12000.00, 2000.00, 75000.00, 0.00, 15, 5200, 520, 1, '2026-01-01'),

-- Aetna Plans
(2, 'ATE-EXEC-2026', 'Aetna Executive Plan', 'Executive', 'Comprehensive coverage for executives with wellness programs', 45000.00, 22500.00, 3750.00, 150000.00, 5000.00, 10, 8900, 650, 1, '2026-01-01'),
(2, 'ATE-CORP-2026', 'Aetna Corporate Plan', 'Premium', 'Full-featured corporate plan with international coverage', 32000.00, 16000.00, 2666.67, 100000.00, 2500.00, 15, 7200, 580, 1, '2026-01-01'),

-- Insular Health Plans
(3, 'INS-PREM-2026', 'Insular Premium 2026', 'Premium', 'Complete health coverage with extensive hospital network', 38000.00, 19000.00, 3166.67, 120000.00, 3000.00, 15, 7800, 610, 1, '2026-01-01'),
(3, 'INS-STD-2026', 'Insular Standard 2026', 'Standard', 'Comprehensive standard plan with good hospital coverage', 22000.00, 11000.00, 1833.33, 70000.00, 1500.00, 20, 5500, 450, 1, '2026-01-01'),

-- Medicard Plans
(4, 'MED-MAXCARE', 'Medicard MaxCare Plan', 'Premium', 'Maximum health benefits with preventive and wellness programs', 40000.00, 20000.00, 3333.33, 130000.00, 3500.00, 15, 8100, 625, 1, '2026-01-01'),
(4, 'MED-BASIC', 'Medicard Basic Plan', 'Standard', 'Affordable basic coverage with essential hospital benefits', 18000.00, 9000.00, 1500.00, 60000.00, 1000.00, 20, 4800, 380, 1, '2026-01-01'),

-- St. Luke's Plans
(5, 'STL-PREMIUM', 'St. Luke''s Premium Plus', 'Premium', 'Comprehensive premium plan with exclusive St. Luke''s network', 42000.00, 21000.00, 3500.00, 140000.00, 4000.00, 15, 7500, 590, 1, '2026-01-01'),
(5, 'STL-DELUXE', 'St. Luke''s Deluxe Plan', 'Executive', 'Top-tier plan with executive benefits and concierge service', 55000.00, 27500.00, 4583.33, 180000.00, 5000.00, 10, 9200, 680, 1, '2026-01-01');

-- ====================================================================
-- Insert Employee HMO Enrollments (Match with HR Core Employees)
-- ====================================================================
INSERT INTO `employee_hmo_enrollments` 
(`employee_id`, `plan_id`, `provider_id`, `enrollment_date`, `effective_date`, `coverage_end_date`, `renewal_date`, `enrollment_status`, `coverage_type`, `employee_premium_amount`, `employer_share_amount`, `total_premium_amount`, `is_primary_enrollment`) 
VALUES
-- Employee 1 (John Smith) - Aetna Executive Plan
(1, 3, 2, '2024-01-15', '2024-02-01', '2025-01-31', '2025-01-15', 'active', 'with_dependents', 11250.00, 33750.00, 45000.00, 1),

-- Employee 2 (Sarah Johnson) - Insular Premium
(2, 5, 3, '2024-06-01', '2024-06-15', '2025-06-14', '2025-06-01', 'active', 'employee_only', 9500.00, 28500.00, 38000.00, 1),

-- Employee 3 (Michael Brown) - Aetna Corporate
(3, 4, 2, '2024-03-20', '2024-04-01', '2025-03-31', '2025-03-20', 'active', 'with_dependents', 8000.00, 24000.00, 32000.00, 1),

-- Employee 4 (Emily Davis) - Medicard MaxCare
(4, 7, 4, '2024-01-10', '2024-01-25', '2025-01-24', '2025-01-10', 'active', 'employee_only', 10000.00, 30000.00, 40000.00, 1),

-- Employee 5 (Robert Wilson) - Philhealth Premium
(5, 2, 1, '2024-11-01', '2024-11-15', '2025-11-14', '2025-11-01', 'active', 'with_dependents', 6000.00, 18000.00, 24000.00, 1),

-- Employee 6 (Jessica Martinez) - St. Luke's Premium
(6, 9, 5, '2024-05-15', '2024-05-20', '2025-05-19', '2025-05-15', 'active', 'employee_only', 10500.00, 31500.00, 42000.00, 1),

-- Employee 7 (David Garcia) - Insular Standard
(7, 6, 3, '2024-08-20', '2024-09-01', '2025-08-31', '2025-08-20', 'active', 'with_dependents', 5500.00, 16500.00, 22000.00, 1),

-- Employee 8 (Amanda Rodriguez) - Medicard Basic
(8, 8, 4, '2024-10-01', '2024-10-15', '2025-10-14', '2025-10-01', 'active', 'employee_only', 4500.00, 13500.00, 18000.00, 1),

-- Employee 9 (Chris Taylor) - St. Luke's Deluxe
(9, 10, 5, '2024-02-10', '2024-02-20', '2025-02-19', '2025-02-10', 'active', 'with_dependents', 13750.00, 41250.00, 55000.00, 1),

-- Employee 10 (Lisa Anderson) - Philhealth Standard
(10, 1, 1, '2024-03-15', '2024-03-20', '2025-03-19', '2025-03-15', 'active', 'employee_only', 3750.00, 11250.00, 15000.00, 1);

-- ====================================================================
-- Insert Enrollment Dependents (Sample family members)
-- ====================================================================
INSERT INTO `hmo_enrollment_dependents` 
(`enrollment_id`, `dependent_name`, `relationship`, `date_of_birth`, `gender`, `is_active`, `added_date`) 
VALUES
-- Employee 1 (John Smith) dependents
(1, 'Maria Santos Smith', 'Spouse', '1987-08-22', 'Female', 1, '2024-01-15'),
(1, 'Juan Smith Jr.', 'Child', '2015-05-10', 'Male', 1, '2024-01-15'),

-- Employee 3 (Michael Brown) dependents
(3, 'Patricia Anne Brown', 'Spouse', '1990-12-15', 'Female', 1, '2024-03-20'),

-- Employee 5 (Robert Wilson) dependents
(5, 'Catherine Wilson', 'Spouse', '1988-04-20', 'Female', 1, '2024-11-01'),
(5, 'Robert Wilson Jr.', 'Child', '2018-07-30', 'Male', 1, '2024-11-01'),

-- Employee 7 (David Garcia) dependents
(7, 'Rosa Garcia', 'Spouse', '1989-09-14', 'Female', 1, '2024-08-20'),

-- Employee 9 (Chris Taylor) dependents
(9, 'Michelle Taylor', 'Spouse', '1991-06-08', 'Female', 1, '2024-02-10'),
(9, 'Emma Taylor', 'Child', '2019-11-25', 'Female', 1, '2024-02-10'),
(9, 'David Taylor', 'Child', '2021-03-17', 'Male', 1, '2024-02-10');

-- ====================================================================
-- Insert Sample HMO Claims
-- ====================================================================
INSERT INTO `hmo_claims` 
(`enrollment_id`, `employee_id`, `plan_id`, `provider_id`, `claim_number`, `service_type`, `service_date`, `hospital_name`, `doctor_name`, `claim_amount`, `approved_amount`, `claim_status`, `approval_date`, `notes`) 
VALUES
-- Claims for Employee 1
(1, 1, 3, 2, 'CLM-202401-001', 'General Consultation', '2024-01-18', 'Makati Medical Center', 'Dr. Juan Cruz', 2500.00, 2500.00, 'paid', '2024-01-20', 'General check-up'),
(1, 1, 3, 2, 'CLM-202402-001', 'Laboratory Services', '2024-02-15', 'Makati Medical Center', 'Dr. Juan Cruz', 5000.00, 5000.00, 'paid', '2024-02-17', 'Annual blood work'),

-- Claims for Employee 2
(2, 2, 5, 3, 'CLM-202406-001', 'Specialty Consultation', '2024-06-20', 'The Medical City', 'Dr. Maria Santos', 3500.00, 3500.00, 'approved', '2024-06-22', 'Dermatology consult'),

-- Claims for Employee 3
(3, 3, 4, 2, 'CLM-202404-001', 'Dental Services', '2024-04-10', 'Aetna Dental Partner', 'Dr. Antonio Garcia', 4000.00, 3200.00, 'paid', '2024-04-12', 'Tooth extraction - 20% copay applied'),

-- Claims for Employee 5
(5, 5, 2, 1, 'CLM-202411-001', 'Hospitalization', '2024-11-20', 'Philippine General Hospital', 'Dr. Pedro Reyes', 25000.00, 25000.00, 'approved', '2024-11-24', 'Surgery with 3-day admission'),

-- Claims for Employee 8
(8, 8, 8, 4, 'CLM-202410-001', 'General Consultation', '2024-10-18', 'Medicard Partner Hospital', 'Dr. Rosa Gomez', 1500.00, 1500.00, 'paid', '2024-10-20', 'Regular check-up'),

-- Claims for Employee 10
(10, 10, 1, 1, 'CLM-202403-001', 'Outpatient Services', '2024-03-25', 'Government Hospital', 'Dr. Luis Fernandez', 2000.00, 2000.00, 'paid', '2024-03-27', 'Minor illness treatment');

-- ====================================================================
-- Insert Sample Premium Payments
-- ====================================================================
INSERT INTO `hmo_premium_payments` 
(`enrollment_id`, `provider_id`, `payment_month`, `premium_amount`, `employee_contribution`, `employer_contribution`, `payment_status`, `payment_date`, `payment_method`, `reference_number`) 
VALUES
-- Employee 1 - Aetna
(1, 2, '2024-01-31', 45000.00, 11250.00, 33750.00, 'paid', '2024-01-30', 'Bank Transfer', 'ATE-202401-001'),
(1, 2, '2024-02-28', 45000.00, 11250.00, 33750.00, 'paid', '2024-02-28', 'Bank Transfer', 'ATE-202402-001'),

-- Employee 2 - Insular
(2, 3, '2024-06-30', 38000.00, 9500.00, 28500.00, 'paid', '2024-06-30', 'Bank Transfer', 'INS-202406-001'),
(2, 3, '2024-07-31', 38000.00, 9500.00, 28500.00, 'paid', '2024-07-31', 'Bank Transfer', 'INS-202407-001'),

-- Employee 3 - Aetna
(3, 2, '2024-04-30', 32000.00, 8000.00, 24000.00, 'paid', '2024-04-30', 'Bank Transfer', 'ATE-202404-001'),
(3, 2, '2024-05-31', 32000.00, 8000.00, 24000.00, 'paid', '2024-05-31', 'Bank Transfer', 'ATE-202405-001'),

-- Employee 4 - Medicard
(4, 4, '2024-01-31', 40000.00, 10000.00, 30000.00, 'paid', '2024-01-30', 'Bank Transfer', 'MED-202401-001'),
(4, 4, '2024-02-29', 40000.00, 10000.00, 30000.00, 'paid', '2024-02-29', 'Bank Transfer', 'MED-202402-001'),

-- Employee 5 - Philhealth
(5, 1, '2024-11-30', 24000.00, 6000.00, 18000.00, 'paid', '2024-11-29', 'Bank Transfer', 'PHH-202411-001'),
(5, 1, '2024-12-31', 24000.00, 6000.00, 18000.00, 'pending', NULL, 'Bank Transfer', NULL),

-- Employee 6 - St. Luke's
(6, 5, '2024-05-31', 42000.00, 10500.00, 31500.00, 'paid', '2024-05-31', 'Bank Transfer', 'STL-202405-001'),
(6, 5, '2024-06-30', 42000.00, 10500.00, 31500.00, 'paid', '2024-06-30', 'Bank Transfer', 'STL-202406-001'),

-- Employee 7 - Insular
(7, 3, '2024-09-30', 22000.00, 5500.00, 16500.00, 'paid', '2024-09-30', 'Bank Transfer', 'INS-202409-001'),
(7, 3, '2024-10-31', 22000.00, 5500.00, 16500.00, 'paid', '2024-10-31', 'Bank Transfer', 'INS-202410-001'),

-- Employee 8 - Medicard
(8, 4, '2024-10-31', 18000.00, 4500.00, 13500.00, 'paid', '2024-10-31', 'Bank Transfer', 'MED-202410-001'),
(8, 4, '2024-11-30', 18000.00, 4500.00, 13500.00, 'pending', NULL, 'Bank Transfer', NULL),

-- Employee 9 - St. Luke's
(9, 5, '2024-02-28', 55000.00, 13750.00, 41250.00, 'paid', '2024-02-28', 'Bank Transfer', 'STL-202402-001'),
(9, 5, '2024-03-31', 55000.00, 13750.00, 41250.00, 'paid', '2024-03-31', 'Bank Transfer', 'STL-202403-001'),

-- Employee 10 - Philhealth
(10, 1, '2024-03-31', 15000.00, 3750.00, 11250.00, 'paid', '2024-03-31', 'Bank Transfer', 'PHH-202403-001'),
(10, 1, '2024-04-30', 15000.00, 3750.00, 11250.00, 'paid', '2024-04-30', 'Bank Transfer', 'PHH-202404-001');

-- ====================================================================
-- Insert Sample Life Events
-- ====================================================================
INSERT INTO `hmo_life_events` 
(`employee_id`, `enrollment_id`, `event_type`, `event_date`, `description`, `change_window_start`, `change_window_end`, `status`) 
VALUES
-- Life event for Employee 1 (Marriage)
(1, 1, 'Marriage', '2023-06-15', 'Employee married on June 15, 2023. Added spouse to coverage.', '2023-06-01', '2023-07-15', 'processed'),

-- Life event for Employee 2 (Birth)
(2, 2, 'Birth', '2024-07-20', 'First child born. Added newborn to health plan coverage.', '2024-07-05', '2024-08-20', 'processed'),

-- Life event for Employee 5 (Birth)
(5, 5, 'Birth', '2023-11-10', 'Second child born. Added to HMO coverage.', '2023-11-01', '2023-12-10', 'processed');

-- ====================================================================
-- Create indexes for better performance
-- ====================================================================
CREATE INDEX idx_hmo_provider_active ON hmo_providers(is_active);
CREATE INDEX idx_hmo_plan_active ON hmo_plans(is_active, provider_id);
CREATE INDEX idx_hmo_enrollment_employee ON employee_hmo_enrollments(employee_id, enrollment_status);
CREATE INDEX idx_hmo_enrollment_provider ON employee_hmo_enrollments(provider_id, effective_date);
CREATE INDEX idx_hmo_claims_employee ON hmo_claims(employee_id, claim_status);
CREATE INDEX idx_hmo_claims_date ON hmo_claims(service_date, claim_status);

-- ====================================================================
-- Summary Statistics
-- ====================================================================
-- Total Employees Enrolled in HMO: 10
-- Providers: 5 (All Philippines-based Top HMO Providers)
-- Plans: 8 (Best plans from each provider)
-- Enrollments: 10 (All HR Core employees matched)
-- Dependents: 9 (Family members added where applicable)
-- Claims: 7 (Sample claims of varying types and statuses)
-- Premium Payments: 17 entries (2 months per enrollment, mostly paid)
-- Life Events: 3 (Marriage, Birth events)
-- Enrollment Status: All 10 employees have ACTIVE enrollments
-- ====================================================================
