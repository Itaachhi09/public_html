<?php
/**
 * Deductions - Manage statutory, voluntary, and operational deductions
 */

if (!defined('SYSTEM_INIT')) {
    http_response_code(403);
    die('No direct access allowed');
}

$appRoot = dirname(dirname(dirname(__DIR__)));
require_once $appRoot . '/config/BaseConfig.php';
require_once $appRoot . '/config/Database.php';
require_once __DIR__ . '/../models/Deduction.php';

$deductionModel = new Deduction();
$db = new Database();

$deductions = $deductionModel->getAll();
$employees = $db->query("SELECT employee_id, first_name, last_name, employee_code FROM employees WHERE employment_status = 'Active' ORDER BY first_name, last_name");
$payrollRuns = $db->query("SELECT id, period_name FROM payroll_runs ORDER BY start_date DESC");

$deductionTypes = [
    'StatutoryDeductions' => [
        'label' => 'Statutory Deductions',
        'types' => ['SSS', 'PhilHealth', 'Pag-IBIG', 'Tax'],
        'colors' => ['bg' => '#dbeafe', 'text' => '#1e40af']
    ],
    'VoluntaryDeductions' => [
        'label' => 'Voluntary Deductions',
        'types' => ['Loan', 'HMO'],
        'colors' => ['bg' => '#fef3c7', 'text' => '#92400e']
    ],
    'OperationalDeductions' => [
        'label' => 'Operational Deductions',
        'types' => ['Absence', 'Late'],
        'colors' => ['bg' => '#fee2e2', 'text' => '#991b1b']
    ]
];

$currentCategory = isset($_GET['category']) ? $_GET['category'] : null;
?>

<div style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; margin: 0;">Deductions</h1>
            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Manage statutory, voluntary, and operational deductions</p>
        </div>
        <button onclick="toggleAddForm()" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
            + Add Deduction
        </button>
    </div>

    <!-- Summary Cards -->
    <?php if (!empty($deductions)): 
        $totalDeductions = count($deductions);
        $totalAmount = array_sum(array_column($deductions, 'amount'));
        $statutoryCount = count(array_filter($deductions, function($d) { 
            return in_array($d['type'], ['SSS', 'PhilHealth', 'Pag-IBIG', 'Tax']); 
        }));
        $employeesAffected = count(array_unique(array_column($deductions, 'employee_id')));
    ?>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Deductions</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;"><?php echo $totalDeductions; ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Amount</div>
            <div style="font-size: 2rem; font-weight: 700; color: #ef4444;">₱<?php echo number_format($totalAmount, 2); ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Statutory</div>
            <div style="font-size: 2rem; font-weight: 700; color: #3b82f6;"><?php echo $statutoryCount; ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Employees Affected</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;"><?php echo $employeesAffected; ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Category Filter -->
    <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <h3 style="font-weight: 600; margin-bottom: 1rem; color: #1f2937;">Filter by Category</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <button onclick="filterByCategory(null)" style="padding: 0.5rem 1rem; background: <?php echo !$currentCategory ? '#3b82f6' : 'white'; ?>; color: <?php echo !$currentCategory ? 'white' : '#3b82f6'; ?>; border: 2px solid #3b82f6; border-radius: 20px; cursor: pointer; font-weight: 500;">All</button>
            <button onclick="filterByCategory('Statutory')" style="padding: 0.5rem 1rem; background: <?php echo $currentCategory === 'Statutory' ? '#dbeafe' : 'white'; ?>; color: #1e40af; border: 2px solid #dbeafe; border-radius: 20px; cursor: pointer; font-weight: 500;">Statutory</button>
            <button onclick="filterByCategory('Voluntary')" style="padding: 0.5rem 1rem; background: <?php echo $currentCategory === 'Voluntary' ? '#fef3c7' : 'white'; ?>; color: #92400e; border: 2px solid #fef3c7; border-radius: 20px; cursor: pointer; font-weight: 500;">Voluntary</button>
            <button onclick="filterByCategory('Operational')" style="padding: 0.5rem 1rem; background: <?php echo $currentCategory === 'Operational' ? '#fee2e2' : 'white'; ?>; color: #991b1b; border: 2px solid #fee2e2; border-radius: 20px; cursor: pointer; font-weight: 500;">Operational</button>
        </div>
    </div>

    <!-- Add Deduction Form -->
    <div id="addFormDiv" style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: none;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #10b981; padding-bottom: 0.5rem;">Add Deduction</h2>
        <form id="addDeductionForm" method="POST" action="modules/payroll/api.php" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <input type="hidden" name="action" value="createDeduction">
            
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Employee *</label>
                <select name="employee_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="">Select Employee</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?php echo (int)$emp['employee_id']; ?>">
                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?> (<?php echo htmlspecialchars($emp['employee_code']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Type *</label>
                <select name="type" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="">Select Type</option>
                    <optgroup label="Statutory Deductions">
                        <option value="SSS">SSS</option>
                        <option value="PhilHealth">PhilHealth</option>
                        <option value="Pag-IBIG">Pag-IBIG</option>
                        <option value="Tax">Tax</option>
                    </optgroup>
                    <optgroup label="Voluntary Deductions">
                        <option value="Loan">Loan</option>
                        <option value="HMO">HMO</option>
                    </optgroup>
                    <optgroup label="Operational Deductions">
                        <option value="Absence">Absence</option>
                        <option value="Late">Late</option>
                    </optgroup>
                    <optgroup label="Other">
                        <option value="Other">Other</option>
                    </optgroup>
                </select>
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Amount *</label>
                <input type="number" name="amount" placeholder="0.00" step="0.01" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Payroll Run</label>
                <select name="payroll_run_id" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;">
                    <option value="">Optional - Select Run</option>
                    <?php foreach ($payrollRuns as $run): ?>
                        <option value="<?php echo (int)$run['id']; ?>"><?php echo htmlspecialchars($run['period_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="grid-column: 1 / -1;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1f2937;">Description</label>
                <textarea name="description" placeholder="Add notes..." style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; min-height: 80px;"></textarea>
            </div>

            <div style="grid-column: 1 / -1; display: flex; gap: 1rem;">
                <button type="submit" style="flex: 1; padding: 0.75rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Add Deduction</button>
                <button type="button" onclick="toggleAddForm()" style="flex: 1; padding: 0.75rem; background: #9ca3af; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Deductions Table -->
    <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #3b82f6; padding-bottom: 0.5rem;">
            Deduction Records
        </h2>

        <?php if (empty($deductions)): ?>
            <p style="color: #9ca3af; text-align: center; padding: 2rem;">No deduction records yet. Add one to get started.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;" id="deductionsTable">
                    <thead style="background: #f3f4f6;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Employee</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Type</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Category</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Amount</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Description</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Payroll Run</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deductions as $ded): 
                            $category = in_array($ded['type'], ['SSS', 'PhilHealth', 'Pag-IBIG', 'Tax']) ? 'Statutory' : 
                                       (in_array($ded['type'], ['Loan', 'HMO']) ? 'Voluntary' : 
                                       (in_array($ded['type'], ['Absence', 'Late']) ? 'Operational' : 'Other'));
                            
                            $colors = in_array($category, ['Statutory']) ? ['bg' => '#dbeafe', 'text' => '#1e40af'] :
                                     (in_array($category, ['Voluntary']) ? ['bg' => '#fef3c7', 'text' => '#92400e'] :
                                     (in_array($category, ['Operational']) ? ['bg' => '#fee2e2', 'text' => '#991b1b'] :
                                     ['bg' => '#f3f4f6', 'text' => '#374151']));
                            $showRow = !$currentCategory || $category === $currentCategory;
                        ?>
                            <tr style="border-bottom: 1px solid #e5e7eb; display: <?php echo $showRow ? '' : 'none'; ?>;" class="deduction-row" data-category="<?php echo htmlspecialchars($category); ?>">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 500; color: #1f2937;"><?php echo htmlspecialchars($ded['first_name'] . ' ' . $ded['last_name']); ?></div>
                                    <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($ded['employee_code']); ?></div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: <?php echo $colors['bg']; ?>; color: <?php echo $colors['text']; ?>; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($ded['type']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;"><?php echo htmlspecialchars($category); ?></td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #ef4444;">
                                    ₱<?php echo number_format($ded['amount'], 2); ?>
                                </td>
                                <td style="padding: 1rem; color: #6b7280; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars(strlen($ded['description']) > 50 ? substr($ded['description'], 0, 50) . '...' : $ded['description']); ?>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo $ded['payroll_run_id'] ? htmlspecialchars($ded['period_name']) : '<span style="color: #d1d5db;">—</span>'; ?>
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
function toggleAddForm() {
    const form = document.getElementById('addFormDiv');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function filterByCategory(category) {
    const rows = document.querySelectorAll('.deduction-row');
    rows.forEach(row => {
        if (!category || row.dataset.category === category) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
