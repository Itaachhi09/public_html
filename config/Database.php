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
    private static $instance = null;

    /**
     * Singleton pattern - Get instance of Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->connect();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection object
     */
    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

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
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        return $this->conn;
    }

    /**
     * Execute query with prepared statements and parameters
     * Supports both SELECT and DML (INSERT, UPDATE, DELETE) operations
     * @param string $sql SQL query with ? placeholders
     * @param array $params Parameter values to bind
     * @return mixed Returns PDOStatement for SELECT or result for others
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            // Check if this is a SELECT statement
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " SQL: " . $sql);
            throw new RuntimeException("Query execution failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Prepare a statement for manual binding and execution
     * @param string $sql SQL query with named placeholders
     * @return PDOStatement
     */
    public function prepare($sql) {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn->prepare($sql);
    }

    /**
     * Execute direct query without parameters
     * @deprecated Use query($sql, []) instead for single queries
     */
    public function directQuery($sql) {
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
