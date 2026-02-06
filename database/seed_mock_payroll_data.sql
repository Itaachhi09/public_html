-- ====================================================================
-- Seed mock data for payroll processing (run after hr_core_schema and payroll_schema)
-- Use: mysql -u root public_html < database/seed_mock_payroll_data.sql
-- Or run in phpMyAdmin / MySQL client
-- ====================================================================

-- Semi-Monthly: Payout 7th & 22nd. Cutoff 15th & 30th (28 for Feb)
INSERT INTO `payroll_calendar` (`payroll_frequency`, `period_name`, `period_code`, `start_date`, `end_date`, `cutoff_date`, `pay_date`, `status`) VALUES
('Semi-Monthly', 'Jan 1-15 2026', '2026-01-A', '2026-01-01', '2026-01-15', '2026-01-15', '2026-01-22', 'Open'),
('Semi-Monthly', 'Jan 16-30 2026', '2026-01-B', '2026-01-16', '2026-01-30', '2026-01-30', '2026-02-07', 'Open'),
('Semi-Monthly', 'Feb 1-15 2026', '2026-02-A', '2026-02-01', '2026-02-15', '2026-02-15', '2026-02-22', 'Open'),
('Semi-Monthly', 'Feb 16-28 2026', '2026-02-B', '2026-02-16', '2026-02-28', '2026-02-28', '2026-03-07', 'Open'),
('Monthly', 'January 2026', '2026-01', '2026-01-01', '2026-01-31', '2026-01-25', '2026-02-05', 'Open'),
('Monthly', 'February 2026', '2026-02', '2026-02-01', '2026-02-28', '2026-02-25', '2026-03-05', 'Open'),
('Monthly', 'March 2026', '2026-03', '2026-03-01', '2026-03-31', '2026-03-25', '2026-04-05', 'Open')
ON DUPLICATE KEY UPDATE `status` = 'Open';

-- Add active salaries for employees who don't have one yet (so they appear in payroll runs)
INSERT INTO `employee_salaries` (`employee_id`, `salary_type`, `basic_rate`, `salary_grade_id`, `tax_status`, `dependents_count`, `bank_name`, `bank_account_number`, `payout_method`, `payroll_eligible`, `effective_date`)
SELECT e.employee_id, 'Monthly', 45000.00, 2, 'Single', 0, 'Sample Bank', CONCAT('ACC', e.employee_id), 'Bank Transfer', 1, '2020-01-01'
FROM `employees` e
WHERE e.employment_status = 'Active'
AND NOT EXISTS (
  SELECT 1 FROM `employee_salaries` es
  WHERE es.employee_id = e.employee_id
  AND es.effective_date <= CURDATE()
  AND (es.end_date IS NULL OR es.end_date >= CURDATE())
  AND es.payroll_eligible = 1
)
LIMIT 20;
