<?php
/**
 * Replace hardcoded employee rows with database loop
 */

$filePath = __DIR__ . '/modules/payroll/views/deductions_management.php';
$content = file_get_contents($filePath);

// The hardcoded employee section starts after the table headers
// Find and replace the entire tbody content with a PHP loop

$oldTbody = '';
$oldTbody .= '        <tbody>' . PHP_EOL;
$oldTbody .= '          <tr>' . PHP_EOL;
$oldTbody .= '            <td>EMP-001</td>' . PHP_EOL;
$oldTbody .= '            <td>John Doe</td>' . PHP_EOL;
$oldTbody .= '            <td class="amount">11,000.00</td>' . PHP_EOL;
$oldTbody .= '            <td class="amount">2,850.00</td>' . PHP_EOL;
$oldTbody .= '            <td class="amount">500.00</td>' . PHP_EOL;
$oldTbody .= '            <td class="amount amount-total">3,350.00</td>' . PHP_EOL;
$oldTbody .= '            <td class="amount">7,650.00</td>' . PHP_EOL;
$oldTbody .= '            <td style="text-align: center;">30.5%</td>' . PHP_EOL;
$oldTbody .= '            <td><span class="badge badge-approved">Approved</span></td>' . PHP_EOL;
$oldTbody .= '            <td>' . PHP_EOL;
$oldTbody .= '              <button type="button" onclick="window.openDeductionsModal(1)" class="btn btn-secondary btn-sm">View</button>' . PHP_EOL;
$oldTbody .= '            </td>' . PHP_EOL;
$oldTbody .= '          </tr>';

// Check if first row exists
if (strpos($content, $oldTbody) !== false) {
    $newTbody = '        <tbody>' . PHP_EOL;
    $newTbody .= '          <?php' . PHP_EOL;
    $newTbody .= '          // Group deductions by employee' . PHP_EOL;
    $newTbody .= '          $empDeductionsSummary = [];' . PHP_EOL;
    $newTbody .= '          foreach ($deductions as $ded) {' . PHP_EOL;
    $newTbody .= '              $empId = $ded[\'employee_id\'];' . PHP_EOL;
    $newTbody .= '              if (!isset($empDeductionsSummary[$empId])) {' . PHP_EOL;
    $newTbody .= '                  $empDeductionsSummary[$empId] = [' . PHP_EOL;
    $newTbody .= '                      \'code\' => $ded[\'employee_code\'],' . PHP_EOL;
    $newTbody .= '                      \'name\' => $ded[\'first_name\'] . \' \' . $ded[\'last_name\'],' . PHP_EOL;
    $newTbody .= '                      \'gross\' => (float)$ded[\'gross_pay\'] ?: 0,' . PHP_EOL;
    $newTbody .= '                      \'stat\' => 0,' . PHP_EOL;
    $newTbody .= '                      \'other\' => 0,' . PHP_EOL;
    $newTbody .= '                      \'status\' => \'approved\'' . PHP_EOL;
    $newTbody .= '                  ];' . PHP_EOL;
    $newTbody .= '              }' . PHP_EOL;
    $newTbody .= '              if ($ded[\'is_mandatory\']) {' . PHP_EOL;
    $newTbody .= '                  $empDeductionsSummary[$empId][\'stat\'] += (float)$ded[\'amount\'];' . PHP_EOL;
    $newTbody .= '              } else {' . PHP_EOL;
    $newTbody .= '                  $empDeductionsSummary[$empId][\'other\'] += (float)$ded[\'amount\'];' . PHP_EOL;
    $newTbody .= '              }' . PHP_EOL;
    $newTbody .= '              if ($ded[\'status\'] === \'pending\') {' . PHP_EOL;
    $newTbody .= '                  $empDeductionsSummary[$empId][\'status\'] = \'pending\';' . PHP_EOL;
    $newTbody .= '              }' . PHP_EOL;
    $newTbody .= '          }' . PHP_EOL;
    $newTbody .= '          ?>' . PHP_EOL;
    $newTbody .= '          <?php if (empty($empDeductionsSummary)): ?>' . PHP_EOL;
    $newTbody .= '          <tr><td colspan="10" style="text-align: center; padding: 2rem;">No deduction data</td></tr>' . PHP_EOL;
    $newTbody .= '          <?php else: foreach ($empDeductionsSummary as $empId => $emp):' . PHP_EOL;
    $newTbody .= '              $tot = $emp[\'stat\'] + $emp[\'other\'];' . PHP_EOL;
    $newTbody .= '              $net = max(0, $emp[\'gross\'] - $tot);' . PHP_EOL;
    $newTbody .= '              $pct = $emp[\'gross\'] > 0 ? round(($tot / $emp[\'gross\']) * 100, 1) : 0;' . PHP_EOL;
    $newTbody .= '              $badge = $emp[\'status\'] === \'pending\' ? \'badge-pending\' : \'badge-approved\';' . PHP_EOL;
    $newTbody .= '              $text = $emp[\'status\'] === \'pending\' ? \'Pending Review\' : \'Approved\';' . PHP_EOL;
    $newTbody .= '          ?>' . PHP_EOL;
    $newTbody .= '          <tr>' . PHP_EOL;
    $newTbody .= '            <td><?php echo htmlspecialchars($emp[\'code\']); ?></td>' . PHP_EOL;
    $newTbody .= '            <td><?php echo htmlspecialchars($emp[\'name\']); ?></td>' . PHP_EOL;
    $newTbody .= '            <td class="amount"><?php echo number_format($emp[\'gross\'], 2); ?></td>' . PHP_EOL;
    $newTbody .= '            <td class="amount"><?php echo number_format($emp[\'stat\'], 2); ?></td>' . PHP_EOL;
    $newTbody .= '            <td class="amount"><?php echo number_format($emp[\'other\'], 2); ?></td>' . PHP_EOL;
    $newTbody .= '            <td class="amount amount-total"><?php echo number_format($tot, 2); ?></td>' . PHP_EOL;
    $newTbody .= '            <td class="amount"><?php echo number_format($net, 2); ?></td>' . PHP_EOL;
    $newTbody .= '            <td style="text-align: center;"><?php echo $pct; ?>%</td>' . PHP_EOL;
    $newTbody .= '            <td><span class="badge <?php echo $badge; ?>"><?php echo $text; ?></span></td>' . PHP_EOL;
    $newTbody .= '            <td><button type="button" onclick="window.openDeductionsModal(<?php echo $empId; ?>)" class="btn btn-secondary btn-sm">View</button></td>' . PHP_EOL;
    $newTbody .= '          </tr>' . PHP_EOL;
    $newTbody .= '          <?php endforeach; endif; ?>' . PHP_EOL;
    $newTbody .= '        </tbody>';
    
    // Find the section to replace (looking for the pattern after table headers)
    $pattern = '/        <tbody>[\s\S]*?<\/tbody>/';
    if (preg_match($pattern, $content, $matches)) {
        $content = preg_replace($pattern, $newTbody, $content, 1); // Replace first occurrence only (in main table)
        echo "✓ Replaced first employee table tbody with PHP loop\n";
    }
}

file_put_contents($filePath, $content);
echo "✅ Converted employee deductions table to use database queries\n";

?>
