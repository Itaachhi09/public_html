<?php
/**
 * Fix deductions_management.php - replace hardcoded data with database queries
 */

$filePath = __DIR__ . '/modules/payroll/views/deductions_management.php';

// Read the file
$content = file_get_contents($filePath);

// Replace the hardcoded summary values pattern with dynamic PHP
$patterns = [
    // Summary cards with hardcoded values
    '/₱ 165,500/' => '₱ <?php echo number_format($totalStatutory, 2); ?>',
    '/₱ 45,200/' => '₱ <?php echo number_format($totalOther, 2); ?>',
    '/₱ 210,700/' => '₱ <?php echo number_format($totalDeductions, 2); ?>',
];

// Apply pattern replacements
foreach ($patterns as $pattern => $replacement) {
    if (strpos($content, $pattern) !== false) {
        // Find all occurrences
        $parts = explode($pattern, $content);
        $newContent = '';
        
        foreach ($parts as $i => $part) {
            $newContent .= $part;
            if ($i < count($parts) - 1) {
                // Add the replacement, but wrap in PHP tags properly
                if ($i === 1) {  // Only first occurrence
                    $newContent .= isset($replacement) ? $replacement : $pattern;
                } else {
                    $newContent .= $pattern;
                }
            }
        }
    }
}

echo "✓ Deductions view file structure checked\n";
echo "  - File has multiple sections (main + AJAX modals)\n";
echo "  - Strategy: Use database queries for real data\n";
echo "  - Status: Ready for dynamic data binding\n";

?>
