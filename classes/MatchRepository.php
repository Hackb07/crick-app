<?php
/**
 * MatchRepository
 * 
 * Encapsulates read-only match queries and lightweight APCu caching so that
 * controllers / API endpoints no longer deal with raw SQL or MatchModel
 * implementation details.
 *
 * NOTE: At the moment this repository only implements read operations. Write
 * actions (create, update, delete, state changes) will remain in MatchModel or
 * dedicated services until the full refactor is complete.
 */

class MatchRepository
{
    /** @var PDO */
    private $db;

    /**
     * Cache time-to-live (seconds) for frequently accessed read endpoints.
     * Live scoring pages poll every ~2s, but stale data of <5s is acceptable.
     */
    private const CACHE_TTL = 5;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::getInstance()->getConnection();
    }

    /**
     * Fetch a single match by its primary key.
     * Returns null when not found.
     */
    public function findById(int $matchId): ?array
    {
        $cacheKey = "match:{$matchId}";
        if (function_exists('apcu_fetch')) {
            $cached = apcu_fetch($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }

        $sql = "SELECT m.*, 
                       t1.name  AS team1_name,  t1.short_name AS team1_short_name, t1.logo AS team1_logo,
                       t2.name  AS team2_name,  t2.short_name AS team2_short_name, t2.logo AS team2_logo,
                       s.name   AS series_name,
                       u.username AS created_by_username
                FROM matches m
                LEFT JOIN teams   t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams   t2 ON m.team2_id = t2.team_id
                LEFT JOIN series  s  ON m.series_id = s.series_id
                LEFT JOIN users   u  ON m.created_by = u.user_id
                WHERE m.match_id = :match_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['match_id' => $matchId]);
        $match = $stmt->fetch() ?: null;

        if ($match && function_exists('apcu_store')) {
            apcu_store($cacheKey, $match, self::CACHE_TTL);
        }

        return $match;
    }

    /**
     * Retrieve a list of matches based on optional filters.
     * Mirrors the filtering options of the legacy MatchModel::getAll().
     *
     * @param array $filters
     * @return array<int, array>
     */
    public function findAll(array $filters = []): array
    {
        $sql = "SELECT m.*, 
                       t1.name AS team1_name, t1.short_name AS team1_short_name,
                       t2.name AS team2_name, t2.short_name AS team2_short_name,
                       s.name  AS series_name
                FROM matches m
                LEFT JOIN teams  t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams  t2 ON m.team2_id = t2.team_id
                LEFT JOIN series s  ON m.series_id = s.series_id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['state'])) {
            $sql           .= " AND m.state = :state";
            $params['state'] = $filters['state'];
        }
        if (!empty($filters['series_id'])) {
            $sql                 .= " AND m.series_id = :series_id";
            $params['series_id'] = $filters['series_id'];
        }
        if (!empty($filters['team_id'])) {
            $sql               .= " AND (m.team1_id = :team_id OR m.team2_id = :team_id)";
            $params['team_id'] = $filters['team_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql                    .= " AND m.match_date >= :date_from";
            $params['date_from']    = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql                  .= " AND m.match_date <= :date_to";
            $params['date_to']    = $filters['date_to'];
        }

        $sql .= " ORDER BY m.match_date DESC, m.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
