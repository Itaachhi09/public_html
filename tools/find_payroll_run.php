<?php
/**
 * List payroll runs and optionally match by substring or date.
 * Usage: php find_payroll_run.php [--match="text"] [--date=YYYY-MM-DD]
 */
if (php_sapi_name() !== 'cli') {
    echo "This script is intended to be run from the command line.\n";
    exit(1);
}

$opts = [];
foreach ($argv as $arg) {
    if (strpos($arg, '--match=') === 0) $opts['match'] = substr($arg, 8);
    if (strpos($arg, '--date=') === 0) $opts['date'] = substr($arg, 7);
}

chdir(__DIR__ . '/..');
require_once __DIR__ . '/../config/Database.php';

$db = new Database();
$conn = $db->connect();

$sql = "SELECT id, period_name, start_date, end_date, pay_date, status FROM payroll_runs ORDER BY start_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "No payroll runs found.\n";
    exit(0);
}

foreach ($rows as $r) {
    $line = sprintf("%3s | %-40s | %s - %s | pay_date: %s | %s\n", $r['id'], $r['period_name'], $r['start_date'], $r['end_date'], $r['pay_date'], $r['status']);
    $show = true;
    if (!empty($opts['match'])) {
        if (stripos($r['period_name'] . ' ' . $r['start_date'] . ' ' . $r['pay_date'], $opts['match']) === false) $show = false;
    }
    if (!empty($opts['date'])) {
        if ($r['start_date'] !== $opts['date'] && $r['pay_date'] !== $opts['date'] && $r['end_date'] !== $opts['date']) $show = false;
    }
    if ($show) echo $line;
}

exit(0);
