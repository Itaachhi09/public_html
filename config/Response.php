<?php
/**
 * Response Handler
 * Standardized API Response Format
 */

class Response {
    /**
     * Send JSON Response
     */
    public static function json($data = [], $status = 200, $message = 'Success') {
        header('Content-Type: application/json');
        http_response_code($status);

        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        echo json_encode($response);
        exit;
    }

    /**
     * Send Success Response
     */
    public static function success($data = [], $message = 'Success', $status = 200) {
        self::json($data, $status, $message);
    }

    /**
     * Send Error Response
     */
    public static function error($message = 'Error', $status = 400, $data = []) {
        self::json($data, $status, $message);
    }

    /**
     * Send Not Found Response
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }

    /**
     * Send Unauthorized Response
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }

    /**
     * Send Forbidden Response
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403);
    }

    /**
     * Send Validation Error Response
     */
    public static function validationError($errors = []) {
        self::json($errors, 422, 'Validation Error');
    }

    /**
     * Send Server Error Response
     */
    public static function serverError($message = 'Internal Server Error') {
        self::error($message, 500);
    }
}
?>
