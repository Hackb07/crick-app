<?php
/**
 * Base Model Class
 * 
 * Provides common functionality for all model classes:
 * - Database connection via dependency injection
 * - Common CRUD operations
 * - Consistent error handling
 * - Query execution helpers
 * 
 * All model classes should extend this base class to eliminate duplication.
 */
abstract class BaseModel {
    /** @var PDO Database connection */
    protected $db;
    
    /** @var string Table name (must be set by child class) */
    protected $tableName;
    
    /** @var string Primary key column name (default: 'id') */
    protected $primaryKey = 'id';
    
    /**
     * Constructor
     * 
     * Accepts PDO connection via dependency injection.
     * Falls back to Database singleton if not provided (for backward compatibility).
     * 
     * @param PDO|null $db Database connection (optional, for dependency injection)
     */
    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
        
        if (empty($this->tableName)) {
            throw new RuntimeException('Child class must set $tableName property');
        }
    }
    
    /**
     * Get database connection
     * 
     * @return PDO Database connection
     */
    protected function getDb(): PDO {
        return $this->db;
    }
    
    /**
     * Execute a prepared statement with error handling
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return PDOStatement Executed statement
     * @throws PDOException If query execution fails
     */
    protected function executeQuery(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log(sprintf(
                'Database query failed in %s: %s | SQL: %s | Params: %s',
                get_class($this),
                $e->getMessage(),
                $sql,
                json_encode($params)
            ));
            throw $e;
        }
    }
    
    /**
     * Get record by ID
     * 
     * Generic implementation that can be overridden by child classes.
     * 
     * @param int $id Record ID
     * @return array|null Record data or null if not found
     */
    public function getById(int $id): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->executeQuery($sql, ['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Get all records with optional filters
     * 
     * Generic implementation. Child classes should override for custom filtering.
     * 
     * @param array $filters Filter options (implementation-specific)
     * @return array List of records
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT * FROM {$this->tableName}";
        $params = [];
        
        // Add WHERE clause if filters provided
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $key => $value) {
                if ($value !== null && $value !== '') {
                    $conditions[] = "{$key} = :{$key}";
                    $params[$key] = $value;
                }
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
        }
        
        $sql .= " ORDER BY {$this->primaryKey} DESC";
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if record exists
     * 
     * @param int $id Record ID
     * @return bool True if exists
     */
    public function exists(int $id): bool {
        $sql = "SELECT 1 FROM {$this->tableName} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->executeQuery($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Count records
     * 
     * @param array $filters Optional filters
     * @return int Record count
     */
    public function count(array $filters = []): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName}";
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $key => $value) {
                if ($value !== null && $value !== '') {
                    $conditions[] = "{$key} = :{$key}";
                    $params[$key] = $value;
                }
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
        }
        
        $stmt = $this->executeQuery($sql, $params);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
}

