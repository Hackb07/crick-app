<?php
/**
 * Production Features Migration Runner
 * Run this file once to add all production-ready features to the database
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Set execution time limit
set_time_limit(300); // 5 minutes

$db = Database::getInstance()->getConnection();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Production Migration</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .success { color: #10b981; font-weight: bold; }
    .error { color: #ef4444; font-weight: bold; }
    .info { color: #3b82f6; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; }
    h1 { color: #1f2937; }
    h2 { color: #374151; margin-top: 20px; }
    pre { background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>";
echo "</head><body>";

echo "<h1>🚀 Production Features Migration</h1>";
echo "<p class='info'>Adding fielder support, bowling style, captain flag, commentary, and fielding stats...</p>";

$errors = [];
$success = [];

try {
    // Start transaction
    $db->beginTransaction();
    
    // 1. Add fielder_id to events table
    echo "<div class='section'>";
    echo "<h2>1. Adding fielder_id to events table</h2>";
    try {
        // Check if column already exists
        $checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'events' 
                     AND COLUMN_NAME = 'fielder_id'";
        $stmt = $db->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "<p class='info'>✓ fielder_id column already exists</p>";
        } else {
            $sql = "ALTER TABLE events 
                    ADD COLUMN fielder_id INT NULL";
            $db->exec($sql);
            
            $sql = "ALTER TABLE events
                    ADD CONSTRAINT fk_events_fielder 
                    FOREIGN KEY (fielder_id) REFERENCES players(player_id) ON DELETE SET NULL";
            $db->exec($sql);
            
            $sql = "CREATE INDEX idx_fielder ON events(fielder_id)";
            $db->exec($sql);
            
            echo "<p class='success'>✓ Added fielder_id column and index</p>";
            $success[] = "fielder_id added to events";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "fielder_id: " . $e->getMessage();
    }
    echo "</div>";
    
    // 2. Add bowling_style to players table
    echo "<div class='section'>";
    echo "<h2>2. Adding bowling_style to players table</h2>";
    try {
        $checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'players' 
                     AND COLUMN_NAME = 'bowling_style'";
        $stmt = $db->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "<p class='info'>✓ bowling_style column already exists</p>";
        } else {
            $sql = "ALTER TABLE players
                    ADD COLUMN bowling_style VARCHAR(50) NULL AFTER batting_hand";
            $db->exec($sql);
            echo "<p class='success'>✓ Added bowling_style column</p>";
            $success[] = "bowling_style added to players";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "bowling_style: " . $e->getMessage();
    }
    echo "</div>";
    
    // 3. Add photo_url to players table
    echo "<div class='section'>";
    echo "<h2>3. Adding photo_url to players table</h2>";
    try {
        $checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'players' 
                     AND COLUMN_NAME = 'photo_url'";
        $stmt = $db->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "<p class='info'>✓ photo_url column already exists</p>";
        } else {
            $sql = "ALTER TABLE players
                    ADD COLUMN photo_url VARCHAR(255) NULL AFTER bowling_style";
            $db->exec($sql);
            echo "<p class='success'>✓ Added photo_url column</p>";
            $success[] = "photo_url added to players";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "photo_url: " . $e->getMessage();
    }
    echo "</div>";
    
    // 4. Add is_captain to player_appearances table
    echo "<div class='section'>";
    echo "<h2>4. Adding is_captain to player_appearances table</h2>";
    try {
        $checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'player_appearances' 
                     AND COLUMN_NAME = 'is_captain'";
        $stmt = $db->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "<p class='info'>✓ is_captain column already exists</p>";
        } else {
            $sql = "ALTER TABLE player_appearances
                    ADD COLUMN is_captain TINYINT(1) DEFAULT 0 AFTER is_guest";
            $db->exec($sql);
            
            $sql = "CREATE INDEX idx_captain ON player_appearances(match_id, is_captain)";
            $db->exec($sql);
            
            echo "<p class='success'>✓ Added is_captain column and index</p>";
            $success[] = "is_captain added to player_appearances";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "is_captain: " . $e->getMessage();
    }
    echo "</div>";
    
    // 5. Create commentary table
    echo "<div class='section'>";
    echo "<h2>5. Creating commentary table</h2>";
    try {
        $checkSql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'commentary'";
        $stmt = $db->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "<p class='info'>✓ commentary table already exists</p>";
        } else {
            $sql = "CREATE TABLE commentary (
                commentary_id INT PRIMARY KEY AUTO_INCREMENT,
                match_id INT NOT NULL,
                event_id INT NOT NULL,
                innings TINYINT NOT NULL,
                commentary_text TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE,
                FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
                INDEX idx_match_innings (match_id, innings),
                INDEX idx_event (event_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $db->exec($sql);
            echo "<p class='success'>✓ Created commentary table</p>";
            $success[] = "commentary table created";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "commentary table: " . $e->getMessage();
    }
    echo "</div>";
    
    // 6. Create fielding_stats table
    echo "<div class='section'>";
    echo "<h2>6. Creating fielding_stats table</h2>";
    try {
        $checkSql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'fielding_stats'";
        $stmt = $db->query($checkSql);
        
        if ($stmt->rowCount() > 0) {
            echo "<p class='info'>✓ fielding_stats table already exists</p>";
        } else {
            $sql = "CREATE TABLE fielding_stats (
                fielding_stat_id INT PRIMARY KEY AUTO_INCREMENT,
                player_id INT NOT NULL,
                match_id INT NOT NULL,
                catches INT DEFAULT 0,
                run_outs INT DEFAULT 0,
                stumpings INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (player_id) REFERENCES players(player_id) ON DELETE CASCADE,
                FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE,
                UNIQUE KEY unique_player_match (player_id, match_id),
                INDEX idx_player (player_id),
                INDEX idx_match (match_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $db->exec($sql);
            echo "<p class='success'>✓ Created fielding_stats table</p>";
            $success[] = "fielding_stats table created";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "fielding_stats table: " . $e->getMessage();
    }
    echo "</div>";
    
    // Commit transaction
    $db->commit();
    
    echo "<div class='section'>";
    echo "<h2>✅ Migration Summary</h2>";
    
    if (empty($errors)) {
        echo "<p class='success'>All migrations completed successfully!</p>";
        echo "<h3>Changes Made:</h3>";
        echo "<ul>";
        foreach ($success as $item) {
            echo "<li class='success'>✓ $item</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>Some migrations failed:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li class='error'>✗ $error</li>";
        }
        echo "</ul>";
        
        if (!empty($success)) {
            echo "<h3>Successful Changes:</h3>";
            echo "<ul>";
            foreach ($success as $item) {
                echo "<li class='success'>✓ $item</li>";
            }
            echo "</ul>";
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🔍 Verification</h2>";
    echo "<p>Checking database structure...</p>";
    
    // Verify all changes
    $verifications = [
        'events.fielder_id' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'fielder_id'",
        'players.bowling_style' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'players' AND COLUMN_NAME = 'bowling_style'",
        'players.photo_url' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'players' AND COLUMN_NAME = 'photo_url'",
        'player_appearances.is_captain' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'player_appearances' AND COLUMN_NAME = 'is_captain'",
        'commentary table' => "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'commentary'",
        'fielding_stats table' => "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fielding_stats'"
    ];
    
    echo "<ul>";
    foreach ($verifications as $name => $sql) {
        $stmt = $db->query($sql);
        if ($stmt->rowCount() > 0) {
            echo "<li class='success'>✓ $name exists</li>";
        } else {
            echo "<li class='error'>✗ $name missing</li>";
        }
    }
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "<div class='section'>";
    echo "<p class='error'>✗ Fatal Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Delete this file for security: <code>run-production-migration.php</code></li>";
echo "<li>Update wicket modal to include fielder selection</li>";
echo "<li>Update players page to show bowling style</li>";
echo "<li>Implement commentary generation</li>";
echo "<li>Create dummy data for testing</li>";
echo "</ol>";
echo "</div>";

echo "<p style='text-align: center; margin-top: 40px;'>";
echo "<a href='admin/matches/' style='display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px;'>Go to Matches →</a>";
echo "</p>";

echo "</body></html>";
?>
