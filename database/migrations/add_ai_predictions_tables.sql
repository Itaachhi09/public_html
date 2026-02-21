-- AI Predictions Cache Tables
-- Stores historical AI predictions for analytics and forecasting

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
    INDEX idx_date (prediction_date),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_type (prediction_type),
    INDEX idx_prediction_date (prediction_date),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_risk_level (risk_level),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    INDEX idx_status (action_status),
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
