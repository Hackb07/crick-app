<?php
require_once __DIR__ . '/includes/bootstrap.php';

$db = Database::getInstance()->getConnection();
$matchModel = new MatchModel();

// Ensure teams exist
$teams = [
    ['India', 'IND', 'https://flagcdn.com/w80/in.png'],
    ['Australia', 'AUS', 'https://flagcdn.com/w80/au.png'],
    ['England', 'ENG', 'https://flagcdn.com/w80/gb-eng.png'],
    ['New Zealand', 'NZ', 'https://flagcdn.com/w80/nz.png'],
    ['South Africa', 'SA', 'https://flagcdn.com/w80/za.png']
];

$teamIds = [];
foreach ($teams as $t) {
    $stmt = $db->prepare("SELECT team_id FROM teams WHERE name = ?");
    $stmt->execute([$t[0]]);
    $id = $stmt->fetchColumn();
    
    if (!$id) {
        $stmt = $db->prepare("INSERT INTO teams (name, short_name, logo, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$t[0], $t[1], $t[2]]);
        $id = $db->lastInsertId();
    }
    $teamIds[] = $id;
}

// Ensure Series exists
$stmt = $db->prepare("SELECT series_id FROM series WHERE name = 'Freedom Series 2025'");
$stmt->execute();
$seriesId = $stmt->fetchColumn();

if (!$seriesId) {
    $stmt = $db->prepare("INSERT INTO series (name, start_date, end_date, description, created_at) VALUES ('Freedom Series 2025', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), 'Test Series', NOW())");
    $stmt->execute();
    $seriesId = $db->lastInsertId();
}

// Create 5 Matches
$venues = ['Melbourne', 'Mumbai', 'London', 'Cape Town', 'Auckland'];

for ($i = 0; $i < 5; $i++) {
    $t1 = $teamIds[$i % count($teamIds)];
    $t2 = $teamIds[($i + 1) % count($teamIds)];
    
    $data = [
        'series_id' => $seriesId,
        'team1_id' => $t1,
        'team2_id' => $t2,
        'match_date' => date('Y-m-d H:i:s', strtotime("+" . ($i * 2) . " days")),
        'venue' => $venues[$i],
        'overs_per_innings' => 20,
        'created_by' => 1
    ];
    
    $matchId = $matchModel->create($data);
    
    // Set some to live/completed for variety
    if ($i == 0) {
        $matchModel->update($matchId, ['state' => 'live', 'toss_winner_id' => $t1, 'toss_decision' => 'bat']);
    } elseif ($i == 1) {
        $matchModel->update($matchId, ['state' => 'completed', 'winner_id' => $t1]);
    }
    
    echo "Created Match $matchId: {$teams[$i % 5][0]} vs {$teams[($i + 1) % 5][0]}\n";
}
