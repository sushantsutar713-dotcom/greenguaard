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

// Base URL (Change according to your local folder or live domain)
// Local XAMPP: 'http://localhost/hackforge' or 'http://localhost/project'
// InfinityFree: 'https://your-subdomain.infinityfreeapp.com'
define('BASE_URL', 'http://localhost/hackforge');

// Google Gemini API Key
// Get your free API key at: https://aistudio.google.com/
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');

// Storage Paths
define('DATA_PATH', __DIR__ . '/../data/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Debug Mode (Set to true in local development, false in production/InfinityFree)
define('DEBUG_MODE', true);

// Timezone
date_default_timezone_set('Asia/Kolkata');
