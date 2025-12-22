<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
requireLogin();

// Initialize models
$seriesModel = new Series();
$matchModel = new MatchModel();

// Create Series if needed
$seriesName = 'Test Series 2025';
$series = $seriesModel->getByName($seriesName);

if ($series) {
    $seriesId = $series['series_id'];
} else {
    $seriesId = $seriesModel->create([
        'name' => $seriesName,
        'type' => 'Test',
        'start_date' => date('Y-m-d'),
        'end_date' => date('Y-m-d', strtotime('+1 month')),
        'description' => 'Auto-generated test series'
    ]);
}

// Create Match
$matchId = $matchModel->create([
    'series_id' => $seriesId,
    'team1_id' => 1,
    'team2_id' => 2,
    'match_date' => date('Y-m-d H:i:s'),
    'venue' => 'Test Venue',
    'overs_per_innings' => 20,
    'created_by' => $_SESSION['user_id'] ?? null
]);

if ($matchId) {
    header("Location: flow.php?id=$matchId");
    exit;
} else {
    die("Failed to create match");
}

