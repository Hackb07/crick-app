<div class="content-container">
    
    <div class="card mb-4">
        <div class="card-body text-center">
            <?php if (!empty($team['logo'])): ?>
                <img src="<?= e($team['logo']) ?>" alt="<?= e($team['name']) ?>" style="width: 80px; height: 80px; object-fit: contain; margin: 0 auto 16px;">
            <?php else: ?>
                <div style="width: 80px; height: 80px; background: var(--bg-body); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                    👥
                </div>
            <?php endif; ?>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 4px;"><?= e($team['name']) ?></h1>
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <?= e($team['short_name'] ?: '-') ?>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Details</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" style="font-size: 1rem;"><?= e($team['name']) ?></div>
                    <div class="stat-label">Team Name</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="font-size: 1rem;"><?= e($team['short_name'] ?: '-') ?></div>
                    <div class="stat-label">Short Name</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Actions</h3>
            <div style="display: flex; gap: 12px;">
                <a href="<?= adminUrl('teams/edit.php?id=' . $team['team_id']) ?>" class="btn btn-primary" style="flex: 1;">Edit</a>
                <a href="<?= adminUrl('teams/delete.php?id=' . $team['team_id']) ?>" class="btn btn-secondary" onclick="return confirm('Delete team?');" style="flex: 1; background: #fee2e2; color: var(--danger); border-color: #fee2e2;">Delete</a>
            </div>
        </div>
    </div>

</div>
