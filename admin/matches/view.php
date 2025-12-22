<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$matchId = (int)getQuery('id', 0);
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

$score = null;
if (in_array($match['state'], ['live', 'completed'])) {
    $score = calculateMatchScore($matchId);
}

renderAdminLayout('Match Details', 'match-view', [
    'match' => $match,
    'matchId' => $matchId,
    'score' => $score
], [
    'activeMenu' => 'matches'
]);
