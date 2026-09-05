<?php
/**
 * GreenGuard — Reusable Header & Navigation Bar
 */

if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
} elseif (file_exists(__DIR__ . '/../config/config.example.php')) {
    require_once __DIR__ . '/../config/config.example.php';
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$currentUser = Auth::user();
$isLoggedIn = Auth::check();
$isAdmin = Auth::isAdmin();

// Fetch unread notification count
$unreadCount = 0;
if ($isLoggedIn && isset($currentUser['user_id'])) {
    $userNotifs = DB::filter('notifications', fn($n) => 
        (int)($n['user_id'] ?? 0) === (int)$currentUser['user_id'] && empty($n['is_read'])
    );
    $unreadCount = count($userNotifs);
}

$pageTitle = $pageTitle ?? (defined('APP_NAME') ? APP_NAME : 'GreenGuard') . ' — Community Environmental Threat Platform';
$activeNav = $activeNav ?? '';
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="GreenGuard is a community-powered environmental threat detection, reporting, verification and resolution platform powered by Leaflet.js, and Google Gemini AI.">
    
    <!-- Design System & Icons -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌱</text></svg>">
    
    <!-- Leaflet Map CSS (Loaded for map and report picker views) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>

    <!-- Sticky Navigation Bar -->
    <header class="navbar">
        <div class="container nav-container">
            <a href="<?= $baseUrl ?>/index.php" class="brand">
                <div class="brand-icon">🌱</div>
                <span class="brand-text"><?= defined('APP_NAME') ? htmlspecialchars(APP_NAME) : 'GreenGuard' ?></span>
                <span class="brand-badge">MVP</span>
            </a>

            <nav>
                <ul class="nav-links">
                    <li><a href="<?= $baseUrl ?>/index.php" class="nav-link <?= $activeNav === 'home' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="<?= $baseUrl ?>/report.php" class="nav-link <?= $activeNav === 'report' ? 'active' : '' ?>">📸 Report Issue</a></li>
                    <li><a href="<?= $baseUrl ?>/explore.php" class="nav-link <?= $activeNav === 'explore' ? 'active' : '' ?>">🗺️ Explore Incidents</a></li>
                    <li><a href="<?= $baseUrl ?>/dashboard.php" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">📊 Analytics</a></li>
                    <li><a href="<?= $baseUrl ?>/about.php" class="nav-link <?= $activeNav === 'about' ? 'active' : '' ?>">ℹ️ About</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <?php if ($isLoggedIn): ?>
                    <!-- Notification Bell -->
                    <div class="notif-btn-wrapper">
                        <a href="<?= $baseUrl ?>/notifications.php" class="notif-bell-btn" title="View Notifications">
                            🔔
                            <?php if ($unreadCount > 0): ?>
                                <span class="notif-badge-count"><?= $unreadCount ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- User Profile Pill -->
                    <a href="<?= $baseUrl ?>/profile.php" class="nav-user-pill" title="My Profile & Eco-Guardian Rank">
                        <div class="user-avatar-circle">
                            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span><?= htmlspecialchars(explode(' ', $currentUser['name'] ?? 'User')[0]) ?></span>
                    </a>

                    <?php if ($isAdmin): ?>
                        <a href="<?= $baseUrl ?>/admin/dashboard.php" class="btn btn-outline-primary btn-sm">
                            🛡️ Admin Triage
                        </a>
                    <?php endif; ?>

                    <a href="<?= $baseUrl ?>/logout.php" class="btn btn-secondary btn-sm" title="Log Out">
                        🚪 Logout
                    </a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>/login.php" class="btn btn-secondary btn-sm">Log In</a>
                    <a href="<?= $baseUrl ?>/register.php" class="btn btn-primary btn-sm">Get Started</a>
                <?php endif; ?>
            </div>

            <button class="mobile-toggle" aria-label="Toggle Navigation">☰</button>
        </div>
    </header>

    <!-- Toast / Flash messages if available in session -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast("<?= addslashes($_SESSION['flash_success']) ?>", 'success');
            });
        </script>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast("<?= addslashes($_SESSION['flash_error']) ?>", 'error');
            });
        </script>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
