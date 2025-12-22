<?php
/**
 * StatsRepository
 * 
 * Handles general database statistics and metrics.
 * Provides a centralized repository for system-wide data counts.
 */
class StatsRepository {
    private $db;

    public function __construct(?PDO $db = null) {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    /**
     * Get count for a specific table with optional filters
     * 
     * @param string $table Table name
     * @param array $where Filter conditions
     * @return int
     */
    public function getCount(string $table, array $where = []): int {
        $sql = "SELECT COUNT(*) FROM `$table`";
        if (!empty($where)) {
            $conditions = [];
            foreach (array_keys($where) as $key) {
                $conditions[] = "`$key` = :$key";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($where);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get comprehensive database statistics
     * 
     * @return array
     */
    public function getSystemStats(): array {
        return [
            'events' => $this->getCount('events'),
            'commentary' => $this->getCount('commentary'),
            'batting_stats' => $this->getCount('batting_stats'),
            'bowling_stats' => $this->getCount('bowling_stats'),
            'fielding_stats' => $this->getCount('fielding_stats'),
            'player_appearances' => $this->getCount('player_appearances'),
            'matches_live' => $this->getCount('matches', ['state' => 'live']),
            'matches_completed' => $this->getCount('matches', ['state' => 'completed']),
            'matches_scheduled' => $this->getCount('matches', ['state' => 'scheduled']),
            'total_matches' => $this->getCount('matches'),
            'total_players' => $this->getCount('players'),
            'total_teams' => $this->getCount('teams'),
        ];
    }
}
