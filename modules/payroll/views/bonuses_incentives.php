<?php
/**
 * Bonuses & Incentives - Manage employee bonuses and incentive payments
 */

if (!defined('SYSTEM_INIT')) {
    http_response_code(403);
    die('No direct access allowed');
}

$appRoot = dirname(dirname(dirname(__DIR__)));
require_once $appRoot . '/config/BaseConfig.php';
require_once $appRoot . '/config/Database.php';
require_once __DIR__ . '/../models/BonusIncentive.php';

$bonusModel = new BonusIncentive();
$db = new Database();

$bonuses = $bonusModel->getAll();
$employees = $db->query("SELECT employee_id, first_name, last_name, employee_code FROM employees WHERE employment_status = 'Active' ORDER BY first_name, last_name");
$payrollRuns = $db->query("SELECT id, period_name FROM payroll_runs ORDER BY start_date DESC");

$bonusTypes = [
    'Bonus' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
    'Incentive' => ['bg' => '#fef3c7', 'text' => '#92400e'],
    'Performance Reward' => ['bg' => '#e0e7ff', 'text' => '#3730a3'],
    'Attendance Bonus' => ['bg' => '#d1fae5', 'text' => '#065f46'],
    '13th Month Pay' => ['bg' => '#f3e8ff', 'text' => '#581c87'],
    'Mid-Year Bonus' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
    'Project Completion' => ['bg' => '#ccfbf1', 'text' => '#134e4a'],
    'Other' => ['bg' => '#f3f4f6', 'text' => '#374151']
];
?>

<div style="max-width: 1400px; margin: 0 auto; padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 2rem; margin: 0;">Bonuses & Incentives</h1>
            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Manage bonus and incentive payments</p>
        </div>
        <button onclick="toggleAddForm()" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
            + Add Bonus
        </button>
    </div>

    <!-- Summary Cards -->
    <?php if (!empty($bonuses)): 
        $totalRecords = count($bonuses);
        $totalAmount = array_sum(array_column($bonuses, 'amount'));
        $beneficiaries = count(array_unique(array_column($bonuses, 'employee_id')));
    ?>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Records</div>
            <div style="font-size: 2rem; font-weight: 700; color: #1f2937;"><?php echo $totalRecords; ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Amount</div>
            <div style="font-size: 2rem; font-weight: 700; color: #059669;">₱<?php echo number_format($totalAmount, 2); ?></div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Beneficiaries</div>
            <div style="font-size: 2rem; font-weight: 700; color: #3b82f6;"><?php echo $beneficiaries; ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add Bonus Form -->
    <div id="addFormDiv" style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem; display: none;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #10b981; padding-bottom: 0.5rem;">Add Bonus/Incentive</h2>
        <form id="addBonusForm" method="POST" action="modules/payroll/api.php" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <input type="hidden" name="action" value="createBonusIncentive">
            
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
                    <?php foreach (array_keys($bonusTypes) as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                    <?php endforeach; ?>
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
                <button type="submit" style="flex: 1; padding: 0.75rem; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Add Bonus</button>
                <button type="button" onclick="toggleAddForm()" style="flex: 1; padding: 0.75rem; background: #9ca3af; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">Cancel</button>
            </div>
        </form>
    </div>

    <!-- Bonuses Table -->
    <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid #3b82f6; padding-bottom: 0.5rem;">
            Bonus & Incentive Records
        </h2>

        <?php if (empty($bonuses)): ?>
            <p style="color: #9ca3af; text-align: center; padding: 2rem;">No bonus records yet. Add one to get started.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f3f4f6;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Employee</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Type</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #d1d5db; font-weight: 600;">Amount</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Description</th>
                            <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #d1d5db; font-weight: 600;">Payroll Run</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #d1d5db; font-weight: 600;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bonuses as $bonus): 
                            $typeColors = $bonusTypes[$bonus['type']] ?? $bonusTypes['Other'];
                        ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 1rem;">
                                    <div style="font-weight: 500; color: #1f2937;"><?php echo htmlspecialchars($bonus['first_name'] . ' ' . $bonus['last_name']); ?></div>
                                    <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($bonus['employee_code']); ?></div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background: <?php echo $typeColors['bg']; ?>; color: <?php echo $typeColors['text']; ?>; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($bonus['type']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600; color: #059669;">
                                    ₱<?php echo number_format($bonus['amount'], 2); ?>
                                </td>
                                <td style="padding: 1rem; color: #6b7280; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars(strlen($bonus['description']) > 50 ? substr($bonus['description'], 0, 50) . '...' : $bonus['description']); ?>
                                </td>
                                <td style="padding: 1rem; color: #6b7280;">
                                    <?php echo $bonus['payroll_run_id'] ? htmlspecialchars($bonus['period_name']) : '<span style="color: #d1d5db;">—</span>'; ?>
                                </td>
                                <td style="padding: 1rem; text-align: center; color: #6b7280;">
                                    <?php echo date('M d, Y', strtotime($bonus['created_at'])); ?>
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

// Handle form submission via AJAX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addBonusForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('modules/payroll/api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Bonus/Incentive added successfully');
                    form.reset();
                    const addFormDiv = document.getElementById('addFormDiv');
                    if (addFormDiv) addFormDiv.style.display = 'none';
                    
                    // Reload the bonuses view content without leaving the module
                    reloadBonusesView();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            });
        });
    }
});

function reloadBonusesView() {
    // Fetch the bonuses view fresh and reload just the content
    const viewUrl = `dashboard.php?module=payroll&view=bonuses_incentives`;
    
    const loader = document.getElementById('content-loader');
    const contentArea = document.getElementById('content-area');
    
    if (loader) loader.style.display = 'block';
    
    fetch(viewUrl)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.text();
        })
        .then(html => {
            // Extract scripts BEFORE parsing
            const scriptRegex = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
            const scripts = [];
            let match;
            while ((match = scriptRegex.exec(html)) !== null) {
                const scriptTag = match[0];
                const scriptContent = scriptTag.replace(/<script[^>]*>/i, '').replace(/<\/script>/i, '');
                scripts.push(scriptContent);
            }
            
            // Remove scripts from HTML
            html = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            
            // Update content area
            if (contentArea) {
                contentArea.innerHTML = html;
                
                // Execute scripts
                scripts.forEach(script => {
                    try {
                        eval(script);
                    } catch (e) {
                        console.error('Script execution error:', e);
                    }
                });
            }
            
            if (loader) loader.style.display = 'none';
        })
        .catch(error => {
            console.error('Error loading bonuses:', error);
            if (loader) loader.style.display = 'none';
        });
}
</script>
