<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT match_id, team1_id, team2_id, state FROM matches");
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($matches);
