<?php
/**
 * Cache Clearing Utility
 * Clears all application cache including OpCache
 */

// Security: Only allow from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('Access denied');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache Cleared - CricApp</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #1a202c;
            margin-bottom: 24px;
            font-size: 28px;
        }
        .status {
            background: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .status h2 {
            color: #166534;
            font-size: 18px;
            margin-bottom: 12px;
        }
        .item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .item:last-child { border-bottom: none; }
        .item .icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            font-size: 18px;
        }
        .item .label {
            flex: 1;
            color: #4b5563;
        }
        .item .value {
            color: #059669;
            font-weight: 600;
        }
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        .note {
            margin-top: 24px;
            padding: 16px;
            background: #fef3c7;
            border-radius: 8px;
            font-size: 14px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Cache Cleared Successfully</h1>
        
        <div class="status">
            <h2>✅ Cache Operations Completed</h2>
            <?php
            $results = [];
            
            // Clear OpCache
            if (function_exists('opcache_reset')) {
                opcache_reset();
                $results[] = ['OpCache', 'Cleared', '✓'];
            } else {
                $results[] = ['OpCache', 'Not Available', 'ℹ'];
            }
            
            // Clear session files
            $sessionPath = session_save_path();
            if ($sessionPath && is_dir($sessionPath)) {
                $count = 0;
                foreach (glob($sessionPath . '/sess_*') as $file) {
                    if (unlink($file)) $count++;
                }
                $results[] = ['Sessions', "$count files cleared", '✓'];
            }
            
            // Clear APCu cache if available
            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
                $results[] = ['APCu Cache', 'Cleared', '✓'];
            }
            
            // Clear realpath cache
            clearstatcache(true);
            $results[] = ['Stat Cache', 'Cleared', '✓'];
            
            // Display results
            foreach ($results as $result) {
                echo '<div class="item">';
                echo '<span class="icon">' . $result[2] . '</span>';
                echo '<span class="label">' . $result[0] . '</span>';
                echo '<span class="value">' . $result[1] . '</span>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="note">
            <strong>⚠️ Important:</strong> Please hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R) to see the changes.
        </div>
        
        <div class="actions">
            <a href="admin/matches/console.php?id=<?= $_GET['id'] ?? 1 ?>" class="btn btn-primary">
                ← Back to Console
            </a>
            <a href="javascript:location.reload(true)" class="btn btn-secondary">
                🔄 Reload Page
            </a>
        </div>
    </div>
    
    <script>
        // Auto-reload after 3 seconds
        setTimeout(() => {
            const url = new URL(window.location.href);
            const matchId = url.searchParams.get('id') || 1;
            window.location.href = 'admin/matches/console.php?id=' + matchId;
        }, 3000);
    </script>
</body>
</html>
