<?php
/**
 * Salaries - Manage employee salary structures
 */

if (!defined('SYSTEM_INIT')) {
    http_response_code(403);
    die('No direct access allowed');
}

$appRoot = dirname(dirname(dirname(__DIR__)));
require_once $appRoot . '/config/BaseConfig.php';
require_once $appRoot . '/config/Database.php';
require_once __DIR__ . '/../models/Salary.php';

$salaryModel = new Salary();
$db = new Database();

$salaries = $salaryModel->getAllWithEmployee();
$employees = $db->query("SELECT employee_id, first_name, last_name, employee_code, employment_status FROM employees WHERE employment_status = 'Active' ORDER BY first_name, last_name");
?>

<div style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; margin: 0;">Salaries</h1>
            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Manage employee salary structures and rates</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if (!empty($salaries)): 
        $totalEmployees = count(array_unique(array_column($salaries, 'employee_id')));
        $avgSalary = array_sum(array_column($salaries, 'basic_rate')) / max(1, count($salaries));
        $maxSalary = max(array_column($salaries, 'basic_rate'));
        $totalPayroll = array_sum(array_column($salaries, 'basic_rate'));
    ?>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Employees</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;"><?php echo $totalEmployees; ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Average Salary</div>
            <div style="font-size: 2rem; font-weight: 700; color: #059669;">₱<?php echo number_format($avgSalary, 2); ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Highest Salary</div>
            <div style="font-size: 2rem; font-weight: 700; color: #3b82f6;">₱<?php echo number_format($maxSalary, 2); ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Payroll</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;">₱<?php echo number_format($totalPayroll, 2); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="font-weight: 600; margin-bottom: 1rem; color: #1f2937;">Filters</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937; font-size: 0.875rem;">Salary Type</label>
                <select id="typeFilter" onchange="applyFilters()" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="">All Types</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Hourly">Hourly</option>
                    <option value="Daily">Daily</option>
                    <option value="Annual">Annual</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937; font-size: 0.875rem;">Payroll Eligible</label>
                <select id="eligibleFilter" onchange="applyFilters()" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="">All</option>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937; font-size: 0.875rem;">Min Salary</label>
                <input type="number" id="minSalaryFilter" onchange="applyFilters()" placeholder="0.00" step="0.01" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>
        </div>
    </div>

    <!-- Salaries Table -->
    <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #3b82f6; padding-bottom: 0.5rem;">
            Salary Records
        </h2>

        <?php if (empty($salaries)): ?>
            <p style="color: #9ca3af; text-align: center; padding: 2rem;">No salary records yet. Add one to get started.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;" id="salariesTable">
                    <thead style="background: #f3f4f6;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Employee</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Code</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Type</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Basic Rate</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Effective Date</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Eligible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salaries as $salary): ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 500; color: #1f2937;"><?php echo htmlspecialchars($salary['first_name'] . ' ' . $salary['last_name']); ?></div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">ID: <?php echo (int)$salary['employee_id']; ?></div>
                                </td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($salary['employee_code']); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #dbeafe; color: #1e40af; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($salary['salary_type']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #059669;">
                                    ₱<?php echo number_format($salary['basic_rate'], 2); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280;">
                                    <?php echo date('M d, Y', strtotime($salary['effective_date'])); ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: <?php echo $salary['payroll_eligible'] ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $salary['payroll_eligible'] ? '#065f46' : '#991b1b'; ?>; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo $salary['payroll_eligible'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function applyFilters() {
    const typeFilter = document.getElementById('typeFilter').value.toLowerCase();
    const eligibleFilter = document.getElementById('eligibleFilter').value;
    const minSalary = parseFloat(document.getElementById('minSalaryFilter').value) || 0;
    
    const table = document.getElementById('salariesTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        const typeCell = row.cells[2].textContent.toLowerCase();
        const rateCell = parseFloat(row.cells[3].textContent.replace('₱', '').replace(/,/g, ''));
        const eligibleCell = row.cells[5].textContent.trim();
        
        let show = true;
        if (typeFilter && !typeCell.includes(typeFilter)) show = false;
        if (eligibleFilter !== '' && ((eligibleFilter === '1' && !eligibleCell.includes('Yes')) || (eligibleFilter === '0' && !eligibleCell.includes('No')))) show = false;
        if (rateCell < minSalary) show = false;
        
        row.style.display = show ? '' : 'none';
    }
}
</script>
