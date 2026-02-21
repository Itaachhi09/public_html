<?php
/**
 * Security Audit Trail Handler
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
if (!canAccessMenuItem('payroll', 'security_audit_trail')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied: security_audit_trail']);
    exit;
}

require_once __DIR__ . '/models/PayrollAuditTrail.php';

$auditTrail = new PayrollAuditTrail();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'export_logs') {
        $start_date = trim($_POST['start_date'] ?? date('Y-m-01'));
        $end_date = trim($_POST['end_date'] ?? date('Y-m-d'));
        
        if (!$start_date || !$end_date) {
            $err = 'Date range is required.';
        } else {
            // Create CSV export using the model's query method
            $query = "SELECT * FROM payroll_audit_trail WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC";
            $records = $auditTrail->query($query, [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="audit_trail_' . date('Y-m-d_His') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'User', 'Action', 'Module', 'Description', 'Timestamp']);
            
            foreach ($records as $record) {
                fputcsv($output, [
                    $record['id'],
                    $record['user_id'],
                    $record['action'],
                    $record['module'],
                    $record['description'],
                    $record['created_at'],
                ]);
            }
            fclose($output);
            exit;
        }
    }
}

if ($err) {
    $params = ['ref' => 'payroll', 'page' => 'security_audit_trail', 'err' => urlencode($err)];
    header('Location: ' . BASE_URL . 'dashboard.php?' . http_build_query($params));
    exit;
}
