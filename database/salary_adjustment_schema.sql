-- Salary Adjustment (Compensation submodule)
-- Controlled changes to base pay: temporary/permanent; triggers: Promotion, Approved exception, Policy change.
-- Adjustments require approval; respect salary bands; store date, reason, approver. No payroll computation.

CREATE TABLE IF NOT EXISTS salary_adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    adjustment_type ENUM('Temporary', 'Permanent') NOT NULL,
    trigger_type ENUM('Promotion', 'Approved exception', 'Policy change') NOT NULL,
    previous_amount DECIMAL(12, 2) NULL,
    new_amount DECIMAL(12, 2) NOT NULL,
    effective_date DATE NOT NULL,
    end_date DATE NULL COMMENT 'For temporary adjustments',
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    salary_band_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_effective_date (effective_date),
    INDEX idx_salary_band_id (salary_band_id),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (salary_band_id) REFERENCES salary_bands(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
