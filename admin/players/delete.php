<?php
/**
 * Delete Player - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireLogin();

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$playerId = (int)getQuery('id', 0);
if (!$playerId) {
    header('Location: ' . adminUrl('players/'));
    exit;
}

$playerModel = new Player();
$player = $playerModel->getById($playerId);

if (!$player) {
    header('Location: ' . adminUrl('players/'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('confirm')) {
    try {
        $result = $playerModel->delete($playerId);
        
        if ($result) {
            // Log action
            logAction('delete', 'player', $playerId, ['name' => $player['name']]);
            
            $_SESSION['success_message'] = 'Player deleted successfully';
            header('Location: ' . adminUrl('players/'));
            exit;
        } else {
            $error = 'Failed to delete player';
        }
    } catch (PDOException $e) {
        error_log('Player deletion error: ' . $e->getMessage());
        $error = 'Failed to delete player: ' . $e->getMessage();
    } catch (Exception $e) {
        error_log('Player deletion error: ' . $e->getMessage());
        $error = 'Failed to delete player: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Player - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-enhanced.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-fixes.css') ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php renderAdminSidebar('players'); ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="admin-header-title">Delete Player</h1>
                    <p style="color: var(--text-secondary); margin: 0;">Confirm deletion</p>
                </div>
                <a href="<?= adminUrl('players/view.php?id=' . $playerId) ?>" class="btn btn-secondary">← Back</a>
            </div>

            <div class="card">
                <?php if (!empty($error)): ?>
                    <div class="error" style="margin-bottom: var(--spacing-lg);"><?= e($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="success" style="margin-bottom: var(--spacing-lg);"><?= e($_SESSION['success_message']) ?></div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <div style="text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 4rem; margin-bottom: var(--spacing-md);">⚠️</div>
                    <h2 style="margin-bottom: var(--spacing-md);">Are you sure?</h2>
                    <p style="margin-bottom: var(--spacing-lg); color: var(--text-secondary);">
                        You are about to delete <strong><?= e($player['name']) ?></strong>. This action cannot be undone.
                    </p>
                    
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn" style="background: var(--live-red); color: white; margin-right: var(--spacing-md);">Yes, Delete</button>
                        <a href="<?= adminUrl('players/view.php?id=' . $playerId) ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <button class="mobile-sidebar-toggle" onclick="toggleSidebar()">
        <svg width="24" height="24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
    </button>

    <script src="<?= adminUrl('includes/sidebar-toggle.js') ?>"></script>
</body>
</html>

