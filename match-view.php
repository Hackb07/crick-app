<?php
/**
 * Match View Page - Premium Experience (Unified Design)
 * Enhanced with better commentary and highlights
 */

require_once __DIR__ . '/includes/bootstrap.php';

$matchId = (int)getQuery('id', 0);
if (!$matchId) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

// Load Match Stats Service
require_once __DIR__ . '/classes/MatchStatsService.php';
$db = Database::getInstance()->getConnection();
$statsService = new MatchStatsService();
$matchStats = $statsService->getMatchStats($matchId);

if (!$matchStats) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

// Extract variables for view
$match = $matchStats['match'];
$events = $matchStats['events'];
$commentaryItems = $matchStats['commentaryItems'];
$players = $matchStats['players'];
$playerLookup = $matchStats['playerLookup'];
$teamPlayers = $matchStats['teamPlayers'];
$inningsData = $matchStats['inningsData'];
$highlights = $matchStats['highlights'];

$activeTab = getQuery('tab', 'scorecard');

// Conditional Caching: Enable caching ONLY if match is completed
if (isset($match['state']) && $match['state'] === 'completed') {
    // Cache completed matches for 1 hour
    if (function_exists('allowBrowserCaching')) {
        allowBrowserCaching(3600);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title><?= e($match['team1_name']) ?> vs <?= e($match['team2_name']) ?> - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
</head>
<body>

    <?php require_once __DIR__ . '/includes/nav-menu.php'; ?>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <a href="<?= publicUrl('matches.php') ?>" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="font-bold"><?= e($match['series_name'] ?? 'Match') ?></span>
                </a>
                <div class="badge badge-live" style="display: <?= $match['state'] === 'live' ? 'inline-block' : 'none' ?>;">LIVE</div>
            </div>
        </div>
    </header>

    <!-- Match Hero -->
    <div style="background: white; padding: 0 16px; border-bottom: 1px solid #f1f5f9;">
        <div class="score-board">
            <div class="team-block">
                <div class="team-name-lg"><?= e($match['team1_name']) ?></div>
                <div class="score-lg">
                    <?php
                    $t1Runs = ($inningsData[1]['batting_team_id'] == $match['team1_id']) ? $inningsData[1]['total_runs'] : ($inningsData[2]['batting_team_id'] == $match['team1_id'] ? $inningsData[2]['total_runs'] : 0);
                    $t1Wkts = ($inningsData[1]['batting_team_id'] == $match['team1_id']) ? $inningsData[1]['total_wickets'] : ($inningsData[2]['batting_team_id'] == $match['team1_id'] ? $inningsData[2]['total_wickets'] : 0);
                    $t1Balls = ($inningsData[1]['batting_team_id'] == $match['team1_id']) ? $inningsData[1]['balls_legal'] : ($inningsData[2]['batting_team_id'] == $match['team1_id'] ? $inningsData[2]['balls_legal'] : 0);
                    
                    $hasBatted1 = ($inningsData[1]['batting_team_id'] == $match['team1_id'] && ($inningsData[1]['balls_legal'] > 0 || $inningsData[1]['total_runs'] > 0)) || 
                                  ($inningsData[2]['batting_team_id'] == $match['team1_id'] && ($inningsData[2]['balls_legal'] > 0 || $inningsData[2]['total_runs'] > 0));
                    
                    echo $hasBatted1 ? "$t1Runs/$t1Wkts" : "-";
                    ?>
                </div>
                <div class="overs-lg">
                    <?php if ($hasBatted1) echo "(" . floor($t1Balls/6) . "." . ($t1Balls%6) . " ov)"; ?>
                </div>
            </div>
            
            <div class="vs-badge">VS</div>
            
            <div class="team-block" style="text-align: right;">
                <div class="team-name-lg"><?= e($match['team2_name']) ?></div>
                <div class="score-lg">
                    <?php
                    $t2Runs = ($inningsData[1]['batting_team_id'] == $match['team2_id']) ? $inningsData[1]['total_runs'] : ($inningsData[2]['batting_team_id'] == $match['team2_id'] ? $inningsData[2]['total_runs'] : 0);
                    $t2Wkts = ($inningsData[1]['batting_team_id'] == $match['team2_id']) ? $inningsData[1]['total_wickets'] : ($inningsData[2]['batting_team_id'] == $match['team2_id'] ? $inningsData[2]['total_wickets'] : 0);
                    $t2Balls = ($inningsData[1]['batting_team_id'] == $match['team2_id']) ? $inningsData[1]['balls_legal'] : ($inningsData[2]['batting_team_id'] == $match['team2_id'] ? $inningsData[2]['balls_legal'] : 0);
                    
                    $hasBatted2 = ($inningsData[1]['batting_team_id'] == $match['team2_id'] && ($inningsData[1]['balls_legal'] > 0 || $inningsData[1]['total_runs'] > 0)) || 
                                  ($inningsData[2]['batting_team_id'] == $match['team2_id'] && ($inningsData[2]['balls_legal'] > 0 || $inningsData[2]['total_runs'] > 0));
                    
                    echo $hasBatted2 ? "$t2Runs/$t2Wkts" : "-";
                    ?>
                </div>
                <div class="overs-lg">
                    <?php if ($hasBatted2) echo "(" . floor($t2Balls/6) . "." . ($t2Balls%6) . " ov)"; ?>
                </div>
            </div>
        </div>
        
        <?php
        // Calculate dynamic match status message
        $statusMessage = '';
        $statusClass = 'text-muted';
        
        // Determine which innings are complete
        $inn1Complete = $hasBatted1 && ($inningsData[1]['total_wickets'] >= 10 || $inningsData[1]['balls_legal'] >= ($match['overs_per_innings'] * 6));
        $inn2Complete = $hasBatted2 && ($inningsData[2]['total_wickets'] >= 10 || $inningsData[2]['balls_legal'] >= ($match['overs_per_innings'] * 6));
        
        if (!$hasBatted1 && !$hasBatted2) {
            // Match not started
            $statusMessage = 'Match has not started yet';
            $statusClass = 'text-muted';
        } elseif ($hasBatted1 && !$hasBatted2) {
            // 1st innings in progress or complete
            if ($inn1Complete) {
                $inn1Team = ($inningsData[1]['batting_team_id'] == $match['team1_id']) ? $match['team1_name'] : $match['team2_name'];
                $statusMessage = "$inn1Team: {$inningsData[1]['total_runs']}/{$inningsData[1]['total_wickets']} - 1st Innings Complete";
                $statusClass = 'text-primary';
            } else {
                $statusMessage = '1st Innings in progress';
                $statusClass = 'text-primary';
            }
        } elseif ($hasBatted1 && $hasBatted2) {
            // 2nd innings in progress or complete
            $inn1Runs = $inningsData[1]['total_runs'];
            $inn2Runs = $inningsData[2]['total_runs'];
            $inn2Wkts = $inningsData[2]['total_wickets'];
            $inn2Team = ($inningsData[2]['batting_team_id'] == $match['team1_id']) ? $match['team1_name'] : $match['team2_name'];
            $inn1Team = ($inningsData[1]['batting_team_id'] == $match['team1_id']) ? $match['team1_name'] : $match['team2_name'];
            
            // Calculate max wickets for 2nd innings team
            $inn2BattingTeamId = $inningsData[2]['batting_team_id'];
            $inn2TeamSize = count($teamPlayers[$inn2BattingTeamId] ?? []);
            $maxWickets = $inn2TeamSize > 0 ? $inn2TeamSize : 10;
            
            $target = $inn1Runs + 1;
            $needed = $target - $inn2Runs;
            
            if ($inn2Complete || $inn2Runs >= $target) {
                // Match complete
                if ($inn2Runs > $inn1Runs) {
                    $wicketsLeft = $maxWickets - $inn2Wkts;
                    $statusMessage = "$inn2Team won by $wicketsLeft wicket" . ($wicketsLeft != 1 ? 's' : '');
                    $statusClass = 'text-success';
                } elseif ($inn2Runs < $inn1Runs) {
                    $runsDiff = $inn1Runs - $inn2Runs;
                    $statusMessage = "$inn1Team won by $runsDiff run" . ($runsDiff != 1 ? 's' : '');
                    $statusClass = 'text-success';
                } else {
                    $statusMessage = 'Match Tied';
                    $statusClass = 'text-warning';
                }
            } else {
                // 2nd innings in progress
                $ballsLeft = ($match['overs_per_innings'] * 6) - $inningsData[2]['balls_legal'];
                $wicketsLeft = $maxWickets - $inn2Wkts;
                $statusMessage = "$inn2Team need $needed run" . ($needed != 1 ? 's' : '') . " to win from $ballsLeft balls ($wicketsLeft wicket" . ($wicketsLeft != 1 ? 's' : '') . " remaining)";
                $statusClass = 'text-danger';
            }
        }
        
        // Use custom status note if available, otherwise use calculated message
        $finalStatus = !empty($match['status_note']) ? $match['status_note'] : $statusMessage;
        ?>
        
        <div class="text-center font-bold pb-3 text-sm <?= $statusClass ?>">
            <?= e($finalStatus) ?>
        </div>
    </div>

    <?php 
    // Display POTM prominently for completed matches
    if ($match['state'] === 'completed'): 
        try {
            require_once __DIR__ . '/classes/POTM.php';
            $potm = new POTM();
            $potmData = $potm->getForMatch($matchId);
            
            if ($potmData):
    ?>
        <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); padding: 16px; margin: 0 16px 16px 16px; border-radius: 12px; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);">
            <div style="text-align: center;">
                <div style="font-size: 0.75rem; color: #92400e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    🏆 Player of the Match
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #f59e0b; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span style="font-size: 2rem;">⭐</span>
                    <?= e($potmData['player_name']) ?>
                </div>
            </div>
        </div>
    <?php 
            endif;
        } catch (Exception $e) {
            error_log("POTM display error: " . $e->getMessage());
        }
    endif; 
    ?>

    <!-- Tabs -->
    <div class="nav-tabs-scroll">
        <a href="?id=<?= $matchId ?>&tab=scorecard" class="nav-tab-link <?= $activeTab === 'scorecard' ? 'active' : '' ?>">Scorecard</a>
        <a href="?id=<?= $matchId ?>&tab=squad" class="nav-tab-link <?= $activeTab === 'squad' ? 'active' : '' ?>">Squad</a>
        <a href="?id=<?= $matchId ?>&tab=commentary" class="nav-tab-link <?= $activeTab === 'commentary' ? 'active' : '' ?>">Commentary</a>
        <a href="?id=<?= $matchId ?>&tab=highlights" class="nav-tab-link <?= $activeTab === 'highlights' ? 'active' : '' ?>">Highlights</a>
        <a href="?id=<?= $matchId ?>&tab=info" class="nav-tab-link <?= $activeTab === 'info' ? 'active' : '' ?>">Info</a>
    </div>

    <!-- Main Content -->
    <div class="container">
        
        <!-- Scorecard Tab -->
        <?php if ($activeTab === 'scorecard'): ?>
            <?php
            // Determine which team batted in each innings for button labels
            $inn1BattingTeamId = $inningsData[1]['batting_team_id'];
            $inn1BattingTeamName = ($inn1BattingTeamId == $match['team1_id']) ? $match['team1_name'] : $match['team2_name'];
            
            $inn2BattingTeamId = $inningsData[2]['batting_team_id'];
            $inn2BattingTeamName = ($inn2BattingTeamId == $match['team1_id']) ? $match['team1_name'] : $match['team2_name'];
            ?>
            
            <!-- Team Toggle Buttons -->
            <div style="margin-bottom: 16px; display: flex; gap: 8px;">
                <button class="btn-toggle active" id="btn-inn-1" onclick="showInnings(1)" style="flex: 1; padding: 12px; border-radius: 8px; border: none; background: var(--primary); color: white; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px;">
                    <?= e($inn1BattingTeamName) ?> <span style="font-size: 0.75rem; opacity: 0.8;">(1st Inn)</span>
                </button>
                <button class="btn-toggle" id="btn-inn-2" onclick="showInnings(2)" style="flex: 1; padding: 12px; border-radius: 8px; border: none; background: #e2e8f0; color: #475569; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px;">
                    <?= e($inn2BattingTeamName) ?> <span style="font-size: 0.75rem; opacity: 0.8;">(2nd Inn)</span>
                </button>
            </div>
            
            <?php 
            $hasData = false;
            foreach ([1, 2] as $inn): 
                $innData = $inningsData[$inn];
                
                if ($innData['total_runs'] > 0 || $innData['balls_legal'] > 0) $hasData = true;
                
                // Determine batting team name
                $battingTeamId = $innData['batting_team_id'];
                $battingTeamName = ($battingTeamId == $match['team1_id']) ? $match['team1_name'] : $match['team2_name'];
                
                // Determine display style (default to innings 1)
                $displayStyle = ($inn == 1) ? 'block' : 'none';
            ?>
                <div class="glass-card innings-card" id="innings-card-<?= $inn ?>" style="display: <?= $displayStyle ?>;">
                    <div class="card-header">
                        <span><?= e($battingTeamName) ?> Innings</span>
                        <span><?= $innData['total_runs'] ?>/<?= $innData['total_wickets'] ?> (<?= floor($innData['balls_legal']/6) . '.' . ($innData['balls_legal']%6) ?>)</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="scorecard-table">
                            <thead>
                                <tr>
                                    <th>Batter</th>
                                    <th>R</th>
                                    <th>B</th>
                                    <th>4s</th>
                                    <th>6s</th>
                                    <th>SR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($innData['batting'])): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted" style="padding: 20px;">No batting data available yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($innData['batting'] as $pid => $stats): 
                                        // Ensure $pid is integer for lookup
                                        $pid = (int)$pid;
                                        
                                        // Try to get player name from lookup
                                        if (isset($playerLookup[$pid])) {
                                            $pName = $playerLookup[$pid]['name'];
                                        } else {
                                            // Fallback: Query database directly
                                            try {
                                                $stmt = $db->prepare("SELECT name FROM players WHERE player_id = :pid");
                                                $stmt->execute(['pid' => $pid]);
                                                $player = $stmt->fetch(PDO::FETCH_ASSOC);
                                                $pName = $player['name'] ?? 'Unknown Player';
                                                
                                                // Add to lookup for future use
                                                if ($player) {
                                                    $playerLookup[$pid] = ['name' => $player['name']];
                                                }
                                            } catch (Exception $e) {
                                                $pName = 'Unknown Player';
                                                error_log("Failed to fetch player $pid: " . $e->getMessage());
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="font-bold"><?= e($pName) ?></div>
                                                <span class="dismissal-info">
                                                    <?= $stats['out'] ? str_replace('_', ' ', $stats['dismissal']) : 'not out' ?>
                                                </span>
                                            </td>
                                            <td class="font-bold"><?= $stats['runs'] ?></td>
                                            <td><?= $stats['balls'] ?></td>
                                            <td><?= $stats['4s'] ?></td>
                                            <td><?= $stats['6s'] ?></td>
                                            <td><?= $stats['balls'] > 0 ? round($stats['runs']/$stats['balls']*100, 1) : '-' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <!-- Extras -->
                                    <tr style="background: #fcfcfc;">
                                        <td>Extras</td>
                                        <td colspan="5" style="text-align: left; color: var(--text-muted);">
                                            <?= $innData['extras']['total'] ?> 
                                            (w <?= $innData['extras']['wide'] ?>, nb <?= $innData['extras']['no_ball'] ?>, b <?= $innData['extras']['bye'] ?>, lb <?= $innData['extras']['leg_bye'] ?>)
                                        </td>
                                    </tr>
                                    
                                    <!-- Total -->
                                    <tr style="background: #f8f9fa; font-weight: 700;">
                                        <td>Total</td>
                                        <td colspan="5" style="text-align: left;">
                                            <?= $innData['total_runs'] ?>/<?= $innData['total_wickets'] ?>
                                            <span style="font-weight: 400; color: var(--text-muted); font-size: 11px; margin-left: 8px;">
                                                (<?= floor($innData['balls_legal']/6) . '.' . ($innData['balls_legal']%6) ?> ov)
                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Bowling Table -->
                    <?php if (!empty($innData['bowling'])): ?>
                        <div class="table-responsive" style="margin-top: 24px;">
                            <div style="padding: 12px 16px; background: #f8f9fa; font-weight: 700; border-bottom: 2px solid var(--primary);">
                                Bowling
                            </div>
                            <table class="scorecard-table">
                                <thead>
                                    <tr>
                                        <th>Bowler</th>
                                        <th>O</th>
                                        <th>M</th>
                                        <th>R</th>
                                        <th>W</th>
                                        <th>Econ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($innData['bowling'] as $pid => $stats): 
                                        // Ensure $pid is integer for lookup
                                        $pid = (int)$pid;
                                        
                                        // Try to get player name from lookup
                                        if (isset($playerLookup[$pid])) {
                                            $pName = $playerLookup[$pid]['name'];
                                        } else {
                                            // Fallback: Query database directly
                                            try {
                                                $stmt = $db->prepare("SELECT name FROM players WHERE player_id = :pid");
                                                $stmt->execute(['pid' => $pid]);
                                                $player = $stmt->fetch(PDO::FETCH_ASSOC);
                                                $pName = $player['name'] ?? 'Unknown Player';
                                                
                                                // Add to lookup for future use
                                                if ($player) {
                                                    $playerLookup[$pid] = ['name' => $player['name']];
                                                }
                                            } catch (Exception $e) {
                                                $pName = 'Unknown Player';
                                                error_log("Failed to fetch player $pid: " . $e->getMessage());
                                            }
                                        }
                                        
                                        $overs = floor($stats['balls'] / 6) . '.' . ($stats['balls'] % 6);
                                        $economy = $stats['balls'] > 0 ? round(($stats['runs'] / $stats['balls']) * 6, 2) : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="font-bold"><?= e($pName) ?></div>
                                            </td>
                                            <td><?= $overs ?></td>
                                            <td><?= $stats['maidens'] ?></td>
                                            <td class="font-bold"><?= $stats['runs'] ?></td>
                                            <td class="font-bold"><?= $stats['wickets'] ?></td>
                                            <td><?= $economy ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <!-- FOW -->
                    <?php if (!empty($innData['fow'])): ?>
                        <div style="padding: 12px 16px; font-size: 12px; color: var(--text-muted); border-top: 1px solid #f1f5f9;">
                            <strong>Fall of Wickets: </strong>
                            <?php 
                            $fowStr = [];
                            foreach ($innData['fow'] as $fow) {
                                $fowStr[] = "{$fow['score']}-{$fow['wickets']} ({$fow['batsman']}, {$fow['over']} ov)";
                            }
                            echo implode(', ', $fowStr);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (!$hasData): ?>
                <!-- Empty State -->
                <div class="glass-card">
                    <div class="card-header">
                        <span><?= e($match['team1_name']) ?> Innings</span>
                        <span>Yet to Bat</span>
                    </div>
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 40px; margin-bottom: 12px;">🏏</div>
                        <div class="font-bold mb-2">Match has not started yet</div>
                        <div class="text-sm">Scorecard will be available once the match begins</div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Squad Tab -->
        <?php if ($activeTab === 'squad'): ?>
            <div class="squads-container" style="display: flex; flex-wrap: wrap; gap: 16px;">
                <!-- Team 1 Squad -->
                <div class="glass-card" style="flex: 1; min-width: 300px; margin-bottom: 0;">
                    <div class="card-header" style="background: var(--primary); color: white;">
                        <?= e($match['team1_name']) ?> Squad
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (empty($teamPlayers[$match['team1_id']])): ?>
                            <div style="padding: 20px; text-align: center; color: var(--text-muted);">No players listed</div>
                        <?php else: ?>
                            <?php foreach ($teamPlayers[$match['team1_id']] as $p): ?>
                                <div class="list-item">
                                    <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748b; margin-right: 12px;">
                                        <?= strtoupper(substr($p['name'], 0, 1)) ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="font-bold">
                                            <?= e($p['name']) ?>
                                            <?php if (!empty($p['is_captain'])): ?>
                                                <span style="background: #f59e0b; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">C</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-muted">
                                            <?= !empty($p['batting_style']) ? ucfirst($p['batting_style']) : 'Batter' ?>
                                            <?= !empty($p['bowling_style']) ? ' • ' . ucfirst($p['bowling_style']) : '' ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Team 2 Squad -->
                <div class="glass-card" style="flex: 1; min-width: 300px; margin-bottom: 0;">
                    <div class="card-header" style="background: var(--primary); color: white;">
                        <?= e($match['team2_name']) ?> Squad
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (empty($teamPlayers[$match['team2_id']])): ?>
                            <div style="padding: 20px; text-align: center; color: var(--text-muted);">No players listed</div>
                        <?php else: ?>
                            <?php foreach ($teamPlayers[$match['team2_id']] as $p): ?>
                                <div class="list-item">
                                    <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748b; margin-right: 12px;">
                                        <?= strtoupper(substr($p['name'], 0, 1)) ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="font-bold">
                                            <?= e($p['name']) ?>
                                            <?php if (!empty($p['is_captain'])): ?>
                                                <span style="background: #f59e0b; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">C</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-muted">
                                            <?= !empty($p['batting_style']) ? ucfirst($p['batting_style']) : 'Batter' ?>
                                            <?= !empty($p['bowling_style']) ? ' • ' . ucfirst($p['bowling_style']) : '' ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Commentary Tab -->
        <?php if ($activeTab === 'commentary'): ?>
            <div class="glass-card">
                <?php 
                // Prefer stored commentary, fallback to on-the-fly
                $useStored = !empty($commentaryItems);
                $displayItems = $useStored ? $commentaryItems : array_reverse($events);
                
                if (empty($displayItems)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 32px; margin-bottom: 8px;">🎙️</div>
                        No commentary available yet.
                    </div>
                <?php else: ?>
                    <?php foreach ($displayItems as $item): 
                        $payload = json_decode($item['payload_json'] ?? '{}', true);
                        $type = $payload['type'] ?? '';
                        $runs = $payload['runs'] ?? 0;
                        
                        // Icon & Highlight Logic
                        $icon = '⚪';
                        $highlight = false;
                        if ($type === 'run') {
                            if ($runs == 4) { $icon = '4️⃣'; $highlight = true; }
                            elseif ($runs == 6) { $icon = '6️⃣'; $highlight = true; }
                            else { $icon = '🏏'; }
                        } elseif ($type === 'wicket') {
                            $icon = '☝️';
                            $highlight = true;
                        } elseif ($type === 'extra') {
                            $icon = '⚠️';
                        }
                        
                        // Text Logic
                        $text = '';
                        if ($useStored) {
                            $text = e($item['commentary_text']);
                        } else {
                            // Fallback Generation
                            $strikerId = (int)($payload['striker_id'] ?? 0);
                            $bowlerId = (int)($payload['bowler_id'] ?? 0);
                            
                            // Get striker name with fallback
                            if (isset($playerLookup[$strikerId])) {
                                $strikerName = $playerLookup[$strikerId]['name'];
                            } else {
                                try {
                                    $stmt = $db->prepare("SELECT name FROM players WHERE player_id = :pid");
                                    $stmt->execute(['pid' => $strikerId]);
                                    $player = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $strikerName = $player['name'] ?? 'Unknown';
                                    if ($player) $playerLookup[$strikerId] = ['name' => $player['name']];
                                } catch (Exception $e) {
                                    $strikerName = 'Unknown';
                                }
                            }
                            
                            // Get bowler name with fallback
                            if (isset($playerLookup[$bowlerId])) {
                                $bowlerName = $playerLookup[$bowlerId]['name'];
                            } else {
                                try {
                                    $stmt = $db->prepare("SELECT name FROM players WHERE player_id = :pid");
                                    $stmt->execute(['pid' => $bowlerId]);
                                    $player = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $bowlerName = $player['name'] ?? 'Unknown';
                                    if ($player) $playerLookup[$bowlerId] = ['name' => $player['name']];
                                } catch (Exception $e) {
                                    $bowlerName = 'Unknown';
                                }
                            }
                            
                            if ($type === 'run') $text = "$strikerName scores $runs run(s) off $bowlerName";
                            elseif ($type === 'wicket') $text = "WICKET! $strikerName out b $bowlerName";
                            elseif ($type === 'extra') $text = "Extra by $bowlerName";
                            else continue;
                        }
                    ?>
                        <div class="comm-item" style="<?= $highlight ? 'background: #fffbeb;' : '' ?>">
                            <div class="comm-ball"><?= $icon ?></div>
                            <div class="comm-text">
                                <div class="text-sm text-muted"><?= $text ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Highlights Tab -->
        <?php if ($activeTab === 'highlights'): ?>
            <div class="glass-card">
                <div class="card-header">Match Highlights</div>
                <?php if (empty($highlights)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <div style="font-size: 32px; margin-bottom: 8px;">⭐</div>
                        No highlights available yet.
                    </div>
                <?php else: ?>
                    <div class="card-body" style="padding: 0;">
                        <?php foreach ($highlights as $highlight): ?>
                            <div class="list-item">
                                <div style="flex: 1;">
                                    <?php if ($highlight['type'] === 'four'): ?>
                                        <div class="font-bold">4️⃣ FOUR!</div>
                                        <div class="text-sm text-muted"><?= e($highlight['player']) ?> - Over <?= $highlight['over'] ?>.<?= $highlight['ball'] ?></div>
                                    <?php elseif ($highlight['type'] === 'six'): ?>
                                        <div class="font-bold">6️⃣ SIX!</div>
                                        <div class="text-sm text-muted"><?= e($highlight['player']) ?> - Over <?= $highlight['over'] ?>.<?= $highlight['ball'] ?></div>
                                    <?php else: ?>
                                        <div class="font-bold text-danger">☝️ WICKET!</div>
                                        <div class="text-sm text-muted"><?= e($highlight['player']) ?> <?= str_replace('_', ' ', $highlight['wicket_type']) ?> - Over <?= $highlight['over'] ?>.<?= $highlight['ball'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Info Tab -->
        <?php if ($activeTab === 'info'): ?>
            <div class="glass-card">
                <div class="card-header">Match Info</div>
                <div class="card-body">
                    <div class="list-item">
                        <div style="flex: 1; color: var(--text-muted);">Series</div>
                        <div class="font-bold"><?= e($match['series_name'] ?? 'Friendly') ?></div>
                    </div>
                    <div class="list-item">
                        <div style="flex: 1; color: var(--text-muted);">Date</div>
                        <div class="font-bold"><?= formatDate($match['match_date'], 'M d, Y h:i A') ?></div>
                    </div>
                    <div class="list-item">
                        <div style="flex: 1; color: var(--text-muted);">Venue</div>
                        <div class="font-bold"><?= e($match['venue'] ?? 'Unknown Venue') ?></div>
                    </div>
                    <div class="list-item">
                        <div style="flex: 1; color: var(--text-muted);">Toss</div>
                        <div class="font-bold">
                            <?php 
                            if ($match['toss_winner_id']) {
                                $tossTeam = ($match['toss_winner_id'] == $match['team1_id']) ? $match['team1_name'] : $match['team2_name'];
                                echo "$tossTeam won toss, elected to " . $match['toss_decision'];
                            } else {
                                echo "To be decided";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <?php 
                    // Display POTM for completed matches
                    if ($match['state'] === 'completed'): 
                        try {
                            require_once __DIR__ . '/classes/POTM.php';
                            $potm = new POTM();
                            $potmData = $potm->getForMatch($matchId);
                            
                            if ($potmData):
                    ?>
                        <div class="list-item" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 8px; padding: 16px; margin-top: 12px;">
                            <div style="flex: 1;">
                                <div style="font-size: 0.75rem; color: #92400e; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                                    Player of the Match
                                </div>
                                <div style="font-size: 1.25rem; font-weight: 700; color: #f59e0b;">
                                    ⭐ <?= e($potmData['player_name']) ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                            endif;
                        } catch (Exception $e) {
                            error_log("POTM display error in Info tab: " . $e->getMessage());
                        }
                    endif; 
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Innings toggle functionality
    function showInnings(inn) {
        document.querySelectorAll('.innings-card').forEach(card => {
            card.style.display = 'none';
        });
        const targetCard = document.getElementById('innings-card-' + inn);
        if (targetCard) {
            targetCard.style.display = 'block';
        }
        
        // Update button states
        document.querySelectorAll('.btn-toggle').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = '#e2e8f0';
            btn.style.color = '#475569';
        });
        const activeBtn = document.getElementById('btn-inn-' + inn);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.background = 'var(--primary)';
            activeBtn.style.color = 'white';
        }
    }
    </script>

</body>
</html>
