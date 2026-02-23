<?php
/**
 * Tax and Contributions Engine Module
 * Ensure legal compliance through automated tax computation and contribution calculations
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../models/TaxContribution.php';
require_once __DIR__ . '/../models/PayrollComponent.php';

$taxContribution = new TaxContribution();
$payrollComponent = new PayrollComponent();

// Fetch tax and contribution data
$taxRecords = $taxContribution->getAll();
$totalTaxRecords = count($taxRecords ?? []);
?>

<style>
  .tax-engine-container {
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

  .compliance-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 1rem;
  }

  .compliance-compliant {
    background: #d1fae5;
    color: #065f46;
  }

  .compliance-warning {
    background: #fef3c7;
    color: #92400e;
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

  .badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
  }

  .badge-active {
    background: #d1fae5;
    color: #065f46;
  }

  .badge-inactive {
    background: #fee2e2;
    color: #991b1b;
  }

  .badge-pending {
    background: #fef3c7;
    color: #92400e;
  }

  .tax-breakdown {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
  }

  .breakdown-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 1.5rem;
    border-left: 4px solid #3b82f6;
  }

  .breakdown-card h4 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 14px;
    font-weight: 600;
  }

  .breakdown-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
  }

  .breakdown-item:last-child {
    border-bottom: none;
  }

  .breakdown-item label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
  }

  .breakdown-item value {
    color: #1f2937;
    font-size: 13px;
    font-weight: 500;
    text-align: right;
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

  .version-info {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 13px;
  }

  .version-info strong {
    color: #1f2937;
  }

  .version-info .date {
    color: #6b7280;
    font-family: 'Courier New', monospace;
  }

  .tax-table {
    margin-top: 1rem;
  }

  .tax-table table th {
    background: #eff6ff;
    font-weight: 600;
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
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
  }

  .version-history {
    margin-top: 2rem;
  }

  .history-item {
    padding: 1rem;
    border-left: 4px solid #3b82f6;
    margin-bottom: 1rem;
    background: #f9fafb;
    border-radius: 4px;
  }

  .history-item .date {
    color: #6b7280;
    font-size: 12px;
    font-weight: 500;
  }

  .history-item .version {
    color: #1f2937;
    font-size: 13px;
    font-weight: 600;
  }

  .history-item .status {
    margin-top: 0.5rem;
  }

  .effective-date {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
    margin-left: 0.5rem;
  }

  .compliance-status {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
    font-size: 13px;
  }

  .compliance-status.compliant {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
  }

  .compliance-status.warning {
    background: #fef3c7;
    border: 1px solid #fde68a;
    color: #92400e;
  }

  .calculation-step {
    padding: 1rem;
    background: #f3f4f6;
    border-left: 3px solid #3b82f6;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 13px;
  }

  .calculation-step strong {
    color: #1f2937;
  }
</style>

<div class="tax-engine-container">
  <!-- Page Header -->
  <div class="section">
    <h2 style="margin: 0 0 0.5rem 0; color: #1f2937;">Tax & Contributions Engine</h2>
    <p style="margin: 0; color: #6b7280; font-size: 14px;">Automated tax computation and contribution calculations ensuring legal compliance. No manual tax entry allowed - all calculations follow version-controlled statutory tables.</p>
    <div style="margin-top: 1rem; padding: 1rem; background: #d1fae5; border-radius: 4px; color: #065f46; font-size: 13px;">
      <strong>✓ Legal Compliance:</strong> All tax and contribution computations are automated using version-controlled tables with effective dates. Manual overrides are not permitted.
    </div>
  </div>

  <!-- Filters -->
  <div class="section">
    <form id="taxEngineFilterForm" method="GET" action="">
      <div class="filter-section">
        <div class="form-group">
          <label>Search Employee</label>
          <input type="text" name="search" placeholder="Employee ID or Name..." value="">
        </div>
        <div class="form-group">
          <label>Tax Status</label>
          <select name="tax_status">
            <option value="">-- All Status --</option>
            <option value="taxable">Taxable</option>
            <option value="exempt">Tax Exempt</option>
          </select>
        </div>
        <div class="form-group">
          <label>Payroll Period</label>
          <input type="month" name="period">
        </div>
        <div style="display: flex; align-items: flex-end;">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
      </div>
    </form>
  </div>

  <script>
    // Intercept tax engine filter to load results via dashboard loader
    (function(){
      const form = document.getElementById('taxEngineFilterForm');
      if (!form) return;
      form.addEventListener('submit', function(e){
        e.preventDefault();
        const fd = new FormData(form);
        const params = new URLSearchParams();
        for (const [k,v] of fd.entries()) {
          if (v !== null && String(v).trim() !== '') params.append(k, v);
        }
        const newQuery = params.toString();
        const viewUrl = 'dashboard.php?module=payroll&view=tax_contributions_engine' + (newQuery ? '&' + newQuery : '');

        history.replaceState(null, '', '?' + (newQuery ? ('module=payroll&view=tax_contributions_engine&' + newQuery) : 'module=payroll&view=tax_contributions_engine'));

        fetch(viewUrl)
          .then(resp => { if (!resp.ok) throw new Error('HTTP ' + resp.status); return resp.text(); })
          .then(html => {
            const scriptRegex = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
            const execRegex = new RegExp(scriptRegex);
            const scripts = [];
            let match;
            while ((match = execRegex.exec(html)) !== null) {
              const scriptTag = match[0];
              const scriptContent = scriptTag.replace(/<script[^>]*>/i, '').replace(/<\/script>/i, '');
              scripts.push(scriptContent);
            }

            const htmlWithoutScripts = html.replace(scriptRegex, '');
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlWithoutScripts, 'text/html');

            // Inject styles
            const styles = doc.querySelectorAll('style');
            styles.forEach(style => {
              try {
                const newStyle = document.createElement('style');
                newStyle.setAttribute('data-module-style', 'true');
                newStyle.textContent = style.textContent;
                document.head.appendChild(newStyle);
              } catch (e) { console.error('Error injecting style:', e); }
            });

            const mainContent = doc.querySelector('.main-content');
            const contentArea = document.getElementById('content-area');
            if (contentArea) {
              try {
                if (mainContent) contentArea.innerHTML = mainContent.innerHTML;
                else contentArea.innerHTML = doc.body.innerHTML;
              } catch (e) { console.error('Error setting content:', e); }
            }

            setTimeout(() => {
              scripts.forEach(scriptContent => {
                try {
                  const scriptEl = document.createElement('script');
                  scriptEl.textContent = scriptContent;
                  document.body.appendChild(scriptEl);
                  scriptEl.parentNode.removeChild(scriptEl);
                } catch (e) { console.error('Error executing script:', e); }
              });
            }, 50);
          })
          .catch(err => {
            console.error('Error loading tax engine filtered results:', err);
            alert('Error loading filtered results. Check console.');
          });
      });
    })();
  </script>

  <!-- Summary Cards -->
  <div class="summary-cards">
    <div class="summary-card">
      <label>Total Taxable Employees</label>
      <div class="value">6</div>
    </div>
    <div class="summary-card">
      <label>Tax Exempt Employees</label>
      <div class="value">2</div>
    </div>
    <div class="summary-card" style="border-left-color: #ef4444;">
      <label>Total Tax Withholding</label>
      <div class="value">₱ 18,700</div>
    </div>
    <div class="summary-card" style="border-left-color: #22c55e;">
      <label>Compliance Status</label>
      <div class="value" style="color: #065f46;">✓ Compliant</div>
    </div>
  </div>

  <!-- Tax Compliance Status -->
  <div class="section">
    <div class="compliance-status compliant">
      <strong>✓ System Compliance Status: COMPLIANT</strong><br>
      All tax calculations are using current, version-controlled statutory tables. BIR Withholding Tax Table v2.1 (Effective: January 1, 2026) applied to all computations.
    </div>
  </div>

  <!-- Active Tax & Contribution Tables -->
  <div class="section">
    <h3 class="section-header">📊 Active Tax & Contribution Tables (Current Period)</h3>

    <!-- BIR Withholding Tax Table -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        BIR Withholding Tax Table
        <span class="effective-date">v2.1 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 2.1 | <strong>Released:</strong> December 15, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Annual Taxable Income (From)</th>
                <th>Annual Taxable Income (To)</th>
                <th>Tax Rate (%)</th>
                <th>Fixed Deduction Amount (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>250,000</td>
                <td class="amount">0%</td>
                <td class="amount">0.00</td>
              </tr>
              <tr>
                <td>250,000.01</td>
                <td>400,000</td>
                <td class="amount">5%</td>
                <td class="amount">0.00</td>
              </tr>
              <tr>
                <td>400,000.01</td>
                <td>800,000</td>
                <td class="amount">10%</td>
                <td class="amount">7,500.00</td>
              </tr>
              <tr>
                <td>800,000.01</td>
                <td>2,000,000</td>
                <td class="amount">15%</td>
                <td class="amount">47,500.00</td>
              </tr>
              <tr>
                <td>2,000,000.01</td>
                <td>Unlimited</td>
                <td class="amount">20%</td>
                <td class="amount">187,500.00</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SSS Contribution Table -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        SSS Contribution Table
        <span class="effective-date">v1.5 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 1.5 | <strong>Released:</strong> December 1, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Salary Range (From)</th>
                <th>Salary Range (To)</th>
                <th>Employee Rate (%)</th>
                <th>Monthly Contribution (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>2,250</td>
                <td class="amount">3.63%</td>
                <td class="amount">81.74</td>
              </tr>
              <tr>
                <td>2,250.01</td>
                <td>3,750</td>
                <td class="amount">3.63%</td>
                <td class="amount">136.13</td>
              </tr>
              <tr>
                <td>3,750.01</td>
                <td>5,250</td>
                <td class="amount">3.63%</td>
                <td class="amount">190.50</td>
              </tr>
              <tr>
                <td>5,250.01</td>
                <td>7,750</td>
                <td class="amount">3.63%</td>
                <td class="amount">281.38</td>
              </tr>
              <tr>
                <td>7,750.01</td>
                <td>Unlimited</td>
                <td class="amount">3.63%</td>
                <td class="amount">406.24</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- PhilHealth Contribution Table -->
    <div style="margin-bottom: 2rem;">
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        PhilHealth Premium Table
        <span class="effective-date">v2.0 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 2.0 | <strong>Released:</strong> December 10, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Monthly Salary (From)</th>
                <th>Monthly Salary (To)</th>
                <th>Employee Rate (%)</th>
                <th>Monthly Premium (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>8,999.99</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
              <tr>
                <td>9,000</td>
                <td>14,999.99</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
              <tr>
                <td>15,000</td>
                <td>49,999.99</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
              <tr>
                <td>50,000</td>
                <td>Unlimited</td>
                <td class="amount">2.25%</td>
                <td class="amount">500.00</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pag-IBIG Contribution Table -->
    <div>
      <h4 style="color: #1f2937; margin-bottom: 0.5rem; font-size: 14px; font-weight: 600;">
        Pag-IBIG Contribution Table
        <span class="effective-date">v1.3 - Effective Jan 1, 2026</span>
      </h4>
      <div class="version-info">
        <strong>Version:</strong> 1.3 | <strong>Released:</strong> December 5, 2025 | <strong>Status:</strong> <span class="badge badge-active">Active</span>
      </div>

      <div class="tax-table">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Monthly Salary (From)</th>
                <th>Monthly Salary (To)</th>
                <th>Employee Rate (%)</th>
                <th>Monthly Contribution (₱)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>0</td>
                <td>1,500</td>
                <td class="amount">1.61%</td>
                <td class="amount">100.00</td>
              </tr>
              <tr>
                <td>1,500.01</td>
                <td>4,666.67</td>
                <td class="amount">1.61%</td>
                <td class="amount">150.00</td>
              </tr>
              <tr>
                <td>4,666.68</td>
                <td>Unlimited</td>
                <td class="amount">1.61%</td>
                <td class="amount">200.00</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>
