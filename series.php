<?php
/**
 * Series List Page - Public Portal
 * 
 * Displays all cricket series
 */

require_once __DIR__ . '/includes/bootstrap.php';

$seriesModel = new Series();
$allSeries = $seriesModel->getAll();

// Group series by status
$activeSeries = [];
$completedSeries = [];
$upcomingSeries = [];

foreach ($allSeries as $series) {
    $matchModel = new MatchModel();
    $matches = $matchModel->getAll(['series_id' => $series['series_id']]);
    
    $hasLive = false;
    $allCompleted = true;
    $hasScheduled = false;
    
    foreach ($matches as $match) {
        if ($match['state'] === 'live') $hasLive = true;
        if ($match['state'] !== 'completed') $allCompleted = false;
        if ($match['state'] === 'scheduled') $hasScheduled = true;
    }
    
    $series['match_count'] = count($matches);
    $series['completed_count'] = count(array_filter($matches, fn($m) => $m['state'] === 'completed'));
    
    if ($hasLive || (!$allCompleted && !$hasScheduled && count($matches) > 0)) {
        $activeSeries[] = $series;
    } elseif ($allCompleted && count($matches) > 0) {
        $completedSeries[] = $series;
    } else {
        $upcomingSeries[] = $series;
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
    <title>Series - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="page-title">🏆 Series</div>
            </div>
        </div>
    </header>

    <div class="container">
        
        <!-- Active Series -->
        <?php if (!empty($activeSeries)): ?>
            <div class="section-title" style="margin-top: 0;">
                <span>Active Series</span>
                <span class="badge badge-live">LIVE</span>
            </div>
            
            <?php foreach ($activeSeries as $series): ?>
                <a href="<?= publicUrl('series-view.php?id=' . $series['series_id']) ?>" style="text-decoration: none; color: inherit;">
                    <div class="glass-card" style="margin-bottom: 16px;">
                        <div class="card-body">
                            <h3 style="margin: 0 0 8px 0; font-size: 1.125rem; color: var(--primary);">
                                <?= e($series['name']) ?>
                            </h3>
                            <?php if (!empty($series['description'])): ?>
                                <p style="margin: 0 0 12px 0; color: var(--text-muted); font-size: 0.875rem;">
                                    <?= e($series['description']) ?>
                                </p>
                            <?php endif; ?>
                            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                <div style="font-size: 0.875rem; color: var(--text-muted);">
                                    📊 <?= $series['match_count'] ?> matches
                                </div>
                                <div style="font-size: 0.875rem; color: var(--text-muted);">
                                    ✅ <?= $series['completed_count'] ?> completed
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Upcoming Series -->
        <?php if (!empty($upcomingSeries)): ?>
            <div class="section-title">
                <span>Upcoming Series</span>
            </div>
            
            <?php foreach ($upcomingSeries as $series): ?>
                <a href="<?= publicUrl('series-view.php?id=' . $series['series_id']) ?>" style="text-decoration: none; color: inherit;">
                    <div class="glass-card" style="margin-bottom: 16px;">
                        <div class="card-body">
                            <h3 style="margin: 0 0 8px 0; font-size: 1.125rem;">
                                <?= e($series['name']) ?>
                            </h3>
                            <?php if (!empty($series['description'])): ?>
                                <p style="margin: 0 0 12px 0; color: var(--text-muted); font-size: 0.875rem;">
                                    <?= e($series['description']) ?>
                                </p>
                            <?php endif; ?>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">
                                📊 <?= $series['match_count'] ?> matches scheduled
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Completed Series -->
        <?php if (!empty($completedSeries)): ?>
            <div class="section-title">
                <span>Completed Series</span>
            </div>
            
            <?php foreach ($completedSeries as $series): ?>
                <a href="<?= publicUrl('series-view.php?id=' . $series['series_id']) ?>" style="text-decoration: none; color: inherit;">
                    <div class="glass-card" style="margin-bottom: 16px;">
                        <div class="card-body">
                            <h3 style="margin: 0 0 8px 0; font-size: 1.125rem;">
                                <?= e($series['name']) ?>
                            </h3>
                            <?php if (!empty($series['description'])): ?>
                                <p style="margin: 0 0 12px 0; color: var(--text-muted); font-size: 0.875rem;">
                                    <?= e($series['description']) ?>
                                </p>
                            <?php endif; ?>
                            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                <div style="font-size: 0.875rem; color: var(--text-muted);">
                                    📊 <?= $series['match_count'] ?> matches
                                </div>
                                <div style="font-size: 0.875rem; color: var(--success);">
                                    🏆 View POTS
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (empty($allSeries)): ?>
            <div class="glass-card" style="padding: 48px; text-align: center; color: var(--text-muted);">
                <div style="font-size: 3rem; margin-bottom: 16px;">🏆</div>
                <div style="font-size: 1.125rem; font-weight: 600; margin-bottom: 8px;">No Series Found</div>
                <div style="font-size: 0.875rem;">Series will appear here once they are created</div>
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
