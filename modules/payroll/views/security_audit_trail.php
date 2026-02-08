<?php
/**
 * Security and Audit Trail Module
 * Prevent fraud and errors through comprehensive logging, access control, and audit trails
 */
?>

<style>
  .security-container {
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

  .form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
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

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1rem;
  }

  .form-row.full {
    grid-template-columns: 1fr;
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

  .btn-group {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
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

  .alert-success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
  }

  .alert-warning {
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
  }

  .alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
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

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-locked {
    background: #fee2e2;
    color: #991b1b;
  }

  .badge-unlock {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-immutable {
    background: #d1fae5;
    color: #065f46;
  }

  .budget-active {
    background: #dbeafe;
    color: #1e40af;
  }

  .summary-cards {
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

  .summary-card.danger {
    border-left-color: #ef4444;
  }

  .summary-card.success {
    border-left-color: #22c55e;
  }

  .role-matrix {
    background: #f9fafb;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
  }

  .role-matrix-header {
    display: grid;
    grid-template-columns: 200px repeat(5, 1fr);
    background: #f3f4f6;
    border-bottom: 2px solid #d1d5db;
    font-weight: 600;
    font-size: 12px;
    color: #374151;
  }

  .role-matrix-row {
    display: grid;
    grid-template-columns: 200px repeat(5, 1fr);
    border-bottom: 1px solid #e5e7eb;
  }

  .role-matrix-row:last-child {
    border-bottom: none;
  }

  .role-matrix-header > div,
  .role-matrix-row > div {
    padding: 1rem;
    text-align: center;
  }

  .role-matrix-row > div:first-child {
    text-align: left;
    font-weight: 500;
    color: #1f2937;
  }

  .permission-check {
    color: #22c55e;
    font-weight: 700;
  }

  .permission-cross {
    color: #ef4444;
  }

  .log-entry {
    display: block;
    padding: 0.75rem;
    border-left: 4px solid #3b82f6;
    background: #f9fafb;
    margin-bottom: 0.75rem;
    border-radius: 4px;
  }

  .log-entry.warning {
    border-left-color: #f59e0b;
  }

  .log-entry.danger {
    border-left-color: #ef4444;
  }

  .log-entry.success {
    border-left-color: #22c55e;
  }

  .log-time {
    display: block;
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0.25rem;
  }

  .log-action {
    display: block;
    font-size: 13px;
    color: #1f2937;
    font-weight: 500;
  }

  .log-detail {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 0.25rem;
  }

  .approval-chain {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .approval-step {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    position: relative;
  }

  .approval-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 24px;
    top: 50px;
    width: 2px;
    height: 30px;
    background: #d1d5db;
  }

  .approval-step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
    z-index: 1;
  }

  .approval-step-icon.completed {
    background: #22c55e;
  }

  .approval-step-icon.pending {
    background: #f59e0b;
  }

  .approval-step-icon.rejected {
    background: #ef4444;
  }

  .approval-step-content {
    margin-left: 1.5rem;
    flex-grow: 1;
  }

  .approval-step-title {
    font-weight: 600;
    color: #1f2937;
    font-size: 13px;
    margin-bottom: 0.25rem;
  }

  .approval-step-detail {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 0.5rem;
  }

  .approval-step-time {
    font-size: 11px;
    color: #9ca3af;
  }

  .locked-period {
    background: #fee2e2;
    border: 2px solid #fecaca;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
  }

  .locked-period h5 {
    margin: 0 0 0.5rem 0;
    color: #991b1b;
    font-size: 13px;
    font-weight: 600;
  }

  .locked-period p {
    margin: 0.25rem 0;
    color: #7c2d12;
    font-size: 12px;
  }

  .unlocked-period {
    background: #fef3c7;
    border: 2px solid #fde68a;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
  }

  .unlocked-period h5 {
    margin: 0 0 0.5rem 0;
    color: #92400e;
    font-size: 13px;
    font-weight: 600;
  }

  .unlocked-period p {
    margin: 0.25rem 0;
    color: #78350f;
    font-size: 12px;
  }

  .tab-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
    overflow-x: auto;
  }

  .tab {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
  }

  .tab:hover {
    color: #374151;
  }

  .tab-content {
    display: none;
  }

  .tab-content.active {
    display: block;
  }

  @media print {
    .section {
      page-break-inside: avoid;
    }
  }
</style>

<div class="security-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Security & Audit Trail</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Comprehensive security controls, role-based access, and complete audit trails to prevent fraud and ensure data integrity. All actions are logged and approved payroll records are immutable.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #dbeafe; border-radius: 4px; color: #1e40af; font-size: 13px;">
      <strong>ℹ️ Features:</strong> Role-based access control, action logging, approval chains, locked payroll periods, user access tracking, data change history, and fraud prevention.
    </div>
  </div>

  <!-- Security Dashboard -->
  <div class="section">
    <h3 class="section-header">🛡️ Security Dashboard</h3>

    <div class="summary-cards">
      <div class="summary-card">
        <label>Active Users</label>
        <div class="value">12</div>
      </div>
      <div class="summary-card success">
        <label>Locked Payroll Periods</label>
        <div class="value">6</div>
      </div>
      <div class="summary-card">
        <label>Actions Logged (This Month)</label>
        <div class="value">284</div>
      </div>
      <div class="summary-card danger">
        <label>Security Alerts</label>
        <div class="value">0</div>
      </div>
    </div>

    <div class="alert alert-success">
      ✓ System Status: <strong>Normal</strong> | Last audit: February 8, 2026 | No anomalies detected
    </div>
  </div>

  <!-- Role-Based Access Control -->
  <div class="section">
    <h3 class="section-header">👥 Role-Based Access Control Matrix</h3>

    <p style="color: #6b7280; font-size: 13px; margin-bottom: 1.5rem;">Payroll system roles and their permissions. Access is strictly role-based to prevent unauthorized actions.</p>

    <div class="role-matrix">
      <div class="role-matrix-header">
        <div></div>
        <div>View Payroll</div>
        <div>Create Payroll</div>
        <div>Approve Payroll</div>
        <div>Process Bank Files</div>
        <div>View Audit Logs</div>
      </div>
      <div class="role-matrix-row">
        <div>HR Officer (4 users)</div>
        <div class="permission-check">✓</div>
        <div class="permission-check">✓</div>
        <div class="permission-cross">✗</div>
        <div class="permission-cross">✗</div>
        <div class="permission-check">✓</div>
      </div>
      <div class="role-matrix-row">
        <div>HR Manager (2 users)</div>
        <div class="permission-check">✓</div>
        <div class="permission-check">✓</div>
        <div class="permission-check">✓ (Step 1)</div>
        <div class="permission-cross">✗</div>
        <div class="permission-check">✓</div>
      </div>
      <div class="role-matrix-row">
        <div>Finance Officer (3 users)</div>
        <div class="permission-check">✓</div>
        <div class="permission-cross">✗</div>
        <div class="permission-check">✓ (Step 2)</div>
        <div class="permission-check">✓</div>
        <div class="permission-check">✓</div>
      </div>
      <div class="role-matrix-row">
        <div>Finance Manager (2 users)</div>
        <div class="permission-check">✓</div>
        <div class="permission-cross">✗</div>
        <div class="permission-check">✓ (Step 3)</div>
        <div class="permission-check">✓</div>
        <div class="permission-check">✓</div>
      </div>
      <div class="role-matrix-row">
        <div>CFO (1 user)</div>
        <div class="permission-check">✓</div>
        <div class="permission-cross">✗</div>
        <div class="permission-check">✓ (Final)</div>
        <div class="permission-check">✓</div>
        <div class="permission-check">✓</div>
      </div>
      <div class="role-matrix-row">
        <div>Employee (Self-Service)</div>
        <div class="permission-check">✓ (Own)</div>
        <div class="permission-cross">✗</div>
        <div class="permission-cross">✗</div>
        <div class="permission-cross">✗</div>
        <div class="permission-cross">✗</div>
      </div>
    </div>

    <div style="margin-top: 1rem; padding: 1rem; background: #f9fafb; border-radius: 4px; border-left: 4px solid #3b82f6;">
      <p style="margin: 0; font-size: 12px; color: #6b7280;"><strong>Note:</strong> Users can only access payroll data for employees in their department. CFO has organization-wide access. All role changes require IT Security approval.</p>
    </div>
  </div>

  <!-- Locked Payroll Periods -->
  <div class="section">
    <h3 class="section-header">🔒 Locked Payroll Periods</h3>

    <p style="color: #6b7280; font-size: 13px; margin-bottom: 1.5rem;">Approved payroll records are automatically locked and immutable. Modifications require director approval and create new adjustment records.</p>

    <div class="locked-period">
      <h5>🔴 LOCKED: January 2026 Period 2 (Jan 16-31)</h5>
      <p><strong>Locked Since:</strong> January 31, 2026 03:45 PM by Maria Garcia (Finance Manager)</p>
      <p><strong>Final Approval:</strong> February 1, 2026 09:15 AM by CEO</p>
      <p><strong>Bank Transmission:</strong> February 1, 2026 10:30 AM (Confirmed)</p>
      <p><strong>Records:</strong> 8 employees | ₱57,890.00 total net payable</p>
      <p><strong>Status:</strong> IMMUTABLE - No changes permitted. Contact Finance Director for exceptions.</p>
    </div>

    <div class="locked-period">
      <h5>🔴 LOCKED: January 2026 Period 1 (Jan 1-15)</h5>
      <p><strong>Locked Since:</strong> January 17, 2026 02:30 PM by Maria Garcia (Finance Manager)</p>
      <p><strong>Final Approval:</strong> January 17, 2026 02:30 PM by Finance Manager</p>
      <p><strong>Bank Transmission:</strong> January 24, 2026 11:15 AM (Confirmed)</p>
      <p><strong>Records:</strong> 8 employees | ₱55,200.00 total net payable</p>
      <p><strong>Status:</strong> IMMUTABLE - No changes permitted. Contact Finance Director for exceptions.</p>
    </div>

    <div class="unlocked-period">
      <h5>🟡 DRAFT: February 2026 Period 1 (Feb 1-15)</h5>
      <p><strong>Status:</strong> In Progress - Processing. Awaiting HR Manager approval.</p>
      <p><strong>Created:</strong> February 5, 2026 10:00 AM by Juan dela Cruz (Payroll Officer)</p>
      <p><strong>Current Record:</strong> 8 employees | ₱58,355.00 projected net payable</p>
      <p><strong>Last Modified:</strong> February 8, 2026 09:30 AM (Payroll Preview Updated)</p>
      <p><strong>Modifications Allowed:</strong> Yes - Changes tracked in audit log until approval</p>
    </div>

    <div style="margin-top: 1.5rem; padding: 1rem; background: #f9fafb; border-radius: 4px; border-left: 4px solid #3b82f6;">
      <h5 style="margin: 0 0 0.5rem 0; color: #1f2937; font-size: 13px; font-weight: 600;">Immutability Policy</h5>
      <p style="margin: 0; font-size: 12px; color: #6b7280;">Once a payroll is approved BY ALL THREE REQUIRED SIGNATORIES (HR Manager + Finance Manager + Finance Director), the records become permanently locked. No modifications are possible without creating a formal adjustment record in the Payroll Adjustments module.</p>
    </div>
  </div>

  <!-- Action Audit Log -->
  <div class="section">
    <h3 class="section-header">📋 Action Audit Log - February 2026</h3>

    <div class="tab-container">
      <button class="tab active" onclick="switchTab(event, 'all-logs')">All Actions</button>
      <button class="tab" onclick="switchTab(event, 'modifications')">Data Changes</button>
      <button class="tab" onclick="switchTab(event, 'approvals')">Approvals</button>
      <button class="tab" onclick="switchTab(event, 'access-logs')">Access Logs</button>
    </div>

    <!-- All Actions Tab -->
    <div id="all-logs" class="tab-content active">
      <div class="log-entry success">
        <span class="log-time">February 8, 2026 10:45 AM</span>
        <span class="log-action">✓ Payroll Period Locked: Jan-2026-Period-2</span>
        <span class="log-detail">User: Maria Garcia (Finance Manager) | Action: LOCK_PAYROLL | Record Count: 8</span>
      </div>
      <div class="log-entry">
        <span class="log-time">February 8, 2026 10:30 AM</span>
        <span class="log-action">Payroll Preview Generated</span>
        <span class="log-detail">User: Juan dela Cruz (Payroll Officer) | Period: Feb-2026-Period-1 | Employees: 8 | Total: ₱58,355.00</span>
      </div>
      <div class="log-entry warning">
        <span class="log-time">February 8, 2026 10:15 AM</span>
        <span class="log-action">Employee Payroll Profile Updated</span>
        <span class="log-detail">User: Maria Santos (HR Officer) | Employee: EMP-006 (Emily Davis) | Field: Bank Account | Old: 1111222233 | New: 1111222234</span>
      </div>
      <div class="log-entry">
        <span class="log-time">February 8, 2026 10:00 AM</span>
        <span class="log-action">Setup Configuration Viewed</span>
        <span class="log-detail">User: Juan dela Cruz (Payroll Officer) | Module: Payroll | Action: VIEW</span>
      </div>
      <div class="log-entry success">
        <span class="log-time">February 7, 2026 04:30 PM</span>
        <span class="log-action">✓ Bank File Successfully Transmitted</span>
        <span class="log-detail">User: Maria Garcia (Finance Manager) | Batch: BATCH-2026-01-02 | Bank: Metrobank | Records: 8 | Amount: ₱54,230.00</span>
      </div>
      <div class="log-entry success">
        <span class="log-time">February 6, 2026 03:15 PM</span>
        <span class="log-action">✓ Adjustment Request Approved (Final Pay)</span>
        <span class="log-detail">User: Finance Director | Employee: Sarah Williams (EMP-004) | Amount: ₱12,500.00 | Status: APPROVED</span>
      </div>
      <div class="log-entry">
        <span class="log-time">February 5, 2026 02:00 PM</span>
        <span class="log-action">Adjustment Request Submitted</span>
        <span class="log-detail">User: Juan dela Cruz (Payroll Officer) | Type: Final Pay | Employee: Sarah Williams | Amount: ₱12,500.00</span>
      </div>
      <div class="log-entry warning">
        <span class="log-time">February 3, 2026 11:30 AM</span>
        <span class="log-action">13th Month Pay Submitted for Approval</span>
        <span class="log-detail">User: Maria Garcia (Finance Manager) | Period: Dec-2025 | Employees: 8 | Total Amount: ₱88,000.00 | Status: PENDING</span>
      </div>
    </div>

    <!-- Data Changes Tab -->
    <div id="modifications" class="tab-content">
      <div class="log-entry warning">
        <span class="log-time">February 8, 2026 10:15 AM</span>
        <span class="log-action">⚠ Employee Payroll Profile Modified</span>
        <span class="log-detail">User: Maria Santos (HR Officer) | Employee: Emily Davis (EMP-006) | Change: Bank Account Number | Old Value: 1111222233 | New Value: 1111222234</span>
      </div>
      <div class="log-entry warning">
        <span class="log-time">February 1, 2026 09:00 AM</span>
        <span class="log-action">⚠ Statutory Table Updated</span>
        <span class="log-detail">User: Maria Garcia (Finance Manager) | Table: BIR Withholding Tax | Version: 2.1 | Effective: Jan 1, 2026 | Change: Updated bracket thresholds</span>
      </div>
      <div class="log-entry warning">
        <span class="log-time">January 28, 2026 02:30 PM</span>
        <span class="log-action">⚠ Salary Component Added</span>
        <span class="log-detail">User: Maria Garcia (Finance Manager) | Component: Hazard Allowance | Type: Earning | Rate: ₱500.00/month | Effective: Feb 1, 2026</span>
      </div>
    </div>

    <!-- Approvals Tab -->
    <div id="approvals" class="tab-content">
      <div class="log-entry success">
        <span class="log-time">February 6, 2026 03:15 PM</span>
        <span class="log-action">✓ Final Pay Approved (Final Step)</span>
        <span class="log-detail">User: Finance Director | Adjustment: ADJ-001 (Sarah Williams) | Amount: ₱12,500.00 | Status: APPROVED | Reason: "Complies with company separation policy"</span>
      </div>
      <div class="log-entry success">
        <span class="log-time">February 6, 2026 10:00 AM</span>
        <span class="log-action">✓ Final Pay Approved (Step 2)</span>
        <span class="log-detail">User: Finance Manager | Adjustment: ADJ-001 (Sarah Williams) | Amount: ₱12,500.00 | Status: APPROVED | Checkmarks: All compliance items verified</span>
      </div>
      <div class="log-entry">
        <span class="log-time">February 5, 2026 04:45 PM</span>
        <span class="log-action">Final Pay Awaiting Approval (Step 1)</span>
        <span class="log-detail">Submitted to: HR Manager | Adjustment: ADJ-001 | Employee: Sarah Williams (EMP-004) | Amount: ₱12,500.00</span>
      </div>
    </div>

    <!-- Access Logs Tab -->
    <div id="access-logs" class="tab-content">
      <div class="log-entry">
        <span class="log-time">February 8, 2026 10:45 AM</span>
        <span class="log-action">User Login</span>
        <span class="log-detail">User: Maria Garcia (Finance Manager) | IP: 192.168.1.50 | Session: SESS-2026-0208-001 | Status: SUCCESS</span>
      </div>
      <div class="log-entry">
        <span class="log-time">February 8, 2026 10:30 AM</span>
        <span class="log-action">Module Access: Payroll Processing & Approval</span>
        <span class="log-detail">User: Juan dela Cruz (Payroll Officer) | IP: 192.168.1.45 | Permission: GRANTED | Reason: Role-based access</span>
      </div>
      <div class="log-entry">
        <span class="log-time">February 8, 2026 10:15 AM</span>
        <span class="log-action">Module Access: Employee Payroll Profile</span>
        <span class="log-detail">User: Maria Santos (HR Officer) | IP: 192.168.1.48 | Permission: GRANTED | Reason: Role-based access</span>
      </div>
      <div class="log-entry success">
        <span class="log-time">February 8, 2026 09:30 AM</span>
        <span class="log-action">✓ User Login</span>
        <span class="log-detail">User: Finance Director | IP: 192.168.1.120 | Session: SESS-2026-0208-000 | Status: SUCCESS</span>
      </div>
      <div class="log-entry danger">
        <span class="log-time">February 7, 2026 03:45 PM</span>
        <span class="log-action">❌ Access Denied: Unauthorized Module Access</span>
        <span class="log-detail">User: Emily Davis (Employee) | Attempted Module: Disbursement & Bank Files | Reason: Insufficient permissions | Incident logged for review</span>
      </div>
    </div>
  </div>

  <!-- Approval Chain History -->
  <div class="section">
    <h3 class="section-header">🔀 Approval Chain Example - Final Pay (Sarah Williams)</h3>

    <div class="approval-chain">
      <div class="approval-step">
        <div class="approval-step-icon completed">1</div>
        <div class="approval-step-content">
          <div class="approval-step-title">Submitted for HR Approval</div>
          <div class="approval-step-detail">Juan dela Cruz (Payroll Officer) submitted Final Pay adjustment request</div>
          <div class="approval-step-time">February 5, 2026 02:00 PM</div>
          <div class="approval-step-detail" style="margin-top: 0.5rem;"><strong>Amount:</strong> ₱12,500.00 (Base ₱9,000 + Accrued Leave ₱3,000 + Separation ₱500)</div>
        </div>
      </div>

      <div class="approval-step">
        <div class="approval-step-icon completed">2</div>
        <div class="approval-step-content">
          <div class="approval-step-title">HR Manager Approval ✓</div>
          <div class="approval-step-detail">Maria Santos (HR Manager) reviewed and approved the request</div>
          <div class="approval-step-time">February 6, 2026 10:00 AM</div>
          <div class="approval-step-detail" style="margin-top: 0.5rem;"><strong>Notes:</strong> "Resignation verified. All accrued leave reconciled. Separation pay per company policy."</div>
        </div>
      </div>

      <div class="approval-step">
        <div class="approval-step-icon completed">3</div>
        <div class="approval-step-content">
          <div class="approval-step-title">Finance Manager Approval ✓</div>
          <div class="approval-step-detail">Maria Garcia (Finance Manager) verified budget and compliance</div>
          <div class="approval-step-time">February 6, 2026 03:15 PM</div>
          <div class="approval-step-detail" style="margin-top: 0.5rem;"><strong>Notes:</strong> "Complies with financial policy. Budget available for February payout."</div>
        </div>
      </div>

      <div class="approval-step">
        <div class="approval-step-icon completed">4</div>
        <div class="approval-step-content">
          <div class="approval-step-title">Final Authorization ✓</div>
          <div class="approval-step-detail">Finance Director (CFO) issued final authorization for payment</div>
          <div class="approval-step-time">February 7, 2026 09:30 AM</div>
          <div class="approval-step-detail" style="margin-top: 0.5rem;"><strong>Status:</strong> READY FOR PAYMENT | Amount locked in approved adjustments queue</div>
        </div>
      </div>
    </div>

    <div class="alert alert-success">
      Status: APPROVED - Ready for disbursement in next special payroll run or bank file transmission.
    </div>
  </div>

  <!-- Data Integrity & Compliance -->
  <div class="section">
    <h3 class="section-header">✓ Data Integrity & Compliance Controls</h3>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #22c55e;">
        <h5 style="margin: 0 0 0.75rem 0; color: #065f46; font-size: 13px; font-weight: 600;">✓ Immutable Records</h5>
        <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.6;">Once approved, payroll records cannot be modified. All changes create new audit trail entries and require separate adjustment requests.</p>
      </div>

      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #22c55e;">
        <h5 style="margin: 0 0 0.75rem 0; color: #065f46; font-size: 13px; font-weight: 600;">✓ Complete Logging</h5>
        <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.6;">All system actions logged with user, timestamp, and details. Actions cannot be deleted from audit trail.</p>
      </div>

      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #22c55e;">
        <h5 style="margin: 0 0 0.75rem 0; color: #065f46; font-size: 13px; font-weight: 600;">✓ Role Separation</h5>
        <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.6;">Duties separated: Entry officers cannot approve. Approvers cannot process disbursements. No single person controls entire payroll.</p>
      </div>

      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #22c55e;">
        <h5 style="margin: 0 0 0.75rem 0; color: #065f46; font-size: 13px; font-weight: 600;">✓ Multi-Level Approval</h5>
        <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.6;">Critical changes require 2-3 levels of approval (HR → Finance → Director) to prevent fraud and errors.</p>
      </div>

      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #22c55e;">
        <h5 style="margin: 0 0 0.75rem 0; color: #065f46; font-size: 13px; font-weight: 600;">✓ Access Control</h5>
        <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.6;">Role-based access strictly enforced. Employees see only their own data. Access logged for compliance.</p>
      </div>

      <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #22c55e;">
        <h5 style="margin: 0 0 0.75rem 0; color: #065f46; font-size: 13px; font-weight: 600;">✓ Exception Handling</h5>
        <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.6;">System enforces business rules. No bypasses allowed. All overrides require director approval and additional logging.</p>
      </div>
    </div>
  </div>

  <!-- Critical Security Rules -->
  <div class="section">
    <h3 class="section-header">🔐 Critical Security Rules & Compliance</h3>

    <div class="alert alert-danger">
      <strong>❌ IMMUTABILITY POLICY:</strong> All approved payroll records are permanently locked. NO MODIFICATIONS allowed once approved. Any required changes must be processed through the Payroll Adjustments & Special Pay module as formal adjustment records.
    </div>

    <div class="alert alert-warning">
      <strong>⚠️ ALL ACTIONS LOGGED:</strong> Every payroll transaction is recorded with user, timestamp, IP address, and details. Audit logs cannot be deleted or modified. Non-compliance results in system audit flags and manual finance review.
    </div>

    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 0;">
      <h4 style="margin: 0 0 1rem 0; color: #1f2937; font-size: 14px; font-weight: 600;">Fraud Prevention Measures</h4>
      <ul style="margin: 0; padding-left: 1.5rem; color: #6b7280; font-size: 13px; line-height: 1.8;">
        <li><strong>Segregation of Duties:</strong> Entry → Review → Approval → Execution by different roles</li>
        <li><strong>Three-Level Approval:</strong> HR Manager → Finance Manager → Director/CFO (no skipping)</li>
        <li><strong>Exception Handling:</strong> No one-person approvals for amounts over ₱50,000</li>
        <li><strong>Bank File Immutability:</strong> Once transmitted to bank, files cannot be regenerated without director approval</li>
        <li><strong>Compensation Review:</strong> Monthly payroll summaries reviewed against prior periods for anomalies</li>
        <li><strong>Access Monitoring:</strong> Failed login attempts tracked. Unusual access patterns flagged for IT security review</li>
        <li><strong>Data Integrity Checks:</strong> System validates: Total deductions ≤ Gross Pay, Bank files match approved payroll, Tax jurisdictions match employee locations</li>
      </ul>
    </div>
  </div>

</div>

<script>
function switchTab(event, tabName) {
  event.preventDefault();
  
  // Hide all tab contents
  const contents = document.querySelectorAll('.tab-content');
  contents.forEach(content => {
    content.classList.remove('active');
  });
  
  // Remove active class from all tabs
  const tabs = document.querySelectorAll('.tab');
  tabs.forEach(tab => {
    tab.classList.remove('active');
  });
  
  // Show selected tab content
  document.getElementById(tabName).classList.add('active');
  
  // Add active class to clicked tab
  event.target.classList.add('active');
}
</script>
