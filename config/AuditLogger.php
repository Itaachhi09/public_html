<?php
/**
 * Audit Logger
 * Tracks all user actions for compliance and security
 */

class AuditLogger {
    protected $db;
    protected $table = 'audit_logs';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Log an action
     */
    public function log($action, $description, $entity_id, $user_id, $old_values = null, $new_values = null) {
        $query = "
            INSERT INTO {$this->table} 
            (action, description, entity_id, user_id, old_values, new_values, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";

        try {
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                $action,
                $description,
                $entity_id,
                $user_id,
                json_encode($old_values),
                json_encode($new_values)
            ]);
        } catch (PDOException $e) {
            error_log('AuditLogger: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get audit logs with filters
     */
    public function getLog($limit = 50, $offset = 0, $actionType = null) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";

        if ($actionType) {
            $query .= " AND action = ?";
        }

        $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($query);
        
        if ($actionType) {
            $stmt->execute([$actionType, $limit, $offset]);
        } else {
            $stmt->execute([$limit, $offset]);
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get total audit log count
     */
    public function getTotalCount($actionType = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";

        if ($actionType) {
            $query .= " WHERE action = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$actionType]);
        } else {
            $stmt = $this->db->query($query);
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get audit logs for specific entity
     */
    public function getEntityAudit($entityId, $limit = 20) {
        $query = "
            SELECT al.*, u.first_name, u.last_name
            FROM {$this->table} al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.entity_id = ?
            ORDER BY al.created_at DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$entityId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get audit logs by user
     */
    public function getUserAudit($userId, $limit = 20) {
        $query = "
            SELECT * FROM {$this->table}
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Export audit logs to CSV
     */
    public function exportAuditCsv($actionType = null) {
        $logs = $this->getLog(10000, 0, $actionType);

        $headers = ['Action', 'Description', 'Entity ID', 'User ID', 'Created At'];
        $csv = implode(',', $headers) . "\n";

        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log['action'],
                str_replace(',', '', $log['description']),
                $log['entity_id'],
                $log['user_id'],
                $log['created_at']
            ]) . "\n";
        }

        return $csv;
    }
}
?>
