<?php
/**
 * Reliable SMTP Email Service for Gmail
 * Properly handles STARTTLS and TLS negotiation
 */

require_once __DIR__ . '/EmailConfig.php';

class ReliableEmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    private $last_error = '';
    
    public function __construct() {
        $config = EmailConfig::getConfig();
        $this->smtp_host = $config['host'];
        $this->smtp_port = $config['port'];
        $this->smtp_username = $config['username'];
        $this->smtp_password = $config['password'];
        $this->from_email = $config['from_email'];
        $this->from_name = $config['from_name'];
    }
    
    /**
     * Send OTP Email
     */
    public function sendOTP($to_email, $to_name, $otp, $expiry_minutes = 5) {
        $subject = 'Your Hospital HR System OTP Code';
        $body = $this->generateOTPEmail($to_name, $otp, $expiry_minutes);
        
        return $this->send($to_email, $to_name, $subject, $body);
    }
    
    /**
     * Send Email via SMTP
     */
    public function send($to_email, $to_name, $subject, $body) {
        try {
            // Connect and authenticate
            $smtp = $this->connectSMTP();
            
            if (!$smtp) {
                $this->last_error = 'Could not connect to SMTP server';
                return false;
            }
            
            // Send email
            $this->sendSMTPCommand($smtp, "MAIL FROM:<{$this->from_email}>\r\n", "250");
            $this->sendSMTPCommand($smtp, "RCPT TO:<{$to_email}>\r\n", "250");
            $this->sendSMTPCommand($smtp, "DATA\r\n", "354");
            
            // Compose message
            $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
            $headers .= "To: {$to_name} <{$to_email}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            
            $message = $headers . "\r\n" . $body;
            
            // Send message (replace line dots with double dots)
            $message = str_replace("\r\n.", "\r\n..", $message);
            fwrite($smtp, $message . "\r\n.\r\n");
            
            $response = $this->readSMTPResponse($smtp);
            
            if (strpos($response, '250') === false) {
                throw new Exception('Failed to send message: ' . $response);
            }
            
            // Close connection
            fwrite($smtp, "QUIT\r\n");
            fclose($smtp);
            
            return true;
            
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Connect to SMTP server with TLS
     */
    private function connectSMTP() {
        try {
            // Create socket connection
            $socket = fsockopen('tcp://' . $this->smtp_host, $this->smtp_port, $errno, $errstr, 30);
            
            if (!$socket) {
                throw new Exception("Connection failed: {$errstr} ({$errno})");
            }
            
            // Read server greeting
            $this->readSMTPResponse($socket);
            
            // Send EHLO
            $this->sendSMTPCommand($socket, "EHLO localhost\r\n");
            
            // Initiate STARTTLS
            $this->sendSMTPCommand($socket, "STARTTLS\r\n", "220");
            
            // Enable crypto
            stream_context_set_option($socket, 'ssl', 'verify_peer', false);
            stream_context_set_option($socket, 'ssl', 'verify_peer_name', false);
            stream_context_set_option($socket, 'ssl', 'allow_self_signed', true);
            
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
                throw new Exception('Failed to enable TLS');
            }
            
            // Send EHLO again after TLS
            $this->sendSMTPCommand($socket, "EHLO localhost\r\n");
            
            // Authenticate
            $this->sendSMTPCommand($socket, "AUTH LOGIN\r\n", "334");
            $this->sendSMTPCommand($socket, base64_encode($this->smtp_username) . "\r\n", "334");
            $this->sendSMTPCommand($socket, base64_encode($this->smtp_password) . "\r\n", "235");
            
            return $socket;
            
        } catch (Exception $e) {
            throw new Exception('SMTP connection error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send SMTP command
     */
    private function sendSMTPCommand($socket, $command, $expectedCode = null) {
        fwrite($socket, $command);
        $response = $this->readSMTPResponse($socket);
        
        if ($expectedCode && strpos($response, $expectedCode) === false) {
            throw new Exception("Unexpected SMTP response: {$response}");
        }
        
        return $response;
    }
    
    /**
     * Read SMTP response
     */
    private function readSMTPResponse($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }
    
    /**
     * Get last error
     */
    public function getLastError() {
        return $this->last_error;
    }
    
    /**
     * Generate OTP Email HTML
     */
    private function generateOTPEmail($name, $otp, $expiry_minutes) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .header p { margin: 5px 0 0 0; font-size: 14px; opacity: 0.9; }
        .content { padding: 30px; }
        .greeting { font-size: 16px; color: #333; margin-bottom: 20px; line-height: 1.5; }
        .otp-box { 
            background: linear-gradient(135deg, #f0f4ff 0%, #f9f9f9 100%);
            border: 2px solid #667eea; 
            border-radius: 8px; 
            padding: 25px; 
            text-align: center; 
            margin: 25px 0;
        }
        .otp-label { font-size: 13px; color: #666; margin-bottom: 10px; font-weight: 600; }
        .otp-code { 
            font-size: 42px; 
            font-weight: bold; 
            letter-spacing: 4px; 
            color: #667eea; 
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .otp-validity { font-size: 12px; color: #999; margin-top: 10px; }
        .warning { 
            background: #fffbea; 
            border-left: 4px solid #ffc107; 
            padding: 15px; 
            margin: 20px 0; 
            border-radius: 4px;
            font-size: 13px;
            color: #856404;
            line-height: 1.5;
        }
        .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hospital HR Management System</h1>
            <p>Two-Factor Authentication</p>
        </div>
        <div class="content">
            <p class="greeting">Hello <strong>{$name}</strong>,</p>
            <p style="color: #666; font-size: 15px; line-height: 1.6;">
                You've initiated a login to your Hospital HR Management account. To complete the authentication process, please use the OTP code below:
            </p>
            
            <div class="otp-box">
                <div class="otp-label">Your Verification Code:</div>
                <div class="otp-code">{$otp}</div>
                <div class="otp-validity">Valid for {$expiry_minutes} minutes • Use only once</div>
            </div>
            
            <div class="warning">
                <strong>🔒 Security Reminder:</strong><br/>
                • Never share this code with anyone<br/>
                • Our team will never ask for your OTP<br/>
                • This code will expire after {$expiry_minutes} minutes
            </div>
            
            <p style="color: #999; font-size: 13px; margin-top: 20px;">
                If you didn't request this code, please ignore this email or contact support immediately.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0;">© 2026 Hospital HR Management System. All rights reserved.</p>
            <p style="margin: 5px 0 0 0;">This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
?>
