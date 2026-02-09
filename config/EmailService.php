<?php
/**
 * Email Service
 * Handles sending emails via SMTP for OTP delivery
 * 
 * Uses PHPMailer-like functionality with native PHP or custom SMTP
 * Supports Gmail SMTP with TLS encryption
 */

require_once __DIR__ . '/EmailConfig.php';

class EmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_encryption;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    private $last_error = '';
    
    /**
     * Constructor - Initialize SMTP settings
     */
    public function __construct() {
        $config = EmailConfig::getConfig();
        $this->smtp_host = $config['host'];
        $this->smtp_port = $config['port'];
        $this->smtp_encryption = $config['encryption'];
        $this->smtp_username = $config['username'];
        $this->smtp_password = $config['password'];
        $this->from_email = $config['from_email'];
        $this->from_name = $config['from_name'];
    }
    
    /**
     * Send OTP via Email
     * 
     * @param string $to_email Recipient email
     * @param string $to_name Recipient name
     * @param string $otp The OTP code to send
     * @param int $expiry_minutes Minutes until OTP expires
     * 
     * @return bool Success/failure
     */
    public function sendOTP($to_email, $to_name, $otp, $expiry_minutes = 5) {
        $subject = 'Your Hospital HR System OTP Code';
        $body = $this->generateOTPEmail($to_name, $otp, $expiry_minutes);
        
        return $this->send($to_email, $to_name, $subject, $body);
    }
    
    /**
     * Send generic email
     * 
     * @param string $to_email Recipient email
     * @param string $to_name Recipient name
     * @param string $subject Email subject
     * @param string $body HTML email body
     * 
     * @return bool Success/failure
     */
    public function send($to_email, $to_name, $subject, $body) {
        try {
            // Prepare headers
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$this->from_name} <{$this->from_email}>\r\n";
            $headers .= "Reply-To: {$this->from_email}\r\n";
            $headers .= "X-Mailer: Hospital-HR-System/1.0\r\n";
            
            // Prepare recipient
            $to = "{$to_name} <{$to_email}>";
            
            // Send using native mail() function with PHP.ini SMTP settings
            // For production, ensure php.ini has proper SMTP configuration
            if ($this->useNativeMail()) {
                return $this->sendViaPhpMail($to, $subject, $body, $headers);
            } else {
                // Fallback to SMTP socket connection
                return $this->sendViaSMTPSocket($to_email, $to_name, $subject, $body);
            }
            
        } catch (Exception $e) {
            $this->last_error = 'Email send error: ' . $e->getMessage();
            $this->logError('send_error', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if native mail() should be used
     * (In production, use proper SMTP configuration)
     */
    private function useNativeMail() {
        // For development/testing with Gmail, return false to use SMTP socket
        // For production sendmail, return true
        $mail_function_enabled = ini_get('sendmail_path') || ini_get('SMTP');
        return $mail_function_enabled && !$this->isGmailSMTP();
    }
    
    /**
     * Check if using Gmail SMTP
     */
    private function isGmailSMTP() {
        return strpos($this->smtp_host, 'gmail.com') !== false;
    }
    
    /**
     * Send via PHP native mail() function
     */
    private function sendViaPhpMail($to, $subject, $body, $headers) {
        $result = mail($to, $subject, $body, $headers);
        if (!$result) {
            $this->last_error = 'PHP mail() function failed';
        }
        return $result;
    }
    
    /**
     * Send via direct SMTP socket connection
     * Implements basic SMTP protocol for Gmail compatibility
     */
    private function sendViaSMTPSocket($to_email, $to_name, $subject, $body) {
        try {
            $socket = $this->connectToSMTP();
            
            if (!$socket) {
                throw new Exception('Failed to connect to SMTP server');
            }
            
            // SMTP conversation
            $this->sendCommand($socket, "EHLO localhost\r\n");
            
            // Enable TLS if required
            if ($this->smtp_encryption === 'tls') {
                $this->sendCommand($socket, "STARTTLS\r\n");
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                $this->sendCommand($socket, "EHLO localhost\r\n");
            }
            
            // Authenticate
            $this->sendCommand($socket, "AUTH LOGIN\r\n");
            $this->sendCommand($socket, base64_encode($this->smtp_username) . "\r\n");
            $this->sendCommand($socket, base64_encode($this->smtp_password) . "\r\n");
            
            // Send message
            $this->sendCommand($socket, "MAIL FROM:<{$this->from_email}>\r\n");
            $this->sendCommand($socket, "RCPT TO:<{$to_email}>\r\n");
            $this->sendCommand($socket, "DATA\r\n");
            
            // Prepare headers and body
            $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
            $headers .= "To: {$to_name} <{$to_email}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            
            $message = $headers . "\r\n" . $body;
            
            fwrite($socket, $message . "\r\n.\r\n");
            $response = fgets($socket, 256);
            
            $this->sendCommand($socket, "QUIT\r\n");
            fclose($socket);
            
            return strpos($response, '250') !== false;
            
        } catch (Exception $e) {
            $this->last_error = 'SMTP socket error: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Connect to SMTP server
     */
    private function connectToSMTP() {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        $port = ($this->smtp_encryption === 'ssl') ? 465 : $this->smtp_port;
        $transport = ($this->smtp_encryption === 'ssl') ? 'ssl' : 'tcp';
        
        $socket = stream_socket_client(
            "{$transport}://{$this->smtp_host}:{$port}",
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$socket) {
            throw new Exception("SMTP connection failed: {$errstr} ({$errno})");
        }
        
        // Read server greeting
        fgets($socket, 256);
        
        return $socket;
    }
    
    /**
     * Send SMTP command and get response
     */
    private function sendCommand($socket, $command) {
        fwrite($socket, $command);
        $response = fgets($socket, 256);
        
        if (strpos($response, '5') === 0) {
            throw new Exception("SMTP error: {$response}");
        }
        
        return $response;
    }
    
    /**
     * Generate OTP email HTML body
     */
    private function generateOTPEmail($name, $otp, $expiry_minutes) {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 20px; }
        .otp-box { 
            background: #f9f9f9; 
            border: 2px solid #667eea; 
            border-radius: 8px; 
            padding: 20px; 
            text-align: center; 
            margin: 25px 0;
        }
        .otp-code { 
            font-size: 36px; 
            font-weight: bold; 
            color: #667eea; 
            letter-spacing: 8px; 
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .otp-expiry { 
            color: #e74c3c; 
            font-size: 14px; 
            margin-top: 10px;
        }
        .info { 
            background: #f0f8ff; 
            border-left: 4px solid #667eea; 
            padding: 15px; 
            margin: 20px 0;
            font-size: 14px;
            color: #555;
        }
        .warning { 
            background: #fff3cd; 
            border-left: 4px solid #ffc107; 
            padding: 15px; 
            margin: 20px 0;
            font-size: 14px;
            color: #856404;
        }
        .footer { 
            background: #f5f5f5; 
            border-top: 1px solid #ddd; 
            padding: 20px; 
            text-align: center; 
            font-size: 12px; 
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hospital HR System</h1>
            <p>Two-Factor Authentication</p>
        </div>
        <div class="content">
            <div class="greeting">
                <p>Hello <strong>{$name}</strong>,</p>
                <p>You've requested to log into your Hospital HR System account. Use the code below to complete your login:</p>
            </div>
            
            <div class="otp-box">
                <p style="margin: 0 0 10px 0; color: #666;">Your OTP Code:</p>
                <div class="otp-code">{$otp}</div>
                <div class="otp-expiry">⏱️ Valid for {$expiry_minutes} minutes only</div>
            </div>
            
            <div class="info">
                <strong>ℹ️ Security Notice:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Never share this code with anyone</li>
                    <li>Hospital HR system will never ask for your OTP via email</li>
                    <li>Each OTP can only be used once</li>
                    <li>Your account will be locked after 3 failed attempts</li>
                </ul>
            </div>
            
            <div class="warning">
                <strong>⚠️ If you didn't request this code:</strong>
                <p style="margin: 10px 0 0 0;">Someone may be trying to access your account. Please change your password immediately at hospital-hr.local/forgot-password.php</p>
            </div>
            
            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                This is an automated message. Please do not reply to this email.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 5px 0;">Hospital HR Management System</p>
            <p style="margin: 5px 0; color: #999;">Protected by Enterprise Security Standards</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get last error message
     */
    public function getLastError() {
        return $this->last_error;
    }
    
    /**
     * Log email errors for debugging
     */
    private function logError($error_type, $message) {
        $log_file = __DIR__ . '/../logs/email_errors.log';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $log_entry = "[{$timestamp}] [{$error_type}] IP: {$ip_address} - {$message}\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
}

?>
