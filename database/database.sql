-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 07:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `public_html`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefit_definitions`
--

CREATE TABLE `benefit_definitions` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `taxable` tinyint(1) DEFAULT 0 COMMENT '1=taxable',
  `eligible_roles` varchar(500) NOT NULL DEFAULT 'All' COMMENT 'Comma-separated e.g. ER Staff,Nurse,Doctor',
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL COMMENT 'NULL = no end date',
  `attach_to` enum('duty','role') NOT NULL DEFAULT 'role' COMMENT 'Benefit attaches to duty or role',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `benefit_definitions`
--

INSERT INTO `benefit_definitions` (`id`, `code`, `name`, `description`, `taxable`, `eligible_roles`, `effective_from`, `effective_to`, `attach_to`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'FREE_MEALS_TID', 'Free Meals TID', 'Free meals three times daily on duty', 0, 'ER Staff,Nurse,Doctor', '2026-02-08', NULL, 'duty', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `compensation_approval_requests`
--

CREATE TABLE `compensation_approval_requests` (
  `id` int(11) NOT NULL,
  `request_type` enum('salary_above_band','double_pay_tagging','manual_adjustment','policy_exception') NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'e.g. salary_adjustment, incentive_event',
  `reference_id` int(11) DEFAULT NULL COMMENT 'id of related record',
  `description` text NOT NULL,
  `status` enum('requested','under_review','approved','rejected') NOT NULL DEFAULT 'requested',
  `requested_by` int(11) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compensation_version_history`
--

CREATE TABLE `compensation_version_history` (
  `id` int(11) NOT NULL,
  `entity_type` enum('pay_component','salary_band','policy','employee_assignment') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `version_number` int(11) NOT NULL,
  `effective_date` date NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('draft','approved') NOT NULL DEFAULT 'approved',
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Only one active per entity_type+entity_id',
  `snapshot_json` text DEFAULT NULL COMMENT 'Optional snapshot of entity state at this version',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `head_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `name`, `code`, `head_id`, `parent_id`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Human Resources', NULL, 'HR', NULL, NULL, 'Human Resources Department', 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(2, 'Operations', NULL, 'OPS', NULL, NULL, 'Operations Department', 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(3, 'Finance', NULL, 'FIN', NULL, NULL, 'Finance Department', 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(4, 'Sales', NULL, 'SAL', NULL, NULL, 'Sales Department', 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(5, 'IT', NULL, 'IT', NULL, NULL, 'Information Technology Department', 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(6, 'Human Resources', NULL, 'HR', NULL, NULL, 'HR and Admin Operations', 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(7, 'Finance', NULL, 'FIN', NULL, NULL, 'Financial Management', 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(8, 'Operations', NULL, 'OPS', NULL, NULL, 'Operations Management', 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(9, 'Clinical', NULL, 'CLIN', NULL, NULL, 'Clinical Services', 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) DEFAULT NULL,
  `batch_reference` varchar(100) NOT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `format` varchar(10) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `record_count` int(11) DEFAULT NULL,
  `status` enum('pending','generated','transmitted','confirmed','failed','cancelled') DEFAULT 'pending',
  `generated_by` int(11) DEFAULT NULL,
  `transmitted_by` int(11) DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL,
  `generated_date` timestamp NULL DEFAULT NULL,
  `transmitted_date` timestamp NULL DEFAULT NULL,
  `confirmed_date` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `employee_code` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT 'Other',
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT 'Single',
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `job_title_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `employment_type_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `date_of_joining` date NOT NULL,
  `employment_status` enum('Active','On Leave','Suspended','Terminated') DEFAULT 'Active',
  `last_working_day` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `employee_code`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `gender`, `marital_status`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `country`, `nationality`, `job_title_id`, `department_id`, `employment_type_id`, `location_id`, `supervisor_id`, `date_of_joining`, `employment_status`, `last_working_day`, `created_at`, `updated_at`) VALUES
(1, NULL, 'EMP001', 'John', 'Michael', 'Smith', '1985-05-15', 'Male', 'Married', 'john.smith@company.com', '555-0101', NULL, 'New York', NULL, NULL, 'USA', NULL, 1, 1, 1, 1, NULL, '2020-01-15', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(2, NULL, 'EMP002', 'Sarah', 'Jane', 'Johnson', '1990-03-22', 'Female', 'Single', 'sarah.johnson@company.com', '555-0102', NULL, 'New York', NULL, NULL, 'USA', NULL, 2, 1, 1, 1, NULL, '2021-06-01', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(3, NULL, 'EMP003', 'Michael', 'David', 'Brown', '1988-07-10', 'Male', 'Married', 'michael.brown@company.com', '555-0103', NULL, 'Los Angeles', NULL, NULL, 'USA', NULL, 3, 2, 1, 2, NULL, '2019-03-20', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(4, NULL, 'EMP004', 'Emily', 'Rose', 'Davis', '1992-11-30', 'Female', 'Single', 'emily.davis@company.com', '555-0104', NULL, 'Chicago', NULL, NULL, 'USA', NULL, 4, 2, 1, 3, NULL, '2022-01-10', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(5, NULL, 'EMP005', 'Robert', 'James', 'Wilson', '1986-09-05', 'Male', 'Divorced', 'robert.wilson@company.com', '555-0105', NULL, 'New York', NULL, NULL, 'USA', NULL, 5, 3, 1, 1, NULL, '2018-11-01', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(6, NULL, 'EMP006', 'Jessica', 'Anne', 'Martinez', '1994-02-14', 'Female', 'Single', 'jessica.martinez@company.com', '555-0106', NULL, 'Houston', NULL, NULL, 'USA', NULL, 6, 3, 1, 4, NULL, '2023-05-15', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(7, NULL, 'EMP007', 'David', 'Christopher', 'Garcia', '1987-12-08', 'Male', 'Married', 'david.garcia@company.com', '555-0107', NULL, 'Los Angeles', NULL, NULL, 'USA', NULL, 7, 4, 1, 2, NULL, '2020-08-20', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(8, NULL, 'EMP008', 'Amanda', 'Lynn', 'Rodriguez', '1991-04-19', 'Female', 'Married', 'amanda.rodriguez@company.com', '555-0108', NULL, 'New York', NULL, NULL, 'USA', NULL, 8, 4, 1, 1, NULL, '2021-10-01', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(9, NULL, 'EMP009', 'Chris', 'Paul', 'Taylor', '1989-06-25', 'Male', 'Single', 'chris.taylor@company.com', '555-0109', NULL, 'Chicago', NULL, NULL, 'USA', NULL, 9, 5, 1, 3, NULL, '2019-02-10', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(10, NULL, 'EMP010', 'Lisa', 'Marie', 'Anderson', '1993-08-16', 'Female', 'Single', 'lisa.anderson@company.com', '555-0110', NULL, 'New York', NULL, NULL, 'USA', NULL, 10, 5, 1, 1, NULL, '2022-03-15', 'Active', NULL, '2026-02-05 16:53:44', '2026-02-05 16:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `employee_assignments`
--

CREATE TABLE `employee_assignments` (
  `assignment_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_assignments`
--

INSERT INTO `employee_assignments` (`assignment_id`, `employee_id`, `schedule_id`, `status`, `assigned_date`, `effective_from`, `effective_to`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Active', '2026-02-05 16:53:45', '2020-01-15', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(2, 2, 1, 'Active', '2026-02-05 16:53:45', '2021-06-01', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(3, 3, 1, 'Active', '2026-02-05 16:53:45', '2019-03-20', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(4, 4, 2, 'Active', '2026-02-05 16:53:45', '2022-01-10', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(5, 5, 1, 'Active', '2026-02-05 16:53:45', '2018-11-01', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(6, 6, 5, 'Active', '2026-02-05 16:53:45', '2023-05-15', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(7, 7, 3, 'Active', '2026-02-05 16:53:45', '2020-08-20', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(8, 8, 2, 'Active', '2026-02-05 16:53:45', '2021-10-01', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(9, 9, 5, 'Active', '2026-02-05 16:53:45', '2019-02-10', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(10, 10, 1, 'Active', '2026-02-05 16:53:45', '2022-03-15', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `employee_contract_assignments`
--

CREATE TABLE `employee_contract_assignments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL COMMENT 'NULL = ongoing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `document_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_documents`
--

INSERT INTO `employee_documents` (`document_id`, `employee_id`, `document_type`, `document_name`, `file_path`, `issue_date`, `expiry_date`, `remarks`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'National ID', 'National Identification Card', NULL, '2015-03-20', '2035-03-20', 'Valid national ID', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(2, 2, 'Passport', 'International Passport', NULL, '2018-06-15', '2028-06-15', 'Valid for international travel', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(3, 3, 'License', 'Driver License', NULL, '2017-04-10', '2027-04-10', 'Valid driver license', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(4, 4, 'Certification', 'Professional Certification', NULL, '2021-09-01', '2024-09-01', 'Professional certification - expiring soon', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(5, 5, 'Contract', 'Employment Contract', NULL, '2018-11-01', NULL, 'Original employment contract', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(6, 6, 'National ID', 'National Identification Card', NULL, '2014-02-28', '2024-02-28', 'Renewal needed', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(7, 7, 'Passport', 'International Passport', NULL, '2019-11-12', '2029-11-12', 'Valid passport', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(8, 8, 'License', 'Driver License', NULL, '2018-07-20', '2028-07-20', 'Valid', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(9, 9, 'Certification', 'IT Professional Certification', NULL, '2022-01-15', '2025-01-15', 'Active certification', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(10, 10, 'Contract', 'Employment Contract', NULL, '2022-03-15', NULL, 'Current employment contract', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `employee_hmo_enrollments`
--

CREATE TABLE `employee_hmo_enrollments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `effective_date` date NOT NULL,
  `coverage_end_date` date DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `enrollment_status` enum('pending','active','waiting_period','suspended','terminated','expired') DEFAULT 'active',
  `coverage_type` enum('employee_only','with_dependents') DEFAULT 'employee_only',
  `waiting_period_days` int(11) DEFAULT 30,
  `waiting_period_start_date` date DEFAULT NULL,
  `waiting_period_end_date` date DEFAULT NULL,
  `is_primary_enrollment` tinyint(4) DEFAULT 1,
  `employee_premium_amount` decimal(12,2) DEFAULT NULL,
  `employer_share_amount` decimal(12,2) DEFAULT NULL,
  `total_premium_amount` decimal(12,2) DEFAULT NULL,
  `suspension_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_hmo_enrollments`
--

INSERT INTO `employee_hmo_enrollments` (`id`, `employee_id`, `plan_id`, `provider_id`, `enrollment_date`, `effective_date`, `coverage_end_date`, `renewal_date`, `termination_date`, `enrollment_status`, `coverage_type`, `waiting_period_days`, `waiting_period_start_date`, `waiting_period_end_date`, `is_primary_enrollment`, `employee_premium_amount`, `employer_share_amount`, `total_premium_amount`, `suspension_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 2, '2024-01-15', '2024-02-01', '2025-01-31', '2025-01-15', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 11250.00, 33750.00, 45000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(2, 2, 5, 3, '2024-06-01', '2024-06-15', '2025-06-14', '2025-06-01', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 9500.00, 28500.00, 38000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(3, 3, 4, 2, '2024-03-20', '2024-04-01', '2025-03-31', '2025-03-20', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 8000.00, 24000.00, 32000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(4, 4, 7, 4, '2024-01-10', '2024-01-25', '2025-01-24', '2025-01-10', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 10000.00, 30000.00, 40000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(5, 5, 2, 1, '2024-11-01', '2024-11-15', '2025-11-14', '2025-11-01', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 6000.00, 18000.00, 24000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(6, 6, 9, 5, '2024-05-15', '2024-05-20', '2025-05-19', '2025-05-15', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 10500.00, 31500.00, 42000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(7, 7, 6, 3, '2024-08-20', '2024-09-01', '2025-08-31', '2025-08-20', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 5500.00, 16500.00, 22000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(8, 8, 8, 4, '2024-10-01', '2024-10-15', '2025-10-14', '2025-10-01', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 4500.00, 13500.00, 18000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(9, 9, 10, 5, '2024-02-10', '2024-02-20', '2025-02-19', '2025-02-10', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 13750.00, 41250.00, 55000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(10, 10, 1, 1, '2024-03-15', '2024-03-20', '2025-03-19', '2025-03-15', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 3750.00, 11250.00, 15000.00, NULL, '2026-02-07 17:37:12', '2026-02-07 17:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `employee_movements`
--

CREATE TABLE `employee_movements` (
  `movement_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `movement_type` enum('Promotion','Transfer','Secondment','Demotion','Status Change') NOT NULL,
  `effective_date` date NOT NULL,
  `from_job_title_id` int(11) DEFAULT NULL,
  `to_job_title_id` int(11) DEFAULT NULL,
  `from_department_id` int(11) DEFAULT NULL,
  `to_department_id` int(11) DEFAULT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `from_salary` decimal(12,2) DEFAULT NULL,
  `to_salary` decimal(12,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Completed') DEFAULT 'Pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_movements`
--

INSERT INTO `employee_movements` (`movement_id`, `employee_id`, `movement_type`, `effective_date`, `from_job_title_id`, `to_job_title_id`, `from_department_id`, `to_department_id`, `from_location_id`, `to_location_id`, `from_salary`, `to_salary`, `reason`, `status`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'Promotion', '2023-07-01', 2, 1, 1, 1, NULL, NULL, 35000.00, 45000.00, 'Promotion to HR Manager', 'Approved', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(2, 4, 'Transfer', '2023-05-15', 4, 3, 2, 2, NULL, NULL, 30000.00, 30000.00, 'Internal transfer', 'Approved', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(3, 6, 'Promotion', '2024-01-01', 6, 5, 3, 3, NULL, NULL, 40000.00, 60000.00, 'Promotion to Finance Manager', 'Pending', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `employee_payroll_profiles`
--

CREATE TABLE `employee_payroll_profiles` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `payroll_eligible` tinyint(1) DEFAULT 1,
  `payroll_frequency` enum('Weekly','Semi-Monthly','Monthly') DEFAULT 'Monthly',
  `pay_schedule_day_1` int(11) DEFAULT 7,
  `pay_schedule_day_2` int(11) DEFAULT 22,
  `bank_account_holder` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_code` varchar(10) DEFAULT NULL,
  `tax_identification_number` varchar(50) DEFAULT NULL,
  `sss_number` varchar(50) DEFAULT NULL,
  `philhealth_number` varchar(50) DEFAULT NULL,
  `pagibig_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_salaries`
--

CREATE TABLE `employee_salaries` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `salary_type` enum('Hourly','Daily','Monthly','Annual') NOT NULL DEFAULT 'Monthly',
  `basic_rate` decimal(12,2) NOT NULL,
  `effective_date` date NOT NULL,
  `tax_status` varchar(50) DEFAULT 'Single',
  `payroll_eligible` tinyint(1) DEFAULT 1,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_shifts`
--

CREATE TABLE `employee_shifts` (
  `employee_shift_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_shifts`
--

INSERT INTO `employee_shifts` (`employee_shift_id`, `employee_id`, `shift_id`, `status`, `assigned_date`, `effective_from`, `effective_to`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Active', '2026-02-05 16:53:45', '2020-01-15', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(2, 2, 1, 'Active', '2026-02-05 16:53:45', '2021-06-01', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(3, 3, 1, 'Active', '2026-02-05 16:53:45', '2019-03-20', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(4, 4, 2, 'Active', '2026-02-05 16:53:45', '2022-01-10', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(5, 5, 1, 'Active', '2026-02-05 16:53:45', '2018-11-01', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(6, 6, 3, 'Active', '2026-02-05 16:53:45', '2023-05-15', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(7, 7, 1, 'Active', '2026-02-05 16:53:45', '2020-08-20', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(8, 8, 2, 'Active', '2026-02-05 16:53:45', '2021-10-01', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(9, 9, 3, 'Active', '2026-02-05 16:53:45', '2019-02-10', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(10, 10, 1, 'Active', '2026-02-05 16:53:45', '2022-03-15', NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `employment_types`
--

CREATE TABLE `employment_types` (
  `employment_type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `probation_period_days` int(11) DEFAULT 90,
  `contract_duration_months` int(11) DEFAULT NULL,
  `benefits_eligible` tinyint(1) DEFAULT 1,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_types`
--

INSERT INTO `employment_types` (`employment_type_id`, `type_name`, `description`, `probation_period_days`, `contract_duration_months`, `benefits_eligible`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Full-time', 'Full-time permanent employment', 90, NULL, 1, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(2, 'Part-time', 'Part-time employment', 60, NULL, 0, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(3, 'Contract', 'Contract-based employment', 30, 12, 0, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(4, 'Probation', 'Probationary period employment', 90, NULL, 1, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(5, 'Intern', 'Internship program', 0, 6, 0, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(6, 'Regular', 'Regular Full-time Employee', 90, NULL, 1, 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(7, 'Probationary', 'Probationary Employee', 90, NULL, 1, 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(8, 'Contractual', 'Contract-based Employee', 90, NULL, 1, 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `government_reports`
--

CREATE TABLE `government_reports` (
  `id` int(11) NOT NULL,
  `agency` enum('SSS','PhilHealth','Pag-IBIG','BIR','Other') NOT NULL,
  `reporting_period_start` date DEFAULT NULL,
  `reporting_period_end` date DEFAULT NULL,
  `reporting_date` date NOT NULL,
  `report_type` varchar(100) DEFAULT NULL,
  `total_employees` int(11) DEFAULT NULL,
  `total_contributions` decimal(12,2) DEFAULT NULL,
  `submission_status` enum('draft','pending','submitted','confirmed','failed') DEFAULT 'draft',
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_date` timestamp NULL DEFAULT NULL,
  `confirmation_reference` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_levels`
--

CREATE TABLE `grade_levels` (
  `id` int(11) NOT NULL,
  `pay_grade_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grade_levels`
--

INSERT INTO `grade_levels` (`id`, `pay_grade_id`, `code`, `name`, `description`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'SENIOR', 'Senior', 'Senior level for doctors', 1, 'Active', '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(2, 2, 'MID', 'Mid', 'Mid level for nurses', 1, 'Active', '2026-02-08 15:11:57', '2026-02-08 15:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_billing_adjustments`
--

CREATE TABLE `hmo_billing_adjustments` (
  `id` int(11) NOT NULL,
  `reconciliation_id` int(11) NOT NULL,
  `adjustment_type` enum('credit','debit','reversal','refund') NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `reference_discrepancy_id` int(11) DEFAULT NULL,
  `adjustment_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` varchar(100) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hmo_billing_discrepancies`
--

CREATE TABLE `hmo_billing_discrepancies` (
  `id` int(11) NOT NULL,
  `reconciliation_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `claim_id` int(11) DEFAULT NULL,
  `discrepancy_type` enum('amount_mismatch','duplicate_claim','unapproved_service','coverage_issue','missing_documentation') NOT NULL,
  `submitted_amount` decimal(12,2) DEFAULT NULL,
  `approved_amount` decimal(12,2) DEFAULT NULL,
  `variance_amount` decimal(12,2) DEFAULT NULL,
  `discrepancy_details` text DEFAULT NULL,
  `resolution_status` enum('pending','investigating','resolved','rejected') DEFAULT 'pending',
  `resolved_by` varchar(100) DEFAULT NULL,
  `resolution_date` datetime DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_billing_discrepancies`
--

INSERT INTO `hmo_billing_discrepancies` (`id`, `reconciliation_id`, `employee_id`, `claim_id`, `discrepancy_type`, `submitted_amount`, `approved_amount`, `variance_amount`, `discrepancy_details`, `resolution_status`, `resolved_by`, `resolution_date`, `resolution_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'amount_mismatch', 2500.00, 2400.00, -100.00, 'Authorized deductible applied', 'resolved', NULL, NULL, NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(2, 2, 2, NULL, 'coverage_issue', 5000.00, 4500.00, -500.00, 'Pre-authorization required for service', 'resolved', NULL, NULL, NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(3, 3, 3, NULL, 'duplicate_claim', 3500.00, 3500.00, 0.00, 'Duplicate claim detected and removed', 'resolved', NULL, NULL, NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_billing_reconciliation`
--

CREATE TABLE `hmo_billing_reconciliation` (
  `id` int(11) NOT NULL,
  `reconciliation_number` varchar(50) NOT NULL,
  `billing_month` date NOT NULL,
  `provider_id` int(11) NOT NULL,
  `total_claims_submitted` decimal(15,2) DEFAULT NULL,
  `total_approved_amount` decimal(15,2) DEFAULT NULL,
  `total_payments_made` decimal(15,2) DEFAULT NULL,
  `variance_amount` decimal(15,2) DEFAULT NULL,
  `variance_percentage` decimal(5,2) DEFAULT NULL,
  `reconciliation_status` enum('pending','in_review','discrepancies_found','resolved','approved') DEFAULT 'pending',
  `reviewed_by` varchar(100) DEFAULT NULL,
  `review_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_billing_reconciliation`
--

INSERT INTO `hmo_billing_reconciliation` (`id`, `reconciliation_number`, `billing_month`, `provider_id`, `total_claims_submitted`, `total_approved_amount`, `total_payments_made`, `variance_amount`, `variance_percentage`, `reconciliation_status`, `reviewed_by`, `review_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'REC-PHH-202601', '2026-01-01', 1, 45000.00, 44500.00, 44500.00, -500.00, -1.11, 'approved', 'Admin', '2026-02-09 00:22:38', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(2, 'REC-ATE-202601', '2026-01-01', 2, 125000.00, 123500.00, 123500.00, -1500.00, -1.20, 'approved', 'Admin', '2026-02-09 00:22:38', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(3, 'REC-INS-202601', '2026-01-01', 3, 95000.00, 94000.00, 94000.00, -1000.00, -1.05, 'approved', 'Admin', '2026-02-09 00:22:38', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(4, 'REC-MED-202601', '2026-01-01', 4, 85000.00, 84500.00, 84500.00, -500.00, -0.59, 'approved', 'Admin', '2026-02-09 00:22:38', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(5, 'REC-STL-202601', '2026-01-01', 5, 110000.00, 108500.00, 108500.00, -1500.00, -1.36, 'approved', 'Admin', '2026-02-09 00:22:38', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_claims`
--

CREATE TABLE `hmo_claims` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `dependent_id` int(11) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `claim_number` varchar(50) NOT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `service_date` date NOT NULL,
  `hospital_name` varchar(255) DEFAULT NULL,
  `doctor_name` varchar(255) DEFAULT NULL,
  `claim_amount` decimal(12,2) DEFAULT NULL,
  `approved_amount` decimal(12,2) DEFAULT NULL,
  `claim_status` enum('pending','approved','rejected','paid','appeal') DEFAULT 'pending',
  `approval_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_claims`
--

INSERT INTO `hmo_claims` (`id`, `enrollment_id`, `employee_id`, `dependent_id`, `plan_id`, `provider_id`, `claim_number`, `service_type`, `service_date`, `hospital_name`, `doctor_name`, `claim_amount`, `approved_amount`, `claim_status`, `approval_date`, `payment_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 3, 2, 'CLM-202401-001', 'General Consultation', '2024-01-18', 'Makati Medical Center', 'Dr. Juan Cruz', 2500.00, 2500.00, 'paid', '2024-01-20', NULL, 'General check-up', '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(2, 1, 1, NULL, 3, 2, 'CLM-202402-001', 'Laboratory Services', '2024-02-15', 'Makati Medical Center', 'Dr. Juan Cruz', 5000.00, 5000.00, 'paid', '2024-02-17', NULL, 'Annual blood work', '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(3, 2, 2, NULL, 5, 3, 'CLM-202406-001', 'Specialty Consultation', '2024-06-20', 'The Medical City', 'Dr. Maria Santos', 3500.00, 3500.00, 'approved', '2024-06-22', NULL, 'Dermatology consult', '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(4, 3, 3, NULL, 4, 2, 'CLM-202404-001', 'Dental Services', '2024-04-10', 'Aetna Dental Partner', 'Dr. Antonio Garcia', 4000.00, 3200.00, 'paid', '2024-04-12', NULL, 'Tooth extraction - 20% copay applied', '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(5, 5, 5, NULL, 2, 1, 'CLM-202411-001', 'Hospitalization', '2024-11-20', 'Philippine General Hospital', 'Dr. Pedro Reyes', 25000.00, 25000.00, 'approved', '2024-11-24', NULL, 'Surgery with 3-day admission', '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(6, 8, 8, NULL, 8, 4, 'CLM-202410-001', 'General Consultation', '2024-10-18', 'Medicard Partner Hospital', 'Dr. Rosa Gomez', 1500.00, 1500.00, 'paid', '2024-10-20', NULL, 'Regular check-up', '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(7, 10, 10, NULL, 1, 1, 'CLM-202403-001', 'Outpatient Services', '2024-03-25', 'Government Hospital', 'Dr. Luis Fernandez', 2000.00, 2000.00, 'paid', '2024-03-27', NULL, 'Minor illness treatment', '2026-02-07 17:37:13', '2026-02-07 17:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_documents`
--

CREATE TABLE `hmo_documents` (
  `id` int(11) NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `document_type` enum('Insurance_Card','Member_Handbook','Benefits_Summary','Hospital_Referral','Pre_authorization','Claim_Form','Policy_Document','Other') NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `validity_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `document_status` enum('active','inactive','expired','archived') DEFAULT 'active',
  `file_path` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_documents`
--

INSERT INTO `hmo_documents` (`id`, `document_number`, `document_type`, `subject`, `employee_id`, `plan_id`, `provider_id`, `issue_date`, `validity_date`, `expiry_date`, `document_status`, `file_path`, `file_name`, `file_size`, `file_type`, `description`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 'DOC-INS-001', 'Insurance_Card', 'Aetna Insurance Card', 1, 3, 2, '2026-01-01', '2026-01-01', '2027-01-31', 'active', NULL, NULL, NULL, NULL, 'Physical insurance card issued', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(2, 'DOC-HB-001', 'Member_Handbook', 'Aetna Member Handbook 2026', 2, 4, 2, '2026-01-01', '2026-01-01', '2027-12-31', 'active', NULL, NULL, NULL, NULL, 'Comprehensive member handbook', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(3, 'DOC-BS-001', 'Benefits_Summary', 'Insular Health Benefits Summary', 3, 5, 3, '2026-01-01', '2026-01-01', '2026-12-31', 'active', NULL, NULL, NULL, NULL, 'Benefits coverage summary', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(4, 'DOC-PA-001', 'Pre_authorization', 'Surgery Pre-authorization', 4, 7, 4, '2026-01-15', '2026-01-15', '2026-03-15', 'active', NULL, NULL, NULL, NULL, 'Pre-auth for elective surgery', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(5, 'DOC-CF-001', 'Claim_Form', 'Claim Submission Form', 5, 2, 1, '2026-01-10', '2026-01-10', '2026-12-31', 'active', NULL, NULL, NULL, NULL, 'Standard claim submission form', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(6, 'DOC-PD-001', 'Policy_Document', 'Philhealth Policy Document', 6, 1, 1, '2026-01-01', '2026-01-01', '2027-12-31', 'active', NULL, NULL, NULL, NULL, 'Full policy terms and conditions', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(7, 'DOC-HR-001', 'Hospital_Referral', 'Hospital Referral Letter', 7, 6, 3, '2026-01-20', '2026-01-20', '2026-02-20', 'active', NULL, NULL, NULL, NULL, 'Referral to partner hospital', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38'),
(8, 'DOC-INS-002', 'Insurance_Card', 'Medicard Insurance Card', 8, 8, 4, '2026-01-01', '2026-01-01', '2027-01-31', 'active', NULL, NULL, NULL, NULL, 'Physical insurance card issued', NULL, '2026-02-08 16:22:38', '2026-02-08 16:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_enrollment_dependents`
--

CREATE TABLE `hmo_enrollment_dependents` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `dependent_name` varchar(255) NOT NULL,
  `relationship` enum('Spouse','Child','Parent','Sibling','Other') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `added_date` date DEFAULT NULL,
  `removed_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_enrollment_dependents`
--

INSERT INTO `hmo_enrollment_dependents` (`id`, `enrollment_id`, `dependent_name`, `relationship`, `date_of_birth`, `gender`, `is_active`, `added_date`, `removed_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'Maria Santos Smith', 'Spouse', '1987-08-22', 'Female', 1, '2024-01-15', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(2, 1, 'Juan Smith Jr.', 'Child', '2015-05-10', 'Male', 1, '2024-01-15', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(3, 3, 'Patricia Anne Brown', 'Spouse', '1990-12-15', 'Female', 1, '2024-03-20', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(4, 5, 'Catherine Wilson', 'Spouse', '1988-04-20', 'Female', 1, '2024-11-01', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(5, 5, 'Robert Wilson Jr.', 'Child', '2018-07-30', 'Male', 1, '2024-11-01', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(6, 7, 'Rosa Garcia', 'Spouse', '1989-09-14', 'Female', 1, '2024-08-20', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(7, 9, 'Michelle Taylor', 'Spouse', '1991-06-08', 'Female', 1, '2024-02-10', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(8, 9, 'Emma Taylor', 'Child', '2019-11-25', 'Female', 1, '2024-02-10', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(9, 9, 'David Taylor', 'Child', '2021-03-17', 'Male', 1, '2024-02-10', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_life_events`
--

CREATE TABLE `hmo_life_events` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `event_type` enum('Birth','Marriage','Divorce','Adoption','Dependent_Loss','Retirement','Rehire') NOT NULL,
  `event_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `change_window_start` date DEFAULT NULL,
  `change_window_end` date DEFAULT NULL,
  `status` enum('pending','approved','rejected','processed') DEFAULT 'pending',
  `processed_at` datetime DEFAULT NULL,
  `supporting_documents` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_life_events`
--

INSERT INTO `hmo_life_events` (`id`, `employee_id`, `enrollment_id`, `event_type`, `event_date`, `description`, `change_window_start`, `change_window_end`, `status`, `processed_at`, `supporting_documents`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Marriage', '2023-06-15', 'Employee married on June 15, 2023. Added spouse to coverage.', '2023-06-01', '2023-07-15', 'processed', NULL, NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(2, 2, 2, 'Birth', '2024-07-20', 'First child born. Added newborn to health plan coverage.', '2024-07-05', '2024-08-20', 'processed', NULL, NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(3, 5, 5, 'Birth', '2023-11-10', 'Second child born. Added to HMO coverage.', '2023-11-01', '2023-12-10', 'processed', NULL, NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_plans`
--

CREATE TABLE `hmo_plans` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `plan_code` varchar(50) NOT NULL,
  `plan_name` varchar(255) NOT NULL,
  `plan_type` enum('Basic','Standard','Premium','Executive') DEFAULT 'Standard',
  `description` text DEFAULT NULL,
  `annual_premium_per_employee` decimal(12,2) NOT NULL,
  `annual_premium_per_dependent` decimal(12,2) DEFAULT NULL,
  `monthly_premium` decimal(12,2) DEFAULT NULL,
  `out_of_pocket_limit` decimal(12,2) DEFAULT NULL,
  `deductible_amount` decimal(12,2) DEFAULT NULL,
  `copay_percentage` int(11) DEFAULT 20,
  `coverage_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`coverage_details`)),
  `in_network_doctors` int(11) DEFAULT NULL,
  `in_network_hospitals` int(11) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `plan_launch_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_plans`
--

INSERT INTO `hmo_plans` (`id`, `provider_id`, `plan_code`, `plan_name`, `plan_type`, `description`, `annual_premium_per_employee`, `annual_premium_per_dependent`, `monthly_premium`, `out_of_pocket_limit`, `deductible_amount`, `copay_percentage`, `coverage_details`, `in_network_doctors`, `in_network_hospitals`, `is_active`, `plan_launch_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'PHH-STD-2026', 'Philhealth Standard Plan 2026', 'Standard', 'Basic government HMO coverage with essential services', 15000.00, 7500.00, 1250.00, 50000.00, 0.00, 20, NULL, 3500, 420, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(2, 1, 'PHH-PREM-2026', 'Philhealth Premium Plan 2026', 'Premium', 'Enhanced coverage with specialist access and preventive care', 24000.00, 12000.00, 2000.00, 75000.00, 0.00, 15, NULL, 5200, 520, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(3, 2, 'ATE-EXEC-2026', 'Aetna Executive Plan', 'Executive', 'Comprehensive coverage for executives with wellness programs', 45000.00, 22500.00, 3750.00, 150000.00, 5000.00, 10, NULL, 8900, 650, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(4, 2, 'ATE-CORP-2026', 'Aetna Corporate Plan', 'Premium', 'Full-featured corporate plan with international coverage', 32000.00, 16000.00, 2666.67, 100000.00, 2500.00, 15, NULL, 7200, 580, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(5, 3, 'INS-PREM-2026', 'Insular Premium 2026', 'Premium', 'Complete health coverage with extensive hospital network', 38000.00, 19000.00, 3166.67, 120000.00, 3000.00, 15, NULL, 7800, 610, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(6, 3, 'INS-STD-2026', 'Insular Standard 2026', 'Standard', 'Comprehensive standard plan with good hospital coverage', 22000.00, 11000.00, 1833.33, 70000.00, 1500.00, 20, NULL, 5500, 450, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(7, 4, 'MED-MAXCARE', 'Medicard MaxCare Plan', 'Premium', 'Maximum health benefits with preventive and wellness programs', 40000.00, 20000.00, 3333.33, 130000.00, 3500.00, 15, NULL, 8100, 625, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(8, 4, 'MED-BASIC', 'Medicard Basic Plan', 'Standard', 'Affordable basic coverage with essential hospital benefits', 18000.00, 9000.00, 1500.00, 60000.00, 1000.00, 20, NULL, 4800, 380, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(9, 5, 'STL-PREMIUM', 'St. Luke\'s Premium Plus', 'Premium', 'Comprehensive premium plan with exclusive St. Luke\'s network', 42000.00, 21000.00, 3500.00, 140000.00, 4000.00, 15, NULL, 7500, 590, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12'),
(10, 5, 'STL-DELUXE', 'St. Luke\'s Deluxe Plan', 'Executive', 'Top-tier plan with executive benefits and concierge service', 55000.00, 27500.00, 4583.33, 180000.00, 5000.00, 10, NULL, 9200, 680, 1, '2026-01-01', '2026-02-07 17:37:12', '2026-02-07 17:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_premium_payments`
--

CREATE TABLE `hmo_premium_payments` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `payment_month` date NOT NULL,
  `premium_amount` decimal(12,2) DEFAULT NULL,
  `employee_contribution` decimal(12,2) DEFAULT NULL,
  `employer_contribution` decimal(12,2) DEFAULT NULL,
  `payment_status` enum('pending','paid','overdue','cancelled') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_premium_payments`
--

INSERT INTO `hmo_premium_payments` (`id`, `enrollment_id`, `provider_id`, `payment_month`, `premium_amount`, `employee_contribution`, `employer_contribution`, `payment_status`, `payment_date`, `payment_method`, `reference_number`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2024-01-31', 45000.00, 11250.00, 33750.00, 'paid', '2024-01-30', 'Bank Transfer', 'ATE-202401-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(2, 1, 2, '2024-02-28', 45000.00, 11250.00, 33750.00, 'paid', '2024-02-28', 'Bank Transfer', 'ATE-202402-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(3, 2, 3, '2024-06-30', 38000.00, 9500.00, 28500.00, 'paid', '2024-06-30', 'Bank Transfer', 'INS-202406-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(4, 2, 3, '2024-07-31', 38000.00, 9500.00, 28500.00, 'paid', '2024-07-31', 'Bank Transfer', 'INS-202407-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(5, 3, 2, '2024-04-30', 32000.00, 8000.00, 24000.00, 'paid', '2024-04-30', 'Bank Transfer', 'ATE-202404-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(6, 3, 2, '2024-05-31', 32000.00, 8000.00, 24000.00, 'paid', '2024-05-31', 'Bank Transfer', 'ATE-202405-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(7, 4, 4, '2024-01-31', 40000.00, 10000.00, 30000.00, 'paid', '2024-01-30', 'Bank Transfer', 'MED-202401-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(8, 4, 4, '2024-02-29', 40000.00, 10000.00, 30000.00, 'paid', '2024-02-29', 'Bank Transfer', 'MED-202402-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(9, 5, 1, '2024-11-30', 24000.00, 6000.00, 18000.00, 'paid', '2024-11-29', 'Bank Transfer', 'PHH-202411-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(10, 5, 1, '2024-12-31', 24000.00, 6000.00, 18000.00, 'pending', NULL, 'Bank Transfer', NULL, NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(11, 6, 5, '2024-05-31', 42000.00, 10500.00, 31500.00, 'paid', '2024-05-31', 'Bank Transfer', 'STL-202405-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(12, 6, 5, '2024-06-30', 42000.00, 10500.00, 31500.00, 'paid', '2024-06-30', 'Bank Transfer', 'STL-202406-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(13, 7, 3, '2024-09-30', 22000.00, 5500.00, 16500.00, 'paid', '2024-09-30', 'Bank Transfer', 'INS-202409-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(14, 7, 3, '2024-10-31', 22000.00, 5500.00, 16500.00, 'paid', '2024-10-31', 'Bank Transfer', 'INS-202410-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(15, 8, 4, '2024-10-31', 18000.00, 4500.00, 13500.00, 'paid', '2024-10-31', 'Bank Transfer', 'MED-202410-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(16, 8, 4, '2024-11-30', 18000.00, 4500.00, 13500.00, 'pending', NULL, 'Bank Transfer', NULL, NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(17, 9, 5, '2024-02-28', 55000.00, 13750.00, 41250.00, 'paid', '2024-02-28', 'Bank Transfer', 'STL-202402-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(18, 9, 5, '2024-03-31', 55000.00, 13750.00, 41250.00, 'paid', '2024-03-31', 'Bank Transfer', 'STL-202403-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(19, 10, 1, '2024-03-31', 15000.00, 3750.00, 11250.00, 'paid', '2024-03-31', 'Bank Transfer', 'PHH-202403-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13'),
(20, 10, 1, '2024-04-30', 15000.00, 3750.00, 11250.00, 'paid', '2024-04-30', 'Bank Transfer', 'PHH-202404-001', NULL, '2026-02-07 17:37:13', '2026-02-07 17:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `hmo_providers`
--

CREATE TABLE `hmo_providers` (
  `id` int(11) NOT NULL,
  `provider_code` varchar(50) NOT NULL,
  `provider_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `head_office_address` varchar(500) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `primary_contact_person` varchar(255) DEFAULT NULL,
  `primary_contact_title` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `accreditation_number` varchar(100) DEFAULT NULL,
  `established_year` int(11) DEFAULT NULL,
  `total_members` int(11) DEFAULT NULL,
  `network_hospitals` int(11) DEFAULT NULL,
  `network_size` varchar(100) DEFAULT NULL,
  `provider_status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `provider_type` enum('Corporate','Government','NGO') DEFAULT 'Corporate',
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `payment_terms_days` int(11) DEFAULT 30,
  `employer_premium_contribution_rate` decimal(5,2) DEFAULT 75.00,
  `employee_premium_contribution_rate` decimal(5,2) DEFAULT 25.00,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hmo_providers`
--

INSERT INTO `hmo_providers` (`id`, `provider_code`, `provider_name`, `description`, `head_office_address`, `contact_phone`, `contact_email`, `primary_contact_person`, `primary_contact_title`, `website`, `accreditation_number`, `established_year`, `total_members`, `network_hospitals`, `network_size`, `provider_status`, `provider_type`, `contract_start_date`, `contract_end_date`, `payment_terms_days`, `employer_premium_contribution_rate`, `employee_premium_contribution_rate`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PHH001', 'Philhealth', 'Philippine Health Insurance Corporation - Government HMO', '1125 East Avenue, Diliman, Quezon City', '+63 2 8441-1500', 'inquiry@philhealth.gov.ph', 'Dr. Maria Santos', 'Executive Vice President', 'www.philhealth.gov.ph', 'GOV-001', 1995, 950000, 850, '850+ Hospital Network', 'Active', 'Government', '2020-01-01', '2030-12-31', 30, 80.00, 25.00, 1, '2026-02-07 17:37:12', '2026-02-07 17:52:53'),
(2, 'ATE001', 'Aetna Health Philippines', 'International HMO provider with comprehensive coverage', '32nd Floor Asean Tower, 6 ADB Avenue, Ortigas Center, Pasig', '+63 2 7706-0001', 'claims@aetna.com.ph', 'Mr. James Chen', 'Country Manager', 'www.aetna.com.ph', 'PRC-2024-001', 2005, 180000, 320, '320+ Hospital Partner Network', 'Active', 'Corporate', '2022-06-01', '2027-05-31', 30, 75.00, 25.00, 1, '2026-02-07 17:37:12', '2026-02-07 17:52:53'),
(3, 'INS001', 'Insular Health', 'Leading local HMO provider in Philippines', '39F Philamlife Tower, 8767 Paseo de Roxas, Makati City', '+63 2 8741-1111', 'support@insularhealth.com.ph', 'Ms. Patricia Reyes', 'Senior Vice President - Sales', 'www.insularhealth.com.ph', 'PRC-2024-002', 1998, 220000, 410, '410+ Hospital Network', 'Active', 'Corporate', '2021-01-15', '2030-01-14', 30, 75.00, 25.00, 1, '2026-02-07 17:37:12', '2026-02-07 17:52:53'),
(4, 'MED001', 'Medicard', 'Healthcare provider with extensive hospital network', 'Medicard Center, 123 Uniform Street, Ayala Avenue, Makati City', '+63 2 8815-2000', 'info@medicard.com.ph', 'Mr. Antonio Garcia', 'Regional Director', 'www.medicard.com.ph', 'PRC-2024-003', 2001, 195000, 380, '380+ Hospital Network', 'Active', 'Corporate', '2020-07-01', '2028-06-30', 30, 75.00, 25.00, 1, '2026-02-07 17:37:12', '2026-02-07 17:52:54'),
(5, 'STL001', 'St. Luke\'s Health Plan', 'Premium healthcare network provider', 'Heritage House, 231 Sen. Gil Puyat Avenue, Makati City', '+63 2 8888-5555', 'support@stlukeshealthplan.com.ph', 'Ms. Isabella Lopez', 'Vice President, Corporate Health', 'www.stlukeshealthplan.com.ph', 'PRC-2024-004', 2003, 165000, 285, '285+ Hospital Partner Network', 'Active', 'Corporate', '2021-03-15', '2031-03-14', 30, 75.00, 25.00, 1, '2026-02-07 17:37:12', '2026-02-07 17:52:54');

-- --------------------------------------------------------

--
-- Table structure for table `incentive_events`
--

CREATE TABLE `incentive_events` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `duty_date` date NOT NULL,
  `procedure_type_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `approved_by` int(11) DEFAULT NULL COMMENT 'User/approver who approved',
  `status` enum('pending','approved') DEFAULT 'pending' COMMENT 'Unapproved = inactive for payroll',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incentive_types`
--

CREATE TABLE `incentive_types` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 1 COMMENT '1=approved, only these allowed when logging',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `incentive_types`
--

INSERT INTO `incentive_types` (`id`, `code`, `name`, `description`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 'ADMISSION', 'Admission', 'Patient admission procedure', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(2, 'ER_CONSULT', 'ER Consult', 'Emergency room consultation', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(3, 'SUTURING', 'Suturing', 'Wound suturing procedure', 0, '2026-02-08 15:11:57', '2026-02-09 17:02:00'),
(4, 'DOA', 'DOA', 'Dead on arrival documentation', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(5, 'WOUND_DEBRIDEMENT', 'Wound Debridement', 'Wound debridement procedure', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(6, 'NGT', 'NGT', 'Nasogastric tube insertion', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(7, 'FBR', 'Foreign Body Removal', 'Foreign body removal procedure', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(8, 'FOLEY', 'Foley Catheter', 'Foley catheter insertion', 0, '2026-02-08 15:11:57', '2026-02-09 17:14:42'),
(9, 'ABG', 'ABG', 'Arterial blood gas collection', 0, '2026-02-08 15:11:57', '2026-02-09 17:00:52'),
(10, 'AMBUCON', 'Ambucon', 'Ambulance convoy support', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(11, 'INTUBATION', 'Intubation', 'Endotracheal intubation', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(12, 'I_AND_D', 'I and D', 'Incision and drainage', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(13, 'OR_ASSIST', 'OR Assist', 'Operating room assistance', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(14, 'MED_CERT', 'Medical Certificate', 'Medical certificate issuance', 1, '2026-02-08 15:11:57', '2026-02-08 15:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `job_titles`
--

CREATE TABLE `job_titles` (
  `job_title_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `salary_grade_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_titles`
--

INSERT INTO `job_titles` (`job_title_id`, `title`, `description`, `department_id`, `salary_grade_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'HR Manager', 'Manages HR operations and employee relations', 1, 4, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(2, 'HR Executive', 'Handles recruitment and employee development', 1, 2, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(3, 'Operations Manager', 'Oversees day-to-day operations', 2, 4, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(4, 'Operations Associate', 'Supports operations team', 2, 1, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(5, 'Finance Manager', 'Manages financial operations', 3, 4, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(6, 'Accountant', 'Handles accounting tasks', 3, 2, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(7, 'Sales Manager', 'Leads sales team', 4, 4, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(8, 'Sales Executive', 'Manages sales operations', 4, 2, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(9, 'IT Manager', 'Manages IT infrastructure', 5, 4, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(10, 'Software Developer', 'Develops software applications', 5, 3, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `location_code` varchar(50) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location_type` enum('Head Office','Branch','Factory','Warehouse','Remote') DEFAULT 'Branch',
  `facility_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `payroll_eligible` tinyint(1) DEFAULT 1,
  `scheduling_enabled` tinyint(1) DEFAULT 1,
  `allow_overtime` tinyint(1) DEFAULT 1,
  `allow_night_shift` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `location_code`, `location_name`, `location_type`, `facility_name`, `address`, `city`, `state`, `country`, `postal_code`, `contact_number`, `phone`, `email`, `manager_id`, `payroll_eligible`, `scheduling_enabled`, `allow_overtime`, `allow_night_shift`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'LOC001', 'Head Office', 'Head Office', NULL, '123 Main Street', 'New York', 'NY', 'USA', '10001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, NULL, 'active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(2, 'LOC002', 'Branch Office', 'Branch', NULL, '456 Oak Avenue', 'Los Angeles', 'CA', 'USA', '90001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, NULL, 'active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(3, 'LOC003', 'Manufacturing Plant', 'Factory', NULL, '789 Industrial Road', 'Chicago', 'IL', 'USA', '60601', NULL, NULL, NULL, NULL, 1, 1, 1, 1, NULL, 'active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(4, 'LOC004', 'Distribution Center', 'Warehouse', NULL, '321 Logistics Lane', 'Houston', 'TX', 'USA', '77001', NULL, NULL, NULL, NULL, 1, 1, 1, 1, NULL, 'active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(5, 'HQ', 'Head Office', 'Head Office', NULL, NULL, 'Manila', NULL, 'Philippines', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, NULL, 'active', '2026-02-05 18:55:33', '2026-02-05 18:55:33'),
(6, 'MED', 'Medical Center', 'Branch', NULL, NULL, 'Cebu', NULL, 'Philippines', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, NULL, 'active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(7, 'LAB', 'Laboratory', 'Factory', NULL, NULL, 'Manila', NULL, 'Philippines', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 1, NULL, 'active', '2026-02-05 18:55:34', '2026-02-05 18:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `login_otp`
--

CREATE TABLE `login_otp` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `used_flag` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_otp`
--

INSERT INTO `login_otp` (`id`, `user_id`, `otp_hash`, `expires_at`, `attempts`, `used_flag`, `created_at`) VALUES
(7, 1, '$2y$10$radGzM7QGEy4W/BDIK674egyX2OqpCYYY1dAo4oLCBalsM5.ZUijK', '2026-02-10 00:38:35', 0, 1, '2026-02-09 16:28:35'),
(8, 1, '$2y$10$FP.FwlrJy/jV/VTE.cDJx.jsAjkBVbAiPBWbPFR0QwtnGMr74Kzh2', '2026-02-10 00:43:03', 0, 1, '2026-02-09 16:33:03'),
(9, 1, '$2y$10$f.476C7uulO/khrLmbiJMuVhJDdZdoY31NzYTUgwmDoxc0l9UVh9u', '2026-02-10 00:43:07', 0, 1, '2026-02-09 16:33:07'),
(10, 1, '$2y$10$1ZMaKLZrPLOrZ5b/R0mlUuaw4tebwyEvy..moMgwoH0nvtxSqsDGW', '2026-02-10 00:43:11', 0, 1, '2026-02-09 16:33:11'),
(11, 1, '$2y$10$CQU2cZQ7FF3RxVpWbgKbj.E2Qt2ZuoghI9FNXrTZqnjjt5evEI2nm', '2026-02-10 00:43:53', 1, 1, '2026-02-09 16:33:53'),
(12, 1, '$2y$10$/n9Gy/53vsMP0MkuAvPnlODcWOmbJa6jmDPgLLO/SdH5MCy6QocoW', '2026-02-11 00:27:10', 1, 1, '2026-02-10 16:17:10');

-- --------------------------------------------------------

--
-- Table structure for table `onboarding_checklists`
--

CREATE TABLE `onboarding_checklists` (
  `onboarding_checklist_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `checklist_item` varchar(255) NOT NULL,
  `responsible_department` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','Overdue') DEFAULT 'Pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `onboarding_checklists`
--

INSERT INTO `onboarding_checklists` (`onboarding_checklist_id`, `employee_id`, `checklist_item`, `responsible_department`, `due_date`, `completed_date`, `completed_by`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 2, 'IT Equipment Setup', 'IT', '2021-06-05', NULL, NULL, 'Completed', 'Laptop and phone provided', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(2, 4, 'Office Tour', 'Operations', '2022-01-12', NULL, NULL, 'Completed', 'Familiarized with office', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(3, 4, 'HR Orientation', 'Human Resources', '2022-01-15', NULL, NULL, 'Completed', 'Completed HR induction', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(4, 4, 'Department Training', 'Operations', '2022-02-01', NULL, NULL, 'Completed', 'Completed operations training', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(5, 6, 'IT Equipment Setup', 'IT', '2023-05-20', NULL, NULL, 'In Progress', 'Pending laptop setup', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(6, 6, 'Office Tour', 'Operations', '2023-05-17', NULL, NULL, 'Completed', 'Completed', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(7, 10, 'IT Equipment Setup', 'IT', '2022-03-20', NULL, NULL, 'Completed', 'Setup completed', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(8, 10, 'HR Orientation', 'Human Resources', '2022-03-22', NULL, NULL, 'Completed', 'Orientation completed', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(9, 10, 'Department Training', 'IT', '2022-04-05', NULL, NULL, 'Completed', 'Training completed', '2026-02-05 16:53:45', '2026-02-05 16:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `otp_attempt_logs`
--

CREATE TABLE `otp_attempt_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempt_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `error_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_attempt_logs`
--

INSERT INTO `otp_attempt_logs` (`id`, `user_id`, `ip_address`, `attempt_type`, `status`, `error_reason`, `created_at`) VALUES
(8, 1, '::1', 'otp_generated', 'success', 'johnpaulaustria321@gmail.com', '2026-02-09 16:28:36'),
(9, 1, '::1', 'otp_verification', 'success', 'OTP verified successfully', '2026-02-09 16:28:55'),
(10, 1, '::1', 'otp_generated', 'success', 'johnpaulaustria321@gmail.com', '2026-02-09 16:33:03'),
(11, 1, '::1', 'otp_generated', 'success', 'johnpaulaustria321@gmail.com', '2026-02-09 16:33:07'),
(12, 1, '::1', 'otp_generated', 'success', 'johnpaulaustria321@gmail.com', '2026-02-09 16:33:11'),
(13, 1, '::1', 'otp_generated', 'success', 'johnpaulaustria321@gmail.com', '2026-02-09 16:33:53'),
(14, 1, '::1', 'otp_verification', 'failed', 'Incorrect OTP', '2026-02-09 16:34:23'),
(15, 1, '::1', 'otp_verification', 'success', 'OTP verified successfully', '2026-02-09 16:34:46'),
(16, 1, '::1', 'otp_generated', 'success', 'johnpaulaustria321@gmail.com', '2026-02-10 16:17:10'),
(17, 1, '::1', 'otp_verification', 'failed', 'Incorrect OTP', '2026-02-10 16:17:34'),
(18, 1, '::1', 'otp_verification', 'success', 'OTP verified successfully', '2026-02-10 16:17:46');

-- --------------------------------------------------------

--
-- Table structure for table `out_of_band_approvals`
--

CREATE TABLE `out_of_band_approvals` (
  `id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `requested_amount` decimal(12,2) NOT NULL,
  `salary_band_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_adjustments`
--

CREATE TABLE `payroll_adjustments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `payroll_run_id` int(11) DEFAULT NULL,
  `adjustment_type` enum('Final Pay','Back Pay','13th Month','Separation Pay','Other') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `effective_date` date NOT NULL,
  `status` enum('pending','approved','processed','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_approvals`
--

CREATE TABLE `payroll_approvals` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) DEFAULT NULL,
  `approval_level` int(11) NOT NULL,
  `approver_id` int(11) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_approvals`
--

INSERT INTO `payroll_approvals` (`id`, `payroll_run_id`, `approval_level`, `approver_id`, `approval_status`, `comments`, `approval_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'pending', NULL, NULL, '2026-02-10 17:22:52', '2026-02-10 17:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_audit_trail`
--

CREATE TABLE `payroll_audit_trail` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_components`
--

CREATE TABLE `payroll_components` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `component_type` enum('salary','deduction','tax','benefit') NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_configurations`
--

CREATE TABLE `payroll_configurations` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_runs`
--

CREATE TABLE `payroll_runs` (
  `id` int(11) NOT NULL,
  `period_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `pay_date` date NOT NULL,
  `status` enum('Draft','Processed','Closed') NOT NULL DEFAULT 'Draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_runs`
--

INSERT INTO `payroll_runs` (`id`, `period_name`, `start_date`, `end_date`, `pay_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'February 2026 Period 1', '2026-02-01', '2026-02-15', '2026-02-20', 'Draft', '2026-02-10 17:22:52', '2026-02-10 17:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_run_employees`
--

CREATE TABLE `payroll_run_employees` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payslip_number` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_run_employees`
--

INSERT INTO `payroll_run_employees` (`id`, `payroll_run_id`, `employee_id`, `basic_pay`, `gross_pay`, `total_deductions`, `net_pay`, `payslip_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 15000.00, 16500.00, 2000.00, 14500.00, 'PS-2026-02-001', 'Pending', '2026-02-10 17:23:03', '2026-02-10 17:23:03'),
(2, 1, 2, 15000.00, 16500.00, 2000.00, 14500.00, 'PS-2026-02-002', 'Pending', '2026-02-10 17:23:03', '2026-02-10 17:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `pay_contracts`
--

CREATE TABLE `pay_contracts` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `pay_grade_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `applicable_policies` text DEFAULT NULL COMMENT 'e.g. policy references or notes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pay_contracts`
--

INSERT INTO `pay_contracts` (`id`, `code`, `name`, `pay_grade_id`, `start_date`, `end_date`, `applicable_policies`, `created_at`, `updated_at`) VALUES
(1, 'ER_DOCTOR_CONTRACT', 'ER Doctor contract', 1, '2026-02-08', '2027-02-08', 'Salary bands; incentive rules; benefits per policy', '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(2, 'STAFF_NURSE_CONTRACT', 'Staff Nurse contract', 2, '2026-02-08', '2027-02-08', 'Salary bands; incentive rules; benefits per policy', '2026-02-08 15:11:57', '2026-02-08 15:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `pay_grades`
--

CREATE TABLE `pay_grades` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pay_grades`
--

INSERT INTO `pay_grades` (`id`, `code`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ER_DOCTOR', 'ER Doctor', 'Emergency room physician pay grade', 'Active', '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(2, 'STAFF_NURSE', 'Staff Nurse', 'Staff nurse pay grade', 'Active', '2026-02-08 15:11:57', '2026-02-08 15:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `role_category` varchar(50) DEFAULT NULL,
  `privilege_level` int(11) DEFAULT 0,
  `permissions_count` int(11) DEFAULT 0,
  `user_count` int(11) DEFAULT 0,
  `is_system_role` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `role_code`, `description`, `role_category`, `privilege_level`, `permissions_count`, `user_count`, `is_system_role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'ADMIN', 'System Administrator - Full access to all features', 'System', 100, 45, 2, 1, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(2, 'HR Manager', 'HR_MANAGER', 'Human Resources Manager - Manage employees, payroll, and benefits', 'HR', 80, 35, 1, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(3, 'HR Executive', 'HR_EXEC', 'HR Executive - Recruitment and employee development', 'HR', 60, 25, 2, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(4, 'Department Manager', 'DEPT_MGR', 'Department Manager - Manage department staff and operations', 'Management', 70, 28, 3, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(5, 'Supervisor', 'SUPERVISOR', 'Team Supervisor - Oversee team tasks and reports', 'Management', 50, 20, 5, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(6, 'Finance Manager', 'FIN_MGR', 'Finance Manager - Manage financial records and reports', 'Finance', 75, 32, 1, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(7, 'Accountant', 'ACCOUNTANT', 'Accountant - Manage accounting transactions', 'Finance', 55, 22, 2, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(8, 'Payroll Administrator', 'PAYROLL_ADMIN', 'Payroll Administrator - Process payroll and benefits', 'Finance', 60, 24, 1, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(9, 'IT Administrator', 'IT_ADMIN', 'IT Administrator - Manage systems and infrastructure', 'IT', 85, 40, 1, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(10, 'IT Support', 'IT_SUPPORT', 'IT Support - Provide technical support to users', 'IT', 45, 18, 2, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(11, 'Sales Manager', 'SALES_MGR', 'Sales Manager - Manage sales team and targets', 'Sales', 70, 28, 2, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(12, 'Sales Executive', 'SALES_EXEC', 'Sales Executive - Handle sales operations', 'Sales', 50, 20, 4, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(13, 'Operations Manager', 'OPS_MGR', 'Operations Manager - Manage operations activities', 'Operations', 70, 28, 1, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(14, 'Operations Staff', 'OPS_STAFF', 'Operations Staff - Support operations tasks', 'Operations', 40, 15, 3, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(15, 'Employee', 'EMPLOYEE', 'Standard Employee - Basic access to company systems', 'User', 30, 10, 50, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(16, 'Guest', 'GUEST', 'Guest Access - Limited access for external users', 'User', 10, 5, 0, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(17, 'Auditor', 'AUDITOR', 'Auditor - Review and audit system activities', 'Compliance', 65, 26, 1, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(18, 'Compliance Officer', 'COMPLIANCE', 'Compliance Officer - Ensure regulatory compliance', 'Compliance', 75, 30, 1, 0, 'Active', '2026-02-05 20:17:18', '2026-02-05 20:17:18'),
(19, 'Viewer', 'VIEWER', 'Report Viewer - View reports and dashboards', 'User', 20, 8, 10, 0, 'Inactive', '2026-02-05 20:17:18', '2026-02-05 20:17:18');

-- --------------------------------------------------------

--
-- Table structure for table `salary_adjustments`
--

CREATE TABLE `salary_adjustments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `adjustment_type` enum('Temporary','Permanent') NOT NULL,
  `trigger_type` enum('Promotion','Approved exception','Policy change') NOT NULL,
  `previous_amount` decimal(12,2) DEFAULT NULL,
  `new_amount` decimal(12,2) NOT NULL,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'For temporary adjustments',
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `salary_band_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_bands`
--

CREATE TABLE `salary_bands` (
  `id` int(11) NOT NULL,
  `pay_grade_id` int(11) NOT NULL,
  `grade_level_id` int(11) DEFAULT NULL,
  `min_salary` decimal(12,2) NOT NULL,
  `midpoint_salary` decimal(12,2) NOT NULL,
  `max_salary` decimal(12,2) NOT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `salary_bands`
--

INSERT INTO `salary_bands` (`id`, `pay_grade_id`, `grade_level_id`, `min_salary`, `midpoint_salary`, `max_salary`, `effective_from`, `effective_to`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 6000.00, 7500.00, 9000.00, NULL, NULL, 'Active', '2026-02-08 15:11:57', '2026-02-08 15:11:57'),
(2, 2, 2, 2500.00, 3000.00, 3500.00, NULL, NULL, 'Active', '2026-02-08 15:11:57', '2026-02-08 15:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `salary_component_definitions`
--

CREATE TABLE `salary_component_definitions` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `component_type` enum('base','allowance','deduction') NOT NULL DEFAULT 'allowance',
  `taxable` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_component_definitions`
--

INSERT INTO `salary_component_definitions` (`id`, `code`, `name`, `description`, `component_type`, `taxable`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ER_DUTY_PAY', 'ER Duty Pay', '24 hour ER duty base pay', 'allowance', 1, 0, '2026-02-08 15:11:57', '2026-02-09 17:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `salary_grades`
--

CREATE TABLE `salary_grades` (
  `salary_grade_id` int(11) NOT NULL,
  `grade_code` varchar(50) NOT NULL,
  `grade_name` varchar(255) NOT NULL,
  `min_salary` decimal(12,2) DEFAULT NULL,
  `max_salary` decimal(12,2) DEFAULT NULL,
  `base_allowance` decimal(12,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_grades`
--

INSERT INTO `salary_grades` (`salary_grade_id`, `grade_code`, `grade_name`, `min_salary`, `max_salary`, `base_allowance`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SG001', 'Entry Level', 25000.00, 35000.00, 2500.00, NULL, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(2, 'SG002', 'Mid Level', 35000.00, 55000.00, 4000.00, NULL, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(3, 'SG003', 'Senior Level', 55000.00, 85000.00, 6000.00, NULL, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(4, 'SG004', 'Management', 85000.00, 120000.00, 8000.00, NULL, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(5, 'SG005', 'Executive', 120000.00, 200000.00, 12000.00, NULL, 'Active', '2026-02-05 16:53:44', '2026-02-05 16:53:44'),
(6, 'SG1', 'Entry Level', 15000.00, 25000.00, NULL, NULL, 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(7, 'SG2', 'Mid Level', 25000.00, 50000.00, NULL, NULL, 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(8, 'SG3', 'Senior Level', 50000.00, 100000.00, NULL, NULL, 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34'),
(9, 'SG4', 'Management', 100000.00, 250000.00, NULL, NULL, 'Active', '2026-02-05 18:55:34', '2026-02-05 18:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `salary_templates`
--

CREATE TABLE `salary_templates` (
  `template_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `employment_type` enum('Per shift','Hourly','Monthly') NOT NULL DEFAULT 'Monthly',
  `salary_type` enum('Per shift','Hourly','Daily','Monthly') NOT NULL,
  `shift_duration_hours` decimal(5,2) DEFAULT NULL COMMENT 'e.g. 24 for doctor, 8 for nurse',
  `basic_rate` decimal(12,2) NOT NULL,
  `rate_per_hour_reference` decimal(12,2) DEFAULT NULL COMMENT 'Computed hourly ref e.g. 125',
  `payroll_frequency` enum('Weekly','Semi-Monthly','Monthly') NOT NULL DEFAULT 'Semi-Monthly',
  `payout_day_1` tinyint(4) DEFAULT NULL COMMENT 'e.g. 7 for 7th',
  `payout_day_2` tinyint(4) DEFAULT NULL COMMENT 'e.g. 22 for 22nd, NULL if not semi-monthly',
  `cutoff_start_day` tinyint(4) DEFAULT NULL COMMENT 'e.g. 1 for period 1-15',
  `cutoff_end_day` tinyint(4) DEFAULT NULL COMMENT 'e.g. 15 for period 1-15',
  `double_pay_addon` decimal(12,2) DEFAULT 0.00 COMMENT 'Add on when date is special e.g. 3000',
  `incentives_in_payroll` tinyint(1) DEFAULT 1,
  `overtime_outside_base` tinyint(1) DEFAULT 0,
  `night_differential_applied` tinyint(1) DEFAULT 0,
  `incentive_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'e.g. ["ER admissions","ER consultations","Medical procedures"]' CHECK (json_valid(`incentive_rules`)),
  `benefits` text DEFAULT NULL COMMENT 'e.g. Free meals three times daily',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `shift_id` int(11) NOT NULL,
  `shift_name` varchar(100) NOT NULL,
  `shift_code` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_hours` decimal(5,2) DEFAULT NULL,
  `break_hours` decimal(5,2) DEFAULT 1.00,
  `description` text DEFAULT NULL,
  `is_night_shift` tinyint(1) DEFAULT 0,
  `shift_type` varchar(50) DEFAULT NULL,
  `overtime_eligible` tinyint(1) DEFAULT 1,
  `required_staff_count` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`shift_id`, `shift_name`, `shift_code`, `start_time`, `end_time`, `duration_hours`, `break_hours`, `description`, `is_night_shift`, `shift_type`, `overtime_eligible`, `required_staff_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Morning Shift', 'SHIFT_MORNING', '08:00:00', '16:00:00', 8.00, 1.00, 'Standard morning shift 8AM-4PM', 0, NULL, 1, NULL, 'Active', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(2, 'Afternoon Shift', 'SHIFT_AFTERNOON', '13:00:00', '21:00:00', 8.00, 1.00, 'Standard afternoon shift 1PM-9PM', 0, NULL, 1, NULL, 'Active', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(3, 'Night Shift', 'SHIFT_NIGHT', '21:00:00', '05:00:00', 8.00, 1.00, 'Standard night shift 9PM-5AM', 1, NULL, 1, NULL, 'Active', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(4, 'Extended Shift', 'SHIFT_EXTENDED', '07:00:00', '18:00:00', 11.00, 1.00, 'Extended shift 7AM-6PM', 0, NULL, 1, NULL, 'Active', '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(5, 'Flexible Shift', 'SHIFT_FLEX', '09:00:00', '17:00:00', 8.00, 0.50, 'Flexible working hours', 0, NULL, 1, NULL, 'Active', '2026-02-05 16:53:45', '2026-02-05 16:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `special_pay_dates`
--

CREATE TABLE `special_pay_dates` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tax_contributions`
--

CREATE TABLE `tax_contributions` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `contribution_type` enum('SSS','PhilHealth','Pag-IBIG','Income Tax','Other') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `effective_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'employee',
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'johnpaulaustria321@gmail.com', '$2y$10$k1PqX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Admin', 'User', 'Administrator', 'admin', 'active', '2026-02-04 16:47:09', '2026-02-09 15:48:12'),
(2, 'hrchief', 'itachiuchiha10012@gmail.com', '$2y$10$fAsfERT.6UQrsOsoKJQyZOxw2RYuLA7eyTSJol8DcQiC1PogIdBFa', 'HR', 'Chief', 'HR Chief', 'hr_chief', 'active', '2026-02-04 16:47:09', '2026-02-09 15:48:12');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `schedule_id` int(11) NOT NULL,
  `schedule_name` varchar(255) NOT NULL,
  `schedule_type` enum('Fixed','Rotating','Flexible','Part-time') DEFAULT 'Fixed',
  `shift_pattern` varchar(100) DEFAULT NULL,
  `hours_per_day` decimal(5,2) DEFAULT NULL,
  `work_days_per_week` int(11) DEFAULT 5,
  `weekly_hours` decimal(5,2) DEFAULT NULL,
  `has_night_shift` tinyint(1) DEFAULT 0,
  `is_trainee` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `required_count` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_schedules`
--

INSERT INTO `work_schedules` (`schedule_id`, `schedule_name`, `schedule_type`, `shift_pattern`, `hours_per_day`, `work_days_per_week`, `weekly_hours`, `has_night_shift`, `is_trainee`, `description`, `status`, `start_date`, `end_date`, `required_count`, `created_at`, `updated_at`) VALUES
(1, 'Standard 5-Day', 'Fixed', '8-8-8-8-8', 8.00, 5, 40.00, 0, 0, 'Standard 5-day work week', 'Active', NULL, NULL, NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(2, 'Rotating Shifts', 'Rotating', 'Morning-Afternoon-Night', 8.00, 7, 56.00, 1, 0, 'Rotating shift pattern', 'Active', NULL, NULL, NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(3, 'Extended Hours', 'Fixed', '11-11-5.5-5.5', 8.25, 5, 41.25, 0, 0, 'Extended hours schedule', 'Active', NULL, NULL, NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(4, 'Part-time Schedule', 'Flexible', '4-4-4-4-4', 4.00, 5, 20.00, 0, 0, 'Part-time 20 hours per week', 'Active', NULL, NULL, NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45'),
(5, 'Night Shift Only', 'Fixed', '8-8-8-8-8', 8.00, 5, 40.00, 1, 0, 'Night shift schedule', 'Active', NULL, NULL, NULL, '2026-02-05 16:53:45', '2026-02-05 16:53:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity_id` (`entity_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `benefit_definitions`
--
ALTER TABLE `benefit_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_effective` (`effective_from`,`effective_to`),
  ADD KEY `idx_attach_to` (`attach_to`);

--
-- Indexes for table `compensation_approval_requests`
--
ALTER TABLE `compensation_approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_type` (`request_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_requested_at` (`requested_at`),
  ADD KEY `idx_reviewed_at` (`reviewed_at`);

--
-- Indexes for table `compensation_version_history`
--
ALTER TABLE `compensation_version_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_entity_version` (`entity_type`,`entity_id`,`version_number`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_effective_date` (`effective_date`),
  ADD KEY `idx_status_active` (`status`,`is_active`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `head_id` (`head_id`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `batch_reference` (`batch_reference`),
  ADD KEY `idx_batch_reference` (`batch_reference`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payroll_run_id` (`payroll_run_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_employee_code` (`employee_code`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_job_title_id` (`job_title_id`),
  ADD KEY `idx_employment_type_id` (`employment_type_id`),
  ADD KEY `idx_location_id` (`location_id`),
  ADD KEY `idx_supervisor_id` (`supervisor_id`),
  ADD KEY `idx_employment_status` (`employment_status`);

--
-- Indexes for table `employee_assignments`
--
ALTER TABLE `employee_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_emp_schedule` (`employee_id`,`schedule_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_schedule_id` (`schedule_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `employee_contract_assignments`
--
ALTER TABLE `employee_contract_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_contract_id` (`contract_id`),
  ADD KEY `idx_effective` (`effective_from`,`effective_to`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_expiry_date` (`expiry_date`);

--
-- Indexes for table `employee_hmo_enrollments`
--
ALTER TABLE `employee_hmo_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_plan_id` (`plan_id`),
  ADD KEY `idx_provider_id` (`provider_id`),
  ADD KEY `idx_enrollment_status` (`enrollment_status`),
  ADD KEY `idx_effective_date` (`effective_date`),
  ADD KEY `idx_hmo_enrollment_employee` (`employee_id`,`enrollment_status`),
  ADD KEY `idx_hmo_enrollment_provider` (`provider_id`,`effective_date`);

--
-- Indexes for table `employee_movements`
--
ALTER TABLE `employee_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_effective_date` (`effective_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_movement_type` (`movement_type`),
  ADD KEY `from_job_title_id` (`from_job_title_id`),
  ADD KEY `to_job_title_id` (`to_job_title_id`),
  ADD KEY `from_department_id` (`from_department_id`),
  ADD KEY `to_department_id` (`to_department_id`),
  ADD KEY `from_location_id` (`from_location_id`),
  ADD KEY `to_location_id` (`to_location_id`);

--
-- Indexes for table `employee_payroll_profiles`
--
ALTER TABLE `employee_payroll_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_payroll_eligible` (`payroll_eligible`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- Indexes for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_effective_date` (`effective_date`);

--
-- Indexes for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD PRIMARY KEY (`employee_shift_id`),
  ADD UNIQUE KEY `unique_emp_shift` (`employee_id`,`shift_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_shift_id` (`shift_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `employment_types`
--
ALTER TABLE `employment_types`
  ADD PRIMARY KEY (`employment_type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `government_reports`
--
ALTER TABLE `government_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agency` (`agency`),
  ADD KEY `idx_submission_status` (`submission_status`),
  ADD KEY `idx_reporting_date` (`reporting_date`);

--
-- Indexes for table `grade_levels`
--
ALTER TABLE `grade_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_grade_level` (`pay_grade_id`,`code`),
  ADD KEY `idx_pay_grade_id` (`pay_grade_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `hmo_billing_adjustments`
--
ALTER TABLE `hmo_billing_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reconciliation_id` (`reconciliation_id`),
  ADD KEY `idx_adjustment_type` (`adjustment_type`),
  ADD KEY `idx_adjustment_status` (`adjustment_status`);

--
-- Indexes for table `hmo_billing_discrepancies`
--
ALTER TABLE `hmo_billing_discrepancies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reconciliation_id` (`reconciliation_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_claim_id` (`claim_id`),
  ADD KEY `idx_discrepancy_type` (`discrepancy_type`);

--
-- Indexes for table `hmo_billing_reconciliation`
--
ALTER TABLE `hmo_billing_reconciliation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reconciliation_number` (`reconciliation_number`),
  ADD KEY `idx_provider_id` (`provider_id`),
  ADD KEY `idx_billing_month` (`billing_month`),
  ADD KEY `idx_reconciliation_status` (`reconciliation_status`);

--
-- Indexes for table `hmo_claims`
--
ALTER TABLE `hmo_claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `claim_number` (`claim_number`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_claim_number` (`claim_number`),
  ADD KEY `idx_claim_status` (`claim_status`),
  ADD KEY `idx_service_date` (`service_date`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `idx_hmo_claims_employee` (`employee_id`,`claim_status`),
  ADD KEY `idx_hmo_claims_date` (`service_date`,`claim_status`);

--
-- Indexes for table `hmo_documents`
--
ALTER TABLE `hmo_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `document_number` (`document_number`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_plan_id` (`plan_id`),
  ADD KEY `idx_provider_id` (`provider_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_document_status` (`document_status`),
  ADD KEY `idx_issue_date` (`issue_date`);

--
-- Indexes for table `hmo_enrollment_dependents`
--
ALTER TABLE `hmo_enrollment_dependents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enrollment_id` (`enrollment_id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `hmo_life_events`
--
ALTER TABLE `hmo_life_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `hmo_plans`
--
ALTER TABLE `hmo_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_code` (`plan_code`),
  ADD KEY `idx_provider_id` (`provider_id`),
  ADD KEY `idx_plan_code` (`plan_code`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_hmo_plan_active` (`is_active`,`provider_id`);

--
-- Indexes for table `hmo_premium_payments`
--
ALTER TABLE `hmo_premium_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enrollment_id` (`enrollment_id`),
  ADD KEY `idx_provider_id` (`provider_id`),
  ADD KEY `idx_payment_month` (`payment_month`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `hmo_providers`
--
ALTER TABLE `hmo_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_code` (`provider_code`),
  ADD KEY `idx_provider_code` (`provider_code`),
  ADD KEY `idx_status` (`provider_status`),
  ADD KEY `idx_hmo_provider_active` (`is_active`),
  ADD KEY `idx_contract_dates` (`contract_start_date`,`contract_end_date`),
  ADD KEY `idx_provider_type` (`provider_type`);

--
-- Indexes for table `incentive_events`
--
ALTER TABLE `incentive_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_duty_date` (`duty_date`),
  ADD KEY `idx_employee_duty` (`employee_id`,`duty_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_procedure_type_id` (`procedure_type_id`);

--
-- Indexes for table `incentive_types`
--
ALTER TABLE `incentive_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_is_approved` (`is_approved`);

--
-- Indexes for table `job_titles`
--
ALTER TABLE `job_titles`
  ADD PRIMARY KEY (`job_title_id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_salary_grade_id` (`salary_grade_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `location_code` (`location_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location_code` (`location_code`);

--
-- Indexes for table `login_otp`
--
ALTER TABLE `login_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_expires` (`user_id`,`expires_at`),
  ADD KEY `idx_user_used` (`user_id`,`used_flag`);

--
-- Indexes for table `onboarding_checklists`
--
ALTER TABLE `onboarding_checklists`
  ADD PRIMARY KEY (`onboarding_checklist_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`);

--
-- Indexes for table `otp_attempt_logs`
--
ALTER TABLE `otp_attempt_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type` (`user_id`,`attempt_type`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `out_of_band_approvals`
--
ALTER TABLE `out_of_band_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_salary_band_id` (`salary_band_id`);

--
-- Indexes for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_adjustment_type` (`adjustment_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- Indexes for table `payroll_approvals`
--
ALTER TABLE `payroll_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_approval_status` (`approval_status`),
  ADD KEY `idx_payroll_run_id` (`payroll_run_id`),
  ADD KEY `idx_approval_level` (`approval_level`);

--
-- Indexes for table `payroll_audit_trail`
--
ALTER TABLE `payroll_audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_payroll_run_id` (`payroll_run_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_date` (`created_date`);

--
-- Indexes for table `payroll_components`
--
ALTER TABLE `payroll_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_component_type` (`component_type`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `payroll_configurations`
--
ALTER TABLE `payroll_configurations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`),
  ADD KEY `idx_config_key` (`config_key`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indexes for table `payroll_run_employees`
--
ALTER TABLE `payroll_run_employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_run_employee` (`payroll_run_id`,`employee_id`),
  ADD KEY `idx_payroll_run_id` (`payroll_run_id`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- Indexes for table `pay_contracts`
--
ALTER TABLE `pay_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_pay_grade_id` (`pay_grade_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indexes for table `pay_grades`
--
ALTER TABLE `pay_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD UNIQUE KEY `role_code` (`role_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_role_code` (`role_code`),
  ADD KEY `idx_privilege_level` (`privilege_level`);

--
-- Indexes for table `salary_adjustments`
--
ALTER TABLE `salary_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_effective_date` (`effective_date`),
  ADD KEY `idx_salary_band_id` (`salary_band_id`);

--
-- Indexes for table `salary_bands`
--
ALTER TABLE `salary_bands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_band_grade_level` (`pay_grade_id`,`grade_level_id`),
  ADD KEY `idx_pay_grade_id` (`pay_grade_id`),
  ADD KEY `idx_grade_level_id` (`grade_level_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_effective` (`effective_from`,`effective_to`);

--
-- Indexes for table `salary_component_definitions`
--
ALTER TABLE `salary_component_definitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `salary_grades`
--
ALTER TABLE `salary_grades`
  ADD PRIMARY KEY (`salary_grade_id`),
  ADD UNIQUE KEY `grade_code` (`grade_code`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `salary_templates`
--
ALTER TABLE `salary_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `unique_position_role_effective` (`position`,`role`,`effective_date`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`shift_id`),
  ADD UNIQUE KEY `shift_code` (`shift_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_night_shift` (`is_night_shift`);

--
-- Indexes for table `special_pay_dates`
--
ALTER TABLE `special_pay_dates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`),
  ADD KEY `idx_date` (`date`);

--
-- Indexes for table `tax_contributions`
--
ALTER TABLE `tax_contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contribution_type` (`contribution_type`),
  ADD KEY `idx_payroll_run_id` (`payroll_run_id`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_has_night_shift` (`has_night_shift`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefit_definitions`
--
ALTER TABLE `benefit_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `compensation_approval_requests`
--
ALTER TABLE `compensation_approval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compensation_version_history`
--
ALTER TABLE `compensation_version_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employee_assignments`
--
ALTER TABLE `employee_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employee_contract_assignments`
--
ALTER TABLE `employee_contract_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employee_hmo_enrollments`
--
ALTER TABLE `employee_hmo_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employee_movements`
--
ALTER TABLE `employee_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_payroll_profiles`
--
ALTER TABLE `employee_payroll_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  MODIFY `employee_shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employment_types`
--
ALTER TABLE `employment_types`
  MODIFY `employment_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `government_reports`
--
ALTER TABLE `government_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grade_levels`
--
ALTER TABLE `grade_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hmo_billing_adjustments`
--
ALTER TABLE `hmo_billing_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hmo_billing_discrepancies`
--
ALTER TABLE `hmo_billing_discrepancies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hmo_billing_reconciliation`
--
ALTER TABLE `hmo_billing_reconciliation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hmo_claims`
--
ALTER TABLE `hmo_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hmo_documents`
--
ALTER TABLE `hmo_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hmo_enrollment_dependents`
--
ALTER TABLE `hmo_enrollment_dependents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hmo_life_events`
--
ALTER TABLE `hmo_life_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hmo_plans`
--
ALTER TABLE `hmo_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hmo_premium_payments`
--
ALTER TABLE `hmo_premium_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `hmo_providers`
--
ALTER TABLE `hmo_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `incentive_events`
--
ALTER TABLE `incentive_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incentive_types`
--
ALTER TABLE `incentive_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `job_titles`
--
ALTER TABLE `job_titles`
  MODIFY `job_title_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `login_otp`
--
ALTER TABLE `login_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `onboarding_checklists`
--
ALTER TABLE `onboarding_checklists`
  MODIFY `onboarding_checklist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `otp_attempt_logs`
--
ALTER TABLE `otp_attempt_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `out_of_band_approvals`
--
ALTER TABLE `out_of_band_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_approvals`
--
ALTER TABLE `payroll_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payroll_audit_trail`
--
ALTER TABLE `payroll_audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_components`
--
ALTER TABLE `payroll_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_configurations`
--
ALTER TABLE `payroll_configurations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payroll_run_employees`
--
ALTER TABLE `payroll_run_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pay_contracts`
--
ALTER TABLE `pay_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pay_grades`
--
ALTER TABLE `pay_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `salary_adjustments`
--
ALTER TABLE `salary_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_bands`
--
ALTER TABLE `salary_bands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_component_definitions`
--
ALTER TABLE `salary_component_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `salary_grades`
--
ALTER TABLE `salary_grades`
  MODIFY `salary_grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `salary_templates`
--
ALTER TABLE `salary_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `special_pay_dates`
--
ALTER TABLE `special_pay_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tax_contributions`
--
ALTER TABLE `tax_contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `departments_ibfk_2` FOREIGN KEY (`head_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`job_title_id`) REFERENCES `job_titles` (`job_title_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`employment_type_id`) REFERENCES `employment_types` (`employment_type_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_5` FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_assignments`
--
ALTER TABLE `employee_assignments`
  ADD CONSTRAINT `employee_assignments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_assignments_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `work_schedules` (`schedule_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_contract_assignments`
--
ALTER TABLE `employee_contract_assignments`
  ADD CONSTRAINT `employee_contract_assignments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_contract_assignments_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `pay_contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD CONSTRAINT `employee_documents_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_movements`
--
ALTER TABLE `employee_movements`
  ADD CONSTRAINT `employee_movements_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_movements_ibfk_2` FOREIGN KEY (`from_job_title_id`) REFERENCES `job_titles` (`job_title_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_movements_ibfk_3` FOREIGN KEY (`to_job_title_id`) REFERENCES `job_titles` (`job_title_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_movements_ibfk_4` FOREIGN KEY (`from_department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_movements_ibfk_5` FOREIGN KEY (`to_department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_movements_ibfk_6` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_movements_ibfk_7` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`location_id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_payroll_profiles`
--
ALTER TABLE `employee_payroll_profiles`
  ADD CONSTRAINT `employee_payroll_profiles_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  ADD CONSTRAINT `employee_salaries_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD CONSTRAINT `employee_shifts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_shifts_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`) ON DELETE CASCADE;

--
-- Constraints for table `grade_levels`
--
ALTER TABLE `grade_levels`
  ADD CONSTRAINT `grade_levels_ibfk_1` FOREIGN KEY (`pay_grade_id`) REFERENCES `pay_grades` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hmo_billing_adjustments`
--
ALTER TABLE `hmo_billing_adjustments`
  ADD CONSTRAINT `hmo_billing_adjustments_ibfk_1` FOREIGN KEY (`reconciliation_id`) REFERENCES `hmo_billing_reconciliation` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hmo_billing_discrepancies`
--
ALTER TABLE `hmo_billing_discrepancies`
  ADD CONSTRAINT `hmo_billing_discrepancies_ibfk_1` FOREIGN KEY (`reconciliation_id`) REFERENCES `hmo_billing_reconciliation` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hmo_billing_reconciliation`
--
ALTER TABLE `hmo_billing_reconciliation`
  ADD CONSTRAINT `hmo_billing_reconciliation_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `hmo_providers` (`id`);

--
-- Constraints for table `hmo_claims`
--
ALTER TABLE `hmo_claims`
  ADD CONSTRAINT `hmo_claims_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `employee_hmo_enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hmo_documents`
--
ALTER TABLE `hmo_documents`
  ADD CONSTRAINT `hmo_documents_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `hmo_plans` (`id`),
  ADD CONSTRAINT `hmo_documents_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `hmo_providers` (`id`);

--
-- Constraints for table `hmo_enrollment_dependents`
--
ALTER TABLE `hmo_enrollment_dependents`
  ADD CONSTRAINT `hmo_enrollment_dependents_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `employee_hmo_enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hmo_plans`
--
ALTER TABLE `hmo_plans`
  ADD CONSTRAINT `hmo_plans_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `hmo_providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hmo_premium_payments`
--
ALTER TABLE `hmo_premium_payments`
  ADD CONSTRAINT `hmo_premium_payments_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `employee_hmo_enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `incentive_events`
--
ALTER TABLE `incentive_events`
  ADD CONSTRAINT `incentive_events_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `incentive_events_ibfk_2` FOREIGN KEY (`procedure_type_id`) REFERENCES `incentive_types` (`id`);

--
-- Constraints for table `job_titles`
--
ALTER TABLE `job_titles`
  ADD CONSTRAINT `job_titles_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `job_titles_ibfk_2` FOREIGN KEY (`salary_grade_id`) REFERENCES `salary_grades` (`salary_grade_id`) ON DELETE SET NULL;

--
-- Constraints for table `login_otp`
--
ALTER TABLE `login_otp`
  ADD CONSTRAINT `login_otp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `onboarding_checklists`
--
ALTER TABLE `onboarding_checklists`
  ADD CONSTRAINT `onboarding_checklists_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_attempt_logs`
--
ALTER TABLE `otp_attempt_logs`
  ADD CONSTRAINT `otp_attempt_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `out_of_band_approvals`
--
ALTER TABLE `out_of_band_approvals`
  ADD CONSTRAINT `out_of_band_approvals_ibfk_1` FOREIGN KEY (`salary_band_id`) REFERENCES `salary_bands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  ADD CONSTRAINT `payroll_adjustments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_run_employees`
--
ALTER TABLE `payroll_run_employees`
  ADD CONSTRAINT `payroll_run_employees_ibfk_1` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_run_employees_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `pay_contracts`
--
ALTER TABLE `pay_contracts`
  ADD CONSTRAINT `pay_contracts_ibfk_1` FOREIGN KEY (`pay_grade_id`) REFERENCES `pay_grades` (`id`);

--
-- Constraints for table `salary_adjustments`
--
ALTER TABLE `salary_adjustments`
  ADD CONSTRAINT `salary_adjustments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_adjustments_ibfk_2` FOREIGN KEY (`salary_band_id`) REFERENCES `salary_bands` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `salary_bands`
--
ALTER TABLE `salary_bands`
  ADD CONSTRAINT `salary_bands_ibfk_1` FOREIGN KEY (`pay_grade_id`) REFERENCES `pay_grades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_bands_ibfk_2` FOREIGN KEY (`grade_level_id`) REFERENCES `grade_levels` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tax_contributions`
--
ALTER TABLE `tax_contributions`
  ADD CONSTRAINT `tax_contributions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
