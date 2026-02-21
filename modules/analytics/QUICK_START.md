# Quick Start Guide - AI Analytics System

**Get everything running in 5 minutes**

---

## 🚀 Quick Start Checklist

### ✅ Step 1: Start the AI Server (30 seconds)

**Open PowerShell and run:**

```powershell
cd "c:\NEWXAMPP\htdocs\public_html\AI_SERVER"
python app.py
```

**Expected Output:**
```
 * Serving Flask app 'create_app'
 * Running on http://0.0.0.0:8000
```

**Status**: ✅ AI Server Online (keep this window open)

---

### ✅ Step 2: Create Database Tables (1 minute)

**Open MySQL Workbench or phpMyAdmin:**

1. Go to: `http://localhost/phpmyadmin`
2. Select database: `hospital_hr_db` (or your DB name)
3. Click "SQL" tab
4. Copy-paste this entire block:

```sql
-- ===== AI Predictions Cache Tables =====

CREATE TABLE IF NOT EXISTS ai_predictions (
    prediction_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    prediction_type VARCHAR(50) NOT NULL,
    prediction_data JSON NOT NULL,
    confidence_score DECIMAL(5,2),
    prediction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    action_taken TINYINT DEFAULT 0,
    action_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_employee (employee_id),
    INDEX idx_type (prediction_type),
    INDEX idx_date (prediction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ai_prediction_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    prediction_type VARCHAR(50) NOT NULL,
    predicted_value DECIMAL(10,4),
    actual_value DECIMAL(10,4),
    accuracy DECIMAL(5,2),
    prediction_date DATE,
    evaluation_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_employee (employee_id),
    INDEX idx_type (prediction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll_anomalies (
    anomaly_id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_run_id INT NOT NULL,
    employee_id INT NOT NULL,
    anomaly_type VARCHAR(100),
    anomaly_score DECIMAL(5,2),
    anomaly_flag TINYINT,
    severity VARCHAR(20),
    description TEXT,
    reviewed TINYINT DEFAULT 0,
    resolved TINYINT DEFAULT 0,
    resolution_notes TEXT,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_employee (employee_id),
    INDEX idx_payroll_run (payroll_run_id),
    INDEX idx_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attrition_alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    risk_score DECIMAL(5,2),
    risk_level VARCHAR(20),
    recommendation TEXT,
    alert_status VARCHAR(20) DEFAULT 'active',
    assigned_to INT,
    action_plan TEXT,
    follow_up_date DATE,
    outcome VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee (employee_id),
    INDEX idx_status (alert_status),
    INDEX idx_risk_level (risk_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS promotion_recommendations (
    recommendation_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    promotion_probability DECIMAL(5,2),
    readiness_score DECIMAL(5,2),
    recommended_position VARCHAR(100),
    recommended_department_id INT,
    recommendation_date DATE,
    action_status VARCHAR(20) DEFAULT 'pending',
    feedback TEXT,
    decision_date DATE,
    decision_made_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_employee (employee_id),
    INDEX idx_status (action_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll_forecasts (
    forecast_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    department_id INT,
    forecast_period VARCHAR(50),
    forecasted_net_pay DECIMAL(12,2),
    forecasted_gross_pay DECIMAL(12,2),
    confidence_level DECIMAL(5,2),
    forecast_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_employee (employee_id),
    INDEX idx_department (department_id),
    INDEX idx_period (forecast_period)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

5. Click "Execute" button at bottom

**Status**: ✅ Tables Created (you only need to do this once)

---

### ✅ Step 3: Access the Analytics Hub (2 minutes)

**Open your browser:**

```
http://localhost/modules/analytics/index.php
```

**You should see:**
- Purple gradient background
- Analytics Hub header
- Sidebar with navigation menu
- Grid of cards for different views

**Status**: ✅ Analytics Hub Loaded

---

## 📊 Quick Navigation

### From the Hub, Click On:

#### 1. **Predictive Insights** (Main Dashboard)
- Shows all AI predictions in one place
- Attrition risks, promotions, anomalies
- Pay forecast chart
- AI server status indicator

#### 2. **Attrition Risk** (Who Might Leave?)
- List of at-risk employees
- Risk percentages in red/orange
- Personalized recommendations
- Filter by department

#### 3. **Promotion Candidates** (Who's Ready?)
- Cards showing promotion-ready employees
- Readiness scores, promotion probability
- Tenure information
- Buttons to create recommendations

#### 4. **Payroll Anomalies** (Fraud Detection)
- Table of suspicious transactions
- Anomaly scores
- Severity levels (Critical/High/Medium)
- Review & resolve buttons

---

## 🔍 Example Workflows

### Workflow 1: React to At-Risk Employees
1. Go to "Attrition Risk" view
2. See employees marked "Critical" (red)
3. Read "Recommendation" column
4. Schedule meeting with employee
5. Set follow-up plan

### Workflow 2: Approve Promotions
1. Go to "Promotion Candidates"
2. Filter high readiness scores (>75%)
3. Click "Create Recommendation" button
4. Complete promotion workflow
5. Document action taken

### Workflow 3: Investigate Payment Issues
1. Go to "Payroll Anomalies"
2. Sort by "Critical" severity
3. Click "Review" button
4. Check employee's historical pay
5. Approve or investigate discrepancy

---

## 🛠️ Troubleshooting

### ❌ Problem: "AI Service: Offline"
**Solution:**
- Make sure AI Server is running (`python app.py` in PowerShell)
- Verify port 8000 is not in use
- Check Flask output for errors

### ❌ Problem: No employee data showing
**Solution:**
- Make sure employees exist in database
- Verify employee records have all required fields
- Check database connection is working

### ❌ Problem: Page shows blank or errors
**Solution:**
- Hard refresh browser: `Ctrl + F5`
- Check browser console: `F12` → Console tab
- Look for error messages in red

### ❌ Problem: Tables don't exist in database
**Solution:**
- Copy all SQL from Step 2 above
- Paste into phpMyAdmin SQL tab
- Make sure you selected correct database first
- Click Execute button

---

## 📱 Access from Other Devices

**From another computer on your network:**

Replace `localhost` with your server IP:
```
http://192.168.1.XXX/modules/analytics/index.php
```

(Ask IT for your server's IP address)

---

## ⏱️ Keeping Everything Running

### For Development/Testing:
- Keep PowerShell window with AI Server running
- Can close and reopen as needed
- Database tables stay permanent

### For Production:
- Schedule AI Server to start on system boot
- Create Windows Task Scheduler entry
- Monitor AI Server health regularly

---

## 📊 Sample Test Data

To test without real employee data:

1. Go to **Main Dashboard** view
2. It loads with sample/demo data showing
3. Or use existing employees in your database

---

## 🎯 Key Features to Try

✅ **View Dashboard**
- Click any card to drill down
- See live KPIs and trends

✅ **Check Attrition**
- See risk scores and recommendations
- Filter by department

✅ **Find Promotions**
- Identify succession ready candidates
- See readiness percentages

✅ **Review Anomalies**
- Spot unusual payroll transactions
- Investigate high-risk items

✅ **Check Server Status**
- Bottom of Predictive Insights shows AI health
- Green = Online, Red = Offline

---

## 💡 Tips & Tricks

1. **Bookmark the Hub**: Save `http://localhost/modules/analytics/index.php` in favorites

2. **Filter Results**: Every view has filters for Department, Date Range, etc.

3. **Export Data**: Look for export buttons to download reports as PDF/Excel

4. **Refresh Predictions**: Press "Refresh" or "F5" to get fresh data from AI model

5. **Check Timeline**: All predictions reset every hour (cache refresh)

---

## 📞 Need Help?

**Check these files:**
- `modules/analytics/AI_INTEGRATION_GUIDE.md` - Complete technical documentation
- `modules/analytics/IMPLEMENTATION_SUMMARY.md` - What was built and how

**Look in these logs:**
- Browser Console: `F12` then "Console" tab
- AI Server: Terminal window showing Flask output
- PHP Logs: `logs/` folder in root directory

---

## ✨ That's it!

You're now using an **AI-powered HR Analytics System** with:
- 📊 Live predictions of employee attrition
- 🎯 Automatic promotion candidate identification  
- 🔍 Payroll anomaly detection
- 💰 3-month payroll forecasting
- 📱 Mobile-responsive interface
- 🔒 Secure with authentication

**Happy analyzing!** 🚀

---

*Quick Start v1.0 - February 17, 2026*
