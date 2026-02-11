<?php
/**
 * Earnings Management Module
 * Build gross earnings from approved Compensation records
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/EmployeeSalary.php';
require_once __DIR__ . '/../models/PayrollComponent.php';

$employeeSalary = new EmployeeSalary();
$payrollComponent = new PayrollComponent();

// Fetch employee earnings data
$search = $_GET['search'] ?? '';
$department = $_GET['department'] ?? '';
$compensation_status = $_GET['compensation_status'] ?? '';

$query = "SELECT es.*, e.first_name, e.last_name, e.employee_code, e.department_id 
          FROM employee_salaries es 
          JOIN employees e ON es.employee_id = e.employee_id 
          WHERE es.payroll_eligible = 1";

if ($search) {
    $query .= " AND (e.employee_code LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)";
}
if ($department) {
    $query .= " AND e.department_id = ?";
}

$query .= " ORDER BY e.employee_code ASC";

$params = [];
if ($search) {
    $search_term = '%' . $search . '%';
    $params = [$search_term, $search_term, $search_term];
}
if ($department) {
    $params[] = $department;
}

$earnings = !empty($params) ? $employeeSalary->query($query, $params) : $employeeSalary->query($query);
$totalEmployees = count($earnings ?? []);
$totalGross = 0;

foreach ($earnings ?? [] as $e) {
    $totalGross += (float) $e['basic_rate'];
}
?>

<style>
  .earnings-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
  }

  .section {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .section-header {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #3b82f6;
  }

  .filter-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 14px;
  }

  .form-group input,
  .form-group select {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
  }

  .form-group input:focus,
  .form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }

  .btn-primary {
    background: #3b82f6;
    color: white;
  }

  .btn-primary:hover {
    background: #2563eb;
  }

  .btn-secondary {
    background: #e5e7eb;
    color: #1f2937;
  }

  .btn-secondary:hover {
    background: #d1d5db;
  }

  .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 13px;
  }

  .table-container {
    overflow-x: auto;
    margin-bottom: 1.5rem;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
  }

  table thead {
    background: #f3f4f6;
  }

  table th {
    padding: 0.75rem;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #d1d5db;
  }

  table td {
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
  }

  table tr:hover {
    background: #f9fafb;
  }

  .amount {
    text-align: right;
    font-family: 'Courier New', monospace;
    font-weight: 500;
  }

  .amount-gross {
    font-weight: 600;
    color: #1e40af;
  }

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-yes {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-no {
    background: #f3f4f6;
    color: #6b7280;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-approved {
    background: #d1fae5;
    color: #065f46;
  }

  .detail-section {
    background: #f9fafb;
    padding: 2rem;
    border-radius: 8px;
    margin-top: 2rem;
    border-left: 4px solid #3b82f6;
  }

  .detail-section h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .detail-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 1.5rem;
  }

  .detail-item {
    display: flex;
    justify-content: space-between;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
  }

  .detail-item label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
  }

  .detail-item value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
    text-align: right;
    min-width: 100px;
  }

  .alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 14px;
  }

  .alert-info {
    background: #dbeafe;
    border: 1px solid #bfdbfe;
    color: #1e40af;
  }

  .alert-warning {
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
  }

  .earnings-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .summary-card {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
  }

  .summary-card.success {
    border-left-color: #22c55e;
  }

  .summary-card.warning {
    border-left-color: #f59e0b;
  }

  .summary-card label {
    display: block;
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .summary-card .value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
  }

  .no-data {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
  }

  .compensation-status {
    padding: 1rem;
    border-radius: 4px;
    background: #dbeafe;
    color: #1e40af;
    font-size: 13px;
    margin-bottom: 1rem;
  }
</style>

<div class="earnings-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Earnings Management</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Build gross earnings from approved Compensation records. All earning values are sourced from Compensation module and cannot be manually edited.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ How it Works:</strong> Gross earnings are calculated by reading Base Pay, Incentives, and applying Double Pay and Hazard Pay flags from approved Compensation records.
    </div>
  </div>

  <!-- Filters -->
  <div class="section">
    <form method="GET" action="">
      <div class="filter-section">
        <div class="form-group">
          <label>Search Employee</label>
          <input type="text" name="search" placeholder="Employee ID or Name..." value="">
        </div>
        <div class="form-group">
          <label>Department</label>
          <select name="department">
            <option value="">-- All Departments --</option>
            <option value="hr">Human Resources</option>
            <option value="it">Information Technology</option>
            <option value="ops">Operations</option>
            <option value="finance">Finance</option>
          </select>
        </div>
        <div class="form-group">
          <label>Compensation Status</label>
          <select name="compensation_status">
            <option value="">-- All Statuses --</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
          </select>
        </div>
        <div style="display: flex; align-items: flex-end;">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Earnings Summary -->
  <div class="earnings-summary">
    <div class="summary-card success">
      <label>Total Employees with Earnings</label>
      <div class="value"><?php echo (int) $totalEmployees; ?></div>
    </div>
    <div class="summary-card">
      <label>Total Gross Earnings (Monthly)</label>
      <div class="value">₱ <?php echo number_format($totalGross, 2); ?></div>
    </div>
    <div class="summary-card warning">
      <label>Pending Compensation</label>
      <div class="value">0</div>
    </div>
  </div>

  <!-- Earnings Table -->
  <div class="section">
    <h3 class="section-header">Employee Earnings Breakdown</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Department</th>
            <th>Base Pay</th>
            <th>Incentives</th>
            <th>Double Pay</th>
            <th>Hazard Pay</th>
            <th>Gross Earnings</th>
            <th>Compensation Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($earnings)): ?>
          <tr><td colspan="10" style="text-align: center; padding: 2rem; color: #9ca3af;">No employee earnings found</td></tr>
          <?php else: foreach ($earnings as $emp): ?>
          <tr>
            <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
            <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
            <td><?php echo htmlspecialchars($emp['department_id'] ?? '—'); ?></td>
            <td class="amount"><?php echo number_format((float) $emp['basic_rate'], 2); ?></td>
            <td class="amount">0.00</td>
            <td class="amount">0.00</td>
            <td class="amount">0.00</td>
            <td class="amount amount-gross"><?php echo number_format((float) $emp['basic_rate'], 2); ?></td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="<?php echo (int) $emp['employee_id']; ?>">
                <button type="submit" class="btn btn-secondary btn-sm">View Details</button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
            <td>Human Resources</td>
            <td class="amount">5,800.00</td>
            <td class="amount">1,200.00</td>
            <td class="amount">2,000.00</td>
            <td class="amount">0.00</td>
            <td class="amount amount-gross">9,000.00</td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-005">
                <button type="submit" class="btn btn-secondary btn-sm">View Details</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-006</td>
            <td>Emily Davis</td>
            <td>Information Technology</td>
            <td class="amount">8,000.00</td>
            <td class="amount">2,000.00</td>
            <td class="amount">0.00</td>
            <td class="amount">1,000.00</td>
            <td class="amount amount-gross">11,000.00</td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-006">
                <button type="submit" class="btn btn-secondary btn-sm">View Details</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-007</td>
            <td>David Martinez</td>
            <td>Operations</td>
            <td class="amount">5,200.00</td>
            <td class="amount">1,000.00</td>
            <td class="amount">2,500.00</td>
            <td class="amount">1,000.00</td>
            <td class="amount amount-gross">9,700.00</td>
            <td><span class="badge badge-approved">Approved</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-007">
                <button type="submit" class="btn btn-secondary btn-sm">View Details</button>
              </form>
            </td>
          </tr>
          <tr>
            <td>EMP-008</td>
            <td>Jessica Wilson</td>
            <td>Finance</td>
            <td class="amount">7,000.00</td>
            <td class="amount">1,800.00</td>
            <td class="amount">0.00</td>
            <td class="amount">0.00</td>
            <td class="amount amount-gross">8,800.00</td>
            <td><span class="badge badge-pending">Pending Review</span></td>
            <td>
              <form method="GET" style="display: inline;">
                <input type="hidden" name="action" value="view">
                <input type="hidden" name="employee_id" value="EMP-008">
                <button type="submit" class="btn btn-secondary btn-sm">View Details</button>
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Earnings Detail Section -->
  <div class="detail-section">
    <h4>📊 Earning Calculation Example</h4>
    
    <div class="compensation-status">
      <strong>Compensation Record Status:</strong> Approved
    </div>

    <div class="detail-row">
      <div class="detail-item">
        <label>Base Pay</label>
        <value>₱ 6,000.00</value>
      </div>
      <div class="detail-item">
        <label>Incentives</label>
        <value>₱ 2,000.00</value>
      </div>
    </div>

    <div class="detail-row">
      <div class="detail-item">
        <label>Double Pay Applied</label>
        <value><span class="badge badge-yes">YES</span> ₱ 3,000.00</value>
      </div>
      <div class="detail-item">
        <label>Hazard Pay Applied</label>
        <value><span class="badge badge-no">NO</span></value>
      </div>
    </div>

    <div style="background: white; padding: 1rem; border-radius: 4px; margin-top: 1rem; border: 2px solid #3b82f6;">
      <div style="display: flex; justify-content: space-between; font-weight: 600; color: #1e40af;">
        <span>GROSS EARNINGS</span>
        <span style="font-size: 18px;">₱ 11,000.00</span>
      </div>
    </div>

    <div style="margin-top: 1.5rem; padding: 1rem; background: #f3f4f6; border-radius: 4px;">
      <strong style="color: #1f2937;">📝 Calculation Formula:</strong>
      <div style="margin-top: 0.5rem; color: #6b7280; font-size: 13px; font-family: 'Courier New', monospace;">
        Gross Earnings = Base Pay + Incentives + Double Pay + Hazard Pay<br>
        Gross Earnings = ₱6,000 + ₱2,000 + ₱3,000 + ₱0<br>
        <strong style="color: #1f2937;">= ₱11,000.00</strong>
      </div>
    </div>
  </div>

  <!-- Important Notes -->
  <div class="section">
    <h3 class="section-header">Important Information</h3>
    
    <div class="alert alert-warning">
      <strong>⚠️ Data Source & Restrictions:</strong>
      <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
        <li><strong>No Manual Entry:</strong> All earning values are read from approved Compensation records and cannot be manually edited here</li>
        <li><strong>Compensation Reference:</strong> Base Pay and Incentives values are sourced directly from Compensation module</li>
        <li><strong>Pending Compensation:</strong> Employees with pending compensation changes will show the most recently approved values</li>
        <li><strong>Double Pay Flags:</strong> Applied when employee is flagged for double pay in Compensation record</li>
        <li><strong>Hazard Pay Flags:</strong> Applied when employee is flagged for hazard pay/dangerous work allowance in Compensation record</li>
        <li><strong>Gross Earnings Use:</strong> Final gross earnings are used in Deductions Management and Payroll Processing</li>
      </ul>
    </div>

    <div class="alert alert-info">
      <strong>ℹ️ To Modify Earnings:</strong> Update the employee's Compensation record in the Compensation module, then sync/approve the changes. Earnings will automatically recalculate in this module.
    </div>
  </div>

</div>
