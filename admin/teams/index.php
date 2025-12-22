<?php
/**
 * Teams Listing - Admin Panel
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/session.php';

requireLogin();

$teamModel = new Team();
$teams = $teamModel->getAll();

$successMessage = null;
if (getQuery('deleted') == '1') {
    $successMessage = 'Team deleted successfully.';
}

// Render
renderAdminLayout('Teams', 'teams/index', [
    'teams' => $teams,
    'successMessage' => $successMessage
], [
    'activeMenu' => 'teams',
    'header' => true,
    'headerActions' => [ 
        [
            'url' => adminUrl('teams/create.php'),
            'label' => '+',
            'class' => 'btn-icon',
            'aria-label' => 'Add Team'
        ]
    ] // Actually renderAdminLayout creates the header logic.
      // But wait, renderAdminLayout (admin-layout.php) doesn't seem to support custom headerActions array passed in options in lines 74-79?
      // It only has a logout button fixed in line 76:
      /*
        <div class="header-actions">
            <a href="<?= adminUrl('logout.php') ?>" class="btn-icon" aria-label="Logout">
                🚪
            </a>
        </div>
      */
      // This is a limitation of the current layout. The original file had a '+' button in the header.
      // Line 40 in original `admin/teams/index.php`:
      /*
        <div class="header-actions">
            <a href="<?= adminUrl('teams/create.php') ?>" class="btn-icon" aria-label="Add Team">
                +
            </a>
        </div>
      */
      
      // The current `renderAdminLayout` (Step 16) DOES NOT support custom header actions. 
      // It HARDCODES the logout button.
      // "2. design change every page must foloow that main and re;ated page design only current and new pages"
      // If the main design (dashboard) only has logout, maybe I should stick to that?
      // BUT `admin/matches/create.php` had a Back button.
      // `admin/teams/index.php` had an Add button.
      
      // I should update `renderAdminLayout` to support custom header actions OR strict adherence to the simple design.
      // However, functionality (Add Team) is critical.
      // If I remove the Add button from header, I must place it in the view.
      // In `admin/teams/index.php` view I just created (Step 42), I added an "Add Team" button in the empty state.
      // But if there ARE teams, there is NO Add Team button in the body of the view I created!
      // This is a REGRESSION.
      
      // I must fix `views/admin/teams/index.php` to include an "Add Team" button in the body if `renderAdminLayout` doesn't support it in header.
      // OR I improve `renderAdminLayout` to support `headerActions`.
      
      // The user said "design change every page must foloow that main and re;ated page design".
      // If `dashboard` only has logout, maybe that's the "main design".
      // But usability requires the Add button.
      
      // I will update `includes/layout/admin-layout.php` to accept `headerActions`.
      // NOTE: `admin/matches/create.php` had a BACK button.
      // `admin/teams/index.php` had an ADD button.
      
      // Let's modify `includes/layout/admin-layout.php` first to be flexible.
]);
