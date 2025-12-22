<?php
/**
 * Player Model
 * 
 * Manages player data and operations.
 * Extends BaseModel to eliminate code duplication.
 */

class Player extends BaseModel {
    /** @var string Table name */
    protected $tableName = 'players';
    
    /** @var string Primary key column */
    protected $primaryKey = 'player_id';
    
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
     * Get all players
     * 
     * Overrides base method to provide custom filtering (team_id, search).
     * 
     * @param array $filters Optional filters (team_id, search)
     * @return array List of players
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT DISTINCT p.* FROM {$this->tableName} p";
        $params = [];
        
        // Filter by team_id (players who have appeared for this team)
        if (!empty($filters['team_id'])) {
            $sql .= " INNER JOIN player_appearances pa ON p.player_id = pa.player_id 
                     WHERE pa.team_id = :team_id";
            $params['team_id'] = (int)$filters['team_id'];
        } else {
            $sql .= " WHERE 1=1";
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $sql .= " AND p.name LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY p.name ASC";
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get player by ID
     * 
     * Uses base class implementation.
     * 
     * @param int $playerId Player ID
     * @return array|null Player data or null
     */
    public function getById(int $playerId): ?array {
        return parent::getById($playerId);
    }
    
    /**
     * Create new player
     * 
     * @param array $data Player data (name, date_of_birth, profile_image, batting_hand, bowling_style)
     * @return int|false Player ID or false on failure
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->tableName} (name, date_of_birth, profile_image, batting_hand, bowling_style, created_at, updated_at) 
                VALUES (:name, :date_of_birth, :profile_image, :batting_hand, :bowling_style, NOW(), NOW())";
        
        try {
            $stmt = $this->executeQuery($sql, [
                'name' => $data['name'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'profile_image' => $data['profile_image'] ?? null,
                'batting_hand' => $data['batting_hand'] ?? null,
                'bowling_style' => $data['bowling_style'] ?? null
            ]);
            
            return (int)$this->getDb()->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to create player: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update player
     * 
     * @param int $playerId Player ID
     * @param array $data Player data
     * @return bool Success
     */
    public function update($playerId, $data) {
        $sql = "UPDATE {$this->tableName} 
                SET name = :name, date_of_birth = :date_of_birth, profile_image = :profile_image, 
                    batting_hand = :batting_hand, bowling_style = :bowling_style, updated_at = NOW() 
                WHERE {$this->primaryKey} = :player_id";
        
        try {
            $this->executeQuery($sql, [
                'player_id' => $playerId,
                'name' => $data['name'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'profile_image' => $data['profile_image'] ?? null,
                'batting_hand' => $data['batting_hand'] ?? null,
                'bowling_style' => $data['bowling_style'] ?? null
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Failed to update player: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete player
     * 
     * Deletes player and all related records in the correct order:
     * 1. Delete events (depends on player_appearances)
     * 2. Delete stats_cache (depends on player_appearances and players)
     * 3. Delete player_edits (depends on player_appearances)
     * 4. Delete player_appearances (depends on players)
     * 5. Delete potm_decisions (depends on players)
     * 6. Delete pots_aggregate (depends on players)
     * 7. Finally, delete the player itself
     * 
     * @param int $playerId Player ID
     * @return bool Success
     * @throws PDOException If deletion fails
     */
    public function delete($playerId) {
        try {
            $this->getDb()->beginTransaction();
            
            // 1. Delete events that reference player_appearances for this player
            $sql = "DELETE e FROM events e
                    INNER JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
                    WHERE pa.player_id = :player_id";
            $this->executeQuery($sql, ['player_id' => $playerId]);
            
            // 2. Delete stats_cache (depends on player_appearances and players)
            $sql = "DELETE FROM stats_cache WHERE player_id = :player_id";
            $this->executeQuery($sql, ['player_id' => $playerId]);
            
            // 3. Delete player_edits that reference player_appearances for this player
            $sql = "DELETE pe FROM player_edits pe
                    INNER JOIN player_appearances pa ON pe.appearance_id = pa.appearance_id
                    WHERE pa.player_id = :player_id";
            $this->executeQuery($sql, ['player_id' => $playerId]);
            
            // 4. Delete player_appearances (depends on players)
            $sql = "DELETE FROM player_appearances WHERE player_id = :player_id";
            $this->executeQuery($sql, ['player_id' => $playerId]);
            
            // 5. Delete potm_decisions (depends on players via computed_player_id and final_player_id)
            $sql = "DELETE FROM potm_decisions 
                    WHERE computed_player_id = :player_id OR final_player_id = :player_id2";
            $this->executeQuery($sql, ['player_id' => $playerId, 'player_id2' => $playerId]);
            
            // 6. Delete pots_aggregate (depends on players)
            $sql = "DELETE FROM pots_aggregate WHERE player_id = :player_id";
            $this->executeQuery($sql, ['player_id' => $playerId]);
            
            // 7. Finally, delete the player itself
            $sql = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :player_id";
            $this->executeQuery($sql, ['player_id' => $playerId]);
            
            $this->getDb()->commit();
            return true;
        } catch (PDOException $e) {
            $this->getDb()->rollBack();
            $errorInfo = $e->errorInfo ?? [];
            error_log('Error deleting player (PDO): ' . $e->getMessage() . ' | SQL State: ' . ($errorInfo[0] ?? 'N/A') . ' | Error Code: ' . ($errorInfo[1] ?? 'N/A') . ' | Error Message: ' . ($errorInfo[2] ?? 'N/A'));
            throw $e; // Re-throw to provide more context to caller
        } catch (Exception $e) {
            $this->getDb()->rollBack();
            error_log('Error deleting player: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            throw $e; // Re-throw to provide more context to caller
        }
    }
    
    /**
     * Get players by team for a match
     * 
     * @param int $matchId Match ID
     * @param int $teamId Team ID
     * @return array List of player appearances
     */
    public function getByTeamForMatch($matchId, $teamId) {
        $sql = "SELECT pa.*, p.name as player_name, p.batting_hand, p.bowling_style 
                FROM player_appearances pa
                INNER JOIN {$this->tableName} p ON pa.player_id = p.player_id
                WHERE pa.match_id = :match_id AND pa.team_id = :team_id
                ORDER BY p.name ASC";
        
        $stmt = $this->executeQuery($sql, [
            'match_id' => $matchId,
            'team_id' => $teamId
        ]);
        return $stmt->fetchAll();
    }
}
