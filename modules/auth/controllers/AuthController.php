<?php
/**
 * Authentication Controller
 * Handles user login, registration, and token generation
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

// Require only essential files
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/Auth.php';

// Start session if not already started; use path / so dashboard and other pages share the same session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
    session_start();
}

class AuthController {
    private $auth;
    private $db;

    public function __construct() {
        try {
            $this->auth = new Auth();
            $this->db = new Database();
            $this->db->connect();
        } catch (Exception $e) {
            $this->sendError('Initialization error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * User Login - POST /api/auth/login
     * Public endpoint - no authentication required
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

            // Find user in database
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email OR username = :username LIMIT 1');
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->sendError('Invalid email or password', 401);
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->sendError('Invalid email or password', 401);
            }

            // Generate JWT token (8 hours expiration)
            $token = $this->auth->generateToken([
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'role' => $user['role'],
                'name' => $user['name']
            ], 28800);

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['token'] = $token;
            $_SESSION['login_time'] = time();

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
            ], 'Login successful', 200);
        } catch (Exception $e) {
            $this->sendError('Database error: ' . $e->getMessage(), 500);
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
