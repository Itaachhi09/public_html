<?php
/**
 * Analytics AI Configuration
 * Customize AI integration settings here
 * 
 * Copy this file to: config/AIConfig.php
 * Then modify the settings below
 */

class AIConfig
{
    // ===== AI SERVER SETTINGS =====
    
    /**
     * AI Server URL
     * Default: http://localhost:8000
     * For remote server: http://192.168.1.100:8000
     */
    const AI_SERVER_URL = 'http://localhost:8000';
    
    /**
     * API Request Timeout (seconds)
     * Default: 30 seconds
     * Increase if network is slow
     */
    const AI_REQUEST_TIMEOUT = 30;
    
    /**
     * Enable/Disable AI Predictions
     * Set to false to use demo data only
     */
    const AI_PREDICTIONS_ENABLED = true;
    
    // ===== CACHING SETTINGS =====
    
    /**
     * Enable prediction caching
     * Reduces AI server load significantly
     */
    const CACHE_ENABLED = true;
    
    /**
     * Cache duration in seconds
     * 3600 = 1 hour
     * 1800 = 30 minutes
     * 300 = 5 minutes
     */
    const CACHE_DURATION = 3600;
    
    /**
     * Cache directory
     * Must be writable by web server
     */
    const CACHE_DIR = __DIR__ . '/../../logs/ai_cache';
    
    // ===== RISK THRESHOLDS =====
    
    /**
     * Attrition Risk Thresholds
     * These determine the risk level (Critical, High, Medium, Low)
     */
    const ATTRITION_CRITICAL_THRESHOLD = 0.7;  // 70%
    const ATTRITION_HIGH_THRESHOLD = 0.5;      // 50%
    const ATTRITION_MEDIUM_THRESHOLD = 0.3;    // 30%
    
    /**
     * Promotion Readiness Threshold
     * Minimum score to show as candidate
     */
    const PROMOTION_MIN_THRESHOLD = 0.3;  // 30%
    
    /**
     * Payroll Anomaly Thresholds
     */
    const ANOMALY_CRITICAL_THRESHOLD = 0.9;  // 90%
    const ANOMALY_HIGH_THRESHOLD = 0.7;      // 70%
    const ANOMALY_MEDIUM_THRESHOLD = 0.5;    // 50%
    
    /**
     * Anomaly detection sensitivity (0-1)
     * Use this as default filter: getPayrollAnomalies(..., $threshold)
     */
    const ANOMALY_DEFAULT_THRESHOLD = 0.7;
    
    // ===== DISPLAY SETTINGS =====
    
    /**
     * Number of records to show by default
     */
    const DEFAULT_LIMIT = 10;
    
    /**
     * Maximum records per request
     */
    const MAX_LIMIT = 100;
    
    /**
     * Default date range for reports (days)
     */
    const DEFAULT_DATE_RANGE = 30;
    
    /**
     * Default number of forecast months
     */
    const DEFAULT_FORECAST_MONTHS = 3;
    
    // ===== NOTIFICATION SETTINGS =====
    
    /**
     * Enable email alerts for critical predictions
     * Requires: EmailService configured
     */
    const ALERTS_ENABLED = false;
    
    /**
     * Email alerts for critical attrition risk
     */
    const ALERT_CRITICAL_ATTRITION = true;
    
    /**
     * Email alerts for payroll anomalies
     */
    const ALERT_PAYROLL_ANOMALY = true;
    
    /**
     * Alert recipient emails
     * Format: ['email1@company.com', 'email2@company.com']
     */
    const ALERT_RECIPIENTS = [
        'hr@company.com',
        'finance@company.com'
    ];
    
    // ===== FEATURE FLAGS =====
    
    /**
     * Enable Attrition Risk Features
     */
    const FEATURE_ATTRITION = true;
    
    /**
     * Enable Promotion Candidate Features
     */
    const FEATURE_PROMOTION = true;
    
    /**
     * Enable Payroll Anomaly Detection
     */
    const FEATURE_ANOMALY = true;
    
    /**
     * Enable Pay Forecasting
     */
    const FEATURE_FORECAST = true;
    
    // ===== DATABASE SETTINGS =====
    
    /**
     * Store predictions in database
     * Enables historical analysis and accuracy tracking
     */
    const DB_STORE_PREDICTIONS = true;
    
    /**
     * Auto-cleanup old predictions
     * Delete predictions older than N days
     */
    const DB_CLEANUP_DAYS = 90;
    
    /**
     * Create alerts on critical predictions
     * Automatically creates records in attrition_alerts table
     */
    const DB_AUTO_CREATE_ALERTS = true;
    
    // ===== LOGGING SETTINGS =====
    
    /**
     * Log level
     * 'DEBUG', 'INFO', 'WARNING', 'ERROR'
     */
    const LOG_LEVEL = 'INFO';
    
    /**
     * Log file location
     */
    const LOG_FILE = __DIR__ . '/../../logs/ai_analytics.log';
    
    /**
     * Log AI API calls
     */
    const LOG_API_CALLS = true;
    
    /**
     * Log predictions
     */
    const LOG_PREDICTIONS = true;
    
    // ===== SECURITY SETTINGS =====
    
    /**
     * Require authentication for AI endpoints
     */
    const REQUIRE_AUTH = true;
    
    /**
     * Role-based access control
     * Roles: 'admin', 'hr', 'finance', 'manager', 'employee'
     */
    const ROLE_ATTRITION_ACCESS = ['admin', 'hr'];
    const ROLE_PROMOTION_ACCESS = ['admin', 'hr', 'manager'];
    const ROLE_ANOMALY_ACCESS = ['admin', 'finance', 'hr'];
    const ROLE_FORECAST_ACCESS = ['admin', 'finance', 'hr'];
    
    /**
     * Mask employee names in reports
     * For privacy: show as "Employee #123" instead of actual name
     */
    const MASK_EMPLOYEE_NAMES = false;
    
    /**
     * Audit logging for predictions
     * Track who viewed what predictions when
     */
    const AUDIT_LOG_PREDICTIONS = true;
    
    // ===== MODEL SETTINGS =====
    
    /**
     * Model versions
     * if new models deployed, update here
     */
    const MODEL_VERSION_ATTRITION = '1.0';
    const MODEL_VERSION_PROMOTION = '1.0';
    const MODEL_VERSION_FORECAST = '1.0';
    const MODEL_VERSION_ANOMALY = '1.0';
    
    /**
     * Model accuracy thresholds (0-1)
     * Warn if model accuracy drops below
     */
    const MODEL_ACCURACY_THRESHOLD = 0.75;
    
    // ===== ACTION PLAN SETTINGS =====
    
    /**
     * Auto-generate action plans
     */
    const AUTO_GENERATE_ACTION_PLANS = true;
    
    /**
     * Send action plans to assigned users
     */
    const EMAIL_ACTION_PLANS = false;
    
    /**
     * Required follow-up days
     * Alert if action not taken within N days
     */
    const ACTION_PLAN_FOLLOW_UP_DAYS = 14;
    
    // ===== INTEGRATION SETTINGS =====
    
    /**
     * Sync with HR system
     * Create HR tasks from predictions
     */
    const SYNC_HR_MODULE = false;
    
    /**
     * Sync with payroll system
     * Add forecasts to payroll planning
     */
    const SYNC_PAYROLL_MODULE = false;
    
    /**
     * Export to external systems
     * Send predictions to 3rd-party analytics
     */
    const SYNC_EXTERNAL_SYSTEMS = false;
    
    // ===== HELPER METHODS =====
    
    /**
     * Get AI server URL
     */
    public static function getAIServerUrl()
    {
        return self::AI_SERVER_URL;
    }
    
    /**
     * Get cache directory
     */
    public static function getCacheDir()
    {
        $dir = self::CACHE_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }
    
    /**
     * Check if feature is enabled
     */
    public static function isFeatureEnabled($feature)
    {
        $featureKey = 'FEATURE_' . strtoupper($feature);
        return defined("self::$featureKey") && constant("self::$featureKey");
    }
    
    /**
     * Get risk level by threshold
     */
    public static function getRiskLevel($score, $type = 'attrition')
    {
        switch ($type) {
            case 'attrition':
                if ($score > self::ATTRITION_CRITICAL_THRESHOLD) return 'Critical';
                if ($score > self::ATTRITION_HIGH_THRESHOLD) return 'High';
                if ($score > self::ATTRITION_MEDIUM_THRESHOLD) return 'Medium';
                return 'Low';
            
            case 'anomaly':
                if ($score > self::ANOMALY_CRITICAL_THRESHOLD) return 'Critical';
                if ($score > self::ANOMALY_HIGH_THRESHOLD) return 'High';
                if ($score > self::ANOMALY_MEDIUM_THRESHOLD) return 'Medium';
                return 'Low';
            
            default:
                return 'Unknown';
        }
    }
    
    /**
     * Check if role has access to feature
     */
    public static function hasRoleAccess($role, $feature)
    {
        $accessKey = 'ROLE_' . strtoupper($feature) . '_ACCESS';
        
        if (!defined("self::$accessKey")) {
            return false; // Feature not defined
        }
        
        $allowedRoles = constant("self::$accessKey");
        return in_array($role, $allowedRoles);
    }
    
    /**
     * Get all configuration as array
     */
    public static function toArray()
    {
        $reflection = new ReflectionClass(self::class);
        $constants = $reflection->getConstants();
        return $constants;
    }
}

?>
