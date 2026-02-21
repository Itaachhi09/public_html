<?php
/**
 * HMO Module Router
 */

session_start();

require_once dirname(__DIR__, 2) . '/config/Database.php';
require_once dirname(__DIR__, 2) . '/config/BaseModel.php';
require_once dirname(__DIR__, 2) . '/config/BaseController.php';
require_once dirname(__DIR__, 2) . '/config/Response.php';
require_once dirname(__DIR__, 2) . '/config/Auth.php';

require_once __DIR__ . '/models/HMOProviderModel.php';
require_once __DIR__ . '/models/HMOPlanModel.php';
require_once __DIR__ . '/controllers/HMOProviderController.php';
require_once __DIR__ . '/controllers/HMOPlanController.php';

$action = $_GET['action'] ?? 'list';
$entity = $_GET['entity'] ?? 'provider';

try {
    switch ($action) {
        case 'list':
            $controller = ($entity === 'plan') ? new HMOPlanController() : new HMOProviderController();
            $controller->list();
            break;
        case 'get':
            $id = $_GET['id'] ?? null;
            $controller = ($entity === 'plan') ? new HMOPlanController() : new HMOProviderController();
            $controller->get($id);
            break;
        case 'create':
            $controller = ($entity === 'plan') ? new HMOPlanController() : new HMOProviderController();
            $controller->create();
            break;
        case 'update':
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            $controller = ($entity === 'plan') ? new HMOPlanController() : new HMOProviderController();
            $controller->update($id);
            break;
        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            $controller = ($entity === 'plan') ? new HMOPlanController() : new HMOProviderController();
            $controller->delete($id);
            break;
        default:
            Response::error('Invalid action', 400);
    }
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
?>
