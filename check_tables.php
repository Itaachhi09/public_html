<?php
require 'config/Database.php';

$db = new Database();
$conn = $db->connect();

// Check for employee_earnings table
$tables_to_check = ['employee_earnings', 'employee_deductions', 'employee_salary_components', 'employee_component_assignments'];

foreach ($tables_to_check as $table_name) {
    $result = $conn->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='$table_name'");
    if ($result->rowCount() > 0) {
        echo "✓ Table exists: $table_name\n";
        
        // Get columns info
        $cols = $conn->query("DESCRIBE $table_name");
        while ($col = $cols->fetch(PDO::FETCH_ASSOC)) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "✗ Table NOT found: $table_name\n";
    }
}
?>
