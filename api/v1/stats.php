<?php
/**
 * Statistics API
 * 
 * Endpoints: /api/v1/stats/*
 */

// Include error wrapper first
require_once __DIR__ . '/../../api-error-wrapper.php';

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

// CRITICAL: Prevent caching for cricket scoring API
// Statistics must always be fresh - no cached data allowed
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Enable error reporting for debugging (remove in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Wrap in try-catch for error handling
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = getApiPath(); // Use helper function with PATH_INFO fallback
    $pathParts = explode('/', trim($path, '/'));

    // Rate limiting (getAuthenticatedUser can return null for public endpoints)
    $user = getAuthenticatedUser();
    $identifier = getClientIdentifier($user);
    applyRateLimit($identifier, '/stats', $method);
} catch (Exception $e) {
    error_log('API Error in stats.php (initialization): ' . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
}

$db = Database::getInstance()->getConnection();

// GET /api/v1/stats/leaderboard - Get leaderboard
if ($method === 'GET' && $path === '/leaderboard') {
    try {
        $seriesId = getQuery('series_id');
        $sortBy = getQuery('sort_by', 'runs');
        $order = getQuery('order', 'desc');
        $limit = (int)getQuery('limit', 10);
        
        // Handle empty stats_cache gracefully
        // First check if stats_cache table exists and has data
        try {
            $checkSql = "SELECT COUNT(*) as cnt FROM stats_cache";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute();
            $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $hasData = $checkResult && isset($checkResult['cnt']) && $checkResult['cnt'] > 0;
        } catch (PDOException $e) {
            // If table doesn't exist or error, return empty array
            $hasData = false;
        }
        
        if (!$hasData) {
            // Return empty leaderboard if no stats data exists
            jsonResponse(['success' => true, 'data' => []]);
        }
        
        $sql = "SELECT sc.player_id, p.name as player_name, 
                SUM(sc.runs) as total_runs,
                SUM(sc.wickets) as total_wickets,
                SUM(sc.balls_faced) as total_balls_faced,
                SUM(sc.overs_bowled) as total_overs_bowled,
                SUM(sc.fours) as total_fours,
                SUM(sc.sixes) as total_sixes,
                SUM(sc.dismissals) as total_dismissals,
                SUM(sc.runs_conceded) as total_runs_conceded,
                AVG(sc.strike_rate) as avg_strike_rate,
                AVG(sc.economy_rate) as avg_economy_rate
                FROM stats_cache sc
                INNER JOIN players p ON sc.player_id = p.player_id";
        
        $params = [];
        
        if ($seriesId) {
            $sql .= " INNER JOIN matches m ON sc.match_id = m.match_id WHERE m.series_id = :series_id";
            $params['series_id'] = $seriesId;
        } else {
            $sql .= " WHERE 1=1";
        }
        
        $sql .= " GROUP BY sc.player_id, p.name";
        
        // Validate sort field
        $allowedSorts = ['runs', 'wickets', 'strike_rate', 'economy_rate', 'sixes', 'boundaries'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'runs';
        }
        
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        // Handle special sorting cases
        if ($sortBy === 'boundaries') {
            // Boundaries = fours + sixes
            $sql .= " ORDER BY (SUM(sc.fours) + SUM(sc.sixes)) $order LIMIT :limit";
        } else {
            $sql .= " ORDER BY total_$sortBy $order LIMIT :limit";
        }
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . print_r($db->errorInfo(), true));
        }
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $leaderboard = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $leaderboard]);
    } catch (PDOException $e) {
        error_log('API Error in stats.php (leaderboard PDO): ' . $e->getMessage());
        error_log('PDO Error Info: ' . print_r($db->errorInfo(), true));
        jsonResponse(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    } catch (Exception $e) {
        error_log('API Error in stats.php (leaderboard): ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch leaderboard: ' . $e->getMessage()], 500);
    }
    
// GET /api/v1/stats/player/{id} - Get player statistics
} elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[0] === 'player' && is_numeric($pathParts[1])) {
    try {
        $playerId = (int)$pathParts[1];
        
        $sql = "SELECT sc.*, m.match_date, m.venue, 
                t1.name as team1_name, t2.name as team2_name
                FROM stats_cache sc
                INNER JOIN matches m ON sc.match_id = m.match_id
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                WHERE sc.player_id = :player_id
                ORDER BY m.match_date DESC";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . print_r($db->errorInfo(), true));
        }
        $stmt->execute(['player_id' => $playerId]);
        $stats = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        error_log('API Error in stats.php (player): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch player stats'], 500);
    }
    
// GET /api/v1/stats/match/{id} - Get match statistics
} elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[0] === 'match' && is_numeric($pathParts[1])) {
    try {
        $matchId = (int)$pathParts[1];
        
        $sql = "SELECT sc.*, p.name as player_name, pa.team_id, t.name as team_name
                FROM stats_cache sc
                INNER JOIN players p ON sc.player_id = p.player_id
                INNER JOIN player_appearances pa ON sc.appearance_id = pa.appearance_id
                LEFT JOIN teams t ON pa.team_id = t.team_id
                WHERE sc.match_id = :match_id
                ORDER BY sc.runs DESC, sc.wickets DESC";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . print_r($db->errorInfo(), true));
        }
        $stmt->execute(['match_id' => $matchId]);
        $stats = $stmt->fetchAll();
        
        jsonResponse(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        error_log('API Error in stats.php (match): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch match stats'], 500);
    }
    
// GET /api/v1/stats/series/{id} - Get series statistics
} elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[0] === 'series' && is_numeric($pathParts[1])) {
    try {
        $seriesId = (int)$pathParts[1];
        
        $pots = new POTS();
        $rankings = $pots->getRankings($seriesId);
        
        jsonResponse(['success' => true, 'data' => $rankings ?: []]);
    } catch (Exception $e) {
        error_log('API Error in stats.php (series): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch series stats'], 500);
    }
    
} else {
    jsonResponse(['success' => false, 'error' => 'Not found'], 404);
}

