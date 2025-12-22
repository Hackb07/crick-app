<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

$columns = [
    "ball_type VARCHAR(20) NULL DEFAULT 'leather' AFTER venue",
    "pitch_type VARCHAR(20) NULL DEFAULT 'turf' AFTER ball_type",
    "umpire1 VARCHAR(100) NULL AFTER scorer_name", 
    // Correct order after state
];

// Let's just add them if they don't exist
$sqls = [
    "ALTER TABLE matches ADD COLUMN ball_type VARCHAR(20) NULL DEFAULT 'leather'",
    "ALTER TABLE matches ADD COLUMN pitch_type VARCHAR(20) NULL DEFAULT 'turf'",
    "ALTER TABLE matches ADD COLUMN umpire1_name VARCHAR(100) NULL",
    "ALTER TABLE matches ADD COLUMN umpire2_name VARCHAR(100) NULL",
    "ALTER TABLE matches ADD COLUMN scorer_name VARCHAR(100) NULL",
    "ALTER TABLE matches ADD COLUMN match_type VARCHAR(20) NULL DEFAULT 'limited_overs'"
];

foreach ($sqls as $sql) {
    try {
        $db->exec($sql);
        echo "Executed: $sql\n";
    } catch (PDOException $e) {
        // Ignore "Duplicate column name" error (Code 42S21)
        if ($e->getCode() == '42S21') {
            echo "Column already exists: $sql\n";
        } else {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "Migration Complete.\n";
