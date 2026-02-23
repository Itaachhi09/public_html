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
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $msg ?: $err,
            'action' => $action
        ]);
        exit;
    }
    
    // Get payroll calendar
    else if ($action === 'get_calendar') {
        try {
            $year = $input['year'] ?? date('Y');
            $config_key = 'payroll_calendar_' . $year;
            
            // First try to get saved calendar
            $sql = "SELECT config_value FROM payroll_configurations WHERE config_key = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$config_key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $periods = [];
            
            if ($result && !empty($result['config_value'])) {
                // Use saved calendar
                $calendar_data = json_decode($result['config_value'], true);
                
                if (isset($calendar_data['period_1'])) {
                    $periods[] = [
                        'period_number' => 1,
                        'cutoff_start' => $calendar_data['period_1']['cutoff_start'],
                        'cutoff_end' => $calendar_data['period_1']['cutoff_end'],
                        'pay_date' => $calendar_data['period_1']['pay_date'],
                        'frequency' => 'Bi-weekly'
                    ];
                }
                if (isset($calendar_data['period_2'])) {
                    $periods[] = [
                        'period_number' => 2,
                        'cutoff_start' => $calendar_data['period_2']['cutoff_start'],
                        'cutoff_end' => $calendar_data['period_2']['cutoff_end'],
                        'pay_date' => $calendar_data['period_2']['pay_date'],
                        'frequency' => 'Bi-weekly'
                    ];
                }
            } else {
                // Build from individual config values
                $sql = "SELECT config_key, config_value FROM payroll_configurations 
                        WHERE config_key IN ('cutoff_1_start', 'cutoff_1_end', 'pay_day_1', 'cutoff_2_start', 'cutoff_2_end', 'pay_day_2') 
                        AND is_active = 1";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                
                if (!empty($configs)) {
                    // Period 1
                    if (isset($configs['cutoff_1_start'], $configs['cutoff_1_end'], $configs['pay_day_1'])) {
                        $cutoff_start = $year . '-' . str_pad((int)$configs['cutoff_1_start'], 2, '0', STR_PAD_LEFT) . '-01';
                        $cutoff_end = $year . '-' . str_pad((int)$configs['cutoff_1_end'], 2, '0', STR_PAD_LEFT) . '-28'; // Approximate
                        $pay_date = $year . '-' . str_pad((int)$configs['pay_day_1'], 2, '0', STR_PAD_LEFT) . '-01';
                        
                        $periods[] = [
                            'period_number' => 1,
                            'cutoff_start' => $cutoff_start,
                            'cutoff_end' => $cutoff_end,
                            'pay_date' => $pay_date,
                            'frequency' => 'Bi-weekly'
                        ];
                    }
                    
                    // Period 2
                    if (isset($configs['cutoff_2_start'], $configs['cutoff_2_end'], $configs['pay_day_2'])) {
                        $cutoff_start = $year . '-' . str_pad((int)$configs['cutoff_2_start'], 2, '0', STR_PAD_LEFT) . '-01';
                        $cutoff_end = $year . '-12-31'; // End of year for last period
                        $pay_date = $year . '-' . str_pad((int)$configs['pay_day_2'], 2, '0', STR_PAD_LEFT) . '-01';
                        
                        $periods[] = [
                            'period_number' => 2,
                            'cutoff_start' => $cutoff_start,
                            'cutoff_end' => $cutoff_end,
                            'pay_date' => $pay_date,
                            'frequency' => 'Bi-weekly'
                        ];
                    }
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'periods' => $periods,
                'message' => empty($periods) ? 'No calendar configured' : 'Calendar loaded'
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving calendar: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Get all salary components (earnings or deductions)
    else if ($action === 'get_components') {
        try {
            $type = $input['type'] ?? 'salary';
            
            // Map type to component_type DB value
            $componentType = ($type === 'salary') ? 'base' : 'deduction';
            
            $sql = "SELECT id, name, code, description FROM salary_component_definitions 
                    WHERE component_type = ? AND is_active = 1 ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$componentType]);
            $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'components' => $components ?: []
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving components: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Get single component for editing
    else if ($action === 'get_component') {
        try {
            $id = $input['id'] ?? 0;
            
            $sql = "SELECT id, name, code, description FROM salary_component_definitions 
                    WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $component = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($component) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'component' => $component
                ]);
            } else {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Component not found'
                ]);
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving component: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Add new component
    else if ($action === 'add_component') {
        try {
            $code = trim($input['code'] ?? '');
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $category = trim($input['category'] ?? '');
            $componentType = $input['component_type'] ?? 'base';
            
            if (empty($code) || empty($name)) {
                throw new Exception('Code and name are required');
            }
            
            // Map type to DB value
            $dbComponentType = ($componentType === 'salary') ? 'base' : 'deduction';
            
            $sql = "INSERT INTO salary_component_definitions 
                    (code, name, description, component_type, is_active, created_at) 
                    VALUES (?, ?, ?, ?, 1, NOW())";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$code, $name, $description, $dbComponentType]);
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Component added successfully',
                    'id' => $conn->lastInsertId()
                ]);
            } else {
                throw new Exception('Failed to add component');
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Edit component
    else if ($action === 'edit_component') {
        try {
            $id = $input['id'] ?? 0;
            $code = trim($input['code'] ?? '');
            $name = trim($input['name'] ?? '');
            $description = trim($input['description'] ?? '');
            $category = trim($input['category'] ?? '');
            
            if (empty($id) || empty($code) || empty($name)) {
                throw new Exception('ID, code and name are required');
            }
            
            $sql = "UPDATE salary_component_definitions 
                    SET name = ?, description = ?, updated_at = NOW()
                    WHERE id = ? AND is_active = 1";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$name, $description, $id]);
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Component updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update component');
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Delete component
    else if ($action === 'delete_component') {
        try {
            $id = $input['id'] ?? 0;
            
            if (empty($id)) {
                throw new Exception('Component ID is required');
            }
            
            // Soft delete
            $sql = "UPDATE salary_component_definitions SET is_active = 0, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Component deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete component');
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
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
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $msg ?: $err,
            'action' => $action
        ]);
        exit;
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
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $msg ?: $err,
            'action' => $action
        ]);
        exit;
    }
    
    // Default - Unknown action
    else {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Unknown action: ' . htmlspecialchars($action)
        ]);
        exit;
    }
}

// Handle GET requests (should not reach here but just in case)
header('Location: ' . BASE_URL . 'dashboard.php?ref=payroll&page=setup_configuration');
exit;
