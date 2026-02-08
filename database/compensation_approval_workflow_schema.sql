-- Compensation Approval Workflow (Compensation submodule)
-- Enforce governance and control. Request → Review → Approval/Rejection. Unapproved items never reach Payroll. Auditable.

CREATE TABLE IF NOT EXISTS compensation_approval_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_type ENUM('salary_above_band', 'double_pay_tagging', 'manual_adjustment', 'policy_exception') NOT NULL,
    reference_type VARCHAR(50) NULL COMMENT 'e.g. salary_adjustment, incentive_event',
    reference_id INT NULL COMMENT 'id of related record',
    description TEXT NOT NULL,
    status ENUM('requested', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'requested',
    requested_by INT NULL,
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_by INT NULL,
    reviewed_at TIMESTAMP NULL,
    approval_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_request_type (request_type),
    INDEX idx_status (status),
    INDEX idx_requested_at (requested_at),
    INDEX idx_reviewed_at (reviewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
