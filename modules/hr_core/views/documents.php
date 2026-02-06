
<main class="main-content documents-container">
  <div class="documents-layout">
    <!-- Left Column: Main Content -->
    <div class="documents-left-column">
      <!-- KPI Cards with Urgency -->
      <section class="document-kpi-section">
        <div class="document-kpi-grid">
          <div class="doc-card doc-card-total">
            <div class="doc-card-icon">📄</div>
            <div class="doc-card-content">
              <p class="doc-card-label">Total Documents</p>
              <h2 class="doc-card-value" id="totalDocs">0</h2>
              <p class="doc-card-subtext">All records</p>
            </div>
          </div>
          
          <div class="doc-card doc-card-valid">
            <div class="doc-card-icon">✓</div>
            <div class="doc-card-content">
              <p class="doc-card-label">Valid</p>
              <h2 class="doc-card-value" id="validDocs">0</h2>
              <p class="doc-card-subtext">Current</p>
            </div>
          </div>
          
          <div class="doc-card doc-card-expiring">
            <div class="doc-card-icon">⚠️</div>
            <div class="doc-card-content">
              <p class="doc-card-label">Expiring</p>
              <h2 class="doc-card-value" id="expiringDocs">0</h2>
              <p class="doc-card-subtext" id="expiringTimeframe">Next 30 days</p>
            </div>
          </div>
          
          <div class="doc-card doc-card-expired">
            <div class="doc-card-icon">🔴</div>
            <div class="doc-card-content">
              <p class="doc-card-label">Expired</p>
              <h2 class="doc-card-value" id="expiredDocs">0</h2>
              <p class="doc-card-subtext">Action required</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Filters with Quick Chips -->
      <section class="document-filters-section">
        <div class="card">
          <div style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <!-- Main Search -->
            <div class="form-group" style="flex: 1; min-width: 250px;">
              <label class="form-label">Search</label>
              <div style="position: relative;">
                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light);">🔍</span>
                <input type="text" id="searchInput" class="form-input" placeholder="Search by employee, type, ID..." style="padding-left: 36px;">
              </div>
            </div>

            <!-- Quick Filter Chips -->
            <div class="quick-filter-chips">
              <button class="filter-chip" data-status="valid" onclick="window.toggleStatusFilter('valid')">
                <span style="color: #22c55e;">✓</span> Valid
              </button>
              <button class="filter-chip" data-status="expiring" onclick="window.toggleStatusFilter('expiring')">
                <span style="color: #f59e0b;">⚠️</span> Expiring
              </button>
              <button class="filter-chip" data-status="expired" onclick="window.toggleStatusFilter('expired')">
                <span style="color: #ef4444;">🔴</span> Expired
              </button>
            </div>

            <!-- Document Type Filter -->
            <div class="form-group" style="min-width: 140px;">
              <label class="form-label">Type</label>
              <select id="typeFilter" class="form-select">
                <option value="">All Types</option>
                <option value="id">ID Documents</option>
                <option value="contract">Contracts</option>
                <option value="certification">Certifications</option>
                <option value="performance">Performance</option>
                <option value="disciplinary">Disciplinary</option>
                <option value="leave">Leave Records</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <!-- Documents Table Section -->
      <section class="document-table-section">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Document Records</h3>
            <button class="btn btn-outline btn-sm" onclick="window.resetFilters()" style="color: var(--text-light);">↻ Reset</button>
          </div>

          <div class="table-container" style="overflow-x: auto;">
            <table class="document-table">
              <thead class="table-head-sticky">
                <tr>
                  <th style="width: 18%; text-align: left;">Employee Name</th>
                  <th style="width: 12%; text-align: left;">Employee ID</th>
                  <th style="width: 15%; text-align: left;">Department</th>
                  <th style="width: 8%; text-align: center;">Valid</th>
                  <th style="width: 10%; text-align: center;">Expiring</th>
                  <th style="width: 10%; text-align: center;">Expired</th>
                  <th style="width: 15%; text-align: center;">Action</th>
                </tr>
              </thead>
              <tbody id="documentsList">
                <tr>
                  <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
                    <div style="color: var(--text-light);">
                      <div style="font-size: 48px; margin-bottom: 1rem;">📄</div>
                      <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No documents found</p>
                      <p style="font-size: 14px; margin-bottom: 1.5rem;">Add documents to track compliance and expiry deadlines.</p>
                      <button class="btn btn-primary" onclick="window.openDocumentModal()">+ Add First Document</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Right Column: Actions & Insights Panel -->
    <aside class="documents-right-column">
      <div class="doc-side-panel">
        <div class="doc-side-panel-header">
          <h3 class="doc-side-panel-title">Actions & Alerts</h3>
        </div>

        <!-- Action Buttons -->
        <div class="doc-side-section">
          <button class="doc-side-btn doc-side-btn-primary" onclick="window.openDocumentModal()">
            <span class="doc-side-btn-icon">➕</span>
            <span>Add Document</span>
          </button>
          <button class="doc-side-btn" onclick="alert('Bulk upload feature coming soon')">
            <span class="doc-side-btn-icon">📦</span>
            <span>Bulk Upload</span>
          </button>
        </div>

        <!-- Required Documents Checklist -->
        <div class="doc-side-section">
          <p class="doc-side-subtitle">Required Documents</p>
          <div id="requiredDocsList" style="max-height: 180px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>

        <!-- Expiring Soon - Top 5 -->
        <div class="doc-side-section">
          <p class="doc-side-subtitle">Expiring Soon</p>
          <div id="expiringSoonList" style="max-height: 200px; overflow-y: auto;">
            <p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">Loading...</p>
          </div>
        </div>

        <!-- Overdue Documents -->
        <div class="doc-side-section doc-side-danger">
          <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
            <span style="font-size: 20px;">🔴</span>
            <div>
              <p class="doc-side-label">Overdue Documents</p>
              <p class="doc-side-count" id="overdueCount">0</p>
            </div>
          </div>
          <button class="btn btn-outline btn-sm" onclick="window.filterByStatus('expired')" style="width: 100%;">View All</button>
        </div>
      </div>
    </aside>
  </div>
</main>

<!-- Document Modal -->
<div id="documentModal" class="modal">
  <div class="modal-content" style="max-width: 600px;">
    <div class="modal-header">
      <h2 class="modal-title">Add Document</h2>
      <button class="modal-close" onclick="closeDocumentModal()">&times;</button>
    </div>
    <form id="documentForm">
      <div class="form-group">
        <label class="form-label">Employee *</label>
        <select name="employee_id" class="form-select" required>
          <option value="">Select employee...</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Document Type *</label>
        <select name="document_type" class="form-select" required>
          <option value="">Select type...</option>
          <option value="id">ID Documents</option>
          <option value="contract">Contracts</option>
          <option value="certification">Certifications</option>
          <option value="performance">Performance Appraisal</option>
          <option value="disciplinary">Disciplinary Record</option>
          <option value="leave">Leave Record</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Upload Date *</label>
        <input type="date" name="upload_date" class="form-input" required>
      </div>
      <div class="form-group">
        <label class="form-label">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-input">
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="valid">Valid</option>
          <option value="expiring">Expiring</option>
          <option value="expired">Expired</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-input" placeholder="Additional notes..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeDocumentModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function() {
    let currentPage = 0;

    window.loadDocuments = function() {
      console.log('[Documents] Loading documents...');
      
      const searchQuery = document.getElementById('searchInput')?.value || '';
      const typeFilter = document.getElementById('typeFilter')?.value || '';
      
      // Get active status chips
      const activeChips = Array.from(document.querySelectorAll('.filter-chip.active'))
        .map(chip => chip.dataset.status)
        .join(',');

      const params = new URLSearchParams({
        search: searchQuery,
        type: typeFilter,
        status: activeChips
      });
      
      const tbody = document.getElementById('documentsList');
      
      // Show loading state
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="100%" style="text-align: center; padding: 2rem;"><div style="display: inline-block;"><div class="spinner" style="margin: 0 auto 1rem; border: 4px solid rgba(30, 64, 175, 0.2); border-top: 4px solid #3b82f6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div><p>Loading...</p></div></td></tr>';
      }

      fetch(`modules/hr_core/api.php?action=getDocuments&${params}`)
        .then(res => res.json())
        .then(response => {
          if (!response.success) {
            console.error('[Documents] API error:', response.message);
            if (tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align: center; padding: 2rem; color: #ef4444;"><strong>Error:</strong> ${response.message}</td></tr>`;
            return;
          }
          
          if (response.data && response.data.documents) {
            window.displayDocumentsByEmployee(response.data.documents);
            
            if (response.data.stats) {
              const updateElement = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value || 0;
              };
              updateElement('totalDocs', response.data.stats.total);
              updateElement('validDocs', response.data.stats.valid);
              updateElement('expiringDocs', response.data.stats.expiring);
              updateElement('expiredDocs', response.data.stats.expired);
              updateElement('overdueCount', response.data.stats.expired);
            }
            
            window.loadExpiringSoonList(response.data.expiring_soon || []);
            window.loadRequiredDocsList();
            
            console.log('[Documents] Loaded successfully');
          } else {
            if (tbody) tbody.innerHTML = '<tr><td colspan="100%" style="text-align: center; padding: 3rem;">📄 No document records found</td></tr>';
          }
        })
        .catch(error => {
          console.error('[Documents] Error:', error);
          if (tbody) tbody.innerHTML = `<tr><td colspan="100%" style="text-align: center; padding: 2rem; color: #ef4444;"><strong>Error:</strong> Failed to load documents</td></tr>`;
        });
    };

    // Group documents by employee
    window.groupDocumentsByEmployee = function(documents) {
      const grouped = {};
      documents.forEach(doc => {
        const employeeId = doc.employee_id;
        if (!grouped[employeeId]) {
          grouped[employeeId] = {
            employee_id: employeeId,
            employee_name: doc.employee_name,
            department: doc.department,
            documents: []
          };
        }
        grouped[employeeId].documents.push(doc);
      });
      return Object.values(grouped);
    };

    // Calculate document summary for an employee
    window.getDocumentSummary = function(documents) {
      const today = new Date();
      let valid = 0, expiring = 0, expired = 0;

      documents.forEach(doc => {
        const expiryDate = doc.expiry_date ? new Date(doc.expiry_date) : null;
        if (!expiryDate) {
          valid++;
          return;
        }

        const timeDiff = expiryDate.getTime() - today.getTime();
        const daysLeftNum = Math.ceil(timeDiff / (1000 * 3600 * 24));

        if (daysLeftNum < 0) {
          expired++;
        } else if (daysLeftNum <= 30) {
          expiring++;
        } else {
          valid++;
        }
      });

      return { valid, expiring, expired };
    };

    window.displayDocumentsByEmployee = function(documents) {
      const tbody = document.getElementById('documentsList');
      tbody.innerHTML = '';

      if (!documents || documents.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="7" style="text-align: center; padding: 3rem 2rem;">
              <div style="color: var(--text-light);">
                <div style="font-size: 48px; margin-bottom: 1rem;">📄</div>
                <p style="font-size: 16px; font-weight: 600; margin-bottom: 0.5rem;">No documents found</p>
                <p style="font-size: 14px; margin-bottom: 1.5rem;">Add documents to track compliance and expiry deadlines.</p>
                <button class="btn btn-primary" onclick="window.openDocumentModal()">+ Add First Document</button>
              </div>
            </td>
          </tr>
        `;
        return;
      }

      // Group documents by employee
      const employees = window.groupDocumentsByEmployee(documents);

      employees.forEach(emp => {
        const summary = window.getDocumentSummary(emp.documents);

        const row = `
          <tr style="cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='white'" onclick="window.showEmployeeDocumentsModal(${emp.employee_id}, '${emp.employee_name.replace(/'/g, "\\'")}', '${emp.department || 'N/A'.replace(/'/g, "\\'")}')">
            <td style="font-weight: 600; color: var(--text-dark);">${emp.employee_name || '-'}</td>
            <td><code style="background: var(--bg-light); padding: 2px 6px; border-radius: 3px; font-size: 12px;">EMP${emp.employee_id}</code></td>
            <td>${emp.department || '-'}</td>
            <td style="text-align: center;">
              <span class="doc-badge doc-badge-valid" title="Valid documents">${summary.valid}</span>
            </td>
            <td style="text-align: center;">
              <span class="doc-badge doc-badge-expiring" title="Expiring soon">${summary.expiring}</span>
            </td>
            <td style="text-align: center;">
              <span class="doc-badge doc-badge-expired" title="Expired documents">${summary.expired}</span>
            </td>
            <td style="text-align: center;">
              <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); window.showEmployeeDocumentsModal(${emp.employee_id}, '${emp.employee_name.replace(/'/g, "\\'")}', '${emp.department || 'N/A'.replace(/'/g, "\\'")}')">View Documents</button>
            </td>
          </tr>
        `;
        tbody.innerHTML += row;
      });
    };

    window.showEmployeeDocumentsModal = function(employeeId, employeeName, department) {
      // Find the employee's documents from current displayed documents
      const tbody = document.getElementById('documentsList');
      const allDocuments = [];
      
      // Get all documents from the page and find ones for this employee
      fetch(`modules/hr_core/api.php?action=getDocuments`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.data && data.data.documents) {
            const employeeDocuments = data.data.documents.filter(doc => doc.employee_id === employeeId);
            
            const content = `
              <div>
                <!-- Employee Header -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 2rem; padding: 1.5rem; background: var(--bg-light); border-radius: 8px;">
                  <div>
                    <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">Employee Name</label>
                    <p style="font-size: 16px; color: var(--text-dark); font-weight: 600;">${employeeName}</p>
                  </div>
                  <div>
                    <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">Employee ID</label>
                    <p style="font-size: 16px; color: var(--text-dark); font-weight: 600;">EMP${employeeId}</p>
                  </div>
                  <div>
                    <label style="display: block; font-size: 12px; color: var(--text-light); margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">Department</label>
                    <p style="font-size: 16px; color: var(--text-dark); font-weight: 600;">${department}</p>
                  </div>
                </div>

                <!-- Quick Actions -->
                <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem; flex-wrap: wrap;">
                  <button class="btn btn-primary btn-sm" onclick="window.openDocumentModal(${employeeId})">+ Add Document</button>
                  <button class="btn btn-outline btn-sm" onclick="window.replaceDocument(${employeeId})">⟳ Replace</button>
                  <button class="btn btn-outline btn-sm" onclick="window.sendReminder(${employeeId})">📧 Send Reminder</button>
                </div>

                <!-- Documents Table -->
                <div style="overflow-x: auto;">
                  <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--bg-light); border-bottom: 2px solid var(--border);">
                      <tr>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-dark);">Document Type</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-dark);">Upload Date</th>
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-dark);">Expiry Date</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-dark);">Status</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-dark);">Days Left</th>
                        <th style="padding: 0.75rem 1rem; text-align: center; font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--text-dark);">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${employeeDocuments.map(doc => {
                        const expiryDate = doc.expiry_date ? new Date(doc.expiry_date) : null;
                        const today = new Date();
                        let daysLeft = '';
                        let statusBadge = '✓ Valid';

                        if (expiryDate) {
                          const timeDiff = expiryDate.getTime() - today.getTime();
                          const daysLeftNum = Math.ceil(timeDiff / (1000 * 3600 * 24));
                          daysLeft = daysLeftNum;

                          if (daysLeftNum < 0) {
                            statusBadge = '🔴 Expired';
                          } else if (daysLeftNum <= 30) {
                            statusBadge = '⚠️ Expiring';
                          }
                        }

                        return `
                          <tr style="border-bottom: 1px solid var(--border); transition: all 0.2s ease;" onmouseover="this.style.background='var(--bg-light)'" onmouseout="this.style.background='white'">
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: var(--text-dark);">${doc.document_type || '-'}</td>
                            <td style="padding: 0.75rem 1rem; color: var(--text-dark);">${doc.upload_date || doc.issue_date || '-'}</td>
                            <td style="padding: 0.75rem 1rem; color: var(--text-dark);">${doc.expiry_date || '-'}</td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                              <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                ${statusBadge}
                              </span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: var(--text-dark);">${daysLeft ? daysLeft + ' days' : '-'}</td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                              <div style="display: flex; gap: 0.25rem; justify-content: center;">
                                <button class="doc-action-btn doc-action-view" onclick="window.viewDocument(${doc.id})" title="View">👁</button>
                                <button class="doc-action-btn doc-action-download" onclick="window.downloadDocument(${doc.id})" title="Download">⬇</button>
                                <button class="doc-action-btn doc-action-edit" onclick="window.editDocument(${doc.id})" title="Edit">✏</button>
                                <button class="doc-action-btn doc-action-delete" onclick="window.deleteDocument(${doc.id})" title="Delete">🗑</button>
                              </div>
                            </td>
                          </tr>
                        `;
                      }).join('')}
                    </tbody>
                  </table>
                </div>
              </div>
            `;

            window.showDocumentModal('Employee Documents', content);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.showDocumentModal = function(title, content) {
      let modal = document.getElementById('documentActionModal');
      if (!modal) {
        modal = document.createElement('div');
        modal.id = 'documentActionModal';
        modal.innerHTML = `
          <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;" onclick="if(event.target === this) window.closeDocumentModal()">
            <div style="background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2); max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto;">
              <div style="padding: 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white;">
                <h2 id="documentModalTitle" style="font-size: 20px; font-weight: 700; color: var(--text-dark); margin: 0;"></h2>
                <button onclick="window.closeDocumentModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-light);">✕</button>
              </div>
              <div id="documentModalContent" style="padding: 2rem;"></div>
            </div>
          </div>
        `;
        document.body.appendChild(modal);
      }

      document.getElementById('documentModalTitle').textContent = title;
      document.getElementById('documentModalContent').innerHTML = content;
      if (modal) {
        modal.style.display = 'flex';
      }
    };

    window.closeDocumentModal = function() {
      const modal = document.getElementById('documentActionModal');
      if (modal) {
        modal.style.display = 'none';
      }
    };

    window.replaceDocument = function(employeeId) {
      alert('Replace document feature for employee ' + employeeId);
    };

    window.sendReminder = function(employeeId) {
      alert('Sending reminder to employee ' + employeeId);
    };

    // Legacy function for backward compatibility
    window.displayDocuments = function(documents) {
      window.displayDocumentsByEmployee(documents);
    };


    window.openDocumentModal = function(employeeId) {
      document.getElementById('documentForm').reset();
      delete document.getElementById('documentForm').dataset.id;
      
      if (employeeId) {
        // Set the employee ID in the form
        const employeeSelect = document.getElementById('documentForm').querySelector('select[name="employee_id"]');
        if (employeeSelect) {
          employeeSelect.value = employeeId;
          employeeSelect.disabled = true;
        }
      }
      
      document.querySelector('#documentModal .modal-title').textContent = 'Add Document';
      document.getElementById('documentModal').classList.add('active');
    };

    window.closeDocumentModal = function() {
      const modal = document.getElementById('documentActionModal');
      if (modal) {
        modal.style.display = 'none';
      }
    };

    window.viewDocument = function(id) {
      fetch(`modules/hr_core/api.php?action=getDocumentById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const doc = data.data;
            alert(`Document: ${doc.document_type}\nEmployee: ${doc.employee_name}\nIssue Date: ${doc.issue_date}\nExpiry: ${doc.expiry_date || 'No expiry'}\nRemarks: ${doc.remarks || 'None'}`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.downloadDocument = function(id) {
      alert('Download feature coming soon');
    };

    window.editDocument = function(id) {
      fetch(`modules/hr_core/api.php?action=getDocumentById&id=${id}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const doc = data.data;
            alert(`Edit document: ${doc.document_type}\nThis feature will open the document edit form`);
          }
        })
        .catch(error => console.error('Error:', error));
    };

    window.deleteDocument = function(id) {
      if (confirm('Are you sure you want to delete this document?')) {
        fetch(`modules/hr_core/api.php?action=deleteDocument&id=${id}`, {
          method: 'DELETE'
        })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert('Document deleted successfully');
              window.loadDocuments();
            } else {
              alert('Error deleting document');
            }
          })
          .catch(error => console.error('Error:', error));
      }
    };

    window.showMoreActions = function(id) {
      alert('Options: View Details, Download, Edit, Delete');
    };

    window.toggleStatusFilter = function(status) {
      const chip = document.querySelector(`.filter-chip[data-status="${status}"]`);
      chip.classList.toggle('active');
      window.loadDocuments();
    };

    window.resetFilters = function() {
      document.getElementById('searchInput').value = '';
      document.getElementById('typeFilter').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      window.loadDocuments();
    };

    window.filterByStatus = function(status) {
      document.getElementById('searchInput').value = '';
      document.getElementById('typeFilter').value = '';
      document.querySelectorAll('.filter-chip').forEach(chip => chip.classList.remove('active'));
      const chip = document.querySelector(`.filter-chip[data-status="${status}"]`);
      if (chip) chip.classList.add('active');
      window.loadDocuments();
    };

    window.loadExpiringSoonList = function(docs) {
      const list = document.getElementById('expiringSoonList');
      if (!list) return; // Element might not be in DOM if side panel not visible
      
      if (!docs || docs.length === 0) {
        list.innerHTML = '<p style="font-size: 12px; color: var(--text-light); text-align: center; padding: 1rem;">All documents valid</p>';
        return;
      }
      list.innerHTML = docs.slice(0, 5).map(doc => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-size: 12px; cursor: pointer;" onclick="window.editDocument(${doc.id})">
          <div style="font-weight: 600; color: var(--text-dark); margin-bottom: 0.25rem;">${doc.employee_name}</div>
          <div style="color: var(--text-light); font-size: 11px;">${doc.document_type} • ${doc.expiry_date}</div>
        </div>
      `).join('');
    };

    window.loadRequiredDocsList = function() {
      const list = document.getElementById('requiredDocsList');
      if (!list) return; // Element might not be in DOM if side panel not visible
      
      const requiredDocs = [
        { name: 'Photo ID', status: 'pending' },
        { name: 'Background Check', status: 'completed' },
        { name: 'Contract', status: 'pending' },
        { name: 'Medical Certificate', status: 'completed' }
      ];
      list.innerHTML = requiredDocs.map(doc => `
        <div style="padding: 0.75rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 0.5rem; font-size: 12px;">
          <span>${doc.status === 'completed' ? '✓' : '○'}</span>
          <span style="color: ${doc.status === 'completed' ? 'var(--text-light)' : 'var(--text-dark)'};">${doc.name}</span>
        </div>
      `).join('');
    };

    function attachEventListeners() {
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('keyup', function() {
          window.loadDocuments();
        });
      }

      const filters = ['typeFilter', 'statusFilter'];
      filters.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', () => window.loadDocuments());
      });

      const form = document.getElementById('documentForm');
      if (form) {
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          const isEdit = this.dataset.id;
          const action = isEdit ? 'updateDocument' : 'createDocument';
          const data = {
            employee_id: formData.get('employee_id'),
            document_type: formData.get('document_type'),
            upload_date: formData.get('upload_date'),
            expiry_date: formData.get('expiry_date'),
            status: formData.get('status'),
            notes: formData.get('notes')
          };
          if (isEdit) data.id = isEdit;

          fetch(`modules/hr_core/api.php?action=${action}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
          })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              alert(isEdit ? 'Document updated' : 'Document created');
              window.closeDocumentModal();
              window.loadDocuments();
            }
          })
          .catch(error => console.error('Error:', error));
        });
      }
    }

    setTimeout(attachEventListeners, 50);
    window.loadDocuments();
  })();
</script>

<style>
  /* Documents Layout */
  .documents-container {
    max-width: 1420px;
    margin: 0 auto;
    padding: 0 1rem;
  }

  .documents-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    align-items: start;
  }

  .documents-left-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  .documents-right-column {
    position: sticky;
    top: 20px;
    height: fit-content;
  }

  /* Document KPI Cards */
  .document-kpi-section {
    margin-bottom: 0;
  }

  .document-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
  }

  .doc-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
  }

  .doc-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: var(--primary);
  }

  .doc-card-icon {
    font-size: 32px;
    line-height: 1;
    flex-shrink: 0;
  }

  .doc-card-content {
    flex: 1;
  }

  .doc-card-label {
    margin: 0;
    font-size: 11px;
    color: var(--text-light);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .doc-card-value {
    margin: 0.5rem 0 0 0;
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    color: var(--text-dark);
  }

  .doc-card-subtext {
    margin: 0.25rem 0 0 0;
    font-size: 12px;
    color: var(--text-light);
  }

  .doc-card-valid .doc-card-value { color: #22c55e; }
  .doc-card-expiring .doc-card-value { color: #f59e0b; }
  .doc-card-expired .doc-card-value { color: #ef4444; border: 2px solid #fee2e2; padding: 0.75rem; border-radius: 4px; }

  /* Filters */
  .document-filters-section {
    margin-bottom: 0;
  }

  .quick-filter-chips {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .filter-chip {
    background: white;
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    color: var(--text-dark);
    white-space: nowrap;
  }

  .filter-chip:hover {
    border-color: var(--primary);
    background: var(--bg-light);
  }

  .filter-chip.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
  }

  /* Document Table */
  .document-table-section {
    margin-bottom: 0;
  }

  .document-table {
    width: 100%;
    border-collapse: collapse;
  }

  .table-head-sticky {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  .document-table thead th {
    padding: 14px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: var(--text-light);
    border-bottom: 2px solid var(--border);
  }

  .document-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background-color 0.2s ease;
  }

  .document-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
  }

  .doc-row-expired {
    background-color: #fef2f2;
  }

  .doc-row-expired:hover {
    background-color: #fee2e2;
  }

  .doc-row-expiring {
    background-color: #fffbeb;
  }

  .doc-row-expiring:hover {
    background-color: #fef3c7;
  }

  .document-table tbody td {
    padding: 14px 16px;
    font-size: 13px;
    vertical-align: middle;
  }

  .doc-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
  }

  .doc-row-expired .doc-status-badge {
    background-color: #fee2e2;
    color: #991b1b;
  }

  .doc-row-expiring .doc-status-badge {
    background-color: #fef3c7;
    color: #92400e;
  }

  .doc-row-valid .doc-status-badge {
    background-color: #dcfce7;
    color: #166534;
  }

  /* Document Summary Badges */
  .doc-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    min-width: 50px;
    text-align: center;
  }

  .doc-badge-valid {
    background-color: #dcfce7;
    color: #166534;
  }

  .doc-badge-expiring {
    background-color: #fef3c7;
    color: #92400e;
  }

  .doc-badge-expired {
    background-color: #fee2e2;
    color: #991b1b;
  }

  /* Button Styles */
  .btn-sm {
    padding: 0.5rem 1rem;
    font-size: 13px;
  }

  /* Action Buttons */
  .doc-action-btn {
    background: none;
    border: none;
    font-size: 16px;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 4px;
    transition: all 0.2s ease;
    color: var(--text-light);
  }

  .doc-action-btn:hover {
    background: rgba(0, 0, 0, 0.05);
    color: var(--primary);
  }

  .doc-action-view:hover { color: #3b82f6; }
  .doc-action-download:hover { color: #10b981; }
  .doc-action-edit:hover { color: #f59e0b; }
  .doc-action-delete:hover { color: #ef4444; }
  .doc-action-edit:hover { color: #f59e0b; }
  .doc-action-more:hover { color: var(--text-dark); }

  /* Side Panel */
  .doc-side-panel {
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
  }

  .doc-side-panel-header {
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, var(--bg-light) 0%, var(--bg-lighter) 100%);
  }

  .doc-side-panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
  }

  .doc-side-section {
    padding: 1.25rem;
    border-bottom: 1px solid var(--border);
  }

  .doc-side-section:last-child {
    border-bottom: none;
  }

  .doc-side-danger {
    background-color: #fef2f2;
    border-bottom: 2px solid #fee2e2;
  }

  .doc-side-subtitle {
    margin: 0 0 1rem 0;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .doc-side-label {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
  }

  .doc-side-count {
    margin: 0.25rem 0 0 0;
    font-size: 22px;
    font-weight: 700;
    color: #ef4444;
  }

  /* Side Panel Buttons */
  .doc-side-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
    border: 1px solid var(--border);
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    color: var(--text-dark);
    transition: all 0.2s ease;
  }

  .doc-side-btn:last-of-type {
    margin-bottom: 0;
  }

  .doc-side-btn:hover {
    background: var(--bg-light);
    border-color: var(--primary);
    color: var(--primary);
  }

  .doc-side-btn-primary {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
  }

  .doc-side-btn-primary:hover {
    opacity: 0.9;
  }

  .doc-side-btn-icon {
    font-size: 16px;
    flex-shrink: 0;
  }

  /* Responsive */
  @media (max-width: 1200px) {
    .documents-layout {
      grid-template-columns: 1fr 280px;
      gap: 1.5rem;
    }

    .document-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }
  }

  @media (max-width: 768px) {
    .documents-layout {
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    .documents-right-column {
      position: static;
    }

    .document-kpi-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 0.75rem;
    }

    .doc-card {
      padding: 1rem;
      gap: 0.75rem;
    }

    .doc-card-icon {
      font-size: 24px;
    }

    .doc-card-value {
      font-size: 24px;
    }

    .quick-filter-chips {
      order: 3;
      flex-basis: 100%;
    }
  }

  @media (max-width: 480px) {
    .document-kpi-grid {
      grid-template-columns: 1fr;
    }

    .quick-filter-chips {
      flex-direction: column;
    }

    .filter-chip {
      width: 100%;
      justify-content: center;
    }

    .document-table thead th,
    .document-table tbody td {
      padding: 8px;
      font-size: 12px;
    }

    .doc-card-value {
      font-size: 20px;
    }
  }
</style>
