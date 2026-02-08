-- Incentives and Bonuses (Compensation submodule)
-- Record workload-based earnings. Log incentive events per ER duty; only approved types; unapproved stay inactive.

-- Approved incentive (procedure) types – only these can be used when logging events
CREATE TABLE IF NOT EXISTS incentive_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_approved TINYINT(1) DEFAULT 1 COMMENT '1=approved, only these allowed when logging',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Incentive events: each event links to one ER duty (employee + duty_date); multiple procedures per duty allowed
CREATE TABLE IF NOT EXISTS incentive_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    duty_date DATE NOT NULL,
    procedure_type_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    approved_by INT NULL COMMENT 'User/approver who approved',
    status ENUM('pending', 'approved') DEFAULT 'pending' COMMENT 'Unapproved = inactive for payroll',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_duty_date (duty_date),
    INDEX idx_employee_duty (employee_id, duty_date),
    INDEX idx_status (status),
    INDEX idx_procedure_type_id (procedure_type_id),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (procedure_type_id) REFERENCES incentive_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
