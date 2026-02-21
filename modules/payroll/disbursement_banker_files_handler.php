<?php
/**
 * Disbursement & Bank Files Handler
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
if (!canAccessMenuItem('payroll', 'disbursement_bank_files')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied: disbursement_bank_files']);
    exit;
}

require_once __DIR__ . '/models/Disbursement.php';
require_once __DIR__ . '/models/PayrollRun.php';

$disbursement = new Disbursement();
$payrollRun = new PayrollRun();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'generate') {
        $payroll_run = (int) ($_POST['payroll_run'] ?? 0);
        $bank_account = trim($_POST['bank_account'] ?? '');
        $file_format = trim($_POST['file_format'] ?? 'TXT');
        
        if (!$payroll_run || !$bank_account) {
            $err = 'Payroll run and bank account are required.';
        } else {
            $batch_ref = 'BATCH-' . date('Ymd') . '-' . uniqid();
            $disbursement->create([
                'payroll_run_id' => $payroll_run,
                'batch_reference' => $batch_ref,
                'bank_account' => $bank_account,
                'format' => $file_format,
                'status' => 'generated',
                'generated_by' => $_SESSION['user_id'] ?? 0,
                'generated_date' => date('Y-m-d H:i:s'),
            ]);
            $msg = "Bank file generated successfully. Batch: $batch_ref";
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'disbursement_bank_files'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: /public_html/dashboard.php?' . http_build_query($params));
exit;
