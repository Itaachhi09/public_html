-- Seed benefit definitions (e.g. Free Meals TID)
INSERT INTO benefit_definitions (code, name, description, taxable, eligible_roles, effective_from, effective_to, attach_to, is_active) VALUES
('FREE_MEALS_TID', 'Free Meals TID', 'Free meals three times daily on duty', 0, 'ER Staff,Nurse,Doctor', CURDATE(), NULL, 'duty', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), taxable = VALUES(taxable), eligible_roles = VALUES(eligible_roles), effective_from = VALUES(effective_from), effective_to = VALUES(effective_to), attach_to = VALUES(attach_to);
