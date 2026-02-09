<?php
/**
 * Database Configuration and Connection Class
 * Microservices Database Manager
 * Timezone: Asia/Manila (Philippines UTC+8)
 */

// Set PHP timezone to Philippines
date_default_timezone_set('Asia/Manila');

class Database {
    private $host = 'localhost';
    private $db_name = 'public_html';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4',
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set MySQL session timezone to Asia/Manila
            $this->conn->exec("SET @@session.time_zone = '+08:00'");
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->conn;
    }

    /**
     * Execute query with prepared statements
     */
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    /**
     * Execute direct query
     */
    public function query($sql) {
        return $this->conn->query($sql);
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->conn->rollBack();
    }
}
?>
