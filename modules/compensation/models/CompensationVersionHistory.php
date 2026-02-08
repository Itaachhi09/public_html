<?php
/**
 * Compensation Version History Model
 * Track versions: version number, effective date, changed by, reason. Old versions immutable. Payroll reads only approved active.
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class CompensationVersionHistory extends BaseModel {
    protected $table = 'compensation_version_history';
    protected $primaryKey = 'id';
    protected $fillable = [
        'entity_type', 'entity_id', 'version_number', 'effective_date', 'changed_by', 'reason',
        'status', 'is_active', 'snapshot_json'
    ];

    /**
     * Get next version number for (entity_type, entity_id)
     */
    public function getNextVersionNumber($entityType, $entityId) {
        $row = $this->queryOne(
            "SELECT COALESCE(MAX(version_number), 0) + 1 AS next FROM {$this->table} WHERE entity_type = ? AND entity_id = ?",
            [$entityType, $entityId]
        );
        return (int) ($row['next'] ?? 1);
    }

    /**
     * Get all version history (audit trail)
     */
    public function getAll($filters = []) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        if (!empty($filters['entity_type'])) {
            $query .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        if (isset($filters['entity_id'])) {
            $query .= " AND entity_id = ?";
            $params[] = $filters['entity_id'];
        }
        if (isset($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        if (isset($filters['is_active'])) {
            $query .= " AND is_active = ?";
            $params[] = (int) $filters['is_active'];
        }
        $query .= " ORDER BY entity_type, entity_id, version_number DESC";
        if (!empty($filters['limit'])) {
            $query .= " LIMIT " . (int) $filters['limit'];
        }
        return $this->query($query, $params);
    }

    /**
     * Get approved active versions only (for Payroll - clean and safe dependency)
     */
    public function getApprovedActive() {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE status = 'approved' AND is_active = 1 ORDER BY entity_type, entity_id"
        );
    }

    /**
     * Set only one row active for (entity_type, entity_id); others become inactive (immutable: we don't update old rows' data, only is_active)
     */
    public function setActiveOnly($entityType, $entityId, $versionId) {
        $this->execute(
            "UPDATE {$this->table} SET is_active = 0 WHERE entity_type = ? AND entity_id = ? AND id != ?",
            [$entityType, $entityId, $versionId]
        );
        $this->execute("UPDATE {$this->table} SET is_active = 1 WHERE id = ?", [$versionId]);
    }
}
