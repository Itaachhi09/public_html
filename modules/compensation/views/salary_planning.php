<?php
/**
 * Salary Planning & Salary Structure View
 * Pay grades, grade levels, salary bands. PHP/HTML/CSS only; no JavaScript.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/PayGrade.php';
require_once __DIR__ . '/../models/GradeLevel.php';
require_once __DIR__ . '/../models/SalaryBand.php';

$db = (new Database())->connect();
$payGradeModel = new PayGrade();
$gradeLevelModel = new GradeLevel();
$salaryBandModel = new SalaryBand();

$payGrades = $payGradeModel->getAllWithBands(false);
$gradeLevels = $gradeLevelModel->getAllWithGrade(false);
$bands = $salaryBandModel->getAllWithDetails(false);

$handlerUrl = 'modules/compensation/salary_planning_handler.php';
?>
<div class="main-content salary-planning-content">
<style>
.salary-planning-content { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1f2937; }
.sp-header { margin-bottom: 1.5rem; }
.sp-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.25rem 0; }
.sp-subtitle { font-size: 0.875rem; color: #6b7280; margin: 0; }
.sp-rules { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; color: #92400e; }
.sp-rules strong { display: block; margin-bottom: 0.25rem; }
.sp-msg { background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.sp-err { background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 0.5rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.875rem; }
.sp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 900px) { .sp-grid { grid-template-columns: 1fr; } }
.sp-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.sp-card h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem 0; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
.sp-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.sp-table th, .sp-table td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #f3f4f6; }
.sp-table th { font-weight: 600; color: #374151; background: #f9fafb; }
.sp-table .sp-num { text-align: right; }
.sp-form label { display: block; font-size: 0.75rem; font-weight: 500; color: #374151; margin-bottom: 0.25rem; }
.sp-form input, .sp-form select, .sp-form textarea { width: 100%; padding: 0.375rem 0.5rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; margin-bottom: 0.5rem; box-sizing: border-box; }
.sp-form textarea { min-height: 60px; resize: vertical; }
.sp-form .sp-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.sp-btn { display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.8125rem; font-weight: 500; border-radius: 6px; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
.sp-btn-primary { background: #1e40af; color: #fff; border-color: #1e40af; }
.sp-btn-primary:hover { background: #1e3a8a; }
.sp-btn-outline { background: #fff; color: #374151; border-color: #d1d5db; }
.sp-btn-outline:hover { background: #f9fafb; }
.sp-btn-danger { background: #dc2626; color: #fff; border-color: #dc2626; }
.sp-btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.sp-mb { margin-bottom: 1rem; }
.sp-empty { color: #9ca3af; font-size: 0.875rem; padding: 1rem; text-align: center; }
</style>

<div class="sp-header">
  <h1 class="sp-title">Salary Planning &amp; Salary Structure</h1>
  <p class="sp-subtitle">Enforce structured pay planning: pay grades, grade levels, and salary bands. Assignments outside the band require approval; bands are enforced system-wide.</p>
</div>

<div class="sp-rules">
  <strong>Rules</strong>
  Assignments outside the band require approval. No random salary values. Bands are enforced system-wide.
</div>

<?php if (!empty($_GET['msg'])): ?>
<div class="sp-msg"><?php echo htmlspecialchars(urldecode($_GET['msg'])); ?></div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div class="sp-err"><?php echo htmlspecialchars(urldecode($_GET['err'])); ?></div>
<?php endif; ?>

<div class="sp-grid">
  <div class="sp-card">
    <h3>Pay Grades</h3>
    <?php if (empty($payGrades)): ?>
    <p class="sp-empty">No pay grades yet. Add ER Doctor and Staff Nurse below.</p>
    <?php else: ?>
    <table class="sp-table">
      <thead><tr><th>Code</th><th>Name</th><th>Bands</th><th>Range</th></tr></thead>
      <tbody>
      <?php foreach ($payGrades as $pg): ?>
        <tr>
          <td><?php echo htmlspecialchars($pg['code']); ?></td>
          <td><?php echo htmlspecialchars($pg['name']); ?></td>
          <td><?php echo (int)($pg['band_count'] ?? 0); ?></td>
          <td><?php echo htmlspecialchars($pg['range_summary'] ?? '—'); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <h3 style="margin-top: 1rem;">Add Pay Grade</h3>
    <form class="sp-form sp-mb" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
      <input type="hidden" name="action" value="create_pay_grade">
      <label>Code *</label>
      <input type="text" name="code" required placeholder="e.g. STAFF_NURSE">
      <label>Name *</label>
      <input type="text" name="name" required placeholder="e.g. Staff Nurse">
      <label>Description</label>
      <textarea name="description" placeholder="Optional"></textarea>
      <button type="submit" class="sp-btn sp-btn-primary">Create Pay Grade</button>
    </form>
  </div>

  <div class="sp-card">
    <h3>Grade Levels</h3>
    <?php if (empty($gradeLevels)): ?>
    <p class="sp-empty">No grade levels. Add Senior (doctors) and Mid (nurses) below.</p>
    <?php else: ?>
    <table class="sp-table">
      <thead><tr><th>Pay Grade</th><th>Code</th><th>Name</th></tr></thead>
      <tbody>
      <?php foreach ($gradeLevels as $gl): ?>
        <tr>
          <td><?php echo htmlspecialchars($gl['pay_grade_name'] ?? $gl['pay_grade_code']); ?></td>
          <td><?php echo htmlspecialchars($gl['code']); ?></td>
          <td><?php echo htmlspecialchars($gl['name']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <h3 style="margin-top: 1rem;">Add Grade Level</h3>
    <form class="sp-form sp-mb" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
      <input type="hidden" name="action" value="create_grade_level">
      <label>Pay Grade *</label>
      <select name="pay_grade_id" required>
        <option value="">Select pay grade</option>
        <?php foreach ($payGradeModel->getAllWithBands(false) as $pg): ?>
        <option value="<?php echo (int)$pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
        <?php endforeach; ?>
      </select>
      <div class="sp-row">
        <div><label>Code *</label><input type="text" name="code" required placeholder="e.g. SENIOR"></div>
        <div><label>Name *</label><input type="text" name="name" required placeholder="e.g. Senior"></div>
      </div>
      <label>Sort order</label>
      <input type="number" name="sort_order" value="0" min="0">
      <label>Description</label>
      <textarea name="description"></textarea>
      <button type="submit" class="sp-btn sp-btn-primary">Create Grade Level</button>
    </form>
  </div>
</div>

<div class="sp-card" style="margin-top: 1rem;">
  <h3>Salary Bands (Min / Midpoint / Max)</h3>
  <?php if (empty($bands)): ?>
  <p class="sp-empty">No salary bands. Define bands per pay grade (e.g. ER Doctor 6000 / 7500 / 9000, Staff Nurse 2500 / 3000 / 3500).</p>
  <?php else: ?>
  <table class="sp-table">
    <thead>
      <tr>
        <th>Pay Grade</th>
        <th>Grade Level</th>
        <th class="sp-num">Min</th>
        <th class="sp-num">Midpoint</th>
        <th class="sp-num">Max</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($bands as $b): ?>
      <tr>
        <td><?php echo htmlspecialchars($b['pay_grade_name']); ?></td>
        <td><?php echo htmlspecialchars($b['grade_level_name'] ?? '—'); ?></td>
        <td class="sp-num"><?php echo number_format((float)$b['min_salary'], 2); ?></td>
        <td class="sp-num"><?php echo number_format((float)$b['midpoint_salary'], 2); ?></td>
        <td class="sp-num"><?php echo number_format((float)$b['max_salary'], 2); ?></td>
        <td>
          <form method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>" style="display:inline;">
            <input type="hidden" name="action" value="delete_band">
            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
            <button type="submit" class="sp-btn sp-btn-outline sp-btn-sm">Remove</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
  <h3 style="margin-top: 1rem;">Add Salary Band</h3>
  <form class="sp-form sp-mb" method="post" action="<?php echo htmlspecialchars($handlerUrl); ?>">
    <input type="hidden" name="action" value="create_band">
    <label>Pay Grade *</label>
    <select name="pay_grade_id" required>
      <option value="">Select pay grade</option>
      <?php foreach ($payGradeModel->getAllWithBands(false) as $pg): ?>
      <option value="<?php echo (int)$pg['id']; ?>"><?php echo htmlspecialchars($pg['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <label>Grade Level (optional)</label>
    <select name="grade_level_id">
      <option value="">— None —</option>
      <?php foreach ($gradeLevelModel->getAllWithGrade(false) as $gl): ?>
      <option value="<?php echo (int)$gl['id']; ?>"><?php echo htmlspecialchars($gl['pay_grade_name'] . ' – ' . $gl['name']); ?></option>
      <?php endforeach; ?>
    </select>
    <div class="sp-row">
      <div><label>Min salary *</label><input type="number" name="min_salary" step="0.01" min="0" required placeholder="6000"></div>
      <div><label>Midpoint *</label><input type="number" name="midpoint_salary" step="0.01" min="0" required placeholder="7500"></div>
      <div><label>Max salary *</label><input type="number" name="max_salary" step="0.01" min="0" required placeholder="9000"></div>
    </div>
    <button type="submit" class="sp-btn sp-btn-primary">Create Band</button>
  </form>
</div>

<div class="sp-card" style="margin-top: 1rem;">
  <h3>Validated Salary Structure</h3>
  <p class="sp-subtitle" style="margin: 0;">All roles use the bands above. Assignments outside the band require approval; no random salary values.</p>
</div>
</div>
