<?php
/**
 * Base Model
 * Abstract base class for all models
 */

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';  // Default - override in child classes
    protected $fillable = [];
    protected $hidden = [];

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    /**
     * Get primary key column name
     */
    public function getPrimaryKey() {
        return $this->primaryKey;
    }

    /**
     * Get table name
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * Find by ID
     */
    public function find($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get by ID (alias for find)
     */
    public function getById($id) {
        return $this->find($id);
    }

    /**
     * Get all records
     */
    public function all($limit = null, $offset = 0) {
        $query = 'SELECT * FROM ' . $this->table;
        
        if ($limit) {
            $query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new record
     */
    public function create($data) {
        $filtered_data = $this->filterFillable($data);
        $columns = implode(', ', array_keys($filtered_data));
        $placeholders = implode(', ', array_fill(0, count($filtered_data), '?'));

        $query = 'INSERT INTO ' . $this->table . ' (' . $columns . ') VALUES (' . $placeholders . ')';
        $stmt = $this->db->prepare($query);
        
        if ($stmt->execute(array_values($filtered_data))) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Update record
     */
    public function update($id, $data) {
        $filtered_data = $this->filterFillable($data);
        
        if (empty($filtered_data)) {
            return false;
        }

        $set = implode('=?, ', array_keys($filtered_data)) . '=?';
        $query = 'UPDATE ' . $this->table . ' SET ' . $set . ' WHERE ' . $this->primaryKey . ' = ?';
        
        $stmt = $this->db->prepare($query);
        $values = array_values($filtered_data);
        $values[] = $id;

        return $stmt->execute($values);
    }

    /**
     * Delete record
     */
    public function delete($id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = ?';
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Filter data by fillable fields
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Where clause
     */
    public function where($column, $operator, $value) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE ' . $column . ' ' . $operator . ' ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * First record matching condition
     */
    public function first($column, $value) {
        $results = $this->where($column, '=', $value);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Count records
     */
    public function count() {
        $query = 'SELECT COUNT(*) as count FROM ' . $this->table;
        $stmt = $this->db->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Hide sensitive fields
     */
    public function hideFields($record) {
        if (is_array($record)) {
            foreach ($this->hidden as $field) {
                unset($record[$field]);
            }
        }
        return $record;
    }

    /**
     * Direct query execution - returns all results
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Direct query execution for single result
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Execute query with parameters (INSERT, UPDATE, DELETE)
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }
}
?>
