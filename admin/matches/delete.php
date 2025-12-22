<?php
/**
 * Delete Match - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireLogin();

$matchId = (int)getQuery('id', 0);
if (!$matchId) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

$matchModel = new MatchModel();
$match = $matchModel->getById($matchId);

if (!$match) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('confirm')) {
    try {
        $result = $matchModel->delete($matchId);
        
        if ($result) {
            // Log action
            if (function_exists('logAction')) {
                logAction('delete', 'match', $matchId, ['match_name' => $match['team1_name'] . ' vs ' . $match['team2_name']]);
            }
            
            $_SESSION['success'] = 'Match deleted successfully.';
            header('Location: ' . adminUrl('matches/'));
            exit;
        } else {
            $error = 'Failed to delete match. Please check error logs for details.';
        }
    } catch (PDOException $e) {
        $errorInfo = $e->errorInfo ?? [];
        $errorMsg = 'Database error: ' . ($errorInfo[2] ?? $e->getMessage());
        error_log('Delete match PDO error: ' . $e->getMessage() . ' | SQL State: ' . ($errorInfo[0] ?? 'N/A') . ' | Error Code: ' . ($errorInfo[1] ?? 'N/A'));
        $error = $errorMsg . ' Please check error logs for details.';
    } catch (Exception $e) {
        error_log('Delete match error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        $error = 'An error occurred while deleting the match: ' . $e->getMessage() . '. Please check error logs for details.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Match - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-enhanced.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin-fixes.css') ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php renderAdminSidebar('matches'); ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="admin-header-title">Delete Match</h1>
                    <p style="color: var(--text-secondary); margin: 0;">Confirm deletion</p>
                </div>
                <a href="<?= adminUrl('matches/view.php?id=' . $matchId) ?>" class="btn btn-secondary">← Back</a>
            </div>

            <div class="card">
                <?php if (isset($error)): ?>
                    <div class="error"><?= e($error) ?></div>
                <?php endif; ?>

                <div style="text-align: center; padding: var(--spacing-xl);">
                    <div style="font-size: 4rem; margin-bottom: var(--spacing-md);">⚠️</div>
                    <h2 style="margin-bottom: var(--spacing-md);">Are you sure?</h2>
                    <p style="margin-bottom: var(--spacing-lg); color: var(--text-secondary);">
                        You are about to delete the match <strong><?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?></strong>. This action cannot be undone.
                    </p>
                    
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn" style="background: var(--live-red); color: white; margin-right: var(--spacing-md);">Yes, Delete</button>
                        <a href="<?= adminUrl('matches/view.php?id=' . $matchId) ?>" class="btn btn-secondary">Cancel</a>
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
            const sidebar = document.getElementById('sidebar');
            const wrapper = document.querySelector('.admin-wrapper');
            sidebar.classList.toggle('open');
            wrapper.classList.toggle('sidebar-open');
        }
        
        // Close sidebar when clicking overlay on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const wrapper = document.querySelector('.admin-wrapper');
            const toggle = document.querySelector('.mobile-sidebar-toggle');
            
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target)) {
                sidebar.classList.remove('open');
                wrapper.classList.remove('sidebar-open');
            }
        });
    </script>
</body>
</html>

