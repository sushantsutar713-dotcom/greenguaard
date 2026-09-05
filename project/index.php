<?php
/**
 * GreenGuard — Community Environmental Threat Platform
 * Landing Page & Public Showcase
 */

// Load Configuration safely
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
} elseif (file_exists(__DIR__ . '/config/config.example.php')) {
    require_once __DIR__ . '/config/config.example.php';
} else {
    define('APP_NAME', 'GreenGuard');
    define('BASE_URL', '');
    define('DEBUG_MODE', false);
}

// Load dynamic data counts from JSON safely
$reportsFile = __DIR__ . '/data/reports.json';
$usersFile = __DIR__ . '/data/users.json';

$reports = [];
$users = [];

if (file_exists($reportsFile)) {
    $reportsData = @file_get_contents($reportsFile);
    $reports = json_decode($reportsData, true) ?: [];
}

if (file_exists($usersFile)) {
    $usersData = @file_get_contents($usersFile);
    $users = json_decode($usersData, true) ?: [];
}

// Compute dynamic stats
$totalReports = count($reports);
$resolvedReports = count(array_filter($reports, fn($r) => ($r['status'] ?? '') === 'RESOLVED'));
$activeReports = count(array_filter($reports, fn($r) => in_array($r['status'] ?? '', ['PENDING', 'VERIFIED', 'IN_PROGRESS'])));
$totalCommunityConfirmations = array_reduce($reports, fn($carry, $r) => $carry + ($r['community']['confirmations'] ?? 0), 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> — Environmental Threat Detection &amp; Resolution</title>
    <meta name="description" content="Community-powered environmental threat detection, reporting, verification and resolution platform powered by PHP, Leaflet, and Google Gemini AI.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌱</text></svg>">
</head>
<body>

    <!-- Sticky Navigation Bar -->
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">🌱</div>
                <span class="brand-text"><?= htmlspecialchars(APP_NAME) ?></span>
                <span class="brand-badge">MVP</span>
            </a>

            <nav>
                <ul class="nav-links">
                    <li><a href="#workflow" class="nav-link">How It Works</a></li>
                    <li><a href="#live-feed" class="nav-link">Active Incidents</a></li>
                    <li><a href="#impact" class="nav-link">Community Impact</a></li>
                    <li><a href="map.php" class="nav-link">🗺️ Threat Map</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <a href="login.php" class="btn btn-secondary btn-sm">Log In</a>
                <a href="register.php" class="btn btn-primary btn-sm">Get Started</a>
            </div>

            <button class="mobile-toggle" aria-label="Toggle Navigation">☰</button>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-pill">
                    <span>⚡ Hackathon MVP</span>
                    <span>•</span>
                    <span>AI + Community Powered</span>
                </div>

                <h1 class="hero-title">
                    Detect &amp; Resolve <span class="text-gradient">Environmental Threats</span> in Your Community
                </h1>

                <p class="hero-description">
                    GreenGuard turns citizens into environmental guardians. Report pollution, illegal dumping, and tree loss with verified photo evidence, automated AI classification, and real-time municipal action.
                </p>

                <div class="hero-actions">
                    <a href="report.php" class="btn btn-primary btn-lg">
                        <span>📸</span> Report an Issue
                    </a>
                    <a href="map.php" class="btn btn-secondary btn-lg">
                        <span>🗺️</span> Explore Live Threat Map
                    </a>
                </div>

                <!-- Live Metrics Counter -->
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?= max($totalReports, 1) ?>"><?= $totalReports ?></div>
                        <div class="stat-label">Reports Logged</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?= max($resolvedReports, 1) ?>"><?= $resolvedReports ?></div>
                        <div class="stat-label">Threats Resolved</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?= max($totalCommunityConfirmations, 10) ?>"><?= $totalCommunityConfirmations ?></div>
                        <div class="stat-label">Citizen Verifications</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-target="<?= max(count($users), 3) ?>"><?= count($users) ?></div>
                        <div class="stat-label">Active Guardians</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4-Pillar Workflow Section -->
        <section id="workflow" class="section">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Continuous Protection Cycle</span>
                    <h2 class="section-title">How GreenGuard Protects Nature</h2>
                    <p class="section-subtitle">
                        From instant citizen reporting to verified administrative resolution in four streamlined steps.
                    </p>
                </div>

                <div class="workflow-grid">
                    <div class="workflow-card">
                        <span class="step-badge">STEP 01</span>
                        <div class="workflow-icon">📍</div>
                        <h3 class="workflow-title">Report Issue</h3>
                        <p class="workflow-text">
                            Capture geotagged photo evidence and incident details with single-click location capture from any mobile or desktop browser.
                        </p>
                    </div>

                    <div class="workflow-card">
                        <span class="step-badge">STEP 02</span>
                        <div class="workflow-icon">🤖</div>
                        <h3 class="workflow-title">Gemini AI Analysis</h3>
                        <p class="workflow-text">
                            Google Gemini AI automatically classifies the environmental threat, evaluates severity, and suggests immediate remediation steps.
                        </p>
                    </div>

                    <div class="workflow-card">
                        <span class="step-badge">STEP 03</span>
                        <div class="workflow-icon">👥</div>
                        <h3 class="workflow-title">Community Monitoring</h3>
                        <p class="workflow-text">
                            Nearby citizens confirm or dispute reports. Multiple verifications dynamically boost the report's priority score for authorities.
                        </p>
                    </div>

                    <div class="workflow-card">
                        <span class="step-badge">STEP 04</span>
                        <div class="workflow-icon">✅</div>
                        <h3 class="workflow-title">Action &amp; Resolution</h3>
                        <p class="workflow-text">
                            Municipal admins triage verified hotspots, deploy field workers, and mark resolved issues turning map markers green.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Incident Preview -->
        <section id="live-feed" class="section" style="background: rgba(15, 23, 42, 0.4);">
            <div class="container">
                <div class="section-header">
                    <span class="section-tag">Real-Time Community Stream</span>
                    <h2 class="section-title">Active Environmental Threats</h2>
                    <p class="section-subtitle">
                        Transparent incident feed backed by local citizen verification.
                    </p>
                </div>

                <div class="reports-preview-grid">
                    <?php if (!empty($reports)): ?>
                        <?php foreach (array_slice($reports, 0, 3) as $r): ?>
                            <?php 
                                $severityClass = 'badge-' . strtolower($r['severity'] ?? 'medium');
                                $statusClass = ($r['status'] ?? '') === 'RESOLVED' ? 'badge-resolved' : 'badge-pending';
                            ?>
                            <div class="report-card">
                                <div class="report-card-header">
                                    <span class="badge <?= $severityClass ?>"><?= htmlspecialchars($r['severity'] ?? 'MEDIUM') ?></span>
                                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($r['status'] ?? 'PENDING') ?></span>
                                </div>
                                <h3 class="report-title"><?= htmlspecialchars(str_replace('_', ' ', $r['issue_type'] ?? 'Issue')) ?></h3>
                                <p class="report-desc"><?= htmlspecialchars($r['description'] ?? '') ?></p>
                                <div class="report-meta">
                                    <span class="meta-item">📍 <?= htmlspecialchars($r['address'] ?? 'Unknown Location') ?></span>
                                    <span class="meta-item">👥 <?= intval($r['community']['confirmations'] ?? 0) ?> confirms</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; grid-column: 1/-1; color: var(--text-muted);">No reports logged yet.</p>
                    <?php endif; ?>
                </div>

                <div style="text-align: center; margin-top: 2.5rem;">
                    <a href="map.php" class="btn btn-outline-primary">
                        View All Incidents on Interactive Map →
                    </a>
                </div>
            </div>
        </section>

        <!-- Problem Statement / Vision Banner -->
        <section id="impact" class="section">
            <div class="container">
                <div class="vision-banner">
                    <h2 class="vision-title">Turning Awareness into Action</h2>
                    <p class="vision-text">
                        "GreenGuard is a community-powered environmental threat detection, reporting, verification and resolution platform that combines photo evidence, location-based reporting, AI-assisted classification, community monitoring and administrative action."
                    </p>
                    <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                        <a href="register.php" class="btn btn-primary">Join as Community Guardian</a>
                        <a href="admin/login.php" class="btn btn-secondary">Admin Portal</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="brand">
                        <div class="brand-icon">🌱</div>
                        <span class="brand-text"><?= htmlspecialchars(APP_NAME) ?></span>
                    </div>
                    <p>Community environmental protection powered by lightweight PHP, JSON storage, and Google Gemini AI.</p>
                </div>

                <div>
                    <h4 class="footer-title">Platform</h4>
                    <ul class="footer-links">
                        <li><a href="report.php" class="footer-link">Report Threat</a></li>
                        <li><a href="map.php" class="footer-link">Interactive Map</a></li>
                        <li><a href="login.php" class="footer-link">Citizen Login</a></li>
                        <li><a href="admin/login.php" class="footer-link">Admin Access</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Security &amp; Tech</h4>
                    <ul class="footer-links">
                        <li><span class="footer-link">🔒 Bcrypt Password Hashing</span></li>
                        <li><span class="footer-link">🛡️ Protected JSON Store</span></li>
                        <li><span class="footer-link">🤖 Gemini AI Vision Backend</span></li>
                        <li><span class="footer-link">⚡ Shared Hosting Ready</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?> — Built for Hackathon Excellence.
                    <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                        <span class="debug-badge">🛠️ Dev Mode Active</span>
                    <?php endif; ?>
                </div>

                <div class="tech-pills">
                    <span class="tech-pill">PHP 8+</span>
                    <span class="tech-pill">JSON Store</span>
                    <span class="tech-pill">Gemini AI</span>
                    <span class="tech-pill">Leaflet.js</span>
                    <span class="tech-pill">InfinityFree Ready</span>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
