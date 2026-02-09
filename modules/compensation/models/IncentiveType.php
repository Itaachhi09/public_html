<?php
/**
 * Incentive Type Model
 * Approved procedure types – only these are allowed when logging incentive events.
 * Enhanced with status tracking, rate configuration, and audit fields.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class IncentiveType extends BaseModel {
    protected $table = 'incentive_types';
    protected $primaryKey = 'id';
    protected $fillable = [
        'code', 'name', 'description', 'is_approved', 'status',
        'rate_type', 'default_rate', 'used_by_roles',
        'configured_by_role', 'last_updated_by_id', 'last_updated_reason'
    ];

    /**
     * Get all approved incentive types (for dropdown when logging)
     */
    public function getApproved() {
        return $this->query("SELECT * FROM {$this->table} WHERE is_approved = 1 ORDER BY name ASC");
    }

    /**
     * Get all types (for admin listing)
     */
    public function getAll($activeOnly = false) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        if ($activeOnly) {
            $query .= " AND is_approved = 1";
        }
        $query .= " ORDER BY name ASC";
        return $this->query($query);
    }

    /**
     * Get by status (active, inactive, archived)
     */
    public function getByStatus($status) {
        $query = "SELECT * FROM {$this->table} WHERE status = ? ORDER BY name ASC";
        return $this->query($query, [$status]);
    }

    /**
     * Check if incentive is active
     */
    public function isActive($id) {
        $row = $this->find($id);
        return $row && ($row['status'] === 'active' || (!empty($row['is_approved']) && $row['status'] !== 'inactive'));
    }

    /**
     * Check if incentive is approved (backward compatibility)
     */
    public function isApproved($id) {
        $row = $this->find($id);
        return $row && (!empty($row['is_approved']) || $row['status'] === 'active');
    }

    /**
     * Get incentives by rate type
     */
    public function getByRateType($rateType) {
        $query = "SELECT * FROM {$this->table} WHERE rate_type = ? AND status = 'active' ORDER BY name ASC";
        return $this->query($query, [$rateType]);
    }

    /**
     * Get incentives used by specific role
     */
    public function getByRole($role) {
        $query = "SELECT * FROM {$this->table} WHERE (used_by_roles IS NULL OR used_by_roles = 'All' OR FIND_IN_SET(?, used_by_roles)) AND status = 'active' ORDER BY name ASC";
        return $this->query($query, [$role]);
    }

    /**
     * Validate code format: uppercase with underscores only
     */
    public static function validateCodeFormat($code) {
        return preg_match('/^[A-Z0-9_]+$/', $code) === 1;
    }

    /**
     * Get count of active incentives for usage check
     */
    public function getActiveCount() {
        $row = $this->queryOne("SELECT COUNT(*) AS c FROM {$this->table} WHERE status = 'active'");
        return (int) ($row['c'] ?? 0);
    }
}
