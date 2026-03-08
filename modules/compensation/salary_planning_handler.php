<?php
/**
 * Salary Planning Form Handler
 * Processes POST from salary_planning view (no JS; server-side only)
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
require_once __DIR__ . '/models/PayGrade.php';
require_once __DIR__ . '/models/GradeLevel.php';
require_once __DIR__ . '/models/SalaryBand.php';

$db = (new Database())->connect();
$payGrade = new PayGrade();
$gradeLevel = new GradeLevel();
$salaryBand = new SalaryBand();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create_pay_grade') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        if ($code && $name) {
            if ($payGrade->codeExists($code)) {
                $err = 'Pay grade code already exists.';
            } else {
                $payGrade->create(['code' => $code, 'name' => $name, 'description' => trim($_POST['description'] ?? ''), 'status' => 'Active']);
                $msg = 'Pay grade created.';
            }
        } else {
            $err = 'Code and name are required.';
        }
    } elseif ($action === 'create_grade_level') {
        $pay_grade_id = (int) ($_POST['pay_grade_id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        if ($pay_grade_id && $code && $name) {
            if ($gradeLevel->codeExistsForGrade($pay_grade_id, $code)) {
                $err = 'Grade level code already exists for this pay grade.';
            } else {
                $gradeLevel->create(['pay_grade_id' => $pay_grade_id, 'code' => $code, 'name' => $name, 'description' => trim($_POST['description'] ?? ''), 'sort_order' => (int) ($_POST['sort_order'] ?? 0), 'status' => 'Active']);
                $msg = 'Grade level created.';
            }
        } else {
            $err = 'Pay grade, code and name are required.';
        }
    } elseif ($action === 'create_band') {
        $pay_grade_id = (int) ($_POST['pay_grade_id'] ?? 0);
        $grade_level_id = !empty($_POST['grade_level_id']) ? (int) $_POST['grade_level_id'] : null;
        $min_salary = (float) ($_POST['min_salary'] ?? 0);
        $midpoint_salary = (float) ($_POST['midpoint_salary'] ?? 0);
        $max_salary = (float) ($_POST['max_salary'] ?? 0);
        if ($pay_grade_id && $min_salary >= 0 && $midpoint_salary >= 0 && $max_salary >= 0) {
            if ($salaryBand->existsForGradeLevel($pay_grade_id, $grade_level_id)) {
                $err = 'A salary band already exists for this pay grade and grade level. Edit or remove it first.';
            } elseif (!$salaryBand->validateBandOrder($min_salary, $midpoint_salary, $max_salary)) {
                $err = 'Band must satisfy: Min ≤ Midpoint ≤ Max.';
            } else {
                $salaryBand->create([
                    'pay_grade_id' => $pay_grade_id,
                    'grade_level_id' => $grade_level_id,
                    'min_salary' => $min_salary,
                    'midpoint_salary' => $midpoint_salary,
                    'max_salary' => $max_salary,
                    'status' => 'Active'
                ]);
                $msg = 'Salary band created.';
            }
        } else {
            $err = 'Pay grade and salary values are required.';
        }
    } elseif ($action === 'update_band') {
        $id = (int) ($_POST['id'] ?? 0);
        $min_salary = (float) ($_POST['min_salary'] ?? 0);
        $midpoint_salary = (float) ($_POST['midpoint_salary'] ?? 0);
        $max_salary = (float) ($_POST['max_salary'] ?? 0);
        if ($id && $salaryBand->validateBandOrder($min_salary, $midpoint_salary, $max_salary)) {
            $salaryBand->update($id, ['min_salary' => $min_salary, 'midpoint_salary' => $midpoint_salary, 'max_salary' => $max_salary]);
            $msg = 'Salary band updated.';
        } else {
            $err = $id ? 'Band must satisfy: Min ≤ Midpoint ≤ Max.' : 'Invalid band.';
        }
    } elseif ($action === 'delete_band') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $salaryBand->delete($id);
            $msg = 'Salary band removed.';
        }
    }
}

$params = ['ref' => 'compensation', 'page' => 'salary_planning'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ' . BASE_URL . 'dashboard.php?' . http_build_query($params));
exit;
