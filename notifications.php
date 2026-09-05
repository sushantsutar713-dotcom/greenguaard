<?php
/**
 * GreenGuard — In-App Notifications Center
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

Auth::requireLogin('login.php');

$userId = Auth::id();
$userNotifs = DB::filter('notifications', fn($n) => (int)($n['user_id'] ?? 0) === $userId);
usort($userNotifs, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

$unreadCount = count(array_filter($userNotifs, fn($n) => empty($n['is_read'])));

$pageTitle = 'Notification Center — GreenGuard';
$activeNav = '';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section">
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.25rem;">🔔 Notification Center</h1>
                <p style="color: var(--text-muted); font-size: 0.92rem; margin: 0;">
                    Real-time status changes, verification updates, and municipal responses for your reported incidents.
                </p>
            </div>

            <?php if ($unreadCount > 0): ?>
                <button type="button" class="btn btn-secondary btn-sm" onclick="markAllAsRead()">
                    ✓ Mark All as Read
                </button>
            <?php endif; ?>
        </div>

        <div class="details-card">
            <?php if (!empty($userNotifs)): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($userNotifs as $n): ?>
                        <?php 
                            $isRead = !empty($n['is_read']);
                            $type = strtoupper($n['type'] ?? '');
                            $icon = '🔔';
                            $borderColor = 'var(--border-glass)';
                            if ($type === 'RESOLVED') { $icon = '✅'; $borderColor = 'var(--status-resolved)'; }
                            elseif ($type === 'VERIFIED') { $icon = '🛡️'; $borderColor = '#8b5cf6'; }
                            elseif ($type === 'IN_PROGRESS' || $type === 'ACTION_INITIATED') { $icon = '🚜'; $borderColor = 'var(--secondary)'; }
                            elseif ($type === 'COMMENT') { $icon = '💬'; $borderColor = '#eab308'; }
                        ?>
                        <div style="background: <?= $isRead ? 'rgba(15, 23, 42, 0.4)' : 'rgba(16, 185, 129, 0.08)' ?>; border: 1px solid <?= $isRead ? 'var(--border-glass)' : 'rgba(16, 185, 129, 0.3)' ?>; border-left: 4px solid <?= $borderColor ?>; border-radius: var(--radius-md); padding: 1.25rem; transition: var(--transition);">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                                <div style="display: flex; gap: 1rem;">
                                    <div style="font-size: 1.5rem; flex-shrink: 0;"><?= $icon ?></div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 1rem; margin-bottom: 0.25rem;">
                                            <?= htmlspecialchars($n['title'] ?? 'Incident Update') ?>
                                            <?php if (!$isRead): ?>
                                                <span class="badge badge-critical" style="font-size: 0.65rem; margin-left: 0.5rem;">NEW</span>
                                            <?php endif; ?>
                                        </div>
                                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.75rem; line-height: 1.5;">
                                            <?= htmlspecialchars($n['message'] ?? '') ?>
                                        </p>
                                        <div style="font-size: 0.78rem; color: var(--text-subtle);">
                                            📅 <?= date('d M Y, h:i A', strtotime($n['created_at'] ?? 'now')) ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($n['report_id'])): ?>
                                    <a href="report_details.php?id=<?= $n['report_id'] ?>" class="btn btn-secondary btn-sm" style="flex-shrink: 0;">
                                        Inspect Threat →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 1rem; color: var(--text-muted);">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔕</div>
                    <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No notifications yet</h3>
                    <p style="font-size: 0.9rem;">You will receive live notifications when your reports are reviewed, verified, or resolved by municipal authorities.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function markAllAsRead() {
    const fd = new FormData();
    fd.append('action', 'mark_all_read');

    try {
        const res = await fetch('api/notifications.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            showToast('All notifications marked as read!', 'success');
            setTimeout(() => window.location.reload(), 800);
        }
    } catch (e) {
        showToast('Network error', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
