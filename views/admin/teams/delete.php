<div class="content-container">
    
    <?php if (isset($error)): ?>
        <div class="error" style="margin-bottom: var(--spacing-lg); color: var(--danger); background: #fee2e2; padding: 12px; border-radius: 8px;"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (!$canDelete): ?>
        <div style="padding: 24px;">
            <div style="font-size: 4rem; margin-bottom: 16px; text-align: center;">🔒</div>
            <h2 style="margin-bottom: 16px; text-align: center; font-size: 1.5rem; font-weight: 700;">Cannot Delete Team</h2>
            <p style="margin-bottom: 24px; color: var(--text-muted); text-align: center;">
                <strong><?= e($team['name']) ?></strong> cannot be deleted because it is referenced by other records.
            </p>
            
            <div style="background: var(--bg-body); padding: 24px; border-radius: 12px; margin-bottom: 24px; border: 1px solid var(--border);">
                <h3 style="margin-bottom: 16px; font-size: 1.1rem; font-weight: 600;">Dependencies:</h3>
                
                <?php 
                // Get all unique matches (a team can be team1 or team2, but not both in same match)
                $allMatches = [];
                $matchIds = [];
                foreach ($dependencies['dependencies']['matches_as_team1'] as $match) {
                    if (!in_array($match['match_id'], $matchIds)) {
                        $allMatches[] = $match;
                        $matchIds[] = $match['match_id'];
                    }
                }
                foreach ($dependencies['dependencies']['matches_as_team2'] as $match) {
                    if (!in_array($match['match_id'], $matchIds)) {
                        $allMatches[] = $match;
                        $matchIds[] = $match['match_id'];
                    }
                }
                $totalMatches = count($allMatches);
                ?>
                
                <?php if ($totalMatches > 0): ?>
                    <div style="margin-bottom: 16px;">
                        <strong style="color: var(--danger);"><?= $totalMatches ?> Match(es):</strong>
                        <ul style="margin-top: 8px; padding-left: 24px;">
                            <?php foreach ($allMatches as $match): ?>
                                <li style="margin-bottom: 4px;">
                                    <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" style="color: var(--primary);">
                                        Match #<?= $match['match_id'] ?>
                                    </a>
                                    <?php if ($match['match_date']): ?>
                                        - <?= date('M d, Y', strtotime($match['match_date'])) ?>
                                    <?php endif; ?>
                                    <?php if ($match['venue']): ?>
                                        at <?= e($match['venue']) ?>
                                    <?php endif; ?>
                                    <span style="color: var(--text-muted);">(<?= e($match['state']) ?>)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($dependencies['dependencies']['player_appearances']['count'] > 0): ?>
                    <div style="margin-bottom: 16px;">
                        <strong style="color: var(--danger);"><?= $dependencies['dependencies']['player_appearances']['count'] ?> Player Appearance(s)</strong>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center;">
                <p style="margin-bottom: 16px; color: var(--text-muted);">
                    Please delete or reassign the related matches before deleting this team.
                </p>
                <a href="<?= adminUrl('teams/view.php?id=' . $team['team_id']) ?>" class="btn btn-secondary">← Back to Team</a>
            </div>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 24px;">
            <div style="font-size: 4rem; margin-bottom: 16px;">⚠️</div>
            <h2 style="margin-bottom: 16px; font-size: 1.5rem; font-weight: 700;">Are you sure?</h2>
            <p style="margin-bottom: 24px; color: var(--text-muted);">
                You are about to delete <strong><?= e($team['name']) ?></strong>. This action cannot be undone.
            </p>
            
            <form method="POST" style="display: inline-block;">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn" style="background: var(--danger); color: white; margin-right: 12px; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600;">Yes, Delete</button>
                <a href="<?= adminUrl('teams/view.php?id=' . $team['team_id']) ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    <?php endif; ?>

</div>
