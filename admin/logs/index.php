<?php
/**
 * Admin Action Logs Viewer - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../includes/sidebar.php';

requireLogin();

$logger = new ActionLogger();

// Get filter parameters
$resourceType = getQuery('resource_type', '');
$action = getQuery('action', '');
$userId = getQuery('user_id') ? (int)getQuery('user_id') : null;
$dateFrom = getQuery('date_from', '');
$dateTo = getQuery('date_to', '');

$filters = [];
if ($resourceType) {
    $filters['resource_type'] = $resourceType;
}
if ($action) {
    $filters['action'] = $action;
}
if ($userId) {
    $filters['user_id'] = $userId;
}
if ($dateFrom) {
    $filters['date_from'] = $dateFrom;
}
if ($dateTo) {
    $filters['date_to'] = $dateTo;
}

$logs = $logger->getLogs($filters);

// Get all users for filter dropdown
$userModel = new User();
$allUsers = $userModel->getAll(['sort' => 'username_asc']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/../../includes/cache-prevention-meta.php'; ?>
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title>Action Logs - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
    <style>
        .log-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .log-table th, .log-table td {
            padding: 12px 8px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .log-table th {
            text-align: left;
            font-weight: 600;
            color: var(--text-muted);
            background: var(--bg-body);
            position: sticky;
            top: 0;
            z-index: 1;
        }
        @media (max-width: 768px) {
            .log-table {
                font-size: 0.75rem;
            }
            .log-table th, .log-table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?php renderAdminSidebar('logs'); ?>

        <header class="app-header">
            <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                ☰
            </button>
            <div class="header-title">Action Logs</div>
            <div class="header-actions">
                <a href="<?= adminUrl('logs/') ?>" class="btn-icon" aria-label="Clear Filters" title="Clear Filters">
                    🔄
                </a>
            </div>
        </header>

        <main class="app-main">
            <div class="content-container">
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET">
                            <div style="margin-bottom: 12px;">
                                <label for="resource_type" class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Resource Type</label>
                                <select id="resource_type" name="resource_type" class="form-select" onchange="this.form.submit()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                                    <option value="">All Types</option>
                                    <option value="match" <?= $resourceType === 'match' ? 'selected' : '' ?>>Match</option>
                                    <option value="player" <?= $resourceType === 'player' ? 'selected' : '' ?>>Player</option>
                                    <option value="team" <?= $resourceType === 'team' ? 'selected' : '' ?>>Team</option>
                                    <option value="series" <?= $resourceType === 'series' ? 'selected' : '' ?>>Series</option>
                                    <option value="user" <?= $resourceType === 'user' ? 'selected' : '' ?>>User</option>
                                </select>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label for="action" class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Action</label>
                                <select id="action" name="action" class="form-select" onchange="this.form.submit()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                                    <option value="">All Actions</option>
                                    <option value="create" <?= $action === 'create' ? 'selected' : '' ?>>Create</option>
                                    <option value="update" <?= $action === 'update' ? 'selected' : '' ?>>Update</option>
                                    <option value="delete" <?= $action === 'delete' ? 'selected' : '' ?>>Delete</option>
                                    <option value="assign_players" <?= $action === 'assign_players' ? 'selected' : '' ?>>Assign Players</option>
                                </select>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <label for="user_id" class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">User</label>
                                <select id="user_id" name="user_id" class="form-select" onchange="this.form.submit()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                                    <option value="">All Users</option>
                                    <?php foreach ($allUsers as $u): ?>
                                        <option value="<?= $u['user_id'] ?>" <?= $userId == $u['user_id'] ? 'selected' : '' ?>>
                                            <?= e($u['username']) ?> <?= $u['full_name'] ? '(' . e($u['full_name']) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <label for="date_from" class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">From</label>
                                    <input type="date" id="date_from" name="date_from" class="form-input" value="<?= e($dateFrom) ?>" onchange="this.form.submit()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                                </div>
                                <div>
                                    <label for="date_to" class="form-label" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">To</label>
                                    <input type="date" id="date_to" name="date_to" class="form-input" value="<?= e($dateTo) ?>" onchange="this.form.submit()" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--bg-body);">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Audit Trail (<?= count($logs) ?> entries)</h3>
                    </div>
                    <?php if (empty($logs)): ?>
                        <div class="card-body text-center">
                            <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                            <h3 style="margin-bottom: 8px;">No Logs Found</h3>
                            <p style="color: var(--text-muted);">Action logs will appear here as admin actions are performed.</p>
                        </div>
                    <?php else: ?>
                        <div class="log-table-container">
                            <table class="log-table">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Resource</th>
                                        <th>ID</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td>
                                                <div style="font-size: 0.75rem;"><?= date('M d', strtotime($log['timestamp'])) ?></div>
                                                <div style="font-size: 0.7rem; color: var(--text-muted);"><?= date('H:i', strtotime($log['timestamp'])) ?></div>
                                            </td>
                                            <td>
                                                <div style="font-weight: 500;"><?= e($log['username'] ?? 'Unknown') ?></div>
                                                <?php if ($log['full_name']): ?>
                                                    <div style="font-size: 0.7rem; color: var(--text-muted);"><?= e($log['full_name']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary" style="font-size: 0.7rem;"><?= e($log['action_type']) ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary" style="font-size: 0.7rem;"><?= e($log['resource_type']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($log['resource_id']): ?>
                                                    <a href="<?= adminUrl($log['resource_type'] . 's/view.php?id=' . $log['resource_id']) ?>" style="color: var(--primary); font-weight: 500;">
                                                        #<?= $log['resource_id'] ?>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($log['reason']): ?>
                                                    <?php
                                                    $details = json_decode($log['reason'], true);
                                                    if ($details && is_array($details)) {
                                                        echo '<div style="font-size: 0.7rem; color: var(--text-muted); max-width: 200px; overflow: hidden; text-overflow: ellipsis;">';
                                                        echo implode(', ', array_map(function($k, $v) {
                                                            return $k . ': ' . (is_array($v) ? json_encode($v) : $v);
                                                        }, array_keys($details), $details));
                                                        echo '</div>';
                                                    } else {
                                                        echo '<div style="font-size: 0.7rem;">' . e(substr($log['reason'], 0, 30)) . '</div>';
                                                    }
                                                    ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
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
    </div>
    <script>
        function toggleSidebar() {
            document.querySelector('.app-shell').classList.toggle('sidebar-open');
        }
    </script>
</body>
</html>

