<?php
/**
 * Email Configuration
 * Stores SMTP credentials and email settings
 * 
 * IMPORTANT: In production, load these from environment variables
 * Use $_ENV or getenv() instead of hardcoding
 */

class EmailConfig {
    // SMTP Server Configuration
    public static $smtp_host = 'smtp.gmail.com';
    public static $smtp_port = 587;
    public static $smtp_encryption = 'tls'; // tls or ssl
    
    // Email Credentials
    public static $smtp_username = 'kingmaster6748@gmail.com';
    public static $smtp_password = 'jjawfunaxozmtxih';
    
    // Sender Information
    public static $from_email = 'kingmaster6748@gmail.com';
    public static $from_name = 'Hospital HR System';
    
    // OTP Configuration
    public static $otp_expiry_minutes = 10; // OTP valid for 10 minutes (increased from 5)
    public static $otp_length = 6; // 6 digit numeric OTP
    public static $max_otp_attempts = 3; // Max failed attempts before lockout
    public static $otp_resend_delay = 60; // Minimum seconds between resend requests
    
    /**
     * Load configuration from environment variables
     */
    public static function loadFromEnv() {
        self::$smtp_host = getenv('SMTP_HOST') ?: self::$smtp_host;
        self::$smtp_port = getenv('SMTP_PORT') ?: self::$smtp_port;
        self::$smtp_encryption = getenv('SMTP_ENCRYPTION') ?: self::$smtp_encryption;
        self::$smtp_username = getenv('SMTP_USERNAME') ?: self::$smtp_username;
        self::$smtp_password = getenv('SMTP_PASSWORD') ?: self::$smtp_password;
        self::$from_email = getenv('FROM_EMAIL') ?: self::$from_email;
        self::$from_name = getenv('FROM_NAME') ?: self::$from_name;
    }
    
    /**
     * Validate SMTP Configuration
     */
    public static function validateConfig() {
        $errors = [];
        
        if (empty(self::$smtp_username)) {
            $errors[] = 'SMTP_USERNAME not configured';
        }
        
        if (empty(self::$smtp_password)) {
            $errors[] = 'SMTP_PASSWORD not configured';
        }
        
        if (empty(self::$from_email)) {
            $errors[] = 'FROM_EMAIL not configured';
        }
        
        return $errors;
    }
    
    /**
     * Get SMTP configuration as array
     */
    public static function getConfig() {
        return [
            'host' => self::$smtp_host,
            'port' => self::$smtp_port,
            'encryption' => self::$smtp_encryption,
            'username' => self::$smtp_username,
            'password' => self::$smtp_password,
            'from_email' => self::$from_email,
            'from_name' => self::$from_name
        ];
    }
}

/**
 * ENVIRONMENT SETUP INSTRUCTIONS:
 * 
 * For Gmail SMTP:
 * 1. Enable 2-Step Verification on your Google Account
 * 2. Generate App Password: myaccount.google.com/apppasswords
 * 3. Set environment variables:
 *    SMTP_HOST=smtp.gmail.com
 *    SMTP_PORT=587
 *    SMTP_ENCRYPTION=tls
 *    SMTP_USERNAME=your-email@gmail.com
 *    SMTP_PASSWORD=your-16-char-app-password
 *    FROM_EMAIL=your-email@gmail.com
 *    FROM_NAME=Hospital HR System
 * 
 * For Windows/XAMPP, edit C:\xampp\apache\bin\php.ini or use .env file
 * 
 * For testing without Gmail credentials:
 * Use: johnpaulaustria321@gmail.com (admin@admin 123)
 *      itachiuchiha10012@gmail.com (hrchief)
 * 
 * Create appropriate Gmail App Passwords for these accounts
 */
?>
