# 🎯 Analytics Dashboard Quick Start Guide

## Accessing the Analytics Dashboard

### URL
```
http://localhost/modules/analytics/
```

### Requirements
- You must be logged in to the HR system
- Your account must have Analytics permission
- Browser must support JavaScript and Chart.js

---

## 📊 Main Dashboard Overview

The main dashboard contains **real-time key metrics** from all modules:

### Headcount Section
Shows your current employee metrics:
- **Total Employees**: Current headcount across all departments
- **Active Employees**: Employees with active status
- **Inactive Employees**: On leave or inactive status
- **By Department**: Quick distribution view

**How to use it:** Get instant overview of workforce size and status

---

### Payroll Section
Shows last 30 days payroll data (configurable):
- **Gross Payroll**: Total payroll disbursement
- **Deductions**: Tax, benefits, other deductions
- **Net Pay**: Actual amount paid to employees
- **Average Salary**: Across all employees

**How to use it:** Monitor payroll spending and budget impact

---

### HMO & Benefits Section
Shows current health insurance coverage:
- **Total Enrolled**: Employees with active HMO coverage
- **Enrollment Rate**: % of workforce covered
- **Monthly Cost**: Total HMO spending
- **By Provider**: Distribution across HMO providers

**How to use it:** Track benefit costs and coverage rates

---

### Compliance Section
Shows upcoming compliance events:
- **Expiring Contracts**: Count of contracts expiring soon
- **Document Expiry**: Critical documents needing renewal
- **Compliance Status**: Overall compliance percentage

**How to use it:** Identify urgent actions needed

---

### Recent Actions
Shows latest compliance and HR events:
- Contract renewals due
- HMO enrollment reviews
- Document updates needed

**How to use it:** Stay on top of pending actions

---

## 🔄 Using Dashboard Tabs

### Click on Blue Tab Headers to View Detailed Analytics

#### 📈 Payroll Trends
**Shows:** Monthly payroll costs, spending trends, top earners, salary distribution

**How to use:**
1. Click "Payroll Trends" tab
2. View monthly trend graph
3. See cost breakdown by category
4. Check top 10 earners by department
5. Analyze salary ranges

**Use for:** Budget planning, cost control, salary benchmarking

---

#### 💰 Compensation Analysis
**Shows:** Cost by department, pay grades, benefit costs, salary equity

**How to use:**
1. Click "Compensation Analysis" tab
2. Select department from dropdown
3. View compensation breakdown
4. Analyze pay grade distribution
5. Check benefit spending

**Use for:** Compensation planning, equity analysis, budget allocation

---

#### 👥 Headcount Analytics
**Shows:** Employees by department, employment type, location, movement trends

**How to use:**
1. Click "Headcount Analytics" tab
2. View distribution charts
3. Monitor movement trends
4. Analyze employment type mix
5. Check location-wise headcount

**Use for:** Workforce planning, org structure analysis, recruitment planning

---

#### 🏥 HMO Insights
**Shows:** Enrollment trends, provider analysis, claim data, cost per employee

**How to use:**
1. Click "HMO Insights" tab
2. View enrollment by provider
3. Check claim trends
4. Analyze cost patterns
5. Monitor coverage gaps

**Use for:** Benefits cost management, provider performance review

---

#### ✅ Compliance Tracking
**Shows:** Contract expiry, documents expiring, compliance actions, alerts

**How to use:**
1. Click "Compliance Tracking" tab
2. View contracts expiring in 30/60/90 days
3. Check document expiry status
4. Review upcoming compliance actions
5. Expand details for specific contracts/documents

**Use for:** Risk mitigation, compliance audit, regulatory alignment

---

#### 🔄 Movement Analytics
**Shows:** Employee joins, departures, transfers, promotion rates

**How to use:**
1. Click "Movement Analytics" tab
2. View joiners (new hires)
3. Monitor leavers (departures)
4. Check transfers and promotions
5. Analyze movement rate/turnover

**Use for:** Attrition analysis, recruitment planning, career progression

---

#### 📍 Department KPIs
**Shows:** Metrics specific to selected department

**How to use:**
1. Click "Department KPIs" tab
2. Select a department from dropdown
3. View all KPIs for that department
4. Compare across departments
5. Drill down into details

**Use for:** Department performance review, cross-departmental comparison

---

## 🔧 Using Filters

Most views support filters at the top:

### Date Range
- **Default**: Last 30 days
- **Options**: 7, 14, 30, 60, 90 days or custom date range
- **Use for**: Comparing different time periods

### Department Filter
- **Default**: All departments
- **Options**: Select specific department
- **Use for**: Department-specific analysis

### Employment Type Filter
- **Options**: Contract, Permanent, Casual, etc.
- **Use for**: Analyzing specific employee categories

### Status Filter
- **Options**: Active, Inactive, On Leave, etc.
- **Use for**: Employee status-based analysis

---

## 📥 Exporting Data

### Export Dashboard Data

**Option 1: Export Current View**
1. Find the **"Export"** button at top-right of dashboard
2. Select format:
   - **PDF** - Formatted report with charts
   - **Excel** - Data in spreadsheet
   - **CSV** - Comma-separated values
3. File downloads to your computer

**Option 2: Export Specific Report**
1. Go to desired tab (e.g., Payroll Trends)
2. Click **"Export"** button in that section
3. Choose format
4. Download file

**Use cases:**
- Email reports to stakeholders
- Archive for compliance
- Import into other tools
- Presentation preparation

---

## 📱 Viewing on Different Screens

### Desktop
- Full dashboard with all metrics visible
- Best for detailed analysis
- Hover over charts for details

### Tablet
- Most features work
- May need to scroll horizontally
- Charts remain responsive

### Mobile
- Basic metrics visible
- Export functionality available
- Charts display vertically
- Recommended: Export and view later

---

## 🔍 Understanding the Charts

### Line Charts (Trends)
- **X-axis**: Time (e.g., months)
- **Y-axis**: Value (e.g., salary amount)
- **Hover Mouse**: See exact values
- **Trend**: Shows increase/decrease over time

**How to read:** Look at slope - upward = increasing, downward = decreasing

---

### Bar Charts (Comparisons)
- **X-axis**: Categories (e.g., departments)
- **Y-axis**: Values (e.g., employee count)
- **Higher bars**: Higher values
- **Compare bars**: Find top/bottom performers

**How to read:** Taller bars = more employees/higher costs

---

### Pie Charts (Distribution)
- **Slices**: Different categories
- **Size**: Relative proportion
- **Numbers**: Count or percentage
- **Hover**: See exact values

**How to read:** Larger slices = larger portion of whole

---

### Histograms (Distribution)
- **X-axis**: Range bands (e.g., salary bands)
- **Y-axis**: Count of employees
- **Shows**: How data is distributed

**How to read:** See concentration - where most values fall

---

## 🚨 Understanding Alerts

### Red Alerts
**Meaning**: Immediate action required
**Examples**: Contracts expiring in < 3 days

**What to do**: Take action immediately

---

### Yellow/Orange Alerts
**Meaning**: Attention needed soon
**Examples**: Documents expiring in < 30 days

**What to do**: Plan action within 1-2 weeks

---

### Green/Info
**Meaning**: Good status
**Examples**: HMO enrollment rate > 90%

**What to do**: Monitor but no immediate action needed

---

## 🤖 AI Predictions (Optional)

If AI Server is running, you'll see additional insights:

### At-Risk Employees
Employees likely to leave based on historical patterns

**Action**: Consider retention strategies

---

### Promotion Candidates
High-potential employees ready for advancement

**Action**: Plan career development conversations

---

### Payroll Anomalies
Unusual payroll patterns detected

**Action**: Investigate unusual calculations

---

### Pay Forecasting
Predicted payroll for next 3 months

**Action**: Use for budgeting

---

## 📊 Sample Analysis Workflows

### Workflow 1: Monthly Payroll Review
1. Open Dashboard → Check Payroll Summary
2. Click "Payroll Trends" tab
3. Verify gross, deductions, net amounts
4. Check for anomalies
5. Export monthly summary report
6. Share with Finance

---

### Workflow 2: Department Performance Review
1. Open Dashboard
2. Click "Department KPIs" tab
3. Select department from dropdown
4. Review all metrics
5. Compare across departments
6. Export department comparison
7. Present to management

---

### Workflow 3: Compliance Audit
1. Open Dashboard → Check Compliance Section
2. Click "Compliance Tracking" tab
3. Export contracts expiring in 90 days
4. Export documents expiring in 90 days
5. Send to compliance team
6. Track completion of renewals

---

### Workflow 4: Compensation Planning
1. Click "Compensation Analysis" tab
2. Review "Cost by Department"
3. Check "Pay Grade Distribution"
4. Analyze salary ranges
5. Review "Salary Equity" metrics
6. Plan adjustments based on equity
7. Export report for board approval

---

### Workflow 5: HR Planning
1. Click "Headcount Analytics" tab
2. Review headcount by department
3. Check movement trends
4. Monitor attrition rate
5. Plan recruitment for high-turnover departments
6. Export headcount forecast
7. Share with operations

---

## ❓ FAQs

### Q: Why is a chart showing no data?
**A:** 
- Check date range (may exclude all data)
- Verify department filter (may be empty)
- Confirm database has records for that period
- Check browser console for errors (F12)

---

### Q: Can I refresh data without reloading page?
**A:** Yes, click the **"Refresh"** button next to Filters

---

### Q: How often is data updated?
**A:** Real-time - data is fetched from database each time you view/refresh

---

### Q: Can I customize which metrics display?
**A:** Currently, all metrics display in standard layout. Contact IT for custom dashboards.

---

### Q: Why are some employees missing from reports?
**A:** Check:
- Employee status (Active/Inactive)
- Employment type filter
- Department filter
- Date range for movement reports

---

### Q: How do I troubleshoot errors?
**A:**
1. Open Browser DevTools (F12)
2. Go to Console tab
3. Look for error messages
4. Check Network tab for failed API calls
5. Note error details and contact support

---

### Q: Can I print the dashboard?
**A:** Yes:
1. Use browser print function (Ctrl+P)
2. Select "Save as PDF" 
3. Or use Dashboard Export button

---

## 🔐 Security Notes

✅ All data access is authenticated  
✅ Only your department data visible (may vary by role)  
✅ All API calls encrypted (HTTPS)  
✅ Session timeout after 30 minutes of inactivity  
✅ Actions are logged for audit trail  

---

## 📞 Need Help?

- **Technical Issues**: Check `/logs/error.log`
- **Data Questions**: Contact HR Department
- **Access Issues**: Contact IT Support
- **Feature Requests**: Submit through IT Portal

---

**Your analytics dashboard is now ready to use!**  
Start exploring your HR data today. 🚀

