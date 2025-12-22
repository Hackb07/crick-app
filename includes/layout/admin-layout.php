<?php
/**
 * Admin Layout Wrapper
 * 
 * Centralized layout for all admin pages
 * Provides consistent structure, navigation, and styling
 * 
 * @package    CricApp
 * @subpackage Includes\Layout
 */

/**
 * Render admin page layout
 * 
 * @param string $title Page title
 * @param string $contentView View file name (without .php)
 * @param array $data Data to pass to view
 * @param array $options Additional options (css, js, etc.)
 * @return void
 */
function renderAdminLayout(string $title, string $contentView, array $data = [], array $options = []): void
{
    // Extract data for view
    extract($data);
    
    // Default options
    $options = array_merge([
        'sidebar' => true,
        'header' => true,
        'bodyClass' => '',
        'additionalCss' => [],
        'additionalJs' => [],
        'activeMenu' => 'dashboard',
        'rawHeadScript' => ''
    ], $options);
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <?php require __DIR__ . '/../cache-prevention-meta.php'; ?>
        <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
        <title><?= e($title) ?> - CricApp Admin</title>
        
        <!-- Base Styles -->
        <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
        <link rel="stylesheet" href="<?= assetUrl('css/admin-pwa.css') ?>">
        
        <!-- Additional CSS -->
        <?php foreach ($options['additionalCss'] as $css): ?>
            <link rel="stylesheet" href="<?= assetUrl($css) ?>">
        <?php endforeach; ?>
        
        <!-- Raw Head Script (e.g. Config) -->
        <?php if (!empty($options['rawHeadScript'])): ?>
            <?= $options['rawHeadScript'] ?>
        <?php endif; ?>
    </head>
    <body class="<?= e($options['bodyClass']) ?>">
        <div class="app-shell <?= !$options['sidebar'] ? 'no-sidebar' : '' ?>">
            <?php if ($options['sidebar']): ?>
                <?php renderAdminSidebar($options['activeMenu']); ?>
            <?php endif; ?>
            
            <?php if ($options['header']): ?>
            <header class="app-header">
                <?php if ($options['sidebar']): ?>
                    <button class="btn-icon" onclick="toggleSidebar()" aria-label="Menu" style="margin-right: 8px;">
                        ☰
                    </button>
                <?php endif; ?>
                <div class="header-title"><?= e($title) ?></div>
                <div class="header-actions">
                    <?php if (isset($options['headerActions']) && is_array($options['headerActions'])): ?>
                        <?php foreach ($options['headerActions'] as $action): ?>
                             <a href="<?= $action['url'] ?>" class="<?= $action['class'] ?? 'btn-icon' ?>" aria-label="<?= $action['aria-label'] ?? '' ?>">
                                <?= $action['label'] ?? '' ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <a href="<?= adminUrl('logout.php') ?>" class="btn-icon" aria-label="Logout">
                        🚪
                    </a>
                </div>
            </header>
            <?php endif; ?>
            
            <main class="app-main">
                <?php 
                $viewPath = __DIR__ . "/../../views/admin/{$contentView}.php";
                if (file_exists($viewPath)) {
                    require $viewPath;
                } else {
                    echo '<div class="content-container">';
                    echo '<div class="alert alert-danger">View file not found: ' . e($contentView) . '</div>';
                    echo '</div>';
                }
                ?>
            </main>
        </div>
        
        <!-- Base Scripts -->
        <script>
        function toggleSidebar() {
            document.querySelector('.app-shell').classList.toggle('sidebar-open');
        }
        </script>
        
        <!-- Additional JS -->
        <?php foreach ($options['additionalJs'] as $js): ?>
            <script src="<?= assetUrl($js) ?>"></script>
        <?php endforeach; ?>
    </body>
    </html>
    <?php
}

/**
 * Render public page layout
 * 
 * @param string $title Page title
 * @param string $contentView View file name (without .php)
 * @param array $data Data to pass to view
 * @param array $options Additional options
 * @return void
 */
function renderPublicLayout(string $title, string $contentView, array $data = [], array $options = []): void
{
    // Extract data for view
    extract($data);
    
    // Default options
    $options = array_merge([
        'additionalCss' => [],
        'additionalJs' => []
    ], $options);
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <title><?= e($title) ?> - CricApp</title>
        
        <!-- Base Styles -->
        <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
        <link rel="stylesheet" href="<?= assetUrl('css/public.css') ?>">
        
        <!-- Additional CSS -->
        <?php foreach ($options['additionalCss'] as $css): ?>
            <link rel="stylesheet" href="<?= assetUrl($css) ?>">
        <?php endforeach; ?>
    </head>
    <body>
        <?php require __DIR__ . '/public-header.php'; ?>
        
        <main class="main-content">
            <?php 
            $viewPath = __DIR__ . "/../../views/public/{$contentView}.php";
            if (file_exists($viewPath)) {
                require $viewPath;
            } else {
                echo '<div class="container">';
                echo '<div class="alert alert-danger">View file not found: ' . e($contentView) . '</div>';
                echo '</div>';
            }
            ?>
        </main>
        
        <?php require __DIR__ . '/public-footer.php'; ?>
        
        <!-- Additional JS -->
        <?php foreach ($options['additionalJs'] as $js): ?>
            <script src="<?= assetUrl($js) ?>"></script>
        <?php endforeach; ?>
    </body>
    </html>
    <?php
}
