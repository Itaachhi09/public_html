-- ====================================================================
-- Hospital Document Requirements Table
-- Database: public_html
-- Purpose: Manage required documents for hospital employees
-- ====================================================================

-- Create Document Categories
CREATE TABLE IF NOT EXISTS `document_categories` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL UNIQUE,
    `category_description` TEXT,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Document Requirements/Templates
CREATE TABLE IF NOT EXISTS `document_requirements` (
    `requirement_id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `document_type` VARCHAR(100) NOT NULL,
    `document_description` TEXT,
    `is_mandatory` BOOLEAN DEFAULT TRUE,
    `requires_expiry_date` BOOLEAN DEFAULT FALSE,
    `expiry_alert_days` INT DEFAULT 30,
    `role_based` VARCHAR(100),
    `employment_status` VARCHAR(50),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_category_id` (`category_id`),
    KEY `idx_document_type` (`document_type`),
    FOREIGN KEY (`category_id`) REFERENCES `document_categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create Employee Document Checklist (tracks which documents are submitted)
CREATE TABLE IF NOT EXISTS `employee_document_checklist` (
    `checklist_id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `requirement_id` INT NOT NULL,
    `document_id` INT,
    `submitted_date` DATE,
    `is_submitted` BOOLEAN DEFAULT FALSE,
    `is_verified` BOOLEAN DEFAULT FALSE,
    `verified_by` INT,
    `verified_date` DATE,
    `remarks` TEXT,
    `status` ENUM('Not Submitted', 'Submitted', 'Verified', 'Expired', 'Pending Renewal') DEFAULT 'Not Submitted',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_employee_requirement` (`employee_id`, `requirement_id`),
    KEY `idx_employee_id` (`employee_id`),
    KEY `idx_requirement_id` (`requirement_id`),
    KEY `idx_status` (`status`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
    FOREIGN KEY (`requirement_id`) REFERENCES `document_requirements` (`requirement_id`) ON DELETE CASCADE,
    FOREIGN KEY (`document_id`) REFERENCES `employee_documents` (`document_id`) ON DELETE SET NULL,
    FOREIGN KEY (`verified_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- INSERT DOCUMENT CATEGORIES
-- ====================================================================
INSERT INTO `document_categories` (`category_name`, `category_description`, `sort_order`) VALUES
('Core Employment Documents', 'Essential employment related documents', 1),
('Identity and Legal Documents', 'Government issued identification and legal documents', 2),
('Government Compliance Numbers', 'Government assigned identification numbers', 3),
('Medical and Health Requirements', 'Pre-employment and mandatory health screening documents', 4),
('Professional and Regulatory Documents', 'Role-based professional credentials and licenses', 5),
('Education Documents', 'Academic qualifications and training certificates', 6),
('Hospital Specific Requirements', 'Hospital-specific compliance and training documents', 7),
('Employment Status Specific', 'Documents required based on employment type', 8),
('Insurance Coverage', 'Insurance and benefits related documents', 9);

-- ====================================================================
-- INSERT DOCUMENT REQUIREMENTS
-- ====================================================================

-- Category 1: Core Employment Documents
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(1, 'Resume or CV', 'Current resume or curriculum vitae', TRUE, FALSE, 0, 'Updated version preferred'),
(1, 'Application Form', 'Completed job application form', TRUE, FALSE, 0, 'Hospital specific form'),
(1, 'Employment Contract', 'Signed employment contract', TRUE, FALSE, 0, 'Original or certified copy'),
(1, 'Job Offer or Appointment Letter', 'Official job offer or appointment letter', TRUE, FALSE, 0, 'From HR department'),
(1, 'Job Description', 'Role specific job description', TRUE, FALSE, 0, 'Signed by employee acknowledging receipt');

-- Category 2: Identity and Legal Documents
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(2, 'Government Issued ID', 'National ID, Drivers License, or valid passport', TRUE, TRUE, 60, 'Photocopy accepted'),
(2, 'Birth Certificate', 'Certified copy of birth certificate', TRUE, FALSE, 0, 'PSA certified copy required'),
(2, 'Marriage Certificate', 'Marriage certificate if applicable', FALSE, FALSE, 0, 'Required if married'),
(2, 'Barangay Clearance', 'Barangay clearance certificate', TRUE, TRUE, 90, 'Valid for one year'),
(2, 'Police Clearance', 'Police clearance certificate', TRUE, TRUE, 365, 'Valid for one year'),
(2, 'NBI Clearance', 'National Bureau of Investigation clearance', TRUE, TRUE, 365, 'Valid for one year, required for healthcare workers');

-- Category 3: Government Compliance Numbers
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(3, 'SSS ID or Number', 'Social Security System ID or number', TRUE, FALSE, 0, 'Required for pension contribution'),
(3, 'PhilHealth ID or Number', 'Philippine Health Insurance Corporation ID', TRUE, FALSE, 0, 'Required for health benefits'),
(3, 'Pag IBIG MID Number', 'Home Development Mutual Fund MID', TRUE, FALSE, 0, 'Required for housing benefits'),
(3, 'TIN', 'Tax Identification Number from BIR', TRUE, FALSE, 0, 'Required for tax purposes');

-- Category 4: Medical and Health Requirements
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(4, 'Pre Employment Medical Examination Result', 'Medical examination clearance from certified physician', TRUE, TRUE, 180, 'Mandatory for all hospital staff'),
(4, 'Chest X-ray', 'Chest X-ray examination result', TRUE, TRUE, 365, 'Required for healthcare workers'),
(4, 'Drug Test Result', 'Negative drug test result', TRUE, TRUE, 180, 'PDEA certified laboratory'),
(4, 'Hepatitis B Screening', 'Hepatitis B antibody test result', TRUE, TRUE, 365, 'Required for direct patient care staff'),
(4, 'Vaccination Records', 'Complete vaccination records', TRUE, FALSE, 0, 'Yellow book or vaccination card'),
(4, 'COVID-19 Vaccination Card', 'COVID-19 vaccination record', TRUE, TRUE, 365, 'At least 2 doses recommended'),
(4, 'Annual Physical Exam Clearance', 'Annual physical examination clearance', TRUE, TRUE, 365, 'Required annually for all staff');

-- Category 5: Professional and Regulatory Documents
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(5, 'PRC License', 'Professional Regulation Commission license', FALSE, TRUE, 60, 'Role-based: Nurses, Doctors, etc.'),
(5, 'PRC Board Rating', 'PRC board examination rating', FALSE, FALSE, 0, 'Role-based: Required for medical professionals'),
(5, 'Certificate of Registration', 'PRC Certificate of Registration', FALSE, TRUE, 60, 'Role-based: Healthcare professionals'),
(5, 'Certificate of Good Standing', 'Professional organization certificate of good standing', FALSE, TRUE, 365, 'Role-based: Healthcare professionals'),
(5, 'Specialty Training Certificates', 'Specialty or advanced training certificates', FALSE, TRUE, 365, 'Role-based: Specialists'),
(5, 'Internship or Residency Certificate', 'Medical internship or residency certificate', FALSE, FALSE, 0, 'Role-based: Doctors and specialists');

-- Category 6: Education Documents
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(6, 'Diploma', 'Educational diploma or degree certificate', TRUE, FALSE, 0, 'Certified copy required'),
(6, 'Transcript of Records', 'Academic transcript of records', TRUE, FALSE, 0, 'Certified by institution'),
(6, 'Training Certificates', 'Professional training and workshop certificates', FALSE, FALSE, 0, 'Job-related training'),
(6, 'Continuing Professional Development Certificates', 'CPD certificates and continuing education records', FALSE, TRUE, 365, 'Healthcare professionals: Minimum hours required');

-- Category 7: Hospital Specific Requirements
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(7, 'Orientation Attendance Record', 'Certificate of attendance in hospital orientation', TRUE, FALSE, 0, 'Signed by HR/Training department'),
(7, 'Infection Control Training Certificate', 'Infection control and prevention training certificate', TRUE, TRUE, 365, 'Annual renewal required'),
(7, 'HIPAA or Data Privacy Acknowledgment', 'Data privacy and confidentiality acknowledgment form', TRUE, FALSE, 0, 'Signed acknowledgment'),
(7, 'Code of Conduct Acknowledgment', 'Hospital code of conduct acknowledgment form', TRUE, FALSE, 0, 'Signed acknowledgment'),
(7, 'Confidentiality Agreement', 'Signed confidentiality and non-disclosure agreement', TRUE, FALSE, 0, 'Legal document'),
(7, 'Hospital Safety Training Certificate', 'Hospital safety procedures training certificate', TRUE, TRUE, 365, 'Annual renewal required'),
(7, 'Fire Safety and Emergency Procedures', 'Fire safety and emergency evacuation training', TRUE, TRUE, 365, 'Annual renewal required');

-- Category 8: Employment Status Specific
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`, `employment_status`) VALUES
(8, 'Contract Duration Agreement', 'Contract employment duration and terms agreement', TRUE, TRUE, 30, 'Contract employees only', 'Contract'),
(8, 'Contract Renewal Documents', 'Documents for contract renewal', FALSE, TRUE, 60, 'For contract renewal', 'Contract'),
(8, 'Probation Evaluation Forms', 'Employee performance during probation period', TRUE, FALSE, 0, 'Probationary employees only', 'Probationary'),
(8, 'School Endorsement Letter', 'School endorsement for OJT students', TRUE, FALSE, 0, 'OJT and interns only', 'OJT/Intern'),
(8, 'Memorandum of Agreement', 'OJT memorandum of agreement with school', TRUE, TRUE, 365, 'OJT and interns only', 'OJT/Intern'),
(8, 'Guardian Consent', 'Parent/guardian consent for minor employees', FALSE, FALSE, 0, 'If employee is minor', 'OJT/Intern');

-- Category 9: Insurance Coverage
INSERT INTO `document_requirements` (`category_id`, `document_type`, `document_description`, `is_mandatory`, `requires_expiry_date`, `expiry_alert_days`, `notes`) VALUES
(9, 'Health Insurance Information', 'PhilHealth or private health insurance details', TRUE, FALSE, 0, 'Insurance provider details'),
(9, 'Life Insurance Coverage', 'Life insurance policy details if applicable', FALSE, TRUE, 90, 'Optional group coverage'),
(9, 'Accidental Death and Dismemberment Insurance', 'AD&D insurance coverage confirmation', FALSE, FALSE, 0, 'If applicable to role'),
(9, 'Disability Insurance', 'Disability insurance information', FALSE, FALSE, 0, 'If applicable');

-- ====================================================================
-- SAMPLE DATA: Employee Document Checklist (for Employee ID 1)
-- ====================================================================
-- This will be populated by triggers or procedures when a new employee is added
-- For now, this shows the structure
