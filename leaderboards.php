<?php
require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance()->getConnection();
$category = getQuery('category', 'runs');

// Cache configuration
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
$cacheFile = $cacheDir . '/leaderboard_' . $category . '.json';
$cacheDuration = 30; // 30 seconds cache

$leaders = [];
$isCached = false;

// Check cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheDuration)) {
    $cachedData = json_decode(file_get_contents($cacheFile), true);
    if ($cachedData) {
        $leaders = $cachedData;
        $isCached = true;
    }
}

if (!$isCached) {
    // Simplified queries
    $battingQueries = [
        'runs' => "SELECT p.name, SUM(bs.runs) as total_runs, COUNT(DISTINCT bs.match_id) as matches,
                          ROUND(SUM(bs.runs) / NULLIF(SUM(bs.balls), 0) * 100, 2) as strike_rate,
                          SUM(bs.fours) as fours, SUM(bs.sixes) as sixes
                   FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                   GROUP BY bs.player_id HAVING total_runs > 0 ORDER BY total_runs DESC LIMIT 10",
        'sixes' => "SELECT p.name, SUM(bs.sixes) as total_sixes, SUM(bs.runs) as total_runs
                    FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                    GROUP BY bs.player_id HAVING total_sixes > 0 ORDER BY total_sixes DESC LIMIT 10",
        'fours' => "SELECT p.name, SUM(bs.fours) as total_fours, SUM(bs.runs) as total_runs
                    FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                    GROUP BY bs.player_id HAVING total_fours > 0 ORDER BY total_fours DESC LIMIT 10",
    ];

    $bowlingQueries = [
        'wickets' => "SELECT p.name, SUM(bw.wickets) as total_wickets, SUM(bw.runs) as runs_conceded,
                             SUM(bw.balls) as balls_bowled, ROUND(SUM(bw.runs) / NULLIF(SUM(bw.wickets), 0), 2) as average
                      FROM bowling_stats bw JOIN players p ON bw.player_id = p.player_id
                      GROUP BY bw.player_id HAVING total_wickets > 0 ORDER BY total_wickets DESC LIMIT 10",
    ];

    $fieldingQueries = [
        'catches' => "SELECT p.name, SUM(fs.catches) as total_catches
                      FROM fielding_stats fs JOIN players p ON fs.player_id = p.player_id
                      GROUP BY fs.player_id HAVING total_catches > 0 ORDER BY total_catches DESC LIMIT 10",
    ];

    $allQueries = array_merge($battingQueries, $bowlingQueries, $fieldingQueries);
    $sql = $allQueries[$category] ?? $battingQueries['runs'];
    
    try {
        $stmt = $db->query($sql);
        $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Save to cache
        file_put_contents($cacheFile, json_encode($leaders));
    } catch (Exception $e) {
        // Fallback to empty array if query fails (e.g. table missing)
        error_log("Leaderboard query failed: " . $e->getMessage());
        $leaders = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboards - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { font-size: 2.5rem; color: #1f2937; margin: 20px 0 10px; }
        .tabs { display: flex; gap: 10px; margin: 20px 0; flex-wrap: wrap; }
        .tab { padding: 10px 20px; background: #f3f4f6; border: none; border-radius: 6px; text-decoration: none; color: #374151; font-weight: 600; }
        .tab.active { background: #2563eb; color: white; }
        .tab:hover { background: #e5e7eb; }
        .tab.active:hover { background: #2563eb; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th { background: #1f2937; color: white; padding: 15px; text-align: left; font-weight: 600; }
        td { padding: 12px 15px; border-bottom: 1px solid #e5e7eb; }
        tr:hover { background: #f9fafb; }
        .rank { font-weight: 700; font-size: 1.2rem; color: #6b7280; }
        .rank-1 { color: #fbbf24; }
        .rank-2 { color: #9ca3af; }
        .rank-3 { color: #cd7f32; }
        .player-name { font-weight: 600; color: #111827; }
        .stat-highlight { font-weight: 700; color: #2563eb; font-size: 1.1rem; }
        .section { margin: 30px 0; }
        .section h3 { color: #1f2937; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏆 Leaderboards</h1>
            <p>Top performers across all matches</p>
        </div>
        
        <div class="section">
            <h3>⚡ Batting</h3>
            <div class="tabs">
                <a href="?category=runs" class="tab <?= $category === 'runs' ? 'active' : '' ?>">Most Runs</a>
                <a href="?category=sixes" class="tab <?= $category === 'sixes' ? 'active' : '' ?>">Most Sixes</a>
                <a href="?category=fours" class="tab <?= $category === 'fours' ? 'active' : '' ?>">Most Fours</a>
            </div>
        </div>
        
        <div class="section">
            <h3>🎾 Bowling</h3>
            <div class="tabs">
                <a href="?category=wickets" class="tab <?= $category === 'wickets' ? 'active' : '' ?>">Most Wickets</a>
            </div>
        </div>
        
        <div class="section">
            <h3>🧤 Fielding</h3>
            <div class="tabs">
                <a href="?category=catches" class="tab <?= $category === 'catches' ? 'active' : '' ?>">Most Catches</a>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <?php if ($category === 'runs'): ?>
                        <th>Runs</th><th>Matches</th><th>Strike Rate</th><th>4s</th><th>6s</th>
                    <?php elseif ($category === 'sixes'): ?>
                        <th>Sixes</th><th>Total Runs</th>
                    <?php elseif ($category === 'fours'): ?>
                        <th>Fours</th><th>Total Runs</th>
                    <?php elseif ($category === 'wickets'): ?>
                        <th>Wickets</th><th>Runs</th><th>Balls</th><th>Average</th>
                    <?php elseif ($category === 'catches'): ?>
                        <th>Catches</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leaders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                            No data available yet. Play some matches to see leaderboards!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leaders as $index => $leader): ?>
                        <tr>
                            <td class="rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></td>
                            <td class="player-name"><?= e($leader['name']) ?></td>
                            <?php if ($category === 'runs'): ?>
                                <td class="stat-highlight"><?= $leader['total_runs'] ?></td>
                                <td><?= $leader['matches'] ?></td>
                                <td><?= $leader['strike_rate'] ?? '-' ?></td>
                                <td><?= $leader['fours'] ?></td>
                                <td><?= $leader['sixes'] ?></td>
                            <?php elseif ($category === 'sixes'): ?>
                                <td class="stat-highlight"><?= $leader['total_sixes'] ?></td>
                                <td><?= $leader['total_runs'] ?></td>
                            <?php elseif ($category === 'fours'): ?>
                                <td class="stat-highlight"><?= $leader['total_fours'] ?></td>
                                <td><?= $leader['total_runs'] ?></td>
                            <?php elseif ($category === 'wickets'): ?>
                                <td class="stat-highlight"><?= $leader['total_wickets'] ?></td>
                                <td><?= $leader['runs_conceded'] ?></td>
                                <td><?= $leader['balls_bowled'] ?></td>
                                <td><?= $leader['average'] ?? '-' ?></td>
                            <?php elseif ($category === 'catches'): ?>
                                <td class="stat-highlight"><?= $leader['total_catches'] ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
