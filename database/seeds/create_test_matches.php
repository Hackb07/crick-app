<?php
/**
 * Create Test Matches with Dummy Data
 * 
 * This script creates 3 complete test matches with:
 * - Teams and players
 * - Captains assigned
 * - Guest players
 * - Complete scorecards (10 overs each)
 * - Wickets with fielders
 * - Auto-generated commentary
 * - Varied scores and dismissals
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

echo "🏏 CricApp Test Data Generator\n";
echo "================================\n\n";

$db = Database::getInstance()->getConnection();

// Start transaction
$db->beginTransaction();

try {
    // ============================================
    // STEP 1: Create Teams
    // ============================================
    echo "📋 Creating teams...\n";
    
    $teams = [
        ['name' => 'Mumbai Indians', 'short_name' => 'MI'],
        ['name' => 'Chennai Super Kings', 'short_name' => 'CSK'],
        ['name' => 'Royal Challengers', 'short_name' => 'RCB'],
        ['name' => 'Kolkata Knights', 'short_name' => 'KKR']
    ];
    
    $teamIds = [];
    foreach ($teams as $team) {
        $stmt = $db->prepare("INSERT INTO teams (name, short_name, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$team['name'], $team['short_name']]);
        $teamIds[$team['short_name']] = $db->lastInsertId();
        echo "  ✓ Created {$team['name']}\n";
    }
    
    // ============================================
    // STEP 2: Create Players
    // ============================================
    echo "\n👥 Creating players...\n";
    
    $players = [
        // Mumbai Indians
        ['name' => 'Rohit Sharma', 'team' => 'MI', 'batting' => 'Right', 'bowling' => 'Right-arm Medium', 'captain' => true],
        ['name' => 'Ishan Kishan', 'team' => 'MI', 'batting' => 'Left', 'bowling' => null],
        ['name' => 'Suryakumar Yadav', 'team' => 'MI', 'batting' => 'Right', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Hardik Pandya', 'team' => 'MI', 'batting' => 'Right', 'bowling' => 'Right-arm Fast'],
        ['name' => 'Kieron Pollard', 'team' => 'MI', 'batting' => 'Right', 'bowling' => 'Right-arm Medium', 'guest' => true],
        ['name' => 'Jasprit Bumrah', 'team' => 'MI', 'batting' => 'Right', 'bowling' => 'Right-arm Fast'],
        ['name' => 'Trent Boult', 'team' => 'MI', 'batting' => 'Left', 'bowling' => 'Left-arm Fast', 'guest' => true],
        ['name' => 'Rahul Chahar', 'team' => 'MI', 'batting' => 'Right', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Krunal Pandya', 'team' => 'MI', 'batting' => 'Left', 'bowling' => 'Left-arm Spin'],
        ['name' => 'Quinton de Kock', 'team' => 'MI', 'batting' => 'Left', 'bowling' => null, 'guest' => true],
        ['name' => 'Nathan Coulter-Nile', 'team' => 'MI', 'batting' => 'Right', 'bowling' => 'Right-arm Fast', 'guest' => true],
        
        // Chennai Super Kings
        ['name' => 'MS Dhoni', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => null, 'captain' => true],
        ['name' => 'Faf du Plessis', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => null, 'guest' => true],
        ['name' => 'Ruturaj Gaikwad', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Ambati Rayudu', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Ravindra Jadeja', 'team' => 'CSK', 'batting' => 'Left', 'bowling' => 'Left-arm Spin'],
        ['name' => 'Dwayne Bravo', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => 'Right-arm Medium', 'guest' => true],
        ['name' => 'Deepak Chahar', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => 'Right-arm Medium'],
        ['name' => 'Shardul Thakur', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => 'Right-arm Fast'],
        ['name' => 'Moeen Ali', 'team' => 'CSK', 'batting' => 'Left', 'bowling' => 'Right-arm Spin', 'guest' => true],
        ['name' => 'Sam Curran', 'team' => 'CSK', 'batting' => 'Left', 'bowling' => 'Left-arm Medium', 'guest' => true],
        ['name' => 'Imran Tahir', 'team' => 'CSK', 'batting' => 'Right', 'bowling' => 'Right-arm Spin', 'guest' => true],
        
        // Royal Challengers
        ['name' => 'Virat Kohli', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => 'Right-arm Medium', 'captain' => true],
        ['name' => 'AB de Villiers', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => null, 'guest' => true],
        ['name' => 'Glenn Maxwell', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => 'Right-arm Spin', 'guest' => true],
        ['name' => 'Devdutt Padikkal', 'team' => 'RCB', 'batting' => 'Left', 'bowling' => null],
        ['name' => 'Yuzvendra Chahal', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Mohammed Siraj', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => 'Right-arm Fast'],
        ['name' => 'Kyle Jamieson', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => 'Right-arm Fast', 'guest' => true],
        ['name' => 'Washington Sundar', 'team' => 'RCB', 'batting' => 'Left', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Harshal Patel', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => 'Right-arm Medium'],
        ['name' => 'Dan Christian', 'team' => 'RCB', 'batting' => 'Right', 'bowling' => 'Right-arm Medium', 'guest' => true],
        ['name' => 'Shahbaz Ahmed', 'team' => 'RCB', 'batting' => 'Left', 'bowling' => 'Left-arm Spin'],
        
        // Kolkata Knights
        ['name' => 'Eoin Morgan', 'team' => 'KKR', 'batting' => 'Left', 'bowling' => null, 'captain' => true, 'guest' => true],
        ['name' => 'Shubman Gill', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => null],
        ['name' => 'Nitish Rana', 'team' => 'KKR', 'batting' => 'Left', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Andre Russell', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => 'Right-arm Fast', 'guest' => true],
        ['name' => 'Sunil Narine', 'team' => 'KKR', 'batting' => 'Left', 'bowling' => 'Right-arm Spin', 'guest' => true],
        ['name' => 'Pat Cummins', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => 'Right-arm Fast', 'guest' => true],
        ['name' => 'Varun Chakravarthy', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => 'Right-arm Spin'],
        ['name' => 'Rahul Tripathi', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => null],
        ['name' => 'Dinesh Karthik', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => null],
        ['name' => 'Lockie Ferguson', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => 'Right-arm Fast', 'guest' => true],
        ['name' => 'Prasidh Krishna', 'team' => 'KKR', 'batting' => 'Right', 'bowling' => 'Right-arm Fast']
    ];
    
    $playerIds = [];
    foreach ($players as $player) {
        $stmt = $db->prepare("INSERT INTO players (name, batting_hand, bowling_style, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$player['name'], $player['batting'], $player['bowling']]);
        $playerId = $db->lastInsertId();
        $playerIds[$player['name']] = [
            'id' => $playerId,
            'team' => $player['team'],
            'captain' => $player['captain'] ?? false,
            'guest' => $player['guest'] ?? false,
            'batting' => $player['batting'],
            'bowling' => $player['bowling']
        ];
        echo "  ✓ Created {$player['name']}\n";
    }
    
    // ============================================
    // STEP 3: Create Series
    // ============================================
    echo "\n🏆 Creating series...\n";
    
    $stmt = $db->prepare("INSERT INTO series (name, start_date, end_date, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute(['Test Tournament 2024', '2024-01-01', '2024-01-31']);
    $seriesId = $db->lastInsertId();
    echo "  ✓ Created Test Tournament 2024\n";
    
    // ============================================
    // STEP 4: Create Matches
    // ============================================
    echo "\n🎯 Creating matches...\n";
    
    $matches = [
        ['team1' => 'MI', 'team2' => 'CSK', 'venue' => 'Wankhede Stadium', 'date' => '2024-01-10'],
        ['team1' => 'RCB', 'team2' => 'KKR', 'venue' => 'M. Chinnaswamy Stadium', 'date' => '2024-01-15'],
        ['team1' => 'MI', 'team2' => 'RCB', 'venue' => 'Wankhede Stadium', 'date' => '2024-01-20']
    ];
    
    $matchIds = [];
    foreach ($matches as $idx => $match) {
        $stmt = $db->prepare("INSERT INTO matches (team1_id, team2_id, series_id, match_date, venue, overs_per_innings, state, created_at) 
                              VALUES (?, ?, ?, ?, ?, 10, 'completed', NOW())");
        $stmt->execute([
            $teamIds[$match['team1']],
            $teamIds[$match['team2']],
            $seriesId,
            $match['date'],
            $match['venue']
        ]);
        $matchId = $db->lastInsertId();
        $matchIds[] = [
            'id' => $matchId,
            'team1' => $match['team1'],
            'team2' => $match['team2']
        ];
        echo "  ✓ Created Match " . ($idx + 1) . ": {$match['team1']} vs {$match['team2']}\n";
    }
    
    // ============================================
    // STEP 5: Assign Players to Matches
    // ============================================
    echo "\n👥 Assigning players to matches...\n";
    
    foreach ($matchIds as $match) {
        $team1Players = array_filter($playerIds, fn($p) => $p['team'] === $match['team1']);
        $team2Players = array_filter($playerIds, fn($p) => $p['team'] === $match['team2']);
        
        // Assign team 1 players
        foreach ($team1Players as $name => $player) {
            $stmt = $db->prepare("INSERT INTO player_appearances (player_id, match_id, team_id, is_guest, is_captain, created_at) 
                                  VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $player['id'],
                $match['id'],
                $teamIds[$match['team1']],
                $player['guest'] ? 1 : 0,
                $player['captain'] ? 1 : 0
            ]);
        }
        
        // Assign team 2 players
        foreach ($team2Players as $name => $player) {
            $stmt = $db->prepare("INSERT INTO player_appearances (player_id, match_id, team_id, is_guest, is_captain, created_at) 
                                  VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $player['id'],
                $match['id'],
                $teamIds[$match['team2']],
                $player['guest'] ? 1 : 0,
                $player['captain'] ? 1 : 0
            ]);
        }
        
        echo "  ✓ Assigned players to Match {$match['id']}\n";
    }
    
    // ============================================
    // STEP 6: Generate Match Events
    // ============================================
    echo "\n⚡ Generating match events...\n";
    
    $eventModel = new Event();
    $commentaryModel = new Commentary();
    
    foreach ($matchIds as $matchIdx => $match) {
        echo "\n  📊 Match " . ($matchIdx + 1) . ": {$match['team1']} vs {$match['team2']}\n";
        
        $team1Players = array_filter($playerIds, fn($p) => $p['team'] === $match['team1']);
        $team2Players = array_filter($playerIds, fn($p) => $p['team'] === $match['team2']);
        
        // Get player IDs for easy access
        $team1Batsmen = array_values(array_map(fn($p) => $p['id'], array_slice($team1Players, 0, 11)));
        $team2Batsmen = array_values(array_map(fn($p) => $p['id'], array_slice($team2Players, 0, 11)));
        $team1Bowlers = array_values(array_map(fn($p) => $p['id'], array_filter($team1Players, fn($p) => $p['bowling'] !== null)));
        $team2Bowlers = array_values(array_map(fn($p) => $p['id'], array_filter($team2Players, fn($p) => $p['bowling'] !== null)));
        
        // Simulate both innings
        for ($innings = 1; $innings <= 2; $innings++) {
            $battingTeam = $innings === 1 ? $team1Batsmen : $team2Batsmen;
            $bowlingTeam = $innings === 1 ? $team2Bowlers : $team1Bowlers;
            $fieldingTeam = $innings === 1 ? $team2Batsmen : $team1Batsmen;
            
            $striker = $battingTeam[0];
            $nonStriker = $battingTeam[1];
            $bowler = $bowlingTeam[0];
            $bowlerIndex = 0;
            $batsmanIndex = 2;
            $wickets = 0;
            $totalRuns = 0;
            
            echo "    Innings $innings: ";
            
            // 10 overs = 60 balls
            for ($ball = 0; $ball < 60 && $wickets < 10; $ball++) {
                // Change bowler every 6 balls
                if ($ball % 6 === 0 && $ball > 0) {
                    $bowlerIndex = ($bowlerIndex + 1) % count($bowlingTeam);
                    $bowler = $bowlingTeam[$bowlerIndex];
                }
                
                // Random outcome
                $rand = rand(1, 100);
                
                if ($rand <= 10 && $wickets < 10) {
                    // Wicket (10% chance)
                    $dismissalTypes = ['bowled', 'caught', 'lbw', 'stumped', 'run out'];
                    $dismissal = $dismissalTypes[array_rand($dismissalTypes)];
                    $fielder = in_array($dismissal, ['caught', 'stumped', 'run out']) ? $fieldingTeam[array_rand($fieldingTeam)] : null;
                    
                    $payload = [
                        'type' => 'wicket',
                        'runs' => 0,
                        'striker_id' => $striker,
                        'non_striker_id' => $nonStriker,
                        'bowler_id' => $bowler,
                        'dismissal_type' => $dismissal,
                        'fielder_id' => $fielder,
                        'innings' => $innings
                    ];
                    
                    $wickets++;
                    
                    // New batsman
                    if ($batsmanIndex < count($battingTeam)) {
                        $striker = $battingTeam[$batsmanIndex++];
                    }
                } elseif ($rand <= 20) {
                    // Extra (10% chance)
                    $extraTypes = ['wide', 'no_ball', 'bye', 'leg_bye'];
                    $extraType = $extraTypes[array_rand($extraTypes)];
                    $runs = rand(1, 2);
                    
                    $payload = [
                        'type' => 'extra',
                        'runs' => $runs,
                        'striker_id' => $striker,
                        'non_striker_id' => $nonStriker,
                        'bowler_id' => $bowler,
                        'extra_type' => $extraType,
                        'innings' => $innings
                    ];
                    
                    $totalRuns += $runs;
                } else {
                    // Normal run
                    $runOptions = [0, 0, 0, 1, 1, 1, 2, 2, 3, 4, 6];
                    $runs = $runOptions[array_rand($runOptions)];
                    
                    $payload = [
                        'type' => 'run',
                        'runs' => $runs,
                        'striker_id' => $striker,
                        'non_striker_id' => $nonStriker,
                        'bowler_id' => $bowler,
                        'innings' => $innings
                    ];
                    
                    $totalRuns += $runs;
                    
                    // Rotate strike on odd runs
                    if ($runs % 2 === 1) {
                        $temp = $striker;
                        $striker = $nonStriker;
                        $nonStriker = $temp;
                    }
                }
                
                // Insert event
                $event = [
                    'event_uuid' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                    ),
                    'client_id' => 'test-seed',
                    'client_ts' => date('Y-m-d H:i:s'),
                    'client_base_seq' => $ball,
                    'ball_index' => $ball,
                    'payload_json' => $payload
                ];
                
                // Use Event model to insert (which will auto-generate commentary)
                $stmt = $db->prepare("INSERT INTO events (event_uuid, match_id, client_id, client_ts, client_base_seq, assigned_server_seq, ball_index, payload_json, fielder_id, created_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $event['event_uuid'],
                    $match['id'],
                    $event['client_id'],
                    $event['client_ts'],
                    $event['client_base_seq'],
                    $ball + 1,
                    $event['ball_index'],
                    json_encode($payload),
                    $payload['fielder_id'] ?? null
                ]);
                
                $eventId = $db->lastInsertId();
                
                // Generate commentary
                $commentaryModel->generateAndSave([
                    'match_id' => $match['id'],
                    'event_id' => $eventId
                ], $payload);
            }
            
            echo "$totalRuns/$wickets\n";
        }
    }

    // Calculate stats for all matches
    echo "\n📊 Calculating stats...\n";
    require_once __DIR__ . '/../../classes/StatsCalculator.php';
    $statsCalculator = new StatsCalculator();
    
    foreach ($matchIds as $matchId) {
        $statsCalculator->updateMatchStats($matchId);
        echo "  ✓ Stats updated for Match #$matchId\n";
    }
    
    // Commit transaction
    $db->commit();
    
    echo "\n✅ SUCCESS! Test data created successfully!\n\n";
    echo "📊 Summary:\n";
    echo "  - Teams: " . count($teams) . "\n";
    echo "  - Players: " . count($players) . "\n";
    echo "  - Matches: " . count($matchIds) . "\n";
    echo "  - Events: ~360 (60 balls × 2 innings × 3 matches)\n";
    echo "  - Commentary: Auto-generated for all events\n\n";
    echo "🎯 You can now:\n";
    echo "  1. View matches in the admin panel\n";
    echo "  2. Check scorecards with team toggle\n";
    echo "  3. View squad tab with captains\n";
    echo "  4. Read ball-by-ball commentary\n";
    echo "  5. See fielder information in wickets\n";
    echo "  6. Check leaderboards\n\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
