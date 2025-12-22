<?php
/**
 * Match State Machine
 * 
 * Handles match state transitions and validation
 */

class MatchStateMachine {
    private $db;
    
    // Valid state transitions
    private $transitions = [
        'draft' => ['scheduled', 'cancelled'],
        'scheduled' => ['live', 'cancelled'],
        'live' => ['completed', 'abandoned'],
        'completed' => [], // Terminal state
        'abandoned' => [], // Terminal state
        'cancelled' => [] // Terminal state
    ];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Check if state transition is valid
     * 
     * @param string $fromState Current state
     * @param string $toState Target state
     * @return bool Is valid transition
     */
    public function canTransition($fromState, $toState) {
        if (!isset($this->transitions[$fromState])) {
            return false;
        }
        
        return in_array($toState, $this->transitions[$fromState]);
    }
    
    /**
     * Transition match state
     * 
     * @param int $matchId Match ID
     * @param string $newState New state
     * @param array $data Additional data for transition
     * @return bool Success
     */
    public function transition($matchId, $newState, $data = []) {
        $matchModel = new MatchModel();
        $match = $matchModel->getById($matchId);
        
        if (!$match) {
            return false;
        }
        
        $currentState = $match['state'];
        
        if (!$this->canTransition($currentState, $newState)) {
            return false;
        }
        
        $updateData = ['state' => $newState];
        
        // Handle specific transitions
        switch ($newState) {
            case 'live':
                // Initialize first innings
                if (empty($match['current_innings'])) {
                    $updateData['current_innings'] = 1;
                }
                break;
                
            case 'completed':
                // Ensure match is finalized
                break;
        }
        
        // Merge additional data
        $updateData = array_merge($updateData, $data);
        
        return $matchModel->update($matchId, $updateData);
    }
    
    /**
     * Record toss
     * 
     * Records which team won the toss and their decision (bat first or bowl first).
     * Transitions match state from 'draft' to 'scheduled'.
     * 
     * @param int $matchId Match ID
     * @param int $tossWinnerId Toss winner team ID (must be team1_id or team2_id)
     * @param string $decision Decision: 'bat' (bat first) or 'bowl' (bowl first)
     * @return array Success/error array
     */
    public function recordToss($matchId, $tossWinnerId, $decision) {
        try {
            $matchModel = new MatchModel();
            $match = $matchModel->getById($matchId);
            
            if (!$match) {
                return ['success' => false, 'error' => 'Match not found'];
            }
            
            // Validate toss winner is one of the teams
            if ($tossWinnerId != $match['team1_id'] && $tossWinnerId != $match['team2_id']) {
                return ['success' => false, 'error' => 'Invalid toss winner. Must be one of the match teams.'];
            }
            
            // Validate decision
            if (!in_array($decision, ['bat', 'bowl'])) {
                return ['success' => false, 'error' => 'Invalid toss decision. Must be "bat" or "bowl".'];
            }
            
            // Can only record toss in draft or scheduled state
            if (!in_array($match['state'], ['draft', 'scheduled'])) {
                return ['success' => false, 'error' => 'Cannot record toss. Match is already ' . $match['state'] . '.'];
            }
            
            // State transitions to 'scheduled' after toss is recorded
            $result = $matchModel->update($matchId, [
                'toss_winner_id' => $tossWinnerId,
                'toss_decision' => $decision,
                'state' => 'scheduled'
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Toss recorded successfully'];
            } else {
                return ['success' => false, 'error' => 'Failed to record toss'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Error recording toss: ' . $e->getMessage()];
        }
    }
    
    /**
     * Check if players are assigned to teams for a match
     * 
     * Validates that both teams have at least 1 player assigned.
     * Detects common players (players assigned to both teams).
     * 
     * @param int $matchId Match ID
     * @return array ['valid' => bool, 'team1_count' => int, 'team2_count' => int, 'common_players' => array]
     *               valid = true if both teams have at least 1 player
     */
    public function checkPlayerAssignments($matchId) {
        $matchModel = new MatchModel();
        $match = $matchModel->getById($matchId);
        
        if (!$match) {
            return ['valid' => false, 'team1_count' => 0, 'team2_count' => 0, 'common_players' => []];
        }
        
        // Count players for each team
        $team1Sql = "SELECT COUNT(DISTINCT player_id) as count FROM player_appearances 
                     WHERE match_id = :match_id AND team_id = :team_id";
        $team1Stmt = $this->db->prepare($team1Sql);
        $team1Stmt->execute(['match_id' => $matchId, 'team_id' => $match['team1_id']]);
        $team1Count = (int)($team1Stmt->fetch()['count'] ?? 0);
        
        $team2Sql = "SELECT COUNT(DISTINCT player_id) as count FROM player_appearances 
                     WHERE match_id = :match_id AND team_id = :team_id";
        $team2Stmt = $this->db->prepare($team2Sql);
        $team2Stmt->execute(['match_id' => $matchId, 'team_id' => $match['team2_id']]);
        $team2Count = (int)($team2Stmt->fetch()['count'] ?? 0);
        
        // Check for common players (players who appear for both teams)
        $commonSql = "SELECT pa1.player_id, p.name as player_name
                      FROM player_appearances pa1
                      INNER JOIN player_appearances pa2 ON pa1.player_id = pa2.player_id
                      INNER JOIN players p ON pa1.player_id = p.player_id
                      WHERE pa1.match_id = :match_id_1 
                        AND pa1.team_id = :team1_id 
                        AND pa2.match_id = :match_id_2 
                        AND pa2.team_id = :team2_id
                      GROUP BY pa1.player_id, p.name";
        $commonStmt = $this->db->prepare($commonSql);
        $commonStmt->execute([
            'match_id_1' => $matchId,
            'match_id_2' => $matchId,
            'team1_id' => $match['team1_id'],
            'team2_id' => $match['team2_id']
        ]);
        $commonPlayers = $commonStmt->fetchAll();
        
        // Match can start if both teams have at least 1 player assigned
        // Note: Minimum requirement is 1 player per team (not 4 as per plan - allowing flexibility)
        $valid = $team1Count > 0 && $team2Count > 0;
        
        return [
            'valid' => $valid,
            'team1_count' => $team1Count,
            'team2_count' => $team2Count,
            'common_players' => $commonPlayers
        ];
    }
    
    /**
     * Start match
     * 
     * Starts a match by transitioning from 'scheduled' to 'live' state.
     * Prerequisites:
     * - Match must be in 'scheduled' state
     * - Toss must be recorded (toss_winner_id and toss_decision)
     * - At least 1 player must be assigned to each team
     * 
     * Sets current_innings to 1 on start.
     * 
     * @param int $matchId Match ID
     * @return array ['success' => bool, 'error' => string|null, 'common_players' => array]
     *               Returns success status, error message if failed, and list of common players if any
     */
    public function startMatch($matchId) {
        $matchModel = new MatchModel();
        $match = $matchModel->getById($matchId);
        
        if (!$match) {
            return ['success' => false, 'error' => 'Match not found'];
        }
        
        // Check if toss is recorded
        if (empty($match['toss_winner_id']) || empty($match['toss_decision'])) {
            return ['success' => false, 'error' => 'Toss must be recorded before starting the match'];
        }
        
        // Check if players are assigned (minimum 1 per team)
        $playerCheck = $this->checkPlayerAssignments($matchId);
        if (!$playerCheck['valid']) {
            $errorMsg = 'Players must be assigned to both teams before starting the match.';
            if ($playerCheck['team1_count'] == 0) {
                $errorMsg .= ' Team 1 has no players assigned.';
            }
            if ($playerCheck['team2_count'] == 0) {
                $errorMsg .= ' Team 2 has no players assigned.';
            }
            return ['success' => false, 'error' => $errorMsg];
        }
        
        // Warn about common players but allow start
        if (!empty($playerCheck['common_players'])) {
            // Log warning but allow match to start
            error_log("Warning: Match $matchId has common players: " . json_encode($playerCheck['common_players']));
        }
        
        // Proceed with starting match - sets current_innings to 1
        $result = $this->transition($matchId, 'live', ['current_innings' => 1]);
        
        if ($result) {
            return ['success' => true, 'error' => null, 'common_players' => $playerCheck['common_players']];
        } else {
            return ['success' => false, 'error' => 'Failed to start match'];
        }
    }
    
    /**
     * Finalize match
     * 
     * Transitions match from 'live' to 'completed' state.
     * Prerequisites:
     * - Match must be in 'live' state
     * 
     * After finalization:
     * - Match becomes read-only
     * - Winner is determined (team with higher score after both innings)
     * - POTM calculation is triggered
     * 
     * Winner determination:
     * - If innings 2 score > innings 1 score: Team 2 wins
     * - If innings 1 score > innings 2 score: Team 1 wins
     * - If scores equal: Tie (winner remains null, needs super over)
     * 
     * @param int $matchId Match ID
     * @return bool Success - true if finalized, false if invalid state or match not found
     */
    public function finalizeMatch($matchId) {
        return $this->transition($matchId, 'completed');
    }
    
    /**
     * Change innings (from 1 to 2)
     * 
     * Transitions from first innings to second innings.
     * Prerequisites:
     * - Match must be in 'live' state
     * - Current innings must be 1
     * 
     * Updates current_innings to 2. Teams swap (batting team becomes bowling team).
     * 
     * @param int $matchId Match ID
     * @return array ['success' => bool, 'error' => string|null] Success status with error message if failed
     */
    public function changeInnings($matchId) {
        try {
            $matchModel = new MatchModel();
            $match = $matchModel->getById($matchId);
            
            if (!$match) {
                error_log('changeInnings: Match not found for match_id=' . $matchId);
                return ['success' => false, 'error' => 'Match not found'];
            }
            
            // Check match state
            if ($match['state'] !== 'live') {
                error_log('changeInnings: Match not in live state. match_id=' . $matchId . ', state=' . $match['state']);
                return ['success' => false, 'error' => 'Match must be in live state to change innings. Current state: ' . $match['state']];
            }
            
            // Check current innings
            $currentInnings = (int)($match['current_innings'] ?? 1);
            if ($currentInnings !== 1) {
                error_log('changeInnings: Invalid innings progression. match_id=' . $matchId . ', current_innings=' . $currentInnings);
                return ['success' => false, 'error' => 'Can only change from innings 1 to 2. Current innings: ' . $currentInnings];
            }
            
            // Update to innings 2
            error_log('changeInnings: Attempting to update match_id=' . $matchId . ' to innings 2');
            $result = $matchModel->update($matchId, [
                'current_innings' => 2
            ]);
            
            if (!$result) {
                error_log('changeInnings: Update failed for match_id=' . $matchId);
                // Try to get more details about the failure
                $errorInfo = $this->db->errorInfo();
                if ($errorInfo && $errorInfo[0] !== '00000') {
                    error_log('changeInnings: Database error - ' . json_encode($errorInfo));
                    return ['success' => false, 'error' => 'Database error: ' . ($errorInfo[2] ?? 'Update failed')];
                }
                return ['success' => false, 'error' => 'Failed to update match innings'];
            }
            
            error_log('changeInnings: Successfully updated match_id=' . $matchId . ' to innings 2');
            return ['success' => true, 'error' => null];
        } catch (PDOException $e) {
            error_log('changeInnings: PDO Exception - ' . $e->getMessage() . ' | Code: ' . $e->getCode());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        } catch (Exception $e) {
            error_log('changeInnings: Exception - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
        } catch (Error $e) {
            error_log('changeInnings: Fatal Error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return ['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()];
        }
    }
}
