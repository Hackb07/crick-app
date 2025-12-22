<?php
/**
 * Update Players Table Schema
 * Adds batting_hand and bowling_style fields
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if batting_hand column exists
    $checkBatting = $db->query("SHOW COLUMNS FROM players LIKE 'batting_hand'");
    if ($checkBatting->rowCount() == 0) {
        echo "Adding batting_hand column...\n";
        $db->exec("ALTER TABLE players ADD COLUMN batting_hand ENUM('right', 'left') DEFAULT NULL AFTER profile_image");
        echo "✓ batting_hand column added\n";
    } else {
        echo "✓ batting_hand column already exists\n";
    }
    
    // Check if bowling_style column exists
    $checkBowling = $db->query("SHOW COLUMNS FROM players LIKE 'bowling_style'");
    if ($checkBowling->rowCount() == 0) {
        echo "Adding bowling_style column...\n";
        $db->exec("ALTER TABLE players ADD COLUMN bowling_style ENUM('fast', 'fast-medium', 'medium', 'off-spin', 'leg-spin', 'left-arm-spin', 'left-arm-orthodox') DEFAULT NULL AFTER batting_hand");
        echo "✓ bowling_style column added\n";
    } else {
        echo "✓ bowling_style column already exists\n";
    }
    
    // Add indexes
    $checkIndex1 = $db->query("SHOW INDEXES FROM players WHERE Key_name = 'idx_batting_hand'");
    if ($checkIndex1->rowCount() == 0) {
        echo "Adding batting_hand index...\n";
        $db->exec("ALTER TABLE players ADD INDEX idx_batting_hand (batting_hand)");
        echo "✓ Index added\n";
    }
    
    $checkIndex2 = $db->query("SHOW INDEXES FROM players WHERE Key_name = 'idx_bowling_style'");
    if ($checkIndex2->rowCount() == 0) {
        echo "Adding bowling_style index...\n";
        $db->exec("ALTER TABLE players ADD INDEX idx_bowling_style (bowling_style)");
        echo "✓ Index added\n";
    }
    
    echo "\n✅ Database schema updated successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

