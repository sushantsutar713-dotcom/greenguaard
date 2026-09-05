<?php
/**
 * GreenGuard - Main Local Configuration File
 * 
 * IMPORTANT: This file is ignored by git (.gitignore) to protect sensitive keys.
 */

// Application Info
if (!defined('APP_NAME')) define('APP_NAME', 'GreenGuard');
if (!defined('APP_TAGLINE')) define('APP_TAGLINE', 'Community-Powered Environmental Threat Detection & Resolution');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');

// Base URL configuration
if (!defined('BASE_URL')) {
    $envBase = getenv('BASE_URL');
    if (!empty($envBase)) {
        define('BASE_URL', rtrim($envBase, '/'));
    } else {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false);
        $protocol = $isHttps ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        // Normalize root folder or use explicit BASE_URL
        $baseDir = rtrim(explode('/admin', explode('/api', $scriptDir)[0])[0], '/');
        define('BASE_URL', $protocol . $host . $baseDir);
    }
}

// Google Gemini API Key
// Can be configured via Render Environment Variables or hardcoded locally
if (!defined('GEMINI_API_KEY')) {
    $envKey = getenv('GEMINI_API_KEY');
    define('GEMINI_API_KEY', !empty($envKey) ? $envKey : 'YOUR_GEMINI_API_KEY_HERE');
}

// Storage Paths
if (!defined('DATA_PATH')) define('DATA_PATH', __DIR__ . '/../data/');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Development / Debug Mode (can be toggled via DEBUG_MODE environment variable)
if (!defined('DEBUG_MODE')) {
    $envDebug = getenv('DEBUG_MODE');
    define('DEBUG_MODE', $envDebug !== false ? filter_var($envDebug, FILTER_VALIDATE_BOOLEAN) : true);
}

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
