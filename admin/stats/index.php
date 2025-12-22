<?php
/**
 * Admin Statistics/Leaderboard Page
 * 
 * Uses the same data source as public leaderboard for consistency
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireLogin();

$seriesId = getQuery('series_id') ? (int)getQuery('series_id') : 0;
$sortBy = getQuery('sort_by', 'runs');
$filterType = getQuery('filter_type', 'all'); // all, batting, bowling

$db = Database::getInstance()->getConnection();

// Get series list for dropdown
$seriesModel = new Series();
$allSeries = $seriesModel->getAll();

// Get player leaderboard (same query as public leaderboard)
$sql = "SELECT sc.player_id, p.name as player_name, p.profile_image,
        SUM(sc.runs) as total_runs,
        SUM(sc.wickets) as total_wickets,
        SUM(sc.balls_faced) as total_balls_faced,
        SUM(sc.overs_bowled) as total_overs_bowled,
        SUM(sc.fours) as total_fours,
        SUM(sc.sixes) as total_sixes,
        SUM(sc.dismissals) as total_dismissals,
        SUM(sc.runs_conceded) as total_runs_conceded,
        AVG(sc.strike_rate) as avg_strike_rate,
        AVG(sc.economy_rate) as avg_economy_rate,
        COUNT(DISTINCT sc.match_id) as matches_played
        FROM stats_cache sc
        INNER JOIN players p ON sc.player_id = p.player_id";

if ($seriesId) {
    $sql .= " INNER JOIN matches m ON sc.match_id = m.match_id WHERE m.series_id = :series_id";
    $params = ['series_id' => $seriesId];
} else {
    $sql .= " WHERE 1=1";
    $params = [];
}

// Filter by batting/bowling
if ($filterType === 'batting') {
    $sql .= " AND sc.runs > 0";
} elseif ($filterType === 'bowling') {
    $sql .= " AND sc.wickets > 0";
}

// Validate sort field
$allowedSorts = ['runs', 'wickets', 'strike_rate', 'economy_rate', 'sixes', 'boundaries'];
if (!in_array($sortBy, $allowedSorts)) {
    $sortBy = 'runs';
}

// Handle special sorting cases
if ($sortBy === 'boundaries') {
    // Boundaries = fours + sixes, need to calculate in ORDER BY
    $sql .= " GROUP BY sc.player_id, p.name, p.profile_image ORDER BY (SUM(sc.fours) + SUM(sc.sixes)) DESC LIMIT 20";
} else {
    $sql .= " GROUP BY sc.player_id, p.name, p.profile_image ORDER BY total_$sortBy DESC LIMIT 20";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if winner_id column exists
$checkWinnerIdSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'matches' 
                     AND COLUMN_NAME = 'winner_id'";
$checkStmt = $db->query($checkWinnerIdSql);
$hasWinnerId = $checkStmt->fetch() !== false;

// Get team standings with wins, losses, and points (same as public)
if ($hasWinnerId) {
    // Use winner_id column if it exists
    $teamSql = "SELECT t.team_id, t.name, t.short_name,
                COUNT(DISTINCT m.match_id) as matches_played,
                SUM(CASE WHEN m.state = 'completed' AND (m.team1_id = t.team_id OR m.team2_id = t.team_id) THEN 1 ELSE 0 END) as matches_completed,
                SUM(CASE WHEN m.state = 'completed' AND m.winner_id = t.team_id THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN m.state = 'completed' AND m.winner_id IS NOT NULL AND m.winner_id != t.team_id AND (m.team1_id = t.team_id OR m.team2_id = t.team_id) THEN 1 ELSE 0 END) as losses,
                SUM(CASE WHEN m.state = 'completed' AND m.winner_id IS NULL AND (m.team1_id = t.team_id OR m.team2_id = t.team_id) THEN 1 ELSE 0 END) as ties
                FROM teams t
                LEFT JOIN matches m ON (m.team1_id = t.team_id OR m.team2_id = t.team_id)
                GROUP BY t.team_id, t.name, t.short_name
                ORDER BY wins DESC, matches_completed DESC, t.name ASC";
} else {
    // Fallback: Calculate wins/losses from match scores if winner_id doesn't exist
    $teamSql = "SELECT t.team_id, t.name, t.short_name,
                COUNT(DISTINCT m.match_id) as matches_played,
                SUM(CASE WHEN m.state = 'completed' AND (m.team1_id = t.team_id OR m.team2_id = t.team_id) THEN 1 ELSE 0 END) as matches_completed,
                0 as wins,
                0 as losses,
                0 as ties
                FROM teams t
                LEFT JOIN matches m ON (m.team1_id = t.team_id OR m.team2_id = t.team_id)
                GROUP BY t.team_id, t.name, t.short_name
                ORDER BY matches_completed DESC, t.name ASC";
}
$teamStmt = $db->prepare($teamSql);
$teamStmt->execute();
$teamStandings = $teamStmt->fetchAll();

// Calculate points for each team (2 points per win, 1 point per tie)
foreach ($teamStandings as &$team) {
    $team['points'] = ($team['wins'] * 2) + ($team['ties'] * 1);
}
unset($team); // Break reference
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/../../includes/cache-prevention-meta.php'; ?>
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title>Statistics - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
    <style>
        .leaderboard-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .leaderboard-table th, .leaderboard-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .leaderboard-table th {
            text-align: left;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--bg-body);
        }
        .leaderboard-table .number-col, .leaderboard-table .number-cell {
            text-align: right;
        }
        .rank-1 { background: rgba(255, 215, 0, 0.1); }
        .rank-2 { background: rgba(192, 192, 192, 0.1); }
        .rank-3 { background: rgba(205, 127, 50, 0.1); }
        
        .filter-scroll {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            -webkit-overflow-scrolling: touch;
        }
        .filter-scroll::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?php renderAdminSidebar('stats'); ?>

        <header class="app-header">
            <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                ☰
            </button>
            <div class="header-title">Statistics</div>
            <div class="header-actions">
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                    <span id="refresh-countdown">30</span>s
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="content-container">
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div style="margin-bottom: 16px;">
                            <select id="series-select" class="form-select" onchange="updateSeries(this.value)" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                                <option value="0" <?= !$seriesId ? 'selected' : '' ?>>All Series</option>
                                <?php foreach ($allSeries as $series): ?>
                                    <option value="<?= $series['series_id'] ?>" <?= $seriesId == $series['series_id'] ? 'selected' : '' ?>>
                                        <?= e($series['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-scroll">
                            <a href="<?= adminUrl('stats/?series_id=' . $seriesId . '&sort_by=runs&filter_type=' . $filterType) ?>" class="btn btn-sm <?= $sortBy === 'runs' ? 'btn-primary' : 'btn-secondary' ?>" style="white-space: nowrap;">Runs</a>
                            <a href="<?= adminUrl('stats/?series_id=' . $seriesId . '&sort_by=wickets&filter_type=' . $filterType) ?>" class="btn btn-sm <?= $sortBy === 'wickets' ? 'btn-primary' : 'btn-secondary' ?>" style="white-space: nowrap;">Wickets</a>
                            <a href="<?= adminUrl('stats/?series_id=' . $seriesId . '&sort_by=sixes&filter_type=' . $filterType) ?>" class="btn btn-sm <?= $sortBy === 'sixes' ? 'btn-primary' : 'btn-secondary' ?>" style="white-space: nowrap;">6s</a>
                            <a href="<?= adminUrl('stats/?series_id=' . $seriesId . '&sort_by=boundaries&filter_type=' . $filterType) ?>" class="btn btn-sm <?= $sortBy === 'boundaries' ? 'btn-primary' : 'btn-secondary' ?>" style="white-space: nowrap;">4s & 6s</a>
                            <div style="width: 1px; background: var(--border); margin: 0 4px;"></div>
                            <a href="<?= adminUrl('stats/?series_id=' . $seriesId . '&sort_by=' . $sortBy . '&filter_type=all') ?>" class="btn btn-sm <?= $filterType === 'all' ? 'btn-primary' : 'btn-secondary' ?>" style="white-space: nowrap;">All</a>
                            <a href="<?= adminUrl('stats/?series_id=' . $seriesId . '&sort_by=' . $sortBy . '&filter_type=batting') ?>" class="btn btn-sm <?= $filterType === 'batting' ? 'btn-primary' : 'btn-secondary' ?>" style="white-space: nowrap;">Bat</a>
                            <a href="<?= adminUrl('stats/?series_id=' . $seriesId . '&sort_by=' . $sortBy . '&filter_type=bowling') ?>" class="btn btn-sm <?= $filterType === 'bowling' ? 'btn-primary' : 'btn-secondary' ?>" style="white-space: nowrap;">Bowl</a>
                        </div>
                    </div>
                </div>

                <!-- Team Standings -->
                <?php if (!empty($teamStandings)): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">Team Standings</h3>
                        </div>
                        <div class="list-group">
                            <?php foreach ($teamStandings as $team): ?>
                                <div class="list-item">
                                    <div class="list-item-content">
                                        <div class="list-item-title"><?= e($team['name']) ?></div>
                                        <div class="list-item-subtitle"><?= (int)$team['matches_completed'] ?> / <?= (int)$team['matches_played'] ?> matches</div>
                                    </div>
                                    <div style="text-align: right; display: flex; gap: 12px;">
                                        <div style="text-align: center;">
                                            <div style="font-size: 0.7rem; color: var(--text-muted);">W</div>
                                            <div style="font-weight: 600; color: var(--success);"><?= (int)$team['wins'] ?></div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="font-size: 0.7rem; color: var(--text-muted);">L</div>
                                            <div style="font-weight: 600; color: var(--danger);"><?= (int)$team['losses'] ?></div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="font-size: 0.7rem; color: var(--text-muted);">Pts</div>
                                            <div style="font-weight: 700;"><?= (int)$team['points'] ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Leaderboard Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Leaderboard</h3>
                    </div>
                    <?php if (empty($leaderboard)): ?>
                        <div class="card-body text-center">
                            <p style="color: var(--text-muted);">No statistics available.</p>
                        </div>
                    <?php else: ?>
                        <div class="leaderboard-table-container">
                            <table class="leaderboard-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Player</th>
                                        <th class="number-col">Mat</th>
                                        <th class="number-col">Runs</th>
                                        <th class="number-col">Wkts</th>
                                        <th class="number-col">SR</th>
                                        <th class="number-col">Avg</th>
                                    </tr>
                                </thead>
                                <tbody id="leaderboard-tbody">
                                    <?php foreach ($leaderboard as $index => $player): ?>
                                        <?php
                                        $rank = $index + 1;
                                        $rankClass = '';
                                        if ($rank == 1) $rankClass = 'rank-1';
                                        elseif ($rank == 2) $rankClass = 'rank-2';
                                        elseif ($rank == 3) $rankClass = 'rank-3';
                                        
                                        $battingAvg = $player['total_dismissals'] > 0
                                            ? round($player['total_runs'] / $player['total_dismissals'], 2)
                                            : ($player['total_runs'] > 0 ? '-' : 0);
                                        ?>
                                        <tr class="<?= $rankClass ?>" data-player-id="<?= $player['player_id'] ?>">
                                            <td><?= $rank ?></td>
                                            <td>
                                                <a href="<?= adminUrl('players/view.php?id=' . $player['player_id']) ?>" style="text-decoration: none; color: inherit; font-weight: 500;">
                                                    <?= e($player['player_name']) ?>
                                                </a>
                                            </td>
                                            <td class="number-cell"><?= (int)$player['matches_played'] ?></td>
                                            <td class="number-cell"><?= number_format($player['total_runs']) ?></td>
                                            <td class="number-cell"><?= number_format($player['total_wickets']) ?></td>
                                            <td class="number-cell"><?= number_format($player['avg_strike_rate'] ?? 0, 1) ?></td>
                                            <td class="number-cell"><?= is_numeric($battingAvg) && $battingAvg > 0 ? number_format($battingAvg, 1) : '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.app-shell').classList.toggle('sidebar-open');
        }

        // Real-time data refresh
        let refreshInterval;
        let countdownInterval;
        let countdown = 30;
        
        function updateSeries(seriesId) {
            const url = new URL(window.location.href);
            url.searchParams.set('series_id', seriesId);
            window.location.href = url.toString();
        }
        
        function refreshData() {
            // Build API URL with current filters
            const apiBase = '<?= getBaseUrl() ?>/api/v1/stats.php/leaderboard';
            const url = new URL(apiBase);
            const seriesSelect = document.getElementById('series-select');
            const currentUrl = new URL(window.location.href);
            
            if (seriesSelect && seriesSelect.value) {
                url.searchParams.set('series_id', seriesSelect.value);
            }
            if (currentUrl.searchParams.get('sort_by')) {
                url.searchParams.set('sort_by', currentUrl.searchParams.get('sort_by'));
            }
            if (currentUrl.searchParams.get('filter_type')) {
                url.searchParams.set('filter_type', currentUrl.searchParams.get('filter_type'));
            }
            url.searchParams.set('limit', '20');
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        updateLeaderboardTable(data.data);
                    }
                    countdown = 30; // Reset countdown
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                });
        }
        
        function updateLeaderboardTable(players) {
            const tbody = document.getElementById('leaderboard-tbody');
            if (!tbody) return;
            
            tbody.innerHTML = players.map((player, index) => {
                const rank = index + 1;
                const rankClass = rank === 1 ? 'rank-1' : rank === 2 ? 'rank-2' : rank === 3 ? 'rank-3' : '';
                
                const battingAvg = player.total_dismissals > 0
                    ? (player.total_runs / player.total_dismissals).toFixed(1)
                    : (player.total_runs > 0 ? '-' : '0.0');
                
                return `
                    <tr class="${rankClass}" data-player-id="${player.player_id}">
                        <td>${rank}</td>
                        <td>
                            <a href="<?= adminUrl('players/view.php?id=') ?>${player.player_id}" style="text-decoration: none; color: inherit; font-weight: 500;">
                                ${player.player_name}
                            </a>
                        </td>
                        <td class="number-cell">${player.matches_played || 0}</td>
                        <td class="number-cell">${parseInt(player.total_runs || 0).toLocaleString()}</td>
                        <td class="number-cell">${parseInt(player.total_wickets || 0).toLocaleString()}</td>
                        <td class="number-cell">${parseFloat(player.avg_strike_rate || 0).toFixed(1)}</td>
                        <td class="number-cell">${battingAvg}</td>
                    </tr>
                `;
            }).join('');
        }
        
        function startAutoRefresh() {
            // Refresh every 30 seconds
            refreshInterval = setInterval(refreshData, 30000);
            
            // Update countdown every second
            countdownInterval = setInterval(() => {
                countdown--;
                const countdownEl = document.getElementById('refresh-countdown');
                if (countdownEl) {
                    countdownEl.textContent = countdown;
                }
                if (countdown <= 0) {
                    countdown = 30;
                }
            }, 1000);
        }
        
        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', () => {
            startAutoRefresh();
        });
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (refreshInterval) clearInterval(refreshInterval);
            if (countdownInterval) clearInterval(countdownInterval);
        });
    </script>
</body>
</html>

