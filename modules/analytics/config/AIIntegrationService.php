<?php
/**
 * AI Integration Service
 * Bridges Flask AI server predictions with analytics module
 * Handles HR predictions (attrition, promotions, pay forecast)
 * and Payroll anomaly detection
 */

class AIIntegrationService
{
    private $aiServerUrl = 'http://localhost:8000';
    private $timeout = 30; // seconds
    private $cacheDir = __DIR__ . '/../../logs/ai_cache';
    private $enableCache = true;
    private $cacheDuration = 3600; // 1 hour

    public function __construct($aiServerUrl = null)
    {
        if ($aiServerUrl) {
            $this->aiServerUrl = $aiServerUrl;
        }
        
        // Create cache directory if it doesn't exist
        if ($this->enableCache && !is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Check if AI server is online
     */
    public function isServerOnline()
    {
        try {
            $response = $this->makeRequest('GET', '/health', []);
            return $response['success'] ?? false;
        } catch (Exception $e) {
            error_log('AI Server Health Check Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Predict HR metrics for employees
     * 
     * @param array $employees Employee records with required fields
     * @return array Predictions with attrition_prob, promotion_prob, next_month_net_pay, overtime_anomaly
     */
    public function predictHRMetrics($employees)
    {
        if (empty($employees)) {
            return [];
        }

        // Check cache first
        $cacheKey = 'hr_predictions_' . md5(json_encode($employees));
        $cached = $this->getFromCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $payload = ['records' => $this->formatHRRecords($employees)];
            $response = $this->makeRequest('POST', '/predict/hr', $payload);
            
            if ($response['success'] ?? false) {
                $predictions = $response['items'] ?? [];
                $this->saveToCache($cacheKey, $predictions);
                return $predictions;
            }
            
            return [];
        } catch (Exception $e) {
            error_log('HR Prediction Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Predict payroll anomalies
     * 
     * @param array $payrollRecords Payroll records for anomaly detection
     * @return array Anomaly scores and flags
     */
    public function predictPayrollAnomalies($payrollRecords)
    {
        if (empty($payrollRecords)) {
            return [];
        }

        $cacheKey = 'payroll_anomalies_' . md5(json_encode($payrollRecords));
        $cached = $this->getFromCache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $payload = ['records' => $payrollRecords];
            $response = $this->makeRequest('POST', '/predict/payroll', $payload);
            
            if ($response['success'] ?? false) {
                $predictions = $response['items'] ?? [];
                $this->saveToCache($cacheKey, $predictions);
                return $predictions;
            }
            
            return [];
        } catch (Exception $e) {
            error_log('Payroll Prediction Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get attrition risk dashboard data
     */
    public function getAttritionRiskSummary($department = null, $limit = 10)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get employees with relevant data
            $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as name,
                           d.department_name, e.job_title_id, et.type_name, e.employment_status,
                           es.basic_rate, DATEDIFF(NOW(), e.date_of_joining) as tenure_days
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
                    LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
                    WHERE e.employment_status = 'Active'";
            
            if ($department) {
                $sql .= " AND e.department_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$department]);
            } else {
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
            
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get predictions
            $predictions = $this->predictHRMetrics($employees);
            
            // Merge and sort by attrition risk
            $riskData = [];
            foreach ($predictions as $pred) {
                $empId = $pred['employee_id'];
                $emp = array_filter($employees, fn($e) => $e['employee_id'] == $empId);
                $emp = reset($emp);
                
                if ($emp) {
                    $riskData[] = [
                        'employee_id' => $empId,
                        'name' => $emp['name'] ?? 'Unknown',
                        'department' => $emp['department_name'] ?? 'N/A',
                        'attrition_risk' => round($pred['attrition_prob'] * 100, 1),
                        'promotion_chance' => round($pred['promotion_prob'] * 100, 1),
                        'next_month_pay' => $pred['next_month_net_pay'] ?? 0,
                        'overtime_flag' => $pred['overtime_anomaly'] ?? false,
                        'risk_level' => $this->getRiskLevel($pred['attrition_prob']),
                        'recommendation' => $this->getRecommendation($pred['attrition_prob'])
                    ];
                }
            }
            
            // Sort by attrition risk descending
            usort($riskData, fn($a, $b) => $b['attrition_risk'] <=> $a['attrition_risk']);
            
            return array_slice($riskData, 0, $limit);
        } catch (Exception $e) {
            error_log('Attrition Risk Summary Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get promotion candidates
     */
    public function getPromotionCandidates($department = null, $limit = 10)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as name,
                           d.department_name, e.job_title_id, es.basic_rate,
                           DATEDIFF(NOW(), e.date_of_joining) as tenure_days
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
                    WHERE e.employment_status = 'Active'";
            
            if ($department) {
                $sql .= " AND e.department_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$department]);
            } else {
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
            
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $predictions = $this->predictHRMetrics($employees);
            
            $promotionData = [];
            foreach ($predictions as $pred) {
                $empId = $pred['employee_id'];
                $emp = array_filter($employees, fn($e) => $e['employee_id'] == $empId);
                $emp = reset($emp);
                
                if ($emp && $pred['promotion_prob'] > 0.3) { // Only high-probability candidates
                    $promotionData[] = [
                        'employee_id' => $empId,
                        'name' => $emp['name'] ?? 'Unknown',
                        'department' => $emp['department_name'] ?? 'N/A',
                        'promotion_probability' => round($pred['promotion_prob'] * 100, 1),
                        'attrition_risk' => round($pred['attrition_prob'] * 100, 1),
                        'tenure_days' => $emp['tenure_days'] ?? 0,
                        'readiness_score' => round($pred['promotion_prob'] * 100, 1)
                    ];
                }
            }
            
            usort($promotionData, fn($a, $b) => $b['promotion_probability'] <=> $a['promotion_probability']);
            
            return array_slice($promotionData, 0, $limit);
        } catch (Exception $e) {
            error_log('Promotion Candidates Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payroll anomalies
     */
    public function getPayrollAnomalies($dateRange = 30, $department = null, $threshold = 0.7)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $startDate = date('Y-m-d', strtotime("-$dateRange days"));
            
            $sql = "SELECT pre.*, e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                           d.department_name, pr.payroll_run_id
                    FROM payroll_run_employees pre
                    JOIN payroll_runs pr ON pre.payroll_run_id = pr.id
                    JOIN employees e ON pre.employee_id = e.employee_id
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    WHERE pr.end_date >= ? AND pr.status IN ('Processed', 'Closed')";
            
            $params = [$startDate];
            if ($department) {
                $sql .= " AND e.department_id = ?";
                $params[] = $department;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $payrollRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get anomaly predictions
            $anomalies = $this->predictPayrollAnomalies($payrollRecords);
            
            // Filter and format results
            $results = [];
            foreach ($anomalies as $i => $anomaly) {
                if (($anomaly['anomaly_score'] ?? 0) > $threshold) {
                    $record = $payrollRecords[$i] ?? [];
                    $results[] = [
                        'employee_id' => $record['employee_id'] ?? null,
                        'employee_name' => $record['employee_name'] ?? 'Unknown',
                        'department' => $record['department_name'] ?? 'N/A',
                        'anomaly_score' => round($anomaly['anomaly_score'] * 100, 1),
                        'anomaly_flag' => $anomaly['anomaly_flag'] ?? false,
                        'gross_pay' => $record['gross_pay'] ?? 0,
                        'net_pay' => $record['net_pay'] ?? 0,
                        'severity' => $this->getAnomalySeverity($anomaly['anomaly_score'])
                    ];
                }
            }
            
            usort($results, fn($a, $b) => $b['anomaly_score'] <=> $a['anomaly_score']);
            
            return $results;
        } catch (Exception $e) {
            error_log('Payroll Anomalies Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pay forecast for department
     */
    public function getPayForecast($department = null, $months = 3)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT e.employee_id, CONCAT(e.first_name, ' ', e.last_name) as name,
                           d.department_name, es.basic_rate
                    FROM employees e
                    LEFT JOIN departments d ON e.department_id = d.department_id
                    LEFT JOIN employee_salaries es ON e.employee_id = es.employee_id
                    WHERE e.employment_status = 'Active'";
            
            if ($department) {
                $sql .= " AND e.department_id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$department]);
            } else {
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }
            
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $predictions = $this->predictHRMetrics($employees);
            
            $forecast = [
                'month_1' => 0,
                'month_2' => 0,
                'month_3' => 0,
                'total_forecasted' => 0,
                'current_total' => 0,
                'employee_forecasts' => []
            ];
            
            foreach ($predictions as $pred) {
                $nextPayForecast = $pred['next_month_net_pay'] ?? 0;
                $forecast['month_1'] += $nextPayForecast;
                
                // Simple linear extrapolation for subsequent months
                $forecast['month_2'] += $nextPayForecast * 1.01; // 1% growth
                $forecast['month_3'] += $nextPayForecast * 1.02; // 2% growth
                
                $empId = $pred['employee_id'];
                $emp = array_filter($employees, fn($e) => $e['employee_id'] == $empId);
                $emp = reset($emp);
                
                if ($emp) {
                    $forecast['employee_forecasts'][] = [
                        'name' => $emp['name'],
                        'next_month_net' => round($nextPayForecast, 2),
                        'trend' => 'stable'
                    ];
                }
            }
            
            $forecast['total_forecasted'] = $forecast['month_1'] + $forecast['month_2'] + $forecast['month_3'];
            $forecast['current_total'] = array_sum(array_column($employees, 'basic_rate'));
            
            return $forecast;
        } catch (Exception $e) {
            error_log('Pay Forecast Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format employee records for HR predictions
     */
    private function formatHRRecords($employees)
    {
        $formatted = [];
        foreach ($employees as $emp) {
            $formatted[] = [
                'EmployeeID' => $emp['employee_id'] ?? null,
                'Age' => $this->calculateAge($emp) ?? 30,
                'Tenure' => $this->calculateTenure($emp) ?? 1,
                'BaseMonthlySalary' => $emp['basic_rate'] ?? 0,
                'Role' => $emp['job_title_id'] ?? 'Unknown',
                'Department' => $emp['department_name'] ?? 'Unknown',
                'OvertimeHours' => 0, // Would need to fetch from actual data
                'AttendanceScore' => 90, // Placeholder
                'PerformanceRating' => 3.5, // Placeholder
                'HMOClaimsAnnual' => 0, // Would need to fetch
                'MonthlyHoursWorked' => 170,
                'MonthlyOvertime' => 0,
                'MonthlyLateCount' => 0,
                'MonthlyAbsenceCount' => 0
            ];
        }
        return $formatted;
    }

    /**
     * Helper: Calculate age from DOB or approximate from hire date
     */
    private function calculateAge($employee)
    {
        // Would calculate from actual DOB if available
        return 35; // Default
    }

    /**
     * Helper: Calculate tenure in years
     */
    private function calculateTenure($employee)
    {
        if (isset($employee['tenure_days'])) {
            return $employee['tenure_days'] / 365.25;
        }
        return 2; // Default
    }

    /**
     * Get risk level label
     */
    private function getRiskLevel($probability)
    {
        if ($probability > 0.7) return 'Critical';
        if ($probability > 0.5) return 'High';
        if ($probability > 0.3) return 'Medium';
        return 'Low';
    }

    /**
     * Get recommendation based on attrition risk
     */
    private function getRecommendation($probability)
    {
        if ($probability > 0.7) {
            return 'Immediate retention intervention needed';
        }
        if ($probability > 0.5) {
            return 'Schedule management meeting';
        }
        if ($probability > 0.3) {
            return 'Monitor closely, plan career development';
        }
        return 'Stable employee, focus on growth opportunities';
    }

    /**
     * Get anomaly severity label
     */
    private function getAnomalySeverity($score)
    {
        if ($score > 0.9) return 'Critical';
        if ($score > 0.7) return 'High';
        if ($score > 0.5) return 'Medium';
        return 'Low';
    }

    /**
     * Make HTTP request to AI server
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        $url = $this->aiServerUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("AI Server returned HTTP $httpCode");
        }
        
        $decoded = json_decode($response, true);
        return ['success' => true, 'items' => $decoded['items'] ?? []];
    }

    /**
     * Cache management
     */
    private function getFromCache($key)
    {
        if (!$this->enableCache) return null;
        
        $filePath = $this->cacheDir . '/' . $key . '.json';
        if (file_exists($filePath)) {
            $age = time() - filemtime($filePath);
            if ($age < $this->cacheDuration) {
                return json_decode(file_get_contents($filePath), true);
            }
            unlink($filePath); // Expired
        }
        return null;
    }

    private function saveToCache($key, $data)
    {
        if (!$this->enableCache) return;
        
        $filePath = $this->cacheDir . '/' . $key . '.json';
        file_put_contents($filePath, json_encode($data), LOCK_EX);
    }
}
?>
