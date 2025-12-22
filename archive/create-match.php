<?php
/**
 * Create Match - Frontend User
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user-login.php');
    exit;
}

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['full_name'] ?? $_SESSION['username'];

$error = '';
$success = '';

// Load teams and series for dropdowns
$db = Database::getInstance()->getConnection();

try {
    $teamsStmt = $db->query("SELECT team_id, team_name FROM teams ORDER BY team_name");
    $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $seriesStmt = $db->query("SELECT series_id, series_name FROM series ORDER BY series_name");
    $series = $seriesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error loading data: " . $e->getMessage());
    $teams = [];
    $series = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireCsrfToken();

        $team1Id = (int)getPost('team1_id', 0);
        $team2Id = (int)getPost('team2_id', 0);
        $seriesId = getPost('series_id') ? (int)getPost('series_id') : null;
        $venue = trim(getPost('venue', ''));
        $matchDate = getPost('match_date', '');
        $oversPerInnings = (int)getPost('overs_per_innings', 20);
        
        // Validation
        if ($team1Id === 0 || $team2Id === 0) {
            $error = 'Please select both teams';
        } elseif ($team1Id === $team2Id) {
            $error = 'Please select different teams';
        } elseif (empty($matchDate)) {
            $error = 'Please select match date and time';
        } elseif ($oversPerInnings < 1 || $oversPerInnings > 50) {
            $error = 'Overs per innings must be between 1 and 50';
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO matches (
                        team1_id, team2_id, series_id, venue, match_date, 
                        overs_per_innings, state, created_by, created_at
                    ) VALUES (
                        :team1_id, :team2_id, :series_id, :venue, :match_date,
                        :overs_per_innings, 'upcoming', :created_by, NOW()
                    )
                ");
                
                $stmt->execute([
                    'team1_id' => $team1Id,
                    'team2_id' => $team2Id,
                    'series_id' => $seriesId,
                    'venue' => $venue,
                    'match_date' => $matchDate,
                    'overs_per_innings' => $oversPerInnings,
                    'created_by' => $userId
                ]);
                
                $matchId = $db->lastInsertId();
                $success = 'Match created successfully!';
                
                // Redirect to match view after 2 seconds
                header("Refresh: 2; url=match-view.php?id=$matchId");
            } catch (Exception $e) {
                error_log("Error creating match: " . $e->getMessage());
                $error = 'Failed to create match. Please try again.';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>Create Match - CricApp</title>
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
        
        /* Hamburger Menu - Same as dashboard */
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
        
        /* Sidebar - Same as dashboard */
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
            max-width: 800px;
            margin: 0 auto;
        }
        
        .page-header {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        
        .page-header h1 {
            font-size: 28px;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .page-header p {
            color: #64748b;
        }
        
        .form-card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 146, 112, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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
            text-decoration: none;
        }
        
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .help-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
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
            <a href="user-dashboard.php" class="menu-item">
                <span class="menu-icon">📊</span>
                <span>Dashboard</span>
            </a>
            <a href="create-match.php" class="menu-item active">
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
        <div class="page-header">
            <h1>Create New Match</h1>
            <p>Set up a new cricket match</p>
        </div>
        
        <div class="form-card">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?> Redirecting...</div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?= csrfInput() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="team1_id">Team 1 *</label>
                        <select id="team1_id" name="team1_id" class="form-control" required>
                            <option value="">Select Team 1</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?= $team['team_id'] ?>" <?= (getPost('team1_id') == $team['team_id']) ? 'selected' : '' ?>>
                                    <?= e($team['team_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="team2_id">Team 2 *</label>
                        <select id="team2_id" name="team2_id" class="form-control" required>
                            <option value="">Select Team 2</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?= $team['team_id'] ?>" <?= (getPost('team2_id') == $team['team_id']) ? 'selected' : '' ?>>
                                    <?= e($team['team_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="series_id">Series (Optional)</label>
                    <select id="series_id" name="series_id" class="form-control">
                        <option value="">No Series</option>
                        <?php foreach ($series as $s): ?>
                            <option value="<?= $s['series_id'] ?>" <?= (getPost('series_id') == $s['series_id']) ? 'selected' : '' ?>>
                                <?= e($s['series_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="help-text">Select a series if this match is part of a tournament</div>
                </div>
                
                <div class="form-group">
                    <label for="venue">Venue</label>
                    <input 
                        type="text" 
                        id="venue" 
                        name="venue" 
                        class="form-control" 
                        placeholder="e.g., Wankhede Stadium, Mumbai"
                        value="<?= e(getPost('venue', '')) ?>"
                    >
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="match_date">Match Date & Time *</label>
                        <input 
                            type="datetime-local" 
                            id="match_date" 
                            name="match_date" 
                            class="form-control" 
                            value="<?= e(getPost('match_date', '')) ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="overs_per_innings">Overs Per Innings *</label>
                        <input 
                            type="number" 
                            id="overs_per_innings" 
                            name="overs_per_innings" 
                            class="form-control" 
                            min="1" 
                            max="50" 
                            value="<?= e(getPost('overs_per_innings', '20')) ?>"
                            required
                        >
                        <div class="help-text">Typically 20 for T20, 50 for ODI</div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <span>✓</span>
                        Create Match
                    </button>
                    <a href="user-dashboard.php" class="btn btn-secondary">
                        <span>←</span>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
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
