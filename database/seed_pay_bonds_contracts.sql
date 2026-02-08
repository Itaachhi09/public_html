-- Seed pay contracts: ER Doctor contract, Staff Nurse contract
SET @pg_er = (SELECT id FROM pay_grades WHERE code = 'ER_DOCTOR' LIMIT 1);
SET @pg_nurse = (SELECT id FROM pay_grades WHERE code = 'STAFF_NURSE' LIMIT 1);

INSERT INTO pay_contracts (code, name, pay_grade_id, start_date, end_date, applicable_policies) VALUES
('ER_DOCTOR_CONTRACT', 'ER Doctor contract', @pg_er, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'Salary bands; incentive rules; benefits per policy'),
('STAFF_NURSE_CONTRACT', 'Staff Nurse contract', @pg_nurse, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'Salary bands; incentive rules; benefits per policy')
ON DUPLICATE KEY UPDATE name = VALUES(name), pay_grade_id = VALUES(pay_grade_id), start_date = VALUES(start_date), end_date = VALUES(end_date), applicable_policies = VALUES(applicable_policies);
