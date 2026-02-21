# Payroll Module Routing Validation

## Issue Fixed
**Problem:** Preview & Process button redirected to main dashboard instead of staying in payroll_processing_approval view
**Root Cause:** dashboard.php lacked POST request handling for form submissions
**Solution:** Added POST middleware to intercept form submissions and route through PayrollRunController

---

## Request Flow (Complete Path)

### 1. Form Submission
```
User clicks "Preview & Process" button
                    ↓
Form POSTs to dashboard.php with:
  - ref=payroll
  - page=payroll_processing_approval
  - action=preview
  - payroll_id=1
```

### 2. Dashboard.php POST Handler (lines 18-59)
```
dashboard.php receives POST request
                    ↓
Extracts parameters: $ref, $page, $action, $payroll_id
                    ↓
Checks: if ($ref==='payroll' && $page==='payroll_processing_approval')
                    ↓
YES → Requires PayrollRunController
    → Calls PayrollRunController::route()
    → Builds redirect URL with GET parameters
    → Issues Location header redirect
                    ↓
Redirect to: dashboard.php?ref=payroll&page=payroll_processing_approval&action=preview&payroll_id=1
```

### 3. PayrollRunController::route() (lines 245-297)
```
Extracts action from $_GET or $_POST
                    ↓
Routes based on action:
  - action=preview → calls previewPayroll($payroll_id)
                      → sets $_SESSION['preview_payroll_id'] = $payroll_id
                      → returns preview data array
  
  - action=cancel_preview → unsets $_SESSION['preview_payroll_id']
  
  - action=approve_hr → calls approveHR($payroll_id, $notes)
                        → extracts $_POST['hr_notes']
                        → creates/updates approval record
  
  - action=reject_hr → calls rejectHR($payroll_id, $reason)
                       → extracts $_POST['hr_reason']
                       → sets payroll to DRAFT status
```

### 4. View Rendering (lines 1-120)
```
dashboard.php GET request loads normally
                    ↓
View (payroll_processing_approval.php) loads
                    ↓
Line 20: Calls PayrollRunController::route() again
Line 24: Checks $_SESSION['preview_payroll_id']
                    ↓
If preview_payroll_id exists:
  → Fetches payroll data and employees
  → Displays preview section
  → Shows HR approval form below
                    ↓
If preview_payroll_id is null:
  → Displays payroll list and create form
```

---

## Forms and Parameters

### Form 1: Preview & Process Button
**Location:** Line 584
**Method:** POST
**Action:** dashboard.php
**Hidden Parameters:**
- ref=payroll
- page=payroll_processing_approval
- action=preview
- payroll_id=1

**Expected Result:** Dashboard redirects → View displays preview section with HR approval form

---

### Form 2: Back to List Button
**Location:** Line 663
**Method:** POST
**Action:** dashboard.php
**Hidden Parameters:**
- ref=payroll
- page=payroll_processing_approval
- action=cancel_preview

**Expected Result:** Session['preview_payroll_id'] cleared → Preview section hidden → Payroll list displayed

---

### Form 3: HR Approval Quick Buttons (in list)
**Location:** Line 798
**Method:** POST
**Action:** dashboard.php
**Hidden Parameters:**
- ref=payroll
- page=payroll_processing_approval
- payroll_id={selected payroll id}

**Button Values:** approve_hr or reject_hr

**Expected Result:** Approval/rejection processed → Redirect back to list

---

### Form 4: HR Review Form
**Location:** Line 822
**Method:** POST
**Action:** dashboard.php
**Hidden Parameters:**
- ref=payroll
- page=payroll_processing_approval
- payroll_id={preview payroll id}

**Textarea:** hr_notes

**Button Values:** approve_hr or reject_hr

**Expected Result:** Review note + action recorded → Redirect back to preview with updated status

---

### Form 5: Create Payroll Run
**Location:** Line 625
**Method:** POST
**Action:** dashboard.php
**Hidden Parameters:**
- ref=payroll
- page=payroll_processing_approval

**Expected Result:** New payroll run created → Redirect back to list to show new run

---

## Session State Management

### $_SESSION['preview_payroll_id']
- **Set When:** action=preview in PayrollRunController::previewPayroll()
- **Cleared When:** action=cancel_preview in PayrollRunController route()
- **Used By:** View (line 24) to determine which section to display
- **Persists:** Across redirect chain (POST → redirect → GET)

```php
// Set during preview action (controller line 265)
$_SESSION['preview_payroll_id'] = (int) $payroll_id;

// Check in view (view line 24)
$previewPayrollId = $_SESSION['preview_payroll_id'] ?? null;

// Use to conditionally display (view lines 26-31)
if ($previewPayrollId) {
    $previewData = $payrollRun->find($previewPayrollId);
    if ($previewData) {
        $previewEmployees = $payrollRunEmployee->getByRunWithEmployee($previewPayrollId);
    }
}
```

---

## Key Design Decisions

### 1. No Direct Handler Files
✅ **Correct**: All forms POST to central dashboard.php
❌ **Avoided**: Direct form submission to payroll_processing_approval_handler.php (404 error)

### 2. POST → GET Pattern
✅ **Reason**: Forms must POST to send data (hr_notes, checkboxes)
✅ **Then redirect to GET**: To preserve standard GET routing for view loading
✅ **Result**: Single source of truth for view state + proper HTTP semantics

### 3. Session Preservation Across Redirects
✅ **Method**: $_SESSION['preview_payroll_id'] persists through redirect chain
✅ **Benefit**: User stays in "preview mode" after redirect, no loss of context
✅ **Alternative Considered**: URL parameters (less secure, visible, max length limits)

### 4. Controller Processes, Not Views
✅ **Pattern**: View posts data to controller → controller updates state → view re-renders
✅ **Benefit**: Clean separation of concerns, reusable controller methods
✅ **Avoid**: Views directly accessing $_POST or database

---

## Testing Checklist

- [ ] Click "Preview & Process" button
  -Verify: URL becomes dashboard.php?ref=payroll&page=payroll_processing_approval&payroll_id=1&action=preview
  - Verify: Page stays in payroll module (NO redirect to main dashboard)
  - Verify: Preview section displays with payroll details and HR approval form
  - Verify: Session['preview_payroll_id'] = 1

- [ ] In preview section, click "Approve for Finance Review"
  - Verify: hr_notes submitted with form
  - Verify: Approval record created in database
  - Verify: Page refreshes and shows success message
  - Verify: Payroll moves to "Approved" status

- [ ] In preview section, click "Reject - Request Corrections"
  - Verify: Payroll moved back to DRAFT status
  - Verify: Page refreshes and shows rejection message
  - Verify: Preview section still displays with rejected payroll

- [ ] Click "← Back to List" button
  - Verify: Preview section hidden
  - Verify: Payroll list displays again
  - Verify: Session['preview_payroll_id'] cleared

- [ ] Create new payroll run
  - Verify: Form submits successfully
  - Verify: New payroll appears in list
  - Verify: Page stays in payroll module (no main dashboard redirect)

---

## Troubleshooting

### Issue: Redirect to main dashboard when clicking Preview & Process
**Cause:** dashboard.php POST handler not being triggered
**Solution:** 
1. Check form's method="POST" attribute
2. Check form's hidden inputs have ref=payroll, page=payroll_processing_approval
3. Check dashboard.php lines 18-59 POST handler is present
4. Verify PayrollRunController.php exists at correct path

### Issue: Preview section not showing after clicking Preview & Process
**Cause:** $_SESSION['preview_payroll_id'] not being set
**Solution:**
1. Check PayrollRunController::previewPayroll() line 265 sets session: `$_SESSION['preview_payroll_id'] = (int) $payroll_id;`
2. Check view line 24 retrieves session: `$previewPayrollId = $_SESSION['preview_payroll_id'] ?? null;`
3. Verify session is not being cleared elsewhere

### Issue: HR buttons showing but doing nothing
**Cause:** Form parameters not matching controller expectations
**Solution:**
1. Check form has hidden inputs for ref, page, payroll_id
2. Check button has name="action" value="approve_hr" or "reject_hr"
3. Check controller route() method handles these action values (lines 274-278)

### Issue: User redirected to login when submitting forms
**Cause:** Session lost between POST and redirect
**Solution:**
1. Verify session_start() called in dashboard.php (line 10)
2. Verify session cookies are not being deleted
3. Check browser allows session cookies

---

## Database Schema Verification

Required tables for payroll module:
- `payroll_run` - stores payroll run records
- `payroll_run_employee` - stores employee payroll details
- `payroll_approval` - stores approval workflow records
- `payroll_audit_log` - stores audit trail

---

## Code References

**dashboard.php POST Handler:** Lines 18-59
**PayrollRunController::route():** Lines 245-297
**View Session Check:** Lines 1-120
**Form Examples:** Lines 584, 663, 798, 822, 625-650

---

Last Updated: 2025-XX-XX
Status: Ready for Testing
