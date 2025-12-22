<?php
// debug_scorer.php
// Simulate logged in admin user
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;
$_SESSION['email'] = 'admin@example.com';

// Simulate GET request
$_GET['id'] = 89; // Ensure this match ID exists and is valid

// Capture output
ob_start();
try {
    include 'admin/matches/scorer.php';
} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
$output = ob_get_clean();

echo "Script executed.\n";
echo "Output length: " . strlen($output) . "\n";

if (strlen($output) > 0) {
    // Check for common errors in output
    if (strpos($output, '<b>Fatal error</b>') !== false || strpos($output, '<b>Warning</b>') !== false) {
        echo "ERRORS FOUND IN OUTPUT:\n";
        // Extract error lines
        preg_match_all('/<b>(Fatal error|Warning|Notice)<\/b>:  (.*?)(<br|$)/', $output, $matches);
        print_r($matches[0]);
    } else {
        echo "No PHP errors detected in output HTML.\n";
    }
} else {
    echo "WARNING: Output is empty.\n";
}
?>
