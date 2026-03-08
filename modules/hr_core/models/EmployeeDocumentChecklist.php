<?php
/**
 * Employee Document Checklist Model
 * Manages employee document submission and verification
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeeDocumentChecklist extends BaseModel {
    protected $table = 'employee_document_checklist';
    protected $primaryKey = 'checklist_id';
    protected $fillable = ['employee_id', 'requirement_id', 'document_id', 'submitted_date', 'is_submitted', 'is_verified', 'verified_by', 'verified_date', 'remarks', 'status'];

    /**
     * Get employee's document checklist with requirements
     */
    public function getEmployeeChecklist($employeeId) {
        $query = "
            SELECT 
                edc.*,
                dr.document_type,
                dr.document_description,
                dr.is_mandatory,
                dr.requires_expiry_date,
                dr.expiry_alert_days,
                dc.category_name,
                dc.sort_order,
                ed.file_path,
                ed.expiry_date as document_expiry_date,
                CONCAT(e.first_name, ' ', e.last_name) as verified_by_name
            FROM {$this->table} edc
            INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
            INNER JOIN document_categories dc ON dr.category_id = dc.category_id
            LEFT JOIN employee_documents ed ON edc.document_id = ed.document_id
            LEFT JOIN employees e ON edc.verified_by = e.employee_id
            WHERE edc.employee_id = ?
            ORDER BY dc.sort_order ASC, dr.document_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get document checklist by category
     */
    public function getByCategory($employeeId, $categoryId) {
        $query = "
            SELECT 
                edc.*,
                dr.document_type,
                dr.document_description,
                dr.is_mandatory,
                dc.category_name,
                ed.file_path,
                ed.expiry_date as document_expiry_date
            FROM {$this->table} edc
            INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
            INNER JOIN document_categories dc ON dr.category_id = dc.category_id
            LEFT JOIN employee_documents ed ON edc.document_id = ed.document_id
            WHERE edc.employee_id = ? AND dr.category_id = ?
            ORDER BY dr.document_type ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId, $categoryId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get completion summary for employee
     */
    public function getCompletionSummary($employeeId) {
        $query = "
            SELECT 
                COUNT(*) as total_required,
                SUM(CASE WHEN status = 'Verified' THEN 1 ELSE 0 END) as verified,
                SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status = 'Not Submitted' THEN 1 ELSE 0 END) as not_submitted,
                SUM(CASE WHEN status = 'Expired' THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = 'Pending Renewal' THEN 1 ELSE 0 END) as pending_renewal,
                ROUND((SUM(CASE WHEN status IN ('Verified', 'Submitted') THEN 1 ELSE 0 END) / COUNT(*) * 100), 2) as completion_percentage
            FROM {$this->table}
            WHERE employee_id = ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get pending documents for review
     */
    public function getPendingReview() {
        $query = "
            SELECT 
                edc.*,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                e.employee_id,
                d.department_name,
                dr.document_type,
                dr.document_description
            FROM {$this->table} edc
            INNER JOIN employees e ON edc.employee_id = e.employee_id
            INNER JOIN departments d ON e.department_id = d.department_id
            INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
            WHERE edc.status = 'Submitted' AND edc.is_verified = FALSE
            ORDER BY edc.submitted_date ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get expiring documents
     */
    public function getExpiringDocuments($daysThreshold = 30) {
        $query = "
            SELECT 
                edc.*,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                e.employee_id,
                d.department_name,
                dr.document_type,
                ed.expiry_date,
                DATEDIFF(ed.expiry_date, CURDATE()) as days_until_expiry
            FROM {$this->table} edc
            INNER JOIN employees e ON edc.employee_id = e.employee_id
            INNER JOIN departments d ON e.department_id = d.department_id
            INNER JOIN document_requirements dr ON edc.requirement_id = dr.requirement_id
            LEFT JOIN employee_documents ed ON edc.document_id = ed.document_id
            WHERE ed.expiry_date IS NOT NULL
            AND DATEDIFF(ed.expiry_date, CURDATE()) BETWEEN 0 AND ?
            AND edc.status IN ('Verified', 'Submitted')
            ORDER BY ed.expiry_date ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$daysThreshold]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Mark document as verified
     */
    public function verifyDocument($checklistId, $verifiedBy) {
        $query = "
            UPDATE {$this->table}
            SET is_verified = TRUE,
                verified_by = ?,
                verified_date = CURDATE(),
                status = 'Verified'
            WHERE checklist_id = ?
        ";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$verifiedBy, $checklistId]);
    }

    /**
     * Mark document as submitted
     */
    public function submitDocument($employeeId, $requirementId, $documentId) {
        $query = "
            UPDATE {$this->table}
            SET document_id = ?,
                submitted_date = CURDATE(),
                is_submitted = TRUE,
                status = 'Submitted'
            WHERE employee_id = ? AND requirement_id = ?
        ";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$documentId, $employeeId, $requirementId]);
    }

    /**
     * Initialize document checklist for new employee
     */
    public function initializeForEmployee($employeeId, $employmentStatus = 'Permanent') {
        try {
            // Get all requirements applicable to this employment status
            $reqModel = new DocumentRequirement();
            $requirements = $reqModel->getByEmploymentStatus($employmentStatus);

            foreach ($requirements as $req) {
                $query = "
                    INSERT INTO {$this->table} (employee_id, requirement_id, status)
                    VALUES (?, ?, 'Not Submitted')
                    ON DUPLICATE KEY UPDATE status = 'Not Submitted'
                ";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$employeeId, $req['requirement_id']]);
            }
            return true;
        } catch (\Exception $e) {
            error_log("Error initializing document checklist: " . $e->getMessage());
            return false;
        }
    }
}

?>
