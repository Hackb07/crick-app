<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/session.php'; // Required for getSession()
require_once __DIR__ . '/admin/matches/includes/score-data-loader.php';

$matchId = 6; // From user screenshot
echo "<h1>Debug State for Match $matchId</h1>";


try {
    $db = Database::getInstance()->getConnection();
    
    // Cleanup Debug Event
    // $stmt = $db->prepare("DELETE FROM events WHERE match_id = :id AND client_id = 'debug-client'");
    // $stmt->execute(['id' => $matchId]);
    // $count = $stmt->rowCount();
    
    // if ($count > 0) {
    //    $db->exec("UPDATE matches SET last_seq = last_seq - 1 WHERE match_id = $matchId");
    //    echo "<h3>Cleaned up $count debug event(s). Reset seq.</h3>";
    // } else {
    //    echo "<h3>No debug events to clean up.</h3>";
    // }

    $data = loadScoreData($matchId);
    
    echo "<h2>Match Info</h2>";
    echo "Current Innings: " . $data['current_innings'] . "<br>";
    echo "Match State: " . $data['match']['state'] . "<br>";
    // ...

    echo "<pre>" . print_r($data['current_state'], true) . "</pre>";

    if (empty($data['current_innings_events'])) {
        echo "<h2>Trying to Insert Test Event</h2>";
        
        $match = $data['match'];
        $batters = $data['batting_team_players'];
        $bowlers = $data['bowling_team_players'];
        
        if (empty($batters) || empty($bowlers)) {
            die("No players found in match data. Check setup.");
        }
        
        $strikerId = $batters[0]['player_id'];
        $nonStrikerId = $batters[1]['player_id'];
        $bowlerId = $bowlers[0]['player_id'];
        
        echo "Striker: $strikerId, Non: $nonStrikerId, Bowler: $bowlerId<br>";
        
        $eventModel = new Event();
        $payload = [
            'innings' => 1,
            'type' => 'run',
            'runs' => 1,
            'striker_id' => $strikerId,
            'non_striker_id' => $nonStrikerId,
            'bowler_id' => $bowlerId
        ];
        
        $eventData = [
            'event_uuid' => uniqid('debug-'),
            'client_id' => 'debug-client',
            'client_ts' => round(microtime(true) * 1000),
            'ball_index' => 1,
            'payload_json' => $payload
        ];
        
        // Fix array key for payload - batchInsert uses $event['payload_json'] directly.
        
        $result = $eventModel->batchInsert($matchId, [$eventData], 0);
        
        echo "<h2>Insert Result</h2>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        if ($result['success'] && $result['inserted_count'] > 0) {
            echo "<h3>Success! Event inserted.</h3>";
        } else {
            echo "<h3>FAILED to insert.</h3>";
        }
    } else {
        echo "<h2>Events Found (" . count($data['current_innings_events']) . ")</h2>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

