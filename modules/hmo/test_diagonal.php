<?php
// Diagnostics endpoint to check HMO database state
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../config/Database.php');

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Check if tables exist
    $tables_check = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='public_html' AND TABLE_NAME IN ('hmo_providers', 'hmo_plans', 'employee_hmo_enrollments')")->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'success' => true,
        'tables_found' => count($tables_check),
        'tables' => $tables_check,
        'data_counts' => []
    ];
    
    // Try to get counts
    foreach (['hmo_providers', 'hmo_plans', 'employee_hmo_enrollments'] as $table) {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM {$table}");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $response['data_counts'][$table] = $result ? $result['cnt'] : 'error';
        } catch (Exception $e) {
            $response['data_counts'][$table] = 'error: ' . $e->getMessage();
        }
    }
    
    // Try to get active plans count
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM hmo_plans WHERE is_active = 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['active_plans'] = $result ? $result['cnt'] : 'error';
    } catch (Exception $e) {
        $response['active_plans'] = 'error: ' . $e->getMessage();
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
