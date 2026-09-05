<?php
/**
 * GreenGuard — Reusable Footer & Script Loader
 */
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
// Auto-upgrade protocol to HTTPS on Render or secure proxies to prevent mixed content
if (!empty($baseUrl) && (
    (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) ||
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
)) {
    $baseUrl = preg_replace('/^http:/i', 'https:', $baseUrl);
}
?>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="brand">
                        <div class="brand-icon">🌱</div>
                        <span class="brand-text"><?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'GreenGuard' ?></span>
                    </div>
                    <p>Community-driven environmental protection powered by geotagged citizen reporting, Google Gemini AI computer vision, and real-time municipal resolution.</p>
                </div>

                <div>
                    <h4 class="footer-title">Platform Hub</h4>
                    <ul class="footer-links">
                        <li><a href="<?= $baseUrl ?>/report.php" class="footer-link">📸 Report Threat</a></li>
                        <li><a href="<?= $baseUrl ?>/explore.php" class="footer-link">🗺️ Interactive Explorer</a></li>
                        <li><a href="<?= $baseUrl ?>/dashboard.php" class="footer-link">📊 Analytics &amp; Hotspots</a></li>
                        <li><a href="<?= $baseUrl ?>/about.php" class="footer-link">ℹ️ Mission &amp; SDGs</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Access Portals</h4>
                    <ul class="footer-links">
                        <li><a href="<?= $baseUrl ?>/login.php" class="footer-link">👤 Citizen Login</a></li>
                        <li><a href="<?= $baseUrl ?>/register.php" class="footer-link">🌱 Register as Guardian</a></li>
                        <li><a href="<?= $baseUrl ?>/admin/login.php" class="footer-link">🛡️ Authority Portal</a></li>
                        <li><a href="<?= $baseUrl ?>/notifications.php" class="footer-link">🔔 Notification Center</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">SDG Alignment</h4>
                    <ul class="footer-links">
                        <li><span class="footer-link">🏙️ SDG 11: Sustainable Cities</span></li>
                        <li><span class="footer-link">🌍 SDG 13: Climate Action</span></li>
                        <li><span class="footer-link">🐟 SDG 14: Life Below Water</span></li>
                        <li><span class="footer-link">🌳 SDG 15: Life on Land</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> <?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'GreenGuard' ?> — Built for Hackathon Excellence.
                    <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                        <span class="debug-badge">🛠️ Dev Mode Active</span>
                    <?php endif; ?>
                </div>

                <div class="tech-pills">
                    <span class="tech-pill">PHP 8.2</span>
                    <span class="tech-pill">Gemini AI Vision</span>
                    <span class="tech-pill">Leaflet.js</span>
                    <span class="tech-pill">Chart.js</span>
                    <span class="tech-pill">Zero-SQL JSON Engine</span>
                    <span class="tech-pill">Bcrypt Security</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="<?= $baseUrl ?>/js/main.js"></script>
    
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
