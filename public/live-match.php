<?php
/**
 * Live Match View
 * Redirects to Vue.js version (default) for better performance
 */

// Redirect to Vue.js version if available
$vueFile = __DIR__ . '/live-match-vue.php';
if (file_exists($vueFile)) {
    try {
        require_once $vueFile;
        exit;
    } catch (Exception $e) {
        error_log('Error loading live-match-vue.php: ' . $e->getMessage());
        // Fall through to old version
    }
}

// Fallback to old version
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/Match.php';
require_once __DIR__ . '/../classes/Database.php';

$matchId = $_GET['id'] ?? 0;

if (!$matchId) {
    header('Location: /cricapp/public/');
    exit;
}

$matchModel = new MatchModel();
$matchData = $matchModel->getById($matchId);

if (!$matchData) {
    header('Location: /cricapp/public/');
    exit;
}

$db = Database::getInstance()->getConnection();
$currentInnings = (int)($matchData['current_innings'] ?? 1);

// Determine batting and bowling teams
$battingTeamId = null;
$bowlingTeamId = null;
$battingTeamName = null;
$bowlingTeamName = null;

if ($matchData['toss_winner_id'] && $matchData['toss_decision']) {
    $tossWinnerBattingFirst = ($matchData['toss_decision'] === 'bat');
    
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
            $bowlingTeamId = $matchData['toss_winner_id'];
            $battingTeamId = ($matchData['toss_winner_id'] == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
        } else {
            $battingTeamId = $matchData['toss_winner_id'];
            $bowlingTeamId = ($matchData['toss_winner_id'] == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
        }
    }
    
    $battingTeamName = ($battingTeamId == $matchData['team1_id']) ? $matchData['team1_name'] : $matchData['team2_name'];
    $bowlingTeamName = ($bowlingTeamId == $matchData['team1_id']) ? $matchData['team1_name'] : $matchData['team2_name'];
}

// Get all events for current innings
$sql = "SELECT e.*, pa.team_id, pa.player_id, p.name as player_name
        FROM events e
        LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
        LEFT JOIN players p ON pa.player_id = p.player_id
        WHERE e.match_id = :match_id
        ORDER BY e.assigned_server_seq ASC";
$stmt = $db->prepare($sql);
$stmt->execute(['match_id' => $matchId]);
$allEvents = $stmt->fetchAll();

// Find when innings 2 starts
$innings2StartSeq = null;
if ($currentInnings === 2) {
    foreach ($allEvents as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        $eventTeamId = $event['team_id'] ?? null;
        $eventType = $payload['event_type'] ?? '';
        
        if ($eventTeamId == $battingTeamId && ($eventType === 'runs' || $eventType === 'wicket')) {
            $innings2StartSeq = $event['assigned_server_seq'];
            break;
        }
    }
}

// Filter events for current innings
$currentInningsEvents = [];
foreach ($allEvents as $event) {
    $payload = json_decode($event['payload_json'], true);
    if (!is_array($payload)) continue;
    
    $eventTeamId = $event['team_id'] ?? null;
    $eventType = $payload['event_type'] ?? '';
    $eventSeq = $event['assigned_server_seq'] ?? 0;
    
    $belongsToInnings = false;
    
    if ($currentInnings === 1) {
        if ($innings2StartSeq === null || $eventSeq < $innings2StartSeq) {
            if ($eventType === 'runs' || $eventType === 'wicket') {
                $belongsToInnings = ($eventTeamId == $battingTeamId);
            } elseif ($eventType === 'extra') {
                $bowlerAppearanceId = $payload['bowler_appearance_id'] ?? null;
                if ($bowlerAppearanceId) {
                    $bowlerSql = "SELECT team_id FROM player_appearances WHERE appearance_id = :appearance_id";
                    $bowlerStmt = $db->prepare($bowlerSql);
                    $bowlerStmt->execute(['appearance_id' => $bowlerAppearanceId]);
                    $bowlerData = $bowlerStmt->fetch();
                    $belongsToInnings = ($bowlerData && $bowlerData['team_id'] == $bowlingTeamId);
                }
            }
        }
    } else {
        if ($innings2StartSeq !== null && $eventSeq >= $innings2StartSeq) {
            if ($eventType === 'runs' || $eventType === 'wicket') {
                $belongsToInnings = ($eventTeamId == $battingTeamId);
            } elseif ($eventType === 'extra') {
                $bowlerAppearanceId = $payload['bowler_appearance_id'] ?? null;
                if ($bowlerAppearanceId) {
                    $bowlerSql = "SELECT team_id FROM player_appearances WHERE appearance_id = :appearance_id";
                    $bowlerStmt = $db->prepare($bowlerSql);
                    $bowlerStmt->execute(['appearance_id' => $bowlerAppearanceId]);
                    $bowlerData = $bowlerStmt->fetch();
                    $belongsToInnings = ($bowlerData && $bowlerData['team_id'] == $bowlingTeamId);
                }
            }
        }
    }
    
    if ($belongsToInnings) {
        $currentInningsEvents[] = $event;
    }
}

// Calculate current stats
$totalRuns = 0;
$totalWickets = 0;
$legalBalls = 0;
$battingStats = [];
$bowlingStats = [];
$currentBowlerAppearanceId = null;
$currentStrikerAppearanceId = null;
$currentNonStrikerAppearanceId = null;
$lastEventBowlerId = null;
$lastEventStrikerId = null;
$dismissedPlayers = [];

foreach ($currentInningsEvents as $event) {
    $payload = json_decode($event['payload_json'], true);
    if (!is_array($payload)) continue;
    
    $eventType = $payload['event_type'] ?? '';
    $appearanceId = $event['appearance_id'] ?? null;
    
    // Update current bowler
    if (isset($payload['bowler_appearance_id'])) {
        $currentBowlerAppearanceId = $payload['bowler_appearance_id'];
        $lastEventBowlerId = $currentBowlerAppearanceId;
    }
    
    if ($eventType === 'runs' && $appearanceId) {
        $runs = $payload['runs'] ?? 0;
        
        // Track current striker
        $currentStrikerAppearanceId = $appearanceId;
        $lastEventStrikerId = $appearanceId;
        
        $totalRuns += $runs;
        $legalBalls++;
        
        if (!isset($battingStats[$appearanceId])) {
            $battingStats[$appearanceId] = [
                'appearance_id' => $appearanceId,
                'player_name' => $event['player_name'] ?? 'Unknown',
                'runs' => 0,
                'balls' => 0,
                'fours' => 0,
                'sixes' => 0
            ];
        }
        
        $battingStats[$appearanceId]['runs'] += $runs;
        $battingStats[$appearanceId]['balls'] += 1;
        if ($runs === 4) $battingStats[$appearanceId]['fours']++;
        if ($runs === 6) $battingStats[$appearanceId]['sixes']++;
    } elseif ($eventType === 'wicket') {
        // For wicket, the appearance_id in event is the NEW batsman coming in
        // But the ball should be counted for the OUT batsman
        // Check payload for out_batsman_appearance_id first
        $outBatsmanAppearanceId = $payload['out_batsman_appearance_id'] ?? null;
        
        // If out_batsman_appearance_id is not available, use current striker
        // (who was batting when wicket fell)
        if (!$outBatsmanAppearanceId && $currentStrikerAppearanceId) {
            $outBatsmanAppearanceId = $currentStrikerAppearanceId;
        }
        
        // If still no out batsman found, we can't reliably know who got out
        $dismissedPlayerName = 'Unknown';
        if ($outBatsmanAppearanceId) {
            // Get dismissed batsman's name
            $outBatsmanSql = "SELECT pa.player_id, p.name as player_name 
                             FROM player_appearances pa 
                             JOIN players p ON pa.player_id = p.player_id 
                             WHERE pa.appearance_id = :appearance_id";
            $outBatsmanStmt = $db->prepare($outBatsmanSql);
            $outBatsmanStmt->execute(['appearance_id' => $outBatsmanAppearanceId]);
            $outBatsmanData = $outBatsmanStmt->fetch();
            
            if ($outBatsmanData) {
                $dismissedPlayerName = $outBatsmanData['player_name'];
            }
            
            // Count the ball for the OUT batsman (not the new batsman)
            if (!isset($battingStats[$outBatsmanAppearanceId])) {
                $battingStats[$outBatsmanAppearanceId] = [
                    'appearance_id' => $outBatsmanAppearanceId,
                    'player_name' => $dismissedPlayerName,
                    'runs' => 0,
                    'balls' => 0,
                    'fours' => 0,
                    'sixes' => 0,
                    'out' => true
                ];
            }
            
            $battingStats[$outBatsmanAppearanceId]['balls'] += 1;
            $battingStats[$outBatsmanAppearanceId]['out'] = true;
            $battingStats[$outBatsmanAppearanceId]['dismissal'] = $payload['wicket_type'] ?? 'b';
            
            // Track dismissed player
            $dismissedPlayers[] = $outBatsmanAppearanceId;
        }
        
        // The new batsman (event.appearance_id) should NOT get this ball counted
        // They start with 0 balls until they face their first delivery
        // But we should initialize their stats entry if it doesn't exist
        $newBatsmanAppearanceId = $payload['new_batsman_appearance_id'] ?? $appearanceId ?? null;
        if ($newBatsmanAppearanceId) {
            // Check if new batsman already has stats (might have batted earlier in match)
            // If they do, don't reinitialize - just use existing stats
            // If they don't, initialize with 0 balls
            if (!isset($battingStats[$newBatsmanAppearanceId])) {
                // Get new batsman's name
                $newBatsmanSql = "SELECT pa.player_id, p.name as player_name 
                                 FROM player_appearances pa 
                                 JOIN players p ON pa.player_id = p.player_id 
                                 WHERE pa.appearance_id = :appearance_id";
                $newBatsmanStmt = $db->prepare($newBatsmanSql);
                $newBatsmanStmt->execute(['appearance_id' => $newBatsmanAppearanceId]);
                $newBatsmanData = $newBatsmanStmt->fetch();
                
                if ($newBatsmanData) {
                    $battingStats[$newBatsmanAppearanceId] = [
                        'appearance_id' => $newBatsmanAppearanceId,
                        'player_name' => $newBatsmanData['player_name'],
                        'runs' => 0,
                        'balls' => 0, // CRITICAL: Start with 0 balls - they haven't faced a delivery yet
                        'fours' => 0,
                        'sixes' => 0
                        // Not marked as out - they're the new batsman coming in
                    ];
                }
            } else {
                // If new batsman already has stats, ensure they haven't been incorrectly given a ball count
                // This shouldn't happen, but as a safeguard, if they have 0 runs and somehow got a ball
                // from the wicket event, reset it to 0
                // Actually, if they have existing stats, don't modify them - they might have batted earlier
            }
        }
        
        // Update current striker to new batsman (after wicket, new batsman comes in)
        if ($newBatsmanAppearanceId) {
            $currentStrikerAppearanceId = $newBatsmanAppearanceId;
            $lastEventStrikerId = $newBatsmanAppearanceId;
        }
        
        $totalWickets++;
        $legalBalls++;
    } elseif ($eventType === 'extra') {
        $runs = $payload['runs'] ?? 0;
        $totalRuns += $runs;
        
        // Only byes and leg-byes count as legal balls (they're legal deliveries)
        // Wides and no-balls don't count as legal deliveries (they get rebowled)
        if ($payload['extra_type'] === 'bye' || $payload['extra_type'] === 'legbye') {
            $legalBalls++;
            // Note: Byes and leg-byes are legal deliveries but batsmen don't face them
            // So they count in $legalBalls but NOT in individual batsman balls
        }
        // Penalty runs don't count as balls either
    }
    
    // Bowling stats
    if ($currentBowlerAppearanceId && ($eventType === 'runs' || $eventType === 'wicket' || $eventType === 'extra')) {
        $bowlerSql = "SELECT pa.player_id, p.name as player_name 
                     FROM player_appearances pa 
                     JOIN players p ON pa.player_id = p.player_id 
                     WHERE pa.appearance_id = :appearance_id";
        $bowlerStmt = $db->prepare($bowlerSql);
        $bowlerStmt->execute(['appearance_id' => $currentBowlerAppearanceId]);
        $bowlerData = $bowlerStmt->fetch();
        
        if ($bowlerData) {
            if (!isset($bowlingStats[$currentBowlerAppearanceId])) {
                $bowlingStats[$currentBowlerAppearanceId] = [
                    'appearance_id' => $currentBowlerAppearanceId,
                    'player_name' => $bowlerData['player_name'],
                    'overs' => 0,
                    'maidens' => 0,
                    'runs' => 0,
                    'wickets' => 0,
                    'legal_balls' => 0,
                    'noballs' => 0,
                    'wides' => 0
                ];
            }
            
            if ($eventType === 'runs') {
                $bowlingStats[$currentBowlerAppearanceId]['runs'] += ($payload['runs'] ?? 0);
                $bowlingStats[$currentBowlerAppearanceId]['legal_balls']++;
            } elseif ($eventType === 'wicket') {
                $bowlingStats[$currentBowlerAppearanceId]['wickets']++;
                $bowlingStats[$currentBowlerAppearanceId]['legal_balls']++;
            } elseif ($eventType === 'extra') {
                $bowlingStats[$currentBowlerAppearanceId]['runs'] += ($payload['runs'] ?? 0);
                if ($payload['extra_type'] === 'noball') {
                    $bowlingStats[$currentBowlerAppearanceId]['noballs']++;
                } elseif ($payload['extra_type'] === 'wide') {
                    $bowlingStats[$currentBowlerAppearanceId]['wides']++;
                } else {
                    $bowlingStats[$currentBowlerAppearanceId]['legal_balls']++;
                }
            }
        }
    }
}

// Calculate overs
$overs = floor($legalBalls / 6) + ($legalBalls % 6) / 10;

// Calculate strike rates
// Also ensure new batsmen who haven't faced deliveries have 0 balls
foreach ($battingStats as &$stat) {
    // Safeguard: If batsman has 0 runs and 0 balls (new batsman), ensure balls stays at 0
    // This prevents any edge case where they might get incorrectly counted
    if ($stat['runs'] == 0 && isset($stat['balls']) && $stat['balls'] > 0) {
        // Check if this is a new batsman who hasn't faced any deliveries
        // If they have 0 runs but balls > 0, this might be an error - reset to 0
        // However, we should be careful - if they're out on 0, they should have balls
        // So only reset if they're NOT out
        if (!isset($stat['out']) || !$stat['out']) {
            // New batsman who hasn't faced delivery shouldn't have balls
            $stat['balls'] = 0;
        }
    }
    
    // Calculate strike rate
    if ($stat['balls'] > 0) {
        $stat['strike_rate'] = round(($stat['runs'] / $stat['balls']) * 100, 2);
    } else {
        $stat['strike_rate'] = 0;
    }
}

// Calculate economy and overs for bowling
foreach ($bowlingStats as &$stat) {
    $stat['overs'] = floor($stat['legal_balls'] / 6) + ($stat['legal_balls'] % 6) / 10;
    $stat['economy'] = $stat['overs'] > 0 ? round($stat['runs'] / $stat['overs'], 2) : 0;
}

// Get current batters (not dismissed, ordered by last appearance)
$currentBatters = [];
$batterIds = [];
$lastStrikerId = null;
$lastNonStrikerId = null;

// Track striker and non-striker from events
if ($battingTeamId && !empty($currentInningsEvents)) {
    // Find last striker from runs events
    foreach (array_reverse($currentInningsEvents) as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        if ($payload['event_type'] === 'runs' && $event['appearance_id']) {
            $appearanceId = $event['appearance_id'];
            if (!in_array($appearanceId, $dismissedPlayers)) {
                $lastStrikerId = $appearanceId;
                break;
            }
        }
    }
    
    // Try to find non-striker from events (previous striker or from payload)
    foreach (array_reverse($currentInningsEvents) as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        // Check if non_striker_appearance_id is saved
        if (isset($payload['non_striker_appearance_id'])) {
            $nonStrikerId = $payload['non_striker_appearance_id'];
            if (!in_array($nonStrikerId, $dismissedPlayers) && $nonStrikerId != $lastStrikerId) {
                $lastNonStrikerId = $nonStrikerId;
                break;
            }
        }
    }
    
    // If no explicit non-striker, find second most recent batter
    if (!$lastNonStrikerId && $lastStrikerId) {
        $foundFirst = false;
        foreach (array_reverse($currentInningsEvents) as $event) {
            $payload = json_decode($event['payload_json'], true);
            if (!is_array($payload)) continue;
            
            if ($payload['event_type'] === 'runs' && $event['appearance_id']) {
                $appearanceId = $event['appearance_id'];
                if ($appearanceId == $lastStrikerId) {
                    $foundFirst = true;
                    continue;
                }
                if ($foundFirst && !in_array($appearanceId, $dismissedPlayers) && $appearanceId != $lastStrikerId) {
                    $lastNonStrikerId = $appearanceId;
                    break;
                }
            }
        }
    }
    
    // Add batters in order (striker first, then non-striker)
    if ($lastStrikerId && isset($battingStats[$lastStrikerId])) {
        $currentBatters[] = $battingStats[$lastStrikerId];
    }
    if ($lastNonStrikerId && isset($battingStats[$lastNonStrikerId]) && $lastNonStrikerId != $lastStrikerId) {
        $currentBatters[] = $battingStats[$lastNonStrikerId];
    }
    
    // If we still don't have batters, get any players with stats who aren't dismissed
    // But only include them if they have actually faced a delivery (balls > 0) OR they have runs
    // This ensures new batsmen who haven't faced any deliveries yet aren't shown with incorrect stats
    if (empty($currentBatters)) {
        foreach ($battingStats as $appearanceId => $stat) {
            if (!in_array($appearanceId, $dismissedPlayers)) {
                // Only include if batsman has faced at least one delivery (balls > 0) or has scored runs
                // This prevents showing new batsmen with 0 balls before they face any delivery
                if ($stat['balls'] > 0 || $stat['runs'] > 0) {
                    $currentBatters[] = $stat;
                    if (count($currentBatters) >= 2) break;
                }
            }
        }
    }
    
    // Handle case where new batsman is set as striker from wicket event but hasn't faced any deliveries yet
    // We should still show them if they're the current striker, but ensure they have 0 balls
    // Check if lastEventStrikerId is set from wicket but not found in currentBatters
    if ($lastEventStrikerId && !empty($battingStats[$lastEventStrikerId])) {
        $foundInCurrentBatters = false;
        foreach ($currentBatters as $batter) {
            if ($batter['appearance_id'] == $lastEventStrikerId) {
                $foundInCurrentBatters = true;
                break;
            }
        }
        
        // If striker from wicket event is not in currentBatters and hasn't faced deliveries
        // They should still be shown as striker with 0 (0)
        if (!$foundInCurrentBatters && !in_array($lastEventStrikerId, $dismissedPlayers)) {
            $newBatsmanStat = $battingStats[$lastEventStrikerId];
            // CRITICAL: Ensure new batsman has 0 balls until they face their first delivery
            // Force reset to 0 if somehow they have balls but no runs (shouldn't happen)
            if ($newBatsmanStat['runs'] == 0 && $newBatsmanStat['balls'] > 0) {
                // This shouldn't happen, but as a safeguard, reset balls to 0
                $newBatsmanStat['balls'] = 0;
                $newBatsmanStat['strike_rate'] = 0;
                $battingStats[$lastEventStrikerId] = $newBatsmanStat; // Update in main stats array
            }
            // Add to current batters - new batsman should be shown even with 0 balls
            if ($newBatsmanStat['balls'] == 0 && $newBatsmanStat['runs'] == 0) {
                $currentBatters = array_merge([$newBatsmanStat], $currentBatters);
            }
        }
    }
}

// Get current bowler
$currentBowler = null;
if ($lastEventBowlerId && isset($bowlingStats[$lastEventBowlerId])) {
    $currentBowler = $bowlingStats[$lastEventBowlerId];
}

// Calculate extras (byes, leg byes, wides, no-balls)
$extrasTotal = 0;
$extrasByes = 0;
$extrasLegByes = 0;
$extrasWides = 0;
$extrasNoballs = 0;
$extrasPenalty = 0;

foreach ($currentInningsEvents as $event) {
    $payload = json_decode($event['payload_json'], true);
    if (!is_array($payload)) continue;
    
    if ($payload['event_type'] === 'extra') {
        $runs = $payload['runs'] ?? 0;
        $extrasTotal += $runs;
        
        $extraType = $payload['extra_type'] ?? '';
        if ($extraType === 'bye') {
            $extrasByes += $runs;
        } elseif ($extraType === 'legbye') {
            $extrasLegByes += $runs;
        } elseif ($extraType === 'wide') {
            $extrasWides += $runs;
        } elseif ($extraType === 'noball') {
            $extrasNoballs += $runs;
        } elseif ($extraType === 'penalty') {
            $extrasPenalty += $runs;
        }
    }
}

// Get all batting team players to show "Yet to Bat"
$allBattingTeamPlayers = [];
if ($battingTeamId) {
    $sql = "SELECT pa.appearance_id, p.name as player_name, p.player_id
            FROM player_appearances pa
            JOIN players p ON pa.player_id = p.player_id
            WHERE pa.match_id = :match_id AND pa.team_id = :team_id
            ORDER BY p.name";
    $stmt = $db->prepare($sql);
    $stmt->execute(['match_id' => $matchId, 'team_id' => $battingTeamId]);
    $allBattingTeamPlayers = $stmt->fetchAll();
}

// Find players who have batted (have stats) or are dismissed
$battedPlayers = array_keys($battingStats);
$allBattedAppearanceIds = array_merge($battedPlayers, $dismissedPlayers);

// Yet to bat players
$yetToBatPlayers = [];
foreach ($allBattingTeamPlayers as $player) {
    if (!in_array($player['appearance_id'], $allBattedAppearanceIds)) {
        $yetToBatPlayers[] = $player;
    }
}

// Calculate powerplay runs (first 6 overs = 36 balls in T20, or first 10 overs = 60 balls in ODIs)
$powerplayOvers = ($matchData['overs_per_innings'] ?? 20) <= 20 ? 6 : 10;
$powerplayBalls = $powerplayOvers * 6;
$powerplayRuns = 0;
$powerplayLegalBalls = 0;

foreach ($currentInningsEvents as $event) {
    $payload = json_decode($event['payload_json'], true);
    if (!is_array($payload)) continue;
    
    // Count legal balls for powerplay
    if ($powerplayLegalBalls >= $powerplayBalls) {
        break;
    }
    
    if ($payload['event_type'] === 'runs') {
        $powerplayRuns += ($payload['runs'] ?? 0);
        $powerplayLegalBalls++;
    } elseif ($payload['event_type'] === 'wicket') {
        $powerplayLegalBalls++;
    } elseif ($payload['event_type'] === 'extra') {
        $powerplayRuns += ($payload['runs'] ?? 0);
        if ($payload['extra_type'] !== 'wide' && $payload['extra_type'] !== 'noball') {
            $powerplayLegalBalls++;
        }
    }
}

// Calculate partnerships (runs scored between wickets)
$partnerships = [];
$partnershipRuns = 0;
$partnershipBalls = 0;
$partnershipStartScore = 0;
$currentPartnershipBatsmen = [];
$wicketNumber = 0;

// Track all partnerships
foreach ($currentInningsEvents as $event) {
    $payload = json_decode($event['payload_json'], true);
    if (!is_array($payload)) continue;
    
    if ($payload['event_type'] === 'runs') {
        $partnershipRuns += ($payload['runs'] ?? 0);
        $partnershipBalls++;
        if ($event['appearance_id'] && isset($battingStats[$event['appearance_id']])) {
            $playerName = $battingStats[$event['appearance_id']]['player_name'];
            if (!in_array($playerName, $currentPartnershipBatsmen)) {
                $currentPartnershipBatsmen[] = $playerName;
            }
        }
    } elseif ($payload['event_type'] === 'extra') {
        $partnershipRuns += ($payload['runs'] ?? 0);
        if ($payload['extra_type'] !== 'wide' && $payload['extra_type'] !== 'noball') {
            $partnershipBalls++;
        }
    } elseif ($payload['event_type'] === 'wicket') {
        $wicketNumber++;
        if ($partnershipRuns > 0 || count($partnerships) == 0) {
            // Save current partnership
            $partnerships[] = [
                'wicket' => $wicketNumber,
                'runs' => $partnershipRuns,
                'balls' => $partnershipBalls,
                'batsmen' => array_slice($currentPartnershipBatsmen, -2, 2),
                'score_at' => $totalRuns
            ];
        }
        // Reset for next partnership
        $partnershipRuns = 0;
        $partnershipBalls = 0;
        $currentPartnershipBatsmen = [];
        $partnershipStartScore = $totalRuns;
    }
}

// Current partnership (last one - not yet completed)
if (!empty($currentInningsEvents)) {
    $currentBatsmen = [];
    foreach ($currentPartnershipBatsmen as $name) {
        foreach ($battingStats as $appearanceId => $batter) {
            if ($batter['player_name'] == $name && (!isset($batter['out']) || !$batter['out'])) {
                $currentBatsmen[] = $batter;
            }
        }
    }
    
    // Get current batters if partnership batsmen not found
    if (empty($currentBatsmen) && !empty($currentBatters)) {
        $currentBatsmen = $currentBatters;
    }
    
    if ($partnershipRuns > 0 || count($partnerships) == 0) {
        $partnerships[] = [
            'wicket' => count($partnerships) + 1,
            'runs' => $partnershipRuns,
            'balls' => $partnershipBalls,
            'batsmen' => array_map(function($b) { return $b['player_name']; }, array_slice($currentBatsmen, 0, 2)),
            'score_at' => $totalRuns
        ];
    }
}

// Calculate innings 1 total for target display (if innings 2)
// Helper function to calculate innings data (from match-view.php)
function calculateInningsData($events, $battingTeamId, $bowlingTeamId, $inningsNum, $innings2StartSeq, $db) {
    $data = ['total_runs' => 0, 'wickets' => 0, 'overs' => 0, 'legal_balls' => 0];
    
    if (!$battingTeamId || !$bowlingTeamId) return $data;
    
    $legalBalls = 0;
    
    foreach ($events as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        $eventTeamId = $event['team_id'] ?? null;
        $eventType = $payload['event_type'] ?? '';
        $eventSeq = $event['assigned_server_seq'] ?? 0;
        
        $belongsToInnings = false;
        
        if ($inningsNum === 1) {
            if ($innings2StartSeq === null || $eventSeq < $innings2StartSeq) {
                if ($eventType === 'runs' || $eventType === 'wicket') {
                    $belongsToInnings = ($eventTeamId == $battingTeamId);
                }
            }
        } else {
            if ($innings2StartSeq !== null && $eventSeq >= $innings2StartSeq) {
                if ($eventType === 'runs' || $eventType === 'wicket') {
                    $belongsToInnings = ($eventTeamId == $battingTeamId);
                }
            }
        }
        
        if (!$belongsToInnings) continue;
        
        if ($eventType === 'runs') {
            $data['total_runs'] += ($payload['runs'] ?? 0);
            $legalBalls++;
        } elseif ($eventType === 'wicket') {
            $data['wickets']++;
            $legalBalls++;
        } elseif ($eventType === 'extra') {
            $data['total_runs'] += ($payload['runs'] ?? 0);
            if ($payload['extra_type'] !== 'wide' && $payload['extra_type'] !== 'noball') {
                $legalBalls++;
            }
        }
    }
    
    $data['legal_balls'] = $legalBalls;
    $data['overs'] = floor($legalBalls / 6) + ($legalBalls % 6) / 10;
    
    return $data;
}

$innings1Total = null;
if ($currentInnings === 2) {
    // Determine which team batted in innings 1 (opposite of current batting team)
    $innings1BattingTeamId = ($battingTeamId == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
    $innings1BowlingTeamId = $battingTeamId; // Current batting team bowled in innings 1
    
    $innings1Data = calculateInningsData($allEvents, $innings1BattingTeamId, $innings1BowlingTeamId, 1, $innings2StartSeq, $db);
    if ($innings1Data && isset($innings1Data['total_runs'])) {
        $innings1Total = $innings1Data['total_runs'];
    }
}

// Get target for innings 2
$target = null;
if ($currentInnings === 2 && $innings1Total !== null) {
    $target = $innings1Total + 1; // Target is innings 1 total + 1
}

// Calculate current run rate
$currentRunRate = $overs > 0 ? round($totalRuns / $overs, 2) : 0;

// Calculate partnership (runs since last wicket)
$partnershipRuns = 0;
$partnershipBalls = 0;
$lastWicketSeq = 0;
foreach (array_reverse($currentInningsEvents) as $event) {
    $payload = json_decode($event['payload_json'], true);
    if (!is_array($payload)) continue;
    
    if ($payload['event_type'] === 'wicket') {
        break;
    }
    
    if ($payload['event_type'] === 'runs') {
        $partnershipRuns += ($payload['runs'] ?? 0);
        $partnershipBalls++;
    } elseif ($payload['event_type'] === 'extra') {
        $partnershipRuns += ($payload['runs'] ?? 0);
        if ($payload['extra_type'] !== 'wide' && $payload['extra_type'] !== 'noball') {
            $partnershipBalls++;
        }
    }
}

// Calculate overs left
$maxOvers = (float)($matchData['overs_per_innings'] ?? 20);
$oversLeft = max(0, $maxOvers - $overs);

// Format overs display
function formatOvers($overs) {
    $full = floor($overs);
    $decimals = round(($overs - $full) * 10);
    return $full . '.' . $decimals;
}

// Get team abbreviation (first 4 chars)
function getTeamAbbr($name) {
    $words = explode(' ', $name);
    $abbr = '';
    foreach ($words as $word) {
        $abbr .= strtoupper(substr($word, 0, 1));
    }
    return strlen($abbr) > 4 ? substr($abbr, 0, 4) : $abbr;
}

$battingTeamAbbr = $battingTeamName ? getTeamAbbr($battingTeamName) : 'T1';

// Function to render commentary similar to displayCommentary in scoring.js
function renderCommentary($events, $db, $matchId) {
    if (empty($events)) {
        return '<div class="loading">No commentary yet</div>';
    }
    
    // Get all unique appearance IDs from events
    $appearanceIds = [];
    foreach ($events as $event) {
        $appearanceId = $event['appearance_id'] ?? null;
        if ($appearanceId) $appearanceIds[] = $appearanceId;
        
        $payload = json_decode($event['payload_json'] ?? '{}', true);
        $bowlerId = $payload['bowler_appearance_id'] ?? null;
        if ($bowlerId) $appearanceIds[] = $bowlerId;
    }
    // Remove duplicates and re-index array for PDO (must be sequential numeric keys)
    $appearanceIds = array_values(array_unique(array_filter($appearanceIds)));
    
    // Build player map in one query
    $playerMap = [];
    if (!empty($appearanceIds)) {
        $placeholders = implode(',', array_fill(0, count($appearanceIds), '?'));
        $sql = "SELECT pa.appearance_id, p.name as player_name 
                FROM player_appearances pa
                JOIN players p ON pa.player_id = p.player_id
                WHERE pa.appearance_id IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($appearanceIds);
        while ($player = $stmt->fetch()) {
            $playerMap[$player['appearance_id']] = $player['player_name'];
        }
    }
    
    // Events are already in ASC order (oldest first) from the database
    // Process them in this order to calculate over/ball correctly
    // Track over/ball as we process events
    $currentOverNum = 0;
    $currentBallNum = 0;
    
    // Process events and build commentary items
    $commentaryItems = [];
    foreach ($events as $event) {
        $payload = json_decode($event['payload_json'] ?? '{}', true);
        if (!is_array($payload)) continue;
        
        $isLegalBall = $payload['event_type'] !== 'extra' || 
                      ($payload['extra_type'] !== 'wide' && $payload['extra_type'] !== 'noball');
        
        $overDisplay = $currentOverNum;
        $ballDisplay = $currentBallNum + 1;
        
        if ($isLegalBall) {
            $currentBallNum++;
            if ($currentBallNum > 6) {
                $currentBallNum = 1;
                $currentOverNum++;
            }
            $ballDisplay = $currentBallNum;
            $overDisplay = $currentOverNum;
        }
        
        // Get player names
        $batsmanId = $event['appearance_id'] ?? $payload['appearance_id'] ?? null;
        $bowlerId = $payload['bowler_appearance_id'] ?? null;
        $batsmanName = ($batsmanId && isset($playerMap[$batsmanId])) ? $playerMap[$batsmanId] : ($event['player_name'] ?? 'Unknown');
        $bowlerName = ($bowlerId && isset($playerMap[$bowlerId])) ? $playerMap[$bowlerId] : 'Unknown';
        
        // Build event description
        $eventType = '';
        $eventDescription = '';
        $visualCircle = '';
        $dismissalDetails = '';
        
        if ($payload['event_type'] === 'runs') {
            $runs = $payload['runs'] ?? 0;
            if ($runs === 0) {
                $eventType = 'no run';
            } elseif ($runs === 4) {
                $eventType = 'FOUR';
            } elseif ($runs === 6) {
                $eventType = 'SIX';
            } else {
                $eventType = $runs . ' run' . ($runs !== 1 ? 's' : '');
            }
            
            if ($runs === 6) {
                $visualCircle = '<span class="commentary-circle" style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background: #9c27b0; color: white; text-align: center; line-height: 28px; font-weight: bold; margin-right: 8px; font-size: 14px;">6</span>';
            } else if ($runs === 4) {
                $visualCircle = '<span class="commentary-circle" style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background: #2196F3; color: white; text-align: center; line-height: 28px; font-weight: bold; margin-right: 8px; font-size: 14px;">4</span>';
            }
            
            $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', <strong>' . $eventType . '</strong>';
        } else if ($payload['event_type'] === 'wicket') {
            $wicketType = $payload['wicket_type'] ?? 'Wicket';
            $eventType = 'OUT ' . strtoupper($wicketType) . '!!';
            $visualCircle = '<span class="commentary-circle" style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background: #f44336; color: white; text-align: center; line-height: 28px; font-weight: bold; margin-right: 8px; font-size: 14px;">W</span>';
            
            $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', <strong>out ' . $wicketType . '!!</strong>';
            $eventDescription .= '<br><span style="font-weight: bold; color: #f44336;">THAT\'S OUT!!</span> ' . strtoupper($wicketType) . '!!';
            
            // Get dismissed batsman details if available
            $outBatsmanId = $payload['out_batsman_appearance_id'] ?? $batsmanId;
            if ($outBatsmanId && isset($playerMap[$outBatsmanId])) {
                $outBatsmanName = $playerMap[$outBatsmanId];
                $dismissalDetails = '<div class="commentary-dismissal" style="margin: 8px 0; padding: 8px; background: #ffebee; border-left: 3px solid #f44336; border-radius: 4px; font-size: 0.9em;">' .
                    htmlspecialchars($outBatsmanName) . ' ' . strtoupper($wicketType) . ' b ' . htmlspecialchars($bowlerName) .
                    '</div>';
            }
        } else if ($payload['event_type'] === 'extra') {
            $extraType = $payload['extra_type'] ?? 'extra';
            $runs = $payload['runs'] ?? 0;
            if ($extraType === 'wide') {
                $eventType = 'Wide';
            } elseif ($extraType === 'noball') {
                $eventType = 'No Ball';
            } else {
                $eventType = 'Extra';
            }
            
            if ($extraType === 'wide') {
                $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', <strong>Wide</strong>' . ($runs > 1 ? ', ' . $runs . ' runs' : '');
            } else if ($extraType === 'noball') {
                $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', <strong>No Ball</strong>' . ($runs > 1 ? ', ' . $runs . ' runs' : '');
            }
        }
        
        // Use custom commentary if available
        if (!empty($event['commentary']) && trim($event['commentary'])) {
            $eventDescription = $bowlerName . ' to ' . $batsmanName . ', ' . $event['commentary'];
        }
        
        $ballLabel = $isLegalBall ? $overDisplay . '.' . $ballDisplay : '';
        
        $commentaryItems[] = [
            'ballLabel' => $ballLabel,
            'visualCircle' => $visualCircle,
            'eventType' => $eventType,
            'eventDescription' => $eventDescription,
            'dismissalDetails' => $dismissalDetails
        ];
    }
    
    // Reverse to show newest first
    $commentaryItems = array_reverse($commentaryItems);
    
    // Build HTML
    $html = '';
    foreach ($commentaryItems as $item) {
        $html .= '<div class="commentary-item" style="margin-bottom: 16px; padding: 12px; border-left: 3px solid #e0e0e0; border-radius: 4px; background: #fafafa;">';
        $html .= '<div class="commentary-header" style="display: flex; align-items: center; margin-bottom: 8px;">';
        $html .= $item['visualCircle'];
        $html .= '<strong style="font-size: 1.1em; color: #333; margin-right: 8px;">' . htmlspecialchars($item['ballLabel']) . '</strong>';
        $html .= '<span style="font-weight: bold; color: #1976d2;">' . htmlspecialchars(strtoupper($item['eventType'])) . '</span>';
        $html .= '</div>';
        $html .= '<div class="commentary-description" style="color: #555; line-height: 1.6; margin-bottom: 4px;">';
        $html .= $item['eventDescription'];
        $html .= '</div>';
        $html .= $item['dismissalDetails'];
        $html .= '</div>';
    }
    
    return $html;
}

// Get events for commentary (all events, oldest first for processing, then reverse for display)
$sql = "SELECT e.*, p.name as player_name 
        FROM events e
        LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
        LEFT JOIN players p ON pa.player_id = p.player_id
        WHERE e.match_id = :match_id
        ORDER BY e.assigned_server_seq ASC
        LIMIT 100";
$stmt = $db->prepare($sql);
$stmt->execute(['match_id' => $matchId]);
$commentaryEvents = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($matchData['team1_name']) ?> vs <?= htmlspecialchars($matchData['team2_name']) ?> - Live</title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/public.css">
    <link rel="stylesheet" href="/cricapp/assets/css/premium-design.css">
    <link rel="stylesheet" href="/cricapp/assets/css/live-match.css">
    <style>
        .scorecard-container {
            margin-top: 2rem;
        }
        
        .innings-section {
            margin-bottom: 3rem;
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* Commentary Section Styles - Matching match-view.php */
        .commentary-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        
        .innings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .innings-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .innings-score {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 0.75rem;
            margin-bottom: 0.5rem;
            padding-bottom: 0.375rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .scorecard-container {
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
        }
        
        .scorecard-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .scorecard-table thead {
            background: #f3f4f6;
        }
        
        .scorecard-table th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .scorecard-table th:last-child {
            text-align: right;
        }
        
        .scorecard-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
            color: #1f2937;
        }
        
        .scorecard-table td:last-child {
            text-align: right;
        }
        
        .scorecard-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .player-out {
            opacity: 0.7;
        }
        
        .player-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .player-dismissal {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: normal;
            margin-top: 0.25rem;
        }
        
        .live-nav-tabs {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .live-nav-tabs .tab:hover {
            color: #1e40af !important;
        }

        /* Compact Header Styles */
        .compact-header {
            position: sticky;
            top: 0;
            z-index: 200;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 8px 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #menu-toggle {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 1.25rem;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        #menu-toggle:hover {
            background: rgba(255,255,255,0.3);
        }

        #menu-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 180px;
            z-index: 201;
        }

        #menu-dropdown a {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.875rem;
        }

        #menu-dropdown a:last-child {
            border-bottom: none;
        }

        .match-stats-compact {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: nowrap;
        }

        .match-stats-compact > div {
            text-align: center;
            min-width: 50px;
        }

        .match-stats-compact > div > div:first-child {
            font-size: 0.6875rem;
            opacity: 0.9;
            margin-bottom: 0.125rem;
        }

        .match-stats-compact > div > div:last-child {
            font-size: 1rem;
            font-weight: 600;
        }

        .match-stats-compact > a {
            padding: 0.375rem 0.75rem;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .match-stats-compact > a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        @media (max-width: 768px) {
            .compact-header {
                padding: 6px 10px !important;
            }
            
            .compact-header div:first-child {
                font-size: 0.75rem !important;
            }
            
            #menu-toggle {
                padding: 4px 8px !important;
                font-size: 1.125rem !important;
            }
            
            #menu-dropdown {
                right: 0 !important;
                min-width: 160px !important;
            }
            
            #menu-dropdown a {
                padding: 10px 14px !important;
                font-size: 0.8125rem !important;
            }

            .live-match-nav {
                top: 40px !important;
                padding: 0.375rem 0.75rem !important;
            }

            .back-to-scorecard {
                font-size: 0.8125rem !important;
                padding: 0.5rem 0.75rem !important;
            }

            .container {
                padding: 0.5rem 0.75rem !important;
                max-width: 100% !important;
            }

            main.container {
                padding-bottom: 5rem !important;
            }

            .match-stats-compact {
                width: 100% !important;
                justify-content: space-between !important;
                gap: 0.375rem !important;
                flex-wrap: wrap !important;
            }

            .match-stats-compact > div {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                text-align: center !important;
                min-width: 45px !important;
            }

            .match-stats-compact > div > div:first-child {
                font-size: 0.625rem !important;
                margin-bottom: 0.125rem !important;
            }

            .match-stats-compact > div > div:last-child {
                font-size: 0.875rem !important;
                font-weight: 600 !important;
            }

            .match-stats-compact > a {
                width: 100% !important;
                margin-top: 0.375rem !important;
                text-align: center !important;
                padding: 0.5rem 0.75rem !important;
                font-size: 0.75rem !important;
            }

            .scorecard-container {
                margin-top: 0.5rem;
                margin-bottom: 0.75rem;
                padding: 0.5rem;
                background: white;
                border-radius: 8px;
            }

            .section-title {
                font-size: 0.8125rem;
                margin-top: 0;
                margin-bottom: 0.5rem;
                padding-bottom: 0.375rem;
            }

            /* Scorecard tables - scrollable */
            .scorecard-table {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                font-size: 0.75rem;
            }

            .scorecard-table thead {
                display: table;
                width: 100%;
                table-layout: auto;
            }

            .scorecard-table tbody {
                display: table;
                width: 100%;
                table-layout: auto;
            }
            
            .scorecard-table th,
            .scorecard-table td {
                padding: 0.5rem 0.375rem;
                font-size: 0.75rem;
                white-space: nowrap;
            }

            .scorecard-table th {
                font-size: 0.6875rem;
                padding: 0.625rem 0.375rem;
            }

            /* Sticky first column */
            .scorecard-table th:first-child,
            .scorecard-table td:first-child {
                position: sticky;
                left: 0;
                background: inherit;
                z-index: 1;
                min-width: 100px;
                white-space: normal;
                word-break: break-word;
            }

            .scorecard-table thead th:first-child {
                background: #f3f4f6;
            }

            .scorecard-table tbody tr:nth-child(even) td:first-child {
                background: white;
            }

            .scorecard-table tbody tr:hover td:first-child {
                background: #f9fafb;
            }

            .target-display {
                font-size: 0.75rem !important;
                padding: 0.5rem !important;
                margin-bottom: 0.5rem !important;
            }

            .extras-display,
            .total-display {
                font-size: 0.75rem !important;
                padding: 0.5rem !important;
            }

            .yet-to-bat {
                margin-top: 0.75rem !important;
            }

            .yet-to-bat > div:first-child {
                font-size: 0.75rem !important;
            }

            .yet-to-bat span {
                font-size: 0.6875rem !important;
            }

            .commentary-section {
                margin-top: 1rem;
                padding: 0.75rem;
            }

            .commentary-feed {
                max-height: 300px;
                padding: 0.75rem;
                font-size: 0.8125rem;
            }

            .commentary-item {
                margin-bottom: 0.75rem;
                padding: 0.75rem;
            }

            .commentary-circle {
                width: 24px;
                height: 24px;
                line-height: 24px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Header with Menu - Like Other Pages -->
    <header class="compact-header">
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; position: relative;">
            <div style="font-size: 0.875rem; font-weight: 600;">🏏 Cricket</div>
            <div style="position: relative;">
                <button id="menu-toggle" onclick="toggleMenu()">☰</button>
                <div id="menu-dropdown">
                    <a href="/cricapp/public/">🏠 Home</a>
                    <a href="/cricapp/public/leaderboard.php">🏆 Leaderboard</a>
                    <a href="/cricapp/public/matches.php">📅 Matches</a>
                    <a href="/cricapp/public/live.php">⚡ Live</a>
                </div>
            </div>
        </div>
    </header>
    
    <script>
        function toggleMenu() {
            const menu = document.getElementById('menu-dropdown');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('menu-dropdown');
            const toggle = document.getElementById('menu-toggle');
            if (menu && !menu.contains(event.target) && event.target !== toggle) {
                menu.style.display = 'none';
            }
        });
    </script>

    <!-- Simple Navigation - Back to match view -->
    <div class="live-match-nav" style="background: white; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 1rem; position: sticky; top: 40px; z-index: 100;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <a href="/cricapp/public/match-view.php?id=<?= $matchId ?>" class="back-to-scorecard" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; text-decoration: none; color: #2563eb; font-size: 0.875rem; font-weight: 500; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                ← Full Scorecard
            </a>
        </div>
    </div>

    <!-- Live Match Content -->
    <main class="container" style="padding: 0.75rem; max-width: 100%;">
        <!-- Target Display (for Innings 2) - Only show if match is not completed and target not achieved -->
        <?php 
        $matchCompleted = ($matchData['state'] === 'completed');
        $targetAchieved = ($currentInnings === 2 && $target !== null && $totalRuns >= $target);
        $targetFailed = ($currentInnings === 2 && $target !== null && $oversLeft <= 0 && $totalRuns < $target);
        $allWicketsDown = ($totalWickets >= 10);
        $shouldHideTarget = $matchCompleted || $targetAchieved || $targetFailed || $allWicketsDown;
        ?>
        <?php if ($currentInnings === 2 && $target !== null && $battingTeamName && !$shouldHideTarget): ?>
        <div class="target-display" style="background: #dc2626; color: white; padding: 6px; border-radius: 4px; margin-bottom: 8px; text-align: center; font-weight: 600; font-size: 12px;">
            <?= htmlspecialchars($battingTeamName) ?> need <?= max(0, $target - $totalRuns) ?> runs to win
        </div>
        <?php elseif ($targetAchieved): ?>
        <div class="target-display" style="background: #10b981; color: white; padding: 6px; border-radius: 4px; margin-bottom: 8px; text-align: center; font-weight: 600; font-size: 12px;">
            <?= htmlspecialchars($battingTeamName) ?> won by <?= (10 - $totalWickets) ?> wicket<?= (10 - $totalWickets) != 1 ? 's' : '' ?>
        </div>
        <?php elseif ($targetFailed || ($oversLeft <= 0 && $totalRuns < $target)): ?>
        <div class="target-display" style="background: #dc2626; color: white; padding: 6px; border-radius: 4px; margin-bottom: 8px; text-align: center; font-weight: 600; font-size: 12px;">
            <?= htmlspecialchars($battingTeamName) ?> lost by <?= ($target - $totalRuns) ?> run<?= ($target - $totalRuns) != 1 ? 's' : '' ?>
        </div>
        <?php endif; ?>

        <!-- Batting Scorecard - Show ALL batters -->
        <div class="scorecard-container">
            <div class="section-title">Batter</div>
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
                        <?php if (empty($battingStats)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 0.75rem; color: #666;">
                                No batting data available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Sort batting stats: current batters first (not dismissed), then dismissed by order
                        $sortedBatters = [];
                        $currentNotOut = [];
                        $dismissed = [];
                        
                        foreach ($battingStats as $appearanceId => $batter) {
                            if (isset($batter['out']) && $batter['out']) {
                                $dismissed[] = $batter;
                            } else {
                                $currentNotOut[] = $batter;
                            }
                        }
                        
                        // Current batters first (striker first if available)
                        if (!empty($currentBatters)) {
                            $sortedBatters = $currentBatters;
                            // Add other current batters not in currentBatters
                            foreach ($currentNotOut as $batter) {
                                $found = false;
                                foreach ($sortedBatters as $existing) {
                                    if ($existing['appearance_id'] == $batter['appearance_id']) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $sortedBatters[] = $batter;
                                }
                            }
                        } else {
                            $sortedBatters = $currentNotOut;
                        }
                        
                        // Add dismissed players
                        $sortedBatters = array_merge($sortedBatters, $dismissed);
                        ?>
                        
                        <?php foreach ($sortedBatters as $batter): ?>
                            <tr class="<?= (isset($batter['out']) && $batter['out']) ? 'player-out' : '' ?>">
                                <td>
                                    <span class="player-name<?= (in_array($batter['appearance_id'], [$lastStrikerId, $lastNonStrikerId]) && !(isset($batter['out']) && $batter['out'])) ? ' on-strike' : '' ?>">
                                        <?= htmlspecialchars($batter['player_name']) ?>
                                        <?php if (in_array($batter['appearance_id'], [$lastStrikerId, $lastNonStrikerId]) && !(isset($batter['out']) && $batter['out'])): ?>
                                            <span style="color: #2563eb;">*</span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if (isset($batter['out']) && $batter['out'] && isset($batter['dismissal'])): ?>
                                        <div style="font-size: 11px; color: #6b7280; font-weight: normal;">
                                            <?= strtoupper($batter['dismissal']) ?>
                                        </div>
                                    <?php elseif (!isset($batter['out']) || !$batter['out']): ?>
                                        <div style="font-size: 11px; color: #10b981; font-weight: normal;">
                                            not out
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= $batter['runs'] ?></td>
                                <td><?= $batter['balls'] ?></td>
                                <td><?= $batter['fours'] ?></td>
                                <td><?= $batter['sixes'] ?></td>
                                <td><?= number_format($batter['strike_rate'], 2) ?> →</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Extras -->
            <?php if ($extrasTotal > 0): ?>
            <div class="extras-display" style="margin-top: 8px; padding: 6px; background: #f9fafb; border-radius: 4px; font-size: 12px;">
                <strong>Extras:</strong> <?= $extrasTotal ?> 
                (b <?= $extrasByes ?>, lb <?= $extrasLegByes ?>, w <?= $extrasWides ?>, nb <?= $extrasNoballs ?><?= $extrasPenalty > 0 ? ', p ' . $extrasPenalty : '' ?>)
            </div>
            <?php endif; ?>
            
            <!-- Total -->
            <div class="total-display" style="margin-top: 6px; padding: 6px; background: #e5e7eb; border-radius: 4px; font-weight: 600; font-size: 13px;">
                <strong>Total:</strong> <?= $totalRuns ?><?= $totalWickets > 0 ? '-' . $totalWickets : '' ?> (<?= formatOvers($overs) ?> Overs, RR: <?= $currentRunRate ?>)
            </div>
            
            <!-- Yet to Bat -->
            <?php if (!empty($yetToBatPlayers)): ?>
            <div class="yet-to-bat" style="margin-top: 10px;">
                <div style="font-size: 11px; font-weight: 600; color: #374151; margin-bottom: 6px;">Yet to Bat:</div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    <?php foreach ($yetToBatPlayers as $player): ?>
                        <span style="font-size: 11px; color: #6b7280;"><?= htmlspecialchars($player['player_name']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Bowling Scorecard - Show ALL bowlers -->
        <?php if (!empty($bowlingStats)): ?>
        <div class="scorecard-container">
            <div class="section-title">Bowler</div>
            <table class="scorecard-table">
                <thead>
                    <tr>
                        <th>Bowler</th>
                        <th>O</th>
                        <th>M</th>
                        <th>R</th>
                        <th>W</th>
                        <th>NB</th>
                        <th>WD</th>
                        <th>ECO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Sort bowling stats: current bowler first, then by wickets desc
                    $sortedBowlers = [];
                    $otherBowlers = [];
                    
                    foreach ($bowlingStats as $appearanceId => $bowler) {
                        if ($appearanceId == $lastEventBowlerId) {
                            $sortedBowlers[] = $bowler;
                        } else {
                            $otherBowlers[] = $bowler;
                        }
                    }
                    
                    // Sort other bowlers by wickets desc, then economy asc
                    usort($otherBowlers, function($a, $b) {
                        if ($b['wickets'] != $a['wickets']) {
                            return $b['wickets'] - $a['wickets'];
                        }
                        return $a['economy'] - $b['economy'];
                    });
                    
                    $sortedBowlers = array_merge($sortedBowlers, $otherBowlers);
                    ?>
                    
                    <?php foreach ($sortedBowlers as $bowler): ?>
                        <tr>
                            <td>
                                <span class="player-name<?= ($bowler['appearance_id'] == $lastEventBowlerId) ? ' on-strike' : '' ?>">
                                    <?= htmlspecialchars($bowler['player_name']) ?>
                                    <?php if ($bowler['appearance_id'] == $lastEventBowlerId): ?>
                                        <span style="color: #2563eb;">*</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?= formatOvers($bowler['overs']) ?></td>
                            <td><?= $bowler['maidens'] ?></td>
                            <td><?= $bowler['runs'] ?></td>
                            <td><?= $bowler['wickets'] ?></td>
                            <td><?= isset($bowler['noballs']) ? $bowler['noballs'] : 0 ?></td>
                            <td><?= isset($bowler['wides']) ? $bowler['wides'] : 0 ?></td>
                            <td><?= number_format($bowler['economy'], 2) ?> →</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Powerplays Section -->
        <?php if ($powerplayRuns > 0): ?>
        <div class="scorecard-container">
            <div class="section-title">Powerplays</div>
            <table class="scorecard-table">
                <thead>
                    <tr>
                        <th>Overs</th>
                        <th>Runs</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>0.1 - <?= $powerplayOvers ?></td>
                        <td><?= $powerplayRuns ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Partnerships Section -->
        <?php if (!empty($partnerships)): ?>
        <div class="scorecard-container">
            <div class="section-title">Partnerships</div>
            <div class="partnerships-list">
                <?php foreach ($partnerships as $part): ?>
                    <div class="partnership-item" style="padding: 8px; margin-bottom: 8px; background: #f9fafb; border-radius: 4px; font-size: 13px;">
                        <?php if (!empty($part['batsmen'])): ?>
                            <strong><?= implode(' ', array_slice($part['batsmen'], 0, 2)) ?></strong>
                            <span style="color: #6b7280;">
                                <?php 
                                $batsman1 = !empty($part['batsmen'][0]) ? $part['batsmen'][0] : '';
                                $batsman2 = !empty($part['batsmen'][1]) ? $part['batsmen'][1] : '';
                                
                                // Find runs for each batsman in partnership
                                $batsman1Runs = 0;
                                $batsman2Runs = 0;
                                foreach ($battingStats as $appearanceId => $batter) {
                                    if ($batter['player_name'] == $batsman1) {
                                        $batsman1Runs = $batter['runs'];
                                    }
                                    if ($batter['player_name'] == $batsman2) {
                                        $batsman2Runs = $batter['runs'];
                                    }
                                }
                                ?>
                                <?php 
                                // Get balls for each batsman
                                $batsman1Balls = 0;
                                $batsman2Balls = 0;
                                foreach ($battingStats as $appearanceId => $batter) {
                                    if ($batter['player_name'] == $batsman1) {
                                        $batsman1Balls = $batter['balls'];
                                    }
                                    if ($batter['player_name'] == $batsman2) {
                                        $batsman2Balls = $batter['balls'];
                                    }
                                }
                                ?>
                                <div style="margin-top: 4px;">
                                    <?= $batsman1 ? htmlspecialchars($batsman1) . ' ' . $batsman1Runs . '(' . $batsman1Balls . ')' : '' ?>
                                    <?= $batsman2 ? htmlspecialchars($batsman2) . ' ' . $batsman2Runs . '(' . $batsman2Balls . ')' : '' ?>
                                </div>
                            </span>
                        <?php endif; ?>
                        <div style="margin-top: 4px; font-weight: 600; color: #374151;">
                            Partnership: <?= $part['runs'] ?>(<?= $part['balls'] ?? 0 ?>)
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Live Commentary Section -->
        <div class="commentary-section">
            <div class="section-title">Live Commentary</div>
            <div class="commentary-feed" id="commentary-feed">
                <?= renderCommentary($commentaryEvents, $db, $matchId) ?>
            </div>
        </div>

        <!-- Recent Updates -->
        <div class="scorecard-container">
            <div class="section-title">Recent Updates</div>
            <div class="updates-list">
                <?php 
                $recentEvents = array_slice(array_reverse($currentInningsEvents), 0, 5);
                foreach ($recentEvents as $event): 
                    $payload = json_decode($event['payload_json'], true);
                    if (!is_array($payload)) continue;
                ?>
                    <div class="update-item">
                        <?php
                        $updateText = '';
                        if ($payload['event_type'] === 'runs') {
                            $updateText = ($payload['runs'] ?? 0) . ' run' . (($payload['runs'] ?? 0) != 1 ? 's' : '');
                            if (isset($event['player_name'])) {
                                $updateText = $event['player_name'] . ': ' . $updateText;
                            }
                        } elseif ($payload['event_type'] === 'wicket') {
                            $updateText = 'Wicket!';
                            if (isset($event['player_name'])) {
                                $updateText = $event['player_name'] . ' ' . $updateText;
                            }
                        } elseif ($payload['event_type'] === 'extra') {
                            $updateText = ucfirst($payload['extra_type'] ?? 'Extra');
                            if (isset($payload['runs']) && $payload['runs'] > 0) {
                                $updateText .= ' - ' . $payload['runs'] . ' run' . ($payload['runs'] != 1 ? 's' : '');
                            }
                        }
                        ?>
                        <?= htmlspecialchars($updateText) ?>
                        <?php if (isset($event['created_at'])): ?>
                            <span class="update-time">(<?= date('h:i A', strtotime($event['created_at'])) ?>)</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav" id="bottom-nav">
        <div class="bottom-nav-container">
            <a href="/cricapp/public/" class="bottom-nav-item">
                <span class="bottom-nav-icon">🏠</span>
                <span class="bottom-nav-label">Home</span>
            </a>
            <a href="/cricapp/public/matches.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">📅</span>
                <span class="bottom-nav-label">Matches</span>
            </a>
            <a href="/cricapp/public/live.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">⚡</span>
                <span class="bottom-nav-label">Live</span>
            </a>
            <a href="/cricapp/public/leaderboard.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">🏆</span>
                <span class="bottom-nav-label">Leaderboard</span>
            </a>
            <a href="/cricapp/public/profile.php" class="bottom-nav-item">
                <span class="bottom-nav-icon">👤</span>
                <span class="bottom-nav-label">Profile</span>
            </a>
        </div>
    </nav>

    <script src="/cricapp/assets/js/api.js"></script>
    <script src="/cricapp/assets/js/live-match.js"></script>
</body>
</html>
