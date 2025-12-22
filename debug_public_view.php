<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/classes/MatchStatsService.php';

$matchId = 6;
$service = new MatchStatsService();

echo "<h1>Debug MatchStatsService for Match $matchId</h1>";

// 1. Check Events Limit
$db = Database::getInstance()->getConnection();
$count = $db->query("SELECT COUNT(*) FROM events WHERE match_id = $matchId")->fetchColumn();
echo "Total Events in DB: $count<br>";

// 2. Run Service
$stats = $service->getMatchStats($matchId);

if (!$stats) {
    echo "Stats Service returned NULL<br>";
    exit;
}

echo "Events Loaded by Service: " . count($stats['events']) . "<br>";
echo "<h2>Innings Data</h2>";
foreach ($stats['inningsData'] as $inn => $data) {
    echo "Innings $inn: Runs: {$data['total_runs']}, Wickets: {$data['total_wickets']}, Balls: {$data['balls_legal']}<br>";
}

echo "<h2>Event Payloads</h2>";
foreach ($stats['events'] as $event) {
    echo "ID: {$event['event_id']}, Seq: {$event['assigned_server_seq']}, Payload: " . htmlspecialchars($event['payload_json']) . "<br>";
}
