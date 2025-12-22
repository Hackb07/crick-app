<?php
require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance()->getConnection();
$category = getQuery('category', 'runs');

// Batting Leaderboards
$battingQueries = [
    'runs' => "SELECT p.name, p.photo_url, SUM(bs.runs) as total_runs, COUNT(DISTINCT bs.match_id) as matches,
                      ROUND(SUM(bs.runs) / NULLIF(SUM(bs.balls), 0) * 100, 2) as strike_rate,
                      SUM(bs.fours) as fours, SUM(bs.sixes) as sixes
               FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
               GROUP BY bs.player_id HAVING total_runs > 0 ORDER BY total_runs DESC LIMIT 10",
    'sixes' => "SELECT p.name, p.photo_url, SUM(bs.sixes) as total_sixes, SUM(bs.runs) as total_runs
                FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                GROUP BY bs.player_id HAVING total_sixes > 0 ORDER BY total_sixes DESC LIMIT 10",
    'fours' => "SELECT p.name, p.photo_url, SUM(bs.fours) as total_fours, SUM(bs.runs) as total_runs
                FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                GROUP BY bs.player_id HAVING total_fours > 0 ORDER BY total_fours DESC LIMIT 10",
    'thirties' => "SELECT p.name, p.photo_url, COUNT(*) as thirties, SUM(bs.runs) as total_runs
                   FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                   WHERE bs.runs >= 30 AND bs.runs < 50
                   GROUP BY bs.player_id ORDER BY thirties DESC LIMIT 10",
    'twenties' => "SELECT p.name, p.photo_url, COUNT(*) as twenties, SUM(bs.runs) as total_runs
                   FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
                   WHERE bs.runs >= 20 AND bs.runs < 30
                   GROUP BY bs.player_id ORDER BY twenties DESC LIMIT 10",
    'tens' => "SELECT p.name, p.photo_url, COUNT(*) as tens, SUM(bs.runs) as total_runs
               FROM batting_stats bs JOIN players p ON bs.player_id = p.player_id
               WHERE bs.runs >= 10 AND bs.runs < 20
               GROUP BY bs.player_id ORDER BY tens DESC LIMIT 10"
];

// Bowling Leaderboards
$bowlingQueries = [
    'wickets' => "SELECT p.name, p.photo_url, SUM(bw.wickets) as total_wickets, SUM(bw.runs) as runs_conceded,
                         SUM(bw.balls) as balls_bowled, ROUND(SUM(bw.runs) / NULLIF(SUM(bw.wickets), 0), 2) as average,
                         ROUND(SUM(bw.runs) / (SUM(bw.balls) / 6), 2) as economy
                  FROM bowling_stats bw JOIN players p ON bw.player_id = p.player_id
                  GROUP BY bw.player_id HAVING total_wickets > 0 ORDER BY total_wickets DESC LIMIT 10",
    'economy' => "SELECT p.name, p.photo_url, SUM(bw.wickets) as total_wickets,
                         ROUND(SUM(bw.runs) / (SUM(bw.balls) / 6), 2) as economy
                  FROM bowling_stats bw JOIN players p ON bw.player_id = p.player_id
                  WHERE bw.balls >= 12 GROUP BY bw.player_id ORDER BY economy ASC LIMIT 10"
];

// Fielding Leaderboards
$fieldingQueries = [
    'catches' => "SELECT p.name, p.photo_url, SUM(fs.catches) as total_catches
                  FROM fielding_stats fs JOIN players p ON fs.player_id = p.player_id
                  GROUP BY fs.player_id HAVING total_catches > 0 ORDER BY total_catches DESC LIMIT 10",
    'runouts' => "SELECT p.name, p.photo_url, SUM(fs.run_outs) as total_runouts
                  FROM fielding_stats fs JOIN players p ON fs.player_id = p.player_id
                  GROUP BY fs.player_id HAVING total_runouts > 0 ORDER BY total_runouts DESC LIMIT 10",
    'stumpings' => "SELECT p.name, p.photo_url, SUM(fs.stumpings) as total_stumpings
                    FROM fielding_stats fs JOIN players p ON fs.player_id = p.player_id
                    GROUP BY fs.player_id HAVING total_stumpings > 0 ORDER BY total_stumpings DESC LIMIT 10"
];

$allQueries = array_merge($battingQueries, $bowlingQueries, $fieldingQueries);
$sql = $allQueries[$category] ?? $battingQueries['runs'];
$stmt = $db->query($sql);
$leaders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>Leaderboards - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        /* Ensure body has enough padding for fixed nav */
        body {
            padding-bottom: 100px;
        }
        
        /* Ensure bottom nav is above everything */
        .bottom-nav {
            z-index: 1000;
            background: white; /* Ensure opacity */
        }

        /* Mobile optimizations for leaderboard table */
        @media (max-width: 600px) {
            .premium-table-primary th, 
            .premium-table-primary td {
                padding: 8px 4px !important;
                font-size: 11px !important;
            }
            .premium-table-primary th {
                font-size: 10px !important;
            }
            .player-info {
                gap: 6px;
            }
            .player-avatar {
                width: 24px;
                height: 24px;
                font-size: 10px;
                margin-right: 0;
            }
            .player-name {
                max-width: 70px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                display: block;
            }
            .rank {
                font-size: 12px;
            }
            .stat-highlight {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="page-title">🏆 Leaderboards</div>
            </div>
        </div>
    </header>

    <div class="container">
        
        <!-- Main Category Selector (Segmented Control) -->
        <?php 
        $mainCat = 'batting';
        if (array_key_exists($category, $bowlingQueries)) $mainCat = 'bowling';
        if (array_key_exists($category, $fieldingQueries)) $mainCat = 'fielding';
        ?>
        <div class="category-selector">
            <a href="?category=runs" class="cat-btn <?= $mainCat === 'batting' ? 'active' : '' ?>">Batting</a>
            <a href="?category=wickets" class="cat-btn <?= $mainCat === 'bowling' ? 'active' : '' ?>">Bowling</a>
            <a href="?category=catches" class="cat-btn <?= $mainCat === 'fielding' ? 'active' : '' ?>">Fielding</a>
        </div>

        <!-- Sub Category Pills -->
        <div class="pills-scroll">
            <?php if ($mainCat === 'batting'): ?>
                <a href="?category=runs" class="stat-pill <?= $category === 'runs' ? 'active' : '' ?>">Most Runs</a>
                <a href="?category=sixes" class="stat-pill <?= $category === 'sixes' ? 'active' : '' ?>">Most Sixes</a>
                <a href="?category=fours" class="stat-pill <?= $category === 'fours' ? 'active' : '' ?>">Most Fours</a>
                <a href="?category=thirties" class="stat-pill <?= $category === 'thirties' ? 'active' : '' ?>">Most 30s</a>
                <a href="?category=twenties" class="stat-pill <?= $category === 'twenties' ? 'active' : '' ?>">Most 20s</a>
                <a href="?category=tens" class="stat-pill <?= $category === 'tens' ? 'active' : '' ?>">Most 10s</a>
            <?php elseif ($mainCat === 'bowling'): ?>
                <a href="?category=wickets" class="stat-pill <?= $category === 'wickets' ? 'active' : '' ?>">Most Wickets</a>
                <a href="?category=economy" class="stat-pill <?= $category === 'economy' ? 'active' : '' ?>">Best Economy</a>
            <?php elseif ($mainCat === 'fielding'): ?>
                <a href="?category=catches" class="stat-pill <?= $category === 'catches' ? 'active' : '' ?>">Most Catches</a>
                <a href="?category=runouts" class="stat-pill <?= $category === 'runouts' ? 'active' : '' ?>">Most Run Outs</a>
                <a href="?category=stumpings" class="stat-pill <?= $category === 'stumpings' ? 'active' : '' ?>">Most Stumpings</a>
            <?php endif; ?>
        </div>
        
        <!-- Leaderboard Table with Responsive Wrapper -->
        <div class="table-responsive">
            <table class="premium-table-primary">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Player</th>
                        <?php if ($category === 'runs'): ?>
                            <th style="text-align: right;">Runs</th>
                            <th style="text-align: right;">Mat</th>
                            <th style="text-align: right;">SR</th>
                            <th style="text-align: right;">4s</th>
                            <th style="text-align: right;">6s</th>
                        <?php elseif ($category === 'sixes'): ?>
                            <th style="text-align: right;">Sixes</th>
                            <th style="text-align: right;">Runs</th>
                        <?php elseif ($category === 'fours'): ?>
                            <th style="text-align: right;">Fours</th>
                            <th style="text-align: right;">Runs</th>
                        <?php elseif (in_array($category, ['thirties', 'twenties', 'tens'])): ?>
                            <th style="text-align: right;">Count</th>
                            <th style="text-align: right;">Runs</th>
                        <?php elseif ($category === 'wickets'): ?>
                            <th style="text-align: right;">Wkts</th>
                            <th style="text-align: right;">Econ</th>
                            <th style="text-align: right;">Avg</th>
                        <?php elseif ($category === 'economy'): ?>
                            <th style="text-align: right;">Econ</th>
                            <th style="text-align: right;">Wkts</th>
                        <?php elseif ($category === 'catches'): ?>
                            <th style="text-align: right;">Catches</th>
                        <?php elseif ($category === 'runouts'): ?>
                            <th style="text-align: right;">Run Outs</th>
                        <?php elseif ($category === 'stumpings'): ?>
                            <th style="text-align: right;">Stumpings</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaders)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <div style="font-size: 40px; margin-bottom: 10px;">📊</div>
                                No data available yet.<br>Play matches to see stats!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaders as $index => $leader): ?>
                            <tr>
                                <td class="rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></td>
                                <td>
                                    <div class="player-info">
                                        <?php if (!empty($leader['photo_url'])): ?>
                                            <img src="<?= e($leader['photo_url']) ?>" class="player-avatar">
                                        <?php else: ?>
                                            <div class="player-avatar">
                                                <?= strtoupper(substr($leader['name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="player-name"><?= e($leader['name']) ?></span>
                                    </div>
                                </td>
                                <?php if ($category === 'runs'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['total_runs'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['matches'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['strike_rate'] ?? '-' ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['fours'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['sixes'] ?></td>
                                <?php elseif ($category === 'sixes'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['total_sixes'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['total_runs'] ?></td>
                                <?php elseif ($category === 'fours'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['total_fours'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['total_runs'] ?></td>
                                <?php elseif ($category === 'thirties'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['thirties'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['total_runs'] ?></td>
                                <?php elseif ($category === 'twenties'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['twenties'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['total_runs'] ?></td>
                                <?php elseif ($category === 'tens'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['tens'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['total_runs'] ?></td>
                                <?php elseif ($category === 'wickets'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['total_wickets'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['economy'] ?? '-' ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['average'] ?? '-' ?></td>
                                <?php elseif ($category === 'economy'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['economy'] ?></td>
                                    <td style="text-align: right; color: var(--text-muted);"><?= $leader['total_wickets'] ?></td>
                                <?php elseif ($category === 'catches'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['total_catches'] ?></td>
                                <?php elseif ($category === 'runouts'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['total_runouts'] ?></td>
                                <?php elseif ($category === 'stumpings'): ?>
                                    <td class="stat-highlight" style="text-align: right;"><?= $leader['total_stumpings'] ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>

    <!-- Bottom Nav using Centralized Classes -->
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
