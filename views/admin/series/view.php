<div class="content-container">
    
    <div class="card mb-4">
        <div class="card-body">
            <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px;"><?= e($series['name']) ?></h1>
            <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">
                <?= formatDate($series['start_date'], 'M d, Y') ?> - <?= formatDate($series['end_date'], 'M d, Y') ?>
            </div>
            <?php if ($series['description']): ?>
                <p style="color: var(--text-body);"><?= nl2br(e($series['description'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Matches (<?= count($matches) ?>)</h3>
        </div>
        <?php if (empty($matches)): ?>
            <div class="card-body">
                <p style="color: var(--text-muted);">No matches in this series yet.</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($matches as $match): ?>
                    <div class="list-item">
                        <div class="list-item-content">
                            <div class="list-item-title">
                                <?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?>
                            </div>
                            <div class="list-item-subtitle">
                                <?= formatDate($match['match_date'], 'M d, Y') ?>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <?php
                            $badgeClass = 'badge-completed';
                            if ($match['state'] === 'live') $badgeClass = 'badge-live';
                            elseif ($match['state'] === 'scheduled') $badgeClass = 'badge-scheduled';
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($match['state']) ?></span>
                            <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" class="btn-icon" aria-label="View">
                                👁️
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Actions</h3>
            <div style="display: flex; gap: 12px;">
                <a href="<?= adminUrl('series/edit.php?id=' . $series['series_id']) ?>" class="btn btn-primary" style="flex: 1;">Edit</a>
                <a href="<?= adminUrl('series/delete.php?id=' . $series['series_id']) ?>" class="btn btn-secondary" onclick="return confirm('Delete series?');" style="flex: 1; background: #fee2e2; color: var(--danger); border-color: #fee2e2;">Delete</a>
            </div>
        </div>
    </div>

</div>
