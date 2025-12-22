<?php
/**
 * Admin Dashboard - Premium Design
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/includes/sidebar.php';

requireLogin();

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLive();
$allMatches = $matchModel->getAll();
$recentMatches = array_slice($allMatches, 0, 5);

// Get stats
$db = Database::getInstance()->getConnection();
$totalMatches = count($allMatches);
$liveCount = count($liveMatches);
$completedCount = count(array_filter($allMatches, function($m) { return $m['state'] === 'completed'; }));

$playerModel = new Player();
$totalPlayers = count($playerModel->getAll());

$teamModel = new Team();
$totalTeams = count($teamModel->getAll());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/../includes/cache-prevention-meta.php'; ?>
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title>Admin Dashboard - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
</head>
<body>
    <div class="app-shell">
        <?php renderAdminSidebar('dashboard'); ?>

        <header class="app-header">
            <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                ☰
            </button>
            <div class="header-title">Dashboard</div>
            <div class="header-actions">
                <a href="<?= adminUrl('logout.php') ?>" class="btn-icon" aria-label="Logout">
                    🚪
                </a>
            </div>
        </header>

        <main class="app-main">
            <div class="content-container">
                
                <!-- Welcome Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 style="margin: 0; font-size: 18px;">Welcome, <?= e(getSession('username', 'Admin')) ?>!</h2>
                        <p style="color: var(--text-muted); margin: 4px 0 0; font-size: 14px;">
                            <?= e(getSession('role', 'admin')) ?> • CricApp Admin Panel
                        </p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?= $totalMatches ?></div>
                        <div class="stat-label">Matches</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--danger);"><?= $liveCount ?></div>
                        <div class="stat-label">Live</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--success);"><?= $completedCount ?></div>
                        <div class="stat-label">Done</div>
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
                        <span>🔥 Live Matches</span>
                        <a href="<?= adminUrl('matches/') ?>" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                </div>
                
                <?php if (empty($recentMatches)): ?>
                    <?= renderEmptyState(
                        '📋', 
                        'No Matches Yet', 
                        '', 
                        ['text' => 'Create Your First Match', 'url' => adminUrl('matches/create.php')]
                    ) ?>
                <?php else: ?>
                    <div class="data-table">
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
                                            <?= renderBadge(ucfirst($match['state']), $match['state']) ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" class="btn-icon btn-icon-view" title="View">
                                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
                                                </a>
                                                <?php if ($match['state'] === 'live'): ?>
                                                    <a href="<?= adminUrl('matches/score.php?id=' . $match['match_id']) ?>" class="btn-icon btn-icon-edit" title="Score">
                                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
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
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.app-shell').classList.toggle('sidebar-open');
        }
    </script>
</body>
</html>
