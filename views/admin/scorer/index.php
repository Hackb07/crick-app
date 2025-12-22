<style>
    .scorer-header {
        background: linear-gradient(135deg, #1e7e34 0%, #155724 100%);
        color: white;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 8px;
    }
    
    .scorer-header h1 {
        margin: 0 0 0.25rem 0;
        font-size: 1.5rem;
    }
    
    .scorer-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 0.875rem;
    }
    
    .match-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #1e7e34;
    }
    
    .match-card.live {
        border-left-color: #dc3545;
    }
    
    .match-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .match-teams {
        font-weight: 600;
        font-size: 1rem;
        color: #212529;
    }
    
    .match-status {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .match-status.live {
        background: #dc3545;
        color: white;
    }
    
    .match-status.scheduled {
        background: #ffc107;
        color: #212529;
    }
    
    .match-info {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .match-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-score {
        flex: 1;
        padding: 0.75rem;
        background: #1e7e34;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        display: block;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-score:hover {
        background: #155724;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .logout-btn {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 56px;
        height: 56px;
        font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    
    @media (max-width: 768px) {
        .scorer-header h1 {
            font-size: 1.25rem;
        }
        
        .match-card {
            padding: 0.875rem;
        }
        
        .match-teams {
            font-size: 0.9375rem;
        }
    }
</style>

<div class="admin-content" style="margin-left: 0; padding: 1rem;">
    <div class="scorer-header">
        <h1>🏏 Scorer Dashboard</h1>
        <p>Welcome, <?= e(getSession('username', 'Scorer')) ?></p>
    </div>
    
    <?php if (!empty($liveMatches)): ?>
        <h2 style="margin: 0 0 1rem 0; font-size: 1.125rem;">🔥 Live Matches</h2>
        <?php foreach ($liveMatches as $match): ?>
            <div class="match-card live">
                <div class="match-card-header">
                    <div class="match-teams">
                        <?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?>
                    </div>
                    <span class="match-status live">Live</span>
                </div>
                <div class="match-info">
                    <div>📍 <?= e($match['venue'] ?: 'TBD') ?></div>
                    <div>📅 <?= formatDate($match['match_date'], 'M d, Y') ?></div>
                </div>
                <div class="match-actions">
                    <a href="<?= adminUrl('matches/scorer.php?id=' . $match['match_id']) ?>" class="btn-score">
                        Score Match
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <h2 style="margin: 1.5rem 0 1rem 0; font-size: 1.125rem;">Recent Matches</h2>
    
    <?php if (empty($recentMatches)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🏏</div>
            <div style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">No Matches Yet</div>
            <div style="font-size: 0.875rem;">Matches will appear here once created</div>
        </div>
    <?php else: ?>
        <?php foreach ($recentMatches as $match): ?>
            <div class="match-card <?= $match['state'] === 'live' ? 'live' : '' ?>">
                <div class="match-card-header">
                    <div class="match-teams">
                        <?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?>
                    </div>
                    <span class="match-status <?= $match['state'] === 'live' ? 'live' : 'scheduled' ?>">
                        <?= ucfirst($match['state']) ?>
                    </span>
                </div>
                <div class="match-info">
                    <div>📍 <?= e($match['venue'] ?: 'TBD') ?></div>
                    <div>📅 <?= formatDate($match['match_date'], 'M d, Y') ?></div>
                </div>
                <?php if ($match['state'] === 'live'): ?>
                    <div class="match-actions">
                        <a href="<?= adminUrl('matches/scorer.php?id=' . $match['match_id']) ?>" class="btn-score">
                            Score Match
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <a href="<?= adminUrl('logout.php') ?>" class="logout-btn" title="Logout">🚪</a>
</div>
