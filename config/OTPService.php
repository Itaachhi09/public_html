<?php
/**
 * OTP Service
 * Handles OTP generation, storage, verification, and security enforcement
 * 
 * Features:
 * - Generate 6-digit numeric OTP
 * - Hash OTP with bcrypt
 * - Set expiry time
 * - Track failed attempts
 * - Lock account after max attempts
 * - Prevent OTP reuse
 * - Log all attempts
 */

require_once __DIR__ . '/EmailConfig.php';
require_once __DIR__ . '/Database.php';

class OTPService {
    private $db;
    private $max_attempts = 3;
    private $otp_expiry_minutes = 5;
    private $resend_delay = 60; // seconds
    private $lockout_duration = 15 * 60; // 15 minutes in seconds
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = new Database();
        $this->db->connect();
        
        // Load configuration
        $this->max_attempts = EmailConfig::$max_otp_attempts ?? 3;
        $this->otp_expiry_minutes = EmailConfig::$otp_expiry_minutes ?? 5;
        $this->resend_delay = EmailConfig::$otp_resend_delay ?? 60;
    }
    
    /**
     * Generate and store OTP for user
     * 
     * @param int $user_id The user ID
     * @param string $user_email User's email (for logging)
     * 
     * @return array ['success' => bool, 'otp' => string or null, 'message' => string]
     */
    public function generateOTP($user_id, $user_email = '') {
        try {
            // Check if user is currently locked out
            if ($this->isUserLockedOut($user_id)) {
                return [
                    'success' => false,
                    'message' => 'Account is temporarily locked due to too many failed attempts. Try again in 15 minutes.'
                ];
            }
            
            // Invalidate any existing unexpired OTP for this user
            $this->invalidateExistingOTP($user_id);
            
            // Generate 6-digit numeric OTP
            $otp_plain = $this->generatePlainOTP();
            
            // Hash OTP using bcrypt
            $otp_hash = password_hash($otp_plain, PASSWORD_BCRYPT);
            
            // Calculate expiry time (current time + 5 minutes)
            $expires_at = date('Y-m-d H:i:s', time() + ($this->otp_expiry_minutes * 60));
            
            // Store OTP in database
            $stmt = $this->db->prepare(
                'INSERT INTO login_otp (user_id, otp_hash, expires_at, attempts, used_flag, created_at)
                 VALUES (:user_id, :otp_hash, :expires_at, 0, 0, NOW())'
            );
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':otp_hash', $otp_hash);
            $stmt->bindParam(':expires_at', $expires_at);
            $stmt->execute();
            
            // Log successful OTP generation
            $this->logAttempt($user_id, 'otp_generated', 'success', $user_email);
            
            return [
                'success' => true,
                'otp' => $otp_plain,  // Return plain OTP for email sending
                'message' => 'OTP generated successfully'
            ];
            
        } catch (Exception $e) {
            $this->logAttempt($user_id, 'otp_generation_error', 'error', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error generating OTP: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify OTP for user
     * 
     * @param int $user_id The user ID
     * @param string $otp_input The OTP entered by user
     * 
     * @return array ['success' => bool, 'message' => string, 'reason' => string]
     */
    public function verifyOTP($user_id, $otp_input) {
        try {
            // Sanitize input
            $otp_input = trim($otp_input);
            
            // Validate format (6 digits)
            if (!preg_match('/^\d{6}$/', $otp_input)) {
                return [
                    'success' => false,
                    'message' => 'OTP must be exactly 6 digits',
                    'reason' => 'invalid_format'
                ];
            }
            
            // Check if user is locked out
            if ($this->isUserLockedOut($user_id)) {
                return [
                    'success' => false,
                    'message' => 'Account locked. Too many failed attempts.',
                    'reason' => 'account_locked'
                ];
            }
            
            // Get active OTP record for user
            $stmt = $this->db->prepare(
                'SELECT * FROM login_otp 
                 WHERE user_id = :user_id 
                 AND used_flag = 0 
                 AND expires_at > NOW()
                 ORDER BY created_at DESC 
                 LIMIT 1'
            );
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $otp_record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$otp_record) {
                return [
                    'success' => false,
                    'message' => 'OTP expired or not found. Please request a new OTP.',
                    'reason' => 'otp_not_found'
                ];
            }
            
            // Check if already used
            if ($otp_record['used_flag'] == 1) {
                return [
                    'success' => false,
                    'message' => 'OTP already used. Please request a new OTP.',
                    'reason' => 'otp_already_used'
                ];
            }
            
            // Check if OTP has expired
            if (strtotime($otp_record['expires_at']) < time()) {
                return [
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new OTP.',
                    'reason' => 'otp_expired'
                ];
            }
            
            // Compare OTP hash
            if (!password_verify($otp_input, $otp_record['otp_hash'])) {
                // Increment failed attempts
                $attempts = $otp_record['attempts'] + 1;
                
                $update_stmt = $this->db->prepare(
                    'UPDATE login_otp SET attempts = :attempts WHERE id = :id'
                );
                $update_stmt->bindParam(':attempts', $attempts, PDO::PARAM_INT);
                $update_stmt->bindParam(':id', $otp_record['id'], PDO::PARAM_INT);
                $update_stmt->execute();
                
                // Log failed attempt
                $this->logAttempt($user_id, 'otp_verification', 'failed', 'Incorrect OTP');
                
                $remaining_attempts = $this->max_attempts - $attempts;
                
                if ($remaining_attempts <= 0) {
                    // Lock the user account
                    $this->lockUser($user_id);
                    return [
                        'success' => false,
                        'message' => 'Account locked. Too many failed attempts.',
                        'reason' => 'max_attempts_exceeded'
                    ];
                }
                
                return [
                    'success' => false,
                    'message' => "Invalid OTP. ({$remaining_attempts} attempts remaining)",
                    'reason' => 'incorrect_otp'
                ];
            }
            
            // OTP is valid - mark as used
            $mark_used = $this->db->prepare(
                'UPDATE login_otp SET used_flag = 1 WHERE id = :id'
            );
            $mark_used->bindParam(':id', $otp_record['id'], PDO::PARAM_INT);
            $mark_used->execute();
            
            // Log successful verification
            $this->logAttempt($user_id, 'otp_verification', 'success', 'OTP verified successfully');
            
            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'reason' => 'verified'
            ];
            
        } catch (Exception $e) {
            $this->logAttempt($user_id, 'otp_verification_error', 'error', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error verifying OTP',
                'reason' => 'server_error'
            ];
        }
    }
    
    /**
     * Check if user is locked out
     */
    public function isUserLockedOut($user_id) {
        try {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) as failed_count FROM otp_attempt_logs 
                 WHERE user_id = :user_id 
                 AND status = "locked" 
                 AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
            );
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['failed_count'] > 0;
        } catch (Exception $e) {
            // Default to not locked if query fails
            return false;
        }
    }
    
    /**
     * Lock user account temporarily
     */
    private function lockUser($user_id) {
        try {
            $this->logAttempt($user_id, 'otp_verification', 'locked', 'Account locked after max failed attempts');
        } catch (Exception $e) {
            // Silent fail
        }
    }
    
    /**
     * Check if user can request new OTP (throttle check)
     */
    private function canResendOTP($user_id) {
        try {
            $stmt = $this->db->prepare(
                'SELECT MAX(created_at) as last_created FROM login_otp 
                 WHERE user_id = :user_id'
            );
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !$result['last_created']) {
                return true; // First OTP request
            }
            
            $last_created = strtotime($result['last_created']);
            $time_diff = time() - $last_created;
            
            return $time_diff >= $this->resend_delay;
        } catch (Exception $e) {
            return true; // Allow if check fails
        }
    }
    
    /**
     * Invalidate existing OTP for user
     */
    private function invalidateExistingOTP($user_id) {
        try {
            $stmt = $this->db->prepare(
                'UPDATE login_otp SET used_flag = 1 
                 WHERE user_id = :user_id AND used_flag = 0'
            );
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            // Silent fail
        }
    }
    
    /**
     * Generate plain 6-digit OTP
     */
    private function generatePlainOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Log OTP attempt for audit trail
     */
    private function logAttempt($user_id, $attempt_type, $status, $error_reason = '') {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            
            $stmt = $this->db->prepare(
                'INSERT INTO otp_attempt_logs (user_id, ip_address, attempt_type, status, error_reason, created_at)
                 VALUES (:user_id, :ip_address, :attempt_type, :status, :error_reason, NOW())'
            );
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':attempt_type', $attempt_type);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':error_reason', $error_reason);
            $stmt->execute();
        } catch (Exception $e) {
            // Silently fail if logging fails
        }
    }
    
    /**
     * Get OTP statistics for user (admin view)
     */
    public function getUserOTPStats($user_id) {
        try {
            $stmt = $this->db->prepare(
                'SELECT 
                    COUNT(*) as total_attempts,
                    SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful,
                    SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                    SUM(CASE WHEN status = "locked" THEN 1 ELSE 0 END) as lockouts,
                    MAX(created_at) as last_attempt
                 FROM otp_attempt_logs
                 WHERE user_id = :user_id
                 AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)'
            );
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Clean up expired OTPs (call periodically)
     */
    public function cleanupExpiredOTPs() {
        try {
            $stmt = $this->db->prepare(
                'DELETE FROM login_otp 
                 WHERE expires_at < NOW() 
                 AND used_flag = 1'
            );
            
            $result = $stmt->execute();
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
}

?>
