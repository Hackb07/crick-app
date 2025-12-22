<?php
require_once __DIR__ . '/includes/bootstrap.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE matches");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") - " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}
