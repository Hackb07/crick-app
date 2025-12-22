<?php
/**
 * User Login Page - Frontend
 */

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/csrf.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: user-dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    try {
        requireCsrfToken(getPost('csrf_token', ''));
    } catch (Exception $e) {
        $error = 'Security validation failed. Please try again.';
        error_log('CSRF validation failed on user login: ' . $e->getMessage());
    }
    
    // Only proceed if CSRF validation passed
    if (empty($error)) {
        $username = trim(getPost('username', ''));
        $password = getPost('password', '');
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if user exists
            $stmt = $db->prepare("SELECT user_id, username, password_hash, full_name FROM users WHERE username = :username AND is_active = 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header('Location: user-dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once __DIR__ . '/includes/cache-prevention-meta.php'; ?>
    <meta name="theme-color" content="#009270">
    <title>User Login - CricApp</title>
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #64748b;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 146, 112, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-login:hover {
            background: #007a5e;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 146, 112, 0.3);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        
        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, #00b894 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">🏏</div>
                <h1>Welcome Back</h1>
                <p>Login to manage your cricket matches</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?= csrfInput() ?>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        placeholder="Enter your username"
                        value="<?= e(getPost('username', '')) ?>"
                        required
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                    >
                </div>
                
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="back-link">
                <a href="<?= publicUrl('index.php') ?>">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
