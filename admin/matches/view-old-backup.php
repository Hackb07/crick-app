<?php
/**
 * Match View - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
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

// Get match score and winner (for live/completed matches)
$score = null;
if (in_array($match['state'], ['live', 'completed'])) {
    $score = calculateMatchScore($matchId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php require_once __DIR__ . '/../../includes/cache-prevention-meta.php'; ?>
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title>Match View - Admin - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
</head>
<body>
    <div class="app-shell">
        <?php renderAdminSidebar('matches'); ?>

        <header class="app-header">
            <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                ☰
            </button>
            <div class="header-title">Match Details</div>
            <div class="header-actions">
                <a href="<?= adminUrl('matches/') ?>" class="btn-icon" aria-label="Back">
                    ←
                </a>
            </div>
        </header>

        <main class="app-main">
            <div class="content-container">
                
                <!-- Notifications -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="card mb-4" style="background: #fee2e2; border-color: #fecaca; color: #991b1b;">
                        <div class="card-body" style="padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
                            <span>⚠️</span>
                            <div><?= e($_SESSION['error']) ?></div>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="card mb-4" style="background: #dcfce7; border-color: #bbf7d0; color: #166534;">
                        <div class="card-body" style="padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
                            <span>✅</span>
                            <div><?= e($_SESSION['success']) ?></div>
                        </div>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <!-- Match Header Card -->
                <div class="card mb-4 text-center">
                    <div class="card-body">
                        <h1 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">
                            <?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?>
                        </h1>
                        <div style="display: flex; justify-content: center; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <?php
                            $badgeClass = 'badge-completed';
                            if ($match['state'] === 'live') $badgeClass = 'badge-live';
                            elseif ($match['state'] === 'scheduled') $badgeClass = 'badge-scheduled';
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($match['state']) ?></span>
                            
                            <?php if ($match['venue']): ?>
                                <span style="color: var(--text-muted); font-size: 0.875rem;">📍 <?= e($match['venue']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($match['match_date']): ?>
                            <div style="color: var(--text-muted); font-size: 0.875rem; margin-top: 4px;">
                                📅 <?= formatDate($match['match_date'], 'M d, Y h:i A') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Match Score (Live/Completed) -->
                <?php if ($score && ($match['state'] === 'live' || $match['state'] === 'completed')): ?>
                    <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white;">
                        <div class="card-header" style="border-bottom-color: rgba(255,255,255,0.1); color: white;">Match Score</div>
                        <div class="card-body">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: center;">
                                <div>
                                    <div style="font-size: 0.75rem; opacity: 0.8; margin-bottom: 4px;">1st Innings</div>
                                    <div style="font-size: 1.5rem; font-weight: 700;">
                                        <?= $score['innings1']['runs'] ?>/<?= $score['innings1']['wickets'] ?>
                                    </div>
                                    <div style="font-size: 0.875rem; opacity: 0.9;">
                                        (<?= number_format($score['innings1']['overs'], 1) ?> Ov)
                                    </div>
                                </div>
                                <?php if ($score['innings2']['runs'] > 0 || $match['state'] === 'completed'): ?>
                                    <div>
                                        <div style="font-size: 0.75rem; opacity: 0.8; margin-bottom: 4px;">2nd Innings</div>
                                        <div style="font-size: 1.5rem; font-weight: 700;">
                                            <?= $score['innings2']['runs'] ?>/<?= $score['innings2']['wickets'] ?>
                                        </div>
                                        <div style="font-size: 0.875rem; opacity: 0.9;">
                                            (<?= number_format($score['innings2']['overs'], 1) ?> Ov)
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($match['state'] === 'completed' && !empty($score['winner_name'])): ?>
                                <div style="margin-top: 16px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px;">
                                    <div style="font-size: 0.875rem; opacity: 0.9;">Winner</div>
                                    <div style="font-size: 1.25rem; font-weight: 700;">
                                        🏆 <?= e($score['winner_name']) ?>
                                    </div>
                                    <?php
                                    $team1Innings = (getBattingTeam($match, 1) == $match['team1_id']) ? 1 : 2;
                                    $team2Innings = ($team1Innings == 1) ? 2 : 1;
                                    $winningInnings = ($score['winner_id'] == $match['team1_id']) 
                                        ? (($team1Innings == 1) ? $score['innings1'] : $score['innings2'])
                                        : (($team2Innings == 1) ? $score['innings1'] : $score['innings2']);
                                    $losingInnings = ($score['winner_id'] == $match['team1_id']) 
                                        ? (($team2Innings == 1) ? $score['innings1'] : $score['innings2'])
                                        : (($team1Innings == 1) ? $score['innings1'] : $score['innings2']);
                                    $winningRuns = $winningInnings['runs'] ?? 0;
                                    $losingRuns = $losingInnings['runs'] ?? 0;
                                    $margin = abs($winningRuns - $losingRuns);
                                    $wickets = $losingInnings['wickets'] ?? 0;
                                    ?>
                                    <div style="font-size: 0.875rem; opacity: 0.9;">
                                        <?php if ($winningRuns > $losingRuns): ?>
                                            by <?= $margin ?> runs
                                        <?php else: ?>
                                            by <?= (10 - $wickets) ?> wickets
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Match Info Grid -->
                <div class="card mb-4">
                    <div class="card-header">Information</div>
                    <div class="card-body">
                        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                            <div class="stat-card">
                                <div class="stat-label">Series</div>
                                <div class="stat-value" style="font-size: 1rem;"><?= e($match['series_name'] ?: 'None') ?></div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Overs</div>
                                <div class="stat-value" style="font-size: 1rem;"><?= e($match['overs_per_innings']) ?></div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Team 1</div>
                                <div class="stat-value" style="font-size: 1rem;"><?= e($match['team1_name']) ?></div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-label">Team 2</div>
                                <div class="stat-value" style="font-size: 1rem;"><?= e($match['team2_name']) ?></div>
                            </div>
                        </div>
                        <?php if ($match['state'] === 'completed'): 
                            $potm = new POTM();
                            $potmData = $potm->getForMatch($matchId);
                            if ($potmData):
                        ?>
                            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                                <div style="font-size: 0.875rem; color: var(--text-muted);">Player of the Match</div>
                                <div style="font-weight: 600; color: #f59e0b;">⭐ <?= e($potmData['player_name']) ?></div>
                                <?php if (!empty($potmData['reason'])): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($potmData['reason']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card mb-4">
                    <div class="card-header">Actions</div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="<?= adminUrl('matches/console.php?id=' . $matchId) ?>" class="list-item" style="background: #f0fdf4; border: 1px solid #16a34a;">
                                <div class="list-content">
                                    <div class="list-title" style="color: #16a34a; font-weight: bold;">🚀 Open Match Console</div>
                                    <div class="list-subtitle">Manage squads, toss, and match state</div>
                                </div>
                                <div class="list-action">→</div>
                            </a>
                            
                            <?php if ($match['state'] === 'live'): ?>
                                <a href="<?= adminUrl('matches/scorer.php?id=' . $matchId) ?>" class="list-item" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                                    <div class="list-content">
                                        <div class="list-title" style="color: white; font-weight: 700;">🏏 Live Scorer</div>
                                        <div class="list-subtitle" style="color: rgba(255,255,255,0.9);">Record match events in real-time</div>
                                    </div>
                                    <div class="list-action" style="color: white;">→</div>
                                </a>
                            <?php endif; ?>

                            <a href="<?= publicUrl('match-view.php?id=' . $matchId) ?>" class="list-item" target="_blank">
                                <div class="list-content">
                                    <div class="list-title">View Public Page</div>
                                </div>
                                <div class="list-action">↗</div>
                            </a>
                            
                            <a href="<?= adminUrl('matches/delete.php?id=' . $matchId) ?>" class="list-item" onclick="return confirm('Are you sure?');" style="color: var(--danger);">
                                <div class="list-content">
                                    <div class="list-title">Delete Match</div>
                                </div>
                                <div class="list-action">🗑️</div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script>
        function toggleSidebar() {
            document.querySelector('.app-shell').classList.toggle('sidebar-open');
        }
        
        async function loadPreviousSetup() {
            if (!confirm('Load player setup from previous match? This will replace current selections.')) {
                return;
            }
            
            try {
                const response = await fetch('<?= apiUrl('match-setup.php') ?>/previous/<?= $matchId ?>', {
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (!result.success || !result.data) {
                    alert(result.message || 'No previous match found');
                    return;
                }
                
                const data = result.data;
                const message = `Found: ${data.previous_match_name}\nDate: ${data.previous_match_date}\nPlayers: ${data.total_players}\n\nApply?`;
                
                if (!confirm(message)) return;
                
                const players = [];
                data.team1_players.forEach(p => players.push({player_id: p.player_id, team_id: <?= $match['team1_id'] ?>, is_guest: 0}));
                data.team2_players.forEach(p => players.push({player_id: p.player_id, team_id: <?= $match['team2_id'] ?>, is_guest: 0}));
                data.guest_players.forEach(p => players.push({player_id: p.player_id, team_id: <?= $match['team1_id'] ?>, is_guest: 1}));
                
                const applyResponse = await fetch('<?= apiUrl('match-setup.php') ?>/apply/<?= $matchId ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ players })
                });
                
                const applyResult = await applyResponse.json();
                
                if (applyResult.success) {
                    alert(`Success! Applied ${applyResult.count} players.`);
                    location.reload();
                } else {
                    alert('Error: ' + (applyResult.error || 'Failed'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to load previous setup');
            }
        }
    </script>
</body>
</html>
