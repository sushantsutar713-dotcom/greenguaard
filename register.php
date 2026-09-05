<?php
/**
 * GreenGuard — Citizen Registration Portal
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill out all required fields.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Passwords do not match. Please verify your password.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $result = Auth::register($name, $email, $password, $phone);
        if ($result['success']) {
            $_SESSION['flash_success'] = 'Welcome to GreenGuard, ' . htmlspecialchars($name) . '! Your Eco-Guardian account is active.';
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Join GreenGuard — Register as Citizen Guardian';
$activeNav = 'register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="section" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 580px;">
        <div class="form-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div class="brand-icon" style="margin: 0 auto 1rem; width: 50px; height: 50px; font-size: 1.6rem;">🌱</div>
                <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.5rem;">Join GreenGuard Community</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Empower your community. Report threats with AI verification &amp; earn Eco-Guardian rank.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: var(--status-critical); padding: 0.85rem 1rem; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span>⚠️</span> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label class="form-label" for="regName">Full Name <span class="required">*</span></label>
                    <input type="text" id="regName" name="name" class="form-control" placeholder="e.g. Ananya Sen" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="regEmail">Email Address <span class="required">*</span></label>
                        <input type="email" id="regEmail" name="email" class="form-control" placeholder="e.g. ananya@domain.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="regPhone">Phone Number (Optional)</label>
                        <input type="tel" id="regPhone" name="phone" class="form-control" placeholder="+91 98000 00000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="regPass">Password <span class="required">*</span></label>
                        <input type="password" id="regPass" name="password" class="form-control" placeholder="Min. 6 characters" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="regPassConf">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="regPassConf" name="password_confirm" class="form-control" placeholder="Repeat password" required minlength="6">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
                    By joining, you agree to report genuine environmental hazards under the GreenGuard Community Guidelines.
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom: 1.5rem;">
                    🌱 Create Eco-Guardian Account →
                </button>
            </form>

            <div style="text-align: center; font-size: 0.9rem; color: var(--text-muted);">
                Already registered? <a href="login.php" style="color: var(--primary); font-weight: 700;">Log In here</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
