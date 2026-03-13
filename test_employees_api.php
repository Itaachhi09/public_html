<?php
// Test the getEmployees API
session_start();

require_once(__DIR__ . '/config/BaseConfig.php');
require_once(__DIR__ . '/config/Database.php');

try {
    $database = new Database();
    $db = $database->connect();
    
    // Test 1: Check if employees table exists and has data
    echo "=== Testing Employees Table ===\n";
    $query = "SELECT id, employee_id, employee_name FROM employees LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($employees) . " employees\n";
    echo json_encode($employees, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
