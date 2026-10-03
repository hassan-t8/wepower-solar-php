<?php
/**
 * Save / remove this device's Firebase Cloud Messaging token for the logged-in admin.
 * POST {token}  |  POST {unsubscribe: token}
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

ensureNotificationSchema();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!empty($input['unsubscribe'])) {
    execute('DELETE FROM push_subscriptions WHERE endpoint = ? AND admin_id = ?', [(string) $input['unsubscribe'], (int) currentAdmin()['id']]);
    jsonResponse(['success' => true]);
}

$token = trim((string) ($input['token'] ?? ''));
if ($token === '' || strlen($token) > 500 || !preg_match('/^[A-Za-z0-9_:\-]+$/', $token)) {
    jsonResponse(['error' => 'Invalid device token'], 400);
}

execute(
    'INSERT INTO push_subscriptions (admin_id, endpoint, keys_json, user_agent) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE admin_id = VALUES(admin_id), user_agent = VALUES(user_agent)',
    [(int) currentAdmin()['id'], $token, json_encode(['provider' => 'fcm']), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250)]
);
jsonResponse(['success' => true, 'devices' => countRows('SELECT COUNT(*) c FROM push_subscriptions WHERE admin_id = ?', [(int) currentAdmin()['id']])]);
