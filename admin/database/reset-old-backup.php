<?php
/**
 * Database Reset Page - Admin Only
 * Allows admin to reset all match data and start fresh
 * 
 * @security Admin access only
 * @warning This action cannot be undone!
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Security: Require login
requireLogin();

// Security: Admin only
if (getSession('role') !== 'admin') {
    $_SESSION[SESSION_KEY_ERROR] = 'Access denied. Admin privileges required.';
    header('Location: ' . adminUrl('index.php'));
    exit;
}

$pageTitle = 'Reset Database';
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    // Double confirmation check
    if ($_POST['confirm_text'] !== 'RESET ALL DATA') {
        $error = 'Confirmation text does not match. Please type exactly: RESET ALL DATA';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get selected options
            $resetMatchData = isset($_POST['reset_match_data']);
            $resetMatches = isset($_POST['reset_matches']);
            $resetPlayers = isset($_POST['reset_players']);
            $resetTeams = isset($_POST['reset_teams']);
            
            // Start transaction
            $db->beginTransaction();
            
            // Disable foreign key checks
            $db->exec('SET FOREIGN_KEY_CHECKS = 0');
            
            $deletedItems = [];
            
            // Reset match data (events, stats, commentary)
            if ($resetMatchData) {
                $tables = [
                    'commentary',
                    'events',
                    'batting_stats',
                    'bowling_stats',
                    'fielding_stats',
                    'player_appearances'
                ];
                
                foreach ($tables as $table) {
                    $db->exec("TRUNCATE TABLE `$table`");
                }
                $deletedItems[] = 'Match Data (events, stats, commentary)';
                
                // If not deleting matches, reset them to scheduled
                if (!$resetMatches) {
                    $stmt = $db->prepare("
                        UPDATE matches SET
                            state = 'scheduled',
                            current_innings = 1,
                            winner_id = NULL,
                            updated_at = NOW()
                        WHERE 1=1
                    ");
                    $stmt->execute();
                    $deletedItems[] = 'Matches reset to scheduled';
                }
            }
            
            // Delete all matches
            if ($resetMatches) {
                $db->exec("TRUNCATE TABLE `matches`");
                $deletedItems[] = 'All Matches';
            }
            
            // Delete all players
            if ($resetPlayers) {
                $db->exec("TRUNCATE TABLE `players`");
                $deletedItems[] = 'All Players';
            }
            
            // Delete all teams
            if ($resetTeams) {
                $db->exec("TRUNCATE TABLE `teams`");
                $deletedItems[] = 'All Teams';
            }
            
            // Re-enable foreign key checks
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
            
            // Commit transaction
            $db->commit();
            
            // Log the action
            $deletedList = implode(', ', $deletedItems);
            error_log('[ADMIN] Database reset by ' . (getSession('username') ?? 'Unknown') . ': ' . $deletedList);
            
            $success = 'Database reset successfully! Deleted: ' . $deletedList;
            
        } catch (Exception $e) {
            // Rollback on error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            error_log('[ADMIN] Database reset failed: ' . $e->getMessage());
            $error = 'Database reset failed: ' . $e->getMessage();
        }
    }
}

// Get current database stats
try {
    $db = Database::getInstance()->getConnection();
    
    $stats = [
        'events' => $db->query('SELECT COUNT(*) FROM events')->fetchColumn(),
        'commentary' => $db->query('SELECT COUNT(*) FROM commentary')->fetchColumn(),
        'batting_stats' => $db->query('SELECT COUNT(*) FROM batting_stats')->fetchColumn(),
        'bowling_stats' => $db->query('SELECT COUNT(*) FROM bowling_stats')->fetchColumn(),
        'fielding_stats' => $db->query('SELECT COUNT(*) FROM fielding_stats')->fetchColumn(),
        'player_appearances' => $db->query('SELECT COUNT(*) FROM player_appearances')->fetchColumn(),
        'matches_live' => $db->query("SELECT COUNT(*) FROM matches WHERE state = 'live'")->fetchColumn(),
        'matches_completed' => $db->query("SELECT COUNT(*) FROM matches WHERE state = 'completed'")->fetchColumn(),
        'matches_scheduled' => $db->query("SELECT COUNT(*) FROM matches WHERE state = 'scheduled'")->fetchColumn(),
        'total_matches' => $db->query('SELECT COUNT(*) FROM matches')->fetchColumn(),
        'total_players' => $db->query('SELECT COUNT(*) FROM players')->fetchColumn(),
        'total_teams' => $db->query('SELECT COUNT(*) FROM teams')->fetchColumn(),
    ];
} catch (Exception $e) {
    $stats = [];
    $error = 'Could not fetch database stats: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/../../includes/cache-prevention-meta.php'; ?>
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title><?= $pageTitle ?> - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        .danger-zone {
            background: #fff5f5;
            border: 2px solid #fc8181;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        .danger-zone h4 {
            color: #c53030;
            margin: 0 0 1rem 0;
        }
        .form-control-lg {
            width: 100%;
            padding: 1rem;
            font-size: 1.125rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?php renderAdminSidebar('database'); ?>

        <header class="app-header">
            <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                ☰
            </button>
            <div class="header-title">⚠️ Reset Database</div>
            <div class="header-actions">
                <a href="<?= adminUrl('logout.php') ?>" class="btn-icon" aria-label="Logout">
                    🚪
                </a>
            </div>
        </header>

        <main class="app-main">
            <div class="content-container">
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <strong>✅ Success!</strong> <?= e($success) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>❌ Error!</strong> <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Current Database Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <span>📊 Current Database Statistics</span>
                    </div>
                    <div class="card-body">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value" style="color: var(--primary);"><?= number_format($stats['events'] ?? 0) ?></div>
                                <div class="stat-label">Events (Balls)</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: var(--info);"><?= number_format($stats['commentary'] ?? 0) ?></div>
                                <div class="stat-label">Commentary</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: var(--success);"><?= number_format($stats['batting_stats'] ?? 0) ?></div>
                                <div class="stat-label">Batting Stats</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: var(--warning);"><?= number_format($stats['bowling_stats'] ?? 0) ?></div>
                                <div class="stat-label">Bowling Stats</div>
                            </div>
                        </div>
                        
                        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                            <div class="stat-card">
                                <div class="stat-value" style="color: var(--success);"><?= $stats['matches_scheduled'] ?? 0 ?></div>
                                <div class="stat-label">Scheduled</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: var(--danger);"><?= $stats['matches_live'] ?? 0 ?></div>
                                <div class="stat-label">Live</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value" style="color: var(--text-muted);"><?= $stats['matches_completed'] ?? 0 ?></div>
                                <div class="stat-label">Completed</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Danger Zone -->
                <div class="card">
                    <div class="card-header" style="background: #dc3545; color: white;">
                        <span>⚠️ DANGER ZONE - Reset Database</span>
                    </div>
                    <div class="card-body">
                        <div class="danger-zone">
                            <h4>⚠️ WARNING: This Action Cannot Be Undone!</h4>
                            <p style="margin-bottom: 0.5rem; font-weight: 600;">Select what to reset:</p>
                        </div>
                        
                        <form method="POST" id="resetForm" onsubmit="return confirmReset()">
                            
                            <!-- Quick Level Selector -->
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                                <p style="margin: 0 0 0.75rem 0; font-weight: 600;">Quick Select:</p>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectLevel(1)">
                                        Level 1: Data Only
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectLevel(2)">
                                        Level 2: Data + Matches
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectLevel(3)">
                                        Level 3: Everything
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="selectAll()">
                                        Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="deselectAll()">
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Checkboxes -->
                            <div style="background: white; border: 2px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
                                <div style="margin-bottom: 1rem;">
                                    <label for="reset_match_data" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                                        <input type="checkbox" name="reset_match_data" id="reset_match_data" checked style="width: 20px; height: 20px; cursor: pointer;">
                                        <div>
                                            <strong>Match Data</strong>
                                            <div style="font-size: 0.875rem; color: var(--text-muted);">Events, Stats, Commentary, Player Appearances</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div style="margin-bottom: 1rem;">
                                    <label for="reset_matches" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background='white'">
                                        <input type="checkbox" name="reset_matches" id="reset_matches" style="width: 20px; height: 20px; cursor: pointer;">
                                        <div>
                                            <strong style="color: #dc3545;">Match Fixtures</strong>
                                            <div style="font-size: 0.875rem; color: var(--text-muted);">⚠️ Delete all match records (cannot be undone!)</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div style="margin-bottom: 1rem;">
                                    <label for="reset_players" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background='white'">
                                        <input type="checkbox" name="reset_players" id="reset_players" style="width: 20px; height: 20px; cursor: pointer;">
                                        <div>
                                            <strong style="color: #dc3545;">All Players</strong>
                                            <div style="font-size: 0.875rem; color: var(--text-muted);">⚠️ Delete all player records (cannot be undone!)</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div>
                                    <label for="reset_teams" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background='white'">
                                        <input type="checkbox" name="reset_teams" id="reset_teams" style="width: 20px; height: 20px; cursor: pointer;">
                                        <div>
                                            <strong style="color: #dc3545;">All Teams</strong>
                                            <div style="font-size: 0.875rem; color: var(--text-muted);">⚠️ Delete all team records (cannot be undone!)</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <label for="confirm_text" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                                Type exactly: <code style="background: #f8f9fa; padding: 0.25rem 0.5rem; border-radius: 4px;">RESET ALL DATA</code>
                            </label>
                            <input 
                                type="text" 
                                class="form-control-lg" 
                                id="confirm_text" 
                                name="confirm_text" 
                                placeholder="Type: RESET ALL DATA"
                                required
                                autocomplete="off"
                            >
                            
                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" name="confirm_reset" class="btn-danger">
                                    🗑️ Reset Selected Items
                                </button>
                                <a href="<?= adminUrl('index.php') ?>" class="btn btn-secondary">
                                    ✖️ Cancel
                                </a>
                            </div>
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
    
    // Level selection functions
    function selectLevel(level) {
        if (level === 1) {
            // Level 1: Data Only
            document.getElementById('reset_match_data').checked = true;
            document.getElementById('reset_matches').checked = false;
            document.getElementById('reset_players').checked = false;
            document.getElementById('reset_teams').checked = false;
        } else if (level === 2) {
            // Level 2: Data + Matches
            document.getElementById('reset_match_data').checked = true;
            document.getElementById('reset_matches').checked = true;
            document.getElementById('reset_players').checked = false;
            document.getElementById('reset_teams').checked = false;
        } else if (level === 3) {
            // Level 3: Everything
            selectAll();
        }
    }
    
    function selectAll() {
        document.getElementById('reset_match_data').checked = true;
        document.getElementById('reset_matches').checked = true;
        document.getElementById('reset_players').checked = true;
        document.getElementById('reset_teams').checked = true;
    }
    
    function deselectAll() {
        document.getElementById('reset_match_data').checked = false;
        document.getElementById('reset_matches').checked = false;
        document.getElementById('reset_players').checked = false;
        document.getElementById('reset_teams').checked = false;
    }
    
    function confirmReset() {
        const confirmText = document.getElementById('confirm_text').value;
        
        if (confirmText !== 'RESET ALL DATA') {
            alert('Please type exactly: RESET ALL DATA');
            return false;
        }
        
        // Check what's selected
        const matchData = document.getElementById('reset_match_data').checked;
        const matches = document.getElementById('reset_matches').checked;
        const players = document.getElementById('reset_players').checked;
        const teams = document.getElementById('reset_teams').checked;
        
        if (!matchData && !matches && !players && !teams) {
            alert('Please select at least one item to reset!');
            return false;
        }
        
        // Build confirmation message
        let items = [];
        if (matchData) items.push('Match Data (events, stats, commentary)');
        if (matches) items.push('All Match Fixtures');
        if (players) items.push('All Players');
        if (teams) items.push('All Teams');
        
        const message = 
            '⚠️ FINAL CONFIRMATION ⚠️\n\n' +
            'You are about to DELETE:\n\n' +
            items.map(item => '• ' + item).join('\n') +
            '\n\nThis action CANNOT be undone!\n\n' +
            'Click OK to proceed or Cancel to abort.';
        
        return confirm(message);
    }
    </script>
</body>
</html>
