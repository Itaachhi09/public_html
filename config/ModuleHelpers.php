<?php

/**
 * Module Helper Functions - Centralized response standardization and utilities
 * Used by all API endpoints across HR Core and Payroll modules
 */

class ModuleHelpers
{
    /**
     * Standardize all API responses to consistent format
     * @param bool $success - Operation success status
     * @param mixed $data - Response data
     * @param string $message - Response message
     * @param string $error - Error code if applicable
     * @return array - Standardized response
     */
    public static function response($success = true, $data = null, $message = '', $error = null)
    {
        return [
            'success' => (bool) $success,
            'message' => $message ?: ($success ? 'Success' : 'Error'),
            'data' => $data,
            'error' => $error
        ];
    }

    /**
     * Success response with data
     * @param mixed $data - Response data
     * @param string $message - Success message
     * @return array
     */
    public static function success($data = null, $message = 'Success')
    {
        return self::response(true, $data, $message);
    }

    /**
     * Error response
     * @param string $message - Error message
     * @param string $error - Error code
     * @param mixed $data - Error data
     * @return array
     */
    public static function error($message = 'Error', $error = 'ERROR', $data = null)
    {
        return self::response(false, $data, $message, $error);
    }

    /**
     * Validation error response
     * @param array $errors - Validation errors
     * @return array
     */
    public static function validationError($errors = [])
    {
        return self::error('Validation failed', 'VALIDATION_ERROR', $errors);
    }

    /**
     * Authorization error response
     * @return array
     */
    public static function unauthorized()
    {
        return self::error('Session expired - please login again', 'UNAUTHORIZED');
    }

    /**
     * Not found error response
     * @param string $resource - Resource not found
     * @return array
     */
    public static function notFound($resource = 'Resource')
    {
        return self::error("{$resource} not found", 'NOT_FOUND');
    }

    /**
     * Database error response
     * @param string $message - Error message
     * @return array
     */
    public static function databaseError($message = 'Database error')
    {
        return self::error($message, 'DATABASE_ERROR');
    }

    /**
     * Server error response
     * @param string $message - Error message
     * @return array
     */
    public static function serverError($message = 'Server error')
    {
        return self::error($message, 'SERVER_ERROR');
    }

    /**
     * Validate API request has required fields
     * @param array $required - Required field names
     * @param array $data - Data to validate (defaults to $_POST)
     * @return array - Array of errors if validation fails
     */
    public static function validateRequired($required = [], $data = null)
    {
        if ($data === null) {
            $data = $_POST;
        }

        $errors = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[$field] = "Field '{$field}' is required";
            }
        }

        return $errors;
    }

    /**
     * Validate email format
     * @param string $email - Email to validate
     * @return bool
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate date format (Y-m-d)
     * @param string $date - Date to validate
     * @return bool
     */
    public static function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Sanitize string input
     * @param string $input - Input to sanitize
     * @return string
     */
    public static function sanitize($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array of inputs
     * @param array $data - Data to sanitize
     * @return array
     */
    public static function sanitizeArray($data)
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitize($value);
            }
        }
        return $sanitized;
    }

    /**
     * Log API request/response for debugging
     * @param string $action - Action being performed
     * @param string $status - Status (GET, POST, SUCCESS, ERROR)
     * @param mixed $data - Data to log
     * @return void
     */
    public static function log($action, $status, $data = null)
    {
        $timestamp = date('Y-m-d H:i:s');
        $message = "[{$timestamp}] [{$status}] {$action}";
        
        if ($data) {
            $message .= " - " . json_encode($data);
        }
        
        error_log($message);
    }

    /**
     * Check if request is AJAX
     * @return bool
     */
    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    public static function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID from session
     * @return int|null
     */
    public static function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role from session
     * @return string|null
     */
    public static function getCurrentUserRole()
    {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Check if user has required role
     * @param array|string $roles - Required role(s)
     * @return bool
     */
    public static function hasRole($roles)
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        $userRole = self::getCurrentUserRole();
        $required = is_array($roles) ? $roles : [$roles];

        return in_array($userRole, $required);
    }

    /**
     * Get Bearer token from Authorization header
     * @return string|null
     */
    public static function getBearerToken()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Validate Bearer token (check if matches session)
     * @return bool
     */
    public static function validateBearerToken()
    {
        $token = self::getBearerToken();
        
        if (!$token) {
            return self::isAuthenticated();
        }

        // Verify token matches session (simplified - enhance in production)
        return isset($_SESSION['api_token']) && $_SESSION['api_token'] === $token;
    }

    /**
     * Convert database errors to user-friendly messages
     * @param Exception $e - Exception caught
     * @return string
     */
    public static function formatDatabaseError($e)
    {
        $message = $e->getMessage();
        
        // Hide database details from user
        if (strpos($message, 'SQLSTATE') !== false) {
            return 'Database operation failed. Please try again later.';
        }
        
        return $message;
    }

    /**
     * Paginate results
     * @param array $data - Data to paginate
     * @param int $page - Page number (1-based)
     * @param int $perPage - Items per page
     * @return array - Paginated data with metadata
     */
    public static function paginate($data, $page = 1, $perPage = 20)
    {
        $total = count($data);
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $items = array_slice($data, $offset, $perPage);

        return [
            'items' => $items,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
}
