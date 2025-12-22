<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /cricapp/admin/login.php');
    exit;
}

$matchId = $_POST['match_id'] ?? 0;
$action = $_POST['action'] ?? '';

if (!$matchId || !$action) {
    header('Location: /cricapp/admin/matches/');
    exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';

$user = $_SESSION['user'];

// Use API to perform action
$url = 'http://localhost/cricapp/api/v1/matches.php';
$data = [];

if ($action === 'toss') {
    // POST /api/v1/matches.php/{matchId}/toss
    $url .= '/' . $matchId . '/toss';
    $data = [
        'toss_winner_id' => (int)($_POST['toss_winner_id'] ?? 0),
        'toss_decision' => $_POST['toss_decision'] ?? ''
    ];
    $method = 'POST';
} elseif ($action === 'add-player') {
    // POST /api/v1/matches.php/{matchId}/players
    $url .= '/' . $matchId . '/players';
    $data = [
        'player_id' => (int)($_POST['player_id'] ?? 0),
        'team_id' => (int)($_POST['team_id'] ?? 0)
    ];
    $method = 'POST';
} elseif ($action === 'remove-player') {
    // DELETE /api/v1/matches.php/{matchId}/players/{appearance_id}
    $appearanceId = (int)($_POST['appearance_id'] ?? 0);
    $url .= '/' . $matchId . '/players/' . $appearanceId;
    $data = [];
    $method = 'DELETE';
} elseif ($action === 'start') {
    // POST /api/v1/matches.php/{matchId}/start
    $url .= '/' . $matchId . '/start';
    $method = 'POST';
    // No body needed for start
} elseif ($action === 'finalize') {
    // POST /api/v1/matches.php/{matchId}/finalize
    $url .= '/' . $matchId . '/finalize';
    $data = ['reason' => 'Manually finalized by admin'];
    $method = 'POST';
} elseif ($action === 'delete') {
    // DELETE /api/v1/matches.php/{matchId}
    $url .= '/' . $matchId;
    $data = [];
    $method = 'DELETE';
} elseif ($action === 'clear-history') {
    // POST /api/v1/matches.php/{matchId}/clear-history
    $url .= '/' . $matchId . '/clear-history';
    $data = [];
    $method = 'POST';
} else {
    header('Location: /cricapp/admin/matches/view.php?id=' . $matchId);
    exit;
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
if (!empty($data)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
}
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $_SESSION['token']
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    $_SESSION['error'] = 'Connection error: ' . $curlError;
} elseif (($httpCode === 200 || $httpCode === 204)) {
    $result = json_decode($response, true);
    if ($result && isset($result['success']) && $result['success']) {
        $_SESSION['success'] = $result['message'] ?? 'Action completed successfully';
    } else {
        $_SESSION['error'] = $result['error'] ?? 'Failed to perform action';
    }
} else {
    $result = json_decode($response, true);
    $_SESSION['error'] = $result['error'] ?? 'Failed to perform action. HTTP Code: ' . $httpCode;
}

// If deleting, redirect to matches list instead of match view
if ($action === 'delete') {
    header('Location: /cricapp/admin/matches/');
} else {
    header('Location: /cricapp/admin/matches/view.php?id=' . $matchId);
}
exit;

