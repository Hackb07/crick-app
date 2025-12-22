<?php
/**
 * Match Model
 * 
 * Manages match data and operations.
 * Extends BaseModel to eliminate code duplication.
 */

class MatchModel extends BaseModel {
    /** @var string Table name */
    protected $tableName = 'matches';
    
    /** @var string Primary key column */
    protected $primaryKey = 'match_id';
    
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
     * Get all matches with filters
     * 
     * @param array $filters Filter options
     * @return array List of matches
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT m.*, 
                t1.name as team1_name, t1.short_name as team1_short_name,
                t2.name as team2_name, t2.short_name as team2_short_name,
                s.name as series_name
                FROM matches m
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                LEFT JOIN series s ON m.series_id = s.series_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['state'])) {
            $sql .= " AND m.state = :state";
            $params['state'] = $filters['state'];
        }
        
        if (!empty($filters['series_id'])) {
            $sql .= " AND m.series_id = :series_id";
            $params['series_id'] = $filters['series_id'];
        }
        
        if (!empty($filters['team_id'])) {
            $sql .= " AND (m.team1_id = :team_id OR m.team2_id = :team_id)";
            $params['team_id'] = $filters['team_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND m.match_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND m.match_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY m.match_date DESC, m.created_at DESC";
        
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get match by ID
     * 
     * Overrides base method to include related data (teams, series, creator).
     * 
     * @param int $matchId Match ID
     * @return array|null Match data or null
     */
    public function getById(int $matchId): ?array {
        $sql = "SELECT m.*, 
                t1.name as team1_name, t1.short_name as team1_short_name, t1.logo as team1_logo,
                t2.name as team2_name, t2.short_name as team2_short_name, t2.logo as team2_logo,
                s.name as series_name,
                u.username as created_by_username
                FROM {$this->tableName} m
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                LEFT JOIN series s ON m.series_id = s.series_id
                LEFT JOIN users u ON m.created_by = u.user_id
                WHERE m.{$this->primaryKey} = :match_id
                LIMIT 1";
        
        $stmt = $this->executeQuery($sql, ['match_id' => $matchId]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Create match
     * 
     * Creates a new match with state 'draft'.
     * Validates that team1_id and team2_id are different.
     * Validates overs_per_innings is between 1 and 50.
     * Sets default overs_per_innings to 20.0 if not specified.
     * 
     * @param array $data Match data (team1_id, team2_id, overs_per_innings, venue, match_date, series_id, created_by)
     * @return int|false Match ID on success, false on failure
     */
    public function create($data) {
        // Validate teams are different
        if (isset($data['team1_id']) && isset($data['team2_id']) && $data['team1_id'] === $data['team2_id']) {
            return false;
        }
        
        // Validate overs_per_innings range (1-50)
        $overs = $data['overs_per_innings'] ?? 20.0;
        if ($overs < 1 || $overs > 50) {
            return false;
        }
        
        $sql = "INSERT INTO {$this->tableName} (series_id, team1_id, team2_id, match_date, venue, overs_per_innings, 
                ball_type, pitch_type, match_type, umpire1_name, umpire2_name, scorer_name,
                state, created_by, created_at, updated_at) 
                VALUES (:series_id, :team1_id, :team2_id, :match_date, :venue, :overs_per_innings, 
                :ball_type, :pitch_type, :match_type, :umpire1_name, :umpire2_name, :scorer_name,
                'draft', :created_by, NOW(), NOW())";
        
        try {
            $stmt = $this->executeQuery($sql, [
                'series_id' => $data['series_id'] ?? null,
                'team1_id' => $data['team1_id'],
                'team2_id' => $data['team2_id'],
                'match_date' => $data['match_date'] ?? null,
                'venue' => $data['venue'] ?? '',
                'overs_per_innings' => $overs,
                'ball_type' => $data['ball_type'] ?? 'leather',
                'pitch_type' => $data['pitch_type'] ?? 'turf',
                'match_type' => $data['match_type'] ?? 'limited_overs',
                'umpire1_name' => $data['umpire1_name'] ?? null,
                'umpire2_name' => $data['umpire2_name'] ?? null,
                'scorer_name' => $data['scorer_name'] ?? null,
                'created_by' => $data['created_by'] ?? null
            ]);
            
            return (int)$this->getDb()->lastInsertId();
        } catch (PDOException $e) {
            error_log('Failed to create match: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update match
     * 
     * @param int $matchId Match ID
     * @param array $data Match data
     * @return bool Success
     * @throws Exception on failure with detailed message
     */
    public function update($matchId, $data) {
        $allowedFields = ['series_id', 'team1_id', 'team2_id', 'match_date', 'venue', 
                         'overs_per_innings', 'state', 'toss_winner_id', 'toss_decision',
                         'current_innings', 'last_seq', 'auto_start_innings2', 'winner_id',
                         'ball_type', 'pitch_type', 'umpire1_name', 'umpire2_name', 'scorer_name', 'match_type'];
        
        $updates = [];
        $params = ['match_id' => $matchId];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            $errorMsg = 'No valid fields to update. Provided fields: ' . implode(', ', array_keys($data));
            error_log('MatchModel::update: ' . $errorMsg . ' for match_id=' . $matchId);
            throw new Exception($errorMsg);
        }
        
        $sql = "UPDATE {$this->tableName} SET " . implode(', ', $updates) . ", updated_at = NOW() 
                WHERE {$this->primaryKey} = :match_id";
        
        try {
            $stmt = $this->executeQuery($sql, $params);
            
            // Check if any rows were affected
            if ($stmt->rowCount() === 0) {
                error_log('MatchModel::update: No rows updated for match_id=' . $matchId);
                // Don't throw - this could be because no changes were made
                return true;
            }
            
            return true;
        } catch (PDOException $e) {
            $errorMsg = 'Database error: ' . $e->getMessage();
            if ($e->errorInfo && isset($e->errorInfo[2])) {
                $errorMsg = $e->errorInfo[2];
            }
            error_log('MatchModel::update: ' . $errorMsg . ' for match_id=' . $matchId);
            throw new Exception($errorMsg);
        }
    }
    
    /**
     * Delete match
     * 
     * Deletes all related records in the correct order to respect foreign key constraints:
     * 1. events_suspense (depends on events and matches)
     * 2. events (depends on matches and player_appearances)
     * 3. stats_cache (depends on matches and player_appearances)
     * 4. player_edits (depends on matches and player_appearances)
     * 5. player_appearances (depends on matches)
     * 6. potm_decisions (depends on matches)
     * 7. clone_links (depends on matches)
     * 8. match_locks (depends on matches)
     * 9. matches (finally)
     * 
     * @param int $matchId Match ID
     * @return bool Success
     */
    public function delete($matchId) {
        try {
            $this->getDb()->beginTransaction();
            
            // 1. Delete events_suspense (depends on events, which depends on matches)
            $sql = "DELETE FROM events_suspense WHERE match_id = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            // 2. Delete events (depends on matches and player_appearances)
            $sql = "DELETE FROM events WHERE match_id = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            // 3. Delete stats_cache (depends on matches and player_appearances)
            $sql = "DELETE FROM stats_cache WHERE match_id = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            // 4. Delete player_edits (depends on matches and player_appearances)
            $sql = "DELETE FROM player_edits WHERE match_id = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            // 5. Delete player_appearances (depends on matches)
            $sql = "DELETE FROM player_appearances WHERE match_id = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            // 6. Delete potm_decisions (depends on matches)
            $sql = "DELETE FROM potm_decisions WHERE match_id = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            // 7. Delete clone_links (depends on matches - both source and target)
            $sql = "DELETE FROM clone_links WHERE source_match_id = :match_id OR target_match_id = :match_id2";
            $this->executeQuery($sql, ['match_id' => $matchId, 'match_id2' => $matchId]);
            
            // 8. Delete match_locks (depends on matches)
            $sql = "DELETE FROM match_locks WHERE match_id = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            // 9. Finally, delete the match itself
            $sql = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :match_id";
            $this->executeQuery($sql, ['match_id' => $matchId]);
            
            $this->getDb()->commit();
            return true;
        } catch (PDOException $e) {
            $this->getDb()->rollBack();
            $errorInfo = $e->errorInfo ?? [];
            error_log('Error deleting match (PDO): ' . $e->getMessage() . ' | SQL State: ' . ($errorInfo[0] ?? 'N/A') . ' | Error Code: ' . ($errorInfo[1] ?? 'N/A') . ' | Error Message: ' . ($errorInfo[2] ?? 'N/A'));
            throw $e; // Re-throw to provide more context to caller
        } catch (Exception $e) {
            $this->getDb()->rollBack();
            error_log('Error deleting match: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            throw $e; // Re-throw to provide more context to caller
        }
    }
    
    /**
     * Get live matches
     * 
     * @return array List of live matches
     */
    public function getLive() {
        $sql = "SELECT m.*, 
                t1.name as team1_name, t1.short_name as team1_short_name,
                t2.name as team2_name, t2.short_name as team2_short_name
                FROM {$this->tableName} m
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                WHERE m.state = 'live'
                ORDER BY m.match_date DESC";
        
        $stmt = $this->executeQuery($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Update match last sequence
     * 
     * @param int $matchId Match ID
     * @param int $seq Sequence number
     * @return bool Success
     */
    public function updateLastSeq($matchId, $seq) {
        $sql = "UPDATE {$this->tableName} SET last_seq = :seq, updated_at = NOW() WHERE {$this->primaryKey} = :match_id";
        try {
            $this->executeQuery($sql, ['match_id' => $matchId, 'seq' => $seq]);
            return true;
        } catch (PDOException $e) {
            error_log('Failed to update last_seq: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Update squad for a team in a match
     * 
     * @param int $matchId Match ID
     * @param int $teamId Team ID
     * @param array $playerIds List of player IDs
     * @param array $meta Metadata (guests, captains)
     * @return bool Success
     * @throws Exception on failure
     */
    public function updateSquad($matchId, $teamId, $playerIds, $meta = []) {
        try {
            $this->getDb()->beginTransaction();

            // 1. Clear existing for this team
            $sql = "DELETE FROM player_appearances WHERE match_id = :match_id AND team_id = :team_id";
            $this->executeQuery($sql, ['match_id' => $matchId, 'team_id' => $teamId]);

            // 2. Insert new
            if (!empty($playerIds)) {
                $insertSql = "INSERT INTO player_appearances (player_id, match_id, team_id, is_guest, is_captain, role_tags, created_at, updated_at) 
                              VALUES (:pid, :mid, :tid, :guest, :capt, :roles, NOW(), NOW())";
                
                // Prepare statement once for efficiency
                $stmt = $this->getDb()->prepare($insertSql);

                foreach ($playerIds as $pid) {
                    $isGuest = isset($meta['guests'][$pid]) ? 1 : 0;
                    $isCaptain = isset($meta['captains'][$pid]) ? 1 : 0;
                    $isWk = isset($meta['wks'][$pid]) ? 1 : 0;
                    
                    $roles = [];
                    if ($isWk) $roles[] = 'WK';
                    
                    $stmt->execute([
                        'pid' => $pid,
                        'mid' => $matchId,
                        'tid' => $teamId,
                        'guest' => $isGuest,
                        'capt' => $isCaptain,
                        'roles' => json_encode($roles)
                    ]);
                }
            }

            $this->getDb()->commit();
            return true;

        } catch (Exception $e) {
            $this->getDb()->rollBack();
            error_log('Failed to update squad: ' . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Get unique venues from all matches
     * 
     * @return array List of unique venue names
     */
    public function getUniqueVenues(): array {
        $sql = "SELECT DISTINCT venue FROM {$this->tableName} WHERE venue IS NOT NULL AND venue != '' ORDER BY venue ASC";
        $stmt = $this->executeQuery($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
