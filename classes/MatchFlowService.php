<?php
/**
 * Match Flow Service
 * 
 * Business logic layer for match flow management.
 * Handles player assignment, toss recording, and flow state determination.
 * 
 * Responsibilities:
 * - Validate match flow operations
 * - Determine current flow step
 * - Filter and categorize players for assignment
 * - Handle player assignment operations
 */

class MatchFlowService
{
    /** @var PDO Database connection */
    private $db;

    /** @var MatchStateMachine State machine for match state transitions */
    private $stateMachine;

    /** @var Team Team model */
    private $teamModel;

    /** @var Player Player model */
    private $playerModel;

    /** @var ActionLogger Action logger */
    private $actionLogger;

    // Flow step constants
    const FLOW_STEP_CREATE = 1;
    const FLOW_STEP_ASSIGN_PLAYERS = 2;
    const FLOW_STEP_RECORD_TOSS = 3;
    const FLOW_STEP_START_MATCH = 4;
    const FLOW_STEP_SCORE_MATCH = 5;
    const FLOW_STEP_FINALIZE = 6;

    /**
     * Constructor
     * 
     * @param PDO|null $db Database connection
     * @param MatchStateMachine|null $stateMachine State machine instance
     * @param Team|null $teamModel Team model instance
     * @param Player|null $playerModel Player model instance
     * @param ActionLogger|null $actionLogger Action logger instance
     */
    public function __construct(
        ?PDO $db = null,
        ?MatchStateMachine $stateMachine = null,
        ?Team $teamModel = null,
        ?Player $playerModel = null,
        ?ActionLogger $actionLogger = null
    ) {
        $this->db = $db ?? Database::getInstance()->getConnection();
        $this->stateMachine = $stateMachine ?? new MatchStateMachine();
        $this->teamModel = $teamModel ?? new Team();
        $this->playerModel = $playerModel ?? new Player();
        $this->actionLogger = $actionLogger ?? new ActionLogger();
    }

    /**
     * Validate if team ID belongs to the match
     * 
     * @param int $teamId Team ID to validate
     * @param array $match Match data
     * @return bool True if valid team for match
     */
    public function isValidTeamForMatch(int $teamId, array $match): bool
    {
        return $teamId == $match['team1_id'] || $teamId == $match['team2_id'];
    }

    /**
     * Validate match flow form input with comprehensive checks
     * 
     * Centralizes validation logic used across multiple form handlers to reduce duplication.
     * Validates required fields, data types, and business rules (e.g., team belongs to match).
     * 
     * @param array $data Form data to validate (e.g., ['team_id' => 1, 'player_ids' => [1,2]])
     * @param array $requiredFields List of required field names
     * @param array $match Match data for business rule validation
     * @param array $rules Optional additional validation rules
     * @return array ['valid' => bool, 'error' => string|null] Validation result
     */
    public function validateMatchFlowInput(array $data, array $requiredFields, array $match, array $rules = []): array
    {
        // Validate required fields
        $validationErrors = validateRequired($data, $requiredFields);
        
        if (!empty($validationErrors)) {
            return ['valid' => false, 'error' => 'Invalid input: ' . implode(', ', $validationErrors)];
        }
        
        // Validate team_id if present
        if (isset($data['team_id'])) {
            $teamId = (int)$data['team_id'];
            $intErrors = validateInt($teamId, 'Team ID', 1);
            if (!empty($intErrors)) {
                return ['valid' => false, 'error' => 'Invalid team ID'];
            }
            if (!$this->isValidTeamForMatch($teamId, $match)) {
                return ['valid' => false, 'error' => 'Invalid team for this match'];
            }
        }
        
        // Validate toss_winner_id if present
        if (isset($data['toss_winner_id'])) {
            $tossWinnerId = (int)$data['toss_winner_id'];
            $intErrors = validateInt($tossWinnerId, 'Toss Winner ID', 1);
            if (!empty($intErrors)) {
                return ['valid' => false, 'error' => 'Invalid toss winner ID'];
            }
            if (!$this->isValidTeamForMatch($tossWinnerId, $match)) {
                return ['valid' => false, 'error' => 'Invalid team for this match'];
            }
        }
        
        // Validate toss_decision if present
        if (isset($data['toss_decision'])) {
            $enumErrors = validateEnum($data['toss_decision'], ['bat', 'bowl'], 'Toss Decision');
            if (!empty($enumErrors)) {
                return ['valid' => false, 'error' => 'Invalid toss decision. Must be "bat" or "bowl".'];
            }
        }
        
        // Validate player_ids if present
        if (isset($data['player_ids'])) {
            $playerIds = $data['player_ids'];
            if (!is_array($playerIds) || empty($playerIds)) {
                return ['valid' => false, 'error' => 'Please select at least one player'];
            }
            if (count($playerIds) > 50) {
                return ['valid' => false, 'error' => 'Too many players selected (maximum 50)'];
            }
        }
        
        // Apply custom rules if provided
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $result = call_user_func($rule, $data, $match);
                if (!$result['valid']) {
                    return $result;
                }
            }
        }
        
        return ['valid' => true, 'error' => null];
    }

    /**
     * Get toss winner team name efficiently
     * Only loads teams data when toss winner exists
     * 
     * @param array $match Match data
     * @return string|null Team name or null
     */
    public function getTossWinnerName(array $match): ?string
    {
        if (empty($match['toss_winner_id'])) {
            return null;
        }
        
        // Optimize: Get team directly by ID instead of loading all teams
        $tossWinnerTeam = $this->teamModel->getById($match['toss_winner_id']);
        return $tossWinnerTeam ? $tossWinnerTeam['name'] : null;
    }

    /**
     * Check if match is ready to start
     * 
     * Match can start when toss is recorded and players are assigned to both teams
     * 
     * @param array $match Match data
     * @param array $playerCheck Player assignment check result
     * @return bool True if ready to start
     */
    public function isMatchReadyToStart(array $match, array $playerCheck): bool
    {
        return $match['state'] === 'scheduled' 
            && !empty($match['toss_winner_id']) 
            && $playerCheck['valid'];
    }

    /**
     * Determine current step based on match state
     * 
     * Uses state machine logic to determine which step in the flow should be active.
     * Steps progress: Create -> Assign Players -> Record Toss -> Start -> Score -> Finalize
     * 
     * @param array $match Match data containing state and toss information
     * @param array $playerCheck Player assignment validation result ['valid' => bool, ...]
     * @return int Current step number (FLOW_STEP_* constant)
     */
    public function determineCurrentStep(array $match, array $playerCheck): int
    {
        $state = $match['state'];
        
        // Completed matches always show finalize step
        if ($state === 'completed') {
            return self::FLOW_STEP_FINALIZE;
        }
        
        // Live matches show scoring step
        if ($state === 'live') {
            return self::FLOW_STEP_SCORE_MATCH;
        }
        
        // Scheduled matches: check prerequisites
        if ($state === 'scheduled') {
            // Ready to start: toss recorded and players assigned
            if ($this->isMatchReadyToStart($match, $playerCheck)) {
                return self::FLOW_STEP_START_MATCH;
            }
            
            // Toss recorded but players not assigned: go back to assign players
            if (!empty($match['toss_winner_id']) && !$playerCheck['valid']) {
                return self::FLOW_STEP_ASSIGN_PLAYERS;
            }
        }
        
        // Draft or scheduled: check if players are assigned
        if ($state === 'scheduled' || $state === 'draft') {
            if ($playerCheck['valid']) {
                return self::FLOW_STEP_RECORD_TOSS; // Players assigned, need to record toss
            } else {
                return self::FLOW_STEP_ASSIGN_PLAYERS; // Need to assign players
            }
        }
        
        return self::FLOW_STEP_CREATE;
    }

    /**
     * Check if player belongs to team based on appearances
     * 
     * @param int $playerId Player ID to check
     * @param array $teamAppearances Array of player appearances for a team
     * @return bool True if player is in team
     */
    public function isPlayerInTeam(int $playerId, array $teamAppearances): bool
    {
        return isset($teamAppearances[$playerId]);
    }

    /**
     * Categorize player into team lists or common players list
     * 
     * Determines which players should appear in each team's selection list.
     * Players assigned to both teams are categorized as "common" for warning display.
     * 
     * @param array $player Player data
     * @param array $team1PlayerIds Array of player IDs in team 1
     * @param array $team2PlayerIds Array of player IDs in team 2
     * @param array $commonPlayerIds Array of player IDs in both teams
     * @return array ['for_team1' => bool, 'for_team2' => bool, 'is_common' => bool]
     */
    public function categorizePlayer(array $player, array $team1PlayerIds, array $team2PlayerIds, array $commonPlayerIds): array
    {
        $playerId = $player['player_id'];
        $inTeam1 = in_array($playerId, $team1PlayerIds);
        $inTeam2 = in_array($playerId, $team2PlayerIds);
        $isCommon = in_array($playerId, $commonPlayerIds);
        
        return [
            'for_team1' => $isCommon || !$inTeam2 || $inTeam1,
            'for_team2' => $isCommon || !$inTeam1 || $inTeam2,
            'is_common' => $isCommon
        ];
    }

    /**
     * Filter players for team assignment
     * 
     * Separates players into team-specific lists and identifies common players.
     * Common players (assigned to both teams) are flagged for warning display.
     * 
     * @param array $allPlayers All available players
     * @param array $team1Appearances Existing appearances for team 1 [player_id => appearance_data]
     * @param array $team2Appearances Existing appearances for team 2 [player_id => appearance_data]
     * @param array $commonPlayerIds Array of player IDs assigned to both teams
     * @return array ['team1' => array, 'team2' => array, 'common' => array]
     */
    public function filterPlayersForTeams(array $allPlayers, array $team1Appearances, array $team2Appearances, array $commonPlayerIds): array
    {
        $team1PlayerIds = array_keys($team1Appearances);
        $team2PlayerIds = array_keys($team2Appearances);
        
        $team1Filtered = [];
        $team2Filtered = [];
        $commonList = [];
        
        foreach ($allPlayers as $player) {
            $category = $this->categorizePlayer($player, $team1PlayerIds, $team2PlayerIds, $commonPlayerIds);
            
            if ($category['is_common']) {
                $commonList[] = $player;
            } else {
                if ($category['for_team1']) {
                    $team1Filtered[] = $player;
                }
                if ($category['for_team2']) {
                    $team2Filtered[] = $player;
                }
            }
        }
        
        return [
            'team1' => $team1Filtered,
            'team2' => $team2Filtered,
            'common' => $commonList
        ];
    }

    /**
     * Load player data for match assignment
     * 
     * Optimized to only load players and appearances needed for this specific match.
     * Uses prepared statements for SQL injection prevention.
     * 
     * @param int $matchId Match ID to load appearances for
     * @return array ['allPlayers' => array, 'existingAppearances' => array]
     * @throws Exception If database query fails
     */
    public function loadPlayerDataForMatch(int $matchId): array
    {
        // Load all players (needed for selection dropdowns)
        $allPlayers = $this->playerModel->getAll();
        
        // Load existing appearances for this match only (optimized query)
        $appearancesSql = "SELECT pa.*, p.name as player_name 
                           FROM player_appearances pa
                           INNER JOIN players p ON pa.player_id = p.player_id
                           WHERE pa.match_id = :match_id";
        $appearancesStmt = $this->db->prepare($appearancesSql);
        $appearancesStmt->execute(['match_id' => $matchId]);
        $existingAppearances = $appearancesStmt->fetchAll();
        
        return [
            'allPlayers' => $allPlayers,
            'existingAppearances' => $existingAppearances
        ];
    }

    /**
     * Assign players to a team for a match
     * 
     * @param int $matchId Match ID
     * @param int $teamId Team ID
     * @param array $playerIds Array of player IDs
     * @param int $userId User ID performing the action
     * @return array ['success' => bool, 'error' => string|null, 'player_count' => int]
     */
    public function assignPlayersToTeam(int $matchId, int $teamId, array $playerIds, int $userId): array
    {
        try {
            $this->db->beginTransaction();
            
            // Delete existing appearances for this team
            $deleteSql = "DELETE FROM player_appearances WHERE match_id = :match_id AND team_id = :team_id";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute(['match_id' => $matchId, 'team_id' => $teamId]);
            
            // Validate and prepare player IDs
            $validPlayerIds = [];
            foreach ($playerIds as $playerId) {
                $playerId = (int)$playerId;
                $intErrors = validateInt($playerId, 'Player ID', 1);
                if (!empty($intErrors)) {
                    continue;
                }
                $player = $this->playerModel->getById($playerId);
                if ($player) {
                    $validPlayerIds[] = $playerId;
                }
            }
            
            if (empty($validPlayerIds)) {
                throw new Exception('No valid players selected');
            }
            
            // Batch insert player appearances
            $insertSql = "INSERT INTO player_appearances (player_id, match_id, team_id, role_tags, created_at, updated_at) 
                          VALUES (:player_id, :match_id, :team_id, :role_tags, NOW(), NOW())";
            $insertStmt = $this->db->prepare($insertSql);
            
            foreach ($validPlayerIds as $playerId) {
                $insertStmt->execute([
                    'player_id' => $playerId,
                    'match_id' => $matchId,
                    'team_id' => $teamId,
                    'role_tags' => json_encode([])
                ]);
            }
            
            $this->db->commit();
            
            // Log action
            $this->actionLogger->log($userId, 'update', 'match_player_assignments', $matchId, [
                'team_id' => $teamId,
                'player_count' => count($validPlayerIds)
            ]);
            
            return [
                'success' => true,
                'error' => null,
                'player_count' => count($validPlayerIds)
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            $errorDetails = sprintf(
                "Error assigning players for match %d: %s | File: %s:%d | Trace: %s",
                $matchId,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
            error_log($errorDetails);
            
            return [
                'success' => false,
                'error' => 'Failed to assign players. Please try again.',
                'player_count' => 0
            ];
        }
    }

    /**
     * Record toss for a match
     * 
     * @param int $matchId Match ID
     * @param int $tossWinnerId Toss winner team ID
     * @param string $tossDecision Toss decision ('bat' or 'bowl')
     * @param int $userId User ID performing the action
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function recordToss(int $matchId, int $tossWinnerId, string $tossDecision, int $userId): array
    {
        $result = $this->stateMachine->recordToss($matchId, $tossWinnerId, $tossDecision);
        
        if ($result) {
            // Log action
            $this->actionLogger->log($userId, 'update', 'match_toss', $matchId, [
                'toss_winner_id' => $tossWinnerId,
                'toss_decision' => $tossDecision
            ]);
            
            return ['success' => true, 'error' => null];
        }
        
        return ['success' => false, 'error' => 'Failed to record toss'];
    }

    /**
     * Get flow steps configuration
     * 
     * @param array $match Match data
     * @param array $playerCheck Player assignment check result
     * @return array Flow steps configuration
     */
    public function getFlowSteps(array $match, array $playerCheck): array
    {
        return [
            self::FLOW_STEP_CREATE => ['name' => 'Create Match', 'state' => 'draft', 'icon' => '📝', 'completed' => true],
            self::FLOW_STEP_ASSIGN_PLAYERS => ['name' => 'Assign Players', 'state' => 'draft', 'icon' => '👥', 'completed' => $playerCheck['valid']],
            self::FLOW_STEP_RECORD_TOSS => ['name' => 'Record Toss', 'state' => 'scheduled', 'icon' => '🪙', 'completed' => !empty($match['toss_winner_id'])],
            self::FLOW_STEP_START_MATCH => ['name' => 'Start Match', 'state' => 'live', 'icon' => '▶️', 'completed' => $match['state'] === 'live' || $match['state'] === 'completed'],
            self::FLOW_STEP_SCORE_MATCH => ['name' => 'Score Match', 'state' => 'live', 'icon' => '🏏', 'completed' => false, 'active' => $match['state'] === 'live'],
            self::FLOW_STEP_FINALIZE => ['name' => 'Finalize Match', 'state' => 'completed', 'icon' => '✅', 'completed' => $match['state'] === 'completed']
        ];
    }
}

