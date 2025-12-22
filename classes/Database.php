<?php
/**
 * Database Singleton Class
 * 
 * Provides PDO database connection with singleton pattern
 */

class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Persistent connection for shared hosting
            ];
            
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                die('Database connection failed: ' . $e->getMessage());
            } else {
                die('Database connection failed. Please check configuration.');
            }
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

