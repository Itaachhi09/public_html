<?php
/**
 * Base Controller
 * Abstract base class for all controllers
 */

abstract class BaseController {
    protected $auth;
    protected $request;
    protected $user;

    public function __construct() {
        $this->auth = new Auth();
        // Only instantiate Request if the class exists
        if (class_exists('Request')) {
            $this->request = new Request();
        }
        $this->checkAuth();
    }

    /**
     * Check if user is authenticated
     */
    protected function checkAuth() {
        $token = $this->auth->getBearerToken();
        
        if (!$token) {
            if (class_exists('Response')) {
                Response::unauthorized('No token provided');
            } else {
                // If Response class doesn't exist, just set empty user
                $this->user = [];
                return;
            }
        }

        $verified = $this->auth->verifyToken($token);
        
        if (!$verified) {
            if (class_exists('Response')) {
                Response::unauthorized('Invalid token');
            } else {
                // If Response class doesn't exist, just set empty user
                $this->user = [];
                return;
            }
        }

        $this->user = isset($verified['data']) && is_array($verified['data']) ? $verified['data'] : [];
        // Payroll/audit expect user_id; JWT payload uses id
        if (isset($this->user['id']) && !isset($this->user['user_id'])) {
            $this->user['user_id'] = $this->user['id'];
        }
    }

    /**
     * Check user role
     */
    protected function checkRole($required_roles = []) {
        if (empty($required_roles)) {
            return true;
        }
        $role = isset($this->user['role']) ? $this->user['role'] : null;
        if (!in_array($role, $required_roles, true)) {
            Response::forbidden('Insufficient permissions');
        }
        return true;
    }

    /**
     * Get current user
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Send JSON response
     */
    protected function respondSuccess($data = [], $message = 'Success', $status = 200) {
        Response::success($data, $message, $status);
    }

    /**
     * Send error response
     */
    protected function respondError($message = 'Error', $status = 400, $data = []) {
        Response::error($message, $status, $data);
    }

    /**
     * Validate request
     */
    protected function validate($rules = []) {
        $errors = $this->request->validate($rules);
        
        if (!empty($errors)) {
            Response::validationError($errors);
        }

        return true;
    }

    /**
     * Get request data
     */
    protected function getInput($key = null) {
        return $this->request->get($key);
    }

    /**
     * Sanitize request data
     */
    protected function sanitizeInput($data = null) {
        return $this->request->sanitize($data);
    }
}
?>
