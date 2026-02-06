<?php
/**
 * Employee Document Model
 * Manages employee documents (IDs, certificates, contracts, etc.)
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeeDocument extends BaseModel {
    protected $table = 'employee_documents';
    protected $primaryKey = 'document_id';  // Correct primary key
    protected $fillable = ['employee_id', 'document_type', 'document_name', 'file_path', 'issue_date', 'expiry_date', 'remarks', 'uploaded_by', 'created_at', 'updated_at'];
    protected $hidden = [];

    /**
     * Get documents by employee
     */
    public function getByEmployee($employeeId) {
        $query = "
            SELECT 
                ed.*,
                CONCAT(u.first_name, ' ', u.last_name) as uploaded_by_name
            FROM {$this->table} ed
            LEFT JOIN users u ON ed.uploaded_by = u.user_id
            WHERE ed.employee_id = ?
            ORDER BY ed.created_at DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get documents by type
     */
    public function getByType($employeeId, $docType) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE employee_id = ? AND document_type = ?
            ORDER BY created_at DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$employeeId, $docType]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get expiring documents
     */
    public function getExpiringDocuments($daysThreshold = 30) {
        $query = "
            SELECT 
                ed.*,
                e.employee_code,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                DATEDIFF(ed.expiry_date, CURDATE()) as days_to_expiry
            FROM {$this->table} ed
            JOIN employees e ON ed.employee_id = e.employee_id
            WHERE ed.expiry_date IS NOT NULL
            AND DATEDIFF(ed.expiry_date, CURDATE()) <= ?
            AND DATEDIFF(ed.expiry_date, CURDATE()) > 0
            ORDER BY ed.expiry_date ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$daysThreshold]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get expired documents
     */
    public function getExpiredDocuments() {
        $query = "
            SELECT 
                ed.*,
                e.employee_code,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM {$this->table} ed
            JOIN employees e ON ed.employee_id = e.employee_id
            WHERE ed.expiry_date IS NOT NULL
            AND ed.expiry_date < CURDATE()
            ORDER BY ed.expiry_date DESC
        ";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
