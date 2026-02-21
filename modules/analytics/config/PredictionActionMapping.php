<?php
/**
 * AI Prediction Action Mapping
 * Maps AI predictions to recommended business actions
 */

class PredictionActionMapping
{
    /**
     * Get recommended actions for attrition risk
     */
    public static function getAttritionActions($riskScore, $riskLevel)
    {
        $actions = [];

        if ($riskLevel === 'Critical' || $riskScore > 0.7) {
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Immediate Retention Intervention',
                'description' => 'Schedule urgent meeting with employee and manager',
                'timeline' => 'Within 2-3 days',
                'responsible' => 'HR Manager',
                'steps' => [
                    'Review employee satisfaction survey responses',
                    'Conduct 1-on-1 conversation to understand concerns',
                    'Prepare retention strategy (compensation, role change, development)',
                    'Follow up on action items weekly'
                ]
            ];
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Compensation Review',
                'description' => 'Review and adjust salary/benefits if needed',
                'timeline' => 'Within 1-2 weeks',
                'responsible' => 'Compensation Team'
            ];
        }

        if ($riskLevel === 'High' || ($riskScore > 0.5 && $riskScore <= 0.7)) {
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Schedule Management Meeting',
                'description' => 'Discuss career development and concerns',
                'timeline' => 'Within 1 week',
                'responsible' => 'Direct Manager'
            ];
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Career Development Plan',
                'description' => 'Create or update employee development plan',
                'timeline' => 'Within 2 weeks',
                'responsible' => 'HR & Manager'
            ];
        }

        if ($riskLevel === 'Medium' || ($riskScore > 0.3 && $riskScore <= 0.5)) {
            $actions[] = [
                'priority' => 'LOW',
                'action' => 'Monitor & Engage',
                'description' => 'Continue regular check-ins and engagement activities',
                'timeline' => 'Ongoing',
                'responsible' => 'Manager'
            ];
        }

        if ($riskLevel === 'Low' || $riskScore <= 0.3) {
            $actions[] = [
                'priority' => 'LOW',
                'action' => 'Focus on Growth',
                'description' => 'Identify promotion or development opportunities',
                'timeline' => 'Quarterly review',
                'responsible' => 'Manager'
            ];
        }

        return $actions;
    }

    /**
     * Get recommended actions for promotion candidates
     */
    public static function getPromotionActions($readinessScore, $promotionProbability)
    {
        $actions = [];

        if ($promotionProbability > 0.7) {
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Fast-Track Promotion',
                'description' => 'Prepare promotion paperwork and announce',
                'timeline' => 'Within 2-4 weeks',
                'responsible' => 'HR Manager & Hiring Manager',
                'steps' => [
                    'Schedule promotion discussion with employee',
                    'Approve by department head and finance',
                    'Prepare new role description and salary',
                    'Announce and conduct transition'
                ]
            ];
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Succession Planning',
                'description' => 'Document as successor for their current role',
                'timeline' => 'Immediate',
                'responsible' => 'HR & Manager'
            ];
        }

        if ($promotionProbability > 0.5 && $promotionProbability <= 0.7) {
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Development & Coaching',
                'description' => 'Prepare employee for next level responsibilities',
                'timeline' => 'Next 3-6 months',
                'responsible' => 'Manager & HR'
            ];
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Extended Projects',
                'description' => 'Assign leadership or high-impact projects',
                'timeline' => 'Next quarter',
                'responsible' => 'Manager'
            ];
        }

        if ($promotionProbability > 0.3 && $promotionProbability <= 0.5) {
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Targeted Skill Development',
                'description' => 'Identify and address skill gaps',
                'timeline' => 'Over 6 months',
                'responsible' => 'HR & Manager'
            ];
        }

        return $actions;
    }

    /**
     * Get recommended actions for payroll anomalies
     */
    public static function getAnomalyActions($anomalyScore, $severity)
    {
        $actions = [];

        if ($severity === 'Critical' || $anomalyScore > 0.9) {
            $actions[] = [
                'priority' => 'CRITICAL',
                'action' => 'Immediate Investigation',
                'description' => 'Flag for immediate payroll audit and investigation',
                'timeline' => 'Immediate (same day)',
                'responsible' => 'Payroll Manager & internal audit',
                'steps' => [
                    'Pull employee payroll record',
                    'Compare with previous months',
                    'Review supporting documents',
                    'Contact employee and manager if needed',
                    'Document findings'
                ]
            ];
            $actions[] = [
                'priority' => 'CRITICAL',
                'action' => 'Hold Payment',
                'description' => 'Consider holding payment pending investigation',
                'timeline' => 'Same day',
                'responsible' => 'Finance Director'
            ];
        }

        if ($severity === 'High' || ($anomalyScore > 0.7 && $anomalyScore <= 0.9)) {
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Detailed Review',
                'description' => 'Conduct thorough payroll review',
                'timeline' => 'Within 1-2 days',
                'responsible' => 'Payroll Specialist'
            ];
            $actions[] = [
                'priority' => 'HIGH',
                'action' => 'Verify Documentation',
                'description' => 'Request and review supporting documents',
                'timeline' => 'Within 3 days',
                'responsible' => 'Payroll Manager'
            ];
        }

        if ($severity === 'Medium' || ($anomalyScore > 0.5 && $anomalyScore <= 0.7)) {
            $actions[] = [
                'priority' => 'MEDIUM',
                'action' => 'Standard Verification',
                'description' => 'Verify against payroll policies',
                'timeline' => 'Within 1 week',
                'responsible' => 'Payroll Team'
            ];
        }

        return $actions;
    }

    /**
     * Get business impact summary for risk scores
     */
    public static function getBusinessImpact($riskScore, $type = 'attrition')
    {
        $impacts = [
            'attrition' => [
                'critical' => [
                    'financial' => 'High cost of replacement (100-200% salary)',
                    'operational' => 'Workflow disruption, knowledge loss',
                    'cultural' => 'Negative team morale impact',
                    'timeline' => 'Immediate action needed'
                ],
                'high' => [
                    'financial' => 'Moderate replacement costs',
                    'operational' => 'Some workflow impact',
                    'timeline' => '1-2 weeks to address'
                ],
                'medium' => [
                    'financial' => 'Monitor costs',
                    'operational' => 'Low impact',
                    'timeline' => '1-2 months for planning'
                ]
            ],
            'anomaly' => [
                'critical' => [
                    'financial' => 'Potential financial loss or compliance violation',
                    'compliance' => 'Audit trail impact, regulatory concern',
                    'operational' => 'Process integrity issue',
                    'timeline' => 'Immediate investigation'
                ],
                'high' => [
                    'financial' => 'Material variance requiring review',
                    'compliance' => 'Documentation gap',
                    'timeline' => '1-2 days'
                ]
            ]
        ];

        $riskLevel = self::getRiskLevel($riskScore);
        return $impacts[$type][$riskLevel] ?? [];
    }

    /**
     * Helper: Map score to risk level
     */
    public static function getRiskLevel($score)
    {
        if ($score > 0.7) return 'critical';
        if ($score > 0.5) return 'high';
        if ($score > 0.3) return 'medium';
        return 'low';
    }

    /**
     * Get KPI metrics affected by prediction
     */
    public static function getAffectedKPIs($prediction, $type)
    {
        $kpis = [];

        if ($type === 'attrition') {
            $kpis[] = ['metric' => 'Attrition Rate', 'status' => 'at risk'];
            $kpis[] = ['metric' => 'Employee Turnover', 'status' => 'increase expected'];
            $kpis[] = ['metric' => 'Workforce Stability', 'status' => 'declining'];
            if ($prediction['attrition_risk'] > 0.5) {
                $kpis[] = ['metric' => 'Recruitment Costs', 'status' => 'budget increase needed'];
            }
        }

        if ($type === 'promotion') {
            $kpis[] = ['metric' => 'Leadership Pipeline', 'status' => 'strengthened'];
            $kpis[] = ['metric' => 'Succession Readiness', 'status' => 'improved'];
            $kpis[] = ['metric' => 'Employee Engagement', 'status' => 'positive impact'];
        }

        if ($type === 'anomaly') {
            $kpis[] = ['metric' => 'Payroll Accuracy', 'status' => 'at risk'];
            $kpis[] = ['metric' => 'Compliance Risk', 'status' => 'flagged'];
            $kpis[] = ['metric' => 'Financial Control', 'status' => 'review needed'];
        }

        return $kpis;
    }

    /**
     * Generate action plan document structure
     */
    public static function generateActionPlan($employeeId, $prediction, $type, $actions)
    {
        return [
            'plan_id' => uniqid('AP_'),
            'employee_id' => $employeeId,
            'prediction_type' => $type,
            'created_date' => date('Y-m-d'),
            'prediction_summary' => $prediction,
            'actions' => $actions,
            'timeline' => self::getTimeline($actions),
            'responsible_parties' => self::getResponsibleParties($actions),
            'success_metrics' => self::getSuccessMetrics($type),
            'review_date' => date('Y-m-d', strtotime('+30 days'))
        ];
    }

    /**
     * Extract timeline from actions
     */
    private static function getTimeline($actions)
    {
        $timeframes = [];
        foreach ($actions as $action) {
            $timeframes[] = $action['timeline'] ?? '';
        }
        return array_filter($timeframes);
    }

    /**
     * Extract responsible parties
     */
    private static function getResponsibleParties($actions)
    {
        $parties = [];
        foreach ($actions as $action) {
            if (!empty($action['responsible'])) {
                if (!in_array($action['responsible'], $parties)) {
                    $parties[] = $action['responsible'];
                }
            }
        }
        return $parties;
    }

    /**
     * Get success metrics for action plan
     */
    private static function getSuccessMetrics($type)
    {
        $metrics = [
            'attrition' => [
                'Employee completes career development plan',
                'Positive sentiment in follow-up survey',
                'Attendance and punctuality maintained',
                'Performance ratings stable or improved'
            ],
            'promotion' => [
                'Employee successfully transitions to new role',
                'Performance meets or exceeds expectations',
                'Team satisfaction maintained',
                'Knowledge transfer completed'
            ],
            'anomaly' => [
                'Investigation completed with findings',
                'Corrective action implemented if needed',
                'No similar anomalies in next cycle',
                'Control process strengthened'
            ]
        ];

        return $metrics[$type] ?? [];
    }
}
?>
