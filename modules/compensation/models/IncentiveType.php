<?php
/**
 * Incentive Type Model
 * Approved procedure types – only these are allowed when logging incentive events.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class IncentiveType extends BaseModel {
    protected $table = 'incentive_types';
    protected $primaryKey = 'id';
    protected $fillable = ['code', 'name', 'description', 'is_approved'];

    /**
     * Get all approved types (for dropdown when logging)
     */
    public function getApproved() {
        return $this->query("SELECT * FROM {$this->table} WHERE is_approved = 1 ORDER BY name ASC");
    }

    /**
     * Get all types (for admin listing)
     */
    public function getAll($approvedOnly = false) {
        $query = "SELECT * FROM {$this->table} ORDER BY name ASC";
        if ($approvedOnly) {
            $query = "SELECT * FROM {$this->table} WHERE is_approved = 1 ORDER BY name ASC";
        }
        return $this->query($query);
    }

    public function isApproved($id) {
        $row = $this->find($id);
        return $row && !empty($row['is_approved']);
    }
}
