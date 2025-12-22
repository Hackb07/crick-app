<?php
/**
 * Edit Series - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

requireLogin();

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireCsrfToken();

        $data = [
            'name' => trim(getPost('name', '')),
            'start_date' => getPost('start_date') ? getPost('start_date') : null,
            'end_date' => getPost('end_date') ? getPost('end_date') : null,
            'description' => trim(getPost('description', ''))
        ];
        
        if (empty($data['name'])) {
            $error = 'Series name is required';
        } else {
            $result = $seriesModel->update($seriesId, $data);
            
            if ($result) {
                header('Location: ' . adminUrl('series/view.php?id=' . $seriesId));
                exit;
            } else {
                $error = 'Failed to update series';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render
renderAdminLayout('Edit Series', 'series/edit', [
    'series' => $series,
    'error' => $error
], [
    'activeMenu' => 'series',
    'headerActions' => [
        [
            'url' => adminUrl('series/view.php?id=' . $seriesId),
            'label' => '←',
            'class' => 'btn-icon',
            'aria-label' => 'Back'
        ]
    ]
]);
