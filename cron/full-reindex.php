<?php
/**
 * Full Reindex Cron Job
 * 
 * Recalculates all stats_cache entries from scratch
 * Runs nightly at 2 AM
 * 
 * This ensures data integrity by recalculating everything
 */

// Allow CLI execution or web access with ?run=1
if (php_sapi_name() !== 'cli' && !getQuery('run')) {
    die('This script can only be run via CLI or with ?run=1');
}

require_once __DIR__ . '/../includes/bootstrap.php';

$db = Database::getInstance()->getConnection();

if (php_sapi_name() === 'cli') {
    echo "Starting full reindex...\n";
}

// Clear all stats_cache
$db->exec("TRUNCATE TABLE stats_cache");

// Reset processed_flag on all events
$db->exec("UPDATE events SET processed_flag = 0");

if (php_sapi_name() === 'cli') {
    echo "Cleared stats_cache and reset event flags.\n";
    echo "Run stats-recompute.php to rebuild stats.\n";
}

