<?php
/**
 * Delete User - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$userId = (int)getQuery('id', 0);
if (!$userId) {
    header('Location: ' . adminUrl('users/'));
    exit;
}

$userModel = new User();
$user = $userModel->getById($userId);

if (!$user) {
    header('Location: ' . adminUrl('users/'));
    exit;
}

// Prevent deleting yourself
if ($userId == getUserId()) {
    header('Location: ' . adminUrl('users/view.php?id=' . $userId) . '&error=Cannot delete your own account');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('confirm')) {
    $userModel = new User();
    $result = $userModel->delete($userId);
    
    if ($result) {
        // Log action
        logAction('delete', 'user', $userId, ['username' => $user['username']]);
        
        header('Location: ' . adminUrl('users/'));
        exit;
    } else {
        $error = 'Failed to delete user';
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
    <title>Delete User - <?= e($user['username']) ?> - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
</head>
<body>
    <div class="app-shell">
        <?php renderAdminSidebar('users'); ?>

        <header class="app-header">
            <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                ☰
            </button>
            <div class="header-title">Delete User</div>
            <div class="header-actions">
                <a href="<?= adminUrl('users/view.php?id=' . $userId) ?>" class="btn-icon" aria-label="Back">
                    ←
                </a>
            </div>
        </header>

        <main class="app-main">
            <div class="content-container">
                
                <?php if (isset($error)): ?>
                    <div class="card mb-4" style="background: #fef2f2; border-color: #fee2e2;">
                        <div class="card-body" style="color: var(--danger);">
                            <?= e($error) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body text-center" style="padding: 32px 16px;">
                        <div style="font-size: 48px; margin-bottom: 16px;">⚠️</div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">Are you sure?</h2>
                        <p style="color: var(--text-muted); margin-bottom: 24px;">
                            You are about to delete user <strong><?= e($user['username']) ?></strong>.<br>
                            This action cannot be undone.
                        </p>
                        
                        <form method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                            <input type="hidden" name="confirm" value="1">
                            <button type="submit" class="btn btn-primary" style="background: var(--danger); border-color: var(--danger); width: 100%;">Yes, Delete User</button>
                            <a href="<?= adminUrl('users/view.php?id=' . $userId) ?>" class="btn btn-secondary" style="width: 100%; text-align: center;">Cancel</a>
                        </form>
                    </div>
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

