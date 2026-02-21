-- Migration: Add missing columns to employee_payroll_profiles table
-- Date: February 15, 2026
-- Description: Adds pay_type, payroll_status, tax_status, sss_status, philhealth_status, 
--              pagibig_status, bank_name, account_type, and account_status columns

ALTER TABLE `employee_payroll_profiles` 
ADD COLUMN `pay_type` enum('Per Duty','Per Shift','Hourly','Daily','Monthly') DEFAULT 'Monthly' AFTER `pay_schedule_day_2`,
ADD COLUMN `payroll_status` varchar(50) DEFAULT 'Active' AFTER `pay_type`,
ADD COLUMN `tax_status` varchar(50) DEFAULT NULL AFTER `payroll_status`,
ADD COLUMN `sss_status` varchar(50) DEFAULT NULL AFTER `tax_status`,
ADD COLUMN `philhealth_status` varchar(50) DEFAULT NULL AFTER `sss_status`,
ADD COLUMN `pagibig_status` varchar(50) DEFAULT NULL AFTER `philhealth_status`,
ADD COLUMN `bank_name` varchar(100) DEFAULT NULL AFTER `bank_code`,
ADD COLUMN `account_type` varchar(50) DEFAULT NULL AFTER `bank_name`,
ADD COLUMN `account_status` varchar(50) DEFAULT 'Active' AFTER `account_type`;
