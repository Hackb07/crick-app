<?php
/**
 * EventRepository
 * 
 * Encapsulates event-related queries for live scoring.
 * Provides efficient retrieval of events by match with eager-loaded related data.
 */

class EventRepository
{
    /** @var PDO */
    private $db;

    private const CACHE_TTL_EVENTS = 3; // Short TTL for live events

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance()->getConnection();
    }

    /**
     * Get all events for a match, optionally from a specific sequence.
     * Returns events eager-loaded with player data to avoid N+1.
     *
     * @param int $matchId
     * @param int $fromSeq Start from this sequence number (for polling)
     * @return array<int, array>
     */
    public function findByMatch(int $matchId, int $fromSeq = 0): array
    {
        $sql = "SELECT e.*,
                       p.name      AS player_name,
                       pa.team_id  AS appearance_team_id
                FROM events e
                LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
                LEFT JOIN players p ON pa.player_id = p.player_id
                WHERE e.match_id = :match_id";

        $params = ['match_id' => $matchId];

        if ($fromSeq > 0) {
            $sql            .= " AND e.assigned_server_seq > :from_seq";
            $params['from_seq'] = $fromSeq;
        }

        $sql .= " ORDER BY e.assigned_server_seq ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get the last sequence number for a match (used for sync).
     *
     * @param int $matchId
     * @return int|0
     */
    public function getLastSequence(int $matchId): int
    {
        $sql = "SELECT MAX(assigned_server_seq) as max_seq FROM events WHERE match_id = :match_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId]);
        $result = $stmt->fetch();

        return (int)($result['max_seq'] ?? 0);
    }

    /**
     * Get count of events in current innings (for over/ball tracking).
     *
     * @param int $matchId
     * @param int $innings
     * @return int
     */
    public function countByInnings(int $matchId, int $innings): int
    {
        $sql = "SELECT COUNT(*) as cnt FROM events 
                WHERE match_id = :match_id AND JSON_EXTRACT(payload_json, '$.innings') = :innings";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId, 'innings' => $innings]);
        $result = $stmt->fetch();

        return (int)($result['cnt'] ?? 0);
    }

    /**
     * Calculate live match statistics (runs, wickets, overs) efficiently.
     * Called only when needed (cached on client-side for 5s).
     *
     * @param int $matchId
     * @param int $innings
     * @return array{runs: int, wickets: int, balls: int, overs_complete: int, balls_in_over: int}
     */
    public function calculateInningsStats(int $matchId, int $innings): array
    {
        $sql = "SELECT 
                    SUM(CASE 
                        WHEN JSON_EXTRACT(payload_json, '$.type') = 'run' 
                        THEN CAST(JSON_EXTRACT(payload_json, '$.runs') AS UNSIGNED) 
                        WHEN JSON_EXTRACT(payload_json, '$.type') = 'extra' 
                        THEN CAST(JSON_EXTRACT(payload_json, '$.runs') AS UNSIGNED)
                        ELSE 0 END) as total_runs,
                    SUM(CASE WHEN JSON_EXTRACT(payload_json, '$.type') = 'wicket' THEN 1 ELSE 0 END) as total_wickets,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(payload_json, '$.type') = 'run' THEN 1
                        WHEN JSON_EXTRACT(payload_json, '$.type') = 'wicket' THEN 1
                        WHEN JSON_EXTRACT(payload_json, '$.type') = 'extra' 
                             AND JSON_EXTRACT(payload_json, '$.extra_type') NOT IN ('wide', 'no-ball') THEN 1
                        ELSE 0 END) as total_balls
                FROM events 
                WHERE match_id = :match_id 
                AND JSON_EXTRACT(payload_json, '$.innings') = :innings";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId, 'innings' => $innings]);
        $result = $stmt->fetch();

        $totalRuns    = (int)($result['total_runs'] ?? 0);
        $totalWickets = (int)($result['total_wickets'] ?? 0);
        $totalBalls   = (int)($result['total_balls'] ?? 0);

        $oversComplete = intdiv($totalBalls, 6);
        $ballsInOver   = $totalBalls % 6;

        return [
            'runs'            => $totalRuns,
            'wickets'         => $totalWickets,
            'balls'           => $totalBalls,
            'overs_complete'  => $oversComplete,
            'balls_in_over'   => $ballsInOver,
        ];
    }
}

