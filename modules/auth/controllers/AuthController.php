<?php
/**
 * Authentication Controller
 * Handles user login, OTP generation, verification, and token generation
 * 
 * Two-Factor Authentication Flow:
 * 1. POST /login -> Validate credentials -> Generate OTP -> Send email -> Return otp_required
 * 2. POST /verify-otp -> Verify OTP -> Generate token -> Create session -> Return token
 * 3. POST /resend-otp -> Generate new OTP -> Send email -> Return success
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

// Require only essential files
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/Auth.php';
require_once __DIR__ . '/../../../config/OTPService.php';
require_once __DIR__ . '/../../../config/ReliableEmailService.php';

// Start session if not already started; use path / so dashboard and other pages share the same session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
    session_start();
}

class AuthController {
    private $auth;
    private $db;
    private $otp_service;
    private $email_service;

    public function __construct() {
        try {
            $this->auth = new Auth();
            $this->db = new Database();
            $this->db->connect();
            $this->otp_service = new OTPService();
            $this->email_service = new ReliableEmailService();
        } catch (Exception $e) {
            $this->sendError('Initialization error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User Login Step 1 - Validate credentials and send OTP
     * POST /api/auth/login
     * 
     * Request: { email, password }
     * Response: { otp_required: true, temp_token: string, message: string }
     */
    public function login() {
        try {
            // Get POST data
            $data = $_POST;
            if (empty($data)) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            }

            // Validate required fields
            $email = isset($data['email']) ? trim($data['email']) : '';
            $password = isset($data['password']) ? trim($data['password']) : '';

            if (empty($email) || empty($password)) {
                $this->sendError('Email and password are required', 400);
            }

            // Find user in database by email or username
            $stmt = $this->db->prepare(
                'SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1'
            );
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Log failed authentication attempt
                $this->logAuthAttempt('login_failed', 'user_not_found', $email);
                $this->sendError('Invalid email or password', 401);
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                // Log failed authentication attempt
                $this->logAuthAttempt('login_failed', 'password_mismatch', $email);
                $this->sendError('Invalid email or password', 401);
            }

            // Check if user account is active
            if (isset($user['status']) && $user['status'] !== 'active') {
                $this->logAuthAttempt('login_failed', 'account_inactive', $email);
                $this->sendError('Account is not active. Contact administrator.', 403);
            }

            // ===== TWO-FACTOR AUTHENTICATION FLOW =====
            
            // Generate OTP
            $otp_result = $this->otp_service->generateOTP($user['id'], $user['email']);
            
            if (!$otp_result['success']) {
                $this->logAuthAttempt('otp_generation_failed', $otp_result['message'], $email);
                $this->sendError('Unable to generate OTP: ' . $otp_result['message'], 500);
            }

            // Send OTP to user's email
            $otp_plain = $otp_result['otp'];
            $user_name = $user['name'] ?? $user['username'] ?? $user['email'];
            
            $email_sent = $this->email_service->sendOTP(
                $user['email'],
                $user_name,
                $otp_plain,
                5 // 5 minutes
            );

            if (!$email_sent) {
                $error_msg = $this->email_service->getLastError();
                $this->logAuthAttempt('email_send_failed', $error_msg, $email);
                $this->sendError('Unable to send OTP email. Please try again. Error: ' . $error_msg, 500);
            }

            // Log successful OTP email send
            $this->logAuthAttempt('otp_sent', 'success', $email);

            // Create temporary token for pending OTP verification
            // This token is only used to link the OTP verification to the login attempt
            $temp_token = $this->auth->generateToken([
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'type' => 'otp_pending'  // Mark as incomplete 2FA
            ], 600); // Valid for 10 minutes

            // Store user ID in session temporarily (for OTP verification)
            $_SESSION['otp_pending_user_id'] = $user['id'];
            $_SESSION['otp_pending_email'] = $user['email'];
            $_SESSION['otp_request_time'] = time();

            // Return response - OTP required
            $this->sendSuccess([
                'otp_required' => true,
                'temp_token' => $temp_token,
                'user_id' => $user['id'],
                'email' => $user['email']
            ], 'OTP sent to your email. Please verify to continue.', 200);

        } catch (Exception $e) {
            $this->sendError('Login error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User Login Step 2 - Verify OTP and complete login
     * POST /api/auth/verify-otp
     * 
     * Request: { user_id, otp }
     * Response: { token, user, message }
     */
    public function verifyOTP() {
        try {
            // Get POST data
            $data = $_POST;
            if (empty($data)) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            }

            // Validate required fields
            $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
            $otp = isset($data['otp']) ? trim($data['otp']) : '';

            if ($user_id <= 0 || empty($otp)) {
                $this->sendError('User ID and OTP are required', 400);
            }

            // Check session - ensure OTP verification is in progress for this request
            if (empty($_SESSION['otp_pending_user_id']) || $_SESSION['otp_pending_user_id'] != $user_id) {
                $this->sendError('No pending OTP verification. Please log in again.', 401);
            }

            // Check if OTP request hasn't expired (should be verified within 10 minutes)
            $otp_request_age = time() - $_SESSION['otp_request_time'];
            if ($otp_request_age > 600) { // 10 minutes
                session_destroy();
                $this->sendError('OTP request expired. Please log in again.', 401);
            }

            // Get user data
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->sendError('User not found', 404);
            }

            // Verify OTP using OTPService
            $otp_verification = $this->otp_service->verifyOTP($user_id, $otp);

            if (!$otp_verification['success']) {
                $this->logAuthAttempt('otp_verification_failed', $otp_verification['reason'], $user['email']);
                $this->sendError($otp_verification['message'], 401);
            }

            // ===== OTP VERIFIED - CREATE SESSION AND TOKEN =====

            // Generate JWT token (8 hours expiration)
            $token = $this->auth->generateToken([
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'role' => $user['role'],
                'name' => $user['name'],
                'type' => 'authenticated'
            ], 28800);

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['token'] = $token;
            $_SESSION['login_time'] = time();
            $_SESSION['otp_verified'] = true;
            
            // Clear OTP pending session data
            unset($_SESSION['otp_pending_user_id']);
            unset($_SESSION['otp_pending_email']);
            unset($_SESSION['otp_request_time']);

            // Log successful login
            $this->logAuthAttempt('login_success', 'otp_verified', $user['email']);

            // Return success response
            $this->sendSuccess([
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'username' => $user['username'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ],
                'token' => $token
            ], 'Login successful after OTP verification', 200);

        } catch (Exception $e) {
            $this->sendError('OTP verification error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resend OTP - Generate new OTP and send email
     * POST /api/auth/resend-otp
     * 
     * Request: { user_id }
     * Response: { message }
     */
    public function resendOTP() {
        try {
            // Get POST data
            $data = $_POST;
            if (empty($data)) {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
            }

            // Validate required fields
            $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;

            if ($user_id <= 0) {
                $this->sendError('User ID is required', 400);
            }

            // Check session - ensure OTP verification is in progress for this request
            if (empty($_SESSION['otp_pending_user_id']) || $_SESSION['otp_pending_user_id'] != $user_id) {
                $this->sendError('No pending OTP verification. Please log in again.', 401);
            }

            // Get user data
            $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->sendError('User not found', 404);
            }

            // Generate new OTP
            $otp_result = $this->otp_service->generateOTP($user['id'], $user['email']);
            
            if (!$otp_result['success']) {
                $this->logAuthAttempt('otp_generation_failed', $otp_result['message'], $user['email']);
                $this->sendError($otp_result['message'], 429);
            }

            // Send OTP to email
            $otp_plain = $otp_result['otp'];
            $user_name = $user['name'] ?? $user['username'] ?? $user['email'];
            
            $email_sent = $this->email_service->sendOTP(
                $user['email'],
                $user_name,
                $otp_plain,
                5
            );

            if (!$email_sent) {
                $error_msg = $this->email_service->getLastError();
                $this->logAuthAttempt('email_send_failed', $error_msg, $user['email']);
                $this->sendError('Unable to send OTP email: ' . $error_msg, 500);
            }

            // Log successful OTP resend
            $this->logAuthAttempt('otp_resent', 'success', $user['email']);

            // Update session request time
            $_SESSION['otp_request_time'] = time();

            $this->sendSuccess([], 'New OTP sent to your email', 200);

        } catch (Exception $e) {
            $this->sendError('Resend OTP error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User Logout
     */
    public function logout() {
        try {
            session_destroy();
            $this->sendSuccess([], 'Logout successful', 200);
        } catch (Exception $e) {
            $this->sendError('Logout error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify Token
     */
    public function verify() {
        try {
            if (!isset($_SESSION['token'])) {
                $this->sendError('No token found', 401);
            }

            $verified = $this->auth->verifyToken($_SESSION['token']);

            if (!$verified) {
                $this->sendError('Invalid or expired token', 401);
            }

            $this->sendSuccess($verified['data'], 'Token is valid', 200);
        } catch (Exception $e) {
            $this->sendError('Token verification error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Log authentication attempt for audit trail
     */
    private function logAuthAttempt($attempt_type, $status_or_reason, $email = '') {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Create logs directory if it doesn't exist
            $log_dir = __DIR__ . '/../../../logs';
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            $log_file = $log_dir . '/auth_attempts.log';
            $timestamp = date('Y-m-d H:i:s');
            $log_entry = "[{$timestamp}] [{$attempt_type}] [{$status_or_reason}] Email: {$email} | IP: {$ip_address}\n";
            
            file_put_contents($log_file, $log_entry, FILE_APPEND);
        } catch (Exception $e) {
            // Silently fail if logging fails
        }
    }

    /**
     * Send success response
     */
    private function sendSuccess($data = [], $message = 'Success', $status = 200) {
        http_response_code($status);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * Send error response
     */
    private function sendError($message = 'Error', $status = 400) {
        http_response_code($status);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Route dispatcher
     */
    public function dispatch($action) {
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->sendError('Action not found', 404);
        }
    }
}

// Initialize and dispatch
try {
    $controller = new AuthController();
    
    // Get the action from URL
    $action = isset($_GET['action']) ? $_GET['action'] : 'login';
    
    // Handle CORS preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        http_response_code(200);
        exit;
    }
    
    $controller->dispatch($action);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}
?>
