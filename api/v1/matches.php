<?php
/**
 * Matches API
 * 
 * Endpoints: /api/v1/matches
 */

// Include error wrapper first
require_once __DIR__ . '/../../api-error-wrapper.php';

// Start output buffering to catch any unexpected output
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/validation.php';
require_once __DIR__ . '/../../classes/MatchRepository.php';


// Set JSON header immediately
header('Content-Type: application/json');


// Enable error reporting for debugging (remove in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Don't display, but log
}

// Wrap in try-catch for error handling
try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Handle path routing with priority:
    // 1. PATH_INFO (for clean URLs: /api/v1/matches.php/9/change-innings)
    // 2. getApiPath() helper (fallback)
    $path = '';
    
    // Priority 1: Try PATH_INFO first
    if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
        $path = $_SERVER['PATH_INFO'];
    } else {
        // Priority 2: Use helper function
        $path = getApiPath();
    }
    
    // Normalize path: remove leading/trailing slashes
    $path = trim($path, '/');
    $pathParts = !empty($path) ? explode('/', $path) : [];
    
    // Debug logging (remove in production)
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log('Matches API Debug: PATH_INFO=' . ($_SERVER['PATH_INFO'] ?? 'NOT SET') . ', REQUEST_URI=' . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . ', Path=' . $path . ', PathParts=' . json_encode($pathParts) . ', Count=' . count($pathParts) . ', Method=' . $method);
    }

    // Rate limiting (getAuthenticatedUser can return null for public endpoints)
    // Only apply rate limiting if function exists (might fail if bootstrap has issues)
    // Skip rate limiting for now to avoid fatal errors - can be re-enabled later
    // if (function_exists('getAuthenticatedUser') && function_exists('getClientIdentifier') && function_exists('applyRateLimit')) {
    //     try {
    //         $user = getAuthenticatedUser();
    //         $identifier = getClientIdentifier($user);
    //         applyRateLimit($identifier, '/matches', $method);
    //     } catch (Exception $rateLimitError) {
    //         // Log but don't fail on rate limit errors
    //         error_log('Rate limit error (non-fatal): ' . $rateLimitError->getMessage());
    //     } catch (Error $rateLimitError) {
    //         // Log but don't fail on rate limit errors
    //         error_log('Rate limit fatal error (non-fatal): ' . $rateLimitError->getMessage());
    //     }
    // }
} catch (Exception $e) {
    error_log('API Error in matches.php (initialization): ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    jsonResponse(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()], 500);
}

$matchModel = new MatchModel();
$stateMachine = new MatchStateMachine();
$matchRepo = new MatchRepository();

// GET /api/v1/matches - List matches
if ($method === 'GET' && empty($path)) {
    try {
        $filters = [
            'state' => getQuery('state'),
            'series_id' => getQuery('series_id'),
            'team_id' => getQuery('team_id'),
            'date_from' => getQuery('date_from'),
            'date_to' => getQuery('date_to')
        ];
        
        $matches = $matchRepo->findAll($filters);
        
        // Include calculated scores for live/completed matches (same as frontend)
        $includeScores = getQuery('include_scores', 'true') !== 'false';
        $matchesWithData = [];
        
        foreach ($matches as $match) {
            $matchData = ['match' => $match];
            
            // Calculate scores for live/completed matches if requested
            if ($includeScores && in_array($match['state'], ['live', 'completed'])) {
                $matchData['score'] = calculateMatchScore($match['match_id']);
            } else {
                $matchData['score'] = null;
            }
            
            $matchesWithData[] = $matchData;
        }
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $matchesWithData]);
    } catch (Exception $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (GET list): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch matches'], 500);
    }
    
// GET /api/v1/matches/{id} - Get match
} elseif ($method === 'GET' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    try {
        $matchId = (int)$pathParts[0];
        $matchData = $matchRepo->findById($matchId);
        
        if (!$matchData) {
            // Clear output buffer
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Match not found'], 404);
        }
        
        // Include calculated score for live/completed matches (same as frontend)
        $includeScores = getQuery('include_scores', 'true') !== 'false';
        $includeDetails = getQuery('include_details', 'false') === 'true';
        
        if ($includeDetails) {
            require_once __DIR__ . '/../../classes/MatchStatsService.php';
            $statsService = new MatchStatsService();
            $matchStats = $statsService->getMatchStats($matchId);
            
            // Add the basic 'score' key for backward compatibility/ease of use
            if ($matchStats) {
                $matchStats['score'] = calculateMatchScore($matchId);
                $responseData = $matchStats;
            } else {
                $responseData = ['match' => $matchData];
            }
        } else {
            $responseData = ['match' => $matchData];
            
            if ($includeScores && in_array($matchData['state'], ['live', 'completed'])) {
                $responseData['score'] = calculateMatchScore($matchId);
            } else {
                $responseData['score'] = null;
            }
        }
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $responseData]);
    } catch (Exception $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (GET by id): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch match'], 500);
    }
    
// POST /api/v1/matches - Create match
} elseif ($method === 'POST' && empty($path)) {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $data = getJsonBody();
        
        $errors = validateRequired($data, ['team1_id', 'team2_id']);
        if (!empty($errors)) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'errors' => $errors], 400);
        }
        
        $data['created_by'] = $user['user_id'];
        $matchId = $matchModel->create($data);
        
        if (!$matchId) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Failed to create match'], 500);
        }
        
        $matchData = $matchModel->getById($matchId);
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $matchData], 201);
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (POST create): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to create match: ' . $e->getMessage()], 500);
    }
    
// PUT /api/v1/matches/{id} - Update match
} elseif ($method === 'PUT' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $matchId = (int)$pathParts[0];
        $data = getJsonBody();
        
        $result = $matchModel->update($matchId, $data);
        
        if (!$result) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Failed to update match'], 500);
        }
        
        $matchData = $matchModel->getById($matchId);
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $matchData]);
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (PUT update): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to update match: ' . $e->getMessage()], 500);
    }
    
// DELETE /api/v1/matches/{id} - Delete match
} elseif ($method === 'DELETE' && count($pathParts) === 1 && is_numeric($pathParts[0])) {
    try {
        $user = requireRole(['admin']);
        
        $matchId = (int)$pathParts[0];
        $result = $matchModel->delete($matchId);
        
        if (!$result) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Failed to delete match'], 500);
        }
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (DELETE): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to delete match: ' . $e->getMessage()], 500);
    }
    
// POST /api/v1/matches/{id}/toss - Record toss
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'toss') {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $matchId = (int)$pathParts[0];
        $data = getJsonBody();
        
        $errors = validateRequired($data, ['toss_winner_id', 'toss_decision']);
        if (!empty($errors)) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'errors' => $errors], 400);
        }
        
        if (!validateEnum($data['toss_decision'], ['bat', 'bowl'])) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Invalid toss decision'], 400);
        }
        
        $result = $stateMachine->recordToss($matchId, $data['toss_winner_id'], $data['toss_decision']);
        
        if (!$result) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Failed to record toss'], 500);
        }
        
        $matchData = $matchModel->getById($matchId);
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $matchData]);
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (POST toss): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to record toss: ' . $e->getMessage()], 500);
    }
    
// POST /api/v1/matches/{id}/start - Start match
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'start') {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $matchId = (int)$pathParts[0];
        $result = $stateMachine->startMatch($matchId);
        
        if (!$result) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Failed to start match'], 500);
        }
        
        $matchData = $matchModel->getById($matchId);
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $matchData]);
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (POST start): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to start match: ' . $e->getMessage()], 500);
    }
    
// POST /api/v1/matches/{id}/finalize - Finalize match
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'finalize') {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $matchId = (int)$pathParts[0];
        $data = getJsonBody();
        $winnerId = isset($data['winner_id']) ? (int)$data['winner_id'] : null;
        
        // Get match to verify winner_id is valid (must be team1_id or team2_id)
        $match = $matchModel->getById($matchId);
        if (!$match) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Match not found'], 404);
        }
        
        // Validate winner_id if provided
        if ($winnerId !== null && $winnerId !== $match['team1_id'] && $winnerId !== $match['team2_id']) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Invalid winner_id'], 400);
        }
        
        // Finalize match with winner_id
        $updateData = ['state' => 'completed'];
        if ($winnerId !== null) {
            $updateData['winner_id'] = $winnerId;
        }
        
        $result = $matchModel->update($matchId, $updateData);
        
        if (!$result) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Failed to finalize match'], 500);
        }
        
        // Trigger immediate stats recomputation for this match
        try {
            require_once __DIR__ . '/../../classes/StatsCalculator.php';
            $statsCalculator = new StatsCalculator();
            $updatedCount = $statsCalculator->updateMatchStats($matchId);
            
            // Mark all events as processed
            $db = Database::getInstance()->getConnection();
            $markProcessedSql = "UPDATE events SET processed_flag = 1 WHERE match_id = :match_id";
            $markStmt = $db->prepare($markProcessedSql);
            $markStmt->execute(['match_id' => $matchId]);
            
            error_log("Match finalized: ID=$matchId - Stats recomputed for $updatedCount appearances");
        } catch (Exception $statsError) {
            error_log("Error recomputing stats: " . $statsError->getMessage());
            // Don't fail finalization if stats recompute fails
        }
        
        // Calculate POTM
        try {
            require_once __DIR__ . '/../../classes/POTM.php';
            $potm = new POTM();
            $potmData = $potm->calculate($matchId);
            if ($potmData && isset($potmData['player_id'])) {
                $potm->saveDecision($matchId, $potmData['player_id'], null, 'Auto-calculated', $user['user_id'] ?? null);
                error_log("POTM calculated for match $matchId: Player ID " . $potmData['player_id']);
            } else {
                error_log("POTM calculation returned no data for match $matchId");
            }
        } catch (Exception $potmError) {
            error_log("Error calculating POTM for match $matchId: " . $potmError->getMessage() . " | Trace: " . $potmError->getTraceAsString());
            // Don't fail finalization if POTM calculation fails
        } catch (Error $potmError) {
            error_log("Fatal error calculating POTM for match $matchId: " . $potmError->getMessage() . " | Trace: " . $potmError->getTraceAsString());
            // Don't fail finalization if POTM calculation fails
        }
        
        // Check if series is complete and calculate POTS
        try {
            $matchData = $matchModel->getById($matchId);
            if (!empty($matchData['series_id'])) {
                // Use findAll instead of getAll
                require_once __DIR__ . '/../../classes/MatchRepository.php';
                $matchRepo = new MatchRepository();
                $seriesMatches = $matchRepo->findAll(['series_id' => $matchData['series_id']]);
                
                if (!empty($seriesMatches)) {
                    $allCompleted = true;
                    
                    foreach ($seriesMatches as $seriesMatch) {
                        if ($seriesMatch['state'] !== 'completed' && $seriesMatch['state'] !== 'abandoned' && $seriesMatch['state'] !== 'cancelled') {
                            $allCompleted = false;
                            break;
                        }
                    }
                    
                    if ($allCompleted && count($seriesMatches) > 0) {
                        require_once __DIR__ . '/../../classes/POTS.php';
                        $pots = new POTS();
                        $rankings = $pots->calculate($matchData['series_id']);
                        if (!empty($rankings)) {
                            $pots->saveRankings($matchData['series_id'], $rankings);
                            error_log("POTS calculated for series " . $matchData['series_id']);
                        }
                    }
                }
            }
        } catch (Exception $potsError) {
            error_log("Error calculating POTS for match $matchId: " . $potsError->getMessage() . " | Trace: " . $potsError->getTraceAsString());
            // Don't fail finalization if POTS calculation fails
        } catch (Error $potsError) {
            error_log("Fatal error calculating POTS for match $matchId: " . $potsError->getMessage() . " | Trace: " . $potsError->getTraceAsString());
            // Don't fail finalization if POTS calculation fails
        }
        
        // Get updated match data
        $matchData = $matchModel->getById($matchId);
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $matchData]);
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('=== FINALIZE MATCH ERROR ===');
        error_log('Match ID: ' . ($matchId ?? 'unknown'));
        error_log('Error Message: ' . $e->getMessage());
        error_log('Error File: ' . $e->getFile() . ':' . $e->getLine());
        error_log('Stack Trace: ' . $e->getTraceAsString());
        error_log('===========================');
        jsonResponse(['success' => false, 'error' => 'Failed to finalize match: ' . $e->getMessage()], 500);
    }
    
// POST /api/v1/matches/{id}/change-innings - Change innings (1 to 2)
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'change-innings') {
    try {
        // Ensure output buffering is active to catch any unexpected output
        if (!ob_get_level()) {
            ob_start();
        }
        
        // Check session first (for admin panel usage)
        $user = null;
        
        // Load session functions if available
        $sessionFile = __DIR__ . '/../../includes/session.php';
        if (file_exists($sessionFile) && !function_exists('isLoggedIn')) {
            require_once $sessionFile;
        }
        
        // Try session-based authentication first
        $authPassed = false;
        if (function_exists('isLoggedIn') && function_exists('getUserId')) {
            if (isLoggedIn()) {
                $authPassed = true; // Session is valid, allow access
                try {
                    // Check if User class exists before instantiating
                    if (class_exists('User')) {
                        $userModel = new User();
                        $userId = getUserId();
                        if ($userId) {
                            $user = $userModel->getById($userId);
                            if ($user && !in_array($user['role'], ['admin', 'scorer'])) {
                                jsonResponse(['success' => false, 'error' => 'Insufficient permissions'], 403);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log('Session auth error: ' . $e->getMessage());
                    // Continue anyway if session is valid
                }
            }
        }
        
        // If no session user, try JWT authentication
        if (!$authPassed && function_exists('getAuthenticatedUser')) {
            try {
                $user = getAuthenticatedUser();
                if ($user) {
                    $authPassed = true;
                    if (!in_array($user['role'], ['admin', 'scorer'])) {
                        jsonResponse(['success' => false, 'error' => 'Insufficient permissions'], 403);
                    }
                }
            } catch (Exception $e) {
                error_log('JWT auth error: ' . $e->getMessage());
            }
        }
        
        // Final authentication check
        if (!$authPassed) {
            jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        // Validate match ID
        $matchId = (int)$pathParts[0];
        if ($matchId <= 0) {
            jsonResponse(['success' => false, 'error' => 'Invalid match ID'], 400);
        }
        
        // Change innings with detailed error handling
        try {
            $result = $stateMachine->changeInnings($matchId);
            
            if (!$result) {
                error_log('changeInnings returned false/null for match_id: ' . $matchId);
                jsonResponse(['success' => false, 'error' => 'changeInnings returned false'], 500);
            }
            
            if (!isset($result['success'])) {
                error_log('changeInnings returned invalid result structure for match_id: ' . $matchId . ', Result: ' . json_encode($result));
                jsonResponse(['success' => false, 'error' => 'Invalid response from changeInnings'], 500);
            }
            
            if (!$result['success']) {
                $errorMsg = $result['error'] ?? 'Failed to change innings';
                error_log('changeInnings failed for match_id: ' . $matchId . ', Error: ' . $errorMsg);
                jsonResponse(['success' => false, 'error' => $errorMsg], 400);
            }
            
            // Get updated match data
            try {
                $matchData = $matchModel->getById($matchId);
                if (!$matchData) {
                    error_log('Match not found after innings change: match_id=' . $matchId);
                    jsonResponse(['success' => false, 'error' => 'Match not found after innings change'], 404);
                }
            } catch (Exception $e) {
                error_log('Error fetching match data after innings change: ' . $e->getMessage());
                // Still return success if innings was changed, even if we can't fetch updated data
                jsonResponse(['success' => true, 'data' => null, 'warning' => 'Innings changed but could not fetch updated match data']);
            }
            
            // Clear any output buffer before sending JSON
            if (ob_get_level()) {
                ob_clean();
            }
            
            jsonResponse(['success' => true, 'data' => $matchData]);
        } catch (PDOException $dbError) {
            error_log('PDO Error in changeInnings: ' . $dbError->getMessage() . ' | Code: ' . $dbError->getCode() . ' | SQL State: ' . $dbError->getCode());
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Database error: ' . $dbError->getMessage()], 500);
        }
    } catch (Exception $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('Error changing innings: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Failed to change innings: ' . $e->getMessage()], 500);
    } catch (Error $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('Fatal error changing innings: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()], 500);
    }
    
// POST /api/v1/matches/{id}/clone - Clone match
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'clone') {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $sourceMatchId = (int)$pathParts[0];
        $sourceMatch = $matchModel->getById($sourceMatchId);
        
        if (!$sourceMatch) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Source match not found'], 404);
        }
        
        $data = getJsonBody();
        $options = $data['options'] ?? [];
        
        // Create new match with cloned data
        $newMatchData = [
            'team1_id' => $options['team1_id'] ?? $sourceMatch['team1_id'],
            'team2_id' => $options['team2_id'] ?? $sourceMatch['team2_id'],
            'series_id' => $options['series_id'] ?? $sourceMatch['series_id'],
            'venue' => $options['venue'] ?? $sourceMatch['venue'],
            'overs_per_innings' => $options['overs_per_innings'] ?? $sourceMatch['overs_per_innings'],
            'match_date' => $options['match_date'] ?? null,
            'created_by' => $user['user_id'],
            'state' => 'draft'
        ];
        
        $newMatchId = $matchModel->create($newMatchData);
        
        if (!$newMatchId) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Failed to clone match'], 500);
        }
        
        // Record clone link
        $db = Database::getInstance()->getConnection();
        $cloneSql = "INSERT INTO clone_links (source_match_id, target_match_id, options_json, timestamp) 
                     VALUES (:source_match_id, :target_match_id, :options_json, NOW())";
        $cloneStmt = $db->prepare($cloneSql);
        $cloneStmt->execute([
            'source_match_id' => $sourceMatchId,
            'target_match_id' => $newMatchId,
            'options_json' => json_encode($options)
        ]);
        
        $newMatchData = $matchModel->getById($newMatchId);
        
        // Clear output buffer before sending JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        jsonResponse(['success' => true, 'data' => $newMatchData], 201);
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (POST clone): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to clone match: ' . $e->getMessage()], 500);
    }
    
// GET /api/v1/matches/{id}/players - Get players for match teams
} elseif ($method === 'GET' && count($pathParts) === 2 && $pathParts[1] === 'players') {
    try {
        $matchId = (int)$pathParts[0];
        $match = $matchModel->getById($matchId);
        
        if (!$match) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Match not found'], 404);
        }
        
        // Get players for both teams that are already selected for this match
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT p.*, pa.team_id, pa.is_guest, pa.is_captain, pa.role_tags 
                FROM player_appearances pa
                JOIN players p ON pa.player_id = p.player_id
                WHERE pa.match_id = :match_id";
        
        $stmt = $db->prepare($sql);
        $stmt->execute(['match_id' => $matchId]);
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $team1Players = [];
        $team2Players = [];
        
        foreach ($players as $player) {
            // CAST is_guest/captain to int for consistency
            $player['is_guest'] = (int)$player['is_guest'];
            $player['is_captain'] = (int)$player['is_captain'];
            
            if ($player['team_id'] == $match['team1_id']) {
                $team1Players[] = $player;
            } elseif ($player['team_id'] == $match['team2_id']) {
                $team2Players[] = $player;
            }
        }
        
        $responseData = [
            'teams' => [
                [
                    'team_id' => $match['team1_id'],
                    'name' => $match['team1_name'],
                    'players' => $team1Players
                ],
                [
                    'team_id' => $match['team2_id'],
                    'name' => $match['team2_name'],
                    'players' => $team2Players
                ]
            ]
        ];
        
        if (ob_get_level()) {
            ob_clean();
        }
        jsonResponse(['success' => true, 'data' => $responseData]);

    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (GET players): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch players: ' . $e->getMessage()], 500);
    }

// POST /api/v1/matches/{id}/players - Add player to match team
} elseif ($method === 'POST' && count($pathParts) === 2 && $pathParts[1] === 'players') {
    try {
        $user = requireRole(['admin', 'scorer']);
        
        $matchId = (int)$pathParts[0];
        $data = getJsonBody();
        
        $errors = validateRequired($data, ['player_id', 'team_id']);
        if (!empty($errors)) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'errors' => $errors], 400);
        }
        
        $match = $matchModel->getById($matchId);
        if (!$match) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Match not found'], 404);
        }
        
        // Validate team_id belongs to this match
        if ($data['team_id'] != $match['team1_id'] && $data['team_id'] != $match['team2_id']) {
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => false, 'error' => 'Invalid team ID for this match'], 400);
        }
        
        // Check if player is already added
        $db = Database::getInstance()->getConnection();
        $checkSql = "SELECT 1 FROM player_appearances WHERE match_id = :match_id AND player_id = :player_id";
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([
            'match_id' => $matchId,
            'player_id' => $data['player_id']
        ]);
        
        if ($checkStmt->fetch()) {
            // Already added, but let's return success to be idempotent
            if (ob_get_level()) {
                ob_clean();
            }
            jsonResponse(['success' => true, 'message' => 'Player already added']);
        }
        
        // Add player to appareances with default values
        $insertSql = "INSERT INTO player_appearances (match_id, team_id, player_id, is_guest, is_captain, role_tags, created_at, updated_at)
                      VALUES (:match_id, :team_id, :player_id, 0, 0, '[]', NOW(), NOW())";
        $insertStmt = $db->prepare($insertSql);
        $result = $insertStmt->execute([
            'match_id' => $matchId,
            'team_id' => $data['team_id'],
            'player_id' => $data['player_id']
        ]);
        
        if (!$result) {
            if (ob_get_level()) {
                ob_clean();
            }
            $errorInfo = $insertStmt->errorInfo();
            error_log('Failed to add player DB Error: ' . json_encode($errorInfo));
            jsonResponse(['success' => false, 'error' => 'Failed to add player'], 500);
        }
        
        if (ob_get_level()) {
            ob_clean();
        }
        jsonResponse(['success' => true, 'message' => 'Player added successfully']);

    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('API Error in matches.php (POST players): ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to add player: ' . $e->getMessage()], 500);
    }
    
    // Debug: Log path info for troubleshooting
    error_log('Matches API: No route matched. Method: ' . $method . ', Path: ' . $path . ', PathParts: ' . json_encode($pathParts));
    jsonResponse([
        'success' => false, 
        'error' => 'Not found',
        'debug' => [
            'method' => $method,
            'path' => $path,
            'pathParts' => $pathParts,
            'pathPartsCount' => count($pathParts)
        ]
    ], 404);
}
