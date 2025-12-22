<?php
/**
 * Matches Page - Premium Design (Unified)
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/classes/Series.php';

$filter = getQuery('filter', 'all'); // all, live, results, schedule, series
$seriesId = (int)getQuery('series_id', 0);

$matchModel = new MatchModel();
$seriesModel = new Series();
$allSeries = $seriesModel->getAll();
$matches = [];

try {
    $params = [];
    if ($filter === 'live') $params['state'] = 'live';
    elseif ($filter === 'results') $params['state'] = 'completed';
    elseif ($filter === 'schedule') $params['state'] = 'scheduled';
    
    if ($seriesId) $params['series_id'] = $seriesId;
    
    $matches = $matchModel->getAll($params);
} catch (Exception $e) {
    error_log("Error loading matches: " . $e->getMessage());
}

// Group by Series
$groupedMatches = [];
foreach ($matches as $match) {
    $sName = $match['series_name'] ?? 'Other Matches';
    $groupedMatches[$sName][] = $match;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>Matches - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="page-title">Matches</div>
            </div>
        </div>
    </header>

    <!-- Filter Tabs -->
    <div class="nav-tabs-scroll">
        <a href="?filter=all" class="nav-tab-link <?= $filter === 'all' ? 'active' : '' ?>">All</a>
        <a href="?filter=live" class="nav-tab-link <?= $filter === 'live' ? 'active' : '' ?>">Live</a>
        <a href="?filter=results" class="nav-tab-link <?= $filter === 'results' ? 'active' : '' ?>">Results</a>
        <a href="?filter=schedule" class="nav-tab-link <?= $filter === 'schedule' ? 'active' : '' ?>">Upcoming</a>
        <a href="?filter=series" class="nav-tab-link <?= $filter === 'series' ? 'active' : '' ?>">Series</a>
    </div>
    
    <!-- Series Filter Dropdown -->
    <?php if ($filter === 'series'): ?>
        <div style="padding: 16px; background: white; border-bottom: 1px solid #f1f5f9;">
            <select id="series-filter" onchange="window.location.href='?filter=series&series_id=' + this.value" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; background: white;">
                <option value="">Select a Series...</option>
                <?php foreach ($allSeries as $series): ?>
                    <option value="<?= $series['series_id'] ?>" <?= $seriesId == $series['series_id'] ? 'selected' : '' ?>>
                        <?= e($series['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="container">
        
        <?php if (empty($groupedMatches)): ?>
            <div class="glass-card" style="text-align: center; padding: 40px; color: var(--text-muted);">
                <div style="font-size: 32px; margin-bottom: 8px;">🏏</div>
                No matches found.
            </div>
        <?php else: ?>
            <?php foreach ($groupedMatches as $seriesName => $seriesMatches): ?>
                <div class="mb-4">
                    <?php 
                    // Find series ID for this series name
                    $currentSeriesId = null;
                    foreach ($seriesMatches as $m) {
                        if (!empty($m['series_id'])) {
                            $currentSeriesId = $m['series_id'];
                            break;
                        }
                    }
                    ?>
                    <?php if ($currentSeriesId): ?>
                        <a href="<?= publicUrl('series-view.php?id=' . $currentSeriesId) ?>" style="text-decoration: none;">
                            <div class="font-bold text-primary text-xs uppercase mb-2" style="padding-left: 4px; display: flex; align-items: center; gap: 4px;">
                                🏆 <?= e($seriesName) ?>
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </div>
                        </a>
                    <?php else: ?>
                        <div class="font-bold text-muted text-xs uppercase mb-2" style="padding-left: 4px;"><?= e($seriesName) ?></div>
                    <?php endif; ?>
                    
                    <?php foreach ($seriesMatches as $match): 
                        $score = null;
                        if ($match['state'] !== 'scheduled') {
                            try { $score = calculateMatchScore($match['match_id']); } catch(Exception $e){}
                        }
                        $matchUrl = 'match-view.php?id=' . $match['match_id'];
                    ?>
                        <div class="glass-card" onclick="window.location.href='<?= $matchUrl ?>'">
                            <div class="card-body">
                                <div class="flex-between mb-2 text-xs text-muted">
                                    <span><?= formatDate($match['match_date'], 'M d, h:i A') ?></span>
                                    <?php if ($match['state'] === 'live'): ?>
                                        <span class="badge badge-live">LIVE</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: var(--text-muted);"><?= strtoupper($match['state']) ?></span>
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
                                
                                <div class="text-xs text-danger font-bold mt-2">
                                    <?= e($match['status_note'] ?? '') ?>
                                    <?php if ($match['state'] === 'completed' && isset($score['winner_name'])): ?>
                                        <?= e($score['winner_name']) ?> won
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
        <a href="<?= publicUrl('matches.php') ?>" class="nav-item active">
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

</body>
</html>


