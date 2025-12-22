<div class="content-container">
    
    <div class="card mb-4">
        <div class="card-body text-center">
            <div style="width: 80px; height: 80px; background: var(--bg-body); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 32px; overflow: hidden;">
                <?php if (!empty($player['profile_image'])): ?>
                    <img src="<?= e($player['profile_image']) ?>" alt="<?= e($player['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    👤
                <?php endif; ?>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 4px;"><?= e($player['name']) ?></h1>
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                <?= e($player['batting_hand'] ?: '-') ?> • <?= e($player['bowling_style'] ?: '-') ?>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Details</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" style="font-size: 1rem;"><?= formatDate($player['date_of_birth'], 'M d, Y') ?: '-' ?></div>
                    <div class="stat-label">Date of Birth</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="font-size: 1rem;"><?= e($player['batting_hand'] ?: '-') ?></div>
                    <div class="stat-label">Batting</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="font-size: 1rem;"><?= e($player['bowling_style'] ?: '-') ?></div>
                    <div class="stat-label">Bowling</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Actions</h3>
            <div style="display: flex; gap: 12px;">
                <a href="<?= adminUrl('players/edit.php?id=' . $player['player_id']) ?>" class="btn btn-primary" style="flex: 1;">Edit</a>
                <a href="<?= adminUrl('players/delete.php?id=' . $player['player_id']) ?>" class="btn btn-secondary" onclick="return confirm('Delete player?');" style="flex: 1; background: #fee2e2; color: var(--danger); border-color: #fee2e2;">Delete</a>
            </div>
        </div>
    </div>

</div>
