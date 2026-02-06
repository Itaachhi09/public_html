<?php
/**
 * Request Handler
 * Centralized request validation and data handling
 */

class Request {
    private $data = [];
    private $method;
    private $headers = [];

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->headers = function_exists('getallheaders') ? getallheaders() : $this->parseHeadersFallback();
        $this->parseInput();
    }

    private function parseHeadersFallback() {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (strpos($k, 'HTTP_') === 0) {
                $key = str_replace('_', '-', substr($k, 5));
                $headers[$key] = $v;
            }
        }
        return $headers;
    }

    /**
     * Parse incoming request data
     */
    private function parseInput() {
        if ($this->method === 'GET') {
            $this->data = $_GET;
        } elseif ($this->method === 'POST') {
            $this->data = $_POST;
            if (empty($this->data)) {
                $this->data = json_decode(file_get_contents('php://input'), true) ?? [];
            }
            // Include query string params (e.g. ?action=validatePayroll&id=7) so getInput('id') works for POST
            $this->data = array_merge($_GET ?? [], $this->data);
        } elseif (in_array($this->method, ['PUT', 'PATCH', 'DELETE'])) {
            $this->data = json_decode(file_get_contents('php://input'), true) ?? [];
            $this->data = array_merge($_GET ?? [], $this->data);
        }
    }

    /**
     * Get request method
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Get single input value
     */
    public function get($key = null) {
        if ($key === null) {
            return $this->data;
        }
        return $this->data[$key] ?? null;
    }

    /**
     * Check if input exists
     */
    public function has($key) {
        return isset($this->data[$key]);
    }

    /**
     * Validate required fields
     */
    public function validate($rules = []) {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if (strpos($rule, 'required') !== false && empty($this->data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }

            if (strpos($rule, 'email') !== false && !empty($this->data[$field])) {
                if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email';
                }
            }

            if (strpos($rule, 'numeric') !== false && !empty($this->data[$field])) {
                if (!is_numeric($this->data[$field])) {
                    $errors[$field] = ucfirst($field) . ' must be numeric';
                }
            }
        }

        return $errors;
    }

    /**
     * Sanitize input
     */
    public function sanitize($data = null) {
        if ($data === null) {
            $data = $this->data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
            return $data;
        }

        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get header value
     */
    public function getHeader($key) {
        return $this->headers[$key] ?? null;
    }

    /**
     * Get all headers
     */
    public function getHeaders() {
        return $this->headers;
    }
}
?>
