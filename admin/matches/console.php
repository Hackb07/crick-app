<?php
/**
 * Match Admin Console
 * Unified interface for managing a match through its lifecycle.
 * 
 * Design: Standard Admin Layout with Mobile-First Elements
 * Focus: Pure Logic, Easy Access
 * 
 * @package CricApp
 * @subpackage Admin\Matches
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../classes/Controllers/MatchConsoleController.php';

// Disable caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

const DEFAULT_MATCH_ID = 0;
const ALLOWED_ACTIONS = ['update_squad', 'record_toss', 'start_match'];
const DEFAULT_ERROR_MESSAGE = 'Failed to process match console request';
const SUCCESS_MESSAGE_DEFAULT = 'Changes saved successfully';

requireLogin();

$matchId = (int)getQuery('id', DEFAULT_MATCH_ID);
if ($matchId <= 0) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

try {
    $controller = new MatchConsoleController($matchId);
} catch (Exception $e) {
    http_response_code(500);
    include __DIR__ . '/../../includes/error-pages/500.php';
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = getPost('action');
    $teamId = getPost('team_id');
    
    if (!in_array($action, ALLOWED_ACTIONS, true)) {
        http_response_code(400);
        die('Invalid action');
    }
    
    try {
        $result = $controller->handleRequest();
        
        if (isset($result['success']) && $result['success']) {
            $message = $result['message'] ?? SUCCESS_MESSAGE_DEFAULT;
            $redirectUrl = "console.php?id=$matchId&success=" . urlencode($message);
            
            // Logic for smart redirection
            if ($action === 'update_squad' && isset($teamId)) {
                $redirectUrl .= "&team_id=$teamId"; // Keep context
            } elseif ($action === 'start_match') {
                // Auto-redirect to scorer on start
                header("Location: " . adminUrl("matches/scorer.php?id=$matchId"));
                exit;
            }
            
            header("Location: $redirectUrl");
            exit;
        }
        
        $error = $result['error'] ?? DEFAULT_ERROR_MESSAGE;
    } catch (Exception $e) {
        $error = 'An unexpected error occurred. Please try again.';
    }
}

// Get View Data
try {
    $data = $controller->getViewData();
    $match = $data['match'];
    $isLive = $match['state'] === 'live';
    $isCompleted = $match['state'] === 'completed';
    $isLocked = $isLive || $isCompleted;
} catch (Exception $e) {
    http_response_code(500);
    include __DIR__ . '/../../includes/error-pages/500.php';
    exit;
}

// Render
renderAdminLayout('Match Console', 'matches/console', [
    'match' => $match,
    'data' => $data,
    'matchId' => $matchId,
    'isLocked' => $isLocked,
    'isLive' => $isLive,
    'isCompleted' => $isCompleted,
    'error' => $error ?? null
], [
    'activeMenu' => 'matches',
    'headerActions' => [
        [
            'url' => adminUrl('matches/'),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);
