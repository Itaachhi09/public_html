-- Payslip Management Tables Migration
-- This migration creates tables for payslip generation and email distribution

-- Create payslip_records table
CREATE TABLE IF NOT EXISTS `payslip_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_run_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `gross_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payslip_number` varchar(50) NOT NULL UNIQUE,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` int(11),
  `pdf_path` varchar(255),
  `download_count` int(11) DEFAULT 0,
  `last_download_at` timestamp NULL,
  `status` enum('generated','archived','deleted') DEFAULT 'generated',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_payslip` (`payroll_run_id`, `employee_id`),
  FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_payroll_run` (`payroll_run_id`),
  INDEX `idx_employee` (`employee_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_generated_at` (`generated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payslip_email_settings table
CREATE TABLE IF NOT EXISTS `payslip_email_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL UNIQUE,
  `setting_value` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`setting_key`),
  FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payslip_email_logs table
CREATE TABLE IF NOT EXISTS `payslip_email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payslip_id` int(11),
  `employee_id` int(11) NOT NULL,
  `payroll_run_id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed','bounced') DEFAULT 'sent',
  `error_message` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`payslip_id`) REFERENCES `payslip_records`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE,
  FOREIGN KEY (`payroll_run_id`) REFERENCES `payroll_runs`(`id`) ON DELETE CASCADE,
  INDEX `idx_payslip` (`payslip_id`),
  INDEX `idx_employee` (`employee_id`),
  INDEX `idx_payroll_run` (`payroll_run_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email settings
INSERT IGNORE INTO `payslip_email_settings` (`setting_key`, `setting_value`, `updated_at`, `updated_by`) VALUES
  ('email_from', 'payroll@healthcare.com', NOW(), 1),
  ('email_subject', 'Your Payslip for [PERIOD]', NOW(), 1),
  ('email_template', 'Dear [EMPLOYEE_NAME],\n\nYour payslip for [PAY_PERIOD] has been generated and is ready for download.\n\nGross Pay: ₱[GROSS_PAY]\nTotal Deductions: ₱[TOTAL_DEDUCTIONS]\nNet Pay: ₱[NET_PAY]\n\nYou can view and download your payslip using the following link:\n[PAYSLIP_PORTAL_LINK]\n\nFor payroll inquiries, contact: payroll@healthcare.com\n\n---\nHealthcare Hospital Inc.\nPayroll Department', NOW(), 1),
  ('auto_email', '1', NOW(), 1);
