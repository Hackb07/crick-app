<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /cricapp/admin/login.php');
    exit;
}

$matchId = $_GET['id'] ?? 0;

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../classes/Match.php';
require_once __DIR__ . '/../../classes/Database.php';

$matchModel = new MatchModel();
$matchData = $matchModel->getById($matchId);

// If match is completed, redirect to match view (scorecard page)
if (!$matchData || ($matchData['state'] !== 'live' && $matchData['state'] !== 'completed')) {
    header('Location: /cricapp/admin/matches/view.php?id=' . $matchId);
    exit;
}

// If match is completed, show complete scorecard instead of scorer page
if ($matchData['state'] === 'completed') {
    header('Location: /cricapp/admin/matches/view.php?id=' . $matchId);
    exit;
}

$user = $_SESSION['user'];
$db = Database::getInstance()->getConnection();

// Get players for this match grouped by team
// Determine which team is batting based on toss and current innings
$battingTeamId = null;
$bowlingTeamId = null;
$currentInnings = (int)($matchData['current_innings'] ?? 1);

if ($matchData['toss_winner_id'] && $matchData['toss_decision']) {
    // Innings 1: toss winner bats if chose to bat, otherwise bowls
    // Innings 2: opposite of innings 1
    $tossWinnerBattingFirst = ($matchData['toss_decision'] === 'bat');
    
    // For innings 1, use toss decision directly
    // For innings 2, swap teams
    if ($currentInnings === 1) {
        if ($tossWinnerBattingFirst) {
            $battingTeamId = $matchData['toss_winner_id'];
            $bowlingTeamId = ($matchData['toss_winner_id'] == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
        } else {
            $bowlingTeamId = $matchData['toss_winner_id'];
            $battingTeamId = ($matchData['toss_winner_id'] == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
        }
    } else {
        // Innings 2: swap teams
        if ($tossWinnerBattingFirst) {
            // Toss winner batted first, so they bowl in innings 2
            $bowlingTeamId = $matchData['toss_winner_id'];
            $battingTeamId = ($matchData['toss_winner_id'] == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
        } else {
            // Toss winner bowled first, so they bat in innings 2
            $battingTeamId = $matchData['toss_winner_id'];
            $bowlingTeamId = ($matchData['toss_winner_id'] == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
        }
    }
}

// Get batting team players
$battingPlayers = [];
if ($battingTeamId) {
    $sql = "SELECT pa.*, p.name as player_name, p.player_id 
            FROM player_appearances pa
            JOIN players p ON pa.player_id = p.player_id
            WHERE pa.match_id = :match_id AND pa.team_id = :team_id
            ORDER BY p.name";
    $stmt = $db->prepare($sql);
    $stmt->execute(['match_id' => $matchId, 'team_id' => $battingTeamId]);
    $battingPlayers = $stmt->fetchAll();
}

// Get bowling team players
$bowlingPlayers = [];
if ($bowlingTeamId) {
    $sql = "SELECT pa.*, p.name as player_name, p.player_id 
            FROM player_appearances pa
            JOIN players p ON pa.player_id = p.player_id
            WHERE pa.match_id = :match_id AND pa.team_id = :team_id
            ORDER BY p.name";
    $stmt = $db->prepare($sql);
    $stmt->execute(['match_id' => $matchId, 'team_id' => $bowlingTeamId]);
    $bowlingPlayers = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Scoring - <?= htmlspecialchars($matchData['team1_name']) ?> vs <?= htmlspecialchars($matchData['team2_name']) ?></title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/admin.css">
    <link rel="stylesheet" href="/cricapp/assets/css/scoring.css">
    <link rel="stylesheet" href="/cricapp/assets/css/toast.css">
    <link rel="stylesheet" href="/cricapp/assets/css/responsive-upgrades.css">
    <style>
        /* Commentary Section Styles - Matching match-view.php */
        .commentary-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .commentary-section h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .commentary-feed {
            max-height: 400px;
            overflow-y: auto;
            background: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        
        .commentary-item {
            margin-bottom: 16px;
            padding: 12px;
            border-left: 3px solid #e0e0e0;
            border-radius: 4px;
            background: #fafafa;
        }
        
        .commentary-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .commentary-circle {
            display: inline-block;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            color: white;
            text-align: center;
            line-height: 28px;
            font-weight: bold;
            margin-right: 8px;
            font-size: 14px;
        }
        
        .commentary-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 4px;
        }
        
        .commentary-dismissal {
            margin: 8px 0;
            padding: 8px;
            background: #ffebee;
            border-left: 3px solid #f44336;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .loading {
            padding: 1rem;
            text-align: center;
            color: #6b7280;
            font-style: italic;
        }
        
        .commentary-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .commentary-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <h1>Live Scoring</h1>
                <div class="header-actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="/cricapp/admin/matches/view.php?id=<?= $matchId ?>" class="btn btn-secondary">Match View</a>
                    <a href="/cricapp/admin/logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="scoring-container">
        <div class="scorecard">
            <div class="scorecard-header">
                <div>
                    <h2><?= htmlspecialchars($matchData['team1_name']) ?> vs <?= htmlspecialchars($matchData['team2_name']) ?></h2>
                    <div class="overs-info">
                        Overs: <?= $matchData['overs_per_innings'] ?> per innings | 
                        <strong>Innings: <?= htmlspecialchars($currentInnings) ?></strong>
                    </div>
                </div>
                <div class="current-score" id="current-score">
                    0 / 0 (0.0)
                </div>
            </div>

            <!-- Player Selection Section -->
            <div class="player-selection-section">
                <!-- Striker -->
                <div class="player-selector">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333;">Striker *</label>
                    <select id="striker-select" class="form-control" style="width: 100%;">
                        <option value="">Select Striker</option>
                        <?php foreach ($battingPlayers as $player): ?>
                            <option value="<?= $player['appearance_id'] ?>" data-player-id="<?= $player['player_id'] ?>" data-player-name="<?= htmlspecialchars($player['player_name']) ?>">
                                <?= htmlspecialchars($player['player_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="striker-display" style="margin-top: 0.5rem; font-weight: bold; color: #2196F3;"></div>
                </div>

                <!-- Non-Striker -->
                <div class="player-selector">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333;">Non-Striker *</label>
                    <select id="non-striker-select" class="form-control" style="width: 100%;">
                        <option value="">Select Non-Striker</option>
                        <?php foreach ($battingPlayers as $player): ?>
                            <option value="<?= $player['appearance_id'] ?>" data-player-id="<?= $player['player_id'] ?>" data-player-name="<?= htmlspecialchars($player['player_name']) ?>">
                                <?= htmlspecialchars($player['player_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="non-striker-display" style="margin-top: 0.5rem; font-weight: bold; color: #4CAF50;"></div>
                </div>

                <!-- Bowler -->
                <div class="player-selector" id="bowler-selector">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333;">Bowler *</label>
                    <select id="bowler-select" class="form-control" style="width: 100%;">
                        <option value="">Select Bowler</option>
                        <?php foreach ($bowlingPlayers as $player): ?>
                            <option value="<?= $player['appearance_id'] ?>" data-player-id="<?= $player['player_id'] ?>" data-player-name="<?= htmlspecialchars($player['player_name']) ?>">
                                <?= htmlspecialchars($player['player_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="bowler-display" style="margin-top: 0.5rem; font-weight: bold; color: #FF9800;"></div>
                    <div id="bowler-change-indicator" style="margin-top: 0.5rem; padding: 0.5rem; background: #fff3cd; border-left: 3px solid #ff9800; border-radius: 4px; display: none;">
                        <strong>⚠️ Over Complete:</strong> Please select new bowler
                    </div>
                </div>
            </div>

            <!-- Player Stats Display (like Cricbuzz) -->
            <div id="player-stats-container" style="margin: 1.5rem 0; padding: 1rem; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <!-- Stats will be populated by JavaScript -->
            </div>

            <div class="scoring-buttons">
                <button class="scoring-btn runs-0" data-runs="0" onclick="scoreRuns(0)">0</button>
                <button class="scoring-btn runs-1" data-runs="1" onclick="scoreRuns(1)">1</button>
                <button class="scoring-btn runs-2" data-runs="2" onclick="scoreRuns(2)">2</button>
                <button class="scoring-btn runs-3" data-runs="3" onclick="scoreRuns(3)">3</button>
                <button class="scoring-btn runs-4" data-runs="4" onclick="scoreRuns(4)">4</button>
                <button class="scoring-btn runs-6" data-runs="6" onclick="scoreRuns(6)">6</button>
                <button class="scoring-btn wicket" data-type="wicket" onclick="scoreWicket()">W</button>
                <button class="scoring-btn extra" data-type="wide" onclick="scoreExtra('wide')">WIDE</button>
                <button class="scoring-btn extra" data-type="noball" onclick="scoreExtra('noball')">NO BALL</button>
                <button class="scoring-btn undo-btn" onclick="undoLastEvent()">↶ UNDO</button>
            </div>
            
            <!-- Complete Innings Button -->
            <div id="complete-innings-section" style="margin: 1rem 0; display: none;">
                <button id="complete-innings-btn" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; background: #4CAF50; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">
                    ✅ Complete Innings <?= htmlspecialchars($currentInnings) ?> & Start Innings <?= htmlspecialchars($currentInnings + 1) ?>
                </button>
            </div>
            
            <div style="margin: 1rem 0; padding: 0.75rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong>⚠️ Important:</strong> Please select Striker, Non-Striker, and Bowler before starting to score. 
                Update these when a wicket falls or when an over completes.
            </div>

            <!-- Commentary Section -->
            <div class="commentary-section">
                <h3 class="section-title">Live Commentary</h3>
                <input type="text" id="commentary-input" class="commentary-input" 
                       placeholder="Add commentary for this ball...">
                <p style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                    Commentary will be automatically added when you score runs, wickets, or extras using the buttons above.
                </p>

                <div class="commentary-feed" id="commentary-feed">
                    <div class="loading">Loading commentary...</div>
                </div>
            </div>
        </div>
    </main>

    <script src="/cricapp/assets/js/api.js"></script>
    <script src="/cricapp/assets/js/toast.js"></script>
    <script src="/cricapp/assets/js/scoring-updates.js"></script>
    <script src="/cricapp/assets/js/scoring.js"></script>
    <script>
        // Initialize scoring interface
        const matchId = <?= $matchId ?>;
        api.setToken('<?= $_SESSION['token'] ?>');
        
        // Player selection handlers
        document.getElementById('striker-select').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                currentStriker = {
                    appearance_id: parseInt(option.value),
                    player_id: parseInt(option.dataset.playerId),
                    player_name: option.dataset.playerName
                };
                updatePlayerDisplays();
            }
        });
        
        document.getElementById('non-striker-select').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                currentNonStriker = {
                    appearance_id: parseInt(option.value),
                    player_id: parseInt(option.dataset.playerId),
                    player_name: option.dataset.playerName
                };
                updatePlayerDisplays();
            }
        });
        
        // Set up bowler change handler - use handleBowlerChange from scoring.js
        const bowlerSelect = document.getElementById('bowler-select');
        if (bowlerSelect && typeof handleBowlerChange === 'function') {
            bowlerSelect.addEventListener('change', handleBowlerChange);
        } else {
            // Fallback if function not yet loaded
            bowlerSelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                if (option.value) {
                    currentBowler = {
                        appearance_id: parseInt(option.value),
                        player_id: parseInt(option.dataset.playerId),
                        player_name: option.dataset.playerName
                    };
                    updatePlayerDisplays();
                }
            });
        }
        
        // Load match data and initialize display
        // Wait for scripts to load before calling functions
        if (typeof loadMatchData === 'function') {
            loadMatchData().then(() => {
                // Ensure scorecard is displayed after data loads
                console.log('Match data loaded, updating scorecard');
                updateScorecard();
            }).catch(err => {
                console.error('Failed to initialize scoring:', err);
                if (typeof toast !== 'undefined') {
                    toast.error('Failed to load match data. Please refresh the page.');
                }
            });
        } else {
            console.error('loadMatchData function not found. Scripts may not have loaded.');
            // Wait a bit and try again
            setTimeout(() => {
                if (typeof loadMatchData === 'function') {
                    loadMatchData().then(() => {
                        updateScorecard();
                    }).catch(err => {
                        console.error('Failed to initialize scoring:', err);
                    });
                } else {
                    console.error('loadMatchData still not available after delay');
                    if (typeof toast !== 'undefined') {
                        toast.error('Failed to load scoring scripts. Please refresh the page.');
                    }
                }
            }, 500);
        }
        
        // Complete innings button handler
        const completeInningsBtn = document.getElementById('complete-innings-btn');
        if (completeInningsBtn) {
            completeInningsBtn.addEventListener('click', function() {
                completeInnings();
            });
        }
        
        // Auto-refresh every 10 seconds
        setInterval(async () => {
            await loadEvents();
            updateScorecard();
            updateCompleteInningsButton();
            loadCommentary();
        }, 10000);
    </script>
</body>
</html>

