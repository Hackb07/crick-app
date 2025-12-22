<?php
/**
 * Database Reset Page - Admin Only
 * Allows admin to reset all match data and start fresh
 * 
 * @package    CricApp
 * @subpackage Admin\Database
 * @security   Admin only, CSRF protected
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

// Security: Require login and admin role
requireLogin();
requireRole('admin');

$pageTitle = 'Reset Database';
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    // CSRF Protection
    try {
        requireCsrfToken(getPost('csrf_token', ''));
    } catch (Exception $e) {
        $error = 'Security validation failed. Please try again.';
    }
    
    // Only proceed if CSRF validation passed
    if (empty($error)) {
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
                error_log('[ADMIN] Database reset by ' . getCurrentUsername() . ': ' . $deletedList);
                
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
}

// Get current database stats
try {
    $statsRepo = new StatsRepository();
    $stats = $statsRepo->getSystemStats();
} catch (Exception $e) {
    $stats = [];
    $error = 'Could not fetch database stats: ' . $e->getMessage();
}

// Render using centralized layout
renderAdminLayout($pageTitle, 'database-reset', [
    'success' => $success,
    'error' => $error,
    'stats' => $stats
], [
    'activeMenu' => 'database'
]);
