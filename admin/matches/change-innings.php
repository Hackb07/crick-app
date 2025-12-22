<?php
/**
 * Change Innings - Simple Form-Based Handler
 * More reliable than AJAX for changing innings
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

// Get user ID from session
$userId = getUserId();
if (!$userId) {
    header('Location: ' . adminUrl('login.php'));
    exit;
}

// Get user details
$userModel = new User();
$user = $userModel->getById($userId);

// Only allow admin and scorer roles
if (!$user || !in_array($user['role'], ['admin', 'scorer'])) {
    header('Location: ' . adminUrl('login.php'));
    exit;
}

// Get match ID from POST or GET (supports both form and direct link)
$matchId = (int)(getPost('match_id') ?? getQuery('match_id', 0));
$currentInnings = (int)(getPost('current_innings') ?? getQuery('current_innings', 1));

// Debug logging
error_log('Change Innings: Request received - match_id=' . $matchId . ', current_innings=' . $currentInnings . ', POST=' . json_encode($_POST) . ', GET=' . json_encode($_GET));

if (!$matchId) {
    error_log('Change Innings: Invalid match ID - match_id=' . $matchId);
    $_SESSION['error'] = 'Invalid match ID';
    header('Location: ' . adminUrl('matches/'));
    exit;
}

try {
    $matchModel = new MatchModel();
    $match = $matchModel->getById($matchId);
    
    if (!$match) {
        error_log('Change Innings: Match not found - match_id=' . $matchId);
        $_SESSION['error'] = 'Match not found';
        header('Location: ' . adminUrl('matches/'));
        exit;
    }
    
    error_log('Change Innings: Match found - match_id=' . $matchId . ', state=' . $match['state'] . ', current_innings=' . ($match['current_innings'] ?? 'NULL'));
    
    // Validate match state
    if ($match['state'] !== 'live') {
        error_log('Change Innings: Match not in live state - match_id=' . $matchId . ', state=' . $match['state']);
        $_SESSION['error'] = 'Match must be in live state to change innings. Current state: ' . $match['state'];
        header('Location: ' . adminUrl('matches/scorer.php?id=' . $matchId));
        exit;
    }
    
    // Validate current innings
    $dbCurrentInnings = (int)($match['current_innings'] ?? 1);
    if ($dbCurrentInnings !== 1) {
        error_log('Change Innings: Invalid innings progression - match_id=' . $matchId . ', current_innings=' . $dbCurrentInnings);
        $_SESSION['error'] = 'Can only change from innings 1 to 2. Current innings: ' . $dbCurrentInnings;
        header('Location: ' . adminUrl('matches/scorer.php?id=' . $matchId));
        exit;
    }
    
    // Update to innings 2
    error_log('Change Innings: Attempting to update match_id=' . $matchId . ' to innings 2');
    
    // Use direct SQL update to ensure it works
    $db = Database::getInstance()->getConnection();
    $updateSql = "UPDATE matches SET current_innings = 2, updated_at = NOW() WHERE match_id = :match_id";
    $updateStmt = $db->prepare($updateSql);
    $updateResult = $updateStmt->execute(['match_id' => $matchId]);
    
    if (!$updateResult) {
        $errorInfo = $db->errorInfo();
        error_log('Change Innings: Direct SQL update failed for match_id=' . $matchId . ', Error: ' . json_encode($errorInfo));
        $_SESSION['error'] = 'Failed to update match innings. Database error: ' . ($errorInfo[2] ?? 'Unknown error');
        header('Location: ' . adminUrl('matches/scorer.php?id=' . $matchId));
        exit;
    }
    
    // Verify the update actually happened
    $verifyMatch = $matchModel->getById($matchId);
    if (!$verifyMatch || (int)($verifyMatch['current_innings'] ?? 0) !== 2) {
        error_log('Change Innings: Update verification failed - match_id=' . $matchId . ', current_innings=' . ($verifyMatch['current_innings'] ?? 'NULL'));
        $_SESSION['error'] = 'Update appeared to succeed but verification failed. Please try again.';
        header('Location: ' . adminUrl('matches/scorer.php?id=' . $matchId));
        exit;
    }
    
    error_log('Change Innings: Successfully updated and verified match_id=' . $matchId . ' to innings 2');
    
    // Log action
    try {
        if (function_exists('logAction')) {
            logAction('change_innings', 'match', $matchId, [
                'from_innings' => 1,
                'to_innings' => 2,
                'user_id' => $user['user_id']
            ]);
        }
    } catch (Exception $e) {
        // Don't fail if logging fails
        error_log('Failed to log innings change: ' . $e->getMessage());
    }
    
    // Success - redirect back to score page with cache-busting parameter
    $_SESSION['success'] = 'Innings changed to 2nd innings successfully!';
    $redirectUrl = adminUrl('matches/scorer.php?id=' . $matchId . '&_t=' . time());
    error_log('Change Innings: Redirecting to score page for match_id=' . $matchId . ', URL: ' . $redirectUrl);
    
    // Ensure no output before redirect
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Location: ' . $redirectUrl);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    exit;
    
} catch (PDOException $e) {
    error_log('Change Innings: Database error - ' . $e->getMessage());
    $_SESSION['error'] = 'Database error occurred. Please check server logs.';
    header('Location: ' . adminUrl('matches/scorer.php?id=' . $matchId));
    exit;
} catch (Exception $e) {
    error_log('Change Innings: Error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    $_SESSION['error'] = 'Error changing innings: ' . $e->getMessage();
    header('Location: ' . adminUrl('matches/scorer.php?id=' . $matchId));
    exit;
} catch (Error $e) {
    error_log('Change Innings: Fatal error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    $_SESSION['error'] = 'Fatal error occurred. Please check server logs.';
    header('Location: ' . adminUrl('matches/scorer.php?id=' . $matchId));
    exit;
}

