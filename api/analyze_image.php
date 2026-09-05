<?php
/**
 * GreenGuard API — Image Threat Analysis Endpoint
 * Analyzes uploaded evidence images via Google Gemini Vision API / Smart AI Heuristics
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ai_service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$contextHint = $_POST['description'] ?? $_POST['hint'] ?? '';
$tempFile = null;

// Case 1: Direct file upload via multipart/form-data
if (isset($_FILES['evidence_photo']) && $_FILES['evidence_photo']['error'] === UPLOAD_ERR_OK) {
    $tempFile = $_FILES['evidence_photo']['tmp_name'];
    $filename = $_FILES['evidence_photo']['name'];
} elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tempFile = $_FILES['image']['tmp_name'];
    $filename = $_FILES['image']['name'];
} elseif (!empty($_POST['image_path'])) {
    // Case 2: Existing sample image path
    $relPath = __DIR__ . '/../' . ltrim($_POST['image_path'], '/\\');
    if (file_exists($relPath)) {
        $tempFile = $relPath;
        $filename = basename($relPath);
    }
}

if (!$tempFile || !file_exists($tempFile)) {
    // If no physical file provided, generate smart estimate from context hint
    $fallbackResult = AIService::getSmartFallback($contextHint ?: 'general pollution', $contextHint);
    echo json_encode([
        'success' => true,
        'ai' => $fallbackResult
    ]);
    exit;
}

// Call AI Service
try {
    $aiAnalysis = AIService::analyzeImage($tempFile, $contextHint);
    echo json_encode([
        'success' => true,
        'ai' => $aiAnalysis
    ]);
} catch (Throwable $e) {
    $fallback = AIService::getSmartFallback($tempFile, $contextHint);
    echo json_encode([
        'success' => true,
        'ai' => $fallback,
        'warning' => 'Fallback engine utilized: ' . $e->getMessage()
    ]);
}
