<?php
/**
 * Admin login — PHP port of POST /admin/login in routes/admin.js.
 * DB-backed rate limiting replaces the in-memory loginLimiter (10/10min).
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

if (isLoginRateLimited()) {
    jsonResponse(['error' => 'Too many login attempts. Please try again later.'], 429);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$email = clean($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

if (!$email || !$password) {
    jsonResponse(['error' => 'Email and password required'], 400);
}

recordLoginAttempt($email);

$admin = fetchOne('SELECT * FROM admins WHERE email = ?', [$email]);
if (!$admin || !password_verify($password, $admin['password_hash'])) {
    jsonResponse(['error' => 'Invalid credentials'], 401);
}

loginAdmin($admin);
jsonResponse(['success' => true]);
