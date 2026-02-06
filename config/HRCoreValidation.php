<?php
/**
 * HR Core API Validation & Integration Helper
 * Provides validation functions and integration utilities for submodules
 */

class HRCoreValidation {
    
    /**
     * Validate employee exists and is active
     * Used by Payroll, Compensation, and other modules
     */
    public static function validateEmployee($employee_id) {
        $db = (new Database())->connect();
        
        $query = "SELECT employee_id, employment_status FROM employees WHERE employee_id = ? AND employment_status = 'Active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$employee_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? true : false;
    }
    
    /**
     * Get employee full details for external modules
     */
    public static function getEmployeeForSync($employee_id) {
        $db = (new Database())->connect();
        
        $query = "
            SELECT 
                e.employee_id,
                e.employee_code,
                e.first_name,
                e.last_name,
                e.email,
                e.phone,
                e.date_of_birth,
                e.gender,
                e.department_id,
                e.job_title_id,
                e.employment_type_id,
                e.location_id,
                e.employment_status,
                e.date_of_joining,
                e.last_working_day,
                d.department_name,
                jt.title as job_title,
                et.type_name as employment_type,
                l.location_name
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.department_id
            LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
            LEFT JOIN employment_types et ON e.employment_type_id = et.employment_type_id
            LEFT JOIN locations l ON e.location_id = l.location_id
            WHERE e.employee_id = ?
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$employee_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all active employees for bulk sync
     */
    public static function getAllActiveEmployees() {
        $db = (new Database())->connect();
        
        $query = "
            SELECT 
                e.employee_id,
                e.employee_code,
                e.first_name,
                e.last_name,
                e.email,
                e.phone,
                e.department_id,
                e.job_title_id,
                e.employment_type_id,
                e.location_id,
                e.employment_status,
                e.date_of_joining,
                d.department_name,
                jt.title as job_title
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.department_id
            LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
            WHERE e.employment_status = 'Active'
            ORDER BY e.employee_id
        ";
        
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Validate department exists
     */
    public static function validateDepartment($department_id) {
        $db = (new Database())->connect();
        
        $query = "SELECT department_id FROM departments WHERE department_id = ? AND status = 'Active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$department_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
    
    /**
     * Validate job title exists
     */
    public static function validateJobTitle($job_title_id) {
        $db = (new Database())->connect();
        
        $query = "SELECT job_title_id FROM job_titles WHERE job_title_id = ? AND status = 'Active'";
        $stmt = $db->prepare($query);
        $stmt->execute([$job_title_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }
    
    /**
     * Get employees by department for Payroll bulk operations
     */
    public static function getEmployeesByDepartment($department_id) {
        $db = (new Database())->connect();
        
        $query = "
            SELECT 
                e.employee_id,
                e.employee_code,
                e.first_name,
                e.last_name,
                e.email,
                e.job_title_id,
                e.employment_type_id,
                jt.title as job_title
            FROM employees e
            LEFT JOIN job_titles jt ON e.job_title_id = jt.job_title_id
            WHERE e.department_id = ? AND e.employment_status = 'Active'
            ORDER BY e.first_name ASC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$department_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Log inter-module API calls for audit trail
     */
    public static function logModuleCall($source_module, $target_module, $action, $status, $details = null) {
        $db = (new Database())->connect();
        
        $query = "
            INSERT INTO module_integration_logs 
            (source_module, target_module, action, status, details, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $db->prepare($query);
        return $stmt->execute([
            $source_module,
            $target_module,
            $action,
            $status,
            $details ? json_encode($details) : null
        ]);
    }
}
?>
