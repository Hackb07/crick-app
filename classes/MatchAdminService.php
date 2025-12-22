<?php
/**
 * Match Admin Service
 * Centralizes all logic for the Match Admin Console.
 */

class MatchAdminService {
    private $db;
    private $matchModel;
    private $teamModel;
    private $playerModel;
    private $stateMachine;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->matchModel = new MatchModel();
        $this->teamModel = new Team();
        $this->playerModel = new Player();
        $this->stateMachine = new MatchStateMachine();
    }

    /**
     * Get all data needed for the console in one go.
     */
    public function getConsoleData($matchId) {
        $match = $this->matchModel->getById($matchId);
        if (!$match) {
            return ['error' => 'Match not found'];
        }

        // Get Teams
        $team1 = $this->teamModel->getById($match['team1_id']);
        $team2 = $this->teamModel->getById($match['team2_id']);

        // Get Squads (Appearances)
        $appearances = $this->getAppearances($matchId);

        // Get All Players (for selection)
        // Optimization: We could filter this, but for now getting all is safe
        $allPlayers = $this->playerModel->getAll();

        // Validation Status
        $squadValidation = $this->validateSquads($matchId, $appearances);
        $tossValidation = $this->validateToss($match);
        
        return [
            'match' => $match,
            'teams' => [
                'team1' => $team1,
                'team2' => $team2
            ],
            'squads' => [
                'team1' => $appearances['team1'],
                'team2' => $appearances['team2'],
                'common' => $appearances['common']
            ],
            'all_players' => $allPlayers,
            'validation' => [
                'squads' => $squadValidation,
                'toss' => $tossValidation,
                'ready_to_start' => $squadValidation['valid'] && $tossValidation['valid']
            ]
        ];
    }

    /**
     * Get single match details
     */
    public function getMatch($matchId) {
        return $this->matchModel->getById($matchId);
    }

    /**
     * Update Match Basics (Tab 1)
     */
    public function updateBasics($matchId, $data) {
        try {
            // Validate inputs
            if ($data['team1_id'] == $data['team2_id']) {
                return ['success' => false, 'error' => 'Teams must be different'];
            }
            
            // Update via Model
            $success = $this->matchModel->update($matchId, $data);
            
            if ($success) {
                return ['success' => true, 'message' => 'Match details updated'];
            }
            
            // Get the actual database error
            $errorInfo = $this->db->errorInfo();
            $errorMessage = 'Failed to update match';
            if (isset($errorInfo[2])) {
                $errorMessage .= ': ' . $errorInfo[2];
            }
            
            return ['success' => false, 'error' => $errorMessage];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Update failed: ' . $e->getMessage()];
        }
    }

    /**
     * Set Squad for a Team (Tab 2)
     */
    public function setSquad($matchId, $teamId, $playerIds, $meta = []) {
        try {
            $this->matchModel->updateSquad($matchId, $teamId, $playerIds, $meta);
            return ['success' => true, 'message' => 'Squad updated', 'count' => count($playerIds)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Set Toss (Tab 3)
     */
    public function setToss($matchId, $winnerId, $decision) {
        return $this->stateMachine->recordToss($matchId, $winnerId, $decision);
    }

    /**
     * Start Match (Tab 4)
     */
    public function startMatch($matchId) {
        return $this->stateMachine->startMatch($matchId);
    }

    // --- Private Helpers ---

    private function getAppearances($matchId) {
        $match = $this->matchModel->getById($matchId);
        
        $team1 = $this->playerModel->getByTeamForMatch($matchId, $match['team1_id']);
        $team2 = $this->playerModel->getByTeamForMatch($matchId, $match['team2_id']);
        $common = []; // For players in both teams (edge case)

        return ['team1' => $team1, 'team2' => $team2, 'common' => $common];
    }

    private function validateSquads($matchId, $appearances) {
        $c1 = count($appearances['team1']);
        $c2 = count($appearances['team2']);
        $min = 1; // Min players per team

        if ($c1 < $min || $c2 < $min) {
            return ['valid' => false, 'error' => "Both teams need at least $min players (Team 1: $c1, Team 2: $c2)"];
        }
        return ['valid' => true];
    }

    private function validateToss($match) {
        if (!$match['toss_winner_id'] || !$match['toss_decision']) {
            return ['valid' => false, 'error' => 'Toss not recorded'];
        }
        return ['valid' => true];
    }
}
