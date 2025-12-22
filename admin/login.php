<?php
/**
 * Admin Login Page - Premium Design
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . adminUrl('index.php'));
    exit;
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    try {
        requireCsrfToken(getPost('csrf_token', ''));
    } catch (Exception $e) {
        $error = 'Security validation failed. Please try again.';
        error_log('CSRF validation failed on login: ' . $e->getMessage());
    }
    
    // Only proceed if CSRF validation passed
    if (empty($error)) {
        $username = getPost('username', '');
        $password = getPost('password', '');
        
        if (!empty($username) && !empty($password)) {
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);
            
            if ($user) {
                // Regenerate session ID on successful login (prevents session fixation)
                session_regenerate_id(true);
                
                // Set session
                setSession('user_id', $user['user_id']);
                setSession('username', $user['username']);
                setSession('role', $user['role']);
                
                // Redirect to admin dashboard
                header('Location: ' . adminUrl('index.php'));
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Please enter username and password';
        }
    }
}

// Generate CSRF token for form
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <?php require_once __DIR__ . '/../includes/cache-prevention-meta.php'; ?>
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#1e7e34">
    <link rel="manifest" href="<?= getBaseUrl() ?>/manifest.json">
    <title>Admin Login - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <!-- Service Worker removed from admin pages - they need real-time data -->

    <style>
        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1e7e34 0%, #155724 50%, #0d3d1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            -webkit-font-smoothing: antialiased;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background pattern */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
            pointer-events: none;
        }
        
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        
        .login-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            width: 100%;
            position: relative;
            backdrop-filter: blur(10px);
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1e7e34 0%, #00b894 100%);
            border-radius: 20px 20px 0 0;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            font-size: 4rem;
            margin-bottom: 1rem;
            line-height: 1;
            animation: bounce 2s ease-in-out infinite;
            display: inline-block;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e7e34 0%, #00b894 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 0.5rem 0;
        }
        
        .login-subtitle {
            color: #6c757d;
            font-size: 0.9375rem;
            margin: 0;
            font-weight: 500;
        }
        
        .error {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            color: #c53030;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            border: 2px solid #fc8181;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2d3436;
            font-size: 0.9375rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            -webkit-appearance: none;
            appearance: none;
            background: white;
            font-weight: 500;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1e7e34;
            box-shadow: 0 0 0 4px rgba(30, 126, 52, 0.1);
            transform: translateY(-2px);
        }
        
        .btn {
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 52px;
            box-shadow: 0 4px 12px rgba(30, 126, 52, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .btn:hover {
            box-shadow: 0 6px 20px rgba(30, 126, 52, 0.4);
            transform: translateY(-2px);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e1e8ed;
        }
        
        .login-footer a {
            font-size: 0.9375rem;
            color: #6c757d;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            display: block;
            margin-bottom: 0.75rem;
        }
        
        .login-footer a:last-child {
            margin-bottom: 0;
        }
        
        .login-footer a:hover {
            color: #1e7e34;
            transform: translateX(4px);
        }
        
        /* Mobile Optimizations */
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 16px;
            }
            
            .login-title {
                font-size: 1.5rem;
            }
            
            .login-logo {
                font-size: 3rem;
            }
            
            body {
                padding: 1rem;
            }
        }
        
        @media (max-width: 375px) {
            .login-card {
                padding: 1.75rem 1.25rem;
            }
            
            .login-title {
                font-size: 1.375rem;
            }
            
            .login-logo {
                font-size: 2.75rem;
            }
        }
        
        /* Landscape mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                padding: 1rem;
            }
            
            .login-card {
                padding: 1.5rem;
            }
            
            .login-header {
                margin-bottom: 1.25rem;
            }
            
            .login-logo {
                font-size: 2.5rem;
                animation: none;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
        }
        
        /* Prevent text size adjustment on iOS */
        @media screen and (max-width: 768px) {
            html {
                -webkit-text-size-adjust: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">🏏</div>
                <h1 class="login-title">CricApp Admin</h1>
                <p class="login-subtitle">Cricket Scoring Application</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error" style="margin-bottom: var(--spacing-lg);">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <?= csrfInput() ?>
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            
            <div class="login-footer">
                <a href="<?= adminUrl('scorer-login.php') ?>">Scorer Login</a>
                <a href="<?= publicUrl('index.php') ?>">← Back to Public Portal</a>
            </div>
        </div>
    </div>
</body>
</html>
