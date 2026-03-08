<?php
require __DIR__ . '/../config/BaseConfig.php';
require __DIR__ . '/../config/Database.php';

$db = new Database();

// Find the benefit with code "d"
$benefit = $db->query("
    SELECT id, code, name, is_active 
    FROM benefit_definitions 
    WHERE code = 'd'
    LIMIT 1
");

if ($benefit) {
    $b = $benefit[0];
    echo "Found benefit:\n";
    echo "  ID: " . $b['id'] . "\n";
    echo "  Code: " . $b['code'] . "\n";
    echo "  Name: " . $b['name'] . "\n";
    echo "  Active: " . ($b['is_active'] ? 'Yes' : 'No') . "\n\n";
    
    // Delete it
    $result = $db->query("DELETE FROM benefit_definitions WHERE id = ?", [$b['id']]);
    
    echo "✓ Benefit deleted successfully!\n";
} else {
    echo "Benefit with code 'd' not found.\n";
}
?>
