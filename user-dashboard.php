<?php
/**
 * User Dashboard - Frontend
 * Shows user's matches and provides match creation
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user-login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'] ?? $_SESSION['username'];

// Load user's matches
$db = Database::getInstance()->getConnection();
$matchModel = new MatchModel();

try {
    // Get matches created by this user
    $stmt = $db->prepare("
        SELECT m.*, 
               t1.team_name as team1_name,
               t2.team_name as team2_name,
               s.series_name
        FROM matches m
        LEFT JOIN teams t1 ON m.team1_id = t1.team_id
        LEFT JOIN teams t2 ON m.team2_id = t2.team_id
        LEFT JOIN series s ON m.series_id = s.series_id
        WHERE m.created_by = :user_id
        ORDER BY m.match_date DESC
        LIMIT 20
    ");
    $stmt->execute(['user_id' => $userId]);
    $userMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error loading user matches: " . $e->getMessage());
    $userMatches = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>My Dashboard - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f8fafc;
        }
        
        /* Hamburger Menu */
        .hamburger {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .hamburger:hover {
            background: var(--primary);
        }
        
        .hamburger span {
            width: 24px;
            height: 3px;
            background: #334155;
            margin: 3px 0;
            transition: all 0.3s;
            border-radius: 2px;
        }
        
        .hamburger:hover span {
            background: white;
        }
        
        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: white;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            transition: left 0.3s;
            z-index: 999;
            overflow-y: auto;
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, #00b894 100%);
            color: white;
        }
        
        .sidebar-header h2 {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .sidebar-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #334155;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: #f1f5f9;
            border-left-color: var(--primary);
            color: var(--primary);
        }
        
        .menu-item.active {
            background: #e0f2fe;
            border-left-color: var(--primary);
            color: var(--primary);
            font-weight: 600;
        }
        
        .menu-icon {
            font-size: 20px;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            display: none;
        }
        
        .overlay.active {
            display: block;
        }
        
        /* Main Content */
        .main-content {
            padding: 80px 20px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .dashboard-header {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        
        .dashboard-header h1 {
            font-size: 28px;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .dashboard-header p {
            color: #64748b;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #007a5e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 146, 112, 0.3);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }
        
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        
        .matches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .match-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .match-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        
        .match-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .status-live {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .match-teams {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .match-info {
            font-size: 14px;
            color: #64748b;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 16px;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #64748b;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu -->
    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>🏏 CricApp</h2>
            <p><?= e($userName) ?></p>
        </div>
        
        <div class="sidebar-menu">
            <a href="user-dashboard.php" class="menu-item active">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="create-match.php" class="menu-item">
                <span class="menu-icon">➕</span>
                <span>Create Match</span>
            </a>
            <a href="<?= publicUrl('matches.php') ?>" class="menu-item">
                <span class="menu-icon">🏏</span>
                <span>All Matches</span>
            </a>
            <a href="<?= publicUrl('index.php') ?>" class="menu-item">
                <span class="menu-icon">🏠</span>
                <span>Home</span>
            </a>
            <a href="user-logout.php" class="menu-item">
                <span class="menu-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="dashboard-header">
            <h1>Welcome, <?= e($userName) ?>!</h1>
            <p>Manage your cricket matches and tournaments</p>
            
            <div class="action-buttons">
                <a href="create-match.php" class="btn btn-primary">
                    <span>➕</span>
                    Create New Match
                </a>
                <a href="<?= publicUrl('matches.php') ?>" class="btn btn-secondary">
                    <span>🏏</span>
                    View All Matches
                </a>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px; color: #1e293b;">My Matches</h2>
        
        <?php if (empty($userMatches)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏏</div>
                <h3>No matches yet</h3>
                <p>Create your first match to get started!</p>
                <a href="create-match.php" class="btn btn-primary">Create Match</a>
            </div>
        <?php else: ?>
            <div class="matches-grid">
                <?php foreach ($userMatches as $match): ?>
                    <a href="<?= publicUrl('match-view.php?id=' . $match['match_id']) ?>" class="match-card">
                        <span class="match-status status-<?= $match['state'] ?>">
                            <?= ucfirst($match['state']) ?>
                        </span>
                        
                        <div class="match-teams">
                            <?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?>
                        </div>
                        
                        <div class="match-info">
                            <?php if ($match['series_name']): ?>
                                📌 <?= e($match['series_name']) ?><br>
                            <?php endif; ?>
                            📅 <?= formatDate($match['match_date'], 'M d, Y') ?><br>
                            📍 <?= e($match['venue'] ?? 'TBD') ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Hamburger menu toggle
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        function toggleMenu() {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        hamburger.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleMenu();
            }
        });
    </script>
</body>
</html>
