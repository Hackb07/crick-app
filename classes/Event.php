<?php
/**
 * Event Model
 * 
 * Handles match events (runs, wickets, extras, etc.)
 */

class Event {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Batch insert events
     * 
     * @param int $matchId Match ID
     * @param array $events Array of event data
     * @param int $clientBaseSeq Client's last known server sequence
     * @return array ['success' => bool, 'server_seq' => int, 'inserted_count' => int, 'conflicts' => array]
     */
    public function batchInsert($matchId, $events, $clientBaseSeq) {
        // Get current server sequence
        $matchModel = new MatchModel();
        $match = $matchModel->getById($matchId);
        
        if (!$match) {
            return ['success' => false, 'error' => 'Match not found'];
        }
        
        $currentServerSeq = (int)($match['last_seq'] ?? 0);
        
        // Prepare SQL for getting/creating appearance_id
        $getAppearanceSql = "SELECT appearance_id FROM player_appearances 
                            WHERE player_id = :player_id AND match_id = :match_id AND team_id = :team_id 
                            LIMIT 1";
        $getAppearanceStmt = $this->db->prepare($getAppearanceSql);
        
        $createAppearanceSql = "INSERT INTO player_appearances (player_id, match_id, team_id, created_at, updated_at) 
                               VALUES (:player_id, :match_id, :team_id, NOW(), NOW())
                               ON DUPLICATE KEY UPDATE updated_at = NOW()";
        $createAppearanceStmt = $this->db->prepare($createAppearanceSql);
        
        // Insert events
        $sql = "INSERT INTO events (event_uuid, match_id, appearance_id, fielder_id, client_id, client_ts, 
                client_base_seq, assigned_server_seq, ball_index, payload_json, created_at, processed_flag) 
                VALUES (:event_uuid, :match_id, :appearance_id, :fielder_id, :client_id, :client_ts, 
                :client_base_seq, :assigned_server_seq, :ball_index, :payload_json, NOW(), 0)";
        
        $stmt = $this->db->prepare($sql);
        $insertedCount = 0;
        $newServerSeq = $currentServerSeq;
        
        // Helper function to get appearance_id for a player
        $getAppearanceId = function($playerId, $teamId) use ($matchId, $getAppearanceStmt, $createAppearanceStmt) {
            if (!$playerId || !$teamId) {
                return null;
            }
            
            // Try to get existing appearance
            $getAppearanceStmt->execute([
                'player_id' => $playerId,
                'match_id' => $matchId,
                'team_id' => $teamId
            ]);
            $appearance = $getAppearanceStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($appearance) {
                return (int)$appearance['appearance_id'];
            }
            
            // Create new appearance if doesn't exist
            try {
                $createAppearanceStmt->execute([
                    'player_id' => $playerId,
                    'match_id' => $matchId,
                    'team_id' => $teamId
                ]);
                
                // Get the newly created appearance_id
                $getAppearanceStmt->execute([
                    'player_id' => $playerId,
                    'match_id' => $matchId,
                    'team_id' => $teamId
                ]);
                $appearance = $getAppearanceStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($appearance) {
                    return (int)$appearance['appearance_id'];
                }
            } catch (Exception $e) {
                error_log("Error creating player appearance: " . $e->getMessage());
                return null;
            }
            
            return null;
        };
        
        $commentaryModel = new Commentary();
        
        foreach ($events as $event) {
            $newServerSeq++;
            $payload = $event['payload_json'] ?? [];
            $appearanceId = $event['appearance_id'] ?? null;
            $fielderId = $payload['fielder_id'] ?? null;
            
            // If appearance_id is not provided, try to resolve it from payload
            if (!$appearanceId) {
                // Determine which player this event is for based on event type
                $playerId = null;
                $teamId = null;
                
                // Use helper function if available, otherwise use simple logic
                $innings = (int)($payload['innings'] ?? $match['current_innings'] ?? 1);
                $battingTeamId = null;
                $bowlingTeamId = null;
                
                if (function_exists('getBattingTeam')) {
                    $battingTeamId = getBattingTeam($match, $innings);
                    $bowlingTeamId = ($battingTeamId == $match['team1_id']) ? $match['team2_id'] : $match['team1_id'];
                } else {
                    // Fallback logic: innings 1 = team1 bats, innings 2 = team2 bats
                    if ($innings == 1) {
                        $battingTeamId = (int)($match['team1_id'] ?? 0);
                        $bowlingTeamId = (int)($match['team2_id'] ?? 0);
                    } else {
                        $battingTeamId = (int)($match['team2_id'] ?? 0);
                        $bowlingTeamId = (int)($match['team1_id'] ?? 0);
                    }
                }
                
                if (isset($payload['striker_id'])) {
                    // For runs, wickets, extras - use striker_id (batsman from batting team)
                    $playerId = (int)$payload['striker_id'];
                    $teamId = $battingTeamId;
                } elseif (isset($payload['bowler_id'])) {
                    // For bowling events - use bowler_id (bowler from bowling team)
                    $playerId = (int)$payload['bowler_id'];
                    $teamId = $bowlingTeamId;
                } elseif (isset($payload['new_batsman_id'])) {
                    // For wicket new batsman - use new_batsman_id (batsman from batting team)
                    $playerId = (int)$payload['new_batsman_id'];
                    $teamId = $battingTeamId;
                } elseif (isset($payload['replacement_player_id'])) {
                    // For retired hurt replacement - use replacement_player_id (batsman from batting team)
                    $playerId = (int)$payload['replacement_player_id'];
                    $teamId = $battingTeamId;
                }
                
                // Get or create appearance_id
                if ($playerId && $teamId) {
                    $appearanceId = $getAppearanceId($playerId, $teamId);
                }
            }
            
            $result = $stmt->execute([
                'event_uuid' => $event['event_uuid'],
                'match_id' => $matchId,
                'appearance_id' => $appearanceId,
                'fielder_id' => $fielderId,
                'client_id' => $event['client_id'] ?? '',
                'client_ts' => $event['client_ts'] ?? now(),
                'client_base_seq' => $clientBaseSeq,
                'assigned_server_seq' => $newServerSeq,
                'ball_index' => $event['ball_index'] ?? 0,
                'payload_json' => json_encode($payload)
            ]);
            
            if ($result) {
                $insertedCount++;
                
                // Generate commentary
                try {
                    $eventId = $this->db->lastInsertId();
                    if ($eventId) {
                        $commentaryModel->generateAndSave([
                            'match_id' => $matchId,
                            'event_id' => $eventId
                        ], $payload);
                    }
                } catch (Exception $e) {
                    error_log("Error generating commentary: " . $e->getMessage());
                }
            } else {
                error_log("Failed to insert event: " . json_encode($stmt->errorInfo()));
            }
        }
        
        // Update match last_seq
        if ($insertedCount > 0) {
            $matchModel->updateLastSeq($matchId, $newServerSeq);
        }
        
        return [
            'success' => true,
            'server_seq' => $newServerSeq,
            'inserted_count' => $insertedCount,
            'conflicts' => []
        ];
    }
    
    /**
     * Get events for a match
     * 
     * @param int $matchId Match ID
     * @param int $fromSeq Starting sequence (optional)
     * @return array List of events
     */
    public function getByMatch($matchId, $fromSeq = 0) {
        $sql = "SELECT e.*, pa.player_id, pa.team_id, p.name as player_name
                FROM events e
                LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
                LEFT JOIN players p ON pa.player_id = p.player_id
                WHERE e.match_id = :match_id AND e.assigned_server_seq > :from_seq
                ORDER BY e.assigned_server_seq ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId, 'from_seq' => $fromSeq]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get events after a specific sequence
     * 
     * @param int $matchId Match ID
     * @param int $seq Sequence number
     * @return array List of events
     */
    public function getEventsAfterSeq($matchId, $seq) {
        $sql = "SELECT e.*, pa.player_id, pa.team_id, p.name as player_name
                FROM events e
                LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
                LEFT JOIN players p ON pa.player_id = p.player_id
                WHERE e.match_id = :match_id AND e.assigned_server_seq > :seq
                ORDER BY e.assigned_server_seq ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId, 'seq' => $seq]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sync status (latest server_seq)
     * 
     * @param int $matchId Match ID
     * @return array Sync status
     */
    public function getSyncStatus($matchId) {
        $matchModel = new MatchModel();
        $match = $matchModel->getById($matchId);
        
        if (!$match) {
            return ['success' => false, 'error' => 'Match not found'];
        }
        
        return [
            'success' => true,
            'match_id' => $matchId,
            'server_seq' => (int)($match['last_seq'] ?? 0)
        ];
    }
}

