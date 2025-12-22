<?php
require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

// First, show table structure
echo "Events Table Structure:\n";
echo str_repeat("=", 80) . "\n";
$stmt = $db->query("DESCRIBE events");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo "{$col['Field']} ({$col['Type']})\n";
}
echo "\n" . str_repeat("=", 80) . "\n\n";

// Now get last 4 events
$sql = "SELECT * FROM events WHERE match_id = 48 ORDER BY event_id DESC LIMIT 4";
$stmt = $db->prepare($sql);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Last 4 Events for Match ID 48:\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($events as $event) {
    foreach ($event as $key => $value) {
        echo "$key: $value\n";
    }
    echo str_repeat("-", 80) . "\n";
}

