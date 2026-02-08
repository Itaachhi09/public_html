-- Compensation History and Versioning (Compensation submodule)
-- Track versions of pay components, salary bands, policies, employee assignments. Old versions immutable. Payroll reads only approved active.

CREATE TABLE IF NOT EXISTS compensation_version_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('pay_component', 'salary_band', 'policy', 'employee_assignment') NOT NULL,
    entity_id INT NOT NULL,
    version_number INT NOT NULL,
    effective_date DATE NOT NULL,
    changed_by INT NULL,
    reason TEXT NOT NULL,
    status ENUM('draft', 'approved') NOT NULL DEFAULT 'approved',
    is_active TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Only one active per entity_type+entity_id',
    snapshot_json TEXT NULL COMMENT 'Optional snapshot of entity state at this version',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_entity_version (entity_type, entity_id, version_number),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_effective_date (effective_date),
    INDEX idx_status_active (status, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
