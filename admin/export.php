<?php
/**
 * GreenGuard — Admin Incident Reports Data Export
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

Auth::requireAdmin('../login.php');

$format = strtolower(trim($_GET['format'] ?? 'csv'));
$reports = DB::all('reports');

if ($format === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="greenguard_reports_' . date('Ymd_His') . '.json"');
    echo json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Default CSV export
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="greenguard_reports_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, [
    'Report ID',
    'Title',
    'Category',
    'Severity',
    'Status',
    'Priority Score',
    'Address',
    'Latitude',
    'Longitude',
    'Reporter Name',
    'Assigned Department',
    'Reported Date',
    'Admin Notes',
    'AI Category',
    'AI Confidence'
]);

foreach ($reports as $r) {
    fputcsv($output, [
        $r['report_id'] ?? '',
        $r['title'] ?? '',
        $r['category'] ?? $r['issue_type'] ?? '',
        $r['severity'] ?? '',
        $r['status'] ?? '',
        $r['priority_score'] ?? '',
        $r['address'] ?? '',
        $r['latitude'] ?? '',
        $r['longitude'] ?? '',
        $r['user_name'] ?? '',
        $r['department'] ?? '',
        $r['created_at'] ?? '',
        $r['admin_notes'] ?? '',
        $r['ai']['category'] ?? '',
        ($r['ai']['confidence'] ?? '') . '%'
    ]);
}

fclose($output);
exit;
