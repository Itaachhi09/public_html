-- Seed Salary Planning: Pay grades, grade levels, salary bands
-- ER Doctor (Senior), Staff Nurse (Mid) with defined bands

INSERT INTO pay_grades (code, name, description, status) VALUES
('ER_DOCTOR', 'ER Doctor', 'Emergency room physician pay grade', 'Active'),
('STAFF_NURSE', 'Staff Nurse', 'Staff nurse pay grade', 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

SET @pg_er = (SELECT id FROM pay_grades WHERE code = 'ER_DOCTOR' LIMIT 1);
SET @pg_nurse = (SELECT id FROM pay_grades WHERE code = 'STAFF_NURSE' LIMIT 1);

INSERT INTO grade_levels (pay_grade_id, code, name, description, sort_order, status) VALUES
(@pg_er, 'SENIOR', 'Senior', 'Senior level for doctors', 1, 'Active'),
(@pg_nurse, 'MID', 'Mid', 'Mid level for nurses', 1, 'Active')
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

SET @gl_er = (SELECT id FROM grade_levels WHERE pay_grade_id = @pg_er AND code = 'SENIOR' LIMIT 1);
SET @gl_nurse = (SELECT id FROM grade_levels WHERE pay_grade_id = @pg_nurse AND code = 'MID' LIMIT 1);

INSERT INTO salary_bands (pay_grade_id, grade_level_id, min_salary, midpoint_salary, max_salary, status) VALUES
(@pg_er, @gl_er, 6000.00, 7500.00, 9000.00, 'Active'),
(@pg_nurse, @gl_nurse, 2500.00, 3000.00, 3500.00, 'Active')
ON DUPLICATE KEY UPDATE min_salary = VALUES(min_salary), midpoint_salary = VALUES(midpoint_salary), max_salary = VALUES(max_salary);
