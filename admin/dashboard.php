<?php
/**
 * GreenGuard — Administrative Triage & Command Center
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/hotspot_service.php';

Auth::requireAdmin('../login.php');

$currentUser = Auth::user();
$reports = DB::all('reports');
$users = DB::all('users');
$hotspots = HotspotService::detectHotspots($reports);

// KPIs
$pendingReports = array_values(array_filter($reports, fn($r) => in_array($r['status'] ?? '', ['PENDING', 'UNDER_REVIEW'])));
$activeOperations = array_values(array_filter($reports, fn($r) => in_array($r['status'] ?? '', ['VERIFIED', 'ACTION_INITIATED'])));
$resolvedReports = array_values(array_filter($reports, fn($r) => ($r['status'] ?? '') === 'RESOLVED'));
$criticalThreats = array_values(array_filter($reports, fn($r) => strtoupper($r['severity'] ?? '') === 'CRITICAL'));

// Sort pending by Priority Score descending
usort($pendingReports, fn($a, $b) => ($b['priority_score'] ?? 0) <=> ($a['priority_score'] ?? 0));

$pageTitle = 'Command Triage — GreenGuard Authority Center';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
</head>
<body>

    <!-- Admin Navigation Header -->
    <header class="navbar" style="border-bottom: 2px solid rgba(239, 68, 68, 0.4);">
        <div class="container nav-container">
            <a href="dashboard.php" class="brand">
                <div class="brand-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">🛡️</div>
                <span class="brand-text">GreenGuard Authority</span>
                <span class="brand-badge" style="background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.3);">
                    OFFICIAL
                </span>
            </a>

            <nav>
                <ul class="nav-links">
                    <li><a href="dashboard.php" class="nav-link active">Triage Overview</a></li>
                    <li><a href="reports.php" class="nav-link">Master Reports Table</a></li>
                    <li><a href="users.php" class="nav-link">Guardian Users</a></li>
                    <li><a href="export.php" class="nav-link">Export Data</a></li>
                    <li><a href="../explore.php" class="nav-link" target="_blank">🗺️ Public Threat Map ↗</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Officer: <strong><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></strong>
                </span>
                <a href="../logout.php" class="btn btn-secondary btn-sm">
                    🚪 Logout
                </a>
            </div>

            <button class="mobile-toggle" aria-label="Toggle Navigation">☰</button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="section" style="padding-top: 2rem;">
        <div class="container">
            
            <!-- Welcome Bar -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem;">Municipal Incident Triage Center</h1>
                    <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0;">
                        Review incoming citizen reports, verify threats, assign field teams, and log resolution actions.
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="reports.php" class="btn btn-primary">
                        📋 Master Reports Queue (<?= count($reports) ?>)
                    </a>
                </div>
            </div>

            <!-- Admin KPIs Grid -->
            <div class="dashboard-metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon yellow">⏳</div>
                    <div>
                        <div class="metric-value"><?= count($pendingReports) ?></div>
                        <div class="metric-title">Pending Immediate Triage</div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon blue">🚜</div>
                    <div>
                        <div class="metric-value"><?= count($activeOperations) ?></div>
                        <div class="metric-title">Active Field Operations</div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon red">🔴</div>
                    <div>
                        <div class="metric-value"><?= count($criticalThreats) ?></div>
                        <div class="metric-title">Critical Ecological Threats</div>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon green">✅</div>
                    <div>
                        <div class="metric-value"><?= count($resolvedReports) ?></div>
                        <div class="metric-title">Total Threats Resolved</div>
                    </div>
                </div>
            </div>

            <!-- Urgent Triage Queue -->
            <div class="details-card" style="margin-bottom: 2.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.25rem;">
                            🚨 Priority Triage Queue (Awaiting Verification)
                        </h2>
                        <p style="color: var(--text-muted); font-size: 0.88rem; margin: 0;">
                            Sorted automatically by AI Urgency Priority Score. Immediate review recommended.
                        </p>
                    </div>
                    <span class="badge badge-critical"><?= count($pendingReports) ?> In Queue</span>
                </div>

                <?php if (!empty($pendingReports)): ?>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Report ID</th>
                                    <th>Threat Title</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Severity</th>
                                    <th>Priority Score</th>
                                    <th>Reported By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingReports as $r): ?>
                                    <?php 
                                        $sev = strtoupper($r['severity'] ?? 'MEDIUM');
                                        $severityClass = 'badge-' . strtolower($sev);
                                    ?>
                                    <tr>
                                        <td><strong>#<?= $r['report_id'] ?></strong></td>
                                        <td>
                                            <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem;">
                                                <?= htmlspecialchars(substr($r['title'] ?? 'Threat', 0, 45)) ?>...
                                            </div>
                                            <span style="font-size: 0.78rem; color: var(--text-muted);">
                                                <?= date('d M Y, h:i A', strtotime($r['created_at'] ?? 'now')) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-low" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars(str_replace('_', ' ', $r['category'] ?? $r['issue_type'] ?? 'OTHER')) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars(explode(',', $r['address'] ?? 'Unknown')[0]) ?></td>
                                        <td><span class="badge <?= $severityClass ?>"><?= $sev ?></span></td>
                                        <td>
                                            <strong style="color: <?= ($r['priority_score'] ?? 50) > 75 ? '#ef4444' : '#f97316' ?>;">
                                                ⚡ <?= $r['priority_score'] ?? 50 ?>
                                            </strong>
                                        </td>
                                        <td><?= htmlspecialchars($r['user_name'] ?? 'Citizen') ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.4rem;">
                                                <a href="report_details.php?id=<?= $r['report_id'] ?>" class="btn btn-primary btn-sm">
                                                    Inspect &amp; Verify →
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">🎉</div>
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main);">Triage Queue is Clear!</h3>
                        <p style="font-size: 0.9rem;">All logged environmental threats have been verified and assigned to field departments.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Department Workload Breakdown & Hotspots Tracker -->
            <div class="charts-grid">
                <!-- Department Workloads -->
                <div class="chart-card">
                    <h3 class="chart-title" style="margin-bottom: 1rem;">🏛️ Municipal Department Allocations</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(15, 23, 42, 0.6); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                            <span>🗑️ Solid Waste Management Dept</span>
                            <span class="badge badge-low">3 Active</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(15, 23, 42, 0.6); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                            <span>🌳 Forest &amp; Tree Protection Authority</span>
                            <span class="badge badge-critical">1 Critical</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(15, 23, 42, 0.6); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                            <span>🧪 State Pollution Control Board</span>
                            <span class="badge badge-high">2 In Progress</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(15, 23, 42, 0.6); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                            <span>🔥 Fire &amp; Emergency Services</span>
                            <span class="badge badge-resolved">1 Resolved</span>
                        </div>
                    </div>
                </div>

                <!-- Hotspots Summary -->
                <div class="chart-card">
                    <h3 class="chart-title" style="margin-bottom: 1rem;">🔥 Concentrated Hotspot Clusters</h3>
                    <?php if (!empty($hotspots)): ?>
                        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                            <?php foreach ($hotspots as $h): ?>
                                <div style="background: rgba(15, 23, 42, 0.6); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.92rem;"><?= htmlspecialchars($h['hotspot_name']) ?></div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($h['top_category_label']) ?> • <?= $h['incident_count'] ?> incidents</div>
                                    </div>
                                    <span class="badge <?= $h['risk_badge'] ?>"><?= $h['risk_level'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">No geographic clusters detected.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>
