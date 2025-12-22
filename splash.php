<?php
/**
 * Splash/Loading Screen - CricApp
 * 
 * Features:
 * - Green splash screen with logo
 * - White splash screen with logo
 * - Smooth transition animation
 * - Auto-redirect to home after 2-3 seconds
 */

require_once __DIR__ . '/includes/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CricApp - Loading</title>
    <link rel="stylesheet" href="<?= assetUrl('css/main.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/premium-design.css') ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            overflow: hidden;
        }
        
        /* Green Splash Screen */
        .splash-green {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: var(--cricket-green);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeOut 0.5s ease-in-out 1.5s forwards;
        }
        
        .splash-green-logo {
            font-size: 4rem;
            font-weight: 700;
            color: white;
            animation: scaleIn 0.5s ease-out;
        }
        
        /* White Splash Screen */
        .splash-white {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 999;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out 1.5s forwards, fadeOut 0.5s ease-in-out 2.5s forwards;
        }
        
        .splash-white-logo {
            font-size: 3rem;
            font-weight: 700;
            color: var(--cricket-green);
            margin-bottom: var(--spacing-lg);
            animation: scaleIn 0.5s ease-out 1.5s both;
        }
        
        .splash-white-shape {
            position: absolute;
            top: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(34, 139, 34, 0.1) 0%, rgba(34, 139, 34, 0.05) 100%);
            animation: float 3s ease-in-out infinite;
        }
        
        /* Loading Spinner */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(34, 139, 34, 0.2);
            border-top-color: var(--cricket-green);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
                visibility: hidden;
            }
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .splash-green-logo {
                font-size: 3rem;
            }
            
            .splash-white-logo {
                font-size: 2.5rem;
            }
            
            .splash-white-shape {
                width: 200px;
                height: 200px;
                top: -30px;
                right: -30px;
            }
        }
    </style>
</head>
<body>
    <!-- Green Splash Screen -->
    <div class="splash-green">
        <div class="splash-green-logo">🏏 CricApp</div>
    </div>
    
    <!-- White Splash Screen -->
    <div class="splash-white">
        <div class="splash-white-shape"></div>
        <div class="splash-white-logo">🏏 CricApp</div>
        <div class="loading-spinner"></div>
    </div>

    <script>
        // Redirect to home after 3 seconds
        setTimeout(function() {
            window.location.href = '<?= publicUrl("index.php") ?>';
        }, 3000);
    </script>
</body>
</html>


