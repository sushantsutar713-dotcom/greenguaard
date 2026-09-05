<?php
/**
 * GreenGuard — Citizen Profile & Eco-Guardian Rank Dashboard
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/impact_calculator.php';

Auth::requireLogin('login.php');

$currentUser = Auth::user();
$userId = Auth::id();

$allReports = DB::all('reports');
$userImpact = ImpactCalculator::getUserImpact($userId, $allReports);

// User's own reports
$myReports = DB::filter('reports', fn($r) => (int)($r['user_id'] ?? 0) === $userId);

// Reports user upvoted
$upvotes = DB::filter('upvotes', fn($u) => (int)($u['user_id'] ?? 0) === $userId);
$upvotedReportIds = array_column($upvotes, 'report_id');
$verifiedReports = DB::filter('reports', fn($r) => in_array($r['report_id'] ?? 0, $upvotedReportIds));

$pageTitle = 'My Eco-Guardian Profile — GreenGuard';
$activeNav = 'profile';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container">
        <!-- Profile Banner -->
        <div class="form-card" style="margin-bottom: 2.5rem; background: linear-gradient(135deg, rgba(18, 28, 45, 0.95), rgba(15, 23, 42, 0.9));">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 76px; height: 76px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: white; box-shadow: 0 0 25px var(--primary-glow);">
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
                            <h1 style="font-size: 1.8rem; font-weight: 800; margin: 0;"><?= htmlspecialchars($currentUser['name'] ?? 'Guardian Citizen') ?></h1>
                            <span class="badge <?= $currentUser['role'] === 'ADMIN' ? 'badge-critical' : 'badge-resolved' ?>">
                                <?= htmlspecialchars($currentUser['role'] ?? 'CITIZEN') ?>
                            </span>
                        </div>
                        <p style="color: var(--text-muted); font-size: 0.92rem; margin: 0;">
                            ✉️ <?= htmlspecialchars($currentUser['email'] ?? '') ?> • 📅 Member since <?= date('M Y', strtotime($currentUser['created_at'] ?? 'now')) ?>
                        </p>
                        <div style="margin-top: 0.5rem;">
                            <span class="badge <?= $userImpact['badge'] ?>" style="font-size: 0.82rem; padding: 0.35rem 0.8rem;">
                                <?= $userImpact['rank'] ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <a href="report.php" class="btn btn-primary">
                        <span>📸</span> Report New Threat
                    </a>
                    <?php if (Auth::isAdmin()): ?>
                        <a href="admin/dashboard.php" class="btn btn-secondary">
                            🛡️ Open Authority Triage
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Impact & Contributions Metrics -->
        <div class="dashboard-metrics-grid" style="margin-bottom: 2.5rem;">
            <div class="metric-card">
                <div class="metric-icon green">🏆</div>
                <div>
                    <div class="metric-value"><?= number_format($userImpact['score']) ?></div>
                    <div class="metric-title">Guardian Impact Points</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon blue">📸</div>
                <div>
                    <div class="metric-value"><?= count($myReports) ?></div>
                    <div class="metric-title">Reports Submitted</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon yellow">✅</div>
                <div>
                    <div class="metric-value"><?= $userImpact['resolved_reports'] ?></div>
                    <div class="metric-title">Threats Resolved</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon purple">👥</div>
                <div>
                    <div class="metric-value"><?= count($verifiedReports) ?></div>
                    <div class="metric-title">Community Upvotes Cast</div>
                </div>
            </div>
        </div>

        <!-- Reports Tabs -->
        <div class="details-card">
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-glass); padding-bottom: 1.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0;">My Environmental Incidents (<?= count($myReports) ?>)</h2>
                <a href="report.php" class="btn btn-outline-primary btn-sm">+ Log New Threat</a>
            </div>

            <?php if (!empty($myReports)): ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Threat Title &amp; Category</th>
                                <th>Location</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Reported Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myReports as $r): ?>
                                <?php 
                                    $sev = strtoupper($r['severity'] ?? 'MEDIUM');
                                    $severityClass = 'badge-' . strtolower($sev);
                                    $status = strtoupper($r['status'] ?? 'PENDING');
                                    $statusClass = $status === 'RESOLVED' ? 'badge-resolved' : ($status === 'VERIFIED' ? 'badge-verified' : ($status === 'ACTION_INITIATED' ? 'badge-progress' : 'badge-pending'));
                                ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($r['report_id'] ?? '') ?></strong></td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem;">
                                            <?= htmlspecialchars($r['title'] ?? 'Environmental Incident') ?>
                                        </div>
                                        <span class="badge badge-low" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars(str_replace('_', ' ', $r['category'] ?? $r['issue_type'] ?? 'OTHER')) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(explode(',', $r['address'] ?? 'Unknown')[0]) ?></td>
                                    <td><span class="badge <?= $severityClass ?>"><?= htmlspecialchars($sev) ?></span></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $status)) ?></span></td>
                                    <td><?= date('d M Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <a href="report_details.php?id=<?= $r['report_id'] ?>" class="btn btn-secondary btn-sm">
                                            Inspect →
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: 0.75rem;">🌱</div>
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No reports submitted yet</h3>
                    <p style="font-size: 0.9rem; max-width: 450px; margin: 0 auto 1.5rem;">Be the eyes and ears of your ecosystem. Capture an environmental threat near you and earn Guardian points.</p>
                    <a href="report.php" class="btn btn-primary">📸 Report First Incident</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
