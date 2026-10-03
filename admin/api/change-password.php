<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAdminApi();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$currentPassword = (string) ($input['current_password'] ?? '');
$newPassword = (string) ($input['new_password'] ?? '');

if ($currentPassword === '' || $newPassword === '') jsonResponse(['error' => 'Enter your current and new password.'], 400);
if (strlen($newPassword) < 8) jsonResponse(['error' => 'The new password must be at least 8 characters.'], 400);
if (strlen($newPassword) > 72) jsonResponse(['error' => 'The new password must be 72 characters or fewer.'], 400); // bcrypt limit
if ($newPassword === $currentPassword) jsonResponse(['error' => 'The new password must be different from the current one.'], 400);

$admin = fetchOne('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
if (!$admin) jsonResponse(['error' => 'Unauthorized'], 401);
if (!password_verify($currentPassword, $admin['password_hash'])) {
    jsonResponse(['error' => 'Current password is incorrect.'], 401);
}

execute('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($newPassword, PASSWORD_BCRYPT), $admin['id']]);
session_regenerate_id(true); // fresh session id after a credential change
jsonResponse(['success' => true, 'message' => 'Password updated.']);
