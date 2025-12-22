<div class="content-container">
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body" style="overflow-x: auto; white-space: nowrap; padding: 12px; display: flex; gap: 8px;">
            <a href="<?= adminUrl('matches/') ?>" class="btn btn-sm <?= !$state ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="<?= adminUrl('matches/?state=live') ?>" class="btn btn-sm <?= $state === 'live' ? 'btn-primary' : 'btn-secondary' ?>">Live</a>
            <a href="<?= adminUrl('matches/?state=completed') ?>" class="btn btn-sm <?= $state === 'completed' ? 'btn-primary' : 'btn-secondary' ?>">Completed</a>
            <a href="<?= adminUrl('matches/?state=scheduled') ?>" class="btn btn-sm <?= $state === 'scheduled' ? 'btn-primary' : 'btn-secondary' ?>">Scheduled</a>
            <a href="<?= adminUrl('matches/?state=draft') ?>" class="btn btn-sm <?= $state === 'draft' ? 'btn-primary' : 'btn-secondary' ?>">Draft</a>
        </div>
    </div>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
        <div class="card" style="background: #fef2f2; border-color: #fee2e2;">
            <div class="card-body" style="color: var(--danger);">
                <strong>Error:</strong> <?= e($error) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Matches List -->
    <div class="card">
        <?php if (empty($matches)): ?>
            <div class="card-body" style="text-align: center; padding: 48px 24px;">
                <div style="font-size: 48px; margin-bottom: 16px;">🏏</div>
                <h3 style="margin: 0 0 8px;">No Matches Found</h3>
                <p style="color: var(--text-muted); margin-bottom: 24px;">Get started by creating a new match.</p>
                <a href="<?= adminUrl('matches/create.php') ?>" class="btn btn-primary">Create Match</a>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($matches as $match): ?>
                    <?php
                    $matchId = (int)($match['match_id'] ?? 0);
                    if ($matchId <= 0) continue;
                    
                    $team1Name = $match['team1_name'] ?? 'Unknown';
                    $team2Name = $match['team2_name'] ?? 'Unknown';
                    $seriesName = $match['series_name'] ?? null;
                    $venue = $match['venue'] ?? null;
                    $matchDate = $match['match_date'] ?? null;
                    $matchState = $match['state'] ?? 'unknown';
                    
                    $badgeColor = 'var(--text-muted)';
                    $badgeBg = 'var(--bg-body)';
                    if ($matchState === 'live') {
                        $badgeColor = 'white';
                        $badgeBg = 'var(--danger)';
                    } elseif ($matchState === 'completed') {
                        $badgeColor = 'white';
                        $badgeBg = 'var(--success)';
                    }
                    ?>
                    <div class="list-item" style="flex-direction: column; align-items: stretch; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <div class="list-item-title" style="font-size: 16px;">
                                    <?= e($team1Name) ?> vs <?= e($team2Name) ?>
                                </div>
                                <div class="list-item-subtitle">
                                    <?= e($seriesName ?: 'Friendly') ?> • <?= e($venue ?: 'TBD') ?>
                                </div>
                                <div class="list-item-subtitle" style="margin-top: 2px;">
                                    📅 <?= formatDate($matchDate, 'M d, Y h:i A') ?>
                                </div>
                            </div>
                            <span style="font-size: 11px; padding: 4px 8px; border-radius: 4px; background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid var(--border); font-weight: 600;">
                                <?= strtoupper($matchState) ?>
                            </span>
                        </div>
                        
                        <div class="action-buttons" style="display: flex; gap: 8px; border-top: 1px solid var(--border); padding-top: 12px;">
                            <a href="<?= adminUrl('matches/console.php?id=' . $matchId) ?>" class="btn btn-sm btn-secondary" style="flex: 1;">Console</a>
                            <a href="<?= adminUrl('matches/view.php?id=' . $matchId) ?>" class="btn btn-sm btn-secondary" style="flex: 1;">View</a>
                            <?php if ($matchState === 'live'): ?>
                                <a href="<?= adminUrl('matches/scorer.php?id=' . $matchId) ?>" class="btn btn-sm" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; font-weight: 600;">🏏 Score</a>
                            <?php endif; ?>
                            <a href="<?= adminUrl('matches/edit.php?id=' . $matchId) ?>" class="btn btn-sm btn-secondary" style="width: auto;">✏️</a>
                            <a href="<?= adminUrl('matches/delete.php?id=' . $matchId) ?>" class="btn btn-sm btn-secondary" style="width: auto; color: var(--danger);" onclick="return confirm('Delete match?')">🗑️</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
