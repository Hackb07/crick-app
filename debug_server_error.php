<?php
// debug_server_error.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/bootstrap.php';

echo "<h1>Debug Event Sync</h1>";

try {
    $db = Database::getInstance()->getConnection();
    echo "<p>Database Connected.</p>";
    
    // 1. Get a live match or create one
    $stmt = $db->query("SELECT match_id, last_seq FROM matches WHERE state IN ('live', 'draft') ORDER BY match_id DESC LIMIT 1");
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$match) {
        die("No live/draft match found to test.");
    }
    
    $matchId = (int)$match['match_id'];
    $lastSeq = (int)$match['last_seq'];
    
    echo "<p>Testing with Match ID: $matchId (Last Seq: $lastSeq)</p>";
    
    // 2. Create a dummy event
    $eventModel = new Event();
    
    $dummyEvent = [
        'event_uuid' => uniqid('debug_'),
        'client_id' => 'debug_script',
        'client_ts' => date('Y-m-d H:i:s'),
        'ball_index' => 0,
        'payload_json' => [
            'type' => 'debug_log',
            'message' => 'Testing sync logic',
            'innings' => 1
        ]
    ];
    
    // 3. Simulate API Logic
    echo "<p>Simulating API processing...</p>";
    
    // Check sequence
    $currentServerSeq = (int)($match['last_seq'] ?? 0);
    $clientBaseSeq = $currentServerSeq; // Simulate valid sync
    
    if ($clientBaseSeq !== $currentServerSeq) {
        die("Sequence conflict detected (Client: $clientBaseSeq, Server: $currentServerSeq)");
    }
    
    // Try batch insert
    $result = $eventModel->batchInsert($matchId, [$dummyEvent], $clientBaseSeq);
    
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<h2 style='color:green'>SUCCESS</h2>";
    } else {
        echo "<h2 style='color:red'>FAILED</h2>";
    }

} catch (Throwable $e) {
    echo "<h2 style='color:red'>EXCEPTION CAUGHT</h2>";
    echo "<h3>" . get_class($e) . ": " . $e->getMessage() . "</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
