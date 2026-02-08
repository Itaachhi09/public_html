<?php
/**
 * HMO Document Model
 * 
 * Manages HMO documents and IDs
 * 
 * Document Types:
 * - Digital HMO card
 * - Policy document
 * - Provider contract
 * - Plan endorsement
 * - Renewal notice
 * 
 * Links to:
 * - Employee enrollment
 * - Dependent (if applicable)
 * - HMO provider
 * 
 * System Features:
 * - Expiry alerts
 * - Auto mark expired
 * - Role-based access
 */

require_once __DIR__ . '/../../config/BaseModel.php';

class HMODocument extends BaseModel {
    protected $table = 'hmo_documents';

    /**
     * Get all HMO documents
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT hd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   ee.plan_id,
                   hp.plan_name,
                   hpr.provider_name
            FROM {$this->table} hd
            LEFT JOIN employees emp ON hd.employee_id = emp.id
            LEFT JOIN employee_hmo_enrollments ee ON hd.enrollment_id = ee.id
            LEFT JOIN hmo_plans hp ON ee.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['document_type'])) {
            $sql .= " AND hd.document_type = ?";
            $params[] = $filters['document_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND hd.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['enrollment_id'])) {
            $sql .= " AND hd.enrollment_id = ?";
            $params[] = $filters['enrollment_id'];
        }

        $sql .= " ORDER BY hd.issue_date DESC";

        return $this->query($sql, $params);
    }

    /**
     * Get document by ID
     */
    public function getById($id) {
        return $this->queryOne("
            SELECT hd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   hp.plan_name,
                   hpr.provider_name
            FROM {$this->table} hd
            LEFT JOIN employees emp ON hd.employee_id = emp.id
            LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
            WHERE hd.id = ?
        ", [$id]);
    }

    /**
     * Get documents for enrollment
     */
    public function getByEnrollment($enrollmentId) {
        return $this->query("
            SELECT * FROM {$this->table}
            WHERE enrollment_id = ?
            ORDER BY issue_date DESC
        ", [$enrollmentId]);
    }

    /**
     * Get documents for employee
     */
    public function getByEmployee($employeeId) {
        return $this->query("
            SELECT hd.*,
                   hp.plan_name,
                   hpr.provider_name
            FROM {$this->table} hd
            LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
            WHERE hd.employee_id = ?
            ORDER BY hd.issue_date DESC
        ", [$employeeId]);
    }

    /**
     * Create new document
     */
    public function create($data) {
        $sql = "
            INSERT INTO {$this->table}
            (employee_id, enrollment_id, dependent_id, plan_id, provider_id,
             document_type, file_path, issue_date, expiry_date, status,
             document_number, description, created_at, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ";

        $params = [
            $data['employee_id'],
            $data['enrollment_id'] ?? null,
            $data['dependent_id'] ?? null,
            $data['plan_id'] ?? null,
            $data['provider_id'] ?? null,
            $data['document_type'],
            $data['file_path'],
            $data['issue_date'],
            $data['expiry_date'] ?? null,
            $data['status'] ?? 'active',
            $data['document_number'] ?? null,
            $data['description'] ?? null,
            null
        ];

        return $this->execute($sql, $params);
    }

    /**
     * Update document status
     */
    public function updateStatus($id, $status) {
        return $this->execute("
            UPDATE {$this->table} SET status = ? WHERE id = ?
        ", [$status, $id]);
    }

    /**
     * Mark documents as expired
     */
    public function markExpired() {
        return $this->execute("
            UPDATE {$this->table} 
            SET status = 'expired'
            WHERE expiry_date IS NOT NULL 
              AND expiry_date <= NOW()
              AND status != 'expired'
        ");
    }

    /**
     * Get expiring documents
     */
    public function getExpiring($daysBeforeExpiry = 30) {
        return $this->query("
            SELECT hd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   hp.plan_name,
                   hpr.provider_name,
                   DATEDIFF(hd.expiry_date, NOW()) as days_until_expiry
            FROM {$this->table} hd
            LEFT JOIN employees emp ON hd.employee_id = emp.id
            LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
            WHERE hd.expiry_date IS NOT NULL
              AND hd.status = 'active'
              AND hd.expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
              AND hd.expiry_date > NOW()
            ORDER BY hd.expiry_date ASC
        ", [$daysBeforeExpiry]);
    }

    /**
     * Get expired documents
     */
    public function getExpired() {
        return $this->query("
            SELECT hd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   hp.plan_name,
                   hpr.provider_name
            FROM {$this->table} hd
            LEFT JOIN employees emp ON hd.employee_id = emp.id
            LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
            WHERE hd.status = 'expired'
            ORDER BY hd.expiry_date DESC
        ");
    }

    /**
     * Get active HMO cards for employee
     */
    public function getActiveCards($employeeId) {
        return $this->query("
            SELECT hd.*,
                   hp.plan_name,
                   hpr.provider_name
            FROM {$this->table} hd
            LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
            WHERE hd.employee_id = ?
              AND hd.document_type = 'hmo_card'
              AND hd.status = 'active'
            ORDER BY hd.issue_date DESC
        ", [$employeeId]);
    }

    /**
     * Get documents by type
     */
    public function getByType($documentType) {
        return $this->query("
            SELECT hd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name
            FROM {$this->table} hd
            LEFT JOIN employees emp ON hd.employee_id = emp.id
            WHERE hd.document_type = ?
            ORDER BY hd.issue_date DESC
        ", [$documentType]);
    }

    /**
     * Get document statistics
     */
    public function getStatistics() {
        return $this->queryOne("
            SELECT 
                COUNT(*) as total_documents,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_documents,
                COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired_documents,
                SUM(CASE WHEN document_type = 'hmo_card' THEN 1 ELSE 0 END) as hmo_cards,
                SUM(CASE WHEN document_type = 'policy_document' THEN 1 ELSE 0 END) as policy_documents,
                SUM(CASE WHEN document_type = 'provider_contract' THEN 1 ELSE 0 END) as provider_contracts,
                SUM(CASE WHEN document_type = 'plan_endorsement' THEN 1 ELSE 0 END) as plan_endorsements,
                SUM(CASE WHEN document_type = 'renewal_notice' THEN 1 ELSE 0 END) as renewal_notices
            FROM {$this->table}
        ");
    }

    /**
     * Download document
     */
    public function getDownloadPath($documentId) {
        $doc = $this->queryOne("
            SELECT file_path FROM {$this->table} WHERE id = ?
        ", [$documentId]);

        return $doc ? $doc['file_path'] : null;
    }

    /**
     * Verify active coverage for employee
     */
    public function verifyActiveCoverage($employeeId) {
        return $this->queryOne("
            SELECT hd.*,
                   hp.plan_name,
                   hpr.provider_name,
                   ee.coverage_type
            FROM {$this->table} hd
            LEFT JOIN employee_hmo_enrollments ee ON hd.enrollment_id = ee.id
            LEFT JOIN hmo_plans hp ON ee.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hp.provider_id = hpr.id
            WHERE hd.employee_id = ?
              AND hd.document_type = 'hmo_card'
              AND hd.status = 'active'
              AND (hd.expiry_date IS NULL OR hd.expiry_date > NOW())
            ORDER BY hd.issue_date DESC
            LIMIT 1
        ", [$employeeId]);
    }

    /**
     * Get documents requiring renewal
     */
    public function getRequiringRenewal() {
        return $this->query("
            SELECT hd.*,
                   emp.employee_code,
                   CONCAT(emp.first_name, ' ', emp.last_name) as employee_name,
                   hp.plan_name,
                   hpr.provider_name
            FROM {$this->table} hd
            LEFT JOIN employees emp ON hd.employee_id = emp.id
            LEFT JOIN hmo_plans hp ON hd.plan_id = hp.id
            LEFT JOIN hmo_providers hpr ON hd.provider_id = hpr.id
            WHERE hd.status = 'expired'
              AND DATE_ADD(hd.expiry_date, INTERVAL 1 MONTH) > NOW()
            ORDER BY hd.expiry_date DESC
        ");
    }
}

