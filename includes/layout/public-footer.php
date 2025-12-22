<?php
/**
 * Public Footer
 * Common footer for all public pages
 */
?>
<footer class="public-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>CricApp</h3>
                <p>Your cricket scoring companion</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= publicUrl('matches.php') ?>">Matches</a></li>
                    <li><a href="<?= publicUrl('series.php') ?>">Series</a></li>
                    <li><a href="<?= publicUrl('stats.php') ?>">Statistics</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Admin</h4>
                <ul>
                    <li><a href="<?= adminUrl('login.php') ?>">Admin Login</a></li>
                    <li><a href="<?= adminUrl('scorer-login.php') ?>">Scorer Login</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> CricApp. All rights reserved.</p>
        </div>
    </div>
</footer>
