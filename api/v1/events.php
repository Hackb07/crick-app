<?php
/**
 * Events API
 * 
 * Endpoints: /api/v1/events
 */



// Start output buffering to catch any unexpected output
if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/validation.php';

// Set JSON header immediately
header('Content-Type: application/json');

// Disable HTML error output to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];

// Handle path routing with priority:
// 1. Query parameter 'path' (for web requests: ?path=/matches/9/events)
// 2. PATH_INFO (for clean URLs: /api/v1/events.php/matches/9/events)
// 3. getApiPath() helper (fallback)
$path = '';

// Priority 1: Check query parameter first (most reliable for web requests)
if (getQuery('path')) {
    $path = getQuery('path');
} else {
    // Priority 2: Try PATH_INFO
    if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
        $path = $_SERVER['PATH_INFO'];
    } else {
        // Priority 3: Use helper function
        $path = getApiPath();
    }
}

// Normalize path: remove leading/trailing slashes
$path = trim($path, '/');
$pathParts = !empty($path) ? explode('/', $path) : [];

// Debug logging (remove in production)
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log('Events API Debug: PATH_INFO=' . ($_SERVER['PATH_INFO'] ?? 'NOT SET') . ', REQUEST_URI=' . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . ', GET[path]=' . (getQuery('path') ?? 'NOT SET') . ', Path=' . $path . ', PathParts=' . json_encode($pathParts) . ', Count=' . count($pathParts));
}

$eventModel = new Event();
$eventRepo = new EventRepository();

// POST /api/v1/events - Batch insert events
if ($method === 'POST' && empty($path)) {
    // For web requests, check session first, then JWT
    $user = null;
    if (function_exists('isLoggedIn') && isLoggedIn()) {
        $userModel = new User();
        $user = $userModel->getById(getUserId());
        if (!$user || !in_array($user['role'], ['admin', 'scorer'])) {
            jsonResponse(['success' => false, 'error' => 'Insufficient permissions'], 403);
        }
    } else {
        // No session, return auth error
        jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
    }
    
    // Rate limiting (stricter for write operations)
    if (function_exists('getClientIdentifier')) {
        $identifier = getClientIdentifier($user);
        applyRateLimit($identifier, '/events', 'POST');
    }
    
    $data = getJsonBody();
    
    
    $errors = validateRequired($data, ['match_id', 'events']);
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 400);
    }
    
    $matchId = (int)$data['match_id'];
    $events = $data['events'];
    $clientBaseSeq = (int)($data['client_base_seq'] ?? 0);
    
    // Validate events array
    if (!is_array($events) || empty($events) || count($events) > 10) {
        jsonResponse(['success' => false, 'error' => 'Invalid events array (max 10 per request)'], 400);
    }
    
    try {
        $result = $eventModel->batchInsert($matchId, $events, $clientBaseSeq);

        
        if (!$result['success']) {
            http_response_code(409); // Conflict
        } else {
            // PERFORMANCE OPTIMIZATION: Disabled real-time stats update on every ball
            // Stats are calculated on page load via score-data-loader.php (line 18-20)
            // This dramatically improves response time for ball recording
            // Stats will be accurate when the page is loaded/reloaded
            
            // ENABLED: Real-time stats update is required for leaderboard accuracy
            try {
                require_once __DIR__ . '/../../classes/StatsCalculator.php';
                $statsCalculator = new StatsCalculator();
                $statsCalculator->updateMatchStats($matchId);
            } catch (Exception $e) {
                error_log("Error updating real-time stats: " . $e->getMessage());
            }
        }
        
        // Clear buffer before sending response
        while (ob_get_level()) {
            ob_end_clean();
        }
        jsonResponse($result, $result['success'] ? 200 : 409);
    } catch (Throwable $e) {
        // Clear any output buffers to prevent HTML leakage
        if (ob_get_level()) {
            ob_clean();
        }
        error_log("Events API Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Server Error: ' . $e->getMessage()], 500);
    }
    
// GET /api/v1/events/sync-status - Get sync status
} elseif ($method === 'GET' && ($path === 'sync-status' || getQuery('path') === '/sync-status')) {
    try {
        // Ensure output buffer is clean
        if (ob_get_level()) {
            ob_clean();
        }
        
        // For web requests, check session first, then JWT
        $user = null;
        if (function_exists('isLoggedIn') && isLoggedIn()) {
            try {
                $userModel = new User();
                $user = $userModel->getById(getUserId());
                if (!$user || !in_array($user['role'], ['admin', 'scorer'])) {
                    jsonResponse(['success' => false, 'error' => 'Insufficient permissions'], 403);
                }
            } catch (Exception $e) {
                error_log('Session auth error in sync-status: ' . $e->getMessage());
                // Continue to JWT auth
            }
        }
        
        // If no session user, try JWT
        if (!$user && function_exists('requireRole')) {
            try {
                $user = requireRole(['admin', 'scorer']);
            } catch (Exception $e) {
                error_log('JWT auth error in sync-status: ' . $e->getMessage());
                jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
            }
        }
        
        if (!$user) {
            jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }
        
        $matchId = (int)getQuery('match_id', 0);
        
        if (!$matchId) {
            jsonResponse(['success' => false, 'error' => 'match_id required'], 400);
        }
        
        $result = $eventModel->getSyncStatus($matchId);
        // Clear buffer before sending response
        while (ob_get_level()) {
            ob_end_clean();
        }
        jsonResponse($result);
    } catch (Exception $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('Error in sync-status: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Failed to get sync status: ' . $e->getMessage()], 500);
    } catch (Error $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('Fatal error in sync-status: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()], 500);
    }
    
// GET /api/v1/events/matches/{id}/events - Get match events
} elseif ($method === 'GET' && count($pathParts) === 3 && $pathParts[0] === 'matches' && $pathParts[2] === 'events') {
    try {
        // Ensure output buffer is clean
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Allow public read access for match events (no auth required for viewing)
        $matchId = (int)$pathParts[1];
        $fromSeq = (int)getQuery('from_seq', 0);
        
        if ($matchId <= 0) {
            jsonResponse(['success' => false, 'error' => 'Invalid match ID'], 400);
        }
        
        // Check if EventRepository exists and has findByMatch method
        if (!method_exists($eventRepo, 'findByMatch')) {
            error_log('EventRepository::findByMatch method not found');
            jsonResponse(['success' => false, 'error' => 'EventRepository method not found'], 500);
        }
        
        // Fetch events with error handling
        try {
            $events = $eventRepo->findByMatch($matchId, $fromSeq);
            
            // Ensure we return an array even if empty
            if (!is_array($events)) {
                $events = [];
            }
            
            // Clear buffer before sending response
            while (ob_get_level()) {
                ob_end_clean();
            }
            jsonResponse(['success' => true, 'data' => $events]);
        } catch (PDOException $dbError) {
            error_log('PDO Error in findByMatch: ' . $dbError->getMessage() . ' | Code: ' . $dbError->getCode());
            throw $dbError; // Re-throw to be caught by outer catch
        }
    } catch (PDOException $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('Database error fetching match events: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    } catch (Exception $e) {
        // Clear output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        error_log('Error fetching match events: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Failed to fetch events: ' . $e->getMessage()], 500);
    } catch (Error $e) {
        // Clear output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        error_log('Fatal error fetching match events: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        jsonResponse(['success' => false, 'error' => 'Fatal error: ' . $e->getMessage()], 500);
    }
    
// GET /api/v1/events?match_id={id} - Get match events (root endpoint fallback)
} elseif ($method === 'GET' && empty($path)) {
    try {
        $matchId = (int)getQuery('match_id', 0);
        $fromSeq = (int)getQuery('from_seq', 0);
        $innings = (int)getQuery('innings', 0);
        
        if ($matchId <= 0) {
            jsonResponse(['success' => false, 'error' => 'match_id required'], 400);
        }
        
        $events = $eventRepo->findByMatch($matchId, $fromSeq);
        
        // Filter by innings if provided
        if ($innings > 0) {
            $events = array_filter($events, function($e) use ($innings) {
                // Ensure payload_json is string before decoding
                $payloadJson = is_string($e['payload_json']) ? $e['payload_json'] : json_encode($e['payload_json']);
                $payload = json_decode($payloadJson, true);
                return (isset($payload['innings']) && (int)$payload['innings'] === $innings);
            });
            $events = array_values($events); // Reset indices
        }
        
        // Clear buffer before sending response
        while (ob_get_level()) {
            ob_end_clean();
        }
        jsonResponse(['success' => true, 'events' => $events]);
    } catch (Exception $e) {
        error_log('Error in GET /events: ' . $e->getMessage());
        jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
    }

} else {
    // Debug: Log what we received for troubleshooting
    error_log('Events API: No route matched. Method: ' . $method . ', Path: ' . $path . ', PathParts: ' . json_encode($pathParts) . ', Count: ' . count($pathParts));
    jsonResponse([
        'success' => false, 
        'error' => 'Not found',
        'debug' => [
            'method' => $method,
            'path' => $path,
            'pathParts' => $pathParts,
            'pathPartsCount' => count($pathParts),
            'GET_path' => getQuery('path')
        ]
    ], 404);
}
