<?php
/**
 * Commentary Class
 * 
 * Handles generation and retrieval of match commentary
 */

class Commentary {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Add a commentary entry
     */
    public function add($matchId, $eventId, $innings, $text) {
        $sql = "INSERT INTO commentary (match_id, event_id, innings, commentary_text, created_at) 
                VALUES (:match_id, :event_id, :innings, :text, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'match_id' => $matchId,
            'event_id' => $eventId,
            'innings' => $innings,
            'text' => $text
        ]);
    }
    
    /**
     * Get commentary for a match
     */
    public function getByMatch($matchId, $limit = 50) {
        $sql = "SELECT c.*, e.ball_index, e.payload_json 
                FROM commentary c 
                JOIN events e ON c.event_id = e.event_id 
                WHERE c.match_id = :match_id 
                ORDER BY c.created_at DESC, c.event_id DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':match_id', $matchId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate and save commentary for an event
     */
    public function generateAndSave($event, $payload) {
        $matchId = $event['match_id'];
        $eventId = $event['event_id']; // We need the ID of the inserted event
        $innings = $payload['innings'] ?? 1;
        
        // Fetch player names
        $playerIds = [];
        if (isset($payload['striker_id'])) $playerIds[] = $payload['striker_id'];
        if (isset($payload['bowler_id'])) $playerIds[] = $payload['bowler_id'];
        if (isset($payload['fielder_id'])) $playerIds[] = $payload['fielder_id'];
        if (isset($payload['non_striker_id'])) $playerIds[] = $payload['non_striker_id'];
        
        $players = $this->getPlayerNames($playerIds);
        
        $strikerName = $players[$payload['striker_id'] ?? 0] ?? 'Batsman';
        $bowlerName = $players[$payload['bowler_id'] ?? 0] ?? 'Bowler';
        $fielderName = $players[$payload['fielder_id'] ?? 0] ?? 'Fielder';
        
        $text = '';
        $type = $payload['type'] ?? 'unknown';
        $runs = $payload['runs'] ?? 0;
        
        if ($type === 'run') {
            if ($runs == 0) {
                $text = "$bowlerName to $strikerName, no run.";
            } elseif ($runs == 4) {
                $text = "FOUR! $strikerName finds the gap nicely off $bowlerName.";
            } elseif ($runs == 6) {
                $text = "SIX! $strikerName smashes $bowlerName over the boundary!";
            } elseif ($runs == 1) {
                $text = "$bowlerName to $strikerName, 1 run.";
            } else {
                $text = "$bowlerName to $strikerName, $runs runs.";
            }
        } elseif ($type === 'wicket') {
            $dismissal = $payload['dismissal_type'] ?? 'out';
            
            if ($dismissal === 'bowled') {
                $text = "OUT! $strikerName b $bowlerName";
            } elseif ($dismissal === 'caught') {
                $text = "OUT! $strikerName c $fielderName b $bowlerName";
            } elseif ($dismissal === 'lbw') {
                $text = "OUT! $strikerName lbw b $bowlerName";
            } elseif ($dismissal === 'stumped') {
                $text = "OUT! $strikerName st $fielderName b $bowlerName";
            } elseif ($dismissal === 'run out') {
                $text = "OUT! $strikerName run out ($fielderName)";
            } else {
                $text = "OUT! $strikerName $dismissal b $bowlerName";
            }
        } elseif ($type === 'extra') {
            $extraType = $payload['extra_type'] ?? 'extra';
            $text = ucfirst($extraType) . " ball from $bowlerName.";
            if ($runs > 0) {
                $text .= " $runs runs added.";
            }
        }
        
        if (!empty($text)) {
            $this->add($matchId, $eventId, $innings, $text);
        }
    }
    
    private function getPlayerNames($playerIds) {
        if (empty($playerIds)) return [];
        
        $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
        $sql = "SELECT player_id, name FROM players WHERE player_id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($playerIds));
        
        $players = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $players[$row['player_id']] = $row['name'];
        }
        return $players;
    }
}
