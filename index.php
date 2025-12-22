<?php
/**
 * Public Portal Home Page - Premium Design (Unified)
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Initialize with error handling
$liveMatches = [];
$recentMatches = [];
$upcomingMatches = [];
$featuredLiveMatch = null;
$featuredLiveScore = null;

try {
    $matchModel = new MatchModel();
    $liveMatches = $matchModel->getLive() ?? [];
    $recentMatches = $matchModel->getAll(['state' => 'completed']) ?? [];
    $recentMatches = array_slice($recentMatches, 0, 3);
    $upcomingMatches = $matchModel->getAll(['state' => 'scheduled']) ?? [];
    $upcomingMatches = array_slice($upcomingMatches, 0, 10);

    // Get first live match score (if any)
    if (!empty($liveMatches)) {
        $featuredLiveMatch = $liveMatches[0];
        try {
            $featuredLiveScore = calculateMatchScore($featuredLiveMatch['match_id']);
            error_log("Featured live match {$featuredLiveMatch['match_id']} score: " . json_encode($featuredLiveScore));
        } catch (Exception $e) {
            error_log("Error calculating match score: " . $e->getMessage());
            $featuredLiveScore = null;
        }
    }
} catch (Exception $e) {
    error_log("Error loading index page data: " . $e->getMessage());
}

// Calculate scores for completed matches
$completedMatchesWithScores = [];
foreach ($recentMatches as $match) {
    try {
        $score = calculateMatchScore($match['match_id']);
        $completedMatchesWithScores[] = [
            'match' => $match,
            'score' => $score
        ];
    } catch (Exception $e) {
        error_log("Error calculating score for match {$match['match_id']}: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>CricApp - Live Cricket Scores</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin: 24px 0 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-main);
        }
        .view-all {
            font-size: 12px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .horizontal-scroll {
            display: flex;
            overflow-x: auto;
            gap: 12px;
            padding-bottom: 4px;
            margin: 0 -16px;
            padding: 0 16px 16px;
            scrollbar-width: none;
        }
        
        .match-card-mini {
            min-width: 280px;
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .score-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .team-name { font-weight: 600; font-size: 14px; color: var(--text-main); }
        .team-score { font-weight: 700; font-size: 14px; color: var(--text-main); }
        
        .match-status-text {
            font-size: 11px;
            color: var(--danger);
            font-weight: 600;
            margin-top: 8px;
        }
        
        /* Featured Match Card (Dark) */
        .featured-match {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            border: none;
        }
        .featured-match .team-name, .featured-match .team-score { color: white; }
        .featured-match .text-muted { color: rgba(255,255,255,0.6); }
    </style>
</head>
<body>

    <?php require_once __DIR__ . '/includes/nav-menu.php'; ?>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="page-title">🏏 CricApp</div>
            </div>
        </div>
    </header>


    <div class="container">
        
        <!-- Live Matches -->
        <?php if (!empty($liveMatches)): ?>
            <div class="section-title" style="margin-top: 0;">
                <span>Live Matches</span>
                <span class="badge badge-live">LIVE</span>
            </div>
            
            <div class="horizontal-scroll">
                <?php foreach ($liveMatches as $match): 
                    $score = null;
                    if ($match['match_id'] == $featuredLiveMatch['match_id']) {
                        $score = $featuredLiveScore;
                    } else {
                        try { $score = calculateMatchScore($match['match_id']); } catch(Exception $e){}
                    }
                    $matchUrl = 'match-view.php?id=' . $match['match_id'];
                ?>
                    <div class="match-card-mini featured-match" onclick="window.location.href='<?= $matchUrl ?>'">
                        <div class="flex-between mb-4 text-xs text-muted">
                            <?php if (!empty($match['series_id'])): ?>
                                <a href="<?= publicUrl('series-view.php?id=' . $match['series_id']) ?>" 
                                   onclick="event.stopPropagation();" 
                                   style="color: rgba(255,255,255,0.9); text-decoration: underline;">
                                    <?= e($match['series_name'] ?? 'Friendly Match') ?>
                                </a>
                            <?php else: ?>
                                <span><?= e($match['series_name'] ?? 'Friendly Match') ?></span>
                            <?php endif; ?>
                            <span class="text-danger font-bold">● LIVE</span>
                        </div>
                        
                        <!-- Team 1 -->
                        <div class="score-row">
                            <div class="team-name">
                                <span class="avatar" style="width: 20px; height: 20px; font-size: 10px; display: inline-flex; margin-right: 8px; vertical-align: middle; background: rgba(255,255,255,0.2); color: white;"><?= substr($match['team1_name'], 0, 1) ?></span>
                                <?= e($match['team1_short_name'] ?: substr($match['team1_name'], 0, 3)) ?>
                            </div>
                            <div class="team-score">
                                <?php
                                $t1Score = '-';
                                if ($score) {
                                    $isT1Batting1 = (getBattingTeam($match, 1) == $match['team1_id']);
                                    $s = $isT1Batting1 ? ($score['innings1'] ?? []) : ($score['innings2'] ?? []);
                                    if (!empty($s) && ($s['runs'] > 0 || $s['balls'] > 0)) {
                                        $t1Score = "{$s['runs']}/{$s['wickets']} <span style='font-weight:400; font-size:11px; opacity:0.7'>(" . number_format($s['overs'], 1) . ")</span>";
                                    }
                                }
                                echo $t1Score;
                                ?>
                            </div>
                        </div>
                        
                        <!-- Team 2 -->
                        <div class="score-row">
                            <div class="team-name">
                                <span class="avatar" style="width: 20px; height: 20px; font-size: 10px; display: inline-flex; margin-right: 8px; vertical-align: middle; background: rgba(255,255,255,0.2); color: white;"><?= substr($match['team2_name'], 0, 1) ?></span>
                                <?= e($match['team2_short_name'] ?: substr($match['team2_name'], 0, 3)) ?>
                            </div>
                            <div class="team-score">
                                <?php
                                $t2Score = '-';
                                if ($score) {
                                    $isT2Batting1 = (getBattingTeam($match, 1) == $match['team2_id']);
                                    $s = $isT2Batting1 ? ($score['innings1'] ?? []) : ($score['innings2'] ?? []);
                                    if (!empty($s) && ($s['runs'] > 0 || $s['balls'] > 0)) {
                                        $t2Score = "{$s['runs']}/{$s['wickets']} <span style='font-weight:400; font-size:11px; opacity:0.7'>(" . number_format($s['overs'], 1) . ")</span>";
                                    }
                                }
                                echo $t2Score;
                                ?>
                            </div>
                        </div>
                        
                        <div class="match-status-text" style="color: #fbbf24;">
                            <?= e($match['status_note'] ?? 'Match in progress') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Recent Results -->
        <div class="section-title">
            <span>Recent Results</span>
            <a href="matches.php?filter=results" class="view-all">View All</a>
        </div>
        
        <?php if (empty($completedMatchesWithScores)): ?>
            <div class="glass-card" style="padding: 32px; text-align: center; color: var(--text-muted);">
                No recent matches found.
            </div>
        <?php else: ?>
            <?php foreach ($completedMatchesWithScores as $item): 
                $match = $item['match'];
                $score = $item['score'];
                $matchUrl = 'match-view.php?id=' . $match['match_id'];
            ?>
                <div class="glass-card" onclick="window.location.href='<?= $matchUrl ?>'">
                    <div class="card-body">
                        <div class="flex-between mb-2 text-xs text-muted">
                            <span><?= formatDate($match['match_date'], 'M d') ?></span>
                            <span><?= e($match['series_name'] ?? 'Match') ?></span>
                        </div>
                        
                        <div class="score-row">
                            <div class="team-name"><?= e($match['team1_name']) ?></div>
                            <div class="team-score">
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
                        
                        <div class="score-row">
                            <div class="team-name"><?= e($match['team2_name']) ?></div>
                            <div class="team-score">
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
                        
                        <div class="text-xs text-primary font-bold mt-2">
                            <?= e($score['winner_name'] ?? '') ?> won
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Upcoming -->
        <div class="section-title">
            <span>Upcoming</span>
            <a href="matches.php?filter=schedule" class="view-all">View All</a>
        </div>
        
        <?php if (empty($upcomingMatches)): ?>
            <div class="glass-card" style="padding: 32px; text-align: center; color: var(--text-muted);">
                No upcoming matches scheduled.
            </div>
        <?php else: ?>
            <div class="horizontal-scroll">
                <?php foreach ($upcomingMatches as $match): ?>
                    <div class="match-card-mini" style="min-width: 240px;">
                        <div class="text-xs text-muted mb-2"><?= formatDate($match['match_date'], 'M d, h:i A') ?></div>
                        <div class="font-bold mb-1 text-sm"><?= e($match['team1_name']) ?></div>
                        <div class="text-xs text-muted mb-1">vs</div>
                        <div class="font-bold mb-2 text-sm"><?= e($match['team2_name']) ?></div>
                        <div class="text-xs text-muted"><?= e($match['venue'] ?? 'Venue TBD') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    </div>

    <!-- Bottom Nav -->
    <nav class="bottom-nav">
        <a href="<?= publicUrl('index.php') ?>" class="nav-item active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Home
        </a>
        <a href="<?= publicUrl('matches.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Matches
        </a>
        <a href="<?= publicUrl('series.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
            Series
        </a>
        <a href="<?= publicUrl('leaderboard.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Stats
        </a>
    </nav>

    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/cricapp/service-worker.js')
                    .then(registration => {
                        console.log('ServiceWorker registered:', registration.scope);
                    })
                    .catch(error => {
                        console.log('ServiceWorker registration failed:', error);
                    });
            });
        }
    </script>

</body>
</html>


