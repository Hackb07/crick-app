<?php
/**
 * Scorer Controller (Clean Version)
 * 
 * Handles the scoring interface logic, data loading, and view rendering.
 * Replaces the monolithic scorer.php with a clean MVC structure.
 * 
 * @package CricApp
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/layout/admin-layout.php';
require_once __DIR__ . '/includes/score-data-loader.php';

// 1. Authentication
requireLogin();
$userRole = getSession('role');
if (!in_array($userRole, ['admin', 'scorer'])) {
    header('Location: ' . adminUrl('index.php'));
    exit;
}

// 2. Input Validation
$matchId = (int)getQuery('id', 0);
if ($matchId <= 0) {
    header('Location: ' . adminUrl('matches/'));
    exit;
}

// 3. Data Loading
try {
    // Load all match data using the dedicated loader service
    $scoreData = loadScoreData($matchId);
    
    // Extract variables for the view (Map snake_case to variables)
    $match = $scoreData['match'];
    $currentInnings = $scoreData['current_innings']; // Changed from currentInnings
    $currentScore = $scoreData['current_state']['runs'] ?? 0;
    $currentWickets = $scoreData['current_state']['wickets'] ?? 0;
    $currentOvers = (int)($scoreData['current_state']['overs'] ?? 0);
    $currentBalls = $scoreData['current_state']['balls'] ?? 0;
    
    // Extract current player IDs for View
    $currentStrikerId = $scoreData['current_striker_id'];
    $currentNonStrikerId = $scoreData['current_non_striker_id'];
    $currentBowlerId = $scoreData['current_bowler_id'];
    
    // Calculate JSON for JS
    $jsonPlayerStats = json_encode($scoreData['js_player_stats'] ?? ['batsmen' => [], 'bowlers' => []], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $jsonDismissedPlayers = json_encode($scoreData['dismissed_player_ids'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    // Prepare JS Configuration (Bridge to Legacy JS)
    // We map PHP data to the global variables expected by existing JS modules
    $jsConfig = [
        'matchId' => $matchId,
        'currentInnings' => $currentInnings,
        'maxOvers' => $match['overs_per_innings'] ?? 20,
        'firstInningsTotal' => $scoreData['first_innings_total'] ?? 0,
        'battingTeamSize' => $scoreData['batting_team_size'] ?? 11,
        'maxWickets' => $scoreData['max_wickets'] ?? 10,
        'team1_id' => $match['team1_id'],
        'team2_id' => $match['team2_id'],
        'api' => [
            'base' => rtrim(dirname($_SERVER['SCRIPT_NAME'], 3), '/\\'),
            'matches' => apiUrl('matches.php'),
            'events' => apiUrl('events.php')
        ],
        'state' => [
            'score' => $currentScore,
            'wickets' => $currentWickets,
            'overs' => $currentOvers,
            'balls' => $currentBalls,
            'strikerId' => $scoreData['current_striker_id'],
            'nonStrikerId' => $scoreData['current_non_striker_id'],
            'bowlerId' => $scoreData['current_bowler_id'],
            'bowlerBalls' => $scoreData['current_state']['bowler_balls'] ?? 0,
            'lastOverBowlerId' => $scoreData['current_state']['last_over_bowler_id'] ?? null
        ],
        'players' => $scoreData['js_player_stats'] ?? ['batsmen' => [], 'bowlers' => []],
        'dismissed' => $scoreData['dismissed_player_ids'] ?? []
    ];

} catch (Exception $e) {
    error_log("Scorer Load Error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to load scorer: " . $e->getMessage();
    header('Location: ' . adminUrl('matches/view.php?id=' . $matchId));
    exit;
}

// 4. Render View
    // Prepare JS values to avoid HEREDOC syntax errors
    $strikerId = $jsConfig['state']['strikerId'] ?? 'null';
    $nonStrikerId = $jsConfig['state']['nonStrikerId'] ?? 'null';
    $bowlerId = $jsConfig['state']['bowlerId'] ?? 'null';
    $lastOverBowlerId = $jsConfig['state']['lastOverBowlerId'] ?? 'null';

    // We pass a special 'before_body_end' script to initialize the globals
$initScript = <<<JS
<script>
    'use strict';
    // Initialize Globals for Legacy Modules
    const MATCH_CONFIG = Object.freeze({
        matchId: {$jsConfig['matchId']},
        currentInnings: {$jsConfig['currentInnings']},
        maxOvers: {$jsConfig['maxOvers']},
        firstInningsTotal: {$jsConfig['firstInningsTotal']},
        battingTeamSize: {$jsConfig['battingTeamSize']},
        maxWickets: {$jsConfig['maxWickets']},
        team1_id: {$jsConfig['team1_id']},
        team2_id: {$jsConfig['team2_id']}
    });

    let matchState = {
        score: {$jsConfig['state']['score']},
        wickets: {$jsConfig['state']['wickets']},
        overs: {$jsConfig['state']['overs']},
        balls: {$jsConfig['state']['balls']},
        strikerId: {$strikerId},
        nonStrikerId: {$nonStrikerId},
        bowlerId: {$bowlerId},
        bowlerBalls: {$jsConfig['state']['bowlerBalls']},
        lastOverBowlerId: {$lastOverBowlerId}
    };


    let playerStats = {$jsonPlayerStats};
    const initialDismissedPlayerIds = {$jsonDismissedPlayers};
    
    // API Config
    const API_ENDPOINTS = Object.freeze({
        basePath: "{$jsConfig['api']['base']}",
        matches: "{$jsConfig['api']['matches']}",
        events: "{$jsConfig['api']['events']}"
    });
    
    // Legacy Global Maps
    const matchesApiUrl = API_ENDPOINTS.matches;
    const eventsApiUrl = API_ENDPOINTS.events;
    const eventsApiEndpoint = API_ENDPOINTS.events;
    
    // Runtime Globals
    let currentMatchId = MATCH_CONFIG.matchId;
    let currentInnings = MATCH_CONFIG.currentInnings;
    let currentScore = matchState.score;
    let currentWickets = matchState.wickets;
    let currentOvers = matchState.overs;
    let currentBalls = matchState.balls;
    let currentStrikerId = matchState.strikerId;
    let currentNonStrikerId = matchState.nonStrikerId;
    let currentBowlerId = matchState.bowlerId;
    
    // Add missing globals that JS files expect
    let maxOvers = MATCH_CONFIG.maxOvers;
    let firstInningsTotal = MATCH_CONFIG.firstInningsTotal;
    let battingTeamSize = MATCH_CONFIG.battingTeamSize;
    let maxWickets = MATCH_CONFIG.maxWickets;
    
    // Bowler management variables
    let currentBowlerBalls = matchState.bowlerBalls || 0;
    let lastOverBowlerId = matchState.lastOverBowlerId || null;
    let userChangedBowlerAfterOver = false;
    
    // Current over tracking
    let currentOverBalls = [];
    let currentOverRuns = 0;
    let currentOverLegalBalls = 0;
    let currentOverExtras = 0;
    
    // Event tracking
    let currentOverState = { balls: [], runs: 0, wickets: 0, extras: 0, legalBalls: 0 };
    let eventTracking = { clientSeq: 0, serverSeq: 0, lastKnownSeq: 0, history: [] };
    let bowlerManagement = { userChangedAfterOver: false, strikerOnStrike: true };
    let eventHistory = []; // For undo functionality
    let clientSeq = 0; // Client sequence number
    
    // Event Queue Init
    let offlineQueue = JSON.parse(localStorage.getItem('score_offline_queue') || '[]');
    let serverSeq = eventTracking.serverSeq; // Will be sync'd
    
    // Debug: Verify all variables are set
    console.log('=== Scorer Globals Initialized ===');
    console.log('maxOvers:', maxOvers);
    console.log('currentScore:', currentScore);
    console.log('currentWickets:', currentWickets);
    console.log('lastOverBowlerId:', lastOverBowlerId);
    console.log('offlineQueue:', offlineQueue);
    console.log('MATCH_CONFIG:', MATCH_CONFIG);
</script>
JS;

    // Prepare Data for View
    $viewData = array_merge($scoreData, [
        'currentInnings' => $currentInnings,
        'currentScore' => $currentScore,
        'currentWickets' => $currentWickets,
        'currentOvers' => $currentOvers,
        'currentBalls' => $currentBalls,
        'battingTeam' => $scoreData['batting_team'],
        'bowlingTeam' => $scoreData['bowling_team'],
        'battingTeamPlayers' => $scoreData['batting_team_players'],
        'bowlingTeamPlayers' => $scoreData['bowling_team_players'],
        'jsonPlayerStats' => $jsonPlayerStats,
        'jsonDismissedPlayers' => $jsonDismissedPlayers,
        // Fix for View Variable Naming Mismatch (View expects camelCase)
        'currentStrikerId' => $scoreData['current_striker_id'],
        'currentNonStrikerId' => $scoreData['current_non_striker_id'],
        'currentBowlerId' => $scoreData['current_bowler_id']
    ]);

renderAdminLayout(
    'Live Scorer',
    'scorer', // maps to views/admin/scorer.php
    $viewData, // passed as variables to view
    [
        'sidebar' => false,  // Scorer has its own custom UI
        'header' => false,   // Scorer has its own header
        'bodyClass' => 'scorer-app',
        'activeMenu' => 'matches',
        'headerActions' => [
            [
                'url' => adminUrl('matches/console.php?id=' . $matchId),
                'label' => '←',
                'class' => 'btn-icon',
                'aria-label' => 'Back to Console'
            ]
        ],
        'additionalCss' => [
            'css/pages/scorer-clean.css' // The new consolidated CSS
        ],
        'additionalJs' => [
            'js/admin/matches/score-state.js?v=' . time(),    // 1. State management (no dependencies)
            'js/admin/matches/score-utils.js?v=' . time(),    // 2. Utility functions (no dependencies)
            'js/admin/matches/score-api.js?v=' . time(),      // 3. API functions (defines saveEvent, loadMatchState, etc.)
            'js/admin/matches/score-ui.js?v=' . time(),       // 4. UI updates (uses utils)
            'js/admin/matches/score-modals.js?v=' . time(),   // 5. Modal handling (uses UI)
            'js/admin/matches/score-events.js?v=' . time(),   // 6. Event handling (uses saveEvent from API)
            'js/admin/matches/score-init.js?v=' . time()      // 7. Initialization (uses everything)
        ],
        'rawHeadScript' => $initScript // Inject globals before JS files load
    ]
);
