<style>
    /* Advanced Mobile-First Design Overrides (Scoped) */
    
    /* Main Layout */
    .console-grid {
        display: grid;
        gap: 16px;
        padding: 0; /* Removing padding as content-container might have it, or we add it */
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Section Cards */
    .section-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .section-header {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .section-title {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Squad Interface */
    .squad-tabs {
        display: flex;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 12px;
        margin: 16px;
    }

    .squad-tab {
        flex: 1;
        padding: 10px;
        text-align: center;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        background: transparent;
    }

    .squad-tab.active {
        background: white;
        color: #0f172a;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Player List */
    /* Player List Styles */
    .player-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .player-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        gap: 12px;
    }

    .player-details {
        flex: 1;
        min-width: 0; /* Critical for flex ellipsis */
    }

    .player-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.95rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .player-meta {
        font-size: 0.8rem;
        color: #64748b;
        display: flex;
        gap: 8px;
        margin-top: 2px;
        white-space: nowrap;
    }

    .player-actions-mini {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    /* Mobile Tweaks */
    @media (max-width: 480px) {
        .player-item {
            padding: 12px;
            gap: 8px;
        }
        .mini-btn {
            width: 28px;
            height: 28px;
            font-size: 11px;
            padding: 0;
        }
    }

    .mini-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #64748b;
        background: white;
        cursor: pointer;
        user-select: none;
    }
    
    .mini-btn.active {
        background: #eff6ff;
        border-color: var(--primary);
        color: var(--primary);
        font-weight: 700;
    }

    /* Toss Interface */
    .toss-interface {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }

    .toss-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8fafc;
        display: block; /* Ensure label behaves like block */
    }

    .toss-card:hover {
        background: white;
        border-color: #cbd5e1;
    }

    .toss-card.active {
        background: #eff6ff; /* blue-50 */
        border-color: #2563eb; /* blue-600 (primary) */
        color: #1e40af; /* blue-800 */
        font-weight: 600;
    }

    .toss-icon {
        font-size: 24px;
        display: block;
        margin-bottom: 8px;
    }
    
    /* FAB for Start Match */
    .start-fab {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #10b981; /* green-500 */
        color: white;
        padding: 12px 24px;
        border-radius: 50px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
        text-decoration: none;
        z-index: 100;
    }
    
    .start-fab:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0,0,0,0.15);
    }
    
    .start-fab:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        transform: none;
    }
</style>

<div class="console-grid">
    <div class="section-card">
        <div class="section-header">
            <span class="section-title">Manage Squads</span>
            <span><?= ($data['validation']['squads']['valid'] ?? false) ? '✅' : 'Pending' ?></span>
        </div>
        
        <div class="squad-tabs">
            <?php 
            $reqTeamId = getQuery('team_id');
            $showTeam2 = $reqTeamId && $reqTeamId == $data['teams']['team2']['team_id'];
            ?>
            <div class="squad-tab <?= !$showTeam2 ? 'active' : '' ?>" id="tab-team1" onclick="showTeam('team1')">
                <?= e($data['teams']['team1']['name'] ?? 'Team 1') ?>
            </div>
            <div class="squad-tab <?= $showTeam2 ? 'active' : '' ?>" id="tab-team2" onclick="showTeam('team2')">
                <?= e($data['teams']['team2']['name'] ?? 'Team 2') ?>
            </div>
        </div>

        <div style="padding: 0;">
                
                <!-- Team 1 Section -->
                <div id="view-team1" style="display: <?= !$showTeam2 ? 'block' : 'none' ?>;">
                    <form method="POST">
                    <input type="hidden" name="action" value="update_squad">
                    <input type="hidden" name="team_id" value="<?= $data['teams']['team1']['team_id'] ?>">
                    
                    <div style="padding: 10px; border-bottom: 1px solid #f1f5f9;">
                        <input type="text" placeholder="Search players..." onkeyup="filterPlayers('team1', this.value)" 
                               style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none;">
                    </div>
                    <div class="player-list" id="list-team1">
                        <?php 
                        $team1Ids = array_column($data['squads']['team1'] ?? [], 'player_id');
                        $team2Ids = array_column($data['squads']['team2'] ?? [], 'player_id');
                        
                        $team1Data = [];
                        if (!empty($data['squads']['team1'])) {
                            foreach ($data['squads']['team1'] as $sq) $team1Data[$sq['player_id']] = $sq;
                        }
                        
                        $team2Data = [];
                        if (!empty($data['squads']['team2'])) {
                            foreach ($data['squads']['team2'] as $sq) $team2Data[$sq['player_id']] = $sq;
                        }
                        
                        $allPlayers = $data['all_players'] ?? [];
                        if (is_array($allPlayers) && !empty($allPlayers)):
                            foreach ($allPlayers as $p): 
                                // EXCLUSIVITY LOGIC:
                                // If player is in Team 2 AND NOT a Guest in Team 2, hide from Team 1 list
                                if (in_array($p['player_id'], $team2Ids)) {
                                    $isTeam2Guest = $team2Data[$p['player_id']]['is_guest'] ?? false;
                                    if (!$isTeam2Guest) continue;
                                }

                                $isSelected = in_array($p['player_id'], $team1Ids);
                                $isGuest = $isSelected && ($team1Data[$p['player_id']]['is_guest'] ?? false);
                                $isCaptain = $isSelected && ($team1Data[$p['player_id']]['is_captain'] ?? false);
                                // Parse Role Tags for WK
                                $roleTags = isset($team1Data[$p['player_id']]['role_tags']) 
                                    ? json_decode($team1Data[$p['player_id']]['role_tags'], true) 
                                    : [];
                                $isWk = $isSelected && in_array('WK', (array)$roleTags);
                        ?>
                        <div class="player-item <?= $isSelected ? 'selected' : '' ?>" onclick="toggleCheck(this)">
                            <div class="player-check">
                                <?= $isSelected ? '✓' : '' ?>
                            </div>
                            <input type="checkbox" name="player_ids[]" value="<?= $p['player_id'] ?>" <?= $isSelected ? 'checked' : '' ?> style="display: none;">
                            
                            <div class="player-details">
                                <div class="player-name"><?= e($p['name']) ?></div>
                                <div class="player-meta">
                                    <span><?= substr($p['batting_hand'], 0, 1) ?>HB</span> • 
                                    <span><?= $p['bowling_style'] ?></span>
                                </div>
                            </div>

                            <div class="player-actions-mini" onclick="event.stopPropagation()">
                                <label class="mini-btn <?= $isGuest ? 'active' : '' ?>" title="Guest">
                                    <input type="checkbox" name="is_guest_<?= $p['player_id'] ?>" value="1" <?= $isGuest ? 'checked' : '' ?> style="display: none;" onchange="this.parentElement.classList.toggle('active')">
                                    G
                                </label>
                                <label class="mini-btn <?= $isCaptain ? 'active' : '' ?>" title="Captain">
                                    <input type="checkbox" name="is_captain_<?= $p['player_id'] ?>" value="1" <?= $isCaptain ? 'checked' : '' ?> style="display: none;" onchange="this.parentElement.classList.toggle('active')">
                                    C
                                </label>
                                <label class="mini-btn <?= $isWk ? 'active' : '' ?>" title="Wicket Keeper">
                                    <input type="checkbox" name="is_wk_<?= $p['player_id'] ?>" value="1" <?= $isWk ? 'checked' : '' ?> style="display: none;" onchange="this.parentElement.classList.toggle('active')">
                                    WK
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    </div> <!-- End list-team1 -->
                    
                    <?php if (!($isLocked ?? false)): ?>
                    <div style="padding: 16px; border-top: 1px solid #f1f5f9;">
                        <button type="submit" class="mini-btn" style="width: 100%; background: #0f172a; color: white; border: none; font-weight: 600;">
                            Save <?= e($data['teams']['team1']['name'] ?? 'Team 1') ?> Squad
                        </button>
                    </div>
                    <?php endif; ?>
                    </form>
                </div> <!-- End view-team1 -->

                <!-- Team 2 Section -->
                <div id="view-team2" style="display: <?= $showTeam2 ? 'block' : 'none' ?>;">
                    <form method="POST">
                    <input type="hidden" name="action" value="update_squad">
                    <input type="hidden" name="team_id" value="<?= $data['teams']['team2']['team_id'] ?>">
                    
                    <div style="padding: 10px; border-bottom: 1px solid #f1f5f9;">
                        <input type="text" placeholder="Search players..." onkeyup="filterPlayers('team2', this.value)" 
                               style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none;">
                    </div>
                    <div class="player-list" id="list-team2">
                        <?php 
                        // Note: $team2Ids, $team2Data, $team1Ids, $team1Data are already defined above or need refresh?
                        // They are defined in the first block, but scope is shared in PHP file include.
                        // However, to be safe and clear, we use the variables established at top of Team 1 block.
                        // Actually, wait, scope IS shared. But we should just reuse them.
                        // Wait, I defined $team2Ids and $team2Data in the first block top.
                        
                        if (is_array($allPlayers) && !empty($allPlayers)):
                            foreach ($allPlayers as $p): 
                                // EXCLUSIVITY LOGIC:
                                // If player is in Team 1 AND NOT a Guest in Team 1, hide from Team 2 list
                                if (in_array($p['player_id'], $team1Ids)) {
                                    $isTeam1Guest = $team1Data[$p['player_id']]['is_guest'] ?? false;
                                    if (!$isTeam1Guest) continue;
                                }

                                $isSelected = in_array($p['player_id'], $team2Ids);
                                $isGuest = $isSelected && ($team2Data[$p['player_id']]['is_guest'] ?? false);
                                $isCaptain = $isSelected && ($team2Data[$p['player_id']]['is_captain'] ?? false);
                                $roleTags = isset($team2Data[$p['player_id']]['role_tags']) 
                                    ? json_decode($team2Data[$p['player_id']]['role_tags'], true) 
                                    : [];
                                $isWk = $isSelected && in_array('WK', (array)$roleTags);
                        ?>
                        <div class="player-item <?= $isSelected ? 'selected' : '' ?>" onclick="toggleCheck(this)">
                            <div class="player-check">
                                <?= $isSelected ? '✓' : '' ?>
                            </div>
                            <input type="checkbox" name="player_ids[]" value="<?= $p['player_id'] ?>" <?= $isSelected ? 'checked' : '' ?> style="display: none;">
                            
                            <div class="player-details">
                                <div class="player-name"><?= e($p['name']) ?></div>
                                <div class="player-meta">
                                    <span><?= substr($p['batting_hand'], 0, 1) ?>HB</span> • 
                                    <span><?= $p['bowling_style'] ?></span>
                                </div>
                            </div>

                            <div class="player-actions-mini" onclick="event.stopPropagation()">
                                <label class="mini-btn <?= $isGuest ? 'active' : '' ?>" title="Guest">
                                    <input type="checkbox" name="is_guest_<?= $p['player_id'] ?>" value="1" <?= $isGuest ? 'checked' : '' ?> style="display: none;" onchange="this.parentElement.classList.toggle('active')">
                                    G
                                </label>
                                <label class="mini-btn <?= $isCaptain ? 'active' : '' ?>" title="Captain">
                                    <input type="checkbox" name="is_captain_<?= $p['player_id'] ?>" value="1" <?= $isCaptain ? 'checked' : '' ?> style="display: none;" onchange="this.parentElement.classList.toggle('active')">
                                    C
                                </label>
                                <label class="mini-btn <?= $isWk ? 'active' : '' ?>" title="Wicket Keeper">
                                    <input type="checkbox" name="is_wk_<?= $p['player_id'] ?>" value="1" <?= $isWk ? 'checked' : '' ?> style="display: none;" onchange="this.parentElement.classList.toggle('active')">
                                    WK
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$isLocked): ?>
                    <div style="padding: 16px; border-top: 1px solid #f1f5f9;">
                        <button type="submit" class="mini-btn" style="width: 100%; background: #0f172a; color: white; border: none; font-weight: 600;">
                            Save <?= e($match['team2_name']) ?> Squad
                        </button>
                    </div>
                    <?php endif; ?>
                    </form>
            </div>
        </div>

        <!-- Toss Section -->
        <div class="section-card toss-section">
            <div class="section-header">
                <span class="section-title">Toss & Decision</span>
                <span><?= $data['validation']['toss']['valid'] ? '✅' : 'Pending' ?></span>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="record_toss">
                
                <div style="padding: 16px;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">WINNER</div>
                    <div class="toss-interface">
                        <label class="toss-card <?= $match['toss_winner_id'] == $match['team1_id'] ? 'active' : '' ?>">
                            <input type="radio" name="toss_winner_id" value="<?= $match['team1_id'] ?>" <?= $match['toss_winner_id'] == $match['team1_id'] ? 'checked' : '' ?> style="display: none;" onchange="updateTossUI(this)">
                            <span class="toss-icon">🪙</span>
                            <div><?= e($match['team1_short_name'] ?: $match['team1_name']) ?></div>
                        </label>
                        <label class="toss-card <?= $match['toss_winner_id'] == $match['team2_id'] ? 'active' : '' ?>">
                            <input type="radio" name="toss_winner_id" value="<?= $match['team2_id'] ?>" <?= $match['toss_winner_id'] == $match['team2_id'] ? 'checked' : '' ?> style="display: none;" onchange="updateTossUI(this)">
                            <span class="toss-icon">🪙</span>
                            <div><?= e($match['team2_short_name'] ?: $match['team2_name']) ?></div>
                        </label>
                    </div>

                    <div style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin: 16px 0 8px;">DECISION</div>
                    <div class="toss-interface">
                        <label class="toss-card <?= $match['toss_decision'] === 'bat' ? 'active' : '' ?>">
                            <input type="radio" name="toss_decision" value="bat" <?= $match['toss_decision'] === 'bat' ? 'checked' : '' ?> style="display: none;" onchange="updateTossUI(this)">
                            <span class="toss-icon">🏏</span>
                            <div>Bat First</div>
                        </label>
                        <label class="toss-card <?= $match['toss_decision'] === 'bowl' ? 'active' : '' ?>">
                            <input type="radio" name="toss_decision" value="bowl" <?= $match['toss_decision'] === 'bowl' ? 'checked' : '' ?> style="display: none;" onchange="updateTossUI(this)">
                            <span class="toss-icon">🥎</span>
                            <div>Bowl First</div>
                        </label>
                    </div>

                    <?php if (!$isLocked): ?>
                    <button type="submit" class="mini-btn" style="width: 100%; margin-top: 16px; background: #0f172a; color: white; border: none; font-weight: 600; height: 40px;">
                        Save Toss
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

    </div>

    <!-- Floating Action Button -->
    <?php if ($isLive): ?>
        <a href="<?= adminUrl('matches/scorer.php?id=' . $matchId) ?>" class="start-fab" style="text-decoration: none;">
            <span>🔴</span> Open Scorer
        </a>
    <?php elseif ($isCompleted): ?>
        <a href="<?= adminUrl('matches/view.php?id=' . $matchId) ?>" class="start-fab" style="background: #0f172a; text-decoration: none;">
            <span>🏁</span> View Scorecard
        </a>
    <?php elseif ($data['validation']['ready_to_start']): ?>
        <form method="POST" onsubmit="return confirm('Start the match now?')">
            <input type="hidden" name="action" value="start_match">
            <button type="submit" class="start-fab">
                <span>🚀</span> Start Match
            </button>
        </form>
    <?php else: ?>
        <button class="start-fab" disabled>
            Complete Setup to Start
        </button>
    <?php endif; ?>

</div>

<script>
    function showTeam(team) {
        document.getElementById('view-team1').style.display = team === 'team1' ? 'block' : 'none';
        document.getElementById('view-team2').style.display = team === 'team2' ? 'block' : 'none';
        
        document.getElementById('tab-team1').classList.toggle('active', team === 'team1');
        document.getElementById('tab-team2').classList.toggle('active', team === 'team2');
    }

    function toggleCheck(el) {
        if (el.classList.contains('disabled')) return;
        
        const checkbox = el.querySelector('input[type="checkbox"]');
        const checkIcon = el.querySelector('.player-check');
        
        checkbox.checked = !checkbox.checked;
        el.classList.toggle('selected');
        checkIcon.innerHTML = checkbox.checked ? '✓' : '';
    }

    function updateTossUI(radio) {
        // Find all radios with same name and remove active class from their parent labels
        const name = radio.name;
        const radios = document.querySelectorAll(`input[name="${name}"]`);
        radios.forEach(r => {
            r.closest('.toss-card').classList.remove('active');
        });
        
        // Add active class to checked one
        if (radio.checked) {
            radio.closest('.toss-card').classList.add('active');
        }
    }
    
    function filterPlayers(team, query) {
        const listId = 'list-' + team;
        const listContainer = document.getElementById(listId);
        const input = query.toLowerCase();
        
        const items = listContainer.getElementsByClassName('player-item');
        for (let i = 0; i < items.length; i++) {
            const nameEl = items[i].querySelector('.player-name');
            if (nameEl) {
                const name = nameEl.textContent.toLowerCase();
                if (name.includes(input)) {
                    items[i].style.display = 'flex';
                } else {
                    items[i].style.display = 'none';
                }
            }
        }
    }
</script>
