<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'public_html';

try {
    $conn = new PDO('mysql:host=' . $servername . ';dbname=' . $dbname, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check current status
    $stmt = $conn->prepare('SELECT id, provider_code, provider_name, provider_status FROM hmo_providers ORDER BY id');
    $stmt->execute();
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current Provider Status:\n";
    foreach ($providers as $p) {
        echo "ID: " . $p['id'] . " | Code: " . $p['provider_code'] . " | Name: " . $p['provider_name'] . " | Status: " . $p['provider_status'] . "\n";
    }
    
    // Update PhilHealth (ID 5) and St. Luke's (ID 4) to Active
    echo "\n--- Updating PhilHealth (ID 5) and St. Luke's (ID 4) to Active ---\n";
    $updateStmt = $conn->prepare('UPDATE hmo_providers SET provider_status = "Active" WHERE id IN (4, 5)');
    $updateStmt->execute();
    echo "Updated " . $updateStmt->rowCount() . " records\n";
    
    // Verify update
    $stmt = $conn->prepare('SELECT id, provider_code, provider_name, provider_status FROM hmo_providers WHERE id IN (4, 5) ORDER BY id');
    $stmt->execute();
    $updated = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nAfter Update:\n";
    foreach ($updated as $p) {
        echo "ID: " . $p['id'] . " | Code: " . $p['provider_code'] . " | Name: " . $p['provider_name'] . " | Status: " . $p['provider_status'] . "\n";
    }
} catch(PDOException $e) {
    echo 'Connection Error: ' . $e->getMessage();
}
?>
