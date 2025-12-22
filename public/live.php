<?php
/**
 * Public Live Matches Page - Active scorecards
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/Match.php';

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLiveMatches();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Matches - Cricket Scoring</title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/public.css">
    <link rel="stylesheet" href="/cricapp/assets/css/premium-design.css">
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
        <div style="margin-bottom: 1.5rem;">
            <h1>⚡ Live Matches</h1>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">Real-time score updates</p>
        </div>

        <!-- Live Matches List -->
        <div id="live-matches-list">
            <?php if (empty($liveMatches)): ?>
                <div class="card-premium text-center" style="padding: 3rem;">
                    <p style="color: var(--text-secondary);">No live matches at the moment</p>
                </div>
            <?php else: ?>
                <?php foreach ($liveMatches as $match): ?>
                    <a href="/cricapp/public/live-match.php?id=<?= $match['match_id'] ?>" class="card-premium" style="display: block; text-decoration: none; color: inherit; margin-bottom: 1rem; border-left: 4px solid var(--danger-color);">
                        <div class="score-card-header">
                            <div>
                                <div class="score-card-title">
                                    <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?>
                                </div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                    <?= htmlspecialchars($match['series_name'] ?? 'Match') ?>
                                </div>
                            </div>
                            <span class="live-badge">LIVE</span>
                        </div>
                        <div style="margin-top: 0.75rem; font-size: 0.875rem; color: var(--text-secondary);">
                            Tap to view scorecard →
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

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
            <a href="/cricapp/public/live.php" class="bottom-nav-item active">
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

    <script src="/cricapp/assets/js/bottom-nav.js"></script>
    <script>
        // Auto-refresh every 10 seconds for live matches
        setInterval(() => {
            fetch('/cricapp/api/v1/matches.php?state=live')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Update live matches list
                        updateLiveMatches(data.data);
                    }
                })
                .catch(err => console.error('Refresh failed:', err));
        }, 10000);

        function updateLiveMatches(matches) {
            const container = document.getElementById('live-matches-list');
            if (!matches || matches.length === 0) {
                container.innerHTML = '<div class="card-premium text-center" style="padding: 3rem;"><p style="color: var(--text-secondary);">No live matches at the moment</p></div>';
                return;
            }

            container.innerHTML = matches.map(match => `
                <a href="/cricapp/public/live-match.php?id=${match.match_id}" class="card-premium" style="display: block; text-decoration: none; color: inherit; margin-bottom: 1rem; border-left: 4px solid var(--danger-color);">
                    <div class="score-card-header">
                        <div>
                            <div class="score-card-title">
                                ${match.team1_name} vs ${match.team2_name}
                            </div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                ${match.series_name || 'Match'}
                            </div>
                        </div>
                        <span class="live-badge">LIVE</span>
                    </div>
                    <div style="margin-top: 0.75rem; font-size: 0.875rem; color: var(--text-secondary);">
                        Tap to view scorecard →
                    </div>
                </a>
            `).join('');
        }
    </script>
</body>
</html>

