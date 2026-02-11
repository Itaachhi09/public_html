<?php
/**
 * HMO Module Controller
 * Manages HMO providers, plans, and employee enrollment
 */

require_once(__DIR__ . '/../../../config/BaseController.php');
require_once(__DIR__ . '/../../../config/Database.php');
require_once(__DIR__ . '/../models/HMOProvider.php');
require_once(__DIR__ . '/../models/HMOPlan.php');
require_once(__DIR__ . '/../models/EmployeeHMOEnrollment.php');

class HMOController extends BaseController {
    private $db;
    private $providerModel;
    private $planModel;
    private $enrollmentModel;

    public function __construct() {
        parent::__construct();
        $database = new Database();
        $this->db = $database->connect();
        $this->providerModel = new HMOProvider();
        $this->planModel = new HMOPlan();
        $this->enrollmentModel = new EmployeeHMOEnrollment();
    }

    /**
     * Get all HMO providers
     */
    public function getProviders() {
        try {
            $query = "SELECT * FROM hmo_providers WHERE is_active = 1 ORDER BY provider_name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get provider details with all information
     */
    public function getProviderDetails($provider_id) {
        try {
            $query = "SELECT * FROM hmo_providers WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$provider_id]);
            $provider = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$provider) {
                return ['success' => false, 'error' => 'Provider not found'];
            }

            // Get associated plans
            $plans_query = "SELECT * FROM hmo_plans WHERE provider_id = ?";
            $plans_stmt = $this->db->prepare($plans_query);
            $plans_stmt->execute([$provider_id]);
            $plans = $plans_stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'provider' => $provider,
                    'plans' => $plans
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get expiring contracts
     */
    public function getExpiringContracts($days = 30) {
        try {
            $query = "SELECT * FROM hmo_providers 
                      WHERE contract_end_date <= DATE_ADD(NOW(), INTERVAL ? DAY) 
                      AND contract_end_date > NOW() 
                      ORDER BY contract_end_date ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$days]);
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get providers by status
     */
    public function getProvidersByStatus($status) {
        try {
            $query = "SELECT * FROM hmo_providers WHERE provider_status = ? ORDER BY provider_name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $result ? $result : []
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all HMO plans
     */
    public function getPlans() {
        try {
            $query = "SELECT hp.*, hpr.provider_name 
                      FROM hmo_plans hp 
                      LEFT JOIN hmo_providers hpr ON hp.provider_id = hpr.id 
                      WHERE hp.is_active = 1 
                      ORDER BY hp.plan_name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get employee enrollments
     */
    public function getEmployeeEnrollments($employee_id = null, $status = null) {
        try {
            $query = "SELECT e.*, hp.plan_name, hpr.provider_name 
                      FROM employee_hmo_enrollments e 
                      LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                      LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id 
                      WHERE 1=1";
            
            $params = [];
            
            if ($employee_id) {
                $query .= " AND e.employee_id = ?";
                $params[] = intval($employee_id);
            }
            
            if ($status) {
                $query .= " AND e.enrollment_status = ?";
                $params[] = $status;
            }
            
            $query .= " ORDER BY e.effective_date DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $result ? $result : []
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get active enrollments
     */
    public function getActiveEnrollments() {
        try {
            $query = "SELECT e.*, hp.plan_name, hpr.provider_name, CONCAT(emp.first_name, ' ', emp.last_name) as employee_name, emp.first_name, emp.last_name, emp.employee_code 
                      FROM employee_hmo_enrollments e 
                      LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                      LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id 
                      LEFT JOIN employees emp ON e.employee_id = emp.employee_id 
                      WHERE e.enrollment_status = 'active' 
                      AND e.effective_date <= NOW() 
                      AND (e.termination_date IS NULL OR e.termination_date > NOW()) 
                      ORDER BY e.employee_id ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get pending enrollments
     */
    public function getPendingEnrollments() {
        try {
            $query = "SELECT e.*, hp.plan_name, hpr.provider_name, emp.first_name, emp.last_name 
                      FROM employee_hmo_enrollments e 
                      LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                      LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id 
                      LEFT JOIN employees emp ON e.employee_id = emp.employee_id 
                      WHERE e.enrollment_status = 'pending' 
                      ORDER BY e.enrollment_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get waiting period enrollments
     */
    public function getWaitingPeriodEnrollments() {
        try {
            $query = "SELECT e.*, hp.plan_name, hpr.provider_name, emp.first_name, emp.last_name, emp.employee_code
                      FROM employee_hmo_enrollments e 
                      LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                      LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id 
                      LEFT JOIN employees emp ON e.employee_id = emp.employee_id 
                      WHERE e.enrollment_status = 'waiting_period' 
                      ORDER BY e.waiting_period_end_date ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get suspended enrollments
     */
    public function getSuspendedEnrollments() {
        try {
            $query = "SELECT e.*, hp.plan_name, hpr.provider_name, emp.first_name, emp.last_name, emp.employee_code
                      FROM employee_hmo_enrollments e 
                      LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                      LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id 
                      LEFT JOIN employees emp ON e.employee_id = emp.employee_id 
                      WHERE e.enrollment_status = 'suspended' 
                      ORDER BY e.updated_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get terminated enrollments
     */
    public function getTerminatedEnrollments() {
        try {
            $query = "SELECT e.*, hp.plan_name, hpr.provider_name, emp.first_name, emp.last_name, emp.employee_code
                      FROM employee_hmo_enrollments e 
                      LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                      LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id 
                      LEFT JOIN employees emp ON e.employee_id = emp.employee_id 
                      WHERE e.enrollment_status = 'terminated' 
                      ORDER BY e.termination_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get enrollment details with stats
     */
    public function getEnrollmentDetails($enrollment_id) {
        try {
            $query = "SELECT e.*, hp.plan_name, hpr.provider_name, emp.first_name, emp.last_name, emp.employee_code
                      FROM employee_hmo_enrollments e 
                      LEFT JOIN hmo_plans hp ON e.plan_id = hp.id 
                      LEFT JOIN hmo_providers hpr ON e.provider_id = hpr.id 
                      LEFT JOIN employees emp ON e.employee_id = emp.employee_id 
                      WHERE e.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$enrollment_id]);
            
            return [
                'success' => true,
                'data' => $stmt->fetch(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStats() {
        try {
            $query = "SELECT 
                        COUNT(CASE WHEN enrollment_status = 'active' THEN 1 END) as active_count,
                        COUNT(CASE WHEN enrollment_status = 'pending' THEN 1 END) as pending_count,
                        COUNT(CASE WHEN enrollment_status = 'waiting_period' THEN 1 END) as waiting_count,
                        COUNT(CASE WHEN enrollment_status = 'suspended' THEN 1 END) as suspended_count,
                        COUNT(CASE WHEN enrollment_status = 'terminated' THEN 1 END) as terminated_count
                      FROM employee_hmo_enrollments";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetch(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all claims
     */
    public function getAllClaims() {
        try {
            $query = "SELECT c.*, emp.employee_code, CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                             hp.plan_name, hpr.provider_name
                      FROM hmo_claims c
                      LEFT JOIN employees emp ON c.employee_id = emp.employee_id
                      LEFT JOIN hmo_plans hp ON c.plan_id = hp.id
                      LEFT JOIN hmo_providers hpr ON c.provider_id = hpr.id
                      ORDER BY c.service_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get high utilization employees
     */
    public function getHighUtilizationEmployees($threshold = 80) {
        try {
            $query = "SELECT e.id, e.employee_code, e.first_name, e.last_name, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                             hp.plan_name, hp.annual_premium_per_employee as plan_premium,
                             SUM(c.claim_amount) as amount_used,
                             COUNT(c.id) as claim_count,
                             ROUND((SUM(c.claim_amount) / (hp.annual_premium_per_employee * 2)) * 100, 2) as utilization_percent,
                             GREATEST(0, (hp.annual_premium_per_employee * 2) - SUM(c.claim_amount)) as remaining_balance
                      FROM employee_hmo_enrollments ee
                      JOIN employees e ON ee.employee_id = e.employee_id
                      JOIN hmo_plans hp ON ee.plan_id = hp.id
                      LEFT JOIN hmo_claims c ON ee.id = c.enrollment_id 
                             AND YEAR(c.service_date) = YEAR(NOW())
                             AND c.claim_status = 'approved'
                      WHERE ee.enrollment_status = 'active'
                      GROUP BY ee.id
                      HAVING utilization_percent >= ?
                      ORDER BY utilization_percent DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([intval($threshold)]);
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get provider comparison
     */
    public function getProviderComparison() {
        try {
            $query = "SELECT hpr.id, hpr.provider_name,
                             COUNT(c.id) as claim_count,
                             SUM(c.claim_amount) as total_claims,
                             ROUND(AVG(c.claim_amount), 2) as average_claim,
                             COUNT(DISTINCT c.employee_id) as unique_patients
                      FROM hmo_providers hpr
                      LEFT JOIN hmo_claims c ON hpr.id = c.provider_id
                             AND YEAR(c.service_date) = YEAR(NOW())
                             AND c.claim_status = 'approved'
                      WHERE hpr.is_active = 1
                      GROUP BY hpr.id
                      ORDER BY total_claims DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve claim
     */
    public function approveClaim($claim_id) {
        try {
            $query = "UPDATE hmo_claims SET claim_status = 'approved', updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$claim_id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Claim approved'];
            }
            
            return ['success' => false, 'error' => 'Claim not found'];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all billing reconciliations
     */
    public function getBillingReconciliations() {
        try {
            $query = "SELECT br.*, 
                             COUNT(DISTINCT bd.id) as discrepancy_count
                      FROM hmo_billing_reconciliation br
                      LEFT JOIN hmo_billing_discrepancies bd ON br.id = bd.reconciliation_id
                      GROUP BY br.id
                      ORDER BY br.billing_month DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'reconciliations' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get billing discrepancies
     */
    public function getBillingDiscrepancies() {
        try {
            $query = "SELECT bd.*, br.billing_month,
                             e.employee_code, 
                             CONCAT(e.first_name, ' ', e.last_name) as employee_name
                      FROM hmo_billing_discrepancies bd
                      JOIN hmo_billing_reconciliation br ON bd.reconciliation_id = br.id
                      LEFT JOIN employees e ON bd.employee_id = e.employee_id
                      ORDER BY br.billing_month DESC, bd.variance_amount DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'discrepancies' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get billing adjustments
     */
    public function getBillingAdjustments() {
        try {
            $query = "SELECT ba.*, br.billing_month,
                             e.employee_code,
                             CONCAT(e.first_name, ' ', e.last_name) as employee_name
                      FROM hmo_billing_adjustments ba
                      JOIN hmo_billing_reconciliation br ON ba.reconciliation_id = br.id
                      LEFT JOIN employees e ON ba.employee_id = e.employee_id
                      WHERE ba.status = 'pending'
                      ORDER BY br.billing_month DESC, ba.amount DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'adjustments' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get billing detail
     */
    public function getBillingDetail($reconciliation_id) {
        try {
            $query = "SELECT * FROM hmo_billing_reconciliation WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$reconciliation_id]);
            $reconciliation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reconciliation) {
                return ['success' => false, 'error' => 'Reconciliation not found'];
            }

            // Get discrepancies
            $disc_query = "SELECT bd.*, 
                                  e.employee_code,
                                  CONCAT(e.first_name, ' ', e.last_name) as employee_name
                           FROM hmo_billing_discrepancies bd
                           LEFT JOIN employees e ON bd.employee_id = e.employee_id
                           WHERE bd.reconciliation_id = ?";
            $disc_stmt = $this->db->prepare($disc_query);
            $disc_stmt->execute([$reconciliation_id]);
            $discrepancies = $disc_stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'reconciliation' => $reconciliation,
                'discrepancies' => $discrepancies
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload billing file
     */
    public function uploadBillingFile($type, $billing_month, $file_path, $notes = null) {
        try {
            $query = "INSERT INTO hmo_billing_uploads 
                      (type, billing_month, file_path, notes, uploaded_by, uploaded_date)
                      VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $type,
                $billing_month,
                $file_path,
                $notes,
                $_SESSION['user_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'file_id' => $this->db->lastInsertId(),
                'message' => 'File uploaded successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Run billing reconciliation
     */
    public function runBillingReconciliation() {
        try {
            // Create reconciliation record
            $billing_month = date('Y-m-01');
            
            $query = "INSERT INTO hmo_billing_reconciliation 
                      (billing_month, status, prepared_by, created_at, created_by)
                      VALUES (?, 'pending', ?, NOW(), ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $billing_month,
                $_SESSION['user_id'] ?? null,
                $_SESSION['user_id'] ?? null
            ]);
            
            $reconciliation_id = $this->db->lastInsertId();

            return [
                'success' => true,
                'reconciliation_id' => $reconciliation_id,
                'message' => 'Reconciliation created'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve billing reconciliation
     */
    public function approveBillingReconciliation($reconciliation_id) {
        try {
            $query = "UPDATE hmo_billing_reconciliation 
                      SET status = 'approved', 
                          reviewed_by = ?,
                          reviewed_date = NOW()
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$_SESSION['user_id'] ?? null, $reconciliation_id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Reconciliation approved'];
            }
            
            return ['success' => false, 'error' => 'Reconciliation not found'];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve discrepancy
     */
    public function approveDiscrepancy($discrepancy_id) {
        try {
            $query = "UPDATE hmo_billing_discrepancies 
                      SET status = 'approved'
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$discrepancy_id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Discrepancy approved'];
            }
            
            return ['success' => false, 'error' => 'Discrepancy not found'];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Approve adjustment
     */
    public function approveAdjustment($adjustment_id) {
        try {
            $query = "UPDATE hmo_billing_adjustments 
                      SET status = 'approved'
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$adjustment_id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Adjustment approved'];
            }
            
            return ['success' => false, 'error' => 'Adjustment not found'];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get life events
     */
    public function getLifeEvents() {
        try {
            $query = "SELECT le.*, 
                             e.employee_code,
                             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                             e.first_name as employee_first_name,
                             e.last_name as employee_last_name
                      FROM hmo_life_events le
                      LEFT JOIN employees e ON le.employee_id = e.employee_id
                      ORDER BY le.event_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'events' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get life event detail
     */
    public function getLifeEventDetail($event_id) {
        try {
            $query = "SELECT le.*, 
                             e.employee_code,
                             CONCAT(e.first_name, ' ', e.last_name) as employee_name
                      FROM hmo_life_events le
                      LEFT JOIN employees e ON le.employee_id = e.employee_id
                      WHERE le.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$event_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                return ['success' => false, 'error' => 'Life event not found'];
            }

            return ['success' => true, 'event' => $event];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create life event
     */
    public function createLifeEvent($data) {
        try {
            $query = "INSERT INTO hmo_life_events 
                      (employee_id, event_type, event_date, description, 
                       change_window_start, change_window_end, status, created_at, created_by)
                      VALUES (?,
                              ?,
                              ?,
                              ?,
                              DATE_SUB(?, INTERVAL 30 DAY),
                              DATE_ADD(?, INTERVAL 30 DAY),
                              'pending',
                              NOW(),
                              ?)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['employee_id'],
                $data['event_type'],
                $data['event_date'],
                $data['description'] ?? '',
                $data['event_date'],
                $data['event_date'],
                $_SESSION['user_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'event_id' => $this->db->lastInsertId(),
                'message' => 'Life event recorded'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process life event
     */
    public function processLifeEvent($event_id) {
        try {
            $query = "UPDATE hmo_life_events 
                      SET status = 'processed', processed_at = NOW()
                      WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$event_id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Life event processed'];
            }
            
            return ['success' => false, 'error' => 'Life event not found'];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get HMO documents
     */
    public function getHMODocuments() {
        try {
            $query = "SELECT hd.*,
                             e.employee_code,
                             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                             e.first_name as employee_first_name,
                             e.last_name as employee_last_name,
                             hp.plan_name,
                             hpr.provider_name
                      FROM hmo_documents hd
                      LEFT JOIN employees e ON hd.employee_id = e.employee_id
                      LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
                      LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
                      ORDER BY hd.issue_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return [
                'success' => true,
                'documents' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get HMO document detail
     */
    public function getHMODocumentDetail($doc_id) {
        try {
            $query = "SELECT hd.*,
                             e.employee_code,
                             CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                             hp.plan_name,
                             hpr.provider_name
                      FROM hmo_documents hd
                      LEFT JOIN employees e ON hd.employee_id = e.employee_id
                      LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
                      LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
                      WHERE hd.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$doc_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doc) {
                return ['success' => false, 'error' => 'Document not found'];
            }

            return ['success' => true, 'document' => $doc];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Download HMO document
     */
    public function downloadHMODocument($doc_id) {
        try {
            $query = "SELECT file_path FROM hmo_documents WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$doc_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doc || !file_exists($doc['file_path'])) {
                return ['success' => false, 'error' => 'Document file not found'];
            }

            return [
                'success' => true,
                'file_path' => $doc['file_path']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get plan details
     */
    public function getPlanDetail($plan_id) {
        try {
            $query = "SELECT hp.*, hpr.provider_name, hpr.provider_status, COUNT(ehe.id) as enrollment_count
                      FROM hmo_plans hp 
                      LEFT JOIN hmo_providers hpr ON hp.provider_id = hpr.id
                      LEFT JOIN employee_hmo_enrollments ehe ON hp.id = ehe.plan_id AND ehe.status = 'Active'
                      WHERE hp.id = ? 
                      GROUP BY hp.id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$plan) {
                return ['success' => false, 'error' => 'Plan not found'];
            }

            return ['success' => true, 'data' => $plan];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get plans by provider
     */
    public function getPlansByProvider($provider_id) {
        try {
            $query = "SELECT hp.*, COUNT(ehe.id) as enrollment_count
                      FROM hmo_plans hp
                      LEFT JOIN employee_hmo_enrollments ehe ON hp.id = ehe.plan_id AND ehe.status = 'Active'
                      WHERE hp.provider_id = ? AND hp.is_active = 1
                      GROUP BY hp.id
                      ORDER BY hp.plan_name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$provider_id]);
            $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $plans ? $plans : []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create enrollment
     */
    public function createEnrollment($data) {
        try {
            $required = ['employee_id', 'plan_id', 'effective_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'error' => "Missing required field: $field"];
                }
            }

            $query = "INSERT INTO employee_hmo_enrollments 
                      (employee_id, plan_id, effective_date, status, created_at) 
                      VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $status = $data['status'] ?? 'Pending';
            $stmt->execute([
                $data['employee_id'],
                $data['plan_id'],
                $data['effective_date'],
                $status
            ]);

            $enrollment_id = $this->db->lastInsertId();
            return ['success' => true, 'data' => ['id' => $enrollment_id, 'message' => 'Enrollment created successfully']];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update enrollment
     */
    public function updateEnrollment($enrollment_id, $data) {
        try {
            if (empty($enrollment_id)) {
                return ['success' => false, 'error' => 'Enrollment ID required'];
            }

            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                if (in_array($key, ['status', 'plan_id', 'effective_date', 'termination_date'])) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            $values[] = $enrollment_id;

            if (!$fields) {
                return ['success' => false, 'error' => 'No valid fields to update'];
            }

            $query = "UPDATE employee_hmo_enrollments SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute($values);

            return ['success' => true, 'message' => 'Enrollment updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Terminate enrollment
     */
    public function terminateEnrollment($enrollment_id, $data = []) {
        try {
            if (empty($enrollment_id)) {
                return ['success' => false, 'error' => 'Enrollment ID required'];
            }

            $termination_date = $data['termination_date'] ?? date('Y-m-d');
            $reason = $data['reason'] ?? null;

            $query = "UPDATE employee_hmo_enrollments 
                      SET status = 'Terminated', termination_date = ?, termination_reason = ?, updated_at = NOW() 
                      WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$termination_date, $reason, $enrollment_id]);

            return ['success' => true, 'message' => 'Enrollment terminated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get claim detail
     */
    public function getClaimDetail($claim_id) {
        try {
            $query = "SELECT * FROM hmo_claims WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$claim_id]);
            $claim = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$claim) {
                return ['success' => false, 'error' => 'Claim not found'];
            }

            return ['success' => true, 'data' => $claim];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get claims by status
     */
    public function getClaimsByStatus($status) {
        try {
            $query = "SELECT * FROM hmo_claims WHERE claim_status = ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$status]);
            $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $claims ? $claims : []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get claims by employee
     */
    public function getClaimsByEmployee($employee_id) {
        try {
            $query = "SELECT * FROM hmo_claims WHERE employee_id = ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$employee_id]);
            $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $claims ? $claims : []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reject claim
     */
    public function rejectClaim($claim_id, $reason) {
        try {
            if (empty($claim_id) || empty($reason)) {
                return ['success' => false, 'error' => 'Claim ID and reason required'];
            }

            $query = "UPDATE hmo_claims 
                      SET claim_status = 'Rejected', rejection_reason = ?, updated_at = NOW() 
                      WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$reason, $claim_id]);

            return ['success' => true, 'message' => 'Claim rejected successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create billing reconciliation
     */
    public function createBillingReconciliation($data) {
        try {
            $required = ['provider_id', 'month', 'year', 'claims_count', 'total_amount'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'error' => "Missing required field: $field"];
                }
            }

            $query = "INSERT INTO billing_reconciliations 
                      (provider_id, billing_month, claims_count, total_amount, status, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $billing_month = $data['year'] . '-' . str_pad($data['month'], 2, '0', STR_PAD_LEFT) . '-01';
            $stmt->execute([
                $data['provider_id'],
                $billing_month,
                $data['claims_count'],
                $data['total_amount'],
                'Pending'
            ]);

            $id = $this->db->lastInsertId();
            return ['success' => true, 'data' => ['id' => $id, 'message' => 'Billing reconciliation created successfully']];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update billing reconciliation
     */
    public function updateBillingReconciliation($reconciliation_id, $data) {
        try {
            $fields = [];
            $values = [];
            foreach ($data as $key => $value) {
                if (in_array($key, ['status', 'total_amount', 'notes'])) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            $values[] = $reconciliation_id;

            if (!$fields) {
                return ['success' => false, 'error' => 'No valid fields to update'];
            }

            $query = "UPDATE billing_reconciliations SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute($values);

            return ['success' => true, 'message' => 'Billing reconciliation updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get billing reconciliations by month
     */
    public function getBillingReconciliationsByMonth($month, $year) {
        try {
            $billing_month = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
            $query = "SELECT br.*, hpr.provider_name 
                      FROM billing_reconciliations br
                      LEFT JOIN hmo_providers hpr ON br.provider_id = hpr.id
                      WHERE DATE_FORMAT(br.billing_month, '%Y-%m') = ? 
                      ORDER BY br.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$billing_month]);
            $reconciliations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $reconciliations ? $reconciliations : []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get billing reconciliations by provider
     */
    public function getBillingReconciliationsByProvider($provider_id) {
        try {
            $query = "SELECT * FROM billing_reconciliations 
                      WHERE provider_id = ? 
                      ORDER BY billing_month DESC, created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$provider_id]);
            $reconciliations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['success' => true, 'data' => $reconciliations ? $reconciliations : []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resolve billing discrepancy
     */
    public function resolveBillingDiscrepancy($discrepancy_id, $resolution) {
        try {
            if (empty($discrepancy_id) || empty($resolution)) {
                return ['success' => false, 'error' => 'Discrepancy ID and resolution required'];
            }

            $query = "UPDATE billing_discrepancies 
                      SET status = 'Resolved', resolution = ?, resolved_at = NOW(), updated_at = NOW() 
                      WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$resolution, $discrepancy_id]);

            return ['success' => true, 'message' => 'Billing discrepancy resolved successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}


