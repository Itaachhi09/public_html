-- Migration: Add approved_date column to employee_movements table
-- Created: 2026-02-15
-- Description: Add column to track when employee movements (promotions, transfers, etc.) were approved/rejected

ALTER TABLE `employee_movements` 
ADD COLUMN `approved_date` TIMESTAMP NULL DEFAULT NULL AFTER `approved_by`;

-- Create index for approved_date for faster queries
ALTER TABLE `employee_movements` 
ADD INDEX `idx_approved_date` (`approved_date`);
