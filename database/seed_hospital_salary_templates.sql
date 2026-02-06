-- ====================================================================
-- Hospital Salary Templates - Doctor (ER Ward ROD) and Nurse (Staff Nurse)
-- Semi-monthly payroll only; Annual frequency not used
-- ====================================================================

-- Doctor: Position=Doctor, Role=ER Ward ROD, Per shift, 24h, 12nn-12nn
-- Basic 3000 per 24h shift; hourly ref 125; double pay addon 3000 (total 6000 on special dates)
-- Incentives: ER admissions, ER consultations, Medical procedures (variable 6k-10k take home)
-- Benefits: Free meals three times daily
-- Payroll: Semi-monthly; payout 7th and 22nd; cutoff 1-15 and 16-30
INSERT INTO `salary_templates` (
    `position`, `role`, `employment_type`, `salary_type`,
    `shift_duration_hours`, `basic_rate`, `rate_per_hour_reference`,
    `payroll_frequency`, `payout_day_1`, `payout_day_2`, `cutoff_start_day`, `cutoff_end_day`,
    `double_pay_addon`, `incentives_in_payroll`, `overtime_outside_base`, `night_differential_applied`,
    `incentive_rules`, `benefits`, `status`, `effective_date`
) VALUES (
    'Doctor', 'ER Ward ROD', 'Per shift', 'Per shift',
    24.00, 3000.00, 125.00,
    'Semi-Monthly', 7, 22, 1, 15,
    3000.00, 1, 0, 0,
    '["ER admissions","ER consultations","Medical procedures"]',
    'Free meals three times daily',
    'Active', '2020-01-01'
) ON DUPLICATE KEY UPDATE `basic_rate` = VALUES(`basic_rate`);

-- Nurse: Position=Nurse, Role=Staff Nurse, Hourly, 8h shift, 718 per 8h
-- Payroll: Semi-monthly; overtime computed outside base; night differential if scheduled
INSERT INTO `salary_templates` (
    `position`, `role`, `employment_type`, `salary_type`,
    `shift_duration_hours`, `basic_rate`, `rate_per_hour_reference`,
    `payroll_frequency`, `payout_day_1`, `payout_day_2`, `cutoff_start_day`, `cutoff_end_day`,
    `double_pay_addon`, `incentives_in_payroll`, `overtime_outside_base`, `night_differential_applied`,
    `incentive_rules`, `benefits`, `status`, `effective_date`
) VALUES (
    'Nurse', 'Staff Nurse', 'Hourly', 'Hourly',
    8.00, 718.00, 89.75,
    'Semi-Monthly', 7, 22, 1, 15,
    0.00, 0, 1, 1,
    NULL,
    NULL,
    'Active', '2020-01-01'
) ON DUPLICATE KEY UPDATE `basic_rate` = VALUES(`basic_rate`);

-- Sample special pay dates (double pay applies on these dates)
INSERT INTO `special_pay_dates` (`date`, `label`) VALUES
('2026-01-01', 'New Year'),
('2026-04-09', 'Araw ng Kagitingan'),
('2026-12-25', 'Christmas')
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`);
