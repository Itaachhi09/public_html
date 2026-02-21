-- Populate complete payroll data for all 10 employees
-- Insert missing payroll_run_employees records

INSERT INTO payroll_run_employees (payroll_run_id, employee_id, basic_pay, gross_pay, total_deductions, net_pay, payslip_number, status, created_at, updated_at) 
VALUES
(1, 3, 18000.00, 19800.00, 2370.00, 17430.00, 'PS-2026-02-003', 'Pending', NOW(), NOW()),
(1, 4, 16000.00, 17600.00, 2080.00, 15520.00, 'PS-2026-02-004', 'Pending', NOW(), NOW()),
(1, 5, 22000.00, 24200.00, 2904.00, 21296.00, 'PS-2026-02-005', 'Pending', NOW(), NOW()),
(1, 6, 19000.00, 20900.00, 2507.00, 18393.00, 'PS-2026-02-006', 'Pending', NOW(), NOW()),
(1, 7, 20000.00, 22000.00, 2640.00, 19360.00, 'PS-2026-02-007', 'Pending', NOW(), NOW()),
(1, 8, 17000.00, 18700.00, 2244.00, 16456.00, 'PS-2026-02-008', 'Pending', NOW(), NOW()),
(1, 9, 21000.00, 23100.00, 2772.00, 20328.00, 'PS-2026-02-009', 'Pending', NOW(), NOW()),
(1, 10, 18500.00, 20350.00, 2442.00, 17908.00, 'PS-2026-02-010', 'Pending', NOW(), NOW());
