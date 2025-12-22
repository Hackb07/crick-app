<?php
/**
 * Match Detail View (Read-only) - Cricbuzz Style Scorecard
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/Match.php';
require_once __DIR__ . '/../classes/Database.php';

$matchDataId = $_GET['id'] ?? 0;

if (!$matchDataId) {
    header('Location: /cricapp/public/');
    exit;
}

$matchDataModel = new MatchModel();
$matchData = $matchDataModel->getById($matchDataId);

if (!$matchData) {
    header('Location: /cricapp/public/');
    exit;
}

$db = Database::getInstance()->getConnection();

// Determine innings teams
$innings1TeamId = null;
$innings2TeamId = null;
$innings1TeamName = null;
$innings2TeamName = null;

if ($matchData['toss_winner_id'] && $matchData['toss_decision']) {
    $tossWinnerBattingFirst = ($matchData['toss_decision'] === 'bat');
    
    if ($matchData['toss_winner_id'] == $matchData['team1_id']) {
        if ($tossWinnerBattingFirst) {
            $innings1TeamId = $matchData['team1_id'];
            $innings2TeamId = $matchData['team2_id'];
            $innings1TeamName = $matchData['team1_name'];
            $innings2TeamName = $matchData['team2_name'];
        } else {
            $innings1TeamId = $matchData['team2_id'];
            $innings2TeamId = $matchData['team1_id'];
            $innings1TeamName = $matchData['team2_name'];
            $innings2TeamName = $matchData['team1_name'];
        }
    } else {
        if ($tossWinnerBattingFirst) {
            $innings1TeamId = $matchData['team2_id'];
            $innings2TeamId = $matchData['team1_id'];
            $innings1TeamName = $matchData['team2_name'];
            $innings2TeamName = $matchData['team1_name'];
        } else {
            $innings1TeamId = $matchData['team1_id'];
            $innings2TeamId = $matchData['team2_id'];
            $innings1TeamName = $matchData['team1_name'];
            $innings2TeamName = $matchData['team2_name'];
        }
    }
}

// Get all events for the match
$sql = "SELECT e.*, pa.team_id, pa.player_id, p.name as player_name
        FROM events e
        LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
        LEFT JOIN players p ON pa.player_id = p.player_id
        WHERE e.match_id = :match_id
        ORDER BY e.assigned_server_seq ASC";
$stmt = $db->prepare($sql);
$stmt->execute(['match_id' => $matchDataId]);
$allEvents = $stmt->fetchAll();

// Find when innings 2 starts (transition point)
$innings2StartSeq = null;
if ($innings1TeamId && $innings2TeamId) {
    foreach ($allEvents as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        $eventTeamId = $event['team_id'] ?? null;
        $eventType = $payload['event_type'] ?? '';
        
        // First event where innings2TeamId bats (runs or wicket)
        if ($eventTeamId == $innings2TeamId && ($eventType === 'runs' || $eventType === 'wicket')) {
            $innings2StartSeq = $event['assigned_server_seq'];
            break;
        }
    }
}

// Calculate innings data
$innings1Data = calculateInningsData($allEvents, $innings1TeamId, $innings2TeamId, 1, $innings2StartSeq, $db);
$innings2Data = calculateInningsData($allEvents, $innings2TeamId, $innings1TeamId, 2, $innings2StartSeq, $db);

// Helper function to calculate innings data
function calculateInningsData($events, $battingTeamId, $bowlingTeamId, $inningsNum, $innings2StartSeq, $db) {
    $data = [
        'team_id' => $battingTeamId,
        'team_name' => '',
        'total_runs' => 0,
        'wickets' => 0,
        'overs' => 0,
        'legal_balls' => 0,
        'batting_stats' => [],
        'bowling_stats' => [],
        'fall_of_wickets' => [],
        'partnerships' => [],
        'powerplay_runs' => 0
    ];
    
    if (!$battingTeamId || !$bowlingTeamId) {
        return $data;
    }
    
    $scoreAtWicket = 0;
    $lastWicketScore = 0;
    
    // Track batting stats per player
    $battingStats = [];
    // Track bowling stats per bowler
    $bowlingStats = [];
    
    // Track current bowler and striker to identify who got out
    $currentBowlerAppearanceId = null;
    $currentStrikerAppearanceId = null;
    
    foreach ($events as $event) {
        $payload = json_decode($event['payload_json'], true);
        if (!is_array($payload)) continue;
        
        $eventTeamId = $event['team_id'] ?? null;
        $eventType = $payload['event_type'] ?? '';
        $eventSeq = $event['assigned_server_seq'] ?? 0;
        
        // Determine if this event belongs to this innings
        $belongsToInnings = false;
        
        if ($inningsNum === 1) {
            // Innings 1: events before innings 2 starts (or all events if innings 2 hasn't started)
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
        } elseif ($inningsNum === 2) {
            // Innings 2: events after innings 2 starts
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
        
        if (!$belongsToInnings) continue;
        
        // Get bowler for this ball
        if (isset($payload['bowler_appearance_id'])) {
            $currentBowlerAppearanceId = $payload['bowler_appearance_id'];
        }
        
        // Process batting stats
        if ($eventType === 'runs' && $event['appearance_id']) {
            $runs = $payload['runs'] ?? 0;
            $appearanceId = $event['appearance_id'];
            
            // Track current striker
            $currentStrikerAppearanceId = $appearanceId;
            
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
            
            $data['total_runs'] += $runs;
            $scoreAtWicket += $runs;
            $data['legal_balls']++;
            
            // Powerplay (first 10 overs = 60 balls)
            if ($data['legal_balls'] <= 60) {
                $data['powerplay_runs'] += $runs;
            }
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
            // This shouldn't happen if events are recorded correctly
            $dismissedPlayerName = 'Unknown';
            if ($outBatsmanAppearanceId) {
                // Get dismissed batsman's name from database
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
            }
            
            // The new batsman (event.appearance_id) should NOT get this ball counted
            // They start with 0 balls until they face their first delivery
            // But we should initialize their stats entry if it doesn't exist
            $newBatsmanAppearanceId = $payload['new_batsman_appearance_id'] ?? $event['appearance_id'] ?? null;
            if ($newBatsmanAppearanceId && !isset($battingStats[$newBatsmanAppearanceId])) {
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
                        'balls' => 0, // Start with 0 balls - they haven't faced a delivery yet
                        'fours' => 0,
                        'sixes' => 0
                        // Not marked as out - they're the new batsman
                    ];
                }
            }
            
            // Update current striker to new batsman (after wicket, new batsman comes in)
            if ($newBatsmanAppearanceId) {
                $currentStrikerAppearanceId = $newBatsmanAppearanceId;
            }
            
            $data['wickets']++;
            $data['legal_balls']++;
            
            // Fall of wickets - use dismissed batsman's name
            $data['fall_of_wickets'][] = [
                'wicket' => $data['wickets'],
                'score' => $data['total_runs'],
                'over' => floor($data['legal_balls'] / 6) . '.' . ($data['legal_balls'] % 6),
                'player' => $dismissedPlayerName
            ];
            
            $scoreAtWicket = 0;
        } elseif ($eventType === 'extra') {
            $runs = $payload['runs'] ?? 0;
            $data['total_runs'] += $runs;
            
            // Powerplay (first 10 overs = 60 balls) - count before incrementing legal_balls
            $ballsBeforeExtra = $data['legal_balls'];
            if ($payload['extra_type'] !== 'wide' && $payload['extra_type'] !== 'noball') {
                $data['legal_balls']++;
            }
            
            // Powerplay runs include extras
            if ($ballsBeforeExtra <= 60) {
                $data['powerplay_runs'] += $runs;
            }
        }
        
        // Process bowling stats
        if ($currentBowlerAppearanceId && ($eventType === 'runs' || $eventType === 'wicket' || $eventType === 'extra')) {
            // Get bowler details
            $bowlerSql = "SELECT pa.player_id, p.name as player_name 
                         FROM player_appearances pa 
                         JOIN players p ON pa.player_id = p.player_id 
                         WHERE pa.appearance_id = :appearance_id";
            $bowlerStmt = $db->prepare($bowlerSql);
            $bowlerStmt->execute(['appearance_id' => $currentBowlerAppearanceId]);
            $bowlerData = $bowlerStmt->fetch();
            
            if ($bowlerData) {
                $bowlerId = $currentBowlerAppearanceId;
                
                if (!isset($bowlingStats[$bowlerId])) {
                    $bowlingStats[$bowlerId] = [
                        'appearance_id' => $bowlerId,
                        'player_name' => $bowlerData['player_name'],
                        'overs' => 0,
                        'maidens' => 0,
                        'runs' => 0,
                        'wickets' => 0,
                        'noballs' => 0,
                        'wides' => 0,
                        'legal_balls' => 0
                    ];
                }
                
                if ($eventType === 'runs') {
                    $bowlingStats[$bowlerId]['runs'] += ($payload['runs'] ?? 0);
                    $bowlingStats[$bowlerId]['legal_balls']++;
                } elseif ($eventType === 'wicket') {
                    $bowlingStats[$bowlerId]['wickets']++;
                    $bowlingStats[$bowlerId]['legal_balls']++;
                } elseif ($eventType === 'extra') {
                    $bowlingStats[$bowlerId]['runs'] += ($payload['runs'] ?? 0);
                    if ($payload['extra_type'] === 'noball') {
                        $bowlingStats[$bowlerId]['noballs']++;
                    } elseif ($payload['extra_type'] === 'wide') {
                        $bowlingStats[$bowlerId]['wides']++;
                    } else {
                        $bowlingStats[$bowlerId]['legal_balls']++;
                    }
                }
            }
        }
    }
    
    // Convert legal balls to overs
    $data['overs'] = floor($data['legal_balls'] / 6) + ($data['legal_balls'] % 6) / 10;
    
    // Calculate strike rates for batting
    foreach ($battingStats as &$stat) {
        $stat['strike_rate'] = $stat['balls'] > 0 ? round(($stat['runs'] / $stat['balls']) * 100, 2) : 0;
    }
    
    // Sort batting by runs desc
    usort($battingStats, function($a, $b) {
        return $b['runs'] - $a['runs'];
    });
    
    // Calculate economy rates and overs for bowling
    foreach ($bowlingStats as &$stat) {
        $stat['overs'] = floor($stat['legal_balls'] / 6) + ($stat['legal_balls'] % 6) / 10;
        $stat['economy'] = $stat['overs'] > 0 ? round($stat['runs'] / $stat['overs'], 2) : 0;
    }
    
    // Sort bowling by wickets desc, then economy asc
    usort($bowlingStats, function($a, $b) {
        if ($b['wickets'] != $a['wickets']) {
            return $b['wickets'] - $a['wickets'];
        }
        return $a['economy'] - $b['economy'];
    });
    
    $data['batting_stats'] = $battingStats;
    $data['bowling_stats'] = $bowlingStats;
    
    // Calculate partnerships (simplified - based on runs between wickets)
    $partnerships = [];
    $lastWicketScore = 0;
    foreach ($data['fall_of_wickets'] as $fow) {
        $partnership = $fow['score'] - $lastWicketScore;
        $partnerships[] = [
            'wicket' => $fow['wicket'],
            'runs' => $partnership,
            'score' => $fow['score']
        ];
        $lastWicketScore = $fow['score'];
    }
    // Add last partnership if not all out
    if ($data['wickets'] < 10 && count($data['fall_of_wickets']) > 0) {
        $lastFow = end($data['fall_of_wickets']);
        $partnerships[] = [
            'wicket' => $data['wickets'] + 1,
            'runs' => $data['total_runs'] - $lastFow['score'],
            'score' => $data['total_runs']
        ];
    } elseif ($data['wickets'] == 0) {
        $partnerships[] = [
            'wicket' => 1,
            'runs' => $data['total_runs'],
            'score' => $data['total_runs']
        ];
    }
    $data['partnerships'] = $partnerships;
    
    return $data;
}

// Get team names
$sql = "SELECT team_id, name FROM teams WHERE team_id IN (:team1_id, :team2_id)";
$stmt = $db->prepare($sql);
$stmt->execute(['team1_id' => $matchData['team1_id'], 'team2_id' => $matchData['team2_id']]);
$teams = [];
while ($row = $stmt->fetch()) {
    $teams[$row['team_id']] = $row['name'];
}

if ($innings1TeamId) $innings1Data['team_name'] = $teams[$innings1TeamId] ?? '';
if ($innings2TeamId) $innings2Data['team_name'] = $teams[$innings2TeamId] ?? '';

// Get match events for commentary
$sql = "SELECT e.*, p.name as player_name 
        FROM events e
        LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
        LEFT JOIN players p ON pa.player_id = p.player_id
        WHERE e.match_id = :match_id
        ORDER BY e.assigned_server_seq DESC
        LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute(['match_id' => $matchDataId]);
$events = $stmt->fetchAll();

// Format overs display (e.g., 49.5)
function formatOvers($overs) {
    $full = floor($overs);
    $decimals = ($overs - $full) * 10;
    return $full . '.' . round($decimals);
}

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
$stmt->execute(['match_id' => $matchDataId]);
$commentaryEvents = $stmt->fetchAll();

// Get active tab from URL parameter
$activeTab = $_GET['tab'] ?? 'scorecard'; // Default to scorecard if no tab specified

// Valid tabs: info, scorecard, squads, overs
$validTabs = ['info', 'scorecard', 'squads', 'overs'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'scorecard';
}

// Get squads data (team members for both teams)
$team1Squad = [];
$team2Squad = [];
if ($matchDataId) {
    $squadSql = "SELECT pa.*, p.name as player_name, p.player_id, p.date_of_birth, p.batting_hand, p.bowling_style, t.name as team_name, t.team_id
                 FROM player_appearances pa
                 JOIN players p ON pa.player_id = p.player_id
                 JOIN teams t ON pa.team_id = t.team_id
                 WHERE pa.match_id = :match_id
                 ORDER BY t.team_id, p.name";
    $squadStmt = $db->prepare($squadSql);
    $squadStmt->execute(['match_id' => $matchDataId]);
    $allSquad = $squadStmt->fetchAll();
    
    foreach ($allSquad as $member) {
        if ($member['team_id'] == $matchData['team1_id']) {
            $team1Squad[] = $member;
        } elseif ($member['team_id'] == $matchData['team2_id']) {
            $team2Squad[] = $member;
        }
    }
}

// Get ball-by-ball data for overs tab
$ballByBallData = [];
if ($matchDataId) {
    $oversSql = "SELECT e.*, pa.team_id, pa.player_id, p.name as player_name,
                 t1.name as team1_name, t2.name as team2_name
                 FROM events e
                 LEFT JOIN player_appearances pa ON e.appearance_id = pa.appearance_id
                 LEFT JOIN players p ON pa.player_id = p.player_id
                 LEFT JOIN matches m ON e.match_id = m.match_id
                 LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                 LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                 WHERE e.match_id = :match_id
                 ORDER BY e.assigned_server_seq ASC";
    $oversStmt = $db->prepare($oversSql);
    $oversStmt->execute(['match_id' => $matchDataId]);
    $ballByBallData = $oversStmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($matchData['team1_name']) ?> vs <?= htmlspecialchars($matchData['team2_name']) ?> - Live Cricket Score</title>
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/public.css">
    <link rel="stylesheet" href="/cricapp/assets/css/premium-design.css">
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
        
        .scorecard-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        
        .scorecard-table thead {
            background: #f3f4f6;
        }
        
        .scorecard-table th {
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .scorecard-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .scorecard-table tbody tr:hover {
            background: #f9fafb;
        }
        
        .player-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .player-out {
            opacity: 0.7;
        }
        
        .player-dismissal {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: normal;
        }
        
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .fall-of-wickets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .fow-item {
            padding: 0.5rem;
            background: #f9fafb;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        
        .powerplay-info {
            padding: 0.75rem;
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .match-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .match-info-card h2 {
            margin: 0 0 1rem 0;
            color: white;
            font-size: 1.75rem;
        }
        
        .match-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .match-info-item {
            font-size: 0.875rem;
        }
        
        .match-info-item strong {
            display: block;
            margin-bottom: 0.25rem;
            opacity: 0.9;
        }
        
        .result-banner {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .result-banner h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
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

            .live-nav-tabs {
                top: 36px !important;
                padding: 0.375rem 0.75rem !important;
            }

            .innings-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .scorecard-table {
                font-size: 0.875rem;
            }
            
            .scorecard-table th,
            .scorecard-table td {
                padding: 0.5rem;
            }
            
            .fall-of-wickets {
                grid-template-columns: 1fr;
            }
        }
    
    /* Commentary Section Styles */
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
    </style>
</head>
<body>
    <header class="compact-header" style="position: sticky; top: 0; z-index: 200; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 8px 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; justify-content: space-between; max-width: 1200px; margin: 0 auto; position: relative;">
            <div style="font-size: 0.875rem; font-weight: 600;">🏏 Cricket</div>
            <div style="position: relative;">
                <button id="menu-toggle" style="background: rgba(255,255,255,0.2); border: none; color: white; font-size: 1.25rem; padding: 6px 10px; border-radius: 6px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'" onclick="toggleMenu()">☰</button>
                <div id="menu-dropdown" style="display: none; position: absolute; top: calc(100% + 4px); right: 0; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 180px; z-index: 201;">
                    <a href="/cricapp/public/" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏠 Home</a>
                    <a href="/cricapp/public/leaderboard.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">🏆 Leaderboard</a>
                    <a href="/cricapp/public/matches.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; border-bottom: 1px solid #e5e7eb; font-size: 0.875rem;">📅 Matches</a>
                    <a href="/cricapp/public/live.php" style="display: block; padding: 12px 16px; text-decoration: none; color: #1f2937; font-size: 0.875rem;">⚡ Live</a>
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

    <!-- Navigation Tabs -->
    <nav class="live-nav-tabs" style="background: white; border-bottom: 1px solid #e5e7eb; padding: 0.5rem 1rem; position: sticky; top: 40px; z-index: 100;">
        <div class="container" style="display: flex; gap: 1rem; overflow-x: auto;">
            <a href="/cricapp/public/match-view.php?id=<?= $matchDataId ?>&tab=info" 
               class="tab <?= $activeTab === 'info' ? 'active' : '' ?>" 
               style="padding: 0.75rem 1rem; text-decoration: none; color: #6b7280; white-space: nowrap; border-bottom: 2px solid transparent; transition: all 0.2s;">
                Info
            </a>
            <a href="/cricapp/public/live-match.php?id=<?= $matchDataId ?>" 
               class="tab" 
               style="padding: 0.75rem 1rem; text-decoration: none; color: #6b7280; white-space: nowrap; border-bottom: 2px solid transparent; transition: all 0.2s;">
                Live
            </a>
            <a href="/cricapp/public/match-view.php?id=<?= $matchDataId ?>&tab=scorecard" 
               class="tab <?= $activeTab === 'scorecard' ? 'active' : '' ?>" 
               style="padding: 0.75rem 1rem; text-decoration: none; color: #6b7280; white-space: nowrap; border-bottom: 2px solid transparent; transition: all 0.2s;">
                Scorecard
            </a>
            <a href="/cricapp/public/match-view.php?id=<?= $matchDataId ?>&tab=squads" 
               class="tab <?= $activeTab === 'squads' ? 'active' : '' ?>" 
               style="padding: 0.75rem 1rem; text-decoration: none; color: #6b7280; white-space: nowrap; border-bottom: 2px solid transparent; transition: all 0.2s;">
                Squads
            </a>
            <a href="/cricapp/public/match-view.php?id=<?= $matchDataId ?>&tab=overs" 
               class="tab <?= $activeTab === 'overs' ? 'active' : '' ?>" 
               style="padding: 0.75rem 1rem; text-decoration: none; color: #6b7280; white-space: nowrap; border-bottom: 2px solid transparent; transition: all 0.2s;">
                Overs
            </a>
        </div>
    </nav>

    <style>
        .live-nav-tabs .tab.active {
            color: #2563eb !important;
            border-bottom-color: #2563eb !important;
            font-weight: 600;
        }
        .live-nav-tabs .tab:hover {
            color: #1e40af;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>

    <main class="container">
        <!-- Tab: Info -->
        <div class="tab-content <?= $activeTab === 'info' ? 'active' : '' ?>">
            <!-- Match Info Card -->
            <div class="match-info-card">
            <h2><?= htmlspecialchars($matchData['team1_name']) ?> vs <?= htmlspecialchars($matchData['team2_name']) ?></h2>
            <div class="match-info-grid">
                <div class="match-info-item">
                    <strong>Series</strong>
                    <?= htmlspecialchars($matchData['series_name'] ?? 'Match') ?>
                </div>
                <div class="match-info-item">
                    <strong>Venue</strong>
                    <?= htmlspecialchars($matchData['venue'] ?? 'TBA') ?>
                </div>
                <div class="match-info-item">
                    <strong>Date & Time</strong>
                    <?= date('D, M d, Y h:i A', strtotime($matchData['match_date'])) ?>
                </div>
                <?php if ($matchData['toss_winner_name']): ?>
                <div class="match-info-item">
                    <strong>Toss</strong>
                    <?= htmlspecialchars($matchData['toss_winner_name']) ?> won and chose to <?= $matchData['toss_decision'] ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php 
        // Only show result if both teams have actually batted (played at least one ball)
        $bothTeamsBatted = $matchData['state'] === 'completed' && 
                          $innings1Data['legal_balls'] > 0 && 
                          $innings2Data['legal_balls'] > 0;
        ?>
        <?php if ($bothTeamsBatted): ?>
            <?php
            $winner = null;
            $margin = 0;
            $resultText = '';
            
            if ($innings2Data['total_runs'] > $innings1Data['total_runs']) {
                $winner = $innings2Data['team_name'];
                $remainingWickets = 10 - $innings2Data['wickets'];
                $resultText = $winner . " won by " . $remainingWickets . " wicket" . ($remainingWickets != 1 ? 's' : '');
            } elseif ($innings1Data['total_runs'] > $innings2Data['total_runs']) {
                $winner = $innings1Data['team_name'];
                $margin = $innings1Data['total_runs'] - $innings2Data['total_runs'];
                $resultText = $winner . " won by " . $margin . " run" . ($margin != 1 ? 's' : '');
            } else {
                $resultText = "Match Tied";
            }
            ?>
            <div class="result-banner">
                <h3>Match Result</h3>
                <p style="font-size: 1.25rem; margin: 0;"><?= htmlspecialchars($resultText) ?></p>
            </div>
        <?php endif; ?>
        </div>
        <!-- End Tab: Info -->

        <!-- Tab: Scorecard -->
        <div class="tab-content <?= $activeTab === 'scorecard' ? 'active' : '' ?>">
        <div class="scorecard-container">
            <!-- Innings 1 -->
            <?php if ($innings1Data['total_runs'] > 0 || $innings1Data['wickets'] > 0): ?>
            <div class="innings-section">
                <div class="innings-header">
                    <div>
                        <div class="innings-title"><?= htmlspecialchars($innings1Data['team_name']) ?></div>
                    </div>
                    <div class="innings-score">
                        <?= $innings1Data['total_runs'] ?><?= $innings1Data['wickets'] > 0 ? '-' . $innings1Data['wickets'] : '' ?> 
                        (<?= formatOvers($innings1Data['overs']) ?> Ov)
                    </div>
                </div>

                <!-- Batting Scorecard -->
                <div class="section-title">Batting</div>
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
                        <?php if (empty($innings1Data['batting_stats'])): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #6b7280; padding: 2rem;">
                                    No batting data available
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($innings1Data['batting_stats'] as $batter): ?>
                                <tr class="<?= isset($batter['out']) && $batter['out'] ? 'player-out' : '' ?>">
                                    <td>
                                        <span class="player-name">
                                            <?= htmlspecialchars($batter['player_name']) ?>
                                        </span>
                                        <?php if (isset($batter['out']) && $batter['out'] && isset($batter['dismissal'])): ?>
                                            <div class="player-dismissal">
                                                <?php
                                                $dismissal = strtoupper($batter['dismissal']);
                                                echo htmlspecialchars($dismissal);
                                                ?>
                                            </div>
                                        <?php elseif (!isset($batter['out']) || !$batter['out']): ?>
                                            <span style="color: #10b981; font-size: 0.875rem;">not out</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $batter['runs'] ?></td>
                                    <td><?= $batter['balls'] ?></td>
                                    <td><?= $batter['fours'] ?></td>
                                    <td><?= $batter['sixes'] ?></td>
                                    <td><?= number_format($batter['strike_rate'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Bowling Scorecard -->
                <div class="section-title">Bowling</div>
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
                        <?php if (empty($innings1Data['bowling_stats'])): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #6b7280; padding: 2rem;">
                                    No bowling data available
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($innings1Data['bowling_stats'] as $bowler): ?>
                                <tr>
                                    <td class="player-name"><?= htmlspecialchars($bowler['player_name']) ?></td>
                                    <td><?= formatOvers($bowler['overs']) ?></td>
                                    <td><?= $bowler['maidens'] ?></td>
                                    <td><?= $bowler['runs'] ?></td>
                                    <td><?= $bowler['wickets'] ?></td>
                                    <td><?= $bowler['noballs'] ?></td>
                                    <td><?= $bowler['wides'] ?></td>
                                    <td><?= number_format($bowler['economy'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Fall of Wickets -->
                <?php if (!empty($innings1Data['fall_of_wickets'])): ?>
                    <div class="section-title">Fall of Wickets</div>
                    <div class="fall-of-wickets">
                        <?php foreach ($innings1Data['fall_of_wickets'] as $fow): ?>
                            <div class="fow-item">
                                <strong><?= $fow['wicket'] ?>/<?= $fow['score'] ?></strong> - <?= htmlspecialchars($fow['player']) ?> (<?= $fow['over'] ?>)
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Powerplay -->
                <div class="section-title">Powerplay</div>
                <div class="powerplay-info">
                    <strong>0.1 - 10</strong>: <?= $innings1Data['powerplay_runs'] ?> runs
                </div>

                <!-- Partnerships -->
                <?php if (!empty($innings1Data['partnerships'])): ?>
                    <div class="section-title">Partnerships</div>
                    <div class="fall-of-wickets">
                        <?php foreach ($innings1Data['partnerships'] as $part): ?>
                            <div class="fow-item">
                                <strong><?= $part['wicket'] ?>th wicket:</strong> <?= $part['runs'] ?> runs (<?= $part['score'] ?>)
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Innings 2 -->
            <?php if ($innings2Data['total_runs'] > 0 || $innings2Data['wickets'] > 0): ?>
            <div class="innings-section">
                <div class="innings-header">
                    <div>
                        <div class="innings-title"><?= htmlspecialchars($innings2Data['team_name']) ?></div>
                    </div>
                    <div class="innings-score">
                        <?= $innings2Data['total_runs'] ?><?= $innings2Data['wickets'] > 0 ? '-' . $innings2Data['wickets'] : '' ?> 
                        (<?= formatOvers($innings2Data['overs']) ?> Ov)
                    </div>
                </div>

                <!-- Batting Scorecard -->
                <div class="section-title">Batting</div>
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
                        <?php if (empty($innings2Data['batting_stats'])): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #6b7280; padding: 2rem;">
                                    No batting data available
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($innings2Data['batting_stats'] as $batter): ?>
                                <tr class="<?= isset($batter['out']) && $batter['out'] ? 'player-out' : '' ?>">
                                    <td>
                                        <span class="player-name">
                                            <?= htmlspecialchars($batter['player_name']) ?>
                                        </span>
                                        <?php if (isset($batter['out']) && $batter['out'] && isset($batter['dismissal'])): ?>
                                            <div class="player-dismissal">
                                                <?php
                                                $dismissal = strtoupper($batter['dismissal']);
                                                echo htmlspecialchars($dismissal);
                                                ?>
                                            </div>
                                        <?php elseif (!isset($batter['out']) || !$batter['out']): ?>
                                            <span style="color: #10b981; font-size: 0.875rem;">not out</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $batter['runs'] ?></td>
                                    <td><?= $batter['balls'] ?></td>
                                    <td><?= $batter['fours'] ?></td>
                                    <td><?= $batter['sixes'] ?></td>
                                    <td><?= number_format($batter['strike_rate'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Bowling Scorecard -->
                <div class="section-title">Bowling</div>
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
                        <?php if (empty($innings2Data['bowling_stats'])): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #6b7280; padding: 2rem;">
                                    No bowling data available
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($innings2Data['bowling_stats'] as $bowler): ?>
                                <tr>
                                    <td class="player-name"><?= htmlspecialchars($bowler['player_name']) ?></td>
                                    <td><?= formatOvers($bowler['overs']) ?></td>
                                    <td><?= $bowler['maidens'] ?></td>
                                    <td><?= $bowler['runs'] ?></td>
                                    <td><?= $bowler['wickets'] ?></td>
                                    <td><?= $bowler['noballs'] ?></td>
                                    <td><?= $bowler['wides'] ?></td>
                                    <td><?= number_format($bowler['economy'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Fall of Wickets -->
                <?php if (!empty($innings2Data['fall_of_wickets'])): ?>
                    <div class="section-title">Fall of Wickets</div>
                    <div class="fall-of-wickets">
                        <?php foreach ($innings2Data['fall_of_wickets'] as $fow): ?>
                            <div class="fow-item">
                                <strong><?= $fow['wicket'] ?>/<?= $fow['score'] ?></strong> - <?= htmlspecialchars($fow['player']) ?> (<?= $fow['over'] ?>)
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Powerplay -->
                <div class="section-title">Powerplay</div>
                <div class="powerplay-info">
                    <strong>0.1 - 10</strong>: <?= $innings2Data['powerplay_runs'] ?> runs
                </div>

                <!-- Partnerships -->
                <?php if (!empty($innings2Data['partnerships'])): ?>
                    <div class="section-title">Partnerships</div>
                    <div class="fall-of-wickets">
                        <?php foreach ($innings2Data['partnerships'] as $part): ?>
                            <div class="fow-item">
                                <strong><?= $part['wicket'] ?>th wicket:</strong> <?= $part['runs'] ?> runs (<?= $part['score'] ?>)
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Live Commentary Section -->
        <div class="commentary-section">
            <div class="section-title">Live Commentary</div>
            <div class="commentary-feed" id="commentary-feed">
                <?= renderCommentary($commentaryEvents, $db, $matchDataId) ?>
            </div>
        </div>
        </div>
        <!-- End Tab: Scorecard -->

        <!-- Tab: Squads -->
        <div class="tab-content <?= $activeTab === 'squads' ? 'active' : '' ?>">
            <div class="scorecard-container">
                <h2 style="margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700;">Team Squads</h2>
                
                <!-- Team 1 Squad -->
                <div class="innings-section">
                    <div class="innings-header">
                        <div class="innings-title"><?= htmlspecialchars($matchData['team1_name']) ?></div>
                        <div style="color: #6b7280; font-size: 0.875rem;"><?= count($team1Squad) ?> players</div>
                    </div>
                    
                    <?php if (empty($team1Squad)): ?>
                        <p style="color: #6b7280; padding: 2rem; text-align: center;">No squad data available</p>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                            <?php foreach ($team1Squad as $player): ?>
                                <div class="fow-item" style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                                        <?= htmlspecialchars($player['player_name']) ?>
                                    </div>
                                    <?php if ($player['date_of_birth']): ?>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            DOB: <?= date('M d, Y', strtotime($player['date_of_birth'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($player['batting_hand'])): ?>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            Batting: <?= ucfirst($player['batting_hand']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($player['bowling_style'])): ?>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            Bowling: <?= ucwords(str_replace('-', ' ', $player['bowling_style'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Team 2 Squad -->
                <div class="innings-section">
                    <div class="innings-header">
                        <div class="innings-title"><?= htmlspecialchars($matchData['team2_name']) ?></div>
                        <div style="color: #6b7280; font-size: 0.875rem;"><?= count($team2Squad) ?> players</div>
                    </div>
                    
                    <?php if (empty($team2Squad)): ?>
                        <p style="color: #6b7280; padding: 2rem; text-align: center;">No squad data available</p>
                    <?php else: ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                            <?php foreach ($team2Squad as $player): ?>
                                <div class="fow-item" style="padding: 1rem; background: #f9fafb; border-radius: 8px;">
                                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 0.5rem;">
                                        <?= htmlspecialchars($player['player_name']) ?>
                                    </div>
                                    <?php if ($player['date_of_birth']): ?>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            DOB: <?= date('M d, Y', strtotime($player['date_of_birth'])) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($player['batting_hand'])): ?>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            Batting: <?= ucfirst($player['batting_hand']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($player['bowling_style'])): ?>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            Bowling: <?= ucwords(str_replace('-', ' ', $player['bowling_style'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- End Tab: Squads -->

        <!-- Tab: Overs (Ball-by-Ball) -->
        <div class="tab-content <?= $activeTab === 'overs' ? 'active' : '' ?>">
            <div class="scorecard-container">
                <h2 style="margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700;">Ball-by-Ball Commentary</h2>
                
                <?php if (!empty($ballByBallData)): ?>
                    <div class="innings-section">
                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php 
                            // Organize events by over
                            $eventsByOver = [];
                            $currentOver = 0;
                            $ballsInOver = 0;
                            
                            foreach ($ballByBallData as $event) {
                                $payload = json_decode($event['payload_json'] ?? '{}', true);
                                if (!is_array($payload)) continue;
                                
                                $eventType = $payload['event_type'] ?? '';
                                
                                // Count legal balls
                                if ($eventType === 'runs' || $eventType === 'wicket') {
                                    $ballsInOver++;
                                    if ($ballsInOver > 6) {
                                        $currentOver++;
                                        $ballsInOver = 1;
                                    }
                                } elseif ($eventType === 'extra') {
                                    if ($payload['extra_type'] !== 'wide' && $payload['extra_type'] !== 'noball') {
                                        $ballsInOver++;
                                        if ($ballsInOver > 6) {
                                            $currentOver++;
                                            $ballsInOver = 1;
                                        }
                                    }
                                }
                                
                                $overKey = $currentOver . '.' . $ballsInOver;
                                if (!isset($eventsByOver[$overKey])) {
                                    $eventsByOver[$overKey] = [];
                                }
                                $eventsByOver[$overKey][] = $event;
                            }
                            ?>
                            
                            <?php foreach ($eventsByOver as $overBall => $overEvents): ?>
                                <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f9fafb; border-radius: 8px; border-left: 4px solid #2563eb;">
                                    <h3 style="margin: 0 0 1rem 0; font-size: 1rem; font-weight: 700; color: #1f2937;">
                                        Over <?= $overBall ?>
                                    </h3>
                                    
                                    <?php foreach ($overEvents as $event): ?>
                                        <div style="padding: 0.75rem; margin-bottom: 0.5rem; background: white; border-radius: 4px; border-left: 3px solid #1e40af;">
                                            <?php 
                                            $payload = json_decode($event['payload_json'] ?? '{}', true);
                                            $commentaryText = $event['commentary'] ?? '';
                                            
                                            // Generate commentary from payload
                                            if (empty($commentaryText) && is_array($payload)) {
                                                if ($payload['event_type'] === 'runs') {
                                                    $commentaryText = ($event['player_name'] ?? 'Player') . ' scores ' . $payload['runs'] . ' run' . ($payload['runs'] != 1 ? 's' : '');
                                                } elseif ($payload['event_type'] === 'wicket') {
                                                    $commentaryText = 'WICKET! ' . ($event['player_name'] ?? 'Player') . ' - ' . strtoupper($payload['wicket_type'] ?? 'out');
                                                } elseif ($payload['event_type'] === 'extra') {
                                                    $commentaryText = ucfirst($payload['extra_type'] ?? 'Extra');
                                                    if (isset($payload['runs']) && $payload['runs'] > 0) {
                                                        $commentaryText .= ' - ' . $payload['runs'] . ' run' . ($payload['runs'] != 1 ? 's' : '');
                                                    }
                                                } else {
                                                    $commentaryText = 'Match event';
                                                }
                                            }
                                            ?>
                                            <div style="font-size: 0.875rem; color: #374151;">
                                                <?= htmlspecialchars($commentaryText) ?>
                                                <?php if (isset($event['created_at'])): ?>
                                                    <span style="font-size: 0.75rem; color: #6b7280; margin-left: 0.5rem;">
                                                        (<?= date('h:i A', strtotime($event['created_at'])) ?>)
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="innings-section">
                        <p style="color: #6b7280; padding: 2rem; text-align: center;">No ball-by-ball data available yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- End Tab: Overs -->


        <div class="text-center mt-2">
            <a href="/cricapp/public/" class="btn-premium btn-premium-accent">Back to Home</a>
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
    <script src="/cricapp/assets/js/bottom-nav.js"></script>
</body>
</html>
