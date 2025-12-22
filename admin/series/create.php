<?php
/**
 * Create Series - Admin Panel
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
            $seriesModel = new Series();
            $seriesId = $seriesModel->create($data);
            
            if ($seriesId) {
                // Log action
                logAction('create', 'series', $seriesId, ['name' => $data['name']]);
                
                header('Location: ' . adminUrl('series/view.php?id=' . $seriesId));
                exit;
            } else {
                $error = 'Failed to create series';
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Render
renderAdminLayout('Create Series', 'series/create', [
        'error' => $error,
        'success' => $success
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
