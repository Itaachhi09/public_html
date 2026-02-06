<?php
/**
 * Employment Type Model
 * Manages employment types (Regular, Probationary, Contractual, etc.)
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class EmploymentType extends BaseModel {
    protected $table = 'employment_types';
    protected $primaryKey = 'employment_type_id';  // Correct primary key
    protected $fillable = ['type_name', 'description', 'probation_period_days', 'contract_duration_months', 'benefits_eligible', 'status', 'created_at', 'updated_at'];
    protected $hidden = [];

    /**
     * Get all employment types
     */
    public function getActive() {
        $query = "SELECT * FROM {$this->table} WHERE status = 'Active'";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get employment type with count of employees
     */
    public function getWithEmployeeCount($id) {
        $query = "
            SELECT 
                et.*,
                COUNT(e.employee_id) as employee_count
            FROM {$this->table} et
            LEFT JOIN employees e ON et.employment_type_id = e.employment_type_id
            WHERE et.employment_type_id = ?
            GROUP BY et.employment_type_id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
