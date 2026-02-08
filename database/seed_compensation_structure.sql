-- Seed Compensation Structure: salary components, ensure full incentive list, benefit example
-- Salary component: ER Duty Pay
INSERT INTO salary_component_definitions (code, name, description, component_type, taxable, is_active) VALUES
('ER_DUTY_PAY', 'ER Duty Pay', '24 hour ER duty base pay', 'allowance', 1, 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), component_type = VALUES(component_type), taxable = VALUES(taxable);

-- Add Admission to incentive types (14 total; each logged per case; only predefined allowed)
INSERT INTO incentive_types (code, name, description, is_approved) VALUES
('ADMISSION', 'Admission', 'Patient admission procedure', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), is_approved = VALUES(is_approved);

-- Benefit: Free Meals TID (non taxable, ER staff on duty only) - ensure exists
INSERT INTO benefit_definitions (code, name, description, taxable, eligible_roles, effective_from, effective_to, attach_to, is_active) VALUES
('FREE_MEALS_TID', 'Free Meals TID', 'Free meals three times daily. Applies only to ER staff on duty.', 0, 'ER Staff', CURDATE(), NULL, 'duty', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), taxable = VALUES(taxable), eligible_roles = VALUES(eligible_roles), attach_to = VALUES(attach_to);
