<?php
/**
 * Setup & Configuration Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/PayrollConfiguration.php';

$configuration = new PayrollConfiguration();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_config') {
        $config_key = trim($_POST['config_key'] ?? '');
        $config_value = trim($_POST['config_value'] ?? '');
        
        if (!$config_key || $config_value === '') {
            $err = 'Configuration key and value are required.';
        } else {
            $existing = $configuration->getByKey($config_key);
            if ($existing) {
                $configuration->update($existing['id'], ['config_value' => $config_value]);
            } else {
                $configuration->create([
                    'config_key' => $config_key,
                    'config_value' => $config_value,
                    'is_active' => 1,
                ]);
            }
            $msg = "Configuration saved: $config_key";
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'setup_configuration'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
