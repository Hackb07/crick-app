<?php
/**
 * Utility Functions
 * 
 * Helper functions for URLs, assets, and common operations
 */

/**
 * Get base path (with leading slash, no trailing slash)
 * 
 * @return string Base path
 */
function getBasePath() {
    $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
    
    // Normalize: ensure leading slash, remove trailing slash
    if ($basePath === '' || $basePath === '/') {
        return '';
    }
    
    $basePath = '/' . ltrim($basePath, '/');
    $basePath = rtrim($basePath, '/');
    
    return $basePath;
}

/**
 * Get base URL (full URL with protocol and host)
 * 
 * @return string Base URL
 */
function getBaseUrl() {
    if (defined('APP_URL')) {
        return rtrim(APP_URL, '/');
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = getBasePath();
    
    return $protocol . '://' . $host . $basePath;
}

/**
 * Generate public URL
 * 
 * @param string $path Path relative to public directory
 * @return string Full URL
 */
function publicUrl($path = '') {
    $basePath = getBasePath();
    $path = ltrim($path, '/');
    return $basePath . '/' . $path;
}

/**
 * Generate admin URL
 * 
 * @param string $path Path relative to admin directory
 * @return string Full URL
 */
function adminUrl($path = '') {
    $basePath = getBasePath();
    $path = ltrim($path, '/');
    return $basePath . '/admin/' . $path;
}

/**
 * Generate asset URL
 * 
 * @param string $path Path relative to assets directory
 * @return string Full URL
 */
function assetUrl($path = '') {
    $basePath = getBasePath();
    $path = ltrim($path, '/');
    return $basePath . '/assets/' . $path;
}

/**
 * Generate API URL
 * 
 * @param string $endpoint API endpoint
 * @return string Full API URL
 */
function apiUrl($endpoint = '') {
    $basePath = getBasePath();
    $endpoint = ltrim($endpoint, '/');
    
    // If endpoint already has .php extension, use as is
    // Otherwise, add .php extension for direct file access
    if (!empty($endpoint) && !preg_match('/\.php$/', $endpoint)) {
        // Check if it's a path segment (like 'matches/1') - don't add .php
        // Only add .php if it's a single filename
        if (!preg_match('/\//', $endpoint)) {
            $endpoint .= '.php';
        }
    }
    
    return $basePath . '/api/v1/' . $endpoint;
}

/**
 * Generate absolute URL (with protocol and host)
 * 
 * @param string $path Relative path
 * @return string Absolute URL
 */
function absoluteUrl($path = '') {
    $baseUrl = getBaseUrl();
    $path = ltrim($path, '/');
    return $baseUrl . '/' . $path;
}

/**
 * Escape HTML output
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get current timestamp
 * 
 * @return string ISO 8601 timestamp
 */
function now() {
    return date('Y-m-d H:i:s');
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Redirect to URL
 * 
 * @param string $url URL to redirect to
 * @param int $code HTTP status code
 */
function redirect($url, $code = 302) {
    header("Location: $url", true, $code);
    exit;
}

/**
 * Send JSON response
 * 
 * @param mixed $data Data to encode
 * @param int $statusCode HTTP status code
 */
function jsonResponse($data, $statusCode = 200) {
    // Clear any previous output
    // Clear all output buffers to prevent content leakage
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Get request method
 * 
 * @return string HTTP method
 */
function getMethod() {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Get request body as JSON
 * 
 * @return array|null Parsed JSON or null
 */
function getJsonBody() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }
    
    return $data;
}

/**
 * Get query parameter
 * 
 * @param string $key Parameter key
 * @param mixed $default Default value
 * @return mixed Parameter value
 */
function getQuery($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Get POST parameter
 * 
 * @param string $key Parameter key
 * @param mixed $default Default value
 * @return mixed Parameter value
 */
function getPost($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Get API path from PATH_INFO or REQUEST_URI
 * 
 * Handles both PATH_INFO and REQUEST_URI parsing for API routing
 * 
 * @return string API path (e.g., '/login', '/123', '/123/toss')
 */
function getApiPath() {
    // First try PATH_INFO (works when Apache routes /api/v1/matches.php/123 correctly)
    if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
        return $_SERVER['PATH_INFO'];
    }
    
    // Fallback to REQUEST_URI parsing
    if (isset($_SERVER['REQUEST_URI'])) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptBase = basename($scriptName, '.php'); // e.g., 'matches'
        
        // Remove query string
        $requestUri = strtok($requestUri, '?');
        
        // Remove base path
        $basePath = getBasePath();
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }
        
        // Pattern: /api/v1/matches.php or /api/v1/matches.php/123
        // We need to extract the path after the script name
        
        // Check if request URI exactly matches the script (e.g., /api/v1/matches.php)
        $expectedScriptPath = '/api/v1/' . $scriptBase . '.php';
        if ($requestUri === $expectedScriptPath) {
            // Base endpoint - return empty string
            return '';
        }
        
        // Check if request URI contains the script base with path after it (e.g., /api/v1/matches.php/123)
        if (preg_match('#/api/v1/' . preg_quote($scriptBase, '#') . '\.php(.*)$#', $requestUri, $matches)) {
            $path = $matches[1];
            // Return empty string if path is empty, otherwise return the path
            return $path ?: '';
        }
        
        // Alternative pattern: /api/v1/matches/123 (without .php)
        if (preg_match('#/api/v1/' . preg_quote($scriptBase, '#') . '(?:\.php)?(.*)$#', $requestUri, $matches)) {
            $path = $matches[1];
            return $path ?: '';
        }
    }
    
    return '';
}

/**
 * Get badge CSS class for match state
 * 
 * @param string $state Match state (live, scheduled, completed, draft, abandoned, cancelled)
 * @return string Badge CSS class
 */
function getMatchBadgeClass($state) {
    $badges = [
        'live' => 'badge-live',
        'scheduled' => 'badge-scheduled',
        'completed' => 'badge-completed',
        'draft' => 'badge-draft',
        'abandoned' => 'badge-abandoned',
        'cancelled' => 'badge-cancelled'
    ];
    return $badges[$state] ?? 'badge-default';
}

/**
 * Render a badge element
 * 
 * @param string $text Badge text
 * @param string $state Match state or badge type
 * @return string HTML badge element
 */
function renderBadge($text, $state) {
    $class = getMatchBadgeClass($state);
    return '<span class="badge ' . $class . '">' . e($text) . '</span>';
}

/**
 * Render an empty state component
 * 
 * @param string $icon Emoji or icon
 * @param string $title Main message
 * @param string $subtitle Optional subtitle
 * @param array|null $action Optional action button ['text' => '', 'url' => '']
 * @return string HTML empty state component
 */
function renderEmptyState($icon, $title, $subtitle = '', $action = null) {
    ob_start();
    ?>
    <div class="empty-state">
        <div class="empty-state-icon"><?= $icon ?></div>
        <div class="empty-state-text"><?= e($title) ?></div>
        <?php if ($subtitle): ?>
            <div class="empty-state-subtext"><?= e($subtitle) ?></div>
        <?php endif; ?>
        <?php if ($action): ?>
            <div class="empty-state-subtext">
                <a href="<?= e($action['url']) ?>" class="btn btn-primary">
                    <?= e($action['text']) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
