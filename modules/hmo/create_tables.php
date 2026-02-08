<?php
// Execute SQL file to create billing and documents tables
require_once 'config/Database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // Read SQL file
    $sql = file_get_contents('database/seed_billing_documents.sql');
    
    // Execute multiple statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $conn->exec($statement);
            $success_count++;
            echo "✓ Executed: " . substr($statement, 0, 60) . "...\n";
        } catch (Exception $e) {
            $error_count++;
            $errors[] = $e->getMessage();
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Successful: $success_count\n";
    echo "Failed: $error_count\n";
    
    if (!empty($errors)) {
        echo "\nErrors:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
    } else {
        echo "\n✓ All tables created successfully!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
