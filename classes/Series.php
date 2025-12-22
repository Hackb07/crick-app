<?php
/**
 * Series Model
 * 
 * Manages series data and operations.
 * Extends BaseModel to eliminate code duplication.
 */

class Series extends BaseModel {
    /** @var string Table name */
    protected $tableName = 'series';
    
    /** @var string Primary key column */
    protected $primaryKey = 'series_id';
    
    /**
     * Constructor
     * 
     * Accepts PDO connection via dependency injection.
     * Falls back to Database singleton if not provided (for backward compatibility).
     * 
     * @param PDO|null $db Database connection (optional, for dependency injection)
     */
    public function __construct(?PDO $db = null) {
        parent::__construct($db);
    }
    
    /**
     * Get all series
     * 
     * Overrides base getAll() to provide custom ordering.
     * 
     * @param array $filters Optional filters (not used, but required for compatibility)
     * @return array List of series
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT * FROM {$this->tableName} ORDER BY start_date DESC, created_at DESC";
        $stmt = $this->executeQuery($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get series by ID
     * 
     * Uses base class implementation.
     * 
     * @param int $seriesId Series ID
     * @return array|null Series data or null
     */
    public function getById(int $seriesId): ?array {
        return parent::getById($seriesId);
    }

    /**
     * Get series by name
     * 
     * @param string $name Series name
     * @return array|null Series data or null
     */
    public function getByName(string $name): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE name = :name LIMIT 1";
        $stmt = $this->executeQuery($sql, ['name' => $name]);
        $series = $stmt->fetch();
        return $series ?: null;
    }
    
    /**
     * Create new series
     * 
     * @param array $data Series data (name, start_date, end_date, description)
     * @return int|false Series ID or false on failure
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->tableName} (name, start_date, end_date, description, created_at) 
                VALUES (:name, :start_date, :end_date, :description, NOW())";
        
        try {
            $stmt = $this->executeQuery($sql, [
                'name' => $data['name'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'description' => $data['description'] ?? ''
            ]);
            
            return (int)$this->getDb()->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to create series: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update series
     * 
     * @param int $seriesId Series ID
     * @param array $data Series data
     * @return bool Success
     */
    public function update($seriesId, $data) {
        $sql = "UPDATE {$this->tableName} 
                SET name = :name, start_date = :start_date, end_date = :end_date, description = :description 
                WHERE {$this->primaryKey} = :series_id";
        
        try {
            $this->executeQuery($sql, [
                'series_id' => $seriesId,
                'name' => $data['name'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'description' => $data['description'] ?? ''
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Failed to update series: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete series
     * 
     * @param int $seriesId Series ID
     * @return bool Success
     */
    public function delete($seriesId) {
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :series_id";
        try {
            $this->executeQuery($sql, ['series_id' => $seriesId]);
            return true;
        } catch (PDOException $e) {
            error_log('Failed to delete series: ' . $e->getMessage());
            return false;
        }
    }
}
