<div class="content-container">
    
    <!-- Welcome Card -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 style="margin: 0; font-size: 18px;">Welcome, <?= e($username) ?>!</h2>
            <p style="color: var(--text-muted); margin: 4px 0 0; font-size: 14px;">
                <?= e($userRole) ?> • CricApp Admin Panel
            </p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $totalMatches ?></div>
            <div class="stat-label">Matches</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--danger);"><?= $liveCount ?></div>
            <div class="stat-label">Live</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--success);"><?= $completedCount ?></div>
            <div class="stat-label">Done</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalPlayers ?></div>
            <div class="stat-label">Players</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalTeams ?></div>
            <div class="stat-label">Teams</div>
        </div>
    </div>

    <!-- Live Matches -->
    <div class="card">
        <div class="card-header">
            <span>🔥 Recent Matches</span>
            <a href="<?= adminUrl('matches/') ?>" class="btn btn-sm btn-secondary">View All</a>
        </div>
    </div>
    
    <?php if (empty($recentMatches)): ?>
        <?= renderEmptyState(
            '📋', 
            'No Matches Yet', 
            '', 
            ['text' => 'Create Your First Match', 'url' => adminUrl('matches/create.php')]
        ) ?>
    <?php else: ?>
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Teams</th>
                        <th>Venue</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMatches as $match): ?>
                        <tr>
                            <td>
                                <strong><?= e($match['team1_name']) ?></strong> vs <strong><?= e($match['team2_name']) ?></strong>
                            </td>
                            <td><?= e($match['venue'] ?: 'TBD') ?></td>
                            <td><?= formatDate($match['match_date'], 'M d, Y') ?></td>
                            <td>
                                <?= renderBadge(ucfirst($match['state']), $match['state']) ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= adminUrl('matches/view.php?id=' . $match['match_id']) ?>" class="btn-icon btn-icon-view" title="View">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
                                    </a>
                                    <?php if ($match['state'] === 'live'): ?>
                                        <a href="<?= adminUrl('matches/scorer.php?id=' . $match['match_id']) ?>" class="btn-icon btn-icon-edit" title="Score">
                                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
