<?php
/**
 * GreenGuard API — Notification Polling & Read Status Handler
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$userId = Auth::id() ?: 2; // Default to demo citizen

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'mark_read';

    if ($action === 'mark_all_read') {
        $allNotifs = DB::all('notifications');
        foreach ($allNotifs as &$n) {
            if ((int)($n['user_id'] ?? 0) === $userId) {
                $n['is_read'] = true;
            }
        }
        unset($n);
        DB::write('notifications', $allNotifs);

        echo json_encode(['success' => true, 'message' => 'All notifications marked as read.']);
        exit;
    } elseif ($action === 'mark_read') {
        $notifId = (int)($_POST['notification_id'] ?? 0);
        if ($notifId) {
            DB::update('notifications', $notifId, ['is_read' => true], 'notification_id');
            echo json_encode(['success' => true]);
            exit;
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userNotifs = DB::filter('notifications', fn($n) => (int)($n['user_id'] ?? 0) === $userId);
    usort($userNotifs, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
    
    $unreadCount = count(array_filter($userNotifs, fn($n) => empty($n['is_read'])));

    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount,
        'notifications' => $userNotifs
    ]);
    exit;
}
