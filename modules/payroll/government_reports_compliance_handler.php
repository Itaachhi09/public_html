<?php
/**
 * Government Reports & Compliance Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/BaseConfig.php';

// Role-based access control
if (!canAccessMenuItem('payroll', 'government_reports_compliance')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied: government_reports_compliance']);
    exit;
}

require_once __DIR__ . '/models/GovernmentReport.php';

$report = new GovernmentReport();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate_report') {
        $report_type = trim($_POST['report_type'] ?? '');
        $period = trim($_POST['period'] ?? '');
        
        if (!$report_type || !$period) {
            $err = 'Report type and period are required.';
        } else {
            $filename = 'report_' . $report_type . '_' . date('Y-m-d_His') . '.pdf';
            $report->create([
                'report_type' => $report_type,
                'report_period' => $period,
                'generated_date' => date('Y-m-d H:i:s'),
                'file_path' => '/reports/' . $filename,
                'submission_status' => 'generated',
            ]);
            $msg = "Report generated: $report_type for $period";
        }
    } elseif ($action === 'submit_report') {
        $id = (int) ($_POST['id'] ?? 0);
        $record = $report->find($id);
        
        if (!$record) {
            $err = 'Report not found.';
        } else {
            $report->update($id, [
                'submission_status' => 'submitted',
                'submission_date' => date('Y-m-d H:i:s'),
            ]);
            $msg = 'Report submitted to government.';
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'government_reports_compliance'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: /public_html/dashboard.php?' . http_build_query($params));
exit;
