<?php
/**
 * GreenGuard - Configuration Template (Example)
 * 
 * Rename this file to config.php for local development or production.
 * NEVER commit config.php containing real API keys to GitHub.
 */

// Application Environment
define('APP_NAME', 'GreenGuard');
define('APP_TAGLINE', 'Community-Powered Environmental Threat Detection & Resolution');
define('APP_VERSION', '1.0.0');

// Base URL (Auto-detected or override via BASE_URL environment variable)
if (!defined('BASE_URL')) {
    $envBase = getenv('BASE_URL');
    if (!empty($envBase)) {
        define('BASE_URL', rtrim($envBase, '/'));
    } else {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $baseDir = rtrim(explode('/admin', explode('/api', $scriptDir)[0])[0], '/');
        define('BASE_URL', $protocol . $host . $baseDir);
    }
}

// Google Gemini API Key (Can be set via GEMINI_API_KEY environment variable)
// Get your free API key at: https://aistudio.google.com/
if (!defined('GEMINI_API_KEY')) {
    $envKey = getenv('GEMINI_API_KEY');
    define('GEMINI_API_KEY', !empty($envKey) ? $envKey : 'YOUR_GEMINI_API_KEY_HERE');
}

// Storage Paths
if (!defined('DATA_PATH')) define('DATA_PATH', __DIR__ . '/../data/');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Debug Mode (Set to false on production servers like Render / InfinityFree)
if (!defined('DEBUG_MODE')) {
    $envDebug = getenv('DEBUG_MODE');
    define('DEBUG_MODE', $envDebug !== false ? filter_var($envDebug, FILTER_VALIDATE_BOOLEAN) : false);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');
