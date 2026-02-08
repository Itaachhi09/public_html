<?php
/**
 * Test all HMO API endpoints to check what they return
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../config/Auth.php');
require_once('controllers/HMOController.php');

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    $controller = new HMOController();
    
    $results = [];
    
    // Test each endpoint
    $endpoints = [
        'getProviders' => function() { global $controller; return $controller->getProviders(); },
        'getPlans' => function() { global $controller; return $controller->getPlans(); },
        'getActiveEnrollments' => function() { global $controller; return $controller->getActiveEnrollments(); },
        'getAllClaims' => function() { global $controller; return $controller->getAllClaims(); },
        'getLifeEvents' => function() { global $controller; return $controller->getLifeEvents(); },
        'getBillingReconciliations' => function() { global $controller; return $controller->getBillingReconciliations(); },
        'getHMODocuments' => function() { global $controller; return $controller->getHMODocuments(); },
    ];
    
    foreach ($endpoints as $name => $callback) {
        try {
            $response = $callback();
            $count = 0;
            if (isset($response['data'])) {
                $count = count($response['data']);
            } elseif (isset($response['events'])) {
                $count = count($response['events']);
            } elseif (isset($response['reconciliations'])) {
                $count = count($response['reconciliations']);
            } elseif (isset($response['documents'])) {
                $count = count($response['documents']);
            }
            
            $results[$name] = [
                'status' => 'success',
                'success' => $response['success'] ?? false,
                'count' => $count,
                'keys_returned' => array_keys($response),
                'error' => $response['error'] ?? null
            ];
        } catch (Exception $e) {
            $results[$name] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
