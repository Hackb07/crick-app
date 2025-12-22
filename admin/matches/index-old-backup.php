<?php
/**
 * Matches Listing - Admin Panel
 */

// Enable error logging (but don't display errors on page)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log
ini_set('log_errors', 1);

// Initialize error variable
$error = '';

try {
    require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../includes/sidebar.php';
} catch (Exception $e) {
    error_log('Matches Index: Bootstrap error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    $error = 'Error loading application. Please check server logs.';
} catch (Error $e) {
    error_log('Matches Index: Bootstrap fatal error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
    $error = 'Fatal error loading application. Please check server logs.';
}

if (empty($error)) {
    try {
        require_once __DIR__ . '/../../includes/session.php';
    } catch (Exception $e) {
        error_log('Matches Index: Session file error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        $error = 'Error loading session. Please check server logs.';
    } catch (Error $e) {
        error_log('Matches Index: Session file fatal error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        $error = 'Fatal error loading session. Please check server logs.';
    }
}

if (empty($error)) {
    try {
        requireLogin();
    } catch (Exception $e) {
        error_log('Matches Index: Authentication error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        // Redirect handled by requireLogin()
        exit;
    } catch (Error $e) {
        error_log('Matches Index: Authentication fatal error - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        $error = 'Fatal authentication error. Please check server logs.';
    }
}

$state = getQuery('state', '');
$filters = [];
if ($state) {
    $filters['state'] = $state;
}

$matches = [];
if (empty($error)) {
    try {
        $matchModel = new MatchModel();
        $matches = $matchModel->getAll($filters);
    } catch (PDOException $e) {
        error_log('Matches Index: Database error - ' . $e->getMessage() . ' | Code: ' . $e->getCode() . ' | SQL State: ' . $e->getCode() . ' | Trace: ' . $e->getTraceAsString());
        $matches = [];
        $error = 'Database error occurred. Please check server logs.';
    } catch (Exception $e) {
        error_log('Matches Index: Error fetching matches - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        $matches = [];
        $error = 'Error loading matches. Please check server logs.';
    } catch (Error $e) {
        error_log('Matches Index: Fatal error fetching matches - ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        $matches = [];
        $error = 'Fatal error loading matches. Please check server logs.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/../../includes/cache-prevention-meta.php'; ?>
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title>Matches - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
</head>
<body>
    <div class="app-shell">
        <?php renderAdminSidebar('matches'); ?>

        <header class="app-header">
            <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                ☰
            </button>
            <div class="header-title">Matches</div>
            <div class="header-actions">
                <a href="<?= adminUrl('matches/create.php') ?>" class="btn-icon" aria-label="Create Match">
                    ➕
                </a>
            </div>
        </header>

        <main class="app-main">
            <div class="content-container">
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body" style="overflow-x: auto; white-space: nowrap; padding: 12px; display: flex; gap: 8px;">
                        <a href="<?= adminUrl('matches/') ?>" class="btn btn-sm <?= !$state ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                        <a href="<?= adminUrl('matches/?state=live') ?>" class="btn btn-sm <?= $state === 'live' ? 'btn-primary' : 'btn-secondary' ?>">Live</a>
                        <a href="<?= adminUrl('matches/?state=completed') ?>" class="btn btn-sm <?= $state === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
                        <a href="<?= adminUrl('matches/?state=scheduled') ?>" class="btn btn-sm <?= $state === 'scheduled' ? 'btn-primary' : 'btn-secondary' ?>">Scheduled</a>
                        <a href="<?= adminUrl('matches/?state=draft') ?>" class="btn btn-sm <?= $state === 'draft' ? 'btn-primary' : 'btn-secondary' ?>">Draft</a>
                    </div>
                </div>

                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                    <div class="card" style="background: #fef2f2; border-color: #fee2e2;">
                        <div class="card-body" style="color: var(--danger);">
                            <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Matches List -->
                <div class="card">
                    <?php if (empty($matches)): ?>
                        <div class="card-body" style="text-align: center; padding: 48px 24px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🏏</div>
                            <h3 style="margin: 0 0 8px;">No Matches Found</h3>
                            <p style="color: var(--text-muted); margin-bottom: 24px;">Get started by creating a new match.</p>
                            <a href="<?= adminUrl('matches/create.php') ?>" class="btn btn-primary">Create Match</a>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($matches as $index => $match): ?>
                                <?php
                                try {
                                    $matchId = (int)($match['match_id'] ?? 0);
                                    $team1Name = $match['team1_name'] ?? 'Unknown';
                                    $team2Name = $match['team2_name'] ?? 'Unknown';
                                    $seriesName = $match['series_name'] ?? null;
                                    $venue = $match['venue'] ?? null;
                                    $matchDate = $match['match_date'] ?? null;
                                    $matchState = $match['state'] ?? 'unknown';
                                    
                                    if ($matchId <= 0) continue;
                                    
                                    $flowUrl = adminUrl('matches/flow.php?id=' . $matchId);
                                    $viewUrl = adminUrl('matches/view.php?id=' . $matchId);
                                    $editUrl = adminUrl('matches/edit.php?id=' . $matchId);
                                    $scoreUrl = adminUrl('matches/score.php?id=' . $matchId);
                                    $deleteUrl = adminUrl('matches/delete.php?id=' . $matchId);
                                    
                                    $badgeColor = 'var(--text-muted)';
                                    $badgeBg = 'var(--bg-body)';
                                    if ($matchState === 'live') {
                                        $badgeColor = 'white';
                                        $badgeBg = 'var(--danger)';
                                    } elseif ($matchState === 'completed') {
                                        $badgeColor = 'white';
                                        $badgeBg = 'var(--success)';
                                    }
                                } catch (Exception $e) { continue; }
                                ?>
                                <div class="list-item" style="flex-direction: column; align-items: stretch; gap: 12px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <div>
                                            <div class="list-item-title" style="font-size: 16px;">
                                                <?= e($team1Name) ?> vs <?= e($team2Name) ?>
                                            </div>
                                            <div class="list-item-subtitle">
                                                <?= e($seriesName ?: 'Friendly') ?> • <?= e($venue ?: 'TBD') ?>
                                            </div>
                                            <div class="list-item-subtitle" style="margin-top: 2px;">
                                                📅 <?= formatDate($matchDate, 'M d, Y h:i A') ?>
                                            </div>
                                        </div>
                                        <span style="font-size: 11px; padding: 4px 8px; border-radius: 4px; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid var(--border); font-weight: 600;">
                                            <?= strtoupper($matchState) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="action-buttons" style="display: flex; gap: 8px; border-top: 1px solid var(--border); padding-top: 12px;">
                                        <a href="<?= $flowUrl ?>" class="btn btn-sm btn-secondary" style="flex: 1;">Flow</a>
                                        <a href="<?= $viewUrl ?>" class="btn btn-sm btn-secondary" style="flex: 1;">View</a>
                                        <?php if ($matchState === 'live'): ?>
                                            <a href="<?= adminUrl('matches/scorer.php?id=' . $matchId) ?>" class="btn btn-sm" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; font-weight: 600;">🏏 Score</a>
                                        <?php endif; ?>
                                        <a href="<?= $editUrl ?>" class="btn btn-sm btn-secondary" style="width: auto;">✏️</a>
                                        <a href="<?= $deleteUrl ?>" class="btn btn-sm btn-secondary" style="width: auto; color: var(--danger);" onclick="return confirm('Delete match?')">🗑️</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
    </script>
</body>
</html>
