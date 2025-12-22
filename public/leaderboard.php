<?php
/**
 * Public Leaderboard Page
 * Shows top batsmen and bowlers with filters
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/Database.php';

$db = Database::getInstance()->getConnection();

// Get filter parameters
$type = $_GET['type'] ?? 'batsman'; // batsman or bowler
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // 5 or 10
$limit = in_array($limit, [5, 10]) ? $limit : 10;

// Get top batsmen leaderboard
$batsmenSql = "SELECT 
        p.player_id,
        p.name,
        COUNT(DISTINCT sc.match_id) as matches_played,
        SUM(sc.runs) as total_runs,
        SUM(sc.balls_faced) as total_balls_faced,
        SUM(sc.fours) as total_fours,
        SUM(sc.sixes) as total_sixes,
        CASE 
            WHEN SUM(sc.balls_faced) > 0 THEN 
                ROUND((SUM(sc.runs) * 100.0 / SUM(sc.balls_faced)), 2)
            ELSE 0 
        END as strike_rate,
        CASE 
            WHEN COUNT(DISTINCT sc.match_id) > 0 THEN 
                ROUND((SUM(sc.runs) * 1.0 / COUNT(DISTINCT sc.match_id)), 2)
            ELSE 0 
        END as avg_runs
        FROM players p
        JOIN stats_cache sc ON p.player_id = sc.player_id
        WHERE sc.runs > 0 OR sc.balls_faced > 0
        GROUP BY p.player_id, p.name
        HAVING SUM(sc.runs) > 0
        ORDER BY total_runs DESC, strike_rate DESC
        LIMIT :limit";

$stmt = $db->prepare($batsmenSql);
$stmt->execute(['limit' => $limit]);
$batsmenLeaderboard = $stmt->fetchAll();

// Get top bowlers leaderboard
// Calculate runs_conceded from economy_rate * overs_bowled
// Use subquery to calculate totals first, then compute economy_rate
$bowlersSql = "SELECT 
        player_id,
        name,
        matches_played,
        total_wickets,
        total_overs_bowled,
        total_runs_conceded,
        CASE 
            WHEN total_overs_bowled > 0 THEN 
                ROUND((total_runs_conceded / total_overs_bowled), 2)
            ELSE 0 
        END as economy_rate,
        CASE 
            WHEN total_wickets > 0 THEN 
                ROUND((total_runs_conceded / total_wickets), 2)
            ELSE 0 
        END as bowling_average
        FROM (
            SELECT 
                p.player_id,
                p.name,
                COUNT(DISTINCT sc.match_id) as matches_played,
                SUM(sc.wickets) as total_wickets,
                SUM(sc.overs_bowled) as total_overs_bowled,
                SUM(CASE 
                    WHEN sc.economy_rate IS NOT NULL AND sc.overs_bowled > 0 THEN 
                        ROUND(sc.economy_rate * sc.overs_bowled)
                    ELSE 0 
                END) as total_runs_conceded
            FROM players p
            JOIN stats_cache sc ON p.player_id = sc.player_id
            WHERE sc.wickets > 0 OR sc.overs_bowled > 0
            GROUP BY p.player_id, p.name
            HAVING SUM(sc.wickets) > 0
        ) as bowler_stats
        ORDER BY total_wickets DESC, economy_rate ASC
        LIMIT :limit";

$stmt = $db->prepare($bowlersSql);
$stmt->execute(['limit' => $limit]);
$bowlersLeaderboard = $stmt->fetchAll();

// Set current leaderboard based on filter
$leaderboard = ($type === 'batsman') ? $batsmenLeaderboard : $bowlersLeaderboard;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Cricket Scoring</title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/public.css">
    <link rel="stylesheet" href="/cricapp/assets/css/premium-design.css">
    <link rel="stylesheet" href="/cricapp/assets/css/leaderboard.css">
    <style>
        @media (max-width: 768px) {
            .compact-header {
                padding: 6px 10px !important;
            }
            
            .compact-header div:first-child {
                font-size: 0.75rem !important;
            }
            
            #menu-toggle {
                padding: 4px 8px !important;
                font-size: 1.125rem !important;
            }
            
            #menu-dropdown {
                right: 0 !important;
                min-width: 160px !important;
            }
            
            #menu-dropdown a {
                padding: 10px 14px !important;
                font-size: 0.8125rem !important;
            }
        }
    </style>
</head>
<body>
    <header class="compact-header" style="position: sticky; top: 0; z-index: 200; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 8px 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; position: relative;">
            <div style="font-size: 0.875rem; font-weight: 600;">🏏 Cricket</div>
            <div style="position: relative;">
                <button id="menu-toggle" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.25rem; padding: 6px 10px; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'" onclick="toggleMenu()">☰</button>
                <div id="menu-dropdown" style="display: none; position: absolute; top: calc(100% + 4px); right: 0; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 180px; z-index: 201;">
                    <a href="/cricapp/public/" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏠 Home</a>
                    <a href="/cricapp/public/leaderboard.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏆 Leaderboard</a>
                    <a href="/cricapp/public/matches.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">📅 Matches</a>
                    <a href="/cricapp/public/live.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; font-size: 0.875rem;">⚡ Live</a>
                </div>
            </div>
        </div>
    </header>
    
    <script>
        function toggleMenu() {
            const menu = document.getElementById('menu-dropdown');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('menu-dropdown');
            const toggle = document.getElementById('menu-toggle');
            if (menu && !menu.contains(event.target) && event.target !== toggle) {
                menu.style.display = 'none';
            }
        });
    </script>

    <main class="container main-content">
        <div class="leaderboard-header">
            <h2>🏆 Leaderboard</h2>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?type=batsman&limit=<?= $limit ?>" class="filter-tab <?= $type === 'batsman' ? 'active' : '' ?>">
                    🏏 Batsmen
                </a>
                <a href="?type=bowler&limit=<?= $limit ?>" class="filter-tab <?= $type === 'bowler' ? 'active' : '' ?>">
                    🎳 Bowlers
                </a>
            </div>
            
            <!-- Limit Selector -->
            <div class="limit-selector">
                <span>Show Top:</span>
                <a href="?type=<?= $type ?>&limit=5" class="limit-btn <?= $limit === 5 ? 'active' : '' ?>">5</a>
                <a href="?type=<?= $type ?>&limit=10" class="limit-btn <?= $limit === 10 ? 'active' : '' ?>">10</a>
            </div>
        </div>
        
        <div class="card">
            <?php if ($type === 'batsman'): ?>
                <table class="table leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player</th>
                            <th>Matches</th>
                            <th>Runs</th>
                            <th>Balls</th>
                            <th>SR</th>
                            <th>Avg</th>
                            <th>4s</th>
                            <th>6s</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($leaderboard as $player): ?>
                            <tr class="player-row" data-player-id="<?= $player['player_id'] ?>" onclick="showPlayerStats(<?= $player['player_id'] ?>)">
                                <td><strong><?= $rank++ ?></strong></td>
                                <td><strong class="player-name"><?= htmlspecialchars($player['name']) ?></strong></td>
                                <td><?= $player['matches_played'] ?></td>
                                <td><strong><?= $player['total_runs'] ?></strong></td>
                                <td><?= $player['total_balls_faced'] ?? 0 ?></td>
                                <td><?= number_format($player['strike_rate'], 2) ?></td>
                                <td><?= number_format($player['avg_runs'], 2) ?></td>
                                <td><?= $player['total_fours'] ?? 0 ?></td>
                                <td><?= $player['total_sixes'] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($leaderboard)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No batsmen statistics available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player</th>
                            <th>Matches</th>
                            <th>Wickets</th>
                            <th>Overs</th>
                            <th>Runs</th>
                            <th>Econ</th>
                            <th>Avg</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($leaderboard as $player): ?>
                            <tr class="player-row" data-player-id="<?= $player['player_id'] ?>" onclick="showPlayerStats(<?= $player['player_id'] ?>)">
                                <td><strong><?= $rank++ ?></strong></td>
                                <td><strong class="player-name"><?= htmlspecialchars($player['name']) ?></strong></td>
                                <td><?= $player['matches_played'] ?></td>
                                <td><strong><?= $player['total_wickets'] ?></strong></td>
                                <td><?= number_format($player['total_overs_bowled'] ?? 0, 1) ?></td>
                                <td><?= $player['total_runs_conceded'] ?? 0 ?></td>
                                <td><?= number_format($player['economy_rate'], 2) ?></td>
                                <td><?= number_format($player['bowling_average'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($leaderboard)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No bowler statistics available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Player Stats Modal -->
    <div id="player-stats-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-player-name">Player Stats</h3>
                <button class="modal-close" onclick="closePlayerStats()">&times;</button>
            </div>
            <div class="modal-body" id="modal-player-stats">
                <div class="loading">Loading player stats...</div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav" id="bottom-nav">
        <div class="bottom-nav-container">
            <a href="/cricapp/public/" class="bottom-nav-item">
                <span class="bottom-nav-icon">🏠</span>
                <span class="bottom-nav-label">Home</span>
            </a>
            <a href="/cricapp/public/matches.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">📅</span>
                <span class="bottom-nav-label">Matches</span>
            </a>
            <a href="/cricapp/public/live.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">⚡</span>
                <span class="bottom-nav-label">Live</span>
            </a>
            <a href="/cricapp/public/leaderboard.php" class="bottom-nav-item active">
                <span class="bottom-nav-icon">🏆</span>
                <span class="bottom-nav-label">Leaderboard</span>
            </a>
            <a href="/cricapp/public/profile.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">👤</span>
                <span class="bottom-nav-label">Profile</span>
            </a>
        </div>
    </nav>

    <script src="/cricapp/assets/js/api.js"></script>
    <script src="/cricapp/assets/js/bottom-nav.js"></script>
    <script>
        const api = new ApiClient();
        
        function showPlayerStats(playerId) {
            const modal = document.getElementById('player-stats-modal');
            const modalBody = document.getElementById('modal-player-stats');
            const modalName = document.getElementById('modal-player-name');
            
            if (!modal || !modalBody) return;
            
            // Show modal
            modal.style.display = 'flex';
            modalBody.innerHTML = '<div class="loading">Loading player stats...</div>';
            
            // Fetch player stats
            fetch(`/cricapp/api/v1/players.php/${playerId}/stats`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        const stats = data.data;
                        const player = stats.player || {};
                        
                        modalName.textContent = player.name || 'Player Stats';
                        
                        // Calculate additional stats
                        const strikeRate = (stats.total_balls_faced > 0) 
                            ? ((stats.total_runs / stats.total_balls_faced) * 100).toFixed(2) 
                            : '0.00';
                        const avgRuns = (stats.matches_played > 0) 
                            ? (stats.total_runs / stats.matches_played).toFixed(2) 
                            : '0.00';
                        const economy = (stats.total_overs_bowled > 0) 
                            ? (stats.total_runs_conceded / stats.total_overs_bowled).toFixed(2) 
                            : '0.00';
                        const bowlingAvg = (stats.total_wickets > 0) 
                            ? (stats.total_runs_conceded / stats.total_wickets).toFixed(2) 
                            : '0.00';
                        
                        modalBody.innerHTML = `
                            <div class="player-stats-grid">
                                <div class="stat-card">
                                    <h4>Matches</h4>
                                    <p class="stat-value">${stats.matches_played || 0}</p>
                                </div>
                                <div class="stat-card">
                                    <h4>Total Runs</h4>
                                    <p class="stat-value">${stats.total_runs || 0}</p>
                                </div>
                                <div class="stat-card">
                                    <h4>Total Wickets</h4>
                                    <p class="stat-value">${stats.total_wickets || 0}</p>
                                </div>
                                <div class="stat-card">
                                    <h4>Strike Rate</h4>
                                    <p class="stat-value">${strikeRate}</p>
                                    <p class="stat-label">${stats.total_balls_faced || 0} balls</p>
                                </div>
                                <div class="stat-card">
                                    <h4>Average Runs</h4>
                                    <p class="stat-value">${avgRuns}</p>
                                    <p class="stat-label">per match</p>
                                </div>
                                <div class="stat-card">
                                    <h4>Economy Rate</h4>
                                    <p class="stat-value">${economy}</p>
                                    <p class="stat-label">${stats.total_overs_bowled || 0} overs</p>
                                </div>
                            </div>
                            
                            <div class="stats-section">
                                <h4>Batting Statistics</h4>
                                <table class="stats-table">
                                    <tr>
                                        <th>Runs</th>
                                        <td><strong>${stats.total_runs || 0}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Balls Faced</th>
                                        <td>${stats.total_balls_faced || 0}</td>
                                    </tr>
                                    <tr>
                                        <th>Strike Rate</th>
                                        <td>${strikeRate}</td>
                                    </tr>
                                    <tr>
                                        <th>Average</th>
                                        <td>${avgRuns}</td>
                                    </tr>
                                    <tr>
                                        <th>Fours</th>
                                        <td>${stats.total_fours || 0}</td>
                                    </tr>
                                    <tr>
                                        <th>Sixes</th>
                                        <td>${stats.total_sixes || 0}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            ${stats.total_wickets > 0 ? `
                            <div class="stats-section">
                                <h4>Bowling Statistics</h4>
                                <table class="stats-table">
                                    <tr>
                                        <th>Wickets</th>
                                        <td><strong>${stats.total_wickets || 0}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Overs Bowled</th>
                                        <td>${stats.total_overs_bowled || 0}</td>
                                    </tr>
                                    <tr>
                                        <th>Runs Conceded</th>
                                        <td>${stats.total_runs_conceded || 0}</td>
                                    </tr>
                                    <tr>
                                        <th>Economy Rate</th>
                                        <td>${economy}</td>
                                    </tr>
                                    <tr>
                                        <th>Bowling Average</th>
                                        <td>${bowlingAvg}</td>
                                    </tr>
                                </table>
                            </div>
                            ` : ''}
                        `;
                    } else {
                        modalBody.innerHTML = '<div class="loading">Failed to load player stats. Please try again.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading player stats:', error);
                    modalBody.innerHTML = '<div class="loading">Error loading player stats. Please try again.</div>';
                });
        }
        
        function closePlayerStats() {
            const modal = document.getElementById('player-stats-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        // Close modal on overlay click
        document.getElementById('player-stats-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closePlayerStats();
            }
        });
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePlayerStats();
            }
        });
    </script>
</body>
</html>


