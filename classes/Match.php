<?php
/**
 * Match Model
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../includes/utils.php';

class MatchModel extends DatabaseModel {
    /**
     * Get match by ID with full details
     */
    public function getById($matchId) {
        $sql = "SELECT m.*, 
                t1.name as team1_name, t2.name as team2_name,
                tw.name as toss_winner_name,
                s.name as series_name,
                u.username as created_by_username
                FROM matches m
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                LEFT JOIN teams tw ON m.toss_winner_id = tw.team_id
                LEFT JOIN series s ON m.series_id = s.series_id
                LEFT JOIN users u ON m.created_by = u.user_id
                WHERE m.match_id = :match_id";
        
        return $this->fetchOne($sql, ['match_id' => $matchId]);
    }

    /**
     * Get all matches with filters
     */
    public function getAll($filters = []) {
        $sql = "SELECT m.*, 
                t1.name as team1_name, t2.name as team2_name,
                s.name as series_name
                FROM matches m
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                LEFT JOIN series s ON m.series_id = s.series_id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['state'])) {
            $sql .= " AND m.state = :state";
            $params['state'] = $filters['state'];
        }
        
        if (isset($filters['series_id'])) {
            $sql .= " AND m.series_id = :series_id";
            $params['series_id'] = $filters['series_id'];
        }
        
        if (isset($filters['team_id'])) {
            $sql .= " AND (m.team1_id = :team_id OR m.team2_id = :team_id)";
            $params['team_id'] = $filters['team_id'];
        }
        
        $sql .= " ORDER BY m.match_date DESC, m.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }
        
        return $this->fetchAll($sql, $params);
    }

    /**
     * Create new match
     */
    public function create($data) {
        $sql = "INSERT INTO matches (series_id, team1_id, team2_id, match_date, venue, 
                overs_per_innings, state, created_by) 
                VALUES (:series_id, :team1_id, :team2_id, :match_date, :venue, 
                :overs_per_innings, :state, :created_by)";
        
        $params = [
            'series_id' => $data['series_id'] ?? null,
            'team1_id' => $data['team1_id'],
            'team2_id' => $data['team2_id'],
            'match_date' => $data['match_date'] ?? date('Y-m-d H:i:s'),
            'venue' => $data['venue'] ?? null,
            'overs_per_innings' => $data['overs_per_innings'] ?? 20.0,
            'state' => $data['state'] ?? 'draft',
            'created_by' => $data['created_by'] ?? null
        ];
        
        return $this->execute($sql, $params);
    }

    /**
     * Update match
     */
    public function update($matchId, $data) {
        $fields = [];
        $params = ['match_id' => $matchId];
        
        $allowedFields = ['state', 'toss_winner_id', 'toss_decision', 'current_innings', 
                         'auto_start_innings2', 'overs_per_innings', 'match_date', 'venue'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No valid fields to update'];
        }
        
        $sql = "UPDATE matches SET " . implode(', ', $fields) . " WHERE match_id = :match_id";
        return $this->execute($sql, $params);
    }

    /**
     * Get last sequence number for match
     */
    public function getLastSeq($matchId) {
        $sql = "SELECT last_seq FROM matches WHERE match_id = :match_id";
        $result = $this->fetchOne($sql, ['match_id' => $matchId]);
        return $result ? (int)$result['last_seq'] : 0;
    }

    /**
     * Increment and get next sequence number
     */
    public function getNextSeq($matchId) {
        $sql = "UPDATE matches SET last_seq = last_seq + 1 WHERE match_id = :match_id";
        $this->execute($sql, ['match_id' => $matchId]);
        return $this->getLastSeq($matchId);
    }

    /**
     * Get matches by state (for public portal)
     */
    public function getLiveMatches() {
        return $this->getAll(['state' => 'live', 'limit' => 20]);
    }

    public function getRecentMatches($limit = 10) {
        $sql = "SELECT m.*, 
                t1.name as team1_name, t2.name as team2_name,
                s.name as series_name
                FROM matches m
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                LEFT JOIN series s ON m.series_id = s.series_id
                WHERE m.state = 'completed'
                ORDER BY m.match_date DESC
                LIMIT :limit";
        return $this->fetchAll($sql, ['limit' => $limit]);
    }

    public function getScheduledMatches() {
        return $this->getAll(['state' => 'scheduled', 'limit' => 20]);
    }
}

