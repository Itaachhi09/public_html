-- ====================================================================
-- HMO Module - Migration Script
-- Adds missing fields to match frontend expectations
-- ====================================================================

-- Add missing columns to hmo_providers table
ALTER TABLE `hmo_providers` ADD COLUMN `provider_type` ENUM('Corporate', 'Government', 'NGO') DEFAULT 'Corporate' AFTER `provider_status`;
ALTER TABLE `hmo_providers` ADD COLUMN `contract_start_date` DATE AFTER `provider_type`;
ALTER TABLE `hmo_providers` ADD COLUMN `contract_end_date` DATE AFTER `contract_start_date`;
ALTER TABLE `hmo_providers` ADD COLUMN `network_size` VARCHAR(100) AFTER `network_hospitals`;
ALTER TABLE `hmo_providers` ADD COLUMN `primary_contact_person` VARCHAR(255) AFTER `contact_email`;
ALTER TABLE `hmo_providers` ADD COLUMN `primary_contact_title` VARCHAR(100) AFTER `primary_contact_person`;

-- Update Philhealth (Government HMO)
UPDATE `hmo_providers` 
SET 
  provider_type = 'Government',
  contract_start_date = '2020-01-01',
  contract_end_date = '2030-12-31',
  network_size = '850+ Hospital Network',
  primary_contact_person = 'Dr. Maria Santos',
  primary_contact_title = 'Executive Vice President'
WHERE provider_code = 'PHH001';

-- Update Aetna (Corporate HMO)
UPDATE `hmo_providers` 
SET 
  provider_type = 'Corporate',
  contract_start_date = '2022-06-01',
  contract_end_date = '2027-05-31',
  network_size = '320+ Hospital Partner Network',
  primary_contact_person = 'Mr. James Chen',
  primary_contact_title = 'Country Manager'
WHERE provider_code = 'ATE001';

-- Update Insular Health (Corporate HMO)
UPDATE `hmo_providers` 
SET 
  provider_type = 'Corporate',
  contract_start_date = '2021-01-15',
  contract_end_date = '2030-01-14',
  network_size = '410+ Hospital Network',
  primary_contact_person = 'Ms. Patricia Reyes',
  primary_contact_title = 'Senior Vice President - Sales'
WHERE provider_code = 'INS001';

-- Update Medicard (Corporate HMO)
UPDATE `hmo_providers` 
SET 
  provider_type = 'Corporate',
  contract_start_date = '2020-07-01',
  contract_end_date = '2028-06-30',
  network_size = '380+ Hospital Network',
  primary_contact_person = 'Mr. Antonio Garcia',
  primary_contact_title = 'Regional Director'
WHERE provider_code = 'MED001';

-- Update St. Luke's Health Plan (Corporate HMO)
UPDATE `hmo_providers` 
SET 
  provider_type = 'Corporate',
  contract_start_date = '2021-03-15',
  contract_end_date = '2031-03-14',
  network_size = '285+ Hospital Partner Network',
  primary_contact_person = 'Ms. Isabella Lopez',
  primary_contact_title = 'Vice President, Corporate Health'
WHERE provider_code = 'STL001';

-- Add indexes for better query performance
ALTER TABLE `hmo_providers` ADD INDEX idx_contract_dates (contract_start_date, contract_end_date);
ALTER TABLE `hmo_providers` ADD INDEX idx_provider_type (provider_type);

-- Optional: Update is_active status to match status field (ensure consistency)
UPDATE `hmo_providers` SET is_active = 1 WHERE provider_status = 'Active';
UPDATE `hmo_providers` SET is_active = 0 WHERE provider_status != 'Active';

-- Verify the data
SELECT 
  id, provider_code, provider_name, provider_type, 
  contract_start_date, contract_end_date, provider_status
FROM `hmo_providers` 
ORDER BY provider_code;

-- Summary
SHOW COLUMNS FROM `hmo_providers`;
