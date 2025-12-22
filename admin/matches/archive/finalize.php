<?php
/**
 * Finalize Match - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

$matchId = (int)getPost('match_id', 0);
if (!$matchId) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

$matchModel = new MatchModel();
$match = $matchModel->getById($matchId);

if (!$match) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

$stateMachine = new MatchStateMachine();
$result = $stateMachine->finalizeMatch($matchId);

// Double check if the state was actually updated (fix for "still shows live" issue)
if ($result) {
    // Force fresh read from DB to verify
    $checkMatch = $matchModel->getById($matchId);
    if ($checkMatch && $checkMatch['state'] !== 'completed') {
        // If not updated, force it directly via model
        error_log("finalize.php: State mismatch detected. Forcing update to 'completed' for match_id=$matchId");
        $matchModel->update($matchId, ['state' => 'completed']);
        // Update local match variable for subsequent logic
        $match['state'] = 'completed';
    }
}

if ($result) {
    // Calculate POTM
    $potmModel = new POTM();
    $potmData = $potmModel->calculate($matchId);
    if ($potmData) {
        $userId = getUserId();
        $potmModel->saveDecision($matchId, $potmData['player_id'], null, 'Auto-calculated', $userId);
    }
    
    // Check if series is complete and calculate POTS
    if (!empty($match['series_id'])) {
        $matchModel = new MatchModel();
        $seriesMatches = $matchModel->getAll(['series_id' => $match['series_id']]);
        $allCompleted = true;
        
        foreach ($seriesMatches as $seriesMatch) {
            if ($seriesMatch['state'] !== 'completed' && $seriesMatch['state'] !== 'abandoned' && $seriesMatch['state'] !== 'cancelled') {
                $allCompleted = false;
                break;
            }
        }
        
        if ($allCompleted && count($seriesMatches) > 0) {
            $pots = new POTS();
            $rankings = $pots->calculate($match['series_id']);
            if (!empty($rankings)) {
                $pots->saveRankings($match['series_id'], $rankings);
            }
        }
    }
    
    // Prevent caching of the redirect to ensure fresh state is loaded
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Location: ' . adminUrl('matches/flow.php?id=' . $matchId));
    exit;
} else {
    header('Location: ' . adminUrl('matches/view.php?id=' . $matchId) . '&error=Failed to finalize match');
    exit;
}

