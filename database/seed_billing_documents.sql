-- =====================================================================
-- HMO Billing and Documents Tables
-- Add missing tables for Billing Reconciliation and HMO Documents
-- =====================================================================

-- Drop if exists
DROP TABLE IF EXISTS `hmo_billing_adjustments`;
DROP TABLE IF EXISTS `hmo_billing_discrepancies`;
DROP TABLE IF EXISTS `hmo_billing_reconciliation`;
DROP TABLE IF EXISTS `hmo_documents`;

-- =====================================================================
-- Billing Reconciliation Tables
-- =====================================================================

-- Main reconciliation table
CREATE TABLE `hmo_billing_reconciliation` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reconciliation_number` VARCHAR(50) NOT NULL UNIQUE,
    `billing_month` DATE NOT NULL,
    `provider_id` INT NOT NULL,
    `total_claims_submitted` DECIMAL(15, 2),
    `total_approved_amount` DECIMAL(15, 2),
    `total_payments_made` DECIMAL(15, 2),
    `variance_amount` DECIMAL(15, 2),
    `variance_percentage` DECIMAL(5, 2),
    `reconciliation_status` ENUM('pending', 'in_review', 'discrepancies_found', 'resolved', 'approved') DEFAULT 'pending',
    `reviewed_by` VARCHAR(100),
    `review_date` DATETIME,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_provider_id` (`provider_id`),
    KEY `idx_billing_month` (`billing_month`),
    KEY `idx_reconciliation_status` (`reconciliation_status`),
    FOREIGN KEY (`provider_id`) REFERENCES `hmo_providers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Billing discrepancies
CREATE TABLE `hmo_billing_discrepancies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reconciliation_id` INT NOT NULL,
    `employee_id` INT,
    `claim_id` INT,
    `discrepancy_type` ENUM('amount_mismatch', 'duplicate_claim', 'unapproved_service', 'coverage_issue', 'missing_documentation') NOT NULL,
    `submitted_amount` DECIMAL(12, 2),
    `approved_amount` DECIMAL(12, 2),
    `variance_amount` DECIMAL(12, 2),
    `discrepancy_details` TEXT,
    `resolution_status` ENUM('pending', 'investigating', 'resolved', 'rejected') DEFAULT 'pending',
    `resolved_by` VARCHAR(100),
    `resolution_date` DATETIME,
    `resolution_notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_reconciliation_id` (`reconciliation_id`),
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_claim_id` (`claim_id`),
    KEY `idx_discrepancy_type` (`discrepancy_type`),
    FOREIGN KEY (`reconciliation_id`) REFERENCES `hmo_billing_reconciliation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Billing adjustments
CREATE TABLE `hmo_billing_adjustments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reconciliation_id` INT NOT NULL,
    `adjustment_type` ENUM('credit', 'debit', 'reversal', 'refund') NOT NULL,
    `amount` DECIMAL(12, 2),
    `reason` VARCHAR(255),
    `reference_discrepancy_id` INT,
    `adjustment_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `approved_by` VARCHAR(100),
    `approved_date` DATETIME,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_reconciliation_id` (`reconciliation_id`),
    KEY `idx_adjustment_type` (`adjustment_type`),
    KEY `idx_adjustment_status` (`adjustment_status`),
    FOREIGN KEY (`reconciliation_id`) REFERENCES `hmo_billing_reconciliation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- HMO Documents Table
-- =====================================================================

CREATE TABLE `hmo_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_number` VARCHAR(50) NOT NULL UNIQUE,
    `document_type` ENUM('Insurance_Card', 'Member_Handbook', 'Benefits_Summary', 'Hospital_Referral', 'Pre_authorization', 'Claim_Form', 'Policy_Document', 'Other') NOT NULL,
    `subject` VARCHAR(255),
    `employee_id` INT,
    `plan_id` INT,
    `provider_id` INT,
    `issue_date` DATE NOT NULL,
    `validity_date` DATE,
    `expiry_date` DATE,
    `document_status` ENUM('active', 'inactive', 'expired', 'archived') DEFAULT 'active',
    `file_path` VARCHAR(500),
    `file_name` VARCHAR(255),
    `file_size` INT,
    `file_type` VARCHAR(50),
    `description` TEXT,
    `uploaded_by` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_plan_id` (`plan_id`),
    KEY `idx_provider_id` (`provider_id`),
    KEY `idx_document_type` (`document_type`),
    KEY `idx_document_status` (`document_status`),
    KEY `idx_issue_date` (`issue_date`),
    FOREIGN KEY (`plan_id`) REFERENCES `hmo_plans` (`id`),
    FOREIGN KEY (`provider_id`) REFERENCES `hmo_providers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Insert Sample Billing Reconciliation Data
-- =====================================================================

INSERT INTO `hmo_billing_reconciliation` 
(`reconciliation_number`, `billing_month`, `provider_id`, `total_claims_submitted`, `total_approved_amount`, `total_payments_made`, `variance_amount`, `variance_percentage`, `reconciliation_status`, `reviewed_by`, `review_date`) 
VALUES
('REC-PHH-202601', '2026-01-01', 1, 45000.00, 44500.00, 44500.00, -500.00, -1.11, 'approved', 'Admin', NOW()),
('REC-ATE-202601', '2026-01-01', 2, 125000.00, 123500.00, 123500.00, -1500.00, -1.20, 'approved', 'Admin', NOW()),
('REC-INS-202601', '2026-01-01', 3, 95000.00, 94000.00, 94000.00, -1000.00, -1.05, 'approved', 'Admin', NOW()),
('REC-MED-202601', '2026-01-01', 4, 85000.00, 84500.00, 84500.00, -500.00, -0.59, 'approved', 'Admin', NOW()),
('REC-STL-202601', '2026-01-01', 5, 110000.00, 108500.00, 108500.00, -1500.00, -1.36, 'approved', 'Admin', NOW());

-- =====================================================================
-- Insert Sample Billing Discrepancies
-- =====================================================================

INSERT INTO `hmo_billing_discrepancies` 
(`reconciliation_id`, `employee_id`, `discrepancy_type`, `submitted_amount`, `approved_amount`, `variance_amount`, `discrepancy_details`, `resolution_status`) 
VALUES
(1, 1, 'amount_mismatch', 2500.00, 2400.00, -100.00, 'Authorized deductible applied', 'resolved'),
(2, 2, 'coverage_issue', 5000.00, 4500.00, -500.00, 'Pre-authorization required for service', 'resolved'),
(3, 3, 'duplicate_claim', 3500.00, 3500.00, 0.00, 'Duplicate claim detected and removed', 'resolved');

-- =====================================================================
-- Insert Sample HMO Documents
-- =====================================================================

INSERT INTO `hmo_documents` 
(`document_number`, `document_type`, `subject`, `employee_id`, `plan_id`, `provider_id`, `issue_date`, `validity_date`, `expiry_date`, `document_status`, `description`) 
VALUES
('DOC-INS-001', 'Insurance_Card', 'Aetna Insurance Card', 1, 3, 2, '2026-01-01', '2026-01-01', '2027-01-31', 'active', 'Physical insurance card issued'),
('DOC-HB-001', 'Member_Handbook', 'Aetna Member Handbook 2026', 2, 4, 2, '2026-01-01', '2026-01-01', '2027-12-31', 'active', 'Comprehensive member handbook'),
('DOC-BS-001', 'Benefits_Summary', 'Insular Health Benefits Summary', 3, 5, 3, '2026-01-01', '2026-01-01', '2026-12-31', 'active', 'Benefits coverage summary'),
('DOC-PA-001', 'Pre_authorization', 'Surgery Pre-authorization', 4, 7, 4, '2026-01-15', '2026-01-15', '2026-03-15', 'active', 'Pre-auth for elective surgery'),
('DOC-CF-001', 'Claim_Form', 'Claim Submission Form', 5, 2, 1, '2026-01-10', '2026-01-10', '2026-12-31', 'active', 'Standard claim submission form'),
('DOC-PD-001', 'Policy_Document', 'Philhealth Policy Document', 6, 1, 1, '2026-01-01', '2026-01-01', '2027-12-31', 'active', 'Full policy terms and conditions'),
('DOC-HR-001', 'Hospital_Referral', 'Hospital Referral Letter', 7, 6, 3, '2026-01-20', '2026-01-20', '2026-02-20', 'active', 'Referral to partner hospital'),
('DOC-INS-002', 'Insurance_Card', 'Medicard Insurance Card', 8, 8, 4, '2026-01-01', '2026-01-01', '2027-01-31', 'active', 'Physical insurance card issued');
