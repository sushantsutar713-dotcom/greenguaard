<?php
/**
 * GreenGuard — Landing Page & Public Showcase
 */

$pageTitle = 'GreenGuard — Protect Nature. Report Threats. Create Change.';
$activeNav = 'home';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/impact_calculator.php';
require_once __DIR__ . '/includes/hotspot_service.php';

$reports = DB::all('reports');
$users = DB::all('users');
$globalImpact = ImpactCalculator::getGlobalImpact($reports, $users);
$hotspots = HotspotService::detectHotspots($reports);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Main Content -->
<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-pill">
                <span>⚡ Community-Driven Environmental Intelligence</span>
                <span>•</span>
                <span>AI Vision Powered</span>
            </div>

            <h1 class="hero-title">
                <span class="text-gradient">Protect Nature.</span> Report Threats. Create Change.
            </h1>

            <p class="hero-description">
                GreenGuard turns citizens into active environmental guardians. Report pollution, illegal dumping, and tree loss with geotagged photo evidence, real-time Google Gemini AI threat classification, and track transparent municipal action.
            </p>

            <div class="hero-actions">
                <a href="report.php" class="btn btn-primary btn-lg">
                    <span>📸</span> Report an Issue
                </a>
                <a href="explore.php" class="btn btn-secondary btn-lg">
                    <span>🗺️</span> Explore Reports
                </a>
                <a href="dashboard.php" class="btn btn-outline-primary btn-lg">
                    <span>📊</span> Environmental Dashboard
                </a>
            </div>

            <!-- Environmental Statistics Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-number" data-target="<?= $globalImpact['total_reports'] ?>"><?= $globalImpact['total_reports'] ?></div>
                    <div class="stat-label">Total Reports</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?= $globalImpact['resolved_reports'] ?>"><?= $globalImpact['resolved_reports'] ?></div>
                    <div class="stat-label">Issues Resolved</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?= $globalImpact['trees_protected'] ?>"><?= $globalImpact['trees_protected'] ?></div>
                    <div class="stat-label">Trees Protected</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="<?= $globalImpact['active_guardians'] ?>"><?= $globalImpact['active_guardians'] ?></div>
                    <div class="stat-label">Active Citizens</div>
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
                    <h3 class="workflow-title">Report Threat</h3>
                    <p class="workflow-text">
                        Capture geotagged photo evidence and incident details with 1-click browser GPS capture on any mobile or desktop device.
                    </p>
                </div>

                <div class="workflow-card">
                    <span class="step-badge">STEP 02</span>
                    <div class="workflow-icon">🤖</div>
                    <h3 class="workflow-title">Gemini AI Analysis</h3>
                    <p class="workflow-text">
                        Google Gemini AI automatically classifies threat categories, assesses ecological severity, and recommends municipal action.
                    </p>
                </div>

                <div class="workflow-card">
                    <span class="step-badge">STEP 03</span>
                    <div class="workflow-icon">👥</div>
                    <h3 class="workflow-title">Community Validation</h3>
                    <p class="workflow-text">
                        Nearby citizens confirm or dispute reports. Multiple verifications dynamically boost priority score (0-100) for authorities.
                    </p>
                </div>

                <div class="workflow-card">
                    <span class="step-badge">STEP 04</span>
                    <div class="workflow-icon">✅</div>
                    <h3 class="workflow-title">Action &amp; Resolution</h3>
                    <p class="workflow-text">
                        Municipal admins triage hotspots, deploy field workers, update live status timelines, and mark threats resolved.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Active Environmental Threats Stream -->
    <section id="live-feed" class="section" style="background: rgba(15, 23, 42, 0.4);">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Real-Time Community Stream</span>
                <h2 class="section-title">Active Environmental Threats</h2>
                <p class="section-subtitle">
                    Transparent incident feed backed by geotagged photo evidence and citizen verifications.
                </p>
            </div>

            <div class="reports-preview-grid">
                <?php if (!empty($reports)): ?>
                    <?php foreach (array_slice($reports, 0, 3) as $r): ?>
                        <?php 
                            $sev = strtoupper($r['severity'] ?? 'MEDIUM');
                            $severityClass = 'badge-' . strtolower($sev);
                            $status = strtoupper($r['status'] ?? 'PENDING');
                            $statusClass = $status === 'RESOLVED' ? 'badge-resolved' : ($status === 'VERIFIED' ? 'badge-verified' : ($status === 'ACTION_INITIATED' ? 'badge-progress' : 'badge-pending'));
                        ?>
                        <div class="report-card">
                            <div class="report-card-header">
                                <span class="badge <?= $severityClass ?>"><?= htmlspecialchars($sev) ?> SEVERITY</span>
                                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $status)) ?></span>
                            </div>
                            <?php if (!empty($r['image_path'])): ?>
                                <img src="<?= htmlspecialchars($r['image_path']) ?>" alt="Evidence photo" style="height: 180px; width: 100%; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 1rem; border: 1px solid var(--border-glass);">
                            <?php endif; ?>
                            <h3 class="report-title"><?= htmlspecialchars($r['title'] ?? ucwords(strtolower(str_replace('_', ' ', $r['category'] ?? 'Issue')))) ?></h3>
                            <p class="report-desc"><?= htmlspecialchars(substr($r['description'] ?? '', 0, 120)) ?>...</p>
                            <div class="report-meta">
                                <span class="meta-item">📍 <?= htmlspecialchars(explode(',', $r['address'] ?? 'Location')[0]) ?></span>
                                <span class="meta-item">👥 <?= intval($r['community']['confirmations'] ?? 0) ?> confirms</span>
                            </div>
                            <div style="margin-top: 1rem;">
                                <a href="report_details.php?id=<?= $r['report_id'] ?>" class="btn btn-outline-primary btn-sm btn-block">
                                    View Report Details &amp; Timeline →
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; grid-column: 1/-1; color: var(--text-muted);">No reports logged yet.</p>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 2.5rem;">
                <a href="explore.php" class="btn btn-primary btn-lg">
                    <span>🗺️</span> Explore All Reports on Interactive Map →
                </a>
            </div>
        </div>
    </section>

    <!-- Problem Statement & Call to Action Banner -->
    <section id="impact" class="section">
        <div class="container">
            <div class="vision-banner">
                <span class="section-tag" style="color: var(--primary-light);">Environmental Protection Platform</span>
                <h2 class="vision-title">Turning Citizen Awareness into Municipal Action</h2>
                <p class="vision-text">
                    Pollution, illegal waste dumping, and tree loss often destroy natural ecosystems before authorities can intervene. GreenGuard bridges the gap through community surveillance, automated AI intelligence, and verified municipal accountability.
                </p>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="register.php" class="btn btn-primary btn-lg">Join as Eco-Guardian</a>
                    <a href="admin/login.php" class="btn btn-secondary btn-lg">🛡️ Authority Triage Portal</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
