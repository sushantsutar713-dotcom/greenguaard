<?php
/**
 * GreenGuard — Citizen & Admin Login Portal
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (Auth::check()) {
    if (Auth::isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        $result = Auth::attempt($email, $password);
        if ($result['success']) {
            $_SESSION['flash_success'] = 'Welcome back, ' . ($result['user']['name'] ?? 'Guardian') . '!';
            header('Location: ' . $result['redirect']);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Log In — GreenGuard';
$activeNav = 'login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 520px;">
        <div class="form-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div class="brand-icon" style="margin: 0 auto 1rem; width: 50px; height: 50px; font-size: 1.6rem;">🌱</div>
                <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.5rem;">Welcome to GreenGuard</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Log in to report threats, verify incidents, and track municipal impact.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--status-critical); padding: 0.85rem 1rem; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span>⚠️</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" id="loginForm">
                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email Address</label>
                    <input type="email" id="loginEmail" name="email" class="form-control" placeholder="e.g. priya@greenguard.local" required autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label" for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; font-size: 0.85rem;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; color: var(--text-muted); cursor: pointer;">
                        <input type="checkbox" name="remember" style="accent-color: var(--primary);"> Remember session
                    </label>
                    <a href="about.php" style="color: var(--primary); text-decoration: underline;">Need assistance?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom: 1.5rem;">
                    Sign In to Account →
                </button>
            </form>

            <!-- Quick Hackathon Demo Logins -->
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-glass); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; text-align: center;">
                    ⚡ 1-Click Demo Credentials
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('priya@greenguard.local', 'citizen123')">
                        👤 Citizen Demo
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('admin@greenguard.local', 'admin123')">
                        🛡️ Admin Demo
                    </button>
                </div>
            </div>

            <div style="text-align: center; font-size: 0.9rem; color: var(--text-muted);">
                Don't have an Eco-Guardian account? <a href="register.php" style="color: var(--primary); font-weight: 700;">Create Account</a>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('loginEmail').value = email;
    document.getElementById('loginPassword').value = pass;
    showToast('Demo credentials auto-filled! Click "Sign In"', 'info');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
