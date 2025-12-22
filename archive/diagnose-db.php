<?php
/**
 * Database Diagnostic Script
 * Checks what data exists in the database
 */

require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "🔍 CricApp Database Diagnostic\n";
echo "================================\n\n";

// Check teams
$stmt = $db->query("SELECT COUNT(*) as count FROM teams");
$teamCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Teams: $teamCount\n";

// Check players
$stmt = $db->query("SELECT COUNT(*) as count FROM players");
$playerCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Players: $playerCount\n";

// Check matches
$stmt = $db->query("SELECT COUNT(*) as count FROM matches");
$matchCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Matches: $matchCount\n";

// Check events
$stmt = $db->query("SELECT COUNT(*) as count FROM events");
$eventCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Events: $eventCount\n";

// Check commentary
$stmt = $db->query("SELECT COUNT(*) as count FROM commentary");
$commentaryCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Commentary: $commentaryCount\n";

// Check player_appearances
$stmt = $db->query("SELECT COUNT(*) as count FROM player_appearances");
$appearanceCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Player Appearances: $appearanceCount\n";

echo "\n";

if ($teamCount == 0 && $playerCount == 0 && $matchCount == 0) {
    echo "❌ NO TEST DATA FOUND!\n\n";
    echo "Please run the test data generator:\n";
    echo "  php run-test-data.php\n\n";
} elseif ($playerCount > 0 && $matchCount > 0) {
    echo "✅ Test data exists!\n\n";
    
    // Show sample data
    echo "Sample Players:\n";
    $stmt = $db->query("SELECT player_id, name FROM players LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['name']} (ID: {$row['player_id']})\n";
    }
    
    echo "\nSample Matches:\n";
    $stmt = $db->query("SELECT m.match_id, t1.name as team1, t2.name as team2 
                        FROM matches m 
                        JOIN teams t1 ON m.team1_id = t1.team_id 
                        JOIN teams t2 ON m.team2_id = t2.team_id 
                        LIMIT 3");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - Match {$row['match_id']}: {$row['team1']} vs {$row['team2']}\n";
    }
} else {
    echo "⚠️ Partial data found. You may need to regenerate test data.\n\n";
}

echo "\n";
