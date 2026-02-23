<?php
/**
 * Salary Planning - Modern Design
 * Pay Grades > Grade Levels > Salary Bands
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayGrade.php';
require_once __DIR__ . '/../models/GradeLevel.php';
require_once __DIR__ . '/../models/SalaryBand.php';
require_once __DIR__ . '/../../../config/currency.php';

$payGradeModel = new PayGrade();
$gradeLevelModel = new GradeLevel();
$salaryBandModel = new SalaryBand();

$payGrades = $payGradeModel->getAllWithBands(false);
$gradeLevels = $gradeLevelModel->getAllWithGrade(false);
$bands = $salaryBandModel->getAllWithDetails(false);

$handlerUrl = 'modules/compensation/salary_planning_handler.php';
$expandGrade = $_GET['expand_grade'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Salary Planning</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body { 
    background: #f5f5f5; 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #1f2937;
    font-size: 13px;
    line-height: 1.4;
}

.container { width: 100%; background: #fff; }
.header { padding: 16px 24px; border-bottom: 1px solid #e5e7eb; }
.title { font-size: 17px; font-weight: 600; color: #111827; }

.content { max-width: 1080px; }
.msg-bar { padding: 12px 24px; display: flex; gap: 8px; }
.msg { background: #d1fae5; border-left: 3px solid #10b981; color: #065f46; padding: 8px 12px; border-radius: 3px; font-size: 12px; flex: 1; }
.err { background: #fee2e2; border-left: 3px solid #ef4444; color: #991b1b; padding: 8px 12px; border-radius: 3px; font-size: 12px; flex: 1; }

.section { padding: 12px 24px; border-bottom: 1px solid #e5e7eb; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.section-title { font-size: 14px; font-weight: 600; color: #111827; }

.rules-notice { font-size: 11px; color: #6b7280; margin-bottom: 8px; padding: 6px 0; border-bottom: 1px solid #e5e7eb; }

.table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 8px; }
.table th { background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; height: 28px; }
.table td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; height: 30px; vertical-align: middle; }
.table tbody tr:hover { background: #f9fafb; }
.table .num { text-align: right; }
.table .code { font-family: 'Courier New', monospace; background: #f3f4f6; padding: 2px 4px; border-radius: 2px; font-size: 11px; }

.badge { font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: 600; text-transform: uppercase; display: inline-block; }
.badge-active { background: #d1fae5; color: #065f46; }
.badge-inactive { background: #f3f4f6; color: #6b7280; }

.btn { padding: 6px 12px; font-size: 12px; font-weight: 500; border: 1px solid #d1d5db; background: #fff; color: #374151; border-radius: 4px; cursor: pointer; height: 28px; display: inline-flex; align-items: center; }
.btn:hover { border-color: #9ca3af; background: #f9fafb; }
.btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.btn-primary:hover { background: #1c3aa0; }
.btn-sm { padding: 4px 8px; font-size: 11px; height: 24px; }
.btn-icon { width: 24px; padding: 0; justify-content: center; }

.empty-state { padding: 24px 8px; color: #9ca3af; font-size: 12px; }

.add-form { display: none; background: #f9fafb; padding: 12px; border: 1px solid #e5e7eb; border-radius: 4px; margin-bottom: 8px; }
.add-form.visible { display: block; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.form-row.full { grid-template-columns: 1fr; }
.form-row.three { grid-template-columns: 1fr 1fr 1fr; }
.form-group { display: flex; flex-direction: column; gap: 3px; }
.form-label { font-size: 11px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
.required { color: #ef4444; }

.form-input, .form-select { padding: 6px 8px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 12px; font-family: inherit; color: #1f2937; height: 30px; }
.form-input:focus, .form-select:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.1); }

.form-actions { display: flex; gap: 6px; margin-top: 8px; }

.inline-form { display: contents; }
.expand-content { display: none; }
.expand-content.visible { display: block; }

.expand-toggle { cursor: pointer; text-decoration: underline; color: #1e40af; font-size: 11px; padding: 0; background: none; border: none; }

.summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
.summary-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 12px; }
.summary-card-title { font-weight: 600; font-size: 12px; margin-bottom: 6px; display: flex; align-items: center; gap: 4px; }
.summary-check { color: #10b981; font-size: 14px; }
.summary-details { font-size: 11px; color: #6b7280; margin-top: 4px; }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .form-row.three { grid-template-columns: 1fr; }
    .section { padding: 12px 16px; }
}
</style>
</head>
<body>

<div class="container">
    
    <!-- Header -->
    <div class="header">
        <h1 class="title">Salary Planning</h1>
    </div>

    <!-- Messages -->
    <?php if (!empty($_GET['msg']) || !empty($_GET['err'])): ?>
    <div class="msg-bar">
        <?php if (!empty($_GET['msg'])): ?>
        <div class="msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['err'])): ?>
        <div class="err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="content">

        <!-- 1. PAY GRADES -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">1. Pay Grades</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-paygrade-form'); return false;">+ Add</button>
            </div>

            <?php if (empty($payGrades)): ?>
            <div class="empty-state">No pay grades yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Bands</th>
                        <th>Range</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payGrades as $pg): ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($pg['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($pg['name']); ?></td>
                        <td><?php echo (int) ($pg['band_count'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars($pg['range_summary'] ?? '—'); ?></td>
                        <td></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Add Form -->
            <div id="add-paygrade-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_pay_grade">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Code <span class="required">•</span></label><input type="text" name="code" required class="form-input" placeholder="STAFF_NURSE" maxlength="50"></div>
                        <div class="form-group"><label class="form-label">Name <span class="required">•</span></label><input type="text" name="name" required class="form-input" placeholder="Staff Nurse" maxlength="255"></div>
                    </div>
                    <div class="form-row full" style="display: none;" id="paygrade-description-row">
                        <div class="form-group"><label class="form-label">Description</label><input type="text" name="description" class="form-input" placeholder="Optional" maxlength="500"></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-paygrade-form'); return false;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. GRADE LEVELS -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">2. Grade Levels</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-gradelevel-form'); return false;">+ Add</button>
            </div>

            <?php if (empty($gradeLevels)): ?>
            <div class="empty-state">No grade levels yet.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pay Grade</th>
                        <th>Code</th>
                        <th>Level</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gradeLevels as $gl): ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($gl['pay_grade_code']); ?></span></td>
                        <td><span class="code"><?php echo htmlspecialchars($gl['code']); ?></span></td>
                        <td><?php echo htmlspecialchars($gl['name']); ?></td>
                        <td></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Add Form -->
            <div id="add-gradelevel-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_grade_level">
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Pay Grade <span class="required">•</span></label>
                            <select name="pay_grade_id" required class="form-select">
                                <option value="">Select Pay Grade</option>
                                <?php foreach ($payGrades as $pg): ?>
                                <option value="<?php echo (int) $pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">Code <span class="required">•</span></label><input type="text" name="code" required class="form-input" placeholder="SENIOR" maxlength="50"></div>
                        <div class="form-group"><label class="form-label">Name <span class="required">•</span></label><input type="text" name="name" required class="form-input" placeholder="Senior Nurse" maxlength="255"></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-gradelevel-form'); return false;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. SALARY BANDS (Most Important) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">3. Salary Bands</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-band-form'); return false;">+ Add</button>
            </div>
            
            <div class="rules-notice">Assignments outside band require approval. Min ≤ Mid ≤ Max.</div>

            <?php if (empty($bands)): ?>
            <div class="empty-state">No salary bands defined.</div>
            <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Pay Grade</th>
                        <th>Level</th>
                        <th class="num">Min</th>
                        <th class="num">Mid</th>
                        <th class="num">Max</th>
                        <th>Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bands as $b): ?>
                    <tr>
                        <td><span class="code"><?php echo htmlspecialchars($b['pay_grade_code']); ?></span></td>
                        <td><?php echo $b['grade_level_name'] ? htmlspecialchars($b['grade_level_name']) : '—'; ?></td>
                        <td class="num"><?php echo format_currency($b['min_salary'], 0); ?></td>
                        <td class="num"><?php echo format_currency($b['midpoint_salary'], 0); ?></td>
                        <td class="num"><?php echo format_currency($b['max_salary'], 0); ?></td>
                        <td><span class="badge badge-<?php echo $b['status'] === 'Active' ? 'active' : 'inactive'; ?>"><?php echo $b['status']; ?></span></td>
                        <td></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- Add Form -->
            <div id="add-band-form" class="add-form">
                <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
                    <input type="hidden" name="action" value="create_salary_band">
                    <div class="form-row full">
                        <div class="form-group"><label class="form-label">Pay Grade <span class="required">•</span></label>
                            <select name="pay_grade_id" required class="form-select">
                                <option value="">Select Pay Grade</option>
                                <?php foreach ($payGrades as $pg): ?>
                                <option value="<?php echo (int) $pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row full">
                        <div class="form-group"><label class="form-label">Grade Level (Optional)</label>
                            <select name="grade_level_id" class="form-select">
                                <option value="">Leave blank for all levels</option>
                                <?php foreach ($gradeLevels as $gl): ?>
                                <option value="<?php echo (int) $gl['id']; ?>"><?php echo htmlspecialchars($gl['pay_grade_name']) . ' - ' . htmlspecialchars($gl['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row three">
                        <div class="form-group"><label class="form-label">Min <span class="required">•</span></label><input type="number" name="min_salary" required class="form-input" placeholder="100000" step="1" min="0"></div>
                        <div class="form-group"><label class="form-label">Mid <span class="required">•</span></label><input type="number" name="midpoint_salary" required class="form-input" placeholder="150000" step="1" min="0"></div>
                        <div class="form-group"><label class="form-label">Max <span class="required">•</span></label><input type="number" name="max_salary" required class="form-input" placeholder="200000" step="1" min="0"></div>
                    </div>
                    <div class="form-actions" style="justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        <button type="button" class="btn btn-sm" onclick="toggleForm('add-band-form'); return false;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. VALIDATED SALARY STRUCTURE (Read-Only Summary) -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">4. Validated Structure</div>
            </div>

            <div class="summary-grid">
                <?php 
                $activeGrades = array_filter($payGrades, fn($p) => $p['status'] === 'Active');
                if (empty($activeGrades)): 
                ?>
                <div class="summary-card">
                    <div style="color: #9ca3af; font-size: 12px;">No pay grades configured yet.</div>
                </div>
                <?php else: ?>
                <?php foreach ($activeGrades as $pg): 
                    $gradeBands = array_filter($bands, fn($b) => $b['pay_grade_id'] === $pg['id'] && $b['status'] === 'Active');
                    $isValid = !empty($gradeBands);
                ?>
                <div class="summary-card">
                    <div class="summary-card-title">
                        <span class="summary-check"><?php echo $isValid ? '✓' : '○'; ?></span>
                        <?php echo htmlspecialchars($pg['name']); ?>
                    </div>
                    <div class="summary-details">
                        <?php if ($isValid): ?>
                            <?php echo count($gradeBands); ?> band(s) configured. Range: <?php echo htmlspecialchars($pg['range_summary']); ?>
                        <?php else: ?>
                            No bands configured.
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
function toggleForm(id) {
    document.getElementById(id).classList.toggle('visible');
}
</script>

</body>
</html>
