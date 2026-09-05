<?php
/**
 * GreenGuard — Admin User & Guardian Management Directory
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/impact_calculator.php';

Auth::requireAdmin('../login.php');

$users = DB::all('users');
$reports = DB::all('reports');

$pageTitle = 'Guardian Directory — GreenGuard Authority';
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
                    <li><a href="reports.php" class="nav-link">Master Reports Table</a></li>
                    <li><a href="users.php" class="nav-link active">Guardian Users</a></li>
                    <li><a href="export.php" class="nav-link">Export Data</a></li>
                    <li><a href="../explore.php" class="nav-link" target="_blank">🗺️ Public Threat Map ↗</a></li>
                </ul>
            </nav>

            <div class="nav-actions">
                <a href="../logout.php" class="btn btn-secondary btn-sm">🚪 Logout</a>
            </div>
        </div>
    </header>

    <main class="section" style="padding-top: 2rem;">
        <div class="container">
            
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.25rem;">Community Guardian Directory</h1>
                    <p style="color: var(--text-muted); font-size: 0.92rem; margin: 0;">
                        Manage registered citizens, view individual contributions, and verify official municipal accounts.
                    </p>
                </div>
            </div>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Guardian Name</th>
                            <th>Email Address</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Reports Logged</th>
                            <th>Impact Points</th>
                            <th>Rank</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <?php 
                                $uid = (int)($u['user_id'] ?? 0);
                                $userReports = array_filter($reports, fn($r) => (int)($r['user_id'] ?? 0) === $uid);
                                $impact = ImpactCalculator::getUserImpact($uid, $reports);
                                $isAdm = ($u['role'] ?? '') === 'ADMIN';
                            ?>
                            <tr>
                                <td><strong>#<?= $uid ?></strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);">
                                        <?= htmlspecialchars($u['name'] ?? 'User') ?>
                                    </div>
                                    <?php if (!empty($u['department'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--secondary);">
                                            <?= htmlspecialchars($u['department']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= $isAdm ? 'badge-critical' : 'badge-resolved' ?>">
                                        <?= htmlspecialchars($u['role'] ?? 'USER') ?>
                                    </span>
                                </td>
                                <td><strong><?= count($userReports) ?></strong></td>
                                <td>
                                    <strong style="color: var(--primary);"><?= number_format($impact['score']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?= $impact['badge'] ?>" style="font-size: 0.72rem;">
                                        <?= $impact['rank'] ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.82rem; color: var(--text-muted);">
                                    <?= date('d M Y', strtotime($u['created_at'] ?? 'now')) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>
