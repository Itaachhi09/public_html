-- Seed payroll: sample employee salaries (run after employees exist)
INSERT INTO employee_salaries (employee_id, salary_type, basic_rate, effective_date, tax_status, payroll_eligible)
SELECT e.employee_id, 'Monthly', 35000.00, CURDATE(), 'Single', 1
FROM employees e
WHERE e.employment_status = 'Active'
AND NOT EXISTS (SELECT 1 FROM employee_salaries es WHERE es.employee_id = e.employee_id)
LIMIT 10;
