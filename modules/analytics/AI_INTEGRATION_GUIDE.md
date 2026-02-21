# AI Analytics Integration Guide

## Overview

This document describes how the AI predictive models have been integrated into the Analytics module for HR4 (Hospital HR Management System).

## Architecture

### Components

1. **AI Server (Flask)**
   - Location: `c:\NEWXAMPP\htdocs\public_html\AI_SERVER`
   - Port: 8000
   - Models: Attrition, Pay Forecast, Promotion, Payroll Anomalies
   - Status: Requires initialization (`python app.py`)

2. **PHP Integration Layer**
   - `AIIntegrationService.php` - Main service class for AI communication
   - `PredictionActionMapping.php` - Maps predictions to business actions
   - API endpoints in `api.php` for frontend access

3. **Frontend Views**
   - `predictive_insights.php` - Combined AI dashboard
   - `attrition_risk.php` - Employee retention risks
   - `promotion_candidates.php` - Promotion-ready employees
   - `payroll_anomalies.php` - Suspicious transactions
   - `index.php` - Analytics module hub

## AI Models

### 1. Attrition Prediction Model
- **Type**: Logistic Regression
- **Output**: Probability (0-1) of employee leaving
- **Features Used**:
  - Age, Tenure, Base Salary
  - Overtime hours, Attendance score
  - Performance rating, HMO claims
  - Role and Department encodings
- **Business Use**: React to at-risk employees with retention strategies

### 2. Pay Forecast Model
- **Type**: Regression
- **Output**: Predicted next month net pay
- **Use Cases**: Budget planning, cash flow forecasting, salary trend analysis

### 3. Promotion Prediction Model
- **Type**: Logistic Regression
- **Output**: Probability (0-1) of promotion readiness
- **Use Cases**: Succession planning, talent development, career pathing

### 4. Overtime Anomaly Detection
- **Type**: Isolation Forest
- **Output**: Boolean flag for unusual patterns
- **Use Cases**: Identify unusual work patterns, workload balancing

### 5. Payroll Anomaly Detection
- **Type**: Binary Classifier
- **Output**: Anomaly score + flag
- **Use Cases**: Fraud detection, compliance checking, payroll audit

## API Endpoints

### Dashboard Integration

```
GET /modules/analytics/api.php?action=getPredictiveInsights
```
Returns combined dashboard with:
- Attrition risks (top 5)
- Promotion candidates (top 5)
- Payroll anomalies (top 5)
- 3-month pay forecast

### Attrition Risk

```
GET /modules/analytics/api.php?action=getAttritionRisk&department=ID&limit=10
```
Returns at-risk employees sorted by risk score with recommendations.

### Promotion Candidates

```
GET /modules/analytics/api.php?action=getPromotionCandidates&department=ID&limit=10
```
Returns employees ready for promotion with readiness scores > 30%.

### Payroll Anomalies

```
GET /modules/analytics/api.php?action=getPayrollAnomalies&dateRange=30&department=ID&threshold=0.7
```
Returns suspicious payroll transactions above threshold.

### Pay Forecast

```
GET /modules/analytics/api.php?action=getPayForecast&department=ID&months=3
```
Returns 3-month payroll forecast for budget planning.

### AI Server Health

```
GET /modules/analytics/api.php?action=getAIServerHealth
```
Returns AI server status and available models.

## Database Tables

Added migration: `database/migrations/add_ai_predictions_tables.sql`

### Key Tables

1. **ai_predictions** - Cache AI predictions for historical analysis
2. **ai_prediction_history** - Track prediction accuracy over time
3. **payroll_anomalies** - Store detected anomalies with resolution tracking
4. **attrition_alerts** - Track attrition alerts and follow-up actions
5. **promotion_recommendations** - Record promotion tracking
6. **payroll_forecasts** - Historical forecast data

## Business Actions Mapping

See `config/PredictionActionMapping.php` for:

- **Attrition**: Intervention levels (Critical → Immediate, High → 1 week, etc.)
- **Promotion**: Fast-track actions and development plans
- **Anomalies**: Investigation priorities and compliance steps
- **KPI Impact**: Affected metrics for each prediction type
- **Success Metrics**: How to measure action plan effectiveness

### Example: Attrition Risk Action Plan

```php
$actions = PredictionActionMapping::getAttritionActions(0.85, 'Critical');
// Returns:
// - Immediate retention intervention
// - Compensation review
// - Career development plan
```

## Security Considerations

1. **Authentication**: All endpoints require valid JWT token
2. **Data Privacy**: AI predictions cached locally, not sent to external services
3. **Access Control**: Role-based visibility (HR, Finance, Managers)
4. **Audit Trail**: AI predictions logged in `ai_prediction_history`

## Performance

1. **Caching**: Predictions cached for 1 hour to reduce AI server load
2. **Batch Processing**: Supports employee list predictions in single API call
3. **Scalability**: Designed for 1000+ employee predictions per run

## Setup Instructions

### 1. Initialize AI Server

```bash
cd c:\NEWXAMPP\htdocs\public_html\AI_SERVER
pip install -r requirements.txt
python app.py
```

The server starts on http://localhost:8000

### 2. Create Database Tables

```sql
-- Run migration
source database/migrations/add_ai_predictions_tables.sql;
```

### 3. Access Analytics Module

Navigate to: `/modules/analytics/index.php`

## Usage Examples

### Display Attrition Risk in Dashboard

```php
$ai = new AIIntegrationService();
$atRisk = $ai->getAttritionRiskSummary($departmentId, 10);

foreach ($atRisk as $emp) {
    echo $emp['name'] .': ' . $emp['attrition_risk'] . '% risk';
    echo $emp['recommendation'];
}
```

### Get Action Plan for Employee

```php
$actions = PredictionActionMapping::getAttritionActions(0.75, 'High');
$plan = PredictionActionMapping::generateActionPlan(
    $employeeId, 
    $prediction, 
    'attrition', 
    $actions
);
```

### Check AI Server Online

```php
$ai = new AIIntegrationService();
if ($ai->isServerOnline()) {
    // Safe to fetch predictions
}
```

## Troubleshooting

### AI Server Not Starting
- Check Python 3.10+ is installed: `python --version`
- Verify all requirements installed: `pip install -r requirements.txt`
- Check port 8000 is available

### No Data Appearing in Predictions
- Verify AI server is running on port 8000
- Check database has employee data with required fields
- Review cache directory permissions: `logs/ai_cache`

### Stale Predictions
- Cache duration is 1 hour (configurable in AIIntegrationService)
- Manually refresh with browser F5 or API call

### Model Loading Errors
- Verify all .joblib files exist in `AI_SERVER/models/`
- Check JSON mapping files (role_map.json, dept_map.json)

## Configuration

### Modify AI Server URL

```php
$ai = new AIIntegrationService('http://your-ai-server:8000');
```

### Adjust Cache Duration

```php
$ai->cacheDuration = 1800; // 30 minutes instead of 1 hour
```

### Disable Caching

```php
$ai->enableCache = false;
```

## Future Enhancements

1. Real-time model retraining based on outcomes
2. Custom model thresholds by department/role
3. Automated action execution (emails, forms, alerts)
4. Prediction confidence intervals
5. Explainability (why this prediction)
6. A/B testing of interventions
7. Mobile app integration
8. Advanced visualization dashboards

## Support

For issues or questions:
1. Check AI server logs: `python app.py` output
2. Check PHP logs: `logs/` directory
3. Review error_log in browser developer tools
4. Verify database schema matches migrations

---

**Last Updated**: February 17, 2026
**Created For**: HR4 Analytics Module v1.0
