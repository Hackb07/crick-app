<div class="content-container">
    
    <?php if (empty($series)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="font-size: 48px; margin-bottom: 16px;">🏆</div>
            <h3 style="margin-bottom: 8px;">No Series Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 24px;">Get started by creating your first series.</p>
            <a href="<?= adminUrl('series/create.php') ?>" class="btn btn-primary">Create Series</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="list-group">
                <?php foreach ($series as $s): ?>
                    <div class="list-item">
                        <div class="list-item-content">
                            <div class="list-item-title"><?= e($s['name']) ?></div>
                            <div class="list-item-subtitle">
                                <?= formatDate($s['start_date'], 'M d, Y') ?> - <?= formatDate($s['end_date'], 'M d, Y') ?>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="<?= adminUrl('series/view.php?id=' . $s['series_id']) ?>" class="btn-icon" aria-label="View">
                                👁️
                            </a>
                            <a href="<?= adminUrl('series/edit.php?id=' . $s['series_id']) ?>" class="btn-icon" aria-label="Edit">
                                ✎
                            </a>
                            <a href="<?= adminUrl('series/delete.php?id=' . $s['series_id']) ?>" class="btn-icon" style="color: var(--danger);" onclick="return confirm('Delete series?');" aria-label="Delete">
                                🗑️
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
