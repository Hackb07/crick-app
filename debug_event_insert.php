<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/classes/Event.php';
require_once __DIR__ . '/classes/StatsCalculator.php';

// getting last match
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT match_id, team1_id, team2_id, current_innings FROM matches ORDER BY match_id DESC LIMIT 1");
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo "No matches found\n";
    exit;
}

$matchId = $match['match_id'];
echo "Testing with Match ID: $matchId\n";

// Create a dummy run event
$eventModel = new Event();
$eventData = [
    'event_uuid' => uniqid('evt_'),
    'payload_json' => [
        'type' => 'run',
        'runs' => 1,
        'innings' => 1,
        'striker_id' => 4, // Valid player ID fetched from DB
        'bowler_id' => 2 
    ]
];

// Get current server seq
$matchModel = new MatchModel();
$matchData = $matchModel->getById($matchId);
$currentSeq = $matchData['last_seq'];

echo "Current Server Seq: $currentSeq\n";

// Insert Event
$result = $eventModel->batchInsert($matchId, [$eventData], $currentSeq);
print_r($result);

if ($result['success']) {
    echo "Event Inserted Successfully. Updating Stats...\n";
    
    $statsCalculator = new StatsCalculator();
    try {
        $statsCalculator->updateMatchStats($matchId);
        echo "Stats Updated.\n";
    } catch (Exception $e) {
        echo "Stats Update Failed: " . $e->getMessage() . "\n";
    }
    
    // Check batting stats
    $stmt = $db->prepare("SELECT * FROM batting_stats WHERE match_id = ?");
    $stmt->execute([$matchId]);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Batting Stats:\n";
    print_r($stats);
} else {
    echo "Event Insert Failed.\n";
}
