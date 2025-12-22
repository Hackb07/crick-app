<?php
/**
 * Player of the Series (POTS) Calculation
 * 
 * Aggregate points across series matches
 */

class POTS {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Calculate POTS for a series
     * 
     * @param int $seriesId Series ID
     * @return array List of player rankings
     */
    public function calculate($seriesId) {
        // Get all matches in series
        $matchModel = new MatchModel();
        $matches = $matchModel->getAll(['series_id' => $seriesId, 'state' => 'completed']);
        
        if (empty($matches)) {
            return [];
        }
        
        $playerPoints = [];
        $potm = new POTM();
        
        // Collect points from each match
        foreach ($matches as $match) {
            $potmData = $potm->getForMatch($match['match_id']);
            
            if ($potmData) {
                $playerId = $potmData['final_player_id'];
                
                if (!isset($playerPoints[$playerId])) {
                    $playerPoints[$playerId] = [
                        'player_id' => $playerId,
                        'total_points' => 0,
                        'matches' => 0
                    ];
                }
                
                // Award points for POTM
                $playerPoints[$playerId]['total_points'] += 10; // Base points for POTM
                $playerPoints[$playerId]['matches']++;
            }
        }
        
        // Add consistency bonus (players who performed in multiple matches)
        foreach ($playerPoints as $playerId => &$data) {
            if ($data['matches'] >= 3) {
                $data['consistency_bonus'] = 5;
                $data['total_points'] += 5;
            } else {
                $data['consistency_bonus'] = 0;
            }
        }
        
        // Sort by total points
        usort($playerPoints, function($a, $b) {
            return $b['total_points'] <=> $a['total_points'];
        });
        
        // Assign ranks
        $rank = 1;
        foreach ($playerPoints as &$data) {
            $data['rank'] = $rank++;
        }
        
        return $playerPoints;
    }
    
    /**
     * Save POTS rankings
     * 
     * @param int $seriesId Series ID
     * @param array $rankings Player rankings
     * @return bool Success
     */
    public function saveRankings($seriesId, $rankings) {
        $sql = "INSERT INTO pots_aggregate (series_id, player_id, total_points, consistency_bonus, rank, updated_at) 
                VALUES (:series_id, :player_id, :total_points, :consistency_bonus, :rank, NOW())
                ON DUPLICATE KEY UPDATE 
                total_points = :total_points_u, 
                consistency_bonus = :consistency_bonus_u, 
                rank = :rank_u, 
                updated_at = NOW()";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($rankings as $ranking) {
            try {
                $stmt->execute([
                    'series_id' => $seriesId,
                    'player_id' => $ranking['player_id'],
                    'total_points' => $ranking['total_points'],
                    'consistency_bonus' => $ranking['consistency_bonus'] ?? 0,
                    'rank' => $ranking['rank'],
                    'total_points_u' => $ranking['total_points'],
                    'consistency_bonus_u' => $ranking['consistency_bonus'] ?? 0,
                    'rank_u' => $ranking['rank']
                ]);
            } catch (PDOException $e) {
                error_log("Error saving ranking for player " . $ranking['player_id'] . ": " . $e->getMessage());
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get POTS rankings for a series
     * 
     * @param int $seriesId Series ID
     * @return array List of rankings
     */
    public function getRankings($seriesId) {
        $sql = "SELECT pa.*, p.name as player_name
                FROM pots_aggregate pa
                INNER JOIN players p ON pa.player_id = p.player_id
                WHERE pa.series_id = :series_id
                ORDER BY pa.rank ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['series_id' => $seriesId]);
        return $stmt->fetchAll();
    }
}

