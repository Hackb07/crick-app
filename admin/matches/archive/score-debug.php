<?php
/**
 * Debug version - shows errors
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/includes/score-data-loader.php';

echo "PHP loaded OK<br>";

// Allow both admin and scorer roles
if (!isLoggedIn()) {
    header('Location: ' . adminUrl('login.php'));
    exit;
}

echo "User logged in<br>";

$matchId = (int)getQuery('id', 0);
$isScorer = (getSession('role') === 'scorer');
$redirectUrl = $isScorer ? adminUrl('scorer/index.php') : adminUrl('matches/');

if (!$matchId) {
    header('Location: ' . $redirectUrl);
    exit;
}

echo "Match ID: $matchId<br>";

// Load match data
try {
    $scoreData = loadScoreData($matchId);
    echo "Score data loaded OK<br>";
    echo "<pre>";
    print_r(array_keys($scoreData));
    echo "</pre>";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

echo "SUCCESS!";
?>
