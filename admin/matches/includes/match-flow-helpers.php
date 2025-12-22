<?php
/**
 * Match Flow Helper Functions
 * 
 * View-level helper functions for rendering match flow interface.
 * These functions handle presentation logic only.
 */

/**
 * Sanitize and get GET parameter
 * 
 * @param string $key Parameter key
 * @param string $default Default value
 * @return string Sanitized value
 */
function getSanitizedParam(string $key, string $default = ''): string
{
    $value = getQuery($key, $default);
    return is_string($value) ? sanitizeString($value) : $default;
}

/**
 * Handle CSRF validation error with detailed logging
 * 
 * Logs full exception details including file, line, and stack trace for debugging.
 * Returns user-friendly error message without exposing sensitive details.
 * 
 * @param Exception $e Exception caught
 * @param string $context Error context for logging (e.g., 'assign players handler')
 * @return string User-friendly error message safe for display
 */
function handleCsrfError(Exception $e, string $context): string
{
    $errorDetails = sprintf(
        "Error in %s: %s | File: %s:%d | Trace: %s",
        $context,
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    
    if (strpos($e->getMessage(), 'CSRF') !== false) {
        error_log($errorDetails);
        return 'Security validation failed. Please try again.';
    } else {
        error_log($errorDetails);
        return 'An error occurred while processing your request. Please try again.';
    }
}

/**
 * Render player assignment form for a team
 * 
 * @param array $team Team data
 * @param int $matchId Match ID
 * @param int $teamId Team ID
 * @param array $filteredPlayers Players to show for this team
 * @param array $appearances Existing appearances for this team
 * @param string $buttonLabel Button label text
 * @return string HTML form
 */
function renderPlayerAssignmentForm(array $team, int $matchId, int $teamId, array $filteredPlayers, array $appearances, string $buttonLabel): string
{
    ob_start();
    ?>
    <div style="margin-bottom: var(--spacing-lg);">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 style="margin-bottom: 0; font-size: 1rem;"><?= e($team['name']) ?> Players</h3>
            <button type="button" class="btn btn-secondary btn-sm" onclick="autoSelectPlayers('form-team-<?= $teamId ?>')">
                🎲 Auto-Pick 11
            </button>
        </div>
        <form method="POST" id="form-team-<?= $teamId ?>">
            <?= csrfInput() ?>
            <input type="hidden" name="action" value="assign_players">
            <input type="hidden" name="team_id" value="<?= $teamId ?>">
            <div class="player-grid">
                <?php foreach ($filteredPlayers as $player): ?>
                    <label class="player-card <?= isset($appearances[$player['player_id']]) ? 'selected' : '' ?>">
                        <input type="checkbox" name="player_ids[]" value="<?= $player['player_id'] ?>" 
                               <?= isset($appearances[$player['player_id']]) ? 'checked' : '' ?>>
                        <div style="font-weight: 500;"><?= e($player['name']) ?></div>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: var(--spacing-md); width: 100%;"><?= e($buttonLabel) ?></button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}



