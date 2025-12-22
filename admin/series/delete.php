<?php
/**
 * Delete Series - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$seriesId = (int)getQuery('id', 0);
if (!$seriesId) {
    header('Location: ' . adminUrl('series/'));
    exit;
}

$seriesModel = new Series();
$series = $seriesModel->getById($seriesId);

if (!$series) {
    header('Location: ' . adminUrl('series/'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('confirm')) {
    $result = $seriesModel->delete($seriesId);
    
    if ($result) {
        header('Location: ' . adminUrl('series/'));
        exit;
    } else {
        $error = 'Failed to delete series';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Series - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-enhanced.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-fixes.css') ?>">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="sidebar">
            <div class="admin-sidebar-header">
                <a href="<?= adminUrl('index.php') ?>" class="admin-sidebar-logo">🏏 CricApp</a>
            </div>
            <nav class="admin-sidebar-nav">
                <a href="<?= adminUrl('index.php') ?>" class="admin-nav-item">
                    <span class="admin-nav-item-icon">📊</span> Dashboard
                </a>
                <a href="<?= adminUrl('matches/') ?>" class="admin-nav-item">
                    <span class="admin-nav-item-icon">🏏</span> Matches
                </a>
                <a href="<?= adminUrl('players/') ?>" class="admin-nav-item">
                    <span class="admin-nav-item-icon">👤</span> Players
                </a>
                <a href="<?= adminUrl('teams/') ?>" class="admin-nav-item">
                    <span class="admin-nav-item-icon">👥</span> Teams
                </a>
                <a href="<?= adminUrl('series/') ?>" class="admin-nav-item active">
                    <span class="admin-nav-item-icon">🏆</span> Series
                </a>
                <a href="<?= adminUrl('logout.php') ?>" class="admin-nav-item">
                    <span class="admin-nav-item-icon">🚪</span> Logout
                </a>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="admin-header-title">Delete Series</h1>
                    <p style="color: var(--text-secondary); margin: 0;">Confirm deletion</p>
                </div>
                <a href="<?= adminUrl('series/view.php?id=' . $seriesId) ?>" class="btn btn-secondary">← Back</a>
            </div>

            <div class="card">
                <?php if (isset($error)): ?>
                    <div class="error"><?= e($error) ?></div>
                <?php endif; ?>

                <div style="text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 4rem; margin-bottom: var(--spacing-md);">⚠️</div>
                    <h2 style="margin-bottom: var(--spacing-md);">Are you sure?</h2>
                    <p style="margin-bottom: var(--spacing-lg); color: var(--text-secondary);">
                        You are about to delete <strong><?= e($series['name']) ?></strong>. This action cannot be undone.
                    </p>
                    
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn" style="background: var(--live-red); color: white; margin-right: var(--spacing-md);">Yes, Delete</button>
                        <a href="<?= adminUrl('series/view.php?id=' . $seriesId) ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <button class="mobile-sidebar-toggle" onclick="toggleSidebar()">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
    </button>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
        }
    </script>
</body>
</html>

