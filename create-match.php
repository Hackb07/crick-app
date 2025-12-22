<?php
/**
 * Create Match Page
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/classes/Series.php';
require_once __DIR__ . '/classes/Team.php';
require_once __DIR__ . '/classes/MatchModel.php';

// Require login
$userId = requireLogin();

$matchModel = new MatchModel();
$seriesModel = new Series();
$teamModel = new Team();

// Get data for dropdowns
$seriesList = $seriesModel->getAll();
$teamsList = $teamModel->getAll();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireCsrfToken(getPost('csrf_token'));
        
        $team1Id = (int)getPost('team1_id');
        $team2Id = (int)getPost('team2_id');
        $seriesId = (int)getPost('series_id');
        $matchDate = getPost('match_date');
        $venue = trim(getPost('venue'));
        $overs = (float)getPost('overs_per_innings', 20.0);
        $matchType = getPost('match_type', 'limited_overs');
        
        // Validation
        if ($team1Id === $team2Id) {
            throw new Exception("Team 1 and Team 2 cannot be the same.");
        }
        
        if (empty($matchDate)) {
            throw new Exception("Match date is required.");
        }
        
        if ($overs < 1 || $overs > 50) {
            throw new Exception("Overs per innings must be between 1 and 50.");
        }
        
        // Create match
        $matchData = [
            'series_id' => $seriesId ?: null,
            'team1_id' => $team1Id,
            'team2_id' => $team2Id,
            'match_date' => $matchDate,
            'venue' => $venue,
            'overs_per_innings' => $overs,
            'match_type' => $matchType,
            'created_by' => $userId
        ];
        
        $matchId = $matchModel->create($matchData);
        
        if ($matchId) {
            header("Location: match-view.php?id=" . $matchId);
            exit;
        } else {
            throw new Exception("Failed to create match in database.");
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#009270">
    <title>Create Match - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 24px;
        }
        
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="app-header">
        <div class="header-content">
            <div class="flex-between" style="width: 100%;">
                <div class="flex-center gap-3">
                    <a href="user-dashboard.php" style="color: white;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <div class="page-title">Create Match</div>
                </div>
            </div>
        </div>
    </header>

    <div class="container form-container">
        
        <?php if ($error): ?>
            <div class="alert alert-error mb-4"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form-section">
            <?= csrfInput() ?>
            
            <div class="form-group mb-4">
                <label class="form-label">Series (Optional)</label>
                <select name="series_id" class="form-control">
                    <option value="">-- Select Series --</option>
                    <?php foreach ($seriesList as $series): ?>
                        <option value="<?= $series['series_id'] ?>">
                            <?= e($series['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-grid mb-4">
                <div class="form-group">
                    <label class="form-label">Team 1 (Home)</label>
                    <select name="team1_id" class="form-control" required>
                        <option value="">-- Select Team 1 --</option>
                        <?php foreach ($teamsList as $team): ?>
                            <option value="<?= $team['team_id'] ?>">
                                <?= e($team['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Team 2 (Away)</label>
                    <select name="team2_id" class="form-control" required>
                        <option value="">-- Select Team 2 --</option>
                        <?php foreach ($teamsList as $team): ?>
                            <option value="<?= $team['team_id'] ?>">
                                <?= e($team['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Venue</label>
                <input type="text" name="venue" class="form-control" placeholder="Stadium Name, City">
            </div>

            <div class="form-grid mb-4">
                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" name="match_date" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Overs per Innings</label>
                    <input type="number" name="overs_per_innings" class="form-control" value="20" min="1" max="50">
                </div>
            </div>
            
            <div class="form-group mb-6">
                 <label class="form-label">Match Type</label>
                 <select name="match_type" class="form-control">
                     <option value="limited_overs">Limited Overs (T20/ODI)</option>
                     <option value="test">Test Match</option>
                 </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="width: 100%; padding: 14px;">
                Create Match
            </button>
        </form>
    </div>

</body>
</html>
