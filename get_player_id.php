<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT player_id FROM players LIMIT 1");
echo $stmt->fetchColumn();
