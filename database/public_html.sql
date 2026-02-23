-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 03:14 AM
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
-- Table structure for table `ai_predictions`
--

CREATE TABLE `ai_predictions` (
  `prediction_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `prediction_type` varchar(50) NOT NULL,
  `prediction_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`prediction_data`)),
  `confidence_score` decimal(5,2) DEFAULT NULL,
  `prediction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_taken` tinyint(4) DEFAULT 0,
  `action_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_prediction_history`
--

CREATE TABLE `ai_prediction_history` (
  `history_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `prediction_type` varchar(50) NOT NULL,
  `predicted_value` decimal(10,4) DEFAULT NULL,
  `actual_value` decimal(10,4) DEFAULT NULL,
  `accuracy` decimal(5,2) DEFAULT NULL,
  `prediction_date` date DEFAULT NULL,
  `evaluation_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attrition_alerts`
--

CREATE TABLE `attrition_alerts` (
  `alert_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `risk_score` decimal(5,2) DEFAULT NULL,
  `risk_level` varchar(20) DEFAULT NULL,
  `recommendation` text DEFAULT NULL,
  `alert_status` varchar(20) DEFAULT 'active',
  `assigned_to` int(11) DEFAULT NULL,
  `action_plan` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `outcome` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'FREE_MEALS_TID', 'Free Meals TID', 'Free meals three times daily on duty for hospital staff', 0, 'Doctor,Nurse,ER Staff,Lab Technician', '2026-01-01', NULL, 'duty', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'HMO_PREMIUM', 'HMO Health Insurance', 'Comprehensive health insurance coverage', 1, 'Doctor,Nurse,ER Staff,Admin', '2026-01-01', NULL, 'role', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'UNIFORM_ALLOW', 'Uniform Allowance', 'Annual uniform allowance', 0, 'All', '2026-01-01', NULL, 'role', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'RICE_SUBSIDY', 'Rice Subsidy', 'Monthly rice subsidy', 0, 'All', '2026-01-01', NULL, 'role', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'MEDICAL_ALLOW', 'Medical Allowance', 'Annual medical allowance', 0, 'Doctor,Nurse,ER Staff', '2026-01-01', NULL, 'role', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'NIGHT_DIFF', 'Night Differential', 'Additional pay for night shifts', 1, 'Doctor,Nurse,ER Staff,Lab Technician', '2026-01-01', NULL, 'duty', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `bir_tax_brackets`
--

CREATE TABLE `bir_tax_brackets` (
  `id` int(11) NOT NULL,
  `annual_salary_from` decimal(12,2) NOT NULL,
  `annual_salary_to` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `deductible_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `effective_year` int(4) NOT NULL DEFAULT year(curdate()),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bir_tax_brackets`
--

INSERT INTO `bir_tax_brackets` (`id`, `annual_salary_from`, `annual_salary_to`, `tax_rate`, `deductible_amount`, `effective_year`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 0.00, 250000.00, 0.00, 0.00, 2026, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 250000.01, 400000.00, 5.00, 0.00, 2026, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 400000.01, 800000.00, 10.00, 0.00, 2026, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 800000.01, 2000000.00, 15.00, 0.00, 2026, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 2000000.01, 8000000.00, 20.00, 0.00, 2026, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 8000000.01, 999999999.00, 30.00, 0.00, 2026, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'Emergency Room', NULL, 'ER', 1, NULL, 'Emergency Room Department - 24/7 emergency services', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Nursing', NULL, 'NUR', 3, NULL, 'Nursing Services Department', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Laboratory', NULL, 'LAB', 5, NULL, 'Medical Laboratory and Diagnostics', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Radiology', NULL, 'RAD', 7, NULL, 'Radiology and Imaging Services', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Pharmacy', NULL, 'PHA', 9, NULL, 'Hospital Pharmacy', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Administration', NULL, 'ADM', 11, NULL, 'Hospital Administration', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'Human Resources', NULL, 'HR', 13, 6, 'Human Resources Department', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'Finance', NULL, 'FIN', 14, 6, 'Finance and Accounting', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'Outpatient Department', NULL, 'OPD', 2, NULL, 'Outpatient Consultation and Treatment', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 'HVL-ER-001', 'Juan', 'Dela', 'Cruz', '1980-03-15', 'Male', 'Married', 'juan.cruz@hvil.ph', '09171234567', '123 Rizal St', 'Manila', 'Metro Manila', '1000', 'Philippines', 'Filipino', 1, 1, 1, 1, NULL, '2020-01-15', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 'HVL-OPD-002', 'Maria', 'Santos', 'Reyes', '1985-07-22', 'Female', 'Married', 'maria.reyes@hvil.ph', '09182345678', '456 Mabini St', 'Quezon City', 'Metro Manila', '1100', 'Philippines', 'Filipino', 2, 9, 1, 1, NULL, '2020-03-10', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 'HVL-NUR-003', 'Jose', 'Mercado', 'Santos', '1990-11-08', 'Male', 'Single', 'jose.santos@hvil.ph', '09193456789', '789 Bonifacio St', 'Makati', 'Metro Manila', '1200', 'Philippines', 'Filipino', 3, 2, 1, 1, 1, '2021-06-01', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, NULL, 'HVL-NUR-004', 'Ana', 'Luna', 'Fernandez', '1992-05-18', 'Female', 'Single', 'ana.fernandez@hvil.ph', '09204567890', '1010 Aguinaldo St', 'Pasig', 'Metro Manila', '1600', 'Philippines', 'Filipino', 4, 2, 1, 1, 3, '2022-01-10', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, NULL, 'HVL-LAB-005', 'Pedro', 'Gomez', 'Villanueva', '1988-09-12', 'Male', 'Married', 'pedro.villanueva@hvil.ph', '09215678901', '2222 Taft Ave', 'Manila', 'Metro Manila', '1000', 'Philippines', 'Filipino', 5, 3, 1, 2, NULL, '2019-03-20', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, NULL, 'HVL-LAB-006', 'Luisa', 'Dizon', 'Mendoza', '1993-12-03', 'Female', 'Single', 'luisa.mendoza@hvil.ph', '09226789012', '3333 Paredes St', 'Sampaloc', 'Metro Manila', '1008', 'Philippines', 'Filipino', 6, 3, 1, 2, 5, '2023-05-15', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, NULL, 'HVL-RAD-007', 'Carlos', 'Jimenez', 'Torres', '1986-04-25', 'Male', 'Married', 'carlos.torres@hvil.ph', '09237890123', '4444 Espana Blvd', 'Manila', 'Metro Manila', '1015', 'Philippines', 'Filipino', 7, 4, 1, 1, NULL, '2020-08-20', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, NULL, 'HVL-RAD-008', 'Sofia', 'Aquino', 'Garcia', '1991-08-30', 'Female', 'Married', 'sofia.garcia@hvil.ph', '09248901234', '5555 Shaw Blvd', 'Mandaluyong', 'Metro Manila', '1550', 'Philippines', 'Filipino', 8, 4, 1, 1, 7, '2021-10-01', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, NULL, 'HVL-PHA-009', 'Antonio', 'Ramos', 'Lopez', '1987-06-17', 'Male', 'Married', 'antonio.lopez@hvil.ph', '09259012345', '6666 Ortigas Ave', 'Pasig', 'Metro Manila', '1605', 'Philippines', 'Filipino', 9, 5, 1, 1, NULL, '2019-02-10', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, NULL, 'HVL-PHA-010', 'Isabel', 'Cruz', 'Martinez', '1994-01-28', 'Female', 'Single', 'isabel.martinez@hvil.ph', '09260123456', '7777 Katipunan Ave', 'Quezon City', 'Metro Manila', '1108', 'Philippines', 'Filipino', 10, 5, 1, 1, 9, '2022-03-15', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 4, 'HVL-ADM-011', 'Ricardo', 'Alonzo', 'Gonzales', '1975-09-05', 'Male', 'Married', 'ricardo.gonzales@hvil.ph', '09271234567', '8888 Ayala Ave', 'Makati', 'Metro Manila', '1226', 'Philippines', 'Filipino', 11, 6, 1, 1, NULL, '2018-11-01', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, NULL, 'HVL-ADM-012', 'Carmen', 'Villanueva', 'Fernando', '1982-02-14', 'Female', 'Married', 'carmen.fernando@hvil.ph', '09282345678', '9999 Buendia Ave', 'Makati', 'Metro Manila', '1233', 'Philippines', 'Filipino', 12, 6, 1, 1, 11, '2020-05-20', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 5, 'HVL-HR-013', 'Ma. Teresa', 'Santos', 'Domingo', '1984-03-21', 'Female', 'Married', 'mt.domingo@hvil.ph', '09293456789', '1111 Timog Ave', 'Quezon City', 'Metro Manila', '1103', 'Philippines', 'Filipino', 13, 7, 1, 1, 11, '2019-08-15', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 6, 'HVL-FIN-014', 'Roberto', 'Sy', 'Tan', '1978-11-11', 'Male', 'Married', 'roberto.tan@hvil.ph', '09304567890', '2222 Chino Roces', 'Makati', 'Metro Manila', '1230', 'Philippines', 'Filipino', 14, 8, 1, 1, 11, '2018-03-01', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, NULL, 'HVL-ER-015', 'Michael', 'Angelo', 'Rivera', '1995-10-09', 'Male', 'Single', 'michael.rivera@hvil.ph', '09315678901', '3333 Banawe St', 'Quezon City', 'Metro Manila', '1114', 'Philippines', 'Filipino', 1, 1, 2, 1, 1, '2024-06-01', 'Active', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 1, 'Active', '2026-01-01 00:00:00', '2020-01-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 2, 'Active', '2026-01-01 00:00:00', '2020-03-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 1, 'Active', '2026-01-01 00:00:00', '2021-06-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 1, 'Active', '2026-01-01 00:00:00', '2022-01-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 3, 'Active', '2026-01-01 00:00:00', '2019-03-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 3, 'Active', '2026-01-01 00:00:00', '2023-05-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 4, 'Active', '2026-01-01 00:00:00', '2020-08-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 4, 'Active', '2026-01-01 00:00:00', '2021-10-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 3, 'Active', '2026-01-01 00:00:00', '2019-02-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 3, 'Active', '2026-01-01 00:00:00', '2022-03-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 5, 'Active', '2026-01-01 00:00:00', '2018-11-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 12, 5, 'Active', '2026-01-01 00:00:00', '2020-05-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 13, 5, 'Active', '2026-01-01 00:00:00', '2019-08-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 14, 5, 'Active', '2026-01-01 00:00:00', '2018-03-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 15, 1, 'Active', '2026-01-01 00:00:00', '2024-06-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `employee_contract_assignments`
--

INSERT INTO `employee_contract_assignments` (`id`, `employee_id`, `contract_id`, `effective_from`, `effective_to`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2020-01-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 2, '2020-03-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 2, '2021-06-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 2, '2022-01-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 3, '2019-03-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 3, '2023-05-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 4, '2020-08-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 4, '2021-10-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 5, '2019-02-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 5, '2022-03-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 6, '2018-11-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 12, 6, '2020-05-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 13, 6, '2019-08-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 14, 6, '2018-03-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 15, 1, '2024-06-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 'PRC License', 'PRC ID - Dr. Juan Cruz', NULL, '2015-03-20', '2027-03-20', 'Active PRC License', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 'PhilHealth', 'PhilHealth ID', NULL, '2015-04-15', '2027-04-15', 'Updated', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 1, 'PAG-IBIG', 'PAG-IBIG ID', NULL, '2015-05-10', NULL, 'Active', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 1, 'SSS', 'SSS ID', NULL, '2015-06-05', NULL, 'Active', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 1, 'TIN', 'TIN ID', NULL, '2015-07-01', NULL, 'Active', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 3, 'PRC License', 'PRC ID - Jose Santos', NULL, '2018-08-15', '2026-08-15', 'Active', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 5, 'PRC License', 'PRC ID - Pedro Villanueva', NULL, '2016-09-10', '2026-09-10', 'Medical Technologist', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 7, 'PRC License', 'PRC ID - Carlos Torres', NULL, '2017-10-20', '2027-10-20', 'Radiologist', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 'PRC License', 'PRC ID - Antonio Lopez', NULL, '2016-11-05', '2026-11-05', 'Pharmacist', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 11, 'Professional', 'MBA Diploma', NULL, '2018-12-01', NULL, 'Graduate Studies', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 13, 'Certification', 'HR Certification', NULL, '2019-01-15', '2027-01-15', 'HRM Professional', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 14, 'Certification', 'CPA License', NULL, '2017-02-20', '2027-02-20', 'Certified Public Accountant', 13, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 3, 2, '2020-01-15', '2020-02-01', '2027-01-31', '2027-01-15', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 15000.00, 45000.00, 60000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 2, 1, '2020-03-10', '2020-04-01', '2027-03-31', '2027-03-10', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 8000.00, 24000.00, 32000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 2, 1, '2021-06-01', '2021-06-15', '2027-06-14', '2027-06-01', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 4500.00, 13500.00, 18000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 2, 1, '2022-01-10', '2022-01-25', '2027-01-24', '2027-01-10', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 4500.00, 13500.00, 18000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 4, 3, '2019-03-20', '2019-04-01', '2027-03-31', '2027-03-20', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 10000.00, 30000.00, 40000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 4, 3, '2023-05-15', '2023-05-30', '2027-05-29', '2027-05-15', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 6000.00, 18000.00, 24000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 5, 4, '2020-08-20', '2020-09-01', '2027-08-31', '2027-08-20', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 12000.00, 36000.00, 48000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 5, 4, '2021-10-01', '2021-10-15', '2027-10-14', '2027-10-01', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 7000.00, 21000.00, 28000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 6, 5, '2019-02-10', '2019-02-20', '2027-02-19', '2027-02-10', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 14000.00, 42000.00, 56000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 6, 5, '2022-03-15', '2022-03-25', '2027-03-24', '2027-03-15', NULL, 'active', 'employee_only', 30, NULL, NULL, 1, 8000.00, 24000.00, 32000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 1, 2, '2018-11-01', '2018-11-15', '2027-11-14', '2027-11-01', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 12000.00, 48000.00, 60000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 13, 1, 2, '2019-08-15', '2019-09-01', '2027-08-31', '2027-08-15', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 9000.00, 36000.00, 45000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 14, 1, 2, '2018-03-01', '2018-03-15', '2027-03-14', '2027-03-01', NULL, 'active', 'with_dependents', 30, NULL, NULL, 1, 9000.00, 36000.00, 45000.00, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
  `approved_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_movements`
--

INSERT INTO `employee_movements` (`movement_id`, `employee_id`, `movement_type`, `effective_date`, `from_job_title_id`, `to_job_title_id`, `from_department_id`, `to_department_id`, `from_location_id`, `to_location_id`, `from_salary`, `to_salary`, `reason`, `status`, `approved_by`, `approved_date`, `created_at`, `updated_at`, `archived`) VALUES
(1, 3, 'Promotion', '2024-01-01', 4, 3, 2, 2, NULL, NULL, 28000.00, 35000.00, 'Promotion to Head Nurse', 'Approved', 11, '2024-01-01 00:00:00', '2024-01-01 00:00:00', '2024-01-01 00:00:00', 0),
(2, 6, 'Promotion', '2025-01-01', 6, 5, 3, 3, NULL, NULL, 25000.00, 32000.00, 'Promotion to Senior Lab Tech', 'Approved', 5, '2025-01-01 00:00:00', '2025-01-01 00:00:00', '2025-01-01 00:00:00', 0),
(3, 10, 'Promotion', '2025-06-01', 10, 9, 5, 5, NULL, NULL, 30000.00, 38000.00, 'Promotion to Senior Pharmacist', 'Approved', 9, '2025-06-01 00:00:00', '2025-06-01 00:00:00', '2025-06-01 00:00:00', 0),
(4, 4, 'Status Change', '2025-03-15', NULL, NULL, NULL, NULL, NULL, NULL, 28000.00, 30000.00, 'Regularization', 'Approved', 3, '2025-03-15 00:00:00', '2025-03-15 00:00:00', '2025-03-15 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `employee_payroll_profiles`
--

CREATE TABLE `employee_payroll_profiles` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `payroll_eligible` tinyint(1) DEFAULT 1,
  `payroll_frequency` enum('Weekly','Semi-Monthly','Monthly') DEFAULT 'Monthly',
  `pay_schedule_day_1` int(11) DEFAULT 15,
  `pay_schedule_day_2` int(11) DEFAULT 30,
  `pay_type` enum('Per Duty','Per Shift','Hourly','Daily','Monthly') DEFAULT 'Monthly',
  `payroll_status` varchar(50) DEFAULT 'Active',
  `tax_status` varchar(50) DEFAULT NULL,
  `sss_status` varchar(50) DEFAULT NULL,
  `philhealth_status` varchar(50) DEFAULT NULL,
  `pagibig_status` varchar(50) DEFAULT NULL,
  `bank_account_holder` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_code` varchar(10) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_type` varchar(50) DEFAULT NULL,
  `account_status` varchar(50) DEFAULT NULL,
  `tax_identification_number` varchar(50) DEFAULT NULL,
  `sss_number` varchar(50) DEFAULT NULL,
  `philhealth_number` varchar(50) DEFAULT NULL,
  `pagibig_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_payroll_profiles`
--

INSERT INTO `employee_payroll_profiles` (`id`, `employee_id`, `payroll_eligible`, `payroll_frequency`, `pay_schedule_day_1`, `pay_schedule_day_2`, `pay_type`, `payroll_status`, `tax_status`, `sss_status`, `philhealth_status`, `pagibig_status`, `bank_account_holder`, `bank_account_number`, `bank_code`, `bank_name`, `account_type`, `account_status`, `tax_identification_number`, `sss_number`, `philhealth_number`, `pagibig_number`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Juan D. Cruz', '1234567890', 'BDO', 'BDO Unibank', 'Savings', 'Active', '123-456-789-000', '34-5678901-2', '12-345678901-2', '1234-5678-9012', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Maria S. Reyes', '2345678901', 'BPI', 'BPI', 'Savings', 'Active', '234-567-890-001', '45-6789012-3', '23-456789012-3', '2345-6789-0123', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Single', 'Active', 'Active', 'Active', 'Jose M. Santos', '3456789012', 'Metrobank', 'Metrobank', 'Savings', 'Active', '345-678-901-002', '56-7890123-4', '34-567890123-4', '3456-7890-1234', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Single', 'Active', 'Active', 'Active', 'Ana L. Fernandez', '4567890123', 'BDO', 'BDO Unibank', 'Savings', 'Active', '456-789-012-003', '67-8901234-5', '45-678901234-5', '4567-8901-2345', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Pedro G. Villanueva', '5678901234', 'PNB', 'PNB', 'Savings', 'Active', '567-890-123-004', '78-9012345-6', '56-789012345-6', '5678-9012-3456', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Single', 'Active', 'Active', 'Active', 'Luisa D. Mendoza', '6789012345', 'BPI', 'BPI', 'Savings', 'Active', '678-901-234-005', '89-0123456-7', '67-890123456-7', '6789-0123-4567', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Carlos J. Torres', '7890123456', 'Metrobank', 'Metrobank', 'Savings', 'Active', '789-012-345-006', '90-1234567-8', '78-901234567-8', '7890-1234-5678', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Sofia A. Garcia', '8901234567', 'BDO', 'BDO Unibank', 'Savings', 'Active', '890-123-456-007', '01-2345678-9', '89-012345678-9', '8901-2345-6789', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Antonio R. Lopez', '9012345678', 'PNB', 'PNB', 'Savings', 'Active', '901-234-567-008', '12-3456789-0', '90-123456789-0', '9012-3456-7890', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Single', 'Active', 'Active', 'Active', 'Isabel C. Martinez', '0123456789', 'BPI', 'BPI', 'Savings', 'Active', '012-345-678-009', '23-4567890-1', '01-234567890-1', '0123-4567-8901', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Ricardo A. Gonzales', '1122334455', 'BDO', 'BDO Unibank', 'Savings', 'Active', '112-233-445-566', '34-5566778-9', '11-223344556-7', '1122-3344-5566', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 13, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Ma. Teresa S. Domingo', '2233445566', 'BPI', 'BPI', 'Savings', 'Active', '223-344-556-677', '45-6677889-0', '22-334455667-8', '2233-4455-6677', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 14, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Married', 'Active', 'Active', 'Active', 'Roberto S. Tan', '3344556677', 'Metrobank', 'Metrobank', 'Savings', 'Active', '334-455-667-788', '56-7788990-1', '33-445566778-9', '3344-5566-7788', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 15, 1, 'Semi-Monthly', 15, 30, 'Monthly', 'Active', 'Single', 'Active', 'Active', 'Active', 'Michael A. Rivera', '4455667788', 'BDO', 'BDO Unibank', 'Savings', 'Active', '445-566-778-899', '67-8899001-2', '44-556677889-0', '4455-6677-8899', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `employee_salaries`
--

INSERT INTO `employee_salaries` (`id`, `employee_id`, `salary_type`, `basic_rate`, `effective_date`, `tax_status`, `payroll_eligible`, `bank_name`, `bank_account`, `created_at`, `updated_at`) VALUES
(1, 1, 'Monthly', 85000.00, '2024-01-01', 'Married', 1, 'BDO', '1234567890', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 'Monthly', 65000.00, '2024-01-01', 'Married', 1, 'BPI', '2345678901', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 'Monthly', 45000.00, '2024-01-01', 'Single', 1, 'Metrobank', '3456789012', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 'Monthly', 30000.00, '2024-01-01', 'Single', 1, 'BDO', '4567890123', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 'Monthly', 48000.00, '2024-01-01', 'Married', 1, 'PNB', '5678901234', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 'Monthly', 32000.00, '2025-01-01', 'Single', 1, 'BPI', '6789012345', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 'Monthly', 52000.00, '2024-01-01', 'Married', 1, 'Metrobank', '7890123456', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 'Monthly', 35000.00, '2024-01-01', 'Married', 1, 'BDO', '8901234567', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 'Monthly', 55000.00, '2024-01-01', 'Married', 1, 'PNB', '9012345678', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 'Monthly', 38000.00, '2025-06-01', 'Single', 1, 'BPI', '0123456789', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 'Monthly', 120000.00, '2024-01-01', 'Married', 1, 'BDO', '1122334455', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 12, 'Monthly', 75000.00, '2024-01-01', 'Married', 1, 'BPI', '2233445566', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 13, 'Monthly', 65000.00, '2024-01-01', 'Married', 1, 'BPI', '2233445566', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 14, 'Monthly', 80000.00, '2024-01-01', 'Married', 1, 'Metrobank', '3344556677', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 15, 'Monthly', 65000.00, '2024-06-01', 'Single', 1, 'BDO', '4455667788', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 1, 'Active', '2026-01-01 00:00:00', '2020-01-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 1, 'Active', '2026-01-01 00:00:00', '2020-03-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 2, 'Active', '2026-01-01 00:00:00', '2021-06-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 2, 'Active', '2026-01-01 00:00:00', '2022-01-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 1, 'Active', '2026-01-01 00:00:00', '2019-03-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 2, 'Active', '2026-01-01 00:00:00', '2023-05-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 1, 'Active', '2026-01-01 00:00:00', '2020-08-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 2, 'Active', '2026-01-01 00:00:00', '2021-10-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 1, 'Active', '2026-01-01 00:00:00', '2019-02-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 1, 'Active', '2026-01-01 00:00:00', '2022-03-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 1, 'Active', '2026-01-01 00:00:00', '2018-11-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 12, 1, 'Active', '2026-01-01 00:00:00', '2020-05-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 13, 1, 'Active', '2026-01-01 00:00:00', '2019-08-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 14, 1, 'Active', '2026-01-01 00:00:00', '2018-03-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 15, 2, 'Active', '2026-01-01 00:00:00', '2024-06-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'Regular', 'Regular Full-time Employee', 90, NULL, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Probationary', 'Probationary Employee', 180, NULL, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Contractual', 'Contract-based Employee', NULL, 12, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Part-time', 'Part-time employment', NULL, NULL, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Intern', 'Internship program', NULL, 6, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `government_reports`
--

INSERT INTO `government_reports` (`id`, `agency`, `reporting_period_start`, `reporting_period_end`, `reporting_date`, `report_type`, `total_employees`, `total_contributions`, `submission_status`, `submitted_by`, `submitted_date`, `confirmation_reference`, `file_path`, `created_at`, `updated_at`) VALUES
(1, 'SSS', '2026-01-01', '2026-01-31', '2026-02-10', 'Monthly Contribution Report', 15, 45600.00, 'submitted', 14, '2026-02-10 00:00:00', 'SSS-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'PhilHealth', '2026-01-01', '2026-01-31', '2026-02-10', 'Monthly Premium Report', 15, 13500.00, 'submitted', 14, '2026-02-10 00:00:00', 'PHIC-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Pag-IBIG', '2026-01-01', '2026-01-31', '2026-02-10', 'Monthly Contribution Report', 15, 3000.00, 'submitted', 14, '2026-02-10 00:00:00', 'PAGIBIG-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'BIR', '2026-01-01', '2026-01-31', '2026-02-15', 'Monthly Withholding Tax Report', 15, 85400.00, 'submitted', 14, '2026-02-15 00:00:00', 'BIR-2316-2026-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'SSS', '2026-02-01', '2026-02-28', '2026-03-10', 'Monthly Contribution Report', 15, 45600.00, 'pending', 14, NULL, NULL, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 'ER-CONSULTANT', 'Consultant', 'Senior ER Consultant level', 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 'ER-SPECIALIST', 'Specialist', 'ER Medical Specialist', 2, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 1, 'ER-RESIDENT', 'Resident', 'ER Resident Doctor', 3, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 2, 'NURSE-MGR', 'Nurse Manager', 'Head Nurse/Manager level', 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 2, 'NURSE-SR', 'Senior Nurse', 'Senior Staff Nurse', 2, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 2, 'NURSE-JR', 'Junior Nurse', 'Junior Staff Nurse', 3, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 3, 'LAB-MGR', 'Lab Manager', 'Laboratory Manager', 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 3, 'LAB-SR', 'Senior Med Tech', 'Senior Medical Technologist', 2, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 3, 'LAB-JR', 'Junior Med Tech', 'Junior Medical Technologist', 3, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 4, 'RAD-MGR', 'Radiology Manager', 'Radiology Department Manager', 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 4, 'RAD-SR', 'Senior Radiologist', 'Senior Radiologic Technologist', 2, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 4, 'RAD-JR', 'Junior Radiologist', 'Junior Radiologic Technologist', 3, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 5, 'PHA-MGR', 'Pharmacy Manager', 'Pharmacy Department Manager', 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 5, 'PHA-SR', 'Senior Pharmacist', 'Senior Pharmacist', 2, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 5, 'PHA-JR', 'Junior Pharmacist', 'Junior Pharmacist', 3, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, 6, 'ADM-EXEC', 'Executive', 'Hospital Executive', 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(17, 6, 'ADM-MGR', 'Manager', 'Administrative Manager', 2, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(18, 6, 'ADM-STAFF', 'Staff', 'Administrative Staff', 3, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `hmo_billing_adjustments`
--

INSERT INTO `hmo_billing_adjustments` (`id`, `reconciliation_id`, `adjustment_type`, `amount`, `reason`, `reference_discrepancy_id`, `adjustment_status`, `approved_by`, `approved_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'credit', 2500.00, 'Premium adjustment for additional dependent', 1, 'approved', 'Finance Manager', '2026-01-20 00:00:00', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 'debit', 1500.00, 'Service fee adjustment', 2, 'approved', 'Finance Manager', '2026-01-21 00:00:00', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 'refund', 5000.00, 'Duplicate payment refund', 3, 'approved', 'Finance Manager', '2026-01-22 00:00:00', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 1, 1, 'amount_mismatch', 25000.00, 22500.00, -2500.00, 'Deductible applied', 'resolved', 'Finance Manager', '2026-01-20 00:00:00', 'Adjusted based on policy terms', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 3, 3, 'coverage_issue', 8000.00, 6500.00, -1500.00, 'Service not fully covered', 'resolved', 'Finance Manager', '2026-01-21 00:00:00', 'Partial coverage applied', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 5, 5, 'duplicate_claim', 15000.00, 15000.00, 0.00, 'Duplicate claim detected', 'resolved', 'Finance Manager', '2026-01-22 00:00:00', 'Duplicate removed', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'REC-MAX-202601', '2026-01-01', 2, 185000.00, 182500.00, 182500.00, -2500.00, -1.35, 'approved', 'Finance Manager', '2026-02-15 00:00:00', 'January 2026 reconciliation completed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'REC-INT-202601', '2026-01-01', 1, 135000.00, 133500.00, 133500.00, -1500.00, -1.11, 'approved', 'Finance Manager', '2026-02-15 00:00:00', 'All discrepancies resolved', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'REC-MED-202601', '2026-01-01', 3, 158000.00, 158000.00, 158000.00, 0.00, 0.00, 'approved', 'Finance Manager', '2026-02-15 00:00:00', 'No discrepancies', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'REC-STL-202601', '2026-01-01', 4, 225000.00, 224000.00, 224000.00, -1000.00, -0.44, 'approved', 'Finance Manager', '2026-02-15 00:00:00', 'Minor adjustments applied', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'REC-PRE-202601', '2026-01-01', 5, 168000.00, 168000.00, 168000.00, 0.00, 0.00, 'approved', 'Finance Manager', '2026-02-15 00:00:00', 'Reconciled', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 1, NULL, 3, 2, 'CLM-2026-001', 'ER Consultation', '2026-01-15', 'HVill Medical Center', 'Dr. Juan Cruz', 25000.00, 22500.00, 'paid', '2026-01-20', '2026-02-01', 'ER visit with diagnostics', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 1, 1, 3, 2, 'CLM-2026-002', 'Pediatric Consultation', '2026-01-20', 'HVill Medical Center', 'Dr. Maria Reyes', 8000.00, 8000.00, 'paid', '2026-01-25', '2026-02-01', 'Dependent consultation', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 3, NULL, 2, 1, 'CLM-2026-003', 'Outpatient Procedure', '2026-01-10', 'HVill Medical Center', 'Dr. Jose Santos', 8000.00, 6500.00, 'paid', '2026-01-18', '2026-02-01', 'Minor procedure', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 5, 5, 2, 4, 3, 'CLM-2026-004', 'Laboratory Services', '2026-01-05', 'HVill Lab', 'Dr. Pedro Villanueva', 12000.00, 12000.00, 'paid', '2026-01-12', '2026-02-01', 'Comprehensive lab work', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 5, 3, 4, 3, 'CLM-2026-005', 'Laboratory Services', '2026-01-18', 'HVill Lab', 'Dr. Pedro Villanueva', 15000.00, 15000.00, 'paid', '2026-01-25', '2026-02-01', 'Duplicate claim', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 7, 7, 4, 5, 4, 'CLM-2026-006', 'Radiology', '2026-01-22', 'HVill Radiology', 'Dr. Carlos Torres', 25000.00, 25000.00, 'paid', '2026-01-28', '2026-02-01', 'MRI scan', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 9, 9, 5, 6, 5, 'CLM-2026-007', 'Prescription', '2026-01-25', 'HVill Pharmacy', 'Dr. Antonio Lopez', 9500.00, 9500.00, 'paid', '2026-01-28', '2026-02-01', 'Maintenance medication', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 11, 11, NULL, 1, 2, 'CLM-2026-008', 'Executive Check-up', '2026-01-28', 'HVill Executive Clinic', 'Dr. Ricardo Gonzales', 35000.00, 35000.00, 'paid', '2026-02-01', '2026-02-15', 'Annual executive physical', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 2, 2, 6, 2, 1, 'CLM-2026-009', 'Outpatient Consultation', '2026-02-05', 'HVill OPD', 'Dr. Maria Reyes', 5500.00, NULL, 'pending', NULL, NULL, 'Regular check-up', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'HVL-IC-001', 'Insurance_Card', 'Maxicare Insurance Card', 1, 3, 2, '2026-01-01', '2026-01-01', '2027-01-01', 'active', NULL, NULL, NULL, NULL, 'Employee insurance card', 'HR Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'HVL-IC-002', 'Insurance_Card', 'Intellicare Insurance Card', 2, 2, 1, '2026-01-01', '2026-01-01', '2027-01-01', 'active', NULL, NULL, NULL, NULL, 'Employee insurance card', 'HR Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'HVL-IC-003', 'Insurance_Card', 'Medicard Insurance Card', 5, 4, 3, '2026-01-01', '2026-01-01', '2027-01-01', 'active', NULL, NULL, NULL, NULL, 'Employee insurance card', 'HR Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'HVL-HB-001', 'Member_Handbook', 'Maxicare Member Handbook 2026', 1, 3, 2, '2026-01-01', '2026-01-01', '2027-12-31', 'active', NULL, NULL, NULL, NULL, 'Member handbook', 'HR Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'HVL-PA-001', 'Pre_authorization', 'Surgery Pre-authorization', 7, 5, 4, '2026-01-15', '2026-01-15', '2026-03-15', 'active', NULL, NULL, NULL, NULL, 'Approved for procedure', 'HR Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'HVL-BS-001', 'Benefits_Summary', 'St. Luke\'s Benefits Summary', 9, 6, 5, '2026-01-01', '2026-01-01', '2027-12-31', 'active', NULL, NULL, NULL, NULL, 'Benefits summary', 'HR Admin', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 'Maria Cruz', 'Spouse', '1982-08-22', 'Female', 1, '2020-01-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 'Jose Cruz Jr.', 'Child', '2015-05-10', 'Male', 1, '2020-01-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 2, 'Roberto Reyes', 'Spouse', '1982-11-30', 'Male', 1, '2020-03-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 5, 'Luz Villanueva', 'Spouse', '1990-07-18', 'Female', 1, '2019-03-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 'Ana Villanueva', 'Child', '2018-09-25', 'Female', 1, '2019-03-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 7, 'Elena Torres', 'Spouse', '1988-06-14', 'Female', 1, '2020-08-20', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 9, 'Carmen Lopez', 'Spouse', '1989-04-05', 'Female', 1, '2019-02-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 9, 'Miguel Lopez', 'Child', '2017-11-15', 'Male', 1, '2019-02-10', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 'Sofia Lopez', 'Child', '2020-03-22', 'Female', 1, '2020-04-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 11, 'Clara Gonzales', 'Spouse', '1978-12-10', 'Female', 1, '2018-11-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 12, 'Ramon Domingo', 'Spouse', '1980-08-05', 'Male', 1, '2019-09-01', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 13, 'Carmelita Tan', 'Spouse', '1980-03-20', 'Female', 1, '2018-03-15', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 1, 'Birth', '2015-05-10', 'Child birth - added Jose Cruz Jr. to coverage', '2015-05-01', '2015-06-10', 'processed', NULL, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 3, NULL, 'Marriage', '2024-12-15', 'Marriage - to add spouse to coverage', '2024-12-01', '2025-01-15', 'pending', NULL, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 9, 9, 'Birth', '2020-03-22', 'Child birth - added Sofia Lopez to coverage', '2020-03-15', '2020-04-22', 'processed', NULL, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 15, NULL, 'Marriage', '2025-11-30', 'Marriage - planning to add spouse', '2025-11-15', '2025-12-30', 'approved', NULL, NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 2, 'MX-EXEC-2026', 'Maxicare Executive Plan', 'Executive', 'Comprehensive executive coverage with international benefits', 60000.00, 30000.00, 5000.00, 200000.00, 5000.00, 10, NULL, 5000, 350, 1, '2026-01-01', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 'IC-PREM-2026', 'Intellicare Premium', 'Premium', 'Premium HMO coverage with extensive network', 32000.00, 16000.00, 2666.67, 150000.00, 3000.00, 15, NULL, 4500, 320, 1, '2026-01-01', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 2, 'MX-PREM-2026', 'Maxicare Premium', 'Premium', 'Full premium coverage with wellness benefits', 45000.00, 22500.00, 3750.00, 180000.00, 4000.00, 15, NULL, 4800, 340, 1, '2026-01-01', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 3, 'MC-PREM-2026', 'Medicard Premium', 'Premium', 'Comprehensive health coverage', 40000.00, 20000.00, 3333.33, 160000.00, 3500.00, 15, NULL, 4200, 300, 1, '2026-01-01', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 4, 'SL-PREMIER', 'St. Luke\'s Premier', 'Premium', 'Premium coverage with St. Luke\'s network', 48000.00, 24000.00, 4000.00, 200000.00, 4500.00, 15, NULL, 3800, 280, 1, '2026-01-01', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 5, 'PHIL-STD', 'Philhealth Standard', 'Standard', 'Basic government HMO coverage', 18000.00, 9000.00, 1500.00, 80000.00, 0.00, 20, NULL, 3500, 420, 1, '2026-01-01', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 1, 'IC-BASIC', 'Intellicare Basic', 'Basic', 'Essential coverage for outpatient services', 24000.00, 12000.00, 2000.00, 100000.00, 2000.00, 20, NULL, 4000, 280, 1, '2026-01-01', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, 2, '2026-01-31', 60000.00, 15000.00, 45000.00, 'paid', '2026-01-15', 'Bank Transfer', 'MX-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 2, 1, '2026-01-31', 32000.00, 8000.00, 24000.00, 'paid', '2026-01-15', 'Bank Transfer', 'IC-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 3, 1, '2026-01-31', 18000.00, 4500.00, 13500.00, 'paid', '2026-01-15', 'Bank Transfer', 'IC-2026-01-002', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 4, 1, '2026-01-31', 18000.00, 4500.00, 13500.00, 'paid', '2026-01-15', 'Bank Transfer', 'IC-2026-01-003', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 3, '2026-01-31', 40000.00, 10000.00, 30000.00, 'paid', '2026-01-15', 'Bank Transfer', 'MC-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 6, 3, '2026-01-31', 24000.00, 6000.00, 18000.00, 'paid', '2026-01-15', 'Bank Transfer', 'MC-2026-01-002', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 4, '2026-01-31', 48000.00, 12000.00, 36000.00, 'paid', '2026-01-15', 'Bank Transfer', 'SL-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 8, 4, '2026-01-31', 28000.00, 7000.00, 21000.00, 'paid', '2026-01-15', 'Bank Transfer', 'SL-2026-01-002', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 9, 5, '2026-01-31', 56000.00, 14000.00, 42000.00, 'paid', '2026-01-15', 'Bank Transfer', 'PH-2026-01-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 10, 5, '2026-01-31', 32000.00, 8000.00, 24000.00, 'paid', '2026-01-15', 'Bank Transfer', 'PH-2026-01-002', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 11, 2, '2026-01-31', 60000.00, 12000.00, 48000.00, 'paid', '2026-01-15', 'Bank Transfer', 'MX-2026-01-002', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 12, 2, '2026-01-31', 45000.00, 9000.00, 36000.00, 'paid', '2026-01-15', 'Bank Transfer', 'MX-2026-01-003', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 13, 2, '2026-01-31', 45000.00, 9000.00, 36000.00, 'paid', '2026-01-15', 'Bank Transfer', 'MX-2026-01-004', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 1, 2, '2026-02-28', 60000.00, 15000.00, 45000.00, 'paid', '2026-02-15', 'Bank Transfer', 'MX-2026-02-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 2, 1, '2026-02-28', 32000.00, 8000.00, 24000.00, 'paid', '2026-02-15', 'Bank Transfer', 'IC-2026-02-001', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'IC001', 'Intellicare', 'Leading HMO provider in the Philippines', '12th Floor, 6789 Ayala Avenue, Makati City', '02-8888-7777', 'corporate@intellicare.com.ph', 'Ms. Anna Santos', 'VP for Corporate Sales', 'www.intellicare.com.ph', 'IC-ACC-2020-001', 1995, 850000, 320, '320+ partner hospitals', 'Active', 'Corporate', '2024-01-01', '2026-12-31', 30, 75.00, 25.00, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'MX001', 'Maxicare', 'One of the largest HMO providers in the Philippines', '25th Floor, 1234 Ayala Avenue, Makati City', '02-8888-8888', 'employeebenefits@maxicare.com.ph', 'Mr. Jose Reyes', 'Director - Group Accounts', 'www.maxicare.com.ph', 'MX-ACC-2019-002', 1987, 1200000, 350, '350+ partner hospitals', 'Active', 'Corporate', '2024-01-01', '2026-12-31', 30, 80.00, 20.00, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'MC001', 'Medicard Philippines', 'Comprehensive health maintenance organization', '15th Floor, 5678 Ayala Avenue, Makati City', '02-8888-6666', 'groups@medicard.com.ph', 'Dr. Maria Gonzales', 'President', 'www.medicard.com.ph', 'MC-ACC-2021-003', 1998, 650000, 300, '300+ partner hospitals', 'Active', 'Corporate', '2024-01-01', '2026-12-31', 30, 75.00, 25.00, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'SL001', 'St. Luke\'s Medical Center HMO', 'Premium healthcare network', '32nd Floor, 7890 Ayala Avenue, Makati City', '02-8888-9999', 'corporate@stlukes.com.ph', 'Mr. Ricardo Cruz', 'VP - Health Plans', 'www.stlukes.com.ph', 'SL-ACC-2020-004', 2005, 450000, 280, '280+ partner hospitals', 'Active', 'Corporate', '2024-01-01', '2026-12-31', 30, 75.00, 25.00, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'PH001', 'PhilHealth', 'Government health insurance program', 'Citystate Centre, 789 Shaw Blvd, Pasig City', '02-8441-7444', 'employer.services@philhealth.gov.ph', 'Dr. Emmanuel Lopez', 'VP - Employer Services', 'www.philhealth.gov.ph', 'PH-GOV-001', 1995, 9500000, 420, '420+ government hospitals', 'Active', 'Government', '2024-01-01', '2026-12-31', 30, 50.00, 50.00, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `incentive_events`
--

INSERT INTO `incentive_events` (`id`, `employee_id`, `duty_date`, `procedure_type_id`, `quantity`, `approved_by`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-02-01', 1, 5, 11, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(2, 1, '2026-02-01', 2, 8, 11, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(3, 3, '2026-02-01', 3, 2, 1, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(4, 4, '2026-02-01', 4, 3, 3, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(5, 5, '2026-02-01', 5, 10, 5, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(6, 6, '2026-02-01', 5, 6, 5, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(7, 7, '2026-02-01', 6, 4, 7, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(8, 8, '2026-02-01', 6, 3, 7, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(9, 15, '2026-02-01', 1, 4, 1, 'approved', '2026-02-01 00:00:00', '2026-02-01 00:00:00');

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
(1, 'ER_ADMISSION', 'ER Admission', 'Emergency room patient admission', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'ER_CONSULT', 'ER Consultation', 'Emergency room consultation', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'MINOR_SURGERY', 'Minor Surgery', 'Minor surgical procedure', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'WOUND_CARE', 'Wound Care', 'Wound treatment and care', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'LAB_PROC', 'Laboratory Procedure', 'Laboratory test/procedure', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'RAD_PROC', 'Radiology Procedure', 'X-ray/imaging procedure', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'PHARMA_DISP', 'Pharmacy Dispensing', 'Medication dispensing', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'VACCINATION', 'Vaccination', 'Vaccine administration', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'MED_CERT', 'Medical Certificate', 'Medical certificate issuance', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'ER Consultant', 'Senior Emergency Room Consultant', 1, 8, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'OPD Specialist', 'Outpatient Department Specialist', 9, 7, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Head Nurse', 'Head of Nursing Services', 2, 7, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Staff Nurse', 'Registered Nurse', 2, 6, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Chief Medical Technologist', 'Head of Laboratory Services', 3, 7, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Medical Technologist', 'Laboratory Technician', 3, 6, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'Chief Radiologist', 'Head of Radiology Department', 4, 7, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'Radiologic Technologist', 'X-ray/Imaging Technician', 4, 6, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'Chief Pharmacist', 'Head of Pharmacy', 5, 7, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 'Pharmacist', 'Licensed Pharmacist', 5, 6, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 'Hospital Administrator', 'Chief Operating Officer', 6, 9, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 'Administrative Officer', 'Administrative Support', 6, 6, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 'HR Manager', 'Human Resources Manager', 7, 7, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 'Finance Manager', 'Finance and Accounting Manager', 8, 8, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'HVL-MAIN', 'HVill Main Hospital', 'Head Office', 'HVill Medical Center', '123 Hospital Drive', 'Manila', 'Metro Manila', 'Philippines', '1000', '02-8888-1234', NULL, 'info@hvil.ph', 11, 1, 1, 1, 1, 'Main hospital facility', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'HVL-ANNEX', 'HVill Annex', 'Branch', 'HVill Diagnostic Center', '456 Health Avenue', 'Quezon City', 'Metro Manila', 'Philippines', '1100', '02-8888-5678', NULL, 'annex@hvil.ph', 5, 1, 1, 1, 1, 'Diagnostic and laboratory annex', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'HVL-CLINIC', 'HVill Outpatient Clinic', 'Branch', 'HVill Medical Arts', '789 Wellness Street', 'Makati', 'Metro Manila', 'Philippines', '1200', '02-8888-9012', NULL, 'clinic@hvil.ph', 2, 1, 1, 1, 0, 'Outpatient clinic', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 1, '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEF', '2026-02-24 00:00:00', 0, 1, '2026-02-23 14:00:00'),
(2, 2, '$2y$10$bcdefghijklmnopqrstuvwxyz1234567890ABCDEFG', '2026-02-24 00:00:00', 0, 1, '2026-02-23 14:00:00'),
(3, 3, '$2y$10$cdefghijklmnopqrstuvwxyz1234567890ABCDEFGH', '2026-02-24 00:00:00', 0, 1, '2026-02-23 14:00:00');

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
(1, 1, 'Submit PRC License', 'HR', '2020-01-20', '2020-01-18', 13, 'Completed', 'License verified', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 'Hospital Orientation', 'HR', '2020-01-25', '2020-01-22', 13, 'Completed', 'Completed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 1, 'IT Equipment Setup', 'IT', '2020-01-20', '2020-01-19', 11, 'Completed', 'Laptop and access provided', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 3, 'Submit PRC License', 'HR', '2021-06-05', '2021-06-03', 13, 'Completed', 'Verified', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 3, 'Nursing Orientation', 'Nursing', '2021-06-10', '2021-06-08', 3, 'Completed', 'Department orientation', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 5, 'Lab Orientation', 'Laboratory', '2019-03-25', '2019-03-23', 5, 'Completed', 'Lab procedures training', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 7, 'Radiology Training', 'Radiology', '2020-08-25', '2020-08-24', 7, 'Completed', 'Equipment training', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 9, 'Pharmacy Induction', 'Pharmacy', '2019-02-15', '2019-02-14', 9, 'Completed', 'Inventory system training', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 11, 'Executive Onboarding', 'Board', '2018-11-05', '2018-11-03', 13, 'Completed', 'Executive orientation', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 15, 'ER Department Orientation', 'Emergency', '2024-06-05', '2024-06-04', 1, 'Completed', 'ER protocols training', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `payroll_adjustments`
--

INSERT INTO `payroll_adjustments` (`id`, `employee_id`, `payroll_run_id`, `adjustment_type`, `amount`, `reason`, `effective_date`, `status`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '13th Month', 7083.33, '13th month pay advance', '2025-12-15', 'approved', 14, '2025-12-10 00:00:00', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 3, 6, '13th Month', 3750.00, '13th month pay', '2025-12-15', 'approved', 14, '2025-12-10 00:00:00', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 11, 6, '13th Month', 10000.00, '13th month pay', '2025-12-15', 'approved', 14, '2025-12-10 00:00:00', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_anomalies`
--

CREATE TABLE `payroll_anomalies` (
  `anomaly_id` int(11) NOT NULL,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `anomaly_type` varchar(100) DEFAULT NULL,
  `anomaly_score` decimal(5,2) DEFAULT NULL,
  `anomaly_flag` tinyint(4) DEFAULT NULL,
  `severity` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reviewed` tinyint(4) DEFAULT 0,
  `resolved` tinyint(4) DEFAULT 0,
  `resolution_notes` text DEFAULT NULL,
  `detected_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(1, 6, 1, 13, 'approved', 'Checked and verified', '2026-02-20 00:00:00', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(2, 6, 2, 14, 'approved', 'Approved for release', '2026-02-21 00:00:00', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(3, 6, 3, 11, 'approved', 'Final approval', '2026-02-22 00:00:00', '2026-02-01 00:00:00', '2026-02-01 00:00:00');

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

--
-- Dumping data for table `payroll_components`
--

INSERT INTO `payroll_components` (`id`, `code`, `name`, `description`, `component_type`, `category`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'BASIC', 'Basic Salary', 'Base monthly salary', 'salary', 'regular', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'RICE', 'Rice Subsidy', 'Monthly rice allowance', 'benefit', 'allowance', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'MEDICAL', 'Medical Allowance', 'Monthly medical allowance', 'benefit', 'allowance', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'NIGHT_DIFF', 'Night Differential', 'Additional pay for night shifts', 'benefit', 'premium', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'OT_PAY', 'Overtime Pay', 'Overtime compensation', 'salary', 'premium', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'SSS', 'SSS Contribution', 'Social Security System', 'deduction', 'government', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'PHIC', 'PhilHealth', 'Philippine Health Insurance', 'deduction', 'government', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'HDMF', 'PAG-IBIG', 'Home Development Mutual Fund', 'deduction', 'government', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'WITHHOLDING_TAX', 'Withholding Tax', 'Monthly withholding tax', 'tax', 'government', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 'HMO', 'HMO Premium', 'Health insurance premium', 'deduction', 'benefit', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `payroll_configurations`
--

INSERT INTO `payroll_configurations` (`id`, `config_key`, `config_value`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'payroll_frequency', 'semi-monthly', 'Payroll processing frequency', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'pay_day_1', '15', 'First pay day of the month', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'pay_day_2', '30', 'Second pay day of the month', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'cutoff_1_start', '1', 'First cutoff start day', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'cutoff_1_end', '15', 'First cutoff end day', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'cutoff_2_start', '16', 'Second cutoff start day', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'cutoff_2_end', 'last day', 'Second cutoff end day', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'sss_employer_rate', '8.00', 'SSS employer contribution rate %', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'sss_employee_rate', '4.00', 'SSS employee contribution rate %', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 'phic_employer_rate', '2.50', 'PhilHealth employer rate %', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 'phic_employee_rate', '2.50', 'PhilHealth employee rate %', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 'hdmf_employer_rate', '2.00', 'PAG-IBIG employer rate %', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 'hdmf_employee_rate', '2.00', 'PAG-IBIG employee rate %', 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_forecasts`
--

CREATE TABLE `payroll_forecasts` (
  `forecast_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `forecast_period` varchar(50) DEFAULT NULL,
  `forecasted_net_pay` decimal(12,2) DEFAULT NULL,
  `forecasted_gross_pay` decimal(12,2) DEFAULT NULL,
  `confidence_level` decimal(5,2) DEFAULT NULL,
  `forecast_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_runs`
--

INSERT INTO `payroll_runs` (`id`, `period_name`, `start_date`, `end_date`, `pay_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'January 2026 Period 1', '2026-01-01', '2026-01-15', '2026-01-20', 'Closed', 14, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'January 2026 Period 2', '2026-01-16', '2026-01-31', '2026-02-05', 'Closed', 14, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'February 2026 Period 1', '2026-02-01', '2026-02-15', '2026-02-20', 'Closed', 14, '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(4, 'February 2026 Period 2', '2026-02-16', '2026-02-28', '2026-03-05', 'Draft', 14, '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(5, 'January 2026 Payroll', '2026-01-01', '2026-01-31', '2026-02-05', 'Closed', 14, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'February 2026 Payroll', '2026-02-01', '2026-02-28', '2026-03-05', 'Draft', 14, '2026-02-01 00:00:00', '2026-02-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_run_employees`
--

CREATE TABLE `payroll_run_employees` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `basic_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `incentives` decimal(12,2) DEFAULT 0.00,
  `gross_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `withholding_tax` decimal(12,2) DEFAULT 0.00,
  `sss_contribution` decimal(12,2) DEFAULT 0.00,
  `philhealth_contribution` decimal(12,2) DEFAULT 0.00,
  `pagibig_contribution` decimal(12,2) DEFAULT 0.00,
  `payslip_number` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll_run_employees`
--

INSERT INTO `payroll_run_employees` (`id`, `payroll_run_id`, `employee_id`, `basic_pay`, `incentives`, `gross_pay`, `total_deductions`, `net_pay`, `withholding_tax`, `sss_contribution`, `philhealth_contribution`, `pagibig_contribution`, `payslip_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 42500.00, 5000.00, 47500.00, 7125.00, 40375.00, 2500.00, 1800.00, 1250.00, 100.00, 'HVL-PS-2026-01-001', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 2, 32500.00, 3000.00, 35500.00, 5325.00, 30175.00, 1800.00, 1500.00, 900.00, 100.00, 'HVL-PS-2026-01-002', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 1, 3, 22500.00, 2500.00, 25000.00, 3750.00, 21250.00, 1000.00, 1200.00, 650.00, 100.00, 'HVL-PS-2026-01-003', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 1, 4, 15000.00, 1500.00, 16500.00, 2475.00, 14025.00, 500.00, 900.00, 450.00, 100.00, 'HVL-PS-2026-01-004', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 1, 5, 24000.00, 2000.00, 26000.00, 3900.00, 22100.00, 1100.00, 1200.00, 700.00, 100.00, 'HVL-PS-2026-01-005', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 1, 6, 16000.00, 1500.00, 17500.00, 2625.00, 14875.00, 600.00, 900.00, 500.00, 100.00, 'HVL-PS-2026-01-006', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 1, 7, 26000.00, 2000.00, 28000.00, 4200.00, 23800.00, 1200.00, 1200.00, 750.00, 100.00, 'HVL-PS-2026-01-007', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 1, 8, 17500.00, 1500.00, 19000.00, 2850.00, 16150.00, 700.00, 900.00, 550.00, 100.00, 'HVL-PS-2026-01-008', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 1, 9, 27500.00, 2000.00, 29500.00, 4425.00, 25075.00, 1300.00, 1200.00, 800.00, 100.00, 'HVL-PS-2026-01-009', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 1, 10, 19000.00, 1500.00, 20500.00, 3075.00, 17425.00, 800.00, 900.00, 600.00, 100.00, 'HVL-PS-2026-01-010', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 1, 11, 60000.00, 5000.00, 65000.00, 9750.00, 55250.00, 5000.00, 2400.00, 2000.00, 100.00, 'HVL-PS-2026-01-011', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 1, 12, 37500.00, 2000.00, 39500.00, 5925.00, 33575.00, 2200.00, 1500.00, 1100.00, 100.00, 'HVL-PS-2026-01-012', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 1, 13, 32500.00, 2000.00, 34500.00, 5175.00, 29325.00, 1800.00, 1500.00, 900.00, 100.00, 'HVL-PS-2026-01-013', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 1, 14, 40000.00, 2500.00, 42500.00, 6375.00, 36125.00, 2500.00, 1800.00, 1200.00, 100.00, 'HVL-PS-2026-01-014', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 1, 15, 32500.00, 2500.00, 35000.00, 5250.00, 29750.00, 1800.00, 1500.00, 900.00, 100.00, 'HVL-PS-2026-01-015', 'Processed', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, 3, 1, 42500.00, 4500.00, 47000.00, 7050.00, 39950.00, 2500.00, 1800.00, 1250.00, 100.00, 'HVL-PS-2026-02-001', 'Processed', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(17, 3, 2, 32500.00, 2500.00, 35000.00, 5250.00, 29750.00, 1800.00, 1500.00, 900.00, 100.00, 'HVL-PS-2026-02-002', 'Processed', '2026-02-01 00:00:00', '2026-02-01 00:00:00'),
(18, 3, 3, 22500.00, 2000.00, 24500.00, 3675.00, 20825.00, 1000.00, 1200.00, 650.00, 100.00, 'HVL-PS-2026-02-003', 'Processed', '2026-02-01 00:00:00', '2026-02-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payslip_email_logs`
--

CREATE TABLE `payslip_email_logs` (
  `id` int(11) NOT NULL,
  `payslip_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `payroll_run_id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed','bounced') DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslip_email_settings`
--

CREATE TABLE `payslip_email_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payslip_email_settings`
--

INSERT INTO `payslip_email_settings` (`id`, `setting_key`, `setting_value`, `updated_at`, `updated_by`) VALUES
(1, 'email_from', 'payroll@hvil.ph', '2026-02-23 02:06:22', 1),
(2, 'email_subject', 'Your HVill Hospital Payslip for [PERIOD]', '2026-02-23 02:06:22', 1),
(3, 'email_template', 'Dear [EMPLOYEE_NAME],\n\nYour payslip for [PAY_PERIOD] has been generated and is ready for download.\n\nGross Pay: ₱[GROSS_PAY]\nTotal Deductions: ₱[TOTAL_DEDUCTIONS]\nNet Pay: ₱[NET_PAY]\n\nYou can view and download your payslip at the HVill Employee Portal:\n[PAYSLIP_PORTAL_LINK]\n\nFor payroll inquiries, contact: payroll@hvil.ph or visit the HR Department.\n\n---\nHVill Hospital Inc.\nPayroll Department', '2026-02-23 02:06:22', 1),
(4, 'auto_email', '1', '2026-02-23 02:06:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payslip_records`
--

CREATE TABLE `payslip_records` (
  `id` int(11) NOT NULL,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `gross_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payslip_number` varchar(50) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` int(11) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `last_download_at` timestamp NULL DEFAULT NULL,
  `status` enum('generated','archived','deleted') DEFAULT 'generated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'ER-DOCTOR-2026', 'ER Doctor Contract 2026', 1, '2026-01-01', '2026-12-31', 'ER incentive policy, night differential, on-call pay', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'OPD-DOCTOR-2026', 'OPD Doctor Contract 2026', 2, '2026-01-01', '2026-12-31', 'Standard OPD compensation', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'NURSE-2026', 'Nurse Contract 2026', 3, '2026-01-01', '2026-12-31', 'Shift differential, night differential', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'LAB-2026', 'Laboratory Staff Contract 2026', 4, '2026-01-01', '2026-12-31', 'Lab incentive policy', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'RAD-2026', 'Radiology Staff Contract 2026', 5, '2026-01-01', '2026-12-31', 'Procedure-based incentives', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'ADMIN-2026', 'Administrative Staff Contract 2026', 6, '2026-01-01', '2026-12-31', 'Standard administrative benefits', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'ER-DOCTOR', 'Emergency Room Physician', 'Medical doctors assigned to ER', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'OPD-DOCTOR', 'Outpatient Physician', 'Medical doctors assigned to OPD', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'NURSING', 'Nursing Staff', 'Registered nurses and nursing assistants', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'LABORATORY', 'Laboratory Staff', 'Medical technologists and lab aides', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'RADIOLOGY', 'Radiology Staff', 'Radiologic technologists', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'ADMINISTRATIVE', 'Administrative Staff', 'HR, Finance, Admin personnel', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `promotion_recommendations`
--

CREATE TABLE `promotion_recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `promotion_probability` decimal(5,2) DEFAULT NULL,
  `readiness_score` decimal(5,2) DEFAULT NULL,
  `recommended_position` varchar(100) DEFAULT NULL,
  `recommended_department_id` int(11) DEFAULT NULL,
  `recommendation_date` date DEFAULT NULL,
  `action_status` varchar(20) DEFAULT 'pending',
  `feedback` text DEFAULT NULL,
  `decision_date` date DEFAULT NULL,
  `decision_made_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 'System Administrator', 'SYS_ADMIN', 'Full system access and configuration', 'System', 100, 50, 1, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Hospital Administrator', 'HOSP_ADMIN', 'Hospital-wide administrative access', 'Management', 90, 45, 1, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'HR Manager', 'HR_MANAGER', 'Human Resources management', 'HR', 80, 40, 1, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Finance Manager', 'FIN_MANAGER', 'Finance and payroll management', 'Finance', 80, 38, 1, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Department Head', 'DEPT_HEAD', 'Department management', 'Management', 70, 30, 5, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Payroll Officer', 'PAYROLL_OFF', 'Payroll processing', 'Finance', 60, 25, 1, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'HR Staff', 'HR_STAFF', 'HR support staff', 'HR', 50, 20, 2, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'Medical Director', 'MED_DIR', 'Medical affairs oversight', 'Medical', 85, 30, 1, 1, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'Senior Doctor', 'SR_DOCTOR', 'Senior medical staff', 'Medical', 60, 15, 2, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 'Doctor', 'DOCTOR', 'Medical doctor', 'Medical', 50, 10, 2, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 'Head Nurse', 'HEAD_NURSE', 'Nursing management', 'Nursing', 55, 15, 1, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 'Nurse', 'NURSE', 'Registered nurse', 'Nursing', 40, 8, 2, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 'Medical Technologist', 'MED_TECH', 'Laboratory staff', 'Technical', 40, 8, 2, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 'Radiologic Technologist', 'RAD_TECH', 'Radiology staff', 'Technical', 40, 8, 2, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 'Pharmacist', 'PHARMACIST', 'Pharmacy staff', 'Technical', 40, 8, 2, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, 'Employee', 'EMPLOYEE', 'Regular employee', 'Employee', 20, 5, 15, 0, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `salary_adjustments`
--

INSERT INTO `salary_adjustments` (`id`, `employee_id`, `adjustment_type`, `trigger_type`, `previous_amount`, `new_amount`, `effective_date`, `end_date`, `reason`, `status`, `approved_by`, `approved_at`, `salary_band_id`, `created_at`, `updated_at`) VALUES
(1, 3, 'Permanent', 'Promotion', 28000.00, 35000.00, '2024-01-01', NULL, 'Promotion to Head Nurse', 'approved', 11, '2023-12-15 00:00:00', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 6, 'Permanent', 'Promotion', 25000.00, 32000.00, '2025-01-01', NULL, 'Promotion to Senior Lab Tech', 'approved', 5, '2024-12-15 00:00:00', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 10, 'Permanent', 'Promotion', 30000.00, 38000.00, '2025-06-01', NULL, 'Promotion to Senior Pharmacist', 'approved', 9, '2025-05-15 00:00:00', NULL, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_bands`
--

INSERT INTO `salary_bands` (`id`, `pay_grade_id`, `grade_level_id`, `min_salary`, `midpoint_salary`, `max_salary`, `effective_from`, `effective_to`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 75000.00, 85000.00, 95000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 1, 2, 65000.00, 70000.00, 80000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 1, 3, 55000.00, 60000.00, 65000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 2, 2, 55000.00, 65000.00, 75000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 2, 3, 45000.00, 50000.00, 55000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 3, 4, 40000.00, 45000.00, 50000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 3, 5, 35000.00, 38000.00, 42000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 3, 6, 28000.00, 32000.00, 35000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 4, 7, 45000.00, 50000.00, 55000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 4, 8, 38000.00, 42000.00, 46000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 4, 9, 30000.00, 34000.00, 38000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 5, 10, 48000.00, 52000.00, 56000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(13, 5, 11, 38000.00, 42000.00, 46000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(14, 5, 12, 32000.00, 36000.00, 40000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(15, 6, 16, 90000.00, 110000.00, 130000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(16, 6, 17, 60000.00, 75000.00, 90000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(17, 6, 18, 35000.00, 50000.00, 60000.00, '2026-01-01', '2026-12-31', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'BASIC_SALARY', 'Basic Salary', 'Base monthly salary', 'base', 1, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'RICE_ALLOW', 'Rice Allowance', 'Monthly rice allowance', 'allowance', 0, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'MED_ALLOW', 'Medical Allowance', 'Monthly medical allowance', 'allowance', 0, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'TRANS_ALLOW', 'Transportation Allowance', 'Monthly transportation allowance', 'allowance', 0, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'NIGHT_DIFF', 'Night Differential', 'Night shift differential pay', 'allowance', 1, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'OT_PAY', 'Overtime Pay', 'Overtime compensation', 'allowance', 1, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'SSS_DED', 'SSS Deduction', 'SSS contribution', 'deduction', 0, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'PHIC_DED', 'PhilHealth Deduction', 'PhilHealth contribution', 'deduction', 0, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'HDMF_DED', 'PAG-IBIG Deduction', 'PAG-IBIG contribution', 'deduction', 0, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 'HMO_DED', 'HMO Deduction', 'Health insurance premium', 'deduction', 0, 1, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'SG1', 'Entry Level', 20000.00, 30000.00, 2000.00, 'Entry level positions', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'SG2', 'Junior Level', 30000.00, 40000.00, 2500.00, 'Junior level positions', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'SG3', 'Intermediate', 40000.00, 50000.00, 3000.00, 'Intermediate level', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'SG4', 'Senior Level', 50000.00, 65000.00, 3500.00, 'Senior level', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'SG5', 'Supervisory', 65000.00, 80000.00, 4000.00, 'Supervisory positions', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'SG6', 'Managerial', 80000.00, 100000.00, 5000.00, 'Managerial positions', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'SG7', 'Senior Manager', 100000.00, 125000.00, 6000.00, 'Senior managerial', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 'SG8', 'Director', 125000.00, 150000.00, 8000.00, 'Director level', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 'SG9', 'Executive', 150000.00, 200000.00, 10000.00, 'Executive level', 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `salary_templates`
--

INSERT INTO `salary_templates` (`template_id`, `position`, `role`, `employment_type`, `salary_type`, `shift_duration_hours`, `basic_rate`, `rate_per_hour_reference`, `payroll_frequency`, `payout_day_1`, `payout_day_2`, `cutoff_start_day`, `cutoff_end_day`, `double_pay_addon`, `incentives_in_payroll`, `overtime_outside_base`, `night_differential_applied`, `incentive_rules`, `benefits`, `status`, `effective_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'ER Doctor', 'ER Physician', 'Monthly', 'Monthly', 8.00, 85000.00, 473.21, 'Semi-Monthly', 15, 30, 1, 15, 3000.00, 1, 1, 1, '[\"ER admissions\",\"ER consultations\",\"Procedures\"]', 'Free meals, HMO, Rice subsidy', 'Active', '2026-01-01', '2026-12-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Nurse', 'Staff Nurse', 'Monthly', 'Monthly', 8.00, 30000.00, 178.57, 'Semi-Monthly', 15, 30, 1, 15, 1500.00, 1, 1, 1, '[\"Procedures\",\"Patient care\"]', 'HMO, Uniform allowance', 'Active', '2026-01-01', '2026-12-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Med Tech', 'Laboratory Staff', 'Monthly', 'Monthly', 8.00, 32000.00, 190.48, 'Semi-Monthly', 15, 30, 1, 15, 1000.00, 1, 1, 0, '[\"Lab procedures\"]', 'HMO, Rice subsidy', 'Active', '2026-01-01', '2026-12-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Radiologic Tech', 'Radiology Staff', 'Monthly', 'Monthly', 8.00, 35000.00, 208.33, 'Semi-Monthly', 15, 30, 1, 15, 1000.00, 1, 1, 0, '[\"Imaging procedures\"]', 'HMO, Medical allowance', 'Active', '2026-01-01', '2026-12-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Pharmacist', 'Pharmacy Staff', 'Monthly', 'Monthly', 8.00, 38000.00, 226.19, 'Semi-Monthly', 15, 30, 1, 15, 1000.00, 0, 1, 0, '[]', 'HMO, Rice subsidy', 'Active', '2026-01-01', '2026-12-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Admin Staff', 'Administrative', 'Monthly', 'Monthly', 8.00, 40000.00, 238.10, 'Semi-Monthly', 15, 30, 1, 15, 0.00, 0, 1, 0, '[]', 'HMO, Rice subsidy', 'Active', '2026-01-01', '2026-12-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'Morning Shift', 'MORNING', '07:00:00', '15:00:00', 8.00, 1.00, 'Morning shift 7AM-3PM', 0, 'Regular', 1, 10, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Afternoon Shift', 'AFTERNOON', '15:00:00', '23:00:00', 8.00, 1.00, 'Afternoon shift 3PM-11PM', 0, 'Regular', 1, 8, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Night Shift', 'NIGHT', '23:00:00', '07:00:00', 8.00, 1.00, 'Night shift 11PM-7AM', 1, 'Regular', 1, 6, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Weekend Morning', 'WEEKEND_AM', '08:00:00', '16:00:00', 8.00, 1.00, 'Weekend morning shift', 0, 'Weekend', 1, 5, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Weekend Afternoon', 'WEEKEND_PM', '16:00:00', '00:00:00', 8.00, 1.00, 'Weekend afternoon shift', 0, 'Weekend', 1, 5, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Holiday Shift', 'HOLIDAY', '08:00:00', '20:00:00', 12.00, 1.00, 'Holiday shift', 0, 'Special', 1, 4, 'Active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `special_pay_dates`
--

INSERT INTO `special_pay_dates` (`id`, `date`, `label`, `created_at`) VALUES
(1, '2026-01-01', 'New Year\'s Day', '2026-01-01 00:00:00'),
(2, '2026-04-09', 'Araw ng Kagitingan', '2026-01-01 00:00:00'),
(3, '2026-05-01', 'Labor Day', '2026-01-01 00:00:00'),
(4, '2026-06-12', 'Independence Day', '2026-01-01 00:00:00'),
(5, '2026-08-30', 'National Heroes Day', '2026-01-01 00:00:00'),
(6, '2026-11-30', 'Bonifacio Day', '2026-01-01 00:00:00'),
(7, '2026-12-25', 'Christmas Day', '2026-01-01 00:00:00'),
(8, '2026-12-30', 'Rizal Day', '2026-01-01 00:00:00');

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

--
-- Dumping data for table `tax_contributions`
--

INSERT INTO `tax_contributions` (`id`, `payroll_run_id`, `employee_id`, `contribution_type`, `amount`, `effective_date`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'SSS', 1800.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 5, 1, 'PhilHealth', 1250.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 5, 1, 'Pag-IBIG', 100.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 5, 1, 'Income Tax', 5250.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 5, 3, 'SSS', 1200.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 5, 3, 'PhilHealth', 650.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 5, 3, 'Pag-IBIG', 100.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(8, 5, 3, 'Income Tax', 2200.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(9, 5, 11, 'SSS', 2400.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(10, 5, 11, 'PhilHealth', 2000.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(11, 5, 11, 'Pag-IBIG', 100.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(12, 5, 11, 'Income Tax', 10000.00, '2026-01-31', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'juan.cruz', 'juan.cruz@hvil.ph', '$2y$10$K1pQX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Juan', 'Cruz', 'Dr. Juan Cruz', 'doctor', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'maria.reyes', 'maria.reyes@hvil.ph', '$2y$10$L2pQX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Maria', 'Reyes', 'Dr. Maria Reyes', 'doctor', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'jose.santos', 'jose.santos@hvil.ph', '$2y$10$M3pQX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Jose', 'Santos', 'Jose Santos', 'nurse', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'ricardo.gonzales', 'ricardo.gonzales@hvil.ph', '$2y$10$N4pQX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Ricardo', 'Gonzales', 'Ricardo Gonzales', 'admin', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'mt.domingo', 'mt.domingo@hvil.ph', '$2y$10$O5pQX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Ma. Teresa', 'Domingo', 'Ma. Teresa Domingo', 'hr_manager', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'roberto.tan', 'roberto.tan@hvil.ph', '$2y$10$P6pQX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Roberto', 'Tan', 'Roberto Tan', 'finance_manager', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(7, 'admin', 'admin@hvil.ph', '$2y$10$Q7pQX1E56lpDb304jZwD.e9usMgVBUaL1dcUEkjcVZhKe7vY9OcKy', 'Admin', 'User', 'System Administrator', 'sys_admin', 'active', '2026-01-01 00:00:00', '2026-01-01 00:00:00');

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
(1, 'Standard 5-Day', 'Fixed', 'M-F 8hrs', 8.00, 5, 40.00, 0, 0, 'Standard 5-day work week', 'Active', NULL, NULL, 20, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(2, 'Medical Staff 6-Day', 'Fixed', 'M-Sat 8hrs', 8.00, 6, 48.00, 0, 0, 'Medical staff 6-day schedule', 'Active', NULL, NULL, 30, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(3, 'Rotating 24/7', 'Rotating', '3-shift rotation', 8.00, 7, 56.00, 1, 0, '24/7 rotating shifts', 'Active', NULL, NULL, 15, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(4, 'Night Shift Only', 'Fixed', 'Sun-Thu night', 8.00, 5, 40.00, 1, 0, 'Permanent night shift', 'Active', NULL, NULL, 8, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(5, 'Administrative', 'Fixed', 'M-F 8hrs', 8.00, 5, 40.00, 0, 0, 'Admin staff schedule', 'Active', NULL, NULL, 10, '2026-01-01 00:00:00', '2026-01-01 00:00:00'),
(6, 'Part-time', 'Part-time', 'Variable', 4.00, 5, 20.00, 0, 1, 'Part-time schedule', 'Active', NULL, NULL, 5, '2026-01-01 00:00:00', '2026-01-01 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_predictions`
--
ALTER TABLE `ai_predictions`
  ADD PRIMARY KEY (`prediction_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_type` (`prediction_type`),
  ADD KEY `idx_date` (`prediction_date`);

--
-- Indexes for table `ai_prediction_history`
--
ALTER TABLE `ai_prediction_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_type` (`prediction_type`),
  ADD KEY `idx_prediction_date` (`prediction_date`);

--
-- Indexes for table `attrition_alerts`
--
ALTER TABLE `attrition_alerts`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_status` (`alert_status`),
  ADD KEY `idx_risk_level` (`risk_level`),
  ADD KEY `assigned_to` (`assigned_to`);

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
-- Indexes for table `bir_tax_brackets`
--
ALTER TABLE `bir_tax_brackets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `compensation_approval_requests`
--
ALTER TABLE `compensation_approval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_type` (`request_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `compensation_version_history`
--
ALTER TABLE `compensation_version_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_entity_version` (`entity_type`,`entity_id`,`version_number`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_effective_date` (`effective_date`);

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
  ADD KEY `to_location_id` (`to_location_id`),
  ADD KEY `idx_approved_date` (`approved_date`),
  ADD KEY `idx_archived` (`archived`);

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
-- Indexes for table `payroll_anomalies`
--
ALTER TABLE `payroll_anomalies`
  ADD PRIMARY KEY (`anomaly_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_payroll_run` (`payroll_run_id`),
  ADD KEY `idx_severity` (`severity`);

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
-- Indexes for table `payroll_forecasts`
--
ALTER TABLE `payroll_forecasts`
  ADD PRIMARY KEY (`forecast_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_period` (`forecast_period`);

--
-- Indexes for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `fk_payroll_runs_created_by` (`created_by`);

--
-- Indexes for table `payroll_run_employees`
--
ALTER TABLE `payroll_run_employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_run_employee` (`payroll_run_id`,`employee_id`),
  ADD KEY `idx_payroll_run_id` (`payroll_run_id`),
  ADD KEY `idx_employee_id` (`employee_id`);

--
-- Indexes for table `payslip_email_logs`
--
ALTER TABLE `payslip_email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payslip` (`payslip_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_payroll_run` (`payroll_run_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `payslip_email_settings`
--
ALTER TABLE `payslip_email_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD UNIQUE KEY `unique_setting` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `payslip_records`
--
ALTER TABLE `payslip_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payslip_number` (`payslip_number`),
  ADD UNIQUE KEY `unique_payslip` (`payroll_run_id`,`employee_id`),
  ADD KEY `generated_by` (`generated_by`),
  ADD KEY `idx_payroll_run` (`payroll_run_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_generated_at` (`generated_at`);

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
-- Indexes for table `promotion_recommendations`
--
ALTER TABLE `promotion_recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_status` (`action_status`),
  ADD KEY `recommended_department_id` (`recommended_department_id`),
  ADD KEY `decision_made_by` (`decision_made_by`);

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
-- AUTO_INCREMENT for table `ai_predictions`
--
ALTER TABLE `ai_predictions`
  MODIFY `prediction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_prediction_history`
--
ALTER TABLE `ai_prediction_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attrition_alerts`
--
ALTER TABLE `attrition_alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefit_definitions`
--
ALTER TABLE `benefit_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bir_tax_brackets`
--
ALTER TABLE `bir_tax_brackets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employee_assignments`
--
ALTER TABLE `employee_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employee_contract_assignments`
--
ALTER TABLE `employee_contract_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employee_hmo_enrollments`
--
ALTER TABLE `employee_hmo_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `employee_movements`
--
ALTER TABLE `employee_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employee_payroll_profiles`
--
ALTER TABLE `employee_payroll_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `employee_salaries`
--
ALTER TABLE `employee_salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  MODIFY `employee_shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employment_types`
--
ALTER TABLE `employment_types`
  MODIFY `employment_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `government_reports`
--
ALTER TABLE `government_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `grade_levels`
--
ALTER TABLE `grade_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `hmo_billing_adjustments`
--
ALTER TABLE `hmo_billing_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hmo_documents`
--
ALTER TABLE `hmo_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `hmo_enrollment_dependents`
--
ALTER TABLE `hmo_enrollment_dependents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `hmo_life_events`
--
ALTER TABLE `hmo_life_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hmo_plans`
--
ALTER TABLE `hmo_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hmo_premium_payments`
--
ALTER TABLE `hmo_premium_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `hmo_providers`
--
ALTER TABLE `hmo_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `incentive_events`
--
ALTER TABLE `incentive_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `incentive_types`
--
ALTER TABLE `incentive_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `job_titles`
--
ALTER TABLE `job_titles`
  MODIFY `job_title_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_otp`
--
ALTER TABLE `login_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `onboarding_checklists`
--
ALTER TABLE `onboarding_checklists`
  MODIFY `onboarding_checklist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `otp_attempt_logs`
--
ALTER TABLE `otp_attempt_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `out_of_band_approvals`
--
ALTER TABLE `out_of_band_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payroll_anomalies`
--
ALTER TABLE `payroll_anomalies`
  MODIFY `anomaly_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_approvals`
--
ALTER TABLE `payroll_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payroll_audit_trail`
--
ALTER TABLE `payroll_audit_trail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_components`
--
ALTER TABLE `payroll_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payroll_configurations`
--
ALTER TABLE `payroll_configurations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `payroll_forecasts`
--
ALTER TABLE `payroll_forecasts`
  MODIFY `forecast_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payroll_run_employees`
--
ALTER TABLE `payroll_run_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payslip_email_logs`
--
ALTER TABLE `payslip_email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslip_email_settings`
--
ALTER TABLE `payslip_email_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payslip_records`
--
ALTER TABLE `payslip_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pay_contracts`
--
ALTER TABLE `pay_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pay_grades`
--
ALTER TABLE `pay_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promotion_recommendations`
--
ALTER TABLE `promotion_recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `salary_adjustments`
--
ALTER TABLE `salary_adjustments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `salary_bands`
--
ALTER TABLE `salary_bands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `salary_component_definitions`
--
ALTER TABLE `salary_component_definitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `salary_grades`
--
ALTER TABLE `salary_grades`
  MODIFY `salary_grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `salary_templates`
--
ALTER TABLE `salary_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `special_pay_dates`
--
ALTER TABLE `special_pay_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tax_contributions`
--
ALTER TABLE `tax_contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_predictions`
--
ALTER TABLE `ai_predictions`
  ADD CONSTRAINT `ai_predictions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_prediction_history`
--
ALTER TABLE `ai_prediction_history`
  ADD CONSTRAINT `ai_prediction_history_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `attrition_alerts`
--
ALTER TABLE `attrition_alerts`
  ADD CONSTRAINT `attrition_alerts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attrition_alerts_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
-- Constraints for table `payroll_anomalies`
--
ALTER TABLE `payroll_anomalies`
  ADD CONSTRAINT `payroll_anomalies_ibfk_1` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_anomalies_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_forecasts`
--
ALTER TABLE `payroll_forecasts`
  ADD CONSTRAINT `payroll_forecasts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_forecasts_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_run_employees`
--
ALTER TABLE `payroll_run_employees`
  ADD CONSTRAINT `payroll_run_employees_ibfk_1` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_run_employees_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `payslip_email_logs`
--
ALTER TABLE `payslip_email_logs`
  ADD CONSTRAINT `payslip_email_logs_ibfk_1` FOREIGN KEY (`payslip_id`) REFERENCES `payslip_records` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payslip_email_logs_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslip_email_logs_ibfk_3` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslip_email_settings`
--
ALTER TABLE `payslip_email_settings`
  ADD CONSTRAINT `payslip_email_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payslip_records`
--
ALTER TABLE `payslip_records`
  ADD CONSTRAINT `payslip_records_ibfk_1` FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslip_records_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslip_records_ibfk_3` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotion_recommendations`
--
ALTER TABLE `promotion_recommendations`
  ADD CONSTRAINT `promotion_recommendations_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_recommendations_ibfk_2` FOREIGN KEY (`recommended_department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotion_recommendations_ibfk_3` FOREIGN KEY (`decision_made_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
