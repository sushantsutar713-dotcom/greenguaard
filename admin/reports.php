<?php
/**
 * GreenGuard — Admin Master Reports Management Table
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ai_service.php';

Auth::requireAdmin('../login.php');

$reports = DB::all('reports');
$categories = AIService::CATEGORIES;

// Filter handling
$categoryFilter = strtoupper(trim($_GET['category'] ?? 'ALL'));
$severityFilter = strtoupper(trim($_GET['severity'] ?? 'ALL'));
$statusFilter = strtoupper(trim($_GET['status'] ?? 'ALL'));
$searchQuery = strtolower(trim($_GET['search'] ?? ''));

$filteredReports = array_filter($reports, function($r) use ($categoryFilter, $severityFilter, $statusFilter, $searchQuery) {
    if ($categoryFilter !== 'ALL') {
        $cat = strtoupper($r['category'] ?? $r['issue_type'] ?? 'OTHER');
        if ($cat !== $categoryFilter) return false;
    }
    if ($severityFilter !== 'ALL') {
        $sev = strtoupper($r['severity'] ?? 'MEDIUM');
        if ($sev !== $severityFilter) return false;
    }
    if ($statusFilter !== 'ALL') {
        $st = strtoupper($r['status'] ?? 'PENDING');
        if ($st !== $statusFilter) return false;
    }
    if (!empty($searchQuery)) {
        $haystack = strtolower(($r['title'] ?? '') . ' ' . ($r['description'] ?? '') . ' ' . ($r['address'] ?? '') . ' ' . ($r['user_name'] ?? ''));
        if (!str_contains($haystack, $searchQuery)) return false;
    }
    return true;
});

// Sort by latest
usort($filteredReports, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

$pageTitle = 'Master Reports Management — GreenGuard Authority';
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
                    <li><a href="dashboard.php" class="nav-link">Triage Overview</a></li>
                    <li><a href="reports.php" class="nav-link active">Master Reports Table</a></li>
                    <li><a href="users.php" class="nav-link">Guardian Users</a></li>
                    <li><a href="export.php" class="nav-link">Export Data</a></li>
                    <li><a href="../explore.php" class="nav-link" target="_blank">🗺️ Public Threat Map ↗</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <a href="../logout.php" class="btn btn-secondary btn-sm">🚪 Logout</a>
            </div>

            <button class="mobile-toggle" aria-label="Toggle Navigation">☰</button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="section" style="padding-top: 2rem;">
        <div class="container">
            
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.25rem;">Master Environmental Incident Records</h1>
                    <p style="color: var(--text-muted); font-size: 0.92rem; margin: 0;">
                        Showing <?= count($filteredReports) ?> of <?= count($reports) ?> total incidents recorded in database.
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="export.php?format=csv" class="btn btn-secondary btn-sm">📥 Export CSV</a>
                    <a href="export.php?format=json" class="btn btn-secondary btn-sm">📥 Export JSON</a>
                </div>
            </div>

            <!-- Filter Controls Form -->
            <form method="GET" action="reports.php" class="explore-filter-bar" style="margin-bottom: 2rem;">
                <div style="flex-grow: 1; min-width: 220px;">
                    <input type="text" name="search" class="form-control" placeholder="🔍 Search issue, reporter, or address..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>

                <div style="min-width: 170px;">
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="ALL">All Categories</option>
                        <?php foreach ($categories as $catKey => $catLabel): ?>
                            <option value="<?= $catKey ?>" <?= $categoryFilter === $catKey ? 'selected' : '' ?>><?= htmlspecialchars($catLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="min-width: 140px;">
                    <select name="severity" class="form-control" onchange="this.form.submit()">
                        <option value="ALL">All Severities</option>
                        <option value="CRITICAL" <?= $severityFilter === 'CRITICAL' ? 'selected' : '' ?>>🔴 Critical</option>
                        <option value="HIGH" <?= $severityFilter === 'HIGH' ? 'selected' : '' ?>>🟠 High</option>
                        <option value="MEDIUM" <?= $severityFilter === 'MEDIUM' ? 'selected' : '' ?>>🟡 Medium</option>
                        <option value="LOW" <?= $severityFilter === 'LOW' ? 'selected' : '' ?>>🔵 Low</option>
                    </select>
                </div>

                <div style="min-width: 150px;">
                    <select name="status" class="form-control" onchange="this.form.submit()">
                        <option value="ALL">All Statuses</option>
                        <option value="PENDING" <?= $statusFilter === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                        <option value="UNDER_REVIEW" <?= $statusFilter === 'UNDER_REVIEW' ? 'selected' : '' ?>>Under Review</option>
                        <option value="VERIFIED" <?= $statusFilter === 'VERIFIED' ? 'selected' : '' ?>>Verified</option>
                        <option value="ACTION_INITIATED" <?= $statusFilter === 'ACTION_INITIATED' ? 'selected' : '' ?>>Action Initiated</option>
                        <option value="RESOLVED" <?= $statusFilter === 'RESOLVED' ? 'selected' : '' ?>>Resolved</option>
                        <option value="REJECTED" <?= $statusFilter === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="reports.php" class="btn btn-secondary btn-sm">Reset</a>
            </form>

            <!-- Master Reports Table -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Report ID</th>
                            <th>Issue</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Severity</th>
                            <th>Date</th>
                            <th>Reporter</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($filteredReports)): ?>
                            <?php foreach ($filteredReports as $r): ?>
                                <?php 
                                    $sev = strtoupper($r['severity'] ?? 'MEDIUM');
                                    $severityClass = 'badge-' . strtolower($sev);
                                    $status = strtoupper($r['status'] ?? 'PENDING');
                                    $statusClass = $status === 'RESOLVED' ? 'badge-resolved' : ($status === 'VERIFIED' ? 'badge-verified' : ($status === 'ACTION_INITIATED' ? 'badge-progress' : ($status === 'REJECTED' ? 'badge-critical' : 'badge-pending')));
                                ?>
                                <tr>
                                    <td><strong>#<?= $r['report_id'] ?></strong></td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem; max-width: 250px;">
                                            <?= htmlspecialchars($r['title'] ?? 'Incident') ?>
                                        </div>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">
                                            ⚡ Score: <?= $r['priority_score'] ?? 50 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-low" style="font-size: 0.72rem;">
                                            <?= htmlspecialchars(str_replace('_', ' ', $r['category'] ?? $r['issue_type'] ?? 'OTHER')) ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 180px; font-size: 0.85rem;">
                                        <?= htmlspecialchars(explode(',', $r['address'] ?? 'Unknown')[0]) ?>
                                    </td>
                                    <td><span class="badge <?= $severityClass ?>"><?= $sev ?></span></td>
                                    <td style="font-size: 0.82rem; color: var(--text-muted);">
                                        <?= date('d M Y, h:i A', strtotime($r['created_at'] ?? 'now')) ?>
                                    </td>
                                    <td style="font-size: 0.88rem;">
                                        <?= htmlspecialchars($r['user_name'] ?? 'Citizen') ?>
                                    </td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(str_replace('_', ' ', $status)) ?></span></td>
                                    <td>
                                        <div style="display: flex; gap: 0.4rem;">
                                            <a href="report_details.php?id=<?= $r['report_id'] ?>" class="btn btn-primary btn-sm" title="Triage & Update Status">
                                                Triage →
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                    No reports found matching criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>
