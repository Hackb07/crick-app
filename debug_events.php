<?php
require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

// Get latest match
$stmt = $db->query("SELECT match_id, team1_name, team2_name, state FROM matches ORDER BY match_id DESC LIMIT 1");
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if ($match) {
    echo "Latest Match: ID {$match['match_id']} ({$match['team1_name']} vs {$match['team2_name']}) - State: {$match['state']}\n";
    
    // Count events
    $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE match_id = ?");
    $stmt->execute([$match['match_id']]);
    $count = $stmt->fetchColumn();
    
    echo "Total Events in DB: $count\n";
    
    // Show last 5 events
    $stmt = $db->prepare("SELECT event_id, event_type, payload_json, created_at FROM events WHERE match_id = ? ORDER BY event_id DESC LIMIT 5");
    $stmt->execute([$match['match_id']]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nLast 5 Events:\n";
    foreach ($events as $event) {
        $payload = json_decode($event['payload_json'], true);
        $type = $payload['type'] ?? 'unknown';
        $desc = "Type: $type";
        if (isset($payload['runs'])) $desc .= ", Runs: {$payload['runs']}";
        if (isset($payload['striker_id'])) $desc .= ", Striker: {$payload['striker_id']}";
        
        echo "[{$event['event_id']}] {$event['created_at']} - $desc\n";
    }
} else {
    echo "No matches found.\n";
}
