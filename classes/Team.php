<?php
/**
 * Team Model
 * 
 * Manages team data and operations.
 * Extends BaseModel to eliminate code duplication.
 */

class Team extends BaseModel {
    /** 
     * @var string Table name
     * 
     * Security Note: This is a class constant set at definition time,
     * not derived from user input, so it's safe from SQL injection.
     * The BaseModel constructor validates it's not empty.
     */
    protected $tableName = 'teams';
    
    /** 
     * @var string Primary key column
     * 
     * Security Note: This is a class constant set at definition time,
     * not derived from user input, so it's safe from SQL injection.
     */
    protected $primaryKey = 'team_id';
    
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
     * Get all teams
     * 
     * Overrides base getAll() to provide custom ordering.
     * 
     * @param array $filters Optional filters (not used, but required for compatibility)
     * @return array List of teams
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT * FROM {$this->tableName} ORDER BY name ASC";
        $stmt = $this->executeQuery($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get team by ID
     * 
     * Uses base class implementation with custom primary key.
     * 
     * @param int $teamId Team ID
     * @return array|null Team data or null
     */
    public function getById(int $teamId): ?array {
        return parent::getById($teamId);
    }
    
    /**
     * Create new team
     * 
     * Validates input data before creating the team.
     * 
     * @param array $data Team data (name, short_name, logo)
     * @return int|false Team ID or false on failure
     * @throws InvalidArgumentException If validation fails
     */
    public function create(array $data) {
        // Validate required fields
        if (empty($data['name']) || !is_string($data['name'])) {
            throw new InvalidArgumentException('Team name is required and must be a string');
        }
        
        // Validate and sanitize name
        $name = trim($data['name']);
        if (empty($name) || mb_strlen($name) > 255) {
            throw new InvalidArgumentException('Team name must be between 1 and 255 characters');
        }
        
        // Validate short_name if provided
        $shortName = '';
        if (isset($data['short_name'])) {
            if (!is_string($data['short_name'])) {
                throw new InvalidArgumentException('Short name must be a string');
            }
            $shortName = trim($data['short_name']);
            if (mb_strlen($shortName) > 50) {
                throw new InvalidArgumentException('Short name must be at most 50 characters');
            }
        }
        
        // Validate logo_url if provided (should be a URL or path)
        $logoUrl = null;
        if (isset($data['logo_url']) && $data['logo_url'] !== '') {
            if (!is_string($data['logo_url'])) {
                throw new InvalidArgumentException('Logo URL must be a string');
            }
            $logoUrl = trim($data['logo_url']);
            // Basic URL/path validation
            if (mb_strlen($logoUrl) > 500) {
                throw new InvalidArgumentException('Logo URL path must be at most 500 characters');
            }
        }
        
        $sql = "INSERT INTO {$this->tableName} (name, short_name, logo_url, created_at) 
                VALUES (:name, :short_name, :logo_url, NOW())";
        
        try {
            $stmt = $this->executeQuery($sql, [
                'name' => $name,
                'short_name' => $shortName,
                'logo_url' => $logoUrl
            ]);
            
            $teamId = (int)$this->getDb()->lastInsertId();
            error_log(sprintf('Team created successfully: ID=%d, Name=%s', $teamId, $name));
            return $teamId;
        } catch (PDOException $e) {
            error_log(sprintf(
                'Failed to create team: %s | Data: %s | Error: %s',
                $e->getMessage(),
                json_encode(['name' => $name, 'short_name' => $shortName]),
                $e->getTraceAsString()
            ));
            return false;
        }
    }
    
    /**
     * Update team
     */
    public function update(int $teamId, array $data): bool {
        // Validate team ID
        if ($teamId <= 0) {
            throw new InvalidArgumentException('Team ID must be a positive integer');
        }
        
        // Check if team exists
        if (!$this->exists($teamId)) {
            return false;
        }
        
        // Validate and sanitize name if provided
        $name = null;
        if (isset($data['name'])) {
            $name = trim($data['name']);
        }
        
        // Validate short_name if provided
        $shortName = null;
        if (isset($data['short_name'])) {
            $shortName = trim($data['short_name']);
        }
        
        // Validate logo_url if provided
        $logoUrl = null;
        if (isset($data['logo_url'])) {
            $logoUrl = trim($data['logo_url']);
        }
        
        // Build update query dynamically
        $updateFields = [];
        $params = ['team_id' => $teamId];
        
        if ($name !== null) {
            $updateFields[] = "name = :name";
            $params['name'] = $name;
        }
        
        if ($shortName !== null) {
            $updateFields[] = "short_name = :short_name";
            $params['short_name'] = $shortName;
        }
        
        if ($logoUrl !== null) {
            $updateFields[] = "logo_url = :logo_url";
            $params['logo_url'] = $logoUrl;
        }
        
        if (empty($updateFields)) return false;
        
        $sql = "UPDATE {$this->tableName} 
                SET " . implode(', ', $updateFields) . " 
                WHERE {$this->primaryKey} = :team_id";
        
        try {
            $this->executeQuery($sql, $params);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Check dependency count for a specific field
     * 
     * Helper method to reduce code duplication in checkDependencies.
     * Uses COUNT(*) for better performance instead of fetching all rows.
     * 
     * @param string $table Table name (must be alphanumeric/underscore only)
     * @param string $field Field name to check (must be alphanumeric/underscore only)
     * @param int $teamId Team ID
     * @return int Count of dependencies
     * @throws InvalidArgumentException If table or field name is invalid
     */
    private function getDependencyCount(string $table, string $field, int $teamId): int {
        // Sanitize table and field names to prevent SQL injection
        // Only allow alphanumeric, underscore, and specific safe characters
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            throw new InvalidArgumentException('Invalid table or field name');
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = :team_id";
        $stmt = $this->executeQuery($sql, ['team_id' => $teamId]);
        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Get dependency details for matches
     * 
     * Returns match details if count is small, otherwise just count.
     * This optimizes performance by only fetching details when needed.
     * 
     * @param string $field Field name to check (team1_id, team2_id, toss_winner_id)
     * @param int $teamId Team ID
     * @param int $maxDetails Maximum number of details to return (default: 10)
     * @return array Array with 'count' and optionally 'details'
     * @throws InvalidArgumentException If field name is invalid
     */
    private function getMatchDependencies(string $field, int $teamId, int $maxDetails = 10): array {
        // Sanitize field name - only allow specific valid fields
        $allowedFields = ['team1_id', 'team2_id', 'toss_winner_id'];
        if (!in_array($field, $allowedFields, true)) {
            throw new InvalidArgumentException('Invalid field name for match dependencies');
        }
        
        // First get count using optimized COUNT query
        $count = $this->getDependencyCount('matches', $field, $teamId);
        
        $result = ['count' => $count];
        
        // Only fetch details if count is small (for performance)
        // This prevents loading thousands of match records when we only need the count
        if ($count > 0 && $count <= $maxDetails) {
            $sql = "SELECT match_id, match_date, venue, state 
                    FROM matches 
                    WHERE {$field} = :team_id 
                    ORDER BY match_date DESC 
                    LIMIT :limit";
            $stmt = $this->executeQuery($sql, ['team_id' => $teamId, 'limit' => $maxDetails]);
            $result['details'] = $stmt->fetchAll();
        }
        
        return $result;
    }
    
    /**
     * Check if team has dependencies that prevent deletion
     * 
     * Uses optimized COUNT queries for better performance.
     * Only fetches match details when count is small (<= 10).
     * 
     * @param int $teamId Team ID
     * @return array Array with 'can_delete' (bool) and 'dependencies' (array) with details
     * @throws InvalidArgumentException If team ID is invalid
     */
    public function checkDependencies(int $teamId): array {
        // Validate team ID
        if ($teamId <= 0) {
            throw new InvalidArgumentException('Team ID must be a positive integer');
        }
        
        $dependencies = [
            'matches_as_team1' => $this->getMatchDependencies('team1_id', $teamId),
            'matches_as_team2' => $this->getMatchDependencies('team2_id', $teamId),
            'matches_as_toss_winner' => $this->getMatchDependencies('toss_winner_id', $teamId),
            'player_appearances' => ['count' => $this->getDependencyCount('player_appearances', 'team_id', $teamId)]
        ];
        
        // Check if team can be deleted
        $totalMatches = $dependencies['matches_as_team1']['count'] 
                      + $dependencies['matches_as_team2']['count']
                      + $dependencies['matches_as_toss_winner']['count'];
        $appearanceCount = $dependencies['player_appearances']['count'];
        
        $canDelete = $totalMatches == 0 && $appearanceCount == 0;
        
        return [
            'can_delete' => $canDelete,
            'dependencies' => $dependencies
        ];
    }
    
    /**
     * Delete team
     * 
     * Checks for dependencies before deletion. Throws exception if team cannot be deleted.
     * 
     * @param int $teamId Team ID
     * @return bool Success
     * @throws InvalidArgumentException If team ID is invalid
     * @throws Exception If team has dependencies that prevent deletion
     */
    public function delete(int $teamId): bool {
        // Validate team ID
        if ($teamId <= 0) {
            throw new InvalidArgumentException('Team ID must be a positive integer');
        }
        
        // Check if team exists
        if (!$this->exists($teamId)) {
            error_log(sprintf('Delete failed: Team ID %d does not exist', $teamId));
            return false;
        }
        
        // Check dependencies first
        $check = $this->checkDependencies($teamId);
        
        if (!$check['can_delete']) {
            $messages = [];
            
            $totalMatches = $check['dependencies']['matches_as_team1']['count'] 
                          + $check['dependencies']['matches_as_team2']['count']
                          + $check['dependencies']['matches_as_toss_winner']['count'];
            
            if ($totalMatches > 0) {
                $messages[] = "This team is associated with {$totalMatches} match(es)";
            }
            
            if ($check['dependencies']['player_appearances']['count'] > 0) {
                $messages[] = "This team has {$check['dependencies']['player_appearances']['count']} player appearance(s)";
            }
            
            $errorMessage = 'Cannot delete team: ' . implode(' and ', $messages) . '. Please delete or reassign related matches first.';
            error_log(sprintf('Delete blocked for team ID %d: %s', $teamId, $errorMessage));
            throw new Exception($errorMessage);
        }
        
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = :team_id";
        try {
            $this->executeQuery($sql, ['team_id' => $teamId]);
            error_log(sprintf('Team deleted successfully: ID=%d', $teamId));
            return true;
        } catch (PDOException $e) {
            error_log(sprintf(
                'Failed to delete team: ID=%d | Error: %s | SQL: %s | Trace: %s',
                $teamId,
                $e->getMessage(),
                $sql,
                $e->getTraceAsString()
            ));
            return false;
        }
    }
}
