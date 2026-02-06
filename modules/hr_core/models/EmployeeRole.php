<?php
/**
 * Employee Role Model
 * Manages employee system roles and permissions with security-focused queries
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class EmployeeRole extends BaseModel {
    protected $table = 'user_roles';
    protected $fillable = ['user_id', 'role_id', 'assigned_date', 'assigned_by', 'status'];
    protected $hidden = [];

    /**
     * High-risk permissions
     */
    private $HIGH_RISK_PERMS = ['employee_delete', 'document_delete', 'payroll_process', 'finance_edit', 'roles_manage', 'user_delete', 'movement_delete'];

    /**
     * Get roles by user/employee
     */
    public function getByUser($userId) {
        $query = "
            SELECT 
                ur.*,
                r.role_id,
                r.role_name,
                r.role_category,
                r.description
            FROM {$this->table} ur
            JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.user_id = ? AND ur.status = 'Active'
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get permissions by user
     */
    public function getPermissionsByUser($userId) {
        $query = "
            SELECT DISTINCT
                r.permission_id,
                r.permission_name
            FROM {$this->table} ur
            JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.user_id = ? AND ur.status = 'Active'
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($userId, $permission) {
        $query = "
            SELECT COUNT(*) as count
            FROM {$this->table} ur
            JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.user_id = ? AND ur.status = 'Active'
            AND FIND_IN_SET(?, r.permissions)
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $permission]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Get all roles with details (for roles management page)
     */
    public function getAllRolesWithDetails($filter = null) {
        $query = "
            SELECT 
                r.*,
                COUNT(DISTINCT ur.user_id) as user_count,
                r.updated_at
            FROM roles r
            LEFT JOIN {$this->table} ur ON r.role_id = ur.role_id AND ur.status = 'Active'
            GROUP BY r.role_id
            ORDER BY r.created_at DESC
        ";
        
        $stmt = $this->db->query($query);
        $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Add risk calculation
        foreach ($roles as &$role) {
            $perms = $role['permissions'] ? explode(',', $role['permissions']) : [];
            $role['high_risk_count'] = count(array_intersect($perms, $this->HIGH_RISK_PERMS));
            $role['is_high_privilege'] = $role['high_risk_count'] > 0;
        }
        
        return $roles;
    }

    /**
     * Get high privilege roles
     */
    public function getHighPrivilegeRoles() {
        $query = "
            SELECT 
                r.*,
                COUNT(DISTINCT ur.user_id) as user_count
            FROM roles r
            LEFT JOIN {$this->table} ur ON r.role_id = ur.role_id AND ur.status = 'Active'
            WHERE r.permissions IS NOT NULL
            GROUP BY r.role_id
            ORDER BY COUNT(DISTINCT ur.user_id) DESC
        ";
        
        $stmt = $this->db->query($query);
        $roles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Filter for high-risk permissions
        $highRisk = [];
        foreach ($roles as $role) {
            $perms = $role['permissions'] ? explode(',', $role['permissions']) : [];
            if (count(array_intersect($perms, $this->HIGH_RISK_PERMS)) > 0) {
                $highRisk[] = $role;
            }
        }
        
        return $highRisk;
    }

    /**
     * Get roles without users
     */
    public function getRolesWithoutUsers() {
        $query = "
            SELECT 
                r.*,
                COUNT(DISTINCT ur.user_id) as user_count
            FROM roles r
            LEFT JOIN {$this->table} ur ON r.role_id = ur.role_id AND ur.status = 'Active'
            WHERE r.status = 'active'
            GROUP BY r.role_id
            HAVING COUNT(DISTINCT ur.user_id) = 0
            ORDER BY r.role_name ASC
        ";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recently modified roles
     */
    public function getRecentlyModified($limit = 5) {
        $query = "
            SELECT 
                r.*,
                COUNT(DISTINCT ur.user_id) as user_count
            FROM roles r
            LEFT JOIN {$this->table} ur ON r.role_id = ur.role_id AND ur.status = 'Active'
            WHERE r.status = 'active'
            GROUP BY r.role_id
            ORDER BY r.updated_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get role statistics
     */
    public function getRoleStats() {
        $query = "
            SELECT 
                COUNT(DISTINCT r.role_id) as total,
                SUM(CASE WHEN r.role_category = 'system' THEN 1 ELSE 0 END) as system_roles,
                SUM(CASE WHEN r.role_category = 'management' THEN 1 ELSE 0 END) as management_roles,
                SUM(CASE WHEN r.role_category = 'operational' THEN 1 ELSE 0 END) as operational_roles,
                SUM(CASE WHEN r.role_category = 'enduser' THEN 1 ELSE 0 END) as enduser_roles,
                SUM(CASE WHEN r.status = 'inactive' THEN 1 ELSE 0 END) as disabled,
                SUM(CASE WHEN ur.user_id IS NOT NULL THEN 1 ELSE 0 END) as in_use
            FROM roles r
            LEFT JOIN {$this->table} ur ON r.role_id = ur.role_id AND ur.status = 'Active'
        ";
        
        $stmt = $this->db->query($query);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Calculate high privilege count
        $high_priv_query = "
            SELECT COUNT(DISTINCT r.role_id) as count
            FROM roles r
            WHERE r.permissions IS NOT NULL
        ";
        $hp_stmt = $this->db->query($high_priv_query);
        $hp_result = $hp_stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Filter for high-risk only (would need to check each role)
        $stats['high_privilege'] = $hp_result['count'] > 0 ? $hp_result['count'] : 0;
        
        return $stats;
    }

    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId, $assignedBy) {
        $query = "
            INSERT INTO {$this->table} (user_id, role_id, assigned_date, assigned_by, status)
            VALUES (?, ?, NOW(), ?, 'Active')
        ";
        return $this->db->prepare($query)->execute([$userId, $roleId, $assignedBy]);
    }

    /**
     * Revoke role from user
     */
    public function revokeRole($userId, $roleId) {
        $query = "
            UPDATE {$this->table}
            SET status = 'Inactive', updated_at = NOW()
            WHERE user_id = ? AND role_id = ?
        ";
        return $this->db->prepare($query)->execute([$userId, $roleId]);
    }

    /**
     * Check if role is system protected
     */
    public function isSystemRole($roleName) {
        $systemRoles = ['admin', 'super_admin', 'employee'];
        return in_array(strtolower($roleName), $systemRoles);
    }

    /**
     * Get roles by category
     */
    public function getByCategory($category) {
        $query = "
            SELECT 
                r.*,
                COUNT(DISTINCT ur.user_id) as user_count
            FROM roles r
            LEFT JOIN {$this->table} ur ON r.role_id = ur.role_id AND ur.status = 'Active'
            WHERE r.role_category = ? AND r.status = 'active'
            GROUP BY r.role_id
            ORDER BY r.role_name ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
