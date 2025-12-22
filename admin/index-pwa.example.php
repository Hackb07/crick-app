<?php
/**
 * Admin Dashboard - PWA Mobile-First Design
 * Example implementation of the new PWA layout system
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';

requireLogin();

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLive();
$allMatches = $matchModel->getAll();
$recentMatches = array_slice($allMatches, 0, 5);

// Get stats
$totalMatches = count($allMatches);
$liveCount = count($liveMatches);
$completedCount = count(array_filter($allMatches, function($m) { return $m['state'] === 'completed'; }));

$playerModel = new Player();
$totalPlayers = count($playerModel->getAll());

$teamModel = new Team();
$totalTeams = count($teamModel->getAll());

// Page configuration
$pageTitle = "Dashboard";
$showBottomNav = true;
$activeNav = "dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1e7e34">
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title><?= e($pageTitle) ?> - CricApp Admin</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
    <!-- Service Worker removed from admin pages - they need real-time data -->
    <script>
        // Toggle filters on mobile
        function toggleFilters() {
            const content = document.querySelector('.filters-content');
            if (content) {
                content.classList.toggle('open');
            }
        }
    </script>
</head>
<body>
    <div class="app-shell">
        <!-- Mobile Header -->
        <header class="app-header">
            <button class="app-header-btn" onclick="toggleSidebar()" aria-label="Menu">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <h1 class="app-header-title"><?= e($pageTitle) ?></h1>
            <div class="app-header-actions">
                <a href="<?= adminUrl('logout.php') ?>" class="app-header-btn" aria-label="Logout">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </a>
            </div>
        </header>

        <!-- Desktop Sidebar -->
        <aside class="app-sidebar">
            <div class="app-sidebar-header">
                <a href="<?= adminUrl('index.php') ?>" class="app-sidebar-logo">🏏 CricApp</a>
            </div>
            <nav class="app-sidebar-nav">
                <a href="<?= adminUrl('index.php') ?>" class="app-nav-item active">
                    <span class="app-nav-icon">📊</span>
                    Dashboard
                </a>
                <a href="<?= adminUrl('matches/') ?>" class="app-nav-item">
                    <span class="app-nav-icon">🏏</span>
                    Matches
                </a>
                <a href="<?= adminUrl('players/') ?>" class="app-nav-item">
                    <span class="app-nav-icon">👤</span>
                    Players
                </a>
                <a href="<?= adminUrl('teams/') ?>" class="app-nav-item">
                    <span class="app-nav-icon">👥</span>
                    Teams
                </a>
                <a href="<?= adminUrl('series/') ?>" class="app-nav-item">
                    <span class="app-nav-icon">🏆</span>
                    Series
                </a>
                <a href="<?= adminUrl('users/') ?>" class="app-nav-item">
                    <span class="app-nav-icon">👥</span>
                    Users
                </a>
                <a href="<?= adminUrl('logs/') ?>" class="app-nav-item">
                    <span class="app-nav-icon">📋</span>
                    Action Logs
                </a>
                <a href="<?= adminUrl('stats/') ?>" class="app-nav-item">
                    <span class="app-nav-icon">📈</span>
                    Statistics
                </a>
                <a href="<?= adminUrl('logout.php') ?>" class="app-nav-item">
                    <span class="app-nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <!-- Desktop Top Bar -->
        <div class="app-topbar">
            <h2 class="app-topbar-title"><?= e($pageTitle) ?></h2>
            <div class="app-topbar-actions">
                <div class="app-topbar-search">
                    <input type="search" placeholder="Search..." aria-label="Search">
                    <svg class="search-bar-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="app-topbar-user">
                    <span><?= e(getSession('username', 'Admin')) ?></span>
                    <span style="color: var(--border-color);">•</span>
                    <span><?= e(getSession('role', 'admin')) ?></span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="app-content <?= $showBottomNav ? 'with-bottom-nav' : '' ?>">
            <!-- Welcome Message -->
            <div class="card mb-md">
                <div class="card-body">
                    <h2 style="font-size: 20px; margin: 0 0 var(--spacing-sm);">Welcome back, <?= e(getSession('username', 'Admin')) ?>!</h2>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 14px;">Here's what's happening with your matches today.</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalMatches ?></div>
                    <div class="stat-label">Total Matches</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #dc3545;"><?= $liveCount ?></div>
                    <div class="stat-label">Live Matches</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: var(--pwa-primary);"><?= $completedCount ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $totalPlayers ?></div>
                    <div class="stat-label">Players</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $totalTeams ?></div>
                    <div class="stat-label">Teams</div>
                </div>
            </div>

            <!-- Live Matches -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">🔥 Live Matches</h2>
                    <a href="<?= adminUrl('matches/') ?>" class="btn btn-sm btn-secondary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($liveMatches)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">🏏</div>
                            <div class="empty-state-text">No Live Matches</div>
                            <div class="empty-state-subtext">Start scoring a match to see it here!</div>
                        </div>
                    <?php else: ?>
                        <!-- Mobile Card View -->
                        <div class="data-table-mobile">
                            <?php foreach ($liveMatches as $match): ?>
                                <div class="data-row">
                                    <div class="data-row-header">
                                        <strong><?= e($match['team1_name']) ?></strong> vs <strong><?= e($match['team2_name']) ?></strong>
                                    </div>
                                    <div class="data-row-item">
                                        <span class="data-row-label">Venue</span>
                                        <span class="data-row-value"><?= e($match['venue'] ?: 'TBD') ?></span>
                                    </div>
                                    <div class="data-row-item">
                                        <span class="data-row-label">Date</span>
                                        <span class="data-row-value"><?= formatDate($match['match_date'], 'M d, Y') ?></span>
                                    </div>
                                    <div class="data-row-item">
                                        <span class="data-row-label">Status</span>
                                        <span class="data-row-value">
                                            <span class="badge badge-live">LIVE</span>
                                        </span>
                                    </div>
                                    <div class="data-row-actions">
                                        <a href="<?= adminUrl('matches/flow.php?id=' . $match['match_id']) ?>" class="btn btn-sm btn-primary">Match Flow</a>
                                        <a href="<?= adminUrl('matches/score.php?id=' . $match['match_id']) ?>" class="btn btn-sm btn-secondary">Score</a>
                                        <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" class="btn btn-sm btn-secondary">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="data-table-desktop">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Teams</th>
                                        <th>Venue</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($liveMatches as $match): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($match['team1_name']) ?></strong> vs <strong><?= e($match['team2_name']) ?></strong>
                                            </td>
                                            <td><?= e($match['venue'] ?: 'TBD') ?></td>
                                            <td><?= formatDate($match['match_date'], 'M d, Y') ?></td>
                                            <td><span class="badge badge-live">LIVE</span></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?= adminUrl('matches/flow.php?id=' . $match['match_id']) ?>" class="btn-icon" title="Match Flow">
                                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="<?= adminUrl('matches/score.php?id=' . $match['match_id']) ?>" class="btn-icon" title="Score">
                                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </a>
                                                    <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" class="btn-icon" title="View">
                                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Matches -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Matches</h2>
                    <div style="display: flex; gap: var(--spacing-sm);">
                        <a href="<?= adminUrl('matches/create.php') ?>" class="btn btn-sm btn-primary">Create Match</a>
                        <a href="<?= adminUrl('matches/') ?>" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recentMatches)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <div class="empty-state-text">No Matches Yet</div>
                            <div class="empty-state-subtext">
                                <a href="<?= adminUrl('matches/create.php') ?>" class="btn btn-primary">Create Your First Match</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Mobile Card View -->
                        <div class="data-table-mobile">
                            <?php foreach ($recentMatches as $match): ?>
                                <div class="data-row">
                                    <div class="data-row-header">
                                        <strong><?= e($match['team1_name']) ?></strong> vs <strong><?= e($match['team2_name']) ?></strong>
                                    </div>
                                    <div class="data-row-item">
                                        <span class="data-row-label">Venue</span>
                                        <span class="data-row-value"><?= e($match['venue'] ?: 'TBD') ?></span>
                                    </div>
                                    <div class="data-row-item">
                                        <span class="data-row-label">Date</span>
                                        <span class="data-row-value"><?= formatDate($match['match_date'], 'M d, Y') ?></span>
                                    </div>
                                    <div class="data-row-item">
                                        <span class="data-row-label">Status</span>
                                        <span class="data-row-value">
                                            <?php
                                            $badgeClass = 'badge-completed';
                                            if ($match['state'] === 'live') $badgeClass = 'badge-live';
                                            elseif ($match['state'] === 'scheduled') $badgeClass = 'badge-scheduled';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($match['state']) ?></span>
                                        </span>
                                    </div>
                                    <div class="data-row-actions">
                                        <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" class="btn btn-sm btn-primary">View</a>
                                        <?php if ($match['state'] === 'live'): ?>
                                            <a href="<?= adminUrl('matches/score.php?id=' . $match['match_id']) ?>" class="btn btn-sm btn-secondary">Score</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="data-table-desktop">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Teams</th>
                                        <th>Venue</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentMatches as $match): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($match['team1_name']) ?></strong> vs <strong><?= e($match['team2_name']) ?></strong>
                                            </td>
                                            <td><?= e($match['venue'] ?: 'TBD') ?></td>
                                            <td><?= formatDate($match['match_date'], 'M d, Y') ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = 'badge-completed';
                                                if ($match['state'] === 'live') $badgeClass = 'badge-live';
                                                elseif ($match['state'] === 'scheduled') $badgeClass = 'badge-scheduled';
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($match['state']) ?></span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" class="btn-icon" title="View">
                                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </a>
                                                    <?php if ($match['state'] === 'live'): ?>
                                                        <a href="<?= adminUrl('matches/score.php?id=' . $match['match_id']) ?>" class="btn-icon" title="Score">
                                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Mobile Bottom Navigation -->
        <?php if ($showBottomNav): ?>
        <nav class="app-bottom-nav">
            <a href="<?= adminUrl('index.php') ?>" class="app-bottom-nav-item active" aria-label="Dashboard">
                <span class="app-bottom-nav-icon">📊</span>
                <span class="app-bottom-nav-label">Dashboard</span>
            </a>
            <a href="<?= adminUrl('matches/') ?>" class="app-bottom-nav-item" aria-label="Matches">
                <span class="app-bottom-nav-icon">🏏</span>
                <span class="app-bottom-nav-label">Matches</span>
            </a>
            <a href="<?= adminUrl('users/') ?>" class="app-bottom-nav-item" aria-label="Users">
                <span class="app-bottom-nav-icon">👥</span>
                <span class="app-bottom-nav-label">Users</span>
            </a>
            <a href="<?= adminUrl('settings/') ?>" class="app-bottom-nav-item" aria-label="Settings">
                <span class="app-bottom-nav-icon">⚙️</span>
                <span class="app-bottom-nav-label">Settings</span>
            </a>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div class="mobile-sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.app-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (window.innerWidth < 1024) {
                sidebar.classList.toggle('open');
                if (overlay) {
                    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
                }
            }
        }
        
        function closeSidebar() {
            const sidebar = document.querySelector('.app-sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('open');
            if (overlay) {
                overlay.style.display = 'none';
            }
        }
        
        // Close sidebar on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
    </script>
    
    <style>
        /* Mobile sidebar overlay */
        .mobile-sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        @media (min-width: 1024px) {
            .mobile-sidebar-overlay {
                display: none !important;
            }
            
            .app-sidebar {
                transform: translateX(0) !important;
            }
        }
        
        @media (max-width: 1023px) {
            .app-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .app-sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</body>
</html>







