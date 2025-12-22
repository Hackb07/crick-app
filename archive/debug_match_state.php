<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/admin/matches/includes/score-data-loader.php';

$matchId = 48;

echo "=== DEBUG: Match State Loading for Match $matchId ===\n\n";

try {
    $scoreData = loadScoreData($matchId);
    
    echo "Current Innings: {$scoreData['current_innings']}\n";
    echo "Current Score: {$scoreData['current_state']['runs']}\n";
    echo "Current Wickets: {$scoreData['current_state']['wickets']}\n";
    echo "Legal Balls: {$scoreData['current_state']['legal_balls']}\n";
    echo "Current Striker ID: " . ($scoreData['current_striker_id'] ?? 'NULL') . "\n";
    echo "Current Non-Striker ID: " . ($scoreData['current_non_striker_id'] ?? 'NULL') . "\n";
    echo "Current Bowler ID: " . ($scoreData['current_bowler_id'] ?? 'NULL') . "\n";
    echo "\nEvents in Current Innings: " . count($scoreData['current_innings_events']) . "\n";
    
    if (count($scoreData['current_innings_events']) > 0) {
        echo "\nLast 3 Events:\n";
        $lastEvents = array_slice($scoreData['current_innings_events'], -3);
        foreach ($lastEvents as $event) {
            $payload = json_decode($event['payload_json'], true);
            echo "- Event {$event['event_id']}: {$payload['type']}, Runs: {$payload['runs']}, Innings: {$payload['innings']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
