<?php
/**
 * Create Match - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

requireLogin();

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$error = '';
$success = '';

$teamModel = new Team();
$teams = $teamModel->getAll();

$seriesModel = new Series();
$series = $seriesModel->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireCsrfToken();

        $data = [
            'team1_id' => (int)getPost('team1_id', 0),
            'team2_id' => (int)getPost('team2_id', 0),
            'series_id' => getPost('series_id') ? (int)getPost('series_id') : null,
            'match_date' => getPost('match_date'),
            'venue' => getPost('venue', ''),
            'overs_per_innings' => (float)getPost('overs_per_innings', 20.0), // Default 20 overs if not specified
            'match_type' => getPost('match_type', 'limited_overs'),
            'ball_type' => getPost('ball_type', 'leather'),
            'pitch_type' => getPost('pitch_type', 'turf'),
            'umpire1_name' => getPost('umpire1_name'),
            'umpire2_name' => getPost('umpire2_name'),
            'scorer_name' => getPost('scorer_name'),
            'created_by' => getUserId()
        ];
        
        // Validate teams
        if (!$data['team1_id'] || !$data['team2_id']) {
            $error = 'Please select both teams';
        } elseif ($data['team1_id'] === $data['team2_id']) {
            $error = 'Team 1 and Team 2 must be different';
        } elseif ($data['overs_per_innings'] < 1 || $data['overs_per_innings'] > 50) {
            $error = 'Overs per innings must be between 1 and 50';
        } else {
            $matchModel = new MatchModel();
            $matchId = $matchModel->create($data);
            
            if ($matchId) {
                // Log action
                logAction('create', 'match', $matchId, ['teams' => [$data['team1_id'], $data['team2_id']]]);
                
                // Redirect to new Match Console
                header('Location: ' . adminUrl('matches/console.php?id=' . $matchId));
                exit;
            } else {
                $error = 'Failed to create match';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render
renderAdminLayout('Create Match', 'matches/create', [
    'error' => $error,
    'success' => $success,
    'teams' => $teams,
    'series' => $series
], [
    'activeMenu' => 'matches',
    'headerActions' => [
        [
            'url' => adminUrl('matches/'),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);
