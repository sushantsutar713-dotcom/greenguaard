<?php
/**
 * GreenGuard — Authentication & Session Management
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

class Auth {
    /**
     * Check if a user is currently logged in
     */
    public static function check(): bool {
        return isset($_SESSION['user']) && !empty($_SESSION['user']['user_id']);
    }

    /**
     * Check if current user is an Admin
     */
    public static function isAdmin(): bool {
        return self::check() && ($_SESSION['user']['role'] ?? '') === 'ADMIN';
    }

    /**
     * Get the authenticated user object or null
     */
    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get user ID or null
     */
    public static function id(): ?int {
        return $_SESSION['user']['user_id'] ?? null;
    }

    /**
     * Attempt to log in with email and password
     */
    public static function attempt(string $email, string $password): array {
        $email = trim(strtolower($email));
        $user = DB::findOne('users', fn($u) => strtolower($u['email'] ?? '') === $email);

        if (!$user) {
            return ['success' => false, 'message' => 'No account found with this email address.'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid password. Please check your credentials.'];
        }

        // Set session
        self::loginUser($user);

        return [
            'success' => true,
            'message' => 'Login successful!',
            'user' => self::sanitizeUser($user),
            'redirect' => $user['role'] === 'ADMIN' ? 'admin/dashboard.php' : 'dashboard.php'
        ];
    }

    /**
     * Register a new citizen account
     */
    public static function register(string $name, string $email, string $password, string $phone = ''): array {
        $name = trim($name);
        $email = trim(strtolower($email));

        if (empty($name) || strlen($name) < 2) {
            return ['success' => false, 'message' => 'Please provide a valid full name.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please provide a valid email address.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
        }

        // Check if email already exists
        $existing = DB::findOne('users', fn($u) => strtolower($u['email'] ?? '') === $email);
        if ($existing) {
            return ['success' => false, 'message' => 'An account with this email address already exists.'];
        }

        $newUser = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'USER',
            'phone' => trim($phone),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $savedUser = DB::insert('users', $newUser, 'user_id');
        self::loginUser($savedUser);

        return [
            'success' => true,
            'message' => 'Account created successfully! Welcome to GreenGuard.',
            'user' => self::sanitizeUser($savedUser),
            'redirect' => 'dashboard.php'
        ];
    }

    /**
     * Set session for user
     */
    public static function loginUser(array $user): void {
        $_SESSION['user'] = self::sanitizeUser($user);
    }

    /**
     * Strip sensitive fields from user array
     */
    public static function sanitizeUser(array $user): array {
        unset($user['password']);
        return $user;
    }

    /**
     * Log out current user
     */
    public static function logout(): void {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    /**
     * Require standard logged-in user
     */
    public static function requireLogin(string $redirectTo = 'login.php'): void {
        if (!self::check()) {
            $_SESSION['flash_error'] = 'Please log in to access this page.';
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Require Admin role
     */
    public static function requireAdmin(string $redirectTo = 'admin/login.php'): void {
        if (!self::isAdmin()) {
            $_SESSION['flash_error'] = 'Admin / Authority privileges required to access this portal.';
            header("Location: $redirectTo");
            exit;
        }
    }

    /**
     * Generate or fetch CSRF token
     */
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrf(?string $token): bool {
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
