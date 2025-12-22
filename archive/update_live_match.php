<?php
$file = __DIR__ . '/live-match.php';
$content = file_get_contents($file);

// Find split point
$splitPos = strpos($content, '<!DOCTYPE html>');
if ($splitPos === false) {
    echo "Error: Could not find <!DOCTYPE html>";
    exit(1);
}

$htmlContent = substr($content, $splitPos);

// Replace $score usage in HTML
$htmlContent = str_replace("\$score['innings1']['runs']", "\$inningsData[1]['total_runs']", $htmlContent);

// New PHP Header
$newPhp = "<?php\n" .
"/**\n" .
" * Live Match View - Premium Design\n" .
" */\n\n" .
"require_once __DIR__ . '/includes/bootstrap.php';\n" .
"require_once __DIR__ . '/includes/utils.php';\n\n" .
"\$matchId = (int)getQuery('id', 0);\n" .
"if (!\$matchId) {\n" .
"    header('Location: ' . publicUrl('index.php'));\n" .
"    exit;\n" .
"}\n\n" .
"// Load Match Stats Service\n" .
"require_once __DIR__ . '/classes/MatchStatsService.php';\n" .
"\$statsService = new MatchStatsService();\n" .
"\$matchData = \$statsService->getLiveMatchData(\$matchId);\n\n" .
"if (!\$matchData) {\n" .
"    header('Location: ' . publicUrl('index.php'));\n" .
"    exit;\n" .
"}\n\n" .
"// Extract variables\n" .
"\$match = \$matchData['match'];\n" .
"\$events = \$matchData['events'];\n" .
"\$inningsData = \$matchData['inningsData'];\n" .
"\$partnership = \$matchData['partnership'];\n" .
"\$recentBalls = \$matchData['recentBalls'];\n" .
"\$playerLookup = \$matchData['playerLookup'];\n\n" .
"// Determine current innings\n" .
"\$currentInnings = 1;\n" .
"if (\$inningsData[2]['balls_legal'] > 0 || \$inningsData[2]['total_runs'] > 0) {\n" .
"    \$currentInnings = 2;\n" .
"}\n\n" .
"\$currentScoreData = \$inningsData[\$currentInnings];\n\n" .
"// Current Batters\n" .
"\$currentStrikerId = \$match['current_striker_id'] ?? 0;\n" .
"\$currentNonStrikerId = \$match['current_non_striker_id'] ?? 0;\n\n" .
"\$currentStriker = \$currentScoreData['batting'][\$currentStrikerId] ?? ['runs' => 0, 'balls' => 0, '4s' => 0, '6s' => 0];\n" .
"\$currentStrikerName = \$playerLookup[\$currentStrikerId]['name'] ?? 'Not Set';\n\n" .
"\$currentNonStriker = \$currentScoreData['batting'][\$currentNonStrikerId] ?? ['runs' => 0, 'balls' => 0, '4s' => 0, '6s' => 0];\n" .
"\$currentNonStrikerName = \$playerLookup[\$currentNonStrikerId]['name'] ?? 'Not Set';\n\n" .
"// Current Bowler\n" .
"\$currentBowlerId = \$match['current_bowler_id'] ?? 0;\n" .
"\$currentBowler = \$currentScoreData['bowling'][\$currentBowlerId] ?? ['overs' => 0, 'maidens' => 0, 'runs' => 0, 'wickets' => 0];\n" .
"\$currentBowlerName = \$playerLookup[\$currentBowlerId]['name'] ?? 'Not Set';\n\n" .
"// Run Rate\n" .
"\$totalBalls = \$currentScoreData['balls_legal'];\n" .
"\$totalRuns = \$currentScoreData['total_runs'];\n" .
"\$runRate = \$totalBalls > 0 ? round((\$totalRuns / \$totalBalls) * 6, 2) : 0.00;\n\n" .
"// Partnership\n" .
"\$partnershipRuns = \$partnership['runs'];\n" .
"\$partnershipBalls = \$partnership['balls'];\n" .
"?>";

$newContent = $newPhp . "\n" . $htmlContent;
file_put_contents($file, $newContent);
echo "Updated $file successfully.";
?>
