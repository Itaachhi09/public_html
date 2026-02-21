# Payslip Management - Quick Setup & Test Guide

## ✅ Setup Steps (Do This First!)

### Step 1: Apply Database Migration (REQUIRED)
Run this SQL in phpMyAdmin or MySQL command line:

```sql
-- Copy entire content from:
-- database/migrations/add_payslip_management_tables.sql
-- And execute in your MySQL client
```

**Or via command line:**
```bash
cd c:\xampp\htdocs\public_html
mysql -u root -p public_html < database/migrations/add_payslip_management_tables.sql
```

**Verify tables created:**
```sql
SHOW TABLES LIKE 'payslip%';
-- Should show:
-- payslip_records
-- payslip_email_settings
-- payslip_email_logs
```

### Step 2: Verify Files in Place
Check these files exist:
- ✓ `modules/payroll/controllers/PayslipController.php`
- ✓ `modules/payroll/models/PayslipRecord.php`
- ✓ `modules/payroll/models/PayslipEmailSetting.php`
- ✓ `modules/payroll/models/PayslipEmailLog.php`
- ✓ `modules/payroll/views/payslip_management.php` (UPDATED)
- ✓ `dashboard.php` (UPDATED)

### Step 3: Clear Any Caches
- Clear browser cache (Ctrl+Shift+Delete)
- Clear PHP opcode cache if applicable
- Restart XAMPP (stop/start Apache)

---

## 🧪 Quick Test (5 Minutes)

### Test 1: Generate All Payslips
**Goal:** Create payslip records in database

1. Open browser: `http://localhost/public_html/dashboard.php`
2. Navigate: **Payroll > Payslip Management**
3. Under "Generate Payslips" section:
   - Select payroll period from dropdown
   - Click **"Generate All Payslips"** button
4. **Expected:** 
   - ✓ Green success message appears
   - ✓ Message: "Payslips generated successfully! Generated: 8, Skipped: 0"
   - ✓ No page navigation/404
   - ✓ Page stays in Payslip Management

5. **Verify in Database:**
```sql
SELECT COUNT(*) as total FROM payslip_records;
-- Should show: 8 (or your employees count)

SELECT * FROM payslip_records LIMIT 5;
-- Should show payslip records with payslip_number, gross_pay, net_pay
```

### Test 2: Generate & Send via Email
**Goal:** Create payslips AND log emails

1. Still on Payslip Management page
2. Select DIFFERENT payroll period (if available)
3. Click **"Generate & Send via Email"** button
4. **Expected:**
   - ✓ Green success message
   - ✓ Message shows: "Payslips generated and emails sent! Sent: X, Failed: Y"
   - ✓ No errors or 404
   - ✓ Page remains in module

5. **Verify in Database:**
```sql
SELECT COUNT(*) FROM payslip_email_logs;
-- Should have entries

SELECT status, COUNT(*) as count FROM payslip_email_logs GROUP BY status;
-- Shows sent/failed breakdown
```

### Test 3: Update Email Settings
**Goal:** Customize email template

1. Scroll to **"Email Distribution Settings"** section
2. Update these fields:
   - Email From Address: `payroll@mycompany.com`
   - Email Subject: `My Custom Payslip - [PERIOD]`
   - Email Body Template: Add your custom text
3. Click **"Update Email Settings"** button
4. **Expected:**
   - ✓ Green success message: "Email settings updated successfully"
   - ✓ Page stays in module
   - ✓ No 404

5. **Verify in Database:**
```sql
SELECT * FROM payslip_email_settings;
-- Should show your custom values
```

### Test 4: Reset Email Settings
**Goal:** Restore defaults

1. Still in "Email Distribution Settings" section
2. Click **"Reset"** button
3. **Expected:**
   - ✓ Green success message: "Email settings reset to defaults"
   - ✓ Page stays in module

4. **Verify in Database:**
```sql
SELECT setting_key, setting_value FROM payslip_email_settings;
-- Should show default template restored
```

---

## ✅ Success Criteria

### All 4 Tests Pass When:
- ✓ No 404 errors on any button click
- ✓ Green success messages appear
- ✓ Page stays in "Payslip Management" view
- ✓ Database records created
- ✓ Audit trail updated
- ✓ Buttons visible and clickable
- ✓ Forms submit properly
- ✓ Redirects happen within module

### If You See 404:
1. **Check:** Migration applied successfully
2. **Check:** PayslipController.php exists
3. **Check:** Tables created: `SHOW TABLES LIKE 'payslip%';`
4. **Check:** browser/server logs for errors
5. **Check:** Restart XAMPP Apache

### If No Message Displays:
1. Check browser developer console (F12)
2. Check browser URL after submit
3. Should be: `http://localhost/public_html/dashboard.php?ref=payroll&page=payslip_management&payroll_id=1`
4. Not: `http://localhost/payslip_management_handler.php`

---

## 📋 Database Verification Queries

### See All Payslips Generated:
```sql
SELECT 
  id,
  payslip_number,
  employee_id,
  gross_pay,
  net_pay,
  generated_at,
  status
FROM payslip_records
ORDER BY generated_at DESC
LIMIT 20;
```

### See Email Logs:
```sql
SELECT 
  employee_id,
  email_address,
  status,
  sent_at,
  error_message
FROM payslip_email_logs
ORDER BY sent_at DESC
LIMIT 20;
```

### See Email Settings Applied:
```sql
SELECT setting_key, setting_value FROM payslip_email_settings;
```

### See Audit Trail:
```sql
SELECT 
  action,
  description,
  timestamp
FROM payroll_audit_trail
WHERE action LIKE '%PAYSLIP%' OR action LIKE '%EMAIL%'
ORDER BY timestamp DESC
LIMIT 20;
```

---

## 🚨 Troubleshooting

### Issue: "404 Not Found" on Button Click
**Solution:**
1. Verify migration applied: `SHOW TABLES LIKE 'payslip%';`
2. Verify PayslipController.php exists
3. Check dashboard.php has payslip_management handler
4. Restart XAMPP

### Issue: "Database Connection Error"
**Solution:**
1. Check Database.php configuration
2. Verify MySQL is running
3. Check credentials in config
4. Run: `mysql -u root -p` to test connection

### Issue: "No Payslips Generated"
**Solution:**
1. Select valid payroll period
2. Verify payroll status is 'Processed'
3. Check employees exist for payroll
4. Run: `SELECT * FROM payroll_runs;` to see available payrolls

### Issue: "Emails Not Sending"
**Solution:**
1. Check ReliableEmailService configuration
2. Verify email settings in database
3. Check email_logs table for error messages
4. Review server mail configuration

### Issue: "Message Not Displaying"
**Solution:**
1. Check browser developer tools (F12)
2. Verify session is active
3. Check for PHP errors in browser console
4. Verify page displays any content at all

---

## 🎯 After Tests Pass

### Production Checklist:
- [ ] All 4 tests passed
- [ ] Database has records
- [ ] Email logs show activity
- [ ] Audit trail updated
- [ ] No errors in PHP error log
- [ ] No errors in browser console
- [ ] Payslips accessible via view
- [ ] Settings persist after logout/login

### Ready for Live Use:
- [ ] Users can generate payslips
- [ ] Users can email payslips
- [ ] Users can customize settings
- [ ] Users can reset settings
- [ ] All within dashboard
- [ ] No standalone handler files
- [ ] Full MVC compliance
- [ ] Clean URL structure

---

## 📞 Support

**Common errors and locations:**
- Form not routing → Check dashboard.php lines 50-80
- Controller not found → Check PayslipController.php exists
- Database errors → Check migration applied
- Message not showing → Check payslip_management.php lines 587-612
- 404 on old handler → Old handler at modules/payroll/payslip_management_handler.php (now unused)

**Files to review if issues:**
1. `dashboard.php` - POST handler
2. `PayslipController.php` - Business logic
3. `payslip_management.php` - View and forms
4. Migration file - Database tables
5. Model classes - Data access

---

## Quick Reference

| Action | Form Value | Expected Result |
|--------|-----------|-----------------|
| Generate All | `action=generate_all` | Green ✓ "Generated: X" |
| Send Email | `action=generate_and_email` | Green ✓ "Sent: X, Failed: Y" |
| Update Settings | `action=update_email_settings` | Green ✓ "Updated successfully" |
| Reset Settings | `action=reset_email_settings` | Green ✓ "Reset to defaults" |

---

## ✨ Success Message Format

```
Green Alert Box:
✓ Payslips generated successfully! Generated: 8, Skipped: 0

Green Alert Box:
✓ Payslips generated and emails sent! Sent: 8, Failed: 0

Green Alert Box:
✓ Email settings updated successfully

Green Alert Box:
✓ Email settings reset to defaults
```

**All messages auto-clear after display. Multiple test runs will show multiple message displays (each for 1 page view).**

---

Good luck! This should give you a fully functional payslip management system with no 404 errors! 🚀
