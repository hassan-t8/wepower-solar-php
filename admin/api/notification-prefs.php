<?php
/**
 * The logged-in admin's notification on/off switches.
 * GET → { prefs: {type: bool} }   POST {type, enabled} → saves one switch
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();

$adminId = (int) currentAdmin()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $type = (string) ($input['type'] ?? '');
    if (!isset(NOTIFY_TYPES[$type])) jsonResponse(['error' => 'Unknown notification type'], 400);
    setNotificationPref($adminId, $type, !empty($input['enabled']));
}
jsonResponse(['success' => true, 'prefs' => getNotificationPrefs($adminId)]);
