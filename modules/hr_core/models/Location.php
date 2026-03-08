<?php
/**
 * Location Model
 * Manages work locations/offices with operational flags for scheduling and payroll
 */

require_once __DIR__ . '/../../../config/BaseModel.php';

class Location extends BaseModel {
    protected $table = 'locations';
    protected $primaryKey = 'location_id';  // Correct primary key
    protected $fillable = [
        'location_code', 
        'location_name', 
        'location_type', 
        'facility_name',
        'address', 
        'city', 
        'state', 
        'country', 
        'postal_code', 
        'contact_number',
        'phone', 
        'email', 
        'manager_id', 
        'payroll_eligible',
        'scheduling_enabled',
        'allow_overtime',
        'allow_night_shift',
        'notes',
        'status', 
        'created_at', 
        'updated_at'
    ];
    protected $hidden = [];

    /**
     * Get location with employee count
     */
    public function getWithDetails($id) {
        $query = "
            SELECT 
                l.*,
                COUNT(DISTINCT e.employee_id) as employee_count
            FROM {$this->table} l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            WHERE l.location_id = ?
            GROUP BY l.location_id
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all active locations with employee count and staffing stats
     */
    public function getAllWithDetails($limit = null, $offset = null) {
        $query = "
            SELECT 
                l.*,
                COUNT(DISTINCT e.employee_id) as employee_count
            FROM {$this->table} l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            WHERE l.status = 'active'
            GROUP BY l.location_id
            ORDER BY l.created_at DESC
        ";

        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get locations without staff
     */
    public function getLocationsWithoutStaff() {
        $query = "
            SELECT 
                l.*,
                COUNT(DISTINCT e.employee_id) as employee_count
            FROM {$this->table} l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            WHERE l.status = 'active'
            GROUP BY l.location_id
            HAVING COUNT(DISTINCT e.employee_id) = 0
            ORDER BY l.location_name ASC
        ";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get recently added locations
     */
    public function getRecentlyAdded($limit = 5) {
        $query = "
            SELECT 
                l.*,
                COUNT(DISTINCT e.employee_id) as employee_count
            FROM {$this->table} l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            WHERE l.status = 'active'
            GROUP BY l.location_id
            ORDER BY l.created_at DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get locations stats
     */
    public function getStats() {
        $query = "
            SELECT 
                COUNT(DISTINCT l.location_id) as total,
                SUM(CASE WHEN l.status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN e.employee_id IS NOT NULL THEN 1 ELSE 0 END) as total_staff,
                COUNT(DISTINCT CASE WHEN e.employee_id IS NULL AND l.status = 'active' THEN l.location_id END) as no_staff_count
            FROM {$this->table} l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
        ";
        $stmt = $this->db->query($query);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Search locations by city or facility
     */
    public function searchByCity($city) {
        $query = "
            SELECT 
                l.*,
                COUNT(DISTINCT e.employee_id) as employee_count
            FROM {$this->table} l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            WHERE l.city LIKE ? AND l.status = 'active'
            GROUP BY l.location_id
            ORDER BY l.location_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["%{$city}%"]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get locations by type
     */
    public function getByType($type) {
        $query = "
            SELECT 
                l.*,
                COUNT(DISTINCT e.employee_id) as employee_count
            FROM {$this->table} l
            LEFT JOIN employees e ON l.location_id = e.location_id AND e.employment_status = 'Active'
            WHERE l.location_type = ? AND l.status = 'active'
            GROUP BY l.location_id
            ORDER BY l.location_name ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$type]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
