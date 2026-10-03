<?php
/**
 * Save / remove this device's browser push subscription for the logged-in admin.
 * POST {subscription: PushSubscription JSON}  |  POST {unsubscribe: endpoint}
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

ensureNotificationSchema();
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!empty($input['unsubscribe'])) {
    execute('DELETE FROM push_subscriptions WHERE endpoint = ?', [(string) $input['unsubscribe']]);
    jsonResponse(['success' => true]);
}

if (!isPushConfigured()) jsonResponse(['error' => 'Push notifications are not configured yet.'], 409);

$sub = $input['subscription'] ?? null;
$endpoint = is_array($sub) ? (string) ($sub['endpoint'] ?? '') : '';
if (!preg_match('#^https://#', $endpoint) || strlen($endpoint) > 500) jsonResponse(['error' => 'Invalid subscription'], 400);

execute(
    'INSERT INTO push_subscriptions (admin_id, endpoint, keys_json, user_agent) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE admin_id = VALUES(admin_id), keys_json = VALUES(keys_json), user_agent = VALUES(user_agent)',
    [(int) currentAdmin()['id'], $endpoint, json_encode($sub['keys'] ?? []), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250)]
);
jsonResponse(['success' => true]);
