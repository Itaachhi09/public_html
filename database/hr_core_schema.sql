-- ====================================================================
-- HR Core Module - Complete Database Schema
-- Database: public_html
-- ====================================================================

-- Drop existing tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS `onboarding_checklists`;
DROP TABLE IF EXISTS `employee_documents`;
DROP TABLE IF EXISTS `employee_movements`;
DROP TABLE IF EXISTS `employee_assignments`;
DROP TABLE IF EXISTS `employee_shifts`;
DROP TABLE IF EXISTS `work_schedules`;
DROP TABLE IF EXISTS `shifts`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `job_titles`;
DROP TABLE IF EXISTS `employment_types`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `locations`;
DROP TABLE IF EXISTS `salary_grades`;

-- ====================================================================
-- 1. LOCATIONS TABLE
-- ====================================================================
CREATE TABLE `locations` (
    `location_id` INT AUTO_INCREMENT PRIMARY KEY,
    `location_code` VARCHAR(50) NOT NULL UNIQUE,
    `location_name` VARCHAR(255) NOT NULL,
    `location_type` ENUM('Head Office', 'Branch', 'Factory', 'Warehouse', 'Remote') DEFAULT 'Branch',
    `facility_name` VARCHAR(255),
    `address` TEXT,
    `city` VARCHAR(100),
    `state` VARCHAR(100),
    `country` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `contact_number` VARCHAR(20),
    `phone` VARCHAR(20),
    `email` VARCHAR(100),
    `manager_id` INT,
    `payroll_eligible` BOOLEAN DEFAULT TRUE,
    `scheduling_enabled` BOOLEAN DEFAULT TRUE,
    `allow_overtime` BOOLEAN DEFAULT TRUE,
    `allow_night_shift` BOOLEAN DEFAULT TRUE,
    `notes` TEXT,
    `status` ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_status` (`status`),
    KEY `idx_location_code` (`location_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 2. DEPARTMENTS TABLE
-- ====================================================================
CREATE TABLE `departments` (
    `department_id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_name` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255),
    `code` VARCHAR(50),
    `head_id` INT,
    `parent_id` INT,
    `description` TEXT,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`parent_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 3. SALARY GRADES TABLE
-- ====================================================================
CREATE TABLE `salary_grades` (
    `salary_grade_id` INT AUTO_INCREMENT PRIMARY KEY,
    `grade_code` VARCHAR(50) NOT NULL UNIQUE,
    `grade_name` VARCHAR(255) NOT NULL,
    `min_salary` DECIMAL(12, 2),
    `max_salary` DECIMAL(12, 2),
    `base_allowance` DECIMAL(12, 2),
    `description` TEXT,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 4. JOB TITLES TABLE
-- ====================================================================
CREATE TABLE `job_titles` (
    `job_title_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `department_id` INT,
    `salary_grade_id` INT,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_department_id` (`department_id`),
    KEY `idx_salary_grade_id` (`salary_grade_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
    FOREIGN KEY (`salary_grade_id`) REFERENCES `salary_grades` (`salary_grade_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 5. EMPLOYMENT TYPES TABLE
-- ====================================================================
CREATE TABLE `employment_types` (
    `employment_type_id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `probation_period_days` INT DEFAULT 90,
    `contract_duration_months` INT,
    `benefits_eligible` BOOLEAN DEFAULT TRUE,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 6. EMPLOYEES TABLE
-- ====================================================================
CREATE TABLE `employees` (
    `employee_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `employee_code` VARCHAR(50) NOT NULL UNIQUE,
    `first_name` VARCHAR(100) NOT NULL,
    `middle_name` VARCHAR(100),
    `last_name` VARCHAR(100) NOT NULL,
    `date_of_birth` DATE,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Other',
    `marital_status` ENUM('Single', 'Married', 'Divorced', 'Widowed') DEFAULT 'Single',
    `email` VARCHAR(100) UNIQUE,
    `phone` VARCHAR(20),
    `address` TEXT,
    `city` VARCHAR(100),
    `state` VARCHAR(100),
    `postal_code` VARCHAR(20),
    `country` VARCHAR(100),
    `nationality` VARCHAR(100),
    `job_title_id` INT,
    `department_id` INT,
    `employment_type_id` INT,
    `location_id` INT,
    `supervisor_id` INT,
    `date_of_joining` DATE NOT NULL,
    `employment_status` ENUM('Active', 'On Leave', 'Suspended', 'Terminated') DEFAULT 'Active',
    `last_working_day` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_code` (`employee_code`),
    KEY `idx_department_id` (`department_id`),
    KEY `idx_job_title_id` (`job_title_id`),
    KEY `idx_employment_type_id` (`employment_type_id`),
    KEY `idx_location_id` (`location_id`),
    KEY `idx_supervisor_id` (`supervisor_id`),
    KEY `idx_employment_status` (`employment_status`),
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
    FOREIGN KEY (`job_title_id`) REFERENCES `job_titles` (`job_title_id`) ON DELETE SET NULL,
    FOREIGN KEY (`employment_type_id`) REFERENCES `employment_types` (`employment_type_id`) ON DELETE SET NULL,
    FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL,
    FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update department head_id with foreign key after employees table is created
ALTER TABLE `departments` 
ADD FOREIGN KEY (`head_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

-- ====================================================================
-- 7. SHIFTS TABLE
-- ====================================================================
CREATE TABLE `shifts` (
    `shift_id` INT AUTO_INCREMENT PRIMARY KEY,
    `shift_name` VARCHAR(100) NOT NULL,
    `shift_code` VARCHAR(50) NOT NULL UNIQUE,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `duration_hours` DECIMAL(5, 2),
    `break_hours` DECIMAL(5, 2) DEFAULT 1.0,
    `description` TEXT,
    `is_night_shift` BOOLEAN DEFAULT FALSE,
    `shift_type` VARCHAR(50),
    `overtime_eligible` BOOLEAN DEFAULT TRUE,
    `required_staff_count` INT,
    `status` VARCHAR(50) DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_status` (`status`),
    KEY `idx_is_night_shift` (`is_night_shift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 8. WORK SCHEDULES TABLE
-- ====================================================================
CREATE TABLE `work_schedules` (
    `schedule_id` INT AUTO_INCREMENT PRIMARY KEY,
    `schedule_name` VARCHAR(255) NOT NULL,
    `schedule_type` ENUM('Fixed', 'Rotating', 'Flexible', 'Part-time') DEFAULT 'Fixed',
    `shift_pattern` VARCHAR(100),
    `hours_per_day` DECIMAL(5, 2),
    `work_days_per_week` INT DEFAULT 5,
    `weekly_hours` DECIMAL(5, 2),
    `has_night_shift` BOOLEAN DEFAULT FALSE,
    `is_trainee` BOOLEAN DEFAULT FALSE,
    `description` TEXT,
    `status` VARCHAR(50) DEFAULT 'Active',
    `start_date` DATE,
    `end_date` DATE,
    `required_count` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_status` (`status`),
    KEY `idx_has_night_shift` (`has_night_shift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 9. EMPLOYEE SHIFTS JUNCTION TABLE
-- ====================================================================
CREATE TABLE `employee_shifts` (
    `employee_shift_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `shift_id` INT NOT NULL,
    `status` VARCHAR(50) DEFAULT 'Active',
    `assigned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `effective_from` DATE,
    `effective_to` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_shift_id` (`shift_id`),
    KEY `idx_status` (`status`),
    UNIQUE KEY `unique_emp_shift` (`employee_id`, `shift_id`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 10. EMPLOYEE ASSIGNMENTS TABLE (for schedule assignments)
-- ====================================================================
CREATE TABLE `employee_assignments` (
    `assignment_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `schedule_id` INT NOT NULL,
    `status` VARCHAR(50) DEFAULT 'Active',
    `assigned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `effective_from` DATE,
    `effective_to` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_schedule_id` (`schedule_id`),
    KEY `idx_status` (`status`),
    UNIQUE KEY `unique_emp_schedule` (`employee_id`, `schedule_id`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`schedule_id`) REFERENCES `work_schedules` (`schedule_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 11. EMPLOYEE MOVEMENTS TABLE
-- ====================================================================
CREATE TABLE `employee_movements` (
    `movement_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `movement_type` ENUM('Promotion', 'Transfer', 'Secondment', 'Demotion', 'Status Change') NOT NULL,
    `effective_date` DATE NOT NULL,
    `from_job_title_id` INT,
    `to_job_title_id` INT,
    `from_department_id` INT,
    `to_department_id` INT,
    `from_location_id` INT,
    `to_location_id` INT,
    `from_salary` DECIMAL(12, 2),
    `to_salary` DECIMAL(12, 2),
    `reason` TEXT,
    `status` ENUM('Pending', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
    `approved_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_effective_date` (`effective_date`),
    KEY `idx_status` (`status`),
    KEY `idx_movement_type` (`movement_type`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_job_title_id`) REFERENCES `job_titles` (`job_title_id`) ON DELETE SET NULL,
    FOREIGN KEY (`to_job_title_id`) REFERENCES `job_titles` (`job_title_id`) ON DELETE SET NULL,
    FOREIGN KEY (`from_department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
    FOREIGN KEY (`to_department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
    FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL,
    FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 12. EMPLOYEE DOCUMENTS TABLE
-- ====================================================================
CREATE TABLE `employee_documents` (
    `document_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `document_type` VARCHAR(100) NOT NULL,
    `document_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500),
    `issue_date` DATE,
    `expiry_date` DATE,
    `remarks` TEXT,
    `uploaded_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_document_type` (`document_type`),
    KEY `idx_expiry_date` (`expiry_date`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- 13. ONBOARDING CHECKLISTS TABLE
-- ====================================================================
CREATE TABLE `onboarding_checklists` (
    `onboarding_checklist_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `checklist_item` VARCHAR(255) NOT NULL,
    `responsible_department` VARCHAR(100),
    `due_date` DATE,
    `completed_date` DATE,
    `completed_by` INT,
    `status` ENUM('Pending', 'In Progress', 'Completed', 'Overdue') DEFAULT 'Pending',
    `remarks` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_status` (`status`),
    KEY `idx_due_date` (`due_date`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- SAMPLE DATA INSERTION
-- ====================================================================

-- Insert Locations
INSERT INTO `locations` (`location_code`, `location_name`, `location_type`, `address`, `city`, `state`, `country`, `postal_code`, `status`) VALUES
('LOC001', 'Head Office', 'Head Office', '123 Main Street', 'New York', 'NY', 'USA', '10001', 'active'),
('LOC002', 'Branch Office', 'Branch', '456 Oak Avenue', 'Los Angeles', 'CA', 'USA', '90001', 'active'),
('LOC003', 'Manufacturing Plant', 'Factory', '789 Industrial Road', 'Chicago', 'IL', 'USA', '60601', 'active'),
('LOC004', 'Distribution Center', 'Warehouse', '321 Logistics Lane', 'Houston', 'TX', 'USA', '77001', 'active');

-- Insert Departments
INSERT INTO `departments` (`department_name`, `code`, `description`, `status`) VALUES
('Human Resources', 'HR', 'Human Resources Department', 'Active'),
('Operations', 'OPS', 'Operations Department', 'Active'),
('Finance', 'FIN', 'Finance Department', 'Active'),
('Sales', 'SAL', 'Sales Department', 'Active'),
('IT', 'IT', 'Information Technology Department', 'Active');

-- Insert Salary Grades
INSERT INTO `salary_grades` (`grade_code`, `grade_name`, `min_salary`, `max_salary`, `base_allowance`, `status`) VALUES
('SG001', 'Entry Level', 25000.00, 35000.00, 2500.00, 'Active'),
('SG002', 'Mid Level', 35000.00, 55000.00, 4000.00, 'Active'),
('SG003', 'Senior Level', 55000.00, 85000.00, 6000.00, 'Active'),
('SG004', 'Management', 85000.00, 120000.00, 8000.00, 'Active'),
('SG005', 'Executive', 120000.00, 200000.00, 12000.00, 'Active');

-- Insert Job Titles
INSERT INTO `job_titles` (`title`, `description`, `department_id`, `salary_grade_id`, `status`) VALUES
('HR Manager', 'Manages HR operations and employee relations', 1, 4, 'Active'),
('HR Executive', 'Handles recruitment and employee development', 1, 2, 'Active'),
('Operations Manager', 'Oversees day-to-day operations', 2, 4, 'Active'),
('Operations Associate', 'Supports operations team', 2, 1, 'Active'),
('Finance Manager', 'Manages financial operations', 3, 4, 'Active'),
('Accountant', 'Handles accounting tasks', 3, 2, 'Active'),
('Sales Manager', 'Leads sales team', 4, 4, 'Active'),
('Sales Executive', 'Manages sales operations', 4, 2, 'Active'),
('IT Manager', 'Manages IT infrastructure', 5, 4, 'Active'),
('Software Developer', 'Develops software applications', 5, 3, 'Active');

-- Insert Employment Types
INSERT INTO `employment_types` (`type_name`, `description`, `probation_period_days`, `contract_duration_months`, `benefits_eligible`, `status`) VALUES
('Full-time', 'Full-time permanent employment', 90, NULL, 1, 'Active'),
('Part-time', 'Part-time employment', 60, NULL, 0, 'Active'),
('Contract', 'Contract-based employment', 30, 12, 0, 'Active'),
('Probation', 'Probationary period employment', 90, NULL, 1, 'Active'),
('Intern', 'Internship program', 0, 6, 0, 'Active');

-- Insert Employees (sample data)
INSERT INTO `employees` (`employee_code`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `marital_status`, `email`, `phone`, `city`, `country`, `job_title_id`, `department_id`, `employment_type_id`, `location_id`, `date_of_joining`, `employment_status`) VALUES
('EMP001', 'John', 'Michael', 'Smith', '1985-05-15', 'Male', 'Married', 'john.smith@company.com', '555-0101', 'New York', 'USA', 1, 1, 1, 1, '2020-01-15', 'Active'),
('EMP002', 'Sarah', 'Jane', 'Johnson', '1990-03-22', 'Female', 'Single', 'sarah.johnson@company.com', '555-0102', 'New York', 'USA', 2, 1, 1, 1, '2021-06-01', 'Active'),
('EMP003', 'Michael', 'David', 'Brown', '1988-07-10', 'Male', 'Married', 'michael.brown@company.com', '555-0103', 'Los Angeles', 'USA', 3, 2, 1, 2, '2019-03-20', 'Active'),
('EMP004', 'Emily', 'Rose', 'Davis', '1992-11-30', 'Female', 'Single', 'emily.davis@company.com', '555-0104', 'Chicago', 'USA', 4, 2, 1, 3, '2022-01-10', 'Active'),
('EMP005', 'Robert', 'James', 'Wilson', '1986-09-05', 'Male', 'Divorced', 'robert.wilson@company.com', '555-0105', 'New York', 'USA', 5, 3, 1, 1, '2018-11-01', 'Active'),
('EMP006', 'Jessica', 'Anne', 'Martinez', '1994-02-14', 'Female', 'Single', 'jessica.martinez@company.com', '555-0106', 'Houston', 'USA', 6, 3, 1, 4, '2023-05-15', 'Active'),
('EMP007', 'David', 'Christopher', 'Garcia', '1987-12-08', 'Male', 'Married', 'david.garcia@company.com', '555-0107', 'Los Angeles', 'USA', 7, 4, 1, 2, '2020-08-20', 'Active'),
('EMP008', 'Amanda', 'Lynn', 'Rodriguez', '1991-04-19', 'Female', 'Married', 'amanda.rodriguez@company.com', '555-0108', 'New York', 'USA', 8, 4, 1, 1, '2021-10-01', 'Active'),
('EMP009', 'Chris', 'Paul', 'Taylor', '1989-06-25', 'Male', 'Single', 'chris.taylor@company.com', '555-0109', 'Chicago', 'USA', 9, 5, 1, 3, '2019-02-10', 'Active'),
('EMP010', 'Lisa', 'Marie', 'Anderson', '1993-08-16', 'Female', 'Single', 'lisa.anderson@company.com', '555-0110', 'New York', 'USA', 10, 5, 1, 1, '2022-03-15', 'Active');

-- Insert Shifts
INSERT INTO `shifts` (`shift_name`, `shift_code`, `start_time`, `end_time`, `duration_hours`, `break_hours`, `description`, `is_night_shift`, `status`) VALUES
('Morning Shift', 'SHIFT_MORNING', '08:00:00', '16:00:00', 8.0, 1.0, 'Standard morning shift 8AM-4PM', 0, 'Active'),
('Afternoon Shift', 'SHIFT_AFTERNOON', '13:00:00', '21:00:00', 8.0, 1.0, 'Standard afternoon shift 1PM-9PM', 0, 'Active'),
('Night Shift', 'SHIFT_NIGHT', '21:00:00', '05:00:00', 8.0, 1.0, 'Standard night shift 9PM-5AM', 1, 'Active'),
('Extended Shift', 'SHIFT_EXTENDED', '07:00:00', '18:00:00', 11.0, 1.0, 'Extended shift 7AM-6PM', 0, 'Active'),
('Flexible Shift', 'SHIFT_FLEX', '09:00:00', '17:00:00', 8.0, 0.5, 'Flexible working hours', 0, 'Active');

-- Insert Work Schedules
INSERT INTO `work_schedules` (`schedule_name`, `schedule_type`, `shift_pattern`, `hours_per_day`, `work_days_per_week`, `weekly_hours`, `has_night_shift`, `description`, `status`) VALUES
('Standard 5-Day', 'Fixed', '8-8-8-8-8', 8.0, 5, 40.0, 0, 'Standard 5-day work week', 'Active'),
('Rotating Shifts', 'Rotating', 'Morning-Afternoon-Night', 8.0, 7, 56.0, 1, 'Rotating shift pattern', 'Active'),
('Extended Hours', 'Fixed', '11-11-5.5-5.5', 8.25, 5, 41.25, 0, 'Extended hours schedule', 'Active'),
('Part-time Schedule', 'Flexible', '4-4-4-4-4', 4.0, 5, 20.0, 0, 'Part-time 20 hours per week', 'Active'),
('Night Shift Only', 'Fixed', '8-8-8-8-8', 8.0, 5, 40.0, 1, 'Night shift schedule', 'Active');

-- Insert Employee Shifts assignments
INSERT INTO `employee_shifts` (`employee_id`, `shift_id`, `status`, `effective_from`) VALUES
(1, 1, 'Active', '2020-01-15'),
(2, 1, 'Active', '2021-06-01'),
(3, 1, 'Active', '2019-03-20'),
(4, 2, 'Active', '2022-01-10'),
(5, 1, 'Active', '2018-11-01'),
(6, 3, 'Active', '2023-05-15'),
(7, 1, 'Active', '2020-08-20'),
(8, 2, 'Active', '2021-10-01'),
(9, 3, 'Active', '2019-02-10'),
(10, 1, 'Active', '2022-03-15');

-- Insert Employee Assignments
INSERT INTO `employee_assignments` (`employee_id`, `schedule_id`, `status`, `effective_from`) VALUES
(1, 1, 'Active', '2020-01-15'),
(2, 1, 'Active', '2021-06-01'),
(3, 1, 'Active', '2019-03-20'),
(4, 2, 'Active', '2022-01-10'),
(5, 1, 'Active', '2018-11-01'),
(6, 5, 'Active', '2023-05-15'),
(7, 3, 'Active', '2020-08-20'),
(8, 2, 'Active', '2021-10-01'),
(9, 5, 'Active', '2019-02-10'),
(10, 1, 'Active', '2022-03-15');

-- Insert Employee Movements (sample promotions/transfers)
INSERT INTO `employee_movements` (`employee_id`, `movement_type`, `effective_date`, `from_job_title_id`, `to_job_title_id`, `from_department_id`, `to_department_id`, `from_salary`, `to_salary`, `reason`, `status`) VALUES
(2, 'Promotion', '2023-07-01', 2, 1, 1, 1, 35000.00, 45000.00, 'Promotion to HR Manager', 'Approved'),
(4, 'Transfer', '2023-05-15', 4, 3, 2, 2, 30000.00, 30000.00, 'Internal transfer', 'Approved'),
(6, 'Promotion', '2024-01-01', 6, 5, 3, 3, 40000.00, 60000.00, 'Promotion to Finance Manager', 'Pending');

-- Insert Employee Documents
INSERT INTO `employee_documents` (`employee_id`, `document_type`, `document_name`, `issue_date`, `expiry_date`, `remarks`) VALUES
(1, 'National ID', 'National Identification Card', '2015-03-20', '2035-03-20', 'Valid national ID'),
(2, 'Passport', 'International Passport', '2018-06-15', '2028-06-15', 'Valid for international travel'),
(3, 'License', 'Driver License', '2017-04-10', '2027-04-10', 'Valid driver license'),
(4, 'Certification', 'Professional Certification', '2021-09-01', '2024-09-01', 'Professional certification - expiring soon'),
(5, 'Contract', 'Employment Contract', '2018-11-01', NULL, 'Original employment contract'),
(6, 'National ID', 'National Identification Card', '2014-02-28', '2024-02-28', 'Renewal needed'),
(7, 'Passport', 'International Passport', '2019-11-12', '2029-11-12', 'Valid passport'),
(8, 'License', 'Driver License', '2018-07-20', '2028-07-20', 'Valid'),
(9, 'Certification', 'IT Professional Certification', '2022-01-15', '2025-01-15', 'Active certification'),
(10, 'Contract', 'Employment Contract', '2022-03-15', NULL, 'Current employment contract');

-- Insert Onboarding Checklists
INSERT INTO `onboarding_checklists` (`employee_id`, `checklist_item`, `responsible_department`, `due_date`, `status`, `remarks`) VALUES
(2, 'IT Equipment Setup', 'IT', '2021-06-05', 'Completed', 'Laptop and phone provided'),
(4, 'Office Tour', 'Operations', '2022-01-12', 'Completed', 'Familiarized with office'),
(4, 'HR Orientation', 'Human Resources', '2022-01-15', 'Completed', 'Completed HR induction'),
(4, 'Department Training', 'Operations', '2022-02-01', 'Completed', 'Completed operations training'),
(6, 'IT Equipment Setup', 'IT', '2023-05-20', 'In Progress', 'Pending laptop setup'),
(6, 'Office Tour', 'Operations', '2023-05-17', 'Completed', 'Completed'),
(10, 'IT Equipment Setup', 'IT', '2022-03-20', 'Completed', 'Setup completed'),
(10, 'HR Orientation', 'Human Resources', '2022-03-22', 'Completed', 'Orientation completed'),
(10, 'Department Training', 'IT', '2022-04-05', 'Completed', 'Training completed');
