<?php
/**
 * Public Matches Page - All matches with filters
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/Match.php';

$matchModel = new MatchModel();

// Get filter parameters
$filterState = $_GET['state'] ?? null;
$filterSeries = $_GET['series_id'] ?? null;

$filters = [];
if ($filterState) $filters['state'] = $filterState;
if ($filterSeries) $filters['series_id'] = $filterSeries;

// Get all matches
$matches = $matchModel->getAll($filters);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - Cricket Scoring</title>
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
            <h1>📅 All Matches</h1>
        </div>

        <!-- Filters -->
        <div class="card-premium" style="margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <select id="filter-state" class="form-input" style="flex: 1; min-width: 150px;">
                    <option value="">All States</option>
                    <option value="live" <?= $filterState === 'live' ? 'selected' : '' ?>>Live</option>
                    <option value="scheduled" <?= $filterState === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                    <option value="completed" <?= $filterState === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
        </div>

        <!-- Matches List -->
        <div id="matches-list">
            <?php if (empty($matches)): ?>
                <div class="card-premium text-center" style="padding: 3rem;">
                    <p style="color: var(--text-secondary);">No matches found</p>
                </div>
            <?php else: ?>
                <?php foreach ($matches as $match): ?>
                    <a href="/cricapp/public/match-view.php?id=<?= $match['match_id'] ?>" class="card-premium" style="display: block; text-decoration: none; color: inherit; margin-bottom: 1rem;">
                        <div class="score-card-header">
                            <div>
                                <div class="score-card-title">
                                    <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?>
                                </div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                    <?= htmlspecialchars($match['series_name'] ?? 'Match') ?>
                                </div>
                            </div>
                            <div>
                                <?php if ($match['state'] === 'live'): ?>
                                    <span class="live-badge">LIVE</span>
                                <?php else: ?>
                                    <span class="status-badge <?= $match['state'] ?>" style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; background: var(--bg-soft); color: var(--text-secondary);">
                                        <?= ucfirst($match['state']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary);">
                            <?= date('M d, Y h:i A', strtotime($match['match_date'])) ?>
                        </div>
                        <?php if ($match['venue']): ?>
                            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                📍 <?= htmlspecialchars($match['venue']) ?>
                            </div>
                        <?php endif; ?>
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
            <a href="/cricapp/public/matches.php" class="bottom-nav-item active">
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

    <script src="/cricapp/assets/js/bottom-nav.js"></script>
    <script>
        // Filter functionality
        document.getElementById('filter-state')?.addEventListener('change', function() {
            const state = this.value;
            const url = new URL(window.location);
            if (state) {
                url.searchParams.set('state', state);
            } else {
                url.searchParams.delete('state');
            }
            window.location.href = url.toString();
        });
    </script>
</body>
</html>

