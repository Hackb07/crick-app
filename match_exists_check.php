<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = Database::getInstance()->getConnection();
$matchId = 6;

$stmt = $db->prepare("SELECT * FROM matches WHERE match_id = ?");
$stmt->execute([$matchId]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if ($match) {
    echo "Match 6 found: " . $match['team1_id'] . " vs " . $match['team2_id'] . "\n";
    echo "State: " . $match['state'] . "\n";
    echo "Last Seq: [" . ($match['last_seq'] ?? 'NULL') . "]\n";
} else {
    echo "Match 6 NOT FOUND in matches table!\n";
}

$stmt = $db->query("SELECT count(*) FROM matches");
echo "Total Matches in DB: " . $stmt->fetchColumn() . "\n";

$stmt = $db->prepare("SELECT count(*) FROM events WHERE match_id = ?");
$stmt->execute([$matchId]);
echo "Events for Match 6: " . $stmt->fetchColumn() . "\n";

$stmt = $db->query("SELECT count(*) FROM events");
echo "Total Events in DB: " . $stmt->fetchColumn() . "\n";
