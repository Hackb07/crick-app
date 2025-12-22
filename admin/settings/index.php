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

// Get system settings (if you have a settings table, query it here)
// For now, showing config values

$user = $_SESSION['user'];
$pageTitle = 'System Settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Settings - Admin</title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/admin.css">
    <link rel="stylesheet" href="/cricapp/assets/css/mobile-app.css">
    <link rel="stylesheet" href="/cricapp/assets/css/premium-design.css">
    <link rel="stylesheet" href="/cricapp/assets/css/responsive-upgrades.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/mobile_app_nav.php'; ?>

    <div style="padding: 1rem;">
        <div class="app-card" style="margin-bottom: 1rem;">
            <div class="app-card-header">
                <h2 class="app-card-title">Configuration</h2>
            </div>
            <div style="padding: 1rem;">
                <table class="table" style="width: 100%;">
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>App Name</td>
                    <td><?= APP_NAME ?></td>
                </tr>
                <tr>
                    <td>App Version</td>
                    <td><?= APP_VERSION ?></td>
                </tr>
                <tr>
                    <td>Database</td>
                    <td><?= DB_NAME ?></td>
                </tr>
                <tr>
                    <td>JWT Expiry</td>
                    <td><?= JWT_EXPIRY ?> seconds (<?= JWT_EXPIRY / 3600 ?> hours)</td>
                </tr>
            </table>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <h2 class="app-card-title">Quick Links</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem;">
                <a href="/cricapp/admin/settings/audit-log.php" class="btn btn-primary btn-block">View Audit Log</a>
                <a href="/cricapp/admin/players/create.php" class="btn btn-secondary btn-block">Add Player</a>
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

