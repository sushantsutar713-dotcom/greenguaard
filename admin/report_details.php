<?php
/**
 * GreenGuard — Admin Incident Triage, Status Transition & Department Assign
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/priority_calculator.php';

Auth::requireAdmin('../login.php');

$reportId = (int)($_GET['id'] ?? 0);
$report = DB::findById('reports', $reportId, 'report_id');

if (!$report) {
    $_SESSION['flash_error'] = 'Report not found.';
    header('Location: reports.php');
    exit;
}

$allReports = DB::all('reports');
$priorityCalc = PriorityCalculator::calculate($report, $allReports);
$auditLogs = DB::filter('admin_actions', fn($a) => (int)($a['report_id'] ?? 0) === $reportId);

$status = strtoupper($report['status'] ?? 'PENDING');
$sev = strtoupper($report['severity'] ?? 'MEDIUM');
$severityClass = 'badge-' . strtolower($sev);

$departments = [
    'Municipal Solid Waste Management Dept',
    'Forest & Tree Protection Authority',
    'State Pollution Control Board (SPCB)',
    'Jal Sansthan / Municipal Water Board',
    'Fire & Disaster Emergency Services',
    'Coastal Zone Management Authority',
    'District Environmental Magistrate'
];

$pageTitle = "Admin Triage #{$reportId} — " . htmlspecialchars($report['title'] ?? '') . ' — GreenGuard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                    <li><a href="../report_details.php?id=<?= $reportId ?>" class="nav-link" target="_blank">👁️ Citizen View ↗</a></li>
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
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">
                    <a href="reports.php" style="color: var(--primary);">← Back to Reports Queue</a>
                    <span>•</span>
                    <span>Admin Triage #<?= $reportId ?></span>
                </div>
                <div>
                    <span class="badge badge-low">Score: <?= $report['priority_score'] ?? 50 ?></span>
                    <span class="badge <?= $severityClass ?>"><?= $sev ?> SEVERITY</span>
                </div>
            </div>

            <!-- 2-Column Triage Grid -->
            <div class="report-details-grid">
                
                <!-- Left Column: Evidence & AI Analysis -->
                <div>
                    <!-- Evidence Photo -->
                    <div class="details-card">
                        <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem;">
                            📸 Photographic Evidence &amp; Reporter Details
                        </h2>
                        
                        <?php if (!empty($report['image_path'])): ?>
                            <img src="../<?= htmlspecialchars($report['image_path']) ?>" alt="Evidence photo" class="evidence-photo-large">
                        <?php endif; ?>

                        <h1 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-main);">
                            <?= htmlspecialchars($report['title'] ?? 'Incident') ?>
                        </h1>

                        <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                            <?= nl2br(htmlspecialchars($report['description'] ?? '')) ?>
                        </p>

                        <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass); font-size: 0.88rem; color: var(--text-muted);">
                            <div><strong>Reporter:</strong> <?= htmlspecialchars($report['user_name'] ?? 'Citizen') ?></div>
                            <div><strong>Location:</strong> <?= htmlspecialchars($report['address'] ?? 'Unknown') ?></div>
                            <div><strong>GPS:</strong> <?= number_format($report['latitude'], 4) ?>° N, <?= number_format($report['longitude'], 4) ?>° E</div>
                            <div><strong>Reported Date:</strong> <?= date('d M Y, h:i A', strtotime($report['created_at'] ?? 'now')) ?></div>
                            <?php if (!empty($report['contact_phone'])): ?>
                                <div><strong>Contact Phone:</strong> <?= htmlspecialchars($report['contact_phone']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- AI Vision Assessment -->
                    <div class="details-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(14, 165, 233, 0.05)); border-color: rgba(16, 185, 129, 0.3);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                            <div class="ai-badge">🤖 <?= htmlspecialchars($report['ai']['source'] ?? 'Google Gemini AI') ?></div>
                            <span style="font-size: 0.8rem; font-weight: 700; color: #38bdf8;"><?= intval($report['ai']['confidence'] ?? 94) ?>% Match</span>
                        </div>
                        <div style="font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <strong>Visible Findings:</strong> <?= htmlspecialchars($report['ai']['description'] ?? 'Automated inspection.') ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                            <strong>Ecological Risk:</strong> <?= htmlspecialchars($report['ai']['environmental_impact'] ?? 'Moderate.') ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--primary);">
                            <strong>Recommended Protocol:</strong> <?= htmlspecialchars($report['ai']['recommendation'] ?? 'Deploy sanitation team.') ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Official Triage Action Form -->
                <div>
                    <!-- Status & Department Transition Form -->
                    <div class="details-card" style="border-top: 4px solid var(--primary);">
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem;">
                            🛡️ Official Action &amp; Triage Portal
                        </h2>

                        <form method="POST" action="../api/update_report_status.php">
                            <input type="hidden" name="report_id" value="<?= $reportId ?>">

                            <div class="form-group">
                                <label class="form-label" for="reportStatus">Transition Incident Status</label>
                                <select id="reportStatus" name="status" class="form-control" required>
                                    <option value="PENDING" <?= $status === 'PENDING' ? 'selected' : '' ?>>📝 Pending Verification</option>
                                    <option value="UNDER_REVIEW" <?= $status === 'UNDER_REVIEW' ? 'selected' : '' ?>>🔍 Under Review / Triage</option>
                                    <option value="VERIFIED" <?= $status === 'VERIFIED' ? 'selected' : '' ?>>🛡️ Verified Threat (Official Inspection)</option>
                                    <option value="ACTION_INITIATED" <?= $status === 'ACTION_INITIATED' ? 'selected' : '' ?>>🚜 Action Initiated (Field Crew On-Site)</option>
                                    <option value="RESOLVED" <?= $status === 'RESOLVED' ? 'selected' : '' ?>>✅ Resolved (Remediation Complete)</option>
                                    <option value="REJECTED" <?= $status === 'REJECTED' ? 'selected' : '' ?>>❌ Rejected / False Alarm</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="deptSelect">Assign Responsible Department</label>
                                <select id="deptSelect" name="department" class="form-control" required>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept) ?>" <?= ($report['department'] ?? '') === $dept ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="adminNotes">Official Resolution Remarks &amp; Citizen Update</label>
                                <textarea id="adminNotes" name="admin_notes" class="form-control" rows="4" placeholder="Enter task force vehicle numbers, inspection findings, fines levied, or cleanup completion notes..."><?= htmlspecialchars($report['admin_notes'] ?? '') ?></textarea>
                                <p class="form-hint">This remark is logged in the public audit trail and sent directly to the reporting citizen.</p>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom: 1rem;">
                                💾 Save Triage Action &amp; Dispatch Notification →
                            </button>
                        </form>

                        <!-- Delete Fraudulent Report Option -->
                        <form method="POST" action="../api/update_report_status.php" onsubmit="return confirm('WARNING: Are you sure you want to delete this report permanently as fraudulent?');" style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-glass);">
                            <input type="hidden" name="report_id" value="<?= $reportId ?>">
                            <input type="hidden" name="delete_report" value="1">
                            <button type="submit" class="btn btn-danger btn-block btn-sm">
                                🗑️ Delete Fraudulent / Duplicate Report
                            </button>
                        </form>
                    </div>

                    <!-- Audit Trail Log -->
                    <div class="details-card">
                        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem;">
                            📜 Official Action Audit Trail
                        </h3>
                        <?php if (!empty($auditLogs)): ?>
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php foreach ($auditLogs as $log): ?>
                                    <div style="background: rgba(15, 23, 42, 0.6); padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-glass); font-size: 0.85rem;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                            <strong style="color: var(--secondary);"><?= htmlspecialchars($log['action'] ?? 'ACTION') ?></strong>
                                            <span style="color: var(--text-muted); font-size: 0.75rem;"><?= date('d M, h:i A', strtotime($log['created_at'] ?? 'now')) ?></span>
                                        </div>
                                        <div style="color: var(--text-main);"><?= htmlspecialchars($log['comment'] ?? '') ?></div>
                                        <div style="color: var(--text-subtle); font-size: 0.75rem; margin-top: 0.25rem;">Officer: <?= htmlspecialchars($log['admin_name'] ?? 'Admin') ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">No prior administrative triage actions recorded.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../js/main.js"></script>
</body>
</html>
