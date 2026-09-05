<?php
/**
 * GreenGuard API — Comments & Discussion Stream Endpoint
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$currentUser = Auth::user();
$userId = Auth::id() ?: 2;
$userName = $currentUser['name'] ?? 'Citizen Guardian';
$userRole = $currentUser['role'] ?? 'USER';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = (int)($_POST['report_id'] ?? 0);
    $commentText = trim($_POST['comment'] ?? '');

    if (!$reportId || empty($commentText)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
        exit;
    }

    $report = DB::findById('reports', $reportId, 'report_id');
    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found.']);
        exit;
    }

    $newComment = [
        'report_id' => $reportId,
        'user_id' => $userId,
        'user_name' => ($userRole === 'ADMIN' ? $userName . ' (Admin)' : $userName),
        'user_role' => $userRole,
        'comment' => $commentText,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $saved = DB::insert('comments', $newComment, 'comment_id');

    // Also notify the report owner if comment is by someone else
    if ((int)($report['user_id'] ?? 0) !== $userId) {
        DB::insert('notifications', [
            'user_id' => (int)$report['user_id'],
            'report_id' => $reportId,
            'title' => 'New Comment on Report',
            'message' => "{$userName} commented on your report #{$reportId}: \"" . substr($commentText, 0, 60) . '..."',
            'type' => 'COMMENT',
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ], 'notification_id');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Comment posted successfully!',
        'comment' => $saved
    ]);
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $reportId = (int)($_GET['report_id'] ?? 0);
    $comments = DB::filter('comments', fn($c) => (int)($c['report_id'] ?? 0) === $reportId);
    echo json_encode(['success' => true, 'comments' => $comments]);
    exit;
}
