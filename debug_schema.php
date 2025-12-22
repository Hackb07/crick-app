<?php
require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance()->getConnection();
echo "<h1>Players Table Columns</h1>";
$stmt = $db->query("DESCRIBE players");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>" . print_r($columns, true) . "</pre>";
