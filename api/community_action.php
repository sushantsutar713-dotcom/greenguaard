<?php
/**
 * GreenGuard API — Community Action (Upvote / Confirm / Dispute)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/priority_calculator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$reportId = (int)($_POST['report_id'] ?? 0);
$action = strtoupper(trim($_POST['action'] ?? 'CONFIRM'));
$userId = Auth::id() ?: 2; // Default to demo citizen if guest

$report = DB::findById('reports', $reportId, 'report_id');
if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found.']);
    exit;
}

// Check existing upvote
$existingUpvote = DB::findOne('upvotes', fn($u) => 
    (int)($u['report_id'] ?? 0) === $reportId && (int)($u['user_id'] ?? 0) === $userId
);

if ($action === 'CONFIRM') {
    if (!$existingUpvote) {
        DB::insert('upvotes', [
            'report_id' => $reportId,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s')
        ], 'upvote_id');

        $currentConfirms = (int)($report['community']['confirmations'] ?? 0) + 1;
        $report['community']['confirmations'] = $currentConfirms;

        // Recalculate priority score
        $allReports = DB::all('reports');
        $priorityCalc = PriorityCalculator::calculate($report, $allReports);
        $report['priority_score'] = $priorityCalc['score'];

        DB::update('reports', $reportId, [
            'community' => $report['community'],
            'priority_score' => $report['priority_score']
        ], 'report_id');

        echo json_encode([
            'success' => true,
            'message' => 'Your confirmation has been added! Priority score updated.',
            'confirmations' => $currentConfirms,
            'priority_score' => $report['priority_score'],
            'priority_level' => $priorityCalc['level']
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'You have already confirmed this environmental report.',
            'confirmations' => (int)($report['community']['confirmations'] ?? 0)
        ]);
        exit;
    }
} elseif ($action === 'DISPUTE') {
    $currentDisputes = (int)($report['community']['disputes'] ?? 0) + 1;
    $report['community']['disputes'] = $currentDisputes;

    $allReports = DB::all('reports');
    $priorityCalc = PriorityCalculator::calculate($report, $allReports);
    $report['priority_score'] = $priorityCalc['score'];

    DB::update('reports', $reportId, [
        'community' => $report['community'],
        'priority_score' => $report['priority_score']
    ], 'report_id');

    echo json_encode([
        'success' => true,
        'message' => 'Dispute recorded for authority review.',
        'disputes' => $currentDisputes,
        'priority_score' => $report['priority_score']
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
