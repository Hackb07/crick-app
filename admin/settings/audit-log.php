<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /cricapp/admin/login.php');
    exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../classes/Database.php';

$db = Database::getInstance()->getConnection();

$sql = "SELECT al.*, u.username as admin_username
        FROM admin_action_logs al
        LEFT JOIN users u ON al.admin_id = u.user_id
        ORDER BY al.timestamp DESC
        LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute();
$logs = $stmt->fetchAll();

$user = $_SESSION['user'];
$pageTitle = 'Audit Log';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Audit Log - Admin</title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/admin.css">
    <link rel="stylesheet" href="/cricapp/assets/css/mobile-app.css">
    <link rel="stylesheet" href="/cricapp/assets/css/premium-design.css">
    <link rel="stylesheet" href="/cricapp/assets/css/responsive-upgrades.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/mobile_app_nav.php'; ?>

    <div style="padding: 1rem;">
        <div class="app-card">
            <div class="app-card-header">
                <h2 class="app-card-title">Recent Admin Actions</h2>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Resource</th>
                            <th>Reason</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No audit logs yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= date('M d, Y H:i:s', strtotime($log['timestamp'])) ?></td>
                                    <td><?= htmlspecialchars($log['admin_username'] ?? 'System') ?></td>
                                    <td><?= htmlspecialchars($log['action_type']) ?></td>
                                    <td><?= htmlspecialchars($log['resource_type']) ?> #<?= $log['resource_id'] ?></td>
                                    <td><?= htmlspecialchars($log['reason'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="/cricapp/assets/js/api.js"></script>
    <script src="/cricapp/assets/js/mobile-app.js"></script>
    <script>
        if (typeof api !== 'undefined') {
            api.setToken('<?= $_SESSION['token'] ?? '' ?>');
        }
    </script>
</body>
</html>


