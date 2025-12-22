<div class="content-container">
    
    <?php if (empty($players)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="font-size: 48px; margin-bottom: 16px;">👤</div>
            <h3 style="margin-bottom: 8px;">No Players Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Get started by adding your first player.</p>
            <a href="<?= adminUrl('players/create.php') ?>" class="btn btn-primary">Add Player</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="list-group">
                <?php foreach ($players as $player): ?>
                    <div class="list-item">
                        <!-- Player Avatar/Photo -->
                        <div style="width: 48px; height: 48px; border-radius: 50%; overflow: hidden; background: #e2e8f0; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0;">
                            <?php if (!empty($player['profile_image'])): ?>
                                <img src="<?= e($player['profile_image']) ?>" alt="<?= e($player['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <span style="font-size: 20px; font-weight: 700; color: #64748b;">
                                    <?= strtoupper(substr($player['name'], 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="list-item-content">
                            <div class="list-item-title"><?= e($player['name']) ?></div>
                            <div class="list-item-subtitle">
                                <?= e($player['batting_hand'] ?: '-') ?> • <?= e($player['bowling_style'] ?: '-') ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="<?= adminUrl('players/edit.php?id=' . $player['player_id']) ?>" class="btn-icon" aria-label="Edit">
                                ✎
                            </a>
                            <a href="<?= adminUrl('players/delete.php?id=' . $player['player_id']) ?>" class="btn-icon" style="color: var(--danger);" onclick="return confirm('Delete player?');" aria-label="Delete">
                                🗑️
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
