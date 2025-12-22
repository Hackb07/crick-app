<?php
/**
 * Public Portal - Home Page
 * Redirects to Vue.js version (default)
 */

// Redirect to Vue.js version for better performance
if (file_exists(__DIR__ . '/index-vue.php')) {
    require_once __DIR__ . '/index-vue.php';
    exit;
}

// Fallback to old version if Vue.js not available
require_once __DIR__ . '/../includes/bootstrap.php';

$matchModel = new MatchModel();

// Get live matches
$liveMatches = $matchModel->getLiveMatches();

// Get recent matches
$recentMatches = $matchModel->getRecentMatches(5);

// Get scheduled matches
$scheduledMatches = $matchModel->getScheduledMatches();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cricket Scoring - Live Scores & Match Updates</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/public.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
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
                    <a href="<?= publicUrl() ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏠 Home</a>
                    <a href="<?= publicUrl('leaderboard.php') ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏆 Leaderboard</a>
                    <a href="<?= publicUrl('matches.php') ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">📅 Matches</a>
                    <a href="<?= publicUrl('live.php') ?>" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; font-size: 0.875rem;">⚡ Live</a>
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
        <!-- Live Matches Section -->
        <section class="section">
            <h2>🔥 Live Matches</h2>
            <div id="live-matches" class="matches-grid">
                <div id="live-matches-skeleton" class="skeleton skeleton-card" style="display: none;"></div>
                <?php if (empty($liveMatches)): ?>
                    <div class="no-matches">No live matches at the moment</div>
                <?php else: ?>
                    <?php foreach ($liveMatches as $matchData): ?>
                        <div class="match-card live">
                            <div class="match-header">
                                <span class="live-badge">LIVE</span>
                                <span class="series-name"><?= htmlspecialchars($matchData['series_name'] ?? 'Match') ?></span>
                            </div>
                            <div class="match-teams">
                                <div class="team">
                                    <strong><?= htmlspecialchars($matchData['team1_name']) ?></strong>
                                </div>
                                <div class="vs">vs</div>
                                <div class="team">
                                    <strong><?= htmlspecialchars($matchData['team2_name']) ?></strong>
                                </div>
                            </div>
                            <div class="match-info">
                                <a href="/cricapp/public/live-match.php?id=<?= $matchData['match_id'] ?>" class="btn btn-primary">
                                    View Live
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Recent Matches Section -->
        <section class="section">
            <h2>📅 Recent Matches</h2>
            <div id="recent-matches" class="matches-grid">
                <?php if (empty($recentMatches)): ?>
                    <div class="no-matches">No recent matches</div>
                <?php else: ?>
                    <?php foreach ($recentMatches as $matchData): ?>
                        <div class="match-card">
                            <div class="match-header">
                                <span class="status-badge completed">Completed</span>
                                <span class="series-name"><?= htmlspecialchars($matchData['series_name'] ?? 'Match') ?></span>
                            </div>
                            <div class="match-teams">
                                <div class="team"><?= htmlspecialchars($matchData['team1_name']) ?></div>
                                <div class="vs">vs</div>
                                <div class="team"><?= htmlspecialchars($matchData['team2_name']) ?></div>
                            </div>
                            <div class="match-date">
                                <?= date('M d, Y', strtotime($matchData['match_date'])) ?>
                            </div>
                            <div class="match-info">
                                <a href="/cricapp/public/match-view.php?id=<?= $matchData['match_id'] ?>" class="btn btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-2">
                <a href="/cricapp/public/recent-matches.php" class="btn btn-secondary">View All Recent Matches</a>
            </div>
        </section>

        <!-- Scheduled Matches Section -->
        <section class="section">
            <h2>📆 Scheduled Matches</h2>
            <div id="scheduled-matches" class="matches-grid">
                <?php if (empty($scheduledMatches)): ?>
                    <div class="no-matches">No scheduled matches</div>
                <?php else: ?>
                    <?php foreach ($scheduledMatches as $matchData): ?>
                        <div class="match-card scheduled">
                            <div class="match-header">
                                <span class="status-badge scheduled">Scheduled</span>
                                <span class="series-name"><?= htmlspecialchars($matchData['series_name'] ?? 'Match') ?></span>
                            </div>
                            <div class="match-teams">
                                <div class="team"><?= htmlspecialchars($matchData['team1_name']) ?></div>
                                <div class="vs">vs</div>
                                <div class="team"><?= htmlspecialchars($matchData['team2_name']) ?></div>
                            </div>
                            <div class="match-date">
                                <strong><?= date('M d, Y', strtotime($matchData['match_date'])) ?></strong><br>
                                <?= date('h:i A', strtotime($matchData['match_date'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-2">
                <a href="/cricapp/public/scheduled-matches.php" class="btn btn-secondary">View All Scheduled Matches</a>
            </div>
        </section>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav" id="bottom-nav">
        <div class="bottom-nav-container">
            <a href="/cricapp/public/" class="bottom-nav-item active">
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
            <a href="/cricapp/public/leaderboard.php" class="bottom-nav-item">
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
    <script src="/cricapp/assets/js/public.js"></script>
    <script src="/cricapp/assets/js/bottom-nav.js"></script>
    <script src="/cricapp/assets/js/skeleton-loader.js"></script>
</body>
</html>

