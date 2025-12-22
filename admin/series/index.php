<?php
/**
 * Series Listing - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$seriesModel = new Series();
$series = $seriesModel->getAll();

// Render
renderAdminLayout('Series', 'series/index', [
    'series' => $series
], [
    'activeMenu' => 'series',
    'headerActions' => [
        [
            'url' => adminUrl('series/create.php'),
            'label' => '+',
            'class' => 'btn-icon',
            'aria-label' => 'Add Series'
        ]
    ]
]);
