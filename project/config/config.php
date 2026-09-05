<?php
/**
 * GreenGuard - Main Local Configuration File
 * 
 * IMPORTANT: This file is ignored by git (.gitignore) to protect sensitive keys.
 */

// Application Info
define('APP_NAME', 'GreenGuard');
define('APP_TAGLINE', 'Community-Powered Environmental Threat Detection & Resolution');
define('APP_VERSION', '1.0.0');

// Base URL configuration
// For local XAMPP: adjust to match your local folder name if needed (e.g. /hackforge or /project)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Normalize root folder or use explicit BASE_URL
    $baseDir = rtrim(explode('/admin', explode('/api', $scriptDir)[0])[0], '/');
    define('BASE_URL', $protocol . $host . $baseDir);
}

// Google Gemini API Key
// Replace with your Google AI Studio key: https://aistudio.google.com/
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');

// Storage Paths
define('DATA_PATH', __DIR__ . '/../data/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Development / Debug Mode (Set to false on live servers like InfinityFree)
define('DEBUG_MODE', true);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting based on debug mode
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
