<?php
/**
 * Setup & Configuration Handler
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../config/BaseConfig.php';

// Role-based access control
if (!canAccessMenuItem('payroll', 'setup_configuration')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied: setup_configuration']);
    exit;
}

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$msg = '';
$err = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON or form data
    $input = $_POST;
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $input = is_array($jsonData) ? $jsonData : $_POST;
    }
    
    $action = $input['action'] ?? '';
    
    // Handle payroll calendar save
    if ($action === 'save_calendar') {
        $payroll_year = $input['payroll_year'] ?? date('Y');
        $cutoff_start_1 = $input['cutoff_start_1'] ?? '';
        $cutoff_end_1 = $input['cutoff_end_1'] ?? '';
        $pay_date_1 = $input['pay_date_1'] ?? '';
        $cutoff_start_2 = $input['cutoff_start_2'] ?? '';
        $cutoff_end_2 = $input['cutoff_end_2'] ?? '';
        $pay_date_2 = $input['pay_date_2'] ?? '';
        
        try {
            // Validate dates
            if (empty($cutoff_start_1) || empty($cutoff_end_1) || empty($pay_date_1) ||
                empty($cutoff_start_2) || empty($cutoff_end_2) || empty($pay_date_2)) {
                throw new Exception('All date fields are required');
            }
            
            // Save calendar configuration as JSON
            $calendar_data = json_encode([
                'year' => $payroll_year,
                'period_1' => [
                    'cutoff_start' => $cutoff_start_1,
                    'cutoff_end' => $cutoff_end_1,
                    'pay_date' => $pay_date_1
                ],
                'period_2' => [
                    'cutoff_start' => $cutoff_start_2,
                    'cutoff_end' => $cutoff_end_2,
                    'pay_date' => $pay_date_2
                ]
            ]);
            
            $config_key = 'payroll_calendar_' . $payroll_year;
            
            // Check if record exists
            $check_sql = "SELECT id FROM payroll_configurations WHERE config_key = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$config_key]);
            $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update existing record
                $update_sql = "UPDATE payroll_configurations SET config_value = ?, updated_at = NOW() WHERE config_key = ?";
                $update_stmt = $conn->prepare($update_sql);
                $result = $update_stmt->execute([$calendar_data, $config_key]);
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO payroll_configurations (config_key, config_value, is_active, created_at) VALUES (?, ?, 1, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $result = $insert_stmt->execute([$config_key, $calendar_data]);
            }
            
            if ($result) {
                $msg = "Payroll calendar for $payroll_year saved successfully";
                $success = true;
            } else {
                throw new Exception('Failed to save calendar data');
            }
        } catch (Exception $e) {
            $err = "Error saving calendar: " . $e->getMessage();
            $success = false;
        }
    }
    
    // Handle bank settings save
    elseif ($action === 'save_bank_settings') {
        $bank_settings = [
            'payroll_bank_account' => $input['payroll_bank_account'] ?? '',
            'bank_name' => $input['bank_name'] ?? '',
            'bank_code' => $input['bank_code'] ?? '',
            'branch_code' => $input['branch_code'] ?? '',
            'bank_file_format' => $input['bank_file_format'] ?? '',
            'batch_reference_pattern' => $input['batch_reference_pattern'] ?? '',
            'next_batch_number' => $input['next_batch_number'] ?? 1,
            'bank_contact_email' => $input['bank_contact_email'] ?? '',
            'bank_contact_person' => $input['bank_contact_person'] ?? ''
        ];
        
        try {
            if (empty($bank_settings['payroll_bank_account']) || empty($bank_settings['bank_name'])) {
                throw new Exception('Bank account and name are required');
            }
            
            $bank_data = json_encode($bank_settings);
            $config_key = 'bank_settings';
            
            // Check if record exists
            $check_sql = "SELECT id FROM payroll_configurations WHERE config_key = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->execute([$config_key]);
            $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update existing record
                $update_sql = "UPDATE payroll_configurations SET config_value = ?, updated_at = NOW() WHERE config_key = ?";
                $update_stmt = $conn->prepare($update_sql);
                $result = $update_stmt->execute([$bank_data, $config_key]);
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO payroll_configurations (config_key, config_value, is_active, created_at) VALUES (?, ?, 1, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $result = $insert_stmt->execute([$config_key, $bank_data]);
            }
            
            if ($result) {
                $msg = "Bank settings saved successfully";
                $success = true;
            } else {
                throw new Exception('Failed to save bank settings');
            }
        } catch (Exception $e) {
            $err = "Error saving bank settings: " . $e->getMessage();
            $success = false;
        }
    }
    
    // Handle generic configuration save
    elseif ($action === 'save_config') {
        $config_key = trim($input['config_key'] ?? '');
        $config_value = trim($input['config_value'] ?? '');
        
        if (!$config_key || $config_value === '') {
            $err = 'Configuration key and value are required.';
            $success = false;
        } else {
            try {
                // Check if record exists
                $check_sql = "SELECT id FROM payroll_configurations WHERE config_key = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->execute([$config_key]);
                $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    // Update existing record
                    $update_sql = "UPDATE payroll_configurations SET config_value = ?, updated_at = NOW() WHERE config_key = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $result = $update_stmt->execute([$config_value, $config_key]);
                } else {
                    // Insert new record
                    $insert_sql = "INSERT INTO payroll_configurations (config_key, config_value, is_active, created_at) VALUES (?, ?, 1, NOW())";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $result = $insert_stmt->execute([$config_key, $config_value]);
                }
                
                if ($result) {
                    $msg = "Configuration saved: $config_key";
                    $success = true;
                } else {
                    throw new Exception('Failed to save configuration');
                }
            } catch (Exception $e) {
                $err = "Error: " . $e->getMessage();
                $success = false;
            }
        }
    }
    
    // Return JSON for AJAX requests
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $msg ?: $err,
        'action' => $action
    ]);
    exit;
}

// Handle GET requests (should not reach here but just in case)
header('Location: ' . BASE_URL . 'dashboard.php?ref=payroll&page=setup_configuration');
exit;
