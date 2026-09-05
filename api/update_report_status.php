<?php
/**
 * GreenGuard API — Admin Status Transition & Triage Endpoint
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

if (!Auth::isAdmin()) {
    if ($isAjax) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Admin privileges required.']);
    } else {
        $_SESSION['flash_error'] = 'Admin privileges required.';
        header('Location: ../admin/login.php');
    }
    exit;
}

$adminUser = Auth::user();
$adminId = Auth::id();
$adminName = $adminUser['name'] ?? 'Admin Officer';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$reportId = (int)($_POST['report_id'] ?? 0);
$deleteAction = !empty($_POST['delete_report']);

if (!$reportId) {
    echo json_encode(['success' => false, 'message' => 'Invalid report ID.']);
    exit;
}

$report = DB::findById('reports', $reportId, 'report_id');
if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found.']);
    exit;
}

// Handle Delete / Flag Fraudulent Report
if ($deleteAction) {
    DB::delete('reports', $reportId, 'report_id');
    
    // Log action
    DB::insert('admin_actions', [
        'admin_id' => $adminId,
        'admin_name' => $adminName,
        'report_id' => $reportId,
        'action' => 'DELETED_FRAUD',
        'department' => $report['department'] ?? 'Administration',
        'comment' => 'Report deleted as fraudulent or duplicate.',
        'created_at' => date('Y-m-d H:i:s')
    ], 'action_id');

    $_SESSION['flash_success'] = "Report #{$reportId} deleted successfully.";
    if ($isAjax) {
        echo json_encode(['success' => true, 'message' => "Report #{$reportId} deleted."]);
    } else {
        header('Location: ../admin/reports.php');
    }
    exit;
}

// Update Status, Department & Notes
$newStatus = strtoupper(trim($_POST['status'] ?? $report['status']));
$newDepartment = trim($_POST['department'] ?? $report['department'] ?? 'Municipal Environment Cell');
$newNotes = trim($_POST['admin_notes'] ?? $report['admin_notes'] ?? '');

$updates = [
    'status' => $newStatus,
    'department' => $newDepartment,
    'admin_notes' => $newNotes,
    'updated_at' => date('Y-m-d H:i:s')
];

$updatedReport = DB::update('reports', $reportId, $updates, 'report_id');

// Record Audit Log
DB::insert('admin_actions', [
    'admin_id' => $adminId,
    'admin_name' => $adminName,
    'report_id' => $reportId,
    'action' => $newStatus,
    'department' => $newDepartment,
    'comment' => $newNotes ?: "Status transitioned to {$newStatus}",
    'created_at' => date('Y-m-d H:i:s')
], 'action_id');

// Notify Reporter Citizen
if (!empty($report['user_id'])) {
    $readableStatus = str_replace('_', ' ', $newStatus);
    DB::insert('notifications', [
        'user_id' => (int)$report['user_id'],
        'report_id' => $reportId,
        'title' => "Report #{$reportId} Status: {$readableStatus}",
        'message' => "Your report #{$reportId} ({$report['title']}) is now {$readableStatus} by {$newDepartment}." . ($newNotes ? " Remarks: \"{$newNotes}\"" : ''),
        'type' => $newStatus,
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s')
    ], 'notification_id');
}

$_SESSION['flash_success'] = "Report #{$reportId} updated to {$newStatus} successfully.";

if ($isAjax) {
    echo json_encode([
        'success' => true,
        'message' => "Report #{$reportId} updated to {$newStatus}.",
        'report' => $updatedReport
    ]);
} else {
    header("Location: ../admin/report_details.php?id={$reportId}");
}
exit;
