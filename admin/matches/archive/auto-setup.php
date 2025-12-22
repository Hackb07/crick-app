<?php
/**
 * Auto-Setup Match - Admin Panel
 * 
 * Automatically assigns random players, records a random toss,
 * and starts the match to streamline testing and development.
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/includes/match-flow-helpers.php';

requireLogin();

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

// Validate match ID
$matchId = (int)getPost('match_id', 0);
if ($matchId <= 0) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

// Validate CSRF token
try {
    requireCsrfToken(getPost('csrf_token', ''));
} catch (Exception $e) {
    header('Location: ' . adminUrl("matches/flow.php?id=$matchId&error=" . urlencode('Security validation failed') . '#match-flow-content'));
    exit;
}

$matchModel = new MatchModel();
$match = $matchModel->getById($matchId);

if (!$match) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

// Initialize services
$flowService = new MatchFlowService();
$stateMachine = new MatchStateMachine();
$playerModel = new Player();
$userId = getSession('user_id');

try {
    // 1. Auto-Assign Players if needed
    $playerCheck = $stateMachine->checkPlayerAssignments($matchId);
    
    if (!$playerCheck['valid']) {
        // Get all players
        $allPlayers = $playerModel->getAll();
        
        if (count($allPlayers) < 22) {
            throw new Exception('Not enough players in the database to auto-assign (need at least 22).');
        }
        
        // Shuffle players
        shuffle($allPlayers);
        
        // Assign 11 to Team 1
        if ($playerCheck['team1_count'] == 0) {
            $team1Players = array_slice($allPlayers, 0, 11);
            $team1Ids = array_column($team1Players, 'player_id');
            $result = $flowService->assignPlayersToTeam($matchId, $match['team1_id'], $team1Ids, $userId);
            if (!$result['success']) {
                throw new Exception('Failed to auto-assign players to Team 1: ' . $result['error']);
            }
        }
        
        // Assign 11 to Team 2 (from remaining)
        if ($playerCheck['team2_count'] == 0) {
            $team2Players = array_slice($allPlayers, 11, 11);
            $team2Ids = array_column($team2Players, 'player_id');
            $result = $flowService->assignPlayersToTeam($matchId, $match['team2_id'], $team2Ids, $userId);
            if (!$result['success']) {
                throw new Exception('Failed to auto-assign players to Team 2: ' . $result['error']);
            }
        }
    }
    
    // 2. Auto-Record Toss if needed
    // Reload match to get latest state
    $match = $matchModel->getById($matchId);
    
    if (empty($match['toss_winner_id'])) {
        // Random toss winner
        $tossWinnerId = (rand(0, 1) == 0) ? $match['team1_id'] : $match['team2_id'];
        
        // Random decision
        $tossDecision = (rand(0, 1) == 0) ? 'bat' : 'bowl';
        
        $result = $flowService->recordToss($matchId, $tossWinnerId, $tossDecision, $userId);
        if (!$result['success']) {
            throw new Exception('Failed to auto-record toss: ' . $result['error']);
        }
    }
    
    // 3. Start Match if needed
    // Reload match to get latest state
    $match = $matchModel->getById($matchId);
    
    if ($match['state'] === 'scheduled') {
        $result = $stateMachine->startMatch($matchId);
        if (!$result['success']) {
            throw new Exception('Failed to start match: ' . $result['error']);
        }
    }
    
    // Success!
    header('Location: ' . adminUrl("matches/flow.php?id=$matchId&warning=" . urlencode('Match auto-setup complete! You can now score the match.') . '#match-flow-content'));
    exit;

} catch (Exception $e) {
    header('Location: ' . adminUrl("matches/flow.php?id=$matchId&error=" . urlencode($e->getMessage()) . '#match-flow-content'));
    exit;
}
