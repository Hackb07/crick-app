<?php
/**
 * Cricket Match Helper Functions
 * 
 * Simplified helper functions to reduce complexity in match logic
 */

if (!function_exists('getBattingTeam')) {
    /**
     * Determine batting team based on innings and toss decision
     * 
     * @param array $match Match data
     * @param int $innings Innings number (1 or 2)
     * @return int Team ID of batting team
     */
    function getBattingTeam($match, $innings = 1) {
        $tossWinnerId = $match['toss_winner_id'] ?? null;
        $tossDecision = $match['toss_decision'] ?? null;
        $team1Id = $match['team1_id'];
        $team2Id = $match['team2_id'];
        
        if (!$tossWinnerId || !$tossDecision) {
            // Default to team1 if toss not recorded
            return $team1Id;
        }
        
        if ($innings == 1) {
            // First innings: toss winner bats if chose 'bat', else other team bats
            if ($tossDecision === 'bat') {
                return $tossWinnerId;
            } else {
                // Toss winner chose to bowl, so other team bats
                return ($tossWinnerId == $team1Id) ? $team2Id : $team1Id;
            }
        } else {
            // Second innings: opposite of first innings
            if ($tossDecision === 'bat') {
                // Toss winner batted first, so other team bats second
                return ($tossWinnerId == $team1Id) ? $team2Id : $team1Id;
            } else {
                // Toss winner bowled first, so they bat second
                return $tossWinnerId;
            }
        }
    }
}

if (!function_exists('calculateOvers')) {
    /**
     * Calculate overs from balls
     * 
     * @param int $balls Number of balls
     * @return float Overs (e.g., 15.3 for 15 overs 3 balls)
     */
    function calculateOvers($balls) {
        $completedOvers = floor($balls / 6);
        $remainingBalls = $balls % 6;
        return $completedOvers + ($remainingBalls / 10);
    }
}

if (!function_exists('isInningsComplete')) {
    /**
     * Check if innings is complete
     * 
     * @param int $wickets Current wickets
     * @param int $overs Current overs
     * @param float $maxOvers Maximum overs per innings
     * @param int $maxWickets Maximum wickets (default 10 for 11-player teams)
     * @return bool True if innings complete
     */
    function isInningsComplete($wickets, $overs, $maxOvers, $maxWickets = 10) {
        return $wickets >= $maxWickets || $overs >= $maxOvers;
    }
}

if (!function_exists('calculateTarget')) {
    /**
     * Calculate target for second innings
     * 
     * @param int $firstInningsTotal First innings total runs
     * @return int Target (first innings total + 1)
     */
    function calculateTarget($firstInningsTotal) {
        return $firstInningsTotal + 1;
    }
}

if (!function_exists('calculateRequiredRunRate')) {
    /**
     * Calculate required run rate for second innings
     * 
     * @param int $target Target runs
     * @param int $currentScore Current score
     * @param float $remainingOvers Remaining overs
     * @return float Required run rate (0 if already reached target or no overs left)
     */
    function calculateRequiredRunRate($target, $currentScore, $remainingOvers) {
        $remainingRuns = $target - $currentScore;
        
        if ($remainingRuns <= 0 || $remainingOvers <= 0) {
            return 0.0;
        }
        
        return round($remainingRuns / $remainingOvers, 2);
    }
}

if (!function_exists('calculateRunRate')) {
    /**
     * Calculate run rate
     * 
     * @param int $runs Runs scored
     * @param int $balls Balls faced
     * @return float Run rate (runs per over)
     */
    function calculateRunRate($runs, $balls) {
        if ($balls == 0) {
            return 0.0;
        }
        return round(($runs / $balls) * 6, 2);
    }
}

if (!function_exists('shouldRotateStrike')) {
    /**
     * Determine if strike should rotate based on runs scored
     * 
     * @param int $runs Runs scored on the ball
     * @param bool $isNoBall Whether it was a no-ball
     * @return bool True if strike should rotate
     */
    function shouldRotateStrike($runs, $isNoBall = false) {
        // For no-ball, use the runs scored (including the no-ball run)
        // Odd runs (1, 3) rotate strike, even runs (0, 2, 4, 6) don't
        return ($runs % 2 === 1);
    }
}

if (!function_exists('isLegalBall')) {
    /**
     * Check if ball counts as legal delivery
     * 
     * @param string $type Event type (run, wicket, extra)
     * @param string|null $extraType Extra type if type is 'extra'
     * @return bool True if counts as legal ball
     */
    function isLegalBall($type, $extraType = null) {
        if ($type === 'extra') {
            // Wide and no-ball don't count as legal deliveries
            return !in_array($extraType, ['wide', 'no-ball']);
        }
        // Runs and wickets count as legal balls
        return true;
    }
}

if (!function_exists('calculateNRR')) {
    /**
     * Calculate Net Run Rate for a team
     * 
     * @param int $teamId Team ID
     * @param int|null $seriesId Series ID (optional)
     * @return float Net Run Rate
     */
    function calculateNRR($teamId, $seriesId = null) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get all completed matches for this team
            $sql = "SELECT m.match_id, m.team1_id, m.team2_id 
                    FROM matches m 
                    WHERE (m.team1_id = :team_id OR m.team2_id = :team_id) 
                    AND m.state = 'completed'";
            
            $params = ['team_id' => $teamId];
            if ($seriesId) {
                $sql .= " AND m.series_id = :series_id";
                $params['series_id'] = $seriesId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $totalRunsScored = 0;
            $totalBallsFaced = 0;
            $totalRunsConceded = 0;
            $totalBallsBowled = 0;
            
            foreach ($matches as $match) {
                $score = calculateMatchScore($match['match_id']);
                if (!$score) continue;
                
                // Determine if team batted first or second
                $isTeam1 = ($match['team1_id'] == $teamId);
                $battingTeam1 = getBattingTeam($match, 1);
                
                $teamBattingInnings = ($battingTeam1 == $teamId) ? 1 : 2;
                $opponentBattingInnings = ($teamBattingInnings == 1) ? 2 : 1;
                
                $teamStats = $score['innings' . $teamBattingInnings] ?? null;
                $oppStats = $score['innings' . $opponentBattingInnings] ?? null;
                
                if ($teamStats) {
                    $totalRunsScored += $teamStats['runs'];
                    // If team was all out, use full quota of overs (e.g. 20.0)
                    // Assuming T20 for now, but ideally should check match type
                    if ($teamStats['wickets'] >= 10) {
                        $totalBallsFaced += 120; // 20 overs * 6 balls
                    } else {
                        $totalBallsFaced += ($teamStats['balls'] ?? 0); // Use legal balls
                    }
                }
                
                if ($oppStats) {
                    $totalRunsConceded += $oppStats['runs'];
                    if ($oppStats['wickets'] >= 10) {
                        $totalBallsBowled += 120;
                    } else {
                        $totalBallsBowled += ($oppStats['balls'] ?? 0);
                    }
                }
            }
            
            if ($totalBallsFaced == 0 || $totalBallsBowled == 0) return 0.0;
            
            $runsPerOverFor = $totalRunsScored / ($totalBallsFaced / 6);
            $runsPerOverAgainst = $totalRunsConceded / ($totalBallsBowled / 6);
            
            return round($runsPerOverFor - $runsPerOverAgainst, 3);
            
        } catch (Exception $e) {
            error_log("Error calculating NRR: " . $e->getMessage());
            return 0.0;
        }
    }
}
