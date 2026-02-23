<?php
require_once 'config/Database.php';

$db = Database::getInstance();

$columns_to_add = [
    'withholding_tax' => "DECIMAL(12,2) DEFAULT 0",
    'sss_contribution' => "DECIMAL(12,2) DEFAULT 0",
    'philhealth_contribution' => "DECIMAL(12,2) DEFAULT 0",
    'pagibig_contribution' => "DECIMAL(12,2) DEFAULT 0",
    'incentives' => "DECIMAL(12,2) DEFAULT 0"
];

// Check which columns already exist
$existing = $db->query("DESCRIBE payroll_run_employees");
$existing_cols = [];
foreach ($existing as $col) {
    $existing_cols[] = $col['Field'];
}

// Add missing columns
foreach ($columns_to_add as $col => $def) {
    if (!in_array($col, $existing_cols)) {
        try {
            if ($col === 'incentives') {
                $db->directQuery("ALTER TABLE `payroll_run_employees` ADD COLUMN `$col` $def AFTER `basic_pay`");
            } elseif ($col === 'withholding_tax') {
                $db->directQuery("ALTER TABLE `payroll_run_employees` ADD COLUMN `$col` $def AFTER `net_pay`");
            } else {
                $db->directQuery("ALTER TABLE `payroll_run_employees` ADD COLUMN `$col` $def AFTER `{$prev_col}`");
            }
            echo "✓ Column `$col` added\n";
            $prev_col = $col;
        } catch (Exception $e) {
            echo "✗ Column `$col` error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✓ Column `$col` already exists\n";
    }
}
?>
