<?php
/**
 * GreenGuard API — Environmental Incident Submission Endpoint
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/priority_calculator.php';
require_once __DIR__ . '/../includes/ai_service.php';

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

$currentUser = Auth::user();
$userId = Auth::id() ?: 2; // Default to demo citizen if guest reporting
$userName = $currentUser['name'] ?? 'Citizen Guardian';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    } else {
        header('Location: ../report.php');
    }
    exit;
}

$title = trim($_POST['title'] ?? '');
$category = trim(strtoupper($_POST['category'] ?? 'OTHER'));
$description = trim($_POST['description'] ?? '');
$severity = trim(strtoupper($_POST['severity'] ?? 'MEDIUM'));
$latitude = isset($_POST['latitude']) && is_numeric($_POST['latitude']) ? (float)$_POST['latitude'] : 19.0760;
$longitude = isset($_POST['longitude']) && is_numeric($_POST['longitude']) ? (float)$_POST['longitude'] : 72.8777;
$address = trim($_POST['address'] ?? "Coordinates: {$latitude}, {$longitude}");
$contactPhone = trim($_POST['contact_phone'] ?? '');

// Validation
if (empty($title) || strlen($title) < 4) {
    $errorMsg = 'Please provide a descriptive title for the environmental threat.';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    } else {
        $_SESSION['flash_error'] = $errorMsg;
        header('Location: ../report.php');
        exit;
    }
}

if (empty($description)) {
    $errorMsg = 'Please provide detailed description of the incident.';
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    } else {
        $_SESSION['flash_error'] = $errorMsg;
        header('Location: ../report.php');
        exit;
    }
}

// Handle Photo Upload
$imagePath = 'uploads/sample_dumping.svg'; // Safe default

if (isset($_FILES['evidence_photo']) && $_FILES['evidence_photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['evidence_photo'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    $fileMime = mime_content_type($file['tmp_name']) ?: $file['type'];

    if (in_array($fileMime, $allowedTypes) || preg_match('/\.(jpg|jpeg|png|webp|svg)$/i', $file['name'])) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $newFilename = 'evidence_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $targetPath = __DIR__ . '/../uploads/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/' . $newFilename;
        }
    }
} elseif (!empty($_POST['selected_sample_image'])) {
    $imagePath = trim($_POST['selected_sample_image']);
}

// AI Analysis data payload
$aiPayload = null;
if (!empty($_POST['ai_category'])) {
    $aiPayload = [
        'source' => $_POST['ai_source'] ?? 'Google Gemini 1.5 Flash Vision',
        'category' => $_POST['ai_category'],
        'confidence' => (int)($_POST['ai_confidence'] ?? 92),
        'suggested_severity' => $_POST['ai_severity'] ?? $severity,
        'description' => $_POST['ai_description'] ?? 'Threat analyzed by vision model.',
        'environmental_impact' => $_POST['ai_impact'] ?? 'Identified risk to ecological surroundings.',
        'recommendation' => $_POST['ai_recommendation'] ?? 'Municipal inspection required.'
    ];
} else {
    // Run instant analysis
    $aiPayload = AIService::analyzeImage(__DIR__ . '/../' . $imagePath, $title . ' ' . $description);
}

// Build Report Object
$newReport = [
    'user_id' => $userId,
    'user_name' => $userName,
    'title' => $title,
    'category' => $category,
    'issue_type' => $category,
    'description' => $description,
    'image_path' => $imagePath,
    'latitude' => $latitude,
    'longitude' => $longitude,
    'address' => $address,
    'contact_phone' => $contactPhone,
    'reported_at' => date('Y-m-d H:i:s'),
    'severity' => in_array($severity, ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL']) ? $severity : 'MEDIUM',
    'status' => 'PENDING',
    'priority_score' => 50, // Will be recalculated
    'department' => 'Pending Triage',
    'ai' => $aiPayload,
    'community' => [
        'confirmations' => 1,
        'disputes' => 0,
        'evidence_count' => 1
    ],
    'community_users' => [
        [
            'user_id' => $userId,
            'action' => 'CONFIRM',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ],
    'admin_notes' => '',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

// Calculate Priority Score
$allReports = DB::all('reports');
$priorityResult = PriorityCalculator::calculate($newReport, $allReports);
$newReport['priority_score'] = $priorityResult['score'];

// Insert into DB
$savedReport = DB::insert('reports', $newReport, 'report_id');
$reportId = $savedReport['report_id'];

// Create Notification for user
DB::insert('notifications', [
    'user_id' => $userId,
    'report_id' => $reportId,
    'title' => 'Report Logged Successfully',
    'message' => "Your environmental incident report #{$reportId} ({$title}) has been submitted and is currently PENDING VERIFICATION.",
    'type' => 'SUBMITTED',
    'is_read' => false,
    'created_at' => date('Y-m-d H:i:s')
], 'notification_id');

$_SESSION['flash_success'] = "Incident report #{$reportId} submitted successfully! Status: Pending Verification.";

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'report_id' => $reportId,
        'message' => "Incident report #{$reportId} submitted successfully!",
        'redirect' => "report_details.php?id={$reportId}"
    ]);
} else {
    header("Location: ../report_details.php?id={$reportId}");
}
exit;
