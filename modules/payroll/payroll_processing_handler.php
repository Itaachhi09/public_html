<?php
/**
 * Payroll Processing Handler: create run, process run. No JS.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['token'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/models/PayrollRun.php';
require_once __DIR__ . '/models/PayrollRunEmployee.php';
require_once __DIR__ . '/models/EmployeeSalary.php';

$run = new PayrollRun();
$runEmployee = new PayrollRunEmployee();
$salary = new EmployeeSalary();
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create_run') {
        $period_name = trim($_POST['period_name'] ?? '');
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        $pay_date = trim($_POST['pay_date'] ?? '');
        if (!$period_name || !$start_date || !$end_date || !$pay_date) {
            $err = 'Period name and dates are required.';
        } else {
            $run->create([
                'period_name' => $period_name,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'pay_date' => $pay_date,
                'status' => 'Draft',
            ]);
            $msg = 'Payroll run created (Draft). Process it to generate payslips.';
        }
    } elseif ($action === 'process_run') {
        $run_id = (int) ($_POST['run_id'] ?? 0);
        $r = $run->find($run_id);
        if (!$r || $r['status'] !== 'Draft') {
            $err = 'Run not found or not in Draft status.';
        } else {
            $existing = $runEmployee->query("SELECT COUNT(*) AS c FROM payroll_run_employees WHERE payroll_run_id = ?", [$run_id]);
            if ((int)($existing[0]['c'] ?? 0) > 0) {
                $err = 'Run already has employees. Close or use a new run.';
            } else {
                $eligible = $salary->getPayrollEligible();
                $count = 0;
                foreach ($eligible as $row) {
                $basic = (float) $row['basic_rate'];
                if ($row['salary_type'] === 'Monthly') {
                    $gross = $basic;
                } elseif ($row['salary_type'] === 'Annual') {
                    $gross = round($basic / 12, 2);
                } else {
                    $gross = $basic;
                }
                $deductions = 0;
                $net = $gross - $deductions;
                $payslip_number = 'PS-' . $run_id . '-' . $row['employee_id'] . '-' . date('Ym');
                $runEmployee->create([
                    'payroll_run_id' => $run_id,
                    'employee_id' => $row['employee_id'],
                    'basic_pay' => $gross,
                    'gross_pay' => $gross,
                    'total_deductions' => $deductions,
                    'net_pay' => $net,
                    'payslip_number' => $payslip_number,
                    'status' => 'Processed',
                ]);
                $count++;
                }
                $run->update($run_id, ['status' => 'Processed']);
                $msg = "Run processed. {$count} employee(s) added.";
            }
        }
    }
}

$params = ['ref' => 'payroll', 'page' => 'payroll_processing'];
if ($msg) $params['msg'] = urlencode($msg);
if ($err) $params['err'] = urlencode($err);
header('Location: ../../dashboard.php?' . http_build_query($params));
exit;
