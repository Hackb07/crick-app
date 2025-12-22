<?php
/**
 * Test Script: Verify Match Flow
 * 
 * Simulates the lifecycle of a match to verify core functionality.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../classes/MatchModel.php';
require_once __DIR__ . '/../classes/Team.php';
require_once __DIR__ . '/../classes/Player.php';
require_once __DIR__ . '/../classes/MatchAdminService.php';
require_once __DIR__ . '/../classes/StatsCalculator.php';
require_once __DIR__ . '/../classes/Event.php';

// Helper for output
function testLog($message, $status = 'INFO') {
    $color = match($status) {
        'PASS' => "\033[32m", // Green
        'FAIL' => "\033[31m", // Red
        'INFO' => "\033[36m", // Cyan
        default => "\033[0m"
    };
    echo "{$color}[{$status}] {$message}\033[0m\n";
}

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Setup Data
    $teamModel = new Team();
    $teams = $teamModel->getAll();
    if (count($teams) < 2) {
        throw new Exception("Need at least 2 teams to run test");
    }
    $team1 = $teams[0];
    $team2 = $teams[1];
    
    testLog("Selected Teams: {$team1['name']} vs {$team2['name']}");

    // 2. Test Create Match (create.php logic)
    $matchModel = new MatchModel();
    $matchData = [
        'team1_id' => $team1['team_id'],
        'team2_id' => $team2['team_id'],
        'series_id' => null,
        'match_date' => date('Y-m-d H:i:s'),
        'venue' => 'Test Venue',
        'overs_per_innings' => 5,
        'created_by' => 1 // Assuming admin ID 1
    ];
    
    $matchId = $matchModel->create($matchData);
    
    if ($matchId) {
        testLog("Match Created ID: $matchId", 'PASS');
    } else {
        throw new Exception("Failed to create match");
    }

    // 3. Test View Match (view.php logic)
    $match = $matchModel->getById($matchId);
    if ($match && $match['venue'] === 'Test Venue') {
        testLog("Match Retrieved Successfully", 'PASS');
    } else {
        throw new Exception("Failed to retrieve match");
    }

    // 4. Test Console Logic (console.php - Squad Update)
    $matchAdminService = new MatchAdminService();
    $playerModel = new Player();
    
    // Get all players (since teams might not have history yet)
    $allPlayers = $playerModel->getAll();
    if (count($allPlayers) < 4) {
        throw new Exception("Need at least 4 players in the database to run test");
    }
    
    // Assign first 2 to team 1, next 2 to team 2
    $team1Squad = [$allPlayers[0]['player_id'], $allPlayers[1]['player_id']];
    $team2Squad = [$allPlayers[2]['player_id'], $allPlayers[3]['player_id']];

    // Update Squads
    $matchAdminService->setSquad($matchId, $team1['team_id'], $team1Squad);
    $matchAdminService->setSquad($matchId, $team2['team_id'], $team2Squad);
    
    // Verify Squads
    $appearances = $playerModel->getByTeamForMatch($matchId, $team1['team_id']);
    if (count($appearances) === 2) {
        testLog("Squads Updated Successfully", 'PASS');
    } else {
        throw new Exception("Squad update failed");
    }

    // 5. Test Toss & Start (console.php logic)
    $matchAdminService->setToss($matchId, $team1['team_id'], 'bat');
    $matchAdminService->startMatch($matchId);
    
    $match = $matchModel->getById($matchId);
    if ($match['state'] === 'live' && $match['toss_winner_id'] == $team1['team_id']) {
        testLog("Match Started Successfully", 'PASS');
    } else {
        throw new Exception("Failed to start match");
    }

    // 6. Test Scoring (scorer.php logic)
    // We need to simulate events. 
    // Ideally we'd use the API handler, but direct DB insertion via Event model is closer to the core logic being tested.
    
    $eventModel = new Event();
    $strikerId = $team1Squad[0];
    $nonStrikerId = $team1Squad[1];
    $bowlerId = $team2Squad[0];
    
    // Record a run using batchInsert
    $payload = [
        'type' => 'run',
        'runs' => 1,
        'striker_id' => $strikerId,
        'non_striker_id' => $nonStrikerId,
        'bowler_id' => $bowlerId,
        'innings' => 1
    ];
    
    $eventData = [
        'event_uuid' => uniqid('evt_'),
        'client_id' => 'test_client',
        'client_ts' => time(),
        'ball_index' => 0,
        'payload_json' => $payload
    ];
    
    $result = $eventModel->batchInsert($matchId, [$eventData], 0);
    
    if (!$result['success']) {
        throw new Exception("Failed to insert event: " . ($result['error'] ?? 'Unknown error'));
    }
    
    // Update Stats
    $statsCalculator = new StatsCalculator();
    $statsCalculator->updateMatchStats($matchId);
    
    // Verify Stats
    $battingStats = $statsCalculator->getBattingStats($matchId);
    $found = false;
    foreach ($battingStats as $stat) {
        if ($stat['player_id'] == $strikerId && $stat['runs'] == 1) {
            $found = true;
            break;
        }
    }
    
    if ($found) {
        testLog("Scoring & Stats Calculation Verified", 'PASS');
    } else {
        throw new Exception("Scoring verification failed");
    }

    // 7. Test Change Innings (change-innings.php logic)
    // Simulate what change-innings.php does: update current_innings to 2
    $matchModel->update($matchId, ['current_innings' => 2]);
    
    $match = $matchModel->getById($matchId);
    if ($match['current_innings'] == 2) {
        testLog("Innings Changed Successfully", 'PASS');
    } else {
        throw new Exception("Failed to change innings");
    }

    // 8. Test Delete Match (delete.php logic)
    $matchModel->delete($matchId);
    $match = $matchModel->getById($matchId);
    
    if (!$match) {
        testLog("Match Deleted Successfully", 'PASS');
    } else {
        throw new Exception("Failed to delete match");
    }

} catch (Exception $e) {
    testLog("Error: " . $e->getMessage(), 'FAIL');
    testLog("Trace: " . $e->getTraceAsString(), 'FAIL');
    exit(1);
}
