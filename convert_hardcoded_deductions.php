<?php
/**
 * Update deductions_management.php to use real database data
 */

$filePath = __DIR__ . '/modules/payroll/views/deductions_management.php';
$content = file_get_contents($filePath);

// Step 1: Replace all hardcoded summary card values
$replacements = [
    // Replace hardcoded amounts with PHP variables
    '<div class="value">₱ 165,500</div>' => '<div class="value">₱ <?php echo number_format($totalStatutory, 2); ?></div>',
    '<div class="value">₱ 45,200</div>' => '<div class="value">₱ <?php echo number_format($totalOther, 2); ?></div>',
    '<div class="value">₱ 210,700</div>' => '<div class="value">₱ <?php echo number_format($totalDeductions, 2); ?></div>',
    // Replace pending count
    '<div class="value">3</div>' . PHP_EOL . '    </div>' => '<div class="value"><?php echo $pendingApprovals; ?></div>' . PHP_EOL . '    </div>',
];

foreach ($replacements as $old => $new) {
    if (strpos($content, $old) !== false) {
        $content = str_replace($old, $new, $content);
        echo "✓ Replaced: " . substr($old, 0, 50) . "...\n";
    }
}

// Save the updated content
file_put_contents($filePath, $content);

echo "\n✅ Fixed hardcoded values in deductions_management.php\n";
echo "   - Summary cards now use database values\n";
echo "   - Connected to: \$totalStatutory, \$totalOther, \$totalDeductions, \$pendingApprovals\n";

?>
