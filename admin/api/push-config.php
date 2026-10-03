<?php
/**
 * Firebase push settings (Admin → Notifications → Push setup).
 * POST {push_enabled, fcm_api_key, fcm_project_id, fcm_sender_id, fcm_app_id,
 *       fcm_vapid_key, fcm_service_account}
 * The service account is write-only: it's validated, stored server-side and
 * never sent back to the browser.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Only replace the service account when a new one was pasted.
$saRaw = trim((string) ($input['fcm_service_account'] ?? ''));
if ($saRaw !== '') {
    $sa = json_decode($saRaw, true);
    if (!is_array($sa) || ($sa['type'] ?? '') !== 'service_account' || empty($sa['client_email']) || empty($sa['private_key'])) {
        jsonResponse(['error' => 'That is not a Firebase service-account JSON file (expected "type": "service_account").'], 400);
    }
    if (!openssl_pkey_get_private($sa['private_key'])) {
        jsonResponse(['error' => 'The service account private key could not be read.'], 400);
    }
    setSetting('fcm_service_account', json_encode($sa));
    setSetting('fcm_access_token_cache', ''); // new credentials → fetch a fresh token
}

foreach (['fcm_api_key', 'fcm_project_id', 'fcm_sender_id', 'fcm_app_id', 'fcm_vapid_key'] as $k) {
    if (array_key_exists($k, $input)) setSetting($k, trim((string) $input[$k]));
}
setSetting('push_enabled', !empty($input['push_enabled']) ? 'true' : 'false');

$sa = fcmServiceAccount();
jsonResponse([
    'success' => true,
    'configured' => isPushConfigured(),
    'service_account_email' => $sa['client_email'] ?? null,
]);
