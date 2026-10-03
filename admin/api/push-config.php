<?php
/**
 * Push notification provider settings (Admin → Notifications → Push setup).
 * POST {push_enabled, push_vapid_public_key, push_vapid_private_key, push_vapid_subject}
 * The private key is write-only: it's never sent back to the browser.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];
setSetting('push_enabled', !empty($input['push_enabled']) ? 'true' : 'false');
foreach (['push_vapid_public_key', 'push_vapid_subject'] as $k) {
    if (array_key_exists($k, $input)) setSetting($k, trim((string) $input[$k]));
}
// Only overwrite the private key when a new one was typed (the form shows it masked).
if (!empty($input['push_vapid_private_key'])) setSetting('push_vapid_private_key', trim((string) $input['push_vapid_private_key']));

jsonResponse(['success' => true, 'configured' => isPushConfigured()]);
