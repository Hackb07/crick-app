<?php
/**
 * Live Match View - Premium Design
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/utils.php';

$matchId = (int)getQuery('id', 0);
if (!$matchId) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

// Load Match Stats Service
require_once __DIR__ . '/classes/MatchStatsService.php';
$statsService = new MatchStatsService();
$matchData = $statsService->getLiveMatchData($matchId);

if (!$matchData) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

// Extract variables
$match = $matchData['match'];
$events = $matchData['events'];
$inningsData = $matchData['inningsData'];
$partnership = $matchData['partnership'];
$recentBalls = $matchData['recentBalls'];
$playerLookup = $matchData['playerLookup'];

// Determine current innings
$currentInnings = 1;
if ($inningsData[2]['balls_legal'] > 0 || $inningsData[2]['total_runs'] > 0) {
    $currentInnings = 2;
}

$currentScoreData = $inningsData[$currentInnings];

// Current Batters
$currentStrikerId = $match['current_striker_id'] ?? 0;
$currentNonStrikerId = $match['current_non_striker_id'] ?? 0;

$currentStriker = $currentScoreData['batting'][$currentStrikerId] ?? ['runs' => 0, 'balls' => 0, '4s' => 0, '6s' => 0];
$currentStrikerName = $playerLookup[$currentStrikerId]['name'] ?? 'Not Set';

$currentNonStriker = $currentScoreData['batting'][$currentNonStrikerId] ?? ['runs' => 0, 'balls' => 0, '4s' => 0, '6s' => 0];
$currentNonStrikerName = $playerLookup[$currentNonStrikerId]['name'] ?? 'Not Set';

// Current Bowler
$currentBowlerId = $match['current_bowler_id'] ?? 0;
$currentBowler = $currentScoreData['bowling'][$currentBowlerId] ?? ['overs' => 0, 'maidens' => 0, 'runs' => 0, 'wickets' => 0];
$currentBowlerName = $playerLookup[$currentBowlerId]['name'] ?? 'Not Set';

// Run Rate
$totalBalls = $currentScoreData['balls_legal'];
$totalRuns = $currentScoreData['total_runs'];
$runRate = $totalBalls > 0 ? round(($totalRuns / $totalBalls) * 6, 2) : 0.00;

// Partnership
$partnershipRuns = $partnership['runs'];
$partnershipBalls = $partnership['balls'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1e7e34">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Live Match - <?= e($match['team1_name'] ?: 'Team 1') ?> vs <?= e($match['team2_name'] ?: 'Team 2') ?> - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/pwa-mobile.css') ?>">
    <style>
        .live-score-container {
            margin-bottom: var(--spacing-xl);
        }
        .current-batsmen {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-md);
            margin-top: var(--spacing-lg);
        }
        .batsman-card {
            background: var(--bg-secondary);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
        }
        .batsman-name {
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
        }
        .batsman-score {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--cricket-green);
        }
        .recent-balls {
            display: flex;
            gap: var(--spacing-sm);
            flex-wrap: wrap;
            margin-top: var(--spacing-md);
        }
        .ball-badge {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .ball-0 { background: #e9ecef; color: var(--text-primary); }
        .ball-1 { background: #cfe2ff; color: #084298; }
        .ball-2 { background: #b6d4fe; color: #052c65; }
        .ball-3 { background: #9ec5fe; color: #031633; }
        .ball-4 { background: #ffc107; color: #664d03; }
        .ball-6 { background: #fd7e14; color: white; }
        .ball-W { background: #dc3545; color: white; }
        .ball-Wd { background: #6c757d; color: white; }
        .ball-Nb { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="app-shell">
        <!-- Fixed Header -->
        <header class="app-header">
            <div class="app-header-content">
                <a href="<?= publicUrl('index.php') ?>" class="app-header-logo">🏏 CricApp</a>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="app-content">
            <div class="container">
        <!-- Match Header -->
        <div class="card" style="margin-bottom: var(--spacing-lg);">
            <div style="text-align: center;">
                <h1 style="margin-bottom: var(--spacing-sm);"><?= e($match['team1_name'] ?: 'Team 1') ?> vs <?= e($match['team2_name'] ?: 'Team 2') ?></h1>
                <div style="display: flex; justify-content: center; gap: var(--spacing-md); flex-wrap: wrap; margin-top: var(--spacing-md);">
                    <span class="badge badge-live">LIVE</span>
                    <span>📍 <?= e($match['venue'] ?: 'Venue TBD') ?></span>
                    <span>📅 <?= formatDate($match['match_date'], 'M d, Y h:i A') ?></span>
                </div>
            </div>
        </div>

        <!-- Live Score -->
        <div class="live-score-container">
            <div class="live-score">
                <div class="live-score-title">Current Score</div>
                <div class="live-score-value" id="live-score-main">
                    <?= ($currentScoreData['runs'] ?? 0) ?>/<?= ($currentScoreData['wickets'] ?? 0) ?>
                </div>
                <div class="live-score-overs" id="live-score-overs">
                    <?= number_format($currentScoreData['overs'] ?? 0, 1) ?> Overs
                </div>
            </div>
        </div>

        <!-- Current Batsmen -->
        <div class="card">
            <h2 class="card-title">Current Batsmen</h2>
            <div class="current-batsmen" id="current-batsmen">
                <div class="batsman-card">
                    <div class="batsman-name" id="striker-name"><?= e($currentStrikerName) ?></div>
                    <div class="batsman-score" id="striker-score">
                        <?= $currentStrikerRuns ?> (<?= $currentStrikerBalls ?>)
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: var(--spacing-xs);" id="striker-sr">
                        SR: <?= $currentStrikerBalls > 0 ? number_format(($currentStrikerRuns / $currentStrikerBalls) * 100, 2) : '0.00' ?>
                    </div>
                </div>
                <div class="batsman-card">
                    <div class="batsman-name" id="non-striker-name"><?= e($currentNonStrikerName) ?></div>
                    <div class="batsman-score" id="non-striker-score">
                        <?= $currentNonStrikerRuns ?> (<?= $currentNonStrikerBalls ?>)
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: var(--spacing-xs);" id="non-striker-sr">
                        SR: <?= $currentNonStrikerBalls > 0 ? number_format(($currentNonStrikerRuns / $currentNonStrikerBalls) * 100, 2) : '0.00' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Balls -->
        <div class="card">
            <h2 class="card-title">Recent Balls</h2>
            <div class="recent-balls" id="recent-balls">
                <?php if (empty($recentBalls)): ?>
                    <div style="color: var(--text-secondary);">No balls recorded yet</div>
                <?php else: ?>
                    <?php foreach ($recentBalls as $ball): ?>
                        <?php
                        // Map ball value to CSS class (handle both numeric and string values)
                        $ballClass = is_numeric($ball) ? (string)$ball : $ball;
                        ?>
                        <div class="ball-badge ball-<?= htmlspecialchars($ballClass, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars((string)$ball, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Match Info -->
        <div class="card">
            <h2 class="card-title">Match Information</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-md);">
                <div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">Run Rate</div>
                    <div style="font-size: 1.25rem; font-weight: 600;" id="run-rate"><?= $runRate ?></div>
                </div>
                <?php if ($currentInnings == 2 && !empty($inningsData[1]['total_runs'])): ?>
                    <?php
                    $target = (int)($inningsData[1]['total_runs'] ?? 0) + 1;
                    $remainingRuns = max(0, $target - (int)($currentScoreData['runs'] ?? 0));
                    $remainingOvers = max(0, (float)($match['overs_per_innings'] ?? 20) - (float)($currentScoreData['overs'] ?? 0));
                    // Defensive: prevent division by zero
                    $requiredRR = $remainingOvers > 0 ? round($remainingRuns / $remainingOvers, 2) : 0;
                    ?>
                    <div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary);">Target</div>
                        <div style="font-size: 1.25rem; font-weight: 600;" id="target"><?= $target ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary);">Required Run Rate</div>
                        <div style="font-size: 1.25rem; font-weight: 600;" id="required-rr"><?= $requiredRR ?></div>
                    </div>
                <?php endif; ?>
                <div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">Partnership</div>
                    <div style="font-size: 1.25rem; font-weight: 600;" id="partnership">
                        <?= $partnershipRuns ?> (<?= $partnershipBalls ?>)
                    </div>
                    <?php if ($currentStrikerName !== 'Not Set' && $currentNonStrikerName !== 'Not Set'): ?>
                        <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: var(--spacing-xs);">
                            <?= e($currentStrikerName) ?> & <?= e($currentNonStrikerName) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- View Full Scorecard -->
        <div style="text-align: center; margin: var(--spacing-xl) 0;">
            <a href="<?= publicUrl('match-view.php?id=' . $matchId) ?>" class="btn btn-primary btn-lg">View Full Scorecard</a>
        </div>
            </div>
        </main>

        <!-- Fixed Bottom Navigation -->
        <nav class="app-bottom-nav">
            <a href="<?= publicUrl('index.php') ?>" class="app-bottom-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                <span>Home</span>
            </a>
            <a href="<?= publicUrl('matches.php') ?>" class="app-bottom-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                <span>Matches</span>
            </a>
            <a href="<?= publicUrl('live.php') ?>" class="app-bottom-nav-item active">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                <span>Live</span>
            </a>
            <a href="<?= publicUrl('leaderboard.php') ?>" class="app-bottom-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                <span>Leaderboard</span>
            </a>
        </nav>
    </div>

    <!-- Auto-refresh script with AJAX polling -->
    <script>
        (function() {
            'use strict';
            
            // Configuration
            const CONFIG = {
                INITIAL_POLL_INTERVAL: 5000,      // 5 seconds
                MAX_POLL_INTERVAL: 30000,         // 30 seconds max
                BACKOFF_MULTIPLIER: 1.5,          // Exponential backoff
                FETCH_TIMEOUT: 10000,             // 10 second timeout
                MAX_RETRIES: 3,
                CORRELATION_ID: 'live-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9)
            };
            
            // State management
            let lastEventSeq = <?= !empty($events) && is_array($events) ? max(array_column($events, 'assigned_server_seq')) : 0 ?>;
            let refreshInterval;
            let currentPollInterval = CONFIG.INITIAL_POLL_INTERVAL;
            let consecutiveErrors = 0;
            let isPolling = false; // Prevent race conditions
            let pendingRequests = new Set();
            
            /**
             * Structured error logging with correlation ID
             */
            function logError(context, error, details = {}) {
                const logData = {
                    correlationId: CONFIG.CORRELATION_ID,
                    context: context,
                    error: error instanceof Error ? error.message : String(error),
                    timestamp: new Date().toISOString(),
                    url: window.location.href,
                    ...details
                };
                console.error('[LiveMatch]', JSON.stringify(logData));
                
                // Optional: Send to error tracking service
                // if (window.errorTracker) {
                //     window.errorTracker.captureException(error, { extra: logData });
                // }
            }
            
            /**
             * Fetch with timeout and retry logic (Rule 6: Defensive Programming)
             */
            function fetchWithTimeout(url, options = {}, retries = CONFIG.MAX_RETRIES) {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), CONFIG.FETCH_TIMEOUT);
                
                const requestId = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                pendingRequests.add(requestId);
                
                return fetch(url, {
                    ...options,
                    signal: controller.signal
                })
                .then(response => {
                    clearTimeout(timeoutId);
                    pendingRequests.delete(requestId);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    clearTimeout(timeoutId);
                    pendingRequests.delete(requestId);
                    
                    if (error.name === 'AbortError') {
                        error = new Error('Request timeout after ' + CONFIG.FETCH_TIMEOUT + 'ms');
                    }
                    
                    if (retries > 0 && !error.message.includes('timeout')) {
                        logError('fetchWithTimeout', error, { url, retriesLeft: retries - 1 });
                        // Exponential backoff retry
                        return new Promise(resolve => {
                            setTimeout(() => {
                                resolve(fetchWithTimeout(url, options, retries - 1));
                            }, 1000 * (CONFIG.MAX_RETRIES - retries + 1));
                        });
                    }
                    
                    throw error;
                });
            }
            
            /**
             * Safe property access with null checks (Rule 6: Defensive Programming)
             */
            function safeGet(obj, path, defaultValue = null) {
                const keys = path.split('.');
                let current = obj;
                for (const key of keys) {
                    if (current == null || typeof current !== 'object') {
                        return defaultValue;
                    }
                    current = current[key];
                }
                return current != null ? current : defaultValue;
            }
            
            /**
             * Safe division with zero check (Rule 6: Defensive Programming)
             */
            function safeDivide(numerator, denominator, defaultValue = 0) {
                if (denominator == null || denominator === 0 || isNaN(denominator)) {
                    return defaultValue;
                }
                const result = numerator / denominator;
                return isNaN(result) ? defaultValue : result;
            }
            
            /**
             * Update live score display (Rule 6: Defensive Programming, Rule 30: Observability)
             */
            function updateLiveScore() {
                // Prevent concurrent executions (Rule 34: Race Conditions)
                if (isPolling) {
                    return;
                }
                isPolling = true;
                
                const matchUrl = '<?= apiUrl('matches.php') ?>/<?= $matchId ?>?include_scores=true';
                const eventsUrl = '<?= apiUrl('events.php') ?>?path=/matches/<?= $matchId ?>/events&from_seq=' + lastEventSeq;
                
                // Fetch match data with timeout and retry
                fetchWithTimeout(matchUrl)
                    .then(result => {
                        // Validate response structure (Rule 6: Defensive Programming)
                        if (!result || typeof result !== 'object') {
                            throw new Error('Invalid response format');
                        }
                        
                        if (result.success && result.data) {
                            const matchData = safeGet(result, 'data.match', {});
                            const scoreData = safeGet(result, 'data.score', null);
                            
                            // Update match state badge
                            if (safeGet(matchData, 'state') === 'live') {
                                // Update score display with null checks
                                if (scoreData) {
                                    const currentInnings = safeGet(matchData, 'current_innings', 1);
                                    const inningsData = currentInnings === 1 
                                        ? safeGet(scoreData, 'innings1', {})
                                        : safeGet(scoreData, 'innings2', {});
                                    
                                    // Update main score
                                    const scoreMain = document.getElementById('live-score-main');
                                    const scoreOvers = document.getElementById('live-score-overs');
                                    if (scoreMain && inningsData.runs != null && inningsData.wickets != null) {
                                        scoreMain.textContent = inningsData.runs + '/' + inningsData.wickets;
                                    }
                                    if (scoreOvers && inningsData.overs != null) {
                                        scoreOvers.textContent = parseFloat(inningsData.overs).toFixed(1) + ' Overs';
                                    }
                                    
                                    // Update run rate (defensive: prevent division by zero)
                                    const totalBalls = safeGet(inningsData, 'balls', 0);
                                    const totalRuns = safeGet(inningsData, 'runs', 0);
                                    const runRate = (safeDivide(totalRuns, totalBalls, 0) * 6).toFixed(2);
                                    const runRateEl = document.getElementById('run-rate');
                                    if (runRateEl) {
                                        runRateEl.textContent = runRate;
                                    }
                                }
                                
                                consecutiveErrors = 0;
                                currentPollInterval = CONFIG.INITIAL_POLL_INTERVAL; // Reset on success
                            } else if (safeGet(matchData, 'state') === 'completed') {
                                // Match completed - stop polling
                                clearInterval(refreshInterval);
                                refreshInterval = null;
                                // Reload once to show final state
                                setTimeout(() => location.reload(), 2000);
                            }
                        }
                    })
                    .catch(error => {
                        consecutiveErrors++;
                        logError('updateLiveScore', error, { 
                            matchId: <?= $matchId ?>,
                            consecutiveErrors 
                        });
                        
                        // Exponential backoff on errors (Rule 28: Cost Control)
                        currentPollInterval = Math.min(
                            currentPollInterval * CONFIG.BACKOFF_MULTIPLIER,
                            CONFIG.MAX_POLL_INTERVAL
                        );
                        
                        // Restart polling with new interval
                        if (refreshInterval) {
                            clearInterval(refreshInterval);
                        }
                        const matchState = <?= json_encode($match['state'] ?? 'unknown') ?>;
                        if (matchState === 'live') {
                            refreshInterval = setInterval(updateLiveScore, currentPollInterval);
                        }
                    })
                    .finally(() => {
                        isPolling = false;
                    });
                
                // Fetch new events (separate request to prevent blocking)
                fetchWithTimeout(eventsUrl)
                    .then(result => {
                        // Validate response structure
                        if (!result || typeof result !== 'object') {
                            return;
                        }
                        
                        if (result.success && result.data && Array.isArray(result.data) && result.data.length > 0) {
                            // New events received - reload to show updated data
                            const newSeqs = result.data
                                .map(e => safeGet(e, 'assigned_server_seq', 0))
                                .filter(seq => seq > 0);
                            
                            if (newSeqs.length > 0) {
                                lastEventSeq = Math.max(...newSeqs);
                                location.reload();
                            }
                        }
                    })
                    .catch(error => {
                        // Events fetch errors are less critical, log but don't affect polling
                        logError('fetchEvents', error, { matchId: <?= $matchId ?> });
                    });
            }
            
            // Only start polling if match is live
            <?php if ($match['state'] === 'live'): ?>
            refreshInterval = setInterval(updateLiveScore, currentPollInterval);
            
            // Clean up on page unload (Rule 6: Defensive Programming)
            window.addEventListener('beforeunload', function() {
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                }
                // Abort pending requests
                pendingRequests.clear();
            });
            
            // Handle page visibility (Rule 28: Cost Control - reduce polling when tab hidden)
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    // Tab hidden - increase polling interval
                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                        refreshInterval = setInterval(updateLiveScore, CONFIG.MAX_POLL_INTERVAL);
                    }
                } else {
                    // Tab visible - resume normal polling
                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                        refreshInterval = setInterval(updateLiveScore, CONFIG.INITIAL_POLL_INTERVAL);
                    } else {
                        refreshInterval = setInterval(updateLiveScore, CONFIG.INITIAL_POLL_INTERVAL);
                    }
                }
            });
            <?php endif; ?>
        })();
    </script>
</body>
</html>



