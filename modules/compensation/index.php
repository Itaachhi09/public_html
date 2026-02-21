<?php
/**
 * Compensation Module Router
 */

session_start();

require_once dirname(__DIR__, 2) . '/config/Database.php';
require_once dirname(__DIR__, 2) . '/config/BaseModel.php';
require_once dirname(__DIR__, 2) . '/config/BaseController.php';
require_once dirname(__DIR__, 2) . '/config/Response.php';
require_once dirname(__DIR__, 2) . '/config/Auth.php';

require_once __DIR__ . '/models/BenefitDefinitionModel.php';
require_once __DIR__ . '/models/SalaryAdjustmentModel.php';
require_once __DIR__ . '/controllers/BenefitDefinitionController.php';
require_once __DIR__ . '/controllers/SalaryAdjustmentController.php';

$action = $_GET['action'] ?? 'list';
$entity = $_GET['entity'] ?? 'benefit';

try {
    switch ($action) {
        case 'list':
            $controller = ($entity === 'adjustment') ? new SalaryAdjustmentController() : new BenefitDefinitionController();
            $controller->list();
            break;
        case 'get':
            $id = $_GET['id'] ?? null;
            $controller = ($entity === 'adjustment') ? new SalaryAdjustmentController() : new BenefitDefinitionController();
            $controller->get($id);
            break;
        case 'create':
            $controller = ($entity === 'adjustment') ? new SalaryAdjustmentController() : new BenefitDefinitionController();
            $controller->create();
            break;
        case 'update':
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            $controller = ($entity === 'adjustment') ? new SalaryAdjustmentController() : new BenefitDefinitionController();
            $controller->update($id);
            break;
        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            $controller = ($entity === 'adjustment') ? new SalaryAdjustmentController() : new BenefitDefinitionController();
            $controller->delete($id);
            break;
        default:
            Response::error('Invalid action', 400);
    }
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
?>
