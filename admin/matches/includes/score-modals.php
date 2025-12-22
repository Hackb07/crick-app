<?php
/**
 * Score Page Modals
 * 
 * Extracted from score.php to reduce file size.
 * Contains all modal dialogs used in the scoring interface.
 */

/**
 * Render all modals for score page
 * 
 * @param array $battingTeamPlayers Batting team players
 * @param array $bowlingTeamPlayers Bowling team players
 * @param array $availableBatsmen Available batsmen for selection
 * @return string HTML
 */
function renderScoreModals($battingTeamPlayers, $bowlingTeamPlayers, $availableBatsmen = []) {
    ob_start();
    ?>
    <!-- Wicket Modal -->
    <div class="wicket-modal" id="wicket-modal">
        <div class="wicket-modal-content">
            <h2 style="margin-bottom: var(--spacing-md);">Select Dismissal Type</h2>
            <div class="wicket-types">
                <button class="wicket-type-btn" onclick="recordWicketType('bowled')">Bowled</button>
                <button class="wicket-type-btn" onclick="recordWicketType('caught')">Caught</button>
                <button class="wicket-type-btn" onclick="recordWicketType('lbw')">LBW</button>
                <button class="wicket-type-btn" onclick="recordWicketType('stumped')">Stumped</button>
                <button class="wicket-type-btn" onclick="recordWicketType('hit_wicket')">Hit Wicket</button>
                <button class="btn-wicket" onclick="showRunOutSelection()" style="background: #fd7e14;">Run Out</button>
            </div>
            <div style="margin-top: var(--spacing-lg);">
                <button class="btn btn-secondary btn-lg" onclick="closeWicketModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Fielder Selection Modal -->
    <div class="wicket-modal" id="fielder-modal">
        <div class="wicket-modal-content">
            <h2 style="margin-bottom: var(--spacing-md);">Who took the catch?</h2>
            <input type="hidden" id="pending-dismissal-type" value="">
            <div style="margin-bottom: var(--spacing-md);">
                <select id="fielder-select" class="form-select" style="width: 100%; padding: var(--spacing-md); font-size: 1.125rem;">
                    <option value="">Select Fielder...</option>
                    <?php foreach ($bowlingTeamPlayers as $player): ?>
                        <option value="<?= $player['player_id'] ?>"><?= e($player['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-top: var(--spacing-lg); display: flex; gap: var(--spacing-md);">
                <button class="btn btn-secondary" onclick="closeFielderModal()" style="flex: 1;">Cancel</button>
                <button class="btn btn-primary" onclick="confirmFielder()" style="flex: 1;">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Run Out Selection Modal -->
    <div class="wicket-modal" id="run-out-modal">
        <div class="wicket-modal-content">
            <h2 style="margin-bottom: var(--spacing-md);">Who is Run Out?</h2>
            <div class="wicket-grid">
                <button class="btn-wicket" onclick="recordRunOut('striker')">Striker</button>
                <button class="btn-wicket" onclick="recordRunOut('non_striker')">Non-Striker</button>
            </div>
            <div style="margin-top: var(--spacing-lg);">
                <button class="btn btn-secondary btn-lg" onclick="closeRunOutModal()">Back</button>
            </div>
        </div>
    </div>

    <!-- Start 2nd Innings Modal -->
    <div class="wicket-modal" id="start-innings-modal">
        <div class="wicket-modal-content">
            <h2 style="margin-bottom: var(--spacing-md);">Start 2nd Innings?</h2>
            <p style="margin-bottom: var(--spacing-md);">
                Are you sure you want to start the 2nd innings?
                <br><br>
                <strong>Warning:</strong> You will NOT be able to undo the last ball of the 1st innings after this action.
            </p>
            <div style="margin-top: var(--spacing-lg); display: flex; gap: var(--spacing-md);">
                <button class="btn btn-secondary" onclick="closeStartInningsModal()" style="flex: 1;">Cancel</button>
                <button class="btn btn-primary" onclick="confirmStartInnings()" style="flex: 1;">Start 2nd Innings</button>
            </div>
        </div>
    </div>

    <!-- Extra Runs Modal (for Wide/No-Ball) -->
    <div class="wicket-modal" id="extra-runs-modal">
        <div class="wicket-modal-content">
            <h2 id="extra-runs-modal-title" style="margin-bottom: var(--spacing-md);">Wide - Additional Runs?</h2>
            <input type="hidden" id="extra-runs-type" value="">
            <div style="margin-bottom: var(--spacing-md);">
                <label style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600;">Additional Runs (0, 1, 2, 3, 4, or 6):</label>
                <select id="extra-runs-amount" class="form-select" style="width: 100%; padding: var(--spacing-md); font-size: 1.125rem;">
                    <option value="1">1 (Base run only, no additional)</option>
                    <option value="2">2 (1 base + 1 additional)</option>
                    <option value="3">3 (1 base + 2 additional)</option>
                    <option value="4">4 (1 base + 3 additional)</option>
                    <option value="5">5 (1 base + 4 additional)</option>
                    <option value="7">7 (1 base + 6 additional)</option>
                </select>
                <small style="display: block; margin-top: var(--spacing-xs); color: var(--text-secondary);">
                    Total runs = 1 (base) + additional runs
                </small>
            </div>
            <div style="margin-top: var(--spacing-lg); display: flex; gap: var(--spacing-md);">
                <button class="btn btn-secondary" onclick="closeExtraRunsModal()" style="flex: 1;">Cancel</button>
                <button class="btn btn-primary" onclick="recordExtraWithRuns()" style="flex: 1;">Record</button>
            </div>
        </div>
    </div>

    <!-- New Batsman Modal (after wicket) -->
    <div class="wicket-modal" id="new-batsman-modal">
        <div class="wicket-modal-content">
            <h2 style="margin-bottom: var(--spacing-md);">Select New Batsman</h2>
            <div style="margin-bottom: var(--spacing-md);">
                <label style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600;">New Batsman:</label>
                <select id="new-batsman-select" class="form-select" style="width: 100%; padding: var(--spacing-md); font-size: 1.125rem;">
                    <option value="">Select...</option>
                    <?php if (!empty($availableBatsmen)): ?>
                        <?php foreach ($availableBatsmen as $player): ?>
                            <option value="<?= $player['player_id'] ?>"><?= e($player['name']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback: Show all batting team players if available batsmen list is empty -->
                        <?php foreach ($battingTeamPlayers as $player): ?>
                            <option value="<?= $player['player_id'] ?>"><?= e($player['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small style="display: block; margin-top: var(--spacing-xs); color: var(--text-secondary);">
                    <?php if (count($availableBatsmen) < count($battingTeamPlayers)): ?>
                        Showing available players only (excludes dismissed and current batsmen)
                    <?php else: ?>
                        New batsman will take the strike position
                    <?php endif; ?>
                </small>
            </div>
            <div style="margin-top: var(--spacing-lg); display: flex; gap: var(--spacing-md);">
                <button class="btn btn-secondary" onclick="closeNewBatsmanModal()" style="flex: 1;">Cancel</button>
                <button class="btn btn-primary" onclick="confirmNewBatsman()" style="flex: 1;">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Retired Hurt Modal -->
    <div class="wicket-modal" id="retired-hurt-modal">
        <div class="wicket-modal-content">
            <h2 style="margin-bottom: var(--spacing-md);">Retired Hurt</h2>
            <div style="margin-bottom: var(--spacing-md);">
                <p style="margin-bottom: var(--spacing-md);">Player can return to bat later. Select replacement batsman:</p>
                <label style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600;">Replacement Batsman:</label>
                <select id="retired-hurt-replacement" class="form-select" style="width: 100%; padding: var(--spacing-md); font-size: 1.125rem;">
                    <option value="">Select...</option>
                    <?php foreach ($battingTeamPlayers as $player): ?>
                        <option value="<?= $player['player_id'] ?>"><?= e($player['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-top: var(--spacing-lg); display: flex; gap: var(--spacing-md);">
                <button class="btn btn-secondary" onclick="closeRetiredHurtModal()" style="flex: 1;">Cancel</button>
                <button class="btn btn-primary" onclick="confirmRetiredHurt()" style="flex: 1;">Record Retired Hurt</button>
            </div>
        </div>
    </div>

    <!-- Over Completion Notification -->
    <div class="over-notification" id="over-notification">
        <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🎉</div>
        <div style="font-size: 1.5rem; font-weight: 700;">Over Complete!</div>
        <div style="font-size: 1rem; margin-top: var(--spacing-sm);" id="over-notification-text"></div>
    </div>

    <!-- Undo Button -->
    <button class="undo-btn" id="undo-btn-fixed" onclick="undoLastBall()" title="Undo Last Ball" disabled>
        ↶
    </button>

    <!-- Start 2nd Innings Button (Floating) -->
    <button id="start-2nd-innings-btn" onclick="showStartInningsModal()" style="
        display: none;
        position: fixed;
        bottom: 80px;
        right: 20px;
        background: var(--primary);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 50px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        z-index: 900;
        animation: pulse 2s infinite;
    ">
        Start 2nd Innings →
    </button>
    <?php
    return ob_get_clean();
}

