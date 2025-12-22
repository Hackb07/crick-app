<?php
/**
 * React App Loader
 * 
 * Loads pre-built React applications
 */

/**
 * Load React app
 * 
 * @param string $appName App name (admin, scorer, public, web)
 * @param string $entryFile Entry HTML file (default: index.html)
 */
function loadReactApp($appName, $entryFile = 'index.html') {
    $appPath = __DIR__ . '/../apps/' . $appName . '/dist';
    $htmlFile = $appPath . '/' . $entryFile;
    
    if (!file_exists($htmlFile)) {
        die("React app '$appName' not found. Please build the app first: npm run build:$appName");
    }
    
    // Read and output HTML
    $html = file_get_contents($htmlFile);
    
    // Replace asset paths if needed
    $basePath = APP_BASE_PATH;
    $html = str_replace('href="/', 'href="' . $basePath . '/apps/' . $appName . '/dist/', $html);
    $html = str_replace('src="/', 'src="' . $basePath . '/apps/' . $appName . '/dist/', $html);
    
    echo $html;
    exit;
}

