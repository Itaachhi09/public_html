<?php
/**
 * Location Model
 */

class LocationModel extends BaseModel {
    protected $table = 'locations';
    protected $primaryKey = 'location_id';
    protected $fillable = [
        'location_name',
        'city',
        'state',
        'country',
        'address',
        'postal_code',
        'status'
    ];

    /**
     * Get all locations with pagination
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY location_id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active locations
     */
    public function getActive() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE status = ? ORDER BY location_name ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['Active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
