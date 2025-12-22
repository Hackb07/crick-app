<?php
/**
 * Scorer View - Professional Dashboard Layout
 * 
 * Replaces card layout with a high-density, professional scoring dashboard.
 */
?>
<!-- Scorer Dashboard Container -->
<div class="scorer-dashboard">
    <!-- Header -->
    <header class="score-header">
        <div class="header-top">
            <span class="match-info-small"><?= e(($match['series_name'] ?? 'Friendly Match')) ?> • <?= e($match['venue']) ?></span>
            <button class="btn-menu-icon" onclick="document.querySelector('.app-sidebar').classList.toggle('active')">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
        
        <div class="header-main-row">
            <div class="team-score-box">
                <div class="team-name-large"><?= e($battingTeam) ?></div>
                <div class="score-large">
                    <span id="score-runs-display"><?= $currentScore ?></span>/<span id="score-wickets-display"><?= $currentWickets ?></span>
                </div>
            </div>
            
            <div class="match-stats-box">
                <div class="stat-pair">
                    <span class="stat-label">OVERS</span>
                    <span class="stat-value" id="score-overs-display"><?= $currentOvers ?>.<?= $currentBalls ?></span>
                </div>
                <div class="stat-pair">
                    <span class="stat-label">CRR</span>
                    <span class="stat-value" id="run-rate">0.00</span>
                </div>
            </div>
        </div>
        
        <div class="header-footer-row">
            <span>Bowling: <?= e($bowlingTeam) ?></span>
            <!-- Optional Target Display for 2nd Innings -->
            <?php if ($currentInnings == 2): ?>
                <span>Target: <span id="target-display"><?= isset($firstInningsTotal) ? $firstInningsTotal + 1 : '-' ?></span></span>
            <?php endif; ?>
        </div>
    </header>

    <!-- Body Content -->
    <div class="score-body">
        
        <!-- Hidden Selects (Preserved Logic) -->
        <div style="display:none;">
            <select id="striker">
                <option value="">Select Striker</option>
                <?php foreach ($battingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>" <?= (isset($currentStrikerId) && $currentStrikerId == $player['player_id']) ? 'selected' : '' ?>><?= e($player['player_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="non-striker">
                <option value="">Select Non-Striker</option>
                <?php foreach ($battingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>" <?= (isset($currentNonStrikerId) && $currentNonStrikerId == $player['player_id']) ? 'selected' : '' ?>><?= e($player['player_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="bowler">
                <option value="">Select Bowler</option>
                <?php foreach ($bowlingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>" <?= (isset($currentBowlerId) && $currentBowlerId == $player['player_id']) ? 'selected' : '' ?>><?= e($player['player_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Batsmen Table -->
        <div class="table-container">
            <table class="stats-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">Batter</th>
                        <th class="text-right">R</th>
                        <th class="text-right">B</th>
                        <th class="text-right">4s</th>
                        <th class="text-right">6s</th>
                        <!-- <th class="text-right">SR</th> -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Striker Row -->
                    <tr class="striker-row active" id="striker-status" onclick="openPlayerSelect('striker')">
                        <td class="player-name-cell">
                             <div class="indicator"></div>
                             <span id="striker-name-display">Select Striker</span>
                        </td>
                        <td class="text-right value-bold" id="striker-runs-display">0</td>
                        <td class="text-right" id="striker-balls-display">0</td>
                        <td class="text-right" id="striker-4s-display">0</td>
                        <td class="text-right" id="striker-6s-display">0</td>
                    </tr>
                    <!-- Non-Striker Row -->
                    <tr class="striker-row" onclick="openPlayerSelect('non-striker')">
                        <td class="player-name-cell">
                             <div class="indicator" style="background:transparent; box-shadow:none;"></div>
                             <span id="non-striker-name-display">Select Non-Striker</span>
                        </td>
                        <td class="text-right value-bold" id="non-striker-runs-display">0</td>
                        <td class="text-right" id="non-striker-balls-display">0</td>
                        <td class="text-right" id="non-striker-4s-display">0</td>
                        <td class="text-right" id="non-striker-6s-display">0</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Bowler & Current Over -->
        <div class="bowler-card" onclick="openPlayerSelect('bowler')">
            <div class="bowler-header">
                <span class="label">CURRENT BOWLER</span>
                <span class="bowler-name" id="bowler-name-display">Select Bowler</span>
            </div>
            <div class="bowler-stats-grid">
                <div class="b-stat"><span class="b-val" id="bowler-overs-display">0.0</span><span class="b-lbl">O</span></div>
                <div class="b-stat"><span class="b-val" id="bowler-maidens-display">0</span><span class="b-lbl">M</span></div>
                <div class="b-stat"><span class="b-val" id="bowler-runs-display">0</span><span class="b-lbl">R</span></div>
                <div class="b-stat"><span class="b-val" id="bowler-wickets-display">0</span><span class="b-lbl">W</span></div>
                <div class="b-stat"><span class="b-val" id="bowler-eco-display">-</span><span class="b-lbl">ECO</span></div>
            </div>
        </div>

        <!-- Ball Tracker -->
        <div id="ball-tracker" class="ball-tracker-modern">
            <div class="ball-item ball-dot">•</div>
            <div class="ball-item ball-dot">•</div>
            <div class="ball-item ball-dot">•</div>
            <div class="ball-item ball-dot">•</div>
            <div class="ball-item ball-dot">•</div>
            <div class="ball-item ball-dot">•</div>
        </div>
    </div>

    <!-- Keypad (Sticky Bottom) -->
    <div class="score-keypad">
        <div class="keypad-grid">
            <!-- Row 1 -->
            <button class="key-btn btn-run" onclick="recordRun(0)">0</button>
            <button class="key-btn btn-run" onclick="recordRun(1)">1</button>
            <button class="key-btn btn-run" onclick="recordRun(2)">2</button>
            <button class="key-btn btn-run" onclick="recordRun(3)">3</button>
            
            <!-- Row 2 -->
            <button class="key-btn btn-run" onclick="recordRun(4)">4</button>
            <button class="key-btn btn-boundary" onclick="recordRun(6)">6</button>
            <button class="key-btn btn-run" onclick="recordRun(5)">5</button>
            <button class="key-btn btn-extra" onclick="showExtrasModal()">EXT</button>
            
            <!-- Row 3 -->
            <button class="key-btn btn-extra" onclick="recordExtra('wide')">WD</button>
            <button class="key-btn btn-extra" onclick="recordExtra('no-ball')">NB</button>
            <button class="key-btn btn-extra" onclick="recordExtra('bye')">BYE</button>
            <button class="key-btn btn-extra" onclick="recordExtra('leg-bye')">LB</button>
            
             <!-- Row 4 -->
            <button class="key-btn btn-out" onclick="showWicketModal()">OUT</button>
            <button class="key-btn" style="color: #64748b;" onclick="undoLastBall()" id="undo-btn" disabled>UNDO</button>
            <button class="key-btn" style="color: #64748b;" onclick="swapStrike()">SWAP</button>
            <button class="key-btn" style="color: #64748b;" onclick="recordRun(7)">7+</button>
        </div>
    </div>
</div>

<!-- Modals (Preserved) -->

<!-- Player Selection Modal -->
<div class="modal-overlay" id="player-modal" onclick="closeModal(event)" role="dialog" aria-labelledby="modal-title" aria-modal="true" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Select Player</h3>
            <button type="button" class="btn-close" onclick="closeModal()" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Striker List -->
            <div class="player-list" id="striker-list" style="display: none;">
                <?php foreach ($battingTeamPlayers as $player): ?>
                    <button class="player-list-item" onclick="selectStriker(<?= (int)$player['player_id'] ?>, '<?= e($player['player_name']) ?>')">
                        <span><?= e($player['player_name']) ?></span>
                        <span class="role-badge"><?= e($player['role'] ?? 'Bat') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Non-Striker List -->
            <div class="player-list" id="non-striker-list" style="display: none;">
                <?php foreach ($battingTeamPlayers as $player): ?>
                    <button class="player-list-item" onclick="selectNonStriker(<?= (int)$player['player_id'] ?>, '<?= e($player['player_name']) ?>')">
                        <span><?= e($player['player_name']) ?></span>
                        <span class="role-badge"><?= e($player['role'] ?? 'Bat') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Bowler List -->
            <div class="player-list" id="bowler-list" style="display: none;">
                <?php foreach ($bowlingTeamPlayers as $player): ?>
                    <button class="player-list-item" onclick="selectBowler(<?= (int)$player['player_id'] ?>, '<?= e($player['player_name']) ?>')">
                        <span><?= e($player['player_name']) ?></span>
                        <span class="role-badge"><?= e($player['role'] ?? 'Bowl') ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Wicket Modal -->
<div class="modal-overlay" id="wicket-modal" onclick="closeModal(event)" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Wicket Type</h3>
            <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="wicket-options-grid">
                <button class="wicket-option-btn danger" onclick="recordWicket('bowled')">Bowled</button>
                <button class="wicket-option-btn danger" onclick="recordWicket('caught')">Caught</button>
                <button class="wicket-option-btn danger" onclick="recordWicket('lbw')">LBW</button>
                <button class="wicket-option-btn danger" onclick="recordWicket('run-out')">Run Out</button>
                <button class="wicket-option-btn danger" onclick="recordWicket('stumped')">Stumped</button>
                <button class="wicket-option-btn" onclick="recordWicket('hit-wicket')">Hit Wicket</button>
                <button class="wicket-option-btn" onclick="recordWicket('retired')">Retired</button>
            </div>
        </div>
    </div>
</div>

<!-- Extras Modal -->
<div class="modal-overlay" id="extras-modal" onclick="closeModal(event)" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">More Extras</h3>
            <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="wicket-options-grid">
                <button class="wicket-option-btn" onclick="recordExtra('penalty-run')">Penalty Run</button>
                <button class="wicket-option-btn" onclick="recordExtra('bonus')">Bonus Run</button>
            </div>
        </div>
    </div>
</div>

<!-- End Innings Modal -->
<div class="modal-overlay" id="start-innings-modal" onclick="closeStartInningsModal()" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">End of 1st Innings</h3>
        </div>
        <div class="modal-body">
            <p>1st Innings Completed.</p>
            <p>Score: <strong id="modal-innings-score"><?= $currentScore ?>/<?= $currentWickets ?></strong></p>
            <button class="key-btn btn-action" onclick="changeInningsAjax()" style="width: 100%; margin-top: 16px; background-color: #10b981; color: white;">Start 2nd Innings</button>
        </div>
    </div>
</div>

<!-- New Batsman Modal -->
<div class="modal-overlay" id="new-batsman-modal" onclick="closeModal(event)" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Select New Batsman</h3>
            <button type="button" class="btn-close" onclick="closeNewBatsmanModal()">&times;</button>
        </div>
        <div class="modal-body">
            <select id="new-batsman-select" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 16px;">
                <option value="">Select Batsman</option>
                <?php foreach ($battingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>"><?= e($player['player_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="key-btn btn-action" onclick="confirmNewBatsman()" style="width: 100%; background-color: #10b981; color: white;">Confirm</button>
        </div>
    </div>
</div>

<!-- Extra Runs Modal (for Wide/No Ball with runs) -->
<div class="modal-overlay" id="extra-runs-modal" onclick="closeModal(event)" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title" id="extra-runs-title">Extra Runs</h3>
            <button type="button" class="btn-close" onclick="closeExtraRunsModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 16px;">How many runs (including the extra)?</p>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                <button class="key-btn" onclick="recordExtraWithRuns(1)">1</button>
                <button class="key-btn" onclick="recordExtraWithRuns(2)">2</button>
                <button class="key-btn" onclick="recordExtraWithRuns(3)">3</button>
                <button class="key-btn" onclick="recordExtraWithRuns(4)">4</button>
                <button class="key-btn" onclick="recordExtraWithRuns(5)">5</button>
                <button class="key-btn btn-success" onclick="recordExtraWithRuns(6)">6</button>
                <button class="key-btn" onclick="recordExtraWithRuns(7)">7</button>
            </div>
        </div>
    </div>
</div>

<!-- Fielder Modal (for caught/stumped) -->
<div class="modal-overlay" id="fielder-modal" onclick="closeModal(event)" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Select Fielder</h3>
            <button type="button" class="btn-close" onclick="closeFielderModal()">&times;</button>
        </div>
        <div class="modal-body">
            <select id="fielder-select" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 16px;">
                <option value="">Select Fielder (Optional)</option>
                <?php foreach ($bowlingTeamPlayers as $player): ?>
                    <option value="<?= $player['player_id'] ?>"><?= e($player['player_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="key-btn btn-action" onclick="confirmFielder()" style="width: 100%; background-color: #10b981; color: white;">Confirm</button>
        </div>
    </div>
</div>

<!-- Run Out Modal (striker or non-striker) -->
<div class="modal-overlay" id="run-out-modal" onclick="closeModal(event)" hidden>
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Who is Run Out?</h3>
            <button type="button" class="btn-close" onclick="closeRunOutModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <button class="key-btn btn-action" onclick="recordRunOut('striker')" style="background-color: #dc3545; color: white;">Striker</button>
                <button class="key-btn btn-action" onclick="recordRunOut('non_striker')" style="background-color: #dc3545; color: white;">Non-Striker</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Inputs for JS State -->
<input type="hidden" id="extra-runs-type" value="">
<input type="hidden" id="extra-runs-amount" value="1">
<input type="hidden" id="pending-dismissal-type" value="">

<!-- Over Notification Toast -->
<div id="over-notification" class="notification-toast">
    <div class="notification-content">
        <span class="notification-icon">🏏</span>
        <span id="over-notification-text">Over Complete</span>
    </div>
</div>
