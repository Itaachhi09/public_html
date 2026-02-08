-- Pay Bonds and Contracts (Compensation submodule)
-- Bind employees to compensation rules. Active contract required to receive compensation; expired contracts block new entries.

-- Pay contracts (e.g. ER Doctor contract, Staff Nurse contract)
CREATE TABLE IF NOT EXISTS pay_contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    pay_grade_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    applicable_policies TEXT COMMENT 'e.g. policy references or notes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pay_grade_id (pay_grade_id),
    INDEX idx_dates (start_date, end_date),
    FOREIGN KEY (pay_grade_id) REFERENCES pay_grades(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employee assignments to contracts (bind employee to contract)
CREATE TABLE IF NOT EXISTS employee_contract_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    contract_id INT NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE NULL COMMENT 'NULL = ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_id (employee_id),
    INDEX idx_contract_id (contract_id),
    INDEX idx_effective (effective_from, effective_to),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (contract_id) REFERENCES pay_contracts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
