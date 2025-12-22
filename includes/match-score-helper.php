<?php
/**
 * Match Score Helper Functions
 * 
 * Utility functions to calculate match scores, innings totals, and statistics
 */

if (!function_exists('calculateMatchScore')) {
    /**
     * Calculate match score from events
     * 
     * Calculates runs, wickets, and overs for each innings from match events.
     * Handles runs, wickets, and extras (wide, no-ball, bye, leg-bye).
     * Wide and no-ball don't count as legal deliveries (don't increment ball count).
     * 
     * Formula:
     * - Total runs = sum of all run events + extras
     * - Overs = floor(balls / 6) + (balls % 6) / 10
     * - Winner = team with higher score after both innings (if match completed)
     * - Tie: if scores equal, winner remains null (needs super over)
     * 
     * @param int $matchId Match ID
     * @param int|null $innings Innings number (1 or 2) or null for both
     * @return array|null Score data with innings1, innings2, winner_id, winner_name, or null if match not found
     */
    function calculateMatchScore($matchId, $innings = null) {
        $db = Database::getInstance()->getConnection();
        
        // Get match info
        $matchModel = new MatchModel();
        $match = $matchModel->getById($matchId);
        
        if (!$match) {
            return null;
        }
        
        // Get events for the match
        $eventModel = new Event();
        $events = $eventModel->getByMatch($matchId);
        
        $innings1Runs = 0;
        $innings1Wickets = 0;
        $innings1Balls = 0;
        $innings2Runs = 0;
        $innings2Wickets = 0;
        $innings2Balls = 0;
        
        // Get current innings from match data
        $currentInnings = (int)($match['current_innings'] ?? 1);
        $eventInnings = $currentInnings;
        
        foreach ($events as $event) {
            $payload = json_decode($event['payload_json'] ?? '{}', true);
            $type = $payload['type'] ?? '';
            
            // Determine innings from event data if present
            if (isset($payload['innings'])) {
                $eventInnings = (int)$payload['innings'];
            } else {
                // Otherwise use match's current_innings
                $eventInnings = $currentInnings;
            }
            
            if ($type === 'run') {
                $runs = (int)($payload['runs'] ?? 0);
                if ($eventInnings == 1) {
                    $innings1Runs += $runs;
                    $innings1Balls++;
                } else {
                    $innings2Runs += $runs;
                    $innings2Balls++;
                }
            } elseif ($type === 'wicket') {
                // Wickets are legal deliveries and count as balls
                if ($eventInnings == 1) {
                    $innings1Wickets++;
                    $innings1Balls++;
                } else {
                    $innings2Wickets++;
                    $innings2Balls++;
                }
            } elseif ($type === 'extra') {
                $runs = (int)($payload['runs'] ?? 1); // Default to 1 run for extras
                $extraType = $payload['extra_type'] ?? '';
                if ($eventInnings == 1) {
                    $innings1Runs += $runs;
                    // Count as ball only if legal (not wide/no-ball)
                    if (isLegalBall('extra', $extraType)) {
                        $innings1Balls++;
                    }
                } else {
                    $innings2Runs += $runs;
                    if (isLegalBall('extra', $extraType)) {
                        $innings2Balls++;
                    }
                }
            } elseif ($type === 'retired_hurt') {
                // Retired hurt doesn't count as wicket or ball
                // Just track the event, no score/ball changes
            } else {
                // Count as ball if it's a valid delivery
                if ($eventInnings == 1) {
                    $innings1Balls++;
                } else {
                    $innings2Balls++;
                }
            }
        }
        
        // Calculate overs using helper function
        $innings1Overs = calculateOvers($innings1Balls);
        $innings2Overs = calculateOvers($innings2Balls);
        
        // Determine winner (if match completed)
        // Use getBattingTeam() to determine which team batted in which innings
        $winner = null;
        if ($match['state'] === 'completed') {
            // Determine which team batted in which innings based on toss
            $innings1BattingTeamId = getBattingTeam($match, 1);
            $innings2BattingTeamId = getBattingTeam($match, 2);
            
            // Get scores for each team
            $team1Score = ($innings1BattingTeamId == $match['team1_id']) ? $innings1Runs : $innings2Runs;
            $team2Score = ($innings1BattingTeamId == $match['team1_id']) ? $innings2Runs : $innings1Runs;
            
            // Determine winner
            if ($team2Score > $team1Score) {
                $winner = $match['team2_id'];
            } elseif ($team1Score > $team2Score) {
                $winner = $match['team1_id'];
            }
            // If scores equal, winner remains null (tie)
        }
        
        return [
            'match_id' => $matchId,
            'team1_id' => $match['team1_id'],
            'team2_id' => $match['team2_id'],
            'team1_name' => $match['team1_name'] ?: 'Team 1',
            'team2_name' => $match['team2_name'] ?: 'Team 2',
            'innings1' => [
                'runs' => $innings1Runs,
                'wickets' => $innings1Wickets,
                'overs' => round($innings1Overs, 1),
                'balls' => $innings1Balls
            ],
            'innings2' => [
                'runs' => $innings2Runs,
                'wickets' => $innings2Wickets,
                'overs' => round($innings2Overs, 1),
                'balls' => $innings2Balls
            ],
            'winner_id' => $winner,
            'winner_name' => $winner ? ($winner == $match['team1_id'] ? $match['team1_name'] : $match['team2_name']) : null
        ];
    }
}

if (!function_exists('getMatchScoreDisplay')) {
    /**
     * Get formatted match score display
     * 
     * @param array $scoreData Score data from calculateMatchScore
     * @param int $innings Innings number (1 or 2) or null for current
     * @return string Formatted score string
     */
    function getMatchScoreDisplay($scoreData, $innings = null) {
        if (!$scoreData) {
            return '0/0 (0)';
        }
        
        // If live match, show current innings
        if ($innings === null) {
            // Determine current innings (assume innings 2 if innings 1 is complete)
            if ($scoreData['innings2']['runs'] > 0 || $scoreData['innings2']['wickets'] > 0) {
                $innings = 2;
            } else {
                $innings = 1;
            }
        }
        
        $inningsData = $innings == 1 ? $scoreData['innings1'] : $scoreData['innings2'];
        
        return sprintf(
            '%d/%d (%s)',
            $inningsData['runs'],
            $inningsData['wickets'],
            number_format($inningsData['overs'], 1)
        );
    }
}

if (!function_exists('calculateNRR')) {
    /**
     * Calculate Net Run Rate (NRR) for a team in a series
     * 
     * @param int $teamId Team ID
     * @param int $seriesId Series ID (optional)
     * @return float NRR
     */
    function calculateNRR($teamId, $seriesId = null) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT m.match_id, m.team1_id, m.team2_id, m.state
                FROM matches m
                WHERE m.state = 'completed' 
                AND (m.team1_id = :team_id OR m.team2_id = :team_id)";
        
        $params = ['team_id' => $teamId];
        
        if ($seriesId) {
            $sql .= " AND m.series_id = :series_id";
            $params['series_id'] = $seriesId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $matches = $stmt->fetchAll();
        
        $totalRunsScored = 0;
        $totalOversFaced = 0;
        $totalRunsConceded = 0;
        $totalOversBowled = 0;
        
        foreach ($matches as $match) {
            $scoreData = calculateMatchScore($match['match_id']);
            
            if (!$scoreData) {
                continue;
            }
            
            // Determine which innings this team batted/bowled
            $isTeam1 = $match['team1_id'] == $teamId;
            
            if ($isTeam1) {
                $runsScored = $scoreData['innings1']['runs'];
                $oversFaced = $scoreData['innings1']['overs'];
                $runsConceded = $scoreData['innings2']['runs'];
                $oversBowled = $scoreData['innings2']['overs'];
            } else {
                $runsScored = $scoreData['innings2']['runs'];
                $oversFaced = $scoreData['innings2']['overs'];
                $runsConceded = $scoreData['innings1']['runs'];
                $oversBowled = $scoreData['innings1']['overs'];
            }
            
            $totalRunsScored += $runsScored;
            $totalOversFaced += $oversFaced;
            $totalRunsConceded += $runsConceded;
            $totalOversBowled += $oversBowled;
        }
        
        if ($totalOversFaced == 0 || $totalOversBowled == 0) {
            return 0.0;
        }
        
        $runRateScored = $totalRunsScored / $totalOversFaced;
        $runRateConceded = $totalRunsConceded / $totalOversBowled;
        
        return round($runRateScored - $runRateConceded, 3);
    }
}

