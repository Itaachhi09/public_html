-- ====================================================================
-- Authentication Module - Users Table
-- Database: public_html
-- ====================================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255),
    `phone` VARCHAR(20),
    `department_id` INT,
    `role` VARCHAR(50) DEFAULT 'employee',
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_email` (`email`),
    KEY `idx_username` (`username`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Insert Sample Users for Testing
-- ====================================================================

-- User 1: Admin
-- Email: johnpaulaustria321@gmail.com
-- Password: admin@123
INSERT INTO `users` (`username`, `email`, `password`, `name`, `role`, `status`)
VALUES (
    'admin',
    'johnpaulaustria321@gmail.com',
    '$2y$10$YourHashedPassword1Here',
    'John Paul Austria',
    'admin',
    'active'
) ON DUPLICATE KEY UPDATE `username` = `username`;

-- User 2: HR Chief
-- Email: itachiuchiha10012@gmail.com
-- Password: hrchief@123
INSERT INTO `users` (`username`, `email`, `password`, `name`, `role`, `status`)
VALUES (
    'hrchief',
    'itachiuchiha10012@gmail.com',
    '$2y$10$YourHashedPassword2Here',
    'HR Chief',
    'hr_chief',
    'active'
) ON DUPLICATE KEY UPDATE `username` = `username`;

-- ====================================================================
-- Update Passwords (Run these separately via PHP)
-- ====================================================================
-- UPDATE users SET password = '$2y$10$8TYaIeNtVoUo/Q0h.CglbeXp0RVMtZG5ZY1d2MWNqeVN6SjRxVDEu' WHERE email = 'johnpaulaustria321@gmail.com';
-- UPDATE users SET password = '$2y$10$c2FsYWZUSXNVV0F3bmlTTu95TjI0Lm5sd2QuZm9yZXcuMjAyNjA5MDI=' WHERE email = 'itachiuchiha10012@gmail.com';
