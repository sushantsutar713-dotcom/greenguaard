<?php
/**
 * GreenGuard — Report Status Tracking, Deep Details & Community Timeline
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/priority_calculator.php';

$reportId = (int)($_GET['id'] ?? 0);
$report = DB::findById('reports', $reportId, 'report_id');

if (!$report) {
    // If invalid ID, redirect to explore
    $_SESSION['flash_error'] = 'Requested environmental report was not found.';
    header('Location: explore.php');
    exit;
}

$allReports = DB::all('reports');
$priorityInfo = PriorityCalculator::calculate($report, $allReports);

$comments = DB::filter('comments', fn($c) => (int)($c['report_id'] ?? 0) === $reportId);
$adminActions = DB::filter('admin_actions', fn($a) => (int)($a['report_id'] ?? 0) === $reportId);

$status = strtoupper($report['status'] ?? 'PENDING');
$sev = strtoupper($report['severity'] ?? 'MEDIUM');
$severityClass = 'badge-' . strtolower($sev);

// Status timeline steps
$steps = [
    'PENDING' => ['num' => 1, 'label' => 'Pending Verification', 'icon' => '📝'],
    'UNDER_REVIEW' => ['num' => 2, 'label' => 'Under Review', 'icon' => '🔍'],
    'VERIFIED' => ['num' => 3, 'label' => 'Verified Threat', 'icon' => '🛡️'],
    'ACTION_INITIATED' => ['num' => 4, 'label' => 'Action Initiated', 'icon' => '🚜'],
    'RESOLVED' => ['num' => 5, 'label' => 'Resolved', 'icon' => '✅']
];

$statusOrder = ['PENDING' => 1, 'UNDER_REVIEW' => 2, 'VERIFIED' => 3, 'ACTION_INITIATED' => 4, 'RESOLVED' => 5, 'REJECTED' => 0];
$currentStepNum = $statusOrder[$status] ?? 1;

$pageTitle = "Report #{$reportId} — " . htmlspecialchars($report['title'] ?? 'Threat Details') . ' — GreenGuard';
$activeNav = 'explore';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container">
        <!-- Breadcrumb & Top Bar -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-muted);">
                <a href="explore.php" style="color: var(--primary);">← Back to All Incidents</a>
                <span>•</span>
                <span>Incident #<?= $reportId ?></span>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <?php if (Auth::isAdmin()): ?>
                    <a href="admin/report_details.php?id=<?= $reportId ?>" class="btn btn-outline-primary btn-sm">
                        🛡️ Admin Inspection View
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary btn-sm" onclick="shareReport()">
                    🔗 Share Report
                </button>
            </div>
        </div>

        <!-- Header Overview Card -->
        <div class="form-card" style="margin-bottom: 2rem; padding: 2rem;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                        <span class="badge badge-low">#<?= $reportId ?></span>
                        <span class="badge <?= $severityClass ?>"><?= $sev ?> SEVERITY</span>
                        <span class="badge badge-resolved"><?= htmlspecialchars(str_replace('_', ' ', $report['category'] ?? $report['issue_type'] ?? 'OTHER')) ?></span>
                        <?php if ($status === 'RESOLVED'): ?>
                            <span class="badge badge-resolved">✅ RESOLVED</span>
                        <?php elseif ($status === 'REJECTED'): ?>
                            <span class="badge badge-critical">❌ REJECTED</span>
                        <?php else: ?>
                            <span class="badge badge-progress">⚡ <?= htmlspecialchars(str_replace('_', ' ', $status)) ?></span>
                        <?php endif; ?>
                    </div>

                    <h1 style="font-size: 1.95rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--text-main);">
                        <?= htmlspecialchars($report['title'] ?? 'Environmental Threat') ?>
                    </h1>

                    <p style="color: var(--text-muted); font-size: 0.92rem; margin: 0;">
                        📍 <?= htmlspecialchars($report['address'] ?? 'Location') ?> • Reported by <strong><?= htmlspecialchars($report['user_name'] ?? 'Citizen') ?></strong> on <?= date('d M Y, h:i A', strtotime($report['created_at'] ?? 'now')) ?>
                    </p>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">Responsible Dept</div>
                    <div style="font-weight: 700; color: var(--secondary); font-size: 0.95rem;">
                        <?= htmlspecialchars($report['department'] ?? 'Municipal Environment Cell') ?>
                    </div>
                </div>
            </div>

            <!-- Visual Status Timeline Stepper -->
            <div class="status-timeline">
                <?php foreach ($steps as $key => $step): ?>
                    <?php 
                        $isCompleted = $currentStepNum >= $step['num'];
                        $isActive = $currentStepNum === $step['num'];
                    ?>
                    <div class="timeline-step <?= $isCompleted ? 'completed' : '' ?> <?= $isActive ? 'active' : '' ?>">
                        <div class="timeline-dot">
                            <?= $isCompleted ? '✓' : $step['icon'] ?>
                        </div>
                        <span class="timeline-label"><?= $step['label'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 2-Column Deep Details Grid -->
        <div class="report-details-grid">
            
            <!-- Left Main Column: Evidence & Community Stream -->
            <div>
                <!-- Large Photo Evidence -->
                <div class="details-card">
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
                        <span>📸 Geotagged Photo Evidence</span>
                        <span style="font-size: 0.8rem; font-weight: 500; color: var(--text-muted);">Verified Photographic Record</span>
                    </h2>

                    <?php if (!empty($report['image_path'])): ?>
                        <img src="<?= htmlspecialchars($report['image_path']) ?>" alt="Evidence Photo" class="evidence-photo-large">
                    <?php endif; ?>

                    <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.5rem;">Incident Description &amp; Citizen Notes</h3>
                    <p style="color: var(--text-main); font-size: 0.95rem; line-height: 1.7; margin-bottom: 1.5rem;">
                        <?= nl2br(htmlspecialchars($report['description'] ?? '')) ?>
                    </p>

                    <!-- Community Validation Actions -->
                    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: var(--radius-md); padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.95rem;">Crowdsourced Verification</div>
                            <div style="font-size: 0.82rem; color: var(--text-muted);">Confirming elevates priority score for municipal triage.</div>
                        </div>

                        <div style="display: flex; gap: 0.75rem;">
                            <button type="button" id="confirmThreatBtn" class="btn btn-primary btn-sm" onclick="submitCommunityAction('CONFIRM')">
                                👍 Confirm Threat (<span id="confirmCountVal"><?= (int)($report['community']['confirmations'] ?? 0) ?></span>)
                            </button>
                            <button type="button" id="disputeThreatBtn" class="btn btn-secondary btn-sm" onclick="submitCommunityAction('DISPUTE')">
                                ⚠️ Dispute
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Discussion & Comments Section -->
                <div class="details-card">
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem;">
                        💬 Community Discussion &amp; Official Updates (<span id="commentsTotalCount"><?= count($comments) ?></span>)
                    </h2>

                    <!-- Post Comment Form -->
                    <form id="commentForm" style="margin-bottom: 2rem;">
                        <div class="form-group" style="margin-bottom: 0.75rem;">
                            <textarea id="commentInput" class="form-control" placeholder="Add witness observations, vehicle registration plates, or municipal updates..." rows="3" required></textarea>
                        </div>
                        <div style="display: flex; justify-content: flex-end;">
                            <button type="submit" id="postCommentBtn" class="btn btn-primary btn-sm">
                                💬 Post Comment
                            </button>
                        </div>
                    </form>

                    <!-- Comments Stream List -->
                    <div id="commentsStream">
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $c): ?>
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <?= strtoupper(substr($c['user_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-author">
                                            <span><?= htmlspecialchars($c['user_name'] ?? 'Citizen') ?></span>
                                            <?php if (($c['user_role'] ?? '') === 'ADMIN'): ?>
                                                <span class="badge badge-critical" style="font-size: 0.65rem; padding: 0.15rem 0.45rem;">AUTHORITY</span>
                                            <?php endif; ?>
                                            <span class="comment-date"><?= date('d M, h:i A', strtotime($c['created_at'] ?? 'now')) ?></span>
                                        </div>
                                        <div class="comment-text">
                                            <?= nl2br(htmlspecialchars($c['comment'] ?? '')) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div id="noCommentsMsg" style="text-align: center; color: var(--text-muted); font-size: 0.9rem; padding: 1.5rem 0;">
                                No comments posted yet. Be the first to add evidence or notes.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: AI Analysis, Priority & Map -->
            <div>
                <!-- Smart Urgency Priority Score Gauge -->
                <div class="details-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; margin: 0;">⚡ Threat Priority Score</h3>
                        <span class="badge <?= 'badge-' . strtolower($priorityInfo['level']) ?>">
                            <?= $priorityInfo['level'] ?> URGENCY
                        </span>
                    </div>

                    <div class="priority-gauge-box">
                        <div style="font-size: 2.8rem; font-weight: 800; color: <?= $priorityInfo['badge_color'] ?>; line-height: 1;">
                            <span id="priorityScoreVal"><?= $priorityInfo['score'] ?></span><span style="font-size: 1.2rem; color: var(--text-muted);">/100</span>
                        </div>
                        <div class="priority-meter-bar">
                            <div id="priorityMeterFill" class="priority-meter-fill" style="width: <?= $priorityInfo['score'] ?>%; background: <?= $priorityInfo['badge_color'] ?>;"></div>
                        </div>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
                            Dynamically calculated from ecological weight, proximity cluster density, and citizen confirmations.
                        </p>
                    </div>
                </div>

                <!-- Google Gemini AI Vision Analysis Box -->
                <div class="details-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(14, 165, 233, 0.05)); border-color: rgba(16, 185, 129, 0.3);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                        <div class="ai-badge">
                            <span>🤖</span> <?= htmlspecialchars($report['ai']['source'] ?? 'Google Gemini Vision AI') ?>
                        </div>
                        <span style="font-size: 0.8rem; font-weight: 700; color: #38bdf8;">
                            <?= intval($report['ai']['confidence'] ?? 94) ?>% Confidence
                        </span>
                    </div>

                    <div style="font-size: 0.92rem; color: var(--text-main); font-weight: 600; margin-bottom: 0.85rem;">
                        <?= htmlspecialchars($report['ai']['description'] ?? 'Automated threat detection complete.') ?>
                    </div>

                    <div style="margin-bottom: 0.75rem; font-size: 0.85rem;">
                        <span style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Ecological Impact</span>
                        <strong style="color: var(--status-high);"><?= htmlspecialchars($report['ai']['environmental_impact'] ?? 'Potential groundwater and soil contamination.') ?></strong>
                    </div>

                    <div style="font-size: 0.85rem;">
                        <span style="color: var(--text-muted); display: block; font-size: 0.75rem; text-transform: uppercase;">Recommended Municipal Action</span>
                        <strong style="color: var(--primary);"><?= htmlspecialchars($report['ai']['recommendation'] ?? 'Deploy municipal remediation crew.') ?></strong>
                    </div>
                </div>

                <!-- Mini Leaflet Map & Coordinates -->
                <div class="details-card">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem;">📍 Geographic Location</h3>
                    <div id="miniReportMap" style="height: 200px; border-radius: var(--radius-md); border: 1px solid var(--border-glass); margin-bottom: 0.75rem;"></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        <strong>GPS Coordinates:</strong> <?= number_format($report['latitude'], 4) ?>° N, <?= number_format($report['longitude'], 4) ?>° E
                    </div>
                </div>

                <!-- Municipal Action & Resolution Notes -->
                <div class="details-card">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem;">🏛️ Official Municipal Action Log</h3>
                    <?php if (!empty($report['admin_notes'])): ?>
                        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: var(--radius-sm); padding: 1rem; font-size: 0.88rem; color: var(--text-main); line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($report['admin_notes'])) ?>
                        </div>
                    <?php else: ?>
                        <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">
                            Awaiting official municipal triage notes. Field inspection scheduled.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Mini Map initialization
    const lat = <?= (float)$report['latitude'] ?>;
    const lng = <?= (float)$report['longitude'] ?>;

    const miniMap = L.map('miniReportMap', {
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false
    }).setView([lat, lng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);

    const pinIcon = L.divIcon({
        className: 'custom-pin',
        html: `<div style="background: #ef4444; width: 22px; height: 22px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 10px rgba(239, 68, 68, 0.8); display: flex; align-items: center; justify-content: center; color: white; font-size: 11px;">📍</div>`,
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });

    L.marker([lat, lng], { icon: pinIcon }).addTo(miniMap);

    // Comment form submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = document.getElementById('commentInput').value.trim();
            if (!text) return;

            const postBtn = document.getElementById('postCommentBtn');
            postBtn.disabled = true;
            postBtn.innerText = 'Posting...';

            const fd = new FormData();
            fd.append('report_id', <?= $reportId ?>);
            fd.append('comment', text);

            try {
                const res = await fetch('api/comments.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success && data.comment) {
                    document.getElementById('commentInput').value = '';
                    appendComment(data.comment);
                    showToast('Comment posted!', 'success');
                } else {
                    showToast(data.message || 'Failed to post comment', 'error');
                }
            } catch (err) {
                showToast('Network error', 'error');
            } finally {
                postBtn.disabled = false;
                postBtn.innerText = '💬 Post Comment';
            }
        });
    }
});

function appendComment(c) {
    const noMsg = document.getElementById('noCommentsMsg');
    if (noMsg) noMsg.remove();

    const stream = document.getElementById('commentsStream');
    const item = document.createElement('div');
    item.className = 'comment-item';
    item.innerHTML = `
        <div class="comment-avatar">${escapeHtml(c.user_name.charAt(0).toUpperCase())}</div>
        <div class="comment-content">
            <div class="comment-author">
                <span>${escapeHtml(c.user_name)}</span>
                ${c.user_role === 'ADMIN' ? '<span class="badge badge-critical" style="font-size: 0.65rem; padding: 0.15rem 0.45rem;">AUTHORITY</span>' : ''}
                <span class="comment-date">Just now</span>
            </div>
            <div class="comment-text">${escapeHtml(c.comment)}</div>
        </div>
    `;
    stream.prepend(item);

    const countEl = document.getElementById('commentsTotalCount');
    if (countEl) {
        countEl.innerText = parseInt(countEl.innerText || '0') + 1;
    }
}

async function submitCommunityAction(action) {
    const fd = new FormData();
    fd.append('report_id', <?= $reportId ?>);
    fd.append('action', action);

    try {
        const res = await fetch('api/community_action.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            if (data.confirmations !== undefined) {
                document.getElementById('confirmCountVal').innerText = data.confirmations;
            }
            if (data.priority_score !== undefined) {
                document.getElementById('priorityScoreVal').innerText = data.priority_score;
                document.getElementById('priorityMeterFill').style.width = data.priority_score + '%';
            }
        } else {
            showToast(data.message, 'info');
        }
    } catch (e) {
        showToast('Network error while processing upvote', 'error');
    }
}

function shareReport() {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href);
        showToast('Report link copied to clipboard!', 'success');
    } else {
        prompt('Copy report link:', window.location.href);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
