<?php
/**
 * Admin session bootstrap + guards.
 * PHP-native-session equivalent of express-session + the requireAuth
 * middleware in routes/admin.js. Include this at the top of every
 * admin/*.php and admin/api/*.php file.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 8 * 3600, // 8h, matches the old express-session cookie maxAge
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (APP_ENV === 'production'), // requires HTTPS in production (Hostinger gives free SSL)
    ]);
    session_start();
}

/** True if an admin is currently logged in. */
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

/**
 * Guard for admin PAGE requests — redirects to login if not authenticated.
 * Call at the very top of every admin/*.php page (not admin/api/*.php).
 */
function requireAdminPage(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: /admin/login');
        exit;
    }
}

/**
 * Guard for admin API requests — returns 401 JSON if not authenticated.
 * Call at the very top of every admin/api/*.php file.
 */
function requireAdminApi(): void
{
    if (!isAdminLoggedIn()) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

function currentAdmin(): array
{
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'email' => $_SESSION['admin_email'] ?? null,
        'name' => $_SESSION['admin_name'] ?? null,
    ];
}

function loginAdmin(array $admin): void
{
    session_regenerate_id(true); // prevent session fixation
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_name'] = $admin['name'];
}

function logoutAdmin(): void
{
    $_SESSION = [];
    session_destroy();
}
