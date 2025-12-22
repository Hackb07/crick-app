<?php
/**
 * Series Aggregates Cron Job
 * 
 * Calculates POTS (Player of the Series) aggregates
 * Runs every 5 minutes
 */

// Allow CLI execution or web access with ?run=1
if (php_sapi_name() !== 'cli' && !getQuery('run')) {
    die('This script can only be run via CLI or with ?run=1');
}

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();
$pots = new POTS();

// Get all series with completed matches
$sql = "SELECT DISTINCT s.series_id, s.name
        FROM series s
        INNER JOIN matches m ON s.series_id = m.series_id
        WHERE m.state = 'completed'
        ORDER BY s.series_id ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$series = $stmt->fetchAll();

$processedCount = 0;

foreach ($series as $seriesData) {
    $seriesId = $seriesData['series_id'];
    
    // Calculate POTS rankings
    $rankings = $pots->calculate($seriesId);
    
    if (!empty($rankings)) {
        // Save rankings
        $pots->saveRankings($seriesId, $rankings);
        $processedCount++;
        
        if (php_sapi_name() === 'cli') {
            echo "Processed series: {$seriesData['name']} (ID: {$seriesId})\n";
        }
    }
}

if (php_sapi_name() === 'cli') {
    echo "Processed {$processedCount} series.\n";
}

