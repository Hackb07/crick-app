<?php
/**
 * Series List API
 * 
 * Returns detailed series list with match counts and status grouping
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/middleware.php';

header('Content-Type: application/json');

try {
    $seriesModel = new Series();
    $allSeries = $seriesModel->getAll();
    $matchModel = new MatchModel();

    $activeSeries = [];
    $completedSeries = [];
    $upcomingSeries = [];

    foreach ($allSeries as $series) {
        $matches = $matchModel->getAll(['series_id' => $series['series_id']]);
        
        $hasLive = false;
        $allCompleted = true;
        $hasScheduled = false;
        
        foreach ($matches as $match) {
            if ($match['state'] === 'live' || $match['state'] === 'inprogress') $hasLive = true;
            if ($match['state'] !== 'completed' && $match['state'] !== 'abandoned') $allCompleted = false;
            if ($match['state'] === 'scheduled') $hasScheduled = true;
        }
        
        $series['match_count'] = count($matches);
        $series['completed_count'] = count(array_filter($matches, fn($m) => $m['state'] === 'completed' || $m['state'] === 'abandoned'));
        
        if ($hasLive || (!$allCompleted && !$hasScheduled && count($matches) > 0)) {
            // Also including 'Active' if matches exist but none are clearly scheduled/upcoming yet not all completed
             $activeSeries[] = $series;
        } elseif ($allCompleted && count($matches) > 0) {
            $completedSeries[] = $series;
        } else {
            // Either upcoming or new empty series
            $upcomingSeries[] = $series;
        }
    }

    echo json_encode(['success' => true, 'data' => [
        'active' => $activeSeries,
        'completed' => $completedSeries,
        'upcoming' => $upcomingSeries
    ]]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
