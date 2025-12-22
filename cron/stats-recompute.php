<?php
/**
 * Statistics Recompute Cron Job
 * 
 * Processes unprocessed events and updates stats_cache table
 * Runs every 30-60 seconds (adjust based on hosting limits)
 * 
 * Chunked, resumable processing with progress cursors
 */

// Allow CLI execution or web access with ?run=1
if (php_sapi_name() !== 'cli' && !getQuery('run')) {
    die('This script can only be run via CLI or with ?run=1');
}

require_once __DIR__ . '/../includes/bootstrap.php';

// Configuration
$chunkSize = defined('STATS_RECOMPUTE_CHUNK_SIZE') ? STATS_RECOMPUTE_CHUNK_SIZE : 100;
$heartbeatInterval = defined('STATS_RECOMPUTE_HEARTBEAT_INTERVAL') ? STATS_RECOMPUTE_HEARTBEAT_INTERVAL : 10;
$staleThreshold = defined('STATS_RECOMPUTE_STALE_THRESHOLD') ? STATS_RECOMPUTE_STALE_THRESHOLD : 60;

$db = Database::getInstance()->getConnection();

// Check for existing running job
$sql = "SELECT job_id, cursor, last_heartbeat, started_at 
        FROM jobs 
        WHERE job_type = 'stats_recompute' 
        AND status = 'running' 
        ORDER BY started_at DESC 
        LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute();
$existingJob = $stmt->fetch();

// If job exists and is stale, mark as failed
if ($existingJob) {
    $lastHeartbeat = strtotime($existingJob['last_heartbeat']);
    $now = time();
    
    if (($now - $lastHeartbeat) > $staleThreshold) {
        // Mark stale job as failed
        $updateSql = "UPDATE jobs SET status = 'failed', completed_at = NOW() WHERE job_id = :job_id";
        $updateStmt = $db->prepare($updateSql);
        $updateStmt->execute(['job_id' => $existingJob['job_id']]);
        $existingJob = null;
    } else {
        // Job is still running, exit
        if (php_sapi_name() === 'cli') {
            echo "Job already running (ID: {$existingJob['job_id']})\n";
        }
        exit(0);
    }
}

// Start new job or resume existing
$cursor = null;
$jobId = null;

if ($existingJob && !empty($existingJob['cursor'])) {
    // Resume from cursor
    $cursor = json_decode($existingJob['cursor'], true);
    $jobId = $existingJob['job_id'];
    
    // Update heartbeat
    $updateSql = "UPDATE jobs SET last_heartbeat = NOW() WHERE job_id = :job_id";
    $updateStmt = $db->prepare($updateSql);
    $updateStmt->execute(['job_id' => $jobId]);
    
    if (php_sapi_name() === 'cli') {
        echo "Resuming job {$jobId} from cursor\n";
    }
} else {
    // Create new job
    $insertSql = "INSERT INTO jobs (job_type, status, cursor, last_heartbeat, started_at) 
                  VALUES ('stats_recompute', 'running', '{}', NOW(), NOW())";
    $db->exec($insertSql);
    $jobId = $db->lastInsertId();
    
    // Initialize cursor
    $cursor = [
        'last_match_id' => 0,
        'last_event_id' => 0,
        'processed_count' => 0
    ];
    
    if (php_sapi_name() === 'cli') {
        echo "Starting new job {$jobId}\n";
    }
}

// Get unprocessed events (chunked)
$sql = "SELECT e.*, pa.player_id, pa.team_id, pa.appearance_id, m.current_innings
        FROM events e
        INNER JOIN matches m ON e.match_id = m.match_id
        LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
        WHERE e.processed_flag = 0
        AND (e.match_id > :last_match_id OR (e.match_id = :last_match_id AND e.event_id > :last_event_id))
        ORDER BY e.match_id ASC, e.assigned_server_seq ASC
        LIMIT :chunk_size";

$stmt = $db->prepare($sql);
$stmt->bindValue(':last_match_id', $cursor['last_match_id'] ?? 0, PDO::PARAM_INT);
$stmt->bindValue(':last_event_id', $cursor['last_event_id'] ?? 0, PDO::PARAM_INT);
$stmt->bindValue(':chunk_size', $chunkSize, PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll();

if (empty($events)) {
    // No more events to process
    $updateSql = "UPDATE jobs SET status = 'completed', completed_at = NOW() WHERE job_id = :job_id";
    $updateStmt = $db->prepare($updateSql);
    $updateStmt->execute(['job_id' => $jobId]);
    
    if (php_sapi_name() === 'cli') {
        echo "No more events to process. Job {$jobId} completed.\n";
    }
    exit(0);
}

$processedCount = 0;
$lastMatchId = $cursor['last_match_id'] ?? 0;
$lastEventId = $cursor['last_event_id'] ?? 0;

// Process each event and update stats_cache
foreach ($events as $event) {
    $matchId = $event['match_id'];
    $appearanceId = $event['appearance_id'];
    $playerId = $event['player_id'];
    $payload = json_decode($event['payload_json'], true);
    $eventType = $payload['type'] ?? 'unknown';
    
    if (!$appearanceId || !$playerId) {
        // Skip events without appearance_id (can't track stats)
        $lastMatchId = $matchId;
        $lastEventId = $event['event_id'];
        $processedCount++;
        continue;
    }
    
    // Get or create stats_cache entry
    $getStatsSql = "SELECT * FROM stats_cache 
                    WHERE appearance_id = :appearance_id AND match_id = :match_id 
                    LIMIT 1";
    $getStatsStmt = $db->prepare($getStatsSql);
    $getStatsStmt->execute([
        'appearance_id' => $appearanceId,
        'match_id' => $matchId
    ]);
    $existingStats = $getStatsStmt->fetch();
    
    // Recalculate stats from all events for this appearance (simpler approach)
    // This ensures accuracy even if events are processed out of order
    $recalcSql = "SELECT 
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'run' 
                        AND JSON_EXTRACT(e.payload_json, '$.striker_id') = :player_id 
                        THEN CAST(JSON_EXTRACT(e.payload_json, '$.runs') AS UNSIGNED) 
                        ELSE 0 
                    END) as total_runs,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'run' 
                        AND JSON_EXTRACT(e.payload_json, '$.striker_id') = :player_id 
                        THEN 1 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'wicket' 
                        AND JSON_EXTRACT(e.payload_json, '$.striker_id') = :player_id 
                        THEN 1
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'extra' 
                        AND JSON_EXTRACT(e.payload_json, '$.striker_id') = :player_id 
                        AND JSON_EXTRACT(e.payload_json, '$.extra_type') NOT IN ('wide', 'no-ball')
                        THEN 1
                        ELSE 0 
                    END) as balls_faced,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'wicket' 
                        AND JSON_EXTRACT(e.payload_json, '$.bowler_id') = :player_id 
                        THEN 1 
                        ELSE 0 
                    END) as wickets,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'run' 
                        AND JSON_EXTRACT(e.payload_json, '$.bowler_id') = :player_id 
                        THEN CAST(JSON_EXTRACT(e.payload_json, '$.runs') AS UNSIGNED)
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'extra' 
                        AND JSON_EXTRACT(e.payload_json, '$.bowler_id') = :player_id 
                        THEN 1
                        ELSE 0 
                    END) as runs_conceded,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'run' 
                        AND JSON_EXTRACT(e.payload_json, '$.bowler_id') = :player_id 
                        THEN 1
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'wicket' 
                        AND JSON_EXTRACT(e.payload_json, '$.bowler_id') = :player_id 
                        THEN 1
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'extra' 
                        AND JSON_EXTRACT(e.payload_json, '$.bowler_id') = :player_id 
                        AND JSON_EXTRACT(e.payload_json, '$.extra_type') NOT IN ('wide', 'no-ball')
                        THEN 1
                        ELSE 0 
                    END) as balls_bowled,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'run' 
                        AND JSON_EXTRACT(e.payload_json, '$.striker_id') = :player_id 
                        AND CAST(JSON_EXTRACT(e.payload_json, '$.runs') AS UNSIGNED) = 4
                        THEN 1 
                        ELSE 0 
                    END) as fours,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'run' 
                        AND JSON_EXTRACT(e.payload_json, '$.striker_id') = :player_id 
                        AND CAST(JSON_EXTRACT(e.payload_json, '$.runs') AS UNSIGNED) = 6
                        THEN 1 
                        ELSE 0 
                    END) as sixes,
                    SUM(CASE 
                        WHEN JSON_EXTRACT(e.payload_json, '$.type') = 'wicket' 
                        AND JSON_EXTRACT(e.payload_json, '$.striker_id') = :player_id 
                        THEN 1 
                        ELSE 0 
                    END) as dismissals
                  FROM events e
                  INNER JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
                  WHERE e.match_id = :match_id AND pa.appearance_id = :appearance_id";
    
    $recalcStmt = $db->prepare($recalcSql);
    $recalcStmt->execute([
        'match_id' => $matchId,
        'appearance_id' => $appearanceId,
        'player_id' => $playerId
    ]);
    $recalcStats = $recalcStmt->fetch();
    
    if ($recalcStats) {
        $totalRuns = (int)($recalcStats['total_runs'] ?? 0);
        $totalBallsFaced = (int)($recalcStats['balls_faced'] ?? 0);
        $totalWickets = (int)($recalcStats['wickets'] ?? 0);
        $totalRunsConceded = (int)($recalcStats['runs_conceded'] ?? 0);
        $totalBallsBowled = (int)($recalcStats['balls_bowled'] ?? 0);
        $totalFours = (int)($recalcStats['fours'] ?? 0);
        $totalSixes = (int)($recalcStats['sixes'] ?? 0);
        $totalDismissals = (int)($recalcStats['dismissals'] ?? 0);
        
        $calcStrikeRate = $totalBallsFaced > 0 ? round(($totalRuns / $totalBallsFaced * 100), 2) : null;
        $calcOversBowled = round($totalBallsBowled / 6.0, 2);
        $calcEconomyRate = $calcOversBowled > 0 ? round(($totalRunsConceded / $calcOversBowled), 2) : null;
        
        // Upsert stats_cache - include all new columns
        $upsertSql = "INSERT INTO stats_cache 
                      (appearance_id, player_id, match_id, runs, wickets, balls_faced, 
                       overs_bowled, strike_rate, economy_rate, fours, sixes, dismissals, runs_conceded, last_event_at, updated_at) 
                      VALUES 
                      (:appearance_id, :player_id, :match_id, :runs, :wickets, :balls_faced, 
                       :overs_bowled, :strike_rate, :economy_rate, :fours, :sixes, :dismissals, :runs_conceded, NOW(), NOW())
                      ON DUPLICATE KEY UPDATE
                      runs = :runs,
                      wickets = :wickets,
                      balls_faced = :balls_faced,
                      overs_bowled = :overs_bowled,
                      strike_rate = :strike_rate,
                      economy_rate = :economy_rate,
                      fours = :fours,
                      sixes = :sixes,
                      dismissals = :dismissals,
                      runs_conceded = :runs_conceded,
                      last_event_at = NOW(),
                      updated_at = NOW()";
        
        $upsertStmt = $db->prepare($upsertSql);
        $upsertStmt->execute([
            'appearance_id' => $appearanceId,
            'player_id' => $playerId,
            'match_id' => $matchId,
            'runs' => $totalRuns,
            'wickets' => $totalWickets,
            'balls_faced' => $totalBallsFaced,
            'overs_bowled' => $calcOversBowled,
            'strike_rate' => $calcStrikeRate,
            'economy_rate' => $calcEconomyRate,
            'fours' => $totalFours,
            'sixes' => $totalSixes,
            'dismissals' => $totalDismissals,
            'runs_conceded' => $totalRunsConceded
        ]);
    }
    
    // Update cursor
    $lastMatchId = $matchId;
    $lastEventId = $event['event_id'];
    $processedCount++;
}

// Mark events as processed
$eventIds = array_column($events, 'event_id');
if (!empty($eventIds)) {
    $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
    $markSql = "UPDATE events SET processed_flag = 1 WHERE event_id IN ($placeholders)";
    $markStmt = $db->prepare($markSql);
    $markStmt->execute($eventIds);
}

// Update cursor and heartbeat
$cursor['last_match_id'] = $lastMatchId;
$cursor['last_event_id'] = $lastEventId;
$cursor['processed_count'] = ($cursor['processed_count'] ?? 0) + $processedCount;

$isComplete = $processedCount < $chunkSize;

$updateSql = "UPDATE jobs 
              SET cursor = :cursor, 
                  last_heartbeat = NOW(),
                  status = :status,
                  completed_at = :completed_at
              WHERE job_id = :job_id";

$updateStmt = $db->prepare($updateSql);
$updateStmt->execute([
    'cursor' => json_encode($cursor),
    'status' => $isComplete ? 'completed' : 'running',
    'completed_at' => $isComplete ? date('Y-m-d H:i:s') : null,
    'job_id' => $jobId
]);

if (php_sapi_name() === 'cli') {
    echo "Processed {$processedCount} events. Cursor updated.\n";
    if ($isComplete) {
        echo "Job {$jobId} completed.\n";
    } else {
        echo "Job {$jobId} continuing...\n";
    }
}
