<?php
/**
 * Match Setup Helper API
 * Endpoints for match setup utilities (previous match copy, guest players)
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$method = $_SERVER['REQUEST_METHOD'];
$path = trim(getApiPath(), '/');
$pathParts = !empty($path) ? explode('/', $path) : [];

$matchModel = new MatchModel();
$db = Database::getInstance()->getConnection();

// GET /api/v1/match-setup/previous/{match_id} - Get previous match player setup
if ($method === 'GET' && count($pathParts) === 2 && $pathParts[0] === 'previous' && is_numeric($pathParts[1])) {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $currentMatchId = (int)$pathParts[1];
        $currentMatch = $matchModel->getById($currentMatchId);
        
        if (!$currentMatch) {
            jsonResponse(['success' => false, 'error' => 'Match not found'], 404);
        }
        
        // Find the most recent completed match with same teams (or in same series)
        $sql = "SELECT m.match_id, m.team1_id, m.team2_id, m.match_date, m.team1_name, m.team2_name
                FROM matches m
                WHERE m.match_id != :current_match_id
                AND m.state IN ('completed', 'live')
                AND (
                    (m.series_id = :series_id AND :series_id IS NOT NULL)
                    OR (
                        (m.team1_id = :team1_id AND m.team2_id = :team2_id)
                        OR (m.team1_id = :team2_id AND m.team2_id = :team1_id)
                    )
                )
                ORDER BY m.match_date DESC, m.match_id DESC
                LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'current_match_id' => $currentMatchId,
            'series_id' => $currentMatch['series_id'],
            'team1_id' => $currentMatch['team1_id'],
            'team2_id' => $currentMatch['team2_id']
        ]);
        
        $previousMatch = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$previousMatch) {
            jsonResponse(['success' => true, 'data' => null, 'message' => 'No previous match found']);
        }
        
        // Fetch player appearances from previous match
        $playersSql = "SELECT pa.player_id, pa.team_id, pa.is_guest, p.name, p.batting_hand, p.bowling_style
                       FROM player_appearances pa
                       INNER JOIN players p ON pa.player_id = p.player_id
                       WHERE pa.match_id = :match_id
                       ORDER BY pa.team_id, p.name";
        
        $playersStmt = $db->prepare($playersSql);
        $playersStmt->execute(['match_id' => $previousMatch['match_id']]);
        $players = $playersStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group players by team
        $team1Players = [];
        $team2Players = [];
        $guestPlayers = [];
        
        foreach ($players as $player) {
            $playerData = [
                'player_id' => (int)$player['player_id'],
                'name' => $player['name'],
                'batting_hand' => $player['batting_hand'],
                'bowling_style' => $player['bowling_style'],
                'is_guest' => (bool)($player['is_guest'] ?? 0)
            ];
            
            if ($player['is_guest'] ?? 0) {
                $guestPlayers[] = $playerData;
            } elseif ($player['team_id'] == $previousMatch['team1_id']) {
                $team1Players[] = $playerData;
            } else {
                $team2Players[] = $playerData;
            }
        }
        
        $responseData = [
            'previous_match_id' => (int)$previousMatch['match_id'],
            'previous_match_date' => $previousMatch['match_date'],
            'previous_match_name' => $previousMatch['team1_name'] . ' vs ' . $previousMatch['team2_name'],
            'team1_players' => $team1Players,
            'team2_players' => $team2Players,
            'guest_players' => $guestPlayers,
            'total_players' => count($players)
        ];
        
        jsonResponse(['success' => true, 'data' => $responseData]);
    } catch (Exception $e) {
        error_log('API Error in match-setup.php (GET previous): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch previous setup: ' . $e->getMessage()], 500);
    }

// POST /api/v1/match-setup/apply/{match_id} - Apply player setup to match
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[0] === 'apply' && is_numeric($pathParts[1])) {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $matchId = (int)$pathParts[1];
        $data = getJsonBody();
        
        $match = $matchModel->getById($matchId);
        if (!$match) {
            jsonResponse(['success' => false, 'error' => 'Match not found'], 404);
        }
        
        // Validate input
        if (!isset($data['players']) || !is_array($data['players'])) {
            jsonResponse(['success' => false, 'error' => 'Players array required'], 400);
        }
        
        // Clear existing player appearances for this match
        $deleteSql = "DELETE FROM player_appearances WHERE match_id = :match_id";
        $deleteStmt = $db->prepare($deleteSql);
        $deleteStmt->execute(['match_id' => $matchId]);
        
        // Insert new player appearances
        $insertSql = "INSERT INTO player_appearances (player_id, match_id, team_id, is_guest, created_at, updated_at)
                      VALUES (:player_id, :match_id, :team_id, :is_guest, NOW(), NOW())";
        $insertStmt = $db->prepare($insertSql);
        
        $insertedCount = 0;
        foreach ($data['players'] as $player) {
            if (!isset($player['player_id']) || !isset($player['team_id'])) {
                continue;
            }
            
            $insertStmt->execute([
                'player_id' => (int)$player['player_id'],
                'match_id' => $matchId,
                'team_id' => (int)$player['team_id'],
                'is_guest' => (int)($player['is_guest'] ?? 0)
            ]);
            $insertedCount++;
        }
        
        jsonResponse([
            'success' => true,
            'message' => "Applied {$insertedCount} players to match",
            'count' => $insertedCount
        ]);
    } catch (Exception $e) {
        error_log('API Error in match-setup.php (POST apply): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to apply setup: ' . $e->getMessage()], 500);
    }

} else {
    jsonResponse(['success' => false, 'error' => 'Not found'], 404);
}
