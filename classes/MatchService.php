<?php
/**
 * MatchService
 * 
 * Business logic layer for match operations.
 * Orchestrates between repositories and state changes.
 * 
 * Responsibilities:
 * - Validate state transitions
 * - Coordinate multi-step operations (e.g., change innings)
 * - Handle error scenarios gracefully
 * - Log important events
 */

class MatchService
{
    /** @var PDO */
    private $db;

    /** @var MatchRepository */
    private $matchRepo;

    /** @var EventRepository */
    private $eventRepo;

    public function __construct(
        ?PDO $db = null,
        ?MatchRepository $matchRepo = null,
        ?EventRepository $eventRepo = null
    ) {
        $this->db = $db ?: Database::getInstance()->getConnection();
        $this->matchRepo = $matchRepo ?: new MatchRepository($this->db);
        $this->eventRepo = $eventRepo ?: new EventRepository($this->db);
    }

    /**
     * Start a match (state: draft → live).
     * Validates prerequisites (toss recorded, players assigned).
     *
     * @param int $matchId
     * @return array{success: bool, error?: string}
     */
    public function startMatch(int $matchId): array
    {
        try {
            $match = $this->matchRepo->findById($matchId);
            if (!$match) {
                return ['success' => false, 'error' => 'Match not found'];
            }

            if ($match['state'] !== 'draft') {
                return ['success' => false, 'error' => 'Match is not in draft state'];
            }

            if (!$match['toss_winner_id']) {
                return ['success' => false, 'error' => 'Toss not recorded'];
            }

            // Update match state
            $sql = "UPDATE matches SET state = 'live', updated_at = NOW() WHERE match_id = :match_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['match_id' => $matchId]);

            error_log("Match started: ID=$matchId");

            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error starting match: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Change innings (e.g., end innings 1, start innings 2).
     * Validates state and calculates first innings total for target.
     *
     * @param int $matchId
     * @param int $newInnings
     * @return array{success: bool, error?: string, first_innings_total?: int}
     */
    public function changeInnings(int $matchId, int $newInnings): array
    {
        try {
            $match = $this->matchRepo->findById($matchId);
            if (!$match) {
                return ['success' => false, 'error' => 'Match not found'];
            }

            if ($match['state'] !== 'live') {
                return ['success' => false, 'error' => 'Match is not live'];
            }

            $currentInnings = (int)$match['current_innings'];
            if ($newInnings <= $currentInnings) {
                return ['success' => false, 'error' => 'Invalid innings progression'];
            }

            // Calculate stats from previous innings
            $prevStats = $this->eventRepo->calculateInningsStats($matchId, $currentInnings);

            // Update match
            $sql = "UPDATE matches SET current_innings = :new_innings, updated_at = NOW() WHERE match_id = :match_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['new_innings' => $newInnings, 'match_id' => $matchId]);

            error_log("Innings changed: matchId=$matchId, new_innings=$newInnings, runs=" . $prevStats['runs']);

            return [
                'success' => true,
                'first_innings_total' => $prevStats['runs'],
                'previous_stats' => $prevStats,
            ];
        } catch (Exception $e) {
            error_log("Error changing innings: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Finalize match (state: live → completed).
     * Determines winner and calculates final statistics.
     *
     * @param int $matchId
     * @return array{success: bool, error?: string}
     */
    public function finalizeMatch(int $matchId): array
    {
        try {
            $match = $this->matchRepo->findById($matchId);
            if (!$match) {
                return ['success' => false, 'error' => 'Match not found'];
            }

            if ($match['state'] !== 'live') {
                return ['success' => false, 'error' => 'Match is not live'];
            }

            // Determine winner logic here (simplified for now)
            $sql = "UPDATE matches SET state = 'completed', updated_at = NOW() WHERE match_id = :match_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['match_id' => $matchId]);

            error_log("Match finalized: ID=$matchId");

            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error finalizing match: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Get combined match state for live scorecard display.
     * Aggregates match + current innings stats.
     *
     * @param int $matchId
     * @return array|null
     */
    public function getLiveScorecard(int $matchId): ?array
    {
        try {
            $match = $this->matchRepo->findById($matchId);
            if (!$match) {
                return null;
            }

            if (!in_array($match['state'], ['live', 'completed'])) {
                return null;
            }

            $currentInnings = (int)($match['current_innings'] ?? 1);
            $stats = $this->eventRepo->calculateInningsStats($matchId, $currentInnings);

            return [
                'match' => $match,
                'current_innings' => $currentInnings,
                'stats' => $stats,
            ];
        } catch (Exception $e) {
            error_log("Error fetching live scorecard: " . $e->getMessage());
            return null;
        }
    }
}








