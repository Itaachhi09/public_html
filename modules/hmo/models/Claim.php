<?php
/**
 * Claims and Utilization Model
 * 
 * Manages claim records, benefit balance tracking, usage controls, and utilization reporting
 * 
 * Claims Records:
 * - Employee and dependent references
 * - HMO provider
 * - Plan name
 * - Service type (Inpatient, Outpatient, ER, Dental)
 * - Claim amount and date of service
 * 
 * Balance Tracking:
 * - Annual benefit limit per plan
 * - Amount used to date
 * - Remaining balance
 * 
 * Usage Controls:
 * - Block claims if plan inactive
 * - Block claims if benefit exhausted
 * - Block claims outside coverage type
 * 
 * Reporting:
 * - High utilization employees
 * - High cost plans
 * - Provider comparison
 * - Cost trends
 * 
 * Data Handling:
 * - No diagnosis details
 * - No medical notes
 * - Financial and utilization only
 */

require_once __DIR__ . '/../../config/BaseModel.php';

class Claim extends BaseModel
{
    protected $table = 'hmo_claims';

    /**
     * Record a new claim
     */
    public function recordClaim($claimData)
    {
        return $this->execute("
            INSERT INTO hmo_claims 
            (enrollment_id, dependent_id, provider_id, plan_id, service_type, 
             claim_amount, date_of_service, claim_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
        ", [
            $claimData['enrollment_id'],
            $claimData['dependent_id'] ?? null,
            $claimData['provider_id'],
            $claimData['plan_id'],
            $claimData['service_type'],
            $claimData['claim_amount'],
            $claimData['date_of_service'],
        ]);
    }

    /**
     * Get all claims with filtering
     */
    public function getAllClaims($filters = [], $limit = 100)
    {
        $query = "
            SELECT c.*,
                   e.first_name,
                   e.last_name,
                   e.employee_code,
                   eee.plan_id,
                   p.plan_name,
                   hp.provider_name,
                   d.dependent_name,
                   d.relationship
            FROM hmo_claims c
            LEFT JOIN employee_hmo_enrollments eee ON eee.id = c.enrollment_id
            LEFT JOIN employees e ON e.id = eee.employee_id
            LEFT JOIN hmo_plans p ON p.id = eee.plan_id
            LEFT JOIN hmo_providers hp ON hp.id = c.provider_id
            LEFT JOIN hmo_dependents d ON d.id = c.dependent_id
            WHERE 1=1
        ";

        $params = [];
        
        if (!empty($filters['plan_id'])) {
            $query .= " AND eee.plan_id = ?";
            $params[] = $filters['plan_id'];
        }

        if (!empty($filters['provider_id'])) {
            $query .= " AND c.provider_id = ?";
            $params[] = $filters['provider_id'];
        }

        if (!empty($filters['service_type'])) {
            $query .= " AND c.service_type = ?";
            $params[] = $filters['service_type'];
        }

        if (!empty($filters['claim_status'])) {
            $query .= " AND c.claim_status = ?";
            $params[] = $filters['claim_status'];
        }

        if (!empty($filters['start_date'])) {
            $query .= " AND DATE(c.date_of_service) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $query .= " AND DATE(c.date_of_service) <= ?";
            $params[] = $filters['end_date'];
        }

        $query .= " ORDER BY c.date_of_service DESC LIMIT ?";
        $params[] = $limit;

        return $this->query($query, $params);
    }

    /**
     * Get claims by enrollment
     */
    public function getClaimsByEnrollment($enrollmentId)
    {
        return $this->query("
            SELECT c.*,
                   p.plan_name,
                   hp.provider_name,
                   d.dependent_name
            FROM hmo_claims c
            LEFT JOIN hmo_plans p ON p.id = c.plan_id
            LEFT JOIN hmo_providers hp ON hp.id = c.provider_id
            LEFT JOIN hmo_dependents d ON d.id = c.dependent_id
            WHERE c.enrollment_id = ?
            ORDER BY c.date_of_service DESC
        ", [$enrollmentId]);
    }

    /**
     * Get annual benefit balance for enrollment
     */
    public function getAnnualBalance($enrollmentId)
    {
        return $this->queryOne("
            SELECT 
                eee.id as enrollment_id,
                p.annual_benefit_limit,
                COALESCE(SUM(c.claim_amount), 0) as amount_used,
                (p.annual_benefit_limit - COALESCE(SUM(c.claim_amount), 0)) as remaining_balance,
                COUNT(c.id) as total_claims,
                MAX(c.date_of_service) as last_claim_date,
                YEAR(NOW()) as current_year
            FROM employee_hmo_enrollments eee
            LEFT JOIN hmo_plans p ON p.id = eee.plan_id
            LEFT JOIN hmo_claims c ON c.enrollment_id = eee.id AND YEAR(c.date_of_service) = YEAR(NOW())
            WHERE eee.id = ?
            GROUP BY eee.id
        ", [$enrollmentId]);
    }

    /**
     * Get benefit limits by plan
     */
    public function getBenefitLimitsByPlan($planId)
    {
        return $this->queryOne("
            SELECT 
                id as plan_id,
                plan_name,
                annual_benefit_limit,
                inpatient_limit,
                outpatient_limit,
                er_limit,
                dental_limit
            FROM hmo_plans
            WHERE id = ?
        ", [$planId]);
    }

    /**
     * Validate claim against enrollment and plan
     */
    public function validateClaim($claimData, $enrollment)
    {
        $errors = [];

        // Check if plan is active
        if ($enrollment['enrollment_status'] !== 'active') {
            $errors[] = 'Claim blocked: Plan is not active';
        }

        // Check if claim is within coverage
        if (!empty($claimData['dependent_id']) && $enrollment['coverage_type'] === 'employee_only') {
            $errors[] = 'Claim blocked: Dependent claim outside coverage type';
        }

        // Check annual benefit limit
        $balance = $this->getAnnualBalance($enrollment['id']);
        if ($balance['remaining_balance'] < $claimData['claim_amount']) {
            $errors[] = 'Claim blocked: Benefit limit exhausted';
        }

        // Check service-specific limits
        $limits = $this->getBenefitLimitsByPlan($enrollment['plan_id']);
        $serviceType = strtolower($claimData['service_type']);
        
        if (!empty($limits[$serviceType . '_limit'])) {
            // Would need to check service-specific usage
        }

        return $errors;
    }

    /**
     * Process claim (approve/deny)
     */
    public function processClaim($claimId, $status, $reason = null)
    {
        return $this->execute("
            UPDATE hmo_claims 
            SET claim_status = ?, denial_reason = ?, processed_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ", [$status, $reason, $claimId]);
    }

    /**
     * Get high utilization employees
     */
    public function getHighUtilizationEmployees($threshold = 80)
    {
        return $this->query("
            SELECT 
                e.id,
                e.first_name,
                e.last_name,
                e.employee_code,
                p.plan_name,
                p.annual_benefit_limit,
                COALESCE(SUM(c.claim_amount), 0) as amount_used,
                ROUND(((COALESCE(SUM(c.claim_amount), 0) / p.annual_benefit_limit) * 100), 2) as usage_percentage,
                COUNT(c.id) as total_claims
            FROM employees e
            LEFT JOIN employee_hmo_enrollments eee ON eee.employee_id = e.id AND eee.enrollment_status = 'active'
            LEFT JOIN hmo_plans p ON p.id = eee.plan_id
            LEFT JOIN hmo_claims c ON c.enrollment_id = eee.id AND YEAR(c.date_of_service) = YEAR(NOW())
            WHERE p.id IS NOT NULL
            GROUP BY e.id, p.id
            HAVING usage_percentage >= ?
            ORDER BY usage_percentage DESC
        ", [$threshold]);
    }

    /**
     * Get plan comparison by cost
     */
    public function getPlanComparison()
    {
        return $this->query("
            SELECT 
                p.id,
                p.plan_name,
                COUNT(DISTINCT c.enrollment_id) as enrollments_with_claims,
                COUNT(c.id) as total_claims,
                SUM(c.claim_amount) as total_claimed,
                AVG(c.claim_amount) as avg_claim,
                ROUND((SUM(c.claim_amount) / COUNT(DISTINCT c.enrollment_id)), 2) as avg_cost_per_enrollment,
                MIN(c.claim_amount) as min_claim,
                MAX(c.claim_amount) as max_claim
            FROM hmo_plans p
            LEFT JOIN hmo_claims c ON c.plan_id = p.id AND YEAR(c.date_of_service) = YEAR(NOW())
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY total_claimed DESC
        ");
    }

    /**
     * Get provider comparison
     */
    public function getProviderComparison()
    {
        return $this->query("
            SELECT 
                hp.id,
                hp.provider_name,
                COUNT(c.id) as total_claims,
                SUM(c.claim_amount) as total_claimed,
                AVG(c.claim_amount) as avg_claim,
                COUNT(DISTINCT c.enrollment_id) as unique_employees,
                MIN(c.date_of_service) as first_claim,
                MAX(c.date_of_service) as last_claim
            FROM hmo_providers hp
            LEFT JOIN hmo_claims c ON c.provider_id = hp.id AND YEAR(c.date_of_service) = YEAR(NOW())
            WHERE hp.is_active = 1
            GROUP BY hp.id
            ORDER BY total_claimed DESC
        ");
    }

    /**
     * Get utilization by service type
     */
    public function getUtilizationByServiceType()
    {
        return $this->query("
            SELECT 
                service_type,
                COUNT(id) as total_claims,
                SUM(claim_amount) as total_claimed,
                AVG(claim_amount) as avg_claim,
                MIN(claim_amount) as min_claim,
                MAX(claim_amount) as max_claim,
                COUNT(DISTINCT enrollment_id) as unique_enrollments
            FROM hmo_claims
            WHERE YEAR(date_of_service) = YEAR(NOW())
            GROUP BY service_type
            ORDER BY total_claimed DESC
        ");
    }

    /**
     * Get cost trend by period
     */
    public function getCostTrend($months = 12)
    {
        return $this->query("
            SELECT 
                DATE_FORMAT(date_of_service, '%Y-%m') as period,
                COUNT(id) as total_claims,
                SUM(claim_amount) as total_claimed,
                AVG(claim_amount) as avg_claim,
                COUNT(DISTINCT enrollment_id) as unique_enrollments
            FROM hmo_claims
            WHERE date_of_service >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(date_of_service, '%Y-%m')
            ORDER BY period DESC
        ", [$months]);
    }

    /**
     * Get claim statistics
     */
    public function getClaimStats()
    {
        return $this->queryOne("
            SELECT 
                COUNT(id) as total_claims,
                COUNT(CASE WHEN claim_status = 'approved' THEN 1 END) as approved_claims,
                COUNT(CASE WHEN claim_status = 'pending' THEN 1 END) as pending_claims,
                COUNT(CASE WHEN claim_status = 'denied' THEN 1 END) as denied_claims,
                SUM(CASE WHEN claim_status = 'approved' THEN claim_amount ELSE 0 END) as total_approved,
                SUM(claim_amount) as total_claimed,
                AVG(claim_amount) as avg_claim,
                YEAR(NOW()) as current_year
            FROM hmo_claims
            WHERE YEAR(date_of_service) = YEAR(NOW())
        ");
    }

    /**
     * Generate utilization report
     */
    public function getUtilizationReport($planId = null, $providerId = null)
    {
        $query = "
            SELECT 
                MONTH(c.date_of_service) as month,
                c.service_type,
                COUNT(c.id) as claims_count,
                SUM(c.claim_amount) as claims_amount,
                COUNT(DISTINCT c.enrollment_id) as unique_enrollments
            FROM hmo_claims c
            WHERE YEAR(c.date_of_service) = YEAR(NOW())
        ";

        $params = [];

        if ($planId) {
            $query .= " AND c.plan_id = ?";
            $params[] = $planId;
        }

        if ($providerId) {
            $query .= " AND c.provider_id = ?";
            $params[] = $providerId;
        }

        $query .= " GROUP BY MONTH(c.date_of_service), c.service_type ORDER BY month DESC, claims_amount DESC";

        return $this->query($query, $params);
    }
}
