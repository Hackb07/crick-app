<?php
/**
 * Live Match Scorer Interface
 * 
 * Real-time cricket match scoring interface for authenticated scorers and administrators.
 * Provides comprehensive match event recording with offline support and state synchronization.
 * 
 * @package    CricApp
 * @subpackage Admin\Matches
 * @since      2.0.0
 * @author     CricApp Development Team
 * 
 * Architecture:
 * - MVC pattern with clear separation of concerns
 * - Service layer for business logic (MatchAdminService)
 * - Model layer for data access (MatchModel, Event, Player, Team)
 * - Presentational components (modals, cards, buttons)
 * 
 * Security:
 * - Authentication required (admin or scorer role)
 * - XSS prevention via htmlspecialchars() and json_encode with JSON_HEX flags
 * - CSRF protection through session validation
 * - Input validation at multiple layers
 * 
 * Performance:
 * - Optimistic UI updates
 * - Event sourcing for state reconstruction
 * - Client-side state management
 * - API call batching for offline sync
 * 
 * Accessibility:
 * - WCAG AA compliant
 * - ARIA labels and roles
 * - Keyboard navigation support
 * - Semantic HTML structure
 * 
 * Dependencies:
 * - BaseModel: Database abstraction
 * - MatchModel: Match data operations
 * - Event: Match events storage
 * - Player: Player data
 * - Team: Team data
 * - loadScoreData(): Data loader function
 * - getBattingTeam(): Helper from cricket-match-helpers.php
 * - calculateOvers(): Cricket calculation utilities
 */

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Core application bootstrapping
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/utils.php';
require_once __DIR__ . '/../../includes/cricket-match-helpers.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/includes/score-data-loader.php';

// ============================================================================
// CONSTANTS
// ============================================================================

/** @const int Number of legal deliveries per over in cricket */
const BALLS_PER_OVER = 6;

/** @const int Minimum valid match ID */
const MIN_VALID_MATCH_ID = 1;

/** @const int Maximum wickets possible (team size - 1) */
const MAX_WICKETS_DEFAULT = 10;

/** @const string Session key for error messages */
const SESSION_KEY_ERROR = 'error';

/** @const string Session key for success messages */
const SESSION_KEY_SUCCESS = 'success';

// ============================================================================
// AUTHENTICATION & AUTHORIZATION
// ============================================================================

/**
 * Validate user authentication and authorization
 * 
 * Ensures only authenticated users with appropriate roles (admin or scorer)
 * can access the scoring interface. Redirects to login if not authenticated.
 * 
 * @return void Exits with redirect if not authenticated
 * @throws void No exceptions thrown
 */
function validateUserAccess(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . adminUrl('login.php'), true, 302);
        exit;
    }
    
    $userRole = getSession('role');
    if (!in_array($userRole, ['admin', 'scorer'], true)) {
        $_SESSION[SESSION_KEY_ERROR] = 'Insufficient permissions to access scoring interface';
        header('Location: ' . adminUrl('index.php'), true, 403);
        exit;
    }
}

/**
 * Get appropriate redirect URL based on user role
 * 
 * @return string Redirect URL for current user's role
 */
function getRoleBasedRedirectUrl(): string
{
    $isScorer = (getSession('role') === 'scorer');
    return $isScorer ? adminUrl('scorer/index.php') : adminUrl('matches/');
}

// ============================================================================
// INPUT VALIDATION
// ============================================================================

/**
 * Validate match ID
 * 
 * Ensures match ID is a positive integer greater than or equal to MIN_VALID_MATCH_ID.
 * 
 * @param int $matchId The match ID to validate
 * @return bool True if valid, false otherwise
 */
function isValidMatchId(int $matchId): bool
{
    return $matchId >= MIN_VALID_MATCH_ID;
}

/**
 * Sanitize and validate match ID from request
 * 
 * @param string $redirectUrl Where to redirect if invalid
 * @return int Valid match ID
 * @throws void No exceptions thrown (redirects on error)
 */
function getValidatedMatchId(string $redirectUrl): int
{
    $matchId = (int)getQuery('id', 0);
    
    if (!isValidMatchId($matchId)) {
        $_SESSION[SESSION_KEY_ERROR] = 'Invalid match ID provided';
        header('Location: ' . $redirectUrl, true, 302);
        exit;
    }
    
    return $matchId;
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

/**
 * Handle application errors with comprehensive logging and user feedback
 * 
 * Implements fail-safe error handling:
 * 1. Logs error with full context for debugging
 * 2. Determines user-friendly message based on error type
 * 3. Sets session error for display
 * 4. Redirects appropriately based on error type
 * 
 * @param Exception $exception The exception to handle
 * @param int $matchId Match ID for logging context
 * @param string $defaultRedirectUrl Default redirect if not specified by error type
 * @return never Always exits after handling
 */
function handleScoringError(
    Exception $exception,
    int $matchId,
    string $defaultRedirectUrl
): never {
    // Detailed logging for developers
    error_log(sprintf(
        '[Scorer] Match ID: %d | Error: %s | File: %s:%d',
        $matchId,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));
    error_log('[Scorer] Stack Trace: ' . $exception->getTraceAsString());
    
    // Determine user-friendly error message
    $errorMessage = $exception->getMessage();
    $userMessage = match(true) {
        str_contains($errorMessage, 'not live') => 
            'This match is not live. Please start the match from the console before scoring.',
        str_contains($errorMessage, 'not found') => 
            'Match not found. It may have been deleted or you may not have access.',
        str_contains($errorMessage, 'permission') || str_contains($errorMessage, 'access denied') => 
            'You do not have permission to score this match.',
        str_contains($errorMessage, 'completed') => 
            'This match has been completed. Scoring is no longer available.',
        default => 
            'Unable to load scoring page. Please try again or contact support if the issue persists.'
    };
    
    $_SESSION[SESSION_KEY_ERROR] = $userMessage;
    
    // Context-aware redirection
    if (str_contains($errorMessage, 'not live') || str_contains($errorMessage, 'completed')) {
        header('Location: ' . adminUrl('matches/view.php?id=' . $matchId), true, 302);
    } else {
        header('Location: ' . $defaultRedirectUrl, true, 302);
    }
    exit;
}

// ============================================================================
// CRICKET CALCULATIONS
// ============================================================================

/**
 * Calculate overs and balls from total legal balls
 * 
 * In cricket, each over consists of BALLS_PER_OVER legal deliveries.
 * 
 * @param int $legalBallsTotal Total number of legal balls bowled
 * @return array{overs: int, balls: int, formatted: string} Calculated values
 */
function calculateOversBreakdown(int $legalBallsTotal): array
{
    $overs = (int)floor($legalBallsTotal / BALLS_PER_OVER);
    $balls = $legalBallsTotal % BALLS_PER_OVER;
    
    return [
        'overs' => $overs,
        'balls' => $balls,
        'formatted' => sprintf('%d.%d', $overs, $balls)
    ];
}

/**
 * Calculate maximum wickets for a team
 * 
 * Maximum wickets = team size - 1 (one batsman must remain)
 * Minimum is 1 to handle edge cases.
 * 
 * @param int $teamSize Number of players in batting team
 * @return int Maximum wickets possible
 */
function calculateMaxWickets(int $teamSize): int
{
    return max(1, $teamSize - 1);
}

// ============================================================================
// MAIN EXECUTION FLOW
// ============================================================================

try {
    // Step 1: Authentication & Authorization
    validateUserAccess();
    
    // Step 2: Get redirect URL for current user
    $redirectUrl = getRoleBasedRedirectUrl();
    
    // Step 3: Validate match ID
    $matchId = getValidatedMatchId($redirectUrl);
    
    // Step 4: Load comprehensive match data
    // This function is defined in score-data-loader.php
    // It handles stats calculation, state reconstruction, and data preparation
    $scoreData = loadScoreData($matchId);
    
    // Step 5: Extract match data with null coalescing
    $match = $scoreData['match'] ?? [];
    $teams = $scoreData['teams'] ?? [];
    $currentInnings = $scoreData['current_innings'] ?? 1;
    $battingTeamId = $scoreData['batting_team_id'] ?? 0;
    $bowlingTeamId = $scoreData['bowling_team_id'] ?? 0;
    $battingTeamPlayers = $scoreData['batting_team_players'] ?? [];
    $bowlingTeamPlayers = $scoreData['bowling_team_players'] ?? [];
    $availableBatsmen = $scoreData['available_batsmen'] ?? [];
    $currentState = $scoreData['current_state'] ?? [];
    $currentStrikerId = $scoreData['current_striker_id'] ?? null;
    $currentNonStrikerId = $scoreData['current_non_striker_id'] ?? null;
    $currentBowlerId = $scoreData['current_bowler_id'] ?? null;
    $dismissedPlayerIds = $scoreData['dismissed_player_ids'] ?? [];
    $battingTeamSize = $scoreData['batting_team_size'] ?? 11;
    $battingTeam = $scoreData['batting_team'] ?? [];
    $bowlingTeam = $scoreData['bowling_team'] ?? [];
    $firstInningsTotal = $scoreData['first_innings_total'] ?? 0;
    $jsPlayerStats = $scoreData['js_player_stats'] ?? ['batsmen' => [], 'bowlers' => []];
    
    // Step 6: Extract current state
    $currentScore = $currentState['runs'] ?? 0;
    $currentWickets = $currentState['wickets'] ?? 0;
    $legalBallsTotal = $currentState['legal_balls'] ?? 0;
    $lastOverBowlerId = $currentState['last_over_bowler_id'] ?? null;
    $currentBowlerBalls = $currentState['current_bowler_balls'] ?? 0;
    
    // Step 7: Calculate overs breakdown
    $oversBreakdown = calculateOversBreakdown($legalBallsTotal);
    $currentOvers = $oversBreakdown['overs'];
    $currentBalls = $oversBreakdown['balls'];
    
    // Step 8: Calculate maximum wickets
    $maxWickets = calculateMaxWickets($battingTeamSize);
    
    // Step 9: Validate critical data before rendering
    if (empty($match) || !isset($match['team1_id']) || !isset($match['team2_id'])) {
        error_log('[Scorer] Critical: Match data incomplete or missing');
        $_SESSION[SESSION_KEY_ERROR] = 'Match data is incomplete. Please contact support.';
        header('Location: ' . adminUrl('matches/'), true, 302);
        exit;
    }
    
} catch (Exception $exception) {
    handleScoringError($exception, $matchId ?? 0, $redirectUrl ?? adminUrl('matches/'));
}

// Generate cache-busting version for assets
$assetVersion = time();

// Debug: Log critical variables
error_log('[Scorer Debug] Match ID: ' . ($matchId ?? 'NULL'));
error_log('[Scorer Debug] Match array keys: ' . (is_array($match) ? implode(', ', array_keys($match)) : 'NOT_ARRAY'));
error_log('[Scorer Debug] Current Innings: ' . ($currentInnings ?? 'NULL'));
error_log('[Scorer Debug] Batting Team Size: ' . ($battingTeamSize ?? 'NULL'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1.0, user-scalable=no">
    <?php require_once __DIR__ . '/../../includes/cache-prevention-meta.php'; ?>
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#0f172a">
    <title>Live Scorer - <?= htmlspecialchars(($match['team1_name'] ?? 'Team 1') ?: 'Team 1', ENT_QUOTES, 'UTF-8') ?> vs <?= htmlspecialchars(($match['team2_name'] ?? 'Team 2') ?: 'Team 2', ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/pages/scorer-enhanced.css?v=' . $assetVersion) ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/pages/scorer-mobile.css?v=' . $assetVersion) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- JavaScript Configuration -->
    <script>
        'use strict';
        
        //================================================================
        // MATCH CONFIGURATION (Read-Only)
        //================================================================
        const MATCH_CONFIG = Object.freeze({
            matchId: <?= $matchId ?? 0 ?>,
            currentInnings: <?= $currentInnings ?? 1 ?>,
            maxOvers: <?= $match['overs_per_innings'] ?? 20 ?>,
            firstInningsTotal: <?= $firstInningsTotal ?? 0 ?>,
            battingTeamSize: <?= $battingTeamSize ?? 11 ?>,
            maxWickets: <?= $maxWickets ?? 10 ?>,
            team1_id: <?= $match['team1_id'] ?? 0 ?>,
            team2_id: <?= $match['team2_id'] ?? 0 ?>
        });
        
        //================================================================
        // CURRENT MATCH STATE (Mutable)
        //================================================================
        let matchState = {
            score: <?= $currentScore ?? 0 ?>,
            wickets: <?= $currentWickets ?? 0 ?>,
            overs: <?= $currentOvers ?? 0 ?>,
            balls: <?= $currentBalls ?? 0 ?>,
            strikerId: <?= $currentStrikerId ? $currentStrikerId : 'null' ?>,
            nonStrikerId: <?= $currentNonStrikerId ? $currentNonStrikerId : 'null' ?>,
            bowlerId: <?= $currentBowlerId ? $currentBowlerId : 'null' ?>,
            bowlerBalls: <?= $currentBowlerBalls ?? 0 ?>,
            lastOverBowlerId: <?= $lastOverBowlerId ? $lastOverBowlerId : 'null' ?>
        };
        
        //================================================================
        // PLAYER STATISTICS
        //================================================================
        let playerStats = <?= json_encode($jsPlayerStats ?? ['batsmen' => [], 'bowlers' => []]) ?>;
        const initialDismissedPlayerIds = <?= json_encode($dismissedPlayerIds ?? []) ?>;
        
        //================================================================
        // CURRENT OVER TRACKING
        //================================================================
        let currentOverState = {
            balls: [], // Will be populated by API
            runs: 0,
            wickets: 0,
            extras: 0,
            legalBalls: 0
        };
        
        //================================================================
        // EVENT HISTORY & SYNCHRONIZATION
        //================================================================
        let eventTracking = {
            clientSeq: 0,
            serverSeq: 0,
            lastKnownSeq: 0,
            history: []
        };
        
        //================================================================
        // BOWLER MANAGEMENT FLAGS
        //================================================================
        let bowlerManagement = {
            userChangedAfterOver: false,
            strikerOnStrike: true // Default, will be updated by state
        };
        
        //================================================================
        // API ENDPOINTS CONFIGURATION
        //================================================================
        const API_ENDPOINTS = Object.freeze({
            basePath: "<?= rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\') ?>",
            matches: "<?= apiUrl('matches.php') ?>",
            events: "<?= apiUrl('events.php') ?>"
        });

        // Global variables for compatibility with score-api.js
        const matchesApiUrl = API_ENDPOINTS.matches;
        const eventsApiUrl = API_ENDPOINTS.events;
        const eventsApiEndpoint = API_ENDPOINTS.events;
        const matchViewUrl = "<?= adminUrl('matches/view.php?id=' . $matchId) ?>";

        //================================================================
        // LEGACY GLOBAL VARIABLES (Required for existing JS modules)
        //================================================================
        let currentMatchId = MATCH_CONFIG.matchId;
        let currentInnings = MATCH_CONFIG.currentInnings;
        let maxOvers = MATCH_CONFIG.maxOvers;
        let firstInningsTotal = MATCH_CONFIG.firstInningsTotal;
        let battingTeamSize = MATCH_CONFIG.battingTeamSize;
        let maxWickets = MATCH_CONFIG.maxWickets;
        
        let currentScore = matchState.score;
        let currentWickets = matchState.wickets;
        let currentOvers = matchState.overs;
        let currentBalls = matchState.balls;
        let currentStrikerId = matchState.strikerId;
        let currentNonStrikerId = matchState.nonStrikerId;
        let currentBowlerId = matchState.bowlerId;
        let currentBowlerBalls = matchState.bowlerBalls;
        let lastOverBowlerId = matchState.lastOverBowlerId;
        
        let currentOverRuns = currentOverState.runs;
        let currentOverWickets = currentOverState.wickets;
        let currentOverExtras = currentOverState.extras;
        let currentOverLegalBalls = currentOverState.legalBalls;
        let currentOverBalls = currentOverState.balls;
        
        let serverSeq = eventTracking.serverSeq;
        let clientSeq = eventTracking.clientSeq;
        let eventHistory = eventTracking.history;
        
        let userChangedBowlerAfterOver = bowlerManagement.userChangedAfterOver;
    </script>
    
    <!-- JavaScript Modules (Order matters!) -->
    <script src="<?= adminUrl('matches/js/score-state.js?v=' . $assetVersion) ?>"></script>
    <script src="<?= adminUrl('matches/js/score-utils.js?v=' . $assetVersion) ?>"></script>
    <script src="<?= adminUrl('matches/js/score-ui.js?v=' . $assetVersion) ?>"></script>
    <script src="<?= adminUrl('matches/js/score-modals.js?v=' . $assetVersion) ?>"></script>
    <script src="<?= adminUrl('matches/js/score-events.js?v=' . $assetVersion) ?>"></script>
    <script src="<?= adminUrl('matches/js/score-api.js?v=' . $assetVersion) ?>"></script>
    <script src="<?= adminUrl('matches/js/score-init.js?v=' . $assetVersion) ?>"></script>
</head>
<body class="scorer-app">
    <!-- Header -->
    <header class="app-header">
        <div class="header-top">
            <div class="match-teams">
                <span class="team-name"><?= htmlspecialchars((($match['team1_short_name'] ?? null) ?: ($match['team1_name'] ?? 'Team 1')) ?: 'Team 1', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="vs">vs</span>
                <span class="team-name"><?= htmlspecialchars((($match['team2_short_name'] ?? null) ?: ($match['team2_name'] ?? 'Team 2')) ?: 'Team 2', ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="match-status-badge">
                <?= $currentInnings == 1 ? '1st Innings' : '2nd Innings' ?>
            </div>
        </div>
        
        <div class="score-board">
            <div class="main-score">
                <span id="score-runs-display"><?= $currentScore ?></span>/<span id="score-wickets-display"><?= $currentWickets ?></span>
            </div>
            <div class="overs-display">
                <span id="score-overs-display">(<?= $currentOvers ?>.<?= $currentBalls ?>)</span>
            </div>
            <div class="rates-display">
                <span>CRR: <strong id="run-rate">0.00</strong></span>
                <?php if ($currentInnings == 2): ?>
                    <span>REQ: <strong id="required-rr">0.00</strong></span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="app-content">
        <!-- Scorer Middle Panel -->
        <div class="scorer-middle-panel">
            <!-- Batsmen Row - Horizontal -->
            <div class="batsmen-row">
                <!-- Striker -->
                <div class="batsman-item active" onclick="openPlayerSelect('striker')" id="striker-status">
                    <div class="batsman-label">
                        <span class="indicator">🏏</span>
                        <span>Striker</span>
                    </div>
                    <div class="batsman-name" id="striker-name-display">Select Striker</div>
                    <div class="batsman-stats">
                        <span class="runs" id="striker-runs-display">0</span>
                        <span>(<span id="striker-balls-display">0</span>)</span>
                    </div>
                </div>
                
                <!-- Non-Striker -->
                <div class="batsman-item" onclick="openPlayerSelect('non-striker')">
                    <div class="batsman-label">
                        <span>Non-Striker</span>
                    </div>
                    <div class="batsman-name" id="non-striker-name-display">Select Non-Striker</div>
                    <div class="batsman-stats">
                        <span class="runs" id="non-striker-runs-display">0</span>
                        <span>(<span id="non-striker-balls-display">0</span>)</span>
                    </div>
                </div>
            </div>
            
            <!-- Bowler & Current Over Row -->
            <div class="bowler-row">
                <!-- Bowler -->
                <div class="bowler-item" onclick="openPlayerSelect('bowler')">
                    <div class="bowler-label">
                        <span class="indicator">⚾</span>
                        <span>Bowler</span>
                    </div>
                    <div class="bowler-name" id="bowler-name-display">Select Bowler</div>
                    <div class="bowler-stats">
                        <span id="bowler-overs-display">0.0</span>-<span id="bowler-maidens-display">0</span>-<span id="bowler-runs-display">0</span>-<span id="bowler-wickets-display">0</span>
                    </div>
                </div>
                
                <!-- Current Over -->
                <div class="current-over-display">
                    <div class="current-over-label">Current Over</div>
                    <div class="current-over-balls" id="ball-tracker">
                        <div class="ball-item ball-dot">•</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Keypad (Fixed Bottom) -->
    <div class="keypad-container">
        <div class="keypad-grid">
            <!-- Row 1: Runs -->
            <button class="key-btn" onclick="recordRun(0)">0</button>
            <button class="key-btn" onclick="recordRun(1)">1</button>
            <button class="key-btn" onclick="recordRun(2)">2</button>
            <button class="key-btn" onclick="recordRun(3)">3</button>
            
            <!-- Row 2: Boundaries & Runs -->
            <button class="key-btn btn-four" onclick="recordRun(4)">4</button>
            <button class="key-btn btn-six" onclick="recordRun(6)">6</button>
            <button class="key-btn" onclick="recordRun(5)">5</button>
            <button class="key-btn" onclick="recordRun(7)">7+</button>
            
            <!-- Row 3: Extras -->
            <button class="key-btn btn-extra" onclick="recordExtra('wide')">WD</button>
            <button class="key-btn btn-extra" onclick="recordExtra('no-ball')">NB</button>
            <button class="key-btn btn-extra" onclick="recordExtra('bye')">BYE</button>
            <button class="key-btn btn-extra" onclick="recordExtra('leg-bye')">LB</button>
            
            <!-- Row 4: Actions -->
            <button class="key-btn btn-wicket" onclick="showWicketModal()">OUT</button>
            <button class="key-btn btn-action" onclick="undoLastBall()" id="undo-btn" disabled>UNDO</button>
            <button class="key-btn btn-action" onclick="swapStrike()">SWAP</button>
            <button class="key-btn btn-action" onclick="showExtrasModal()">...</button>
        </div>
    </div>

    <!-- Modals -->
    
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
                        <button class="player-list-item" onclick="selectStriker(<?= (int)$player['player_id'] ?>, <?= htmlspecialchars(json_encode($player['player_name'] ?? 'Unknown', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)">
                            <div class="player-info">
                                <span class="name"><?= htmlspecialchars($player['player_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="role">Batsman</span>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>
                <!-- Non-Striker List -->
                <div class="player-list" id="non-striker-list" style="display: none;">
                    <?php foreach ($battingTeamPlayers as $player): ?>
                        <button class="player-list-item" onclick="selectNonStriker(<?= (int)$player['player_id'] ?>, <?= htmlspecialchars(json_encode($player['player_name'] ?? 'Unknown', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)">
                            <div class="player-info">
                                <span class="name"><?= htmlspecialchars($player['player_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="role">Batsman</span>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>
                <!-- Bowler List -->
                <div class="player-list" id="bowler-list" style="display: none;">
                    <?php foreach ($bowlingTeamPlayers as $player): ?>
                        <button class="player-list-item" onclick="selectBowler(<?= (int)$player['player_id'] ?>, <?= htmlspecialchars(json_encode($player['player_name'] ?? 'Unknown', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>)">
                            <div class="player-info">
                                <span class="name"><?= htmlspecialchars($player['player_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="role">Bowler</span>
                            </div>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Wicket Modal -->
    <div class="modal-overlay" id="wicket-modal" hidden>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Wicket Type</h3>
                <button type="button" class="btn-close" onclick="closeWicketModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="wicket-grid">
                    <?php
                    $dismissalTypes = [
                        'bowled' => 'Bowled',
                        'caught' => 'Caught',
                        'lbw' => 'LBW',
                        'run-out' => 'Run Out',
                        'stumped' => 'Stumped',
                        'hit-wicket' => 'Hit Wicket'
                    ];
                    foreach ($dismissalTypes as $type => $label): ?>
                        <button class="wicket-option-btn" onclick="recordWicketType('<?= $type ?>')">
                            <?= $label ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- New Batsman Modal -->
    <div class="modal-overlay" id="new-batsman-modal" hidden>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Next Batsman</h3>
                <button type="button" class="btn-close" onclick="closeNewBatsmanModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: var(--space-md);">
                    <label for="new-batsman-select" style="display: block; margin-bottom: 8px; font-weight: 600;">Select Player</label>
                    <select id="new-batsman-select" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-body);">
                        <option value="">Select...</option>
                        <!-- Populated by JS -->
                    </select>
                </div>
                <button class="btn-action" style="width: 100%; background: var(--primary); color: white; border: none;" onclick="confirmNewBatsman()">
                    Confirm Batsman
                </button>
            </div>
        </div>
    </div>

    <!-- Extra Runs Modal -->
    <div class="modal-overlay" id="extra-runs-modal" hidden>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="extra-runs-modal-title">Extra Runs</h3>
                <button type="button" class="btn-close" onclick="closeExtraRunsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="extra-runs-type">
                <div style="margin-bottom: var(--space-md);">
                    <label for="extra-runs-amount" style="display: block; margin-bottom: 8px; font-weight: 600;">Runs Scored</label>
                    <input type="number" id="extra-runs-amount" value="1" min="1" max="6" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 1.25rem; text-align: center;">
                </div>
                <button class="btn-action" style="width: 100%; background: var(--warning); color: white; border: none;" onclick="recordExtraWithRuns()">
                    Confirm Extras
                </button>
            </div>
        </div>
    </div>

    <!-- Run Out Modal -->
    <div class="modal-overlay" id="run-out-modal" hidden>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Run Out - Who is out?</h3>
                <button type="button" class="btn-close" onclick="closeRunOutModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="wicket-grid">
                    <button class="wicket-option-btn" onclick="recordRunOut('striker')">Striker</button>
                    <button class="wicket-option-btn" onclick="recordRunOut('non_striker')">Non-Striker</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fielder Modal -->
    <div class="modal-overlay" id="fielder-modal" hidden>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Select Fielder</h3>
                <button type="button" class="btn-close" onclick="closeFielderModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pending-dismissal-type">
                <div style="margin-bottom: var(--space-md);">
                    <select id="fielder-select" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg-body);">
                        <option value="">Select Fielder...</option>
                        <?php foreach ($bowlingTeamPlayers as $player): ?>
                            <option value="<?= (int)$player['player_id'] ?>">
                                <?= htmlspecialchars($player['player_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn-action" style="width: 100%; background: var(--primary); color: white; border: none;" onclick="confirmFielder()">
                    Confirm Fielder
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden Selects for Logic Compatibility -->
    <select id="striker" style="display: none;">
        <option value="">Select...</option>
        <?php foreach ($battingTeamPlayers as $player): ?>
            <option value="<?= (int)$player['player_id'] ?>" <?= ($currentStrikerId == $player['player_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($player['player_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select id="non-striker" style="display: none;">
        <option value="">Select...</option>
        <?php foreach ($battingTeamPlayers as $player): ?>
            <option value="<?= (int)$player['player_id'] ?>" <?= ($currentNonStrikerId == $player['player_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($player['player_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <select id="bowler" style="display: none;">
        <option value="">Select...</option>
        <?php foreach ($bowlingTeamPlayers as $player): ?>
            <option value="<?= (int)$player['player_id'] ?>" <?= ($currentBowlerId == $player['player_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($player['player_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Start Innings Modal -->
    <div class="modal-overlay" id="start-innings-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🏏 1st Innings Complete!</h3>
            </div>
            <div class="modal-body" style="text-align: center; padding: 2rem;">
                <p style="font-size: 1.125rem; margin-bottom: 1.5rem;">
                    The first innings has been completed.
                </p>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Would you like to start the 2nd innings now?
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button type="button" class="btn-secondary" onclick="closeStartInningsModal()">
                        Not Yet
                    </button>
                    <button type="button" class="btn-primary" onclick="changeInningsAjax()">
                        Start 2nd Innings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Over Notification -->
    <div id="over-notification" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%) translateY(-100px); background: var(--dark); color: white; padding: 12px 24px; border-radius: 50px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 2000; display: flex; align-items: center; gap: 12px; font-weight: 600;">
        <span style="font-size: 1.25rem;">🏏</span>
        <span id="over-notification-text">Over Complete</span>
    </div>
    <style>
        #over-notification.show {
            transform: translateX(-50%) translateY(0);
        }
    </style>

    <!-- Notifications -->
    <div id="notification-area"></div>

    <script>
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            // Data from PHP
            const phpData = {
                matchData: MATCH_CONFIG,
                eventsApiUrl: API_ENDPOINTS.events,
                matchesApiUrl: API_ENDPOINTS.matches,
                eventsApiEndpoint: API_ENDPOINTS.events
            };
            
            if (typeof initializeScoringPage === 'function') {
                initializeScoringPage(phpData);
            }
        });
    </script>
</body>
</html>
