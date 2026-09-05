<?php
/**
 * GreenGuard — Admin / Authority Dedicated Login Portal
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (Auth::isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = Auth::attempt($email, $password);
    if ($result['success']) {
        if (Auth::isAdmin()) {
            $_SESSION['flash_success'] = 'Welcome to Municipal Command Center, ' . ($result['user']['name'] ?? 'Officer') . '.';
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Access denied. This account does not possess Administrative / Authority privileges.';
            Auth::logout();
        }
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Authority & Admin Portal — GreenGuard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #070d18;">

    <div class="container" style="max-width: 460px; padding: 1.5rem;">
        <div class="form-card" style="border-top: 4px solid var(--status-critical);">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 54px; height: 54px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 1rem;">
                    🛡️
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.35rem;">Authority Triage Portal</h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Authorized municipal officers, forest authorities &amp; pollution control officers.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--status-critical); padding: 0.85rem 1rem; border-radius: var(--radius-md); font-size: 0.88rem; margin-bottom: 1.5rem;">
                    ⚠️ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label" for="adminEmail">Official Authority Email</label>
                    <input type="email" id="adminEmail" name="email" class="form-control" placeholder="admin@greenguard.local" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="adminPassword">Master Password</label>
                    <input type="password" id="adminPassword" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom: 1.25rem; background: linear-gradient(135deg, #ef4444, #dc2626);">
                    🛡️ Enter Command Triage →
                </button>
            </form>

            <div style="background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border-glass); border-radius: var(--radius-sm); padding: 1rem; text-align: center; margin-bottom: 1.25rem;">
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 0.5rem;">Hackathon Demo Quick Fill</div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillAdminDemo()" style="width: 100%;">
                    ⚡ Fill Admin Credentials (`admin@greenguard.local`)
                </button>
            </div>

            <div style="text-align: center;">
                <a href="../index.php" style="color: var(--text-muted); font-size: 0.85rem;">← Return to Citizen Public Home</a>
            </div>
        </div>
    </div>

    <script src="../js/main.js"></script>
    <script>
    function fillAdminDemo() {
        document.getElementById('adminEmail').value = 'admin@greenguard.local';
        document.getElementById('adminPassword').value = 'admin123';
        showToast('Admin credentials filled!', 'info');
    }
    </script>
</body>
</html>
