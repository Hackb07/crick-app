<?php
/**
 * Series View Page - Public Portal (Premium Design)
 */

require_once __DIR__ . '/includes/bootstrap.php';

$seriesId = (int)getQuery('id', 0);
if (!$seriesId) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

$seriesModel = new Series();
$series = $seriesModel->getById($seriesId);

if (!$series) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

$matchModel = new MatchModel();
$matches = $matchModel->getAll(['series_id' => $seriesId]);

// Get POTS rankings if available
$pots = new POTS();
$rankings = $pots->getRankings($seriesId);

// Calculate series stats
$completedMatches = array_filter($matches, function($m) {
    return $m['state'] === 'completed';
});
$liveMatches = array_filter($matches, function($m) {
    return $m['state'] === 'live';
});
$scheduledMatches = array_filter($matches, function($m) {
    return $m['state'] === 'scheduled';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title><?= e($series['name']) ?> - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        .hero-banner {
            background: linear-gradient(135deg, var(--primary) 0%, #00b894 100%);
            color: white;
            padding: 24px;
            border-radius: 0 0 24px 24px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px -10px rgba(0, 146, 112, 0.5);
        }
        
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 20px;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 12px;
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 11px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    <?php require_once __DIR__ . '/includes/nav-menu.php'; ?>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="flex-center gap-3">
                    <a href="<?= publicUrl('series.php') ?>" style="color: white;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <div class="page-title">Series Details</div>
                </div>
            </div>
        </div>
    </header>

    <div class="hero-banner">
        <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;"><?= e($series['name']) ?></h1>
        <p style="opacity: 0.9; font-size: 14px; line-height: 1.4;"><?= e($series['description'] ?? 'Tournament details') ?></p>
        
        <div class="stat-grid">
            <div class="stat-item">
                <div class="stat-value"><?= count($matches) ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= count($completedMatches) ?></div>
                <div class="stat-label">Done</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= count($liveMatches) ?></div>
                <div class="stat-label">Live</div>
            </div>
        </div>
    </div>

    <div class="container">
        
        <!-- Player of the Series -->
        <?php if (!empty($rankings)): ?>
            <div class="flex-between mb-4">
                <h2 class="text-lg font-bold">Top Players</h2>
            </div>
            
            <div class="glass-card mb-6">
                <div class="card-body">
                    <?php foreach (array_slice($rankings, 0, 3) as $index => $ranking): ?>
                        <div class="flex-between mb-3 last:mb-0" style="padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; last-of-type:border-bottom:0;">
                            <div class="flex-center gap-3">
                                <div style="width: 24px; font-weight: 700; color: var(--primary); font-size: 14px;">#<?= $ranking['rank'] ?></div>
                                <div>
                                    <div class="font-bold text-sm"><?= e($ranking['player_name']) ?></div>
                                    <div class="text-xs text-muted"><?= e($ranking['team_name']) ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-primary"><?= (int)$ranking['total_points'] ?></div>
                                <div class="text-xs text-muted">pts</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Matches List -->
        <h2 class="text-lg font-bold mb-4">Matches</h2>

        <?php if (empty($matches)): ?>
            <div class="glass-card" style="padding: 40px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 32px; margin-bottom: 12px;">📅</div>
                No matches scheduled yet.
            </div>
        <?php else: ?>
            <div class="grid gap-3 mb-6">
                <?php foreach ($matches as $match): 
                     $score = null;
                     if ($match['state'] !== 'scheduled') {
                         try { $score = calculateMatchScore($match['match_id']); } catch(Exception $e){}
                     }
                     
                     // Format Date Check
                     $dateStr = 'Date TBD';
                     $venueStr = 'Venue TBD';
                     if (!empty($match['match_date']) && strpos($match['match_date'], '-0001') === false) {
                         $dateStr = formatDate($match['match_date'], 'M d, h:i A');
                     }
                     if (!empty($match['venue'])) {
                         $venueStr = $match['venue'];
                     }
                ?>
                    <div class="glass-card" onclick="window.location.href='match-view.php?id=<?= $match['match_id'] ?>'">
                        <div class="card-body">
                            <div class="flex-between mb-2 text-xs text-muted">
                                <span><?= $dateStr ?></span>
                                <?php if ($match['state'] === 'live'): ?>
                                    <span class="badge badge-live">LIVE</span>
                                <?php elseif ($match['state'] === 'completed'): ?>
                                    <span class="badge badge-completed">RESULT</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #f1f5f9; color: #64748b;">UPCOMING</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Team 1 -->
                            <div class="flex-between mb-2">
                                <div class="flex-center gap-2">
                                    <div class="avatar" style="width: 24px; height: 24px; font-size: 10px;"><?= substr($match['team1_name'], 0, 1) ?></div>
                                    <div class="font-bold text-sm"><?= e($match['team1_name']) ?></div>
                                </div>
                                <div class="font-bold text-sm">
                                    <?php
                                    $t1Score = '';
                                    if ($score) {
                                        $isT1Batting1 = (getBattingTeam($match, 1) == $match['team1_id']);
                                        $s = $isT1Batting1 ? ($score['innings1'] ?? []) : ($score['innings2'] ?? []);
                                        if (!empty($s)) $t1Score = "{$s['runs']}/{$s['wickets']} (" . number_format($s['overs'], 1) . ")";
                                    }
                                    echo $t1Score;
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Team 2 -->
                            <div class="flex-between mb-2">
                                <div class="flex-center gap-2">
                                    <div class="avatar" style="width: 24px; height: 24px; font-size: 10px;"><?= substr($match['team2_name'], 0, 1) ?></div>
                                    <div class="font-bold text-sm"><?= e($match['team2_name']) ?></div>
                                </div>
                                <div class="font-bold text-sm">
                                    <?php
                                    $t2Score = '';
                                    if ($score) {
                                        $isT2Batting1 = (getBattingTeam($match, 1) == $match['team2_id']);
                                        $s = $isT2Batting1 ? ($score['innings1'] ?? []) : ($score['innings2'] ?? []);
                                        if (!empty($s)) $t2Score = "{$s['runs']}/{$s['wickets']} (" . number_format($s['overs'], 1) . ")";
                                    }
                                    echo $t2Score;
                                    ?>
                                </div>
                            </div>
                            
                            <div class="flex-between mt-2">
                                <div class="text-xs text-muted">📍 <?= e($venueStr) ?></div>
                                <div class="text-xs text-primary font-bold">
                                    <?php if ($match['state'] === 'completed' && isset($score['winner_name'])): ?>
                                        <?= e($score['winner_name']) ?> won
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
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
        <a href="<?= publicUrl('series.php') ?>" class="nav-item active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            Series
        </a>
        <a href="<?= publicUrl('leaderboard.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Stats
        </a>
    </nav>

</body>
</html>
