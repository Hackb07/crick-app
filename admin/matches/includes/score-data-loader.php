<?php
/**
 * Score Data Loader
 * 
 * Extracted from score.php to reduce file size and improve maintainability.
 * Handles all data loading and preparation for the scoring interface.
 */

/**
 * Load all data required for the scoring interface
 * 
 * @param int $matchId Match ID
 * @return array Data array with all required information
 * @throws Exception If match not found or invalid
 */
function loadScoreData(int $matchId): array {
    // Run stats calculator to ensure DB is up to date
    require_once __DIR__ . '/../../../classes/StatsCalculator.php';
    $statsCalculator = new StatsCalculator();
    $statsCalculator->updateMatchStats($matchId);
    
    $matchModel = new MatchModel();
    
    // Force refresh match data if cache-busting parameter is present
    if (getQuery('_t')) {
        $match = $matchModel->getById($matchId);
    } else {
        $match = $matchModel->getById($matchId);
    }
    
    if (!$match) {
        throw new Exception("Match not found: {$matchId}");
    }
    
    // Only allow scoring for live matches
    if ($match['state'] !== 'live') {
        throw new Exception("Match is not live. Current state: {$match['state']}");
    }
    
    // Get teams
    $teamModel = new Team();
    $teams = $teamModel->getAll();
    
    $db = Database::getInstance()->getConnection();
    
    // Determine current innings
    $currentInnings = $match['current_innings'] ?? 1;
    
    // Get batting team players
    $battingTeamId = getBattingTeam($match, $currentInnings);
    $bowlingTeamId = ($battingTeamId == $match['team1_id']) ? $match['team2_id'] : $match['team1_id'];
    
    // Get players from player_appearances for this match
    $playerModel = new Player();
    $battingTeamPlayers = $playerModel->getByTeamForMatch($matchId, $battingTeamId);
    $bowlingTeamPlayers = $playerModel->getByTeamForMatch($matchId, $bowlingTeamId);
    
    // For backward compatibility
    $team1Players = ($battingTeamId == $match['team1_id']) ? $battingTeamPlayers : $bowlingTeamPlayers;
    $team2Players = ($battingTeamId == $match['team1_id']) ? $bowlingTeamPlayers : $battingTeamPlayers;
    
    // Get current striker/non-striker/bowler (processes events)
    $currentState = getCurrentMatchState($matchId, $currentInnings);
    
    // Get dismissed players
    $dismissedPlayerIds = getDismissedPlayers($matchId, $currentInnings);
    
    // Extract IDs from current state
    $currentStrikerId = $currentState['striker_id'] ?? null;
    $currentNonStrikerId = $currentState['non_striker_id'] ?? null;
    $currentBowlerId = $currentState['bowler_id'] ?? null;
    
    // Get available batsmen (exclude dismissed and current batsmen)
    $availableBatsmen = getAvailableBatsmen(
        $battingTeamPlayers,
        $dismissedPlayerIds,
        $currentStrikerId,
        $currentNonStrikerId
    );
    
    // Calculate team sizes and max wickets
    $battingTeamSize = count($battingTeamPlayers);
    $bowlingTeamSize = count($bowlingTeamPlayers);
    $maxWickets = max(1, $battingTeamSize - 1);
    
    // Get team names
    $battingTeam = $battingTeamId == $match['team1_id'] ? $match['team1_name'] : $match['team2_name'];
    $bowlingTeam = $battingTeamId == $match['team1_id'] ? $match['team2_name'] : $match['team1_name'];
    
    // Calculate first innings total for target (if innings 2)
    $firstInningsTotal = 0;
    if ($currentInnings == 2) {
        $scoreData = calculateMatchScore($matchId, 1);
        if ($scoreData && isset($scoreData['innings1'])) {
            $firstInningsTotal = $scoreData['innings1']['runs'];
        }
    }
    
    // Get player stats for JavaScript
    $jsPlayerStats = getPlayerStatsForJs($statsCalculator, $matchId);
    
    // Get success/error messages from session
    $messages = [
        'success' => getSession('success', ''),
        'error' => getSession('error', '')
    ];
    unsetSession('success');
    unsetSession('error');
    
    return [
        'match' => $match,
        'teams' => $teams,
        'current_innings' => $currentInnings,
        'batting_team_id' => $battingTeamId,
        'bowling_team_id' => $bowlingTeamId,
        'batting_team_players' => $battingTeamPlayers,
        'bowling_team_players' => $bowlingTeamPlayers,
        'team1_players' => $team1Players,
        'team2_players' => $team2Players,
        'available_batsmen' => $availableBatsmen,
        'current_state' => $currentState,
        'current_striker_id' => $currentStrikerId,
        'current_non_striker_id' => $currentNonStrikerId,
        'current_bowler_id' => $currentBowlerId,
        'dismissed_player_ids' => $dismissedPlayerIds,
        'batting_team_size' => $battingTeamSize,
        'bowling_team_size' => $bowlingTeamSize,
        'max_wickets' => $maxWickets,
        'batting_team' => $battingTeam,
        'bowling_team' => $bowlingTeam,
        'first_innings_total' => $firstInningsTotal,
        'js_player_stats' => $jsPlayerStats,
        'messages' => $messages,
        'current_innings_events' => $currentState['current_innings_events'] ?? []
    ];
}

/**
 * Get current match state (striker, non-striker, bowler)
 * 
 * Processes all events for current innings to determine current state.
 * This matches the original logic in score.php.
 * 
 * @param int $matchId Match ID
 * @param int $currentInnings Current innings
 * @return array Current state including events array
 */
function getCurrentMatchState(int $matchId, int $currentInnings): array {
    // Get all events for the match
    $eventModel = new Event();
    $events = $eventModel->getByMatch($matchId);
    
    $state = [
        'striker_id' => null,
        'non_striker_id' => null,
        'bowler_id' => null,
        'runs' => 0,
        'wickets' => 0,
        'balls' => 0,
        'overs' => 0,
        'legal_balls' => 0,
        'current_innings_events' => [],
        'last_over_bowler_id' => null,
        'current_bowler_balls' => 0
    ];
    
    // Process all events for current innings
    $currentScore = 0;
    $currentWickets = 0;
    $legalBalls = 0;
    
    // Variables for over tracking
    $ballsInCurrentOver = 0;
    $lastOverBowlerId = null;
    $currentOverBowlerBalls = 0;
    $currentOverBowlerId = null;
    
    foreach ($events as $event) {
        $payload = json_decode($event['payload_json'] ?? '{}', true);
        $eventInnings = (int)($payload['innings'] ?? 1);
        
        if ($eventInnings == $currentInnings) {
            $state['current_innings_events'][] = $event;
            
            // Calculate Score & Wickets
            $type = $payload['type'] ?? '';
            $runs = (int)($payload['runs'] ?? 0);
            $extraType = $payload['extra_type'] ?? '';
            $bowlerId = isset($payload['bowler_id']) ? (int)$payload['bowler_id'] : null;
            
            $isLegalDelivery = false;
            
            if ($type === 'run') {
                $currentScore += $runs;
                $legalBalls++;
                $isLegalDelivery = true;
            } elseif ($type === 'extra') {
                $currentScore += $runs;
                if ($extraType !== 'wide' && $extraType !== 'no-ball') {
                    $legalBalls++;
                    $isLegalDelivery = true;
                }
            } elseif ($type === 'wicket') {
                $currentWickets++;
                $legalBalls++;
                $isLegalDelivery = true;
            }
            
            // Track over progress
            if ($isLegalDelivery) {
                $ballsInCurrentOver++;
                
                // Track current bowler balls in this over
                if ($bowlerId) {
                    if ($currentOverBowlerId !== $bowlerId) {
                        // Bowler changed mid-over (or start of over)
                        $currentOverBowlerId = $bowlerId;
                        $currentOverBowlerBalls = 1;
                    } else {
                        $currentOverBowlerBalls++;
                    }
                }
                
                // Check for over completion
                if ($ballsInCurrentOver >= 6) {
                    $ballsInCurrentOver = 0;
                    $lastOverBowlerId = $currentOverBowlerId; // The bowler who finished the over
                    $currentOverBowlerBalls = 0; // Reset for next over
                    $currentOverBowlerId = null;
                }
            }
            
            // Track current batsmen and bowler (from last run/ball event)
            if (in_array($type, ['run', 'extra', 'wicket'])) {
                if (isset($payload['striker_id'])) {
                    $state['striker_id'] = (int)$payload['striker_id'];
                }
                if (isset($payload['non_striker_id'])) {
                    $state['non_striker_id'] = (int)$payload['non_striker_id'];
                }
                if (isset($payload['bowler_id'])) {
                    $state['bowler_id'] = (int)$payload['bowler_id'];
                }
            }
            
            // Handle wicket with new batsman
            if ($type === 'wicket' && isset($payload['new_batsman_id'])) {
                $state['striker_id'] = (int)$payload['new_batsman_id'];
            }
            
            // Handle retired hurt replacement
            if ($type === 'retired_hurt' && isset($payload['replacement_player_id'])) {
                $state['striker_id'] = (int)$payload['replacement_player_id'];
            }
        }
    }
    
    $state['runs'] = $currentScore;
    $state['wickets'] = $currentWickets;
    $state['legal_balls'] = $legalBalls;
    $state['balls'] = $legalBalls;
    $state['last_over_bowler_id'] = $lastOverBowlerId;
    $state['current_bowler_balls'] = $currentOverBowlerBalls;
    
    // Calculate overs
    $state['overs'] = calculateOvers($legalBalls);
    
    return $state;
}

/**
 * Get dismissed players for current innings
 * 
 * @param int $matchId Match ID
 * @param int $currentInnings Current innings
 * @return array Dismissed player IDs
 */
function getDismissedPlayers(int $matchId, int $currentInnings): array {
    // Get all events for the match to find dismissed players
    $eventModel = new Event();
    $events = $eventModel->getByMatch($matchId);
    
    $dismissedIds = [];
    
    foreach ($events as $event) {
        $payload = json_decode($event['payload_json'] ?? '{}', true);
        $eventInnings = (int)($payload['innings'] ?? 1);
        
        if ($eventInnings == $currentInnings && ($payload['type'] ?? '') === 'wicket') {
            // Track dismissed striker
            if (isset($payload['striker_id'])) {
                $dismissedIds[] = (int)$payload['striker_id'];
            }
            // Also check batsman_id (for consistency)
            if (isset($payload['batsman_id'])) {
                $dismissedIds[] = (int)$payload['batsman_id'];
            }
        }
    }
    
    return array_unique($dismissedIds);
}

/**
 * Get available batsmen (exclude dismissed and current batsmen)
 * 
 * @param array $battingTeamPlayers All batting team players
 * @param array $dismissedPlayerIds Dismissed player IDs
 * @param int|null $currentStrikerId Current striker ID
 * @param int|null $currentNonStrikerId Current non-striker ID
 * @return array Available batsmen
 */
function getAvailableBatsmen(
    array $battingTeamPlayers,
    array $dismissedPlayerIds,
    ?int $currentStrikerId,
    ?int $currentNonStrikerId
): array {
    $available = [];
    
    foreach ($battingTeamPlayers as $player) {
        $playerId = (int)$player['player_id'];
        
        // Skip if dismissed
        if (in_array($playerId, $dismissedPlayerIds)) {
            continue;
        }
        
        // Skip if currently batting
        if ($playerId == $currentStrikerId || $playerId == $currentNonStrikerId) {
            continue;
        }
        
        $available[] = $player;
    }
    
    return $available;
}

/**
 * Get player stats for JavaScript
 * 
 * @param StatsCalculator $statsCalculator Stats Calculator instance
 * @param int $matchId Match ID
 * @return array Player stats formatted for JS
 */
function getPlayerStatsForJs(StatsCalculator $statsCalculator, int $matchId): array {
    $stats = ['batsmen' => [], 'bowlers' => []];
    
    // Get batting stats
    $battingStats = $statsCalculator->getBattingStats($matchId);
    foreach ($battingStats as $row) {
        $stats['batsmen'][$row['player_id']] = [
            'runs' => (int)$row['runs'],
            'balls' => (int)$row['balls'],
            'fours' => (int)$row['fours'],
            'sixes' => (int)$row['sixes']
        ];
    }
    
    // Get bowling stats
    $bowlingStats = $statsCalculator->getBowlingStats($matchId);
    foreach ($bowlingStats as $row) {
        $stats['bowlers'][$row['player_id']] = [
            'runs' => (int)$row['runs'],
            'balls' => (int)$row['balls'],
            'wickets' => (int)$row['wickets'],
            'overs' => (float)$row['overs']
        ];
    }
    
    return $stats;
}

