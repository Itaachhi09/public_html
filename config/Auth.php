<?php
/**
 * JWT Authentication Handler
 * Secure token-based authentication for API endpoints
 */

class Auth {
    private $secret_key = 'hospital_hr_system_secret_key_2025';
    private $algorithm = 'HS256';

    /**
     * Generate JWT Token
     */
    public function generateToken($data, $expiration = 3600) {
        $issued_at = time();
        $expire = $issued_at + $expiration;

        $payload = [
            'iat' => $issued_at,
            'exp' => $expire,
            'data' => $data
        ];

        $header = $this->base64url_encode(json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]));
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $header . "." . $payload_encoded, $this->secret_key, true);
        $signature_encoded = $this->base64url_encode($signature);

        return $header . "." . $payload_encoded . "." . $signature_encoded;
    }

    /**
     * Verify JWT Token
     */
    public function verifyToken($token) {
        $parts = explode('.', $token);
        
        if (count($parts) != 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        $signature_check = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $signature_check_encoded = $this->base64url_encode($signature_check);

        if ($signature !== $signature_check_encoded) {
            return false;
        }

        $payload_data = json_decode($this->base64url_decode($payload), true);

        if ($payload_data['exp'] < time()) {
            return false;
        }

        return $payload_data;
    }

    /**
     * Base64 URL Encode
     */
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL Decode
     */
    private function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }

    /**
     * Hash Password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify Password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Get Authorization Header (works with Apache, PHP-FPM, CGI)
     */
    public function getAuthorizationHeader() {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        }
        if (empty($headers) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        }
        if (empty($headers) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }
        if (empty($headers) && function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        if (empty($headers) && function_exists('getallheaders')) {
            $all = getallheaders();
            foreach ($all as $k => $v) {
                if (strtolower($k) === 'authorization') {
                    $headers = trim($v);
                    break;
                }
            }
        }
        if (empty($headers)) {
            foreach ($_SERVER as $k => $v) {
                if (strpos($k, 'HTTP_') === 0 && str_replace('_', '-', substr($k, 5)) === 'Authorization') {
                    $headers = trim($v);
                    break;
                }
            }
        }
        return $headers;
    }

    /**
     * Get Bearer Token
     */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}
?>
