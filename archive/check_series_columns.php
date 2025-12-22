<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE series");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) {
    echo $c['Field'] . "\n";
}
