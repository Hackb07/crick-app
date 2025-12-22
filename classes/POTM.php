<?php
/**
 * Player of the Match (POTM) Calculation
 * 
 * Automated calculation based on runs, wickets, and fielding
 */

class POTM {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Calculate Player of the Match (POTM) for a match
     * 
     * Scoring algorithm:
     * - Batting: runs × weight + milestone bonuses (50: +10, 100: +20) + strike rate bonus (>150: +5)
     * - Bowling: wickets × weight × 10 + milestone bonuses (3: +10, 5: +20) + economy bonus (<6: +5)
     * - Fielding: catches × weight × 5
     * 
     * Player with highest total score is selected as POTM.
     * 
     * @param int $matchId Match ID
     * @return array|null POTM data with player_id, player_name, score, or null if no stats
     */
    public function calculate($matchId) {
        // Get match stats for all players
        $stats = $this->getMatchStats($matchId);
        
        if (empty($stats)) {
            return null;
        }
        
        $scores = [];
        
        foreach ($stats as $stat) {
            $score = 0;
            
            // Batting points
            if ($stat['runs'] > 0) {
                $score += $stat['runs'] * POTM_WEIGHT_RUNS;
                
                // Milestone bonuses
                if ($stat['runs'] >= 100) {
                    $score += 20;
                } elseif ($stat['runs'] >= 50) {
                    $score += 10;
                }
                
                // Strike rate bonus (if > 150)
                if ($stat['strike_rate'] > 150 && $stat['balls_faced'] >= 10) {
                    $score += 5;
                }
            }
            
            // Bowling points
            if ($stat['wickets'] > 0) {
                $wicketPoints = $stat['wickets'] * POTM_WEIGHT_WICKETS * 10;
                $score += $wicketPoints;
                
                // Milestone bonuses
                if ($stat['wickets'] >= 5) {
                    $score += 20;
                } elseif ($stat['wickets'] >= 3) {
                    $score += 10;
                }
                
                // Economy bonus (if < 6)
                if ($stat['economy_rate'] < 6 && $stat['overs_bowled'] >= 2) {
                    $score += 5;
                }
            }
            
            // Fielding points (catches, runouts, stumpings)
            $fieldingPoints = ($stat['catches'] ?? 0) * POTM_WEIGHT_FIELDING * 5;
            $score += $fieldingPoints;
            
            $scores[] = [
                'appearance_id' => $stat['appearance_id'],
                'player_id' => $stat['player_id'],
                'player_name' => $stat['player_name'],
                'score' => $score,
                'runs' => $stat['runs'],
                'wickets' => $stat['wickets'],
                'catches' => $stat['catches'] ?? 0
            ];
        }
        
        // Sort by score descending
        usort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        if (empty($scores) || $scores[0]['score'] <= 0) {
            return null;
        }
        
        return $scores[0];
    }
    
    /**
     * Get match statistics for all players
     * 
     * @param int $matchId Match ID
     * @return array Player statistics
     */
    private function getMatchStats($matchId) {
        $sql = "SELECT sc.*, p.name as player_name
                FROM stats_cache sc
                INNER JOIN players p ON sc.player_id = p.player_id
                WHERE sc.match_id = :match_id
                ORDER BY sc.runs DESC, sc.wickets DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Save POTM decision
     * 
     * @param int $matchId Match ID
     * @param int $computedPlayerId System-calculated player ID
     * @param int|null $finalPlayerId Final POTM (may be overridden)
     * @param string $reason Decision reason
     * @param int|null $adminId Admin who made decision
     * @return bool Success
     */
    public function saveDecision($matchId, $computedPlayerId, $finalPlayerId = null, $reason = '', $adminId = null) {
        if ($finalPlayerId === null) {
            $finalPlayerId = $computedPlayerId;
        }
        
        $sql = "INSERT INTO potm_decisions (match_id, computed_player_id, final_player_id, reason, admin_id, timestamp) 
                VALUES (:match_id, :computed_player_id, :final_player_id, :reason, :admin_id, NOW())
                ON DUPLICATE KEY UPDATE 
                final_player_id = :final_player_id, 
                reason = :reason, 
                admin_id = :admin_id, 
                timestamp = NOW()";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'match_id' => $matchId,
            'computed_player_id' => $computedPlayerId,
            'final_player_id' => $finalPlayerId,
            'reason' => $reason,
            'admin_id' => $adminId
        ]);
    }
    
    /**
     * Get POTM for a match
     * 
     * @param int $matchId Match ID
     * @return array|null POTM data or null
     */
    public function getForMatch($matchId) {
        $sql = "SELECT pd.*, p.name as player_name
                FROM potm_decisions pd
                INNER JOIN players p ON pd.final_player_id = p.player_id
                WHERE pd.match_id = :match_id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId]);
        return $stmt->fetch() ?: null;
    }
}

