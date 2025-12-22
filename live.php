<?php
/**
 * Live Matches Page - Premium Design (Unified)
 */

require_once __DIR__ . '/includes/bootstrap.php';

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLive();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>Live Matches - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="page-title">Live Matches</div>
                <div class="badge badge-live">LIVE</div>
            </div>
        </div>
    </header>

    <div class="container">
        
        <?php if (empty($liveMatches)): ?>
            <div class="glass-card" style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 40px; margin-bottom: 16px;">🏏</div>
                <div class="font-bold mb-2">No Live Matches</div>
                <div class="text-sm">Check back later for live action!</div>
                <a href="matches.php?filter=schedule" class="btn btn-primary mt-4">View Schedule</a>
            </div>
        <?php else: ?>
            <?php foreach ($liveMatches as $match): 
                $score = null;
                try {
                    $score = calculateMatchScore($match['match_id']);
                } catch (Exception $e) {
                    // Ignore error
                }
                $matchUrl = 'match-view.php?id=' . $match['match_id'];
            ?>
                <div class="glass-card" onclick="window.location.href='<?= $matchUrl ?>'" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="flex-between mb-4 text-xs text-muted">
                            <span><?= e($match['series_name'] ?? 'Friendly Match') ?></span>
                            <span class="text-danger font-bold">● LIVE NOW</span>
                        </div>
                        
                        <!-- Team 1 -->
                        <div class="flex-between mb-2">
                            <div class="flex-center gap-2">
                                <div class="avatar" style="width: 32px; height: 32px; font-size: 14px;"><?= substr($match['team1_name'], 0, 1) ?></div>
                                <div class="font-bold"><?= e($match['team1_name']) ?></div>
                            </div>
                            <div class="font-bold text-lg">
                                <?php
                                $t1Score = '-';
                                if ($score) {
                                    $isT1Batting1 = (getBattingTeam($match, 1) == $match['team1_id']);
                                    $s = $isT1Batting1 ? ($score['innings1'] ?? []) : ($score['innings2'] ?? []);
                                    if (!empty($s) && ($s['runs'] > 0 || $s['balls'] > 0)) {
                                        $t1Score = "{$s['runs']}/{$s['wickets']} <span class='text-sm text-muted font-normal'>(" . number_format($s['overs'], 1) . ")</span>";
                                    }
                                }
                                echo $t1Score;
                                ?>
                            </div>
                        </div>
                        
                        <!-- Team 2 -->
                        <div class="flex-between mb-4">
                            <div class="flex-center gap-2">
                                <div class="avatar" style="width: 32px; height: 32px; font-size: 14px;"><?= substr($match['team2_name'], 0, 1) ?></div>
                                <div class="font-bold"><?= e($match['team2_name']) ?></div>
                            </div>
                            <div class="font-bold text-lg">
                                <?php
                                $t2Score = '-';
                                if ($score) {
                                    $isT2Batting1 = (getBattingTeam($match, 1) == $match['team2_id']);
                                    $s = $isT2Batting1 ? ($score['innings1'] ?? []) : ($score['innings2'] ?? []);
                                    if (!empty($s) && ($s['runs'] > 0 || $s['balls'] > 0)) {
                                        $t2Score = "{$s['runs']}/{$s['wickets']} <span class='text-sm text-muted font-normal'>(" . number_format($s['overs'], 1) . ")</span>";
                                    }
                                }
                                echo $t2Score;
                                ?>
                            </div>
                        </div>
                        
                        <div class="text-center text-sm text-danger font-bold border-top pt-2" style="border-color: rgba(0,0,0,0.05);">
                            <?= e($match['status_note'] ?? 'Match in progress') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
        <a href="<?= publicUrl('live.php') ?>" class="nav-item active">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Live
        </a>
        <a href="<?= publicUrl('leaderboard.php') ?>" class="nav-item">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Stats
        </a>
    </nav>

    <script>
        // Auto-refresh every 10 seconds
        setTimeout(function() {
            window.location.reload();
        }, 10000);
    </script>

</body>
</html>


