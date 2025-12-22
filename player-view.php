<?php
/**
 * Player View Page - Premium Design (Unified)
 */

require_once __DIR__ . '/includes/bootstrap.php';

$playerId = (int)getQuery('id', 0);
if (!$playerId) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

$player = null;
$stats = [];
$recentMatches = [];

try {
    $playerModel = new Player();
    $player = $playerModel->getById($playerId);

    if (!$player) {
        header('Location: ' . publicUrl('index.php'));
        exit;
    }

    $db = Database::getInstance()->getConnection();

    // Get player statistics from stats_cache
    try {
        $statsSql = "SELECT 
                        COUNT(DISTINCT sc.match_id) as matches_played,
                        COALESCE(SUM(sc.runs), 0) as total_runs,
                        COALESCE(SUM(sc.balls_faced), 0) as total_balls_faced,
                        COALESCE(SUM(sc.wickets), 0) as total_wickets,
                        COALESCE(SUM(sc.overs_bowled), 0) as total_overs_bowled,
                        AVG(sc.strike_rate) as avg_strike_rate,
                        AVG(sc.economy_rate) as avg_economy_rate,
                        MAX(sc.runs) as highest_score,
                        MAX(sc.wickets) as best_bowling_wickets
                    FROM stats_cache sc
                    INNER JOIN player_appearances pa ON sc.appearance_id = pa.appearance_id
                    WHERE pa.player_id = :player_id";
        $statsStmt = $db->prepare($statsSql);
        $statsStmt->execute(['player_id' => $playerId]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error loading player stats: " . $e->getMessage());
        $stats = null;
    }

    // Ensure stats array exists with defaults
    if (!$stats) {
        $stats = [
            'matches_played' => 0,
            'total_runs' => 0,
            'total_balls_faced' => 0,
            'total_wickets' => 0,
            'total_overs_bowled' => 0,
            'avg_strike_rate' => 0,
            'avg_economy_rate' => 0,
            'highest_score' => 0,
            'best_bowling_wickets' => 0
        ];
    }

    // Get recent matches
    try {
        $matchesSql = "SELECT m.*, 
                        t1.name as team1_name, t2.name as team2_name,
                        sc.runs, sc.wickets, sc.balls_faced, sc.overs_bowled,
                        sc.strike_rate, sc.economy_rate
                      FROM matches m
                      INNER JOIN player_appearances pa ON m.match_id = pa.match_id
                      LEFT JOIN stats_cache sc ON pa.appearance_id = sc.appearance_id
                      LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                      LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                      WHERE pa.player_id = :player_id
                      ORDER BY m.match_date DESC, m.created_at DESC
                      LIMIT 10";
        $matchesStmt = $db->prepare($matchesSql);
        $matchesStmt->execute(['player_id' => $playerId]);
        $recentMatches = $matchesStmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error loading player matches: " . $e->getMessage());
        $recentMatches = [];
    }
} catch (Exception $e) {
    error_log("Error loading player view for player ID $playerId: " . $e->getMessage());
    header('Location: ' . publicUrl('index.php'));
    exit;
}

// Calculate averages
$batting_avg = $stats['matches_played'] > 0 && $stats['total_balls_faced'] > 0 
    ? round(($stats['total_runs'] / $stats['total_balls_faced']) * 100, 2) 
    : 0;
$bowling_avg = $stats['total_overs_bowled'] > 0 
    ? round($stats['total_runs'] / $stats['total_overs_bowled'], 2) 
    : 0;
    
$tab = getQuery('tab', 'overview');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($player['name']) ?> - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <a href="leaderboard.php" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="font-bold">Player Profile</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <div class="player-hero">
        <div class="player-hero-avatar">
            <?php if (!empty($player['profile_image'])): ?>
                <img src="<?= e($player['profile_image']) ?>" alt="">
            <?php else: ?>
                <?= substr($player['name'], 0, 1) ?>
            <?php endif; ?>
        </div>
        <div class="player-hero-name"><?= e($player['name']) ?></div>
        <div class="player-hero-meta">
            <?php if ($player['batting_hand']): ?>
                <span>🏏 <?= ucfirst($player['batting_hand']) ?></span>
            <?php endif; ?>
            <?php if ($player['bowling_style']): ?>
                <span>⚾ <?= ucfirst(str_replace('-', ' ', $player['bowling_style'])) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="nav-tabs-scroll">
        <a href="?id=<?= $playerId ?>&tab=overview" class="nav-tab-link <?= $tab === 'overview' ? 'active' : '' ?>">Overview</a>
        <a href="?id=<?= $playerId ?>&tab=stats" class="nav-tab-link <?= $tab === 'stats' ? 'active' : '' ?>">Stats</a>
        <a href="?id=<?= $playerId ?>&tab=matches" class="nav-tab-link <?= $tab === 'matches' ? 'active' : '' ?>">Matches</a>
    </div>

    <div class="container">
        
        <div class="glass-card">
            
            <!-- Overview Tab -->
            <?php if ($tab === 'overview'): ?>
                <div class="card-body">
                    <h3 class="font-bold mb-4 text-sm uppercase text-muted">Career Summary</h3>
                    <div class="stat-grid">
                        <div class="stat-box">
                            <div class="stat-value"><?= (int)$stats['matches_played'] ?></div>
                            <div class="stat-label">Matches</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= number_format((int)$stats['total_runs']) ?></div>
                            <div class="stat-label">Runs</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= number_format((int)$stats['total_wickets']) ?></div>
                            <div class="stat-label">Wickets</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= (int)$stats['highest_score'] ?></div>
                            <div class="stat-label">High Score</div>
                        </div>
                    </div>
                    
                    <h3 class="font-bold mt-4 mb-4 text-sm uppercase text-muted">Personal Info</h3>
                    <div class="list-item" style="padding: 12px 0;">
                        <span class="text-muted" style="flex: 1;">Born</span>
                        <span class="font-bold"><?= $player['date_of_birth'] ? formatDate($player['date_of_birth'], 'M d, Y') : 'N/A' ?></span>
                    </div>
                    <div class="list-item" style="padding: 12px 0;">
                        <span class="text-muted" style="flex: 1;">Batting Style</span>
                        <span class="font-bold"><?= ucfirst($player['batting_hand'] ?? 'Unknown') ?></span>
                    </div>
                    <div class="list-item" style="padding: 12px 0;">
                        <span class="text-muted" style="flex: 1;">Bowling Style</span>
                        <span class="font-bold"><?= ucfirst(str_replace('-', ' ', $player['bowling_style'] ?? 'Unknown')) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Stats Tab -->
            <?php if ($tab === 'stats'): ?>
                <div class="card-body">
                    <h3 class="font-bold mb-4 text-primary">Batting</h3>
                    <div class="stat-grid mb-4">
                        <div class="stat-box">
                            <div class="stat-value"><?= number_format((int)$stats['total_runs']) ?></div>
                            <div class="stat-label">Runs</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= number_format((int)$stats['total_balls_faced']) ?></div>
                            <div class="stat-label">Balls</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $stats['avg_strike_rate'] ? number_format($stats['avg_strike_rate'], 1) : '-' ?></div>
                            <div class="stat-label">Strike Rate</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $batting_avg > 0 ? number_format($batting_avg, 1) : '-' ?></div>
                            <div class="stat-label">Average</div>
                        </div>
                    </div>
                    
                    <h3 class="font-bold mb-4 text-danger">Bowling</h3>
                    <div class="stat-grid">
                        <div class="stat-box">
                            <div class="stat-value"><?= number_format((int)$stats['total_wickets']) ?></div>
                            <div class="stat-label">Wickets</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $stats['total_overs_bowled'] ? number_format($stats['total_overs_bowled'], 1) : '-' ?></div>
                            <div class="stat-label">Overs</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $stats['avg_economy_rate'] ? number_format($stats['avg_economy_rate'], 2) : '-' ?></div>
                            <div class="stat-label">Economy</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value"><?= $bowling_avg > 0 ? number_format($bowling_avg, 2) : '-' ?></div>
                            <div class="stat-label">Average</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Matches Tab -->
            <?php if ($tab === 'matches'): ?>
                <div class="card-body">
                    <?php if (empty($recentMatches)): ?>
                        <div class="text-center text-muted p-4">No recent matches found.</div>
                    <?php else: ?>
                        <?php foreach ($recentMatches as $match): ?>
                            <div class="glass-card mb-2" style="background: #f8f9fa; box-shadow: none; border: 1px solid rgba(0,0,0,0.05);" onclick="window.location.href='match-view.php?id=<?= $match['match_id'] ?>'">
                                <div class="card-body">
                                    <div class="flex-between mb-2">
                                        <span class="text-xs text-muted"><?= formatDate($match['match_date'], 'M d, Y') ?></span>
                                        <span class="badge badge-primary">Played</span>
                                    </div>
                                    <div class="font-bold mb-2 text-sm">
                                        <?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?>
                                    </div>
                                    <div class="flex-between text-sm">
                                        <span>Runs: <strong><?= (int)$match['runs'] ?></strong></span>
                                        <span>Wickets: <strong><?= (int)$match['wickets'] ?></strong></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        </div>
        
    </div>

    <!-- Bottom Nav -->
    <nav class="bottom-nav">
        <a href="<?= publicUrl('index.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Home
        </a>
        <a href="<?= publicUrl('matches.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Matches
        </a>
        <a href="<?= publicUrl('live.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Live
        </a>
        <a href="<?= publicUrl('leaderboard.php') ?>" class="nav-item active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Stats
        </a>
    </nav>

</body>
</html>

