<?php
/**
 * Stats Calculator
 * 
 * Handles calculation and storage of player statistics from match events.
 * Updates batting_stats, bowling_stats, fielding_stats, and stats_cache tables.
 */

class StatsCalculator {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Recalculate and update stats for a match
     * Optimized to use aggregated queries instead of N+1 loops
     * 
     * @param int $matchId Match ID
     * @return int Number of players updated
     */
    public function updateMatchStats($matchId) {
        // 1. Get all appearances to map player_id to appearance_id
        $appearancesSql = "SELECT player_id, appearance_id FROM player_appearances WHERE match_id = ?";
        $stmt = $this->db->prepare($appearancesSql);
        $stmt->execute([$matchId]);
        $appearances = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [player_id => appearance_id]
        
        if (empty($appearances)) return 0;

        // Initialize stats container
        $stats = [];
        foreach ($appearances as $pid => $aid) {
            $stats[$pid] = [
                'batting' => ['runs' => 0, 'balls' => 0, 'fours' => 0, 'sixes' => 0, 'is_out' => 0, 'how_out' => null],
                'bowling' => ['balls' => 0, 'runs' => 0, 'wickets' => 0],
                'fielding' => ['catches' => 0, 'run_outs' => 0, 'stumpings' => 0]
            ];
        }

        // 2. Aggregated Batting Stats
        $battingSql = "SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.striker_id')) as player_id,
            SUM(CASE 
                WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'run' 
                THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.runs')) AS UNSIGNED) 
                WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'extra' 
                AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.extra_type')) = 'no-ball'
                THEN GREATEST(0, CAST(JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.runs')) AS UNSIGNED) - 1)
                ELSE 0 
            END) as runs,
            SUM(CASE 
                WHEN (JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'run' 
                      OR JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'wicket'
                      OR (JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'extra' 
                          AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.extra_type')) != 'wide'))
                THEN 1 ELSE 0 
            END) as balls,
            SUM(CASE 
                WHEN (JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'run' AND CAST(JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.runs')) AS UNSIGNED) = 4)
                OR (JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'extra' AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.extra_type')) = 'no-ball' AND CAST(JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.runs')) AS UNSIGNED) = 5)
                THEN 1 ELSE 0 
            END) as fours,
            SUM(CASE 
                WHEN (JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'run' AND CAST(JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.runs')) AS UNSIGNED) = 6)
                OR (JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'extra' AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.extra_type')) = 'no-ball' AND CAST(JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.runs')) AS UNSIGNED) = 7)
                THEN 1 ELSE 0 
            END) as sixes,
            MAX(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'wicket' THEN 1 ELSE 0 END) as is_out,
            MAX(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'wicket' THEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.dismissal_type')) ELSE NULL END) as how_out
            FROM events 
            WHERE match_id = ? 
            AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.striker_id')) IS NOT NULL
            GROUP BY JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.striker_id'))";

        $stmt = $this->db->prepare($battingSql);
        $stmt->execute([$matchId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pid = (int)$row['player_id'];
            if (isset($stats[$pid])) {
                $stats[$pid]['batting'] = $row;
            }
        }

        // 3. Aggregated Bowling Stats
        $bowlingSql = "SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.bowler_id')) as player_id,
            COUNT(*) as balls,
            SUM(CASE 
                WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'run' THEN CAST(JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.runs')) AS UNSIGNED)
                WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'extra' THEN 1 
                ELSE 0 
            END) as runs,
            SUM(CASE 
                WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'wicket' 
                AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.dismissal_type')) != 'run_out'
                THEN 1 ELSE 0 
            END) as wickets
            FROM events 
            WHERE match_id = ? 
            AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.bowler_id')) IS NOT NULL
            AND (
                JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'run' 
                OR JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'wicket'
                OR (JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'extra' 
                    AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.extra_type')) NOT IN ('wide', 'no-ball'))
            )
            GROUP BY JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.bowler_id'))";

        $stmt = $this->db->prepare($bowlingSql);
        $stmt->execute([$matchId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pid = (int)$row['player_id'];
            if (isset($stats[$pid])) {
                $stats[$pid]['bowling'] = $row;
            }
        }

        // 4. Aggregated Fielding Stats
        $fieldingSql = "SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.fielder_id')) as player_id,
            SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.dismissal_type')) = 'caught' THEN 1 ELSE 0 END) as catches,
            SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.dismissal_type')) = 'run_out' THEN 1 ELSE 0 END) as run_outs,
            SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.dismissal_type')) = 'stumped' THEN 1 ELSE 0 END) as stumpings
            FROM events 
            WHERE match_id = ? 
            AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.type')) = 'wicket'
            AND JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.fielder_id')) IS NOT NULL
            GROUP BY JSON_UNQUOTE(JSON_EXTRACT(payload_json, '$.fielder_id'))";

        $stmt = $this->db->prepare($fieldingSql);
        $stmt->execute([$matchId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pid = (int)$row['player_id'];
            if (isset($stats[$pid])) {
                $stats[$pid]['fielding'] = $row;
            }
        }

        // 5. Batch Updates
        $this->db->beginTransaction();
        try {
            // Prepare statements
            $upsertBatting = $this->db->prepare("INSERT INTO batting_stats 
                (player_id, match_id, runs, balls, fours, sixes, strike_rate, is_out, how_out)
                VALUES (:pid, :mid, :runs, :balls, :fours, :sixes, :sr, :is_out, :how_out)
                ON DUPLICATE KEY UPDATE
                runs=VALUES(runs), balls=VALUES(balls), fours=VALUES(fours), sixes=VALUES(sixes), 
                strike_rate=VALUES(strike_rate), is_out=VALUES(is_out), how_out=VALUES(how_out)");

            $upsertBowling = $this->db->prepare("INSERT INTO bowling_stats 
                (player_id, match_id, overs, balls, runs, wickets, economy)
                VALUES (:pid, :mid, :overs, :balls, :runs, :wickets, :econ)
                ON DUPLICATE KEY UPDATE
                overs=VALUES(overs), balls=VALUES(balls), runs=VALUES(runs), wickets=VALUES(wickets), economy=VALUES(economy)");

            $upsertFielding = $this->db->prepare("INSERT INTO fielding_stats 
                (player_id, match_id, catches, run_outs, stumpings)
                VALUES (:pid, :mid, :catches, :run_outs, :stumpings)
                ON DUPLICATE KEY UPDATE
                catches=VALUES(catches), run_outs=VALUES(run_outs), stumpings=VALUES(stumpings)");

            $upsertCache = $this->db->prepare("INSERT INTO stats_cache 
                (appearance_id, player_id, match_id, runs, wickets, balls_faced, overs_bowled, 
                 strike_rate, economy_rate, fours, sixes, dismissals, runs_conceded, last_event_at, updated_at) 
                VALUES 
                (:aid, :pid, :mid, :runs, :wickets, :balls, :overs, :sr, :econ, :fours, :sixes, :dismissals, :runs_conc, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                runs=VALUES(runs), wickets=VALUES(wickets), balls_faced=VALUES(balls_faced),
                overs_bowled=VALUES(overs_bowled), strike_rate=VALUES(strike_rate),
                economy_rate=VALUES(economy_rate), fours=VALUES(fours), sixes=VALUES(sixes),
                dismissals=VALUES(dismissals), runs_conceded=VALUES(runs_conceded),
                last_event_at=NOW(), updated_at=NOW()");

            foreach ($stats as $pid => $data) {
                $aid = $appearances[$pid];
                
                // Batting
                $b = $data['batting'];
                $sr = $b['balls'] > 0 ? round(($b['runs'] / $b['balls']) * 100, 2) : 0.00;
                if ($b['balls'] > 0 || $b['is_out']) {
                    $upsertBatting->execute([
                        'pid' => $pid, 'mid' => $matchId, 'runs' => $b['runs'], 'balls' => $b['balls'],
                        'fours' => $b['fours'], 'sixes' => $b['sixes'], 'sr' => $sr,
                        'is_out' => $b['is_out'], 'how_out' => $b['how_out']
                    ]);
                }

                // Bowling
                $bw = $data['bowling'];
                $overs = floor($bw['balls'] / 6) + ($bw['balls'] % 6) / 10;
                $econ = $overs > 0 ? round($bw['runs'] / ((floor($overs) * 6 + ($overs - floor($overs)) * 10) / 6), 2) : 0.00;
                if ($bw['balls'] > 0) {
                    $upsertBowling->execute([
                        'pid' => $pid, 'mid' => $matchId, 'overs' => $overs, 'balls' => $bw['balls'],
                        'runs' => $bw['runs'], 'wickets' => $bw['wickets'], 'econ' => $econ
                    ]);
                }

                // Fielding
                $f = $data['fielding'];
                if ($f['catches'] > 0 || $f['run_outs'] > 0 || $f['stumpings'] > 0) {
                    $upsertFielding->execute([
                        'pid' => $pid, 'mid' => $matchId,
                        'catches' => $f['catches'], 'run_outs' => $f['run_outs'], 'stumpings' => $f['stumpings']
                    ]);
                }

                // Cache
                $dismissals = $f['catches'] + $f['stumpings'];
                $upsertCache->execute([
                    'aid' => $aid, 'pid' => $pid, 'mid' => $matchId,
                    'runs' => $b['runs'], 'wickets' => $bw['wickets'], 'balls' => $b['balls'],
                    'overs' => $overs > 0 ? round($bw['balls'] / 6, 2) : 0,
                    'sr' => $sr, 'econ' => $econ, 'fours' => $b['fours'], 'sixes' => $b['sixes'],
                    'dismissals' => $dismissals, 'runs_conc' => $bw['runs']
                ]);
            }
            
            $this->db->commit();
            return count($stats);

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Stats Update Failed: " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Get batting stats for a match
     * 
     * @param int $matchId Match ID
     * @return array Batting stats
     */
    public function getBattingStats(int $matchId): array {
        $sql = "SELECT * FROM batting_stats WHERE match_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$matchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get bowling stats for a match
     * 
     * @param int $matchId Match ID
     * @return array Bowling stats
     */
    public function getBowlingStats(int $matchId): array {
        $sql = "SELECT * FROM bowling_stats WHERE match_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$matchId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
