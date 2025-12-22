<?php
/**
 * Live Match View - Vue.js Version
 * Modern real-time match view with Vue.js
 */

require_once __DIR__ . '/../includes/bootstrap.php';

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

// Get all events - same logic as live-match.php
$db = Database::getInstance()->getConnection();
$currentInnings = (int)($matchData['current_innings'] ?? 1);

// Determine batting and bowling teams
$battingTeamId = null;
$bowlingTeamId = null;
$battingTeamName = null;
$bowlingTeamName = null;

// If no toss data, default to team1 batting first  
if (!$matchData['toss_winner_id'] || !$matchData['toss_decision']) {
    // Default: team1 bats first
    $battingTeamId = $matchData['team1_id'] ?? null;
    $bowlingTeamId = $matchData['team2_id'] ?? null;
    $battingTeamName = $matchData['team1_name'] ?? '';
    $bowlingTeamName = $matchData['team2_name'] ?? '';
}

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

// Get all events for current innings calculation
$sql = "SELECT e.*, pa.team_id, pa.player_id, p.name as player_name
        FROM events e
        LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
        LEFT JOIN players p ON pa.player_id = p.player_id
        WHERE e.match_id = :match_id
        ORDER BY e.assigned_server_seq ASC";
$stmt = $db->prepare($sql);
$stmt->execute(['match_id' => $matchId]);
$allEvents = $stmt->fetchAll();

// Find when innings 2 starts (if in innings 2, find when current batting team started batting)
$innings2StartSeq = null;
if ($currentInnings === 2 && $battingTeamId) {
    // First, determine which team batted in innings 1 (opposite of current batting team)
    $innings1BattingTeamId = ($battingTeamId == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
    
    // Find the last event of innings 1 to determine when innings 2 starts
    $lastInnings1Seq = null;
    foreach ($allEvents as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        $eventTeamId = $event['team_id'] ?? null;
        $eventType = $payload['event_type'] ?? '';
        
        // Find last event where innings 1 team bats
        if ($eventTeamId == $innings1BattingTeamId && ($eventType === 'runs' || $eventType === 'wicket')) {
            $lastInnings1Seq = $event['assigned_server_seq'];
        }
    }
    
    // Now find first event where current batting team bats (innings 2)
    if ($lastInnings1Seq !== null) {
        foreach ($allEvents as $event) {
            $payload = json_decode($event['payload_json'], true);
            if (!is_array($payload)) continue;
            
            $eventTeamId = $event['team_id'] ?? null;
            $eventType = $payload['event_type'] ?? '';
            $eventSeq = $event['assigned_server_seq'] ?? 0;
            
            // Find first event after innings 1 where current batting team bats
            if ($eventSeq > $lastInnings1Seq && $eventTeamId == $battingTeamId && ($eventType === 'runs' || $eventType === 'wicket')) {
                $innings2StartSeq = $eventSeq;
                break;
            }
        }
    } else {
        // If no innings 1 events found, try direct method
        foreach ($allEvents as $event) {
            $payload = json_decode($event['payload_json'], true);
            if (!is_array($payload)) continue;
            
            $eventTeamId = $event['team_id'] ?? null;
            $eventType = $payload['event_type'] ?? '';
            
            // Find first event where current batting team bats
            if ($eventTeamId == $battingTeamId && ($eventType === 'runs' || $eventType === 'wicket')) {
                $innings2StartSeq = $event['assigned_server_seq'];
                break;
            }
        }
    }
}

// Filter events for current innings (same logic as live-match.php lines 98-144)
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
        // Innings 2: Show events from innings 2 start onwards
        // If innings2StartSeq is null, innings 2 hasn't started yet (no events to show)
        if ($innings2StartSeq !== null) {
            if ($eventSeq >= $innings2StartSeq) {
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
            // Innings 2 hasn't started yet (no events for current batting team)
            // This is expected if innings 2 just began but no runs scored yet
            $belongsToInnings = false;
        }
    }
    
    if ($belongsToInnings) {
        $currentInningsEvents[] = $event;
    }
}

// Calculate current stats (lines 146-348 from live-match.php)
// Initialize all variables to prevent undefined errors
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

// Only process if there are events
if (!empty($currentInningsEvents)) {
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
        $outBatsmanAppearanceId = $payload['out_batsman_appearance_id'] ?? null;
        
        if (!$outBatsmanAppearanceId && $currentStrikerAppearanceId) {
            $outBatsmanAppearanceId = $currentStrikerAppearanceId;
        }
        
        $dismissedPlayerName = 'Unknown';
        if ($outBatsmanAppearanceId) {
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
            
            $dismissedPlayers[] = $outBatsmanAppearanceId;
        }
        
        $newBatsmanAppearanceId = $payload['new_batsman_appearance_id'] ?? $appearanceId ?? null;
        if ($newBatsmanAppearanceId && !isset($battingStats[$newBatsmanAppearanceId])) {
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
                    'balls' => 0,
                    'fours' => 0,
                    'sixes' => 0
                ];
            }
        }
        
        if ($newBatsmanAppearanceId) {
            $currentStrikerAppearanceId = $newBatsmanAppearanceId;
            $lastEventStrikerId = $newBatsmanAppearanceId;
        }
        
        $totalWickets++;
        $legalBalls++;
    } elseif ($eventType === 'extra') {
        $runs = $payload['runs'] ?? 0;
        $totalRuns += $runs;
        
        if ($payload['extra_type'] === 'bye' || $payload['extra_type'] === 'legbye') {
            $legalBalls++;
        }
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
} // End of foreach loop - only runs if events exist

// Calculate overs (ensure we have a value even if no events)
$overs = floor($legalBalls / 6) + ($legalBalls % 6) / 10;

// Calculate strike rates
foreach ($battingStats as &$stat) {
    if ($stat['runs'] == 0 && isset($stat['balls']) && $stat['balls'] > 0) {
        if (!isset($stat['out']) || !$stat['out']) {
            $stat['balls'] = 0;
        }
    }
    
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

// Get current batters (lines 383-495 from live-match.php)
$currentBatters = [];
$lastStrikerId = null;
$lastNonStrikerId = null;

if ($battingTeamId && !empty($currentInningsEvents)) {
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
    
    foreach (array_reverse($currentInningsEvents) as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        if (isset($payload['non_striker_appearance_id'])) {
            $nonStrikerId = $payload['non_striker_appearance_id'];
            if (!in_array($nonStrikerId, $dismissedPlayers) && $nonStrikerId != $lastStrikerId) {
                $lastNonStrikerId = $nonStrikerId;
                break;
            }
        }
    }
    
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
    
    if ($lastStrikerId && isset($battingStats[$lastStrikerId])) {
        $currentBatters[] = $battingStats[$lastStrikerId];
    }
    if ($lastNonStrikerId && isset($battingStats[$lastNonStrikerId]) && $lastNonStrikerId != $lastStrikerId) {
        $currentBatters[] = $battingStats[$lastNonStrikerId];
    }
    
    if (empty($currentBatters)) {
        foreach ($battingStats as $appearanceId => $stat) {
            if (!in_array($appearanceId, $dismissedPlayers)) {
                if ($stat['balls'] > 0 || $stat['runs'] > 0) {
                    $currentBatters[] = $stat;
                    if (count($currentBatters) >= 2) break;
                }
            }
        }
    }
    
    if ($lastEventStrikerId && !empty($battingStats[$lastEventStrikerId])) {
        $foundInCurrentBatters = false;
        foreach ($currentBatters as $batter) {
            if ($batter['appearance_id'] == $lastEventStrikerId) {
                $foundInCurrentBatters = true;
                break;
            }
        }
        
        if (!$foundInCurrentBatters && !in_array($lastEventStrikerId, $dismissedPlayers)) {
            $newBatsmanStat = $battingStats[$lastEventStrikerId];
            if ($newBatsmanStat['runs'] == 0 && $newBatsmanStat['balls'] > 0) {
                $newBatsmanStat['balls'] = 0;
                $newBatsmanStat['strike_rate'] = 0;
                $battingStats[$lastEventStrikerId] = $newBatsmanStat;
            }
            if ($newBatsmanStat['balls'] == 0 && $newBatsmanStat['runs'] == 0) {
                $currentBatters = array_merge([$newBatsmanStat], $currentBatters);
            }
        }
    }
}

// Calculate extras (lines 503-532)
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

// Get all batting team players to show "Yet to Bat" (lines 534-557)
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

$battedPlayers = array_keys($battingStats);
$allBattedAppearanceIds = array_merge($battedPlayers, $dismissedPlayers);

$yetToBatPlayers = [];
foreach ($allBattingTeamPlayers as $player) {
    if (!in_array($player['appearance_id'], $allBattedAppearanceIds)) {
        $yetToBatPlayers[] = $player;
    }
}

// Calculate powerplay runs (lines 559-585)
$powerplayOvers = ($matchData['overs_per_innings'] ?? 20) <= 20 ? 6 : 10;
$powerplayBalls = $powerplayOvers * 6;
$powerplayRuns = 0;
$powerplayLegalBalls = 0;

foreach ($currentInningsEvents as $event) {
    $payload = json_decode($event['payload_json'], true);
    if (!is_array($payload)) continue;
    
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

// Calculate partnerships (lines 587-659)
$partnerships = [];
$partnershipRuns = 0;
$partnershipBalls = 0;
$currentPartnershipBatsmen = [];
$wicketNumber = 0;

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
            $partnerships[] = [
                'wicket' => $wicketNumber,
                'runs' => $partnershipRuns,
                'balls' => $partnershipBalls,
                'batsmen' => array_slice($currentPartnershipBatsmen, -2, 2),
                'score_at' => $totalRuns
            ];
        }
        $partnershipRuns = 0;
        $partnershipBalls = 0;
        $currentPartnershipBatsmen = [];
    }
}

if (!empty($currentInningsEvents)) {
    $currentBatsmen = [];
    foreach ($currentPartnershipBatsmen as $name) {
        foreach ($battingStats as $appearanceId => $batter) {
            if ($batter['player_name'] == $name && (!isset($batter['out']) || !$batter['out'])) {
                $currentBatsmen[] = $batter;
            }
        }
    }
    
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

// Calculate innings 1 total for target display (if innings 2) (lines 661-732)
if (!function_exists('calculateInningsData')) {
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
}

$innings1Total = null;
if ($currentInnings === 2) {
    $innings1BattingTeamId = ($battingTeamId == $matchData['team1_id']) ? $matchData['team2_id'] : $matchData['team1_id'];
    $innings1BowlingTeamId = $battingTeamId;
    
    $innings1Data = calculateInningsData($allEvents, $innings1BattingTeamId, $innings1BowlingTeamId, 1, $innings2StartSeq, $db);
    if ($innings1Data && isset($innings1Data['total_runs'])) {
        $innings1Total = $innings1Data['total_runs'];
    }
}

$target = null;
if ($currentInnings === 2 && $innings1Total !== null) {
    $target = $innings1Total + 1;
}

// Calculate current run rate and partnership (lines 734-758)
$currentRunRate = $overs > 0 ? round($totalRuns / $overs, 2) : 0;

$partnershipRuns = 0;
$partnershipBalls = 0;
if (!empty($currentInningsEvents)) {
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
}

// Calculate overs left (lines 760-762)
$maxOvers = (float)($matchData['overs_per_innings'] ?? 20);
$oversLeft = max(0, $maxOvers - $overs);

// Get initial events for commentary
$eventModel = new Event();
$events = $eventModel->getByMatchId($matchId, 100);

// Format events for commentary display
$commentaryEvents = [];
if (!empty($events)) {
    // Get all unique appearance IDs from events
    $appearanceIds = [];
    foreach ($events as $event) {
        $appearanceId = $event['appearance_id'] ?? null;
        if ($appearanceId) $appearanceIds[] = $appearanceId;
        
        $payload = json_decode($event['payload_json'] ?? '{}', true);
        $bowlerId = $payload['bowler_appearance_id'] ?? null;
        if ($bowlerId) $appearanceIds[] = $bowlerId;
    }
    // Remove duplicates
    $appearanceIds = array_values(array_unique(array_filter($appearanceIds)));
    
    // Build player map
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
    
    // Process events and format for commentary
    $currentOverNum = 0;
    $currentBallNum = 0;
    
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
        
        // Format event description
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
                $visualCircle = '6';
                $circleClass = 'purple';
            } else if ($runs === 4) {
                $visualCircle = '4';
                $circleClass = 'blue';
            }
            
            $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', ' . $eventType;
        } else if ($payload['event_type'] === 'wicket') {
            $wicketType = $payload['wicket_type'] ?? 'Wicket';
            $eventType = 'OUT ' . strtoupper($wicketType);
            $visualCircle = 'W';
            $circleClass = 'red';
            
            $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', out ' . $wicketType;
            $eventDescription .= ' - THAT\'S OUT!!';
            
            // Get dismissed batsman details
            $outBatsmanId = $payload['out_batsman_appearance_id'] ?? $batsmanId;
            if ($outBatsmanId && isset($playerMap[$outBatsmanId])) {
                $outBatsmanName = $playerMap[$outBatsmanId];
                $dismissalDetails = $outBatsmanName . ' ' . strtoupper($wicketType) . ' b ' . $bowlerName;
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
                $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', Wide' . ($runs > 1 ? ', ' . $runs . ' runs' : '');
            } else if ($extraType === 'noball') {
                $eventDescription = $event['commentary'] ?? $bowlerName . ' to ' . $batsmanName . ', No Ball' . ($runs > 1 ? ', ' . $runs . ' runs' : '');
            }
        }
        
        // Use custom commentary if available
        if (!empty($event['commentary']) && trim($event['commentary'])) {
            $eventDescription = $bowlerName . ' to ' . $batsmanName . ', ' . $event['commentary'];
        }
        
        $ballLabel = $isLegalBall ? $overDisplay . '.' . $ballDisplay : '';
        
        $commentaryEvents[] = [
            'event_id' => $event['event_id'] ?? null,
            'ball' => $ballLabel,
            'event_type' => strtoupper($eventType),
            'description' => $eventDescription,
            'commentary' => $event['commentary'] ?? $eventDescription,
            'visualCircle' => $visualCircle,
            'circleClass' => $circleClass ?? '',
            'dismissalDetails' => $dismissalDetails
        ];
    }
    
    // Reverse to show newest first
    $commentaryEvents = array_reverse($commentaryEvents);
}

// Format overs display
if (!function_exists('formatOvers')) {
    function formatOvers($overs) {
        $full = floor($overs);
        $decimals = round(($overs - $full) * 10);
        return $full . '.' . $decimals;
    }
}

// Get team abbreviation
if (!function_exists('getTeamAbbr')) {
    function getTeamAbbr($name) {
        $words = explode(' ', $name);
        $abbr = '';
        foreach ($words as $word) {
            $abbr .= strtoupper(substr($word, 0, 1));
        }
        return strlen($abbr) > 4 ? substr($abbr, 0, 4) : $abbr;
    }
}

$battingTeamAbbr = $battingTeamName ? getTeamAbbr($battingTeamName) : 'T1';

// Prepare data for Vue.js - all calculated stats
// Ensure all variables are initialized even if empty
$liveMatchData = [
    'match' => $matchData,
    'currentInnings' => $currentInnings ?? 1,
    'battingTeam' => [
        'id' => $battingTeamId ?? null,
        'name' => $battingTeamName ?? '',
        'abbr' => $battingTeamAbbr ?? 'T1'
    ],
    'bowlingTeam' => [
        'id' => $bowlingTeamId ?? null,
        'name' => $bowlingTeamName ?? ''
    ],
    'scorecard' => [
        'totalRuns' => isset($totalRuns) ? $totalRuns : 0,
        'totalWickets' => isset($totalWickets) ? $totalWickets : 0,
        'overs' => isset($overs) ? $overs : 0,
        'oversFormatted' => isset($overs) ? formatOvers($overs) : '0.0',
        'currentRunRate' => isset($currentRunRate) ? $currentRunRate : 0,
        'partnershipRuns' => isset($partnershipRuns) ? $partnershipRuns : 0,
        'partnershipBalls' => isset($partnershipBalls) ? $partnershipBalls : 0,
        'oversLeft' => isset($oversLeft) ? $oversLeft : 0,
        'oversLeftFormatted' => isset($oversLeft) ? formatOvers($oversLeft) : '0.0',
        'target' => isset($target) ? $target : null,
        'targetAchieved' => ($currentInnings === 2 && isset($target) && $target !== null && isset($totalRuns) && $totalRuns >= $target),
        'targetFailed' => ($currentInnings === 2 && isset($target) && $target !== null && isset($oversLeft) && $oversLeft <= 0 && isset($totalRuns) && $totalRuns < $target),
        'allWicketsDown' => (isset($totalWickets) && $totalWickets >= 10),
        'shouldHideTarget' => ($matchData['state'] === 'completed') || ($currentInnings === 2 && isset($target) && $target !== null && isset($totalRuns) && $totalRuns >= $target) || ($currentInnings === 2 && isset($target) && $target !== null && isset($oversLeft) && $oversLeft <= 0 && isset($totalRuns) && $totalRuns < $target) || (isset($totalWickets) && $totalWickets >= 10)
    ],
    'battingStats' => isset($battingStats) ? array_values($battingStats) : [],
    'currentBatters' => isset($currentBatters) ? $currentBatters : [],
    'lastStrikerId' => isset($lastStrikerId) ? $lastStrikerId : null,
    'lastNonStrikerId' => isset($lastNonStrikerId) ? $lastNonStrikerId : null,
    'dismissedPlayers' => isset($dismissedPlayers) ? $dismissedPlayers : [],
    'bowlingStats' => isset($bowlingStats) ? array_values($bowlingStats) : [],
    'lastEventBowlerId' => isset($lastEventBowlerId) ? $lastEventBowlerId : null,
    'extras' => [
        'total' => isset($extrasTotal) ? $extrasTotal : 0,
        'byes' => isset($extrasByes) ? $extrasByes : 0,
        'legByes' => isset($extrasLegByes) ? $extrasLegByes : 0,
        'wides' => isset($extrasWides) ? $extrasWides : 0,
        'noballs' => isset($extrasNoballs) ? $extrasNoballs : 0,
        'penalty' => isset($extrasPenalty) ? $extrasPenalty : 0
    ],
    'yetToBatPlayers' => isset($yetToBatPlayers) ? $yetToBatPlayers : [],
    'powerplay' => [
        'overs' => isset($powerplayOvers) ? $powerplayOvers : 0,
        'runs' => isset($powerplayRuns) ? $powerplayRuns : 0
    ],
    'partnerships' => isset($partnerships) ? $partnerships : [],
    'events' => isset($events) ? $events : [],
    'commentaryEvents' => $commentaryEvents
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($matchData['team1_name']) ?> vs <?= htmlspecialchars($matchData['team2_name']) ?> - Live</title>
    
    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Modern CSS -->
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/vue-modern.css">
    
    <style>
        body {
            background: var(--gray-50);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .live-match-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            padding-top: 1.5rem;
            padding-bottom: 80px;
        }
        
        .match-header-modern {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-lg);
        }
        
        .match-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .match-info {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .scorecard-modern {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
        }
        
        .scorecard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-200);
        }
        
        .scorecard-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }
        
        .score-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .overs-display {
            font-size: 1rem;
            color: var(--gray-600);
        }
        
        .players-list {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .player-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--gray-50);
            border-radius: var(--radius-md);
        }
        
        .player-name {
            font-weight: 600;
            color: var(--gray-900);
        }
        
        .player-stats {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: var(--gray-600);
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
        
        .commentary-feed-modern {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            max-height: 600px;
            overflow-y: auto;
            box-shadow: var(--shadow-md);
        }
        
        .commentary-item-modern {
            padding: 1rem;
            border-left: 3px solid var(--primary);
            margin-bottom: 0.75rem;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }
        
        .commentary-item-modern:hover {
            background: var(--gray-100);
            transform: translateX(4px);
        }
        
        .commentary-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .ball-badge {
            background: var(--primary);
            color: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .event-type {
            font-weight: 600;
            color: var(--gray-900);
        }
        
        .commentary-text {
            color: var(--gray-700);
            line-height: 1.6;
        }
        
        .live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--error);
            color: var(--white);
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
            animation: pulse 2s infinite;
        }
        
        .live-dot {
            width: 8px;
            height: 8px;
            background: var(--white);
            border-radius: 50%;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        
        .live-nav-tabs {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 0.5rem 1rem;
            position: sticky;
            top: 56px;
            z-index: 100;
        }
        
        .live-nav-tabs .container {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
        }
        
        .live-nav-tabs .tab {
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: var(--gray-600);
            white-space: nowrap;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .live-nav-tabs .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            font-weight: 600;
        }
        
        .live-nav-tabs .tab:hover {
            color: var(--primary);
        }
        
        @media (max-width: 768px) {
            .match-title {
                font-size: 1.25rem;
            }
            
            .score-display {
                font-size: 1.5rem;
            }
            
            .live-nav-tabs {
                top: 40px;
                padding: 0.375rem 0.75rem;
            }
        }
        
        .bottom-nav-modern {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-around;
            padding: 0.5rem 0;
            box-shadow: var(--shadow-lg);
            z-index: 100;
        }
        
        .bottom-nav-modern .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: var(--gray-500);
            transition: var(--transition);
            border-radius: var(--radius);
        }
        
        .bottom-nav-modern .nav-item.active {
            color: var(--primary);
        }
        
        .bottom-nav-modern .nav-icon {
            font-size: 1.25rem;
        }
        
        .bottom-nav-modern .nav-label {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        @media (min-width: 769px) {
            .bottom-nav-modern {
                display: none;
            }
            .live-match-container {
                padding-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Public Header Navigation -->
        <header class="header-modern">
            <div class="header-content">
                <div class="header-title">
                    <a href="/cricapp/public/" style="color: white; text-decoration: none;">🏏 Cricket Scoring</a>
                </div>
                <nav class="header-nav">
                    <a href="/cricapp/public/" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'home' }]" style="color: white; background: rgba(255,255,255,0.2);">Home</a>
                    <a href="/cricapp/public/matches.php" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'matches' }]" style="color: white; background: rgba(255,255,255,0.2);">Matches</a>
                    <a href="/cricapp/public/live.php" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'live' }]" style="color: white; background: rgba(255,255,255,0.2);">Live</a>
                    <a href="/cricapp/public/leaderboard.php" :class="['btn-modern', 'btn-secondary', { 'active': currentPage === 'leaderboard' }]" style="color: white; background: rgba(255,255,255,0.2);">Leaderboard</a>
                </nav>
            </div>
        </header>
        
        <!-- Navigation Tabs -->
        <nav class="live-nav-tabs">
            <div class="container" style="display: flex; gap: 1rem; overflow-x: auto;">
                <a :href="`/cricapp/public/match-view.php?id=${match.match_id}&tab=info`" class="tab">Info</a>
                <a :href="`/cricapp/public/live-match.php?id=${match.match_id}`" class="tab active">Live</a>
                <a :href="`/cricapp/public/match-view.php?id=${match.match_id}&tab=scorecard`" class="tab">Scorecard</a>
                <a :href="`/cricapp/public/match-view.php?id=${match.match_id}&tab=squads`" class="tab">Squads</a>
                <a :href="`/cricapp/public/match-view.php?id=${match.match_id}&tab=overs`" class="tab">Overs</a>
            </div>
        </nav>

        <div class="live-match-container">
            <!-- Match Header -->
            <div class="match-header-modern">
                <div class="match-title">
                    {{ match.team1_name || 'Team 1' }} vs {{ match.team2_name || 'Team 2' }}
                </div>
                <div class="match-info">
                    <span>{{ match.series_name || 'Match' }}</span>
                    <span v-if="match && match.match_date">{{ formatDate(match.match_date) }}</span>
                </div>
                <div v-if="isLive" class="live-indicator" style="margin-top: 1rem;">
                    <span class="live-dot"></span>
                    LIVE
                </div>
            </div>

            <!-- Target Display (for Innings 2) -->
            <div v-if="scorecard && currentInnings === 2 && scorecard.target && !scorecard.shouldHideTarget" class="target-display" style="background: #dc2626; color: white; padding: 6px; border-radius: 4px; margin-bottom: 8px; text-align: center; font-weight: 600; font-size: 12px;">
                {{ (battingTeam && battingTeam.name) ? battingTeam.name : 'Team' }} need {{ Math.max(0, scorecard.target - (scorecard.totalRuns || 0)) }} runs to win
            </div>
            <div v-else-if="scorecard && scorecard.targetAchieved" class="target-display" style="background: #10b981; color: white; padding: 6px; border-radius: 4px; margin-bottom: 8px; text-align: center; font-weight: 600; font-size: 12px;">
                {{ (battingTeam && battingTeam.name) ? battingTeam.name : 'Team' }} won by {{ 10 - (scorecard.totalWickets || 0) }} wicket{{ (10 - (scorecard.totalWickets || 0)) !== 1 ? 's' : '' }}
            </div>
            <div v-else-if="scorecard && (scorecard.targetFailed || (scorecard.oversLeft <= 0 && (scorecard.totalRuns || 0) < (scorecard.target || 0)))" class="target-display" style="background: #dc2626; color: white; padding: 6px; border-radius: 4px; margin-bottom: 8px; text-align: center; font-weight: 600; font-size: 12px;">
                {{ (battingTeam && battingTeam.name) ? battingTeam.name : 'Team' }} lost by {{ (scorecard.target || 0) - (scorecard.totalRuns || 0) }} run{{ ((scorecard.target || 0) - (scorecard.totalRuns || 0)) !== 1 ? 's' : '' }}
            </div>

            <!-- Team Score -->
            <div class="team-score-section" style="background: var(--white); padding: 1rem; border-radius: var(--radius-xl); margin-bottom: 1rem; box-shadow: var(--shadow-md);">
                <div style="font-size: 0.875rem; font-weight: 600; color: var(--gray-600); margin-bottom: 0.5rem;">{{ (battingTeam && battingTeam.abbr) ? battingTeam.abbr : ((match && match.team1_name) ? match.team1_name.substring(0, 2).toUpperCase() : 'T1') }}</div>
                <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                    {{ (scorecard && scorecard.totalRuns !== undefined) ? scorecard.totalRuns : 0 }}{{ (scorecard && scorecard.totalWickets > 0) ? '-' + scorecard.totalWickets : '' }}
                    <span style="font-size: 1rem; color: var(--gray-600); font-weight: 500;">({{ (scorecard && scorecard.oversFormatted) ? scorecard.oversFormatted : '0.0' }})</span>
                </div>
            </div>

            <!-- Match Stats -->
            <div class="match-stats" style="background: var(--white); padding: 1rem; border-radius: var(--radius-xl); margin-bottom: 1rem; box-shadow: var(--shadow-md); display: flex; gap: 1rem; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div class="stat-item" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span class="stat-label" style="font-size: 0.75rem; color: var(--gray-600);">CRR</span>
                    <span class="stat-value" style="font-weight: 700; color: var(--primary);">{{ (scorecard && scorecard.currentRunRate !== undefined) ? scorecard.currentRunRate : 0 }}</span>
                </div>
                <div class="stat-item" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span class="stat-label" style="font-size: 0.75rem; color: var(--gray-600);">P'SHIP</span>
                    <span class="stat-value" style="font-weight: 700; color: var(--primary);">{{ (scorecard && scorecard.partnershipRuns !== undefined) ? scorecard.partnershipRuns : 0 }}({{ (scorecard && scorecard.partnershipBalls !== undefined) ? scorecard.partnershipBalls : 0 }})</span>
                </div>
                <div class="stat-item" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <span class="stat-label" style="font-size: 0.75rem; color: var(--gray-600);">OVS LEFT</span>
                    <span class="stat-value" style="font-weight: 700; color: var(--primary);">{{ (scorecard && scorecard.oversLeftFormatted) ? scorecard.oversLeftFormatted : '0.0' }}</span>
                </div>
                <a v-if="match && match.match_id" :href="`/cricapp/public/match-view.php?id=${match.match_id}`" class="more-link" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.875rem;">More</a>
            </div>

            <!-- Batting Scorecard -->
            <div class="scorecard-modern">
                <div class="section-title" style="font-size: 1rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; padding-bottom: 0.375rem; border-bottom: 1px solid var(--gray-200);">Batter</div>
                <table class="scorecard-table" style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;">
                    <thead style="background: var(--gray-50);">
                        <tr>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">Batter</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">R</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">B</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">4s</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">6s</th>
                            <th style="padding: 0.75rem; text-align: right; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">SR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="battingStats.length === 0 && (!yetToBatPlayers || yetToBatPlayers.length === 0)">
                            <td colspan="6" style="text-align: center; padding: 0.75rem; color: var(--gray-600);">
                                Waiting for innings to start
                            </td>
                        </tr>
                        <tr v-else-if="battingStats.length === 0 && yetToBatPlayers && yetToBatPlayers.length > 0">
                            <td colspan="6" style="text-align: center; padding: 0.75rem; color: var(--gray-600);">
                                Innings not started yet
                            </td>
                        </tr>
                        <tr v-for="batter in battingStats" :key="batter.appearance_id" :class="{ 'player-out': batter.out }" :style="batter.out ? 'opacity: 0.7;' : ''">
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">
                                <span :class="{ 'on-strike': (batter.appearance_id === lastStrikerId || batter.appearance_id === lastNonStrikerId) && !batter.out }" style="font-weight: 600; color: var(--gray-900);">
                                    {{ batter.player_name }}
                                    <span v-if="(batter.appearance_id === lastStrikerId || batter.appearance_id === lastNonStrikerId) && !batter.out" style="color: #2563eb;">*</span>
                                </span>
                                <div v-if="batter.out && batter.dismissal" style="font-size: 11px; color: var(--gray-600); font-weight: normal;">
                                    {{ batter.dismissal.toUpperCase() }}
                                </div>
                                <div v-else-if="!batter.out" style="font-size: 11px; color: #10b981; font-weight: normal;">
                                    not out
                                </div>
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ batter.runs }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ batter.balls }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ batter.fours }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ batter.sixes }}</td>
                            <td style="padding: 0.75rem; text-align: right; border-bottom: 1px solid var(--gray-200);">{{ parseFloat(batter.strike_rate || 0).toFixed(2) }} →</td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Extras -->
                <div v-if="extras && extras.total > 0" style="margin-top: 8px; padding: 6px; background: var(--gray-50); border-radius: 4px; font-size: 12px;">
                    <strong>Extras:</strong> {{ extras.total || 0 }} 
                    (b {{ extras.byes || 0 }}, lb {{ extras.legByes || 0 }}, w {{ extras.wides || 0 }}, nb {{ extras.noballs || 0 }}{{ extras.penalty > 0 ? ', p ' + extras.penalty : '' }})
                </div>
                
                <!-- Total -->
                <div style="margin-top: 6px; padding: 6px; background: var(--gray-200); border-radius: 4px; font-weight: 600; font-size: 13px;">
                    <strong>Total:</strong> {{ (scorecard && scorecard.totalRuns !== undefined) ? scorecard.totalRuns : 0 }}{{ (scorecard && scorecard.totalWickets > 0) ? '-' + scorecard.totalWickets : '' }} ({{ (scorecard && scorecard.oversFormatted) ? scorecard.oversFormatted : '0.0' }} Overs, RR: {{ (scorecard && scorecard.currentRunRate !== undefined) ? scorecard.currentRunRate : 0 }})
                </div>
                
                <!-- Yet to Bat -->
                <div v-if="yetToBatPlayers.length > 0" style="margin-top: 10px;">
                    <div style="font-size: 11px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px;">Yet to Bat:</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span v-for="player in yetToBatPlayers" :key="player.appearance_id" style="font-size: 11px; color: var(--gray-600);">{{ player.player_name }}</span>
                    </div>
                </div>
            </div>

            <!-- Bowling Scorecard -->
            <div v-if="bowlingStats.length > 0" class="scorecard-modern">
                <div class="section-title" style="font-size: 1rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; padding-bottom: 0.375rem; border-bottom: 1px solid var(--gray-200);">Bowler</div>
                <table class="scorecard-table" style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--gray-50);">
                        <tr>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">Bowler</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">O</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">M</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">R</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">W</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">NB</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">WD</th>
                            <th style="padding: 0.75rem; text-align: right; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">ECO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="bowler in bowlingStats" :key="bowler.appearance_id">
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">
                                <span :class="{ 'on-strike': bowler.appearance_id === lastEventBowlerId }" style="font-weight: 600; color: var(--gray-900);">
                                    {{ bowler.player_name }}
                                    <span v-if="bowler.appearance_id === lastEventBowlerId" style="color: #2563eb;">*</span>
                                </span>
                            </td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ formatOvers(bowler.overs) }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ bowler.maidens || 0 }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ bowler.runs }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ bowler.wickets }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ bowler.noballs || 0 }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ bowler.wides || 0 }}</td>
                            <td style="padding: 0.75rem; text-align: right; border-bottom: 1px solid var(--gray-200);">{{ parseFloat(bowler.economy || 0).toFixed(2) }} →</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Powerplays Section -->
            <div v-if="powerplay.runs > 0" class="scorecard-modern">
                <div class="section-title" style="font-size: 1rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; padding-bottom: 0.375rem; border-bottom: 1px solid var(--gray-200);">Powerplays</div>
                <table class="scorecard-table" style="width: 100%; border-collapse: collapse;">
                    <thead style="background: var(--gray-50);">
                        <tr>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">Overs</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600; color: var(--gray-700); font-size: 0.875rem; border-bottom: 2px solid var(--gray-200);">Runs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">0.1 - {{ powerplay.overs }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid var(--gray-200);">{{ powerplay.runs }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Partnerships Section -->
            <div v-if="partnerships.length > 0" class="scorecard-modern">
                <div class="section-title" style="font-size: 1rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; padding-bottom: 0.375rem; border-bottom: 1px solid var(--gray-200);">Partnerships</div>
                <div class="partnerships-list">
                    <div v-for="part in partnerships" :key="part.wicket" style="padding: 8px; margin-bottom: 8px; background: var(--gray-50); border-radius: 4px; font-size: 13px;">
                        <div v-if="part.batsmen && part.batsmen.length > 0">
                            <strong>{{ part.batsmen.join(' ') }}</strong>
                            <div style="margin-top: 4px; font-weight: 600; color: var(--gray-900);">
                                Partnership: {{ part.runs }}({{ part.balls || 0 }})
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Commentary Section -->
            <div class="commentary-section">
                <div class="section-title">Live Commentary</div>
                <div class="commentary-feed" id="commentary-feed">
                    <div v-if="loading" class="loading">Loading commentary...</div>
                    <div v-else-if="!commentaryEvents || commentaryEvents.length === 0" class="loading">No commentary yet</div>
                    <div v-else>
                        <div class="commentary-item" v-for="event in commentaryEvents" :key="event.event_id">
                            <div class="commentary-header">
                                <span v-if="event.visualCircle" 
                                      :class="'commentary-circle'"
                                      :style="{
                                          'background': event.circleClass === 'purple' ? '#9c27b0' : 
                                                       event.circleClass === 'blue' ? '#2196F3' : 
                                                       event.circleClass === 'red' ? '#f44336' : '#e0e0e0'
                                      }">
                                    {{ event.visualCircle }}
                                </span>
                                <strong style="font-size: 1.1em; color: #333; margin-right: 8px;">{{ event.ball }}</strong>
                                <span style="font-weight: bold; color: #1976d2;">{{ event.event_type }}</span>
                            </div>
                            <div class="commentary-description" v-html="event.description || event.commentary"></div>
                            <div v-if="event.dismissalDetails" class="commentary-dismissal" v-html="event.dismissalDetails"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Navigation -->
        <nav class="bottom-nav-modern">
            <a href="/cricapp/public/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="/cricapp/public/matches.php" class="nav-item">
                <span class="nav-icon">📅</span>
                <span class="nav-label">Matches</span>
            </a>
            <a href="/cricapp/public/live.php" class="nav-item active">
                <span class="nav-icon">⚡</span>
                <span class="nav-label">Live</span>
            </a>
            <a href="/cricapp/public/leaderboard.php" class="nav-item">
                <span class="nav-icon">🏆</span>
                <span class="nav-label">Leaderboard</span>
            </a>
            <a href="/cricapp/public/profile.php" class="nav-item">
                <span class="nav-icon">👤</span>
                <span class="nav-label">Profile</span>
            </a>
        </nav>
    </div>

    <!-- Vue App Script - Load after Vue.js -->
    <script src="/cricapp/assets/js/vue-app.js"></script>
    <script>
        // Wait for Vue.js and VueApp to be available
        function initVueApp() {
            if (typeof Vue === 'undefined') {
                console.error('Vue.js failed to load. Check CDN connection.');
                document.getElementById('app').innerHTML = '<div style="padding: 2rem; text-align: center;"><h2>Error Loading Page</h2><p>Vue.js failed to load. Please check your internet connection and try again.</p></div>';
                return;
            }
            
            if (typeof VueApp === 'undefined' || !VueApp.createApp) {
                console.error('VueApp is not defined. Check vue-app.js file.');
                document.getElementById('app').innerHTML = '<div style="padding: 2rem; text-align: center;"><h2>Error Loading Page</h2><p>Vue.js application failed to initialize. Please refresh the page.</p></div>';
                return;
            }
            
            const { createApp } = Vue;
            
            createApp({
            data() {
                const liveMatchData = <?= json_encode($liveMatchData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                
                // Debug: Log data to console
                console.log('Live Match Data:', liveMatchData);
                console.log('Match:', liveMatchData.match);
                console.log('Batting Team:', liveMatchData.battingTeam);
                console.log('Scorecard:', liveMatchData.scorecard);
                console.log('Batting Stats:', liveMatchData.battingStats);
                console.log('Events:', liveMatchData.events);
                console.log('Current Innings:', liveMatchData.currentInnings);
                console.log('Total Runs:', liveMatchData.scorecard?.totalRuns);
                console.log('Total Wickets:', liveMatchData.scorecard?.totalWickets);
                
                // Check if data structure is valid
                if (!liveMatchData || !liveMatchData.match) {
                    console.error('Invalid liveMatchData structure');
                }
                
                // Check if scorecard data exists
                if (!liveMatchData.scorecard) {
                    console.warn('Scorecard data is missing');
                }
                
                // Determine current page
                const path = window.location.pathname;
                let currentPage = 'home';
                if (path.includes('/live')) currentPage = 'live';
                else if (path.includes('/matches')) currentPage = 'matches';
                else if (path.includes('/leaderboard')) currentPage = 'leaderboard';
                
                // Sort batting stats: current batters first, then dismissed
                const sortedBattingStats = [];
                const currentNotOut = [];
                const dismissed = [];
                
                // Ensure battingStats is an array
                const battingStatsArray = Array.isArray(liveMatchData.battingStats) ? liveMatchData.battingStats : [];
                
                battingStatsArray.forEach(batter => {
                    if (batter && batter.out) {
                        dismissed.push(batter);
                    } else if (batter) {
                        currentNotOut.push(batter);
                    }
                });
                
                // Current batters first (striker first)
                if (liveMatchData.currentBatters && liveMatchData.currentBatters.length > 0) {
                    sortedBattingStats.push(...liveMatchData.currentBatters);
                    // Add other current batters not in currentBatters
                    currentNotOut.forEach(batter => {
                        const found = sortedBattingStats.some(existing => existing.appearance_id === batter.appearance_id);
                        if (!found) {
                            sortedBattingStats.push(batter);
                        }
                    });
                } else {
                    sortedBattingStats.push(...currentNotOut);
                }
                
                // Add dismissed players
                sortedBattingStats.push(...dismissed);
                
                // Sort bowling stats: current bowler first, then by wickets desc
                const sortedBowlingStats = [];
                const otherBowlers = [];
                
                // Ensure bowlingStats is an array
                const bowlingStatsArray = Array.isArray(liveMatchData.bowlingStats) ? liveMatchData.bowlingStats : [];
                
                bowlingStatsArray.forEach(bowler => {
                    if (bowler && bowler.appearance_id === liveMatchData.lastEventBowlerId) {
                        sortedBowlingStats.push(bowler);
                    } else if (bowler) {
                        otherBowlers.push(bowler);
                    }
                });
                
                // Sort other bowlers by wickets desc, then economy asc
                otherBowlers.sort((a, b) => {
                    if (b.wickets !== a.wickets) {
                        return b.wickets - a.wickets;
                    }
                    return a.economy - b.economy;
                });
                
                sortedBowlingStats.push(...otherBowlers);
                
                // Ensure scorecard has default values - use PHP calculated data if available
                const scorecard = liveMatchData.scorecard ? {
                    totalRuns: liveMatchData.scorecard.totalRuns !== undefined ? liveMatchData.scorecard.totalRuns : 0,
                    totalWickets: liveMatchData.scorecard.totalWickets !== undefined ? liveMatchData.scorecard.totalWickets : 0,
                    overs: liveMatchData.scorecard.overs !== undefined ? liveMatchData.scorecard.overs : 0,
                    oversFormatted: liveMatchData.scorecard.oversFormatted || '0.0',
                    currentRunRate: liveMatchData.scorecard.currentRunRate !== undefined ? liveMatchData.scorecard.currentRunRate : 0,
                    partnershipRuns: liveMatchData.scorecard.partnershipRuns !== undefined ? liveMatchData.scorecard.partnershipRuns : 0,
                    partnershipBalls: liveMatchData.scorecard.partnershipBalls !== undefined ? liveMatchData.scorecard.partnershipBalls : 0,
                    oversLeft: liveMatchData.scorecard.oversLeft !== undefined ? liveMatchData.scorecard.oversLeft : 0,
                    oversLeftFormatted: liveMatchData.scorecard.oversLeftFormatted || '0.0',
                    target: liveMatchData.scorecard.target !== undefined ? liveMatchData.scorecard.target : null,
                    targetAchieved: liveMatchData.scorecard.targetAchieved || false,
                    targetFailed: liveMatchData.scorecard.targetFailed || false,
                    allWicketsDown: liveMatchData.scorecard.allWicketsDown || false,
                    shouldHideTarget: liveMatchData.scorecard.shouldHideTarget !== undefined ? liveMatchData.scorecard.shouldHideTarget : true
                } : {
                    totalRuns: 0,
                    totalWickets: 0,
                    overs: 0,
                    oversFormatted: '0.0',
                    currentRunRate: 0,
                    partnershipRuns: 0,
                    partnershipBalls: 0,
                    oversLeft: 0,
                    oversLeftFormatted: '0.0',
                    target: null,
                    targetAchieved: false,
                    targetFailed: false,
                    allWicketsDown: false,
                    shouldHideTarget: true
                };
                
                return {
                    match: liveMatchData.match || {},
                    events: Array.isArray(liveMatchData.events) ? liveMatchData.events : [],
                    commentaryEvents: Array.isArray(liveMatchData.commentaryEvents) ? liveMatchData.commentaryEvents : [],
                    battingStats: sortedBattingStats,
                    bowlingStats: sortedBowlingStats,
                    currentBatters: liveMatchData.currentBatters || [],
                    scorecard: scorecard,
                    battingTeam: liveMatchData.battingTeam || { id: null, name: '', abbr: 'T1' },
                    bowlingTeam: liveMatchData.bowlingTeam || { id: null, name: '' },
                    extras: liveMatchData.extras || { total: 0, byes: 0, legByes: 0, wides: 0, noballs: 0, penalty: 0 },
                    yetToBatPlayers: liveMatchData.yetToBatPlayers || [],
                    powerplay: liveMatchData.powerplay || { overs: 0, runs: 0 },
                    partnerships: liveMatchData.partnerships || [],
                    currentInnings: liveMatchData.currentInnings || 1,
                    lastStrikerId: liveMatchData.lastStrikerId || null,
                    lastNonStrikerId: liveMatchData.lastNonStrikerId || null,
                    lastEventBowlerId: liveMatchData.lastEventBowlerId || null,
                    dismissedPlayers: liveMatchData.dismissedPlayers || [],
                    loading: false,
                    refreshInterval: null,
                    currentPage: currentPage
                };
            },
            computed: {
                isLive() {
                    return this.match.state === 'live';
                }
            },
            mounted() {
                // Don't call loadScorecard() here - use PHP calculated data instead
                // this.loadScorecard();
                
                // Auto-refresh every 10 seconds if live
                if (this.isLive) {
                    this.refreshInterval = setInterval(() => {
                        this.refreshData();
                    }, 10000);
                }
            },
            beforeUnmount() {
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                }
            },
            methods: {
                async loadScorecard() {
                    if (!VueApp || !VueApp.apiClient || !this.match || !this.match.match_id) {
                        return;
                    }
                    try {
                        // Only refresh if we don't have calculated data
                        // The PHP calculated scorecard should be used, not overwritten
                        if (!this.scorecard || this.scorecard.totalRuns === undefined) {
                            const data = await VueApp.apiClient.getMatch(this.match.match_id);
                            if (data && data.success && data.data) {
                                // Don't overwrite PHP calculated data
                                // Just update if completely missing
                                if (!this.scorecard || !this.scorecard.totalRuns) {
                                    this.scorecard = this.processScorecard(data.data);
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Failed to load scorecard:', error);
                    }
                },
                processScorecard(matchData) {
                    // Simplified scorecard processing - only use as fallback
                    return {
                        team_name: matchData.team1_name,
                        runs: matchData.team1_runs || 0,
                        wickets: matchData.team1_wickets || 0,
                        overs: matchData.team1_overs || '0.0',
                        batters: [] // Would process from matchData
                    };
                },
                async refreshData() {
                    if (!VueApp || !VueApp.apiClient || !this.match || !this.match.match_id) {
                        return;
                    }
                    this.loading = true;
                    try {
                        // Refresh events
                        const eventsData = await VueApp.apiClient.getMatchEvents(this.match.match_id);
                        if (eventsData && eventsData.success && eventsData.data) {
                            this.events = Array.isArray(eventsData.data) ? eventsData.data : [];
                            // Format events for commentary (simplified version)
                            // For now, keep using PHP formatted commentaryEvents
                            // You can add formatting logic here if needed
                        }
                        
                        // Refresh scorecard
                        await this.loadScorecard();
                    } catch (error) {
                        console.error('Failed to refresh data:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                formatDate(date) {
                    if (!date) return '';
                    try {
                        return new Date(date).toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                    } catch (e) {
                        console.error('Error formatting date:', e);
                        return date;
                    }
                },
                formatOvers(overs) {
                    const full = Math.floor(overs);
                    const decimals = Math.round((overs - full) * 10);
                    return full + '.' + decimals;
                }
            }
            }).mount('#app');
        }
        
        // Initialize when page loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initVueApp);
        } else {
            initVueApp();
        }
    </script>
</body>
</html>

