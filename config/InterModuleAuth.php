<?php
/**
 * Inter-Module Authentication
 * Allows secure communication between HR Core and other modules (Payroll, Compensation, etc.)
 */

class InterModuleAuth {
    private static $apiKey = 'HR_CORE_INTEGRATION_KEY_2026';  // Store in .env in production
    private static $timeout = 300;  // 5 minutes
    
    /**
     * Generate inter-module token
     * Used by HR Core to call other modules
     */
    public static function generateModuleToken($module_name, $user_id) {
        $timestamp = time();
        $token_data = [
            'module' => $module_name,
            'user_id' => $user_id,
            'timestamp' => $timestamp,
            'signature' => self::generateSignature($module_name, $user_id, $timestamp)
        ];
        
        return base64_encode(json_encode($token_data));
    }
    
    /**
     * Verify inter-module token
     * Used by receiving module to validate sender
     */
    public static function verifyModuleToken($token) {
        try {
            $decoded = json_decode(base64_decode($token), true);
            
            if (!$decoded || !isset($decoded['signature']) || !isset($decoded['timestamp'])) {
                return false;
            }
            
            // Check if token is expired
            if ((time() - $decoded['timestamp']) > self::$timeout) {
                return false;
            }
            
            // Verify signature
            $expected_signature = self::generateSignature(
                $decoded['module'],
                $decoded['user_id'],
                $decoded['timestamp']
            );
            
            if ($decoded['signature'] !== $expected_signature) {
                return false;
            }
            
            return $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Generate HMAC signature for token validation
     */
    private static function generateSignature($module, $user_id, $timestamp) {
        $data = $module . '|' . $user_id . '|' . $timestamp;
        return hash_hmac('sha256', $data, self::$apiKey);
    }
    
    /**
     * Make authenticated request from one module to another
     */
    public static function callModule($target_module, $endpoint, $data = [], $method = 'POST') {
        // Get current user ID from session
        $user_id = $_SESSION['user_id'] ?? 0;
        
        // Generate module token
        $token = self::generateModuleToken('hr_core', $user_id);
        
        // Prepare request
        $url = "/modules/{$target_module}/{$endpoint}";
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\nX-Module-Token: {$token}\r\n",
                'method' => $method,
                'content' => json_encode($data),
                'ignore_errors' => true,
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        
        try {
            $result = @file_get_contents($_SERVER['DOCUMENT_ROOT'] . $url, false, $context);
            
            if ($result === false) {
                return ['success' => false, 'error' => 'Module call failed'];
            }
            
            return json_decode($result, true) ?: ['success' => false, 'error' => 'Invalid response'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>
