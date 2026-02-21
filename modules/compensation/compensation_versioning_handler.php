<?php
/**
 * Compensation History and Versioning Handler
 * Record new versions (version number, effective date, changed by, reason). Old versions immutable. No JS.
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
if (!canAccessMenuItem('compensation', 'compensation_versioning')) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied: compensation_versioning']);
    exit;
}

require_once __DIR__ . '/models/CompensationVersionHistory.php';

$version = new CompensationVersionHistory();
$msg = '';
$err = '';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
$validTypes = ['pay_component', 'salary_band', 'policy', 'employee_assignment'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'record_version') {
        $entity_type = trim($_POST['entity_type'] ?? '');
        $entity_id = (int) ($_POST['entity_id'] ?? 0);
        $effective_date = trim($_POST['effective_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        if (!in_array($entity_type, $validTypes, true)) {
            $err = 'Invalid entity type.';
        } elseif (!$entity_id || !$effective_date || !$reason) {
            $err = 'Entity ID, effective date, and reason are required.';
        } else {
            $version_number = $version->getNextVersionNumber($entity_type, $entity_id);
            $newId = $version->create([
                'entity_type' => $entity_type,
                'entity_id' => $entity_id,
                'version_number' => $version_number,
                'effective_date' => $effective_date,
                'changed_by' => $userId,
                'reason' => $reason,
                'status' => 'approved',
                'is_active' => 1,
            ]);
            if ($newId) {
                $version->setActiveOnly($entity_type, $entity_id, $newId);
            }
            $msg = 'Version recorded. Old versions stay immutable. Payroll reads only approved active versions.';
        }
    }
}

$params = ['ref' => 'compensation', 'page' => 'compensation_versioning'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ' . BASE_URL . 'dashboard.php?' . http_build_query($params));
exit;
