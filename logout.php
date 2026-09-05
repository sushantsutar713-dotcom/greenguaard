<?php
/**
 * GreenGuard — Session Termination
 */

require_once __DIR__ . '/includes/auth.php';

Auth::logout();
session_start();
$_SESSION['flash_success'] = 'You have been successfully logged out.';
header('Location: index.php');
exit;
