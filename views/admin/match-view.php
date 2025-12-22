<div class="content-container">
    
    <!-- Match Header -->
    <div class="card mb-4 text-center">
        <div class="card-body">
            <h1 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 8px;">
                <?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?>
            </h1>
            <div style="display: flex; justify-content: center; gap: 8px; align-items: center; flex-wrap: wrap;">
                <?= renderBadge(ucfirst($match['state']), $match['state']) ?>
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

    <!-- Match Score -->
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
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Match Info -->
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
