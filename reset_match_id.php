<?php
/**
 * Reset Match ID Auto-Increment Counter
 * 
 * This script resets the AUTO_INCREMENT value for the matches table.
 * Use with caution - ensure no conflicts with existing data.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Match ID Auto-Increment Reset Tool ===\n\n";
    
    // Get current AUTO_INCREMENT value
    $stmt = $db->query("SHOW TABLE STATUS LIKE 'matches'");
    $tableStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentAutoIncrement = $tableStatus['Auto_increment'] ?? 0;
    
    echo "Current AUTO_INCREMENT value: " . ($currentAutoIncrement ?: 'Not set') . "\n";
    
    // Get max match_id if any records exist
    $stmt = $db->query("SELECT MAX(match_id) as max_id FROM matches");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxMatchId = $result['max_id'] ?? 0;
    
    echo "Maximum existing match_id: " . ($maxMatchId ?: 'No records') . "\n";
    echo "Record count: ";
    $stmt = $db->query("SELECT COUNT(*) as count FROM matches");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo $count . "\n\n";
    
    // Reset to 1 as requested
    $resetTo = 1;
    
    if ($maxMatchId > 0 && $maxMatchId >= $resetTo) {
        echo "⚠️  WARNING: There are existing matches in the database.\n";
        echo "MySQL will not allow AUTO_INCREMENT to be set below the maximum existing ID.\n";
        echo "To reset to 1, we need to delete all existing matches first.\n\n";
        
        // Check for foreign key constraints
        echo "Checking for related data...\n";
        
        // Count related records that might be affected
        $tables = [
            'player_appearances' => 'match_id',
            'events' => 'match_id',
            'events_suspense' => 'match_id',
            'clone_links' => 'source_match_id',
            'match_locks' => 'match_id'
        ];
        
        $relatedCount = 0;
        foreach ($tables as $table => $column) {
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM {$table} WHERE {$column} IN (SELECT match_id FROM matches)");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $result['count'] ?? 0;
                if ($count > 0) {
                    echo "  - {$table}: {$count} related records\n";
                    $relatedCount += $count;
                }
            } catch (PDOException $e) {
                // Table might not exist or have different structure
            }
        }
        
        if ($relatedCount > 0) {
            echo "\n⚠️  There are {$relatedCount} related records that will be deleted due to CASCADE constraints.\n";
        }
        
        echo "\nProceeding to delete all matches and reset AUTO_INCREMENT to 1...\n\n";
        
        // Delete all matches (CASCADE will handle related records)
        try {
            $db->exec("DELETE FROM `matches`");
            echo "✅ Deleted all matches\n";
            
            // Now reset AUTO_INCREMENT to 1
            $db->exec("ALTER TABLE `matches` AUTO_INCREMENT = 1");
            echo "✅ Reset AUTO_INCREMENT to 1\n";
            
            echo "\n✅ Reset completed successfully\n";
        } catch (PDOException $e) {
            throw $e;
        }
    } else {
        // No existing records, safe to reset to 1
        echo "No existing matches found. Resetting to 1...\n";
        $db->exec("ALTER TABLE `matches` AUTO_INCREMENT = 1");
    }
    
    // Verify the reset
    $stmt = $db->query("SHOW TABLE STATUS LIKE 'matches'");
    $tableStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    $newAutoIncrement = $tableStatus['Auto_increment'] ?? 0;
    
    echo "\n✅ Successfully reset AUTO_INCREMENT to: {$newAutoIncrement}\n";
    echo "\nNext match_id will be: {$newAutoIncrement}\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

