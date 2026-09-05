<?php
/**
 * GreenGuard — Environmental Analytics Dashboard & Hotspot Intelligence
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/impact_calculator.php';
require_once __DIR__ . '/includes/hotspot_service.php';
require_once __DIR__ . '/includes/ai_service.php';

$reports = DB::all('reports');
$users = DB::all('users');
$globalImpact = ImpactCalculator::getGlobalImpact($reports, $users);
$hotspots = HotspotService::detectHotspots($reports);

// Calculate metrics
$totalReports = count($reports);
$verifiedCount = count(array_filter($reports, fn($r) => in_array($r['status'] ?? '', ['VERIFIED', 'ACTION_INITIATED', 'RESOLVED'])));
$resolvedCount = count(array_filter($reports, fn($r) => ($r['status'] ?? '') === 'RESOLVED'));
$pendingCount = count(array_filter($reports, fn($r) => in_array($r['status'] ?? '', ['PENDING', 'UNDER_REVIEW'])));
$criticalHighCount = count(array_filter($reports, fn($r) => in_array(strtoupper($r['severity'] ?? ''), ['CRITICAL', 'HIGH'])));

// Aggregations for Chart.js
$categoryCounts = [];
$categories = AIService::CATEGORIES;
foreach ($categories as $catKey => $catLabel) {
    $categoryCounts[$catLabel] = 0;
}
foreach ($reports as $r) {
    $cat = $r['category'] ?? $r['issue_type'] ?? 'OTHER';
    $label = $categories[$cat] ?? 'Other';
    $categoryCounts[$label] = ($categoryCounts[$label] ?? 0) + 1;
}

$statusCounts = [
    'Pending' => 0,
    'Under Review' => 0,
    'Verified' => 0,
    'In Progress' => 0,
    'Resolved' => 0
];
foreach ($reports as $r) {
    $st = strtoupper($r['status'] ?? 'PENDING');
    if ($st === 'PENDING') $statusCounts['Pending']++;
    elseif ($st === 'UNDER_REVIEW') $statusCounts['Under Review']++;
    elseif ($st === 'VERIFIED') $statusCounts['Verified']++;
    elseif ($st === 'ACTION_INITIATED') $statusCounts['In Progress']++;
    elseif ($st === 'RESOLVED') $statusCounts['Resolved']++;
}

$severityCounts = [
    'Critical' => 0,
    'High' => 0,
    'Medium' => 0,
    'Low' => 0
];
foreach ($reports as $r) {
    $sev = ucfirst(strtolower($r['severity'] ?? 'Medium'));
    if (isset($severityCounts[$sev])) {
        $severityCounts[$sev]++;
    }
}

$pageTitle = 'Environmental Analytics Dashboard — GreenGuard';
$activeNav = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container">
        <!-- Dashboard Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="section-tag">Municipal Environmental Intelligence</span>
                <h1 class="section-title" style="margin-bottom: 0.35rem;">Environmental Health Dashboard</h1>
                <p class="section-subtitle">
                    Real-time community telemetry, AI threat categorization, and remediation velocity.
                </p>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <a href="report.php" class="btn btn-primary">📸 Report Threat</a>
                <a href="explore.php" class="btn btn-secondary">🗺️ Open Map</a>
            </div>
        </div>

        <!-- KPI Metric Cards Grid -->
        <div class="dashboard-metrics-grid">
            <div class="metric-card">
                <div class="metric-icon blue">📊</div>
                <div>
                    <div class="metric-value"><?= $totalReports ?></div>
                    <div class="metric-title">Total Threats Logged</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon purple">🛡️</div>
                <div>
                    <div class="metric-value"><?= $verifiedCount ?></div>
                    <div class="metric-title">Verified Incidents</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon green">✅</div>
                <div>
                    <div class="metric-value"><?= $resolvedCount ?></div>
                    <div class="metric-title">Resolved by Authorities</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon yellow">⏳</div>
                <div>
                    <div class="metric-value"><?= $pendingCount ?></div>
                    <div class="metric-title">Pending / In Triage</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon red">🔴</div>
                <div>
                    <div class="metric-value"><?= $criticalHighCount ?></div>
                    <div class="metric-title">High / Critical Urgency</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon green">🌿</div>
                <div>
                    <div class="metric-value"><?= $globalImpact['score'] ?></div>
                    <div class="metric-title">Platform Impact Score</div>
                </div>
            </div>
        </div>

        <!-- 4-Chart Visualizations Grid -->
        <div class="charts-grid">
            <!-- 1. Reports by Category (Bar Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">📊 Reports by Environmental Category</h3>
                    <span class="badge badge-low">All-Time</span>
                </div>
                <div class="chart-canvas-wrapper">
                    <canvas id="categoryBarChart"></canvas>
                </div>
            </div>

            <!-- 2. Report Status Breakdown (Doughnut Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">🍩 Incident Status Lifecycle</h3>
                    <span class="badge badge-resolved">Live Ratio</span>
                </div>
                <div class="chart-canvas-wrapper">
                    <canvas id="statusDoughnutChart"></canvas>
                </div>
            </div>

            <!-- 3. Report Trends Over Time (Smooth Line Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">📈 Threat Frequency Trends</h3>
                    <span class="badge badge-progress">Monthly Velocity</span>
                </div>
                <div class="chart-canvas-wrapper">
                    <canvas id="trendLineChart"></canvas>
                </div>
            </div>

            <!-- 4. Severity Distribution (Polar/Bar Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">⚠️ Ecological Severity Distribution</h3>
                    <span class="badge badge-critical">Risk Profile</span>
                </div>
                <div class="chart-canvas-wrapper">
                    <canvas id="severityPolarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Environmental Hotspots Detection Section -->
        <div class="details-card" style="margin-bottom: 2.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 0.25rem;">
                        🔥 Environmental Hotspot Detection
                    </h2>
                    <p style="color: var(--text-muted); font-size: 0.92rem; margin: 0;">
                        AI proximity clustering automatically pinpoints concentrated geographical clusters requiring urgent municipal focus.
                    </p>
                </div>
                <span class="badge badge-critical" style="font-size: 0.82rem; padding: 0.35rem 0.8rem;">
                    <?= count($hotspots) ?> Hotspot Clusters Detected
                </span>
            </div>

            <?php if (!empty($hotspots)): ?>
                <div class="hotspots-grid">
                    <?php foreach ($hotspots as $h): ?>
                        <div class="hotspot-card">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span class="badge <?= $h['risk_badge'] ?>"><?= $h['risk_level'] ?> RISK</span>
                                <span style="font-size: 0.85rem; font-weight: 700; color: #38bdf8;">
                                    📍 <?= $h['incident_count'] ?> Incidents
                                </span>
                            </div>
                            <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main);">
                                <?= htmlspecialchars($h['hotspot_name']) ?>
                            </h3>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                                <strong>Primary Threat:</strong> <?= htmlspecialchars($h['top_category_label']) ?> • 
                                <span style="color: var(--status-critical);"><?= $h['unresolved_count'] ?> Unresolved</span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-subtle); margin-bottom: 1rem;">
                                Coordinates: <?= $h['center_lat'] ?>° N, <?= $h['center_lon'] ?>° E
                            </div>
                            <a href="explore.php?search=<?= urlencode(explode(',', $h['hotspot_name'])[0]) ?>" class="btn btn-outline-primary btn-sm btn-block">
                                Inspect Cluster on Threat Map →
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                    No concentrated hotspots detected in this radius.
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Incidents Table -->
        <div class="details-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0;">⚡ Latest Threat Activity Stream</h2>
                <a href="explore.php" class="btn btn-secondary btn-sm">View All in Master Explorer →</a>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Threat Issue</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Severity</th>
                            <th>Priority Score</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($reports, 0, 5) as $r): ?>
                            <?php 
                                $sev = strtoupper($r['severity'] ?? 'MEDIUM');
                                $severityClass = 'badge-' . strtolower($sev);
                                $status = strtoupper($r['status'] ?? 'PENDING');
                                $statusClass = $status === 'RESOLVED' ? 'badge-resolved' : ($status === 'VERIFIED' ? 'badge-verified' : ($status === 'ACTION_INITIATED' ? 'badge-progress' : 'badge-pending'));
                            ?>
                            <tr>
                                <td><strong>#<?= $r['report_id'] ?></strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem;">
                                        <?= htmlspecialchars(substr($r['title'] ?? 'Incident', 0, 45)) ?>...
                                    </div>
                                    <span style="font-size: 0.78rem; color: var(--text-muted);">
                                        By <?= htmlspecialchars($r['user_name'] ?? 'Citizen') ?>
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
                                    <strong style="color: <?= $r['priority_score'] > 75 ? '#ef4444' : ($r['priority_score'] > 50 ? '#f97316' : '#10b981') ?>;">
                                        ⚡ <?= $r['priority_score'] ?? 50 ?>
                                    </strong>
                                </td>
                                <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $status)) ?></span></td>
                                <td>
                                    <a href="report_details.php?id=<?= $r['report_id'] ?>" class="btn btn-secondary btn-sm">
                                        Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Embed Dynamic Chart Data for js/dashboard.js -->
<script>
window.chartData = {
    categories: <?= json_encode($categoryCounts) ?>,
    statuses: <?= json_encode($statusCounts) ?>,
    severities: <?= json_encode($severityCounts) ?>
};
</script>

<?php 
$extraScripts = '<script src="js/dashboard.js"></script>';
require_once __DIR__ . '/includes/footer.php'; 
?>
