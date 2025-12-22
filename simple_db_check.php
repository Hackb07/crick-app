<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/bootstrap.php';
echo "Bootstrap loaded\n";
try {
    $db = Database::getInstance()->getConnection();
    echo "DB Connected\n";
    $matchId = 6;
    $stmt = $db->prepare("SELECT count(*) FROM events WHERE match_id = ?");
    $stmt->execute([$matchId]);
    $count = $stmt->fetchColumn();
    echo "Match $matchId Event Count: $count\n";
    
    $stmt = $db->prepare("SELECT last_seq FROM matches WHERE match_id = ?");
    $stmt->execute([$matchId]);
    $lastSeq = $stmt->fetchColumn();
    echo "Match $matchId last_seq: $lastSeq\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
