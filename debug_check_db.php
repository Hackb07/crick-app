<?php
require_once __DIR__ . '/includes/bootstrap.php';

$matchId = 6;
$db = Database::getInstance()->getConnection();

echo "<h1>Raw Database Events for Match $matchId</h1>";

$stmt = $db->prepare("SELECT event_id, payload_json FROM events WHERE match_id = ?");
$stmt->execute([$matchId]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Count: " . count($events) . "<br><br>";

foreach ($events as $row) {
    echo "ID: " . $row['event_id'] . "<br>";
    echo "Raw Payload: <textarea cols=100>" . htmlspecialchars($row['payload_json']) . "</textarea><br>";
    
    $decoded = json_decode($row['payload_json'], true);
    echo "Decoded Type: " . gettype($decoded) . "<br>";
    echo "Decoded Content: <pre>" . print_r($decoded, true) . "</pre>";
    echo "<hr>";
}
