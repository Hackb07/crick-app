<?php
/**
 * Login/Sign Up Page - Public Portal
 * 
 * Features:
 * - Green header with CricApp logo
 * - Login form with email/password
 * - Sign up form with username, email, password
 * - Tab switching between Login and Sign Up
 * - Mobile responsive design
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . publicUrl('index.php'));
    exit;
}

$activeTab = getQuery('tab', 'login'); // login or signup
$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('action') === 'login') {
    $email = trim(getPost('email', ''));
    $password = getPost('password', '');
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $userModel = new User();
        $user = $userModel->getByUsername($email);
        
        if (!$user) {
            // Try email
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
        }
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if (!$user['is_active']) {
                $error = 'Your account is deactivated';
            } else {
                // Start session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect
                $redirect = getQuery('redirect', publicUrl('index.php'));
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Handle sign up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && getPost('action') === 'signup') {
    $username = trim(getPost('username', ''));
    $email = trim(getPost('email', ''));
    $password = getPost('password', '');
    $confirmPassword = getPost('confirm_password', '');
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $userModel = new User();
        
        // Check if username exists
        if ($userModel->getByUsername($username)) {
            $error = 'Username already exists';
        } else {
            // Check if email exists
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $error = 'Email already exists';
            } else {
                // Create user
                $userId = $userModel->create([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'user',
                    'full_name' => $username,
                    'is_active' => 1
                ]);
                
                if ($userId) {
                    $success = 'Account created successfully! Please login.';
                    $activeTab = 'login';
                } else {
                    $error = 'Failed to create account. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1e7e34">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= $activeTab === 'login' ? 'Login' : 'Sign Up' ?> - CricApp</title>
    <link rel="manifest" href="<?= publicUrl('manifest.json') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/pwa-mobile.css') ?>">
    <style>
        /* Green Header */
        .green-header {
            background: var(--cricket-green);
            color: white;
            padding: var(--spacing-md) 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-md);
        }
        
        .green-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .green-header-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        /* Auth Container */
        .auth-container {
            max-width: 500px;
            margin: var(--spacing-xl) auto;
            padding: var(--spacing-lg);
        }
        
        .auth-tabs {
            display: flex;
            gap: var(--spacing-sm);
            border-bottom: 2px solid var(--border-color);
            margin-bottom: var(--spacing-lg);
        }
        
        .auth-tab {
            padding: var(--spacing-md) var(--spacing-lg);
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            flex: 1;
        }
        
        .auth-tab:hover {
            color: var(--cricket-green);
        }
        
        .auth-tab.active {
            color: var(--cricket-green);
            border-bottom-color: var(--cricket-green);
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: var(--spacing-md);
        }
        
        .form-label {
            display: block;
            margin-bottom: var(--spacing-xs);
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }
        
        .form-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--cricket-green);
        }
        
        .form-error {
            background: #fee;
            color: #c33;
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
            border-left: 4px solid #c33;
        }
        
        .form-success {
            background: #efe;
            color: #3c3;
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
            border-left: 4px solid #3c3;
        }
        
        .form-footer {
            margin-top: var(--spacing-lg);
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .form-footer a {
            color: var(--cricket-green);
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .auth-container {
                margin: var(--spacing-md);
                padding: var(--spacing-md);
            }
            
            .auth-tabs {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .auth-tab {
                white-space: nowrap;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <!-- Fixed Header -->
        <header class="app-header">
            <div class="app-header-content">
                <a href="<?= publicUrl('index.php') ?>" class="app-header-logo">🏏 CricApp</a>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="app-content">
            <div class="container">
        <div class="auth-container">
            <div class="card">
                <h1 style="text-align: center; margin-bottom: var(--spacing-lg); font-size: 2rem; font-weight: 700;"><?= $activeTab === 'login' ? 'Login' : 'Sign Up' ?></h1>
                
                <!-- Tabs -->
                <div class="auth-tabs">
                    <button class="auth-tab <?= $activeTab === 'login' ? 'active' : '' ?>" onclick="showAuthTab('login')">Login</button>
                    <button class="auth-tab <?= $activeTab === 'signup' ? 'active' : '' ?>" onclick="showAuthTab('signup')">Sign Up</button>
                </div>
                
                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="form-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="form-success">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form id="login-form" class="auth-form <?= $activeTab === 'login' ? 'active' : '' ?>" method="POST" action="<?= publicUrl('login.php?tab=login') ?>">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label class="form-label" for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" class="form-input" required value="<?= e(getPost('email', '')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: var(--spacing-md); font-size: 1.125rem; font-weight: 600;">Login</button>
                    
                    <div class="form-footer">
                        Don't have an account? <a href="#" onclick="showAuthTab('signup'); return false;">Sign Up</a>
                    </div>
                </form>
                
                <!-- Sign Up Form -->
                <form id="signup-form" class="auth-form <?= $activeTab === 'signup' ? 'active' : '' ?>" method="POST" action="<?= publicUrl('login.php?tab=signup') ?>">
                    <input type="hidden" name="action" value="signup">
                    
                    <div class="form-group">
                        <label class="form-label" for="signup-username">Username</label>
                        <input type="text" id="signup-username" name="username" class="form-input" required value="<?= e(getPost('username', '')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" class="form-input" required value="<?= e(getPost('email', '')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="signup-password">Password</label>
                        <input type="password" id="signup-password" name="password" class="form-input" required minlength="6">
                        <small style="color: var(--text-secondary); font-size: 0.75rem; margin-top: var(--spacing-xs); display: block;">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="signup-confirm-password">Confirm Password</label>
                        <input type="password" id="signup-confirm-password" name="confirm_password" class="form-input" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: var(--spacing-md); font-size: 1.125rem; font-weight: 600;">Sign Up</button>
                    
                    <div class="form-footer">
                        Already have an account? <a href="#" onclick="showAuthTab('login'); return false;">Login</a>
                    </div>
                </form>
            </div>
        </main>

        <!-- Fixed Bottom Navigation -->
        <nav class="app-bottom-nav">
            <a href="<?= publicUrl('index.php') ?>" class="app-bottom-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                <span>Home</span>
            </a>
            <a href="<?= publicUrl('matches.php') ?>" class="app-bottom-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
                <span>Matches</span>
            </a>
            <a href="<?= publicUrl('live.php') ?>" class="app-bottom-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                <span>Live</span>
            </a>
            <a href="<?= publicUrl('leaderboard.php') ?>" class="app-bottom-nav-item">
                <svg fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
                <span>Leaderboard</span>
            </a>
        </nav>
    </div>

    <script>
        function showAuthTab(tabName) {
            // Hide all forms
            document.querySelectorAll('.auth-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.auth-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected form
            const form = document.getElementById(tabName + '-form');
            if (form) {
                form.classList.add('active');
            }
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }
        
        // Validate password match on signup
        document.getElementById('signup-confirm-password')?.addEventListener('input', function() {
            const password = document.getElementById('signup-password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>



