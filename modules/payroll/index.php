<?php
/**
 * Payroll Module Router
 */

session_start();

require_once dirname(__DIR__, 2) . '/config/Database.php';
require_once dirname(__DIR__, 2) . '/config/BaseModel.php';
require_once dirname(__DIR__, 2) . '/config/BaseController.php';
require_once dirname(__DIR__, 2) . '/config/Response.php';
require_once dirname(__DIR__, 2) . '/config/Auth.php';

require_once __DIR__ . '/models/PayrollComponentModel.php';
require_once __DIR__ . '/models/EmployeeSalaryModel.php';
require_once __DIR__ . '/controllers/PayrollComponentController.php';
require_once __DIR__ . '/controllers/EmployeeSalaryController.php';

$action = $_GET['action'] ?? 'list';
$entity = $_GET['entity'] ?? 'component';

try {
    switch ($action) {
        case 'list':
            $controller = ($entity === 'salary') ? new EmployeeSalaryController() : new PayrollComponentController();
            $controller->list();
            break;
        case 'get':
            $id = $_GET['id'] ?? null;
            $controller = ($entity === 'salary') ? new EmployeeSalaryController() : new PayrollComponentController();
            $controller->get($id);
            break;
        case 'create':
            $controller = ($entity === 'salary') ? new EmployeeSalaryController() : new PayrollComponentController();
            $controller->create();
            break;
        case 'update':
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            $controller = ($entity === 'salary') ? new EmployeeSalaryController() : new PayrollComponentController();
            $controller->update($id);
            break;
        case 'delete':
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            $controller = ($entity === 'salary') ? new EmployeeSalaryController() : new PayrollComponentController();
            $controller->delete($id);
            break;
        default:
            Response::error('Invalid action', 400);
    }
} catch (Exception $e) {
    Response::error($e->getMessage(), 500);
}
?>
