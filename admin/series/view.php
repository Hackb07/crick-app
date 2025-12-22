<?php
/**
 * View Series - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$seriesId = (int)getQuery('id', 0);
if (!$seriesId) {
    header('Location: ' . adminUrl('series/'));
    exit;
}

$seriesModel = new Series();
$series = $seriesModel->getById($seriesId);

if (!$series) {
    header('Location: ' . adminUrl('series/'));
    exit;
}

// Get matches in this series
$matchModel = new MatchModel();
$matches = $matchModel->getAll(['series_id' => $seriesId]);

// Render
renderAdminLayout($series['name'], 'series/view', [
    'series' => $series,
    'matches' => $matches
], [
    'activeMenu' => 'series',
    'headerActions' => [
        [
            'url' => adminUrl('series/'),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);
