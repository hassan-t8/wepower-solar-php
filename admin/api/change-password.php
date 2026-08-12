<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$currentPassword = (string) ($input['current_password'] ?? '');
$newPassword = (string) ($input['new_password'] ?? '');

if (!$currentPassword || !$newPassword) jsonResponse(['error' => 'Both fields required'], 400);
if (strlen($newPassword) < 8) jsonResponse(['error' => 'Password must be at least 8 characters'], 400);

$admin = fetchOne('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
if (!password_verify($currentPassword, $admin['password_hash'])) {
    jsonResponse(['error' => 'Current password incorrect'], 401);
}

execute('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($newPassword, PASSWORD_BCRYPT), $admin['id']]);
jsonResponse(['success' => true, 'message' => 'Password updated successfully']);
