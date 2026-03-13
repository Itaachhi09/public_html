<?php
/**
 * Fix Foreign Key Constraints for HMO Billing Reconciliation
 * Adds ON DELETE CASCADE to prevent integrity constraint violations
 */

require_once(__DIR__ . '/config/Database.php');

try {
    $db = new Database();
    
    // Drop existing constraint
    echo "Dropping existing foreign key constraint...\n";
    $dropQuery = "ALTER TABLE hmo_billing_reconciliation 
                  DROP FOREIGN KEY hmo_billing_reconciliation_ibfk_1";
    $db->query($dropQuery);
    echo "✓ Constraint dropped\n";
    
    // Add new constraint with ON DELETE CASCADE
    echo "Adding new foreign key constraint with ON DELETE CASCADE...\n";
    $addQuery = "ALTER TABLE hmo_billing_reconciliation 
                 ADD CONSTRAINT hmo_billing_reconciliation_ibfk_1 
                 FOREIGN KEY (provider_id) 
                 REFERENCES hmo_providers (id) 
                 ON DELETE CASCADE";
    $db->query($addQuery);
    echo "✓ New constraint added\n";
    
    echo "\n✓ Foreign key constraints fixed successfully!\n";
    echo "You can now disable providers without encountering integrity constraint errors.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
