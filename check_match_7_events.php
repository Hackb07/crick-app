<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = Database::getInstance()->getConnection();
$matchId = 7;
$stmt = $db->prepare("SELECT count(*) FROM events WHERE match_id = ?");
$stmt->execute([$matchId]);
echo "Events for Match 7: " . $stmt->fetchColumn() . "\n";
