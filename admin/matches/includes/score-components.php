<?php
/**
 * Score Page HTML Components
 * 
 * Extracted from score.php to reduce file size and improve maintainability.
 * Contains reusable HTML components for the scoring interface.
 */

/**
 * Render score display header
 * 
 * @param array $match Match data
 * @param string $battingTeam Batting team name
 * @param string $bowlingTeam Bowling team name
 * @param int $currentInnings Current innings number
 * @param int $firstInningsTotal First innings total (for target calculation)
 * @return string HTML
 */
function renderScoreDisplayHeader($match, $battingTeam, $bowlingTeam, $currentInnings, $firstInningsTotal) {
    ob_start();
    ?>
    <!-- 1. Header Area -->
    <div class="score-display">
        <div class="score-main-info">
            <div class="score-big" id="current-score">0/0</div>
            <div class="score-details">
                <div class="score-overs" id="current-overs">0.0 Overs</div>
                <div class="score-rr">CRR: <span id="run-rate">0.00</span></div>
            </div>
        </div>
        
        <div class="match-status-bar">
            <span><?= e($battingTeam) ?> vs <?= e($bowlingTeam) ?></span>
            <?php if ($currentInnings == 2 && $firstInningsTotal > 0): ?>
                <span>Target: <?= $firstInningsTotal + 1 ?></span>
                <span>Req RR: <span id="required-rr">0.00</span></span>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render players card with selection dropdowns
 * 
 * @param array $battingTeamPlayers Batting team players
 * @param array $bowlingTeamPlayers Bowling team players
 * @param int|null $currentStrikerId Current striker ID
 * @param int|null $currentNonStrikerId Current non-striker ID
 * @param int|null $currentBowlerId Current bowler ID
 * @return string HTML
 */
function renderPlayersCard($battingTeamPlayers, $bowlingTeamPlayers, $currentStrikerId, $currentNonStrikerId, $currentBowlerId) {
    ob_start();
    ?>
    <!-- 2. Players Card - Unified Design -->
    <div class="players-card">
        <div class="players-card-header">
            <span>Players</span>
            <button class="btn-change" onclick="document.getElementById('player-selects').style.display = document.getElementById('player-selects').style.display === 'none' ? 'block' : 'none'">
                Change
            </button>
        </div>

        <!-- Player Selection Dropdowns (Hidden by default) -->
        <?= renderPlayerSelects($battingTeamPlayers, $bowlingTeamPlayers, $currentStrikerId, $currentNonStrikerId, $currentBowlerId) ?>

        <!-- Compact Player Rows -->
        <?= renderCompactPlayerRows() ?>
    </div>
    <?php
    return ob_get_clean();
}

function renderPlayerSelects($battingTeamPlayers, $bowlingTeamPlayers, $currentStrikerId, $currentNonStrikerId, $currentBowlerId) {
    ob_start();
    ?>
    <div id="player-selects" style="display: none; padding: 12px; background: var(--bg-body); border-bottom: 1px solid var(--border);">
        <div style="margin-bottom: 8px;">
            <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Striker</label>
            <select id="striker" onchange="updateStriker()" style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
                <option value="">Select...</option>
                <?php foreach ($battingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>" <?= ($currentStrikerId == $player['player_id']) ? 'selected' : '' ?>><?= e($player['name']) ?><?php if ($player['is_guest']): ?> 🌟<?php endif; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-bottom: 8px;">
            <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Non-Striker</label>
            <select id="non-striker" onchange="updateNonStriker()" style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
                <option value="">Select...</option>
                <?php foreach ($battingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>" <?= ($currentNonStrikerId == $player['player_id']) ? 'selected' : '' ?>><?= e($player['name']) ?><?php if ($player['is_guest']): ?> 🌟<?php endif; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Bowler</label>
            <select id="bowler" onchange="updateBowler()" style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.85rem;">
                <option value="">Select...</option>
                <?php foreach ($bowlingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>" <?= ($currentBowlerId == $player['player_id']) ? 'selected' : '' ?>><?= e($player['name']) ?><?php if ($player['is_guest']): ?> 🌟<?php endif; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderCompactPlayerRows() {
    ob_start();
    ?>
    <!-- Striker -->
    <div class="player-row" id="striker-compact">
        <div class="player-indicator striker"></div>
        <div class="player-info">
            <div class="player-label">Striker 🏏</div>
            <div class="player-name" id="striker-name-compact">Select Player</div>
            <div class="player-stats" id="striker-stats-compact">
                <span><span class="player-stat-val" id="striker-runs-compact">0</span> Runs</span>
                <span><span class="player-stat-val" id="striker-balls-compact">0</span> Balls</span>
                <span>SR: <span class="player-stat-val" id="striker-sr-compact">0.0</span></span>
            </div>
        </div>
    </div>

    <!-- Non-Striker -->
    <div class="player-row" id="non-striker-compact">
        <div class="player-indicator non-striker"></div>
        <div class="player-info">
            <div class="player-label">Non-Striker</div>
            <div class="player-name" id="non-striker-name-compact">Select Player</div>
            <div class="player-stats" id="non-striker-stats-compact">
                <span><span class="player-stat-val" id="non-striker-runs-compact">0</span> Runs</span>
                <span><span class="player-stat-val" id="non-striker-balls-compact">0</span> Balls</span>
            </div>
        </div>
    </div>

    <!-- Bowler -->
    <div class="player-row" id="bowler-compact">
        <div class="player-indicator bowler"></div>
        <div class="player-info">
            <div class="player-label">Bowler 🎾</div>
            <div class="player-name" id="bowler-name-compact">Select Player</div>
            <div class="player-stats" id="bowler-stats-compact">
                <span><span class="player-stat-val" id="bowler-overs-compact">0.0</span> Ov</span>
                <span><span class="player-stat-val" id="bowler-runs-compact">0</span> Runs</span>
                <span><span class="player-stat-val" id="bowler-wickets-compact">0</span> Wkts</span>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render current over summary card
 * 
 * @return string HTML
 */
function renderCurrentOverSummary() {
    ob_start();
    ?>
    <!-- 3. Current Over Summary -->
    <div class="card" style="margin-bottom: var(--spacing-md); background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd;">
        <div style="padding: 12px;">
            <div style="font-size: 0.75rem; font-weight: 600; color: #0369a1; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Current Over</div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: #0c4a6e;" id="current-over-runs">0</div>
                    <div style="font-size: 0.7rem; color: #0369a1;">Runs</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #0c4a6e;" id="current-over-balls">0</div>
                    <div style="font-size: 0.7rem; color: #0369a1;">Balls</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: #0c4a6e;" id="current-over-extras">0</div>
                    <div style="font-size: 0.7rem; color: #0369a1;">Extras</div>
                </div>
            </div>
            <div id="current-over-balls-display" style="display: flex; gap: 4px; margin-top: 12px; flex-wrap: wrap; min-height: 24px;">
                <!-- Ball badges will be added here dynamically -->
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render scoring controls (run buttons, extras, wicket)
 * 
 * @return string HTML
 */
function renderScoringControls($matchId = null, $currentInnings = null) {
    ob_start();
    ?>
    <!-- 4. Scoring Controls -->
    <div id="overs-list" class="overs-list" style="margin-bottom: var(--spacing-md); font-size: 0.9rem; color: var(--text-secondary);"></div>
    <div class="control-section">
        <div class="control-section-title">Record Runs</div>
        <div class="control-grid">
            <button class="btn-score btn-0" onclick="recordRun(0)">0</button>
            <button class="btn-score btn-1" onclick="recordRun(1)">1</button>
            <button class="btn-score btn-2" onclick="recordRun(2)">2</button>
            <button class="btn-score btn-3" onclick="recordRun(3)">3</button>
            <button class="btn-score btn-4" onclick="recordRun(4)">4</button>
            <button class="btn-score btn-6" onclick="recordRun(6)">6</button>
        </div>

        <div class="control-section-title" style="margin-top: 16px;">Extras & Wicket</div>
        <div class="extras-grid">
            <button class="btn-extra" onclick="recordExtra('wide')">Wide</button>
            <button class="btn-extra" onclick="recordExtra('no-ball')">No Ball</button>
            <button class="btn-extra" onclick="recordExtra('bye')">Bye</button>
            <button class="btn-extra" onclick="recordExtra('leg-bye')">Leg Bye</button>
            <button class="btn-wicket" onclick="showWicketModal()">OUT / WICKET</button>
        </div>

        <button class="btn-undo" onclick="undoLastBall()" id="undo-btn" disabled>Undo Last Ball</button>

        <?php if ($currentInnings == 1 && $matchId): ?>
            <a href="<?= adminUrl('matches/change-innings.php?match_id=' . $matchId . '&current_innings=1') ?>" 
               class="btn-undo" 
               style="text-align: center; text-decoration: none; display: block; background: var(--primary);"
               onclick="return confirm('End Innings?');">
                End Innings ⏭️
            </a>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render info panel (right side)
 * 
 * @return string HTML
 */
function renderInfoPanel() {
    ob_start();
    ?>
    <!-- 4. Info Panel (Right) -->
    <div class="panel-info">
        <div class="info-header">This Over</div>
        <div class="info-content">
            <div class="this-over-balls" id="current-over-balls-compact">
                <!-- JS will populate this -->
                <div class="ball-circle"></div>
                <div class="ball-circle"></div>
                <div class="ball-circle"></div>
                <div class="ball-circle"></div>
                <div class="ball-circle"></div>
                <div class="ball-circle"></div>
            </div>

            <div class="info-header" style="margin: 0 -1rem 1rem -1rem; padding-left: 1rem;">Recent Balls</div>
            <div id="recent-balls-compact" class="this-over-balls" style="justify-content: flex-start; gap: 4px; flex-wrap: wrap;">
                <!-- JS will populate this -->
                <div style="color: var(--text-secondary); font-size: 0.75rem;">No balls yet</div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

