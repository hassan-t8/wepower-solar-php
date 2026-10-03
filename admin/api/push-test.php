<?php
/**
 * Send a test push to the logged-in admin's registered devices and report
 * per-device results, so push setup problems are visible right away.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/notifications.php';
requireAdminApi();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

ensureNotificationSchema();
if (!isPushConfigured()) jsonResponse(['error' => 'Push is not configured: switch it on and add the Firebase service account first.'], 409);

$adminId = (int) currentAdmin()['id'];
if (!countRows('SELECT COUNT(*) c FROM push_subscriptions WHERE admin_id = ?', [$adminId])) {
    jsonResponse(['error' => 'No devices registered yet — click "Enable push on this device" first.'], 409);
}

// The test ignores the per-type switches: it goes to all of this admin's devices.
$results = [];
foreach (fetchAll('SELECT * FROM push_subscriptions WHERE admin_id = ?', [$adminId]) as $sub) {
    try {
        $results[] = deliverPush($sub, [
            'id' => 'test-' . time(), 'type' => 'test',
            'title' => 'WePower test notification',
            'body' => 'Push notifications are working on this device.',
            'url' => '/admin/notifications.php',
        ]);
    } catch (Throwable $e) {
        $results[] = ['ok' => false, 'error' => $e->getMessage()];
    }
}
$sent = count(array_filter($results, fn($r) => $r['ok']));
$errors = array_values(array_unique(array_filter(array_map(fn($r) => $r['error'] ?? null, $results))));
jsonResponse(['success' => $sent > 0, 'sent' => $sent, 'total' => count($results), 'errors' => $errors], $sent > 0 ? 200 : 502);
