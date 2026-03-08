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

$handlerUrl = '/PUBLIC_HTML/modules/compensation/salary_planning_handler.php';
$expandGrade = $_GET['expand_grade'] ?? '';
$pageTitle = 'Salary Planning';
require __DIR__ . '/partials/header.php';

/* Messages */
?>
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

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div style="display:flex; gap:8px; align-items:center;">
            <input id="comp-search" class="form-input" placeholder="Search pay grades, bands, levels..." onkeypress="if(event.key==='Enter') performCompSearch();">
            <button class="btn btn-sm" onclick="performCompSearch();">Search</button>
        </div>
    </div>

        <!-- 1. PAY GRADES -->
        <div class="section">
            <div class="section-header">
                <div class="section-title">1. Pay Grades</div>
                <button class="btn btn-primary btn-sm" onclick="toggleForm('add-paygrade-form'); return false;">+ Add</button>
            </div>

            <div class="callout callout-info">
                <div class="callout-icon">ℹ</div>
                <div class="callout-text"><strong>Pay Grades</strong> group positions by salary level. Each grade can have multiple bands and levels. Required to set up salary structure.</div>
            </div>

            <?php if (empty($payGrades)): ?>
            <div class="empty-state">No pay grades yet.</div>
            <?php else: ?>
            <table class="table comp-table">
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
                        <td class="action-cell"><button class="btn btn-sm btn-outline">✎ Edit</button></td>
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

            <div class="callout callout-info">
                <div class="callout-icon">ℹ</div>
                <div class="callout-text"><strong>Grade Levels</strong> represent steps within a pay grade (e.g., Junior, Senior, Lead). Optional but recommended for career progression.</div>
            </div>

            <?php if (empty($gradeLevels)): ?>
            <div class="empty-state">No grade levels yet.</div>
            <?php else: ?>
            <table class="table comp-table">
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
                        <td class="action-cell"><button class="btn btn-sm btn-outline">✎ Edit</button></td>
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
            
            <div class="callout callout-warning">
                <div class="callout-icon">⚠</div>
                <div class="callout-text"><strong>Critical:</strong> Salary bands define compensation ranges. Min ≤ Mid ≤ Max must be maintained. Assignments outside bands require approval. This is the foundation of salary equity.</div>
            </div>

            <?php if (empty($bands)): ?>
            <div class="empty-state">No salary bands defined.</div>
            <?php else: ?>
            <table class="table comp-table">
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
                        <td class="action-cell"><button class="btn btn-sm btn-outline">✎ Edit</button></td>
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

            <div class="callout callout-success">
                <div class="callout-icon">✓</div>
                <div class="callout-text"><strong>Structure Status:</strong> All sections must be configured to enable employee compensation management. Use the checklist below.</div>
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
// Toggle form visibility
function toggleForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.classList.toggle('visible');
        if (form.classList.contains('visible')) {
            setTimeout(() => {
                form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        }
    }
}

// Compensation table enhancer (per-view)
;(function(){
    try {
        var table = document.querySelector('table.comp-table');
        if (!table) return;
        var parent = table.parentElement;
        if (!parent.classList.contains('list-card')) {
            var card = document.createElement('div'); card.className='list-card'; parent.insertBefore(card, table); card.appendChild(table); parent = card;
        }
        var toolbar = document.createElement('div'); toolbar.className='comp-toolbar';
        var search = document.createElement('input'); search.type='search'; search.className='comp-search'; search.placeholder='Search table...';
        var pageSize = document.createElement('select'); pageSize.className='comp-page-size'; [10,25,50,100].forEach(function(n){ var o=document.createElement('option'); o.value=n; o.textContent=n+' / page'; pageSize.appendChild(o); });
        var pagination = document.createElement('div'); pagination.className='comp-pagination';
        toolbar.appendChild(search); toolbar.appendChild(pageSize); toolbar.appendChild(pagination);
        parent.parentElement.insertBefore(toolbar, parent);
        var tbody = table.tBodies[0]; var rows = Array.prototype.slice.call(tbody.rows); var currentPage=1;
        function renderPagination(totalPages){ pagination.innerHTML=''; if(totalPages<=1) return; var prev=document.createElement('button'); prev.textContent='Prev'; prev.disabled=currentPage===1; prev.addEventListener('click', function(){ if(currentPage>1){ currentPage--; update(); } }); pagination.appendChild(prev); for(var i=1;i<=totalPages;i++){ (function(p){ var b=document.createElement('button'); b.textContent=p; if(p===currentPage) b.classList.add('active'); b.addEventListener('click', function(){ currentPage=p; update(); }); pagination.appendChild(b); })(i); } var next=document.createElement('button'); next.textContent='Next'; next.disabled=currentPage===totalPages; next.addEventListener('click', function(){ if(currentPage<totalPages){ currentPage++; update(); } }); pagination.appendChild(next); }
        function update(){ var q=(search.value||'').toLowerCase().trim(); var filtered = rows.filter(function(r){ return r.textContent.toLowerCase().indexOf(q)!==-1; }); var pageSizeVal = parseInt(pageSize.value,10)||10; var totalPages = Math.max(1, Math.ceil(filtered.length/pageSizeVal)); if(currentPage>totalPages) currentPage=totalPages; rows.forEach(function(r){ r.style.display='none'; }); var start=(currentPage-1)*pageSizeVal, end=start+pageSizeVal; filtered.slice(start,end).forEach(function(r){ r.style.display=''; }); renderPagination(totalPages); }
        search.addEventListener('input', function(){ currentPage=1; update(); }); pageSize.addEventListener('change', function(){ currentPage=1; update(); }); update();
    } catch (e){ console && console.error('comp table enhancer', e); }
})();
function performCompSearch(){
    var q = document.getElementById('comp-search')?.value.toLowerCase() || '';
    var rows = document.querySelectorAll('table.comp-table tbody tr');
    
    rows.forEach(row => {
        var text = row.textContent.toLowerCase();
        if (q === '' || text.indexOf(q) !== -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>
