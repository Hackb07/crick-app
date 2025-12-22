<?php
/**
 * Points Table Page - CricBuzz-style
 * 
 * Features:
 * - Green header with dropdown for series selection
 * - Full points table with P/W/L/T/NR/Pts/NRR columns
 * - Multiple tables support (different series/tournaments)
 * - Responsive table with horizontal scroll on mobile
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/utils.php';

$seriesId = (int)getQuery('series_id', 0);
$teamId = (int)getQuery('team_id', 0);

$db = Database::getInstance()->getConnection();
$allSeries = [];
$seriesToShow = [];
$allTeams = [];
$pointsTables = [];

try {
    $seriesModel = new Series();
    $allSeries = $seriesModel->getAll() ?? [];

    // If series_id is provided, show that series only, otherwise show all series
    if ($seriesId) {
        $series = $seriesModel->getById($seriesId);
        if ($series) {
            $seriesToShow[] = $series;
        }
    } else {
        $seriesToShow = $allSeries;
        // Also add an "All Series" option
        if (!empty($allSeries)) {
            array_unshift($seriesToShow, ['series_id' => 0, 'name' => 'All Series']);
        }
    }

    // Get all teams
    $teamModel = new Team();
    $allTeams = $teamModel->getAll() ?? [];
} catch (Exception $e) {
    error_log("Error loading points table data: " . $e->getMessage());
    // Continue with empty arrays
}

// Calculate points table for each series
try {
    foreach ($seriesToShow as $series) {
        $currentSeriesId = $series['series_id'] ?? 0;
        
        $teamStats = [];
        
        foreach ($allTeams as $team) {
            // Get matches for this team in this series
            $matchSql = "SELECT m.match_id, m.team1_id, m.team2_id, m.state
                         FROM matches m
                         WHERE (m.team1_id = :team_id OR m.team2_id = :team_id)";
            
            $params = ['team_id' => $team['team_id']];
            
            if ($currentSeriesId > 0) {
                $matchSql .= " AND m.series_id = :series_id";
                $params['series_id'] = $currentSeriesId;
            }
            
            $matchStmt = $db->prepare($matchSql);
            $matchStmt->execute($params);
            $teamMatches = $matchStmt->fetchAll();
            
            $played = 0;
            $wins = 0;
            $losses = 0;
            $ties = 0;
            $noResults = 0;
            
            foreach ($teamMatches as $teamMatch) {
                if ($teamMatch['state'] === 'completed') {
                    $played++;
                    $score = calculateMatchScore($teamMatch['match_id']);
                    if ($score) {
                        if ($score['winner_id'] == $team['team_id']) {
                            $wins++;
                        } elseif ($score['winner_id'] && $score['winner_id'] != $team['team_id']) {
                            $losses++;
                        } elseif (!$score['winner_id']) {
                            // Check if it's a tie (same runs)
                            if ($score['innings1']['runs'] == $score['innings2']['runs']) {
                                $ties++;
                            } else {
                                $noResults++;
                            }
                        }
                    } else {
                        $noResults++;
                    }
                } elseif ($teamMatch['state'] === 'abandoned') {
                    $noResults++;
                }
            }
            
            // Calculate points (2 points per win, 1 point per tie)
            $points = ($wins * 2) + ($ties * 1);
            
            // Calculate NRR
            $nrr = calculateNRR($team['team_id'], $currentSeriesId > 0 ? $currentSeriesId : null);
            
            // Only add teams that have played at least one match
            if ($played > 0 || $wins > 0 || $losses > 0) {
                $teamStats[] = [
                    'team_id' => $team['team_id'],
                    'name' => $team['name'],
                    'short_name' => $team['short_name'],
                    'logo' => $team['logo'],
                    'played' => $played,
                    'wins' => $wins,
                    'losses' => $losses,
                    'ties' => $ties,
                    'no_results' => $noResults,
                    'points' => $points,
                    'nrr' => $nrr
                ];
            }
        }
        
        // Sort by points (descending), then by NRR (descending)
        usort($teamStats, function($a, $b) {
            // Primary sort: points
            if ($b['points'] != $a['points']) {
                return $b['points'] <=> $a['points'];
            }
            
            // Secondary sort: NRR
            return $b['nrr'] <=> $a['nrr'];
        });
        
        if (!empty($teamStats)) {
            $pointsTables[] = [
                'series' => $series,
                'teams' => $teamStats
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error calculating points table: " . $e->getMessage());
    // Continue with empty points tables
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Points Table - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        /* Green Header */
        .green-header {
            background: var(--cricket-green);
            color: white;
            padding: var(--spacing-md) 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-md);
        }
        
        .green-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--spacing-md);
        }
        
        .green-header-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .filter-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .filter-dropdown-button {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            transition: background 0.2s;
        }
        
        .filter-dropdown-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .filter-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: var(--spacing-xs);
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .filter-dropdown-menu.show {
            display: block;
        }
        
        .filter-dropdown-item {
            display: block;
            padding: var(--spacing-md);
            color: var(--text-primary);
            text-decoration: none;
            transition: background 0.2s;
            border-bottom: 1px solid var(--border-color);
        }
        
        .filter-dropdown-item:last-child {
            border-bottom: none;
        }
        
        .filter-dropdown-item:hover {
            background: var(--bg-secondary);
            color: var(--cricket-green);
        }
        
        .filter-dropdown-item.active {
            background: var(--cricket-green);
            color: white;
        }
        
        /* Points Table */
        .points-table-container {
            overflow-x: auto;
            margin-bottom: var(--spacing-xl);
            -webkit-overflow-scrolling: touch;
        }
        
        .points-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            font-size: 1rem;
        }
        
        .points-table thead {
            background: linear-gradient(135deg, var(--cricket-green) 0%, var(--cricket-dark-green) 100%);
            color: white;
        }
        
        .points-table th {
            padding: var(--spacing-md);
            text-align: left;
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .points-table th.team-col {
            min-width: 200px;
            position: sticky;
            left: 0;
            background: linear-gradient(135deg, var(--cricket-green) 0%, var(--cricket-dark-green) 100%);
            z-index: 10;
        }
        
        .points-table th.number-col {
            text-align: right;
            width: 80px;
        }
        
        .points-table th.nrr-col {
            text-align: right;
            width: 100px;
        }
        
        .points-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        
        .points-table tbody tr:hover {
            background: var(--bg-secondary);
        }
        
        .points-table tbody tr:nth-child(even) {
            background: var(--bg-secondary);
        }
        
        .points-table tbody tr.top-3 {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 237, 78, 0.05) 100%);
            border-left: 3px solid #ffd700;
        }
        
        .points-table td {
            padding: var(--spacing-md);
            font-size: 0.95rem;
        }
        
        .points-table td.team-cell {
            position: sticky;
            left: 0;
            background: var(--bg-primary);
            z-index: 5;
            font-weight: 700;
            font-size: 1rem;
        }
        
        .points-table tbody tr:hover td.team-cell {
            background: var(--bg-secondary);
        }
        
        .points-table tbody tr:nth-child(even) td.team-cell {
            background: var(--bg-secondary);
        }
        
        .points-table tbody tr:hover td.team-cell {
            background: var(--bg-secondary);
        }
        
        .points-table td.number-cell {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }
        
        .points-table td.points-cell {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--cricket-green);
        }
        
        .points-table td.nrr-cell {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
        }
        
        .points-table td.nrr-positive {
            color: var(--success-green);
        }
        
        .points-table td.nrr-negative {
            color: var(--live-red);
        }
        
        .table-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: var(--spacing-xl) 0 var(--spacing-md) 0;
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--border-color);
        }
        
        .table-title:first-child {
            margin-top: var(--spacing-lg);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .green-header-content {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-dropdown {
                width: 100%;
            }
            
            .filter-dropdown-button {
                width: 100%;
                justify-content: space-between;
            }
            
            .filter-dropdown-menu {
                width: 100%;
                right: auto;
            }
            
            .points-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }
            
            .points-table {
                min-width: 700px;
                font-size: 0.875rem;
            }
            
            .points-table th,
            .points-table td {
                padding: var(--spacing-sm);
                font-size: 0.875rem;
            }
            
            .points-table th.team-col,
            .points-table td.team-cell {
                position: static;
                min-width: auto;
            }
            
            .points-table th.number-col {
                width: 60px;
            }
            
            .points-table td.points-cell {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Green Header -->
    <header class="green-header">
        <div class="container green-header-content">
            <a href="<?= publicUrl('index.php') ?>" class="green-header-logo">🏏 CricApp</a>
            <div class="filter-dropdown">
                <button class="filter-dropdown-button" onclick="toggleDropdown()">
                    <span id="filter-text">
                        <?php
                        if ($seriesId && !empty($allSeries)) {
                            $selectedSeries = array_filter($allSeries, function($s) use ($seriesId) {
                                return $s['series_id'] == $seriesId;
                            });
                            if (!empty($selectedSeries)) {
                                echo e(reset($selectedSeries)['name'] . ' Points Table');
                            } else {
                                echo 'Points Table';
                            }
                        } else {
                            echo 'Points Table';
                        }
                        ?>
                    </span>
                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 16px; height: 16px;">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div class="filter-dropdown-menu" id="filter-menu">
                    <a href="<?= publicUrl('points-table.php') ?>" class="filter-dropdown-item <?= !$seriesId ? 'active' : '' ?>">All Series</a>
                    <?php foreach ($allSeries as $series): ?>
                        <a href="<?= publicUrl('points-table.php?series_id=' . $series['series_id']) ?>" class="filter-dropdown-item <?= $seriesId == $series['series_id'] ? 'active' : '' ?>">
                            <?= e($series['name']) ?> Points Table
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (empty($pointsTables)): ?>
            <div class="card">
                <div class="empty-state">
                    <div class="empty-state-icon">📊</div>
                    <div class="empty-state-text">No Points Table Available</div>
                    <div class="empty-state-subtext">Points table will appear once matches are completed in a series.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pointsTables as $table): ?>
                <h2 class="table-title"><?= e($table['series']['name'] ?? 'Points Table') ?></h2>
                
                <div class="card" style="margin-bottom: var(--spacing-xl);">
                    <div class="points-table-container">
                        <table class="points-table">
                            <thead>
                                <tr>
                                    <th class="team-col">Team</th>
                                    <th class="number-col">P</th>
                                    <th class="number-col">W</th>
                                    <th class="number-col">L</th>
                                    <th class="number-col">T</th>
                                    <th class="number-col">NR</th>
                                    <th class="number-col">Pts</th>
                                    <th class="nrr-col">NRR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table['teams'] as $index => $team): ?>
                                    <?php
                                    $isTop3 = ($index < 3) && ($team['points'] > 0);
                                    ?>
                                    <tr class="<?= $isTop3 ? 'top-3' : '' ?>" onclick="window.location.href='<?= publicUrl('teams-ranking.php?team_id=' . $team['team_id']) ?>'">
                                        <td class="team-cell"><?= e($team['name']) ?></td>
                                        <td class="number-cell"><?= $team['played'] ?></td>
                                        <td class="number-cell"><?= $team['wins'] ?></td>
                                        <td class="number-cell"><?= $team['losses'] ?></td>
                                        <td class="number-cell"><?= $team['ties'] ?></td>
                                        <td class="number-cell"><?= $team['no_results'] ?></td>
                                        <td class="points-cell"><?= $team['points'] ?></td>
                                        <td class="nrr-cell <?= $team['nrr'] >= 0 ? 'nrr-positive' : 'nrr-negative' ?>">
                                            <?= $team['nrr'] >= 0 ? '+' : '' ?><?= number_format($team['nrr'], 3) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Series Winner Display -->
                    <?php
                    // Determine series winner: team with highest points (if tied, highest NRR)
                    // Only show if series has completed matches
                    $seriesWinner = null;
                    $tableSeriesId = $table['series']['series_id'] ?? 0;
                    if (!empty($table['teams']) && $tableSeriesId > 0) {
                        // Check if series has any completed matches
                        $completedMatchesSql = "SELECT COUNT(*) as count 
                                               FROM matches 
                                               WHERE series_id = :series_id AND state = 'completed'";
                        $completedStmt = $db->prepare($completedMatchesSql);
                        $completedStmt->execute(['series_id' => $tableSeriesId]);
                        $completedCount = $completedStmt->fetch()['count'] ?? 0;
                        
                        if ($completedCount > 0 && !empty($table['teams'][0]) && $table['teams'][0]['points'] > 0) {
                            // Get top team (already sorted by points, then NRR)
                            $topTeam = $table['teams'][0];
                            
                            // Check if there's a clear winner (not tied on points with same NRR)
                            $isClearWinner = true;
                            if (count($table['teams']) > 1) {
                                $secondTeam = $table['teams'][1];
                                // If second team has same points and NRR, it's a tie
                                if ($secondTeam['points'] == $topTeam['points'] && 
                                    abs($secondTeam['nrr'] - $topTeam['nrr']) < 0.001) {
                                    $isClearWinner = false;
                                }
                            }
                            
                            if ($isClearWinner) {
                                $seriesWinner = $topTeam;
                            }
                        }
                    }
                    ?>
                    <?php if ($seriesWinner): ?>
                        <div style="margin-top: var(--spacing-lg); padding: var(--spacing-md); background: linear-gradient(135deg, var(--cricket-green) 0%, var(--cricket-dark-green) 100%); color: white; border-radius: var(--radius-md); text-align: center;">
                            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: var(--spacing-xs);">Series Winner</div>
                            <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: var(--spacing-xs);">
                                🏆 <?= e($seriesWinner['name']) ?>
                            </div>
                            <div style="font-size: 0.875rem; opacity: 0.9;">
                                <?= $seriesWinner['points'] ?> Points • <?= $seriesWinner['wins'] ?> Wins
                                <?php if ($seriesWinner['nrr'] >= 0): ?>
                                    • NRR: +<?= number_format($seriesWinner['nrr'], 3) ?>
                                <?php else: ?>
                                    • NRR: <?= number_format($seriesWinner['nrr'], 3) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <div class="mobile-nav-items">
            <a href="<?= publicUrl('index.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                <span>Home</span>
            </a>
            <a href="<?= publicUrl('matches.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                <span>Matches</span>
            </a>
            <a href="<?= publicUrl('live.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                <span>Live</span>
            </a>
            <a href="<?= publicUrl('leaderboard.php') ?>" class="mobile-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                <span>Leaderboard</span>
            </a>
        </div>
    </nav>

    <script>
        function toggleDropdown() {
            const menu = document.getElementById('filter-menu');
            menu.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.filter-dropdown');
            const menu = document.getElementById('filter-menu');
            if (!dropdown.contains(event.target)) {
                menu.classList.remove('show');
            }
        });
    </script>
</body>
</html>


