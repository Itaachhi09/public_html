<?php
/**
 * Payroll Module Setup - Create Database Tables
 * Run this once to initialize the payroll module tables
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protect setup script - only allow if admin
if (empty($_SESSION['token']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    die('Access denied. Only admins can run setup.');
}

$appRoot = dirname(dirname(__DIR__));
require_once $appRoot . '/config/BaseConfig.php';
require_once $appRoot . '/config/Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read SQL file
    $sqlFile = __DIR__ . '/create_tables.sql';
    if (!file_exists($sqlFile)) {
        http_response_code(404);
        die('SQL file not found: ' . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)), function($stmt) {
        return !empty($stmt) && strpos(trim($stmt), '--') !== 0;
    });
    
    $createdTables = [];
    foreach ($statements as $statement) {
        try {
            $conn->exec($statement);
            // Extract table name from CREATE TABLE statement
            if (preg_match('/CREATE TABLE IF NOT EXISTS\s+(\w+)/i', $statement, $matches)) {
                $createdTables[] = $matches[1];
            }
        } catch (Exception $e) {
            // Table might already exist, continue
            error_log("Setup notice: " . $e->getMessage());
        }
    }
    
    // Return success
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Payroll module tables created successfully',
        'tables' => $createdTables,
        'count' => count($createdTables)
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
