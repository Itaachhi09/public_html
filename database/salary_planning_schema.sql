-- Salary Planning & Salary Structure (Compensation submodule)
-- Pay grades, grade levels, salary bands. Bands enforced system-wide; out-of-band requires approval.

-- Pay Grades (e.g. ER Doctor, Staff Nurse)
CREATE TABLE IF NOT EXISTS pay_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grade Levels (e.g. Senior for doctors, Mid for nurses)
CREATE TABLE IF NOT EXISTS grade_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pay_grade_id INT NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_grade_level (pay_grade_id, code),
    INDEX idx_pay_grade_id (pay_grade_id),
    INDEX idx_status (status),
    FOREIGN KEY (pay_grade_id) REFERENCES pay_grades(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salary Bands (min, midpoint, max per pay grade; optionally per grade level)
CREATE TABLE IF NOT EXISTS salary_bands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pay_grade_id INT NOT NULL,
    grade_level_id INT NULL,
    min_salary DECIMAL(12, 2) NOT NULL,
    midpoint_salary DECIMAL(12, 2) NOT NULL,
    max_salary DECIMAL(12, 2) NOT NULL,
    effective_from DATE NULL,
    effective_to DATE NULL,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pay_grade_id (pay_grade_id),
    INDEX idx_grade_level_id (grade_level_id),
    INDEX idx_status (status),
    INDEX idx_effective (effective_from, effective_to),
    UNIQUE KEY uk_band_grade_level (pay_grade_id, grade_level_id),
    FOREIGN KEY (pay_grade_id) REFERENCES pay_grades(id) ON DELETE CASCADE,
    FOREIGN KEY (grade_level_id) REFERENCES grade_levels(id) ON DELETE SET NULL,
    CONSTRAINT chk_band_order CHECK (min_salary <= midpoint_salary AND midpoint_salary <= max_salary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Out-of-band approval requests (assignments outside band require approval)
CREATE TABLE IF NOT EXISTS out_of_band_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    requested_amount DECIMAL(12, 2) NOT NULL,
    salary_band_id INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_status (status),
    INDEX idx_salary_band_id (salary_band_id),
    FOREIGN KEY (salary_band_id) REFERENCES salary_bands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
