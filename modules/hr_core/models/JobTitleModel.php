<?php
/**
 * Job Title Model
 */

class JobTitleModel extends BaseModel {
    protected $table = 'job_titles';
    protected $primaryKey = 'job_title_id';
    protected $fillable = [
        'title',
        'description',
        'level',
        'department_id',
        'status'
    ];

    /**
     * Get all job titles with pagination
     */
    public function getAllPaginated($limit = 10, $offset = 0) {
        $query = 'SELECT jt.*, jt.title AS job_title_name, d.department_name FROM ' . $this->table . ' jt 
                  LEFT JOIN departments d ON jt.department_id = d.department_id 
              ORDER BY jt.job_title_id DESC LIMIT ? OFFSET ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get by ID with department info
     */
    public function findWithDepartment($id) {
        $query = 'SELECT jt.*, jt.title AS job_title_name, d.department_name FROM ' . $this->table . ' jt 
                  LEFT JOIN departments d ON jt.department_id = d.department_id 
                  WHERE jt.job_title_id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get active job titles
     */
    public function getActive() {
        $query = 'SELECT *, title AS job_title_name FROM ' . $this->table . ' WHERE status = ? ORDER BY title ASC';
        $stmt = $this->db->prepare($query);
        $stmt->execute(['Active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Override create to accept 'job_title_name' from older code and map to 'title'
     */
    public function create($data) {
        if (isset($data['job_title_name'])) {
            $data['title'] = $data['job_title_name'];
            unset($data['job_title_name']);
        }
        return parent::create($data);
    }

    /**
     * Override update to accept 'job_title_name' and map to 'title'
     */
    public function update($id, $data) {
        if (isset($data['job_title_name'])) {
            $data['title'] = $data['job_title_name'];
            unset($data['job_title_name']);
        }
        return parent::update($id, $data);
    }
}
?>
