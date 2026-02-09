-- Login OTP Storage Table
-- Stores OTP verification records for two-factor authentication
-- Clean up expired OTPs using scheduled events or manual cleanup

CREATE TABLE IF NOT EXISTS login_otp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    otp_hash VARCHAR(255) NOT NULL COMMENT 'Bcrypt hashed OTP',
    expires_at TIMESTAMP NOT NULL COMMENT 'OTP expiration timestamp',
    attempts INT DEFAULT 0 COMMENT 'Failed verification attempts (max 3)',
    used_flag TINYINT(1) DEFAULT 0 COMMENT 'Flag to prevent OTP reuse',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at),
    INDEX idx_user_used (user_id, used_flag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Two-factor authentication OTP records';

-- Table for logging failed OTP attempts globally
CREATE TABLE IF NOT EXISTS otp_attempt_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    attempt_type VARCHAR(50) COMMENT 'otp_verification, otp_resend',
    status VARCHAR(20) COMMENT 'success, failed, locked',
    error_reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, attempt_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit log for OTP verification attempts';

-- Add is_2fa_enabled column to users table if doesn't exist
-- ALTER TABLE users ADD COLUMN is_2fa_enabled TINYINT(1) DEFAULT 1 COMMENT '2FA enabled for user' AFTER role;
