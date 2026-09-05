<?php
/**
 * GreenGuard API — Filtered Reports & GeoJSON Data Endpoint
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/priority_calculator.php';

$category = strtoupper(trim($_GET['category'] ?? 'ALL'));
$severity = strtoupper(trim($_GET['severity'] ?? 'ALL'));
$status = strtoupper(trim($_GET['status'] ?? 'ALL'));
$search = strtolower(trim($_GET['search'] ?? ''));

$reports = DB::all('reports');

// Apply filters
$filtered = array_filter($reports, function($r) use ($category, $severity, $status, $search) {
    if ($category !== 'ALL') {
        $cat = strtoupper($r['category'] ?? $r['issue_type'] ?? 'OTHER');
        if ($cat !== $category) return false;
    }

    if ($severity !== 'ALL') {
        $sev = strtoupper($r['severity'] ?? 'MEDIUM');
        if ($sev !== $severity) return false;
    }

    if ($status !== 'ALL') {
        $st = strtoupper($r['status'] ?? 'PENDING');
        if ($st !== $status) return false;
    }

    if (!empty($search)) {
        $haystack = strtolower(($r['title'] ?? '') . ' ' . ($r['description'] ?? '') . ' ' . ($r['address'] ?? ''));
        if (!str_contains($haystack, $search)) return false;
    }

    return true;
});

// Sort by latest first
usort($filtered, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

echo json_encode([
    'success' => true,
    'count' => count($filtered),
    'reports' => array_values($filtered)
]);
