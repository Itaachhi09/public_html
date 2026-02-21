<?php
/**
 * Deductions Management Handler
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
require_once __DIR__ . '/models/PayrollComponent.php';

$component = new PayrollComponent();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_deduction') {
        $component_name = trim($_POST['component_name'] ?? '');
        $component_type = 'deduction';
        $percentage = (float) ($_POST['percentage'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        
        if (!$component_name || $percentage < 0) {
            $err = 'Component name and percentage are required.';
        } else {
            $component->create([
                'component_name' => $component_name,
                'component_type' => $component_type,
                'percentage' => $percentage,
                'description' => $description,
                'is_active' => 1,
            ]);
            $msg = "Deduction component added: $component_name";
        }
    } elseif ($action === 'update_deduction') {
        $id = (int) ($_POST['id'] ?? 0);
        $percentage = (float) ($_POST['percentage'] ?? 0);
        $is_active = !empty($_POST['is_active']) ? 1 : 0;
        
        if (!$id || $percentage < 0) {
            $err = 'ID and percentage are required.';
        } else {
            $component->update($id, [
                'percentage' => $percentage,
                'is_active' => $is_active,
            ]);
            $msg = 'Deduction component updated.';
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'deductions_management'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: /public_html/dashboard.php?' . http_build_query($params));
exit;
