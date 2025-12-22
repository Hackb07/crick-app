<?php
/**
 * Teams Ranking Page - Premium Design (Unified)
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/utils.php';

$seriesId = (int)getQuery('series_id', 0);
$sortBy = getQuery('sort_by', 'count'); // count, name

$db = Database::getInstance()->getConnection();
$allTeams = [];
$teamStats = [];
$allSeries = [];

try {
    $teamModel = new Team();
    $allTeams = $teamModel->getAll() ?? [];
} catch (Exception $e) {
    error_log("Error loading teams: " . $e->getMessage());
    $allTeams = [];
}

// Calculate team statistics
try {

foreach ($allTeams as $team) {
    $sql = "SELECT COUNT(DISTINCT m.match_id) as total_matches,
            SUM(CASE WHEN m.state = 'completed' AND 
                ((m.team1_id = :team_id AND (SELECT calculateMatchScore(m.match_id) WHERE winner_id = m.team1_id)) OR
                 (m.team2_id = :team_id AND (SELECT calculateMatchScore(m.match_id) WHERE winner_id = m.team2_id))) 
                THEN 1 ELSE 0 END) as wins,
            SUM(CASE WHEN m.state = 'completed' AND 
                ((m.team1_id = :team_id AND (SELECT calculateMatchScore(m.match_id) WHERE winner_id != m.team1_id AND winner_id IS NOT NULL)) OR
                 (m.team2_id = :team_id AND (SELECT calculateMatchScore(m.match_id) WHERE winner_id != m.team2_id AND winner_id IS NOT NULL))) 
                THEN 1 ELSE 0 END) as losses
            FROM matches m
            WHERE (m.team1_id = :team_id OR m.team2_id = :team_id)";
    
    $params = ['team_id' => $team['team_id']];
    
    if ($seriesId) {
        $sql .= " AND m.series_id = :series_id";
        $params['series_id'] = $seriesId;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats = $stmt->fetch();
    
    // Calculate wins and losses manually from matches
    $wins = 0;
    $losses = 0;
    $totalMatches = 0;
    
    $matchSql = "SELECT m.match_id, m.team1_id, m.team2_id, m.state
                 FROM matches m
                 WHERE (m.team1_id = :team_id OR m.team2_id = :team_id)";
    
    if ($seriesId) {
        $matchSql .= " AND m.series_id = :series_id";
    }
    
    $matchStmt = $db->prepare($matchSql);
    $matchStmt->execute($params);
    $teamMatches = $matchStmt->fetchAll();
    
    foreach ($teamMatches as $teamMatch) {
        if ($teamMatch['state'] === 'completed') {
            $totalMatches++;
            $score = calculateMatchScore($teamMatch['match_id']);
            if ($score && $score['winner_id'] == $team['team_id']) {
                $wins++;
            } elseif ($score && $score['winner_id'] && $score['winner_id'] != $team['team_id']) {
                $losses++;
            }
        }
    }
    
    // Calculate NRR
    $nrr = calculateNRR($team['team_id'], $seriesId ?: null);
    
    // Calculate points (2 points per win)
    $points = $wins * 2;
    
    // Calculate "count" - total matches played or wins
    $count = $totalMatches;
    
    $teamStats[] = [
        'team_id' => $team['team_id'],
        'name' => $team['name'],
        'short_name' => $team['short_name'],
        'logo' => $team['logo'],
        'matches' => $totalMatches,
        'wins' => $wins,
        'losses' => $losses,
        'points' => $points,
        'nrr' => $nrr,
        'count' => $count
    ];
}

// Sort by count/points (descending), then by NRR (descending)
usort($teamStats, function($a, $b) use ($sortBy) {
    if ($sortBy === 'name') {
        return strcasecmp($a['name'], $b['name']);
    }
    
    // Primary sort: count/points
    if ($b['count'] != $a['count']) {
        return $b['count'] <=> $a['count'];
    }
    
    // Secondary sort: NRR
    return $b['nrr'] <=> $a['nrr'];
});
} catch (Exception $e) {
    error_log("Error calculating team statistics: " . $e->getMessage());
    $teamStats = [];
}

// Get series list for dropdown
try {
    $seriesModel = new Series();
    $allSeries = $seriesModel->getAll() ?? [];
} catch (Exception $e) {
    error_log("Error loading series list: " . $e->getMessage());
    $allSeries = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>Teams Ranking - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="page-title">Stats</div>
                <div style="position: relative;">
                    <select onchange="window.location.href=this.value" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 6px 12px; border-radius: 20px; outline: none; font-size: 12px; font-weight: 600;">
                        <option value="?sort_by=<?= $sortBy ?>" <?= !$seriesId ? 'selected' : '' ?>>All Series</option>
                        <?php foreach ($allSeries as $series): ?>
                            <option value="?series_id=<?= $series['series_id'] ?>&sort_by=<?= $sortBy ?>" <?= $seriesId == $series['series_id'] ? 'selected' : '' ?>>
                                <?= e($series['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </header>

    <!-- Filter Tabs -->
    <div class="nav-tabs-scroll">
        <a href="leaderboard.php" class="nav-tab-link">Most Runs</a>
        <a href="leaderboard.php?sort_by=wickets" class="nav-tab-link">Most Wickets</a>
        <a href="teams-ranking.php" class="nav-tab-link active">Team Standings</a>
    </div>

    <div class="container">
        
        <!-- Ranking Table -->
        <div class="glass-card">
            <div class="card-header">
                <span>Team Standings</span>
                <span class="text-xs text-muted">NRR / Pts</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($teamStats)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        No teams found.
                    </div>
                <?php else: ?>
                    <table class="premium-table">
                        <tbody>
                            <?php foreach ($teamStats as $index => $team): 
                                $rank = $index + 1;
                                $rankClass = $rank <= 3 ? "rank-$rank" : "";
                            ?>
                                <tr onclick="window.location.href='points-table.php?team_id=<?= $team['team_id'] ?>'" style="cursor: pointer;">
                                    <td style="width: 40px; text-align: center; font-weight: 700; color: var(--text-muted);">
                                        #<?= $rank ?>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="avatar">
                                                <?php if (!empty($team['logo'])): ?>
                                                    <img src="<?= e($team['logo']) ?>" alt="" style="width: 100%; height: 100%; border-radius: 50%;">
                                                <?php else: ?>
                                                    <?= substr($team['name'], 0, 1) ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="font-bold"><?= e($team['name']) ?></div>
                                                <div class="text-xs text-muted">
                                                    <?= (int)$team['matches'] ?> Mat • <?= (int)$team['wins'] ?> W • <?= (int)$team['losses'] ?> L
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="font-bold text-primary" style="font-size: 16px;"><?= (int)$team['points'] ?></div>
                                        <div class="text-xs text-muted"><?= $team['nrr'] >= 0 ? '+' : '' ?><?= number_format($team['nrr'], 3) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
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


