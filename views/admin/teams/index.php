<div class="content-container">
    
    <?php if (!empty($successMessage)): ?>
        <div class="card mb-4" style="background: #f0fdf4; border-color: #bbf7d0;">
            <div class="card-body" style="color: var(--success);">
                <?= e($successMessage) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (empty($teams)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="font-size: 48px; margin-bottom: 16px;">👥</div>
            <h3 style="margin-bottom: 8px;">No Teams Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Get started by adding your first team.</p>
            <a href="<?= adminUrl('teams/create.php') ?>" class="btn btn-primary">Add Team</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="list-group">
                <?php foreach ($teams as $team): ?>
                    <div class="list-item">
                        <div class="list-item-content">
                            <div class="list-item-title"><?= e($team['name']) ?></div>
                            <div class="list-item-subtitle">
                                <?= e($team['short_name'] ?: '-') ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="<?= adminUrl('teams/view.php?id=' . $team['team_id']) ?>" class="btn-icon" aria-label="View">
                                👁️
                            </a>
                            <a href="<?= adminUrl('teams/edit.php?id=' . $team['team_id']) ?>" class="btn-icon" aria-label="Edit">
                                ✎
                            </a>
                            <a href="<?= adminUrl('teams/delete.php?id=' . $team['team_id']) ?>" class="btn-icon" style="color: var(--danger);" onclick="return confirm('Delete team?');" aria-label="Delete">
                                🗑️
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
